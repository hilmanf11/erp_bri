<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Pr_subcont_productions extends CI_Controller
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

    private function format_number($input) 
    {
        $numeric_value = str_replace(',', '', $input);
        return number_format($numeric_value, 0, '.', '.');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/pr_subcont_productions');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $pr_no = $this->input->get('pr_no');
        $subcont_id = $this->input->get('subcont_id');

        $this->db->select("
            a.id,
            a.doc_no,
            a.item_fg_id,

            b.number AS item_number,
            b.name AS item_name,
            b.uom,

            a.order_qty AS qty,
            a.cost_price AS price,
            (a.order_qty * a.cost_price) AS total,

            '' AS po_no
        ");

        $this->db->from('pr_subcont_productions a');
        $this->db->join('item_fg b', 'b.id = a.item_fg_id');

        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.doc_no', $pr_no);
        $this->db->where('a.subcont_id', $subcont_id);

        $this->db->order_by('b.number', 'ASC');

        echo json_encode($this->db->get()->result_array());
    }

    public function checkPRRegular($year, $month, $order_type, $subcont_id = '')
    {
        $year       = base64_decode($year);
        $month      = base64_decode($month);
        $order_type = base64_decode($order_type);
        $subcont_id = base64_decode($subcont_id);

        if (!empty($subcont_id)) {

            $cek = $this->db->query("
                SELECT id
                FROM pr_subcont_productions
                WHERE p_month = '{$month}'
                AND p_year  = '{$year}'
                AND subcont_id = '{$subcont_id}'
                AND order_type = 'Regular'
                LIMIT 1
            ");


            if ($cek->num_rows() > 0) {
                echo json_encode([
                    'status'  => true,
                    'message' => 'PR Regular already exists'
                ]);
            } else {
                echo json_encode([
                    'status'  => false,
                    'message' => 'PR Regular does not exist for the selected subcont in the selected period'
                ]);
            }

        } else {

            $arr_subcont = $this->db->query("
                SELECT DISTINCT c.id
                FROM item_fg a
                JOIN setting_subconts b ON a.id = b.item_fg_id
                JOIN subconts c ON c.id = b.subcont_id
                WHERE c.subcont_type_id = 'TS002'
                AND c.status = 0
            ")->result_array();

            if (empty($arr_subcont)) {
                echo json_encode([
                    'status'  => false,
                    'message' => 'No Subcont Production found'
                ]);
                return;
            }

            $arr_subcont_id     = array_column($arr_subcont, 'id');

            $arr_subcont_id = array_map(function($id){
                return $this->db->escape($id);
            }, $arr_subcont_id);

            $arr_subcont_id_str = implode(',', $arr_subcont_id);

            $cek = $this->db->query("
                SELECT COUNT(DISTINCT subcont_id) AS total
                FROM pr_subcont_productions
                WHERE p_month = '{$month}'
                AND p_year  = '{$year}'
                AND order_type = 'Regular'
                AND subcont_id IN ({$arr_subcont_id_str})
            ")->row();

            if ((int)$cek->total === count($arr_subcont_id)) {
                echo json_encode([
                    'status'  => true,
                    'message' => 'All subconts already have PR Regular for the specified period'
                ]);
            } else {
                echo json_encode([
                    'status'  => false,
                    'message' => 'Not all subconts have PR Regular for the specified period'
                ]);
            }
        }
    }

    // public function number($year, $month)
    // {
    //     $year  = base64_decode($year);
    //     $month = base64_decode($month);

    //     // ambil sequence TERBESAR untuk periode tsb (tanpa subcont)
    //     $query = $this->db->query("
    //         SELECT 
    //             MAX(CAST(SUBSTRING_INDEX(number, '/', 1) AS UNSIGNED)) AS max_seq
    //         FROM pr_subcont_productions
    //         WHERE p_month = ?
    //         AND p_year = ?
    //     ", [$month, $year]);

    //     $next_seq = 1;
    //     if ($query->row()->max_seq) {
    //         $next_seq = (int)$query->row()->max_seq + 1;
    //     }

    //     // format sequence (2 digit / 4 digit bebas)
    //     $seq = sprintf('%02d', $next_seq);

    //     echo $seq.'/PR/SCP/'.$month.'/'.$year;
    // }


    public function readDocNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $month = $this->input->post('month');
        $year  = $this->input->post('year');

        $p_month    = sprintf('%02d', intval($month));

        $send = $this->crud->query("
            SELECT DISTINCT doc_no FROM pr_subcont_productions 
            WHERE (doc_no like '%$post%')
            AND p_month = '$p_month'
            AND p_year = '$year'
            AND status = 0
            AND deleted = 0
        ");

        echo json_encode($send);
    }

    public function readSubcontProduction()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $send = $this->crud->query("
            SELECT * FROM subconts 
            WHERE (number like '%$post%' or name like '%$post%' or id like '%$post%') 
            AND subcont_type_id = 'TS002'
            AND status = 0
            AND deleted = 0
        ");

        echo json_encode($send);
    }

    public function readItemSubProd()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sql = "
            SELECT DISTINCT a.*
            FROM item_fg a
            JOIN setting_subconts b 
                ON b.item_fg_id = a.id
            JOIN subconts c 
                ON c.id = b.subcont_id
            WHERE c.subcont_type_id = 'TS002'
            AND (
                    a.number LIKE '%$post%' 
                    OR a.number_customer LIKE '%$post%' 
                    OR a.name LIKE '%$post%' 
                    OR a.id LIKE '%$post%'
            )
            AND a.deleted = 0
            ORDER BY a.number ASC
        ";

        $send = $this->crud->query($sql);
        echo json_encode($send);
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array(
            '01' => 'January', 
            '02' => 'February', 
            '03' => 'March', 
            '04' => 'April', 
            '05' => 'May', 
            '06' => 'June', 
            '07' => 'July', 
            '08' => 'August', 
            '09' => 'September', 
            '10' => 'October', 
            '11' => 'November', 
            '12' => 'December
        ');

        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    // public function readRevisions()
    // {
    //     $arr = array(
    //         ["id" => "0", "name" => "Revision 0"],
    //         ["id" => "1", "name" => "Revision 1"],
    //         ["id" => "2", "name" => "Revision 2"],
    //         ["id" => "3", "name" => "Revision 3"],
    //         ["id" => "4", "name" => "Revision 4"],
    //         ["id" => "5", "name" => "Revision 5"],
    //     );

    //     echo json_encode($arr);
    // }

    public function getData()
    {
        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_subcont_id = base64_decode($this->input->get('filter_subcont_id'));
            $filter_order_type = base64_decode($this->input->get('filter_order_type'));
            // $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));
            // $filter_revision = base64_decode($this->input->get('filter_revision'));

            // $start_date = date('Y-m-d', strtotime($filter_year . '-' . $filter_month . '-15'));
            // $end_date = date('Y-m-d', strtotime('+1 month', strtotime($filter_year . '-' . $filter_month . '-16')));

            $start_date = date("Y-m-d", strtotime($filter_year . '-' . $filter_month . '-16'));

            $start_date = date('Y-m-d', strtotime('-1 month', strtotime($filter_year . '-' . $filter_month .'-15')));
            $end_date = date('Y-m-d', strtotime($filter_year . '-' . $filter_month . '-16'));

            // $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            
            // $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            // $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $isGeneratedDate = date('Y-m-d');
            $cutoff = date('Y-m-d', strtotime($isGeneratedDate . ' -1 day'));

            // $cutoff_to= "$filter_year-$filter_month-01";


            $query_qty_in_fg_scan_in = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as fg_scan_in
            FROM fg_scan_in_label a
            WHERE a.deleted = 0
            AND a.scan_date = '$cutoff'
            GROUP BY a.item_fg_id";

            $query_qty_os_fg = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as qty_os_fg
            FROM os_fg a
            WHERE a.deleted = 0
            AND a.trans_date = '$cutoff'
            GROUP BY a.item_fg_id";

            $query_transaction_fg_in = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as initial_in
            FROM transaction_fg a
            WHERE a.deleted = 0
            AND a.request_date = '$cutoff'
            AND LEFT(a.transaction_type, 2) = 'RE'
            GROUP BY a.item_fg_id";

            $query_qty_out = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as qty_out
            FROM transaction_fg a
            WHERE a.deleted = 0
            AND a.request_date = '$cutoff'
            AND LEFT(a.transaction_type, 2) = 'IS'
            GROUP BY a.item_fg_id";

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
            AND DATE(dn.delivery_note_date) = '$cutoff'
            GROUP BY item_fg_id";



            $query_qty_in_fg_scan_in2 = "SELECT a.item_fg_id, SUM(a.qty) as fg_scan_in
            FROM fg_scan_in_label a
            WHERE a.deleted = 0
            AND a.scan_date < '$cutoff'
            GROUP BY a.item_fg_id";

            $query_qty_os_fg2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_os_fg
            FROM os_fg a
            WHERE a.deleted = 0
            AND a.trans_date < '$cutoff'
            GROUP BY a.item_fg_id";
                        
            $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
            FROM transaction_fg a
            WHERE a.deleted = 0
            AND a.request_date < '$cutoff'
            AND LEFT(a.transaction_type, 2) = 'RE'
            GROUP BY a.item_fg_id";

            $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
            FROM transaction_fg a
            WHERE a.deleted = 0
            AND a.request_date < '$cutoff'
            AND LEFT(a.transaction_type, 2) = 'IS'
            GROUP BY a.item_fg_id";

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
            AND dn.delivery_note_date < '$cutoff'
            GROUP BY dn.item_fg_id";

            $subquery_end_stock = "
                SELECT 
                    a.id as item_fg_id,
                    (
                        COALESCE(x.begin_stock, 0) + 
                        COALESCE(qc.fg_scan_in, 0) + 
                        COALESCE(qnc.qty_os_fg, 0) + 
                        COALESCE(qi.initial_in, 0) -
                        (
                            COALESCE(qo.qty_out, 0) + 
                            COALESCE(qg.initial_out_g, 0)
                        )
                    ) as fg
                FROM item_fg a
                LEFT JOIN (
                    SELECT a.id,
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
                    LEFT JOIN ($query_qty_in_fg_scan_in2) qc ON a.id = qc.item_fg_id
                    LEFT JOIN ($query_qty_os_fg2) qnc ON a.id = qnc.item_fg_id
                    LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
                    LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
                    LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
                    GROUP BY a.id
                ) x ON a.id = x.id
                LEFT JOIN ($query_qty_in_fg_scan_in) qc ON a.id = qc.item_fg_id
                LEFT JOIN ($query_qty_os_fg) qnc ON a.id = qnc.item_fg_id
                LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
                LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
                LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
            ";

            //Select Query
            $this->db->select('
                a.id, 
                a.number, 
                a.name, 
                a.mpq,
                COALESCE(b.fg, 0) as fg,
                d.id as subcont_id,
                c.share_order
            ');

            $this->db->from('item_fg a');
            $this->db->join("($subquery_end_stock) b", 'a.id = b.item_fg_id', 'left');
            $this->db->join('setting_subconts c', "a.id = c.item_fg_id");
            $this->db->join('subconts d', "d.id = c.subcont_id and d.subcont_type_id = 'TS002'");

            // if ($filter_item_fg_id != "") {
            //     $this->db->where('a.id', $filter_item_fg_id);
            // }

            if ($filter_subcont_id != "") {
                $this->db->where('d.id', $filter_subcont_id);
            }

            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->group_by(['a.id', 'd.id']);
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            // $grouped = [];
            $arr = [];
            foreach ($records as $data) {

                $item_fg_id = $data['id'];
                $fg = $data['fg'] ? intval($data['fg']) : 0;

                $this->db->select('SUM(a.outstanding) as qty_outstanding');
                $this->db->from('sales_orders a');
                $this->db->where('a.sales_order_date >=', $start_date);
                $this->db->where('a.sales_order_date <=', $end_date);
                $this->db->where('a.item_fg_id', $item_fg_id);
                $this->db->group_by('a.item_fg_id');
                $ostSoRow = $this->db->get()->row();
                $ostSo = $ostSoRow ? floatval($ostSoRow->qty_outstanding) : 0;

                // $forecast = $this->db->select('
                //         SUM(month_1) AS month_1,
                //         SUM(month_2) AS month_2
                //     ')
                //     ->from('forecasts')
                //     ->where([
                //         'item_fg_id' => $item_fg_id,
                //         'p_month'    => $filter_month,
                //         'p_year'     => $filter_year,
                //         // 'revision'   => $filter_revision,
                //         'revision'   => 0,
                //         'deleted'    => 0
                //     ])
                //     ->get()
                //     ->row();


                // $forecast = $this->db->select('
                //         SUM(f.month_1) AS month_1,
                //         SUM(f.month_2) AS month_2
                //     ')
                //     ->from('forecasts f')
                //     ->where('f.item_fg_id', $item_fg_id)
                //     ->where('f.p_month', $filter_month)
                //     ->where('f.p_year', $filter_year)
                //     ->where('f.deleted', 0)
                //     ->where('f.revision = (
                //         SELECT MAX(revision)
                //         FROM forecasts
                //         WHERE item_fg_id = f.item_fg_id
                //         AND p_month    = f.p_month
                //         AND p_year     = f.p_year
                //         AND deleted    = 0
                //     )', null, false)
                //     ->get()
                //     ->row();

                $forecast = $this->db->select('
                        SUM(f.month_1) AS month_1,
                        SUM(f.month_2) AS month_2
                    ')
                    ->from('forecasts f')
                    ->where('f.item_fg_id', $item_fg_id)
                    ->where('f.p_month', $filter_month)
                    ->where('f.p_year', $filter_year)
                    ->where('f.deleted', 0)
                    ->where('f.revision = (
                        SELECT MAX(f2.revision)
                        FROM forecasts f2
                        WHERE f2.document_no = f.document_no
                        AND f2.deleted = 0
                    )', null, false)
                    ->get()
                    ->row();

                $fcMonth1 = $forecast ? floatval($forecast->month_1) : 0;
                $fcMonth2 = $forecast ? floatval($forecast->month_2) : 0;
                // $fcM2Percent = round($fcMonth2 * 0.3, 2);
                // $fcM2Percent = ceil($fcMonth2 * 0.3);
                $fcM2Percent = ceil($fcMonth2 * 0.5);

                // SKIP jika forecast month_1 dan month_2 sama-sama 0
                if ($fcMonth1 == 0 && $fcMonth2 == 0) {
                    continue;
                }

                // if (!isset($grouped[$item_fg_id])) {
                //     $grouped[$item_fg_id] = [
                //         'rows'     => [],
                //         'need_qty' => 0,
                //     ];
                // }

                // if ($grouped[$item_fg_id]['need_qty'] === 0) {
                //     $ostOrFcMonth1 = max($ostSo, $fcMonth1);
                //     $needQty = ($ostOrFcMonth1 - $fg) + $fcM2Percent;

                //     // $needQty = ($ostSo - $fg) + $fcMonth1 + $fcM2Percent;
                //     $needQty = max(0, ceil($needQty));
                //     $grouped[$item_fg_id]['need_qty'] = $needQty;
                // }

                $selling = $this->db->select('price')
                    ->from('customer_items')
                    ->where([
                        'item_fg_id' => $item_fg_id,
                        'type_item'  => 'ORIGINAL',
                        'deleted'    => 0
                    ])
                    ->limit(1)
                    ->get()
                    ->row();
                $sellingPrice = $selling ? floatval($selling->price) : 0;

                $cost = $this->db->select('price')
                    ->from('setting_subconts')
                    ->where([
                        'item_fg_id' => $item_fg_id,
                        'subcont_id' => $data['subcont_id'],
                        'deleted'    => 0
                    ])
                    ->limit(1)
                    ->get()
                    ->row();
                $costPrice = $cost ? floatval($cost->price) : 0;

                // $grouped[$item_fg_id]['rows'][] = [
                //     "order_type"     => $filter_order_type,
                //     "subcont_id"     => $data['subcont_id'],
                //     "item_fg_id"     => $item_fg_id,
                //     "p_month"        => $filter_month,
                //     "p_year"         => $filter_year,
                //     // "revision"       => $filter_revision,
                //     "ost_so"         => $ostSo,
                //     "total_stock"    => $fg,
                //     "fc_m1"          => $fcMonth1,
                //     "fc_m2"          => $fcMonth2,
                //     "fc_m2_percent"  => $fcM2Percent,
                //     "share_order"    => $data['share_order'],
                //     "mpq"            => $data['mpq'],
                //     "selling_price"  => $sellingPrice,
                //     "cost_price"     => $costPrice,
                //     "balance"        => $sellingPrice - $costPrice,
                // ];


                // $share = floatval($data['share_order']) / 100;
                // $fgShare = $fg * $share;

                // $needQty = max(0, ceil($fcMonth1 + $ostSo + $fcM2Percent - $fgShare));
                // $orderQty = ($data['mpq'] > 0) ? ceil($needQty / $data['mpq']) * $data['mpq'] : 0;


                $share = floatval($data['share_order']) / 100;
                $totalNeedQty = max(0, ceil($fcMonth1 + $ostSo + $fcM2Percent - $fg));

                $needQty = ceil($totalNeedQty * $share);
                $orderQty = ($data['mpq'] > 0) ? ceil($needQty / $data['mpq']) * $data['mpq'] : 0;

                $arr[] = [
                    "order_type"     => $filter_order_type,
                    "subcont_id"     => $data['subcont_id'],
                    "item_fg_id"     => $item_fg_id,
                    "p_month"        => $filter_month,
                    "p_year"         => $filter_year,
                    "ost_so"         => $ostSo,
                    "total_stock"    => $fg,
                    "fc_m1"          => $fcMonth1,
                    "fc_m2"          => $fcMonth2,
                    "fc_m2_percent"  => $fcM2Percent,
                    "share_order"    => $data['share_order'],
                    "need_qty"       => $needQty,
                    "order_qty"      => $orderQty,
                    "mpq"            => $data['mpq'],
                    "selling_price"  => $sellingPrice,
                    "cost_price"     => $costPrice,
                    "balance"        => $sellingPrice - $costPrice,
                ];
            }

            // $arr = [];
            // foreach ($grouped as $item_fg_id => $group) {

            //     $totalNeedQty = $group['need_qty'];
            //     $subcontCount = count($group['rows']);

            //     // 1 subcont → langsung
            //     if ($subcontCount === 1) {
            //         $row = $group['rows'][0];

            //         $row['need_qty'] = $totalNeedQty;
            //         $row['order_qty'] = ($row['mpq'] > 0)
            //             ? ceil($totalNeedQty / $row['mpq']) * $row['mpq']
            //             : 0;

            //         $arr[] = $row;
            //         continue;
            //     }

            //     // > 1 subcont → bagi rata
            //     $splitQty = ceil($totalNeedQty / $subcontCount);

            //     foreach ($group['rows'] as $row) {

            //         $row['need_qty'] = $splitQty;
            //         $row['order_qty'] = ($row['mpq'] > 0)
            //             ? ceil($splitQty / $row['mpq']) * $row['mpq']
            //             : 0;

            //         $arr[] = $row;
            //     }
            // }

            // $arr = [];
            // foreach ($grouped as $group) {

            //     foreach ($group['rows'] as $row) {

            //         $share = floatval($row['share_order']) / 100;
            //         $fgShare = $row['total_stock'] * $share;

            //         $needQty = $row['fc_m1'] + $row['ost_so'] + $row['fc_m2_percent'] - $fgShare;
            //         $needQty = max(0, ceil($needQty));

            //         $row['need_qty'] = $needQty;

            //         $row['order_qty'] = ($row['mpq'] > 0) ? ceil($needQty / $row['mpq']) * $row['mpq'] : 0;
            //         $arr[] = $row;
            //     }
            // }


            die(json_encode($arr, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function revision()
    // {
    //     $filter_month = $this->input->post('filter_month');
    //     $filter_year = $this->input->post('filter_year');

    //     $this->db->select('revision');
    //     $this->db->from('pr_subcont_productions');
    //     if ($filter_month != "" or $filter_year != "") {
    //         $this->db->where('p_month', $filter_month);
    //         $this->db->where('p_year', $filter_year);
    //     }
    //     $this->db->where('deleted', 0);
    //     $this->db->group_by('revision');
    //     $this->db->order_by('revision', 'desc');
    //     $this->db->limit(1);
    //     $record = $this->db->get()->row();
    //     echo @$record->revision ? $record->revision : 0;
    // }

    // public function checkForecast()
    // {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));

    //     $this->db->select('*');
    //     $this->db->from('forecasts');
    //     if ($filter_month != "" or $filter_year != "") {
    //         $this->db->where('p_month', $filter_month);
    //         $this->db->where('p_year', $filter_year);
    //     }
    //     $this->db->where('revision', intval($filter_revision));
    //     $records = $this->db->get()->result_array();

    //     if (count($records) > 0) {
    //         echo json_encode(array("theme" => "success"));
    //     } else {
    //         echo json_encode(array("theme" => "error"));
    //     }
    // }

    public function checkForecast()
    {
        $filter_month    = base64_decode($this->input->get('filter_month'));
        $filter_year     = base64_decode($this->input->get('filter_year'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_subcont_id = base64_decode($this->input->get('filter_subcont_id'));

        $this->db->distinct();
        $this->db->select('a.item_fg_id');
        $this->db->from('forecasts a');
        $this->db->join('setting_subconts b', 'b.item_fg_id = a.item_fg_id');
        $this->db->join('subconts c', 'c.id = b.subcont_id');
        $this->db->where('c.subcont_type_id', 'TS002');

        if($filter_subcont_id !== ""){
            $this->db->where('c.id', $filter_subcont_id);
        }

        if ($filter_month !== "" && $filter_year !== "") {
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
        }

        $this->db->limit(1);

        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    // public function checkFg()
    // {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));

    //     $cutoff = Date();// harus tanggal 25 berdasarkan filter month dan year
    //     $today = new Date();

    //     if ($cutoff == $today) {
    //         echo json_encode(array("theme" => "success"));
    //     } else {
    //         echo json_encode(array("theme" => "error"));
    //     }
    // }

    // public function checkFg()
    // {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));

    //     if ($this->isGenerateDay($filter_month, $filter_year)) {
    //         echo json_encode(array("theme" => "success"));
    //     } else {
    //         echo json_encode(array("theme" => "error"));
    //     }
    // }

    public function checkFg()
    {
        echo json_encode(array("theme" => "success"));
    }

    public function checkOstSo()
    {
        echo json_encode(array("theme" => "success"));
    }

    // public function create()
    // {
    //     if (!$this->input->post()) {
    //         show_error("Invalid Request");
    //     }

    //     $post = $this->input->post('data');
    //     $p_month = sprintf('%02d', intval($post['p_month']));

    //     // $where = [
    //     //     "p_month"    => $p_month,
    //     //     "p_year"     => $post['p_year'],
    //     //     // "revision"   => intval($post['revision']),
    //     //     "subcont_id" => $post['subcont_id'],
    //     //     "item_fg_id" => $post['item_fg_id']
    //     // ];

    //     $data = [
    //         "p_month"        => $p_month,
    //         "p_year"         => $post['p_year'],
    //         "doc_no"         => $post['doc_no'],
    //         "order_type"     => $post['order_type'],
    //         "subcont_id"     => $post['subcont_id'],
    //         "item_fg_id"     => $post['item_fg_id'],
    //         "ost_so"         => intval($post['ost_so']),
    //         "total_stock"    => intval($post['total_stock']),
    //         "fc_m1"          => intval($post['fc_m1']),
    //         "fc_m2"          => intval($post['fc_m2']),
    //         "fc_m2_percent"  => intval($post['fc_m2_percent']),
    //         "mpq"            => intval($post['mpq']),
    //         "need_qty"       => intval($post['need_qty']),
    //         "order_qty"      => intval($post['order_qty']),
    //         "selling_price"  => floatval($post['selling_price']),
    //         "cost_price"     => floatval($post['cost_price']),
    //         "balance"        => floatval($post['balance']),
    //     ];

    //     // $exists = $this->crud->reads('pr_subcont_productions', [], $where);

    //     // if (count($exists) > 0) {
    //     //     $this->crud->update('pr_subcont_productions', $where, $data);
    //     // } else {
    //     //     }
        
    //     $this->crud->create('pr_subcont_productions', $data);

    //     echo json_encode([
    //         "theme"   => "success",
    //         "title"   => "Success",
    //         "message" => "PR Subcont Production saved"
    //     ]);
    // }

    private function generate_doc_no($year, $month, $order_type, $subcont_id)
    {
        $doc    = ($order_type == 'Regular') ? 'PR' : 'PRA';
        $prefix = $doc . '-SCP-' . $month . '-' . $year;

        $subconts = $this->db->distinct()->select('d.id AS subcont_id')
                ->from('item_fg a')
                ->join('setting_subconts c', 'a.id = c.item_fg_id')
                ->join('subconts d', "d.id = c.subcont_id AND d.subcont_type_id = 'TS002'")
                ->get()
                ->result_array();


        $whereSubcont = '';

        if (!empty($subcont_id)) {

            // generate per subcont
            $whereSubcont = " AND subcont_id = '{$subcont_id}' ";

        } else {

            // generate global → ambil semua subcont TS002
            $ids      = array_column($subconts, 'subcont_id');

            if (!empty($ids)) {
                $whereSubcont = " AND subcont_id IN ('" . implode("','", $ids) . "')";
            }
        }

        $q = $this->db->query("
            SELECT CAST(SUBSTRING_INDEX(doc_no,'-',1) AS UNSIGNED) AS seq
            FROM pr_subcont_productions
            WHERE p_month = '{$month}'
            AND p_year  = '{$year}'
            AND order_type = '{$order_type}'
            {$whereSubcont}
            ORDER BY seq DESC
            LIMIT 1
        ");

        $seq = ($q->num_rows() > 0)
            ? sprintf('%02d', $q->row()->seq + 1)
            : '01';

        return $seq . '-' . $prefix;
    }

    public function generate_doc_no_additional()
    {
        $p_month    = sprintf('%02d', intval($this->input->post('p_month')));
        $p_year     = $this->input->post('p_year');
        $subcont_id = $this->input->post('subcont_id');

        $doc_no = $this->generate_doc_no(
            $p_year,
            $p_month,
            'Additional',
            $subcont_id
        );

        echo json_encode([
            'doc_no' => $doc_no
        ]);
    }


    // public function create()
    // {
    //     if (!$this->input->post()) {
    //         show_error("Invalid Request");
    //     }

    //     $post = $this->input->post('data');
    //     $p_month    = sprintf('%02d', intval($post['p_month']));
    //     $order_type = $post['order_type'];

    //     $data = [
    //         "p_month"        => $p_month,
    //         "p_year"         => $post['p_year'],
    //         "order_type"     => $order_type,
    //         "subcont_id"     => $post['subcont_id'],
    //         "item_fg_id"     => $post['item_fg_id'],
    //         "ost_so"         => intval($post['ost_so']),
    //         "total_stock"    => intval($post['total_stock']),
    //         "fc_m1"          => intval($post['fc_m1']),
    //         "fc_m2"          => intval($post['fc_m2']),
    //         "fc_m2_percent"  => intval($post['fc_m2_percent']),
    //         "mpq"            => intval($post['mpq']),
    //         "need_qty"       => intval($post['need_qty']),
    //         "order_qty"      => intval($post['order_qty']),
    //         "selling_price"  => floatval($post['selling_price']),
    //         "cost_price"     => floatval($post['cost_price']),
    //         "balance"        => floatval($post['balance']),
    //     ];

    //     if ($order_type === 'Regular') {

    //         $existing = $this->db->get_where(
    //             'pr_subcont_productions',
    //             [
    //                 'p_month'    => $p_month,
    //                 'p_year'     => $post['p_year'],
    //                 'subcont_id' => $post['subcont_id'],
    //                 'order_type' => 'Regular'
    //             ]
    //         )->row();

    //         if ($existing) {
    //             // UPDATE (tidak generate doc_no)
    //             $this->crud->update('pr_subcont_productions', 
    //                 ['id' => $existing->id],
    //                 $data,
    //             );

    //             $action = 'updated';
    //         } else {
    //             // CREATE + NUMBER
    //             $data['doc_no'] = $this->generate_doc_no(
    //                 $post['p_year'],
    //                 $p_month,
    //                 $order_type,
    //                 $post['subcont_id']
    //             );

    //             $this->crud->create('pr_subcont_productions', $data);
    //             $action = 'created';
    //         }

    //     } else {

    //         $data['doc_no'] = $this->generate_doc_no(
    //             $post['p_year'],
    //             $p_month,
    //             $order_type,
    //             $post['subcont_id']
    //         );

    //         $this->crud->create('pr_subcont_productions', $data);
    //         $action = 'created';
    //     }

    //     echo json_encode([
    //         "theme"   => "success",
    //         "title"   => "Success",
    //         "message" => "PR Subcont Production {$action}"
    //     ]);
    // }


    public function create()
    {
        if (!$this->input->post()) {
            show_error("Invalid Request");
        }

        $post       = $this->input->post('data');
        $p_month    = sprintf('%02d', intval($post['p_month']));
        $p_year     = $post['p_year'];
        $order_type = $post['order_type'];
        $subcont_id = $post['subcont_id'];

        if ($order_type === 'Regular') {

            $header = $this->db->get_where(
                'pr_subcont_productions',
                [
                    'p_month'    => $p_month,
                    'p_year'     => $p_year,
                    'subcont_id' => $subcont_id,
                    'order_type' => 'Regular'
                ]
            )->row();

            if ($header) {
                $doc_no = $header->doc_no;
            } else {
                $doc_no = $this->generate_doc_no(
                    $p_year,
                    $p_month,
                    'Regular',
                    $subcont_id
                );
            }

        } else {
            // $doc_no = $this->generate_doc_no(
            //     $p_year,
            //     $p_month,
            //     'Additional',
            //     $subcont_id
            // );

            $doc_no = $post['doc_no'];
        }

        $data = [
            "doc_no"         => $doc_no,
            "p_month"        => $p_month,
            "p_year"         => $p_year,
            "order_type"     => $order_type,
            "subcont_id"     => $subcont_id,
            "item_fg_id"     => $post['item_fg_id'],
            "ost_so"         => intval($post['ost_so']),
            "total_stock"    => intval($post['total_stock']),
            "fc_m1"          => intval($post['fc_m1']),
            "fc_m2"          => intval($post['fc_m2']),
            "fc_m2_percent"  => intval($post['fc_m2_percent']),
            "mpq"            => intval($post['mpq']),
            "share_order"    => intval($post['share_order']),
            "need_qty"       => intval($post['need_qty']),
            "order_qty"      => intval($post['order_qty']),
            "selling_price"  => floatval($post['selling_price']),
            "cost_price"     => floatval($post['cost_price']),
            "balance"        => floatval($post['balance']),
        ];

        if ($order_type === 'Regular') {

            $existing_item = $this->db->get_where(
                'pr_subcont_productions',
                [
                    'p_month'    => $p_month,
                    'p_year'     => $p_year,
                    'subcont_id' => $subcont_id,
                    'order_type' => 'Regular',
                    'item_fg_id' => $post['item_fg_id']
                ]
            )->row();

            // $existing_item_po = $this->db->get_where(
            //     'po_subcont_productions',
            //     [
            //         'subcont_id' => $subcont_id,
            //         'doc_no_pr'  => $doc_no,
            //         'item_fg_id' => $post['item_fg_id']
            //     ]
            // )->row();

            // if ($existing_item && $existing_item_po) {
            //     echo json_encode([
            //         "theme"   => "error",
            //         "title"   => "Error",
            //         "message" => "PR No. $doc_no already exists in PO and cannot be modified"
            //     ]);
            //     return;
            // }

            // if ($existing_item && !$existing_item_po) {
            //     // REGULAR → UPDATE
            //     $this->crud->update('pr_subcont_productions',
            //         ['id' => $existing_item->id],
            //         [
            //             "ost_so"         => intval($post['ost_so']),
            //             "total_stock"    => intval($post['total_stock']),
            //             "fc_m1"          => intval($post['fc_m1']),
            //             "fc_m2"          => intval($post['fc_m2']),
            //             "fc_m2_percent"  => intval($post['fc_m2_percent']),
            //             "mpq"            => intval($post['mpq']),
            //             "need_qty"       => intval($post['need_qty']),
            //             "order_qty"      => intval($post['order_qty']),
            //             "selling_price"  => floatval($post['selling_price']),
            //             "cost_price"     => floatval($post['cost_price']),
            //             "balance"        => floatval($post['balance']),
            //         ]
            //     );
            //     $action = "updated";
            // } else if(!$existing_item) {
            //     // REGULAR → CREATE
            //     $this->crud->create('pr_subcont_productions', $data);
            //     $action = 'created';
            // }


            if ($existing_item) {
                // REGULAR → UPDATE
                $this->crud->update('pr_subcont_productions',
                    ['id' => $existing_item->id],
                    [
                        "ost_so"         => intval($post['ost_so']),
                        "total_stock"    => intval($post['total_stock']),
                        "fc_m1"          => intval($post['fc_m1']),
                        "fc_m2"          => intval($post['fc_m2']),
                        "fc_m2_percent"  => intval($post['fc_m2_percent']),
                        "mpq"            => intval($post['mpq']),
                        "need_qty"       => intval($post['need_qty']),
                        "order_qty"      => intval($post['order_qty']),
                        "selling_price"  => floatval($post['selling_price']),
                        "cost_price"     => floatval($post['cost_price']),
                        "balance"        => floatval($post['balance']),
                    ]
                );
                $action = "updated";
            } else {
                // REGULAR → CREATE
                $this->crud->create('pr_subcont_productions', $data);
                $action = 'created';
            }

        } else {
            // ADDITIONAL → CREATE
            $this->crud->create('pr_subcont_productions', $data);
            $action = 'created';
        }

        echo json_encode([
            "theme"   => "success",
            "title"   => "Success",
            "message" => "PR Subcont Production {$action}"
        ]);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/pr_subcont_productions.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/pr_subcont_productions.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/pr_subcont_productions.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function monthName($id)
    {
        if ($id == "01") {
            return "JANUARY";
        } elseif ($id == "02") {
            return "FEBRUARY";
        } elseif ($id == "03") {
            return "MARCH";
        } elseif ($id == "04") {
            return "APRIL";
        } elseif ($id == "05") {
            return "MAY";
        } elseif ($id == "06") {
            return "JUNE";
        } elseif ($id == "07") {
            return "JULY";
        } elseif ($id == "08") {
            return "AUGUST";
        } elseif ($id == "09") {
            return "SEPTEMBER";
        } elseif ($id == "10") {
            return "OCTOBER";
        } elseif ($id == "11") {
            return "NOVEMBER";
        } elseif ($id == "12") {
            return "DECEMBER";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=pr_subcont_productions_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_subcont_id = base64_decode($this->input->get('filter_subcont_id'));
        $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));
        $filter_order_type = base64_decode($this->input->get('filter_order_type'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        // $monthM1 = $this->monthName($filter_month) . ' ' . $filter_year;
        // $nextMonth = strtotime('+1 month', strtotime($filter_year . '-' . $filter_month . '-01'));
        // $monthM2 = $this->monthName(date('m', $nextMonth)) . ' ' . date('Y', $nextMonth);

        // $monthM1 = date('M', strtotime($filter_year . '-' . $filter_month . '-01'));
        // $nextMonth = strtotime('+1 month', strtotime($filter_year . '-' . $filter_month . '-01'));
        // $monthM2 = date('M', $nextMonth);

        $monthM1 = date('M', strtotime($filter_year . '-' . $filter_month . '-01')) . ' ' . $filter_year;
        $nextMonth = strtotime('+1 month', strtotime($filter_year . '-' . $filter_month . '-01'));
        $monthM2 = date('M', $nextMonth) . ' ' . date('Y', $nextMonth);

        $this->db->select('
            a.*,
            b.name as subcont_name,
            e.id as item_fg_id,
            e.number as item_fg_number,
            e.name as item_fg_name,
            e.mpq
        ');
        $this->db->from('pr_subcont_productions a');
        $this->db->join('subconts b', 'a.subcont_id = b.id');
        $this->db->join('item_fg e', 'a.item_fg_id = e.id');

        if ($filter_month != "") {
            $this->db->where('a.p_month', (int)$filter_month);
        }

        if ($filter_year != "") {
            $this->db->where('a.p_year', (int)$filter_year);
        }

        // if ($filter_revision != "") {
        //     $this->db->where('a.revision', (int)$filter_revision);
        // }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_order_type != "") {
            $this->db->where('a.order_type', $filter_order_type);
        }

        if ($filter_subcont_id != "") {
            $this->db->where('b.id', $filter_subcont_id);
        }

        $this->db->group_by(['e.id', 'b.id', 'a.doc_no']);
        $this->db->order_by('b.name', 'asc');
        $this->db->order_by('a.doc_no', 'asc');
        $this->db->order_by('e.number', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html>
                <head>
                <title>Print Data</title>
                </head>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }
                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 10px;
                    }
                    #customers td, 
                    #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                    }
                    #customers tr:nth-child(even){
                        background-color: #f2f2f2;
                    }
                    #customers tr:hover {
                        background-color: #ddd;
                    }
                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: left;
                        color: black;
                    }
                </style>
                <body>
        <div style="width:1600px;">
        <table style="width: 100%;">
            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                <img src="' . $config->logo . '" width="30">
            </td>
            <td style="font-size: 14px; text-align: left; margin:2px;">
                <b>' . $config->name . '</b><br>
                <small>PPC DEPARTEMENT</small>
            </td>
        </table>

        <center>
            <b style="font-size:18px;">PURCHASE REQUEST TO SUBCONT PRODUCTION</b>
        </center>

        <p style="font-size:12px; margin:0;">PERIOD ' . $this->monthName($filter_month) . ' ' . $filter_year . '</p>
        <p style="font-size:12px; margin:0;">PRINT DATE ' . date("d M Y H:m:s") . '</p>
        <p style="font-size:12px; margin:0;">PRINT BY ' . $this->session->username . '</p>
        <br>
        <table id="customers" border="1">';

        $html .= '
            <tr>
                <th rowspan="2" style="text-align: center;">No</th>
                <th rowspan="2" style="text-align: center;">SUBCONT NAME</th>
                <th rowspan="2" style="text-align: center;">DOC NO</th>
                <th rowspan="2" style="text-align: center;">ORDER TYPE</th>
                <th rowspan="2" style="text-align: center;">PRODUCT ID</th>
                <th rowspan="2" style="text-align: center;">PRODUCT NO</th>
                <th rowspan="2" style="text-align: center;">PRODUCT NAME</th>
                <th rowspan="2" style="text-align: center;">OST SO</th>
                <th rowspan="2" style="text-align: center;">STOCK FG</th>
                <th colspan="3" style="text-align: center;">FORECAST</th>
                <th rowspan="2" style="text-align: center;">MPQ</th>
                <th rowspan="2" style="text-align: center;">SHARE ORDER %</th>
                <th rowspan="2" style="text-align: center;">NEED QTY<br>(before round MPQ)</th>
                <th rowspan="2" style="text-align: center;">ORDER QTY<br>(round MPQ)</th>
                <th rowspan="2" style="text-align: center;">SELL PRICE</th>
                <th rowspan="2" style="text-align: center;">COST PRICE</th>
                <th rowspan="2" style="text-align: center;">BALANCE</th>
                <th rowspan="2" style="text-align: center;">GENERATE DATE</th>
            </tr>
            <tr>
                <th style="text-align: center;">'.$monthM1.'</th>
                <th style="text-align: center;">'.$monthM2.'</th>
                <th style="text-align: center;">50% * '.$monthM2.'</th>
            </tr>';

        if(!empty($records)){

            $no = 1;
            foreach ($records as $data) {
                $generate_date = date('Y-m-d', strtotime($data['created_date']));

                $html .= '<tr>
                    <td width="25" style="text-align:center;">'.$no.'</td>
                    <td width="250" style="text-align:left;">'.$data['subcont_name'].'</td>
                    <td width="170" style="text-align:left;">'.$data['doc_no'].'</td>
                    <td width="100" style="text-align:left;">'.$data['order_type'].'</td>
                    <td width="100" style="text-align:left;">'.$data['item_fg_id'].'</td>
                    <td width="150" style="text-align:left;">'.$data['item_fg_number'].'</td>
                    <td width="200" style="text-align:left;">'.$data['item_fg_name'].'</td>
                    <td width="80" style="text-align:right;">'.$this->format_number($data['ost_so']).'</td>
                    <td width="80" style="text-align:right;">'.$this->format_number($data['total_stock']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['fc_m1']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['fc_m2']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['fc_m2_percent']).'</td>
                    <td width="80" style="text-align:right;">'.$data['mpq'].'</td>
                    <td width="80" style="text-align:right;">'.$data['share_order'].'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['need_qty']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['order_qty']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['selling_price']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['cost_price']).'</td>
                    <td width="100" style="text-align:right;">'.$this->format_number($data['balance']).'</td>
                    <td width="100" style="text-align:center;">'.$generate_date.'</td>
                </tr>';

                $no++;
            }
        }

        $html .= '</tr></table></div></body></html>';
        echo $html;
    }
}
