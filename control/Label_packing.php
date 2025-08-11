<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Label_packing extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/label_packing');
        } else {
            redirect('error_access');
        }
    }

    public function readitemsFG()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT id, number as item_number, name as item_name, box_sub FROM item_fg WHERE number like '%$post%' or name like '%$post%'");
        echo json_encode($send);
    }

    public function readitemsRM()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name FROM bom a JOIN item_rm b ON a.item_rm_id = b.id WHERE a.item_fg_id = '$post'");
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $filter_from = $post['filter_from'] ?? "";
            $filter_to = $post['filter_to'] ?? "";
            $filter_shift = $post['filter_shift'] ?? "";
            $filter_product_no = $post['filter_product_no'] ?? "";

            $page = intval($post['page'] ?? 1);
            $rows = intval($post['rows'] ?? 10);
            $offset = ($page - 1) * $rows;
            $result = [];

            $this->db->select('a.*, b.number as item_number, b.name as item_name, c.number as item_rm_number, c.name as material');
            $this->db->from('label_packing a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');

            // Apply filters
            if (!empty($filter_from) && !empty($filter_to)) {
                $this->db->where("DATE(a.trans_date) BETWEEN '$filter_from' AND '$filter_to'");
            } elseif (!empty($filter_from)) {
                $this->db->where('DATE(a.trans_date) >=', $filter_from);
            } elseif (!empty($filter_to)) {
                $this->db->where('DATE(a.trans_date) <=', $filter_to);
            }

            if (!empty($filter_shift)) {
                $this->db->where('a.shift', $filter_shift);
            }

            if (!empty($filter_product_no)) {
                $this->db->like('b.number', $filter_product_no);
            }

            // Count total rows before limiting for pagination
            $totalRows = $this->db->count_all_results('', false);
            
            // Apply pagination
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();

            $result['total'] = $totalRows;
            $result['rows'] = $records;

            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $data = [
                    'trans_date' => $post['trans_date'],
                    'shift' => $post['shift'],
                    'leader' => $post['leader'],
                    'item_fg_id' => $post['item_fg_id'],
                    'item_rm_id' => $post['item_rm_id'], // Ensure this is set correctly
                    'qty_wip' => $post['qty_wip'],
                    'qty_packing' => $post['qty_packing'],
                    'qty_label' => $post['qty_label'],
                    'packing_size' => $post['packing_size'],
                    'compound_lot' => $post['compound_lot'],
                    'prod_date' => date('Y-m-d', strtotime($post['prod_date'])), // Ensure correct date format
                    'operator' => $post['operator'],
                    'qc' => $post['qc'],
                    'created_by' => $this->session->username,
                    'created_date' => date('Y-m-d H:i:s')
                ];
                $send = $this->crud->create('label_packing', $data);
                echo json_encode(['success' => true, 'message' => 'Data saved successfully', 'id' => $send]);
            } else {
                echo json_encode(['success' => false, 'message' => validation_errors()]);
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $deleteLabelPacking = $this->crud->delete('label_packing', ["id" => $data['id']]);
        if ($deleteLabelPacking) {
            echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete data']);
        }
    }

    public function print_label($id)
    {
        // Config retrieval
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Decode ID
        $id = base64_decode($id);

        // Query dengan JOIN untuk mendapatkan number dan name dari item_fg, serta name dari item_rm
        $this->db->select('
            label_packing.*, 
            item_fg.number AS product_no, 
            item_fg.name AS product_name, 
            item_rm.name AS material_name
        ');
        $this->db->from('label_packing');
        $this->db->join('item_fg', 'label_packing.item_fg_id = item_fg.id', 'left');
        $this->db->join('item_rm', 'label_packing.item_rm_id = item_rm.id', 'left');
        $this->db->where('label_packing.id', $id);
        
        $label_packing = $this->db->get()->row();

        if (!$label_packing) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }

        // HTML Template
        $html = '<html>
                    <head>
                        <title>Label Packing ' . $id . '</title>
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 5px; }
                            table { border-collapse: collapse; width: 70mm; height: 50mm; font-size: 10px; border: 1px solid black; table-layout: fixed; }
                            th, td { border: 1px solid black; padding: 2px; text-align: left; }
                            th { text-align: center; font-weight: bold; }
                            .header { text-align: center; font-size: 12px; font-weight: bold; }
                            .logo { text-align: center; width: 90px; padding: 2px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 8px; text-align: center; height: 15mm; vertical-align: bottom; }
                            .qr-code img { width: 25px; height: 25px; }
                        </style>
                    </head>
                    <body>';

        for ($i = 0; $i < $label_packing->qty_label; $i++) {
            $html .= '<table>
                        <tr>
                            <td class="logo">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="30"/>
                            </td>
                            <th colspan="2" class="header">LABEL PACKING</th>
                        </tr>
                        <tr>
                            <td><b>Product No:</b></td>
                            <td colspan="2">' . $label_packing->product_no . '</td>
                        </tr>
                        <tr>
                            <td><b>Product Name:</b></td>
                            <td colspan="2">' . $label_packing->product_name . '</td>
                        </tr>
                        <tr>
                            <td><b>Material:</b></td>
                            <td colspan="2">' . $label_packing->material_name . '</td>
                        </tr>
                        <tr>
                            <td><b>Prod Date:</b></td>
                            <td colspan="2">' . $label_packing->prod_date . '</td>
                        </tr>
                        <tr>
                            <td><b>Pack Date:</b></td>
                            <td colspan="2">' . $label_packing->trans_date . '</td>
                        </tr>
                        <tr>
                            <td><b>Shift:</b></td>
                            <td colspan="2">' . $label_packing->shift . '</td>
                        </tr>
                        <tr>
                            <td><b>Qty/Pack:</b></td>
                            <td colspan="2">' . $label_packing->qty_packing . '</td>
                        </tr>
                        <tr>
                            <td><b>No LOT:</b></td>
                            <td colspan="2">' . $label_packing->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th>Operator</th>
                            <th>QC</th>
                            <th>QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign">' . $label_packing->operator . '</td>
                            <td class="qc-sign">' . $label_packing->qc . '</td>
                            <td class="qr-code">
                                <img src="' . base_url('assets/image/qrcode/' . $label_packing->compound_lot . '.png') . '"/>
                            </td>
                        </tr>
                    </table><br>';
        }

        $html .= '<script>window.print()</script>
                </body>
            </html>';

        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=label_packing_$format.xls");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_shift = $this->input->get('filter_shift');
        $filter_product_no = $this->input->get('filter_product_no');

        // Config
        $config = $this->db->select('*')->from('config')->get()->row();
        if (!$config) {
            echo "Konfigurasi tidak ditemukan.";
            return;
        }

        // Fetch data
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.number as item_rm_number, c.name as material');
        $this->db->from('label_packing a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        if ($filter_from) $this->db->where('a.trans_date >=', $filter_from);
        if ($filter_to) $this->db->where('a.trans_date <=', $filter_to);
        if ($filter_shift) $this->db->where('a.shift', $filter_shift);
        if ($filter_product_no) $this->db->like('b.number', $filter_product_no);

        $records = $this->db->get()->result_array();

        if (empty($records)) {
            echo "Data tidak ditemukan.";
            return;
        }

        $html = '<html><head><title>Print Data</title></head>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;}
            #customers {border-collapse: collapse;width: 100%;font-size: 12px;}
            #customers td, #customers th {border: 1px solid #ddd;padding: 5px;}
            #customers tr:nth-child(even) {background-color: #f2f2f2;}
            #customers tr:hover {background-color: #ddd;}
            #customers th {padding: 8px;text-align: center;background-color: #f4f4f4;color: black;}
        </style>
        <body>
        <center>
            <table style="width: 100%;">
                <tr>
                    <td width="50" style="text-align: center;">
                        <img src="' . htmlspecialchars($config->favicon) . '" width="30">
                    </td>
                    <td style="text-align: left;">
                        <b>' . htmlspecialchars($config->name) . '</b><br>
                        <small>LABEL PACKING</small>
                    </td>
                    <td style="text-align: right;">
                        Print Date: ' . date("d M Y H:i:s") . ' <br>
                        Print By: ' . htmlspecialchars($this->session->username) . '  
                    </td>
                </tr>
            </table>
        </center>
        <br><br>
        
        <table id="customers">
            <tr>
                <th>No</th>
                <th>Transaction Date</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Material</th>
                <th>Qty WIP</th>
                <th>Qty Packing</th>
                <th>Qty Label Packing</th>
                <th>Packing Size</th>
                <th>Compound Lot</th>
                <th>Production Date</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                <td style="text-align:center">' . $no . '</td>
                <td>' . htmlspecialchars($data['trans_date']) . '</td>
                <td>' . htmlspecialchars($data['item_number']) . '</td>
                <td>' . htmlspecialchars($data['item_name']) . '</td>
                <td>' . htmlspecialchars($data['material']) . '</td>
                <td>' . htmlspecialchars($data['qty_wip']) . '</td>
                <td>' . htmlspecialchars($data['qty_packing']) . '</td>
                <td>' . htmlspecialchars($data['qty_label']) . '</td>
                <td>' . htmlspecialchars($data['packing_size']) . '</td>
                <td>' . htmlspecialchars($data['compound_lot']) . '</td>
                <td>' . htmlspecialchars($data['prod_date']) . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';

        echo $html;

        // Jika mode excel, pastikan tidak ada output lain
        if ($option == "excel") {
            exit;
        }
    }
}
