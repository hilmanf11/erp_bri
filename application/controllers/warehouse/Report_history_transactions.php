<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions');
        } else {
            redirect('error_access');
        }
    }

    public function readEndingStock()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $trans_date = @$this->input->post('trans_date');

            if (@$trans_date == "") {
                $date = date("Y-m-d");
            } else {
                $date = $trans_date;
            }

            $records = $this->crud->query("SELECT
                a.id,
                a.number, 
                a.name, 
                b.name as prodfam, 
                a.uom, 
                COALESCE(0,0) as begin_stock,
                (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0)) as qty_in,
                f.qty as qty_out,
                (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0)) as end_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$date'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date <=  '$date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                FROM os_rm a
                WHERE a.trans_date <= '$date'
                GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            echo json_encode($records);
        }
    }

    public function readBalanceWip()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");

            echo json_encode($wip_balances);
        }
    }

    public function readItemFamily($item_category_id)
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('id !=', "P08"); 
        $this->db->where('deleted', 0);
        $this->db->where("item_category_id", $item_category_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readItemFamilys()
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        //$this->db->where('id !=', "P08"); 
        $this->db->where('item_category_id =', "C02"); 
        $this->db->where('deleted', 0);
        // $this->db->where("item_category_id", $item_category_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_trans_type = $this->input->get('filter_trans_type');
        $filter_qty_in = $this->input->get("filter_qty_in");
        $filter_qty_out = $this->input->get("filter_qty_out");
        $filter_plant = $this->input->get("filter_plant");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

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

        $where_condition = '';
        if (!empty($filter_plant)) {
            $where_condition .= " AND 
                CASE 
                    WHEN b.number = 'PP' THEN 'EXT'
                    ELSE 'RP'
                END = '$filter_plant'";
        }

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.number_internal,
            a.name, 
            b.name as prodfam, 
            a.uom,
            COALESCE(0,0) as begin_stock,
            (
                COALESCE(SUM(e.qty),0) + 
                COALESCE(g.return_qty, 0) + 
                COALESCE(h.qty_stock_rm, 0) + 
                COALESCE(i.qty_in, 0)
            ) as qty_in,
            (
                COALESCE(f.qty, 0) + 
                COALESCE(j.qty_out, 0)
            ) as qty_out,
            (
                COALESCE(SUM(e.qty),0) - 
                COALESCE(f.qty, 0) + 
                COALESCE(g.return_qty, 0) + 
                COALESCE(h.qty_stock_rm, 0) + 
                COALESCE(i.qty_in, 0) - 
                COALESCE(j.qty_out, 0)
            ) as end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
        LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
        LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
        LEFT JOIN (
            SELECT 
                item_rm_id, 
                COALESCE(SUM(qty), 0) as qty 
            FROM issued_material_details 
            WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' 
            GROUP BY item_rm_id
        ) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
            FROM os_rm a
            JOIN item_rm b ON a.item_rm_id = b.id
            WHERE a.trans_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty_in 
            FROM transaction_rm 
            WHERE transaction_type LIKE 'RE%' 
            AND request_date between '$filter_from' and '$filter_to'
            GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty_out 
            FROM transaction_rm 
            WHERE transaction_type LIKE 'IS%' 
            AND request_date between '$filter_from' and '$filter_to'
            GROUP BY item_rm_id) j ON a.id = j.item_rm_id
        WHERE b.number like '%$filter_item_family%' and a.id like '%$filter_items%'
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
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th colspan="3">Part No External</th>
                    <th colspan="3">Part No Internal</th>
                    <th colspan="2">Part Name</th>
                    <th colspan="2">Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';


        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                a.id,
                (
                    -- Qty In sebelum filter_from
                    COALESCE(SUM(e.qty),0) +  -- scan_item_receipts
                    COALESCE(g.return_qty, 0) +  -- return_materials
                    COALESCE(h.qty_stock_rm, 0) +  -- os_rm
                    COALESCE(i.qty_in, 0)  -- transaction_rm (RE%)
                ) - (
                    -- Qty Out sebelum filter_from
                    COALESCE(f.qty, 0) +  -- issued_material_details
                    COALESCE(j.qty_out, 0)  -- transaction_rm (IS%)
                ) as begin_stock   
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date < '$filter_from'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN (
                SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty 
                FROM issued_material_details 
                WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' 
                GROUP BY item_rm_id
            ) f ON a.id = f.item_rm_id
            LEFT JOIN (
                SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date < '$filter_from'
                GROUP BY a.item_rm_id
            ) g ON a.id = g.item_rm_id
            LEFT JOIN (
                SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                FROM os_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.trans_date < '$filter_from'
                GROUP BY a.item_rm_id
            ) h ON a.id = h.item_rm_id
            LEFT JOIN (
                SELECT item_rm_id, SUM(qty) as qty_in 
                FROM transaction_rm 
                WHERE transaction_type LIKE 'RE%' 
                AND request_date < '$filter_from'
                GROUP BY item_rm_id
            ) i ON a.id = i.item_rm_id
            LEFT JOIN (
                SELECT item_rm_id, SUM(qty) as qty_out 
                FROM transaction_rm 
                WHERE transaction_type LIKE 'IS%' 
                AND request_date < '$filter_from'
                GROUP BY item_rm_id
            ) j ON a.id = j.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            $totalBeginStock += @$itemReceipts[0]->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td colspan="3">' . $record->number_internal . '</td>
                            <td colspan="2">' . $record->name . '</td>
                            <td colspan="2">' . $record->uom . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$itemReceipts[0]->begin_stock, 2, ',', '.') . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2, ',', '.') . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2, ',', '.') . '</td>
                            <td style="text-align:right;">' . number_format((@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out, 2, ',', '.') . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="16" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th colspan="2">Created By</th>
                                <th colspan="2">Trans Date</th>
                                <th>Custom. Kind</th>
                                <th>Custom. No</th>
                                <th colspan="2">Doc. No</th>
                                <th>Custom. Date</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';

                $nod = 1;
                $begin = @$itemReceipts[0]->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    if ($filter_trans_type == '' ) {
                        //-------------- Awal Query disini----------------------------------//                    
                        //RECEIPT
                        $receipts = $this->crud->query("SELECT
                            a.receipt_date, 
                            a.bc_kind, 
                            a.bc_aju, 
                            a.bc_document, 
                            a.bc_date, 
                            (SUM(b.qty) + COALESCE(SUM(d.qty), 0)) as qty_receipt,
                            c.name as username
                        FROM purchase_order_receipts a 
                        JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                        JOIN users c ON a.created_by = c.username
                        LEFT JOIN os_rm d ON a.item_rm_id = d.item_rm_id AND d.trans_date = a.receipt_date
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
                        
                        //ISSUED
                        $issueds = $this->crud->query("SELECT created_by, qty, created_date, label_no, request_no FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        //TRANSACTION RM
                        $transactions = $this->crud->query("SELECT 
                            a.*,
                            b.name as username,
                            c.name as transaction_name
                        FROM transaction_rm a
                        LEFT JOIN users b ON a.created_by = b.username
                        LEFT JOIN transaction_type c ON a.transaction_type = c.type
                        WHERE a.item_rm_id = '$item_rm_id' 
                        AND DATE_FORMAT(a.request_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        //RETURN
                        $returns = $this->crud->query("SELECT
                            a.return_no,
                            a.return_id,
                            a.return_name,
                            a.return_date,
                            b.label_no,
                            b.qty,
                            d.name as username
                        FROM return_materials a 
                        JOIN return_material_labels b ON a.return_id = b.return_id
                        JOIN scan_item_receipts c ON a.return_id = c.receipt_id
                        JOIN users d ON a.created_by = d.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$working_date' and '$working_date'
                        GROUP BY b.label_no");

                        //OS RM
                        $os_rms = $this->crud->query("SELECT os_rm.*, transaction_type.name as transaction_name
                            FROM os_rm
                            LEFT JOIN transaction_type ON os_rm.transaction_type = transaction_type.type
                            WHERE os_rm.item_rm_id = '$item_rm_id' 
                            AND DATE_FORMAT(os_rm.trans_date, '%Y-%m-%d') BETWEEN '$working_date' AND '$working_date'");

                        //OS RM
                        foreach ($os_rms as $os_rm) {
                            $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                            $balance = ($begin + $os_rm->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>RECEIVE</td>
                                            <td colspan="2">' . $user->name . '</td>
                                            <td colspan="2">' . $os_rm->trans_date . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td colspan="2">-</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($os_rm->qty, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2, ',', '.')  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.')  . '</td>
                                        </tr>';
                            $begin += $os_rm->qty;
                            $nod++;
                        }

                        //Purchase Order Receipt
                        foreach ($receipts as $receipt) {
                            $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>RECEIVE</td>
                                            <td colspan="2">' . $receipt->username . '</td>
                                            <td colspan="2">' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td colspan="2">' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2, ',', '.')  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.')  . '</td>
                                        </tr>';
                            $begin += $receipt->qty_receipt;
                            $nod++;
                        }

                        //Issued Material
                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>ISSUED</td>
                                            <td colspan="2">' . $user->name . '</td>
                                            <td colspan="2">' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td colspan="2">' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2, ',', '.')  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.')  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $nod++;
                        }

                        //TRANSACTION RM
                        foreach ($transactions as $transaction) {
                            if (strpos($transaction->transaction_type, 'RE') === 0) {
                                $balance = $begin + $transaction->qty;
                                $qty_in = $transaction->qty;
                                $qty_out = 0;
                            } else if (strpos($transaction->transaction_type, 'IS') === 0) {
                                $balance = $begin - $transaction->qty;
                                $qty_in = 0;
                                $qty_out = $transaction->qty;
                            }
                            
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>' . $transaction->transaction_name . '</td>
                                            <td colspan="2">' . $transaction->username . '</td>
                                            <td colspan="2">' . $transaction->request_date . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td colspan="2">' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($qty_in, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($qty_out, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.') . '</td>
                                        </tr>';
                            $begin = $balance;
                            $nod++;
                        }
                    }
            
                    if ($filter_trans_type == 'RECEIVE') {
                        //RECEIPT
                        $receipts = $this->crud->query("SELECT
                            a.receipt_date, 
                            a.bc_kind, 
                            a.bc_aju, 
                            a.bc_document, 
                            a.bc_date, 
                            (SUM(b.qty) + COALESCE(SUM(d.qty), 0)) as qty_receipt,
                            c.name as username
                        FROM purchase_order_receipts a 
                        JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                        JOIN users c ON a.created_by = c.username
                        LEFT JOIN os_rm d ON a.item_rm_id = d.item_rm_id AND d.trans_date = a.receipt_date
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
            
                        foreach ($receipts as $receipt) {
                            $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>RECEIVE</td>
                                            <td colspan="2">' . $receipt->username . '</td>
                                            <td colspan="2">' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td colspan="2">' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2, ',', '.')  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.')  . '</td>
                                        </tr>';
                            $begin += $receipt->qty_receipt;
                            $nod++;
                        }

                        //OS RM
                        $os_rms = $this->crud->query("SELECT os_rm.*, transaction_type.name as transaction_name
                            FROM os_rm
                            LEFT JOIN transaction_type ON os_rm.transaction_type = transaction_type.type
                            WHERE os_rm.item_rm_id = '$item_rm_id' 
                            AND DATE_FORMAT(os_rm.trans_date, '%Y-%m-%d') BETWEEN '$working_date' AND '$working_date'");

                        //OS RM
                        foreach ($os_rms as $os_rm) {
                            $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                            $balance = ($begin + $os_rm->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>RECEIVE</td>
                                            <td colspan="2">' . $user->name . '</td>
                                            <td colspan="2">' . $os_rm->trans_date . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td colspan="2">-</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($os_rm->qty, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2, ',', '.')  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.')  . '</td>
                                        </tr>';
                            $begin += $os_rm->qty;
                            $nod++;
                        }
                    }

                    if ($filter_trans_type == 'ISSUED') {
                        //ISSUED
                        $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");
            
                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>ISSUED</td>
                                            <td colspan="2">' . $user->name . '</td>
                                            <td colspan="2">' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td colspan="2">' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2, ',', '.') . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2, ',', '.')  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2, ',', '.')  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $nod++;
                        }
                    }
                }
            }
            $no++;
        }

        $html .= '<tr>
            <td colspan="12" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2, ',', '.') . '</td>
            <td style="text-align:right;">' . number_format($totalIn, 2, ',', '.') . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2, ',', '.') . '</td>
            <td style="text-align:right;">' . number_format($totalEndingStock, 2, ',', '.') . '</td>
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }

    public function detail_transaction($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=detail_transaction_rm_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_item_family = $this->input->get("filter_product_family");
        $filter_qty_in = $this->input->get("filter_qty_in");
        $filter_qty_out = $this->input->get("filter_qty_out");
        $filter_plant = $this->input->get("filter_plant");
        $filter_display = $this->input->get("filter_display");
        $filter_trans_type = $this->input->get("filter_trans_type");

        // $filter_trans_type = '';

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

        $where_condition = '';
        if (!empty($filter_plant)) {
            $where_condition .= " AND 
                CASE 
                    WHEN b.number = 'PP' THEN 'EXT'
                    ELSE 'RP'
                END = '$filter_plant'";
        }

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.number_internal, 
            a.name, 
            b.name as prodfam, 
            a.uom,
            dv.number as division_number,
            COALESCE(0,0) as begin_stock,
            (
                COALESCE(SUM(e.qty),0) + 
                COALESCE(g.return_qty, 0) + 
                COALESCE(h.qty_stock_rm, 0) + 
                COALESCE(i.qty_in, 0)
            ) as qty_in,
            (
                COALESCE(f.qty, 0) + 
                COALESCE(j.qty_out, 0)
            ) as qty_out
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
        LEFT JOIN divisions dv ON a.division = dv.number
        LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
        LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
        LEFT JOIN (
            SELECT 
                item_rm_id, 
                COALESCE(SUM(qty), 0) as qty 
            FROM issued_material_details 
            WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' 
            GROUP BY item_rm_id
        ) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
            FROM os_rm a
            JOIN item_rm b ON a.item_rm_id = b.id
            WHERE a.trans_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty_in 
            FROM transaction_rm 
            WHERE transaction_type LIKE 'RE-0002%' 
            AND request_date between '$filter_from' and '$filter_to'
            GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty_out 
            FROM transaction_rm 
            WHERE transaction_type LIKE 'IS%' 
            AND request_date between '$filter_from' and '$filter_to'
            GROUP BY item_rm_id) j ON a.id = j.item_rm_id
        WHERE b.number like '%$filter_item_family%' and a.id like '%$filter_items%'
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
                <h3 style="margin:0;">DETAIL TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th>No</th>
                    <th>Trans Date</th>
                    <th>Part No External</th>
                    <th>Part No Internal</th>
                    <th>Part Name</th>
                    <th>Uom</th>
                    <th>Plant</th>
                    <th>Product Family</th>
                    <th>Trans Type</th>
                    <th>Created By</th>
                    <th>Begin Stock</th>
                    <th>IN</th>
                    <th>OUT</th>
                    <th>Ending Stock</th>
                </tr>';


        $no = 1;
        $nod = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                    a.id,
                    (
                        -- Qty In sebelum filter_from
                        COALESCE(SUM(e.qty),0) +  -- scan_item_receipts
                        COALESCE(g.return_qty, 0) +  -- return_materials
                        COALESCE(h.qty_stock_rm, 0) +  -- os_rm
                        COALESCE(i.qty_in, 0)  -- transaction_rm (RE%)
                    ) - (
                        -- Qty Out sebelum filter_from
                        COALESCE(f.qty, 0) +  -- issued_material_details
                        COALESCE(j.qty_out, 0)  -- transaction_rm (IS%)
                    ) as begin_stock   
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
                LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date < '$filter_from'
                LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
                LEFT JOIN (
                    SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty 
                    FROM issued_material_details 
                    WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' 
                    GROUP BY item_rm_id
                ) f ON a.id = f.item_rm_id
                LEFT JOIN (
                    SELECT a.item_rm_id, SUM(c.qty) as return_qty
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                    WHERE a.return_date < '$filter_from'
                    GROUP BY a.item_rm_id
                ) g ON a.id = g.item_rm_id
                LEFT JOIN (
                    SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                    FROM os_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.trans_date < '$filter_from'
                    GROUP BY a.item_rm_id
                ) h ON a.id = h.item_rm_id
                LEFT JOIN (
                    SELECT item_rm_id, SUM(qty) as qty_in 
                    FROM transaction_rm 
                    WHERE transaction_type LIKE 'RE%' 
                    AND request_date < '$filter_from'
                    GROUP BY item_rm_id
                ) i ON a.id = i.item_rm_id
                LEFT JOIN (
                    SELECT item_rm_id, SUM(qty) as qty_out 
                    FROM transaction_rm 
                    WHERE transaction_type LIKE 'IS%' 
                    AND request_date < '$filter_from'
                    GROUP BY item_rm_id
                ) j ON a.id = j.item_rm_id
                WHERE a.id like '$item_rm_id'
                GROUP BY a.id
                ORDER BY a.number
            ");

            $totalBeginStock += @$itemReceipts[0]->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;

            if ($filter_display == "DETAIL" || $filter_display == "RECAP") {
                $begin = @$itemReceipts[0]->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;

                if ($filter_trans_type == '') {

                    // $receipts = $this->crud->query("SELECT
                    //     a.receipt_date, 
                    //     a.bc_kind, 
                    //     a.bc_aju, 
                    //     a.bc_document, 
                    //     a.bc_date, 
                    //     (SUM(b.qty) + COALESCE(SUM(d.qty), 0)) as qty_receipt,
                    //     c.name as username
                    // FROM purchase_order_receipts a 
                    // JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                    // JOIN users c ON a.created_by = c.username
                    // LEFT JOIN os_rm d ON a.item_rm_id = d.item_rm_id AND d.trans_date = a.receipt_date
                    // WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                    // GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");

                    //RECEIPT
                    $receipts = $this->crud->query("SELECT
                        a.receipt_date, 
                        a.bc_kind, 
                        a.bc_aju, 
                        a.bc_document, 
                        a.bc_date, 
                        SUM(b.qty) as qty_receipt,
                        c.name as username
                    FROM purchase_order_receipts a 
                    JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                    JOIN users c ON a.created_by = c.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                    GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
                    

                    //ISSUED
                    $issueds = $this->crud->query("SELECT created_by, qty, created_date, label_no, request_no FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    //RETURN
                    $returns = $this->crud->query("SELECT
                        a.return_no,
                        a.return_id,
                        a.return_name,
                        a.return_date,
                        b.label_no,
                        b.qty,
                        d.name as username
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$filter_from' and '$filter_to'
                    GROUP BY b.label_no");

                    //OS RM
                    $os_rms = $this->crud->query("SELECT os_rm.*, transaction_type.name as transaction_name
                        FROM os_rm
                        LEFT JOIN transaction_type ON os_rm.transaction_type = transaction_type.type
                        WHERE os_rm.item_rm_id = '$item_rm_id' 
                        AND DATE_FORMAT(os_rm.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                    //SCAN BPM
                    // $bpm_scans = $this->crud->query("SELECT 
                    // created_by, 
                    // qty, 
                    // created_date, 
                    // label, 
                    // request_date, 
                    // request_id 
                    // FROM scan_item_bpm 
                    // WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                    //TRANSACTION RM
                    $transactions = $this->crud->query("SELECT 
                        a.*,
                        b.name as username,
                        c.name as transaction_name
                    FROM transaction_rm a
                    LEFT JOIN users b ON a.created_by = b.username
                    LEFT JOIN transaction_type c ON a.transaction_type = c.type
                    WHERE a.item_rm_id = '$item_rm_id' 
                    AND DATE_FORMAT(a.request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    $all_data = [];

                    //Purchase Order Receipt
                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty_receipt - $end_qty));

                        $all_data[] = [
                            'trans_date'  => $receipt->receipt_date,
                            'trans_type'  => 'RECEIVE',
                            'created_by'  => $receipt->username,
                            'begin'       => $begin,
                            'qty_in'      => $receipt->qty_receipt,
                            'qty_out'      => 0,
                            'balance'      => $balance,
                        ];

                        $begin += $receipt->qty_receipt;
                    }

                    //Issued Material
                    foreach ($issueds as $issued) {
                        $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                        $balance = ($begin - $issued->qty);

                        $all_data[] = [
                            'trans_date'  => $issued->created_date,
                            'trans_type'  => 'ISSUED',
                            'created_by'  => $user->name,
                            'begin'       => $begin,
                            'qty_in'      => 0,
                            'qty_out'      => $issued->qty,
                            'balance'      => $balance,
                        ];

                        $begin -= $issued->qty;
                    }


                    //Return Material
                    foreach ($returns as $return) {
                        $balance = ($begin + $return->qty);

                        $all_data[] = [
                            'trans_date'  => $return->return_date,
                            'trans_type'  => 'RETURN',
                            'created_by'  => $return->username,
                            'begin'       => $begin,
                            'qty_in'      => $return->qty,
                            'qty_out'      => 0,
                            'balance'      => $balance,
                        ];

                        $begin += $return->qty;
                    }

                    //OS RM
                    foreach ($os_rms as $os_rm) {
                        $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                        $balance = ($begin + $os_rm->qty);

                        $all_data[] = [
                            'trans_date'  => $os_rm->created_date,
                            'trans_type'  => $os_rm->transaction_name,
                            'created_by'  => $user->name,
                            'begin'       => $begin,
                            'qty_in'      => $os_rm->qty,
                            'qty_out'      => 0,
                            'balance'      => $balance,
                        ];

                        $begin += $os_rm->qty;
                    }

                    //SCAN BPM
                    // foreach ($bpm_scans as $bpm_scan) {
                    //     $user = $this->crud->read("users", [], ["username" => $bpm_scan->created_by]);
                    //     $balance = ($begin + $bpm_scan->qty);
                    //     $html .= '  <tr>
                    //                      <td style="text-align:center">' . $no . '</td>
                    //                     <td>' . $record->number . '</td>
                    //                     <td>' . $record->category_name . '</td>
                    //                     <td>' . $record->prodfam . '</td>
                    //                     <td>' . $record->sub_prodfam . '</td>
                    //                     <td>' . $record->uom . '</td>
                    //                     <td>BPM</td>
                    //                     <td>' . $user->name . '</td>
                    //                     <td>' . date("Y-m-d", strtotime($bpm_scan->request_date)) . '</td>
                    //                     <td>-</td>
                    //                     <td>-</td>
                    //                     <td>-</td>
                    //                     <td>' . date("Y-m-d", strtotime($bpm_scan->request_date)) . '</td>
                    //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                    //                     <td style="text-align:right;">' . number_format($bpm_scan->qty, 2)  . '</td>
                    //                     <td style="text-align:right;">' . number_format(0, 2) . '</td>
                    //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                    //                 </tr>';
                    //     $begin += $bpm_scan->qty;
                    //     $no++;
                    // }

                    foreach ($transactions as $transaction) {
                        if (strpos($transaction->transaction_type, 'RE') === 0) {
                            $balance = $begin + $transaction->qty;
                            $qty_in = $transaction->qty;
                            $qty_out = 0;
                        } else if (strpos($transaction->transaction_type, 'IS') === 0) {
                            $balance = $begin - $transaction->qty;
                            $qty_in = 0;
                            $qty_out = $transaction->qty;
                        }

                        $all_data[] = [
                            'trans_date'  => $transaction->request_date,
                            'trans_type'  => $transaction->transaction_name,
                            'created_by'  => $transaction->username,
                            'begin'       => $begin,
                            'qty_in'      => $qty_in,
                            'qty_out'      => $qty_out,
                            'balance'      => $balance,
                        ];

                        $begin = $balance;
                    }

                    usort($all_data, function($a, $b) {
                        return strtotime($a['trans_date']) - strtotime($b['trans_date']);
                    });

                    foreach ($all_data as $data) {
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . date("Y-m-d", strtotime($data['trans_date'])) . '</td>
                                        <td>' . $record->number . '</td>
                                        <td>' . $record->number_internal . '</td>
                                        <td>' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $record->division_number . '</td>
                                        <td>' . $record->prodfam . '</td>
                                        <td>' . $data['trans_type'] . '</td>
                                        <td>' . $data['created_by'] . '</td>
                                        <td style="text-align:right;">' . number_format($data['begin'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_in'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_out'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['balance'], 2, ',', '.') . '</td>
                                    </tr>';
                        $nod++;
                    }

                }

                if ($filter_trans_type == 'ISSUED' ) {

                    //ISSUED
                    $issueds = $this->crud->query("SELECT created_by, qty, created_date, label_no, request_no FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    //TRANSACTION RM
                    $transactions = $this->crud->query("SELECT 
                        a.*,
                        b.name as username,
                        c.name as transaction_name
                    FROM transaction_rm a
                    LEFT JOIN users b ON a.created_by = b.username
                    JOIN transaction_type c ON a.transaction_type = c.type
                    WHERE a.item_rm_id = '$item_rm_id' 
                    AND c.type LIKE 'IS%'
                    AND DATE_FORMAT(a.request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    $all_data = [];

                    //Issued Material
                    foreach ($issueds as $issued) {
                        $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                        $balance = ($begin - $issued->qty);

                        $all_data[] = [
                            'trans_date'  => $issued->created_date,
                            'trans_type'  => 'ISSUED',
                            'created_by'  => $user->name,
                            'begin'       => $begin,
                            'qty_in'      => 0,
                            'qty_out'      => $issued->qty,
                            'balance'      => $balance,
                        ];

                        $begin -= $issued->qty;
                    }
                    
                    foreach ($transactions as $transaction) {
                        if (strpos($transaction->transaction_type, 'IS') === 0) {
                            $balance = $begin - $transaction->qty;
                            $qty_in = 0;
                            $qty_out = $transaction->qty;
                        }

                        $all_data[] = [
                            'trans_date'  => $transaction->request_date,
                            'trans_type'  => $transaction->transaction_name,
                            'created_by'  => $transaction->username,
                            'begin'       => $begin,
                            'qty_in'      => $qty_in,
                            'qty_out'      => $qty_out,
                            'balance'      => $balance,
                        ];

                        $begin = $balance;
                    }

                    usort($all_data, function($a, $b) {
                        return strtotime($a['trans_date']) - strtotime($b['trans_date']);
                    });

                    foreach ($all_data as $data) {
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . date("Y-m-d", strtotime($data['trans_date'])) . '</td>
                                        <td>' . $record->number . '</td>
                                        <td>' . $record->number_internal . '</td>
                                        <td>' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $record->division_number . '</td>
                                        <td>' . $record->prodfam . '</td>
                                        <td>' . $data['trans_type'] . '</td>
                                        <td>' . $data['created_by'] . '</td>
                                        <td style="text-align:right;">' . number_format($data['begin'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_in'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_out'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['balance'], 2, ',', '.') . '</td>
                                    </tr>';
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'RECEIVE' ) {
                    //RECEIPT
                    $receipts = $this->crud->query("SELECT
                        a.receipt_date, 
                        a.bc_kind, 
                        a.bc_aju, 
                        a.bc_document, 
                        a.bc_date, 
                        SUM(b.qty) as qty_receipt,
                        c.name as username
                    FROM purchase_order_receipts a 
                    JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                    JOIN users c ON a.created_by = c.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                    GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");

                    //RETURN
                    $returns = $this->crud->query("SELECT
                        a.return_no,
                        a.return_id,
                        a.return_name,
                        a.return_date,
                        b.label_no,
                        b.qty,
                        d.name as username
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$filter_from' and '$filter_to'
                    GROUP BY b.label_no");

                    //OS RM
                    $os_rms = $this->crud->query("SELECT os_rm.*, transaction_type.name as transaction_name
                        FROM os_rm
                        LEFT JOIN transaction_type ON os_rm.transaction_type = transaction_type.type
                        WHERE os_rm.item_rm_id = '$item_rm_id' 
                        AND DATE_FORMAT(os_rm.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                    //TRANSACTION RM
                    $transactions = $this->crud->query("SELECT 
                        a.*,
                        b.name as username,
                        c.name as transaction_name
                    FROM transaction_rm a
                    LEFT JOIN users b ON a.created_by = b.username
                    JOIN transaction_type c ON a.transaction_type = c.type
                    WHERE a.item_rm_id = '$item_rm_id' 
                    AND c.type LIKE 'RE%'
                    AND DATE_FORMAT(a.request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    $all_data = [];

                    //Purchase Order Receipt
                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty_receipt - $end_qty));

                        $all_data[] = [
                            'trans_date'  => $receipt->receipt_date,
                            'trans_type'  => 'RECEIVE',
                            'created_by'  => $receipt->username,
                            'begin'       => $begin,
                            'qty_in'      => $receipt->qty_receipt,
                            'qty_out'      => 0,
                            'balance'      => $balance,
                        ];

                        $begin += $receipt->qty_receipt;
                    }

                    //Return Material
                    foreach ($returns as $return) {
                        $balance = ($begin + $return->qty);

                        $all_data[] = [
                            'trans_date'  => $return->return_date,
                            'trans_type'  => 'RETURN',
                            'created_by'  => $return->username,
                            'begin'       => $begin,
                            'qty_in'      => $return->qty,
                            'qty_out'      => 0,
                            'balance'      => $balance,
                        ];

                        $begin += $return->qty;
                    }

                    //OS RM
                    foreach ($os_rms as $os_rm) {
                        $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                        $balance = ($begin + $os_rm->qty);

                        $all_data[] = [
                            'trans_date'  => $os_rm->created_date,
                            'trans_type'  => $os_rm->transaction_name,
                            'created_by'  => $user->name,
                            'begin'       => $begin,
                            'qty_in'      => $os_rm->qty,
                            'qty_out'      => 0,
                            'balance'      => $balance,
                        ];

                        $begin += $os_rm->qty;
                    }

                    foreach ($transactions as $transaction) {
                        if (strpos($transaction->transaction_type, 'RE') === 0) {
                            $balance = $begin + $transaction->qty;
                            $qty_in = $transaction->qty;
                            $qty_out = 0;
                        }

                        $all_data[] = [
                            'trans_date'  => $transaction->request_date,
                            'trans_type'  => $transaction->transaction_name,
                            'created_by'  => $transaction->username,
                            'begin'       => $begin,
                            'qty_in'      => $qty_in,
                            'qty_out'      => $qty_out,
                            'balance'      => $balance,
                        ];

                        $begin = $balance;
                    }

                    usort($all_data, function($a, $b) {
                        return strtotime($a['trans_date']) - strtotime($b['trans_date']);
                    });

                    foreach ($all_data as $data) {
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . date("Y-m-d", strtotime($data['trans_date'])) . '</td>
                                        <td>' . $record->number . '</td>
                                        <td>' . $record->number_internal . '</td>
                                        <td>' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $record->division_number . '</td>
                                        <td>' . $record->prodfam . '</td>
                                        <td>' . $data['trans_type'] . '</td>
                                        <td>' . $data['created_by'] . '</td>
                                        <td style="text-align:right;">' . number_format($data['begin'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_in'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_out'], 2, ',', '.') . '</td>
                                        <td style="text-align:right;">' . number_format($data['balance'], 2, ',', '.') . '</td>
                                    </tr>';
                        $nod++;
                    }

                }
            }
            // }

            $no++;
        }

        $html .= '<tr>
            <td colspan="10" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2, ',', '.') . '</td>
            <td style="text-align:right;">' . number_format($totalIn, 2, ',', '.') . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2, ',', '.') . '</td>
            <td style="text-align:right;">' . number_format($totalEndingStock, 2, ',', '.') . '</td>
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
