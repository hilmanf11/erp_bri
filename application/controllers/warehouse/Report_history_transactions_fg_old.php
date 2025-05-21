<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_fg extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_fg');
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
            header("Content-Disposition: attachment; filename=history_transactions_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_product_family = $this->input->get("filter_product_family");
        $filter_qty_in = $this->input->get("filter_qty_in");
        $filter_qty_out = $this->input->get("filter_qty_out");
        
        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $qty_in_condition = "";
        if ($filter_qty_in == "ZERO") {
            $qty_in_condition = "HAVING qty_in = 0";
        } else if ($filter_qty_in == "NONZERO") {
            $qty_in_condition = "HAVING qty_in > 0";
        }

        $qty_out_condition = "";
        if ($filter_qty_out == "ZERO") {
            $qty_out_condition = "HAVING qty_out = 0";
        } else if ($filter_qty_out == "NONZERO") {
            $qty_out_condition = "HAVING qty_out > 0";
        }

        // Gabungkan kondisi qty_in dan qty_out
        $having_condition = "";
        if ($qty_in_condition != "" && $qty_out_condition != "") {
            $having_condition = str_replace("HAVING", "AND", $qty_out_condition);
            $having_condition = $qty_in_condition . " " . $having_condition;
        } else if ($qty_in_condition != "") {
            $having_condition = $qty_in_condition;
        } else if ($qty_out_condition != "") {
            $having_condition = $qty_out_condition;
        }

        $where_condition = "WHERE a.id like '%$filter_items%'";
        if (!empty($filter_product_family)) {
            $where_condition .= " AND a.item_family_number = '$filter_product_family'";
        }

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name,
            a.uom,
            a.item_family_number,
            f.name as family_name,
            COALESCE((
                -- Total Qty IN (fg_scan_in_label + os_fg)
                (
                    SELECT IFNULL(SUM(f2.qty), 0)
                    FROM fg_scan_in_label f2
                    WHERE f2.item_fg_id = a.id
                    AND f2.deleted = 0
                    AND f2.scan_date < DATE('$filter_from')
                ) +
                (
                    SELECT IFNULL(SUM(o2.qty), 0)
                    FROM os_fg o2
                    WHERE o2.item_fg_id = a.id
                    AND o2.deleted = 0
                    AND o2.trans_date < DATE('$filter_from')
                ) -
                -- Total Qty OUT (shipping_orders)
                (
                    SELECT IFNULL(SUM(sh2.qty), 0)
                    FROM shipping_orders sh2
                    WHERE sh2.item_fg_id = a.id
                    AND sh2.deleted = 0
                    AND sh2.created_date < DATE('$filter_from')
                )
            ), 0) as begin_stock,
            COALESCE((SELECT SUM(f.qty) FROM fg_scan_in_label f WHERE f.item_fg_id = a.id AND f.deleted = 0 AND f.scan_date BETWEEN '$filter_from' AND '$filter_to'), 0) +
            COALESCE((SELECT SUM(o.qty) FROM os_fg o WHERE o.item_fg_id = a.id AND o.deleted = 0 AND o.trans_date BETWEEN '$filter_from' AND '$filter_to'), 0) as qty_in,
            COALESCE((SELECT SUM(sh.qty) FROM shipping_orders sh 
                WHERE sh.item_fg_id = a.id 
                AND sh.deleted = 0 
                AND DATE(sh.created_date) BETWEEN '$filter_from' AND '$filter_to'), 0) as qty_out,
            (COALESCE((SELECT 
                (COALESCE(SUM(CASE 
                    WHEN f2.deleted = 0 AND DATE(f2.scan_date) < '$filter_from'
                    THEN f2.qty 
                    ELSE 0 
                END),0) + 
                COALESCE(SUM(CASE 
                    WHEN o2.deleted = 0 AND DATE(o2.trans_date) < '$filter_from'
                    THEN o2.qty
                    ELSE 0 
                END),0) - 
                COALESCE(SUM(CASE 
                    WHEN sh2.deleted = 0 AND DATE(sh2.created_date) < '$filter_from'
                    THEN sh2.qty
                    ELSE 0 
                END),0))
            FROM fg_scan_in_label f2
            LEFT JOIN os_fg o2 ON f2.item_fg_id = o2.item_fg_id
            LEFT JOIN shipping_orders sh2 ON f2.item_fg_id = sh2.item_fg_id
            WHERE f2.item_fg_id = a.id), 0) +
            COALESCE((SELECT SUM(f.qty) FROM fg_scan_in_label f WHERE f.item_fg_id = a.id AND f.deleted = 0 AND f.scan_date BETWEEN '$filter_from' AND '$filter_to'), 0) +
            COALESCE((SELECT SUM(o.qty) FROM os_fg o WHERE o.item_fg_id = a.id AND o.deleted = 0 AND o.trans_date BETWEEN '$filter_from' AND '$filter_to'), 0) -
            COALESCE((SELECT SUM(sh.qty) FROM shipping_orders sh 
                WHERE sh.item_fg_id = a.id 
                AND sh.deleted = 0 
                AND DATE(sh.created_date) BETWEEN '$filter_from' AND '$filter_to'), 0)) as end_stock
        FROM item_fg a 
        LEFT JOIN item_familys f ON a.item_family_number = f.number
        $where_condition
        GROUP BY a.id
        $having_condition
        ORDER BY a.number");
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
            <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (FG)</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="3">Product No</th>
                    <th colspan="2">Product Name</th>
                    <th>Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';
        $no = 1;
        $total_begin = 0;
        $total_in = 0;
        $total_out = 0;
        $total_end = 0;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $endstock = $this->crud->query("SELECT
                a.id,
                COALESCE((
                    -- Total Qty IN (fg_scan_in_label + os_fg)
                    (
                        SELECT IFNULL(SUM(f2.qty), 0)
                        FROM fg_scan_in_label f2
                        WHERE f2.item_fg_id = a.id
                        AND f2.deleted = 0
                        AND f2.scan_date < DATE('$filter_from')
                    ) +
                    (
                        SELECT IFNULL(SUM(o2.qty), 0)
                        FROM os_fg o2
                        WHERE o2.item_fg_id = a.id
                        AND o2.deleted = 0
                        AND o2.trans_date < DATE('$filter_from')
                    ) -
                    -- Total Qty OUT (shipping_orders)
                    (
                        SELECT IFNULL(SUM(sh2.qty), 0)
                        FROM shipping_orders sh2
                        WHERE sh2.item_fg_id = a.id
                        AND sh2.deleted = 0
                        AND sh2.created_date < DATE('$filter_from')
                    )
                ), 0) as begin_stock
            FROM item_fg a 
            WHERE a.id = '$item_fg_id'
            GROUP BY a.id
            ORDER BY a.number");

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3" style="mso-number-format:\@">' . $record->number . '</td>
                            <td colspan="2" style="mso-number-format:\@">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->family_name . '</td>
                            <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format(@$endstock[0]->begin_stock, 0, ',', '.') . '</td>
                            <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($record->qty_in, 0, ',', '.') . '</td>
                            <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($record->qty_out, 0, ',', '.') . '</td>
                            <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format((@$endstock[0]->begin_stock + $record->qty_in - $record->qty_out), 0, ',', '.') . '</td>
                        </tr>';

            // Menghitung total
            $total_begin += @$endstock[0]->begin_stock;
            $total_in += $record->qty_in;
            $total_out += $record->qty_out;
            $total_end += (@$endstock[0]->begin_stock + $record->qty_in - $record->qty_out);

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="12" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Production Date</th>
                                <th>Label No/DN No</th>
                                <th>Lot No</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';
                $nod = 1;
                $begin = @$endstock[0]->begin_stock;
                $balance = $begin;

                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    // FG Scan In Label
                    $scan_ins = $this->crud->query("SELECT 
                        f.*, 
                        u.name as username,
                        t.name as trans_type,
                        lp.compound_lot,
                        lp.prod_date
                    FROM fg_scan_in_label f
                    JOIN users u ON f.created_by = u.username
                    JOIN transaction_type t ON f.transaction_type = t.type
                    LEFT JOIN label_packing_detail lpd ON f.serial_label = lpd.serial_label
                    LEFT JOIN label_packing lp ON lpd.serial_no = lp.serial_no
                    WHERE f.item_fg_id = '$item_fg_id' 
                    AND DATE(f.scan_date) = '$working_date'
                    AND f.deleted = 0");

                    // OS FG
                    $os_fgs = $this->crud->query("SELECT 
                        o.*, 
                        u.name as username,
                        t.name as trans_type
                    FROM os_fg o
                    JOIN users u ON o.created_by = u.username
                    JOIN transaction_type t ON o.transaction_type = t.type
                    WHERE o.item_fg_id = '$item_fg_id' 
                    AND DATE(o.trans_date) = '$working_date'
                    AND o.deleted = 0");

                    // Shipping Orders
                    $shipping_orders = $this->crud->query("SELECT 
                        sh.*, 
                        u.name as username,
                        'Shipping Order' as trans_type,
                        CASE 
                            WHEN NULLIF(lp.compound_lot, '') IS NOT NULL THEN lp.compound_lot
                            WHEN NULLIF(nbf.compound_lot, '') IS NOT NULL THEN nbf.compound_lot
                            ELSE (
                                SELECT compound_lot 
                                FROM new_barcode_fg 
                                WHERE item_fg_id = sh.item_fg_id 
                                AND compound_lot IS NOT NULL 
                                AND LENGTH(TRIM(compound_lot)) > 0
                                GROUP BY compound_lot 
                                ORDER BY COUNT(*) DESC 
                                LIMIT 1
                            )
                        END as compound_lot,
                        CASE
                            WHEN lp.prod_date IS NOT NULL THEN lp.prod_date
                            WHEN nbf.prod_date IS NOT NULL THEN nbf.prod_date
                            ELSE (
                                SELECT prod_date 
                                FROM new_barcode_fg 
                                WHERE item_fg_id = sh.item_fg_id 
                                AND prod_date IS NOT NULL 
                                AND LENGTH(TRIM(prod_date)) > 0
                                GROUP BY prod_date 
                                ORDER BY COUNT(*) DESC 
                                LIMIT 1
                            )
                        END as prod_date,
                        do.actual_delivery_date as delivery_date
                    FROM shipping_orders sh
                    JOIN users u ON sh.created_by = u.username
                    LEFT JOIN label_packing_detail lpd ON sh.delivery_order_no = lpd.serial_label
                    LEFT JOIN label_packing lp ON lpd.serial_no = lp.serial_no
                    LEFT JOIN new_barcode_fg_detail nbfd ON sh.serial_label = nbfd.serial_label
                    LEFT JOIN new_barcode_fg nbf ON nbfd.request_no = nbf.request_no
                    LEFT JOIN delivery_orders do ON sh.delivery_order_no = do.delivery_order_no
                    WHERE sh.item_fg_id = '$item_fg_id' 
                    AND DATE(sh.created_date) = '$working_date'
                    AND sh.deleted = 0");

                    // Process FG Scan In
                    foreach ($scan_ins as $scan) {
                        $balance += $scan->qty;
                        $html .= '<tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $scan->trans_type . '</td>
                                    <td>' . $scan->username . '</td>
                                    <td>' . $scan->scan_date . '</td>
                                    <td>' . $scan->prod_date . '</td>
                                    <td>' . $scan->serial_label . '</td>
                                    <td>' . $scan->compound_lot . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($begin, 0, ',', '.') . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($scan->qty, 0, ',', '.') . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">0</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($balance, 0, ',', '.') . '</td>
                                </tr>';
                        $begin = $balance;
                        $nod++;
                    }

                    // Process OS FG
                    foreach ($os_fgs as $os) {
                        $balance += $os->qty;
                        $html .= '<tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $os->trans_type . '</td>
                                    <td>' . $os->username . '</td>
                                    <td>' . $os->trans_date . '</td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($begin, 0, ',', '.') . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($os->qty, 0, ',', '.') . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">0</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($balance, 0, ',', '.') . '</td>
                                </tr>';
                        $begin = $balance;
                        $nod++;
                    }

                    // Process Shipping Orders (OUT)
                    foreach ($shipping_orders as $order) {
                        $balance -= $order->qty;
                        $html .= '<tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $order->trans_type . '</td>
                                    <td>' . $order->username . '</td>
                                    <td>' . $order->delivery_date . '</td>
                                    <td>' . $order->prod_date . '</td>
                                    <td>' . $order->delivery_order_no . '</td>
                                    <td>' . $order->compound_lot . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($begin, 0, ',', '.') . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">0</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($order->qty, 0, ',', '.') . '</td>
                                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($balance, 0, ',', '.') . '</td>
                                </tr>';
                        $begin = $balance;
                        $nod++;
                    }
                }
            }
            $no++;
        }
        
        // Menambahkan baris total di dalam tabel yang sama
        $html .= '<tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="8" style="text-align: right;">TOTAL</td>
                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($total_begin, 0, ',', '.') . '</td>
                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($total_in, 0, ',', '.') . '</td>
                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($total_out, 0, ',', '.') . '</td>
                    <td style="text-align:right;mso-number-format:&quot;@&quot;">' . number_format($total_end, 0, ',', '.') . '</td>
                </tr>';
        $html .= '</table>';
        $html .= '</body></html>';
        echo $html;
    }
}