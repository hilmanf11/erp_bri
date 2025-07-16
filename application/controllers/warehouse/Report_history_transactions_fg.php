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
        $filter_plant = $this->input->get("filter_plant");
        
        // $start = strtotime($filter_from);
        // $finish = strtotime($filter_to);

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

        if (!empty($filter_items)) {
            $where_condition = "WHERE a.id LIKE '%$filter_items%'";
        } else {
            $where_condition = "WHERE a.status = 0";
        }
        
        if (!empty($filter_product_family)) {
            $where_condition .= " AND a.item_family_number = '$filter_product_family'";
        }

        if (!empty($filter_plant)) {
            $where_condition .= " AND a.division_id = '$filter_plant'";
        }
        
        $cols3 = ($filter_display == "DETAIL") ? "3" : "1";
        $cols2 = ($filter_display == "DETAIL") ? "2" : "1";
        $cols5 = ($filter_display == "DETAIL") ? "8" : "5";

        //! Perhitungan qty_in untuk setiap item_fg_id

        //? Step 1: Hitung qty_in dari fg_scan_in_label
        $query_qty_fg_scan_in = "SELECT a.item_fg_id, SUM(a.qty) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        AND a.scan_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
        GROUP BY a.item_fg_id";

        //? Step 2: Hitung qty_in dari os_fg
        $query_qty_os_fg = "SELECT a.item_fg_id, SUM(a.qty) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        AND a.trans_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
        GROUP BY a.item_fg_id";

        //? Step 3: Hitung qty_in dari transaction_fg (RE)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date BETWEEN DATE('$filter_from') AND DATE('$filter_to')
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";


        //! Perhitungan qty_out untuk setiap item_fg_id

        //? Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date BETWEEN DATE('$filter_from') AND ('$filter_to')
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

        //? Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT dn.item_fg_id, SUM(dn.qty) as initial_out_g
        FROM delivery_notes dn
        JOIN (
            SELECT 
                delivery_order_no, 
                item_fg_id, 
                customer_order_no,
                SUM(qty) AS total_shipping_qty
            FROM shipping_orders
            WHERE deleted = 0
            GROUP BY delivery_order_no, item_fg_id, customer_order_no
        ) s ON dn.delivery_order_no = s.delivery_order_no
            AND dn.item_fg_id = s.item_fg_id
            AND dn.customer_order_no = s.customer_order_no
            AND dn.qty = s.total_shipping_qty
        WHERE dn.deleted = 0
        AND DATE(dn.delivery_note_date) BETWEEN DATE('$filter_from') AND DATE('$filter_to')
        GROUP BY item_fg_id";


        //! Perhitungan awal (begin stock) untuk setiap item_fg_id

        //? Step 1: Hitung qty_in dari fg_scan_in_label
        $query_qty_fg_scan_in2 = "SELECT a.item_fg_id, SUM(a.qty) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        AND a.scan_date < DATE('$filter_from')
        GROUP BY a.item_fg_id";

        //? Step 2: Hitung qty_in dari os_fg
        $query_qty_os_fg2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        AND a.trans_date < DATE('$filter_from')
        GROUP BY a.item_fg_id";

        //? Step 3: Hitung qty_in dari transaction_fg (RE)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date < DATE('$filter_from')
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";

        //? Step 4: Hitung qty_out dari transaction_fg (IS)
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date < DATE('$filter_from')
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

        //? Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT dn.item_fg_id, SUM(dn.qty) as initial_out_g
        FROM delivery_notes dn
        JOIN (
            SELECT 
                delivery_order_no, 
                item_fg_id, 
                customer_order_no,
                SUM(qty) AS total_shipping_qty
            FROM shipping_orders
            WHERE deleted = 0
            GROUP BY delivery_order_no, item_fg_id, customer_order_no
        ) s ON dn.delivery_order_no = s.delivery_order_no
            AND dn.item_fg_id = s.item_fg_id
            AND dn.customer_order_no = s.customer_order_no
            AND dn.qty = s.total_shipping_qty
        WHERE dn.deleted = 0
        AND dn.delivery_note_date < DATE('$filter_from')
        GROUP BY dn.item_fg_id";

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name,
            a.uom,
            a.item_family_number,
            f.name as family_name,
            COALESCE(x.begin_stock, 0) AS begin_stock,
            (
                COALESCE(qc.fg_scan_in, 0) + 
                COALESCE(qnc.qty_os_fg, 0) + 
                COALESCE(qi.initial_in, 0)
            ) AS qty_in,
            (
                COALESCE(qo.qty_out, 0) + 
                COALESCE(qg.initial_out_g, 0)
            ) AS qty_out
        FROM item_fg a 
        LEFT JOIN item_familys f ON a.item_family_number = f.number
        LEFT JOIN divisions dv ON a.division_id = dv.id

        -- * Perhitungan qty_in
        LEFT JOIN ($query_qty_fg_scan_in) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_os_fg) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id

        -- * Perhitungan qty_out
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id

        -- * Perhitungan awal (begin stock)
        LEFT JOIN (
            SELECT 
                a.id,
                (
                    COALESCE(qc.fg_scan_in, 0) + 
                    COALESCE(qi.initial_in, 0) + 
                    COALESCE(qnc.qty_os_fg, 0) - 
                    (
                        COALESCE(qo.qty_out, 0) + 
                        COALESCE(qg.initial_out_g, 0)
                    )
                ) AS begin_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_fg_scan_in2) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_os_fg2) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
            GROUP BY a.id
        ) x ON a.id = x.id

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
                    <th colspan='.$cols3.'>Product No</th>
                    <th colspan='.$cols2.'>Product Name</th>
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

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan='.$cols3.' style="mso-number-format:\@">' . $record->number . '</td>
                            <td colspan='.$cols2.' style="mso-number-format:\@">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->family_name . '</td>
                            <td style="text-align:right;">' . number_format($record->begin_stock, 0, '.', '.') . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 0, '.', '.') . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 0, '.', '.') . '</td>
                            <td style="text-align:right">' . number_format(($record->begin_stock + $record->qty_in - $record->qty_out), 0, '.', '.') . '</td>
                        </tr>';

            // Menghitung total
            $total_begin += $record->begin_stock;
            $total_in += $record->qty_in;
            $total_out += $record->qty_out;
            $total_end += ($record->begin_stock + $record->qty_in - $record->qty_out);

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
                $begin = $record->begin_stock;
                $balance = $begin;

                // Mengambil semua transaksi dalam satu query
                $all_transactions = $this->crud->query("SELECT DISTINCT * FROM (
                    (SELECT 
                        'IN' as trans_category,
                        f.scan_date as trans_date,
                        f.serial_label as ref_no,
                        f.qty,
                        u.name as username,
                        t.name as trans_type,
                        lp.compound_lot,
                        lp.prod_date,
                        f.created_date
                    FROM fg_scan_in_label f
                    JOIN users u ON f.created_by = u.username
                    JOIN transaction_type t ON f.transaction_type = t.type
                    LEFT JOIN label_packing_detail lpd ON f.serial_label = lpd.serial_label
                    LEFT JOIN label_packing lp ON lpd.serial_no = lp.serial_no
                    WHERE f.item_fg_id = '$item_fg_id' 
                    AND DATE(f.scan_date) BETWEEN '$filter_from' AND '$filter_to'
                    AND f.deleted = 0)
                    UNION ALL
                    (SELECT 
                        'IN' as trans_category,
                        o.trans_date as trans_date,
                        '' as ref_no,
                        o.qty,
                        u.name as username,
                        t.name as trans_type,
                        '' as compound_lot,
                        '' as prod_date,
                        o.created_date
                    FROM os_fg o
                    JOIN users u ON o.created_by = u.username
                    JOIN transaction_type t ON o.transaction_type = t.type
                    WHERE o.item_fg_id = '$item_fg_id' 
                    AND DATE(o.trans_date) BETWEEN '$filter_from' AND '$filter_to'
                    AND o.deleted = 0)
                    UNION ALL
                    (SELECT 
                        IF(LEFT(tf.transaction_type, 2) = 'RE', 'IN', 'OUT') as trans_category,
                        tf.request_date as trans_date,
                        tf.request_no as ref_no,
                        IF(LEFT(tf.transaction_type, 2) = 'RE', tf.qty, -tf.qty) as qty,
                        u.name as username,
                        t.name as trans_type,
                        '' as compound_lot,
                        '' as prod_date,
                        tf.created_date
                    FROM transaction_fg tf
                    JOIN users u ON tf.created_by = u.username
                    JOIN transaction_type t ON tf.transaction_type = t.type
                    WHERE tf.item_fg_id = '$item_fg_id'
                    AND DATE(tf.request_date) BETWEEN '$filter_from' AND '$filter_to'
                    AND tf.deleted = 0
                    AND (LEFT(tf.transaction_type, 2) = 'RE' OR LEFT(tf.transaction_type, 2) = 'IS')
                    )
                    UNION ALL
                    (
                        SELECT 
                            'OUT' AS trans_category,
                            do.actual_delivery_date AS trans_date,
                            sh.delivery_order_no AS ref_no,
                            -sh.qty AS qty,
                            -- -so_qty.total_qty AS qty,
                            u.name AS username,
                            'Shipping Order' AS trans_type,

                            -- compound_lot
                            CASE 
                                WHEN NULLIF(lp.compound_lot, '') IS NOT NULL THEN lp.compound_lot
                                WHEN (
                                    SELECT NULLIF(nbf.compound_lot, '')
                                    FROM shipping_orders so
                                    JOIN new_barcode_fg_detail nbfd ON so.serial_label = nbfd.serial_label
                                    JOIN new_barcode_fg nbf ON nbfd.request_no = nbf.request_no
                                    WHERE so.delivery_order_no = sh.delivery_order_no
                                    AND so.item_fg_id = sh.item_fg_id
                                    AND so.customer_order_no = sh.customer_order_no
                                    LIMIT 1
                                ) IS NOT NULL THEN (
                                    SELECT nbf.compound_lot
                                    FROM shipping_orders so
                                    JOIN new_barcode_fg_detail nbfd ON so.serial_label = nbfd.serial_label
                                    JOIN new_barcode_fg nbf ON nbfd.request_no = nbf.request_no
                                    WHERE so.delivery_order_no = sh.delivery_order_no
                                    AND so.item_fg_id = sh.item_fg_id
                                    AND so.customer_order_no = sh.customer_order_no
                                    LIMIT 1
                                ) ELSE (
                                    SELECT compound_lot 
                                    FROM new_barcode_fg 
                                    WHERE item_fg_id = sh.item_fg_id 
                                    AND compound_lot IS NOT NULL 
                                    AND LENGTH(TRIM(compound_lot)) > 0
                                    GROUP BY compound_lot 
                                    ORDER BY COUNT(*) DESC 
                                    LIMIT 1
                                )
                            END AS compound_lot,

                            -- prod_date
                            CASE
                                WHEN lp.prod_date IS NOT NULL THEN lp.prod_date
                                WHEN (
                                    SELECT nbf.prod_date
                                    FROM shipping_orders so
                                    JOIN new_barcode_fg_detail nbfd ON so.serial_label = nbfd.serial_label
                                    JOIN new_barcode_fg nbf ON nbfd.request_no = nbf.request_no
                                    WHERE so.delivery_order_no = sh.delivery_order_no
                                    AND so.item_fg_id = sh.item_fg_id
                                    AND so.customer_order_no = sh.customer_order_no
                                    LIMIT 1
                                ) IS NOT NULL THEN (
                                    SELECT nbf.prod_date
                                    FROM shipping_orders so
                                    JOIN new_barcode_fg_detail nbfd ON so.serial_label = nbfd.serial_label
                                    JOIN new_barcode_fg nbf ON nbfd.request_no = nbf.request_no
                                    WHERE so.delivery_order_no = sh.delivery_order_no
                                    AND so.item_fg_id = sh.item_fg_id
                                    AND so.customer_order_no = sh.customer_order_no
                                    LIMIT 1
                                ) ELSE (
                                    SELECT prod_date 
                                    FROM new_barcode_fg 
                                    WHERE item_fg_id = sh.item_fg_id 
                                    AND prod_date IS NOT NULL 
                                    AND LENGTH(TRIM(prod_date)) > 0
                                    GROUP BY prod_date 
                                    ORDER BY COUNT(*) DESC 
                                    LIMIT 1
                                )
                            END AS prod_date,

                            sh.delivery_note_date
                        FROM delivery_notes sh
                        JOIN users u ON sh.created_by = u.username

                        -- Label packing
                        LEFT JOIN label_packing_detail lpd ON sh.delivery_order_no = lpd.serial_label
                        LEFT JOIN label_packing lp ON lpd.serial_no = lp.serial_no

                        -- Shipping summary join 
                        JOIN (
                            SELECT 
                                delivery_order_no, 
                                item_fg_id, 
                                customer_order_no,
                                SUM(qty) AS total_shipping_qty
                            FROM shipping_orders
                            WHERE deleted = 0
                            GROUP BY delivery_order_no, item_fg_id, customer_order_no
                        ) so ON sh.delivery_order_no = so.delivery_order_no
                            AND sh.item_fg_id = so.item_fg_id
                            AND sh.customer_order_no = so.customer_order_no
                            AND sh.qty = so.total_shipping_qty

                        -- Delivery orders
                        LEFT JOIN delivery_orders do ON sh.delivery_order_no = do.delivery_order_no

                        WHERE sh.item_fg_id = '$item_fg_id' 
                        AND DATE(sh.delivery_note_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND sh.deleted = 0
                )) as combined_transactions
                ORDER BY trans_date ASC");                

                foreach ($all_transactions as $trans) {
                    $balance += $trans->qty;
                    $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $trans->trans_type . '</td>
                                <td>' . $trans->username . '</td>
                                <td>' . $trans->trans_date . '</td>
                                <td>' . $trans->prod_date . '</td>
                                <td>' . $trans->ref_no . '</td>
                                <td>' . $trans->compound_lot . '</td>
                                <td style="text-align:right;">' . number_format($begin, 0, '.', '.') . '</td>
                                <td style="text-align:right;">' . ($trans->trans_category == 'IN' ? number_format($trans->qty, 0, '.', '.') : '0') . '</td>
                                <td style="text-align:right;">' . ($trans->trans_category == 'OUT' ? number_format(abs($trans->qty), 0, '.', '.') : '0') . '</td>
                                <td style="text-align:right;">' . number_format($balance, 0, '.', '.') . '</td>
                            </tr>';
                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }
        
        // Menambahkan baris total di dalam tabel yang sama
        $html .= '<tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan='.$cols5.' style="text-align: right;">TOTAL</td>
                    <td style="text-align:right;">' . number_format($total_begin, 0, '.', '.') . '</td>
                    <td style="text-align:right;">' . number_format($total_in, 0, '.', '.') . '</td>
                    <td style="text-align:right;">' . number_format($total_out, 0, '.', '.') . '</td>
                    <td style="text-align:right;">' . number_format($total_end, 0, '.', '.') . '</td>
                </tr>';
        $html .= '</table>';
        $html .= '</body></html>';
        echo $html;
    }

    public function detail_transaction($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=detail_transaction_fg_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_product_family = $this->input->get("filter_product_family");
        $filter_qty_in = $this->input->get("filter_qty_in");
        $filter_qty_out = $this->input->get("filter_qty_out");
        $filter_plant = $this->input->get("filter_plant");

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

        if (!empty($filter_items)) {
            $where_condition = "WHERE a.id LIKE '%$filter_items%'";
        } else {
            $where_condition = "WHERE a.status = 0";
        }
        
        if (!empty($filter_product_family)) {
            $where_condition .= " AND a.item_family_number = '$filter_product_family'";
        }

        if (!empty($filter_plant)) {
            $where_condition .= " AND a.division_id = '$filter_plant'";
        }


        //! Perhitungan qty_in untuk setiap item_fg_id

        //? Step 1: Hitung qty_in dari fg_scan_in_label
        $query_qty_fg_scan_in = "SELECT a.item_fg_id, SUM(a.qty) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        AND a.scan_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        //? Step 2: Hitung qty_in dari os_fg
        $query_qty_os_fg = "SELECT a.item_fg_id, SUM(a.qty) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        AND a.trans_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        //? Step 3: Hitung qty_in dari transaction_fg (RE)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";


        //! Perhitungan qty_out untuk setiap item_fg_id

        //? Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

        //? Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT dn.item_fg_id, SUM(dn.qty) as initial_out_g
        FROM delivery_notes dn
        JOIN (
            SELECT 
                delivery_order_no, 
                item_fg_id, 
                customer_order_no,
                SUM(qty) AS total_shipping_qty
            FROM shipping_orders
            WHERE deleted = 0
            GROUP BY delivery_order_no, item_fg_id, customer_order_no
        ) s ON dn.delivery_order_no = s.delivery_order_no
            AND dn.item_fg_id = s.item_fg_id
            AND dn.customer_order_no = s.customer_order_no
            AND dn.qty = s.total_shipping_qty
        WHERE dn.deleted = 0
        AND dn.delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY item_fg_id";

        //! Perhitungan awal (begin stock) untuk setiap item_fg_id

        //? Step 1: Hitung qty_in dari fg_scan_in_label
        $query_qty_fg_scan_in2 = "SELECT a.item_fg_id, SUM(a.qty) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        AND a.scan_date < '$filter_from'
        GROUP BY a.item_fg_id";

        //? Step 2: Hitung qty_in dari os_fg
        $query_qty_os_fg2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        AND a.trans_date < '$filter_from'
        GROUP BY a.item_fg_id";

        //? Step 3: Hitung qty_in dari transaction_fg (RE)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date < '$filter_from'
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";

        //? Step 4: Hitung qty_out dari transaction_fg (IS)
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date < '$filter_from'
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

        //? Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT dn.item_fg_id, SUM(dn.qty) as initial_out_g
        FROM delivery_notes dn
        JOIN (
            SELECT 
                delivery_order_no, 
                item_fg_id, 
                customer_order_no,
                SUM(qty) AS total_shipping_qty
            FROM shipping_orders
            WHERE deleted = 0
            GROUP BY delivery_order_no, item_fg_id, customer_order_no
        ) s ON dn.delivery_order_no = s.delivery_order_no
            AND dn.item_fg_id = s.item_fg_id
            AND dn.customer_order_no = s.customer_order_no
            AND dn.qty = s.total_shipping_qty
        WHERE dn.deleted = 0
        AND dn.delivery_note_date < '$filter_from'
        GROUP BY dn.item_fg_id";

        $query_main = "SELECT
            a.id,
            a.number, 
            a.name,
            a.uom,
            a.item_family_number,
            f.name as family_name,
            COALESCE(x.begin_stock, 0) AS begin_stock,
            (
                COALESCE(qc.fg_scan_in, 0) + 
                COALESCE(qnc.qty_os_fg, 0) + 
                COALESCE(qi.initial_in, 0)
            ) AS qty_in,
            (
                COALESCE(qo.qty_out, 0) + 
                COALESCE(qg.initial_out_g, 0)
            ) AS qty_out
        FROM item_fg a 
        LEFT JOIN item_familys f ON a.item_family_number = f.number
        LEFT JOIN divisions dv ON a.division_id = dv.id

        -- * Perhitungan qty_in
        LEFT JOIN ($query_qty_fg_scan_in) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_os_fg) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id

        -- * Perhitungan qty_out
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id

        -- * Perhitungan awal (begin stock)
        LEFT JOIN (
            SELECT 
                a.id,
                (
                    COALESCE(qc.fg_scan_in, 0) + 
                    COALESCE(qi.initial_in, 0) + 
                    COALESCE(qnc.qty_os_fg, 0) - 
                    (
                        COALESCE(qo.qty_out, 0) + 
                        COALESCE(qg.initial_out_g, 0)
                    )
                ) AS begin_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_fg_scan_in2) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_os_fg2) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
            GROUP BY a.id
        ) x ON a.id = x.id
        $where_condition
        GROUP BY a.id
        $having_condition
        ORDER BY a.number";

        $records = $this->crud->query($query_main);

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
                <h3 style="margin:0;">DETAIL TRANSACTION (FG)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th>Transaction. Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Uom</th>
                    <th>Trans Type</th>
                    <th>Created By</th>
                    <th>Begin</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>End Stock</th>
                </tr>';


        $no = 1;
        $nod = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;

            $begin = @$record->begin_stock;
            $in_qty = 0;
            $end_qty = 0;
            $balance = 0;

            $fg_scan_in_labels = $this->crud->query("SELECT 
                    t.name as trans_type,
                    u.name as username,
                    f.scan_date as trans_date,
                    SUM(f.qty) as qty_in
                FROM fg_scan_in_label f
                JOIN users u ON f.created_by = u.username
                JOIN transaction_type t ON f.transaction_type = t.type
                WHERE f.item_fg_id = '$item_fg_id' 
                AND f.scan_date BETWEEN '$filter_from' AND '$filter_to'
                AND f.deleted = 0
                GROUP BY f.item_fg_id, f.scan_date, t.name
            ");

            $os_fgs = $this->crud->query("SELECT 
                o.*, 
                u.name as username, 
                t.name as transaction_type
                FROM os_fg o
                JOIN users u ON o.created_by = u.username
                JOIN transaction_type t ON o.transaction_type = t.type
                WHERE o.item_fg_id = '$item_fg_id' 
                AND o.trans_date BETWEEN '$filter_from' AND '$filter_to'
                AND o.deleted = 0
            ");

            $delivery_notes = $this->crud->query("SELECT a.*, SUM(a.qty) as qty_out, d.name AS username
                FROM delivery_notes a
                JOIN users d ON a.created_by = d.username

                -- Shipping summary join 
                JOIN (
                    SELECT 
                        delivery_order_no, 
                        item_fg_id, 
                        customer_order_no,
                        SUM(qty) AS total_shipping_qty
                    FROM shipping_orders
                    WHERE deleted = 0
                    GROUP BY delivery_order_no, item_fg_id, customer_order_no
                ) so ON a.delivery_order_no = so.delivery_order_no
                    AND a.item_fg_id = so.item_fg_id
                    AND a.customer_order_no = so.customer_order_no
                    AND a.qty = so.total_shipping_qty
                WHERE a.item_fg_id = '$item_fg_id'
                AND a.delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_fg_id, a.delivery_note_date
                ");

            $transactions_fg = $this->crud->query("SELECT 
                    tf.*,
                    u.name as username,
                    t.name as trans_type
                FROM transaction_fg tf
                JOIN users u ON tf.created_by = u.username
                JOIN transaction_type t ON tf.transaction_type = t.type
                WHERE tf.item_fg_id = '$item_fg_id'
                AND tf.request_date BETWEEN '$filter_from' AND '$filter_to'
                AND tf.deleted = 0
                AND (LEFT(tf.transaction_type, 2) = 'RE' OR LEFT(tf.transaction_type, 2) = 'IS')
            ");

            // Proses data berdasarkan tanggal
            $all_data = [];

            foreach ($fg_scan_in_labels as $fg_scan_in_label) {
                $all_data[] = [
                    'type' => $fg_scan_in_label->trans_type,
                    'username' => $fg_scan_in_label->username,
                    'date' => $fg_scan_in_label->trans_date,
                    'qty_in' => $fg_scan_in_label->qty_in,
                    'qty_out' => 0,
                ];
            }

            foreach ($os_fgs as $os_fg) {
                $all_data[] = [
                    'type' => $os_fg->transaction_type,
                    'username' => $os_fg->username,
                    'date' => $os_fg->trans_date,
                    'qty_in' => $os_fg->qty,
                    'qty_out' => 0,
                ];
            }

            foreach ($transactions_fg as $transaction) {
                $all_data[] = [
                    'type' => $transaction->trans_type,
                    'username' => $transaction->username,
                    'date' => $transaction->request_date,
                    'qty_in'  => strpos($transaction->transaction_type, 'RE') === 0 ? $transaction->qty : 0,
                    'qty_out' => strpos($transaction->transaction_type, 'IS') === 0 ? $transaction->qty : 0,
                ];
            }

            // Gabungkan data delivery notes
            foreach ($delivery_notes as $delivery_note) {
                $all_data[] = [
                    'type' => 'DELIVERY NOTE',
                    'username' => $delivery_note->username,
                    'date' => $delivery_note->delivery_note_date,
                    'qty_in' => 0,
                    'qty_out' => $delivery_note->qty_out,
                ];
            }

            usort($all_data, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            // Generate HTML
            $balance = $begin;
            foreach ($all_data as $data) {
                $balance += $data['qty_in'] - $data['qty_out'];
                $html .= '  <tr>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $data['date'] . '</td>
                                <td style="mso-number-format:\@;">' . $record->number . '</td>
                                <td style="mso-number-format:\@;">' . $record->name . '</td>
                                <td>' . $record->uom . '</td>
                                <td>' . $data['type'] . '</td>
                                <td>' . $data['username'] . '</td>
                                <td style="text-align:right;">' . number_format($begin, 0, '.', '.') . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_in'], 0, '.', '.') . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_out'], 0, '.', '.') . '</td>
                                <td style="text-align:right;">' . number_format($balance, 0, '.', '.') . '</td>
                            </tr>';

                $begin = $balance;
                $nod++;
            }

            $no++;
        }

        $html .= '<tr style="background-color: #f2f2f2;">
            <td colspan="7" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;"><b>' . number_format($totalBeginStock, 0, '.', '.') . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalIn, 0, '.', '.') . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalOut, 0, '.', '.') . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalEndingStock, 0, '.', '.') . '</b></td>
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}