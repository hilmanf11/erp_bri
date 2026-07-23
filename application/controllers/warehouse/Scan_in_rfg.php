<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_in_rfg extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('serial_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/scan_in_rfg');
        } else {
            redirect('error_access');
        }
    }

    public function getAllScannedData()
    {
        $page = $this->input->get('page'); // Mendapatkan nomor halaman
        $rows = $this->input->get('rows'); // Mendapatkan jumlah baris per halaman
        $offset = ($page - 1) * $rows; // Menghitung offset
        $today = date('Y-m-d'); // Ambil tanggal hari ini

        $this->db->select('a.*, b.number as item_number, b.name as item_name');
        $this->db->from('fg_scan_in_label a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('DATE(a.scan_date)', $today); // Filter hanya untuk scan hari ini
        $this->db->limit($rows, $offset); // Pagination
        $records = $this->db->get()->result_array();

        // Menghitung total baris
        $this->db->where('DATE(scan_date)', $today);
        $totalRows = $this->db->count_all_results('fg_scan_in_label');

        $result['total'] = $totalRows;
        $result['rows'] = $records;

        echo json_encode($result);
    }

    public function getSerial()
    {
        if ($this->input->get()) {
            $serial_label = $this->input->get('serial_label');
            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('fg_scan_in_label a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.serial_label', $serial_label);
            $this->db->group_by('b.number');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getSerialLabelV1()
    {
        if ($this->input->post()) {
            $serial_label = $this->input->post('serial_label');

            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
            $this->db->from('label_packing_detail a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.serial_label', $serial_label);
            $this->db->group_by('a.serial_label');

            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }


    public function getSerialLabel()
    {
        if (!$this->input->post()) {
            return;
        }

        $serial_label = $this->input->post('serial_label');

        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('label_packing_detail a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.serial_label', $serial_label);
        $this->db->group_by('a.serial_label');
        $rows = $this->db->get()->result_array();

        if (!empty($rows)) {

            echo json_encode([
                'source' => 'packing',
                'total'  => count($rows),
                'rows'   => $rows
            ]);
            return;
        }

        $label = $this->db->get_where('fg_visual_checker_label', [
            'serial_label' => $serial_label
        ])->row_array();

        if (!$label) {
            echo json_encode([
                'total' => 0
            ]);
            return;
        }

        if ($label['status'] == 1) {
            echo json_encode([
                'title'   => 'Available',
                'message' => 'Label has already been scanned',
                'theme'   => 'error'
            ]);
            return;
        }

        $this->db->select("a.item_fg_id, a.type_status, b.serial_label");
        $this->db->from("scan_visual_checker_detail a");
        $this->db->join("fg_visual_checker_label b", "a.scan_id = b.scan_id and a.item_fg_id = b.item_fg_id");
        $this->db->where("b.serial_label", $serial_label);
        $this->db->limit(1);
        $labelScanVcReturn = $this->db->get()->row_array();

        if ($labelScanVcReturn && $labelScanVcReturn['type_status'] == 'completed') {
            echo json_encode([
                'title' => 'Process Scanned',
                'message' => 'Label is currently being processed in Visual Checker',
                'theme'   => 'error',
                'data' => $label
            ]);
            return;
        }

        $this->db->select("
            a.item_fg_id,
            a.serial_label,
            a.qty as qty_packing,
            c.number as item_number,
            c.name as item_name,
            c.uom
        ");
        $this->db->from('fg_visual_checker_label a');
        $this->db->join('item_fg c','a.item_fg_id=c.id');
        $this->db->where('a.serial_label',$serial_label);
        $rows = $this->db->get()->result_array();

        echo json_encode([
            'source' => 'visual_checker',
            'total'  => count($rows),
            'rows'   => $rows
        ]);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $post['scan_date'] = date('Y-m-d H:i:s');
                $post['scan_by'] = $this->session->username;
                $post['transaction_type'] = 'REFG-001';

                $source = $post['source'];

                // Validasi berdasarkan source
                if ($source == 'packing') {

                    $label_exists = $this->db
                        ->where('serial_label', $post['serial_label'])
                        ->where('item_fg_id', $post['item_fg_id'])
                        ->count_all_results('label_packing_detail');

                    if ($label_exists == 0) {
                        echo json_encode([
                            "title"   => "Not Registered",
                            "message" => "Label not found in Packing Detail",
                            "theme"   => "error"
                        ]);
                        return;
                    }

                } else if ($source == 'visual_checker') {

                    $label_exists = $this->db
                        ->where('serial_label', $post['serial_label'])
                        ->where('item_fg_id', $post['item_fg_id'])
                        ->count_all_results('fg_visual_checker_label');

                    if ($label_exists == 0) {
                        echo json_encode([
                            "title"   => "Not Registered",
                            "message" => "Label not found in Visual Checker",
                            "theme"   => "error"
                        ]);
                        return;
                    }

                } else {
                    echo json_encode([
                        "title"   => "Not Registered",
                        "message" => "Unknown label source",
                        "theme"   => "error"
                    ]);
                    return;
                }

                // Cek apakah sudah pernah di-scan sebelumnya
                $already_scanned = $this->db
                            ->where('serial_label', $post['serial_label'])
                            ->where('item_fg_id', $post['item_fg_id'])
                            ->get('fg_scan_in_label')
                            ->num_rows();

                if ($already_scanned > 0) {
                    echo json_encode(array(
                        "theme" => "error", 
                        "title" => "Available", 
                        "message" => "Data Receipt FG has been Scanning"
                    ));
                    return;
                }

                unset($post['source']);
                $this->db->trans_begin();

                $send = $this->crud->create('fg_scan_in_label', $post);
                if ($send) {
                    if ($source == 'visual_checker') {
                        $this->crud->update('fg_visual_checker_label', [
                            'serial_label' => $post['serial_label'],
                            'status'       => 0
                        ], ['status' => 1]);

                        $this->crud->update('fg_visual_checker_label_lot_tracking', [
                            'serial_label' => $post['serial_label'],
                            'status'       => 0
                        ], ['status' => 1]);

                        $message = "Data Visual Checker Label has been scanned successfully.";
                    } else {

                        $message = "Data Receipt FG has been scanned successfully.";
                    }

                    if ($this->db->trans_status() === FALSE) {
                        $this->db->trans_rollback();

                        echo json_encode([
                            "theme"   => "error",
                            "title"   => "Error",
                            "message" => "Failed to save data"
                        ]);

                    } else {
                        $this->db->trans_commit();

                        echo json_encode([
                            "theme"   => "success",
                            "title"   => "Success",
                            "message" => $message
                        ]);

                    }

                } else {
                    $this->db->trans_rollback();

                    echo json_encode([
                        "theme"   => "error",
                        "title"   => "Error",
                        "message" => "Failed to save data"
                    ]);

                }

                return;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
