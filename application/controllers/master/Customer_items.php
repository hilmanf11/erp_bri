<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');

class Customer_items extends CI_Controller

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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[customer_items.customer_id]');
        $this->form_validation->set_rules('item_fg_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[customer_items.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/customer_items');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readPlant($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM customer_address WHERE customer_id = '$customer_id' and (plant LIKE '%$post%' or `address` LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readItems()
    {
        $customer_id = base64_decode($this->input->get('customer_id'));
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readItems2()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            WHERE (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    //GET DATATABLES

    public function datatables()

    {

        if ($this->input->post()) {

            $get = $this->input->get();

            $filter_customer_id = @base64_decode($get['filter_customer_id']);

            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);

            $filter_type_item = @base64_decode($get['filter_type_item']);


            $page = $this->input->post('page');

            $rows = $this->input->post('rows');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.item_fg_id, a.item_fg_customer, c.number as item_fg_number, c.name as item_fg_name, a.type_item, a.valid_to, a.valid_from, a.price, a.remark, b.id as customer_id, b.currency, b.number as customer_number, b.name as customer_name, b.type, b.status, a.created_by, a.created_date, a.updated_by, a.updated_date, c.status as status_product');
            $this->db->from('customer_items a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            if (!empty($filter_customer_id)) {
                $this->db->where('a.customer_id', $filter_customer_id);
            }
            if (!empty($filter_item_fg_id)) {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            if (!empty($filter_type_item)) {
                $this->db->where('a.type_item', $filter_type_item);
            }
            $this->db->order_by('a.customer_id', 'ASC');
            $this->db->order_by('c.number', 'ASC');
            $this->db->order_by('a.type_item', 'ASC');
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

            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));



            $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.currency, c.number as item_fg_number, c.name as item_fg_name');

            $this->db->from('customer_items a');

            $this->db->join('customers b', 'a.customer_id = b.id');

            $this->db->join('item_fg c', 'a.item_fg_id = c.id');

            $this->db->where('b.number', $number);

            $this->db->like('a.customer_id', $filter_customer_id);

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
            $item_fg_id = $this->input->get('item_fg_id') ? base64_decode($this->input->get('item_fg_id')) : null;

            // Mendapatkan nilai filter_item_fg_id dari GET jika ada
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_name, c.currency');
            $this->db->from('customer_items a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.customer_id', $customer_id);

            // Tambahkan kondisi where untuk item_fg_id jika ada
            if (!empty($item_fg_id)) {
                $this->db->where('a.item_fg_id', $item_fg_id);
            }

            // Tambahkan kondisi where untuk filter_item_fg_id jika ada
            if (!empty($filter_item_fg_id)) {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }

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

            $type_item = base64_decode($this->input->get('type_item'));


            $this->db->select('a.*, COALESCE(a.type_item, ""),  c.number as item_fg_number');

            $this->db->from('customer_item_histories a');

            $this->db->join('item_fg c', 'a.item_fg_id = c.id');

            $this->db->where('a.customer_id', $customer_id);

            $this->db->where('a.item_fg_id', $item_fg_id);

            $this->db->where('a.type_item', $type_item);

            $this->db->order_by('a.valid_to, a.valid_from', 'DESC');

            $records = $this->db->get()->result_array();



            echo json_encode($records);
        }
    }



    public function uploadatt()

    {

        // Pastikan file disimpan dalam direktori yang diinginkan

        $uploadDir = 'assets/image/customer_items/';



        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Pastikan ada file yang diunggah dari permintaan

            if (isset($_FILES['file'])) {

                $file = $_FILES['file'];



                // Validasi ekstensi file yang diunggah

                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));



                if (!in_array($fileExtension, $allowedExtensions)) {

                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);

                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid

                }



                // Validasi ukuran file yang diunggah (maksimal 5MB)

                $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes

                if ($file['size'] > $maxFileSize) {

                    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB yang diperbolehkan.']);

                    exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar

                }



                // Pastikan tidak ada error dalam proses upload

                if ($file['error'] === UPLOAD_ERR_OK) {

                    // Buat nama unik untuk file yang diunggah

                    $fileName = uniqid() . '_' . $file['name'];

                    $uploadPath = $uploadDir . $fileName;



                    // Pindahkan file dari temporary directory ke lokasi yang diinginkan

                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

                        // File berhasil diunggah

                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {

                        // Gagal menyimpan file

                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {

                    // Ada error dalam proses upload

                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {

                // File tidak ditemukan dalam permintaan

                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {

            // Metode request yang diperlukan adalah POST

            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }



    //CREATE DATA

    // public function create()

    // {

    //     if ($this->input->post()) {

    //         $post = $this->input->post();



    //         $customer_items = $this->crud->read("customer_items", [], ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id']]);

    //         $customer_item_histories = $this->crud->read("customer_item_histories", [], ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id'], "price" => $post['price']]);

    //         if (@$customer_items->customer_id != "") {

    //             $send = $this->crud->update('customer_items', ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id']], $post);

    //             if (@$customer_item_histories->customer_id == "") {

    //                 $send2 = $this->crud->create('customer_item_histories', $post);
    //             }
    //         } else {

    //             $send = $this->crud->create('customer_items', $post);

    //             $send2 = $this->crud->create('customer_item_histories', $post);
    //         }

    //         echo $send;
    //     } else {

    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if (! $this->input->post()) {
            show_error("Cannot Process your request");
            return;
        }

        $post = $this->input->post();
        $type_item = isset($post['type_item']) ? trim($post['type_item']) : '';

        $found = $this->crud->read(
            "customer_items",
            [],
            [
                "customer_id" => $post['customer_id'],
                "item_fg_id"  => $post['item_fg_id'],
                "type_item"   => $type_item
            ]
        );

        $row = null;
        if ($found) {
            if (is_array($found)) $row = reset($found);
            elseif (is_object($found)) $row = $found;
        }

        if (!$row) {
            $row = $this->db
                ->where('customer_id', $post['customer_id'])
                ->where('item_fg_id', $post['item_fg_id'])
                ->group_start()
                    ->where('type_item', '')
                    ->or_where('type_item IS NULL', null, false)
                ->group_end()
                ->get('customer_items')
                ->row();
        }

        if ($row) {
            if (!empty($row->id)) {
                $where = ['id' => $row->id];
            } else {
                $where = [
                    'customer_id' => $post['customer_id'],
                    'item_fg_id'  => $post['item_fg_id']
                ];
            }

            $send = $this->crud->update('customer_items', $where, $post);

            $history = $this->crud->read(
                "customer_item_histories",
                [],
                [
                    "customer_id" => $post['customer_id'],
                    "item_fg_id"  => $post['item_fg_id'],
                    "type_item"   => $type_item,
                    "price"       => $post['price']
                ]
            );
            if (empty($history)) {
                $send2 = $this->crud->create('customer_item_histories', $post);
            }
        } else {
            $send  = $this->crud->create('customer_items', $post);
            $send2 = $this->crud->create('customer_item_histories', $post);
        }

        echo $send;
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('customer_items', $data);
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
                'customer_id' => $data->val($i, 2),
                'product_no' => $data->val($i, 3),
                'product_customer' => $data->val($i, 4),
                'price' => $data->val($i, 5),
                'valid_to' => $data->val($i, 6),
                'valid_from' => $data->val($i, 7),
                'remark' => $data->val($i, 8)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/customer_items.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customer_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/customer_items.txt";
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
            //Cek Process Number     //table        //field           //field excel
            $item = $this->crud->read('item_fg', [], ["number" => $data['product_no']]);
            $customer = $this->crud->read('customers', [], ["number" => $data['customer_id']]);
            $customer_items = $this->crud->read('customer_items', [], ["customer_id" => $customer->id, "item_fg_id" => $item->id]);

            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($customer_items->customer_id) && !empty($customer_items->item_fg_id)) {
                $dataUpdate = array(
                    "customer_id" => $customer->id,
                    "item_fg_id" => $item->id,
                    "item_fg_customer" => $data['product_customer'],
                    "price" => $data['price'],
                    "valid_to" => $data['valid_to'],
                    "valid_from" => $data['valid_from'],
                    "remark" => $data['remark'],
                );

                $dataHistory = array(
                    "customer_id" => $customer_items->customer_id,
                    "item_fg_id" => $customer_items->item_fg_id,
                    "item_fg_customer" => $customer_items->item_fg_customer,
                    "price" => $customer_items->price,
                    "valid_to" => $customer_items->valid_to,
                    "valid_from" => $customer_items->valid_from,
                    "remark" => $customer_items->remark,
                );
                $send = $this->crud->update('customer_items', ["customer_id" => $customer->id, "item_fg_id" => $item->id], $dataUpdate);
                $send2 = $this->crud->create('customer_item_histories', $dataHistory);
                echo $send;
            } else {

                $dataFinal = array(
                    "customer_id" => @$customer->id,
                    "item_fg_id" => @$item->id,
                    "item_fg_customer" => $data['product_customer'],
                    "price" => $data['price'],
                    "valid_to" => $data['valid_to'],
                    "valid_from" => $data['valid_from'],
                    "remark" => $data['remark'],
                );
                $send   = $this->crud->create('customer_items', $dataFinal);
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
            header("Content-Disposition: attachment; filename=customer_items_$format.xls");
        }

        $get = $this->input->get();
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_type_item = @base64_decode($get['filter_type_item']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.currency, c.number as item_fg_number, c.name as item_fg_name');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        // $this->db->where('a.customer_id', $filter_customer_id);
        if (!empty($filter_customer_id)) {
            $this->db->where('a.customer_id', $filter_customer_id);
        }
        if (!empty($filter_item_fg_id)) {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }
        if (!empty($filter_type_item)) {
            $this->db->where('a.type_item', $filter_type_item);
        }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER CUSTOMER ITEM</h3>
            </div>
        </center>

        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer ID</th>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th>Part ID</th>
                <th>Part No.</th>
                <th>Part Name</th>
                <th>Part Customer</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Valid To</th>
                <th>Valid From</th>
                <th>Remarks</th>
            </tr>';

        $no = 1;

        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['customer_id'] . '</td>
                    <td>' . $data['customer_number'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_customer'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . (fmod($data['price'], 1) == 0 ? intval($data['price']) : number_format($data['price'], 2)) . '</td>
                    <td>' . $data['valid_to'] . '</td>
                    <td>' . $data['valid_from'] . '</td>
                    <td>' . $data['remark'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
