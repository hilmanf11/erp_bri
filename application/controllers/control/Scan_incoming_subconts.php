<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_incoming_subconts extends CI_Controller
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
            $this->load->view('control/scan_incoming_subconts');
        } else {
            redirect('error_access');
        }
    }

    public function readIncomingFrom()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sql = "
            SELECT 
                id,
                number,
                name,
                'Subcont' AS type
            FROM subconts
            WHERE status = 0
            AND deleted = 0
            AND (number LIKE '%$post%' OR name LIKE '%$post%' OR id LIKE '%$post%')

            UNION ALL

            SELECT 
                id,
                number,
                name,
                'Teaching Factory' AS type
            FROM teaching_factory
            WHERE status = 0
            AND deleted = 0
            AND (number LIKE '%$post%' OR name LIKE '%$post%' OR id LIKE '%$post%')
        ";

        $send = $this->crud->query($sql);

        echo json_encode($send);
    }

    // public function incoming_doc_nov1()
    // {
    //     $incoming_date = $this->input->post('incoming_date');
    //     $incoming_from = $this->input->post('incoming_from');

    //     $date = $incoming_date ? date("Y-m-d", strtotime($incoming_date)) : date("Y-m-d");
    //     $month = date("m", strtotime($date));
    //     $year = date("y", strtotime($date));
    //     $day = date("d", strtotime($date));

    //     // Tentukan periode reset berdasarkan tanggal 16
    //     if ($day < 16) {
    //         $period_start = date("Y-m-16", strtotime("-1 month", strtotime($date)));
    //         $period_end   = date("Y-m-15", strtotime($date));
    //     } else {
    //         $period_start = date("Y-m-16", strtotime($date));
    //         $period_end   = date("Y-m-15", strtotime("+1 month", strtotime($date)));
    //     }

    //     $period_month = date("m", strtotime($period_end));
    //     $period_year  = date("y", strtotime($period_end));

    //     $sql = $this->db->query("
    //         SELECT MAX(SUBSTRING_INDEX(incoming_doc_no, '/', 1)) AS kode
    //         FROM scan_incoming_sctf
    //         WHERE incoming_doc_no LIKE '%/{$incoming_from}/IN/{$period_month}/{$period_year}'
    //         AND incoming_date BETWEEN '{$period_start}' AND '{$period_end}'
    //     ");

    //     $row = $sql->row();

    //     if ($row->kode == null) {
    //         $seq = "001";
    //     } else {
    //         $seq = sprintf("%03s", intval($row->kode) + 1);
    //     }

    //     $autonumber = "{$seq}/{$incoming_from}/IN/{$period_month}/{$period_year}";

    //     echo $autonumber;
    // }

    public function incoming_doc_no()
    {
        $incoming_date = $this->input->post('incoming_date');
        $incoming_from = $this->input->post('incoming_from');

        $date = $incoming_date ? date("Y-m-d", strtotime($incoming_date)) : date("Y-m-d");
        $day  = date("d", strtotime($date));

        if ($day < 16) {
            $period_start = date("Y-m-16", strtotime("-1 month", strtotime($date)));
            $period_end   = date("Y-m-15", strtotime($date));
        } else {
            $period_start = date("Y-m-16", strtotime($date));
            $period_end   = date("Y-m-15", strtotime("+1 month", strtotime($date)));
        }

        $doc_month = date("m", strtotime($date));
        $doc_year  = date("y", strtotime($date));

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(incoming_doc_no, '/', 1) AS UNSIGNED)) AS kode
            FROM scan_incoming_sctf
            WHERE incoming_date BETWEEN '{$period_start}' AND '{$period_end}'
            AND incoming_doc_no LIKE '%/{$incoming_from}/IN/%'
        ");

        $row = $sql->row();
        $seq = $row->kode ? sprintf("%03d", $row->kode + 1) : "001";

        echo "{$seq}/{$incoming_from}/IN/{$doc_month}/{$doc_year}";
    }

    public function getScanIncoming()
    {
        $this->db->select('
            a.incoming_type,
            a.incoming_doc_no,
            a.incoming_date,
            a.incoming_from,
            COALESCE(c.number, d.number) as incoming_from_number,
            a.delivery_note_no,
            a.is_partial,

            a.scan_id,
            a.item_fg_id,
            a.workorder,
            a.workorder_label,
            a.qty,
            a.created_date,
            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom,
            (
            SELECT COUNT(*)
                FROM scan_incoming_sctf x
                WHERE x.workorder_label = a.workorder_label
            ) AS total_label
        ');

        $this->db->from('scan_incoming_sctf a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.incoming_from = c.id', 'left');
        $this->db->join('teaching_factory d', 'a.incoming_from = d.id', 'left');
        $this->db->where('a.type_status', 'scanning');
        $this->db->where('a.status', 0);
        $this->db->order_by('a.created_date', 'DESC');

        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => count($records),
            'rows'  => $records
        ]);
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $workorder_label = $this->input->post('workorder_label');
            $incoming_from_code = $this->input->post('incoming_from_code');

            $label = $this->db->get_where('shipping_to_subconts', [
                'workorder_label' => $workorder_label,
            ])->row_array();

            $this->db->select("sum(qty) as qty_incoming, is_partial");
            $this->db->from("scan_incoming_sctf");
            $this->db->where('workorder_label', $workorder_label);
            $labelIncoming = $this->db->get()->row_array();

            if (!$label) {
                echo json_encode([
                    'title' => 'Not Found',
                    'message' => 'Label not found in Shipping to Subconts!'
                ]);
                return;
            }

            if ($label['type_status'] == 'scanning') {
                echo json_encode([
                    'title' => 'Process Scanned',
                    'message' => 'Label in the process of being scanned in shipping',
                    'data' => $label
                ]);
                return;
            }

            $is_partial = isset($labelIncoming['is_partial']) ? $labelIncoming['is_partial'] : 0;

            if ($label['status'] == 1 && $is_partial == 0) {
                echo json_encode([
                    'title' => 'Available',
                    'message' => 'Label has already been scanned',
                    'theme' => 'error'
                ]);
                return;
            }

            $qty_incoming = isset($labelIncoming['qty_incoming']) ? $labelIncoming['qty_incoming'] : 0;
            $qty_label = $label['qty'] - $qty_incoming;

            // if ($qty_incoming > $label['qty'] && $is_partial == 1) {
            //     echo json_encode([
            //         'title' => 'Partial Scan Exceeded',
            //         'message' => 'The total scanned qty for this partial label exceeds the original qty',
            //         'theme' => 'error'
            //     ]);
            //     return;
            // }

            if($qty_label <= 0) {
                echo json_encode([
                    'title' => 'Partial Scan Exceeded',
                    'message' => 'The scanned qty for this partial label matches the original qty',
                    'theme' => 'error'
                ]);
                return;
            }

            $this->db->select("a.item_fg_id, a.workorder, a.workorder_label, b.destination, b.delivery_note_no");
            $this->db->from('shipping_to_subconts a');
            $this->db->join('delivery_to_subconts b', 'a.scan_id = b.scan_id and a.workorder = b.workorder');
            $this->db->where('b.deleted', 0);
            $this->db->where('a.workorder_label', $workorder_label);
            // $this->db->where('approved_by', 'scmbri01');
            $this->db->where('approved_to', '');

            $checkApproval = $this->db->get()->row_array();

            if (empty($checkApproval)) {
                echo json_encode([
                    'title'   => 'Invalid Label',
                    'message' => 'The label does not exists or is pending approval',
                    'theme'   => 'error'
                ]);
                return;
            }

            if ($checkApproval['destination'] != $incoming_from_code) {
                echo json_encode([
                    'title'   => 'Invalid Incoming From',
                    'message' => 'This label does not match the selected incoming source',
                    'theme'   => 'error'
                ]);
                return;
            }

            $delivery_note_no = $checkApproval['delivery_note_no'];

            $this->db->select("item_fg_id, workorder, workorder_label, qty");
            $this->db->from('shipping_to_subconts');
            $this->db->where('workorder_label', $workorder_label);

            if($is_partial == 0) {
                $this->db->where('status', '0');
            }

            $result = $this->db->get()->row_array();

            $result['delivery_note_no'] = $delivery_note_no;
            $result['qty'] = $qty_label;
            $result['is_partial'] = $is_partial == 1 ? 1 : 0;

            echo json_encode([ 
                'title' => 'success', 
                'data' => $result
            ]);
        }
    }


    public function getSummaryIncoming()
    {
        $records = $this->db
            ->select("
                SUM(a.qty) as qty_total,
                b.number as item_fg_number,
                b.name as item_fg_name,
                b.mpq as qty_packing
            ")
            ->from('scan_incoming_sctf a')
            ->join('item_fg b', 'a.item_fg_id = b.id')
            ->where('a.qty > 0')
            ->where('a.type_status', 'scanning')
            ->order_by('b.number', 'ASC')
            ->group_by(['scan_id', 'item_fg_id'])
            ->get()
            ->result_array();

        echo json_encode([
            "total" => count($records),
            "rows"  => $records
        ]);
    }

    public function create()
    {
        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {

                $post = $this->input->post();
                $item_fg_id = $post['item_fg_id'] ?? null;

                if (!$item_fg_id) {
                    echo json_encode(["title" => "Error", "message" => "Item Fg is missing", "theme" => "error"]);
                    return;
                }

                $this->db->select("a.*");
                $this->db->from('shipping_to_subconts a');
                $this->db->where('a.workorder_label', $post['workorder_label']);
                $this->db->where('a.type_status', 'completed');
                $label_item = $this->db->get()->row();


                if (empty($label_item)) {
                    echo json_encode([
                        "title"   => "Not Found",
                        "message" => "Label not found!",
                        "theme"   => "error"
                    ]);
                    return;
                }

                // $existing = $this->db->select("id")
                //     ->from("scan_incoming_sctf")
                //     ->where("workorder_label", $label_item->workorder_label)
                //     ->where("status", 0)
                //     ->get()
                //     ->row();

                // if ($existing) {
                //     echo json_encode([
                //         "title"   => "Available",
                //         "message" => "Label has already been scanned",
                //         "theme"   => "error"
                //     ]);
                //     return;
                // }

                if (
                    empty($post['incoming_type']) ||
                    empty($post['incoming_doc_no']) ||
                    empty($post['incoming_date']) ||
                    empty($post['incoming_from'])
                ) {
                    echo json_encode([
                        "title"   => "Invalid Data",
                        "message" => "Incoming header is required",
                        "theme"   => "error"
                    ]);
                    return;
                }


                $this->db->select("scan_id, workorder_label");
                $this->db->from("scan_incoming_sctf");
                $this->db->where("type_status", "scanning");
                $this->db->where("status", 0);
                $this->db->limit(1);
                $session_row = $this->db->get()->row();

                $scan_id = $session_row->scan_id ?? $this->generate_uuid();

                if(isset($session_row) && $session_row->workorder_label == $post['workorder_label'] && $session_row->scan_id) {
                    echo json_encode([
                        "title"   => "Label Already Used",
                        "message" => "This partial label has already been used in the current scan",
                        "theme"   => "error"
                    ]);
                    return;
                }

                $scan_incoming_sctf = $this->crud->read("scan_incoming_sctf", [], [
                        "workorder_label" => $post['workorder_label'],
                        "is_partial" => 0
                    ],
                );

                if (!$scan_incoming_sctf) {
                    $qty = $post['qty'] ?? 0;

                    $data_to_insert = [
                        'incoming_type'     => $post['incoming_type'],
                        'incoming_doc_no'   => $post['incoming_doc_no'],
                        'incoming_date'     => $post['incoming_date'],
                        'incoming_from'     => $post['incoming_from'],
                        'delivery_note_no'  => $post['delivery_note_no'],
                        'scan_id'           => $scan_id,
                        'workorder'         => $label_item->workorder,
                        'workorder_label'   => $label_item->workorder_label,
                        'item_fg_id'        => $label_item->item_fg_id,
                        'qty'               => $qty,
                        'type_status'       => 'scanning',
                        'status'            => 0,
                        'is_partial'        => $post['is_partial'],
                    ];

                    $this->crud->create('scan_incoming_sctf', $data_to_insert);

                    echo json_encode([
                        "theme" => "success",
                        "message" => "Data berhasil disimpan",
                        "title" => "Success"
                    ]);

                    // $this->db->trans_begin();

                    // try {

                    //     $this->crud->create('scan_incoming_sctf', $data_to_insert);

                        // if ($post['incoming_type'] === 'BPM') {

                        //     $qty_label = (int) $label_item->qty;

                        //     // ambil qty_delivery saat ini
                        //     $dn = $this->db->select('qty_delivery')
                        //         ->from('delivery_to_subconts')
                        //         ->where('delivery_note_no', $post['delivery_note_no'])
                        //         ->where('item_fg_id', $label_item->item_fg_id)
                        //         ->where('workorder', $label_item->workorder)
                        //         ->where('deleted', 0)
                        //         ->get()
                        //         ->row();

                        //     if (!$dn) {
                        //         throw new Exception('Delivery Note not found');
                        //     }

                        //     if ($qty_label > $dn->qty_delivery) {
                        //         throw new Exception('Qty BPM exceeds Delivery Qty');
                        //     }

                        //     // $this->db->where('workorder_label', $label_item->workorder_label)
                        //     //         ->delete('shipping_to_subconts');


                        //     $this->crud->delete('shipping_to_subconts', ['workorder_label' => $label_item->workorder_label]);


                        //     $data = [
                        //         "updated_by" => $this->session->username,
                        //         "updated_date" => date('Y-m-d H:i:s')
                        //     ];

                        //     $table  = "delivery_to_subconts";

                        //     $dataBefore = $this->crud->read($table, [], [
                        //         "delivery_note_no" => $post['delivery_note_no'],
                        //         "item_fg_id" => $label_item->item_fg_id,
                        //         "workorder" => $label_item->workorder,
                        //         "deleted" => 0
                        //     ]);

                        //     if($qty_label == $dn->qty_delivery) {

                        //         // $this->crud->update("delivery_to_subconts", [
                        //         //     "delivery_note_no" => $post['delivery_note_no'],
                        //         //     "item_fg_id" => $label_item->item_fg_id,
                        //         //     "workorder" => $label_item->workorder,
                        //         //     "deleted" => 0,
                        //         // ], [
                        //         //     "deleted" => 1,
                        //         //     "qty_delivery" => 0,
                        //         // ]);


                        //         $this->db->update("delivery_to_subconts", [
                        //             "updated_by" => $this->session->username,
                        //             "updated_date" => date('Y-m-d H:i:s'),
                        //             "deleted" => 1,
                        //             "qty_delivery" => 0,
                        //         ], [
                        //             "delivery_note_no" => $post['delivery_note_no'],
                        //             "item_fg_id" => $label_item->item_fg_id,
                        //             "workorder" => $label_item->workorder,
                        //             "deleted" => 0,
                        //         ]);

                        //         $this->crud->logs("Update Before", json_encode($dataBefore), $table);
                        //         $this->crud->logs("Update New", json_encode($data), $table);

                        //     } else {

                        //         $qty_last = $dn->qty_delivery - $qty_label;
    
                        //         // $this->crud->update("delivery_to_subconts", [
                        //         //     "delivery_note_no" => $post['delivery_note_no'],
                        //         //     "item_fg_id" => $label_item->item_fg_id,
                        //         //     "workorder" => $label_item->workorder,
                        //         //     "deleted" => 0,
                        //         // ], [
                        //         //     "qty_delivery" => $qty_last,
                        //         // ]);

                        //         $this->db->update("delivery_to_subconts", [
                        //             "updated_by" => $this->session->username,
                        //             "updated_date" => date('Y-m-d H:i:s'),
                        //             "qty_delivery" => $qty_last,
                        //         ], [
                        //             "delivery_note_no" => $post['delivery_note_no'],
                        //             "item_fg_id" => $label_item->item_fg_id,
                        //             "workorder" => $label_item->workorder,
                        //             "deleted" => 0,
                        //         ]);

                        //         $this->crud->logs("Update Before", json_encode($dataBefore), $table);
                        //         $this->crud->logs("Update New", json_encode($data), $table);
                        //     }


                        //     if($label_item->item_fg_id == "FGRPNA-0207") {

                        //         $this->crud->update("output_production_press_detail", [
                        //             "workorder_label" => $label_item->workorder_label,
                        //             "item_fg_id" => $label_item->item_fg_id,
                        //         ], [
                        //             "status" => 2,
                        //         ]);
                        //     } else {

                        //         $this->crud->update("output_production_press_detail", [
                        //             "workorder_label" => $label_item->workorder_label,
                        //             "item_fg_id" => $label_item->item_fg_id,
                        //         ], [
                        //             "status" => 0,
                        //         ]);
                        //     }


                        // } else {

                        //     $this->crud->update("shipping_to_subconts", [
                        //         "workorder_label" => $post['workorder_label'],
                        //         "item_fg_id" => $post['item_fg_id'],
                        //     ], [
                        //         "status" => 1,
                        //     ]);
                        // }

                    //     if ($this->db->trans_status() === FALSE) {
                    //         throw new Exception('Transaction failed');
                    //     }

                    //     $this->db->trans_commit();


                    // } catch (Exception $e) {

                    //     $this->db->trans_rollback();

                    //     echo json_encode([
                    //         "title" => "Error",
                    //         "message" => $e->getMessage(),
                    //         "theme" => "error"
                    //     ]);
                    // }

                } else {
                    echo json_encode([
                        "title"   => "Available",
                        "message" => "Data Incoming From SC/TF has been Scanning",
                        "theme"   => "error"
                    ]);
                    return;
                }

            } else {
                show_error(validation_errors());
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updateQty()
    {
        $scan_id          = $this->input->post('scan_id');
        $item_fg_id       = $this->input->post('item_fg_id');
        $workorder        = $this->input->post('workorder');
        $workorder_label  = $this->input->post('workorder_label');
        $qty              = $this->input->post('qty');
        $is_partial       = $this->input->post('is_partial');
        $old_qty          = $this->input->post('old_qty');

        if (!$item_fg_id || !$workorder || !$workorder_label || !$qty) {
            echo json_encode([
                'title' => 'Invalid Data',
                "message" => "Incoming data is not completed",
                'theme' => 'error'
            ]);
            return;
        }

        $label = $this->db->get_where('shipping_to_subconts', [
            'workorder_label' => $workorder_label,
        ])->row_array();

        $this->db->select("SUM(qty) AS qty_incoming");
        $this->db->from("scan_incoming_sctf");
        $this->db->where('workorder_label', $workorder_label);
        $totalRow = $this->db->get()->row_array();

        $total_existing = (int) ($totalRow['qty_incoming'] ?? 0);

        $qty_input = (int) $qty;
        $total_after = ($total_existing - $old_qty) + $qty_input;

        if ($is_partial == 1 && $total_after > $label['qty']) {
            echo json_encode([
                'title'   => 'Partial Qty Exceeded',
                'message' => 'Total scanned partial qty after update exceeds original label qty',
                'theme'   => 'error'
            ]);
            return;
        }

        $partial = $is_partial == 1 ? 1 : 0;

        $send = $this->crud->update('scan_incoming_sctf', [
            'scan_id' => $scan_id,
            'item_fg_id' => $item_fg_id,
            'workorder' => $workorder,
            'workorder_label' => $workorder_label,
        ], [
            'qty' => $qty,
            'is_partial' => $partial
        ]);


        if ($send) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Update failed'
            ]);
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

    // public function saveSummaryIncomingv1()
    // {
    //     if (!$this->input->post('items')) {
    //         show_error("Cannot process your request.");
    //     }

    //     $items = $this->input->post('items');
    //     $errors = [];
    //     $success_count = 0;

    //     $this->db->trans_begin();

    //     foreach ($items as $post) {

    //         $result = $this->crud->update('scan_incoming_sctf', [
    //             'workorder_label' => $post['workorder_label'],
    //             'item_fg_id' => $post['item_fg_id']
    //         ], [
    //             'type_status' => 'completed'
    //         ]);

    //         if ($result) {
    //             $success_count++;
    //         } else {
    //             $errors[] = "Failed saving incoming SC/TF";
    //         }
    //     }

    //     if ($this->db->trans_status() === FALSE || !empty($errors)) {
    //         $this->db->trans_rollback();
    //         echo json_encode([
    //             "title" => "Failed to Save",
    //             "message" => implode("\n", array_unique($errors)),
    //             "theme" => "error"
    //         ]);
    //         return;
    //     }

    //     $this->db->trans_commit();

    //     echo json_encode([
    //         "title" => "Success",
    //         "message" => "Data saved successfully",
    //         "theme" => "success"
    //     ]);
    // }

    // public function saveSummaryIncomingv2()
    // {
    //     if (!$this->input->post('items')) {
    //         show_error("Cannot process your request.");
    //     }

    //     $items = $this->input->post('items');
    //     $errors = [];
    //     $success_count = 0;

    //     $this->db->trans_begin();

    //     foreach ($items as $post) {

    //         $updateIncoming = $this->crud->update('scan_incoming_sctf', [
    //                 'workorder_label' => $post['workorder_label'],
    //                 'item_fg_id'      => $post['item_fg_id']
    //             ],
    //             [
    //                 'type_status' => 'completed'
    //             ]
    //         );

    //         $this->db->select("a.*");
    //         $this->db->from('shipping_to_subconts a');
    //         $this->db->where('a.workorder_label', $post['workorder_label']);
    //         $this->db->where('a.type_status', 'completed');
    //         $label_item = $this->db->get()->row();

    //         if ($post['incoming_type'] === 'BPM') {

    //             $qty_label = (int) $label_item->qty;

    //             // ambil qty_delivery saat ini
    //             $dn = $this->db->select('qty_delivery')
    //                 ->from('delivery_to_subconts')
    //                 ->where('delivery_note_no', $post['delivery_note_no'])
    //                 ->where('item_fg_id', $label_item->item_fg_id)
    //                 ->where('workorder', $label_item->workorder)
    //                 ->where('deleted', 0)
    //                 ->get()
    //                 ->row();

    //             if (!$dn) {
    //                 $errors[] = "Delivery Note No not found";
    //                 continue;
    //             }

    //             if ($qty_label > $dn->qty_delivery) {
    //                 $errors[] = "Qty BPM exceeds Delivery Qty";
    //                 continue;
    //             }

    //             $this->crud->delete('shipping_to_subconts', ['workorder_label' => $label_item->workorder_label]);

    //             $data = [
    //                 "updated_by" => $this->session->username,
    //                 "updated_date" => date('Y-m-d H:i:s')
    //             ];

    //             $table  = "delivery_to_subconts";

    //             $dataBefore = $this->crud->read($table, [], [
    //                 "delivery_note_no" => $post['delivery_note_no'],
    //                 "item_fg_id" => $label_item->item_fg_id,
    //                 "workorder" => $label_item->workorder,
    //                 "deleted" => 0
    //             ]);

    //             if($qty_label == $dn->qty_delivery) {

    //                 $this->db->update("delivery_to_subconts", [
    //                     "qty_delivery" => 0,
    //                     "deleted" => 1,
    //                     "updated_by" => $this->session->username,
    //                     "updated_date" => date('Y-m-d H:i:s'),
    //                 ], [
    //                     "delivery_note_no" => $post['delivery_note_no'],
    //                     "item_fg_id" => $label_item->item_fg_id,
    //                     "workorder" => $label_item->workorder,
    //                     "deleted" => 0,
    //                 ]);

    //                 $this->crud->logs("Update Before", json_encode($dataBefore), $table);
    //                 $this->crud->logs("Update New", json_encode($data), $table);

    //             } else {

    //                 $qty_last = $dn->qty_delivery - $qty_label;

    //                 $this->db->update("delivery_to_subconts", [
    //                     "qty_delivery" => $qty_last,
    //                     "updated_by" => $this->session->username,
    //                     "updated_date" => date('Y-m-d H:i:s'),
    //                 ], [
    //                     "delivery_note_no" => $post['delivery_note_no'],
    //                     "item_fg_id" => $label_item->item_fg_id,
    //                     "workorder" => $label_item->workorder,
    //                     "deleted" => 0,
    //                 ]);

    //                 $this->crud->logs("Update Before", json_encode($dataBefore), $table);
    //                 $this->crud->logs("Update New", json_encode($data), $table);
    //             }

    //             $status = ($label_item->item_fg_id === 'FGRPNA-0207') ? 2 : 0;

    //             $this->crud->update("output_production_press_detail", [
    //                 "workorder_label" => $label_item->workorder_label,
    //                 "item_fg_id" => $label_item->item_fg_id,
    //             ], [
    //                 "status" => $status,
    //             ]);

    //         } else {

    //             $this->crud->update("shipping_to_subconts", [
    //                 "workorder_label" => $post['workorder_label'],
    //                 "item_fg_id" => $post['item_fg_id'],
    //             ], [
    //                 "status" => 1,
    //             ]);
    //         }


    //         // Validasi hasil
    //         if ($updateIncoming) {
    //             $success_count++;
    //         } else {
    //             $errors[] = "Failed update incoming / shipping for WO : {$post['workorder_label']}";
    //         }
    //     }

    //     if ($this->db->trans_status() === FALSE || !empty($errors)) {
    //         $this->db->trans_rollback();
    //         echo json_encode([
    //             "title"   => "Failed to Save",
    //             "message" => implode("\n", array_unique($errors)),
    //             "theme"   => "error"
    //         ]);
    //         return;
    //     }

    //     $this->db->trans_commit();

    //     echo json_encode([
    //         "title"   => "Success",
    //         "message" => "Data saved successfully",
    //         "theme"   => "success"
    //     ]);
    // }

    public function saveSummaryIncoming()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');

        $this->db->trans_begin();

        try {

            foreach ($items as $post) {
                $updateIncoming = $this->crud->update('scan_incoming_sctf', [
                        'workorder_label' => $post['workorder_label'],
                        'item_fg_id'      => $post['item_fg_id']
                    ], ['type_status' => 'completed']
                );

                if (!$updateIncoming) {
                    throw new Exception("Failed update incoming {$post['workorder_label']}");
                }

                $label_item = $this->db->get_where('shipping_to_subconts', [
                    'workorder_label' => $post['workorder_label'],
                    'type_status'     => 'completed'
                ])->row();

                if (!$label_item) {
                    throw new Exception("Label not found {$post['workorder_label']} in Shipping");
                }

                $qty_label = (int) $post['qty'];

                $dn = $this->db->get_where('delivery_to_subconts', [
                    'delivery_note_no' => $post['delivery_note_no'],
                    'item_fg_id'       => $label_item->item_fg_id,
                    'workorder'        => $label_item->workorder,
                    'deleted'          => 0
                ])->row();

                if (!$dn) {
                    throw new Exception("Delivery Note No not found");
                }

                // if ($qty_label > $dn->qty_delivery) {
                //     throw new Exception("Qty label exceeds delivery quantity for label {$post['workorder_label']}");
                // }

                if ($post['incoming_type'] === 'BPM') {

                    if ($qty_label > $dn->qty_delivery) {
                        throw new Exception("Qty label exceeds delivery quantity for label {$post['workorder_label']}");
                    }

                    $this->crud->delete('shipping_to_subconts', ['workorder_label' => $label_item->workorder_label]);

                    $data = [
                        "updated_by" => $this->session->username,
                        "updated_date" => date('Y-m-d H:i:s')
                    ];

                    $table  = "delivery_to_subconts";

                    $dataBefore = $this->crud->read($table, [], [
                        "delivery_note_no" => $post['delivery_note_no'],
                        "item_fg_id" => $label_item->item_fg_id,
                        "workorder" => $label_item->workorder,
                        "deleted" => 0
                    ]);

                    if($qty_label == $dn->qty_delivery) {

                        $this->db->update("delivery_to_subconts", [
                            "qty_delivery" => 0,
                            "deleted" => 1,
                            "updated_by" => $this->session->username,
                            "updated_date" => date('Y-m-d H:i:s'),
                        ], [
                            "delivery_note_no" => $post['delivery_note_no'],
                            "item_fg_id" => $label_item->item_fg_id,
                            "workorder" => $label_item->workorder,
                            "deleted" => 0,
                        ]);

                        $this->crud->logs("Update Before", json_encode($dataBefore), $table);
                        $this->crud->logs("Update New", json_encode($data), $table);

                    } else {

                        $qty_last = $dn->qty_delivery - $qty_label;

                        $this->db->update("delivery_to_subconts", [
                            "qty_delivery" => $qty_last,
                            "updated_by" => $this->session->username,
                            "updated_date" => date('Y-m-d H:i:s'),
                        ], [
                            "delivery_note_no" => $post['delivery_note_no'],
                            "item_fg_id" => $label_item->item_fg_id,
                            "workorder" => $label_item->workorder,
                            "deleted" => 0,
                        ]);

                        $this->crud->logs("Update Before", json_encode($dataBefore), $table);
                        $this->crud->logs("Update New", json_encode($data), $table);
                    }

                    $status = ($label_item->item_fg_id === 'FGRPNA-0207') ? 2 : 0;

                    $this->crud->update(
                        'output_production_press_detail',
                        [
                            'workorder_label' => $label_item->workorder_label,
                            'item_fg_id'      => $label_item->item_fg_id,
                        ],
                        ['status' => $status]
                    );

                } else {

                    $updateShipping = $this->crud->update('shipping_to_subconts', [
                            'workorder_label' => $post['workorder_label'],
                            'item_fg_id'      => $post['item_fg_id'],
                        ],
                        ['status' => 1]
                    );

                    if (!$updateShipping) {
                        throw new Exception("Failed update shipping {$post['workorder_label']}");
                    }
                }
            }

            $this->db->trans_commit();

            echo json_encode([
                "title"   => "Success",
                "message" => "Data saved successfully",
                "theme"   => "success"
            ]);

        } catch (Exception $e) {

            $this->db->trans_rollback();

            echo json_encode([
                "title"   => "Failed",
                "message" => $e->getMessage(),
                "theme"   => "error"
            ]);
        }
    }
}