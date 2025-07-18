<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\IOFactory;
class Forecasts extends CI_Controller
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
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[forecasts.customer_id]');
        // $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[forecasts.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/forecasts');
        } else {
            redirect('error_access');
        }
    }

    public function validate_date($date) {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) === $date) {
            return TRUE;
        } else {
            // $this->form_validation->set_message('validate_date', 'The {field} must be a valid date (yyyy-mm-dd).');
            return FALSE;
        }
    }

    public function validate_year($year) {
        if (preg_match('/^\d{4}$/', $year)) {
            return TRUE;
        } else {
            // $this->form_validation->set_message('validate_year', 'The {field} must be a valid year (yyyy).');
            return FALSE;
        }
    }
    

    public function validate_month($month) {
        if (preg_match('/^(0[1-9]|1[0-2]|[1-9])$/', $month)) {
            return TRUE;
        } else {
            // $this->form_validation->set_message('validate_month', 'The {field} must be a valid month (mm).');
            return FALSE;
        }
    }

    private function format_number($input) {
        $numeric_value = str_replace(',', '', $input);
        return number_format($numeric_value, 0, '.', '.');
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('forecasts', ["customer_id" => $post]);
        echo json_encode($send);
    }

    // GET PRODUCT FAMILY USE ALL FORECAST MODULE
    public function readsProductFamily()
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('item_category_id', 'C01');
        $this->db->order_by('name', 'ASC');

        $data = $this->db->get()->result();
        echo json_encode($data);
    }

    //GET DATA ITEMS
    public function read_items($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price, a.item_fg_customer FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    //GET REVISION LAST
    public function readRevisionLast()
    {
        $customer_id = $this->input->post('customer_id');
        $send = $this->crud->query("SELECT max(revision) as rev FROM forecasts WHERE customer_id = '$customer_id'");

        if (count($send) > 0) {
            if ($send[0]->rev == "5") {
                $data = array("revision" => ($send[0]->rev));
            } else {
                $data = array("revision" => ($send[0]->rev + 1));
            }
        } else {
            $data = array("revision" => 1);
        }

        echo json_encode($data);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            // $filter_issued_date_from = @base64_decode($get['filter_issued_date_from']);
            $filter_issued_date_to = @base64_decode($get['filter_issued_date_to']);
            $filter_period_month = @base64_decode($get['filter_period_month']);
            $filter_period_year = @base64_decode($get['filter_period_year']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_revision = @base64_decode($get['filter_revision']);
            $filter_product_family = @base64_decode($get['filter_product_family']);
            $filter_plant = @base64_decode($get['filter_plant']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as customer_number, b.name as customer_name');
            $this->db->from('forecasts a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            // if ($filter_issued_date_from != "" && $filter_issued_date_to != "") {
            //     $this->db->where('a.issued_date >=', $filter_issued_date_from);
            //     $this->db->where('a.issued_date <=', $filter_issued_date_to);
            // }
            $this->db->where('a.p_month', $filter_period_month);
            $this->db->where('a.p_year', $filter_period_year);
            if ($filter_customer_id != "") {
                $this->db->where('a.customer_id', $filter_customer_id);
            }
            if ($filter_revision != "") {
                $this->db->where('a.revision', $filter_revision);
            }
            if ($filter_product_family != "") {
                $this->db->where('c.item_family_number', $filter_product_family);
            }
            if ($filter_plant != "") {
                $this->db->where('a.plant', $filter_plant);
            }
            $this->db->group_by('a.customer_id');
            $this->db->group_by('a.p_month');
            $this->db->group_by('a.p_year');
            $this->db->group_by('a.revision');
            // $this->db->order_by('a.created_date', 'DESC');
            $this->db->order_by('a.revision', 'DESC');
            $this->db->order_by('a.issued_date', 'ASC');
            $this->db->order_by('b.name', 'ASC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1-10
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
            $customer_id = base64_decode($this->input->get('customer_id'));
            $p_month = base64_decode($this->input->get('p_month'));
            $p_year = base64_decode($this->input->get('p_year'));
            $revision = base64_decode($this->input->get('revision'));
            $product_family = base64_decode($this->input->get('product_family'));
            $filter_plant = base64_decode($this->input->get('filter_plant'));

            $this->db->select('a.*, c.number as item_fg_number, c.name as item_fg_name, d.item_fg_customer as item_fg_customer');
            $this->db->from('forecasts a');
            // $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('customer_items d', 'd.customer_id = a.customer_id and d.item_fg_id=a.item_fg_id', 'left');
            $this->db->where('a.customer_id', $customer_id);
            $this->db->where('a.p_month', $p_month);
            $this->db->where('a.p_year', $p_year);
            $this->db->where('a.revision', $revision);
            if ($product_family != "") {
                $this->db->where('c.item_family_number', $product_family);
            }
            if ($filter_plant != "") {
                $this->db->where('a.plant', $filter_plant);
            }
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $customer_id = base64_decode($this->input->get('customer_id'));
            $p_month = base64_decode($this->input->get('p_month'));
            $p_year = base64_decode($this->input->get('p_year'));
            $revision = base64_decode($this->input->get('revision'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.item_fg_customer as item_fg_customer');
            $this->db->from('forecasts a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id','left');
            $this->db->join('customer_items c', 'c.customer_id = a.customer_id and and c.item_fg_id=a.item_fg_id', 'left');
            $this->db->where('a.customer_id', $customer_id);
            $this->db->where('a.p_month', $p_month);
            $this->db->where('a.p_year', $p_year);
            $this->db->where('a.revision', $revision);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    // GET DATATABLE HISTORY PRICE
    public function datatableHistories()
    {
        if ($this->input->get()) {
            $customer_id = base64_decode($this->input->get('customer_id'));
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));
            $p_month = base64_decode($this->input->get('p_month'));
            $p_year = base64_decode($this->input->get('p_year'));
            $revision = base64_decode($this->input->get('revision'));

            $this->db->select('*');
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('forecast_histories a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.customer_id', $customer_id);
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->where('a.p_month', $p_month);
            $this->db->where('a.p_year', $p_year);
            $this->db->where('a.revision', $revision);
            $this->db->order_by('a.created_date', 'DESC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //AUTO ID
    public function autoid()
    {
        $post = $this->input->post();
        $issued_date = $post["issued_date"];
        $month = date('ym', strtotime($issued_date));
        $format = "FC" . $month;
        $sql = $this->db->query("SELECT max(document_no) as kode FROM forecasts WHERE document_no LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == "") {
            $kode = 0;
        } else {
            $kode = substr($row->kode, -3);
        }
        $autoid = $format . sprintf("%03s", $kode + 1);
        echo $autoid;
    }

    //CREATE DATA
    public function create()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request");
        }

        $items = $this->input->post('items');
        $errors = [];
        $success_count = 0;

        $this->db->trans_begin();

        foreach ($items as $post) {
            $mode = isset($post['mode']) ? $post['mode'] : 'insert';

            $where = [
                "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "revision" => $post['revision'],
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year']
            ];

            $existing = $this->crud->read("forecasts", [], $where);
            unset($post['mode']);

            if ($mode === 'insert') {
                if (!empty($existing)) {
                    $errors[] = "Customer ID, Product No, Revision, Month, and Year already exists";
                    continue;
                }

                $res1 = $this->crud->create("forecasts", $post);
                $res2 = $this->crud->create("forecast_histories", $post);
            } else if($mode === 'update') {

                $checkForcast = $this->crud->read("forecasts", [], [
                    "customer_id" => $post['customer_id'], 
                    "item_fg_id" => $post['item_fg_id'], 
                    "revision" => $post['revision'],
                    "p_month" => $post['p_month'], 
                    "p_year" => $post['p_year']
                ]);

                if(@$checkForcast->customer_id != "") {
                    $res1 = $this->crud->update('forecasts', [
                        "customer_id" => $post['customer_id'], 
                        "item_fg_id" => $post['item_fg_id'],  
                        "revision" => $post['revision'],
                        "p_month" => $post['p_month'], 
                        "p_year" => $post['p_year']
                    ], $post);
                }else{
                    $res1 = $this->crud->create("forecasts", $post);
                    // $res1 = true;
                }

                // Untuk `forecast_histories`, hanya insert jika belum ada histori identik
                $historyExists = $this->crud->read("forecast_histories", [], [
                    "customer_id" => $post['customer_id'], 
                    "item_fg_id" => $post['item_fg_id'], 
                    "p_month" => $post['p_month'], 
                    "p_year" => $post['p_year'], 
                    "revision" => $post['revision'], 
                    "month_1" => $post['month_1'], 
                    "month_2" => $post['month_2'], 
                    "month_3" => $post['month_3'], 
                    "month_4" => $post['month_4'], 
                    "month_5" => $post['month_5'], 
                    "month_6" => $post['month_6'], 
                    "month_7" => $post['month_7'], 
                    "month_8" => $post['month_8'], 
                    "month_9" => $post['month_9'], 
                    "month_10" => $post['month_10'], 
                    "month_11" => $post['month_11'], 
                    "month_12" => $post['month_12']
                ]);

                if (!$historyExists) {
                    $res2 = $this->crud->create("forecast_histories", $post);
                } else {
                    $res2 = true;
                }
            }

            if (!$res1 || !$res2) {
                $errors[] = "Failed to save forecasts customer data";
            } else {
                $success_count++;
            }
        }

        if ($this->db->trans_status() === FALSE || !empty($errors)) {
            $this->db->trans_rollback();
            echo json_encode([
                "title" => "Failed to Save",
                "message" => implode("\n", array_unique($errors)),
                "theme" => "error"
            ]);
            return;
        }

        $this->db->trans_commit();
        echo json_encode([
            "title" => "Success",
            "message" => "$success_count items have been saved successfully",
            "theme" => "success"
        ]);
    }

    public function deleted()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('forecasts', $data);
        $send2 = $this->crud->delete('forecast_histories', $data);
        echo $send;
    }

    public function delete()
    {
        $items = $this->input->post('items');

        if (!is_array($items) || empty($items)) {
            echo json_encode([
                "title" => "Delete Failed",
                "message" => "No forecasts customer selected",
                "theme" => "error"
            ]);
            return;
        }

        $this->db->trans_begin();

        foreach ($items as $item) {
            $this->crud->delete('forecasts', $item);
            $this->crud->delete('forecast_histories', $item);
        }

        // Commit / Rollback
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                "title" => "Delete Failed",
                "message" => "Some data failed to delete",
                "theme" => "error"
            ]);
        } else {
            $this->db->trans_commit();
            echo json_encode([
                "title" => "Success",
                "message" => "Forecasts customer deleted successfully",
                "theme" => "success"
            ]);
        }
    }

    // //UPLOAD DATA
    // public function upload()
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);
    //     for ($i = 4; $i <= $total_row; $i++) {
    //         $datas[] = array(
    //             //excel
    //             'customer_id' => $data->val($i, 2),
    //             'item_fg_id' => $data->val($i, 3),
    //             'issued_date' => $data->val($i, 4),
    //             'p_month' => $data->val($i, 5),
    //             'p_year' => $data->val($i, 6),
    //             'revision' => $data->val($i, 7),
    //             'month_1' => $data->val($i, 8),
    //             'month_2' => $data->val($i, 9),
    //             'month_3' => $data->val($i, 10),
    //             'month_4' => $data->val($i, 11),
    //             'month_5' => $data->val($i, 12),
    //             'month_6' => $data->val($i, 13),
    //             'month_7' => $data->val($i, 14),
    //             'month_8' => $data->val($i, 15),
    //             'month_9' => $data->val($i, 16),
    //             'month_10' => $data->val($i, 17),
    //             'month_11' => $data->val($i, 18),
    //             'month_12' => $data->val($i, 19),
    //             'remark' => $data->val($i, 20)
    //         );
    //     }
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }
    public function upload()
    {
        error_reporting(0);
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777);
        $spreadsheet = IOFactory::load($target);
        $sheet = $spreadsheet->getActiveSheet();
        $totalRows = $sheet->getHighestDataRow();
        
        $datas = [];
        for ($i = 4; $i <= $totalRows; $i++) {
            $datas[] = array(
                'customer_id' => $sheet->getCell('B' . $i)->getValue(),
                'item_fg_id' => $sheet->getCell('C' . $i)->getValue(),
                'issued_date' => $sheet->getCell('D' . $i)->getValue(),
                'p_month' => $sheet->getCell('E' . $i)->getValue(),
                'p_year' => $sheet->getCell('F' . $i)->getValue(),
                'revision' => $sheet->getCell('G' . $i)->getValue(),
                'month_1' => $sheet->getCell('H' . $i)->getValue(),
                'month_2' => $sheet->getCell('I' . $i)->getValue(),
                'month_3' => $sheet->getCell('J' . $i)->getValue(),
                'month_4' => $sheet->getCell('K' . $i)->getValue(),
                'month_5' => $sheet->getCell('L' . $i)->getValue(),
                'month_6' => $sheet->getCell('M' . $i)->getValue(),
                'month_7' => $sheet->getCell('N' . $i)->getValue(),
                'month_8' => $sheet->getCell('O' . $i)->getValue(),
                'month_9' => $sheet->getCell('P' . $i)->getValue(),
                'month_10' => $sheet->getCell('Q' . $i)->getValue(),
                'month_11' => $sheet->getCell('R' . $i)->getValue(),
                'month_12' => $sheet->getCell('S' . $i)->getValue(),
                'remark' => $sheet->getCell('T' . $i)->getValue(),
            );
        }
    
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/forecasts.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/forecasts.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/forecasts.txt";
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
            if($this->validate_date($data['issued_date']) == FALSE){
                 echo json_encode(array("title" => "Failed", "message" => 'The Issued Date must be a valid year (yyyy).', "theme" => "error"));
                 return;
                }
            if($this->validate_month($data['p_month']) == FALSE){
                 echo json_encode(array("title" => "Failed", "message" => 'The Month must be a valid month (mm).', "theme" => "error"));
                 return;
                }
            if($this->validate_year($data['p_year']) == FALSE){
                 echo json_encode(array("title" => "Failed", "message" => 'The Year must be a valid year (yyyy).', "theme" => "error"));
                 return;
                }
            // if ($this->form_validation->run() == TRUE) {
                // Validasi apakah customer_id ada di tabel customers

                if(!empty($data['customer_id'])) {
                    $customer_exists = $this->crud->read('customers', [], ["number" => $data['customer_id']]);
                    if (empty($customer_exists)) {
                        echo json_encode(array("title" => "Not Found", "message" => "Customer Code " . $data['customer_id'] . " not found", "theme" => "error"));
                        return;
                    }
                }else{
                    echo json_encode(array("title" => "Failed", "message" => "Customer Code cannot empty", "theme" => "error"));
                    return;
                }

                // Validasi apakah item_fg_id ada di tabel item_fg
                $item_fg_exists = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
                if (empty($item_fg_exists)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Product No. " . $data['item_fg_id'] . " not found", "theme" => "error"));
                    return;
                }
                $customer_item_exists = $this->crud->read('customer_items', [], ["customer_id" => $customer_exists->id, "item_fg_id" => $item_fg_exists->id]);
                // $customer_item_exists = $this->crud->read('customer_items', [], ["customer_id" => $customer_exists->id, "item_fg_customer" => $data['item_fg_id']]);
                if (empty($customer_item_exists)) {
                    echo json_encode(array("title" => "Failed", "message" => "Product No. " . $data['item_fg_id'] . " is Not Match with Customer " . $data['customer_id'], "theme" => "error"));
                    return;
                }
                $forecast_exists = $this->crud->read('forecasts', [], ["customer_id" => $customer_exists->id, "item_fg_id" => $item_fg_exists->id, "p_month" => $data['p_month'], "p_year" => $data['p_year'], "revision" => $data['revision']]);
                if (!empty($forecast_exists)) {
                    echo json_encode(array("title" => "Failed", "message" => "Data Already Uploaded", "theme" => "error"));
                    return;
                }
                //Cek Process Number          //table       //field        //field excel
                $forecasts = $this->crud->read('forecasts', [], ["customer_id" => $data['customer_id'], "item_fg_id" => $data['item_fg_id']]);

                $issued_date = $data["issued_date"];
                $month = date('ym', strtotime($issued_date));
                $format = "FC" . $month;
                $sql = $this->db->query("SELECT max(document_no) as kode FROM forecasts WHERE document_no LIKE '%$format%'");
                $row = $sql->row();
                if ($row->kode == "") {
                    $kode = 0;
                } else {
                    $kode = substr($row->kode, -3);
                }
                $autoid = $format . sprintf("%03s", $kode + 1);

                if (!empty($forecasts->customer_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => " Customer " . $data['customer_id'] . " is Duplicate Data", "theme" => "error"));
                } elseif (!empty($forecasts->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
                } else {
                    $dataFinal = array(
                        //field
                        "customer_id" => $customer_exists->id,
                        "item_fg_id" => $item_fg_exists->id,
                        "document_no" => $autoid,
                        "issued_date" => $data['issued_date'],
                        "p_month" => $data['p_month'],
                        "p_year" => $data['p_year'],
                        "revision" => $data['revision'],
                        "month_1" => $data['month_1'],
                        "month_2" => $data['month_2'],
                        "month_3" => $data['month_3'],
                        "month_4" => $data['month_4'],
                        "month_5" => $data['month_5'],
                        "month_6" => $data['month_6'],
                        "month_7" => $data['month_7'],
                        "month_8" => $data['month_8'],
                        "month_9" => $data['month_9'],
                        "month_10" => $data['month_10'],
                        "month_11" => $data['month_11'],
                        "month_12" => $data['month_12'],
                        "remark" => $data['remark'],
                    );
                    $send   = $this->crud->create('forecasts', $dataFinal);
                    $send2  = $this->crud->create('forecast_histories', $dataFinal);
                    echo $send;
                }
            // } else {
            //     echo json_encode(array("title" => "Failed", "message" => validation_errors(), "theme" => "error"));
            //     // show_error(validation_errors());
            // }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecasts_$format.xls");
        }

        $get = $this->input->get();
        $filter_issued_date_from = @base64_decode($get['filter_issued_date_from']);
        $filter_issued_date_to = @base64_decode($get['filter_issued_date_to']);
        $filter_period_month = @base64_decode($get['filter_period_month']);
        $filter_period_year = @base64_decode($get['filter_period_year']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_revision = @base64_decode($get['filter_revision']);
        $filter_product_family = @base64_decode($get['filter_product_family']);
        $filter_plant = @base64_decode($get['filter_plant']);

        $p_date_start = date("Y-m-d", strtotime($filter_period_year . "-" . $filter_period_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));
        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name');
        $this->db->from('forecasts a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        if ($filter_issued_date_from != "" && $filter_issued_date_to != "") {
            $this->db->where('a.issued_date >=', $filter_issued_date_from);
            $this->db->where('a.issued_date <=', $filter_issued_date_to);
        }
        $this->db->where('a.p_month', $filter_period_month);
        $this->db->where('a.p_year', $filter_period_year);
        if ($filter_customer_id != "") {
            $this->db->where('a.customer_id', $filter_customer_id);
        }
        if ($filter_revision != "") {
            $this->db->where('a.revision', $filter_revision);
        }
        if ($filter_product_family != "") {
            $this->db->where('c.item_family_number', $filter_product_family);
        }
        if ($filter_plant != "") {
            $this->db->where('a.plant', $filter_plant);
        }
        // $this->db->like('a.p_month', $filter_period_month);
        // $this->db->like('a.p_year', $filter_period_year);
        // $this->db->like('a.customer_id', $filter_customer_id);
        // $this->db->like('a.revision', $filter_revision);
        // $this->db->group_by('a.customer_id');
        // $this->db->group_by('a.p_month');
        // $this->db->group_by('a.p_year');
        // $this->db->group_by('a.revision');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        if ($filter_customer_id == "") {
            $i_d_from = date_create($filter_issued_date_from);
            $i_d_to = date_create($filter_issued_date_to);
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#forecasts {border-collapse: collapse;width: 100%;font-size: 12px;}#forecasts td, #forecasts th {border: 1px solid #ddd;padding: 2px;}#forecasts tr:nth-child(even){background-color: #f2f2f2;}#forecasts tr:hover {background-color: #ddd;}#forecasts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>FORECAST CUSTOMER</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>ISSUED DATE</small><br>
                                    <small>CUSTOMER NAME</small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>: </small><br>
                                    <small>: </small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small><b>' . date_format($i_d_from, "d F Y") . '</b> to <b>' . date_format($i_d_to, "d F Y") . '</b></small><br>
                                    <small><b>ALL</b></small>
                                </td>
                            </tr>
                        </table>
                    </div>
            </center>
            
            <table id="forecasts" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Customer Name</th>
                    <th>Document No</th>
                    <th>Issued Date</th>
                    <th>Period</th>
                    <th>Revision</th>
                    <th>Remark</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>' . $dates[0]['name'] . '</th>
                    <th>' . $dates[1]['name'] . '</th>
                    <th>' . $dates[2]['name'] . '</th>
                    <th>' . $dates[3]['name'] . '</th>
                    <th>' . $dates[4]['name'] . '</th>
                    <th>' . $dates[5]['name'] . '</th>
                    <th>' . $dates[6]['name'] . '</th>
                    <th>' . $dates[7]['name'] . '</th>
                    <th>' . $dates[8]['name'] . '</th>
                    <th>' . $dates[9]['name'] . '</th>
                    <th>' . $dates[10]['name'] . '</th>
                    <th>' . $dates[11]['name'] . '</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['issued_date'] . '</td>
                        <td>' . $data['p_month'] . '/' . $data['p_year'] . '</td>
                        <td>' . $data['revision'] . '</td>
                        <td>' . $data['remark'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $this->format_number($data['month_1']) . '</td>
                        <td>' . $this->format_number($data['month_2']) . '</td>
                        <td>' . $this->format_number($data['month_3']) . '</td>
                        <td>' . $this->format_number($data['month_4']) . '</td>
                        <td>' . $this->format_number($data['month_5']) . '</td>
                        <td>' . $this->format_number($data['month_6']) . '</td>
                        <td>' . $this->format_number($data['month_7']) . '</td>
                        <td>' . $this->format_number($data['month_8']) . '</td>
                        <td>' . $this->format_number($data['month_9']) . '</td>
                        <td>' . $this->format_number($data['month_10']) . '</td>
                        <td>' . $this->format_number($data['month_11']) . '</td>
                        <td>' . $this->format_number($data['month_12']) . '</td>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_customer_id != "") {
            $i_d_from = date_create($filter_issued_date_from);
            $i_d_to = date_create($filter_issued_date_to);
            foreach ($records as $data) {
                $filter_customer_id = $data['customer_name'];
            }
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#forecasts {border-collapse: collapse;width: 100%;font-size: 12px;}#forecasts td, #forecasts th {border: 1px solid #ddd;padding: 2px;}#forecasts tr:nth-child(even){background-color: #f2f2f2;}#forecasts tr:hover {background-color: #ddd;}#forecasts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>FORECAST CUSTOMER</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>ISSUED DATE</small><br>
                                    <small>CUSTOMER NAME</small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>: </small><br>
                                    <small>: </small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small><b>' . date_format($i_d_from, "d F Y") . '</b> to <b>' . date_format($i_d_to, "d F Y") . '</b></small><br>
                                    <small><b>' . $filter_customer_id . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </div>
            </center>
            
            <table id="forecasts" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Customer Name</th>
                    <th>Document No</th>
                    <th>Issued Date</th>
                    <th>Period</th>
                    <th>Revision</th>
                    <th>Remark</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>' . $dates[0]['name'] . '</th>
                    <th>' . $dates[1]['name'] . '</th>
                    <th>' . $dates[2]['name'] . '</th>
                    <th>' . $dates[3]['name'] . '</th>
                    <th>' . $dates[4]['name'] . '</th>
                    <th>' . $dates[5]['name'] . '</th>
                    <th>' . $dates[6]['name'] . '</th>
                    <th>' . $dates[7]['name'] . '</th>
                    <th>' . $dates[8]['name'] . '</th>
                    <th>' . $dates[9]['name'] . '</th>
                    <th>' . $dates[10]['name'] . '</th>
                    <th>' . $dates[11]['name'] . '</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['issued_date'] . '</td>
                        <td>' . $data['p_month'] . '/' . $data['p_year'] . '</td>
                        <td>' . $data['revision'] . '</td>
                        <td>' . $data['remark'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $this->format_number($data['month_1']) . '</td>
                        <td>' . $this->format_number($data['month_2']) . '</td>
                        <td>' . $this->format_number($data['month_3']) . '</td>
                        <td>' . $this->format_number($data['month_4']) . '</td>
                        <td>' . $this->format_number($data['month_5']) . '</td>
                        <td>' . $this->format_number($data['month_6']) . '</td>
                        <td>' . $this->format_number($data['month_7']) . '</td>
                        <td>' . $this->format_number($data['month_8']) . '</td>
                        <td>' . $this->format_number($data['month_9']) . '</td>
                        <td>' . $this->format_number($data['month_10']) . '</td>
                        <td>' . $this->format_number($data['month_11']) . '</td>
                        <td>' . $this->format_number($data['month_12']) . '</td>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }
    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'B2' => ['admin:','Isi dengan CODE dari Master Customer'],
            'C2' => ['admin:','Isi dengan PRODUCT NO dari Master Item Finish Good'],
            'D2' => ['admin:','format date =','yyyy-mm-dd'],
            'E3' => ['admin:','format month = "mm"'],
            'F3' => ['admin:','format year = "yyyy"'],
            'G2' => ['admin:','Isi dengan angka','0, 1, 2, 3, 4, 5'],
            'H2' => ['Tuliskan angkanya saja'],
            'I2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'J2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'K2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'L2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'M2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'N2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'O2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'P2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'Q2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'R2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja'],
            'S2' => ['Tuliskan angkanya saja. Apabila kosong, kolom bisa dikosongkan saja']
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('FORECAST');
        $templateSheet->mergeCells('A1:T1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16) ->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(25);
        $templateSheet->getColumnDimension('C')->setWidth(25);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(25);
        $templateSheet->getColumnDimension('F')->setWidth(25);
        $templateSheet->getColumnDimension('G')->setWidth(25);
        $templateSheet->getColumnDimension('H')->setWidth(20);
        $templateSheet->getColumnDimension('I')->setWidth(20);
        $templateSheet->getColumnDimension('J')->setWidth(20);
        $templateSheet->getColumnDimension('K')->setWidth(20);
        $templateSheet->getColumnDimension('L')->setWidth(20);
        $templateSheet->getColumnDimension('M')->setWidth(20);
        $templateSheet->getColumnDimension('N')->setWidth(20);
        $templateSheet->getColumnDimension('O')->setWidth(20);
        $templateSheet->getColumnDimension('P')->setWidth(20);
        $templateSheet->getColumnDimension('Q')->setWidth(20);
        $templateSheet->getColumnDimension('R')->setWidth(20);
        $templateSheet->getColumnDimension('S')->setWidth(20);
        $templateSheet->getColumnDimension('T')->setWidth(20);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD MASTER FORECAST');
        $templateSheet->setCellValue('A2', 'No');
        $templateSheet->setCellValue('B2', 'CUSTOMER CODE');
        $templateSheet->setCellValue('C2', 'PRODUCT NO');
        $templateSheet->setCellValue('D2', 'ISSUED DATE');
        $templateSheet->setCellValue('E2', 'PERIOD');
        $templateSheet->setCellValue('F2', 'PERIOD');
        $templateSheet->setCellValue('G2', 'REVISION');
        $templateSheet->setCellValue('H2', 'MONTH 1');
        $templateSheet->setCellValue('I2', 'MONTH 2');
        $templateSheet->setCellValue('J2', 'MONTH 3');
        $templateSheet->setCellValue('K2', 'MONTH 4');
        $templateSheet->setCellValue('L2', 'MONTH 5');
        $templateSheet->setCellValue('M2', 'MONTH 6');
        $templateSheet->setCellValue('N2', 'MONTH 7');
        $templateSheet->setCellValue('O2', 'MONTH 8');
        $templateSheet->setCellValue('P2', 'MONTH 9');
        $templateSheet->setCellValue('Q2', 'MONTH 10');
        $templateSheet->setCellValue('R2', 'MONTH 11');
        $templateSheet->setCellValue('S2', 'MONTH 12');
        $templateSheet->setCellValue('T2', 'REMARKS');
        $templateSheet->setCellValue('E3', 'MONTH')->setCellValue('F3', 'YEAR');
        $templateSheet->mergeCells('A2:A3');
        $templateSheet->mergeCells('B2:B3');
        $templateSheet->mergeCells('C2:C3');
        $templateSheet->mergeCells('D2:D3');
        $templateSheet->mergeCells('E2:F2');
        $templateSheet->mergeCells('G2:G3');
        $templateSheet->mergeCells('H2:H3');
        $templateSheet->mergeCells('I2:I3');
        $templateSheet->mergeCells('J2:J3');
        $templateSheet->mergeCells('K2:K3');
        $templateSheet->mergeCells('L2:L3');
        $templateSheet->mergeCells('M2:M3');
        $templateSheet->mergeCells('N2:N3');
        $templateSheet->mergeCells('O2:O3');
        $templateSheet->mergeCells('P2:P3');
        $templateSheet->mergeCells('Q2:Q3');
        $templateSheet->mergeCells('R2:R3');
        $templateSheet->mergeCells('S2:S3');
        $templateSheet->mergeCells('T2:T3');
        $templateSheet->getStyle('A2:T2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A3:T3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A2')->getFont()->setBold(true);
        $templateSheet->getStyle('B2:H2')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('E3:F3')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('I2:T2')->getFont()->setBold(true);
        $templateSheet->getStyle('A2:T2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $templateSheet->getStyle('A3:T3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach ($comments as $cell => $commentLines) {
            $richText = new RichText();
            foreach ($commentLines as $index => $line) {
                $run = new Run($line);
                $run->getFont()->setSize(9);
                $run->getFont()->setName('Times New Roman');

                if ($index === 0) {
                    $run->getFont()->setBold(true);
                }
        
                $richText->createText($line);
                if ($index < count($commentLines) - 1) {
                    $richText->createText("\n");
                }
            }
        
            $comment = $templateSheet->getComment($cell);
            $comment->setText($richText);
            $comment->setWidth('135px');
            $comment->setHeight('120px');
            $comment->setAuthor('Author Name');
        }
        // Second Sheet: Reference
        $item_refSheet = $spreadsheet->createSheet(1);
        $item_refSheet->setTitle('REFERENCE');

        $this->db->select('a.item_fg_customer as product_no_customer, a.item_fg_id as product_id, c.name as product_name,a.price, a.valid_to as valid_date, b.number as customer_code, b.name as customer_name, c.number as product_no');
        // $this->db->select('a.item_fg_customer as product_no, a.item_fg_id as product_id, c.name as product_name,a.price, a.valid_date, b.number as customer_code, b.name as customer_name');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
        $this->db->order_by('b.name','asc');
        $this->db->order_by('a.item_fg_id','asc');
        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(10);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(25);
        $item_refSheet->getColumnDimension('D')->setWidth(20);
        $item_refSheet->getColumnDimension('E')->setWidth(20);
        $item_refSheet->getColumnDimension('F')->setWidth(25);
        $item_refSheet->getColumnDimension('G')->setWidth(25);
        $item_refSheet->getColumnDimension('H')->setWidth(20);
        $item_refSheet->getColumnDimension('I')->setWidth(15);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Customer Code');
        $item_refSheet->setCellValue('C1', 'Customer Name');
        $item_refSheet->setCellValue('D1', 'Product ID');
        $item_refSheet->setCellValue('E1', 'Product No');
        $item_refSheet->setCellValue('F1', 'Product No Customer');
        $item_refSheet->setCellValue('G1', 'Product Name');
        $item_refSheet->setCellValue('H1', 'Price');
        $item_refSheet->setCellValue('I1', 'Valid Date');
        $item_refSheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:I1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['customer_code']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['customer_name']);
            $item_refSheet->setCellValue('D' . $rowItem_ref, $itemref['product_id']);
            $item_refSheet->setCellValue('E' . $rowItem_ref, $itemref['product_no']);
            $item_refSheet->setCellValue('F' . $rowItem_ref, $itemref['product_no_customer']);
            $item_refSheet->setCellValue('G' . $rowItem_ref, $itemref['product_name']);
            $item_refSheet->setCellValue('H' . $rowItem_ref, $itemref['price']);
            $item_refSheet->setCellValue('I' . $rowItem_ref, $itemref['valid_date']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('H' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('I' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':I' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':I' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        $spreadsheet->setActiveSheetIndex(0); 
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_forecasts.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
    
    // public function checkDuplicate()
    // {
    //     if ($this->input->post()) {
    //         $customer_id = $this->input->post('customer_id');
    //         $item_fg_id = $this->input->post('item_fg_id');
    //         $revision = $this->input->post('revision');
    //         $p_month = $this->input->post('p_month');
    //         $p_year = $this->input->post('p_year');

    //         $existingForecast = $this->crud->read("forecasts", [], [
    //             "customer_id" => $customer_id,
    //             "item_fg_id" => $item_fg_id,
    //             "revision" => $revision,
    //             "p_month" => $p_month,
    //             "p_year" => $p_year
    //         ]);

    //         echo json_encode(['exists' => !empty($existingForecast)]);
    //     }
    // }
    
    public function checkDuplicate()
    {
        if ($this->input->post()) {
            $customer_id = $this->input->post('customer_id');
            $item_fg_id = $this->input->post('item_fg_id');
            $revision = $this->input->post('revision');
            $p_month = $this->input->post('p_month');
            $p_year = $this->input->post('p_year');
            $mode = $this->input->post('mode');

            $where = [
                "customer_id" => $customer_id,
                "item_fg_id" => $item_fg_id,
                "revision" => $revision,
                "p_month" => $p_month,
                "p_year" => $p_year
            ];

            $existingForecast = $this->crud->read("forecasts", [], $where);

            // Jika mode update, dan data yang ditemukan adalah data yang sama, maka anggap tidak duplikat
            if ($mode === 'update' && !empty($existingForecast)) {
                echo json_encode(['exists' => false]);
            } else {
                echo json_encode(['exists' => !empty($existingForecast)]);
            }
        }
    }

}
