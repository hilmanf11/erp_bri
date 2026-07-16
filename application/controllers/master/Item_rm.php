<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Item_rm extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[100]|is_unique[item_rm.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_rm');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.number like '%$post%' or a.name like '%$post%' or a.id like '%$post%' or a.item_family_id like '%$post%'");
        echo json_encode($send);
    }

    public function readsRM()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_rm WHERE number like '%$post%' or name like '%$post%' or id like '%$post%' or number_internal like '%$post%'");
        echo json_encode($send);
    }

    public function readsNumberInternal()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.number_internal like '%$post%' or a.name like '%$post%' or a.id like '%$post%' or a.item_family_id like '%$post%'");
        echo json_encode($send);
    }

    public function readsC()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('item_rm', ["number" => $post], ["item_family_id" => "P03"]);
        echo json_encode($send);
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
            $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as item_sub_family_number, d.name as item_sub_family_name, e.name as division, e.number as division_number');
            $this->db->from('item_rm a');
            $this->db->join('item_categories b', 'a.item_category_id = b.id');
            $this->db->join('item_familys c', 'a.item_family_id = c.id');
            $this->db->join('item_family_subs d', 'a.item_sub_family_id = d.id', 'left');
            $this->db->join('divisions e', 'a.division = e.number', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "item_category_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "item_sub_family_name") {
                        $this->db->like("d.name", $filter->value);
                    } elseif ($filter->field == "status") {
                        // Menambahkan filter untuk kolom status
                        if ($filter->value == "Active") {
                            $this->db->where("a.status", 0);
                        } elseif ($filter->value == "Not Active") {
                            $this->db->where("a.status", 1);
                        }
                    } elseif ($filter->field == "supply") {
                        // Menambahkan filter untuk kolom supply
                        if ($filter->value == "YES") {
                            $this->db->where("a.supply", 0);
                        } elseif ($filter->value == "NO") {
                            $this->db->where("a.supply", 1);
                        }
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.id', 'ASC');
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
    //CODE OTOMATIS
    public function autoid($item_category_number, $item_family_number, $item_family_sub_number = "NA")
    {
        $code = $item_category_number . $item_family_number . $item_family_sub_number;
        $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From item_rm where id like '%$code%'");
        $row = $sql->row();
        $kode = substr($row->kode, -4);
        $autoid = $code . "-" . sprintf("%04s", $kode + 1);
        echo $autoid;
    }
    //CODE OTOMATIS
    public function autoid_ps($item_family_sub_number = "NA")
    {
        // $code = $item_category_number . $item_family_number . $item_family_sub_number;
        $sql = $this->db->query("SELECT COALESCE(MAX(number_internal), 0) as kode From item_rm where number_internal like '%$item_family_sub_number%'");
        $row = $sql->row();
        $kode = substr($row->kode, -5);
        $autoid = $item_family_sub_number . "-" . sprintf("%05s", (int) $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('item_rm', $post);
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
            $send = $this->crud->update('item_rm', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadatt($isImage = "")
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/item_rm/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan ada file yang diunggah dari permintaan
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                // Validasi ekstensi file yang diunggah

                if($isImage != "") {
                    $allowedExtensions = ['jpg', 'jpeg', 'png'];
                } else {
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                }

                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {

                    if($isImage != "") {

                        echo json_encode(['success' => false, 'message' => 'Only files with the extension .jpg, .jpeg, or .png are allowed.']);
                    } else {

                        echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, .jpeg, or .png are allowed.']);
                    }
                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
                }

                // Validasi ukuran file yang diunggah (maksimal 5MB)
                $maxFileSize = 2 * 1024 * 1024; // 2MB dalam bytes
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'The file size is too large, maximum 2MB']);
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

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_rm', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777); // Perbaikan: Menggunakan nama file yang benar

        $file = $target;
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        $datas = [];

        for ($i = 3; $i <= $total_row; $i++) {
            $part_no = $data->val($i, 2);
            $name = $data->val($i, 3);
            $cas_no = $data->val($i, 4);
            $unit_of_measure = $data->val($i, 5);
            $type = $data->val($i, 6);
            $category = $data->val($i, 7);
            $product_family = $data->val($i, 8);
            $sub_product_family = $data->val($i, 9);
            $account_number = $data->val($i, 10);
            $account_name = $data->val($i, 11);
            $description = $data->val($i, 12);
            $specification = $data->val($i, 13);
            $leadtime = $data->val($i, 14);
            $lifetime = $data->val($i, 15);
            $safety_stock = $data->val($i, 16);
            $supply = $data->val($i, 17);
            $status = $data->val($i, 18);

            $datas[] = array(
                'part_no' => $part_no,
                'name' => $name,
                'cas_no' => $cas_no,
                'unit_of_measure' => $unit_of_measure,
                'type' => $type,
                'category' => $category,
                'product_family' => $product_family,
                'sub_product_family' => $sub_product_family,
                'account_number' => $account_number,
                'account_name' => $account_name,
                'description' => $description,
                'specification' => $specification,
                'leadtime' => $leadtime,
                'lifetime' => $lifetime,
                'safety_stock' => $safety_stock,
                'supply' => $supply,
                'status' => $status
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($file);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/item_rm.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_rm.txt";
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

            // Validasi kolom yang kosong
            $required_fields = [
                'part_no' => 'Part No',
                'name' => 'Part Name',
                'unit_of_measure' => 'Unit of Measure',
                'type' => 'Part Type',
                'category' => 'Category',
                'product_family' => 'Product Family',
                'supply' => 'Supply',
                'status' => 'Status'
            ];
            $missing_fields = [];

            foreach ($required_fields as $field => $field_name) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $missing_fields[] = $field_name;
                }
            }

            if (!empty($missing_fields)) {
                echo json_encode(array("title" => "Error", "message" => "Column Cannot Be Empty : " . implode(', ', $missing_fields), "theme" => "error"));
                return;
            }

            // Validasi kategori tidak boleh FG
            if ($data['category'] === 'FG') {
                echo json_encode(array("title" => "Error", "message" => "Category 'FG' is not allowed", "theme" => "error"));
                return;
            }

            // Validasi status (harus 0 atau 1)
            if (!in_array($data['status'], ['0', '1'])) {
                echo json_encode(array("title" => "Error", "message" => "Status must be 0 (Active) atau 1 (Not Active)", "theme" => "error"));
                return;
            }

            // Validasi supply (harus 0 atau 1)
            if (!in_array($data['supply'], ['0', '1'])) {
                echo json_encode(array("title" => "Error", "message" => "Supply must be 0 or 1", "theme" => "error"));
                return;
            }

            if (!empty($data['cas_no']) && !preg_match('/^[0-9-]{1,15}$/', $data['cas_no'])) {
                echo json_encode(array(
                    "title" => "Error",
                    "message" => "CAS No must contain only numbers and dash (-), maximum 15 characters",
                    "theme" => "error"
                ));
                return;
            }

            //Cek Process Number
            $category = $this->crud->read('item_categories', [], ["number" => $data['category']]);
            $prod_fam = $this->crud->read('item_familys', [], ["number" => $data['product_family']]);
            $prod_sub_fam = $this->crud->read('item_family_subs', [], ["number" => $data['sub_product_family']]);
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['part_no']]);

            if (empty($category->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category Code " . $data['category'] . " Not Found", "theme" => "error"));
            } elseif (empty($prod_fam->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family Code " . $data['product_family'] . " Not Found", "theme" => "error"));
            } elseif (!empty($item_rm->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['part_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                //autoid
                $code = $data['category'] . $data['product_family'] . "NA";

                // if (empty($prod_sub_fam->number)) {
                // } else {
                //     $code = $data['category'] . $data['product_family'] . $data['sub_product_family'];
                // }

                //Cek Number Internal
                $sqlItem = $this->db->query("SELECT COALESCE(MAX(number_internal), 0) as kode From item_rm where number_internal like '%$prod_sub_fam->number%'");
                $rowItem = $sqlItem->row();
                $codeItem = substr($rowItem->kode, -5);
                $number_internal = $prod_sub_fam->number . "-" . sprintf("%05s", (int) $codeItem + 1);

                $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From item_rm where id like '%$code%'");
                $row = $sql->row();
                $kode = substr($row->kode, -4);
                $autoid = $code . "-" . sprintf("%04s", $kode + 1);
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "number" => $data['part_no'],
                    "cas_no" => $data['cas_no'],
                    "number_internal" => $number_internal,
                    "name" => $data['name'],
                    "uom" => $data['unit_of_measure'],
                    "type" => $data['type'],
                    "item_category_id" => @$category->id,
                    "item_family_id" => @$prod_fam->id,
                    "item_sub_family_id" => @$prod_sub_fam->id,
                    "account_number" => $data['account_number'],
                    "account_name" => $data['account_name'],
                    "description" => $data['description'],
                    "specification" => $data['specification'],
                    "leadtime" => $data['leadtime'],
                    "lifetime" => $data['lifetime'],
                    "safety_stock" => $data['safety_stock'],
                    "supply" => $data['supply'],
                    "status" => $data['status'],
                );
                $send = $this->crud->create('item_rm', $dataFinal);
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
            header("Content-Disposition: attachment; filename=item_rm_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as item_sub_family_number');
        $this->db->from('item_rm a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->join('item_family_subs d', 'a.item_sub_family_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_rm {border-collapse: collapse;width: 100%;font-size: 12px;}#item_rm td, #item_rm th {border: 1px solid #ddd;padding: 2px;}#item_rm tr:nth-child(even){background-color: #f2f2f2;}#item_rm tr:hover {background-color: #ddd;}#item_rm th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM RAW MATERIAL</h3>
            </div>
        </center>
        
        <table id="item_rm" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Part No External</th>
                <th>Part No Internal</th>
                <th>Part Name</th>
                <th>CAS No</th>
                <th>UOM</th>
                <th>Type</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Product Family Sub</th>
                <th>Account No.</th>
                <th>Account Name</th>
                <th>Description</th>
                <th>Specification</th>
                <th>Leadtime</th>
                <th>Lifetime</th>
                <th>Safety Stock (%)</th>
                <th>Supply</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number_internal'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['cas_no'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['item_sub_family_number'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['description'] . '</td>
                        <td>' . $data['specification'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['lifetime'] . '</td>
                        <td>' . $data['safety_stock'] . '</td>
                        <td>' . $data['supply'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
