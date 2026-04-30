<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_in_from_internal_finishing extends CI_Controller
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
            $this->load->view('control/scan_in_from_internal_finishing');
        } else {
            redirect('error_access');
        }
    }

    public function getScanInFromInternalFinishing()
    {
        $this->db->select('
            a.scan_id,
            a.item_fg_id,
            a.workorder,
            a.workorder_label,
            a.serial_label,
            a.qty,
            a.is_partial,
            a.created_date,
            b.number as item_fg_number, 
            b.name as item_fg_name, 
            b.uom,
            (
            SELECT COUNT(*)
                FROM scan_in_from_internal_finishing x
                WHERE x.workorder_label = a.workorder_label
            ) AS total_label
        ');

        $this->db->from('scan_in_from_internal_finishing a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.type_status', 'scanning');
        $this->db->order_by('a.created_date', 'DESC');

        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => count($records),
            'rows'  => $records
        ]);
    }

    public function getChecksheetLabelV1()
    {
        if ($this->input->post()) {
            $workorder_label = $this->input->post('workorder_label');

            $label = $this->db->get_where('scan_in_finishing', [
                'workorder_label' => $workorder_label
            ])->row_array();

            $this->db->select("sum(qty) as qty_finishing, is_partial");
            $this->db->from("scan_in_from_internal_finishing");
            $this->db->where('workorder_label', $workorder_label);
            $labelInFinishing = $this->db->get()->row_array();

            if (!$label) {
                echo json_encode([
                    'title' => 'Not Found',
                    'message' => 'Label not found!'
                ]);
                return;
            }

            if ($label['type_status'] == 'scanning') {
                echo json_encode([
                    'title' => 'Process Scanned',
                    'message' => 'Label in the process of being scanned in from internal finishing',
                    'data' => $label
                ]);
                return;
            }

            $is_partial = isset($labelInFinishing['is_partial']) ? $labelInFinishing['is_partial'] : 0;

            if ($label['status'] == 1 && $is_partial == 0) {
                echo json_encode([
                    'title' => 'Available',
                    'message' => 'Label has already been scanned',
                    'theme' => 'error'
                ]);
                return;
            }

            $checkLabelInFinish = $this->db->get_where('scan_in_from_internal_finishing', [
                'workorder_label'   => $workorder_label,
                'type_status'       => 'scanning',
                'is_partial'        => 1
            ])->row_array();

            if($checkLabelInFinish) {
                echo json_encode([
                    'title' => 'Partial Already Scanned',
                    'message' => 'This label has an active partial scan process',
                    'theme' => 'error'
                ]);
                return;
            }

            $qtyFinishing = isset($labelInFinishing['qty_finishing']) ? $labelInFinishing['qty_finishing'] : 0;
            $qtyLabel = $label['qty'] - $qtyFinishing;

            if($qtyLabel <= 0) {
                echo json_encode([
                    'title' => 'Partial Scan Exceeded',
                    'message' => 'The scanned qty for this partial label matches the original qty',
                    'theme' => 'error'
                ]);
                return;
            }

            $this->db->select("item_fg_id, workorder, workorder_label, qty");
            $this->db->from('scan_in_finishing');
            $this->db->where('workorder_label', $workorder_label);

            if($is_partial == 0) {
                $this->db->where('status', '0');
            }

            $result = $this->db->get()->row_array();

            $result['qty'] = $qtyLabel;
            $result['is_partial'] = $is_partial == 1 ? 1 : 0;

            echo json_encode([ 
                'title' => 'success', 
                'data' => $result
            ]);
        }
    }


    // public function getChecksheetLabel()
    // {
    //     if ($this->input->post()) {
    //         $input_label = $this->input->post('workorder_label');

    //         if(strpos($input_label, 'RWIN') === 0) {

    //             $label = $this->db->get_where('scan_in_finishing', [
    //                 'serial_label' => $input_label
    //             ])->row_array();

    //             if (!$label) {
    //                 echo json_encode([
    //                     'title' => 'Not Found',
    //                     'message' => 'Label not found!'
    //                 ]);
    //                 return;
    //             }

    //             if ($label['type_status'] == 'scanning') {
    //                 echo json_encode([
    //                     'title' => 'Process Scanned',
    //                     'message' => 'Label in the process of being scanned in from internal finishing',
    //                     'data' => $label
    //                 ]);
    //                 return;
    //             }

    //             $this->db->select("SUM(qty) as qty_finishing, MAX(is_partial) as is_partial");
    //             $this->db->from("scan_in_from_internal_finishing");
    //             $this->db->where('serial_label', $input_label);
    //             $labelInFinishing = $this->db->get()->row_array();

    //             $is_partial = isset($labelInFinishing['is_partial']) ? $labelInFinishing['is_partial'] : 0;

    //             if ($label['status'] == 1 && $is_partial == 0) {
    //                 echo json_encode([
    //                     'title' => 'Available',
    //                     'message' => 'Label has already been scanned',
    //                     'theme' => 'error'
    //                 ]);
    //                 return;
    //             }

    //             $checkLabelInFinish = $this->db->get_where('scan_in_from_internal_finishing', [
    //                 'serial_label'      => $input_label,
    //                 'type_status'       => 'scanning',
    //                 'is_partial'        => 1
    //             ])->row_array();

    //             if($checkLabelInFinish) {
    //                 echo json_encode([
    //                     'title' => 'Partial Already Scanned',
    //                     'message' => 'This label has an active partial scan process',
    //                     'theme' => 'error'
    //                 ]);
    //                 return;
    //             }

    //             $qtyFinishing = isset($labelInFinishing['qty_finishing']) ? $labelInFinishing['qty_finishing'] : 0;
    //             $qtyLabel = $label['qty'] - $qtyFinishing;

    //             if($qtyLabel <= 0) {
    //                 echo json_encode([
    //                     'title' => 'Partial Scan Exceeded',
    //                     'message' => 'The scanned qty for this partial label matches the original qty',
    //                     'theme' => 'error'
    //                 ]);
    //                 return;
    //             }

    //             $this->db->select("item_fg_id, workorder, workorder_label, qty");
    //             $this->db->from('scan_in_finishing');
    //             $this->db->where('serial_label', $input_label);

    //             if($is_partial == 0) {
    //                 $this->db->where('status', '0');
    //             }

    //             $result = $this->db->get()->result_array();

    //             foreach ($result as &$row) {
    //                 $row['is_partial'] = $is_partial == 1 ? 1 : 0;
    //                 $row['serial_label'] = $input_label;
    //             }

    //             unset($row);
                

    //             echo json_encode([
    //                 'title' => 'success',
    //                 'total' => count($result),
    //                 'data'  => $result
    //             ]);
    //             return;
    //         }

    //         /**
    //          * PRESS
    //          */

    //         $label = $this->db->get_where('scan_in_finishing', [
    //             'workorder_label' => $input_label
    //         ])->row_array();

    //         if (!$label) {
    //             echo json_encode([
    //                 'title' => 'Not Found',
    //                 'message' => 'Label not found!'
    //             ]);
    //             return;
    //         }

    //         if ($label['type_status'] == 'scanning') {
    //             echo json_encode([
    //                 'title' => 'Process Scanned',
    //                 'message' => 'Label in the process of being scanned in from internal finishing',
    //                 'data' => $label
    //             ]);
    //             return;
    //         }

    //         $this->db->select("sum(qty) as qty_finishing, is_partial");
    //         $this->db->from("scan_in_from_internal_finishing");
    //         $this->db->where('workorder_label', $input_label);
    //         $labelInFinishing = $this->db->get()->row_array();

    //         $is_partial = isset($labelInFinishing['is_partial']) ? $labelInFinishing['is_partial'] : 0;

    //         if ($label['status'] == 1 && $is_partial == 0) {
    //             echo json_encode([
    //                 'title' => 'Available',
    //                 'message' => 'Label has already been scanned',
    //                 'theme' => 'error'
    //             ]);
    //             return;
    //         }

    //         $checkLabelInFinish = $this->db->get_where('scan_in_from_internal_finishing', [
    //             'workorder_label'   => $input_label,
    //             'type_status'       => 'scanning',
    //             'is_partial'        => 1
    //         ])->row_array();

    //         if($checkLabelInFinish) {
    //             echo json_encode([
    //                 'title' => 'Partial Already Scanned',
    //                 'message' => 'This label has an active partial scan process',
    //                 'theme' => 'error'
    //             ]);
    //             return;
    //         }

    //         $qtyFinishing = isset($labelInFinishing['qty_finishing']) ? $labelInFinishing['qty_finishing'] : 0;
    //         $qtyLabel = $label['qty'] - $qtyFinishing;

    //         if($qtyLabel <= 0) {
    //             echo json_encode([
    //                 'title' => 'Partial Scan Exceeded',
    //                 'message' => 'The scanned qty for this partial label matches the original qty',
    //                 'theme' => 'error'
    //             ]);
    //             return;
    //         }

    //         $this->db->select("item_fg_id, workorder, workorder_label, qty");
    //         $this->db->from('scan_in_finishing');
    //         $this->db->where('workorder_label', $input_label);

    //         if($is_partial == 0) {
    //             $this->db->where('status', '0');
    //         }

    //         $result = $this->db->get()->row_array();

    //         $result['qty'] = $qtyLabel;
    //         $result['is_partial'] = $is_partial == 1 ? 1 : 0;
    //         $result['serial_label'] = null;

    //         echo json_encode([
    //             'title' => 'success',
    //             'total' => $result ? 1 : 0,
    //             'data'  => $result ? [$result] : []
    //         ]);
    //     }
    // }

    public function getChecksheetLabel()
    {
        if (!$this->input->post()) return;

        $input_label = $this->input->post('workorder_label');

        $isSerial = (strpos($input_label, 'RWIN') === 0);
        $field = $isSerial ? 'serial_label' : 'workorder_label';

        $this->db->from('scan_in_finishing');
        $this->db->where($field, $input_label);
        $labels = $this->db->get()->result_array();

        if (!$labels) {
            echo json_encode([
                'title' => 'Not Found',
                'message' => 'Label not found!'
            ]);
            return;
        }

        foreach ($labels as $row) {
            if ($row['type_status'] == 'scanning') {
                echo json_encode([
                    'title' => 'Process Scanned',
                    'message' => 'Label in the process of being scanned in from internal finishing',
                    'data' => $row
                ]);
                return;
            }
        }

        $result = [];

        foreach ($labels as $row) {

            if ($row['status'] == 1) continue;

            $result[] = [
                'item_fg_id'     => $row['item_fg_id'],
                'workorder'      => $row['workorder'],
                'workorder_label'=> $row['workorder_label'],
                'qty'            => (int) $row['qty'],
                'is_partial'     => 0,
                'serial_label'   => $isSerial ? $input_label : null
            ];
        }

        if (empty($result)) {
            echo json_encode([
                'title' => 'Already Scanned',
                'message' => "Label {$input_label} already scanned",
                'theme' => 'error'
            ]);
            return;
        }

        echo json_encode([
            'title' => 'success',
            'total' => count($result),
            'data'  => $result
        ]);
    }


    public function getSummary()
    {
        $records = $this->db
            ->select("
                SUM(a.qty) as qty_total,
                b.number as item_fg_number,
                b.name as item_fg_name,
                b.mpq as qty_packing
            ")
            ->from('scan_in_from_internal_finishing a')
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
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();
        $item_fg_id = $post['item_fg_id'] ?? null;

        if (!$item_fg_id) {
            return $this->jsonResponse(
                'Error',
                'Item FG ID is missing',
                'error'
            );
        }

        $this->db->trans_begin();

        try {

            $label_item = $this->db->query("
                SELECT *
                FROM scan_in_finishing
                WHERE workorder_label = ?
                FOR UPDATE
            ", [$post['workorder_label']])->row();


            if (!$label_item){
                throw new Exception(json_encode([
                    'title'=>'Not Found',
                    'message'=>'Label not found!',
                    'theme'=>'error',
                ]));
            }

            // if ($label_item->status == 1){
            //     throw new Exception(json_encode([
            //         'title'=>'Available',
            //         'message'=>'Label has already been scanned',
            //         'theme'=>'warning',
            //     ]));
            // }

            $session_row = $this->db->select('scan_id, workorder_label')
                ->from('scan_in_from_internal_finishing')
                ->where('type_status', 'scanning')
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

            if(isset($session_row) && $session_row->workorder_label == $post['workorder_label'] && $session_row->scan_id) {
                echo json_encode([
                    "title"   => "Label Already Used",
                    "message" => "This partial label has already been used in the current scan",
                    "theme"   => "error"
                ]);
                return;
            }

            $scanInFinishing = $this->crud->read("scan_in_from_internal_finishing", [], [
                    "workorder_label" => $post['workorder_label'],
                    "is_partial" => 0
                ],
            );

            if(!$scanInFinishing) {
                $qty = $post['qty'] ?? 0;

                $data_to_insert = [
                    'scan_id'         => $scan_id,
                    'workorder'       => $label_item->workorder,
                    'workorder_label' => $label_item->workorder_label,
                    'item_fg_id'      => $label_item->item_fg_id,
                    'qty'             => $qty,
                    'type_status'     => 'scanning',
                    'status'          => 0,
                    'is_partial'      => $post['is_partial']
                ];

                $this->crud->create('scan_in_from_internal_finishing', $data_to_insert);
    
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Failed create scan in from internal finishing');
                }
            } else {
                throw new Exception('Data From Internal Finishing has been Scanning');
            }


            $checkWOLabel = $this->crud->query("
                SELECT workorder_label
                FROM scan_in_finishing
                WHERE workorder_label = '".$post['workorder_label']."'
                AND (status = 0)
                LIMIT 1
            ");

            if($checkWOLabel) {
                $this->crud->update('scan_in_finishing', [
                    'workorder_label' => $post['workorder_label']
                ], [
                    'status' => 1
                ]);
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

            $json=@json_decode($e->getMessage(),true);

            if($json){
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

            $session_row = $this->db->select('scan_id')
                ->from('scan_in_from_internal_finishing')
                ->where('type_status', 'scanning')
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

            $firstRow = $rows[0];
            $isSerial = !empty($firstRow['serial_label']);

            if ($isSerial) {

                $serial_label = $rows[0]['serial_label'];
                $checkSerial = $this->crud->read("scan_in_from_internal_finishing", [], [
                        "serial_label" => $serial_label,
                        "is_partial" => 0
                    ]
                );

                if ($checkSerial) {
                    throw new Exception(json_encode([
                        'title' => 'Already Scanned',
                        'message' => "Serial {$serial_label} already scanned",
                        'theme' => 'error',
                    ]));
                }
            }
            
            foreach ($rows as $row) {

                $workorder_label = $row['workorder_label'];
                $serial_label = $row['serial_label'] ?? null;

                $label_item = $this->db->query("
                    SELECT *
                    FROM scan_in_finishing
                    WHERE workorder_label = ?
                    FOR UPDATE
                ", [$workorder_label])->row();

                if (!$label_item) {
                    throw new Exception(json_encode([
                        'title' => 'Not Found',
                        'message' => "Label {$workorder_label} not found!",
                        'theme' => 'error',
                    ]));
                }

                if (!$isSerial) {
                    $checkScan = $this->crud->read("scan_in_from_internal_finishing", [], [
                            "workorder_label" => $workorder_label,
                            "is_partial" => 0
                        ]
                    );

                    if ($checkScan) {
                        throw new Exception(json_encode([
                            'title' => 'Already Scanned',
                            'message' => "Label {$workorder_label} already scanned",
                            'theme' => 'error',
                        ]));
                    }
                }

                $data_to_insert = [
                    'scan_id'         => $scan_id,
                    'workorder'       => $row['workorder'],
                    'workorder_label' => $workorder_label,
                    'serial_label'    => $isSerial ? $serial_label : null,
                    'item_fg_id'      => $row['item_fg_id'],
                    'qty'             => (int) $row['qty'],
                    'type_status'     => 'scanning',
                    'status'          => 0,
                    'is_partial'      => 0
                ];

                $this->crud->create('scan_in_from_internal_finishing', $data_to_insert);

                if(!$isSerial) {
                    $this->crud->update('scan_in_finishing',[
                        'workorder_label' => $workorder_label
                    ], ['status' => 1],
                    );
                } else {
                    $this->crud->update('scan_in_finishing',[
                        'serial_label' => $serial_label
                    ], ['status' => 1],
                    );
                }

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
                "message" => "Scan In From Internal Finishing data is not completed",
                'theme' => 'error'
            ]);
            return;
        }

        $label = $this->db->get_where('scan_in_finishing', [
            'workorder_label' => $workorder_label,
        ])->row_array();

        $this->db->select("SUM(qty) AS qty_finishing");
        $this->db->from("scan_in_from_internal_finishing");
        $this->db->where('workorder_label', $workorder_label);
        $totalRow = $this->db->get()->row_array();

        $total_existing = (int) ($totalRow['qty_finishing'] ?? 0);

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

        $send = $this->crud->update('scan_in_from_internal_finishing', [
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


    public function saveSummary()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');

        $this->db->trans_begin();

        try {

            foreach ($items as $post) {
                $updateFromInternalFinishing = $this->crud->update('scan_in_from_internal_finishing', [
                        'workorder_label' => $post['workorder_label'],
                        'item_fg_id'      => $post['item_fg_id']
                    ], ['type_status' => 'completed']
                );

                if (!$updateFromInternalFinishing) {
                    throw new Exception("Failed update Scan In From Internal Finishing {$post['workorder_label']}");
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