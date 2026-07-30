<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_wip extends CI_Controller
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
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/report_history_transactions_wip');
        } else {
            redirect('error_access');
        }
    }

    public function reads_product_family()
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('item_category_id', 'C01');
        $this->db->order_by('name', 'ASC');

        $data = $this->db->get()->result();
        echo json_encode($data);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_wip_$format.xls");
        }

        $filter_from            = $this->input->get('filter_from');
        $filter_to              = $this->input->get('filter_to');
        $filter_items           = $this->input->get('filter_items');
        $filter_display         = $this->input->get("filter_display");
        $filter_product_family  = $this->input->get("filter_product_family");
        $filter_customer_id     = $this->input->get("filter_customer_id");
        $filter_plant           = $this->input->get("filter_plant");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_display == "RECAP" ? "RECAP" : "DETAIL";

        $html = '<html><head><title>Print Data</title></head>
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }

                #history_wip {
                    border-collapse: collapse;
                    width: 100%;
                    font-size: 12px;
                }

                #history_wip td, 
                #history_wip th {
                    border: 1px solid #ddd;
                    padding: 2px;
                }

                #history_wip tr:nth-child(even){
                    background-color: #f2f2f2;
                }

                #history_wip tr:hover {
                    background-color: #ddd;
                }
                #history_wip th {
                    padding-top: 2px;
                    padding-bottom: 2px;
                    text-align: center;
                    color: black;
                }
                .text-center {
                    text-align: center;
                }
                .text-right {
                    text-align: right;
                }
            </style>
            <body>
                <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">HISTORICAL TRANSACTION WIP ('. $filter_display .')</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>

            <br>';

            if ($filter_display == "RECAP") {

                $where_condition = "WHERE a.status = 0";
                if (!empty($filter_items)) {
                    $where_condition .= " AND a.id = '$filter_items'";
                }

                if (!empty($filter_product_family)) {
                    $where_condition .= " AND a.item_family_number = '$filter_product_family'";
                }

                if (!empty($filter_plant)) {
                    $where_condition .= " AND a.division_id = '$filter_plant'";
                }

                if (!empty($filter_customer_id)) {
                    $where_condition .= "
                        AND EXISTS (
                            SELECT 1
                            FROM customer_items ci
                            WHERE ci.item_fg_id = a.id
                            AND ci.customer_id = '$filter_customer_id'
                        )
                    ";
                }

                // Begin & Ending Stock Regular

                $query_before_begin_base = "SELECT
                        item_fg_id,
                        SUM(qty) qty
                    FROM balance_begin_wip
                    WHERE deleted = 0
                    AND label_type = 'R01'
                    AND location IN ('WIP01', 'WIP02')
                    AND trans_date = '2026-05-25'
                    GROUP BY item_fg_id
                ";

                $query_before_output_press = "SELECT
                    b.item_fg_id,
                    SUM(b.qty_packing) qty
                FROM output_production_press a
                JOIN output_production_press_detail b
                    ON b.number_output = a.number
                    AND b.item_fg_id = a.item_fg_id
                    AND b.workorder = a.workorder
                WHERE b.status = 0
                AND a.trans_date >= '2026-05-25'
                AND a.trans_date < DATE('$filter_from')
                GROUP BY b.item_fg_id
                ";

                $query_before_in_internal = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_from_internal_finishing
                WHERE status = 0
                AND type_status = 'completed'
                AND created_date >= '2026-05-25'
                AND created_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_in_external = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Regular'
                AND status = 0
                AND type_status = 'completed'
                AND incoming_date >= '2026-05-25'
                AND incoming_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_in_return = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_return
                WHERE status = 0
                AND type_status = 'completed'
                AND created_date >= '2026-05-25'
                AND created_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_in_bpm = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_incoming_sctf
                WHERE incoming_type = 'BPM'
                AND status = 0
                AND type_status = 'completed'
                AND incoming_date >= '2026-05-25'
                AND incoming_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_out_internal = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_finishing
                WHERE status = 0
                AND type_status = 'completed'
                AND trans_date >= '2026-05-25'
                AND trans_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_out_external = "SELECT
                    d.item_fg_id,
                    SUM(s.qty) qty
                FROM delivery_to_subconts d
                JOIN shipping_to_subconts s
                    ON s.scan_id = d.scan_id
                    AND s.item_fg_id = d.item_fg_id
                    AND s.workorder = d.workorder
                WHERE s.status = 0
                AND s.type_status = 'completed'
                AND d.delivery_date >= '2026-05-25'
                AND d.delivery_date < DATE('$filter_from')
                GROUP BY d.item_fg_id
                ";


                // Begin & Ending Stock Rework

                $query_before_begin_base_rework = "SELECT
                        item_fg_id,
                        SUM(qty) qty
                    FROM balance_begin_wip
                    WHERE deleted = 0
                    AND label_type = 'R02'
                    AND location IN ('WIP01', 'WIP02')
                    AND trans_date = '2026-05-25'
                    GROUP BY item_fg_id
                ";

                $query_before_in_rework = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_rework
                WHERE status = 0
                AND type_status = 'completed'
                AND created_date >= '2026-05-25'
                AND created_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_incoming_rework = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Rework'
                AND status = 0
                AND type_status = 'completed'
                AND incoming_date >= '2026-05-25'
                AND incoming_date < DATE('$filter_from')
                GROUP BY item_fg_id
                ";

                $query_before_out_rework = "SELECT
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
                    AND d.delivery_date >= '2026-05-25'
                    AND d.delivery_date < DATE('$filter_from')
                    GROUP BY s.item_fg_id
                    ";

                $query_before_out_supply_vc = " SELECT
                        d.item_fg_id,
                        SUM(d.qty_on_label) qty
                    FROM scan_visual_checker h
                    JOIN scan_visual_checker_detail d
                        ON d.scan_id = h.scan_id
                        AND d.visual_checker_id = h.id
                    WHERE h.deleted = 0
                    AND d.deleted = 0
                    AND (
                        d.serial_label IS NULL
                        OR d.serial_label = ''
                    )
                    AND h.check_date >= '2026-05-25'
                    AND h.check_date < DATE('$filter_from')
                    GROUP BY d.item_fg_id
                ";

                $query_before_rw_to_vc = "SELECT
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

                    AND h.check_date >= '2026-05-25'
                    AND h.check_date < DATE('$filter_from')

                    GROUP BY d.item_fg_id
                ";


                // Query Data Transaction Date

                $query_output_press = "SELECT
                    b.item_fg_id,
                    SUM(b.qty_packing) qty
                FROM output_production_press a
                JOIN output_production_press_detail b
                    ON b.number_output = a.number
                    AND b.item_fg_id = a.item_fg_id
                    AND b.workorder = a.workorder
                WHERE b.status = 0
                AND a.trans_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY b.item_fg_id
                ";

                $query_in_internal = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_from_internal_finishing
                WHERE status = 0
                AND type_status = 'completed'
                AND DATE(created_date) BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_in_external = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Regular'
                AND status = 0
                AND type_status = 'completed'
                AND incoming_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_in_return = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_return
                WHERE status = 0
                AND type_status = 'completed'
                AND DATE(created_date) BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_in_bpm = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_incoming_sctf
                WHERE incoming_type = 'BPM'
                AND status = 0
                AND type_status = 'completed'
                AND incoming_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_in_rework = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_rework
                WHERE status = 0
                AND type_status = 'completed'
                AND created_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_incoming_rework = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Rework'
                AND status = 0
                AND type_status = 'completed'
                AND incoming_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_out_internal = "SELECT
                    item_fg_id,
                    SUM(qty) qty
                FROM scan_in_finishing
                WHERE status = 0
                AND type_status = 'completed'
                AND trans_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY item_fg_id
                ";

                $query_out_external = "SELECT
                    d.item_fg_id,
                    SUM(s.qty) qty
                FROM delivery_to_subconts d
                JOIN shipping_to_subconts s
                    ON s.scan_id = d.scan_id
                    AND s.item_fg_id = d.item_fg_id
                    AND s.workorder = d.workorder
                WHERE s.status = 0
                AND s.type_status = 'completed'
                AND d.delivery_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                GROUP BY d.item_fg_id
                ";

                $query_out_rework = "SELECT
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
                    AND d.delivery_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                    GROUP BY s.item_fg_id
                    ";

                $query_supply_vc = "SELECT
                        d.item_fg_id,
                        SUM(d.qty_on_label) qty
                    FROM scan_visual_checker h
                    JOIN scan_visual_checker_detail d
                        ON d.scan_id = h.scan_id
                        AND d.visual_checker_id = h.id
                    WHERE h.deleted = 0
                    AND d.deleted = 0
                    AND (
                        d.serial_label IS NULL
                        OR d.serial_label = ''
                    )
                    AND h.check_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                    GROUP BY d.item_fg_id
                ";

                $query_rw_to_vc = "SELECT
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

                    AND h.check_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                    GROUP BY d.item_fg_id
                ";

                $query_begin_stock_regular = "SELECT
                        b.item_fg_id,

                        COALESCE(b.qty,0)

                        + COALESCE(op.qty,0)
                        + COALESCE(ii.qty,0)
                        + COALESCE(ie.qty,0)
                        + COALESCE(ir.qty,0)
                        + COALESCE(bp.qty,0)

                        - COALESCE(oi.qty,0)
                        - COALESCE(oe.qty,0)
                        - COALESCE(svc.qty,0)

                        AS qty

                    FROM ($query_before_begin_base) b

                    LEFT JOIN ($query_before_output_press) op
                        ON b.item_fg_id = op.item_fg_id

                    LEFT JOIN ($query_before_in_internal) ii
                        ON b.item_fg_id = ii.item_fg_id

                    LEFT JOIN ($query_before_in_external) ie
                        ON b.item_fg_id = ie.item_fg_id

                    LEFT JOIN ($query_before_in_return) ir
                        ON b.item_fg_id = ir.item_fg_id

                    LEFT JOIN ($query_before_in_bpm) bp
                        ON b.item_fg_id = bp.item_fg_id

                    LEFT JOIN ($query_before_out_internal) oi
                        ON b.item_fg_id = oi.item_fg_id

                    LEFT JOIN ($query_before_out_external) oe
                        ON b.item_fg_id = oe.item_fg_id

                    LEFT JOIN ($query_before_out_supply_vc) svc
                        ON b.item_fg_id = svc.item_fg_id
                ";

                $query_begin_stock_rework = "SELECT
                        b.item_fg_id,

                        COALESCE(b.qty,0)

                        + COALESCE(ir.qty,0)
                        + COALESCE(irw.qty,0)

                        - COALESCE(orw.qty,0)
                        - COALESCE(rwvc.qty,0)

                        AS qty

                    FROM ($query_before_begin_base_rework) b

                    LEFT JOIN ($query_before_in_rework) ir
                        ON b.item_fg_id = ir.item_fg_id

                    LEFT JOIN ($query_before_incoming_rework) irw
                        ON b.item_fg_id = irw.item_fg_id

                    LEFT JOIN ($query_before_out_rework) orw
                        ON b.item_fg_id = orw.item_fg_id

                    LEFT JOIN ($query_before_rw_to_vc) rwvc
                        ON b.item_fg_id = rwvc.item_fg_id
                ";

                $records = $this->crud->query("SELECT
                        a.id,
                        a.number,
                        a.name,

                        COALESCE(bsr.qty,0) AS begin_stock_regular,
                        COALESCE(bsw.qty,0) AS begin_stock_rework,

                        COALESCE(op.qty,0) output_press,
                        COALESCE(ii.qty,0) in_internal,
                        COALESCE(ie.qty,0) in_external,
                        COALESCE(ir.qty,0) in_return,
                        COALESCE(bp.qty,0) in_bpm,

                        COALESCE(rw.qty,0) in_rework,
                        COALESCE(irw.qty,0) incoming_rework,

                        COALESCE(oi.qty,0) out_internal,
                        COALESCE(oe.qty,0) out_external,
                        COALESCE(svc.qty,0) supply_vc,

                        COALESCE(orw.qty,0) out_rework,
                        COALESCE(rwvc.qty,0) out_rw_to_vc

                    FROM item_fg a

                    LEFT JOIN ($query_output_press) op ON a.id = op.item_fg_id
                    LEFT JOIN ($query_in_internal) ii ON a.id = ii.item_fg_id
                    LEFT JOIN ($query_in_external) ie ON a.id = ie.item_fg_id
                    LEFT JOIN ($query_in_return) ir ON a.id = ir.item_fg_id
                    LEFT JOIN ($query_in_bpm) bp ON a.id = bp.item_fg_id

                    LEFT JOIN ($query_in_rework) rw ON a.id = rw.item_fg_id
                    LEFT JOIN ($query_incoming_rework) irw ON a.id = irw.item_fg_id

                    LEFT JOIN ($query_out_internal) oi ON a.id = oi.item_fg_id
                    LEFT JOIN ($query_out_external) oe ON a.id = oe.item_fg_id
                    LEFT JOIN ($query_supply_vc) svc ON a.id = svc.item_fg_id

                    LEFT JOIN ($query_out_rework) orw ON a.id = orw.item_fg_id
                    LEFT JOIN ($query_rw_to_vc) rwvc ON a.id = rwvc.item_fg_id

                    LEFT JOIN ($query_begin_stock_regular) bsr ON a.id = bsr.item_fg_id
                    LEFT JOIN ($query_begin_stock_rework) bsw ON a.id = bsw.item_fg_id

                    $where_condition
                    AND (
                        op.item_fg_id IS NOT NULL
                        OR ii.item_fg_id IS NOT NULL
                        OR ie.item_fg_id IS NOT NULL
                        OR ir.item_fg_id IS NOT NULL
                        OR bp.item_fg_id IS NOT NULL
                        OR rw.item_fg_id IS NOT NULL
                        OR irw.item_fg_id IS NOT NULL
                        OR oi.item_fg_id IS NOT NULL
                        OR oe.item_fg_id IS NOT NULL
                        OR orw.item_fg_id IS NOT NULL
                        OR svc.item_fg_id IS NOT NULL
                        OR rwvc.item_fg_id IS NOT NULL
                    )

                    GROUP BY a.id
                    ORDER BY a.number
                ");

                $html .= '
                    <table id="history_wip" border="1">

                        <tr>
                            <th class="text-center" rowspan="2" width="30">No</th>
                            <th rowspan="2" width="120">Product ID</th>
                            <th rowspan="2" width="150">Product No</th>
                            <th rowspan="2" width="250">Product Name</th>
                            <th rowspan="2" width="100">Begin Stock Regular</th>
                            <th rowspan="2" width="100">Begin Stock Rework</th>
                            <th colspan="5">Transaction In Regular</th>
                            <th rowspan="2" width="100">Total In Regular</th>
                            <th colspan="2">Transaction In Rework</th>
                            <th rowspan="2" width="100">Total In Rework</th>
                            <th colspan="3">Transaction Out Regular</th>
                            <th rowspan="2" width="100">Total Out Regular</th>
                            <th colspan="2" width="100">Transaction Out Rework</th>
                            <th rowspan="2" width="100">Total Out Rework</th>
                            <th rowspan="2" width="100">Ending Stock Regular</th>
                            <th rowspan="2" width="100">Ending Stock Rework</th>
                        </tr>

                        <tr>
                            <th width="100">Output Press</th>
                            <th width="120">In from Internal</th>
                            <th width="120">In from External</th>
                            <th width="100">In Return</th>
                            <th width="120">In BPM</th>
                            
                            <th width="120">In Rework</th>
                            <th width="120">Incoming Rework</th>

                            <th width="120">Out to Internal</th>
                            <th width="120">Out to External</th>
                            <th width="180">Supply to VC</th>

                            <th width="120">Out Rework</th>
                            <th width="120">RW to VC</th>
                        </tr>';

                // LOOP DATA RECAP

                $no = 1;

                foreach($records as $row){

                    $total_in_regular =
                        $row->output_press
                        + $row->in_internal
                        + $row->in_external
                        + $row->in_return
                        + $row->in_bpm;

                    $total_out_regular = $row->out_internal + $row->out_external + $row->supply_vc;

                    $total_in_rework = $row->in_rework + $row->incoming_rework;
                    $total_out_rework = $row->out_rework + $row->out_rw_to_vc;

                    // $ending_stock_regular = $total_in_regular - $total_out_regular;
                    // $ending_stock_rework = $total_in_rework - $row->out_rework;

                    $ending_stock_regular = $row->begin_stock_regular + $total_in_regular - $total_out_regular;
                    $ending_stock_rework = $row->begin_stock_rework + $total_in_rework - $total_out_rework;

                    $html .= '
                    <tr>
                        <td class="text-center">'.$no++.'</td>
                        <td>'.($row->id ?? '').'</td>
                        <td style="mso-number-format:\@">'. ($row->number ?? '') .'</td>
                        <td>'. ($row->name ?? '') .'</td>
                        <td class="text-right">'.number_format($row->begin_stock_regular, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->begin_stock_rework, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->output_press, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->in_internal, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->in_external, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->in_return, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->in_bpm, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($total_in_regular, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->in_rework, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->incoming_rework, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($total_in_rework, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->out_internal, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->out_external, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->supply_vc, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($total_out_regular, 0, ',', '.').'</td>

                        <td class="text-right">'.number_format($row->out_rework, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($row->out_rw_to_vc, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($total_out_rework, 0, ',', '.').'</td>

                        <td class="text-right">'.number_format($ending_stock_regular, 0, ',', '.').'</td>
                        <td class="text-right">'.number_format($ending_stock_rework, 0, ',', '.').'</td>

                    </tr>';
                }

                $html .= '</table>';

            } else if ($filter_display == "DETAIL") {

                $item_where = "WHERE status = 0";
                if (!empty($filter_items)) {
                    $item_where .= " AND id = '$filter_items'";
                }

                if (!empty($filter_product_family)) {
                    $item_where .= " AND item_family_number = '$filter_product_family'";
                }

                if (!empty($filter_plant)) {
                    $item_where .= " AND division_id = '$filter_plant'";
                }

                if (!empty($filter_customer_id)) {
                    $item_where .= "
                        AND EXISTS (
                            SELECT 1
                            FROM customer_items ci
                            WHERE ci.item_fg_id = item_fg.id
                            AND ci.customer_id = '$filter_customer_id'
                        )
                    ";
                }

                $items = $this->crud->query("
                    SELECT
                        id,
                        number,
                        name
                    FROM item_fg
                    $item_where
                ");

                $item_ids = [];
                $item_info = [];

                foreach ($items as $item) {

                    $item_ids[] = $item->id;

                    $item_info[$item->id] = [
                        'number' => $item->number,
                        'name'   => $item->name
                    ];
                }

                $use_item_filter =
                    !empty($filter_items) ||
                    !empty($filter_product_family) ||
                    !empty($filter_customer_id) ||
                    !empty($filter_plant);

                $item_ids_sql = '';

                if ($use_item_filter && !empty($item_ids)) {
                    $item_ids_sql = "'" . implode("','", $item_ids) . "'";
                }

                $query_output_press_detail = "SELECT
                        a.trans_date,
                        b.item_fg_id,
                        'IN' AS trans_type,
                        'Output Press' AS trans_name,
                        a.number AS doc_no,

                        SUM(b.qty_packing) AS qty_in,
                        0 AS qty_out

                    FROM output_production_press a
                    JOIN output_production_press_detail b
                        ON b.number_output = a.number
                        AND b.item_fg_id = a.item_fg_id
                        AND b.workorder = a.workorder

                    WHERE b.status = 0
                    AND a.trans_date >= '$filter_from'
                    AND a.trans_date <= '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND b.item_fg_id IN ($item_ids_sql)" : "")."

                    GROUP BY
                        a.trans_date,
                        b.item_fg_id,
                        a.number
                ";

                $query_in_internal_detail = "SELECT
                        DATE(created_date) trans_date,
                        item_fg_id,
                        'IN' AS trans_type,
                        'In From Internal' AS trans_name,
                        workorder_label AS doc_no,
                        SUM(qty) qty_in,
                        0 qty_out
                    FROM scan_in_from_internal_finishing
                    WHERE status = 0
                    AND type_status = 'completed'
                    -- AND DATE(created_date) BETWEEN DATE('$filter_from') AND DATE('$filter_to')
                    AND created_date >= '$filter_from 00:00:00'
                    AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                    ".(!empty($item_ids_sql) ? "AND item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY DATE(created_date),item_fg_id,workorder_label
                ";

                $query_in_external_detail = "SELECT
                        incoming_date trans_date,
                        item_fg_id,
                        'IN' AS trans_type,
                        'In From External' AS trans_name,
                        incoming_doc_no AS doc_no,
                        SUM(qty) qty_in,
                        0 qty_out
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Regular'
                    AND status = 0
                    AND type_status = 'completed'
                    AND incoming_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY incoming_date,item_fg_id,incoming_doc_no
                ";

                $query_in_return_detail = "SELECT
                        DATE(created_date) trans_date,
                        item_fg_id,
                        'IN' AS trans_type,
                        'In Return' AS trans_name,
                        serial_label AS doc_no,
                        SUM(qty) qty_in,
                        0 qty_out
                    FROM scan_in_return
                    WHERE status = 0
                    AND type_status = 'completed'
                    -- AND created_date BETWEEN '$filter_from' AND '$filter_to'
                    AND created_date >= '$filter_from 00:00:00'
                    AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                    ".(!empty($item_ids_sql) ? "AND item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY DATE(created_date),item_fg_id,serial_label
                ";

                $query_in_bpm_detail = "SELECT
                        incoming_date trans_date,
                        item_fg_id,
                        'IN' AS trans_type,
                        'In BPM' AS trans_name,
                        incoming_doc_no AS doc_no,
                        SUM(qty) qty_in,
                        0 qty_out
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'BPM'
                    AND status = 0
                    AND type_status = 'completed'
                    AND incoming_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY incoming_date,item_fg_id,incoming_doc_no
                ";

                $query_incoming_rework_detail = "SELECT
                        incoming_date trans_date,
                        item_fg_id,
                        'IN' AS trans_type,
                        'Incoming Rework' AS trans_name,
                        incoming_doc_no AS doc_no,
                        SUM(qty) qty_in,
                        0 qty_out
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Rework'
                    AND status = 0
                    AND type_status = 'completed'
                    AND incoming_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY incoming_date,item_fg_id,incoming_doc_no
                ";

                $query_out_internal_detail = "SELECT
                        trans_date,
                        item_fg_id,
                        'OUT' AS trans_type,
                        'Out to Internal' AS trans_name,
                        workorder_label AS doc_no,
                        0 qty_in,
                        SUM(qty) qty_out
                    FROM scan_in_finishing
                    WHERE status = 0
                    AND type_status = 'completed'
                    AND trans_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY trans_date,item_fg_id,workorder_label
                ";

                $query_out_external_detail = "SELECT
                        d.delivery_date trans_date,
                        d.item_fg_id,
                        'OUT' AS trans_type,
                        'Out to External' AS trans_name,
                        d.delivery_note_no as doc_no,
                        0 qty_in,
                        SUM(s.qty) qty_out
                    FROM delivery_to_subconts d
                    JOIN shipping_to_subconts s
                        ON s.scan_id = d.scan_id
                        AND s.item_fg_id = d.item_fg_id
                        AND s.workorder = d.workorder
                    WHERE s.status = 0
                    AND s.type_status = 'completed'
                    AND d.delivery_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND s.item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY d.delivery_date,d.item_fg_id,d.delivery_note_no
                ";

                $query_supply_vc_detail = "SELECT
                        h.check_date trans_date,
                        d.item_fg_id,
                        'OUT' AS trans_type,
                        'Supply Visual Checker' AS trans_name,
                        d.workorder_label AS doc_no,
                        0 qty_in,
                        SUM(d.qty_on_label) qty_out
                    FROM scan_visual_checker h
                    JOIN scan_visual_checker_detail d
                        ON d.scan_id = h.scan_id
                        AND d.visual_checker_id = h.id
                    WHERE h.deleted = 0
                    AND d.deleted = 0
                    AND (
                        d.serial_label IS NULL
                        OR d.serial_label = ''
                    )
                    AND h.check_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND d.item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY h.check_date,d.item_fg_id,d.workorder_label
                ";

                $query_out_rework_detail = "SELECT
                        d.delivery_date trans_date,
                        s.item_fg_id,
                        'OUT' AS trans_type,
                        'Out Rework' AS trans_name,
                        d.dnr_no AS doc_no,
                        0 qty_in,
                        SUM(s.qty) qty_out
                    FROM delivery_rework d
                    JOIN scan_out_rework s
                        ON s.dnr_no = d.dnr_no
                        AND s.item_fg_id = d.item_fg_id
                        AND s.scan_id = d.scan_id
                        AND s.workorder = d.workorder
                    WHERE d.status = 0
                    AND s.status = 0
                    AND s.type_status = 'completed'
                    AND d.delivery_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND s.item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY d.delivery_date,s.item_fg_id,d.dnr_no
                ";

                $query_rw_to_vc_detail = "SELECT
                        h.check_date trans_date,
                        d.item_fg_id,
                        'OUT' AS trans_type,
                        'Rework to VC' AS trans_name,
                        d.serial_label AS doc_no,
                        0 qty_in,
                        SUM(d.qty_on_label) qty_out
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
                    AND h.check_date BETWEEN '$filter_from' AND '$filter_to'
                    ".(!empty($item_ids_sql) ? "AND d.item_fg_id IN ($item_ids_sql)" : "")."
                    GROUP BY h.check_date,d.item_fg_id,d.serial_label
                ";

                $records = [];

                if (!empty($item_ids)) {

                    $queries = [
                        $query_output_press_detail,
                        $query_in_internal_detail,
                        $query_in_external_detail,
                        $query_in_return_detail,
                        $query_in_bpm_detail,
                        $query_incoming_rework_detail,
                        $query_out_internal_detail,
                        $query_out_external_detail,
                        $query_supply_vc_detail,
                        $query_out_rework_detail,
                        $query_rw_to_vc_detail
                    ];

                    foreach ($queries as $sql) {

                        $rows = $this->crud->query($sql);

                        foreach ($rows as $row) {

                            $row->item_fg_number =
                                $item_info[$row->item_fg_id]['number'] ?? '';

                            $row->item_fg_name =
                                $item_info[$row->item_fg_id]['name'] ?? '';

                            $records[] = $row;
                        }
                    }
                }

                usort($records, function ($a, $b) {

                    $cmp = strcmp($a->trans_date, $b->trans_date);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    $cmp = strcmp($a->item_fg_id, $b->item_fg_id);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    $cmp = strcmp($a->trans_name, $b->trans_name);

                    if ($cmp !== 0) {
                        return $cmp;
                    }

                    return strcmp($a->doc_no, $b->doc_no);
                });                

                $html .= '
                    <table id="history_wip" border="1">

                        <tr>
                            <th width="30">No</th>
                            <th width="120">Trans Date</th>
                            <th width="120">Product ID</th>
                            <th width="150">Product No</th>
                            <th width="250">Product Name</th>
                            <th width="100">Trans Type</th>
                            <th width="180">Trans Name</th>
                            <th width="250">Doc No/Label No</th>
                            <th width="100">Begin<br>Stock</th>
                            <th width="100">In</th>
                            <th width="100">Out</th>
                            <th width="100">Ending<br>Stock</th>
                        </tr>';

                // LOOP DATA DETAIL
                $no = 1;

                foreach($records as $row){

                    $html .= '
                    <tr>
                        <td class="text-center">'.$no++.'</td>
                        <td class="text-center">'.date('Y-m-d', strtotime($row->trans_date)).'</td>
                        <td>'.$row->item_fg_id.'</td>
                        <td style="mso-number-format:\@">'.$row->item_fg_number.'</td>
                        <td>'.$row->item_fg_name.'</td>
                        <td class="text-center">'.$row->trans_type.'</td>
                        <td>'.$row->trans_name.'</td>
                        <td style="mso-number-format:\@">'.$row->doc_no.'</td>
                        <td class="text-right">0</td>
                        <td class="text-right">'.number_format($row->qty_in,0,",",".").'</td>
                        <td class="text-right">'.number_format($row->qty_out,0,",",".").'</td>
                        <td class="text-right">0</td>
                    </tr>';
                }

                $html .= '</table>';
            }

        $html .= '</body></html>';
        echo $html;
    }
}