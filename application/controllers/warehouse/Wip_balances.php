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
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
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

            // Filter rules jika ada
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if ($filter->field == "item_number") {
                        $this->db->like("b.number", $filter->value);
                    } elseif ($filter->field == "item_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "request_no") {
                        $this->db->like("a.request_no", $filter->value);
                    } elseif ($filter->field == "uom") {
                        $this->db->like("b.uom", $filter->value);
                    }
                }
            }

            $this->db->order_by('a.created_date', 'ASC');
            $this->db->order_by('a.request_no', 'ASC');
            $this->db->order_by('a.item_rm_id', 'ASC');

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
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('wip_balances a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->where('a.deleted', 0);
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
