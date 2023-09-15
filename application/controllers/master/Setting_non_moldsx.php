<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Setting_non_molds extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Code', 'required|min_length[1]|max_length[20]|is_unique[setting_non_molds.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/setting_non_molds');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('setting_non_molds', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('setting_non_molds a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('b.number');
            $this->db->order_by('b.number', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));

            $this->db->select('a.*, b.name as item_fg_name, c.number as item_rm_number, c.name as item_rm_name, c.uom as uom, c.item_family_number as product_family, d.number as machine_no, e.name as product_family_name');
            $this->db->from('setting_non_molds a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('machines d', 'a.machine_id = d.id');
            $this->db->join('item_familys e', 'c.item_family_number = e.number');
            $this->db->where('b.number', $number);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            $this->db->select('a.*, b.number as item_fg_number, c.number as item_rm_number, d.number as machine_no, e.name as product_family_name');
            $this->db->from('setting_non_molds a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('machines d', 'a.machine_id = d.id');
            $this->db->join('item_familys e', 'c.item_family_number = e.number');
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {

                $post = $this->input->post();
                $setting_non_molds = $this->crud->read("setting_non_molds", [], ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
                if (@$setting_non_molds->item_fg_id != "") {
                    $send = $this->crud->update('setting_non_molds', ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
                } else {
                    $send = $this->crud->create('setting_non_molds', $post);
                }
                echo $send;
           
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('setting_non_molds', $data);
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
                'item_fg_id' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'machine_id' => $data->val($i, 4),
                'cycle_time' => $data->val($i, 5),
                'priority' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/setting_non_molds.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/setting_non_molds.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/setting_non_molds.txt";
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

            //Cek Process Number          //table       //field        //field excel
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $machine = $this->crud->read('machines', [], ["id" => $data['machine_id']]);
            $setting_non_molds = $this->crud->read("setting_non_molds", [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);

            if (empty($item_fg->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Product ID. " . $data['item_fg_id'] . " is Not Found", "theme" => "error"));
            } elseif (empty($item_rm->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Part ID. " . $data['item_rm_id'] . " is Not Found", "theme" => "error"));
            } elseif (empty($machine->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Machine ID. " . $data['machine_id'] . " is Not Found", "theme" => "error"));
            } elseif (!empty($setting_non_molds->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " & Part No. " . $data['item_rm_id']." is Duplicate Data", "theme" => "error"));
            } else {
                
                $send   = $this->crud->create('setting_non_molds', $data);
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
            header("Content-Disposition: attachment; filename=setting_non_molds_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as item_fg_name, b.number as item_fg_number, c.number as item_rm_number, c.name as item_rm_name, c.uom as uom, c.item_family_number as product_family, d.number as machine_no, e.name as product_family_name');
        $this->db->from('setting_non_molds a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('machines d', 'a.machine_id = d.id');
        $this->db->join('item_familys e', 'c.item_family_number = e.number');
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#setting_non_molds {border-collapse: collapse;width: 100%;font-size: 12px;}#setting_non_molds td, #setting_non_molds th {border: 1px solid #ddd;padding: 2px;}#setting_non_molds tr:nth-child(even){background-color: #f2f2f2;}#setting_non_molds tr:hover {background-color: #ddd;}#setting_non_molds th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>MASTER BILL OF MATERIAL</h3>
            </div>
        </center>
        
        <table id="setting_non_molds" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Part ID</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Product Family</th>
                <th>Machine ID</th>
                <th>Machine No</th>
                <th>Cycle Time (Shot/Second)</th>
                <th>Priority</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $numberfg = $data['item_fg_number'];
            $numberrm = $data['item_rm_number'];
    
            if (strpos($data['item_fg_number'], '0') === 0 || strpos($data['item_fg_number'], '+') === 0) {
                $number = "'" . $data['item_fg_number'];
            } else {
                // Leave the data unchanged
                $number = $data['item_fg_number'];
            }
            
            if (strpos($data['item_rm_number'], '0') === 0 || strpos($data['item_rm_number'], '+') === 0) {
                $number = "'" . $data['item_rm_number'];
            } else {
                // Leave the data unchanged
                $number = $data['item_rm_number'];
            }
            
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $numberfg . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $numberrm . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['product_family_name'] . '</td>
                        <td>' . $data['machine_id'] . '</td>
                        <td>' . $data['machine_no'] . '</td>
                        <td>' . $data['cycle_time'] . '</td>
                        <td>' . $data['priority'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
