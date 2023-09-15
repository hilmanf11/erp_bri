<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Molds extends CI_Controller
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
        $this->form_validation->set_rules('name', 'Code', 'required|min_length[1]|max_length[30]|is_unique[molds.name]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/molds');
        } else {
            redirect('error_access');
        }
    }
    
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('molds', ["name" => $post]);
        echo json_encode($send);
    }


    //CODE OTOMATIS
    public function autoid($type, $model){
        $code = $type. $model;
        $sql = $this->db->query("SELECT coalesce(max(`id`),0) as kode From molds where id like '%$code%'");
        $row = $sql->row();
        $kode = substr($row->kode, -3);
        $autoid = "MD".$code."-".sprintf("%03s", $kode + 1);
        echo $autoid;

    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as customer_name');
            $this->db->from('molds a');
            $this->db->join('customers b', 'a.customer_id  = b.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "customer_name"){
                        $this->db->like("b.name", $filter->value);    
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('id', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('molds', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
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
            $send = $this->crud->update('molds', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('molds', $data);
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
                //excel
                'mold_name' => $data->val($i, 2),
                'type' => $data->val($i, 3),
                'customer_number' => $data->val($i, 4),
                'model' => $data->val($i, 5),
                'project_year' => $data->val($i, 6),
                'standard_cavity' => $data->val($i, 7),
                'actual_cavity' => $data->val($i, 8),
                
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/molds.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/molds.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/molds.txt";
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
            $data = $this->input->post('data');//field excel

            //AUTOID
            $code = $data['type'].$data['model']; 
            $sql = $this->db->query("SELECT coalesce(max(`id`),0) as kode From molds where id like '%$code%'");
            $row = $sql->row();
            $kode = substr($row->kode, -3);
            $autoid = "MD".$code."-".sprintf("%03s", $kode + 1);

           //Cek Process Number                //table       //field           //field excel
           $customers = $this->crud->read('customers', [], ["number" => $data['customer_number']]);
           $molds = $this->crud->read('molds', [], ["name" => $data['mold_name']]);
           $type = array('IN','EX');
           $model = array('INJ','TRF','COM');


         if (empty($customers->name)) {
             echo json_encode(array("title" => "Not Found", "message" => "Customer No " . $data['customer_number'] . " is Not Found", "theme" => "error"));
            } elseif (!in_array($data['type'], $type)) {
                echo json_encode(array("title" => "Not Found", "message" => "Mold Type " . $data['type'] . " is Not Found", "theme" => "error"));
            } elseif (!in_array($data['model'], $model)) {
                echo json_encode(array("title" => "Not Found", "message" => "Mold Model " . $data['model'] . " is Not Found", "theme" => "error"));
            } elseif (!empty($molds->name)) {
               echo json_encode(array("title" => "Duplicated", "message" => "Mold Name " . $data['mold_name'] . " Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field        //excel
                    "id" => $autoid,
                    "name" => $data['mold_name'],
                    "type" => $data['type'],
                    "customer_id" => $customers->id,//membaca parameter $customers dengan inputan number berdasarkan data inputan dari excel 
                    "model" => $data['model'],
                    "project_year" => $data['project_year'],
                    "standard" => $data['standard_cavity'],
                    "actual" => $data['actual_cavity'],
                );
                $send   = $this->crud->create('molds', $dataFinal);
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
            header("Content-Disposition: attachment; filename=molds_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('molds a');
        $this->db->join('customers b', 'a.customer_id  = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('id', 'ASC');
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
                            <small>MASTER MOLDS</small>
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
                <th>Id</th>
                <th>Mold Name</th>
                <th>Type</th>
                <th>Customer Name</th>
                <th>Model</th>
                <th>Project Year</th>
                <th>Standard Cavity</th>
                <th>Actual Cavity</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['type']=="EX"){
                $type="EXTERNAL";
            }else if ($data['type']=="IN"){
                $type="INTERNAL";
            }else{
                $type="ERROR!";
            }

            if ($data['model']=="COM"){
                $model="COMPRESS";
            }else if ($data['type']=="INJ"){
                $type="INJECTION";
            }else if ($data['type']=="TRF"){
                $type="TRANSFER";
            }else{
                $type="ERROR!";
            }
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $type . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td>' . $model . '</td>
                    <td>' . $data['project_year'] . '</td>
                    <td>' . $data['standard'] . '</td>
                    <td>' . $data['actual'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
