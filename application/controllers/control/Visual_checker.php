<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Visual_checker extends CI_Controller
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

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/visual_checker');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readDeliveryNoteNoSCTF()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $query = "
            SELECT DISTINCT
                a.delivery_to, 
                a.delivery_note_no,
                a.delivery_date,
                a.destination,
                CASE 
                    WHEN a.delivery_to = 'SUBCONT' THEN 'Subcont'
                    ELSE 'Teaching Factory'
                END AS delivery_from,
                COALESCE(b.name, c.name) as incoming_from,
                COALESCE(b.number, c.number) as destination_code
            FROM delivery_to_subconts a
                LEFT JOIN subconts b ON b.id = a.destination
                LEFT JOIN teaching_factory c ON c.id = a.destination
            WHERE (a.delivery_note_no LIKE '%{$post}%' 
                OR a.delivery_to LIKE '%{$post}%' 
                OR a.id LIKE '%{$post}%') 
            AND a.status = 0
        ";

        $send = $this->crud->query($query);

        echo json_encode($send);
    }

    public function readSources()
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

    public function datatablesTemp($delivery_order_no, $delivery_note_date)
    {
        $delivery_order_no = explode(",", base64_decode($delivery_order_no));
        $delivery_note_date = base64_decode($delivery_note_date);

        $this->db->select("a.delivery_order_no, 
            b.id as item_fg_id, 
            b.number as item_fg_number, 
            b.name as item_fg_name,
            c.customer_order_no, 
            c.sales_order_no,
            a.qty_del as qty,
            (CASE
            WHEN a.actual_delivery_date < a.delivery_date THEN 1
            WHEN a.actual_delivery_date = a.delivery_date THEN 0
            ELSE 0
            END) as status_delivery,
            b.uom");
        $this->db->from('delivery_orders a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no 
                                          AND a.item_fg_id = c.item_fg_id 
                                          AND a.customer_id = c.customer_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.delivery_order_no', $delivery_order_no);
        $this->db->order_by('a.delivery_order_no, b.number');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function number($delivery_note_date, $divison_number)
    {
        $divison_number = base64_decode($divison_number);
        $customer_number = base64_decode($this->input->post('customer_number'));

        $numberCust = $customer_number;
        $divisions  = "DN". $divison_number;
        $datenow    = date("my", strtotime(base64_decode($delivery_note_date)));
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 7, 4) as kode FROM visual_checker WHERE `delivery_note_no` like '%$dn_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = @$rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $divisions. "-" . $autoID . "-" . $numberCust . "-" . $datenow;
    }

    public function readIncomingDocNo()
    {
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $filter_delivery_from = $this->input->get("filter_delivery_from");

        $this->db->distinct();
        $this->db->select('incoming_doc_no');
        $this->db->from('visual_checker');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('incoming_date >=', $filter_from);
            $this->db->where('incoming_date <=', $filter_to);
        }

        if (!empty($filter_delivery_from)) {
            $this->db->where('filter_delivery_from', $filter_delivery_from);
        }

        $this->db->order_by('incoming_doc_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }

    // public function readItemFg()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $customer_id = $this->input->post('customer_id');

    //     $query = "
    //         SELECT
    //             a.item_fg_id,
    //             b.number,
    //             b.name,
    //             a.workorder,
    //             (COALESCE(SUM(a.qty_receive), 0) - COALESCE(c.qty_receive_total, 0)) AS qty_receive,
    //             MIN(a.delivery_date) as delivery_date,
    //             b.uom,
    //             COALESCE(sc.number, tf.number) AS source
    //         FROM incoming_from_sc_tf a
    //         JOIN item_fg b ON a.item_fg_id = b.id

    //         LEFT JOIN (
    //             SELECT 
    //                 vc.item_fg_id,
    //                 vc.workorder,
    //                 SUM(vc.qty_receive) AS qty_receive_total
    //             FROM visual_checker vc
    //             WHERE vc.deleted = 0
    //             GROUP BY vc.item_fg_id, vc.workorder
    //         ) c ON c.item_fg_id = a.item_fg_id AND c.workorder = a.workorder

    //         JOIN customer_items d 
    //             ON d.item_fg_id = a.item_fg_id
    //             AND d.deleted = 0
    //             AND d.customer_id = '$customer_id'

    //         LEFT JOIN subconts sc ON sc.id = a.delivery_from
    //         LEFT JOIN teaching_factory tf ON tf.id = a.delivery_from

    //         LEFT JOIN (
    //             SELECT 
    //                 x.item_fg_id,
    //                 x.workorder,
    //                 x.doc_no,
    //                 x.process_date AS process_date,
    //                 CASE 
    //                     WHEN MAX(x.process_name) = 'Internal Finishing' THEN SUM(x.external)
    //                     ELSE NULL
    //                 END AS source_value
    //             FROM internal_process x
    //             WHERE x.deleted = 0
    //             GROUP BY x.item_fg_id, x.workorder
    //         ) proc ON proc.item_fg_id = a.item_fg_id AND proc.workorder = a.workorder

    //         WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
    //         GROUP BY a.item_fg_id, a.workorder, b.number, b.name, b.uom, c.qty_receive_total
    //         HAVING (COALESCE(SUM(a.qty_receive), 0) - COALESCE(c.qty_receive_total, 0)) > 0
    //         ORDER BY a.workorder ASC, b.number ASC
    //     ";

    //     $send = $this->crud->query($query);
    //     echo json_encode($send);
    // }

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        // $customer_id = $this->input->post('customer_id');

        // LEFT JOIN customer_items ci 
        //     ON ci.item_fg_id = x.item_fg_id
        // AND ci.deleted = 0
        // AND ci.customer_id = '$customer_id'
        

        $exclude_keys = $this->input->post('exclude_keys');
        $exclude_sql = '';

        if (!empty($exclude_keys)) {
            $exclude_arr = explode(',', $exclude_keys);
            $exclude_arr = array_filter($exclude_arr);
            if (!empty($exclude_arr)) {
                $escaped = array_map(function($v) {
                    return $this->db->escape_str($v);
                }, $exclude_arr);
                $in = "'" . implode("','", $escaped) . "'";
                $exclude_sql = "AND CONCAT(x.item_fg_id, '_', x.workorder) NOT IN ($in)";
            }
        }

        $query = "
            SELECT
                x.item_fg_id,
                b.number,
                b.name,
                x.workorder,
                (COALESCE(SUM(x.qty_receive), 0) - COALESCE(vc.qty_receive_total, 0)) AS qty_receive,
                MIN(x.delivery_date) AS delivery_date,
                b.uom,
                x.source
            FROM (

                /* 1. Incoming From SC/TF */
                SELECT
                    a.item_fg_id,
                    a.workorder,
                    a.qty_receive,
                    a.delivery_date,
                    COALESCE(sc.number, tf.number) AS source
                FROM incoming_from_sc_tf a
                LEFT JOIN subconts sc ON sc.id = a.delivery_from
                LEFT JOIN teaching_factory tf ON tf.id = a.delivery_from
                WHERE a.deleted = 0

                UNION ALL

                /* 2. Internal Process – Internal Finishing */
                SELECT
                    ip.item_fg_id,
                    ip.workorder,
                    ip.external AS qty_receive,
                    ip.process_date AS delivery_date,
                    'Internal Finishing' AS source
                FROM internal_process ip
                WHERE ip.deleted = 0
                AND ip.process_name = 'Internal Finishing'
            ) x

            JOIN item_fg b ON b.id = x.item_fg_id

            LEFT JOIN (
                SELECT 
                    item_fg_id,
                    workorder,
                    SUM(check_qty) AS qty_receive_total
                FROM visual_checker_detail
                WHERE deleted = 0
                GROUP BY item_fg_id, workorder
            ) vc ON vc.item_fg_id = x.item_fg_id AND vc.workorder = x.workorder

            WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%')

            $exclude_sql

            GROUP BY 
                x.item_fg_id, 
                x.workorder, 
                b.number, 
                b.name, 
                b.uom, 
                vc.qty_receive_total,
                x.source

            HAVING (COALESCE(SUM(x.qty_receive), 0) - COALESCE(vc.qty_receive_total, 0)) > 0

            ORDER BY x.workorder ASC, b.number ASC
        ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {

            $get = $this->input->get();

            $filter_type = @base64_decode($get['filter_type']);
            $filter_from           = @base64_decode($get['filter_from']);
            $filter_to             = @base64_decode($get['filter_to']);
            $filter_visual_process = @base64_decode($get['filter_visual_process']);
            $filter_item_fg        = @base64_decode($get['filter_item_fg']);
            $filter_source         = @base64_decode($get['filter_source']);
            $filter_inspector      = @base64_decode($get['filter_inspector']);

            $page   = intval($this->input->post('page')) ?: 1;
            $rows   = intval($this->input->post('rows')) ?: 10;
            $offset = ($page - 1) * $rows;

            $result = [];

            $this->db->select("
                a.id,
                a.check_date,
                a.printed,
                a.visual_process,
                d.name as inspector,

                b.item_fg_id,
                c.number AS item_fg_number,
                c.name   AS item_fg_name,

                b.source,
                b.workorder,
                b.check_qty,
                b.ok_qty,
                b.rework_qty,
                b.qty_ng_total AS total_ng_qty,

                a.created_by,
                a.created_date,
                a.updated_by,
                a.updated_date
            ");

            $this->db->from('visual_checker a');
            $this->db->join('visual_checker_detail b', 'b.visual_checker_id = a.id AND b.deleted = 0');
            $this->db->join('item_fg c', 'c.id = b.item_fg_id', 'left');

            $this->db->join('man_powers d', 'd.nik = a.inspector', 'left');

            // if ($filter_from !== "" && $filter_to !== "") {
            //     $this->db->where('a.check_date >=', $filter_from);
            //     $this->db->where('a.check_date <=', $filter_to);
            // }

            if ($filter_type == "Check Date") {
                if ($filter_from !== "" && $filter_to !== "") {
                    $this->db->where('a.check_date >=', $filter_from);
                    $this->db->where('a.check_date <=', $filter_to);
                }
            }

            if ($filter_type == "Production Date") {

                $this->db->join("(SELECT 
                                    op.item_fg_id,
                                    op.workorder,
                                    op.trans_date,
                                    op.shift,
                                    op.operator
                                FROM output_production_press op
                                WHERE op.deleted = 0
                                AND op.id = (
                                    SELECT op2.id
                                    FROM output_production_press op2
                                    WHERE op2.item_fg_id = op.item_fg_id
                                    AND op2.workorder = op.workorder
                                    AND op2.deleted = 0
                                    ORDER BY op2.shift DESC, op2.trans_date DESC
                                    LIMIT 1
                                )) opp", 
                                "opp.item_fg_id = b.item_fg_id AND opp.workorder = b.workorder", 
                                "left");

                $filter_from_prod = @base64_decode($get['filter_from_prod']);
                $filter_to_prod   = @base64_decode($get['filter_to_prod']);

                if ($filter_from_prod !== "" && $filter_to_prod !== "") {
                    $this->db->where('opp.trans_date >=', $filter_from_prod);
                    $this->db->where('opp.trans_date <=', $filter_to_prod);
                }
            }

            if ($filter_visual_process !== "") {
                $this->db->where('a.visual_process', $filter_visual_process);
            }

            if ($filter_item_fg !== "") {
                $this->db->where('b.item_fg_id', $filter_item_fg);
            }

            if ($filter_source !== "") {
                $this->db->where('b.source', $filter_source);
            }

            if ($filter_inspector !== "") {
                $this->db->where('a.inspector', $filter_inspector);
            }

            $this->db->where('a.deleted', 0);
            $this->db->order_by('a.check_date');
            $this->db->order_by('a.visual_process');
            $this->db->order_by('d.name');
            $this->db->order_by('c.number');

            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();

            $result['total'] = $totalRows;
            $result['rows']  = $records;

            echo json_encode($result);
        }
    }

    public function datatableDetails()
    {
        if ($this->input->get()) {

            $filter_type = base64_decode($this->input->get('filter_type'));
            $id      = base64_decode($this->input->get('id'));
            $item_fg = base64_decode($this->input->get('item_fg'));
            $filter_from_prod = base64_decode($this->input->get('filter_from_prod'));
            $filter_to_prod   = base64_decode($this->input->get('filter_to_prod'));

            $master_ng = $this->db->select("code, name")
                ->from("master_ng")
                ->where("deleted", 0)
                ->order_by("code")
                ->get()
                ->result_array();

            $subQuery = "
                SELECT 
                    op.item_fg_id,
                    op.workorder,
                    op.trans_date,
                    op.shift,
                    op.operator
                FROM output_production_press op
                WHERE op.deleted = 0
                AND op.id = (
                    SELECT op2.id
                    FROM output_production_press op2
                    WHERE op2.item_fg_id = op.item_fg_id
                    AND op2.workorder = op.workorder
                    AND op2.deleted = 0
                    ORDER BY 
                        op2.shift DESC,
                        op2.trans_date DESC
                    LIMIT 1
                )
            ";

            $this->db->select("
                d.id as detail_id,
                d.compound_lot_no,
                d.source,
                d.operator_finishing,
                d.qty_ng_total,
                vc.inspector,
                vc.check_date,
                opp.operator as operator_name,
                opp.trans_date as prod_date
            ");

            $this->db->from("visual_checker_detail d");
            $this->db->join("visual_checker vc", "vc.id = d.visual_checker_id");
            $this->db->join('man_powers mp', "mp.nik = vc.inspector");
            $this->db->join("($subQuery) opp", "opp.item_fg_id = d.item_fg_id AND opp.workorder = d.workorder", "left");
            $this->db->where("d.visual_checker_id", $id);

            if (!empty($item_fg)) {
                $this->db->where("d.item_fg_id", $item_fg);
            }

            if ($filter_type == "Production Date") {
                if ($filter_from_prod !== "" && $filter_to_prod !== "") {
                    $this->db->where('opp.trans_date >=', $filter_from_prod);
                    $this->db->where('opp.trans_date <=', $filter_to_prod);
                }
            }

            $detail_rows = $this->db->get()->result_array();

            $detail_ids = array_column($detail_rows, 'detail_id');

            $ng_rows_query = $this->db->select("
                    ng.detail_id,
                    ng.ng_code,
                    ng.qty_ng
                ")
                ->from("visual_checker_ng ng")
                ->join("master_ng m", "m.code = ng.ng_code", "left")
                ->where("ng.deleted", 0);

            if (!empty($detail_ids)) {
                $ng_rows_query->where_in('ng.detail_id', $detail_ids);
            }

            $ng_rows = $ng_rows_query->get()->result_array();


            $ng_map = [];
            foreach ($ng_rows as $r) {
                $ng_map[$r['detail_id']][$r['ng_code']] = (int)$r['qty_ng'];
            }

            foreach ($detail_rows as &$row) {

                foreach ($master_ng as $m) {
                    $code = $m['code'];
                    $row[$code] = isset($ng_map[$row['detail_id']][$code])
                        ? $ng_map[$row['detail_id']][$code]
                        : 0;
                }
            }

            $ng_with_values = [];

            foreach ($detail_rows as $row) {
                foreach ($master_ng as $m) {
                    $code = $m['code'];
                    if (!empty($row[$code]) && $row[$code] > 0) {
                        $ng_with_values[$code] = true;
                    }
                }
            }

            // Filter master_ng -> hanya sisakan kode yg memang ada nilainya
            $master_ng = array_values(array_filter($master_ng, function($m) use ($ng_with_values) {
                return isset($ng_with_values[$m['code']]);
            }));

            // Hilangkan kolom NG bernilai 0 dari detail_rows
            foreach ($detail_rows as &$row) {
                foreach ($master_ng as $m) {
                    $code = $m['code'];
                    // pastikan kolom selain yg valid saja yg dikirim
                    // kolom ng yg tidak termasuk ng_with_values sudah tidak ada
                }
            }

            echo json_encode([
                "columns" => $master_ng,
                "rows" => $detail_rows
            ]);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $incoming_doc_no = base64_decode($this->input->get('incoming_doc_no'));

            $this->db->select("
                a.*, 
                b.number as item_fg_number, 
                b.name as item_fg_name, 
                b.uom
            ");
            $this->db->from('visual_checker a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.incoming_doc_no', $incoming_doc_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function createHeader()
    {
        if ($this->input->post()) {

            $post = $this->input->post();

            $this->crud->create('visual_checker', $post);

            $this->db->where('check_date', $post['check_date']);
            $this->db->where('inspector', $post['inspector']);
            $this->db->where('visual_process', $post['visual_process']);
            $this->db->where('customer_id', $post['customer_id']);
            $this->db->order_by('created_date', 'DESC');
            $this->db->limit(1);

            $row = $this->db->get('visual_checker')->row();

            echo json_encode([
                "status" => "success",
                "id" => $row ? $row->id : null
            ]);

        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createDetail()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $this->crud->create('visual_checker_detail', $post);

            $this->db->where('visual_checker_id', $post['visual_checker_id']);
            $this->db->where('item_fg_id', $post['item_fg_id']);
            $this->db->order_by('created_date', 'DESC');
            $this->db->limit(1);
            $row = $this->db->get('visual_checker_detail')->row();

            echo json_encode([
                "status" => "success",
                "id" => $row ? $row->id : null
            ]);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createNG()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $this->crud->create('visual_checker_ng', $post);

            echo json_encode([
                "status" => "success"
            ]);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('visual_checker_detail', $data);
        echo $send;
    }

    //DELETE DATA
    public function deleteAll()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('visual_checker', $data);
        echo $send;
    }

    public function print_vc($id, $item_fg_id)
    {
        $id = base64_decode($id);
        $item_fg_id = base64_decode($item_fg_id);

        $this->db->query("UPDATE visual_checker SET printed=1 WHERE `id` = '$id'");
        $visual_checker = $this->crud->read('visual_checker', [], ["id" => $id]);
        $config = $this->db->get('config')->row();

        //Header Print
        $html = '<html><head><title>Checksheet' . $visual_checker->check_date . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="14x14"></head>';
        $html .= '<style>
            body {
                font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI";
                margin: 0;
                padding: 0;
                font-size: 10pt;
            }
            #customers {
                border-collapse: collapse;
                width: 100%;
                margin-bottom: 0;
                font-size: 9pt;
                font-weight: bold;
            }
            #customers td, #customers th {
                border: 0.1mm solid black;
                padding: 2px;
                font-weight: bold;
            }
            @media screen {
                .print {display: none !important;}
            }
            @media print {
                .noprint {display: none !important;}

                @page {
                    margin: 0;
                    padding:0;
                }

                body { 
                    width:100% !important; 
                    height:100%; 
                    font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; 
                    padding: 0; 
                    margin: 0;
                }

                .print { font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI";width:100% !important; height:100%;margin:0; padding-top:5mm;padding-left:8mm}
                
                .page {
                    page-break-after: always;
                    max-width:29cm !important; 
                    max-height: 27cm !important; 
                    margin-left: 0; 
                    margin-right:0; 
                    margin-top: 0;
                    margin-bottom: 0; 
                    padding: 0; 
                    justify-content: center; 
                    box-sizing: border-box;
                }

                .content {
                    width: 100% !important;
                    padding:0;
                    margin:0;
                }
                table {
                    width: 100% !important;
                }
            }

        </style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Margin 0.5 inch, Scale 100%</p>
                </center></div><div class="print">';

        $this->db->select("
            a.id,
            a.check_date,
            a.printed,
            b.id AS detail_id,
            a.visual_process,
            d.name as inspector,
            
            b.item_fg_id,
            c.number AS item_fg_number,
            c.name   AS item_fg_name,

            b.source,
            b.workorder,
            b.compound_lot_no,
            b.check_qty,
            b.ok_qty,
            b.rework_qty,
            b.qty_ng_total AS total_ng_qty,
            b.operator_finishing,

            a.created_by,
            a.created_date,
            a.updated_by,
            a.updated_date,

            opp.trans_date,
            opp.operator,
        ");

        $this->db->from('visual_checker a');
        $this->db->join('visual_checker_detail b', 'b.visual_checker_id = a.id AND b.deleted = 0');
        $this->db->join('item_fg c', 'c.id = b.item_fg_id', 'left');
        $this->db->join('man_powers d', 'd.nik = a.inspector', 'left');

        $this->db->join("(SELECT 
                            op.item_fg_id,
                            op.workorder,
                            op.trans_date,
                            op.shift,
                            op.operator
                        FROM output_production_press op
                        WHERE op.deleted = 0
                        AND op.id = (
                            SELECT op2.id
                            FROM output_production_press op2
                            WHERE op2.item_fg_id = op.item_fg_id
                            AND op2.workorder = op.workorder
                            AND op2.deleted = 0
                            ORDER BY op2.shift DESC, op2.trans_date DESC
                            LIMIT 1
                        )) opp", 
                        "opp.item_fg_id = b.item_fg_id AND opp.workorder = b.workorder", 
                        "left");

        $this->db->order_by('a.check_date', 'asc');
        $this->db->where('a.id', $id);

        $records = $this->db->get()->result_array();

        $detail_ids = array_column($records, 'detail_id');

        $ng_rows = $this->db->select("
                ng.detail_id,
                ng.ng_code,
                m.name AS ng_name,
                ng.qty_ng
            ")
            ->from("visual_checker_ng ng")
            ->join("master_ng m", "m.code = ng.ng_code", "left")
            ->where("ng.deleted", 0)
            ->where_in('ng.detail_id', $detail_ids)
            ->order_by("ng.ng_code")
            ->get()
            ->result_array();

        // BUAT MAP PER DETAIL ID
        $ng_map = [];
        foreach ($ng_rows as $r) {
            $ng_map[$r['detail_id']][] = [
                'name' => $r['ng_name'],
                'qty'  => $r['qty_ng']
            ];
        }

        $html .= '<div class="page">
                    <div class="content">
                        <table style="width:100%; margin-bottom: 5px;">
                            <tr>
                                <th width="10px"><img src="' . $config->favicon . '" width="30px" /></th>
                                <td width="150px" style="padding:10px;">
                                    <b style="font-size:9pt;">' . $config->name . '</b><br>
                                    <span style="font-size:8pt;">' . $config->description . '</span><br>
                                </td>
                                <th width="150px;"><center><h3>LAPORAN HASIL SORTIR/REPAIR/CHECKER</h3></center></th>
                                <th width="150px" style="text-align:right;">
                                    <table style="width:100%; text-align: right; font-size:8pt;font-weight: bold;">
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>

                        <div style="width:100%; min-height:25%; position: relative;">
                            <div>
                                <div style="clear: both; height: 20px;"></div>
                                <table id="customers">
                                    <tr>
                                        <th width="20px">No</th>
                                        <th>Check Date</th>
                                        <th>Visual Process</th>
                                        <th>Inspector</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>WO No</th>
                                        <th>Operator Press</th>
                                        <th>Source</th>
                                        <th>Operator Finishing</th>
                                        <th>Compound Lot No</th>
                                        <th>Check Qty</th>
                                        <th>OK Qty</th>
                                        <th>Rework Qty</th>
                                        <th>Problems</th>
                                        <th>NG Qty</th>
                                    </tr>';

        $no = 1;
        $total_all_ng = 0;

        foreach ($records as $record) {

            $detail_id = $record['detail_id'];

            $list_ng = isset($ng_map[$detail_id]) ? $ng_map[$detail_id] : [];

            $rowspan = max(1, count($list_ng));

            $total_all_ng += (int)$record['total_ng_qty'];

            $html .= '<tr>';

            $html .= '
                <td rowspan="'.$rowspan.'" style="text-align:center">'.$no.'</td>
                <td rowspan="'.$rowspan.'">'.$record['check_date'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['visual_process'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['inspector'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['item_fg_number'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['item_fg_name'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['workorder'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['operator'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['source'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['operator_finishing'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['compound_lot_no'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['check_qty'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['ok_qty'].'</td>
                <td rowspan="'.$rowspan.'">'.$record['rework_qty'].'</td>
            ';

            if (!empty($list_ng)) {
                $first = array_shift($list_ng);
                $html .= '
                    <td>'.$first['name'].'</td>
                    <td style="text-align: right;">'.$first['qty'].'</td>
                ';
            } else {
                $html .= '<td></td><td></td>';
            }

            $html .= '</tr>';

            foreach ($list_ng as $ng) {
                $html .= '
                    <tr>
                        <td>'.$ng['name'].'</td>
                        <td style="text-align: right;">'.$ng['qty'].'</td>
                    </tr>
                ';
            }

            $no++;
        }

        $html .= '
            <tr>
                <td colspan="15" style="text-align:right; font-weight:bold;">Total NG</td>
                <td style="font-weight:bold;text-align:right;">'.$total_all_ng.'</td>
            </tr>
        ';

        $html .= '</table></div></div>
                    </div>';

        $html .= '</div><script>window.print()</script>';
        die($html);
    }


    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=visual_checker_report_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);

        // $filter_delivery_to = @base64_decode($get['filter_delivery_to']);
        // $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select("a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom, COALESCE(c.name, d.name) as incoming_from");
        $this->db->from('visual_checker a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.delivery_from = c.id', 'left');
        $this->db->join('teaching_factory d', 'a.delivery_from = d.id', 'left');

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.incoming_date >=', $filter_from);
            $this->db->where('a.incoming_date <=', $filter_to);
        }

        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }

        // if ($filter_delivery_to != "") {
        //     $this->db->where('a.delivery_to', $filter_delivery_to);
        // }
        // $this->db->like('a.delivery_note_no', $filter_delivery_note_no);

        $this->db->group_by('a.incoming_doc_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('a.incoming_doc_no', 'ASC');
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                margin: 20px;
            }
            #customers {
                border-collapse: collapse;
                width: 100%;
                font-size: 11px;
                margin: 15px 0;
            }
            .table-container {
                margin: 20px;
            }
            #customers td, #customers th {
                border: 1px solid black;
                padding: 4px;
                text-align: left;
                white-space: nowrap;
            }
            #customers th {
                background-color: white;
                color: black;
                font-weight: bold;
                text-align: center;
                border-bottom: 1px solid black;
            }
            #customers tr:hover {
                background-color: #f5f5f5;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .no-wrap {
                white-space: nowrap;
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
            <h3 style="margin:0;">INCOMING FROM SC/TF REPORT</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px;">No</th>
                        <th style="width: 80px;">Incoming Date</th>
                        <th style="width: 120px;">Incoming Doc No</th>
                        <th style="width: 120px;">Delivery Note No</th>
                        <th style="width: 80px;">Delivery Date</th>
                        <th style="width: 80px;">Incoming From</th>
                        <th style="width: 100px;">Product ID</th>
                        <th style="width: 100px;">Product No</th>
                        <th style="width: 150px;">Product Name</th>
                        <th style="width: 80px;">WO No</th>
                        <th style="width: 80px;">Qty Delivery</th>
                        <th style="width: 80px;">Qty Receive</th>
                        <th style="width: 80px;">UOM</th>
                    </tr>';

        $no = 1;
        foreach ($records as $row) {
            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap">'.date('Y-m-d', strtotime($row['incoming_date'])).'</td>
                        <td class="no-wrap">'.$row['incoming_doc_no'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td class="no-wrap">'.$row['incoming_from'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td class="no-wrap">'.$row['workorder'].'</td>
                        <td style="text-align: center;">'.number_format($row['qty_delivery'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_receive'],0,".",".").'</td>
                        <td class="no-wrap" style="text-align: center;">'.$row['uom'].'</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></div>';
        echo $html;
    }
}