<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_in_rework extends CI_Controller
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
        $this->form_validation->set_rules('serial_label', 'Serial Label', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/scan_in_rework');
        } else {
            redirect('error_access');
        }
    }

    public function getScanInRework()
    {
        $this->db->select('
            a.scan_id,
            a.item_fg_id,
            a.workorder_label,
            a.serial_label,
            a.source,
            a.qty,
            a.created_date,
            b.number as item_fg_number, 
            b.name as item_fg_name
        ');

        $this->db->from('scan_in_rework a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.type_status', 'scanning');
        $this->db->order_by('a.created_date', 'DESC');

        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => count($records),
            'rows'  => $records
        ]);
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $serial_label = $this->input->post('serial_label');

            $label = $this->db->get_where('rework_visual_checker_label', [
                'serial_label' => $serial_label
            ])->row_array();

            if (!$label) {
                echo json_encode([
                    'title' => 'Not Found',
                    'message' => 'Label not found!'
                ]);
                return;
            }

            if ($label['status'] == 1) {
                echo json_encode([
                    'title' => 'Available',
                    'message' => 'Label has already been scanned',
                    'theme' => 'error'
                ]);
                return;
            }

            $this->db->select("a.item_fg_id, a.type_status, b.serial_label, b.source");
            $this->db->from("scan_visual_checker_detail a");
            $this->db->join("rework_visual_checker_label b", "a.scan_id = b.scan_id and a.item_fg_id = b.item_fg_id");
            $this->db->where("b.serial_label", $serial_label);
            $this->db->limit(1);
            $labelScanVcRework = $this->db->get()->row_array();

            if ($labelScanVcRework && $labelScanVcRework['type_status'] == 'completed') {
                echo json_encode([
                    'title' => 'Process Scanned',
                    'message' => 'Label is currently being processed in Visual Checker',
                    'data' => $label
                ]);
                return;
            }

            if ($labelScanVcRework && $labelScanVcRework['source'] == 'IN') {
                echo json_encode([
                    'title' => 'Invalid Scan',
                    'message' => 'Internal rework label cannot be scanned',
                    'data' => $label
                ]);
                return;
            }

            $this->db->select("a.item_fg_id, a.serial_label, b.workorder_label, b.compound_lot_no, b.source, b.qty, c.workorder, c.workorder_label, c.delivery_note_no");
            $this->db->from('rework_visual_checker_label a');
            $this->db->join('rework_visual_checker_label_lot_tracking b', 'a.serial_label = b.serial_label');
            $this->db->join('scan_visual_checker_detail c', 'a.scan_id = c.scan_id and b.workorder_label = c.workorder_label');
            $this->db->where('a.serial_label', $serial_label);
            $this->db->group_by([
                'b.serial_label',
                'b.workorder_label',
            ]);

            $details = $this->db->get()->result_array();

            echo json_encode([ 
                'title' => 'success', 
                'header' => $label,
                'details' => array_values($details)
            ]);
        }
    }

    public function getSummary()
    {
        $records = $this->db
            ->select("
                SUM(a.qty) as qty_total,
                a.serial_label,
                b.number as item_fg_number,
                b.name as item_fg_name
            ")
            ->from('scan_in_rework a')
            ->join('item_fg b', 'a.item_fg_id = b.id')
            ->where('a.qty > 0')
            ->where('a.type_status', 'scanning')
            ->order_by('b.number', 'ASC')
            ->group_by(['scan_id', 'item_fg_id'])
            ->get()
            ->result_array();

        echo json_encode([
            "total" => count($records),
            "rows"  => $records
        ]);
    }

    public function create()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();
        $header = json_decode($post['header'], true);
        $details = json_decode($post['details'], true);

        if (!$header || !$details) {
            return $this->jsonResponse(
                'Error',
                'Data is missing',
                'error'
            );
        }

        $this->db->trans_begin();

        try {

            $serial_label = $header['serial_label'];

            $label_header = $this->db->query("
                SELECT serial_label, item_fg_id, compound_lot_no, status
                FROM rework_visual_checker_label
                WHERE serial_label = ?
                FOR UPDATE
            ", [$serial_label])->row();

            if (!$label_header) {
                throw new Exception(json_encode([
                    'title'=>'Not Found',
                    'message'=>'Label not found!',
                    'theme'=>'error',
                ]));
            }

            if ($label_header->status == 1) {
                throw new Exception(json_encode([
                    'title'=>'Available',
                    'message'=>'Label has already been scanned',
                    'theme'=>'warning',
                ]));
            }

            $label_items = $this->db->query("
                SELECT serial_label, compound_lot_no, workorder_label, status, qty
                FROM rework_visual_checker_label_lot_tracking
                WHERE serial_label = ?
                FOR UPDATE
            ", [$serial_label])->result();

            if (!$label_items) {
                throw new Exception(json_encode([
                    'title'=>'Not Found',
                    'message'=>'Label not found!',
                    'theme'=>'error',
                ]));
            }

            $session_row = $this->db->select('scan_id, workorder_label, serial_label')
                ->from('scan_in_rework')
                ->where('type_status', 'scanning')
                ->where('status', 0)
                ->order_by('created_date', 'desc')
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

            foreach ($details as $d) {

                $exists = $this->db->get_where('scan_in_rework', [
                    'serial_label' => $serial_label,
                    'workorder_label' => $d['workorder_label']
                ])->row();

                if ($exists) {
                    continue;
                }

                $workorder_label = $d['workorder_label'];

                $base = explode('-', $workorder_label)[0];
                $last3 = substr($base, -3);
                $prefix = substr($base, 0, -3);

                $workorder = $prefix . '-' . $last3;

                $data = [
                    'scan_id'         => $scan_id,
                    'serial_label'    => $serial_label,
                    'item_fg_id'      => $header['item_fg_id'],
                    'workorder'       => $workorder,
                    'workorder_label' => $d['workorder_label'],
                    'source'          => $d['source'],
                    'delivery_note_no'=> $d['delivery_note_no'],
                    'compound_lot_no' => $d['compound_lot_no'],
                    'qty'             => $d['qty'],
                    'type_status'     => 'scanning',
                    'status'          => 0,
                ];

                $this->crud->create('scan_in_rework', $data);
            }

            $this->crud->update('rework_visual_checker_label', [
                'serial_label' => $serial_label
            ], [
                'status' => 1
            ]);

            $this->crud->update('rework_visual_checker_label_lot_tracking', [
                'serial_label' => $serial_label
            ], [
                'status' => 1
            ]);

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->db->trans_commit();

            return $this->jsonResponse(
                'Success',
                'Data berhasil disimpan',
                'success'
            );

        } catch (Exception $e) {

            $this->db->trans_rollback();

            $json = @json_decode($e->getMessage(), true);

            if ($json) {
                return $this->jsonResponse(
                    $json['title'],
                    $json['message'],
                    $json['theme']
                );
            }

            return $this->jsonResponse(
                'Error',
                $e->getMessage(),
                'error'
            );
        }
    }


    private function jsonResponse($title, $message, $theme = 'error')
    {
        echo json_encode([
            'title'   => $title,
            'message' => $message,
            'theme'   => $theme
        ]);
        return;
    }

    private function generate_uuid()
    {
        $uuid = $this->uuid->v4();
        return $uuid;
    }


    public function saveSummary()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');

        $this->db->trans_begin();

        try {

            foreach ($items as $post) {
                $updateRework = $this->crud->update('scan_in_rework', [
                        'workorder_label' => $post['workorder_label'],
                        'serial_label' => $post['serial_label'],
                        'item_fg_id'      => $post['item_fg_id']
                    ], ['type_status' => 'completed']
                );

                if (!$updateRework) {
                    throw new Exception("Failed update Scan In Rework {$post['serial_label']}");
                }
            }

            $this->db->trans_commit();

            echo json_encode([
                "title"   => "Success",
                "message" => "Data saved successfully",
                "theme"   => "success"
            ]);

        } catch (Exception $e) {

            $this->db->trans_rollback();

            echo json_encode([
                "title"   => "Failed",
                "message" => $e->getMessage(),
                "theme"   => "error"
            ]);
        }
    }
}