<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Finishing_invoices extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/finishing_invoices');
        } else {
            redirect('error_access');
        }
    }


    public function readInvoice()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT * FROM finishing_invoices WHERE `status` = 0 and finishing_invoice_no LIKE '%$post%' ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readSubcont()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        if (!empty($post)) {
            $records = $this->crud->query("
                SELECT id AS subcont, name FROM teaching_factory WHERE name LIKE '%$post%'
                UNION 
                SELECT id AS subcont, name FROM subconts WHERE name LIKE '%$post%'
                ORDER BY name ASC
            ");
        } else {
            $records = $this->crud->query("
                SELECT id AS subcont, name FROM teaching_factory 
                UNION 
                SELECT id AS subcont, name FROM subconts 
                ORDER BY name ASC
            ");
        }

        echo json_encode($records);
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_invoice_no = $this->input->get('filter_invoice_no');
        $filter_subcont = $this->input->get('filter_subcont');

        $user = $this->crud->currentUserDept();

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        
        $this->db->select('
            a.*, 
            COALESCE(tf.name, sc.name) as vendor_name
        ');
        $this->db->from('finishing_invoices a');
        $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
        $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
        $this->db->join('users u', 'u.username = a.created_by', 'left');

        if (!empty($user->department_id) && !in_array($user->department, $this->crud->getIgnoreDept())) {
            $this->db->where('u.department_id', $user->department_id);
        }

        $this->db->where_in('a.deleted', [0, 2]);

        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.finishing_invoice_date >=', $filter_from);
            $this->db->where('a.finishing_invoice_date <=', $filter_to);
        }
        if ($filter_invoice_no != "") {
            $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
        }
        if ($filter_subcont != "") {
            $this->db->where('a.subcont', $filter_subcont);
        }

        $sort = $this->input->post('sort');
        $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
        if ($sort == "finishing_invoice_no") {
            $this->db->order_by('a.finishing_invoice_no', $order);
        } else {
            $this->db->order_by('a.created_date', 'DESC');
        }

        $totalRows = $this->db->count_all_results('', false);
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();

        $arr = [];
        foreach ($records as $record) {
            $arr[] = array(
                "id" => $record['id'], 
                "finishing_invoice_no" => $record['finishing_invoice_no'],
                "finishing_invoice_date" => $record['finishing_invoice_date'],
                "period_start" => $record['period_start'],
                "period_end" => $record['period_end'],
                "vendor_name" => $record['vendor_name'],
                "total" => number_format($record['total'], 0, ',', '.'),
                "biaya_fee" => number_format($record['biaya_fee'], 0, ',', '.'),
                "grand_total" => number_format($record['grand_total'], 0, ',', '.'),
                "deleted" => $record['deleted'],
                "status" => $record['status'],
                "approved_to" => $record['approved_to'],
                "approved_by" => $record['approved_by'],
                "approved_date" => $record['approved_date'],
                "created_by" => $record['created_by'],
                "created_date" => $record['created_date'],
                "updated_by" => $record['updated_by'],
                "updated_date" => $record['updated_date'],
            );
        }
        
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);
        echo json_encode($result);
    }

    public function datatableDetails()
    {
        $id = base64_decode($this->input->get('id'));

        $this->db->select('
            d.item_fg_id, 
            i.number as item_number, 
            i.name as item_name,
            d.price,
            SUM(d.qty) as qty,
            SUM(d.price_fg) as price_fg,
            SUM(d.qty_1) as qty_1,
            SUM(d.price_defect) as price_defect,
            SUM(d.sub_total) as sub_total
        ');
        $this->db->from('finishing_invoice_details d');
        $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
        $this->db->where('d.finishing_invoice_id', $id);
        
        $this->db->group_by(['d.item_fg_id', 'i.number', 'i.name', 'd.price']);
        
        $this->db->order_by('i.number', 'ASC');
        
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    // public function datatable_updates()
    // {
    //     $request_no = base64_decode($this->input->get('request_no'));
    //     $records = $this->crud->query("SELECT a.id, c.number as item_number, c.name as item_name, c.id as item_rm_id, a.qty, a.remarks, e.name as plant, e.id as plant_id
    //         FROM purchase_requests a
    //         JOIN item_rm c on a.item_rm_id = c.id
    //         LEFT JOIN divisions e on a.division = e.id
    //         -- JOIN item_categories d on d.id = c.item_category_id
    //         WHERE a.status = 0 and a.request_no = '$request_no'
    //         GROUP BY c.number");
    //     echo json_encode($records);
    // }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post   = $this->input->post();
    //         $purchase_request_item = $this->crud->read('purchase_requests', [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
    //         if (@$purchase_request_item->id != "") {
    //             $send = $this->crud->updateV2('purchase_requests', 'purchase_requests', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], $post);
    //         } else {

    //             // $send = $this->crud->create('purchase_requests', $post);
    //             $send = $this->crud->createV2('purchase_requests', 'purchase_requests', $post);

    //             if(!$send['status']) {
    //                 echo json_encode($send);
    //                 return;
    //             }

    //             echo json_encode($send);
    //             return;

    //         }

    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = $this->input->post('id');
    //         $request_no  = $this->input->post('request_no');
    //         $request_date  = $this->input->post('request_date');
    //         $request_name  = $this->input->post('request_name');
    //         $item_rm_id  = $this->input->post('item_rm_id');
    //         $expected_date  = $this->input->post('expected_date');
    //         $qty  = $this->input->post('qty');
    //         $remarks = $this->input->post('remarks');
    //         $division = $this->input->post('division');

    //         // Validate inputs
    //         if (empty($qty)) {
    //             echo json_encode(array("title" => "Error", "message" => "Quantity are required", "theme" => "error"));
    //             return;
    //         }
    //         // if (empty($id) && empty($qty)) {
    //         //     echo json_encode(array("title" => "Error", "message" => "ID and Quantity are required", "theme" => "error"));
    //         //     return;
    //         // }

    //         // Prepare data for update
    //         $data = array(
    //             "qty" => $qty,
    //             "remarks" => $remarks,
    //             "expected_date" => $expected_date,
    //             "item_rm_id" => $item_rm_id,
    //             "request_name" => $request_name,
    //             "request_date" => $request_date,
    //             "request_no" => $request_no,
    //             "remarks" => $remarks,
    //             "division" => $division,
    //         );

    //         try {
    //             $purchase_request_item = $this->crud->read('purchase_requests', [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);
    //             // Execute update query
    //             // $send = $this->crud->update('purchase_requests', ["id" => $id], $data);
    //             if (@$purchase_request_item->id != "") {
    //                 $send = $this->crud->updateV2('purchase_requests', 'purchase_requests', ["id" => $id], $data);
    //             } else {
    //                 $send = $this->crud->createV2('purchase_requests', 'purchase_requests', $data);
    //             }
    //             if ($send) {
    //                 echo json_encode(array("title" => "Success", "message" => "Data successfully updated", "theme" => "success"));
    //             } else {
    //                 echo json_encode(array("title" => "Error", "message" => "Failed to update data", "theme" => "error"));
    //             }
    //         } catch (Exception $e) {
    //             // Log exception
    //             log_message('error', 'Update failed: ' . $e->getMessage());

    //             // Return error response
    //             echo json_encode(array("title" => "Error", "message" => "An error occurred while updating data", "theme" => "error"));
    //         }
    //     } else {
    //         show_error("Cannot process your request");
    //     }
    // }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('finishing_invoices', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        $target = basename($_FILES['file_upload']['name']);
        if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $target)) {
            echo json_encode(array("title" => "Error", "message" => "Failed to upload file", "theme" => "error"));
            return;
        }

        chmod($target, 0777);
        $data = new Spreadsheet_Excel_Reader($target, false);
        $total_row = $data->rowcount($sheet_index = 0);

        $vendor_id = trim($data->val(2, 3)); 

        $datas = [];
        for ($i = 4; $i <= $total_row; $i++) {
            $product_number = trim($data->val($i, 5));
            if (empty($product_number)) continue; 

            $price = (float)trim($data->val($i, 6));
            $qty   = (float)trim($data->val($i, 7));
            $qty_1 = (float)trim($data->val($i, 8));

            $price_fg     = $price * $qty;
            $price_defect = $price * $qty_1;

            $datas[] = array(
                'invoice_date' => trim($data->val($i, 2)),
                'period_start' => trim($data->val($i, 3)),
                'period_end'   => trim($data->val($i, 4)),
                'item_number'  => $product_number,        
                'price'        => $price,
                'qty'          => $qty,
                'price_fg'     => $price_fg,
                'qty_1'        => $qty_1,
                'price_defect' => $price_defect
            );
        }

        $response = [
            'header' => [
                'vendor_id' => $vendor_id,
            ],
            'details' => $datas,
            'total_items' => count($datas)
        ];

        echo json_encode($response);
        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/finishing_invoices.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/finishing_invoices.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/finishing_invoices.txt";
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $header_data = $this->input->post('header');
    //         $detail_data = $this->input->post('details'); 

    //         $vendor_id = $header_data['vendor_id'];

    //         $vendor_type = '';
    //         $vendor_id = '';
    //         $fee_db = 0;

    //         $cek_tf = $this->crud->read('teaching_factory', [], ["id" => $vendor_id]);
            
    //         if (!empty($cek_tf)) {
    //             $vendor_type = 'TF';
    //             $vendor_id = $cek_tf->id;
    //             $fee_db = (float)$cek_tf->fee;
    //         } else {
    //             $cek_subcont = $this->crud->read('subconts', [], ["id" => $vendor_id]);
    //             if (!empty($cek_subcont)) {
    //                 $vendor_type = 'Koordinator';
    //                 $vendor_id = $cek_subcont->id;
    //                 $fee_db = (float)$cek_subcont->fee;
    //             }
    //         }

    //         if (empty($vendor_type)) {
    //             echo json_encode(array("title" => "Error", "message" => "Subcont atau Koordinator tidak ditemukan: " . $vendor_id, "theme" => "error"));
    //             return;
    //         }

    //         // ==================== MULAI TRANSAKSI DATABASE ====================
    //         $this->db->trans_start();

    //         $invoice_date = !empty($detail_data[0]['invoice_date']) ? $detail_data[0]['invoice_date'] : date('Y-m-d');
    //         $period_start = !empty($detail_data[0]['period_start']) ? $detail_data[0]['period_start'] : null;
    //         $period_end   = !empty($detail_data[0]['period_end']) ? $detail_data[0]['period_end'] : null;

    //         $period_ym = date('ym'); 
    //         $prefix = ($vendor_type == 'TF') ? 'INV-TF' : 'INV-SUB';

    //         $sql = $this->db->query("
    //             SELECT MAX(CAST(RIGHT(finishing_invoice_no,3) AS UNSIGNED)) AS nomor
    //             FROM finishing_invoices
    //             WHERE finishing_invoice_no LIKE '{$prefix}-{$period_ym}-%'
    //         ");
            
    //         $row = $sql->row();
    //         $next = ($row && $row->nomor) ? $row->nomor + 1 : 1;
    //         $finishing_invoice_no = sprintf('%s-%s-%03d', $prefix, $period_ym, $next);

    //         // --- 3. VALIDASI ITEM & KALKULASI DETAIL ---
    //         $total_amount = 0;
    //         $detail_insert = [];
            
    //         // Generate ID Master di awal agar bisa mengikat tabel Details dan dilempar ke crud->create
    //         $invoice_id = 'INV-' . time() . rand(10, 99); 

    //         foreach ($detail_data as $row_item) {
    //             // Validasi Item FG ke master
    //             $item_fg = $this->crud->read('item_fg', [], ["number" => $row_item['item_number']]); 
                
    //             // JIKA ADA 1 BARIS YANG GAGAL, BATALKAN SEMUA PROSES
    //             if (empty($item_fg)) {
    //                 $this->db->trans_rollback(); // Membatalkan seluruh transaksi jika ada yg sempat tereksekusi
    //                 echo json_encode(array("title" => "Error", "message" => "Proses Dibatalkan! Product Number " . $row_item['item_number'] . " tidak terdaftar di sistem.", "theme" => "error"));
    //                 return;
    //             }

    //             // Kalkulasi Sub Total
    //             $sub_total = $row_item['price_fg'] - $row_item['price_defect'];
    //             $total_amount += $sub_total;

    //             $detail_insert[] = array(
    //                 "id" => 'DTL-' . uniqid(),
    //                 "finishing_invoice_id" => $invoice_id, // Menggunakan ID yang sudah di-generate di atas
    //                 "item_fg_id" => $item_fg->id, 
    //                 "qty" => $row_item['qty'],
    //                 "qty_1" => $row_item['qty_1'],
    //                 "price" => $row_item['price'],
    //                 "price_fg" => $row_item['price_fg'],
    //                 "price_defect" => $row_item['price_defect'],
    //                 "sub_total" => $sub_total
    //             );
    //         }

    //         // --- 4. KALKULASI TOTAL HEADER ---
    //         if ($vendor_type == 'TF') {
    //             $biaya_fee = $total_amount * ($fee_db / 100); 
    //         } else {
    //             $biaya_fee = $fee_db; 
    //         }
    //         $grand_total = $total_amount + $biaya_fee;

    //         // --- 5. INSERT MASTER MENGGUNAKAN LIBRARY CRUD ---
    //         $header_insert = array(
    //             "id" => $invoice_id, // Kita paksa set ID agar crud->create tidak melakukan auto-id yang tidak bisa kita lacak
    //             "finishing_invoice_no" => $finishing_invoice_no,
    //             "finishing_invoice_date" => $invoice_date,
    //             "period_start" => $period_start,
    //             "period_end" => $period_end,
    //             "subcont" => $vendor_id, 
    //             "total" => $total_amount,
    //             "biaya_fee" => $biaya_fee,
    //             "grand_total" => $grand_total,
    //             "status" => 0,
    //             "deleted" => 0
    //         );
            
    //         // Eksekusi insert header lewat library bawaanmu
    //         // Kita tampung kembalian JSON-nya agar tidak langsung ter-echo ganda
    //         $crud_response = $this->crud->create('finishing_invoices', $header_insert); 

    //         // --- 6. INSERT DETAILS MENGGUNAKAN BATCH ---
    //         // Proses ini sangat cepat dan efisien meski berisi puluhan row
    //         $this->db->insert_batch('finishing_invoice_details', $detail_insert);

    //         // ==================== SELESAIKAN TRANSAKSI DATABASE ====================
    //         $this->db->trans_complete();

    //         // Pengecekan akhir: Apakah ada query yang gagal di level sistem/database?
    //         if ($this->db->trans_status() === FALSE) {
    //             echo json_encode(array("title" => "Error", "message" => "Gagal menyimpan data ke database. Silakan periksa kembali struktur tabel.", "theme" => "error"));
    //         } else {
    //             echo json_encode(array("title" => "Success", "message" => "Data Berhasil Disimpan!", "theme" => "success"));
    //         }
    //     }
    // }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $header_data = $this->input->post('header');
            $detail_data = $this->input->post('details'); 

            $vendor_id = $header_data['vendor_id'];

            $vendor_type = '';
            $fee_db = 0;

            $cek_tf = $this->crud->read('teaching_factory', [], ["id" => $vendor_id]);
            
            if (!empty($cek_tf)) {
                $vendor_type = 'TF';
                $vendor_id = $cek_tf->id;
                $fee_db = (float)$cek_tf->fee;
            } else {
                $cek_subcont = $this->crud->read('subconts', [], ["id" => $vendor_id]);
                if (!empty($cek_subcont)) {
                    $vendor_type = 'Koordinator';
                    $vendor_id = $cek_subcont->id;
                    $fee_db = (float)$cek_subcont->fee;
                }
            }

            if (empty($vendor_type)) {
                echo json_encode(array("title" => "Error", "message" => "Subcont atau Koordinator tidak ditemukan dengan ID: " . $vendor_id, "theme" => "error"));
                return;
            }

            // ==================== MULAI TRANSAKSI DATABASE ====================
            $this->db->trans_start();

            $invoice_date = !empty($detail_data[0]['invoice_date']) ? $detail_data[0]['invoice_date'] : date('Y-m-d');
            $period_start = !empty($detail_data[0]['period_start']) ? $detail_data[0]['period_start'] : null;
            $period_end   = !empty($detail_data[0]['period_end']) ? $detail_data[0]['period_end'] : null;

            $period_ym = date('ym'); 
            $prefix = ($vendor_type == 'TF') ? 'INV-TF' : 'INV-SUB';

            $sql = $this->db->query("
                SELECT MAX(CAST(RIGHT(finishing_invoice_no,3) AS UNSIGNED)) AS nomor
                FROM finishing_invoices
                WHERE finishing_invoice_no LIKE '{$prefix}-{$period_ym}-%'
            ");
            
            $row = $sql->row();
            $next = ($row && $row->nomor) ? $row->nomor + 1 : 1;
            $finishing_invoice_no = sprintf('%s-%s-%03d', $prefix, $period_ym, $next);

            // --- 3. VALIDASI ITEM & KALKULASI DETAIL ---
            $total_amount = 0;
            $detail_insert = [];
            
            $invoice_id = 'INV-' . time() . rand(10, 99); 

            foreach ($detail_data as $row_item) {
                $item_fg = $this->crud->read('item_fg', [], ["number" => $row_item['item_number']]); 
                
                if (empty($item_fg)) {
                    $this->db->trans_rollback(); 
                    echo json_encode(array("title" => "Error", "message" => "Proses Dibatalkan! Product Number " . $row_item['item_number'] . " tidak terdaftar di sistem.", "theme" => "error"));
                    return;
                }

                // PERHITUNGAN ULANG DI AGAR LEBIH AMAN
                $price = (float)$row_item['price'];
                $qty   = (float)$row_item['qty'];
                $qty_1 = (float)$row_item['qty_1'];

                $price_fg     = $price * $qty;
                $price_defect = $price * $qty_1;
                
                // Kalkulasi Sub Total
                $sub_total = $price_fg - $price_defect;
                $total_amount += $sub_total;

                $detail_insert[] = array(
                    "id"                   => 'DTL-' . uniqid(),
                    "finishing_invoice_id" => $invoice_id, 
                    "item_fg_id"           => $item_fg->id, 
                    "qty"                  => $qty,
                    "qty_1"                => $qty_1,
                    "price"                => $price,
                    "price_fg"             => $price_fg,
                    "price_defect"         => $price_defect,
                    "sub_total"            => $sub_total
                );
            }

            // --- 4. KALKULASI TOTAL HEADER ---
            if ($vendor_type == 'TF') {
                $biaya_fee = $total_amount * ($fee_db / 100); 
            } else {
                $biaya_fee = $fee_db; 
            }
            $grand_total = $total_amount + $biaya_fee;

            // --- 5. INSERT MASTER ---
            $header_insert = array(
                "id"                     => $invoice_id, 
                "finishing_invoice_no"   => $finishing_invoice_no,
                "finishing_invoice_date" => $invoice_date,
                "period_start"           => $period_start,
                "period_end"             => $period_end,
                "subcont"                => $vendor_id, 
                "total"                  => $total_amount,
                "biaya_fee"              => $biaya_fee,
                "grand_total"            => $grand_total,
                "status"                 => 0,
                "deleted"                => 0
            );
            
            $crud_response = $this->crud->create('finishing_invoices', $header_insert); 

            // --- 6. INSERT DETAILS BATCH ---
            $this->db->insert_batch('finishing_invoice_details', $detail_insert);

            // ==================== SELESAIKAN TRANSAKSI DATABASE ====================
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array("title" => "Error", "message" => "Gagal menyimpan data ke database. Silakan periksa kembali struktur tabel.", "theme" => "error"));
            } else {
                echo json_encode(array("title" => "Success", "message" => "Data Berhasil Disimpan!", "theme" => "success"));
            }
        }
    }

    public function print_invoice($id)
    {
        // 1. Ambil Data Header Invoice
        $header = $this->db->get_where('finishing_invoices', ['id' => $id])->row();
        if (empty($header)) {
            show_404();
        }

        // 2. Cek vendor (TF / Subcont)
        $vendor = $this->db->get_where('teaching_factory', ['id' => $header->subcont])->row();
        $is_tf = true;
        if (empty($vendor)) {
            $vendor = $this->db->get_where('subconts', ['id' => $header->subcont])->row();
            $is_tf = false;
        }

        // 3. Ambil Data Details (DENGAN GROUPING DAN SUM)
        $this->db->select('
            d.item_fg_id, 
            i.number as item_number, 
            i.name as item_name,
            d.price,
            SUM(d.qty) as qty,
            SUM(d.price_fg) as price_fg,
            SUM(d.qty_1) as qty_1,
            SUM(d.price_defect) as price_defect,
            SUM(d.sub_total) as sub_total
        ');
        $this->db->from('finishing_invoice_details d');
        $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
        $this->db->where('d.finishing_invoice_id', $id);
        $this->db->group_by(['d.item_fg_id', 'i.number', 'i.name', 'd.price']);
        $this->db->order_by('i.number', 'ASC');
        $details = $this->db->get()->result();

        $total_rows_count = count($details);

        if ($is_tf) {
            $text_fee = "Biaya fee 23 % Sekolah ( Perawatan gedung, Listrik, Pengawasan kualitas dan siswa, keamanan dan kebersihan )";
        } else {
            $text_fee = "Biaya Fee Koordinator ( Kontrol Kualitas dan Stok Inventory, BBM, Sewa Tempat, Mapping Produk, Training Anggota Finishing )";
        }

        $bank_name = !empty($vendor->bank_account_name) ? $vendor->bank_account_name : '';
        $bank_holder = !empty($vendor->bank_account_holder) ? $vendor->bank_account_holder : '';
        $bank_no = !empty($vendor->bank_account_no) ? $vendor->bank_account_no : '';
        $note_bank = "Note : " . trim("$bank_name a.n $bank_holder $bank_no");

        function format_money($amount) {
            $formatted = number_format(abs($amount), 0, ',', '.');
            if ($amount < 0) {
                return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
            }
            return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
        }

        // ========================================================================
        // 4. LOGIKA BARCODE & APPROVAL MAPPING
        // ========================================================================
        
        $approval_rule = $this->db->get_where('approvals', ['table_name' => 'finishing_invoices'])->row();
        $approvedLevel = (int)$header->approved; 
        $is_fully_approved = (empty($header->approved_to) && !empty($header->approved_by));

        $buildApproval = function($username, $is_approved) {
            if (empty($username)) {
                return ['name' => '', 'position' => '', 'barcode' => ''];
            }

            $user = $this->db->get_where('users', ['username' => $username])->row();
            
            $barcode_img = '';
            if ($is_approved && !empty($user)) {
                $this->createQrcode(md5($user->name), "assets/image/qrcode/");
                $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
                $barcode_img = '<img src="' . $img_url . '" style="max-height: 65px;">';
            }

            return [
                'name'     => !empty($user) ? $user->name : $username,
                'position' => !empty($user) ? $user->position : '',
                'barcode'  => $barcode_img
            ];
        };

        $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
        $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
        $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
        $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
        $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
        
        $col_create = $buildApproval($header->created_by, true);

        // ========================================================================
        // 5. RENDER HTML
        // ========================================================================
        $html = '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Print Invoice - ' . $header->finishing_invoice_no . '</title>
            <style>
                /* Menyetel orientasi cetak default ke Landscape */
                @page {
                    size: A4 landscape;
                    margin: 10mm;
                }
                
                body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; background-color: #fff; }
                .screen-instruction { text-align: center; margin-top: 150px; }
                .screen-instruction h2 { font-size: 18pt; font-weight: bold; margin: 0 0 10px 0; color: #000; }
                .screen-instruction p { font-size: 11pt; margin: 4px 0; color: #000; }
                .invoice-container { display: none; }
                
                @media print {
                    .screen-instruction { display: none !important; }
                    .invoice-container { display: block !important; }
                    body { padding: 0; }
                }
                
                .header-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px; }
                .sub-title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 5px; }
                .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 20px; }
                
                table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9pt; }
                table.print-table th { text-align: center; background-color: #f2f2f2; }
                
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .bold { font-weight: bold; }
                .nowrap { white-space: nowrap; } /* Mencegah teks turun ke bawah */
                
                .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
                .money-box .rp { text-align: left; }
                .money-box .val { text-align: right; flex-grow: 1; }
                
                .signature-section { margin-top: 25px; width: 100%; page-break-inside: avoid; }
                table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
                table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
            </style>
        </head>
        <body onload="window.print()">

            <div class="screen-instruction">
                <h2>Press CTRL + P for Print</h2>
                <p>Display pages for ' . $total_rows_count . ' rows</p>
                <p>Paper Size A4, Layout Landscape</p>
                <p>Margin Default, Scale 98</p>
            </div>

            <div class="invoice-container">
                <div class="header-title">INVOICE</div>
                <div class="sub-title">' . strtoupper(@$vendor->name) . '</div>
                <div class="period">
                    PERIODE : ' . date('d F Y', strtotime($header->period_start)) . ' - ' . date('d F Y', strtotime($header->period_end)) . '
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 35px;">NO</th>
                            <th rowspan="2">PART NUMBER</th>
                            <th rowspan="2">PART NAME</th>
                            <th rowspan="2" style="width: 90px;">PRICE</th>
                            <th colspan="2">FG</th>
                            <th colspan="2">DEFECT</th>
                            <th rowspan="2" style="width: 140px;">TOTAL PENDAPATAN</th>
                        </tr>
                        <tr>
                            <th style="width: 60px;">QTY</th>
                            <th style="width: 120px;">Rp.</th>
                            <th style="width: 60px;">QTY 1</th>
                            <th style="width: 120px;">Rp.</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        $tot_qty_fg = 0;
        $tot_rp_fg = 0;
        $tot_qty_def = 0;
        $tot_rp_def = 0;
        
        foreach ($details as $row) {
            $tot_qty_fg += $row->qty;
            $tot_rp_fg += $row->price_fg;
            $tot_qty_def += $row->qty_1;
            $tot_rp_def += $row->price_defect;
            
            $html .= '<tr>
                <td class="text-center">' . $no++ . '</td>
                <td class="nowrap">' . $row->item_number . '</td>
                <td class="nowrap">' . $row->item_name . '</td>
                <td>' . format_money($row->price) . '</td>
                <td class="text-right">' . number_format($row->qty, 0, ',', '.') . '</td>
                <td>' . format_money($row->price_fg) . '</td>
                <td class="text-center">' . ($row->qty_1 > 0 ? number_format($row->qty_1, 0, ',', '.') : '-') . '</td>
                <td>' . format_money($row->price_defect) . '</td>
                <td>' . format_money($row->sub_total) . '</td>
            </tr>';
        }

        $html .= '<tr class="bold">
                        <td colspan="4" class="text-center">TOTAL</td>
                        <td class="text-right">' . number_format($tot_qty_fg, 0, ',', '.') . '</td>
                        <td>' . format_money($tot_rp_fg) . '</td>
                        <td class="text-right">' . number_format($tot_qty_def, 0, ',', '.') . '</td>
                        <td>' . format_money($tot_rp_def) . '</td>
                        <td>' . format_money($header->total) . '</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="bold">' . $text_fee . '</td>
                        <td>' . format_money($header->biaya_fee) . '</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="bold">Total Invoice yg dibayarkan</td>
                        <td>' . format_money($header->grand_total) . '</td>
                    </tr>
                    </tbody>
                </table>

                <div style="margin-top: 10px; font-weight: bold; font-size: 9pt;">
                    ' . $note_bank . '
                </div>

                <!-- TABEL TANDA TANGAN DINAMIS DENGAN BARCODE -->
                <table style="width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid;">
                    <tr>
                        <td style="width: 35%;"></td>
                        <td style="width: 65%; text-align: center;">
                            <div style="margin-bottom: 8px; font-size: 9pt;">
                                Purwakarta, ' . date('d F Y', strtotime($header->finishing_invoice_date)) . '
                            </div>
                            <table class="sign-table">
                                <tr>
                                    <td colspan="2" style="width: 33.33%;">Disetujui</td>
                                    <td style="width: 16.66%;">Diketahui</td>
                                    <td colspan="2" style="width: 33.33%;">Diperiksa</td>
                                    <td style="width: 16.66%;">Dibuat</td>
                                </tr>
                                
                                <!-- Baris Gambar Barcode -->
                                <tr>
                                    <td style="height: 75px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
                                </tr>
                                
                                <!-- Baris Nama Personil -->
                                <tr>
                                    <td>' . $col_app5['name'] . '</td>
                                    <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
                                    <td>' . $col_app3['name'] . '</td>
                                    <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
                                    <td>' . $col_app1['name'] . '</td>
                                    <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
                                </tr>
                                
                                <!-- Baris Jabatan / Posisi -->
                                <tr>
                                    <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
                                    <td>' . ($col_app4['position'] ?: 'COO') . '</td>
                                    <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
                                    <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
                                    <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
                                    <td>' . ($col_create['position'] ?: 'Staff') . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>';

        echo $html;
    }

    public function print_recap()
    {
        $ids_param = $this->input->get('ids');

        if (empty($ids_param)) {
            show_error("Tidak ada data invoice yang dipilih untuk dicetak rekap.");
        }

        $id_array = explode(',', $ids_param);

        // Ambil data invoice berdasarkan ID yang dicentang saja
        $this->db->select('a.*, COALESCE(tf.name, sc.name) as vendor_name');
        $this->db->from('finishing_invoices a');
        $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
        $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.id', $id_array);
        $this->db->order_by('a.finishing_invoice_no', 'ASC');
        $all_invoices = $this->db->get()->result();

        if (empty($all_invoices)) {
            show_error("Data invoice yang dipilih tidak ditemukan di database.");
        }

        // VALIDASI 1: Cek apakah ada invoice yang approved_to nya masih ada isi (belum fully approved)
        foreach ($all_invoices as $inv) {
            if (!empty($inv->approved_to)) {
                echo "<script>
                        alert('Gagal! Invoice nomor [ " . $inv->finishing_invoice_no . " ] belum fully approved.');
                        window.close();
                    </script>";
                return;
            }
        }

        // VALIDASI 2: Pastikan Tanggal Invoice, Period Start, dan Period End sama
        $first_invoice_date = $all_invoices[0]->finishing_invoice_date;
        $first_period_start = $all_invoices[0]->period_start;
        $first_period_end   = $all_invoices[0]->period_end;

        foreach ($all_invoices as $inv) {
            if ($inv->finishing_invoice_date != $first_invoice_date || 
                $inv->period_start != $first_period_start || 
                $inv->period_end != $first_period_end) {
                echo "<script>
                        alert('Validasi Gagal! Tanggal Invoice, Period Start, dan Period End dari data yang dipilih harus sama.');
                        window.close();
                    </script>";
                return;
            }
        }

        // Pisahkan otomatis berdasarkan teks 'TF' atau 'SUB' pada nomor invoice
        $tf_invoices = [];
        $sub_invoices = [];

        foreach ($all_invoices as $inv) {
            if (strpos(strtoupper($inv->finishing_invoice_no), 'TF') !== false) {
                $tf_invoices[] = $inv;
            } else {
                $sub_invoices[] = $inv;
            }
        }

        $period_text = "PERIODE : " . date('d F Y', strtotime($first_period_start)) . " - " . date('d F Y', strtotime($first_period_end));
        $doc_date    = date('d F Y', strtotime($first_invoice_date)); // Mengambil tanggal dari finishing_invoice_date yang valid

        function format_money_recap($amount) {
            $formatted = number_format(abs($amount), 0, ',', '.');
            if ($amount < 0) {
                return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
            }
            return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
        }

        $html = '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Rekap Finishing Invoice</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 20px; background-color: #fff; }
                .screen-instruction { text-align: center; margin-top: 150px; }
                .invoice-container { display: none; }
                @media print {
                    .screen-instruction { display: none !important; }
                    .invoice-container { display: block !important; }
                    body { padding: 0; }
                }
                .header-title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 3px; }
                .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 15px; }
                table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9.5pt; }
                table.print-table th { text-align: center; background-color: #f2f2f2; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .bold { font-weight: bold; }
                .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
                .money-box .rp { text-align: left; }
                .money-box .val { text-align: right; flex-grow: 1; }
                table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
                table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
            </style>
        </head>
        <body onload="window.print()">

            <div class="screen-instruction">
                <h2>Press CTRL + P for Print</h2>
                <button onclick="window.print()" style="margin-top:15px; padding:8px 18px; cursor:pointer;">Print Rekap Terpilih</button>
            </div>

            <div class="invoice-container">';

        // ==========================================
        // TABEL 1: TEACHING FACTORY (TF)
        // ==========================================
        if (!empty($tf_invoices)) {
            $html .= '<div class="header-title">INVOICE TEACHING FACTORY</div>
                    <div class="period">' . strtoupper($period_text) . '</div>
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">NO</th>
                                <th>NAMA</th>
                                <th style="width: 150px;">INVOICE</th>
                                <th style="width: 150px;">FEE</th>
                                <th style="width: 160px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            $no = 1;
            $tf_total = 0; $tf_fee = 0; $tf_grand = 0;
            foreach ($tf_invoices as $row) {
                $tf_total += $row->total;
                $tf_fee += $row->biaya_fee;
                $tf_grand += $row->grand_total;

                $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . $row->vendor_name . '</td>
                    <td>' . format_money_recap($row->total) . '</td>
                    <td>' . format_money_recap($row->biaya_fee) . '</td>
                    <td>' . format_money_recap($row->grand_total) . '</td>
                </tr>';
            }
            $html .= '<tr class="bold">
                        <td colspan="2" class="text-center">Total</td>
                        <td>' . format_money_recap($tf_total) . '</td>
                        <td>' . format_money_recap($tf_fee) . '</td>
                        <td>' . format_money_recap($tf_grand) . '</td>
                    </tr>
                    <tr class="bold">
                        <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
                        <td>' . format_money_recap($tf_grand) . '</td>
                    </tr>
                    </tbody></table><br>';
        }

        // ==========================================
        // TABEL 2: SUBCONT / KOORDINATOR (SUB)
        // ==========================================
        if (!empty($sub_invoices)) {
            $html .= '<div class="header-title">INVOICE SUBCONT / KOORDINATOR</div>
                    <div class="period">' . strtoupper($period_text) . '</div>
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">NO</th>
                                <th>NAMA</th>
                                <th style="width: 150px;">INVOICE</th>
                                <th style="width: 150px;">FEE</th>
                                <th style="width: 160px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            $no = 1;
            $sub_total = 0; $sub_fee = 0; $sub_grand = 0;
            foreach ($sub_invoices as $row) {
                $sub_total += $row->total;
                $sub_fee += $row->biaya_fee;
                $sub_grand += $row->grand_total;

                $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . $row->vendor_name . '</td>
                    <td>' . format_money_recap($row->total) . '</td>
                    <td>' . format_money_recap($row->biaya_fee) . '</td>
                    <td>' . format_money_recap($row->grand_total) . '</td>
                </tr>';
            }
            $html .= '<tr class="bold">
                        <td colspan="2" class="text-center">Total</td>
                        <td>' . format_money_recap($sub_total) . '</td>
                        <td>' . format_money_recap($sub_fee) . '</td>
                        <td>' . format_money_recap($sub_grand) . '</td>
                    </tr>
                    <tr class="bold">
                        <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
                        <td>' . format_money_recap($sub_grand) . '</td>
                    </tr>
                    </tbody></table>';
        }

        // ==========================================
        // BAGIAN TANDA TANGAN (Menggunakan $doc_date)
        // ==========================================
        $html .= '<table style="width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid;">
                <tr>
                    <td style="text-align: center;">
                        <div style="margin-bottom: 8px; font-size: 10pt; font-weight: bold;">
                            Purwakarta, ' . $doc_date . '
                        </div>
                        <table class="sign-table">
                            <tr>
                                <td colspan="2" style="width: 33.33%;">Disetujui</td>
                                <td style="width: 16.66%;">Diketahui</td>
                                <td colspan="2" style="width: 33.33%;">Diperiksa</td>
                                <td style="width: 16.66%;">Dibuat</td>
                            </tr>
                            <tr>
                                <td style="height: 60px; width: 16.66%;"></td>
                                <td style="width: 16.66%;"></td>
                                <td style="width: 16.66%;"></td>
                                <td style="width: 16.66%;"></td>
                                <td style="width: 16.66%;"></td>
                                <td style="width: 16.66%;"></td>
                            </tr>
                            <tr>
                                <td>Kinenta Harsono</td>
                                <td style="font-size: 7.5pt;">Babu Rajendra</td>
                                <td>Maya Evilia</td>
                                <td style="font-size: 7.5pt;">Achmad Goesly</td>
                                <td>Rohman</td>
                                <td style="font-size: 7.5pt;">Muthiatur R</td>
                            </tr>
                            <tr>
                                <td>BOD</td>
                                <td>COO</td>
                                <td>Leader FAT</td>
                                <td>Plant Manager</td>
                                <td>Leader</td>
                                <td>Staff</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            </div>
        </body>
        </html>';

        echo $html;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=finishing_invoices_$format.xls");
        }
        
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_invoice_no = $this->input->get('filter_invoice_no');
        $filter_subcont = $this->input->get('filter_subcont');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('
            a.*, 
            COALESCE(tf.name, sc.name) as subcont_name, 
            i.number as part_no, 
            i.name as part_name, 
            d.price, 
            SUM(d.qty) as qty_fg, 
            SUM(d.price_fg) as price_fg, 
            SUM(d.qty_1) as qty_defect, 
            SUM(d.price_defect) as price_defect, 
            SUM(d.sub_total) as total_pendapatan
        ');
        $this->db->from('finishing_invoices a');
        
        $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
        $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
        $this->db->join('users u', 'u.username = a.created_by', 'left');
        $this->db->join('finishing_invoice_details d', 'a.id = d.finishing_invoice_id', 'left');
        $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');

        // Filter User Department
        if (!empty($user->department_id) && !in_array($user->department, $this->crud->getIgnoreDept())) {
            $this->db->where('u.department_id', $user->department_id);
        }

        $this->db->where_in('a.deleted', [0, 2]);

        // Filter Pencarian
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.finishing_invoice_date >=', $filter_from);
            $this->db->where('a.finishing_invoice_date <=', $filter_to);
        }
        if ($filter_invoice_no != "") {
            $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
        }
        if ($filter_subcont != "") {
            $this->db->where('a.subcont', $filter_subcont);
        }

        $this->db->group_by(['a.id', 'd.item_fg_id', 'i.number', 'i.name', 'd.price']);

        $sort = $this->input->post('sort');
        $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
        if ($sort == "finishing_invoice_no") {
            $this->db->order_by('a.finishing_invoice_no', $order);
        } else {
            $this->db->order_by('a.created_date', 'DESC');
        }

        $records = $this->db->get()->result_array();
        
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
                                <img src="' .  @$config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . @$config->name . '</b><br>
                                <small>FINISHING INVOICES</small>
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
                <th>Finishing Invoice No</th>
                <th>Finishing Invoice Date</th>
                <th>Period Start</th>
                <th>Period End</th>
                <th>Subcont</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Price</th>
                <th>Qty FG</th>
                <th>Price FG</th>
                <th>Qty Defect</th>
                <th>Price Defect</th>
                <th>Total Pendapatan</th>
            </tr>';
            
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['finishing_invoice_no'] . '</td>
                        <td>' . $data['finishing_invoice_date'] . '</td>
                        <td>' . $data['period_start'] . '</td>
                        <td>' . $data['period_end'] . '</td>
                        <td>' . $data['subcont_name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                        <td style="text-align:right;">' . (float)$data['price'] . '</td>
                        <td style="text-align:center;">' . (float)$data['qty_fg'] . '</td>
                        <td style="text-align:right;">' . (float)$data['price_fg'] . '</td>
                        <td style="text-align:center;">' . (float)$data['qty_defect'] . '</td>
                        <td style="text-align:right;">' . (float)$data['price_defect'] . '</td>
                        <td style="text-align:right;">' . (float)$data['total_pendapatan'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        
        echo $html;
    }

    // public function print($option = "")// PLUS TOTAL
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=finishing_invoices_$format.xls");
    //     }
        
    //     $filter_from = $this->input->get('filter_from');
    //     $filter_to   = $this->input->get('filter_to');
    //     $filter_invoice_no = $this->input->get('filter_invoice_no');
    //     $filter_subcont = $this->input->get('filter_subcont');

    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     // Ambil data user yang sedang login untuk keperluan filter departemen
    //     $user = $this->crud->currentUserDept();

    //     $this->db->select('
    //         a.*, 
    //         COALESCE(tf.name, sc.name) as subcont_name, 
    //         i.number as part_no, 
    //         i.name as part_name, 
    //         d.price, 
    //         SUM(d.qty) as qty_fg, 
    //         SUM(d.price_fg) as price_fg, 
    //         SUM(d.qty_1) as qty_defect, 
    //         SUM(d.price_defect) as price_defect, 
    //         SUM(d.sub_total) as total_pendapatan
    //     ');
    //     $this->db->from('finishing_invoices a');
        
    //     // Hapus deklarasi ganda 'users u' di FROM, gabungkan semua lewat JOIN
    //     $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
    //     $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
    //     $this->db->join('users u', 'u.username = a.created_by', 'left');
    //     $this->db->join('finishing_invoice_details d', 'a.id = d.finishing_invoice_id', 'left');
    //     $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
    //     $this->db->join('departments dept', 'dept.id = u.department_id', 'left');
    //     $this->db->join('divisions div', 'div.id = dept.plant_id', 'left');

    //     // Filter User Department
    //     if (!empty($user->department_id) && !in_array($user->department, $this->crud->getIgnoreDept())) {
    //         $this->db->where('u.department_id', $user->department_id);
    //     }

    //     $this->db->where_in('a.deleted', [0, 2]);

    //     // Filter Pencarian
    //     if ($filter_from != "" or $filter_to != "") {
    //         $this->db->where('a.finishing_invoice_date >=', $filter_from);
    //         $this->db->where('a.finishing_invoice_date <=', $filter_to);
    //     }
    //     if ($filter_invoice_no != "") {
    //         $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
    //     }
    //     if ($filter_subcont != "") {
    //         $this->db->where('a.subcont', $filter_subcont);
    //     }

    //     $this->db->group_by(['a.id', 'd.item_fg_id', 'i.number', 'i.name', 'd.price']);

    //     $sort = $this->input->post('sort');
    //     $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
    //     if ($sort == "finishing_invoice_no") {
    //         $this->db->order_by('a.finishing_invoice_no', $order);
    //     } else {
    //         $this->db->order_by('a.created_date', 'DESC');
    //     }

    //     $records = $this->db->get()->result_array();

    //     // Pengelompokan data berdasarkan nomor invoice
    //     $grouped_invoices = [];
    //     foreach ($records as $row) {
    //         $inv_no = $row['finishing_invoice_no'];
    //         if (!isset($grouped_invoices[$inv_no])) {
    //             $grouped_invoices[$inv_no] = [
    //                 'header' => $row,
    //                 'items' => []
    //             ];
    //         }
    //         $grouped_invoices[$inv_no]['items'][] = $row;
    //     }

    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 4px;}#customers tr:nth-child(even){background-color: #f9f9f9;}#customers tr:hover {background-color: #f1f1f1;}#customers th {padding-top: 6px;padding-bottom: 6px;text-align: center;color: black; background-color: #e2e2e2;}</style><body>
    //         <center>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
    //                             <img src="' .  @$config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . @$config->name . '</b><br>
    //                             <small>FINISHING INVOICES</small>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 Print Date ' . date("d M Y H:i:s") . ' <br>
    //                 Print By ' . $this->session->username . '  
    //             </div>
    //         </center>
    //         <br><br><br>
            
    //         <table id="customers" border="1">
    //         <thead>
    //             <tr>
    //                 <th width="20">No</th>
    //                 <th>Finishing Invoice No</th>
    //                 <th>Finishing Invoice Date</th>
    //                 <th>Period Start</th>
    //                 <th>Period End</th>
    //                 <th>Subcont</th>
    //                 <th>Part No</th>
    //                 <th>Part Name</th>
    //                 <th>Price</th>
    //                 <th>Qty FG</th>
    //                 <th>Price FG</th>
    //                 <th>Qty Defect</th>
    //                 <th>Price Defect</th>
    //                 <th>Total Pendapatan</th>
    //             </tr>
    //         </thead>
    //         <tbody>';
            
    //     $no = 1;
    //     foreach ($grouped_invoices as $inv_no => $data) {
    //         $header = $data['header'];
            
    //         foreach ($data['items'] as $item) {
    //             $html .= '<tr>
    //                         <td style="text-align:center">' . $no++ . '</td>
    //                         <td>' . $item['finishing_invoice_no'] . '</td>
    //                         <td>' . $item['finishing_invoice_date'] . '</td>
    //                         <td>' . $item['period_start'] . '</td>
    //                         <td>' . $item['period_end'] . '</td>
    //                         <td>' . $item['subcont_name'] . '</td>
    //                         <td style="mso-number-format:\@;">' . $item['part_no'] . '</td>
    //                         <td style="mso-number-format:\@;">' . $item['part_name'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['price'] . '</td>
    //                         <td style="text-align:center;">' . (float)$item['qty_fg'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['price_fg'] . '</td>
    //                         <td style="text-align:center;">' . (float)$item['qty_defect'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['price_defect'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['total_pendapatan'] . '</td>
    //                     </tr>';
    //         }
            
    //         $html .= '<tr style="background-color: #fffbdd; font-weight: bold;">
    //                     <td colspan="13" style="text-align:right;">TOTAL INVOICE (' . $inv_no . ')</td>
    //                     <td style="text-align:right;">' . (float)$header['total'] . '</td>
    //                   </tr>
    //                   <tr style="background-color: #fffbdd; font-weight: bold;">
    //                     <td colspan="13" style="text-align:right;">BIAYA FEE</td>
    //                     <td style="text-align:right;">' . (float)$header['biaya_fee'] . '</td>
    //                   </tr>
    //                   <tr style="background-color: #e5f2cf; font-weight: bold;">
    //                     <td colspan="13" style="text-align:right;">GRAND TOTAL (' . $inv_no . ')</td>
    //                     <td style="text-align:right;">' . (float)$header['grand_total'] . '</td>
    //                   </tr>';
    //     }
        
    //     $html .= '</tbody></table></body></html>';
        
    //     echo $html;
    // }
}
