<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_sto_wip_fg extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/report_sto_wip_fg');
        } else {
            redirect('error_access');
        }
    }

    public function readDocNo()
    {
        $filter_period_month = $this->input->get("filter_period_month");
        $filter_period_year   = $this->input->get("filter_period_year");
        $filter_location = $this->input->get("filter_location");

        $this->db->distinct();
        $this->db->select('doc_no');
        $this->db->from('sto_wip_fg');
        $this->db->where('type_status', 'completed');

        if (!empty($filter_period_month)) {
            $this->db->where('period_month', $filter_period_month);
        }

        if (!empty($filter_period_year)) {
            $this->db->where('period_year', $filter_period_year);
        }

        if (!empty($filter_location)) {
            $this->db->where('location', $filter_location);
        }

        $this->db->order_by('doc_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }


    public function readLocations()
    {
        // $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sql = "
            SELECT 
                'WIPP' as id,
                'WIPP' as code,
                'WIP Press' as name,
                'Internal' AS type

            UNION ALL

            SELECT 
                'WIPS' as id,
                'WIPS' as code,
                'WIP Store' as name,
                'Internal' AS type

            UNION ALL

            SELECT 
                'WIPC' as id,
                'WIPC' as code,
                'WIP Checker' as name,
                'Internal' AS type

            UNION ALL

            SELECT 
                id,
                number as code,
                name,
                'Subcont' AS type
            FROM subconts
            WHERE status = 0
            AND deleted = 0

            UNION ALL

            SELECT 
                id,
                number as code,
                name,
                'Teaching Factory' AS type
            FROM teaching_factory
            WHERE status = 0
            AND deleted = 0
        ";

        $send = $this->crud->query($sql);

        echo json_encode($send);
    }

    private function getLabelTypes($location = '')
    {
        $all = [
            'PR1' => [
                'code' => 'PR1',
                'name' => 'REGULAR',
                'description' => 'Product after finishing (internal/external).'
            ],
            'PR2' => [
                'code' => 'PR2',
                'name' => 'REWORK',
                'description' => 'Product sudah scan out ke SC/TF untuk di-rework.'
            ],
            'PR3' => [
                'code' => 'PR3',
                'name' => 'INCOMING REWORK',
                'description' => 'Product yang sudah di-rework oleh SC/TF.'
            ],
            'PR4' => [
                'code' => 'PR4',
                'name' => 'RETURN',
                'description' => 'Product after finishing (internal/external).'
            ],
            'PR5' => [
                'code' => 'PR5',
                'name' => 'WIP PRESS',
                'description' => 'Product original output press.'
            ],
            'PR6' => [
                'code' => 'PR6',
                'name' => 'WIP FINISHING',
                'description' => 'Product siap finishing SC/TF.'
            ],
            'PR7' => [
                'code' => 'PR7',
                'name' => 'WIP CHECKER',
                'description' => 'Product Visual Checker, Oven & CP, siap RFG.'
            ]
        ];

        switch ($location) {
            case 'WIPP':
                return [
                    $all['PR5']
                ];

            case 'WIPS':
                return [
                    $all['PR1'],
                    $all['PR2'],
                    $all['PR3'],
                    $all['PR4']
                ];

            case 'WIPC':
                return [
                    $all['PR7']
                ];

            default:
                return [
                    $all['PR2'],
                    $all['PR6']
                ];
        }
    }

    public function readLabelTypes()
    {
        $location = $this->input->get('location');

        if (empty($location)) {
            echo json_encode([]);
            return;
        }

        echo json_encode(
            $this->getLabelTypes($location)
        );
    }

    private function normalizeLabelType($label_type)
    {
        return strtoupper(trim($label_type));
    }

    private function getMovementConfigs()
    {
        return [

            'WIPS' => [
                'REGULAR' => [
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_in_from_internal_finishing
                            WHERE status = 0
                            AND type_status = 'completed'
                            AND created_date >= '{FROM} 00:00:00'
                            AND created_date <= '{TO} 23:59:59'
                            GROUP BY item_fg_id
                        "
                    ],
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_incoming_sctf
                            WHERE status = 0
                            AND type_status = 'completed'
                            AND incoming_type = 'Regular'
                            AND incoming_date >= '{FROM}'
                            AND incoming_date <= '{TO}'
                            GROUP BY item_fg_id
                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                svcd.item_fg_id,
                                SUM(svcd.qty_on_label) qty
                            FROM scan_visual_checker vc
                            JOIN scan_visual_checker_detail svcd
                                ON svcd.scan_id = vc.scan_id
                                AND svcd.visual_checker_id = vc.id
                            WHERE vc.deleted = 0
                            AND vc.type_status = 'completed'
                            AND vc.check_date >= '{FROM}'
                            AND vc.check_date <= '{TO}'
                            AND (
                                svcd.serial_label IS NULL
                                OR svcd.serial_label = ''
                            )
                            GROUP BY svcd.item_fg_id
                        "
                    ]
                ],

                'REWORK' => [
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_in_rework
                            WHERE status = 0
                            AND type_status = 'completed'
                            AND created_date >= '{FROM} 00:00:00'
                            AND created_date <= '{TO} 23:59:59'
                            GROUP BY item_fg_id
                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                s.item_fg_id,
                                SUM(s.qty) qty
                            FROM delivery_rework d
                            JOIN scan_out_rework s
                                ON s.dnr_no = d.dnr_no
                                AND s.item_fg_id = d.item_fg_id
                                AND s.scan_id = d.scan_id
                                AND s.workorder = d.workorder
                            WHERE d.status = 0
                            AND s.status = 0
                            AND s.type_status = 'completed'
                            AND d.delivery_date >= '{FROM} 00:00:00'
                            AND d.delivery_date <= '{TO} 23:59:59'
                            GROUP BY s.item_fg_id
                        "
                    ],
                ],
                'RETURN' => [
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_in_return
                            WHERE status = 0
                            AND type_status = 'completed'
                            AND created_date >= '{FROM} 00:00:00'
                            AND created_date <= '{TO} 23:59:59'
                            GROUP BY item_fg_id

                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                d.item_fg_id,
                                SUM(d.qty_on_label) qty
                            FROM scan_visual_checker h
                            JOIN scan_visual_checker_detail d
                                ON d.scan_id = h.scan_id
                                AND d.visual_checker_id = h.id
                            WHERE h.deleted = 0
                            AND d.deleted = 0

                            AND d.serial_label IS NOT NULL
                            AND d.serial_label <> ''

                            AND d.serial_label LIKE 'RT%'

                            AND h.check_date >= '{FROM} 00:00:00'
                            AND h.check_date <= '{TO} 23:59:59'
                            GROUP BY d.item_fg_id
                        "
                    ],
                ],
                'INCOMING REWORK' => [
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_incoming_sctf
                            WHERE incoming_type = 'Rework'
                            AND status = 0
                            AND type_status = 'completed'
                            AND incoming_date >= '{FROM} 00:00:00'
                            AND incoming_date <= '{TO} 23:59:59'
                            GROUP BY item_fg_id
                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                d.item_fg_id,
                                SUM(d.qty_on_label) qty
                            FROM scan_visual_checker h
                            JOIN scan_visual_checker_detail d
                                ON d.scan_id = h.scan_id
                                AND d.visual_checker_id = h.id
                            WHERE h.deleted = 0
                            AND d.deleted = 0

                            AND d.serial_label IS NOT NULL
                            AND d.serial_label <> ''

                            AND d.serial_label LIKE 'RW%'
                            AND d.serial_label NOT LIKE 'RWIN%'

                            AND h.check_date >= '{FROM} 00:00:00'
                            AND h.check_date <= '{TO} 23:59:59'
                            GROUP BY d.item_fg_id
                        "
                    ],
                ]
            ],

            'WIPC' => [
                'WIP CHECKER' => [
                    [
                        // Scan In RFG
                    ],
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                svcd.item_fg_id,
                                SUM(svcd.qty_on_label) qty
                            FROM scan_visual_checker vc
                            JOIN scan_visual_checker_detail svcd
                                ON svcd.scan_id = vc.scan_id
                                AND svcd.visual_checker_id = vc.id
                            WHERE vc.deleted = 0
                            AND vc.type_status = 'completed'
                            AND vc.check_date >= '{FROM}'
                            AND vc.check_date <= '{TO}'
                            AND (
                                svcd.serial_label IS NULL
                                OR svcd.serial_label = ''
                            )
                            GROUP BY svcd.item_fg_id
                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_in_return
                            WHERE status = 0
                            AND type_status = 'completed'
                            AND created_date >= '{FROM} 00:00:00'
                            AND created_date <= '{TO} 23:59:59'
                            GROUP BY item_fg_id

                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                item_fg_id,
                                SUM(qty) qty
                            FROM scan_in_rework
                            WHERE status = 0
                            AND type_status = 'completed'
                            AND created_date >= '{FROM} 00:00:00'
                            AND created_date <= '{TO} 23:59:59'
                            GROUP BY item_fg_id
                        "
                    ],
                ]
            ],

            'OTHERS' => [
                'WIP FINISHING' => [
                    [
                        'type' => 'IN',
                        'sql' => "

                            SELECT
                                d.item_fg_id,
                                SUM(s.qty) qty
                            FROM delivery_to_subconts d
                            JOIN shipping_to_subconts s
                                ON s.scan_id = d.scan_id
                                AND s.item_fg_id = d.item_fg_id
                                AND s.workorder = d.workorder
                            LEFT JOIN subconts sc
                                ON sc.id = d.destination

                            LEFT JOIN teaching_factory tf
                                ON tf.id = d.destination
                            WHERE s.status = 0
                            AND s.type_status = 'completed'
                            AND (
                                sc.number = '{LOCATION}'
                                OR tf.number = '{LOCATION}'
                            )
                            AND d.delivery_date >= '{FROM} 00:00:00'
                            AND d.delivery_date <= '{TO} 23:59:59'
                            GROUP BY d.item_fg_id
                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                si.item_fg_id,
                                SUM(si.qty) qty
                            FROM scan_incoming_sctf si
                            LEFT JOIN subconts sc
                                ON sc.id = si.incoming_from
                            LEFT JOIN teaching_factory tf
                                ON tf.id = si.incoming_from

                            WHERE si.incoming_type = 'Regular'
                            AND si.status = 0
                            AND si.type_status = 'completed'
                            AND (
                                sc.number = '{LOCATION}'
                                OR tf.number = '{LOCATION}'
                            )
                            AND si.incoming_date >= '{FROM} 00:00:00'
                            AND si.incoming_date <= '{TO} 23:59:59'

                            GROUP BY si.item_fg_id
                        "
                    ],
                ],
                'REWORK' => [
                    [
                        'type' => 'IN',
                        'sql' => "
                            SELECT
                                s.item_fg_id,
                                SUM(s.qty) qty
                            FROM delivery_rework d
                            JOIN scan_out_rework s
                                ON s.dnr_no = d.dnr_no
                                AND s.item_fg_id = d.item_fg_id
                                AND s.scan_id = d.scan_id
                                AND s.workorder = d.workorder
                            WHERE d.status = 0
                            AND d.destination = '{LOCATION}'
                            AND s.status = 0
                            AND s.type_status = 'completed'
                            AND d.delivery_date >= '{FROM} 00:00:00'
                            AND d.delivery_date <= '{TO} 23:59:59'
                            GROUP BY s.item_fg_id
                        "
                    ],
                    [
                        'type' => 'OUT',
                        'sql' => "
                            SELECT
                                si.item_fg_id,
                                SUM(si.qty) qty
                            FROM scan_incoming_sctf si
                            LEFT JOIN subconts sc
                                ON sc.id = si.incoming_from
                            LEFT JOIN teaching_factory tf
                                ON tf.id = si.incoming_from
                            WHERE si.incoming_type = 'Rework'
                            AND si.status = 0
                            AND si.type_status = 'completed'
                            AND (
                                sc.number = '{LOCATION}'
                                OR tf.number = '{LOCATION}'
                            )
                            AND si.incoming_date >= '{FROM} 00:00:00'
                            AND si.incoming_date <= '{TO} 23:59:59'

                            GROUP BY si.item_fg_id
                        "
                    ],
                ],
            ]
        ];
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_sto_wip_fg_$format.xls");
        }

        $filter_period_month    = base64_decode($this->input->get("filter_period_month"));
        $filter_period_year     = base64_decode($this->input->get("filter_period_year"));
        $filter_location        = base64_decode($this->input->get("filter_location"));
        $filter_location_name   = base64_decode($this->input->get("filter_location_name"));
        $filter_doc_no          = base64_decode($this->input->get("filter_doc_no"));
        $filter_label_type      = base64_decode($this->input->get("filter_label_type"));
        $filter_item_fg         = base64_decode($this->input->get("filter_item_fg"));
        $filter_deviation       = base64_decode($this->input->get("filter_deviation"));
        $filter_display         = base64_decode($this->input->get("filter_display"));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $period_month_display = date('F', mktime(0, 0, 0, (int)$filter_period_month, 1));
        $schedule_display = $period_month_display . ' ' . $filter_period_year;
        $location_display = $filter_location != '' ? $filter_location_name : '-';
        $filter_display == "RECAP" ? "RECAP" : "DETAIL";

        $cut_of_stock = $filter_period_year . '-' . str_pad($filter_period_month, 2, '0', STR_PAD_LEFT) . '-25';

        $overflow = 'overflow: hidden;';

        $html = '<html>
                 <head>
                    <title>Print Data</title>
                </head>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }
                    #sto_wip_fg tr:hover {
                        background-color: #ddd;
                    }

                    .table-container {
                        overflow: auto;
                    }
                    table#sto_wip_fg {
                        border-collapse: separate;
                        border-spacing: 0;
                        /* width: max-content; */
                        font-size: 12px;
                    }
                    table#sto_wip_fg {
                        border: none;
                    }
                    table#sto_wip_fg th, table#sto_wip_fg td {
                        border: 1px solid #ddd;
                        border-right: none;
                        border-bottom: none;
                    }
                    table#sto_wip_fg th:last-child, table#sto_wip_fg td:last-child {
                        border-right: 1px solid #ddd;
                    }
                    table#sto_wip_fg tr:last-child td {
                        border-bottom: 1px solid #ddd;
                    }

                    .header {
                        position: sticky;
                        top: 0;
                        background-color: white;
                        z-index: 10;
                        padding-bottom: 20px !important;
                    }
                    #sto_wip_fg thead th {
                        position: sticky;
                        top: 0;
                        z-index: 200;
                        background: #f2f2f2;
                    }

                    #table-detail {
                        max-height: 72vh;
                        margin-left: 18px;
                        background: white !important;
                    }
                    #table-detail::before {
                        content: "";
                        position: sticky;
                        left: 0;
                        width: 18px;
                        background: white;
                        z-index: 10;
                        display: block;
                    }

                    #sto_wip_fg th,
                    #sto_wip_fg td {
                        padding: 4px 8px;
                        white-space: nowrap;
                    }
                    .table-container {
                        max-height: 72vh;
                        overflow: auto;
                    }

                    .freeze-col {
                        position: sticky;
                        left: 0;
                        background: white;
                        z-index: 50;
                    }

                    #sto_wip_fg thead .freeze-col {
                        z-index: 250;
                    }

                    #sto_wip_fg th,
                    #sto_wip_fg td {
                        box-sizing: border-box;
                    }

                    .freeze-col {
                        position: sticky;
                        background: #fff;
                        z-index: 20;
                    }

                    table#sto_wip_fg {
                        width: max-content;
                    }

                </style>
                <body style="margin: 0; '.$overflow.'">
                <div class="header" style="padding: 18px;">
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
                        <h3>REPORT STO WIP FG (' .$filter_display. ')</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Location</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">'. $location_display .'</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Cut Of Stock</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $cut_of_stock . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $schedule_display . '</td>
                    </tr>
                </table>
                </div>';


            if($filter_display == "RECAP") {
                $html .= '<div class="table-container" style="overflow:auto; margin: 0 18px;">
                            <table id="sto_wip_fg" style="width: 100%; padding: 0px;" border="1">
                            <thead style="position: sticky; z-index: 100; top: 0px; background: #f2f2f2;">
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Product ID</th>
                                <th rowspan="2">Product No</th>
                                <th rowspan="2">Product Name</th>
                                <th rowspan="2">Qty Stock</th>
                                <th rowspan="2">Total Qty STO</th>
                                <th rowspan="2">Label Type</th>
                                <th rowspan="2">Deviation</th>
                                <th rowspan="2">Uom</th>
                                <th rowspan="2">Description</th>
                                
                                <th colspan="2">Created</th>
                            </tr>
                            <tr>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            ';

                    $movement_configs = $this->getMovementConfigs();

                    $stock_qty = [];
                    if ($filter_location == 'WIPP') {

                        $stock_to = sprintf('%04d-%02d-25',$filter_period_year,$filter_period_month);
                        $stock_from = date('Y-m-d',strtotime($stock_to . ' -1 month'));

                        $this->db->select("
                            opd.item_fg_id,
                            SUM(opd.qty_packing) AS qty_stock
                        ");
                        $this->db->from('output_production_press op');
                        $this->db->join(
                            'output_production_press_detail opd',
                            'opd.number_output = op.number and opd.item_fg_id = op.item_fg_id and opd.workorder = op.workorder'
                        );

                        $this->db->where('opd.deleted', 0);
                        $this->db->where('opd.status', 0);
                        $this->db->where('op.trans_date >=', $stock_from);
                        $this->db->where('op.trans_date <=', $stock_to);

                        $this->db->group_by('opd.item_fg_id');
                        $stocks = $this->db->get()->result();

                        foreach ($stocks as $stock) {
                            $stock_qty[$stock->item_fg_id] = $stock->qty_stock;
                        }
                    }

                    if($filter_location != 'WIPP') {
                        $period_to = sprintf('%04d-%02d-25',$filter_period_year,$filter_period_month);
                        $period_from = date('Y-m-d',strtotime($period_to . ' -1 month'));

                        $current_period = intval($filter_period_year . str_pad($filter_period_month, 2, '0', STR_PAD_LEFT));

                        if($current_period == 202606) {

                            $this->db->select("
                                item_fg_id,
                                label_type,
                                location,
                                SUM(qty) qty
                            ");
                            $this->db->from('balance_begin_wip');
                            $this->db->where('deleted', 0);

                            if (!empty($filter_location)) {
                                $this->db->where('location', $filter_location);
                            }

                            if (!empty($filter_label_type)) {
                                $this->db->where('label_type', $filter_label_type);
                            }

                            $this->db->group_by([
                                'item_fg_id',
                                'label_type',
                                'location'
                            ]);

                            $begin_balances = $this->db->get()->result();
                            foreach ($begin_balances as $row) {
                                $key = $this->normalizeLabelType($row->label_type) . '|' . $row->location .'|'. $row->item_fg_id;
                                $stock_qty[$key] = (float)$row->qty;
                            }
                            
                        } else {

                            if ($filter_location == 'WIPS') {

                                if (!empty($filter_label_type)) {
                                    $label_types = [$this->normalizeLabelType($filter_label_type)];
                                } else {
                                    $label_types = array_keys($movement_configs['WIPS']);
                                }

                                foreach ($label_types as $label_type) {
                                    $configs = $movement_configs['WIPS'][$label_type] ?? [];

                                    foreach ($configs as $config) {
                                        $sql = str_replace(
                                            ['{FROM}', '{TO}'],
                                            [
                                                $period_from,
                                                $period_to,
                                            ],
                                            $config['sql']
                                        );

                                        $query = $this->db->query($sql);

                                        foreach ($query->result() as $row) {

                                            $key = $label_type . '|' . $filter_location . '|' . $row->item_fg_id;

                                            if (!isset($stock_qty[$key])) {
                                                $stock_qty[$key] = 0;
                                            }

                                            if ($config['type'] == 'IN') {
                                                $stock_qty[$key] += $row->qty;
                                            } else {
                                                $stock_qty[$key] -= $row->qty;
                                            }
                                        }
                                    }
                                }
                            } else if ($filter_location == 'WIPC') {

                                if (!empty($filter_label_type)) {
                                    $label_types = [
                                        $this->normalizeLabelType($filter_label_type)
                                    ];
                                } else {
                                    $label_types = array_keys(
                                        $movement_configs['WIPC']
                                    );
                                }

                                foreach ($label_types as $label_type) {

                                    $configs = $movement_configs['WIPC'][$label_type] ?? [];

                                    foreach ($configs as $config) {

                                        if (empty($config['sql'])) {
                                            continue;
                                        }

                                        $sql = str_replace(
                                            ['{FROM}', '{TO}'],
                                            [
                                                $period_from,
                                                $period_to
                                            ],
                                            $config['sql']
                                        );

                                        $query = $this->db->query($sql);

                                        foreach ($query->result() as $row) {
                                            $key = $label_type . '|' . $filter_location . '|' . $row->item_fg_id;

                                            if (!isset($stock_qty[$key])) {
                                                $stock_qty[$key] = 0;
                                            }

                                            if ($config['type'] == 'IN') {
                                                $stock_qty[$key] += $row->qty;
                                            } else {
                                                $stock_qty[$key] -= $row->qty;
                                            }
                                        }
                                    }
                                }
                            } else if ($filter_location != 'WIPP') {

                                if (!empty($filter_label_type)) {
                                    $label_types = [
                                        $this->normalizeLabelType($filter_label_type)
                                    ];
                                } else {
                                    $label_types = array_keys(
                                        $movement_configs['OTHERS']
                                    );
                                }

                                foreach ($label_types as $label_type) {
                                    $configs = $movement_configs['OTHERS'][$label_type] ?? [];

                                    foreach ($configs as $config) {
                                        $sql = str_replace(
                                            ['{FROM}', '{TO}', '{LOCATION}'],
                                            [
                                                $period_from,
                                                $period_to,
                                                $filter_location
                                            ],
                                            $config['sql']
                                        );

                                        $query = $this->db->query($sql);

                                        foreach ($query->result() as $row) {

                                            $key = $label_type . '|' . $filter_location . '|' . $row->item_fg_id;

                                            if (!isset($stock_qty[$key])) {
                                                $stock_qty[$key] = 0;
                                            }

                                            if ($config['type'] == 'IN') {
                                                $stock_qty[$key] += $row->qty;
                                            } else {
                                                $stock_qty[$key] -= $row->qty;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $master_rows = [];

                    foreach ($stock_qty as $key => $qty) {

                        if ($filter_location == 'WIPP') {
                            $master_rows[$key] = [
                                'item_fg_id' => $key,
                                'label_type' => 'WIP PRESS',
                                'location' => $filter_location,
                                'qty_stock' => $qty
                            ];
                        } else {

                            $explode = explode('|', $key);
                            $master_rows[$key] = [
                                'label_type' => $explode[0],
                                'location' => $explode[1],
                                'item_fg_id' => $explode[2],
                                'qty_stock' => $qty
                            ];
                        }
                    }

                    $this->db->select("
                        h.location,
                        d.item_fg_id,
                        d.label_type,

                        MAX(h.created_by) created_by,
                        MAX(h.created_date) created_date,

                        SUM(d.qty) total_qty_sto
                    ");

                    // $this->db->select("
                    //     h.id,
                    //     h.created_by,
                    //     h.created_date,
                    //     h.location,

                    //     d.item_fg_id,
                    //     d.label_type,

                    //     SUM(d.qty) AS total_qty_sto,

                    //     fg.number AS item_fg_number,
                    //     fg.name AS item_fg_name,
                    //     fg.uom
                    // ");

                    $this->db->from('sto_wip_fg h');
                    $this->db->join('sto_wip_fg_detail d', 'd.sto_wip_fg_id = h.id');
                    $this->db->join('item_fg fg', 'fg.id = d.item_fg_id', 'left');

                    $this->db->where('h.type_status', 'completed');
                    $this->db->where('h.deleted', 0);

                    if (!empty($filter_location)) {
                        $this->db->where('h.location', $filter_location);
                    }
                    if (!empty($filter_period_month)) {
                        $this->db->where('h.period_month', $filter_period_month);
                    }
                    if (!empty($filter_period_year)) {
                        $this->db->where('h.period_year', $filter_period_year);
                    }
                    if (!empty($filter_doc_no)) {
                        $this->db->where('h.doc_no', $filter_doc_no);
                    }
                    if (!empty($filter_label_type) && $filter_location != 'WIPP') {
                        $this->db->where('d.label_type', $filter_label_type);
                    }
                    if (!empty($filter_item_fg)) {
                        $this->db->where('d.item_fg_id', $filter_item_fg);
                    }

                    $this->db->group_by([
                        'd.item_fg_id',
                        'd.label_type',
                        'h.location'
                    ]);

                    $this->db->order_by('h.doc_no', 'ASC');
                    $this->db->order_by('d.item_fg_id', 'ASC');

                    // $rows = $this->db->get()->result();
                    $sto_rows = $this->db->get()->result();

                    $sto_data = [];
                    foreach ($sto_rows as $row)
                    {
                        if ($filter_location == 'WIPP') {
                            $key = $row->item_fg_id;
                            $label_type = 'WIP PRESS';

                        } else {
                            $label_type = $this->normalizeLabelType($row->label_type);
                            $key = $label_type . '|' . $row->location . '|' . $row->item_fg_id;
                        }

                        $sto_data[$key] = [
                            'qty' => $row->total_qty_sto,
                            'created_by' => $row->created_by,
                            'created_date' => $row->created_date
                        ];

                        if (!isset($master_rows[$key])) {

                            $master_rows[$key] = [
                                'item_fg_id' => $row->item_fg_id,
                                'label_type' => $label_type,
                                'location' => $row->location,
                                'qty_stock' => 0
                            ];
                        }
                    }

                    $item_ids = [];

                    foreach ($master_rows as $row) {
                        $item_ids[] = $row['item_fg_id'];
                    }

                    $item_fg_master = [];

                    if (!empty($item_ids)) {

                        $this->db->where_in(
                            'id',
                            array_unique($item_ids)
                        );

                        $items = $this->db->get('item_fg')->result();

                        foreach ($items as $item) {
                            $item_fg_master[$item->id] = $item;
                        }
                    }

                    $no = 1;

                    ksort($master_rows);

                    foreach ($master_rows as $key => $row) {
                        $item_fg_id = $row['item_fg_id'];

                        if (!empty($filter_item_fg) &&
                            $filter_item_fg != $item_fg_id) {
                            continue;
                        }

                        $item = $item_fg_master[$item_fg_id] ?? null;

                        $qty_stock = (float)$row['qty_stock'];
                        $total_qty_sto = isset($sto_data[$key]) ? (float)$sto_data[$key]['qty'] : 0;

                        $created_by = $sto_data[$key]['created_by'] ?? '';
                        $created_date = $sto_data[$key]['created_date'] ?? '';

                        $deviation =
                            $qty_stock -
                            $total_qty_sto;

                        if ($filter_deviation == 'plus' && $deviation <= 0) {
                            continue;
                        }

                        if ($filter_deviation == 'minus' && $deviation >= 0) {
                            continue;
                        }

                        if ($deviation > 0) {
                            $description = 'Deviation +';
                            $description_color = '#FF0000';

                        } elseif ($deviation < 0) {
                            $description = 'Deviation -';
                            $description_color = '#FF0000';

                        } else {
                            $description = 'Deviation 0';
                            $description_color = '#000000';
                        }

                        $html .= '
                        <tr align="center">
                            <td>'.$no.'</td>
                            <td>'.$item_fg_id.'</td>
                            <td>'.($item->number ?? '').'</td>
                            <td>'.($item->name ?? '').'</td>
                            <td align="right">'.number_format($qty_stock,0,",",".").'</td>
                            <td align="right">'.number_format($total_qty_sto,0,",",".").'</td>
                            <td>'.($filter_location == 'WIPP' ? 'WIP PRESS' : $row['label_type']).'</td>
                            <td align="right">('.number_format($deviation,0,",",".").')</td>
                            <td>'.($item->uom ?? '').'</td>
                            <td style="color: '.$description_color.';">'.$description.'</td>
                            <td width="120px">'.$created_by.'</td>
                            <td width="30px">'.$created_date.'</td>
                        </tr>';

                        $no++;
                    }

                $html .= '</table></div></div>';
            } else if($filter_display == "DETAIL") {

                $html .= '<div class="table-container" style="overflow:auto; margin: 0 18px;">
                            <table id="sto_wip_fg" style="width: 100%; padding: 0px;" border="1">
                            <thead style="position: sticky; z-index: 100; top: 0px; background: #f2f2f2;">
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">STO Doc No</th>
                                <th rowspan="2">Product ID</th>
                                <th rowspan="2">Product No</th>
                                <th rowspan="2">Product Name</th>
                                <th rowspan="2">Total Qty STO</th>
                                <th rowspan="2">Label Type</th>
                                <th rowspan="2">Uom</th>
                                
                                <th colspan="2">Created</th>
                            </tr>
                            <tr>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            ';

                
                    $this->db->select("
                        h.id,
                        h.doc_no,
                        h.created_by,
                        h.created_date,

                        d.item_fg_id,
                        d.label_type,

                        SUM(d.qty) AS total_qty_sto,

                        fg.number AS item_fg_number,
                        fg.name AS item_fg_name,
                        fg.uom
                    ");
                    $this->db->from('sto_wip_fg h');
                    $this->db->join('sto_wip_fg_detail d', 'd.sto_wip_fg_id = h.id');
                    $this->db->join('item_fg fg', 'fg.id = d.item_fg_id', 'left');

                    $this->db->where('h.type_status', 'completed');
                    $this->db->where('h.deleted', 0);

                    if (!empty($filter_location)) {
                        $this->db->where('h.location', $filter_location);
                    }
                    if (!empty($filter_period_month)) {
                        $this->db->where('h.period_month', $filter_period_month);
                    }
                    if (!empty($filter_period_year)) {
                        $this->db->where('h.period_year', $filter_period_year);
                    }
                    if (!empty($filter_doc_no)) {
                        $this->db->where('h.doc_no', $filter_doc_no);
                    }
                    if (!empty($filter_label_type)) {
                        $this->db->where('d.label_type', $filter_label_type);
                    }
                    if (!empty($filter_item_fg)) {
                        $this->db->where('d.item_fg_id', $filter_item_fg);
                    }

                    $this->db->group_by([
                        'h.doc_no',
                        'd.item_fg_id'
                    ]);

                    $this->db->order_by('h.doc_no', 'ASC');
                    $this->db->order_by('d.item_fg_id', 'ASC');

                    $rows = $this->db->get()->result();

                    $no = 1;
                    foreach ($rows as $row) {

                        $html .= '
                        <tr align="center">
                            <td>'.$no.'</td>
                            <td>'.$row->doc_no.'</td>
                            <td>'.$row->item_fg_id.'</td>
                            <td>'.$row->item_fg_number.'</td>
                            <td>'.$row->item_fg_name.'</td>
                            <td align="right">'.number_format($row->total_qty_sto,0,",",".").'</td>
                            <td>'.$row->label_type.'</td>
                            <td>'.$row->uom.'</td>
                            <td width="120px">'.$row->created_by.'</td>
                            <td width="30px">'.$row->created_date.'</td>
                        </tr>';

                        $no++;
                    }

                $html .= '</table></div></div>';
            }

        $html .='</body></html>';

        echo $html;
    }

    public function excel_label()
    {
        $format  = date("Ymd");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=report_sto_wip_fg_labels_$format.xls");

        $filter_period_month    = base64_decode($this->input->get("filter_period_month"));
        $filter_period_year     = base64_decode($this->input->get("filter_period_year"));
        $filter_location        = base64_decode($this->input->get("filter_location"));
        $filter_location_name   = base64_decode($this->input->get("filter_location_name"));
        $filter_doc_no          = base64_decode($this->input->get("filter_doc_no"));
        $filter_label_type      = base64_decode($this->input->get("filter_label_type"));
        $filter_item_fg         = base64_decode($this->input->get("filter_item_fg"));

        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $period_month_display = date('F', mktime(0, 0, 0, (int)$filter_period_month, 1));
        $schedule_display = $period_month_display . ' ' . $filter_period_year;
        $location_display = $filter_location != '' ? $filter_location_name : '-';

        $cut_of_stock = $filter_period_year . '-' . str_pad($filter_period_month, 2, '0', STR_PAD_LEFT) . '-25';
        $overflow = 'overflow: hidden;';

        $html = '<html>
                 <head>
                    <title>Print Data</title>
                </head>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }
                    #sto_wip_fg tr:hover {
                        background-color: #ddd;
                    }

                    .table-container {
                        overflow: auto;
                    }
                    table#sto_wip_fg {
                        border-collapse: separate;
                        border-spacing: 0;
                        /* width: max-content; */
                        font-size: 12px;
                    }
                    table#sto_wip_fg {
                        border: none;
                    }
                    table#sto_wip_fg th, table#sto_wip_fg td {
                        border: 1px solid #ddd;
                        border-right: none;
                        border-bottom: none;
                    }
                    table#sto_wip_fg th:last-child, table#sto_wip_fg td:last-child {
                        border-right: 1px solid #ddd;
                    }
                    table#sto_wip_fg tr:last-child td {
                        border-bottom: 1px solid #ddd;
                    }

                    .header {
                        position: sticky;
                        top: 0;
                        background-color: white;
                        z-index: 10;
                        padding-bottom: 20px !important;
                    }
                    #sto_wip_fg thead th {
                        position: sticky;
                        top: 0;
                        z-index: 200;
                        background: #f2f2f2;
                    }

                    #table-detail {
                        max-height: 72vh;
                        margin-left: 18px;
                        background: white !important;
                    }
                    #table-detail::before {
                        content: "";
                        position: sticky;
                        left: 0;
                        width: 18px;
                        background: white;
                        z-index: 10;
                        display: block;
                    }

                    #sto_wip_fg th,
                    #sto_wip_fg td {
                        padding: 4px 8px;
                        white-space: nowrap;
                    }
                    .table-container {
                        max-height: 72vh;
                        overflow: auto;
                    }

                    .freeze-col {
                        position: sticky;
                        left: 0;
                        background: white;
                        z-index: 50;
                    }

                    #sto_wip_fg thead .freeze-col {
                        z-index: 250;
                    }

                    #sto_wip_fg th,
                    #sto_wip_fg td {
                        box-sizing: border-box;
                    }

                    .freeze-col {
                        position: sticky;
                        background: #fff;
                        z-index: 20;
                    }

                    table#sto_wip_fg {
                        width: max-content;
                    }

                </style>
                <body style="margin: 0; '.$overflow.'">
                <div class="header" style="padding: 18px;">
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
                        <h3>REPORT STO WIP FG LABEL</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Location</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">'. $location_display .'</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Cut Of Stock</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $cut_of_stock . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $schedule_display . '</td>
                    </tr>
                </table>
                </div>';

                $html .= '<div class="table-container" style="overflow:auto; margin: 0 18px;">
                            <table id="sto_wip_fg" style="width: 100%; padding: 0px;" border="1">
                            <thead style="position: sticky; z-index: 100; top: 0px; background: #f2f2f2;">
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">STO Doc No</th>
                                <th rowspan="2">Product ID</th>
                                <th rowspan="2">Product No</th>
                                <th rowspan="2">Product Name</th>
                                <th rowspan="2">Qty STO</th>
                                <th rowspan="2">Workorder</th>
                                <th rowspan="2">Serial Label</th>
                                <th rowspan="2">Label Type</th>
                                <th rowspan="2">Uom</th>
                                
                                <th colspan="2">Created</th>
                            </tr>
                            <tr>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            ';

                    $this->db->select("
                        h.id,
                        h.doc_no,
                        h.created_by,
                        h.created_date,

                        d.item_fg_id,
                        d.label_type,

                        d.qty AS qty_sto,
                        d.workorder,

                        COALESCE(d.workorder_label, d.serial_label) as serial_label,

                        fg.number AS item_fg_number,
                        fg.name AS item_fg_name,
                        fg.uom
                    ");
                    $this->db->from('sto_wip_fg h');
                    $this->db->join('sto_wip_fg_detail d', 'd.sto_wip_fg_id = h.id');
                    $this->db->join('item_fg fg', 'fg.id = d.item_fg_id', 'left');

                    $this->db->where('h.type_status', 'completed');
                    $this->db->where('h.deleted', 0);

                    if (!empty($filter_location)) {
                        $this->db->where('h.location', $filter_location);
                    }
                    if (!empty($filter_period_month)) {
                        $this->db->where('h.period_month', $filter_period_month);
                    }
                    if (!empty($filter_period_year)) {
                        $this->db->where('h.period_year', $filter_period_year);
                    }
                    if (!empty($filter_doc_no)) {
                        $this->db->where('h.doc_no', $filter_doc_no);
                    }
                    if (!empty($filter_label_type)) {
                        $this->db->where('d.label_type', $filter_label_type);
                    }
                    if (!empty($filter_item_fg)) {
                        $this->db->where('d.item_fg_id', $filter_item_fg);
                    }

                    $this->db->order_by('h.doc_no', 'ASC');
                    $this->db->order_by('d.item_fg_id', 'ASC');

                    $rows = $this->db->get()->result();

                    $no = 1;
                    foreach ($rows as $row) {

                        $html .= '
                        <tr align="center">
                            <td>'.$no.'</td>
                            <td>'.$row->doc_no.'</td>
                            <td>'.$row->item_fg_id.'</td>
                            <td>'.$row->item_fg_number.'</td>
                            <td>'.$row->item_fg_name.'</td>
                            <td align="right">'.number_format($row->qty_sto,0,",",".").'</td>
                            <td>'.$row->workorder.'</td>
                            <td>'.$row->serial_label.'</td>
                            <td>'.$row->label_type.'</td>
                            <td>'.$row->uom.'</td>
                            <td width="120px">'.$row->created_by.'</td>
                            <td width="30px">'.$row->created_date.'</td>
                        </tr>';

                        $no++;
                    }

                $html .= '</table></div></div>';

        $html .='</body></html>';

        echo $html;
    }
}
