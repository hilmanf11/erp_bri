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

    public function getSerialLabel()
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $post['scan_date'] = date('Y-m-d H:i:s');
                $post['scan_by'] = $this->session->username;
                $post['transaction_type'] = 'REFG-001';

                // Cek apakah label sudah ada di label_packing_detail
                $label_exists = $this->db->where('serial_label', $post['serial_label'])
                                       ->where('item_fg_id', $post['item_fg_id'])
                                       ->get('label_packing_detail')
                                       ->num_rows();

                if ($label_exists == 0) {
                    echo json_encode(array("title" => "Not Registered", "message" => "Label not found in packing detail", "theme" => "error"));
                    return;
                }

                // Cek apakah sudah pernah di-scan sebelumnya
                $already_scanned = $this->db->where('serial_label', $post['serial_label'])
                                          ->where('item_fg_id', $post['item_fg_id'])
                                          ->get('fg_scan_in_label')
                                          ->num_rows();

                if ($already_scanned > 0) {
                    echo json_encode(array("theme" => "error", "title" => "Available", "message" => "Data Receipt FG has been Scanning"));
                    return;
                }

                $send = $this->crud->create('fg_scan_in_label', $post);
                if ($send) {
                    echo json_encode([
                        "theme" => "success",
                        "title" => "Success",
                        "message" => "Data Receipt FG has been Scanning"
                    ]);
                } else {
                    echo json_encode([
                        "theme" => "error",
                        "title" => "Error",
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
