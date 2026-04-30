<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_out_rework extends CI_Controller
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
        $this->form_validation->set_rules('serial_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/scan_out_rework');
        } else {
            redirect('error_access');
        }
    }

    public function readScanOutRework()
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
            AND subcont_type_id = 'TS001'
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

    public function dnr_no()
    {
        $delivery_date = $this->input->post('delivery_date');
        $destination   = $this->input->post('destination');

        $date = $delivery_date ? date("Y-m-d", strtotime($delivery_date)) : date("Y-m-d");

        $doc_month = date("m", strtotime($date));
        $doc_year  = date("y", strtotime($date));

        $period_start = date("Y-m-01", strtotime($date));
        $period_end   = date("Y-m-t", strtotime($date));

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(dnr_no, '/', 1) AS UNSIGNED)) AS kode
            FROM scan_out_rework
            WHERE delivery_date BETWEEN '{$period_start}' AND '{$period_end}'
            AND dnr_no LIKE '%/{$destination}/RW/{$doc_month}/{$doc_year}'
        ");

        $row = $sql->row();

        $seq = $row->kode ? sprintf("%02d", $row->kode + 1) : "01";

        echo "{$seq}/{$destination}/RW/{$doc_month}/{$doc_year}";
    }

    public function getScanOutRework()
    {
        $this->db->select('
            a.dnr_no,
            a.delivery_date,
            a.delivery_category,
            a.destination,
            COALESCE(c.number, d.number) as destination_number,
            a.delivery_note_no,

            a.scan_id,
            a.item_fg_id,
            a.workorder,
            a.workorder_label,
            a.serial_label,
            a.qty,
            a.created_date,
            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom
        ');

        $this->db->from('scan_out_rework a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.destination = c.number', 'left');
        $this->db->join('teaching_factory d', 'a.destination = d.number', 'left');
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

            $serial_label     = $this->input->post('serial_label');
            $destination_code = $this->input->post('destination_code');

            $labels = $this->db->get_where('scan_in_rework', [
                'serial_label' => $serial_label,
            ])->result_array();

            if (!$labels) {
                echo json_encode([
                    'title' => 'Not Found',
                    'message' => 'Label not found in WIP Store!'
                ]);
                return;
            }

            foreach ($labels as $label) {

                if ($label['type_status'] == 'scanning') {
                    echo json_encode([
                        'title' => 'Process Scanned',
                        'message' => 'Label in the process of being scanned in shipping',
                        'data' => $label
                    ]);
                    return;
                }

                if ($label['status'] == 1) {
                    echo json_encode([
                        'title' => 'Available',
                        'message' => 'Label has already been scanned',
                        'theme' => 'error'
                    ]);
                    return;
                }
            }


            $this->db->select("delivery_note_no, item_fg_id, workorder, workorder_label, serial_label, source");
            $this->db->from('scan_in_rework');
            $this->db->where('deleted', 0);
            $this->db->where('serial_label', $serial_label);

            $checkReworks = $this->db->get()->result_array();

            if (!$checkReworks) {
                echo json_encode([
                    'title' => 'Invalid',
                    'message' => 'Data rework not found',
                    'theme' => 'error'
                ]);
                return;
            }

            foreach ($checkReworks as $row) {
                if ($row['source'] != $destination_code) {
                    echo json_encode([
                        'title'   => 'Invalid Scan Out Rework',
                        'message' => 'This label does not match the selected destination source',
                        'theme'   => 'error'
                    ]);
                    return;
                }
            }

            $this->db->select("item_fg_id, workorder, workorder_label, serial_label, qty, delivery_note_no");
            $this->db->from('scan_in_rework');
            $this->db->where('serial_label', $serial_label);
            $this->db->where('status', '0');

            $result = $this->db->get()->result_array();

            echo json_encode([
                'title' => 'success',
                'data'  => $result
            ]);
        }
    }



    public function getSummaryScanOutRework()
    {
        $records = $this->db
            ->select("
                SUM(a.qty) as qty_total,
                b.number as item_fg_number,
                b.name as item_fg_name
            ")
            ->from('scan_out_rework a')
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

    public function createV1()
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
                $this->db->from('scan_out_rework a');
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
                //     ->from("scan_out_rework")
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
                        "message" => "ScanOutRework header is required",
                        "theme"   => "error"
                    ]);
                    return;
                }


                $this->db->select("scan_id, workorder_label");
                $this->db->from("scan_out_rework");
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

                $scan_out_rework = $this->crud->read("scan_out_rework", [], [
                        "workorder_label" => $post['workorder_label'],
                        "is_partial" => 0
                    ],
                );

                if (!$scan_out_rework) {
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

                    $this->crud->create('scan_out_rework', $data_to_insert);

                    echo json_encode([
                        "theme" => "success",
                        "message" => "Data berhasil disimpan",
                        "title" => "Success"
                    ]);

                    // $this->db->trans_begin();

                    // try {

                    //     $this->crud->create('scan_out_rework', $data_to_insert);

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
                        //     //         ->delete('scan_out_rework');


                        //     $this->crud->delete('scan_out_rework', ['workorder_label' => $label_item->workorder_label]);


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

                        //     $this->crud->update("scan_out_rework", [
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
                        "message" => "Data ScanOutRework From SC/TF has been Scanning",
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

    public function create_bulk()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();
        $rows = $post['rows'] ?? [];

        if (empty($rows)) {
            return $this->jsonResponse(
                'Error',
                'No data to process',
                'error'
            );
        }

        $this->db->trans_begin();

        try {

            $serial_label = $rows[0]['serial_label'];

            $checkExisting = $this->db->get_where('scan_out_rework', [
                'serial_label' => $serial_label,
            ])->row();

            if ($checkExisting) {
                throw new Exception(json_encode([
                    'title' => 'Already Scanned',
                    'message' => "Serial {$serial_label} already scanned",
                    'theme' => 'error',
                ]));
            }

            $session_row = $this->db->select('scan_id')
                ->from('scan_out_rework')
                ->where('type_status', 'scanning')
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();
            
            foreach ($rows as $row) {

                $workorder_label = $row['workorder_label'];
                $serial_label = $row['serial_label'] ?? null;

                $label_item = $this->db->query("
                    SELECT *
                    FROM scan_in_rework
                    WHERE workorder_label = ?
                    AND serial_label = ?
                    FOR UPDATE
                ", [$workorder_label, $serial_label])->row();

                if (!$label_item) {
                    throw new Exception(json_encode([
                        'title' => 'Not Found',
                        'message' => "Label {$workorder_label} not found!",
                        'theme' => 'error',
                    ]));
                }

                if ($label_item->status == 1) {
                    throw new Exception(json_encode([
                        'title' => 'Already Scanned',
                        'message' => "Label {$workorder_label} already scanned",
                        'theme' => 'error',
                    ]));
                }

                $data_to_insert = [
                    'scan_id'           => $scan_id,
                    'dnr_no'            => $row['dnr_no'],
                    'delivery_note_no'  => $row['delivery_note_no'],
                    'delivery_category' => $row['delivery_category'],
                    'delivery_date'     => $row['delivery_date'],
                    'destination'       => $row['destination'],
                    'workorder'         => $row['workorder'],
                    'workorder_label'   => $workorder_label,
                    'serial_label'      => $serial_label,
                    'item_fg_id'        => $row['item_fg_id'],
                    'qty'               => (int) $row['qty'],
                    'type_status'       => 'scanning',
                    'status'            => 0,
                ];

                $this->crud->create('scan_out_rework', $data_to_insert);

                $this->crud->update('scan_in_rework',[
                    'workorder_label' => $workorder_label,
                    'serial_label' => $serial_label,
                ], ['status' => 1],
                );

            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->db->trans_commit();

            return $this->jsonResponse(
                'Success',
                'Data berhasil disimpan',
                'success'
            );

        } catch (Exception $e) {

            $this->db->trans_rollback();

            $json = @json_decode($e->getMessage(), true);

            if ($json) {
                return $this->jsonResponse(
                    $json['title'],
                    $json['message'],
                    $json['theme']
                );
            }

            return $this->jsonResponse(
                'Error',
                $e->getMessage(),
                'error'
            );
        }
    }


    private function jsonResponse($title, $message, $theme = 'error')
    {
        echo json_encode([
            'title'   => $title,
            'message' => $message,
            'theme'   => $theme
        ]);
        return;
    }

    private function generate_uuid()
    {
        $uuid = $this->uuid->v4();
        return $uuid;
    }

    public function saveSummaryScanOutRework()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');

        if (!$items || count($items) == 0) {
            echo json_encode([
                "title" => "Error",
                "message" => "Items data not received",
                "theme" => "error",
            ]);
            return;
        }

        $delivery_date     = $items[0]['delivery_date'] ?? '';
        $delivery_category = $items[0]['delivery_category'] ?? '';
        $destination       = $items[0]['destination'] ?? '';
        $dnr_no            = $items[0]['dnr_no'] ?? '';

        if (empty($delivery_date)) {
            echo json_encode([
                "title" => "Error",
                "message" => "Delivery Date is required"
            ]);
            return;
        }

        if (empty($delivery_category)) {
            echo json_encode([
                "title" => "Error",
                "message" => "Delivery Category is required"
            ]);
            return;
        }

        if (empty($destination)) {
            echo json_encode([
                "title" => "Error",
                "message" => "Destination is required"
            ]);
            return;
        }

        if (empty($dnr_no)) {
            echo json_encode([
                "title" => "Error",
                "message" => "DNR No is required"
            ]);
            return;
        }

        $exists = $this->crud->read('delivery_rework', [], [
            'dnr_no' => $items[0]['dnr_no']
        ]);

        if ($exists) {
            echo json_encode([
                "title" => "Error",
                "message"=>"DNR No already created",
                "theme"=>"error",
            ]);
            return;
        }

        $this->db->trans_begin();

        try {
            $grouped = [];

            foreach ($items as $item) {
                $key = $item['dnr_no'] . '|' . $item['item_fg_id'] . '|' . $item['workorder'];

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'scan_id'           => $item['scan_id'],
                        'item_fg_id'        => $item['item_fg_id'],
                        'delivery_note_no'  => $item['delivery_note_no'],
                        'dnr_no'            => $item['dnr_no'],
                        'delivery_category' => $item['delivery_category'],
                        'delivery_date'     => $item['delivery_date'],
                        'destination'       => $item['destination'],
                        'workorder'         => $item['workorder'],
                        'qty_delivery'      => 0
                    ];
                }

                $grouped[$key]['qty_delivery'] += (float)$item['qty'];
            }

            foreach ($grouped as $row) {

                $getTransDate = $this->getOutputPressTransDate(
                    $row['item_fg_id'],
                    $row['workorder'],
                );

                $insert = [
                    'scan_id'           => $row['scan_id'],
                    'item_fg_id'        => $row['item_fg_id'],
                    'delivery_note_no'  => $row['delivery_note_no'],
                    'dnr_no'            => $row['dnr_no'],
                    'delivery_category' => $row['delivery_category'],
                    'delivery_date'     => $row['delivery_date'],
                    'destination'       => $row['destination'],
                    'prod_date'         => $getTransDate['trans_date'] ?? null,
                    'workorder'         => $row['workorder'],
                    'qty_delivery'      => $row['qty_delivery'],
                    'status'            => 0
                ];

                $table_approval = 'delivery_rework';
                $insert_dr = $this->crud->createPO('delivery_rework', $table_approval , $insert);

                if (!$insert_dr) {
                    throw new Exception("Failed insert delivery_rework");
                }
            }

            foreach ($items as $item) {
                $updateScanOutRework = $this->crud->update('scan_out_rework', [
                        'workorder_label' => $item['workorder_label'],
                        'serial_label'    => $item['serial_label'],
                        'item_fg_id'      => $item['item_fg_id']
                    ], ['type_status'     => 'completed'],
                );

                if (!$updateScanOutRework) {
                    throw new Exception("Failed Update Scan Out Rework {$item['serial_label']}");
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode([
                    "theme"=>"error",
                    "message"=>"Failed to save row"
                ]);
                return;
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

    private function getOutputPressTransDate($item_fg_id, $workorder)
    {
        $query = "
            SELECT MIN(a.trans_date) AS trans_date
            FROM output_production_press a
            WHERE a.item_fg_id = ?
            AND a.workorder = ?
        ";

        return $this->db->query($query, [$item_fg_id, $workorder])->row_array();
    }

}