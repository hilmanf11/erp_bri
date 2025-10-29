<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wip_balances extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[30]|is_unique[wip_balances.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/wip_balances');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('wip_balances', ["name" => $post]);
        echo json_encode($send);
    }

    public function readRequestNoWP()
    {
        $records = $this->crud->query("SELECT a.request_no FROM wip_balances a JOIN supply_sheets b ON a.request_no = b.request_no WHERE a.status = '0' GROUP BY a.request_no");
        echo json_encode($records);
    }

    public function calculate_balance()
    {
        if ($this->input->post()) {
            $start_date   = $this->input->post('start_date');
            $end_date     = $this->input->post('end_date');
            $item_rm_id   = $this->input->post('cal_item_rm_id');

            if (empty($start_date) || empty($end_date) || empty($item_rm_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
                return;
            }

            $this->db->select('id, item_rm_id, request_no, need, issued, created_date');
            $this->db->from('wip_balances');
            $this->db->where('item_rm_id', $item_rm_id);
            $this->db->where('created_date >=', $start_date);
            $this->db->where('created_date <=', $end_date);
            $this->db->where('deleted', 0);
            $this->db->order_by('created_date', 'ASC');

            $records = $this->db->get()->result();

            if (!$records) {
                echo json_encode(['status' => 'error', 'message' => 'No records found for selected period.']);
                return;
            }

            $this->db->select('balance');
            $this->db->from('wip_balances');
            $this->db->where('item_rm_id', $item_rm_id);
            $this->db->where('created_date <', $start_date);
            $this->db->where('deleted', 0);
            $this->db->order_by('created_date', 'DESC');
            $last_before = $this->db->get()->row();

            $begin_balance = $last_before ? floatval($last_before->balance) : 0;
            $current_balance = $begin_balance;

            foreach ($records as $r) {
                $need   = floatval($r->need);
                $issued = floatval($r->issued);

                $balance = $current_balance + $issued - $need;
                // if ($balance < 0) $balance = 0;

                $this->db->where('id', $r->id);
                $this->db->update('wip_balances', [
                    'begin' => $current_balance,
                    'balance' => $balance,
                    'updated_by' => $this->session->userdata('username'),
                    'updated_date' => date('Y-m-d H:i:s')
                ]);

                $current_balance = $balance;
            }

            echo json_encode(['status' => 'success', 'message' => 'Recalculation completed successfully.']);
        }
    }

    
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {

            $filter_item_rm_id = $this->input->get('filter_item_rm_id');
            $filter_request_no = $this->input->get('filter_request_no');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            // Select Query
            $this->db->select('a.created_by, a.created_date, a.updated_by, a.updated_date,
                a.id, a.request_no, a.item_rm_id, a.begin, a.need, a.issued, a.balance, a.warehouse, a.status,
                b.number_internal as item_number, b.name as item_name, b.uom, IFNULL(c.qty_req, 0) AS qty_req,,
                IFNULL(c.qty_act, 0) as qty_act, g.mpq
            ');
            $this->db->from('wip_balances a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'LEFT');
            $this->db->join('supply_sheets c', 'a.item_rm_id = c.item_rm_id AND a.request_no = c.request_no', 'LEFT');
            $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id', 'left');
            $this->db->where('a.deleted', 0);

            if ($filter_request_no != "") {
                $this->db->where('a.request_no', $filter_request_no);
            }

            $this->db->like('a.item_rm_id', $filter_item_rm_id);

            $this->db->order_by('a.created_date', 'ASC');
            // $this->db->order_by('a.request_no', 'ASC');
            // $this->db->order_by('a.item_rm_id', 'ASC');

            // Hitung total baris sebelum limit
            $totalRows = $this->db->count_all_results('', false);

            // Batasi jumlah data yang diambil
            $this->db->limit($rows, $offset);

            // Eksekusi query
            $records = $this->db->get()->result_array();

            // Mapping hasil
            $result['total'] = $totalRows;
            $result['rows'] = $records;

            echo json_encode($result);
        }
    }

    // public function datatables()
    // {
    //     if ($this->input->post()) {

    //         $filter_item_rm_id = $this->input->get('filter_item_rm_id');

    //         $page = $this->input->post('page');
    //         $rows = $this->input->post('rows');
    //         $page   = isset($page) ? intval($page) : 1;
    //         $rows   = isset($rows) ? intval($rows) : 10;
    //         $offset = ($page - 1) * $rows;
    //         $result = array();

    //         // Ambil semua data
    //         $this->db->select("
    //             a.id, a.request_no, a.item_rm_id, a.need, a.issued,
    //             a.created_by, a.created_date, a.updated_by, a.updated_date,
    //             b.number_internal AS item_number, b.name AS item_name, b.uom,
    //             IFNULL(c.qty_req, 0) AS qty_req,
    //             g.mpq,
    //             a.warehouse
    //         ");
    //         $this->db->from('wip_balances a');
    //         $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'LEFT');
    //         $this->db->join('supply_sheets c', 'a.item_rm_id = c.item_rm_id AND a.request_no = c.request_no', 'LEFT');
    //         $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id', 'LEFT');
    //         $this->db->where('a.deleted', 0);

    //         if (!empty($filter_item_rm_id)) {
    //             $this->db->like('a.item_rm_id', $filter_item_rm_id);
    //         }

    //         $this->db->order_by('a.created_date', 'ASC');
    //         // $this->db->order_by('a.item_rm_id', 'ASC');
    //         // $this->db->order_by('a.request_no', 'ASC');

    //         $records_all = $this->db->get()->result_array();
    //         $totalRows = count($records_all);

    //         $computed = [];
    //         $last_balance_per_item = [];

    //         foreach ($records_all as $row) {
    //             $item_rm_id = $row['item_rm_id'];

    //             $begin = isset($last_balance_per_item[$item_rm_id]) ? $last_balance_per_item[$item_rm_id] : '0.00';
    //             $balance = $begin + floatval($row['issued']) - floatval($row['need']);

    //             $row['begin'] = $begin;
    //             $row['balance'] = $balance;

    //             $last_balance_per_item[$item_rm_id] = $balance; // simpan balance terakhir
    //             $computed[] = $row;
    //         }

    //         // Pagination manual (karena kita hitung balance di PHP)
    //         $paged = array_slice($computed, $offset, $rows);

    //         $result['total'] = $totalRows;
    //         $result['rows'] = $paged;

    //         echo json_encode($result);
    //     }
    // }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->create('wip_balances', $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('wip_balances', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('wip_balances', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'request_no' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'begin' => $data->val($i, 4)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/wip_balances.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/wip_balances.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/wip_balances.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $data['balance'] = $data['begin'];
            
            $item_rm = $this->crud->read('item_rm', [], [
                "id" => $data['item_rm_id'],
            ]);

            $wip_balance = $this->crud->read('wip_balances', [], [
                "request_no" => $data['request_no'],
                "item_rm_id" => $data['item_rm_id'],
            ]);

            if (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Part ID. " . $data['item_rm_id'] . " is Not Found!", "theme" => "error"));
            } elseif (!empty($wip_balance)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Supply Sheet No. ". $data['request_no'] ." and Part ID. " . $data['item_rm_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $send   = $this->crud->create('wip_balances', $data);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=wip_balances_$format.xls");
        }

        $filter_item_rm_id = $this->input->get('filter_item_rm_id');
        $filter_request_no = $this->input->get('filter_request_no');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('wip_balances a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->where('a.deleted', 0);

        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('a.request_no', 'ASC');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER WIP BALANCE</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Trans Date</th>
                <th>Supply Sheet</th>
                <th>Uom</th>
                <th>Begin</th>
                <th>Need</th>
                <th>Issued</th>
                <th>Balance</th>
                <th>Warehouse</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['updated_date'] != "") {
                $trans_date = $data['updated_date'];
            } else {
                $trans_date = $data['created_date'];
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_number'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . $trans_date . '</td>
                    <td>' . $data['request_no'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['begin'] . '</td>
                    <td>' . $data['need'] . '</td>
                    <td>' . $data['issued'] . '</td>
                    <td>' . $data['balance'] . '</td>
                    <td>' . $data['warehouse'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
