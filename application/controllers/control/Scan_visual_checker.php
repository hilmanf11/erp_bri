<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scan_visual_checker extends CI_Controller
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
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/scan_visual_checker');
        } else {
            redirect('error_access');
        }
    }

    public function getDataMasterNg()
    {
        $data = $this->db->select("id, code, name")
                        ->from("master_ng")
                        ->where("deleted", 0)
                        ->order_by("code", "ASC")
                        ->get()
                        ->result();

        echo json_encode([
            "total" => count($data),
            "rows" => $data
        ]);
    }

    public function getNGByDetail($detail_id)
    {
        $data = $this->db
            ->select("ng_code as code, qty_ng")
            ->from("scan_visual_checker_ng")
            ->where("detail_id", $detail_id)
            ->get()
            ->result();

        echo json_encode($data);
    }

    public function getScanVisualChecker()
    {
        $username = $this->session->username;
        $this->db->select('
            a.*,
            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom,
            c.check_date,
            c.inspector,
            c.customer_id,
            c.visual_process,
            d.name as customer_name,
        ');

        $this->db->from('scan_visual_checker_detail a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('scan_visual_checker c', 'a.scan_id = c.scan_id');
        $this->db->join('customers d', 'c.customer_id = d.id', 'left');
        $this->db->where('a.created_by', "$username");
        // $this->db->where('a.type_status', 'scanning');
        $this->db->where_in('a.type_status', ['scanning', 'completed']);
        $this->db->where('a.status', 0);
        $this->db->order_by('a.created_date', 'DESC');

        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => count($records),
            'rows'  => $records
        ]);
    }

    private function _getTableConfigs()
    {
        return [
            [
                'key' => 'FNS',
                'table' => 'scan_in_from_internal_finishing',
                'field' => 'workorder_label',
                'prefix' => null,

                'multi_status_check' => false
            ],
            [
                'key' => 'FNS_RW',
                'table' => 'scan_in_from_internal_finishing',
                'field' => 'serial_label',
                'prefix' => 'RWIN',

                'multi_status_check' => true,
                'tracker' => 'RWIN'
            ],
            [
                'key' => 'SCTF',
                'table' => 'scan_incoming_sctf',
                'field' => 'workorder_label',
                'prefix' => null,

                'multi_status_check' => false
            ],
            [
                'key' => 'SCTF_RW',
                'table' => 'scan_incoming_sctf',
                'field' => 'serial_label',
                'prefix' => 'RW',

                'multi_status_check' => true,
                'tracker' => 'RW'
            ],
            [
                'key' => 'RETURN',
                'table' => 'scan_in_return',
                'field' => 'serial_label',
                'prefix' => 'RT',

                'multi_status_check' => true,
                'tracker' => 'RT'
            ]
        ];
    }

    private function _getConfigMap()
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        foreach ($this->_getTableConfigs() as $config) {
            $map[$config['key']] = $config;
        }

        return $map;
    }

    public function getChecksheetLabelV2()
    {
        if (!$this->input->post()) {
            return;
        }

        $input_label = $this->input->post('workorder_label');

        $tableConfigs = [
            [
                'table' => 'scan_in_from_internal_finishing',
                'field' => 'workorder_label',
                'prefix' => null
            ],
            [
                'table' => 'scan_in_from_internal_finishing',
                'field' => 'serial_label',
                'prefix' => 'RWIN'
            ],
            [
                'table' => 'scan_incoming_sctf',
                'field' => 'workorder_label',
                'prefix' => null
            ],
            [
                'table' => 'scan_incoming_sctf',
                'field' => 'serial_label',
                'prefix' => 'RW'
            ],
            [
                'table' => 'scan_in_return',
                'field' => 'serial_label',
                'prefix' => 'RT'
            ],
        ];

        $foundTable = null;
        $foundField = null;
        $label = null;

        foreach ($tableConfigs as $config) {
            if ($config['prefix'] !== null) {
                if (strpos($input_label, $config['prefix']) !== 0) {
                    continue;
                }
            }

            $this->db->where($config['field'], $input_label);
            $this->db->where('type_status', 'completed');

            if ($config['table'] == 'scan_incoming_sctf') {
                $this->db->where('incoming_type !=', 'BPM');
            }

            $label = $this->db->get($config['table'])->row_array();

            if ($label) {
                $foundTable = $config['table'];
                $foundField = $config['field'];
                break;
            }
        }

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

        if (
            $foundTable == 'scan_in_return' || 
            ($foundTable == 'scan_in_from_internal_finishing' && $foundField == 'serial_label') ||
            ($foundTable == 'scan_incoming_sctf' && $foundField == 'serial_label')
        ) {

            $this->db->select("item_fg_id, workorder, workorder_label, qty, {$foundField} as label");
            $this->db->from($foundTable);
            $this->db->where($foundField, $input_label);
            $this->db->where('status', '0');

            $result = $this->db->get()->result_array();

            echo json_encode([
                'title' => 'success',
                'total' => count($result),
                'data'  => $result
            ]);

        } else {

            $this->db->select("item_fg_id, workorder, workorder_label, qty, {$foundField} as label");
            $this->db->from($foundTable);
            $this->db->where($foundField, $input_label);
            $this->db->where('status', '0');
            $this->db->limit(1);

            $result = $this->db->get()->row_array();

            echo json_encode([
                'title' => 'success',
                'total' => $result ? 1 : 0,
                'data'  => $result ? [$result] : []
            ]);

        }

    }

    public function getChecksheetLabel()
    {
        if (!$this->input->post()) {
            return;
        }

        $input_label = $this->input->post('workorder_label');
        $tableConfigs = $this->_getTableConfigs();

        $label = null;
        $foundConfig = null;

        foreach ($tableConfigs as $config) {

            if ($config['prefix'] !== null && strpos($input_label, $config['prefix']) !== 0) {
                continue;
            }

            $this->db->where($config['field'], $input_label);
            $this->db->where('type_status', 'completed');

            if ($config['table'] == 'scan_incoming_sctf') {
                $this->db->where('incoming_type !=', 'BPM');
            }

            $row = $this->db->get($config['table'])->row_array();

            if ($row) {
                $label = $row;
                $foundConfig = $config;
                break;
            }
        }

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

        $isMulti =
            $foundConfig['table'] == 'scan_in_return' ||
            (
                $foundConfig['table'] == 'scan_in_from_internal_finishing' &&
                $foundConfig['field'] == 'serial_label'
            ) ||
            (
                $foundConfig['table'] == 'scan_incoming_sctf' &&
                $foundConfig['field'] == 'serial_label'
            );

        $this->db->select("item_fg_id, workorder, workorder_label, qty, {$foundConfig['field']} as label");
        $this->db->from($foundConfig['table']);
        $this->db->where($foundConfig['field'], $input_label);
        $this->db->where('status', '0');
        $this->db->where('type_status', 'completed');

        if ($foundConfig['table'] == 'scan_incoming_sctf') {
            $this->db->where('incoming_type !=', 'BPM');
        }

        if ($isMulti) {
            $result = $this->db->get()->result_array();
            foreach ($result as &$row) {
                $row['config_key'] = $foundConfig['key'];
            }

            echo json_encode([
                'title' => 'success',
                'total' => count($result),
                'data'  => $result
            ]);

        } else {

            $result = $this->db->limit(1)->get()->row_array();
            $result['config_key'] = $foundConfig['key'];

            echo json_encode([
                'title' => 'success',
                'total' => $result ? 1 : 0,
                'data'  => $result ? [$result] : []
            ]);
        }
    }

    public function getSummaryVc()
    {
        $username = $this->session->username;

        $this->db->select("
            a.scan_id,
            SUM(b.qty_ok) as total_qty_ok,
            SUM(b.qty_return) as total_qty_return,
            SUM(b.qty_rework) as total_qty_rework,
            FLOOR(SUM(b.qty_ok) / c.box_sub) as total_label_rfg,

            MOD(SUM(b.qty_ok), c.box_sub) as return_ok,
            SUM(b.qty_return) as return_original,
            (MOD(SUM(b.qty_ok), c.box_sub) + SUM(b.qty_return)) as total_qty_return,

            MIN(b.is_print_rfg) as is_print_rfg,
            MIN(b.is_print_return) as is_print_return,
            MIN(b.is_print_rework) as is_print_rework,

            GROUP_CONCAT(DISTINCT b.compound_lot_no SEPARATOR '\n') as compound_lot_no,
            c.id as item_fg_id,
            c.box_sub as std_packing,
            c.number as item_fg_number,
            c.name as item_fg_name,
        ");
        $this->db->from('scan_visual_checker a');
        $this->db->join('scan_visual_checker_detail b', 'a.id = b.visual_checker_id and a.scan_id = b.scan_id');
        $this->db->join('item_fg c', 'b.item_fg_id = c.id', 'left');
        $this->db->where('b.type_status', 'completed');
        $this->db->where('b.created_by', $username);
        $this->db->where('a.status', 0);

        $this->db->order_by('c.number', 'ASC');
        // $this->db->order_by('b.workorder_label','ASC');
        $this->db->group_by(['a.scan_id', 'b.item_fg_id']);

        $records = $this->db->get()->result_array();

        echo json_encode([
            "total" => count($records),
            "rows"  => $records
        ]);
    }

    public function setPrintRfg()
    {
        $scan_id = $this->input->post('scan_id');
        $item_fg_id = $this->input->post('item_fg_id');
        $username   = $this->session->username;

        $this->db->where('scan_id', $scan_id);
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('created_by', $username);
        $this->db->where('type_status', 'completed');
        $this->db->where('is_print_rfg', 0);

        $this->db->update('scan_visual_checker_detail', [
            'is_print_rfg' => 1
        ]);

        echo json_encode([
            'status'   => true,
            'affected' => $this->db->affected_rows()
        ]);
    }


    public function setPrintReturn()
    {
        $scan_id = $this->input->post('scan_id');
        $item_fg_id = $this->input->post('item_fg_id');
        $username   = $this->session->username;

        $this->db->where('scan_id', $scan_id);
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('created_by', $username);
        $this->db->where('type_status', 'completed');
        $this->db->where('is_print_return', 0);

        $this->db->update('scan_visual_checker_detail', [
            'is_print_return' => 1
        ]);

        echo json_encode([
            'status'   => true,
            'affected' => $this->db->affected_rows()
        ]);
    }


    public function setPrintRework()
    {
        $scan_id = $this->input->post('scan_id');
        $item_fg_id = $this->input->post('item_fg_id');
        $username   = $this->session->username;

        $this->db->where('scan_id', $scan_id);
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('created_by', $username);
        $this->db->where('type_status', 'completed');
        $this->db->where('is_print_rework', 0);

        $this->db->update('scan_visual_checker_detail', [
            'is_print_rework' => 1
        ]);

        echo json_encode([
            'status'   => true,
            'affected' => $this->db->affected_rows()
        ]);
    }


    public function create_bulk_v2()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();
        $username = $this->session->username;

        $rows = $post['rows'] ?? [];

        if (empty($rows)) {
            return $this->jsonResponse(
                'Error',
                'No data to process!',
                'error'
            );
        }

        $this->db->trans_begin();

        try {

            $session_row = $this->db->select('scan_id, created_by')
                ->from('scan_visual_checker')
                ->where('type_status', 'scanning')
                ->where('created_by', $username)
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

            $data_header = [
                'scan_id'         => $scan_id,
                'check_date'      => $post['check_date'],
                'inspector'       => $post['inspector'],
                'customer_id'     => $post['customer'],
                'visual_process'  => $post['visual_process'],
                'type_status'     => 'scanning',
                'status'          => 0
            ];

            $checkHeader = $this->db->get_where('scan_visual_checker', [
                'scan_id' => $scan_id
            ])->row_array();

            if (!$checkHeader) {
                $this->crud->create('scan_visual_checker', $data_header);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Failed create header');
                }

                $checkHeader = $this->db->get_where('scan_visual_checker', [
                    'scan_id' => $scan_id
                ])->row_array();
            }

            $checkedRT = [];
            $checkedRWIN = [];
            $checkedRW = [];

            foreach ($rows as $row) {

                $label = $row['label']; //workorder_label / serial_label

                $label_sources = [
                    [
                        'table' => 'scan_in_from_internal_finishing',
                        'field' => 'workorder_label'
                    ],
                    [
                        'table' => 'scan_in_from_internal_finishing',
                        'field' => 'serial_label'
                    ],
                    [
                        'table' => 'scan_incoming_sctf',
                        'field' => 'workorder_label'
                    ],
                    [
                        'table' => 'scan_incoming_sctf',
                        'field' => 'serial_label'
                    ],
                    [
                        'table' => 'scan_in_return',
                        'field' => 'serial_label'
                    ]
                ];

                $label_item = null;
                $label_table = null;
                $label_field = null;

                foreach ($label_sources as $src) {
                    $query = $this->db->query("
                        SELECT *
                        FROM {$src['table']}
                        WHERE {$src['field']} = ?
                        AND type_status = ?
                        FOR UPDATE
                    ", [$label, 'completed']);

                    $check = $query->row();

                    if ($check) {
                        $label_item  = $check;
                        $label_table = $src['table'];
                        $label_field = $src['field'];
                        break;
                    }
                }

                $workorder_label = ($label_field == 'workorder_label') ? $label : $row['workorder_label'];
                $serial_label    = ($label_field == 'serial_label') ? $label : null;

                if (!$label_item) {
                    throw new Exception(json_encode([
                        'title'=>'Not Found',
                        'message'=>'Label not found!',
                        'theme'=>'error',
                    ]));
                }

                if ($label_table == 'scan_in_return') {

                    if (!in_array($label, $checkedRT)) {

                        $checkRemaining = $this->db->query("
                            SELECT COUNT(*) as total
                            FROM scan_in_return
                            WHERE serial_label = ?
                            AND type_status = ?
                            AND status = 0
                        ", [$label, 'completed'])->row();

                        if ($checkRemaining->total == 0) {
                            throw new Exception(json_encode([
                                'title'=>'Available',
                                'message'=>'Label has already been scanned',
                                'theme'=>'warning',
                            ]));
                        }

                        $checkedRT[] = $label;
                    }

                } else if(
                    $label_table == 'scan_in_from_internal_finishing' && 
                    $label_field == 'serial_label'
                ) {

                    if (!in_array($label, $checkedRWIN)) {

                        $checkRemainingRWIN = $this->db->query("
                            SELECT COUNT(*) as total
                            FROM scan_in_from_internal_finishing
                            WHERE serial_label = ?
                            AND type_status = ?
                            AND status = 0
                        ", [$label, 'completed'])->row();

                        if ($checkRemainingRWIN->total == 0) {
                            throw new Exception(json_encode([
                                'title'=>'Available',
                                'message'=>'Label has already been scanned',
                                'theme'=>'warning',
                            ]));
                        }

                        $checkedRWIN[] = $label;
                    }
                } else if(
                    $label_table == 'scan_incoming_sctf' &&
                    $label_field == 'serial_label'
                ) {
                    if(!in_array($label, $checkedRW)) {

                        $checkRemainingRW = $this->db->query("
                            SELECT COUNT(*) as total
                            FROM scan_incoming_sctf
                            WHERE serial_label = ?
                            AND type_status = ?
                            AND status = 0
                        ", [$label, 'completed'])->row();

                        if($checkRemainingRW->total == 0) {
                            throw new Exception(json_encode([
                                'title'=>'Available',
                                'message'=>'Label has already been scanned',
                                'theme'=>'warning',
                            ]));
                        }

                    }
                } else {

                    if ($label_item->status == 1) {
                        throw new Exception(json_encode([
                            'title'=>'Available',
                            'message'=>'Label has already been scanned',
                            'theme'=>'warning',
                        ]));
                    }
                }

                $source = 'INTERNAL';
                $delivery_note_no = '';

                if ($label_table === 'scan_incoming_sctf' && $label_field == 'workorder_label') {

                    $checkSubTF = $this->db->query("
                        SELECT 
                            COALESCE(b.number, c.number) AS incoming_from_number,
                            a.delivery_note_no
                        FROM scan_incoming_sctf a
                        LEFT JOIN subconts b ON b.id = a.incoming_from
                        LEFT JOIN teaching_factory c ON c.id = a.incoming_from
                        WHERE a.workorder_label = ?
                        LIMIT 1
                    ", [$label])->row();

                    if ($checkSubTF) {
                        $source = $checkSubTF->incoming_from_number ?? 'INTERNAL';
                        $delivery_note_no = $checkSubTF->delivery_note_no ?? '';
                    }
                }

                if($label_table === 'scan_incoming_sctf' && $label_field == 'serial_label') {

                    $checkSubTF = $this->db->query("
                        SELECT 
                            COALESCE(b.number, c.number) AS incoming_from_number,
                            a.delivery_note_no
                        FROM scan_incoming_sctf a
                        LEFT JOIN subconts b ON b.id = a.incoming_from
                        LEFT JOIN teaching_factory c ON c.id = a.incoming_from
                        WHERE a.serial_label = ?
                        LIMIT 1
                    ", [$label])->row();

                    if ($checkSubTF) {
                        $source = $checkSubTF->incoming_from_number ?? 'INTERNAL';
                        $delivery_note_no = $checkSubTF->delivery_note_no ?? '';
                    }
                }

                $prevData = null;

                if (
                    $label_table == 'scan_in_return' || 
                    ($label_table == 'scan_in_from_internal_finishing' && $label_field == 'serial_label') ||
                    ($label_table == 'scan_incoming_sctf' && $label_field == 'serial_label')
                ) {

                    $prevData = $this->db->select('operator_finishing, compound_lot_no, source, delivery_note_no')
                        ->from('scan_visual_checker_detail')
                        ->where('workorder_label', $row['workorder_label'])
                        ->where('type_status', 'finished')
                        // ->where('status', 1)
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row();

                    $source = $prevData ? $prevData->source : 'INTERNAL';
                }

                $data_to_insert = [
                    'scan_id'            => $scan_id,
                    'visual_checker_id'  => $checkHeader['id'],
                    'item_fg_id'         => $row['item_fg_id'],
                    'workorder'          => $row['workorder'],

                    'workorder_label'    => $workorder_label,
                    'serial_label'       => $serial_label,

                    'operator_finishing' => $prevData->operator_finishing ?? null,
                    'compound_lot_no'    => $prevData->compound_lot_no ?? null,
                    'delivery_note_no'   => $prevData ? $prevData->delivery_note_no : $delivery_note_no,

                    'source'             => $source,
                    'qty_on_label'       => $row['qty'],
                    'type_status'        => 'scanning',
                    'status'             => 0
                ];

                $this->crud->create('scan_visual_checker_detail', $data_to_insert);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Failed insert detail');
                }

                if (
                    $label_table != 'scan_in_return' && 
                    !($label_table == 'scan_in_from_internal_finishing' && $label_field == 'serial_label') &&
                    !($label_table == 'scan_incoming_sctf' && $label_field == 'serial_label')
                ) {
                    $this->db->where($label_field, $label);
                    $this->db->update($label_table, ['status' => 1]);

                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception('Failed update status');
                    }
                }
            }

            if(!empty($checkedRT)) {

                foreach ($checkedRT as $rt_label) {
    
                    $this->db->where('serial_label', $rt_label);
                    $this->db->update('scan_in_return', ['status' => 1]);

                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception('Failed update RT status');
                    }

                }
            }

            if(!empty($checkedRWIN)) {

                foreach ($checkedRWIN as $rwin_label) {
    
                    $this->db->where('serial_label', $rwin_label);
                    $this->db->update('scan_in_from_internal_finishing', ['status' => 1]);

                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception('Failed update RWIN status');
                    }
                }
            }

            if(!empty($checkedRW)) {

                foreach ($checkedRW as $rw_label) {
    
                    $this->db->where('serial_label', $rw_label);
                    $this->db->update('scan_incoming_sctf', ['status' => 1]);

                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception('Failed update RW status');
                    }
                }
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

    public function create_bulk()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();
        $username = $this->session->username;

        $rows = $post['rows'] ?? [];

        if (empty($rows)) {
            return $this->jsonResponse(
                'Error',
                'No data to process!',
                'error'
            );
        }

        $this->db->trans_begin();

        try {

            $session_row = $this->db->select('scan_id, created_by')
                ->from('scan_visual_checker')
                ->where('type_status', 'scanning')
                ->where('created_by', $username)
                ->where('status', 0)
                ->limit(1)->get()->row();

            $scan_id = $session_row->scan_id ?? $this->generate_uuid();

            $data_header = [
                'scan_id'         => $scan_id,
                'check_date'      => $post['check_date'],
                'inspector'       => $post['inspector'],
                'customer_id'     => $post['customer'],
                'visual_process'  => $post['visual_process'],
                'type_status'     => 'scanning',
                'status'          => 0
            ];

            $checkHeader = $this->db->get_where('scan_visual_checker', [
                'scan_id' => $scan_id
            ])->row_array();

            if (!$checkHeader) {
                $this->crud->create('scan_visual_checker', $data_header);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Failed create header');
                }

                $checkHeader = $this->db->get_where('scan_visual_checker', [
                    'scan_id' => $scan_id
                ])->row_array();
            }


            $trackers = [
                'RT' => [
                    'labels' => [],
                    'table'  => 'scan_in_return'
                ],
                'RWIN' => [
                    'labels' => [],
                    'table'  => 'scan_in_from_internal_finishing'
                ],
                'RW' => [
                    'labels' => [],
                    'table'  => 'scan_incoming_sctf'
                ]
            ];

            foreach ($rows as $row) {

                $label = $row['label']; //workorder_label / serial_label

                $configKey = $row['config_key'] ?? null;
                $config = $this->_getConfigMap()[$configKey] ?? null;

                if (!$config) {
                    throw new Exception(json_encode([
                        'title'   => 'Error',
                        'message' => 'Invalid data',
                        'theme'   => 'error',
                    ]));
                }

                // $query = $this->db->query("
                //     SELECT *
                //     FROM {$config['table']}
                //     WHERE {$config['field']} = ?
                //     AND type_status = ?
                //     FOR UPDATE
                // ", [$label, 'completed']);

                // $label_item = $query->row();

                $sql = "
                    SELECT *
                    FROM {$config['table']}
                    WHERE {$config['field']} = ?
                    AND type_status = ?
                ";

                $params = [$label, 'completed'];

                if ($config['table'] === 'scan_incoming_sctf') {
                    $sql .= " AND incoming_type != ?";
                    $params[] = 'BPM';
                }

                $sql .= " FOR UPDATE";

                $query = $this->db->query($sql, $params);

                $label_item = $query->row();
                $label_table = $config['table'];
                $label_field = $config['field'];

                $workorder_label = ($label_field == 'workorder_label') ? $label : $row['workorder_label'];
                $serial_label    = ($label_field == 'serial_label') ? $label : null;

                if (!$label_item) {
                    throw new Exception(json_encode([
                        'title'=>'Not Found',
                        'message'=>'Label not found!',
                        'theme'=>'error',
                    ]));
                }

                $isMulti = !empty($config['multi_status_check']);

                if ($isMulti) {

                    $checkedList = &$trackers[$config['tracker']]['labels'];
                    if (!in_array($label, $checkedList)) {

                        $checkRemaining = $this->db->query("
                            SELECT COUNT(*) as total
                            FROM {$config['table']}
                            WHERE {$config['field']} = ?
                            AND type_status = ?
                        ", [$label, 'completed'])->row();

                        if ($checkRemaining->total == 0) {

                            throw new Exception(json_encode([
                                'title'   => 'Available',
                                'message' => 'Label has already been scanned',
                                'theme'   => 'warning',
                            ]));
                        }

                        $checkedList[] = $label;
                    }

                } else {

                    if ($label_item->status == 1) {

                        throw new Exception(json_encode([
                            'title'   => 'Available',
                            'message' => 'Label has already been scanned',
                            'theme'   => 'warning',
                        ]));
                    }
                }

                $source = 'INTERNAL';
                $delivery_note_no = '';

                if ($label_table === 'scan_incoming_sctf') {

                    $checkSubTF = $this->db->query("
                        SELECT
                            COALESCE(b.number, c.number) AS incoming_from_number,
                            a.delivery_note_no
                        FROM scan_incoming_sctf a
                        LEFT JOIN subconts b ON b.id = a.incoming_from
                        LEFT JOIN teaching_factory c ON c.id = a.incoming_from
                        WHERE a.{$label_field} = ?
                        AND a.incoming_type != 'BPM'
                        AND a.status = 0
                        LIMIT 1
                        ", [$label])->row();

                    if ($checkSubTF) {
                        $source = $checkSubTF->incoming_from_number ?? 'INTERNAL';
                        $delivery_note_no = $checkSubTF->delivery_note_no ?? '';
                    }
                }

                $prevData = null;

                if ($isMulti) {

                    $prevData = $this->db->select('operator_finishing, compound_lot_no, source, delivery_note_no')
                        ->from('scan_visual_checker_detail')
                        ->where('workorder_label', $row['workorder_label'])
                        ->where('type_status', 'finished')
                        ->order_by('id', 'DESC')
                        ->limit(1)->get()->row();

                    $source = $prevData ? $prevData->source : 'INTERNAL';
                }

                $data_to_insert = [
                    'scan_id'            => $scan_id,
                    'visual_checker_id'  => $checkHeader['id'],
                    'item_fg_id'         => $row['item_fg_id'],
                    'workorder'          => $row['workorder'],
                    'workorder_label'    => $workorder_label,
                    'serial_label'       => $serial_label,
                    'operator_finishing' => $prevData->operator_finishing ?? null,
                    'compound_lot_no'    => $prevData->compound_lot_no ?? null,
                    'delivery_note_no'   => $prevData ? $prevData->delivery_note_no : $delivery_note_no,
                    'source'             => $source,
                    'qty_on_label'       => $row['qty'],
                    'type_status'        => 'scanning',
                    'status'             => 0
                ];

                $this->crud->create('scan_visual_checker_detail', $data_to_insert);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Failed insert detail');
                }

                if (!$isMulti) {
                    // $this->db->where($label_field, $label);
                    // $this->db->update($label_table, ['status' => 1]);

                    $this->crud->update($label_table, [
                        $label_field => $label,
                        'status'     => 0
                    ], [
                        'status' => 1
                    ]);


                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception('Failed update status');
                    }
                }
            }

            foreach ($trackers as $tracker) {

                if (empty($tracker['labels'])) {
                    continue;
                }

                foreach ($tracker['labels'] as $label) {

                    // $this->db->where('serial_label', $label);
                    // $this->db->update($tracker['table'], ['status' => 1]);

                    $this->crud->update($tracker['table'], [
                        'serial_label' => $label,
                        'status'     => 0
                    ], [
                        'status' => 1
                    ]);

                    if ($this->db->trans_status() === FALSE) {
                        throw new Exception('Failed update label status');
                    }
                }
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

    public function updateQty()
    {
        $scan_id            = $this->input->post('scan_id');
        $item_fg_id         = $this->input->post('item_fg_id');
        $workorder          = $this->input->post('workorder');
        $workorder_label    = $this->input->post('workorder_label');

        $operator_finishing = $this->input->post('operator_finishing');
        $compound_lot_no    = $this->input->post('compound_lot_no');

        $qty_actual         = $this->input->post('qty_actual');
        $qty_deviation      = $this->input->post('qty_deviation');
        $qty_ok             = $this->input->post('qty_ok');
        $qty_rework         = $this->input->post('qty_rework');
        $qty_ng_total       = $this->input->post('total_ng');
        $qty_return         = $this->input->post('qty_return');


        if (
            !$item_fg_id || !$workorder || !$workorder_label || !$operator_finishing || !$compound_lot_no || !$qty_actual || !$qty_ok
        ) {
            echo json_encode([
                'title' => 'Invalid Data',
                "message" => "Data is not completed",
                'theme' => 'error'
            ]);
            return;
        }

        $send = $this->crud->update('scan_visual_checker_detail', [
            'scan_id' => $scan_id,
            'item_fg_id' => $item_fg_id,
            'workorder' => $workorder,
            'workorder_label' => $workorder_label,
        ], [
            'operator_finishing' => $operator_finishing,
            'compound_lot_no' => $compound_lot_no,
            'qty_actual' => $qty_actual,
            'qty_deviation' => $qty_deviation,
            'qty_ok' => $qty_ok,
            'qty_rework' => $qty_rework,
            'qty_ng_total' => $qty_ng_total,
            'qty_return' => $qty_return,
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

    public function saveNGDetail()
    {
        $detail_id = $this->input->post('detail_id');
        $ng_input  = json_decode($this->input->post('ng'),true);

        if(!$detail_id){
            echo json_encode(["status"=>"error","msg"=>"no detail id"]);
            return;
        }

        $existing = $this->db
            ->where('detail_id',$detail_id)
            ->get('scan_visual_checker_ng')
            ->result_array();

        $mapExisting=[];
        foreach($existing as $e){
            $mapExisting[$e['ng_code']]=$e;
        }

        foreach($ng_input as $code => $qty){
            $qty=intval($qty);

            if(isset($mapExisting[$code])){
                $oldQty=intval($mapExisting[$code]['qty_ng']);

                if($oldQty!==$qty){
                    if($qty > 0){

                        $this->crud->update('scan_visual_checker_ng', [
                            'id' => $mapExisting[$code]['id']
                        ], [
                            'qty_ng' => $qty
                        ]);

                    }else{

                        $this->crud->delete('scan_visual_checker_ng', [
                            'id' => $mapExisting[$code]['id']
                        ]);
                    }
                }

            }else{

                if($qty > 0){

                    $this->crud->create('scan_visual_checker_ng', [
                        'detail_id'=>$detail_id,
                        'ng_code'=>$code,
                        'qty_ng'=>$qty
                    ]);

                }
            }
        }

        foreach($mapExisting as $code=>$row){

            if(!isset($ng_input[$code])){

                $this->crud->delete('scan_visual_checker_ng', [
                    'id' => $row['id']
                ]);
            }
        }

        echo json_encode(["status"=>"success"]);
    }

    public function completeScanVc()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');
        $this->db->trans_begin();

        try {
            $first = $items[0];

            $parent_id = $first['parent_id'];
            $scan_id   = $first['scan_id'];

            $updateHeader = $this->crud->update('scan_visual_checker', [
                    'id'      => $parent_id,
                    'scan_id' => $scan_id,
                ], ['type_status' => 'completed']
            );

            if (!$updateHeader) {
                throw new Exception("Failed update scan visual checker header");
            }

            foreach ($items as $post) {
                $updateSvcDetail = $this->crud->update('scan_visual_checker_detail', [
                        'id'       => $post['detail_id'],
                        'scan_id'  => $post['scan_id'],
                    ], ['type_status' => 'completed']
                );

                if (!$updateSvcDetail) {
                    throw new Exception("Failed complete scan visual checker detail");
                }
            }

            $this->db->trans_commit();

            echo json_encode([
                "title"   => "Success",
                "message" => "Data completed successfully",
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

    private function generateSerialLabel($prefix)
    {
        while(true){

            $sql = "
                SELECT serial_label
                FROM fg_visual_checker_label
                WHERE serial_label LIKE ?
                ORDER BY serial_label DESC
                LIMIT 1
                FOR UPDATE
            ";

            $last = $this->db->query($sql, [$prefix.'%'])->row();

            $sequence = 1;

            if($last){
                $sequence = intval(substr($last->serial_label, -4)) + 1;
            }

            $serial = $prefix . sprintf("%04d",$sequence);

            $check = $this->db
                ->where('serial_label',$serial)
                ->get('fg_visual_checker_label')
                ->row();

            if(!$check){
                return $serial;
            }
        }
    }


    private function generateSerialLabelReturn($prefix)
    {
        while(true){

            $sql = "
                SELECT serial_label
                FROM return_visual_checker_label
                WHERE serial_label LIKE ?
                ORDER BY serial_label DESC
                LIMIT 1
                FOR UPDATE
            ";

            $last = $this->db->query($sql, [$prefix.'%'])->row();

            $sequence = 1;

            if($last){
                $sequence = intval(substr($last->serial_label, -4)) + 1;
            }

            $serial = $prefix . sprintf("%04d",$sequence);

            $check = $this->db
                ->where('serial_label',$serial)
                ->get('return_visual_checker_label')
                ->row();

            if(!$check){
                return $serial;
            }
        }
    }


    private function generateSerialLabelRework($prefix)
    {
        while(true){

            $sql = "
                SELECT serial_label
                FROM rework_visual_checker_label
                WHERE serial_label LIKE ?
                ORDER BY serial_label DESC
                LIMIT 1
                FOR UPDATE
            ";

            $last = $this->db->query($sql, [$prefix.'%'])->row();

            $sequence = 1;

            if($last){
                $sequence = intval(substr($last->serial_label, -4)) + 1;
            }

            $serial = $prefix . sprintf("%04d",$sequence);

            $check = $this->db
                ->where('serial_label',$serial)
                ->get('rework_visual_checker_label')
                ->row();

            if(!$check){
                return $serial;
            }
        }
    }


    public function print_label_rfg($item_fg_id, $scan_id) 
    {
        $item_fg_id = base64_decode($item_fg_id);
        $scan_id = base64_decode($scan_id);
        
        $username = $this->session->username;

        if(empty($item_fg_id)){
            show_error("Missing parameter",400);
        }
        
        $this->db->trans_begin();

        $this->db->select("
            a.scan_id,
            a.item_fg_id,
            a.qty_ok AS qty_packing,
            a.compound_lot_no,
            a.workorder_label,

            b.check_date AS trans_date,

            c.number AS product_no,
            c.name AS product_name,
            c.uom,
            c.box_sub as std_packing,

            e.number,

            g.trans_date as prod_date,

            h.name AS qc,
        ");

        $this->db->from("scan_visual_checker_detail a");
        $this->db->join("scan_visual_checker b","b.id = a.visual_checker_id", "left");
        $this->db->join("item_fg c","c.id = a.item_fg_id", "left");
        $this->db->join("bom d", "a.item_fg_id = d.item_fg_id and d.priority = 1", "left");
        $this->db->join("item_rm e", "d.item_rm_id = e.id", "left");
        $this->db->join("output_production_press_detail f", 
            "f.workorder_label = a.workorder_label 
            and f.workorder = a.workorder
        ");
        $this->db->join("output_production_press g", 
            "g.number = f.number_output 
            and g.workorder = f.workorder
        ");
        $this->db->join("man_powers h", "b.inspector = h.nik");

        $this->db->where("a.scan_id", $scan_id);
        $this->db->where("a.item_fg_id", $item_fg_id);
        $this->db->where("a.created_by", "$username");
        $this->db->where("a.type_status", "completed");
        $this->db->where("a.deleted", 0);

        $this->db->order_by("a.workorder_label","ASC");

        $label_packing_details = $this->db->get()->result();
        
        if (empty($label_packing_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }

        usort($label_packing_details, function($a,$b){
            return $b->qty_packing - $a->qty_packing;
        });

        $std_packing = $label_packing_details[0]->std_packing;
        $detail = $label_packing_details[0];


        $labels = [];
        $std = $std_packing;

        # STEP 1 pisahkan full packing
        $remaining_rows = [];
        foreach($label_packing_details as $row){

            while($row->qty_packing >= $std){

                $labels[] = [
                    'qty'=>$std,
                    'rows'=>[
                        [
                            'qty'=>$std,
                            'lot'=>$row->compound_lot_no,
                            'wo'=>$row->workorder_label,
                            'qc'=>$row->qc
                        ]
                    ]
                ];

                $row->qty_packing -= $std;
            }

            if($row->qty_packing > 0){
                $remaining_rows[] = $row;
            }
        }

        # STEP 2 group by lot
        $lot_groups = [];
        foreach($remaining_rows as $row){
            $lot_groups[$row->compound_lot_no][] = $row;
        }

        # STEP 3 proses tiap lot
        foreach($lot_groups as $lot => &$rows){

            usort($rows,function($a,$b){
                return $b->qty_packing - $a->qty_packing;
            });

            foreach($rows as $row){

                if($row->qty_packing == 0) continue;

                $current = [
                    'qty'=>0,
                    'rows'=>[]
                ];

                $need = $std;

                # ambil dari row utama
                $take = min($row->qty_packing,$need);

                $current['rows'][]=[
                    'qty'=>$take,
                    'lot'=>$row->compound_lot_no,
                    'wo'=>$row->workorder_label,
                    'qc'=>$row->qc
                ];

                $current['qty'] += $take;
                $row->qty_packing -= $take;
                $need -= $take;

                # cari topping dari lot yang sama qty terkecil
                if($need > 0){

                    usort($rows,function($a,$b){
                        return $a->qty_packing - $b->qty_packing;
                    });

                    foreach($rows as $r){

                        if($r->qty_packing == 0) continue;

                        $take = min($r->qty_packing,$need);

                        $current['rows'][]=[
                            'qty'=>$take,
                            'lot'=>$r->compound_lot_no,
                            'wo'=>$r->workorder_label,
                            'qc'=>$r->qc
                        ];

                        $current['qty'] += $take;
                        $r->qty_packing -= $take;
                        $need -= $take;

                        if($need == 0) break;
                    }
                }

                # jika masih kurang ambil dari lot lain
                if($need > 0){

                    foreach($lot_groups as $lot2 => &$rows2){

                        if($lot2 == $lot) continue;

                        usort($rows2,function($a,$b){
                            return $a->qty_packing - $b->qty_packing;
                        });

                        foreach($rows2 as $r){

                            if($r->qty_packing == 0) continue;

                            $take = min($r->qty_packing,$need);

                            $current['rows'][]=[
                                'qty'=>$take,
                                'lot'=>$r->compound_lot_no,
                                'wo'=>$r->workorder_label,
                                'qc'=>$r->qc
                            ];

                            $current['qty'] += $take;
                            $r->qty_packing -= $take;
                            $need -= $take;

                            if($need == 0) break;
                        }

                        if($need == 0) break;
                    }
                }

                if($current['qty'] > 0){
                    $labels[] = $current;
                }
            }
        }

        $html = '<html>
                    <head>
                        <title>Label Packing - '.$detail->item_fg_id.'</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }
                            table { 
                                border-collapse: collapse; 
                                width: 7.5cm; 
                                height: 8cm; 
                                font-size: 11px;
                                border: 2px solid black; 
                                table-layout: fixed; 
                            }
                            th, td { 
                                border: 1px solid black; 
                                padding: 2px; 
                                text-align: left; 
                            }
                            th { 
                                text-align: center; 
                                font-size: 14px; 
                                font-weight: bold; 
                            }
                            .header { 
                                text-align: center; 
                                font-size: 15px; 
                                font-weight: bold; 
                            }
                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding: 3px; 
                            }
                            .operator-sign, 
                            .qc-sign, 
                            .qr-code { 
                                font-size: 12px; 
                                text-align: center; 
                                height: 20mm; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 20mm; 
                            }
                            .qr-code img { 
                                width: 60px; 
                                height: 60px; 
                                display: block; 
                                margin: 0 auto; 
                            }
                            .serial-label { 
                                font-size: 11px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                    size: 7.5cm 8cm;
                                    margin: 0;
                            }
                            @media print {
                                    .printLabel {
                                        page-break-after: always;
                                        width: 7.5cm;
                                        height: 8cm;
                                        display: block;
                                        padding: 0mm;
                                        margin: 0;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 12px;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    body {
                                        margin: 0;
                                        padding: 0;
                                    }
                                }
                        </style>
                    </head>
                <body>';


        $today = date('Y-m-d');
        $lot_label_counter = [];
        
        $date_prefix = date('ymd');
        $prefix = $date_prefix . $item_fg_id;

        foreach($labels as $label){

            $lot_counter = [];
            $lot_wo_counter = [];
            $qc = '';

            foreach($label['rows'] as $r){

                if(!isset($lot_counter[$r['lot']])){
                    $lot_counter[$r['lot']] = 0;
                }
                $lot_counter[$r['lot']] += $r['qty'];

                if(!isset($lot_wo_counter[$r['lot']])){
                    $lot_wo_counter[$r['lot']] = [];
                }

                if(!isset($lot_wo_counter[$r['lot']][$r['wo']])){
                    $lot_wo_counter[$r['lot']][$r['wo']] = 0;
                }

                $lot_wo_counter[$r['lot']][$r['wo']] += $r['qty'];

                $qc = $r['qc'];
            }

            if($label['qty'] < $std){

                foreach($lot_wo_counter as $lot => $wo_rows){

                    foreach($wo_rows as $wo => $qty){
                        $exists = $this->db
                            ->where('scan_id', $scan_id)
                            ->where('item_fg_id', $item_fg_id)
                            ->where('workorder_label', $wo)
                            ->where('compound_lot_no', $lot)
                            ->get('fg_visual_checker_label_lot_balance')
                            ->row();

                        if($exists) {
                            continue;
                        }

                        $tracking = [
                            'scan_id'             => $scan_id,
                            'source_serial_label' => NULL,
                            'item_fg_id'          => $item_fg_id,
                            'workorder_label'     => $wo,
                            'compound_lot_no'     => $lot,
                            'qty_remaining'       => $qty,
                        ];

                        $this->crud->create('fg_visual_checker_label_lot_balance', $tracking);
                    }
                }

                // tidak membuat label baru
                continue;
            }

            arsort($lot_counter);

            $dominant_lot = array_key_first($lot_counter);

            if(!isset($lot_label_counter[$dominant_lot])){
                $lot_label_counter[$dominant_lot] = 1;
            }else{
                $lot_label_counter[$dominant_lot]++;
            }

            $lot_index = $lot_label_counter[$dominant_lot];

            $this->db->where('scan_id', $detail->scan_id);
            $this->db->where('item_fg_id', $item_fg_id);
            $this->db->where('compound_lot_no', $dominant_lot);
            $this->db->where('prod_date', $detail->prod_date);
            $this->db->limit(1, $lot_index - 1);
            $existing_label = $this->db->get('fg_visual_checker_label')->row();


            if($existing_label){

                $serial_label = $existing_label->serial_label;
                $today = $existing_label->pack_date;

            }else{

                // $serial_label = $prefix . sprintf("%04d",$sequence);
                $serial_label = $this->generateSerialLabel($prefix);



                $insert = [
                    'scan_id' => $detail->scan_id,
                    'item_fg_id' => $item_fg_id,
                    'prod_date' => $detail->prod_date,
                    'pack_date' => $today,
                    'qty' => $label['qty'],
                    'compound_lot_no' => $dominant_lot,
                    'serial_label' => $serial_label,
                    'status' => 0
                ];

                $this->crud->create('fg_visual_checker_label', $insert);

                foreach($lot_wo_counter as $lot => $wo_rows){

                    foreach($wo_rows as $wo => $qty){

                        $tracking = [
                            'serial_label' => $serial_label,
                            'workorder_label' => $wo,
                            'compound_lot_no' => $lot,
                            'qty' => $qty
                        ];

                        $this->crud->create('fg_visual_checker_label_lot_tracking', $tracking);
                    }
                }

                $this->createQrcode($serial_label, "assets/image/qrcode/");

            }

            $qty_packing_formatted = number_format($label['qty'], 0, ',', '.') . ' ' . strtoupper($detail->uom);

            $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                <span class="header" style="font-size: 20px; height: 20px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->number . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $today . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $dominant_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $serial_label . '.png') . '"/>
                                <div class="serial-label">' . $serial_label . '</div>
                            </td>
                        </tr>
                    </table>
            </div>';
        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
        }else{
            $this->db->trans_commit();
        }

        $html .= '<script>window.print()</script>
                </body>
            </html>';

        die($html);
    }


    public function print_label_return($item_fg_id, $scan_id) 
    {
        $item_fg_id = base64_decode($item_fg_id);
        $scan_id = base64_decode($scan_id);
        
        $username = $this->session->username;

        if(empty($item_fg_id) || empty($scan_id)){
            show_error("Missing parameter", 400);
        }

        $this->db->trans_begin();

        $this->db->select("
            a.scan_id,
            a.item_fg_id,
            a.qty_return AS qty_packing,
            a.compound_lot_no,
            a.workorder_label,

            c.number AS product_no,
            c.name AS product_name,
            c.uom,

            e.number,
            g.trans_date as prod_date,
            h.name AS qc
        ");

        $this->db->from("scan_visual_checker_detail a");
        $this->db->join("scan_visual_checker b","b.id = a.visual_checker_id", "left");
        $this->db->join("item_fg c","c.id = a.item_fg_id", "left");
        $this->db->join("bom d", "a.item_fg_id = d.item_fg_id and d.priority = 1", "left");
        $this->db->join("item_rm e", "d.item_rm_id = e.id", "left");
        $this->db->join("output_production_press_detail f", 
            "f.workorder_label = a.workorder_label 
            and f.workorder = a.workorder
        ");
        $this->db->join("output_production_press g", 
            "g.number = f.number_output 
            and g.workorder = f.workorder
        ");
        $this->db->join("man_powers h", "b.inspector = h.nik");

        $this->db->where("a.scan_id", $scan_id);
        $this->db->where("a.item_fg_id", $item_fg_id);
        $this->db->where("a.created_by", $username);
        $this->db->where("a.qty_return >",0);
        $this->db->where("a.type_status", "completed");
        $this->db->where("a.deleted", 0);

        $rows = $this->db->get()->result();

        $tracking = [];

        foreach ($rows as $row) {

            $lot = trim($row->compound_lot_no);
            $wo  = trim($row->workorder_label);
            $qty = (int)$row->qty_packing;

            if (!isset($tracking[$lot])) {
                $tracking[$lot] = [];
            }

            if (!isset($tracking[$lot][$wo])) {
                $tracking[$lot][$wo] = [];
            }

            $tracking[$lot][$wo][] = [
                'scan_id' => $row->scan_id,
                'qty' => $qty
            ];
        }

        $this->db->select("
            compound_lot_no,
            workorder_label,
            qty_remaining
        ");

        $this->db->from("fg_visual_checker_label_lot_balance");

        $this->db->where("scan_id", $scan_id);
        $this->db->where("item_fg_id", $item_fg_id);
        $this->db->where("qty_remaining >", 0);

        $balance_rows = $this->db->get()->result();

        $rows_ok_ref = [];

        if (empty($balance_rows)) {

            $this->db->select("
                a.scan_id,
                a.item_fg_id,
                a.qty_ok,
                a.compound_lot_no,
                a.workorder_label,

                c.number AS product_no,
                c.name AS product_name,
                c.uom,
                c.box_sub as std_packing,

                e.number,
                g.trans_date as prod_date,
                h.name AS qc
            ");

            $this->db->from("scan_visual_checker_detail a");
            $this->db->join("scan_visual_checker b","b.id = a.visual_checker_id", "left");
            $this->db->join("item_fg c","c.id = a.item_fg_id", "left");
            $this->db->join("bom d", "a.item_fg_id = d.item_fg_id and d.priority = 1", "left");
            $this->db->join("item_rm e", "d.item_rm_id = e.id", "left");
            $this->db->join("output_production_press_detail f", 
                "f.workorder_label = a.workorder_label 
                and f.workorder = a.workorder
            ");
            $this->db->join("output_production_press g", 
                "g.number = f.number_output 
                and g.workorder = f.workorder
            ");
            $this->db->join("man_powers h", "b.inspector = h.nik");

            $this->db->where("a.scan_id", $scan_id);
            $this->db->where("a.item_fg_id", $item_fg_id);
            $this->db->where("a.created_by", $username);
            $this->db->where("a.type_status", "completed");
            $this->db->where("a.deleted", 0);

            $rows_ok = $this->db->get()->result();
            $rows_ok_ref = $rows_ok;

            foreach ($rows_ok as $row_ok) {

                $std = (int)$row_ok->std_packing;
                $qty_ok = (int)$row_ok->qty_ok;

                if ($std <= 0) continue;

                $sisa = $qty_ok % $std;

                if ($sisa > 0) {

                    $this->db->where('scan_id', $scan_id);
                    $this->db->where('item_fg_id', $row_ok->item_fg_id);
                    $this->db->where('workorder_label', $row_ok->workorder_label);
                    $this->db->where('compound_lot_no', $row_ok->compound_lot_no);

                    $exist = $this->db->get('fg_visual_checker_label_lot_balance')->row();

                    if (!$exist) {
                        $this->crud->create('fg_visual_checker_label_lot_balance', [
                            'scan_id' => $scan_id,
                            'source_serial_label' => NULL,
                            'item_fg_id' => $row_ok->item_fg_id,
                            'workorder_label' => $row_ok->workorder_label,
                            'compound_lot_no' => $row_ok->compound_lot_no,
                            'qty_remaining' => $sisa,
                            'status' => 0
                        ]);
                    }
                }
            }

            $this->db->select("
                compound_lot_no,
                workorder_label,
                qty_remaining
            ");

            $this->db->from("fg_visual_checker_label_lot_balance");
            $this->db->where("scan_id", $scan_id);
            $this->db->where("item_fg_id", $item_fg_id);
            $this->db->where("qty_remaining >", 0);

            $balance_rows = $this->db->get()->result();
        }

        if (empty($rows) && empty($balance_rows)) {
            show_error("No data available for label", 400);
        }

        foreach ($balance_rows as $row) {

            $lot = trim($row->compound_lot_no);
            $wo  = trim($row->workorder_label);
            $qty = (int)$row->qty_remaining;

            if (!isset($tracking[$lot])) {
                $tracking[$lot] = [];
            }

            if (!isset($tracking[$lot][$wo])) {
                $tracking[$lot][$wo] = [];
            }

            $tracking[$lot][$wo][] = [
                'scan_id' => $scan_id,
                'qty' => $qty
            ];
        }


        $total_qty = 0;
        $lot_counter = [];

        foreach ($tracking as $lot => $wo_rows){

            foreach ($wo_rows as $wo => $entries){

                foreach ($entries as $entry){

                    $qty = (int)$entry['qty'];

                    $total_qty += $qty;

                    if (!isset($lot_counter[$lot])){
                        $lot_counter[$lot] = 0;
                    }

                    $lot_counter[$lot] += $qty;
                }
            }
        }

        if ($total_qty <= 0) {
            show_error("Total qty is zero", 400);
        }

        arsort($lot_counter);
        $dominant_lot = array_key_first($lot_counter);


        if (!empty($rows)) {

            $detail = $rows[0];

        } elseif (!empty($rows_ok_ref)) {

            $detail = $rows_ok_ref[0];

        } elseif (!empty($balance_rows)) {

            $this->db->select("
                a.scan_id,
                a.item_fg_id,
                c.number AS product_no,
                c.name AS product_name,
                c.uom,
                e.number,
                g.trans_date as prod_date,
                h.name AS qc
            ");

            $this->db->from("scan_visual_checker_detail a");
            $this->db->join("scan_visual_checker b","b.id = a.visual_checker_id", "left");
            $this->db->join("item_fg c","c.id = a.item_fg_id", "left");
            $this->db->join("bom d", "a.item_fg_id = d.item_fg_id and d.priority = 1", "left");
            $this->db->join("item_rm e", "d.item_rm_id = e.id", "left");
            $this->db->join("output_production_press_detail f", 
                "f.workorder_label = a.workorder_label 
                and f.workorder = a.workorder
            ");
            $this->db->join("output_production_press g", 
                "g.number = f.number_output 
                and g.workorder = f.workorder
            ");
            $this->db->join("man_powers h", "b.inspector = h.nik");

            $this->db->where("a.scan_id", $scan_id);
            $this->db->where("a.item_fg_id", $item_fg_id);
            $this->db->where("a.created_by", $username);

            $detail = $this->db->get()->row();

        } else {

            $detail = new stdClass();
            $detail->product_no = $detail->product_no ?? '';
            $detail->product_name = $detail->product_name ?? '';
            $detail->uom = $detail->uom ?? '';
            $detail->number = $detail->number ?? '';
            $detail->qc = $detail->qc ?? '-';
            $detail->prod_date = $detail->prod_date ?? date('Y-m-d');
        }


        $tracking_grouped = [];

        foreach ($tracking as $lot => $wo_rows){
            foreach ($wo_rows as $wo => $entries){
                foreach ($entries as $entry){

                    $scan = $entry['scan_id'];
                    $qty  = (int)$entry['qty'];

                    if (!isset($tracking_grouped[$scan])) {
                        $tracking_grouped[$scan] = [];
                    }

                    if (!isset($tracking_grouped[$scan][$lot])) {
                        $tracking_grouped[$scan][$lot] = [];
                    }

                    if (!isset($tracking_grouped[$scan][$lot][$wo])) {
                        $tracking_grouped[$scan][$lot][$wo] = 0;
                    }

                    $tracking_grouped[$scan][$lot][$wo] += $qty;
                }
            }
        }


        $prefix = "RT" . date('ymd') . $item_fg_id;

        $this->db->where('scan_id', $scan_id);
        $this->db->where('item_fg_id', $item_fg_id);
        $existing = $this->db->get('return_visual_checker_label')->row();


        if ($existing){
            $serial_label = $existing->serial_label;
        } else {

            $serial_label = $this->generateSerialLabelReturn($prefix);

            $this->crud->create('return_visual_checker_label', [
                'scan_id' => $scan_id,
                'item_fg_id' => $item_fg_id,
                'prod_date' => $detail->prod_date,
                'pack_date' => date('Y-m-d'),
                'qty' => $total_qty,
                'compound_lot_no' => $dominant_lot,
                'serial_label' => $serial_label,
                'status' => 0
            ]);

            foreach ($tracking_grouped as $scan => $lot_rows){
                foreach ($lot_rows as $lot => $wo_rows){
                    foreach ($wo_rows as $wo => $qty){

                        $this->crud->create('return_visual_checker_label_lot_tracking', [
                            'serial_label' => $serial_label,
                            'scan_id' => $scan,
                            'workorder_label' => $wo,
                            'compound_lot_no' => $lot,
                            'qty' => $qty
                        ]);
                    }
                }
            }

            foreach ($tracking_grouped as $scan => $lot_rows){
                foreach ($lot_rows as $lot => $wo_rows){
                    foreach ($wo_rows as $wo => $qty){

                        $this->db->where('scan_id', $scan);
                        $this->db->where('item_fg_id', $item_fg_id);
                        $this->db->where('compound_lot_no', $lot);
                        $this->db->where('workorder_label', $wo);
                        $this->db->where('qty_remaining >', 0);

                        $this->db->group_start();
                        $this->db->where('source_serial_label IS NULL', null, false);
                        $this->db->or_where('source_serial_label', '');
                        $this->db->group_end();

                        $this->db->update('fg_visual_checker_label_lot_balance', [
                            'source_serial_label' => $serial_label
                        ]);
                    }
                }
            }

            $this->createQrcode($serial_label, "assets/image/qrcode/");
        }

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            echo "FAILED";
            return;
        } else {
            $this->db->trans_commit();
        }

        $today = date('Y-m-d');
        $qc = $detail->qc;
        $qty_packing_formatted = number_format($total_qty, 0, ',', '.') . ' ' . strtoupper($detail->uom);

        $detail->item_fg_id = $detail->item_fg_id ?? $item_fg_id;

        $html = '<html>
                    <head>
                        <title>Label Return - '.$detail->item_fg_id.'</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }
                            table { 
                                border-collapse: collapse; 
                                width: 7.5cm; 
                                height: 8cm; 
                                font-size: 11px;
                                border: 2px solid black; 
                                table-layout: fixed; 
                            }
                            th, td { 
                                border: 1px solid black; 
                                padding: 2px; 
                                text-align: left; 
                            }
                            th { 
                                text-align: center; 
                                font-size: 14px;
                                font-weight: bold; 
                            }
                            .header { 
                                text-align: center; 
                                font-size: 15px; 
                                font-weight: bold; 
                            }
                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding: 3px; 
                            }
                            .operator-sign, 
                            .qc-sign, 
                            .qr-code { 
                                font-size: 12px; 
                                text-align: center; 
                                height: 20mm; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 20mm; 
                            }
                            .qr-code img { 
                                width: 60px; 
                                height: 60px; 
                                display: block; 
                                margin: 0 auto; 
                            }
                            .serial-label { 
                                font-size: 11px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                size: 7.5cm 8cm;
                                margin: 0;
                            }
                            @media print {
                                .printLabel {
                                    page-break-after: always;
                                    width: 7.5cm;
                                    height: 8cm;
                                    display: block;
                                    padding: 0mm;
                                    margin: 0;
                                }

                                table {
                                    width: 100%;
                                    font-size: 12px;
                                    margin: 0;
                                    padding: 0;
                                }

                                body {
                                    margin: 0;
                                    padding: 0;
                                }
                            }
                        </style>
                    </head>
                <body>';


        $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                <span class="header" style="font-size: 20px; height: 20px;">RETURN</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->number . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $today . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $dominant_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $serial_label . '.png') . '"/>
                                <div class="serial-label">' . $serial_label . '</div>
                            </td>
                        </tr>
                    </table>
            </div>';

        $html .= '<script>window.print()</script></body></html>';

        die($html);
    }


    public function print_label_rework($item_fg_id) 
    {
        $item_fg_id = base64_decode($item_fg_id);
        
        $username = $this->session->username;

        if(empty($item_fg_id)){
            show_error("Missing parameter", 400);
        }

        $this->db->trans_begin();

        $this->db->select("
            a.scan_id,
            a.item_fg_id,
            a.qty_rework AS qty_packing,
            a.compound_lot_no,
            a.workorder_label,
            b.check_date AS trans_date,
            c.number AS product_no,
            c.name AS product_name,
            c.uom,
            e.number,
            g.trans_date as prod_date,
            h.name AS qc,
            
            i.id as subcont_id,
            j.id as tf_id,

            COALESCE(i.id, j.id) as source_id,

            a.source,
            a.delivery_note_no
        ");

        $this->db->from("scan_visual_checker_detail a");
        $this->db->join("scan_visual_checker b","b.id = a.visual_checker_id", "left");
        $this->db->join("item_fg c","c.id = a.item_fg_id", "left");
        $this->db->join("bom d", "a.item_fg_id = d.item_fg_id and d.priority = 1", "left");
        $this->db->join("item_rm e", "d.item_rm_id = e.id", "left");
        $this->db->join("output_production_press_detail f", 
            "f.workorder_label = a.workorder_label 
            and f.workorder = a.workorder
        ");
        $this->db->join("output_production_press g", 
            "g.number = f.number_output 
            and g.workorder = f.workorder
        ");

        $this->db->join("man_powers h", "b.inspector = h.nik");
        $this->db->join("subconts i", "a.source = i.number", "left");
        $this->db->join("teaching_factory j", "a.source = j.number", "left");

        $this->db->where("a.item_fg_id", $item_fg_id);
        $this->db->where("a.created_by", $username);
        $this->db->where("a.qty_rework >",0);
        $this->db->where("a.type_status", "completed");
        $this->db->where("a.deleted", 0);

        $this->db->group_by([
            'a.scan_id',
            'a.compound_lot_no',
            'a.workorder_label',
            'a.source'
        ]);

        $rows = $this->db->get()->result();

        if(empty($rows)){
            echo "<center><h3>Data not found</h3></center>";
            return;
        }

        
        // GROUP DATA BY SOURCE

        $group_source = [];

        foreach($rows as $row){

            $key = $row->source ? $row->source : 'IN';

            if(!isset($group_source[$key])){
                $group_source[$key] = [];
            }

            $group_source[$key][] = $row;
        }

        // HTML HEADER

        $html = '<html>
                    <head>
                        <title>Label Rework - </title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }
                            table { 
                                border-collapse: collapse; 
                                width: 7.5cm; 
                                height: 8cm; 
                                font-size: 11px;
                                border: 2px solid black; 
                                table-layout: fixed; 
                            }
                            th, td { 
                                border: 1px solid black; 
                                padding: 2px; 
                                text-align: left; 
                            }
                            th { 
                                text-align: center; 
                                font-size: 11px; // 14px;
                                font-weight: bold; 
                            }
                            .header { 
                                text-align: center; 
                                font-size: 15px; 
                                font-weight: bold; 
                            }
                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding: 3px; 
                            }
                            .operator-sign, 
                            .qc-sign, 
                            .qr-code { 
                                font-size: 12px; 
                                text-align: center; 
                                height: 20mm; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 20mm; 
                            }
                            .qr-code img { 
                                width: 60px; 
                                height: 60px; 
                                display: block; 
                                margin: 0 auto; 
                            }
                            .serial-label { 
                                font-size: 11px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                size: 7.5cm 8cm;
                                margin: 0;
                            }
                            @media print {
                                .printLabel {
                                    page-break-after: always;
                                    width: 7.5cm;
                                    height: 8cm;
                                    display: block;
                                    padding: 0mm;
                                    margin: 0;
                                }

                                table {
                                    width: 100%;
                                    font-size: 11px; // 12px;
                                    margin: 0;
                                    padding: 0;
                                }

                                body {
                                    margin: 0;
                                    padding: 0;
                                }
                            }
                        </style>
                    </head>
                <body>';

        $today = date('Y-m-d');
        $date_prefix = date('ymd');

        // LOOP PER SOURCE

        foreach($group_source as $source_code => $rows){

            $total_qty = 0;
            $lot_counter = [];
            $lot_wo_counter = [];
            $qc = '';

            foreach($rows as $row){

                $total_qty += $row->qty_packing;
                if(!isset($lot_counter[$row->compound_lot_no])){
                    $lot_counter[$row->compound_lot_no] = 0;
                }

                $lot_counter[$row->compound_lot_no] += $row->qty_packing;
                if(!isset($lot_wo_counter[$row->compound_lot_no])){
                    $lot_wo_counter[$row->compound_lot_no] = [];
                }

                if(!isset($lot_wo_counter[$row->compound_lot_no][$row->workorder_label])){
                    $lot_wo_counter[$row->compound_lot_no][$row->workorder_label] = 0;
                }

                $lot_wo_counter[$row->compound_lot_no][$row->workorder_label] += $row->qty_packing;
                $qc = $row->qc;
            }

            arsort($lot_counter);
            $dominant_lot = array_key_first($lot_counter);

            $detail = $rows[0];

            $source = $detail->source_id ? $source_code : "IN";
            $source_idx = $this->buildSourcePrefix($detail->subcont_id,$detail->tf_id);
            $prefix = "RW".$source_idx.$date_prefix.$item_fg_id;

            // CHECK LABEL EXIST

            $this->db->where('scan_id',$detail->scan_id);
            $this->db->where('item_fg_id',$item_fg_id);
            $this->db->where('compound_lot_no',$dominant_lot);
            $this->db->where('source', $source);

            $existing = $this->db->get('rework_visual_checker_label')->row();

            if($existing){
                $serial_label = $existing->serial_label;
                $today = $existing->pack_date;
            }else{

                $serial_label = $this->generateSerialLabelRework($prefix);

                $insert = [
                    'scan_id'=>$detail->scan_id,
                    'item_fg_id'=>$item_fg_id,
                    'prod_date'=>$detail->prod_date,
                    'pack_date'=>$today,
                    'source'=>$source,
                    'qty'=>$total_qty,
                    'compound_lot_no'=>$dominant_lot,
                    'serial_label'=>$serial_label,
                    'status'=>0
                ];

                $this->crud->create('rework_visual_checker_label',$insert);

                foreach($lot_wo_counter as $lot=>$wo_rows){

                    foreach($wo_rows as $wo=>$qty){

                        $tracking = [
                            'serial_label'=>$serial_label,
                            'workorder_label'=>$wo,
                            'source'=>$source,
                            'compound_lot_no'=>$lot,
                            'qty'=>$qty
                        ];

                        $this->crud->create('rework_visual_checker_label_lot_tracking',$tracking);
                    }
                }

                $this->createQrcode($serial_label,"assets/image/qrcode/");
            }

            $qty_packing_formatted = number_format($total_qty,0,',','.')." ".strtoupper($detail->uom);

            // APPEND LABEL HTML

            $html .= '<div class="printLabel">
                            <table style="max-width: 7.5cm; max-height:8cm;">
                            <tr>
                                <th class="logo" colspan="6" style="text-align: center;">
                                    <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                    <span class="header" style="font-size: 20px; height: 20px;">REWORK</span>
                                </th>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $detail->number . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $today . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $dominant_lot . '</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="width: 30%;"><b>Source:</b></td>
                                <td colspan="4" style="font-weight: bold;">' . $source . '</td>
                            </tr>
                            <tr>
                                <th colspan="2">QC</th>
                                <th colspan="4">QR Code</th>
                            </tr>
                            <tr>
                                <td class="operator-sign" colspan="2">' . $qc . '</td>
                                <td class="qr-code" colspan="4">
                                    <img src="' . base_url('assets/image/qrcode/' . $serial_label . '.png') . '"/>
                                    <div class="serial-label">' . $serial_label . '</div>
                                </td>
                            </tr>
                        </table>
                </div>';

        }

        // COMMIT

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
        }else{
            $this->db->trans_commit();
        }

        $html .= '<script>window.print()</script></body></html>';

        die($html);
    }

    private function buildSourcePrefix($subcont_id, $tf_id)
    {
        if(!empty($subcont_id)){

            if(preg_match('/^S(\d+)/', $subcont_id, $m)){
                return 'S'.intval($m[1]);
            }

        }

        if(!empty($tf_id)){

            if(preg_match('/^TF(\d+)/', $tf_id, $m)){
                return 'T'.intval($m[1]);
            }

        }

        return "IN";
    }

    public function saveSummaryScanVc()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        $items = $this->input->post('items');

        $this->db->trans_begin();

        try {
            $parent_id = $items[0]['parent_id'] ?? null;
            $scan_id = $items[0]['scan_id'] ?? null;

            if (!$scan_id) {
                throw new Exception("ID not found");
            }

            $updateScanVcHeder = $this->crud->update('scan_visual_checker', [
                    'id' => $parent_id,
                    'scan_id' => $scan_id,
                ], ['type_status' => 'finished']
            );

            if (!$updateScanVcHeder) {
                throw new Exception("Failed update Scan Visual Checker");
            }

            foreach ($items as $post) {
                $updateScanVcDetail = $this->crud->update('scan_visual_checker_detail', [
                        'workorder_label' => $post['workorder_label'],
                        'item_fg_id'      => $post['item_fg_id']
                    ], ['type_status' => 'finished']
                );

                if (!$updateScanVcDetail) {
                    throw new Exception("Failed update Scan Visual Checker Detail {$post['workorder_label']}");
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