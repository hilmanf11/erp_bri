<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Shipping_orders extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('checksheet_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/shipping_orders');
        } else {
            redirect('error_access');
        }
    }

    public function getDeliveryOrders()
    {
        if ($this->input->get()) {
            $input = $this->input->get('delivery_order_no');
            $part1 = substr($input, 0, 4);
            $part3 = substr($input, -4, 2);
            $part4 = substr($input, -2);
            $part2 = substr($input, 4, -4);
            $delivery_order_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;


            $this->db->select('a.delivery_order_no, a.delivery_order_date, a.uom, a.sales_order_no, a.trans_type, a.remarks, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, SUM(a.qty_del) as delivery, a.created_by, a.created_date, COALESCE(e.shipping, 0) as shipping');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join("(SELECT 
                                a.delivery_order_no, 
                                a.item_fg_id, 
                                SUM(a.qty) AS shipping
                            FROM shipping_orders a
                            WHERE a.delivery_order_no = '$delivery_order_no'
                            GROUP BY a.delivery_order_no, a.item_fg_id
                            ) e", 'a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id', 'left');

            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->group_by('b.number');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $checksheet_label = $this->input->post('checksheet_label');
            $input = $this->input->post('delivery_order_no');
            $part1 = substr($input, 0, 4);
            $part3 = substr($input, -4, 2);
            $part4 = substr($input, -2);
            $part2 = substr($input, 4, -4);
            $delivery_order_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;

            // Cari di tabel fg_scan_in_label
            $this->db->select("qty, serial_label, scan_date, scan_by, '0' as delivery, item_fg_id");
            $this->db->from('fg_scan_in_label');
            $this->db->where('serial_label', $checksheet_label);
            $this->db->where('status', '0');
            $this->db->group_by('serial_label');
            $result1 = $this->db->get()->result_array();

            if (empty($result1)) {
                // Jika tidak ditemukan di fg_scan_in_label, cari di new_barcode_fg_detail
                $this->db->select("a.qty_packing as qty, a.serial_label, a.created_date as scan_date, a.created_by as scan_by, '0' as delivery, a.item_fg_id");
                $this->db->from('new_barcode_fg_detail a');
                $this->db->join('new_barcode_fg b', 'a.request_no = b.request_no', 'left');
                $this->db->where('a.serial_label', $checksheet_label);
                $this->db->where('a.status', '0');
                $this->db->group_by('a.serial_label');
                $result1 = $this->db->get()->result_array();
            }

            //Mapping Data
            $result['total'] = count($result1);
            $result = array_merge($result, ['rows' => $result1]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $post['transaction_type'] = 'ISFG-001';

                $input = $post['delivery_order_no'];
                $part1 = substr($input, 0, 4);
                $part3 = substr($input, -4, 2);
                $part4 = substr($input, -2);
                $part2 = substr($input, 4, -4);
                $delivery_order_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;
                $item_fg_id = isset($post['item_fg_id']) ? $post['item_fg_id'] : null;

                if (!$item_fg_id) {
                    echo json_encode(array("title" => "Error", "message" => "Item FG ID is missing", "theme" => "error"));
                    return;
                }

                $do_no = $this->crud->read("delivery_orders", [], ["delivery_order_no" => $delivery_order_no, "item_fg_id" => $post['item_fg_id']]);

                if (!$do_no) {
                    echo json_encode(array("title" => "Error", "message" => "Delivery order not found", "theme" => "error"));
                    return;
                }

                $totalShipping = $this->crud->query("SELECT SUM(qty) as qty FROM shipping_orders WHERE delivery_order_no = '$delivery_order_no' and item_fg_id = '$item_fg_id'");

                if ($totalShipping[0]->qty + $post['qty'] > $do_no->qty_del) {
                    echo json_encode(array("title" => "Exceeds Delivery", "message" => "Qty Shipping exceeds Delivery Qty", "theme" => "error"));
                    return;
                }

                // Cek apakah label ada di fg_scan_in_label
                $this->db->select("a.qty, a.serial_label, a.item_fg_id, b.delivery_order_no, b.qty_del as delivery, b.sales_order_no, b.customer_order_no");
                $this->db->from('fg_scan_in_label a');
                $this->db->join('delivery_orders b', 'a.item_fg_id = b.item_fg_id');
                $this->db->where('a.serial_label', $post['checksheet_label']);
                $this->db->where('b.delivery_order_no', $delivery_order_no);
                $this->db->where('a.status', '0');
                $label_items = $this->db->get()->result_array();

                // Jika tidak ada di fg_scan_in_label, cek di new_barcode_fg_detail
                if (empty($label_items)) {
                    $this->db->select("a.qty_packing as qty, a.serial_label, a.item_fg_id, b.delivery_order_no, b.qty_del as delivery, b.sales_order_no, b.customer_order_no");
                    $this->db->from('new_barcode_fg_detail a');
                    $this->db->join('delivery_orders b', 'a.item_fg_id = b.item_fg_id');
                    $this->db->where('a.serial_label', $post['checksheet_label']);
                    $this->db->where('b.delivery_order_no', $delivery_order_no);
                    $this->db->where('a.status', '0');
                    $label_items = $this->db->get()->result_array();
                }

                if (empty($label_items)) {
                    echo json_encode(array("title" => "Not Match", "message" => "Label does not match the list item", "theme" => "error"));
                    return;
                }

                $shipping_orders = $this->crud->read("shipping_orders", [], ["delivery_order_no" => $delivery_order_no, "serial_label" => $post['checksheet_label']]);

                if (!$shipping_orders) {
                    // Siapkan data untuk disimpan
                    foreach ($label_items as $item) {
                        if ($item['qty'] > $item['delivery']) {
                            echo json_encode(array("title" => "Exceeds Delivery", "message" => "Label qty exceeds delivery qty", "theme" => "error"));
                            return;
                        }

                        $data_to_insert = [
                            'delivery_order_no' => $delivery_order_no,
                            'sales_order_no' => $item['sales_order_no'],
                            'customer_order_no' => $item['customer_order_no'], 
                            'serial_label' => $item['serial_label'],
                            'item_fg_id' => $item['item_fg_id'],
                            'delivery' => $item['delivery'],
                            'qty' => $item['qty'],
                            'status' => 0
                        ];
                        $this->crud->create('shipping_orders', $data_to_insert);
                        // Tambahkan update kolom delivery pada tabel sales_orders
                        // Ambil data sales_orders terkait
                        $sales_order = $this->crud->read('sales_orders', [], [
                            'sales_order_no' => $item['sales_order_no'],
                            'item_fg_id' => $item['item_fg_id']
                        ]);
                        if ($sales_order) {
                            // Hitung total qty shipping untuk sales_order_no dan item_fg_id
                            $total_shipping = $this->crud->query("SELECT SUM(qty) as total FROM shipping_orders WHERE sales_order_no = '".$item['sales_order_no']."' AND item_fg_id = '".$item['item_fg_id']."'");
                            $new_delivery = isset($total_shipping[0]->total) ? $total_shipping[0]->total : 0;
                            $new_outstanding = $sales_order->qty - $new_delivery;
                            $this->crud->update('sales_orders', [
                                'sales_order_no' => $item['sales_order_no'],
                                'item_fg_id' => $item['item_fg_id']
                            ], [
                                'delivery' => $new_delivery,
                                'outstanding' => $new_outstanding
                            ]);
                        }
                    }

                    // Update status di fg_scan_in_label atau new_barcode_fg_detail
                    $this->db->where('serial_label', $post['checksheet_label']);
                    $this->db->where('status', '0');
                    $this->db->update('fg_scan_in_label', ['status' => 1]);

                    if ($this->db->affected_rows() == 0) {
                        $this->db->where('serial_label', $post['checksheet_label']);
                        $this->db->where('status', '0');
                        $this->db->update('new_barcode_fg_detail', ['status' => 1]);
                    }

                    echo json_encode(array("theme" => "success", "message" => "Data berhasil disimpan", "title" => "Success"));
                } else {
                    echo json_encode(array("title" => "Available", "message" => "Data Shipping Orders has been Scanning", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createDN()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            
            $input = $post['delivery_note_no'];
            $part1 = substr($input, 0, 4);
            $part3 = substr($input, -4, 2);
            $part4 = substr($input, -2);
            $part2 = substr($input, 4, -4);
            $delivery_note_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;
            $delivery_order_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;

            $delivery_orders = $this->crud->query("SELECT a.*, b.qty_shipping, d.plant as division, e.id as address_id
            FROM delivery_orders a
            JOIN (
                SELECT delivery_order_no, item_fg_id, SUM(qty) AS qty_shipping
                FROM shipping_orders
                GROUP BY delivery_order_no, item_fg_id
            ) b ON a.delivery_order_no = b.delivery_order_no AND a.item_fg_id = b.item_fg_id
            JOIN customers c ON a.customer_id = c.id
            JOIN sales_orders d ON c.id = d.customer_id
            JOIN customer_address e ON d.customer_address_id = e.id
            WHERE a.delivery_order_no = '$delivery_order_no'
            GROUP BY a.delivery_order_no, a.item_fg_id");

            // Cek apakah delivery_note_no sudah ada
            $existing_note = $this->crud->read('delivery_notes', [], ['delivery_note_no' => $delivery_note_no]);
            if ($existing_note) {
                echo json_encode(array("theme" => "error", "message" => "Delivery Note has been created", "title" => "Error"));
                return; // Keluar dari fungsi jika sudah ada
            }

            foreach ($delivery_orders as $delivery_order) {

                $status_delivery = 1;

                if ($delivery_order->actual_delivery_date == $delivery_order->delivery_date) {
                    $status_delivery = 0; // Tepat waktu
                } else if ($delivery_order->actual_delivery_date > $delivery_order->delivery_date) {
                    $status_delivery = 1; // Terlambat
                } else {
                    $status_delivery = 2; // Lebih awal
                }

                $data_to_insert = [
                    'created_by' => $this->session->username,
                    'created_date' => date('Y-m-d H:i:s'),
                    'customer_id' => $delivery_order->customer_id,
                    'item_fg_id' => $delivery_order->item_fg_id,
                    'sales_order_no' => $delivery_order->sales_order_no,
                    'customer_order_no' => $delivery_order->customer_order_no,
                    'delivery_order_no' => $delivery_order->delivery_order_no,
                    'delivery_note_no' => $delivery_note_no,
                    'delivery_note_date' => $delivery_order->actual_delivery_date,
                    'uom' => $delivery_order->uom,
                    'qty' => $delivery_order->qty_shipping,
                    'division' => $delivery_order->division,
                    'address_id' => $delivery_order->address_id,
                    'trans_type' => $delivery_order->trans_type,
                    'status_delivery' => $status_delivery, // Atur status pengiriman
                    'status' => 0,
                ];

                // Ambil data sales_order terkait
                $total_sales_order_qty = $this->db->select_sum('qty')
                    ->from('sales_orders')
                    ->where('sales_order_no', $delivery_order->sales_order_no)
                    ->where('customer_id', $delivery_order->customer_id)
                    ->get()
                    ->row()
                    ->qty ?? 0;

                if ($total_sales_order_qty) {
                    // Total qty_del untuk semua DO dengan sales_order_no yang sama
                    $total_qty_do = $this->db->select_sum('qty_del')
                        ->from('delivery_orders')
                        ->where('sales_order_no', $delivery_order->sales_order_no)
                        ->where('customer_id', $delivery_order->customer_id)
                        ->get()
                        ->row()
                        ->qty_del ?? 0;

                    $status = 0;

                    if($total_qty_do == $total_sales_order_qty) {
                        $status = 1;
                    }else if($total_qty_do > 0 && $total_qty_do < $total_sales_order_qty){
                        $status = 2;
                    }else{
                        $status = 0;
                    }

                    $this->crud->update('sales_orders', [
                        'sales_order_no' => $delivery_order->sales_order_no,
                        'customer_id'    => $delivery_order->customer_id
                    ], ['status' => $status]);
                }

                // Simpan data baru ke database
                $this->crud->create('delivery_notes', $data_to_insert);
                $this->crud->update('delivery_orders', ['delivery_order_no' => $delivery_order_no], ['status' => 1]);
            }

            echo json_encode(array("theme" => "success", "message" => "Delivery Note has been created", "title" => "Success"));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getNewDeliveryNoteNo()
    {
        $delivery_order_no = $this->input->get('delivery_order_no');
        if (!$delivery_order_no) {
            show_error("Delivery Order No is required");
            return;
        }

        $input = base64_decode($delivery_order_no);
        $part1 = substr($input, 0, 4);
        $part3 = substr($input, -4, 2);
        $part4 = substr($input, -2);
        $part2 = substr($input, 4, -4);
        $delivery_order_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;
        $delivery_order = $this->crud->read('delivery_orders', [], ['delivery_order_no' => $delivery_order_no]);
        $customer = $this->crud->read('customers', [], ['id' => $delivery_order->customer_id]);

        $numberCust = $customer->number;
        $divisions  = "DNMANUFACTUR";
        $datenow    = date("my");
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 3, 4) as kode FROM delivery_notes WHERE `delivery_note_no` like '%$dn_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = @$rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo json_encode(['delivery_note_no' => $divisions. "-" . $autoID . "-" . $numberCust . "-" . $datenow]);
    }
}