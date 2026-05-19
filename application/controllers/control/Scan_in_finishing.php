<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_in_finishing extends CI_Controller
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
            $this->load->view('control/scan_in_finishing');
        } else {
            redirect('error_access');
        }
    }

    public function generateDocNo()
    {
        $trans_date = $this->input->post('trans_date');

        $date = $trans_date ? date("Y-m-d", strtotime($trans_date)) : date("Y-m-d");
        $year = date("y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $prefix = "INF/{$year}{$month}{$day}/";

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(doc_no, '/', -1) AS UNSIGNED)) AS kode
            FROM scan_in_finishing
            WHERE doc_no LIKE 'INF/{$year}{$month}%'
        ");
        $row = $sql->row();

        if ($row && $row->kode) {
            $seq = sprintf('%03d', $row->kode + 1);
        } else {
            $seq = '001';
        }

        $autonumber = "{$prefix}{$seq}";

        echo $autonumber;
    }

    public function getScanInFinishing()
    {
        $username = $this->session->username;
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
        $this->db->from("(SELECT 
                            scan_id,
                            item_fg_id,
                            SUM(qty) AS qty_product,
                            COUNT(*) AS qty_label,
                            MIN(workorder) as workorder,
                            MAX(created_date) AS last_created_date
                        FROM scan_in_finishing
                        WHERE type_status = 'scanning'
                        AND created_by = '$username'
                        AND status = 0
                        GROUP BY scan_id, item_fg_id, workorder
                        ) a");
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->order_by('a.last_created_date', 'DESC');

        $records = $this->db->get()->result_array();

        $result['total'] = count($records);
        $result['rows']  = $records;

        echo json_encode($result);
    }

    public function getChecksheetLabelV1()
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
            $this->db->group_by('workorder_label');
            $result1 = $this->db->get()->result_array();

            // if(empty($result1)) {
            //     $this->db->select("a.item_fg_id, a.workorder, a.workorder_label, a.ok_punch as qty_packing");
            //     $this->db->from('internal_process a');
            //     $this->db->join('output_production_press_detail b', 'a.item_fg_id = b.item_fg_id AND a.workorder = b.workorder and a.workorder_label = b.workorder_label');
            //     $this->db->where('a.workorder_label', $workorder_label);
            //     $this->db->where('b.status', '2');
            //     $this->db->where('a.item_fg_id', 'FGRPNA-0207');
            //     // $this->db->group_by('a.workorder_label');
            //     $result1 = $this->db->get()->result_array();
            // }

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

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {

            $input_label = $this->input->post('workorder_label');

            if (strpos($input_label, 'RWIN') === 0) {

                $this->db->select("a.item_fg_id, a.type_status, b.serial_label, b.status");
                $this->db->from("scan_visual_checker_detail a");
                $this->db->join("rework_visual_checker_label b", "a.scan_id = b.scan_id and a.item_fg_id = b.item_fg_id");
                $this->db->where("b.serial_label", $input_label);
                $this->db->limit(1);

                $label = $this->db->get()->row_array();

                if (!$label) {
                    echo json_encode([
                        'title' => 'Not Found',
                        'message' => 'Rework label not found!'
                    ]);
                    return;
                }

                if ($label['type_status'] == 'completed') {
                    echo json_encode([
                        'title' => 'Process Scanned',
                        'message' => 'Label is currently being processed in Visual Checker',
                        'data' => $label
                    ]);
                    return;
                }

                if ($label['status'] == 1) {
                    echo json_encode([
                        'title'   => 'Scanned',
                        'message' => 'Label has already been scanned',
                        'data'    => $label
                    ]);
                    return;
                }

                $this->db->select("a.item_fg_id, c.workorder, b.workorder_label, b.qty as qty_packing, a.serial_label as label");
                $this->db->from('rework_visual_checker_label a');
                $this->db->join('rework_visual_checker_label_lot_tracking b', 'a.serial_label = b.serial_label');
                $this->db->join('scan_visual_checker_detail c', 'a.scan_id = c.scan_id and a.item_fg_id = c.item_fg_id and b.workorder_label = c.workorder_label');
                $this->db->where('a.serial_label', $input_label);
                $this->db->where('a.status', 0);

                $result = $this->db->get()->result_array();

                echo json_encode([
                    'title' => 'success',
                    'total' => count($result),
                    'data'  => $result
                ]);
                return;
            }

            /**
             * PRESS
             */
            $label = $this->db->get_where('output_production_press_detail', [
                'workorder_label' => $input_label
            ])->row_array();

            if (!$label) {
                echo json_encode([
                    'title'   => 'Not Found',
                    'message' => 'Label not found!'
                ]);
                return;
            }

            if ($label['status'] == 1) {
                echo json_encode([
                    'title'   => 'Scanned',
                    'message' => 'Label has already been scanned',
                    'data'    => $label
                ]);
                return;
            }

            $this->db->select("item_fg_id, workorder, workorder_label, qty_packing, workorder_label as label");
            $this->db->from('output_production_press_detail');
            $this->db->where('workorder_label', $input_label);
            $this->db->where('status', 0);
            $this->db->limit(1);

            $result = $this->db->get()->row_array();

            echo json_encode([
                'title' => 'success',
                'total' => $result ? 1 : 0,
                'data'  => $result ? [$result] : []
            ]);
        }
    }


    public function createV1()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        if ($this->form_validation->run() !== TRUE) {
            show_error(validation_errors());
        }

        $post = $this->input->post();
        $username = $this->session->username;
        $item_fg_id = $post['item_fg_id'] ?? null;

        if (!$item_fg_id) {
            return $this->jsonResponse(
                'Error',
                'Item FG ID is missing',
                'error'
            );
        }

        // $this->db->select("a.*")
        //     ->from('output_production_press_detail a')
        //     ->where('a.workorder_label', $post['workorder_label'])
        //     ->where('a.status', '0');
        //     // ->where('a.item_fg_id !=', 'FGRPNA-0207');
        // $label_item = $this->db->get()->row();

        // if (empty($label_item)) {
        //     $this->db->select("a.*")
        //         ->from('internal_process a')
        //         ->join(
        //             'output_production_press_detail b',
        //             'a.item_fg_id = b.item_fg_id 
        //             AND a.workorder = b.workorder 
        //             AND a.workorder_label = b.workorder_label'
        //         )
        //         ->where('a.workorder_label', $post['workorder_label'])
        //         ->where('b.status', '2')
        //         ->where('a.item_fg_id', 'FGRPNA-0207');
        //     $label_item = $this->db->get()->row();
        // }

        // if (empty($label_item)) {
        //     return $this->jsonResponse(
        //         'Not Found',
        //         'Label not found!',
        //         'error'
        //     );
        // }

        // $summary = $this->getOutputPressSummary(
        //     $label_item->item_fg_id,
        //     $label_item->workorder
        // );

        // if (!$summary || ($summary['qty_output'] ?? 0) <= 0) {
        //     return $this->jsonResponse(
        //         'Already Scanned',
        //         'Item has been finished in internal finishing or already delivered to the subcont',
        //         'error'
        //     );
        // }

        // $existing = $this->db->select('id')
        //     ->from('scan_in_finishing')
        //     ->where('workorder_label', $label_item->workorder_label)
        //     ->where('status', 0)
        //     ->get()
        //     ->row();

        // if ($existing) {
        //     return $this->jsonResponse(
        //         'Available',
        //         'Label has already been scanned',
        //         'error'
        //     );
        // }

        $this->db->trans_begin();

        try {

            $label_item = $this->db->query("
                SELECT *
                FROM output_production_press_detail
                WHERE workorder_label = ?
                AND status = 0
                FOR UPDATE
            ", [$post['workorder_label']])->row();

            if (!$label_item) {
                throw new Exception('Label already scanned or not found');
            }

            $session_row = $this->db->select('scan_id, created_by')
                ->from('scan_in_finishing')
                ->where('type_status', 'scanning')
                ->where('created_by', "$username")
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

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

            $this->crud->create('scan_in_finishing', $data_to_insert);

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed create Scan In Finishing');
            }

            // $checkWOLabel = $this->crud->query("
            //     SELECT workorder_label
            //     FROM output_production_press_detail
            //     WHERE workorder_label = '".$post['workorder_label']."'
            //     AND status = 0
            //     LIMIT 1
            // ");

            // if($checkWOLabel) {
            //     $this->crud->update('output_production_press_detail', [
            //         'workorder_label' => $post['workorder_label']
            //     ], [
            //         'status' => 1
            //     ]);
            // }


            $where = [
                'workorder_label'=>$post['workorder_label'],
            ];

            $before = $this->db->get_where('output_production_press_detail',$where)->row_array();

            $update = [
                'status'=>1,
            ];

            $this->db->where('workorder_label', $post['workorder_label']);
            $this->db->where('status',0);
            $this->db->update('output_production_press_detail',[
                'status'=>1
            ]);

            $this->db->insert('logs',[
                'created_by'=>$this->session->username,
                'created_date'=>date('Y-m-d H:i:s'),
                'ip_address'=>$this->input->ip_address(),
                'action'=>'Update Before',
                'menu'=>'output_production_press_detail',
                'description'=>json_encode($before)
            ]);

            $this->db->insert('logs',[
                'created_by'=>$this->session->username,
                'created_date'=>date('Y-m-d H:i:s'),
                'ip_address'=>$this->input->ip_address(),
                'action'=>'Update New',
                'menu'=>'output_production_press_detail',
                'description'=>json_encode($update)
            ]);

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

            return $this->jsonResponse(
                'Error',
                $e->getMessage(),
                'error'
            );
        }
    }

    public function create()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        if ($this->form_validation->run() !== TRUE) {
            show_error(validation_errors());
        }

        $post = $this->input->post();
        $username = $this->session->username;
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
                FROM output_production_press_detail
                WHERE workorder_label = ?
                FOR UPDATE
            ", [$post['workorder_label']])->row();

            // if (!$label_item) {
            //     throw new Exception(json_encode([
            //         'title'=>'Not Found',
            //         'message'=>'Label not found!',
            //         'theme'=>'error',
            //     ]));

            // }

            if (!$label_item){
                throw new Exception(json_encode([
                    'title'=>'Not Found',
                    'message'=>'Label not found!',
                    'theme'=>'error',
                ]));
            }

            if ($label_item->status == 1){
                throw new Exception(json_encode([
                    'title'=>'Available',
                    'message'=>'Label has already been scanned',
                    'theme'=>'warning',
                ]));
            }


            $session_row = $this->db->select('scan_id, created_by')
                ->from('scan_in_finishing')
                ->where('type_status', 'scanning')
                ->where('created_by', "$username")
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

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

            $this->crud->create('scan_in_finishing', $data_to_insert);

            // if ($this->db->affected_rows() == 0) {
            //     throw new Exception(json_encode([
            //         'title'=>'Error',
            //         'message'=>'Scan label failed',
            //         'theme'=>'error',
            //     ]));
            // }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception(json_encode([
                    'title'=>'Error',
                    'message'=>'Failed create Scan In Finishing',
                    'theme'=>'error',
                ]));
            }

            $before = (array)$label_item;

            $update = [
                'workorder_label'=>$post['workorder_label'],
                'status'=>1
            ];

            $this->db->where('workorder_label', $post['workorder_label']);
            $this->db->where('status',0);
            $this->db->update('output_production_press_detail',[
                'status'=>1
            ]);

            if ($this->db->affected_rows() == 0) {
                throw new Exception(json_encode([
                    'title'=>'Available',
                    'message'=>'Label has already been scanned',
                    'theme'=>'error',
                ]));
            }

            $this->db->insert('logs',[
                'created_by'=>$this->session->username,
                'created_date'=>date('Y-m-d H:i:s'),
                'ip_address'=>$this->input->ip_address(),
                'action'=>'Update Before',
                'menu'=>'output_production_press_detail',
                'description'=>json_encode($before)
            ]);

            $this->db->insert('logs',[
                'created_by'=>$this->session->username,
                'created_date'=>date('Y-m-d H:i:s'),
                'ip_address'=>$this->input->ip_address(),
                'action'=>'Update New',
                'menu'=>'output_production_press_detail',
                'description'=>json_encode($update)
            ]);

            if ($this->db->trans_status() === FALSE) {
                throw new Exception(json_encode([
                    'title'=>'Error',
                    'message'=>'Transaction failed',
                    'theme'=>'error',
                ]));
            }

            $this->db->trans_commit();

            return $this->jsonResponse(
                'Success',
                'Data berhasil disimpan',
                'success'
            );

        } 
        
        // catch (Exception $e) {
        //     $this->db->trans_rollback();

        //     return $this->jsonResponse(
        //         'Error',
        //         $e->getMessage(),
        //         'error'
        //     );
        // }

        catch(Exception $e){

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
        $username = $this->session->username;

        $rows = $post['rows'] ?? [];

        if(empty($rows)){
            return $this->jsonResponse(
                'Error',
                'No data to process',
                'error'
            );
        }

        $this->db->trans_begin();

        try {
            $session_row = $this->db->select('scan_id, created_by')
                ->from('scan_in_finishing')
                ->where('type_status', 'scanning')
                ->where('created_by', "$username")
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

            $checkRw = [];

            foreach($rows as $row) {

                // workorder_label / serial_label
                // $label = $row['label'];
                $label = strtoupper(trim($row['label']));
                $is_rw = strpos($label, 'RWIN') === 0;
            
                $labelSource = [
                    [
                        'table' => 'rework_visual_checker_label',
                        'field' => 'serial_label',
                    ],
                    [
                        'table' => 'output_production_press_detail',
                        'field' => 'workorder_label',
                    ]
                ];
                
                $labelItem  = null;
                $labelTable = null;
    
                foreach($labelSource as $source) {
                    $query = $this->db->query("
                        SELECT *
                        FROM {$source['table']}
                        WHERE {$source['field']} = ?
                        FOR UPDATE
                    ", [$label]);
    
                    $checkQuery = $query->row();
    
                    if($checkQuery) {
                        $labelItem  = $checkQuery;
                        $labelTable = $source['table'];
                        break;
                    }
                }
    
                if (!$labelItem){
                    throw new Exception(json_encode([
                        'title'=>'Not Found',
                        'message'=>'Label not found!',
                        'theme'=>'error',
                    ]));
                }
    
    
                if($labelTable == 'rework_visual_checker_label') {
    
                    if(!in_array($label, $checkRw)) {
                        
                        $checkRemaining = $this->db->query("
                            SELECT COUNT(*) as total
                            FROM rework_visual_checker_label_lot_tracking
                            WHERE serial_label = ?
                        ", [$label])->row();
    
                        if($checkRemaining->total == 0) {
                            throw new Exception(json_encode([
                                'title'=>'Available',
                                'message'=>'All label in this Re already scanned',
                                'theme'=>'warning',
                            ]));
                        }
    
                        $checkRw[] = $label;
    
                        $this->crud->update('rework_visual_checker_label', [
                            'serial_label' => $label,
                            'status' => 0
                        ], [
                            'status' => 1
        
                        ]);
                    }
                } else {
                    if ($labelItem->status == 1) {
                        throw new Exception(json_encode([
                            'title'=>'Available',
                            'message'=>'Label has already been scanned',
                            'theme'=>'warning',
                        ]));
                    }
                }
    
                $qty = (int) ($row['qty_packing'] ?? 0);

                if($qty <= 0){
                    throw new Exception(json_encode([
                        'title'=>'Invalid Qty',
                        'message'=>'Qty must be greater than 0',
                        'theme'=>'warning',
                    ]));
                }

                $workorder = isset($labelItem->workorder) 
                ? $labelItem->workorder 
                : ($row['workorder'] ?? null);

                 $workorder_label = isset($labelItem->workorder_label) 
                ? $labelItem->workorder_label 
                : ($row['workorder_label'] ?? null);

    
                $data_to_insert = [
                    'scan_id'         => $scan_id,
                    'workorder'       => $workorder,
                    'workorder_label' => $workorder_label,
                    'serial_label'    => $is_rw ? $label : null,
                    'item_fg_id'      => $labelItem->item_fg_id,
                    'qty'             => $qty,
                    'type_status'     => 'scanning',
                    'status'          => 0
                ];
    
                $this->crud->create('scan_in_finishing', $data_to_insert);
    
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception(json_encode([
                        'title'=>'Error',
                        'message'=>'Failed create Scan In Finishing',
                        'theme'=>'error',
                    ]));
                }
    

                if($labelTable == 'rework_visual_checker_label') {

                    $this->crud->update('rework_visual_checker_label_lot_tracking', [
                        'serial_label' => $label,
                        'workorder_label' => $workorder_label,
                        'status' => 0
                    ], [
                        'status' => 1
    
                    ]);

                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception(json_encode([
                            'title'=>'Error',
                            'message'=>'Transaction failed',
                            'theme'=>'error',
                        ]));
                    }
                } else {
    
                    $this->crud->update('output_production_press_detail', [
                        'workorder_label' => $row['workorder_label'],
                        'status' => 0
                    ], [
                        'status' => 1,
                    ]);
    
                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception(json_encode([
                            'title'=>'Error',
                            'message'=>'Transaction failed',
                            'theme'=>'error',
                        ]));
                    }
                }
            }

            $this->db->trans_commit();

            return $this->jsonResponse(
                'Success',
                'Data berhasil disimpan',
                'success'
            );

        } catch(Exception $e){

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

    // private function getOutputPressSummary($item_fg_id, $workorder)
    // {
    //     $query = "
    //         SELECT
    //             a.item_fg_id,
    //             a.workorder,
    //             MIN(a.trans_date) AS trans_date,
    //             COALESCE(
    //                 CASE 
    //                     WHEN proc.source_value IS NOT NULL THEN 
    //                         (proc.source_value - COALESCE(del.qty_delivery_internal, 0))
    //                     ELSE 
    //                         (SUM(a.qty_ok) - COALESCE(del.qty_delivery_press, 0))
    //                 END, 0
    //             ) AS qty_output
    //         FROM output_production_press a

    //         LEFT JOIN (
    //             SELECT 
    //                 d.item_fg_id,
    //                 d.workorder,
    //                 -- SUM(CASE WHEN d.source_type = 'Output Production Press' THEN d.qty_delivery ELSE 0 END) AS qty_delivery_press,

    //                 SUM(
    //                     CASE 
    //                         WHEN d.source_type IN ('Output Production Press', 'Shipping') 
    //                             THEN d.qty_delivery 
    //                         ELSE 0 
    //                     END
    //                 ) AS qty_delivery_press,

    //                 SUM(CASE WHEN d.source_type = 'Internal Process' THEN d.qty_delivery ELSE 0 END) AS qty_delivery_internal
    //             FROM delivery_to_subconts d
    //             WHERE d.deleted = 0
    //             GROUP BY d.item_fg_id, d.workorder
    //         ) del ON del.item_fg_id = a.item_fg_id AND del.workorder = a.workorder

    //         LEFT JOIN (
    //             SELECT 
    //                 x.item_fg_id,
    //                 x.workorder,
    //                 CASE 
    //                     WHEN MAX(x.process_name) = 'Internal Finishing' THEN SUM(x.external)
    //                     WHEN MAX(x.process_name) = 'Cutting Punch' THEN SUM(x.ok_punch)
    //                     ELSE NULL
    //                 END AS source_value
    //             FROM internal_process x
    //             WHERE x.deleted = 0
    //             GROUP BY x.item_fg_id, x.workorder
    //         ) proc ON proc.item_fg_id = a.item_fg_id AND proc.workorder = a.workorder

    //         WHERE a.item_fg_id = '$item_fg_id' 
    //         AND a.workorder = '$workorder'

    //         GROUP BY a.item_fg_id, a.workorder
    //         HAVING qty_output > 0
    //         LIMIT 1
    //     ";

    //     return $this->db->query($query)->row_array();
    // }

    // public function createDocNoV1()
    // {
    //     if ($this->input->post()) {

    //         $post = $this->input->post();
    //         $items = json_decode($post['items'], true);

    //         if (!$items) {
    //             echo json_encode([
    //                 "title" => "Error",
    //                 "message" => "Items data not received",
    //                 "theme" => "error",
    //             ]);
    //             return;
    //         }

    //         $exists = $this->crud->read('scan_in_finishing', [], [
    //             'doc_no' => $post['doc_no']
    //         ]);

    //         if ($exists) {
    //             echo json_encode([
    //                 "title" => "Error",
    //                 "message"=>"Document No already created",
    //                 "theme"=>"error",
    //             ]);
    //             return;
    //         }

    //         $this->db->trans_begin();

    //         foreach ($items as $row) {

    //             if(empty($post['doc_no']) || empty($post['trans_date'])) {
    //                 echo json_encode([
    //                     "title" => "Error",
    //                     "message"=>"Please fill in all required fields first",
    //                     "theme"=>"error",
    //                 ]);
    //                 return;
    //             }

    //             $update = [
    //                 'doc_no'            => $post['doc_no'],
    //                 'trans_date'        => $post['trans_date'],
    //                 'type_status'       => 'completed',
    //             ];

    //             $updateDocNo = $this->crud->update('scan_in_finishing', [
    //                 'scan_id'           => $row['scan_id'],
    //                 'item_fg_id'        => $row['item_fg_id'],
    //                 'workorder'         => $row['workorder'],
    //             ], $update);

    //             if (!$updateDocNo) {
    //                 $this->db->trans_rollback();
    //                 echo json_encode([
    //                     "theme" => "error",
    //                     "message" => "Document No failed to be updated",
    //                     "title" => "Error"
    //                 ]);
    //                 return;
    //             }
    //         }

    //         if ($this->db->trans_status() === FALSE) {
    //             $this->db->trans_rollback();
    //             echo json_encode(["theme"=>"error","message"=>"Failed to save row"]);
    //             return;
    //         }

    //         $this->db->trans_commit();

    //         echo json_encode([
    //             "theme"=>"success",
    //             "message"=>"Document No created successfully"
    //         ]);
    //     }
    // }

    public function createDocNo()
    {
        if (!$this->input->post()) return;

        $post  = $this->input->post();
        $items = json_decode($post['items'], true);

        if (!$items) {
            echo json_encode([
                "title"=>"Error",
                "message"=>"Items data not received",
                "theme"=>"error"
            ]);
            return;
        }

        if (empty($post['trans_date'])) {
            echo json_encode([
                "title"=>"Error",
                "message"=>"Transaction date required",
                "theme"=>"error"
            ]);
            return;
        }

        $this->db->trans_begin();

        try {

            $date  = date("Y-m-d", strtotime($post['trans_date']));
            $year  = date("y", strtotime($date));
            $month = date("m", strtotime($date));
            $day   = date("d", strtotime($date));

            $prefix = "INF/{$year}{$month}{$day}/";

            $this->db->query("
                LOCK TABLES
                    scan_in_finishing WRITE,
                    logs WRITE
            ");

            $sql = $this->db->query("
                SELECT MAX(CAST(SUBSTRING_INDEX(doc_no, '/', -1) AS UNSIGNED)) AS kode
                FROM scan_in_finishing
                WHERE doc_no LIKE 'INF/{$year}{$month}%'
            ");

            $row  = $sql->row();
            $next = ($row && $row->kode) ? $row->kode + 1 : 1;
            $seq  = sprintf('%03d',$next);

            $doc_no = "{$prefix}{$seq}";

            foreach ($items as $r) {

                $where = [
                    'scan_id'=>$r['scan_id'],
                    'item_fg_id'=>$r['item_fg_id'],
                    'workorder'=>$r['workorder'],
                ];

                $before = $this->db->get_where('scan_in_finishing',$where)->row_array();

                $update = [
                    'doc_no'=>$doc_no,
                    'trans_date'=>$date,
                    'type_status'=>'completed',
                    'updated_by'=>$this->session->username,
                    'updated_date'=>date('Y-m-d H:i:s')
                ];

                $this->db->where($where);
                $ok = $this->db->update('scan_in_finishing',$update);

                if (!$ok) {
                    throw new Exception("Create Document No failed");
                }

                $this->db->insert('logs',[
                    'created_by'=>$this->session->username,
                    'created_date'=>date('Y-m-d H:i:s'),
                    'ip_address'=>$this->input->ip_address(),
                    'action'=>'Update Before',
                    'menu'=>'scan_in_finishing',
                    'description'=>json_encode($before)
                ]);

                $this->db->insert('logs',[
                    'created_by'=>$this->session->username,
                    'created_date'=>date('Y-m-d H:i:s'),
                    'ip_address'=>$this->input->ip_address(),
                    'action'=>'Update New',
                    'menu'=>'scan_in_finishing',
                    'description'=>json_encode($update)
                ]);

                // sleep(5);
            }

            $this->db->query("UNLOCK TABLES");

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction failed");
            }

            $this->db->trans_commit();

            echo json_encode([
                "theme"=> "success",
                "message"=>"Document No created successfully",
                "doc_no"=>$doc_no
            ]);

        } catch (Exception $e) {

            $this->db->query("UNLOCK TABLES");
            $this->db->trans_rollback();

            echo json_encode([
                "theme"=>"error",
                "message"=>$e->getMessage(),
                "title"=>"Error"
            ]);
        }
    }


    public function createDocNoForGetLock()
    {
        if (!$this->input->post()) return;

        $post  = $this->input->post();
        $items = json_decode($post['items'], true);

        if (!$items) {
            echo json_encode([
                "title"=>"Error",
                "message"=>"Items data not received",
                "theme"=>"error"
            ]);
            return;
        }

        if (empty($post['trans_date'])) {
            echo json_encode([
                "title"=>"Error",
                "message"=>"Transaction date required",
                "theme"=>"error"
            ]);
            return;
        }

        $date  = date("Y-m-d", strtotime($post['trans_date']));
        $year  = date("y", strtotime($date));
        $month = date("m", strtotime($date));
        $day   = date("d", strtotime($date));

        $lockName = "doc_no_INF_{$year}{$month}";
        $lock = $this->db->query("SELECT GET_LOCK('{$lockName}', 10) AS l")->row();

        if (!$lock || $lock->l != 1) {
            echo json_encode([
                "theme"=>"error",
                "message"=>"System busy, please try again",
                "title"=>"Error"
            ]);
            return;
        }

        // sleep(15);

        $this->db->trans_begin();

        try {

            $prefix = "INF/{$year}{$month}{$day}/";
            $prefixMonth = "INF/{$year}{$month}";

            $sql = $this->db->query("
                SELECT MAX(CAST(SUBSTRING_INDEX(doc_no, '/', -1) AS UNSIGNED)) AS kode
                FROM scan_in_finishing
                WHERE doc_no LIKE '{$prefixMonth}%'
            ");

            $row  = $sql->row();
            $next = ($row && $row->kode) ? $row->kode + 1 : 1;
            $seq  = sprintf('%03d',$next);

            $doc_no = "{$prefix}{$seq}";

            foreach ($items as $r) {

                $where = [
                    'scan_id'=>$r['scan_id'],
                    'item_fg_id'=>$r['item_fg_id'],
                    'workorder'=>$r['workorder'],
                ];

                $update = [
                    'doc_no'=>$doc_no,
                    'trans_date'=>$date,
                    'type_status'=>'completed'
                ];

                $ok = $this->crud->update('scan_in_finishing', $where, $update);
                if (!$ok) {
                    throw new Exception("Create Document No failed");
                }

                // sleep(5);
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction failed");
            }

            $this->db->trans_commit();
            $this->db->query("SELECT RELEASE_LOCK('{$lockName}')");

            echo json_encode([
                "theme"=> "success",
                "message"=>"Document No created successfully",
                "doc_no"=>$doc_no
            ]);

        } catch (Exception $e) {

            $this->db->trans_rollback();
            $this->db->query("SELECT RELEASE_LOCK('{$lockName}')");
            
            echo json_encode([
                "theme"=>"error",
                "message"=>$e->getMessage(),
                "title"=>"Error"
            ]);
        }
    }

    public function createDocNoForUpdate()
    {
        if (!$this->input->post()) return;

        $post  = $this->input->post();
        $items = json_decode($post['items'], true);

        if (!$items) {
            echo json_encode([
                "title"=>"Error",
                "message"=>"Items data not received",
                "theme"=>"error"
            ]);
            return;
        }

        if (empty($post['trans_date'])) {
            echo json_encode([
                "title"=>"Error",
                "message"=>"Transaction date required",
                "theme"=>"error"
            ]);
            return;
        }

        $this->db->trans_begin();

        try {

            $date  = date("Y-m-d", strtotime($post['trans_date']));
            $year  = date("y", strtotime($date));
            $month = date("m", strtotime($date));
            $day   = date("d", strtotime($date));

            $prefix = "INF/{$year}{$month}{$day}/";

            $sql = $this->db->query("
                SELECT MAX(CAST(SUBSTRING_INDEX(doc_no, '/', -1) AS UNSIGNED)) AS kode
                FROM scan_in_finishing
                WHERE doc_no LIKE 'INF/{$year}{$month}%'
                FOR UPDATE
            ");

            $row  = $sql->row();
            $next = ($row && $row->kode) ? $row->kode + 1 : 1;
            $seq  = sprintf('%03d',$next);

            $doc_no = "{$prefix}{$seq}";

            foreach ($items as $r) {

                $where = [
                    'scan_id'=>$r['scan_id'],
                    'item_fg_id'=>$r['item_fg_id'],
                    'workorder'=>$r['workorder'],
                ];

                $update = [
                    'doc_no'=>$doc_no,
                    'trans_date'=>$date,
                    'type_status'=>'completed'
                ];

                $ok = $this->crud->update('scan_in_finishing', $where, $update);
                if (!$ok) {
                    throw new Exception("Create Document No failed");
                }

                // sleep(5);
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Transaction failed");
            }

            $this->db->trans_commit();

            echo json_encode([
                "theme"=> "success",
                "message"=>"Document No created successfully",
                "doc_no"=>$doc_no
            ]);

        } catch (Exception $e) {

            $this->db->query("UNLOCK TABLES");
            $this->db->trans_rollback();

            echo json_encode([
                "theme"=>"error",
                "message"=>$e->getMessage(),
                "title"=>"Error"
            ]);
        }
    }

}