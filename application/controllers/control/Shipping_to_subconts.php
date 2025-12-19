<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Shipping_to_subconts extends CI_Controller
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
        $this->form_validation->set_rules('workorder_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/shipping_to_subconts');
        } else {
            redirect('error_access');
        }
    }

    // public function getShippingSubconts()
    // {
    //     if ($this->input->get()) {

    //         $this->db->select('b.*');
    //         $this->db->from("(SELECT 
    //                             scan_id,
    //                             item_fg_id,
    //                             SUM(qty) AS shipping
    //                         FROM shipping_to_subconts
    //                         WHERE type_status = 'scanning' AND status = 0
    //                         GROUP BY scan_id, item_fg_id
    //                         ) b");

    //         $records = $this->db->get()->result_array();

    //         $result['total'] = count($records);
    //         $result['rows']  = $records;

    //         echo json_encode($result);
    //     }
    // }

    public function getShippingSubconts()
    {
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
        $this->db->from("(SELECT 
                            scan_id,
                            item_fg_id,
                            SUM(qty) AS shipping,
                            MIN(workorder) as workorder,
                            MAX(created_date) AS last_created_date
                        FROM shipping_to_subconts
                        WHERE type_status = 'scanning' AND status = 0
                        GROUP BY scan_id, item_fg_id, workorder
                        ) a");
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->order_by('a.last_created_date', 'DESC');

        $records = $this->db->get()->result_array();

        $result['total'] = count($records);
        $result['rows']  = $records;

        echo json_encode($result);
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $workorder_label = $this->input->post('workorder_label');

            $label = $this->db->get_where('output_production_press_detail', [
                'workorder_label' => $workorder_label
            ])->row_array();

            if (!$label) {
                echo json_encode([
                    'title' => 'Not Found',
                    'message' => 'Label not found!'
                ]);
                return;
            }

            if ($label['status'] == 1) {
                echo json_encode([
                    'title' => 'Scanned',
                    'message' => 'Label has already been scanned',
                    'data' => $label
                ]);
                return;
            }

            $this->db->select("item_fg_id, workorder, workorder_label, qty_packing");
            $this->db->from('output_production_press_detail');
            $this->db->where('workorder_label', $workorder_label);
            $this->db->where('status', '0');
            // $this->db->where('item_fg_id !=', 'FGRPNA-0207');
            // $this->db->group_by('workorder_label');
            $result1 = $this->db->get()->result_array();

            if(empty($result1)) {
                $this->db->select("a.item_fg_id, a.workorder, a.workorder_label, a.ok_punch as qty_packing");
                $this->db->from('internal_process a');
                $this->db->join('output_production_press_detail b', 'a.item_fg_id = b.item_fg_id AND a.workorder = b.workorder and a.workorder_label = b.workorder_label');
                $this->db->where('a.workorder_label', $workorder_label);
                $this->db->where('b.status', '2');
                $this->db->where('a.item_fg_id', 'FGRPNA-0207');
                // $this->db->group_by('a.workorder_label');
                $result1 = $this->db->get()->result_array();
            }

            // $result['total'] = count($result1);
            // $result = array_merge($result, ['rows' => $result1]);
            // echo json_encode($result);

            echo json_encode([
                'title' => 'Success',
                'total'  => count($result1),
                'rows'   => $result1
            ]);
        }
    }

    // public function createv1()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post = $this->input->post();
    //             $post['transaction_type'] = 'ISFG-001';

    //             $input = $post['delivery_order_no'];
    //             $part1 = substr($input, 0, 4);
    //             $part3 = substr($input, -4, 2);
    //             $part4 = substr($input, -2);
    //             $part2 = substr($input, 4, -4);
    //             $delivery_order_no = $part1 . '/' . $part2 . '/' . $part3 . '/' . $part4;
    //             $item_fg_id = isset($post['item_fg_id']) ? $post['item_fg_id'] : null;

    //             if (!$item_fg_id) {
    //                 echo json_encode(array("title" => "Error", "message" => "Item FG ID is missing", "theme" => "error"));
    //                 return;
    //             }

    //             $do_no = $this->crud->read("delivery_orders", [], ["delivery_order_no" => $delivery_order_no, "item_fg_id" => $post['item_fg_id']]);

    //             if (!$do_no) {
    //                 echo json_encode(array("title" => "Error", "message" => "Delivery order not found", "theme" => "error"));
    //                 return;
    //             }

    //             $totalShipping = $this->crud->query("SELECT SUM(qty) as qty FROM shipping_to_subconts WHERE delivery_order_no = '$delivery_order_no' and item_fg_id = '$item_fg_id'");

    //             if ($totalShipping[0]->qty + $post['qty'] > $do_no->qty_del) {
    //                 echo json_encode(array("title" => "Exceeds Delivery", "message" => "Qty Shipping exceeds Delivery Qty", "theme" => "error"));
    //                 return;
    //             }

    //             // Cek apakah label ada di fg_scan_in_label
    //             $this->db->select("a.qty, a.serial_label, a.item_fg_id, b.delivery_order_no, b.qty_del as delivery, b.sales_order_no, b.customer_order_no");
    //             $this->db->from('fg_scan_in_label a');
    //             $this->db->join('delivery_orders b', 'a.item_fg_id = b.item_fg_id');
    //             $this->db->where('a.serial_label', $post['checksheet_label']);
    //             $this->db->where('b.delivery_order_no', $delivery_order_no);
    //             $this->db->where('a.status', '0');
    //             $label_items = $this->db->get()->result_array();

    //             // Jika tidak ada di fg_scan_in_label, cek di new_barcode_fg_detail
    //             if (empty($label_items)) {
    //                 $this->db->select("a.qty_packing as qty, a.serial_label, a.item_fg_id, b.delivery_order_no, b.qty_del as delivery, b.sales_order_no, b.customer_order_no");
    //                 $this->db->from('new_barcode_fg_detail a');
    //                 $this->db->join('delivery_orders b', 'a.item_fg_id = b.item_fg_id');
    //                 $this->db->where('a.serial_label', $post['checksheet_label']);
    //                 $this->db->where('b.delivery_order_no', $delivery_order_no);
    //                 $this->db->where('a.status', '0');
    //                 $label_items = $this->db->get()->result_array();
    //             }

    //             if (empty($label_items)) {
    //                 echo json_encode(array("title" => "Not Match", "message" => "Label does not match the list item", "theme" => "error"));
    //                 return;
    //             }

    //             $shipping_to_subconts = $this->crud->read("shipping_to_subconts", [], ["delivery_order_no" => $delivery_order_no, "serial_label" => $post['checksheet_label']]);

    //             if (!$shipping_to_subconts) {
    //                 // Siapkan data untuk disimpan
    //                 foreach ($label_items as $item) {
    //                     if ($item['qty'] > $item['delivery']) {
    //                         echo json_encode(array("title" => "Exceeds Delivery", "message" => "Label qty exceeds delivery qty", "theme" => "error"));
    //                         return;
    //                     }

    //                     $data_to_insert = [
    //                         'delivery_order_no' => $delivery_order_no,
    //                         'sales_order_no' => $item['sales_order_no'],
    //                         'customer_order_no' => $item['customer_order_no'], 
    //                         'serial_label' => $item['serial_label'],
    //                         'item_fg_id' => $item['item_fg_id'],
    //                         'delivery' => $item['delivery'],
    //                         'qty' => $item['qty'],
    //                         'status' => 0
    //                     ];
    //                     $this->crud->create('shipping_to_subconts', $data_to_insert);
    //                     // Tambahkan update kolom delivery pada tabel sales_orders
    //                     // Ambil data sales_orders terkait
    //                     $sales_order = $this->crud->read('sales_orders', [], [
    //                         'sales_order_no' => $item['sales_order_no'],
    //                         'item_fg_id' => $item['item_fg_id']
    //                     ]);
    //                     if ($sales_order) {
    //                         // Hitung total qty shipping untuk sales_order_no dan item_fg_id
    //                         $total_shipping = $this->crud->query("SELECT SUM(qty) as total FROM shipping_to_subconts WHERE sales_order_no = '".$item['sales_order_no']."' AND item_fg_id = '".$item['item_fg_id']."'");
    //                         $new_delivery = isset($total_shipping[0]->total) ? $total_shipping[0]->total : 0;
    //                         $new_outstanding = $sales_order->qty - $new_delivery;
    //                         $this->crud->update('sales_orders', [
    //                             'sales_order_no' => $item['sales_order_no'],
    //                             'item_fg_id' => $item['item_fg_id']
    //                         ], [
    //                             'delivery' => $new_delivery,
    //                             'outstanding' => $new_outstanding
    //                         ]);
    //                     }
    //                 }

    //                 // Update status di fg_scan_in_label atau new_barcode_fg_detail
    //                 $this->db->where('serial_label', $post['checksheet_label']);
    //                 $this->db->where('status', '0');
    //                 $this->db->update('fg_scan_in_label', ['status' => 1]);

    //                 if ($this->db->affected_rows() == 0) {
    //                     $this->db->where('serial_label', $post['checksheet_label']);
    //                     $this->db->where('status', '0');
    //                     $this->db->update('new_barcode_fg_detail', ['status' => 1]);
    //                 }

    //                 echo json_encode(array("theme" => "success", "message" => "Data berhasil disimpan", "title" => "Success"));
    //             } else {
    //                 echo json_encode(array("title" => "Available", "message" => "Data Shipping Orders has been Scanning", "theme" => "error"));
    //             }
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {

                $post = $this->input->post();
                $item_fg_id = $post['item_fg_id'] ?? null;

                if (!$item_fg_id) {
                    echo json_encode(["title" => "Error", "message" => "Item FG ID is missing", "theme" => "error"]);
                    return;
                }

                $this->db->select("a.*");
                $this->db->from('output_production_press_detail a');
                $this->db->where('a.workorder_label', $post['workorder_label']);
                $this->db->where('a.status', '0');
                $this->db->where('a.item_fg_id !=', 'FGRPNA-0207');
                $label_item = $this->db->get()->row();

                if(empty($label_item)) {
                    $this->db->select("a.*");
                    $this->db->from('internal_process a');
                $this->db->join('output_production_press_detail b', 'a.item_fg_id = b.item_fg_id AND a.workorder = b.workorder and a.workorder_label = b.workorder_label');
                    $this->db->where('a.workorder_label', $post['workorder_label']);
                    $this->db->where('b.status', '2');
                    $this->db->where('a.item_fg_id', 'FGRPNA-0207');
                    $label_item = $this->db->get()->row();
                }

                if (empty($label_item)) {
                    echo json_encode([
                        "title"   => "Not Found",
                        "message" => "Label not found!",
                        "theme"   => "error"
                    ]);
                    return;
                }

                $summary = $this->getOutputPressSummary($label_item->item_fg_id, $label_item->workorder);

                if (!$summary || ($summary['qty_output'] ?? 0) <= 0) {
                    echo json_encode([
                        "title"   => "Already Scanned",
                        "message" => "Item has been finished in internal finishing or already delivered to the subcont",
                        "theme"   => "error"
                    ]);
                    return;
                }

                // if (!$label_item) {
                //     echo json_encode(["title" => "Not Found", "message" => "Label not found!", "theme" => "error"]);
                //     return;
                // }

                $existing = $this->db->select("id")
                    ->from("shipping_to_subconts")
                    ->where("workorder_label", $label_item->workorder_label)
                    ->where("status", 0)
                    ->get()
                    ->row();

                if ($existing) {
                    echo json_encode([
                        "title"   => "Available",
                        "message" => "Label has already been scanned",
                        "theme"   => "error"
                    ]);
                    return;
                }

                $this->db->select("scan_id");
                $this->db->from("shipping_to_subconts");
                $this->db->where("type_status", "scanning");
                $this->db->where("status", 0);
                $this->db->limit(1);
                $session_row = $this->db->get()->row();

                $scan_id = $session_row->scan_id ?? $this->generate_uuid();

                $shipping_to_subconts = $this->crud->read(
                    "shipping_to_subconts", [],
                    ["workorder_label" => $post['workorder_label']]
                );

                if (!$shipping_to_subconts) {
                    $qty = $post['qty'] ?? 0;

                    $data_to_insert = [
                        'scan_id'         => $scan_id,
                        'workorder'       => $label_item->workorder,
                        'workorder_label' => $label_item->workorder_label,
                        'item_fg_id'      => $label_item->item_fg_id,
                        'qty'             => $qty,
                        'type_status'     => 'scanning',
                        'status'          => 0
                    ];

                    $this->crud->create('shipping_to_subconts', $data_to_insert);

                    $this->db->where('workorder_label', $post['workorder_label']);
                    // $this->db->where('status', '0');

                    $this->db->group_start();
                        $this->db->where('status', 0);
                        $this->db->or_where('status', 2);
                    $this->db->group_end();

                    $this->db->update('output_production_press_detail', ['status' => 1]);

                    echo json_encode(["theme" => "success", "message" => "Data berhasil disimpan", "title" => "Success"]);
                } else {
                    echo json_encode(["title" => "Available", "message" => "Data Shipping To Subcont has been Scanning", "theme" => "error"]);
                }

            } else {
                show_error(validation_errors());
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    private function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function getOutputPressSummary($item_fg_id, $workorder)
    {
        $query = "
            SELECT
                a.item_fg_id,
                a.workorder,
                MIN(a.trans_date) AS trans_date,
                COALESCE(
                    CASE 
                        WHEN proc.source_value IS NOT NULL THEN 
                            (proc.source_value - COALESCE(del.qty_delivery_internal, 0))
                        ELSE 
                            (SUM(a.qty_ok) - COALESCE(del.qty_delivery_press, 0))
                    END, 0
                ) AS qty_output
            FROM output_production_press a

            LEFT JOIN (
                SELECT 
                    d.item_fg_id,
                    d.workorder,
                    -- SUM(CASE WHEN d.source_type = 'Output Production Press' THEN d.qty_delivery ELSE 0 END) AS qty_delivery_press,

                    SUM(
                        CASE 
                            WHEN d.source_type IN ('Output Production Press', 'Shipping') 
                                THEN d.qty_delivery 
                            ELSE 0 
                        END
                    ) AS qty_delivery_press,

                    SUM(CASE WHEN d.source_type = 'Internal Process' THEN d.qty_delivery ELSE 0 END) AS qty_delivery_internal
                FROM delivery_to_subconts d
                WHERE d.deleted = 0
                GROUP BY d.item_fg_id, d.workorder
            ) del ON del.item_fg_id = a.item_fg_id AND del.workorder = a.workorder

            LEFT JOIN (
                SELECT 
                    x.item_fg_id,
                    x.workorder,
                    CASE 
                        WHEN MAX(x.process_name) = 'Internal Finishing' THEN SUM(x.external)
                        WHEN MAX(x.process_name) = 'Cutting Punch' THEN SUM(x.ok_punch)
                        ELSE NULL
                    END AS source_value
                FROM internal_process x
                WHERE x.deleted = 0
                GROUP BY x.item_fg_id, x.workorder
            ) proc ON proc.item_fg_id = a.item_fg_id AND proc.workorder = a.workorder

            WHERE a.item_fg_id = '$item_fg_id' 
            AND a.workorder = '$workorder'

            GROUP BY a.item_fg_id, a.workorder
            HAVING qty_output > 0
            LIMIT 1
        ";

        return $this->db->query($query)->row_array();
    }

    public function createDN()
    {
        if ($this->input->post()) {

            $post = $this->input->post();
            $items = json_decode($post['items'], true);

            if (!$items) {
                echo json_encode([
                    "theme" => "error",
                    "message" => "Items data not received"
                ]);
                return;
            }

            $exists = $this->crud->read('delivery_to_subconts', [], [
                'delivery_note_no' => $post['delivery_note_no']
            ]);

            if ($exists) {
                echo json_encode(["theme"=>"error","message"=>"Delivery Note already created"]);
                return;
            }

            $this->db->trans_begin();

            foreach ($items as $row) {

                $summary = $this->getOutputPressSummary(
                    $row['item_fg_id'],
                    $row['workorder'],
                );

                $insert = [
                    'item_fg_id'        => $row['item_fg_id'],
                    'workorder'         => $row['workorder'],
                    'qty_delivery'      => $row['shipping'],
                    'delivery_note_no'  => $post['delivery_note_no'],
                    'delivery_date'     => $post['delivery_date'],
                    'delivery_category' => $post['delivery_category'],
                    'delivery_to'       => $post['delivery_to'],
                    'destination'       => $post['destination'],
                    'source_type'       => 'Shipping',
                    'prod_date'         => $summary['trans_date'] ?? null,
                    'qty_output'        => $summary['qty_output'] ?? 0,
                    'status'            => 0
                ];


                $table_approval = 'delivery_to_subconts';

                $insert_dn = $this->crud->createPO('delivery_to_subconts',$table_approval, $insert);
                if (!$insert_dn) {
                    $this->db->trans_rollback();
                    echo json_encode([
                        "theme" => "error",
                        "message" => "Delivery Note failed to be created",
                        "title" => "Error"
                    ]);
                    return;
                }

                // $this->crud->create('delivery_to_subconts', $insert);

                $this->db->where('scan_id', $row['scan_id']);
                $this->db->where('item_fg_id', $row['item_fg_id']);
                $this->db->where('workorder', $row['workorder']);
                $this->db->update('shipping_to_subconts', [
                    'type_status' => 'completed',
                    'updated_date'  => date('Y-m-d H:i:s')
                ]);
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(["theme"=>"error","message"=>"Failed to save row"]);
                return;
            }

            $this->db->trans_commit();
            echo json_encode(["theme"=>"success","message"=>"Delivery Note created successfully"]);
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