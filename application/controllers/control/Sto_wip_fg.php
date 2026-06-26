<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sto_wip_fg extends CI_Controller
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
            $this->load->view('control/sto_wip_fg');
        } else {
            redirect('error_access');
        }
    }

    public function getSummary()
    {

        $username = $this->session->username;

        $this->db->select("
            a.item_fg_id,
            a.label_type,
            SUM(a.qty) as qty,

            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom
        ");

        $this->db->from('sto_wip_fg_detail a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('sto_wip_fg c', 'a.scan_id = c.scan_id');

        $this->db->where('a.created_by', $username);
        $this->db->where('a.type_status', 'scanning');
        $this->db->where('a.status', 0);

        $this->db->group_by("
            a.item_fg_id,
            a.label_type,
            b.number,
            b.name,
            b.uom
        ");

        $this->db->order_by('MAX(a.created_date)', 'DESC');

        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => count($records),
            'rows'  => $records
        ]);
    }

    private function generateStoDocNo($location, $period_month, $period_year)
    {
        $sql = $this->db->query("
            SELECT 
                MAX(CAST(SUBSTRING_INDEX(doc_no, '/', 1) AS UNSIGNED)) AS kode
            FROM sto_wip_fg
            WHERE period_month = ?
            AND period_year = ?
            AND location = ?
            AND doc_no LIKE ?
        ", [
            $period_month,
            $period_year,
            $location,
            "%/STO/{$location}/%"
        ]);

        $row = $sql->row();

        $seq = $row->kode
            ? sprintf("%02d", ((int)$row->kode) + 1)
            : "01";

        $doc_month = sprintf("%02d", $period_month);
        $doc_year  = substr($period_year, -2);

        return "{$seq}/STO/{$location}/{$doc_month}/{$doc_year}";
    }

    public function sto_doc_no()
    {
        echo $this->generateStoDocNo(
            $this->input->post('location'),
            $this->input->post('period_month'),
            $this->input->post('period_year')
        );
    }

    public function readLocations()
    {
        // $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sql = "
            SELECT 
                'WIPP' as id,
                'WIPP' as number,
                'WIP Press' as name,
                'Internal' AS type

            UNION ALL

            SELECT 
                'WIPS' as id,
                'WIPS' as number,
                'WIP Store' as name,
                'Internal' AS type

            UNION ALL

            SELECT 
                id,
                number,
                name,
                'Subcont' AS type
            FROM subconts
            WHERE status = 0
            AND deleted = 0

            UNION ALL

            SELECT 
                id,
                number,
                name,
                'Teaching Factory' AS type
            FROM teaching_factory
            WHERE status = 0
            AND deleted = 0
        ";

        $send = $this->crud->query($sql);

        echo json_encode($send);
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getCurrentHeader()
    {
        $username = $this->session->username;

        $row = $this->db
            ->where('created_by', $username)
            ->where('type_status', 'scanning')
            ->where('status', 0)
            ->order_by('created_date', 'DESC')
            ->limit(1)
            ->get('sto_wip_fg')
            ->row_array();

        if (!$row) {
            echo json_encode([]);
            return;
        }

        $location_number = $row['location'];
        $location_name   = '';

        switch ($location_number) {
            case 'WIPP':
                $location_name = 'WIP Press';
                break;

            case 'WIPS':
                $location_name = 'WIP Store';
                break;

            default:

                $subcont = $this->db
                    ->select('name')
                    ->where('number', $location_number)
                    ->get('subconts')
                    ->row();

                if ($subcont) {
                    $location_name = $subcont->name;
                } else {

                    $tf = $this->db
                        ->select('name')
                        ->where('number', $location_number)
                        ->get('teaching_factory')
                        ->row();

                    if ($tf) {
                        $location_name = $tf->name;
                    }
                }
                break;
        }

        $row['location_code'] = $location_number;
        $row['location_name'] = $location_name;

        echo json_encode($row);
    }

    public function getStoWipFg()
    {
        $username = $this->session->username;

        $this->db->select("
            a.id,
            a.sto_wip_fg_id,
            a.scan_id,
            a.item_fg_id,
            a.label_type,
            a.created_by,
            a.created_date,
            SUM(a.qty) as qty,

            COALESCE(
                NULLIF(a.serial_label, ''),
                a.workorder_label
            ) as label,

            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom,

            COALESCE(d.name, e.name,
                CASE 
                    WHEN c.location = 'WIPP' THEN 'WIP Press'
                    WHEN c.location = 'WIPS' THEN 'WIP Store'
                END
            ) as location_name,

            c.period_month,
            c.period_year
        ");

        $this->db->from('sto_wip_fg_detail a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('sto_wip_fg c', 'a.scan_id = c.scan_id');
        $this->db->join('subconts d', 'c.location = d.number', 'left');
        $this->db->join('teaching_factory e', 'c.location = e.number', 'left');

        $this->db->where('a.created_by', $username);
        $this->db->where('a.type_status', 'scanning');
        $this->db->where('a.status', 0);

        $this->db->group_by("
            a.item_fg_id,
            a.workorder,
            COALESCE(NULLIF(a.serial_label, ''), a.workorder_label),
            c.location,
            c.period_month,
            c.period_year,
            b.number,
            b.name,
            b.uom
        ");

        $this->db->order_by('MAX(a.created_date)', 'DESC');

        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => count($records),
            'rows'  => $records
        ]);
    }

    private function _getTableConfigs()
    {
        return [
            'WIPP' => [
                [
                    'key'   => 'WIPP_PRESS',
                    'table' => 'output_production_press_detail',
                    'field' => 'workorder_label',
                    'prefix' => null,
                    'use_type_status' => false,
                    'qty_field' => 'qty_packing',
                    'label_type' => 'WIP Press'
                ],
            ],

            'WIPS' => [
                [
                    'key'   => 'WIPS_FNS',
                    'table' => 'scan_in_from_internal_finishing',
                    'field' => 'workorder_label',
                    'prefix' => null,
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Regular'
                ],
                [
                    'key'   => 'WIPS_FNS_RW',
                    'table' => 'scan_in_from_internal_finishing',
                    'field' => 'serial_label',
                    'prefix' => 'RWIN',
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Incoming Rework'
                ],
                [
                    'key'   => 'WIPS_SCTF',
                    'table' => 'scan_incoming_sctf',
                    'field' => 'workorder_label',
                    'prefix' => null,
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Regular'
                ],
                [
                    'key'   => 'WIPS_SCTF_RW',
                    'table' => 'scan_incoming_sctf',
                    'field' => 'serial_label',
                    'prefix' => 'RW',
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Incoming Rework'
                ],
                [
                    'key'   => 'WIPS_RETURN',
                    'table' => 'scan_in_return',
                    'field' => 'serial_label',
                    'prefix' => 'RT',
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Return'
                ],
                [
                    'key'   => 'WIPS_REWORK',
                    'table' => 'scan_in_rework',
                    'field' => 'serial_label',
                    'prefix' => 'RW',
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Rework'
                ],
            ],

            'OTHER' => [
                [
                    'key'   => 'OTHER_SBN',
                    'table' => 'shipping_to_subconts',
                    'field' => 'workorder_label',
                    'prefix' => null,
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'WIP Finishing'
                ],
                [
                    'key'   => 'OTHER_RW',
                    'table' => 'scan_out_rework',
                    'field' => 'serial_label',
                    'prefix' => 'RW',
                    'use_type_status' => true,
                    'qty_field' => 'qty',
                    'label_type' => 'Rework'
                ],
            ],
        ];
    }

    private function _getConfigMap()
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        foreach ($this->_getTableConfigs() as $group) {
            foreach ($group as $config) {
                $map[$config['key']] = $config;
            }
        }

        return $map;
    }

    private function getLocationId($location_number)
    {
        $subcont = $this->db->get_where('subconts', [
            'number' => $location_number
        ])->row_array();

        if ($subcont) {
            return $subcont['id'];
        }

        $tf = $this->db->get_where('teaching_factory', [
            'number' => $location_number
        ])->row_array();

        if ($tf) {
            return $tf['id'];
        }

        return null;
    }

    private function _generateAutoId()
    {
        $date = date('Ymd');

        $query = $this->db->query("
            SELECT id
            FROM sto_wip_fg
            WHERE id LIKE '{$date}%'
            ORDER BY id DESC
            LIMIT 1
            FOR UPDATE
        ");

        if ($query === false) {

            $error = $this->db->error();

            throw new Exception(json_encode([
                'db_error_code' => $error['code'] ?? 0,
                'db_error_message' => $error['message'] ?? ''
            ]));
        }

        $row = $query->row();

        if (!$row) {
            $id = $date . '000001';
        } else {
            $id = (string)((int)$row->id + 1);
        }

        return $id;
    }

    public function resetHeader()
    {
        $doc_no = $this->input->post('doc_no');
        $header = $this->db->where('doc_no', $doc_no)->get('sto_wip_fg')->row_array();

        if (!$header) {
            return $this->jsonResponse(
                'Not Found',
                'Header not found, please reload',
                'reload'
            );
        }

        $exists = $this->db->where('scan_id', $header['scan_id'])->count_all_results('sto_wip_fg_detail');

        if ($exists > 0) {
            return $this->jsonResponse(
                'Cannot Reset',
                'Data scan already exists',
                'warning'
            );
        }

        $this->crud->delete('sto_wip_fg', ['id' => $header['id']]);

        return $this->jsonResponse(
            'Success',
            'Data Header has been reset',
            'success'
        );
    }

    public function createHeaderV1()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();

        $existing = $this->db
            ->where('created_by', $this->session->username)
            ->where('type_status', 'scanning')
            ->where('status', 0)
            ->where('deleted', 0)
            ->order_by('created_date', 'DESC')
            ->limit(1)
            ->get('sto_wip_fg')
            ->row_array();

        if ($existing) {
            return $this->jsonResponse(
                'Scanning Active',
                'You already have an active scanning session on another device, please logout of your account here!',
                'reload',
            );
        }

        $this->db->trans_begin();

        try {

            $scan_id = $this->generate_uuid();

            $original_doc_no = $post['doc_no'];
            $doc_no = $post['doc_no'];

            $data_header = [
                'scan_id'       => $scan_id,
                'doc_no'        => $doc_no,
                'location'      => $post['location'],
                'period_month'  => $post['period_month'],
                'period_year'   => $post['period_year'],
                'type_status'   => 'scanning',
                'status'        => 0
            ];

            $old_debug = $this->db->db_debug;
            $this->db->db_debug = FALSE;

            try {

                $max_retry = 10;
                $inserted = false;

                for ($i = 0; $i < $max_retry; $i++) {

                    $id = $this->crud->autoid('sto_wip_fg');

                    $data = array_merge($data_header, [
                        'id'           => $id,
                        'created_by'   => $this->session->username,
                        'created_date' => date('Y-m-d H:i:s')
                    ]);

                    $inserted = $this->db->insert('sto_wip_fg', $data);

                    if ($inserted === true) {
                        $this->crud->logs("Create", json_encode($data), 'sto_wip_fg');

                        $doc_no = $data_header['doc_no'];
                        break;
                    }

                    $error = $this->db->error();

                    if (($error['code'] ?? 0) != 1062) {
                        throw new Exception($error['message']);
                    }

                    usleep(100000); // 100 ms

                    // log_message('error', 'Retry #' . $i);
                    // log_message('error', 'Duplicate key detected');

                    $data_header['doc_no'] = $this->generateStoDocNo(
                        $post['location'],
                        $post['period_month'],
                        $post['period_year']
                    );

                    // log_message('error', 'Generated = ' . $data_header['doc_no']);
                }

                if (!$inserted) {
                    throw new Exception('Failed to generate Document No, please try again later');
                }

            } finally {
                $this->db->db_debug = $old_debug;
            }

            $this->db->trans_commit();

            return $this->jsonResponse(
                'Success',
                'Data berhasil disimpan',
                'success',
                [
                    'doc_no' => $doc_no,
                    'original_doc_no' => $original_doc_no
                ]
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

    public function createHeader()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();

        $existing = $this->db
            ->where('created_by', $this->session->username)
            ->where('type_status', 'scanning')
            ->where('status', 0)
            ->where('deleted', 0)
            ->order_by('created_date', 'DESC')
            ->limit(1)
            ->get('sto_wip_fg')
            ->row_array();

        if ($existing) {
            return $this->jsonResponse(
                'Scanning Active',
                'You already have an active scanning session on another device, please logout of your account here!',
                'reload'
            );
        }

        $scan_id = $this->generate_uuid();

        $original_doc_no = $post['doc_no'];
        $doc_no          = $post['doc_no'];

        $data_header = [
            'scan_id'      => $scan_id,
            'doc_no'       => $doc_no,
            'location'     => $post['location'],
            'period_month' => $post['period_month'],
            'period_year'  => $post['period_year'],
            'type_status'  => 'scanning',
            'status'       => 0
        ];

        $old_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;

        try {

            $max_retry = 5;
            $inserted  = false;

            for ($i = 0; $i < $max_retry; $i++) {

                $this->db->trans_begin();

                try {

                    $id = $this->_generateAutoId();

                    $data = array_merge($data_header, [
                        'id'           => $id,
                        'created_by'   => $this->session->username,
                        'created_date' => date('Y-m-d H:i:s')
                    ]);

                    $inserted = $this->db->insert('sto_wip_fg', $data);

                    if ($inserted === true) {

                        $this->crud->logs(
                            "Create",
                            json_encode($data),
                            'sto_wip_fg'
                        );
                        $this->db->trans_commit();

                        $doc_no = $data_header['doc_no'];
                        break;
                    }

                    $error = $this->db->error();

                    $this->db->trans_rollback();

                    $errorCode = $error['code'] ?? 0;

                    if (!in_array($errorCode, [1062, 1213, 1205])) {
                        throw new Exception($error['message']);
                    }

                    // log_message('error', 'Retry #' . ($i + 1));
                    // log_message('error', 'Error Code : ' . $errorCode);
                    // log_message('error', 'Message : ' . $error['message']);

                    $errorMessage = $error['message'] ?? '';

                    if (strpos($errorMessage, 'uk_doc_no') !== false) {

                        $data_header['doc_no'] = $this->generateStoDocNo(
                            $post['location'],
                            $post['period_month'],
                            $post['period_year']
                        );
                    }

                    // log_message(
                    //     'error',
                    //     'NEW DOC NO = ' . $data_header['doc_no']
                    // );

                    usleep(random_int(100000, 300000));

                } catch (Exception $e) {
                    $this->db->trans_rollback();

                    $json = json_decode($e->getMessage(), true);
                    $dbErrorCode = $json['db_error_code'] ?? 0;
                    if (in_array($dbErrorCode, [1213, 1205])) {

                        // log_message(
                        //     'error',
                        //     'Retry because deadlock/timeout #' . ($i + 1)
                        // );

                        usleep(random_int(100000, 300000));
                        continue;
                    }

                    throw $e;
                }
            }

            if (!$inserted) {
                throw new Exception(
                    'Failed to generate unique ID / Document No after '
                    . $max_retry .
                    ' retries'
                );
            }

            return $this->jsonResponse(
                'Success',
                'Data berhasil disimpan',
                'success',
                [
                    'doc_no'         => $doc_no,
                    'original_doc_no'=> $original_doc_no
                ]
            );

        } catch (Exception $e) {

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

        } finally {

            $this->db->db_debug = $old_debug;
        }
    }

    public function getChecksheetLabel()
    {
        if (!$this->input->post()) {
            return;
        }

        $input_label = $this->input->post('workorder_label');
        $location    = $this->input->post('location');

        $configGroups = $this->_getTableConfigs();
        $group = $configGroups[$location] ?? $configGroups['OTHER'];

        $label = null;
        $foundConfig = null;

        foreach ($group as $config) {

            if ($config['table'] === 'shipping_to_subconts') {

                $this->db->select('
                    s.item_fg_id,
                    s.workorder,
                    s.workorder_label,
                    s.qty,
                    d.destination,
                    s.status
                ');

                $this->db->from('shipping_to_subconts s');
                $this->db->join(
                    'delivery_to_subconts d',
                    'd.scan_id = s.scan_id 
                    AND d.item_fg_id = s.item_fg_id 
                    AND d.workorder = s.workorder'
                );

                $this->db->where('s.workorder_label', $input_label);
                $row = $this->db->get()->row_array();

                if (!$row) {
                    continue;
                }

                $location_id = $this->getLocationId($location);

                if ($row['destination'] != $location_id) {
                    echo json_encode([
                        'title' => 'Warning',
                        'message' => 'This label does not match the selected location',
                        'data' => $row
                    ]);
                    return;
                }

                $label = $row;
                $foundConfig = $config;
                break;
            }

            if ($config['table'] === 'scan_out_rework') {

                $this->db->select('
                    s.item_fg_id,
                    s.workorder,
                    s.workorder_label,
                    s.serial_label,
                    s.qty,
                    d.destination,
                    s.status
                ');

                $this->db->from('scan_out_rework s');
                $this->db->join(
                    'delivery_rework d',
                    'd.scan_id = s.scan_id 
                    AND d.item_fg_id = s.item_fg_id 
                    AND d.dnr_no = s.dnr_no 
                    AND d.workorder = s.workorder'
                );

                $this->db->where('s.serial_label', $input_label);

                $row = $this->db->get()->row_array();

                if (!$row) {
                    continue;
                }

                if ($row['destination'] != $location) {
                    echo json_encode([
                        'title' => 'Warning',
                        'message' => 'This rework label does not match the selected location',
                        'data' => $row
                    ]);
                    return;
                }

                $label = $row;
                $foundConfig = $config;
                break;
            }

            if ($config['prefix'] !== null && strpos($input_label, $config['prefix']) !== 0) {
                continue;
            }

            $where = [
                $config['field'] => $input_label
            ];

            // if (!empty($config['use_type_status'])) {
            //     $where['type_status'] = 'completed';
            // } else {
            //     $where['status'] = 0;
            // }

            if (!empty($config['use_type_status'])) {
                $where['type_status'] = 'completed';
            }

            $row = $this->db->get_where($config['table'], $where)->row_array();

            if ($row) {
                $label = $row;
                $foundConfig = $config;
                break;
            }
        }

        if (!$label) {
            echo json_encode([
                'title' => 'Not Found',
                'message' => 'Label not found!'
            ]);
            return;
        }

        if (($label['status']) == 1) {
            echo json_encode([
                'title' => 'Label not available',
                'message' => 'Label is already in use in another module ',
                'data' => $label
            ]);
            return;
        }

        $serial_label = ($foundConfig['field'] === 'serial_label') ? $input_label : '';
        $workorder_label = ($foundConfig['field'] === 'workorder_label') ? $input_label : ($label['workorder_label'] ?? '');

        $checkDuplicate = $this->db->get_where('sto_wip_fg_detail', [
            'workorder_label' => $workorder_label,
            'serial_label'    => $serial_label
        ])->row_array();

        if ($checkDuplicate) {
            echo json_encode([
                'title' => 'Scanned',
                'message' => 'Label has already been scanned',
            ]);
            return;
        }

        $label_type = $foundConfig['label_type'];
        $qtyField = $foundConfig['qty_field'];

        $this->db->select("
            item_fg_id,
            workorder,
            workorder_label,
            {$qtyField} as qty,
            {$foundConfig['field']} as label
        ");

        $this->db->from($foundConfig['table']);
        $this->db->where($foundConfig['field'], $input_label);

        if (!empty($foundConfig['use_type_status'])) {
            $this->db->where('type_status', 'completed');
        } else {
            $this->db->where('status', '0');
        }

        $isMulti = ($foundConfig['field'] === 'serial_label');

        if ($isMulti) {
            $result = $this->db->get()->result_array();

            foreach ($result as &$row) {
                $row['label_type'] = $label_type;
                $row['config_key'] = $foundConfig['key'];
            }

            echo json_encode([
                'title' => 'success',
                'total' => count($result),
                'data'  => $result
            ]);
        } else {
            $result = $this->db->limit(1)->get()->row_array();

            if ($result) {
                $result['label_type'] = $label_type;
                $result['config_key'] = $foundConfig['key'];
            }

            echo json_encode([
                'title' => 'success',
                'total' => $result ? 1 : 0,
                'data'  => $result ? [$result] : []
            ]);
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
            return $this->jsonResponse('Error', 'No data to process!', 'error');
        }

        $this->db->trans_begin();

        try {

            $session_row = $this->db->select('scan_id, created_by')
                ->from('sto_wip_fg')
                ->where('type_status', 'scanning')
                ->where('created_by', $username)
                ->where('status', 0)
                ->limit(1)
                ->get()
                ->row();

            $scan_id = $session_row->scan_id ?? null;

            $checkHeader = $this->crud->read('sto_wip_fg', [], [
                'scan_id' => $scan_id
            ]);

            foreach ($rows as $row) {

                $label = $row['label'];

                $label_item = null;
                $found_config = null;

                $configKey = $row['config_key'] ?? null;

                $config = $this->_getConfigMap()[$configKey] ?? null;

                if (!$config) {
                    throw new Exception(json_encode([
                        'title'   => 'Error',
                        'message' => 'Invalid config key',
                        'theme'   => 'error'
                    ]));
                }

                $where = [
                    $config['field'] => $label
                ];

                if (!empty($config['use_type_status'])) {
                    $where['type_status'] = 'completed';
                } else {
                    $where['status'] = 0;
                }

                $label_item = $this->db
                    ->get_where($config['table'], $where)
                    ->row_array();

                $found_config = $config;

                if (!$label_item) {
                    throw new Exception(json_encode([
                        'title' => 'Not Found',
                        'message' => 'Label not found!',
                        'theme' => 'error'
                    ]));
                }

                $workorder_label = ($found_config['field'] === 'workorder_label')
                    ? $label
                    : ($row['workorder_label'] ?? '');

                $serial_label = ($found_config['field'] === 'serial_label')
                    ? $label
                    : '';

                $exists = $this->db->get_where('sto_wip_fg_detail', [
                    'workorder_label' => $workorder_label,
                    'serial_label'    => $serial_label
                ])->row_array();

                if ($exists) {
                    throw new Exception(json_encode([
                        'title' => 'Scanned',
                        'message' => 'Label has already been scanned',
                        'theme' => 'warning'
                    ]));
                }

                $data_to_insert = [
                    'scan_id'         => $scan_id,
                    'sto_wip_fg_id'   => $checkHeader->id,
                    'doc_no'          => $checkHeader->doc_no,
                    'item_fg_id'      => $row['item_fg_id'],
                    'workorder'       => $row['workorder'],
                    'label_type'      => $row['label_type'],
                    'workorder_label' => $workorder_label,
                    'serial_label'    => $serial_label,
                    'qty'             => $row['qty'],
                    'type_status'     => 'scanning',
                    'status'          => 0
                ];

                $this->crud->create('sto_wip_fg_detail', $data_to_insert);

                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Failed insert detail');
                }
            }

            $this->db->trans_commit();

            return $this->jsonResponse('Success', 'Data berhasil disimpan', 'success');

        } catch (Exception $e) {

            $this->db->trans_rollback();

            $json = @json_decode($e->getMessage(), true);

            if ($json) {
                return $this->jsonResponse(
                    $json['title'],
                    $json['message'],
                    $json['theme'],
                );
            }

            return $this->jsonResponse('Error', $e->getMessage(), 'error');
        }
    }

    private function jsonResponse($title, $message, $theme = 'error', $meta = [])
    {
        echo json_encode(array_merge([
            'title'   => $title,
            'message' => $message,
            'theme'   => $theme
        ], $meta));

        return;
    }

    private function generate_uuid()
    {
        $uuid = $this->uuid->v4();
        return $uuid;
    }

    public function saveSummaryStoWipFg()
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

            $updateStoWipFG = $this->crud->update('sto_wip_fg', [
                    'id' => $parent_id,
                    'scan_id' => $scan_id,
                ], ['type_status' => 'completed']
            );

            if (!$updateStoWipFG) {
                throw new Exception("Failed update STO WIP FG");
            }

            foreach ($items as $post) {
                $updateStoWipFGDetail = $this->crud->update('sto_wip_fg_detail', [
                        'scan_id'      => $post['scan_id'],
                        'doc_no'      => $post['doc_no'],
                    ], ['type_status' => 'completed']
                );

                if (!$updateStoWipFGDetail) {
                    throw new Exception("Failed update STO WIP FG Detail {$post['workorder_label']}");
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