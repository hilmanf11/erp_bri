<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Incoming_from_sc_tf extends CI_Controller
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

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/incoming_from_sc_tf');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readDeliveryNoteNoSCTF()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $source_id = $this->input->post('source_id');
        
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
            AND a.destination = '{$source_id}'
        ";

        $send = $this->crud->query($query);

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
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 7, 4) as kode FROM incoming_from_sc_tf WHERE `delivery_note_no` like '%$dn_no%'");
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
        $this->db->from('incoming_from_sc_tf');
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
    //     $incoming_date = $this->input->post('incoming_date');
    //     $incoming_date = $incoming_date ?: date('Y-m-d');
    //     $source_id = $this->input->post('source_id');
    //     $delivery_note_no = $this->input->post('delivery_note_no');

    //     // $period = date('Ym', strtotime($incoming_date));

    //     $query = "
    //         SELECT
    //             a.item_fg_id,
    //             b.number,
    //             b.name,
    //             a.workorder,
    //             COALESCE(SUM(a.qty_delivery), 0) AS total_qty_delivery,
    //             COALESCE(d.qty_receive_total, 0) AS total_qty_receive,
    //             (COALESCE(SUM(a.qty_delivery), 0) - COALESCE(d.qty_receive_total, 0)) AS qty_delivery,
    //             MIN(a.delivery_date) as delivery_date,
    //             b.uom
    //         FROM delivery_to_subconts a
    //         JOIN item_fg b ON a.item_fg_id = b.id

    //         LEFT JOIN (
    //             SELECT 
    //                 istf.item_fg_id,
    //                 istf.workorder,
    //                 SUM(istf.qty_receive) AS qty_receive_total
    //             FROM incoming_from_sc_tf istf
    //             WHERE istf.deleted = 0
    //             AND istf.delivery_note_no = '$delivery_note_no'
    //             GROUP BY istf.item_fg_id, istf.workorder
    //         ) d ON d.item_fg_id = a.item_fg_id AND d.workorder = a.workorder

    //         WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
    //         AND a.delivery_note_no = '$delivery_note_no'
    //         GROUP BY a.item_fg_id, a.workorder, b.number, b.name, b.uom, d.qty_receive_total
    //         HAVING (COALESCE(SUM(a.qty_delivery), 0) - COALESCE(d.qty_receive_total, 0)) > 0
    //         ORDER BY a.workorder ASC, b.number ASC
    //     ";

    //     // WHERE a.period = '$period'

    //     $send = $this->crud->query($query);
    //     echo json_encode($send);
    // }

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $incoming_date = $this->input->post('incoming_date');
        $incoming_date = $incoming_date ?: date('Y-m-d');

        $source_id = $this->input->post('source_id');
        $delivery_note_no = $this->input->post('delivery_note_no');

        $filterDeliveryNote = "";
        $filterDestination  = "";

        if (strpos($source_id, "TF") === 0) {
            $filterDeliveryNote = "AND a.delivery_note_no = '$delivery_note_no'";
            $filterDestination  = "AND a.destination = '$source_id'";

        } elseif (strpos($source_id, "S") === 0) {
            $dateFrom = date('Y-m-d', strtotime($incoming_date . ' -7 days'));
            $dateTo   = $incoming_date;

            $filterDeliveryNote = "AND a.delivery_date BETWEEN '$dateFrom' AND '$dateTo'";
            $filterDestination  = "AND a.destination = '$source_id'";

        } else {
            echo json_encode([]);
            return;
        }

        $exclude_keys = $this->input->post('exclude_keys');
        $exclude_sql = '';

        if (!empty($exclude_keys)) {
            $exclude_arr = explode(',', $exclude_keys);
            $exclude_arr = array_filter($exclude_arr);
            if (!empty($exclude_arr)) {
                $escaped = array_map(function ($v) {
                    return $this->db->escape_str($v);
                }, $exclude_arr);
                $in = "'" . implode("','", $escaped) . "'";
                $exclude_sql = "AND CONCAT(a.item_fg_id, '_', a.workorder) NOT IN ($in)";
            }
        }

        $isTF = strpos($source_id, 'TF') === 0;
        $isS  = strpos($source_id, 'S') === 0;

        $incomingFilter = "";

        if ($isTF) {
            $incomingFilter = "AND istf.delivery_note_no = '$delivery_note_no'";
        }

        if ($isS) {
            $incomingFilter = "
                AND istf.delivery_from = '$source_id'
                AND istf.incoming_date BETWEEN '$dateFrom' AND '$dateTo'
            ";
        }


        $query = "
            SELECT
                a.item_fg_id,
                b.number,
                b.name,
                a.workorder,
                COALESCE(SUM(a.qty_delivery), 0) AS total_qty_delivery,
                COALESCE(d.qty_receive_total, 0) AS total_qty_receive,
                (COALESCE(SUM(a.qty_delivery), 0) - COALESCE(d.qty_receive_total, 0)) AS qty_delivery,
                MIN(a.delivery_date) as delivery_date,
                b.uom
            FROM delivery_to_subconts a
            JOIN item_fg b ON a.item_fg_id = b.id

            LEFT JOIN (
                SELECT 
                    istf.item_fg_id,
                    istf.workorder,
                    SUM(istf.qty_receive) AS qty_receive_total
                FROM incoming_from_sc_tf istf
                WHERE istf.deleted = 0
                $incomingFilter
                GROUP BY istf.item_fg_id, istf.workorder
            ) d ON d.item_fg_id = a.item_fg_id AND d.workorder = a.workorder

            WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
            $filterDestination
            $filterDeliveryNote
            $exclude_sql
            GROUP BY a.item_fg_id, a.workorder, b.number, b.name, b.uom, d.qty_receive_total
            HAVING (COALESCE(SUM(a.qty_delivery), 0) - COALESCE(d.qty_receive_total, 0)) > 0
            ORDER BY a.workorder ASC, b.number ASC
        ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function incoming_doc_no($type = "")
    {
        $incoming_date = $this->input->post('incoming_date');
        $destination_code = $this->input->post('destination_code');

        $date = $incoming_date ? date("Y-m-d", strtotime($incoming_date)) : date("Y-m-d");
        $month = date("m", strtotime($date));
        $year = date("y", strtotime($date));
        $day = date("d", strtotime($date));

        // Tentukan periode reset berdasarkan tanggal 16
        if ($day < 16) {
            $period_start = date("Y-m-16", strtotime("-1 month", strtotime($date)));
            $period_end   = date("Y-m-15", strtotime($date));
        } else {
            $period_start = date("Y-m-16", strtotime($date));
            $period_end   = date("Y-m-15", strtotime("+1 month", strtotime($date)));
        }

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING(incoming_doc_no, 2, 3) AS UNSIGNED)) AS kode
            FROM incoming_from_sc_tf
            WHERE incoming_doc_no LIKE 'R%/{$destination_code}/{$month}/{$year}'
            AND incoming_date BETWEEN '{$period_start}' AND '{$period_end}'
        ");


        $row = $sql->row();

        if ($row->kode == null) {
            $seq = "001";
        } else {
            $seq = sprintf("%03s", intval($row->kode) + 1);
        }

        $autonumber = "R{$seq}/{$destination_code}/{$month}/{$year}";

        if ($type == "return") {
            return $autonumber;
        }

        echo $autonumber;
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();

            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);

            $filter_incoming_doc_no = @base64_decode($get['filter_incoming_doc_no']);
            $filter_delivery_from = @base64_decode($get['filter_delivery_from']);
            $filter_subcont_id = @base64_decode($get['filter_subcont_id']);
            $filter_teaching_factory_id = @base64_decode($get['filter_teaching_factory_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select("
                a.*, 
                COALESCE(c.name, d.name) as incoming_from,    
                CASE 
                    WHEN a.delivery_from LIKE '%S%' THEN 'Subcont'
                    ELSE 'Teaching Factory'
                END AS delivery_from_text
            ");
            $this->db->from('incoming_from_sc_tf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.delivery_from = c.id', 'left');
            $this->db->join('teaching_factory d', 'a.delivery_from = d.id', 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.incoming_date >=', $filter_from);
                $this->db->where('a.incoming_date <=', $filter_to);
            }
            if ($filter_incoming_doc_no != "") {
                $this->db->where('a.incoming_doc_no', $filter_incoming_doc_no);
            }
            if ($filter_item_fg != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            if ($filter_delivery_from == "SUBCONT") {
                $this->db->like('LOWER(a.delivery_from)', 's', 'after', false);
            }
            if ($filter_delivery_from == "TEFA") {
                $this->db->like('LOWER(a.delivery_from)', 'tf', 'after', false);
            }
            if ($filter_subcont_id != "") {
                $this->db->like('a.delivery_from', $filter_subcont_id);
            }
            if ($filter_teaching_factory_id != "") {
                $this->db->like('a.delivery_from', $filter_teaching_factory_id);
            }
            // $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            $this->db->group_by('a.incoming_doc_no');
            $this->db->order_by('a.incoming_doc_no', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('a.id', 'ASC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails()
    {
        if ($this->input->get()) {
            $incoming_doc_no = base64_decode($this->input->get('incoming_doc_no'));
            // $item_fg = base64_decode($this->input->get('item_fg'));

            $this->db->select("
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,
                a.workorder,
                a.qty_delivery,
                a.qty_receive,
                b.uom
            ");

            $this->db->from('incoming_from_sc_tf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.incoming_doc_no', $incoming_doc_no);

            // if ($item_fg != "") {
            //     $this->db->where('b.id', $item_fg);
            // }

            // $this->db->group_by([
            //     'a.incoming_doc_no',
            // ]);

            $this->db->group_by('a.incoming_doc_no');
            $this->db->group_by('a.item_fg_id');
            $this->db->order_by('a.workorder');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
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
            $this->db->from('incoming_from_sc_tf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.incoming_doc_no', $incoming_doc_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $dataFinal = array(
                "item_fg_id" => $post['item_fg_id'],
                "workorder" => $post['workorder'],
                "qty_delivery" => $post['qty_delivery'],
                "qty_receive" => $post['qty_receive'],
            );

            if (!isset($post['delivery_note_no']) || $post['delivery_note_no'] === '' || $post['delivery_note_no'] === 'null') {
                $post['delivery_note_no'] = null;
            }

            if (@$post['id'] != "") {
                $send = $this->crud->update('incoming_from_sc_tf', ["id" => $post['id']], $dataFinal);
            } else {
                $send = $this->crud->create('incoming_from_sc_tf', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('incoming_from_sc_tf', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=incoming_from_sc_tf_report_$format.xls");
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
        $this->db->from('incoming_from_sc_tf a');
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
