<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_visual_checker extends CI_Controller
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
            $this->load->view('control/report_visual_checker');
        } else {
            redirect('error_access');
        }
    }

    public function readSourceLists()
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_visual_checker$format.xls");
        }

        $filter_date_from   = base64_decode($this->input->get("filter_date_from"));
        $filter_date_to     = base64_decode($this->input->get("filter_date_to"));
        $filter_ng_kind     = base64_decode($this->input->get("filter_ng_kind"));
        $filter_item_fg     = base64_decode($this->input->get("filter_item_fg"));
        $filter_display     = base64_decode($this->input->get("filter_display"));
        $filter_source      = base64_decode($this->input->get("filter_source"));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $item_fg = empty($filter_item_fg) ? "ALL" : $filter_item_fg;
        $schedule_display = $filter_date_from . ' To ' . $filter_date_to;
        // $overflow = $filter_display === "RECAP" ? '' : 'overflow-y: hidden;';
        $overflow = 'overflow: hidden;';

        $html = '<html>
                 <head>
                    <title>Print Data</title>
                </head>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }
                    #scan_vc tr:hover {
                        background-color: #ddd;
                    }

                    .table-container {
                        overflow: auto;
                    }
                    table#scan_vc {
                        border-collapse: separate;
                        border-spacing: 0;
                        /* width: max-content; */
                        font-size: 12px;
                    }
                    table#scan_vc {
                        border: none;
                    }
                    table#scan_vc th, table#scan_vc td {
                        border: 1px solid #ddd;
                        border-right: none;
                        border-bottom: none;
                    }
                    table#scan_vc th:last-child, table#scan_vc td:last-child {
                        border-right: 1px solid #ddd;
                    }
                    table#scan_vc tr:last-child td {
                        border-bottom: 1px solid #ddd;
                    }

                    .header {
                        position: sticky;
                        top: 0;
                        background-color: white;
                        z-index: 10;
                        padding-bottom: 20px !important;
                    }
                    #scan_vc thead th {
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

                    #scan_vc th,
                    #scan_vc td {
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

                    #scan_vc thead .freeze-col {
                        z-index: 250;
                    }

                    #scan_vc th,
                    #scan_vc td {
                        box-sizing: border-box;
                    }

                    .freeze-col {
                        position: sticky;
                        background: #fff;
                        z-index: 20;
                    }

                    .col-no        { width: 50px;  min-width: 50px;  left: 0; }
                    .col-date      { width: 110px; min-width: 110px; left: 50px; }
                    .col-process   { width: 140px; min-width: 140px; left: 160px; }
                    .col-id        { width: 100px;  min-width: 100px;  left: 300px; }
                    .col-number    { width: 170px; min-width: 170px; left: 400px; }

                    .col-name      { width: 300px; min-width: 300px; left: 570px; }
                    .col-workorder { width: 160px; min-width: 160px; left: 870px; }

                    .col-workorder {
                        box-shadow: 3px 0 6px rgba(0,0,0,0.08);
                    }

                    table#scan_vc {
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
                        <h3>' . $filter_display . ' REPORT VISUAL CHECKER</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $schedule_display . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Product No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $item_fg . '</td>
                    </tr>
                </table>
                </div>';

        if ($filter_display == "RECAP") {
            $this->db->select("
                a.check_date as check_date,
                a.visual_process as visual_process,
                a.customer_id as customer_id,
                b.item_fg_id,
                d.number as item_fg_number,
                d.name as item_fg_name,

                SUM(b.qty_ok) AS ok,
                SUM(b.qty_rework) AS rework,

                SUM(COALESCE(ng.ng_production,0)) AS ng_production,
                SUM(COALESCE(ng.ng_finishing,0)) AS ng_finishing,
                SUM(COALESCE(ng.total_ng,0)) AS total_ng,

                (SUM(b.qty_ok) + SUM(b.qty_rework) + SUM(COALESCE(ng.total_ng,0))) AS total_check,

                ROUND(
                    SUM(COALESCE(ng.ng_production,0)) 
                    / NULLIF((SUM(b.qty_ok) + SUM(COALESCE(ng.total_ng,0))),0) * 100,2
                ) AS ng_production_percent,

                ROUND(
                    SUM(COALESCE(ng.ng_finishing,0)) 
                    / NULLIF((SUM(b.qty_ok) + SUM(COALESCE(ng.total_ng,0))),0) * 100,2
                ) AS ng_finishing_percent,

                ROUND(
                    SUM(COALESCE(ng.total_ng,0)) 
                    / NULLIF((SUM(b.qty_ok) + SUM(COALESCE(ng.total_ng,0))),0) * 100,2
                ) AS total_ng_percent
            ");

            $this->db->from('scan_visual_checker_detail b');
            $this->db->join('scan_visual_checker a', 'a.id = b.visual_checker_id');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');

            $this->db->join("
                (SELECT 
                    detail_id,
                    SUM(CASE WHEN ng_code <> 'NG-13' THEN qty_ng ELSE 0 END) AS ng_production,
                    SUM(CASE WHEN ng_code = 'NG-13' THEN qty_ng ELSE 0 END) AS ng_finishing,
                    SUM(qty_ng) AS total_ng
                FROM scan_visual_checker_ng
                GROUP BY detail_id
            ) ng", 'b.id = ng.detail_id', 'left');

            $this->db->where('a.deleted', 0);
            $this->db->where('b.deleted', 0);
            $this->db->where('b.type_status', 'finished');

            if($filter_date_from != "" && $filter_date_to != "") {
                $this->db->where("a.check_date BETWEEN '$filter_date_from' AND '$filter_date_to'");
            }

            if ($filter_item_fg != "") {
                $this->db->where('b.item_fg_id', $filter_item_fg);
            }

            $this->db->group_by([
                'a.check_date',
                'a.visual_process',
                'b.item_fg_id',
            ]);

            $this->db->order_by('a.check_date', 'ASC');
            $this->db->order_by('b.item_fg_id', 'ASC');

            $records = $this->db->get()->result_array();

            $html .= '<div class="table-container" style="overflow:auto; margin: 0 18px;">
                        <table id="scan_vc" style="width: 100%; padding: 0px;" border="1">
                        <thead style="position: sticky; z-index: 100; top: 0px; background: #f2f2f2;">
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Check Date</th>
                            <th rowspan="2">Visual Process</th>
                            <th rowspan="2">Product ID</th>
                            <th rowspan="2">Product No</th>
                            <th rowspan="2">Product Name</th>
                            
                            <th colspan="6">QTY (pcs)</th>

                            <th rowspan="2">NG Production %</th>
                            <th rowspan="2">NG Finishing %</th>
                            <th rowspan="2">Total NG %</th>
                        </tr>
                        <tr>
                            <th style="width: 70px">OK</th>
                            <th>Rework</th>
                            <th>NG Production</th>
                            <th>NG Finishing</th>
                            <th>Total NG</th>
                            <th>Total Check</th>
                        </tr>
                        </thead>
                        ';

            $no = 1;

            foreach ($records as $data) {
                $html .= '<tr align="center">
                            <td>' . $no . '</td>
                            <td>' . $data['check_date'] . '</td>
                            <td>' . $data['visual_process'] . '</td>
                            <td>' . $data['item_fg_id'] . '</td>
                            <td align="left">' . $data['item_fg_number'] . '</td>
                            <td align="left">' . $data['item_fg_name'] . '</td>
                            <td align="center">' . number_format($data['ok'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['rework'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['ng_production'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['ng_finishing'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['total_ng'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['total_check'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['ng_production_percent'], 2, ',', '.') . '</td>
                            <td align="center">' . number_format($data['ng_finishing_percent'], 2, ',', '.') . '</td>
                            <td align="center">' . number_format($data['total_ng_percent'], 2, ',', '.') . '</td>
                        </tr>';
                $no++;
            }
        } else {
            // KODE DETAIL

            $this->db->select("
                a.check_date,
                a.visual_process,
                a.customer_id,

                b.item_fg_id,
                d.number as item_fg_number,
                d.name as item_fg_name,

                b.workorder_label,
                op.trans_date as prod_date,
                op.operator as operator_press,
                e.number as machine_no,
                op.mold_id as mold_id,
                f.name as operator_checker,

                b.compound_lot_no,
                b.source,
                b.operator_finishing,
                b.id as detail_id,

                b.qty_ok as ok,
                b.qty_rework as rework,

                COALESCE(ng.ng_production,0) as ng_production,
                COALESCE(ng.ng_finishing,0) as ng_finishing,
                COALESCE(ng.total_ng,0) as total_ng,

                (b.qty_ok + b.qty_rework + COALESCE(ng.total_ng,0)) as total_check,

                ROUND(
                    COALESCE(ng.ng_production,0) 
                    / NULLIF((b.qty_ok + COALESCE(ng.total_ng,0)),0) * 100,2
                ) as ng_production_percent,

                ROUND(
                    COALESCE(ng.ng_finishing,0) 
                    / NULLIF((b.qty_ok + COALESCE(ng.total_ng,0)),0) * 100,2
                ) as ng_finishing_percent,

                ROUND(
                    COALESCE(ng.total_ng,0) 
                    / NULLIF((b.qty_ok + COALESCE(ng.total_ng,0)),0) * 100,2
                ) as total_ng_percent
            ", false);

            $this->db->from('scan_visual_checker_detail b');
            $this->db->join('scan_visual_checker a', 'a.id = b.visual_checker_id');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');

            $this->db->join("
                (SELECT 
                    detail_id,
                    ng_code,
                    SUM(CASE WHEN ng_code <> 'NG-13' THEN qty_ng ELSE 0 END) AS ng_production,
                    SUM(CASE WHEN ng_code = 'NG-13' THEN qty_ng ELSE 0 END) AS ng_finishing,
                    SUM(qty_ng) AS total_ng
                FROM scan_visual_checker_ng
                GROUP BY detail_id
            ) ng", 'b.id = ng.detail_id', 'left');

            $this->db->join('output_production_press_detail opd', 'b.workorder = opd.workorder and b.workorder_label = opd.workorder_label', 'left');

            $this->db->join('output_production_press op', 'opd.number_output = op.number and opd.workorder = op.workorder', 'left');

            $this->db->join('machines e', 'e.id = op.machine_id', 'left');

            $this->db->join('man_powers f', 'a.inspector = f.nik', 'left');

            $this->db->where('a.deleted', 0);
            $this->db->where('b.deleted', 0);
            $this->db->where('b.type_status', 'finished');

            if($filter_date_from != "" && $filter_date_to != "") {
                $this->db->where("a.check_date BETWEEN '$filter_date_from' AND '$filter_date_to'");
            }

            if ($filter_item_fg != "") {
                $this->db->where('b.item_fg_id', $filter_item_fg);
            }

            if ($filter_ng_kind != "") {
                $this->db->where("
                    EXISTS (
                        SELECT 1 
                        FROM scan_visual_checker_ng x
                        WHERE x.detail_id = b.id
                        AND x.ng_code = " . $this->db->escape($filter_ng_kind) . "
                    )
                ", null, false);
            }

            if ($filter_source != "") {
                $this->db->where('b.source', $filter_source);
            }

            $this->db->order_by('a.check_date', 'ASC');
            $this->db->order_by('b.item_fg_id', 'ASC');
            $this->db->order_by('b.workorder_label', 'ASC');

            $records = $this->db->get()->result_array();

            $master_ng = $this->db->get('master_ng')->result_array();
            $ng_rows = $this->db->query("
                SELECT detail_id, ng_code, SUM(qty_ng) as qty_ng
                FROM scan_visual_checker_ng
                GROUP BY detail_id, ng_code
            ")->result_array();

            $ng_map = [];

            foreach ($ng_rows as $row) {
                $ng_map[$row['detail_id']][$row['ng_code']] = $row['qty_ng'];
            }

            // <div class="table-container" style="overflow:auto; margin: 0 18px;">

            $html .= '<div class="table-container" style="overflow:auto; margin: 0 18px;">
                        <table id="scan_vc" style="width: 100%; padding: 0px;" border="1">
                        <thead style="position: sticky; z-index: 100; top: 0px; background: #f2f2f2;">
                        <tr>
                            <th class="freeze-col col-no" rowspan="2">No</th>
                            <th class="freeze-col col-date" rowspan="2">Check Date</th>
                            <th class="freeze-col col-process" rowspan="2">Visual Process</th>
                            <th class="freeze-col col-id" rowspan="2">Product ID</th>
                            <th class="freeze-col col-number" rowspan="2">Product No</th>
                            <th class="freeze-col col-name" rowspan="2">Product Name</th>

                            <th class="freeze-col col-workorder" rowspan="2">Workorder Label</th>
                            <th rowspan="2">Prod Date</th>
                            <th rowspan="2">Operator Press</th>
                            <th rowspan="2">Machine No</th>
                            <th rowspan="2">Mold ID</th>
                            <th rowspan="2">Compound Lot No</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Operator Finishing</th>
                            <th rowspan="2">Inspector</th>

                            <th colspan="6">QTY (pcs)</th>

                            <th rowspan="2">NG Production %</th>
                            <th rowspan="2">NG Finishing %</th>
                            <th rowspan="2">Total NG %</th>
                            ';

                            foreach ($master_ng as $ng) {
                                $html .= '<th rowspan="2">' . $ng['name'] . '</th>';
                            }
                            $html .='

                        </tr>
                        <tr>
                            <th style="width: 50px">OK</th>
                            <th>Rework</th>
                            <th>NG Production</th>
                            <th>NG Finishing</th>
                            <th>Total NG</th>
                            <th>Total Check</th>
                        </tr>
                        </thead>
                        ';

            $no = 1;

            foreach ($records as $data) {
                $html .= '<tr align="center">
                            <td class="freeze-col col-no">' . $no . '</td>
                            <td class="freeze-col col-date">' . $data['check_date'] . '</td>
                            <td class="freeze-col col-process">' . $data['visual_process'] . '</td>
                            <td class="freeze-col col-id">' . $data['item_fg_id'] . '</td>
                            <td class="freeze-col col-number" align="left">' . $data['item_fg_number'] . '</td>
                            <td class="freeze-col col-name" align="left">' . $data['item_fg_name'] . '</td>

                            <td class="freeze-col col-workorder" align="center">' . $data['workorder_label'] . '</td>
                            <td align="center">' . $data['prod_date'] . '</td>
                            <td align="center">' . $data['operator_press'] . '</td>
                            <td align="center">' . $data['machine_no'] . '</td>
                            <td align="center">' . $data['mold_id'] . '</td>
                            <td align="center">' . $data['compound_lot_no'] . '</td>
                            <td align="center">' . $data['source'] . '</td>
                            <td align="center">' . $data['operator_finishing'] . '</td>
                            <td align="center">' . $data['operator_checker'] . '</td>

                            <td align="center">' . number_format($data['ok'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['rework'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['ng_production'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['ng_finishing'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['total_ng'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['total_check'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['ng_production_percent'], 2, ',', '.') . '</td>
                            <td align="center">' . number_format($data['ng_finishing_percent'], 2, ',', '.') . '</td>
                            <td align="center">' . number_format($data['total_ng_percent'], 2, ',', '.') . '</td>
                            ';

                            foreach ($master_ng as $ng) {
                                $qty = isset($ng_map[$data['detail_id']][$ng['code']])
                                    ? $ng_map[$data['detail_id']][$ng['code']]
                                    : 0;

                                $html .= '<td align="center">' . number_format($qty, 0, '.', '.') . '</td>';
                            }
                            $html .='

                        </tr>';
                $no++;
            }

        }

        $html .= '</table></div></div>';

        $html .='</body></html>';

        echo $html;
    }
}
