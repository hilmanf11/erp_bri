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
class Sales_orders extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[customer_items.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readProductNo()
    {
        $send = $this->crud->query("SELECT b.id, b.number, b.name
            FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id and b.type = 'FG'
            JOIN customers c ON a.customer_id = c.id
            GROUP BY b.number");
        echo json_encode($send);
    }

    // public function readItemFg($customer_id)
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $send = $this->crud->query("SELECT 
    //             b.id, 
    //             b.number, 
    //             b.name, 
    //             b.number_customer, 
    //             a.price, 
    //             c.currency, 
    //             b.uom, 
    //             a.valid_from, 
    //             a.valid_to, 
    //             COALESCE(SUM(d.qty_del), 0) AS delivery
    //         FROM customer_items a 
    //         JOIN item_fg b 
    //             ON a.item_fg_id = b.id AND b.type = 'FG'
    //         JOIN customers c 
    //             ON a.customer_id = c.id
    //         JOIN delivery_orders d 
    //             ON b.id = d.item_fg_id 
    //             AND d.status = 0 
    //             AND c.id = d.customer_id
    //         JOIN sales_orders so 
    //             ON d.customer_order_no = so.customer_order_no 
    //             AND d.customer_id = so.customer_id 
    //             AND d.item_fg_id = so.item_fg_id
    //         WHERE a.customer_id = '$customer_id' 
    //             AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
    //         GROUP BY b.number
    //     ");

    //     echo json_encode($send);
    // }

    public function readItemFg($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_order_no = isset($_POST['customer_order_no']) ? $_POST['customer_order_no'] : "";

        $sql = "
            SELECT 
                b.id, 
                b.number, 
                b.name, 
                b.number_customer, 
                a.price, 
                c.currency, 
                b.uom, 
                a.valid_from, 
                a.valid_to, 
                COALESCE(SUM(d.qty_del), 0) AS delivery
            FROM customer_items a 
            JOIN item_fg b 
                ON a.item_fg_id = b.id AND b.type = 'FG'
            JOIN customers c 
                ON a.customer_id = c.id
            LEFT JOIN delivery_orders d 
                ON b.id = d.item_fg_id 
                AND d.status = 0 
                AND c.id = d.customer_id
                " . (!empty($customer_order_no) ? " AND d.customer_order_no = '$customer_order_no'" : "") . "
            LEFT JOIN sales_orders so 
                ON d.customer_order_no = so.customer_order_no 
                AND d.customer_id = so.customer_id 
                AND d.item_fg_id = so.item_fg_id
            WHERE a.customer_id = '$customer_id' 
            AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
            GROUP BY b.id, b.number, b.name, b.number_customer, a.price, c.currency, b.uom, a.valid_from, a.valid_to
        ";

        $send = $this->crud->query($sql);
        echo json_encode($send);
    }


    public function readItems($customer_id, $sales_order_no)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty
            FROM sales_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            WHERE a.customer_id = '$customer_id' and a.sales_order_no = '$sales_order_no' and a.status = 0 and (b.number LIKE '%$post%' or b.name LIKE '%$post%') ");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id = null)
    {
        if (!empty($customer_id)) {
            $query = "SELECT DISTINCT sales_order_no, sales_order_date FROM sales_orders WHERE customer_id = '$customer_id' and status = 0";
        } else {
            $query = "SELECT DISTINCT sales_order_no, sales_order_date FROM sales_orders WHERE status = 0";
        }
        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id = null)
    {
        if (!empty($customer_id)) {
            $query = "SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id'";
        } else {
            $query = "SELECT DISTINCT customer_order_no FROM sales_orders";
        }
        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function number($customer_id, $sales_order_date)
    {
        $datenow    = "SO" . $customer_id . date("ymd", strtotime(base64_decode($sales_order_date)));
        $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        echo $datenow . $autoID;
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_division = @base64_decode($get['filter_division']);
            $filter_status = @base64_decode($get['filter_status']);
            $filter_product_family = @base64_decode($get['filter_product_family']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, c.name as division_name, case when a.order_type = 1 then 'Regular' when a.order_type = 2 then 'Additional' else 'Regular' end as so_type, b.name as customer_name");
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('divisions c', 'a.division = c.number', 'left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.sales_order_date >=', $filter_from);
                $this->db->where('a.sales_order_date <=', $filter_to);
            }
            if ($filter_product_family != "") {
                $this->db->where('d.item_family_number', $filter_product_family);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_fg_id', $filter_sales_order_no);
            $this->db->like('a.division', $filter_division);
            $this->db->like('a.status', $filter_status);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->group_by(['a.sales_order_no', 'a.customer_order_no']);
            $this->db->order_by('a.status', 'ASC');
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
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));
            $product_family = base64_decode($this->input->get('product_family'));
            $filter_division = base64_decode($this->input->get('filter_division'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.sales_order_no', $sales_order_no);
            if ($product_family != "") {
                $this->db->where('b.item_family_number', $product_family);
            }
            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function create()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');
        $errors = [];
        $success_count = 0;

        $this->db->trans_begin();

        foreach ($items as $post) {
            if (empty($post['sales_order_no'])) {
                $errors[] = "Sales Order No cannot be empty.";
                continue;
            }

            // Validasi duplikasi berdasarkan customer_order_no + item_fg_id dengan delivery_date berbeda
            $date_conflict_check = $this->crud->read("sales_orders", [], [
                "customer_order_no" => $post['customer_order_no'],
                "item_fg_id" => $post['item_fg_id'],
                "delivery_date !=" => $post['delivery_date'],
                "sales_order_no !=" => $post['sales_order_no']
            ]);

            if (!empty($date_conflict_check)) {
                $errors[] = "Duplicate: Customer Order No and Product Name exist with another delivery date";
                continue;
            }

            // Cek apakah data sudah ada
            $sales_orders = $this->crud->read("sales_orders", [], [
                "customer_order_no" => $post['customer_order_no'],
                "sales_order_no" => $post['sales_order_no'],
                "item_fg_id" => $post['item_fg_id'],
                "delivery_date"  => $post["delivery_date"],
            ]);

            if (!empty($sales_orders)) {
                $old_qty = $sales_orders->qty;
                $new_qty = $post['qty'];

                if($old_qty > $new_qty) {
                    $errors[] = "Qty must be greater than before";
                    continue;
                }

                $result = $this->crud->update('sales_orders', [
                    "customer_order_no" => $post['customer_order_no'],
                    "sales_order_no" => $post['sales_order_no'],
                    "item_fg_id" => $post['item_fg_id'],
                    "delivery_date"  => $post["delivery_date"],
                ], $post);

                if ($result) {
                    if ($new_qty > $old_qty) {
                        // Ambil semua delivery_orders terkait dan urutkan berdasarkan ID ASC
                        $this->db->select('id, qty_del');
                        $this->db->where([
                            "customer_order_no" => $post['customer_order_no'],
                            "sales_order_no"    => $post['sales_order_no'],
                            "item_fg_id"        => $post['item_fg_id'],
                        ]);
                        $this->db->order_by('id', 'ASC');
                        $delivery_orders = $this->db->get('delivery_orders')->result();

                        if (!empty($delivery_orders)) {
                            $qty_so = $post['qty'];
                            $total_qty_del = 0;

                            foreach ($delivery_orders as $do) {
                                // qty_remain dihitung berdasarkan qty_so - total qty_del sebelum baris ini
                                $qty_remain = $qty_so - $total_qty_del;

                                // Update baris ini
                                $this->db->where('id', $do->id);
                                $this->db->update('delivery_orders', [
                                    'qty_so'     => $qty_so,
                                    'qty_remain' => $qty_remain
                                ]);

                                // Tambahkan qty_del untuk iterasi berikutnya
                                $total_qty_del += $do->qty_del;
                            }
                        }
                    }
                }


            } else {
                // Cek apakah customer_order_no sudah pernah digunakan oleh data sebelumnya
                $existing_customer_order = $this->crud->reads("sales_orders", [], [
                    "customer_order_no" => $post['customer_order_no']
                ]);

                if (!is_array($existing_customer_order)) {
                    $existing_customer_order = [];
                }                

                if (!empty($existing_customer_order)) {
                    $is_existing_combination = false;
                    foreach ($existing_customer_order as $existing) {
                        if (
                            $existing->sales_order_no == $post['sales_order_no'] &&
                            $existing->delivery_date == $post['delivery_date']
                        ) {
                            $is_existing_combination = true;
                            break;
                        }
                    }

                    if (!$is_existing_combination) {
                        $errors[] = "Customer Order No '{$post['customer_order_no']}' has already been used.";
                        continue;
                    }
                }

                $result = $this->crud->create('sales_orders', $post);
            }

            if ($result) {
                $success_count++;
            } else {
                $errors[] = "Failed to save item for Sales Order No: {$post['sales_order_no']}.";
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

    
    public function updateSO()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);
            if (!empty($sales_orders->sales_order_no)) {
                $send = $this->crud->update('sales_orders', ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']], $post);
                echo $send;
            }else{
                show_error("Cannot Process your update request");
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/sales_orders/';

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

    //DELETE DATA
    public function deleted()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_orders', $data);
        echo $send;
    }

    public function delete()
    {
        $sales_order_no = $this->input->post('sales_order_no');
        $item_fg_id = $this->input->post('item_fg_id');

        // Cek apakah ada relasi ke delivery_orders
        $this->db->where([
            'sales_order_no' => $sales_order_no
        ]);
        $exists = $this->db->get('delivery_orders')->num_rows();

        if ($exists > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete. This Sales Order is already linked to a Delivery Order.'
            ]);
            return;
        }

        // Jika tidak ada relasi, hapus
        $deleted = $this->crud->delete('sales_orders', [
            'sales_order_no' => $sales_order_no,
            'item_fg_id'     => $item_fg_id
        ]);

        echo json_encode([
            'success' => $deleted,
            'message' => $deleted ? 'Sales Order deleted successfully' : 'Failed to delete Sales Order'
        ]);
    }

    public function upload()
    {
        error_reporting(0);
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777);
        $spreadsheet = IOFactory::load($target);
        $sheet = $spreadsheet->getActiveSheet();
        $totalRows = $sheet->getHighestDataRow();

        $total_sub = 0;
        $last_customer_order_no = "";
        $urutan_id = 0;
        $last_customer_id = "";
        $last_sales_order_no = "";
        $sales_order_date = date("Y-m-d");

        $datas = [];
        $total_per_sales_order = []; // Array untuk menyimpan total_sub per sales_order_no

        for ($i = 4; $i <= $totalRows; $i++) {
            $customer_id = $this->crud->read('customers', [], ["number" => $sheet->getCell('B' . $i)->getValue()]);
            // $cust = $this->crud->read('customers', [], ["number" => $customer_id]);
            $customer_order_no = $sheet->getCell('C' . $i)->getValue();

            $item_fg_number = $sheet->getCell('D' . $i)->getValue();
            $qty = $sheet->getCell('E' . $i)->getValue();
            $delivery_date = $sheet->getCell('F' . $i)->getValue();
            $order_type = $sheet->getCell('G' . $i)->getValue();
            $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
            if (!empty($item_fg->number)) {
            // Skip baris kosong
            if (empty($sheet->getCell('B' . $i)->getValue()) && empty($customer_order_no) && empty($item_fg_number) && empty($qty)) {
                continue;
            }

            if ($last_customer_order_no == $customer_order_no) {
                $customer_id->id = $last_customer_id;
                $sales_order_no = $last_sales_order_no;
                $urutan_id = $urutan_id;
            } else {
                if ($last_customer_id != $customer_id->id) {
                    $datenow    = "SO" . $customer_id->id . date("ymd");
                    $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
                    $rowID      = $sqlGetID->row();
                    $kode       = $rowID->kode;
                    if ($kode == NULL) {
                        $autoID = sprintf("%03s", $kode + 1);
                        $urutan_id = 1;
                    } else {
                        $urutan = (int) substr($kode, -3);
                        $urutan++;
                        $autoID = sprintf("%03s", $urutan);
                        $urutan_id = $urutan;
                    }

                    $sales_order_no = $datenow . $autoID;
                } else {
                    $datenow    = "SO" . $customer_id->id . date("ymd");
                    $sales_order_no = $datenow . sprintf("%03s", $urutan_id + 1);
                    $urutan_id++;
                }
            }

            $cust_add = $this->crud->read('customer_address', [], ["customer_id" => @$customer_id->id]);
            $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id,"customer_id" => @$customer_id->id]);
            $price = $customer_items->price;
            $total = ($qty * $price);

            // Simpan total untuk setiap sales_order_no di array $total_per_sales_order
            if (isset($total_per_sales_order[$sales_order_no])) {
                $total_per_sales_order[$sales_order_no] += $total;
            } else {
                $total_per_sales_order[$sales_order_no] = $total;
            }

            $datas[] = array(
                //excel
                'customer_id' => $customer_id->id,
                'sales_order_date' => $sales_order_date,
                'customer_address_id' => @$cust_add->id,
                'customer_order_no' => $customer_order_no,
                'item_fg_id' => $item_fg->id,
                'qty' => $qty,
                'outstanding' => $qty,
                'delivery_date' => $delivery_date,
                // 'remarks' => $data->val(4, 5),
                'currency' => $customer_id->currency,
                'taxes' => $customer_id->taxes,
                'price' => $customer_items->price,
                'sales_order_no' => $sales_order_no,
                "total" => $total,
                'uom' => $item_fg->uom,
                'plant' => $cust_add->plant,
                'department' => $cust_add->department,
                'attention_to' => $cust_add->contact_person,
                'order_type' => $order_type
            );

            $last_customer_order_no = $customer_order_no;
            $urutan_id = $urutan_id;
            $last_customer_id = $customer_id->id;
            $last_sales_order_no = $sales_order_no;
            
        }
        }

        // Tambahkan total_sub per sales_order_no ke array data
        foreach ($datas as &$data_row) {
            $sales_order_no = $data_row['sales_order_no'];
            if (isset($total_per_sales_order[$sales_order_no])) {
                $data_row['total_sub'] = $total_per_sales_order[$sales_order_no];
            }
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    // public function upload()
    // {
    //     error_reporting(0);
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($target, 0777);
    //     $spreadsheet = IOFactory::load($target);
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $totalRows = $sheet->getHighestDataRow();

    //     // $customer_id = $sheet->getCell('B')->getValue();
    //     // $sales_order_date = date('Y-m-d');

    //     // $datenow    = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
    //     // $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //     // $rowID      = $sqlGetID->row();
    //     // $kode       = $rowID->kode;
    //     // if ($kode == NULL) {
    //     //     $autoID = sprintf("%03s", $kode + 1);
    //     // } else {
    //     //     $urutan = (int) substr($kode, -3);
    //     //     $urutan++;
    //     //     $autoID = sprintf("%03s", $urutan);
    //     // }        

    //     // $sales_order_no = $datenow . $autoID;

    //     $total_sub = 0;
    //     $last_customer_order_no = "";
    //     $urutan_id = 0;
    //     $last_customer_id = "";
    //     $last_sales_order_no = "";
        
    //     $datas = [];
    //     $total_per_sales_order = [];
    //     for ($i = 4; $i <= $totalRows; $i++) {
            
    //         $customer_id = $this->crud->read('customers', [], ["number" => $sheet->getCell('B' . $i)->getValue()]);
    //         $sales_order_date = date("Y-m-d");
            

    //         if ($last_customer_order_no == $sheet->getCell('C' . $i)->getValue()) {
    //             $customer_id["id"] = $last_customer_id;
    //             $sales_order_no = $last_sales_order_no;
    //             $urutan_id = $urutan_id;
    //         } else {
    //             if ($last_customer_id != $customer_id->id) {
    //                 // $datenow    = "SO" . $customer_id->id . date("ymd", strtotime($sales_order_date));
    //                 $datenow    = "SO" . $customer_id->id . date("ymd");
    //                 $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //                 $rowID      = $sqlGetID->row();
    //                 $kode       = $rowID->kode;
    //                 if ($kode == NULL) {
    //                     $autoID = sprintf("%03s", $kode + 1);
    //                     $urutan_id = 1;
    //                 } else {
    //                     $urutan = (int) substr($kode, -3);
    //                     $urutan++;
    //                     $autoID = sprintf("%03s", $urutan);
    //                     $urutan_id = $urutan;
    //                 }        
            
    //                 $sales_order_no = $datenow . $autoID;
    //             }else{
    //                 $datenow    = "SO" . $customer_id->id . date("ymd");
    //                 $sales_order_no = $datenow . sprintf("%03s", $urutan_id + 1);
    //                 $urutan_id++;
                    
    //             }
    //         }

    //         $item_fg_number = $sheet->getCell('D' . $i)->getValue();
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
    //         $cust_add = $this->crud->read('customer_address', [], ["customer_id" => $$customer_id->id]);

    //         if (!empty($item_fg->number)) {
    //             $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id,"customer_id" => $customer_id->id]);
    //             $total = (($sheet->getCell('E' . $i)->getValue()) * $customer_items->price);
                
    //                         // Simpan total untuk setiap sales_order_no di array $total_per_sales_order
    //             if (isset($total_per_sales_order[$sales_order_no])) {
    //                 $total_per_sales_order[$sales_order_no] += $total;
    //             } else {
    //                 $total_per_sales_order[$sales_order_no] = $total;
    //             }
    //             $datas[] = array(
    //                 //excel
    //                 'customer_id' => $customer_id->id,
    //                 'sales_order_date' => $sales_order_date,
    //                 'delivery_date' => $sheet->getCell('F')->getValue(),
    //                 'customer_address_id' => @$cust_add->id,
    //                 'customer_order_no' => $sheet->getCell('C' . $i)->getValue(),
    //                 'item_fg_id' => $item_fg->id,
    //                 'qty' => $sheet->getCell('E' . $i)->getValue(),
    //                 'outstanding' => $sheet->getCell('E' . $i)->getValue(),
    //                 'currency' => $customer_id->currency,
    //                 'taxes' => $customer_id->taxes,
    //                 'price' => $customer_items->price,
    //                 'sales_order_no' => $sales_order_no,
    //                 "total" => $total,
    //                 'uom' => $item_fg->uom,
    //                 'plant' => $cust_add->plant,
    //                 'department' => $cust_add->department,
    //                 'attention_to' => $cust_add->contact_person,
                
    //             );
    //             // $total_sub += $total;
                    
    //             $last_customer_order_no = $sheet->getCell('C' . $i)->getValue();
    //             $urutan_id = $urutan_id;
    //             $last_customer_id = $customer_id->id;
    //             $last_sales_order_no = $sales_order_no;
    //         }
    //     }

    //     // Tambahkan total_sub per sales_order_no ke array data
    //     foreach ($datas as &$data_row) {
    //         $sales_order_no = $data_row['sales_order_no'];
    //         if (isset($total_per_sales_order[$sales_order_no])) {
    //             $data_row['total_sub'] = $total_per_sales_order[$sales_order_no];
    //         }
    //     }
    
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($target);
    // }
    // public function upload()
    // {
    //     error_reporting(0);
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($target, 0777);
    //     $spreadsheet = IOFactory::load($target);
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $totalRows = $sheet->getHighestDataRow();

    //     $customer_id = $sheet->getCell('B')->getValue();
    //     $sales_order_date = date('Y-m-d');

    //     $datenow    = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
    //     $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%03s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -3);
    //         $urutan++;
    //         $autoID = sprintf("%03s", $urutan);
    //     }        

    //     $sales_order_no = $datenow . $autoID;

    //     $total_sub = 0;
        
    //     $datas = [];
    //     for ($i = 4; $i <= $totalRows; $i++) {
            
    //         $customer_id = $this->crud->read('customers', [], ["number" => $sheet->getCell('B' . $i)->getValue()]);
    //         $sales_order_date = date('Y-m-d');
    
    //         $datenow    = "SO" . $customer_id->id . date("ymd", strtotime($sales_order_date));
    //         $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //         $rowID      = $sqlGetID->row();
    //         $kode       = $rowID->kode;
    //         if ($kode == NULL) {
    //             $autoID = sprintf("%03s", $kode + 1);
    //         } else {
    //             $urutan = (int) substr($kode, -3);
    //             $urutan++;
    //             $autoID = sprintf("%03s", $urutan);
    //         }        
    
    //         $sales_order_no = $datenow . $autoID;

    //         $item_fg_number = $sheet->getCell('D' . $i)->getValue();
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
    //         $cust_add = $this->crud->read('customer_address', [], ["customer_id" => $$customer_id->id]);

    //         if (!empty($item_fg->number)) {
    //             $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id,"customer_id" => $customer_id->id]);
    //             $total = (($sheet->getCell('E' . $i)->getValue()) * $customer_items->price);
    //             $datas[] = array(
    //                 //excel
    //                 'customer_id' => $customer_id->id,
    //                 'sales_order_date' => $sales_order_date,
    //                 'delivery_date' => $sheet->getCell('F')->getValue(),
    //                 'customer_address_id' => $cust_add->id,
    //                 'customer_order_no' => $sheet->getCell('C' . $i)->getValue(),
    //                 'item_fg_id' => $item_fg->id,
    //                 'qty' => $sheet->getCell('E' . $i)->getValue(),
    //                 'price' => $customer_items->price,
    //                 'sales_order_no' => $sales_order_no,
    //                 "total" => $total,
    //                 'uom' => $item_fg->uom,
    //                 'plant' => $cust_add->plant,
    //                 'department' => $cust_add->department,
    //                 'attention_to' => $cust_add->contact_person,
                
    //             );
    //             $total_sub += $total;
    //         }
    //     }
    
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($target);
    // }

    public function uploadclearFailed()
    {
        @unlink('failed/sales_orders.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/sales_orders.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/sales_orders.txt";
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
            $data = $this->input->post('data'); //field excel

            $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);
            $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $data['item_fg_id'], "customer_id" => $data['customer_id']]);
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);

            // Check for duplicate sales order by customer_order_no AND delivery_date
            $sales_orders = $this->crud->read('sales_orders', [], [
                "customer_order_no" => $data['customer_order_no'],
                "item_fg_id" => $data['item_fg_id'],
                "delivery_date" => $data['delivery_date'] // Include delivery date in the check
            ]);

            if (empty($customers)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customers ID " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } else if (empty($customer_items)) {
                echo json_encode(array("title" => "Not Match", "message" => "Product No " . $data['item_fg_id'] . " Not Match With Cust ID " . $data['customer_id'], "theme" => "error"));
            } else if (!empty($sales_orders->sales_order_no)) {
                // Duplicate check for both customer_order_no and delivery_date
                echo json_encode(array("title" => "Duplicated", "message" => "Product ID " . $data['item_fg_id'] . " and Customer Order No " . $data['customer_order_no'] . " with Delivery Date " . $data['delivery_date'] . " Duplicated", "theme" => "error"));
            } else if (empty($data['customer_id'])) {
                $this->uploadcreateFailed();
            } else {
                $dataFinal = array(
                    //field        //excel
                    "customer_id" => $data['customer_id'],
                    "sales_order_date" => $data['sales_order_date'],
                    "sales_order_no" => $data['sales_order_no'],
                    "delivery_date" => $data['delivery_date'],
                    "customer_address_id" => $data['customer_address_id'],
                    "plant" => $data['plant'],
                    "attention_to" => $data['attention_to'],
                    "department" => $data['department'],
                    "customer_order_no" => $data['customer_order_no'],
                    "item_fg_id" => $customer_items->item_fg_id,
                    "qty" => $data['qty'],
                    "uom" => $data['uom'],
                    "outstanding" => $data['qty'],
                    "currency" => $customers->currency,
                    "taxes" => $customers->taxes,
                    "price" => $customer_items->price,
                    "total" => $data['total'],
                    "total_sub" => $data['total_sub'],
                    "total_tax" => ($data['total_sub'] * ($customers->taxes / 100)),
                    "total_pph" => 0,
                    "total_grand" => ($data['total_sub'] + ($data['total_sub'] * ($customers->taxes / 100))),
                    "order_type" => $order_type
                );
                $send = $this->crud->create('sales_orders', $dataFinal);
                echo json_encode(array("title" => "Success", "message" => "Data successfully created with Sales Order No: " . $data['sales_order_no'], "theme" => "success"));
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_division = @base64_decode($get['filter_division']);
        $filter_status = @base64_decode($get['filter_status']);
        $filter_product_family = @base64_decode($get['filter_product_family']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, d.name as division_name, case when a.order_type = 1 then 'Regular' when a.order_type = 2 then 'Additional' else 'Regular' end as so_type, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name");
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('divisions d', 'a.division = d.number', 'left');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.sales_order_date >=', $filter_from);
            $this->db->where('a.sales_order_date <=', $filter_to);
        }
        if ($filter_product_family != "") {
            $this->db->where('c.item_family_number', $filter_product_family);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.item_fg_id', $filter_sales_order_no);
        $this->db->like('a.division', $filter_division);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.sales_order_no', 'ASC');
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
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
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
                <h3>SALES ORDER</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Customer Order No</th>
                <th>Division</th>
                <th>SO Type</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Division</th>
                <th>Delivery Date</th>
                <th>Remarks</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Delivery</th>
                <th>Outstanding</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Total</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['division_name'] . '</td>
                        <td>' . $data['so_type'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['sales_order_date'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['delivery'] . '</td>
                        <td>' . $data['outstanding'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['price'], 2, ',', '.') . '</td>
                        <td>' . number_format($data['total'], 2, ',', '.') . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'E3' => ['PLEASE INPUT WITH NUMBER'],
            'F3' => ['PLEASE INPUT WITH DATE : YYYY-MM-DD'],
            'G3' => ['1 = REGULAR', '2 = ADDITIONAL'],
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('SALES ORDERS');
        $templateSheet->mergeCells('A1:G1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16) ->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(25);
        $templateSheet->getColumnDimension('C')->setWidth(25);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(25);
        $templateSheet->getColumnDimension('F')->setWidth(25);
        $templateSheet->getColumnDimension('G')->setWidth(25);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD SALES ORDERS');
        $templateSheet->setCellValue('A3', 'No');
        $templateSheet->setCellValue('B3', 'CUSTOMER CODE');
        $templateSheet->setCellValue('C3', 'CUSTOMER ORDER NO');
        $templateSheet->setCellValue('D3', 'PRODUCT NO');
        $templateSheet->setCellValue('E3', 'QTY');
        $templateSheet->setCellValue('F3', 'DELIVERY DATE');
        $templateSheet->setCellValue('G3', 'ORDER TYPE');
        $templateSheet->getStyle('A3:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A3')->getFont()->setBold(true);
        $templateSheet->getStyle('B3:G3')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A3:G3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
        header('Content-Disposition: attachment; filename="tmp_sales_orders.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }

    // public function checkDuplicate()
    // {
    //     if ($this->input->post()) {
    //         $customer_order_no = $this->input->post('customer_order_no');
    //         $item_fg_id = $this->input->post('item_fg_id');

    //         $existingOrder = $this->crud->read("sales_orders", [], [
    //             "customer_order_no" => $customer_order_no,
    //             "item_fg_id" => $item_fg_id
    //         ]);

    //         echo json_encode(['exists' => !empty($existingOrder)]);
    //     }
    // }
}
