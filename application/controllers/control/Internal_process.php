<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Internal_process extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        // $this->load->library('Ciqrcode');
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
            $this->load->view('control/internal_process');
        } else {
            redirect('error_access');
        }
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
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 7, 4) as kode FROM internal_process WHERE `delivery_note_no` like '%$dn_no%'");
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

    public function readDocNo()
    {
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $filter_process_name = $this->input->get("filter_process_name");

        $this->db->distinct();
        $this->db->select('doc_no');
        $this->db->from('internal_process');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('process_date >=', $filter_from);
            $this->db->where('process_date <=', $filter_to);
        }
        if (!empty($filter_process_name)) {
            $this->db->where('process_name', $filter_process_name);
        }

        $this->db->order_by('doc_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }

    public function readWorkorder()
    {
        $send = $this->crud->query("
            SELECT DISTINCT workorder
            FROM output_production_press_detail
            WHERE `status` = 0 
            AND item_fg_id = 'FGRPNA-0207'
            ORDER BY workorder ASC
        ");
        echo json_encode($send);
    }

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $process_date = $this->input->post('process_date');
        $process_date = $process_date ?: date('Y-m-d');
        $today = date('Y-m-d');

        // $period = date('Ym', strtotime($process_date));

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
                $exclude_sql = "AND CONCAT(a.item_fg_id, '_', a.workorder) NOT IN ($in)";
            }
        }


        $query = "
            SELECT
                a.item_fg_id,
                b.number,
                b.name,
                a.workorder,
                (
                    (COALESCE(SUM(a.qty_ok), 0) 
                    - COALESCE(d.ok_press_total, 0)
                    - COALESCE(del.qty_delivery_total, 0))
                ) AS ok_press,
                MIN(a.wp) AS wp,
                b.uom
            FROM output_production_press a
            JOIN item_fg b ON a.item_fg_id = b.id

            LEFT JOIN (
                SELECT 
                    dt.item_fg_id,
                    dt.workorder,
                    COALESCE(dt.internal, 0) + COALESCE(dt.external, 0) AS ok_press_total
                FROM internal_process dt
                WHERE dt.deleted = 0
                GROUP BY dt.item_fg_id, dt.workorder
            ) d ON d.item_fg_id = a.item_fg_id AND d.workorder = a.workorder

            LEFT JOIN (
                SELECT 
                    ds.item_fg_id,
                    ds.workorder,
                    SUM(ds.qty_delivery) AS qty_delivery_total
                FROM delivery_to_subconts ds
                WHERE ds.deleted = 0
                GROUP BY ds.item_fg_id, ds.workorder
            ) del ON del.item_fg_id = a.item_fg_id AND del.workorder = a.workorder

            WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
            AND a.trans_date BETWEEN '2025-11-18' AND '$today'
            AND b.id != 'FGRPNA-0207'

            $exclude_sql

            GROUP BY a.item_fg_id, a.workorder
            HAVING ok_press > 0
            ORDER BY a.workorder ASC, b.number ASC
        ";

        // WHERE a.period = '$period'

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function readItemFgCP()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        // $process_date = $this->input->post('process_date');
        // $process_date = $process_date ?: date('Y-m-d');
        // $today = date('Y-m-d');
        $workorder = $this->input->post('workorder');

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
                $exclude_sql = "AND CONCAT(a.item_fg_id, '_', a.workorder, '_', c.workorder_label) NOT IN ($in)";
            }
        }

        $query = "
            SELECT  
                a.item_fg_id,  
                b.number,  
                b.name,  
                a.workorder,
                c.workorder_label,
                c.qty_packing ok_press,
                MIN(a.workorder) AS workorder,
                CONCAT(c.id, '_', c.workorder, '_', a.item_fg_id, '_', c.workorder_label) AS unique_key,
                b.uom
            FROM output_production_press a  
            JOIN item_fg b ON a.item_fg_id = b.id  
            JOIN output_production_press_detail c ON a.item_fg_id = c.item_fg_id AND a.workorder = c.workorder

            WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%' OR c.workorder_label LIKE '%$post%')
            -- AND a.trans_date >= '2025-10-01'
            AND b.id = 'FGRPNA-0207'
            AND c.status = 0
            AND c.workorder = '$workorder'

            $exclude_sql

            GROUP BY a.item_fg_id, a.workorder, c.workorder_label
            ORDER BY c.workorder_label ASC
        ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function doc_no_internal()
    {
        $process_date = $this->input->post('process_date');

        $date = $process_date ? date("Y-m-d", strtotime($process_date)) : date("Y-m-d");
        $year = date("y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $prefix = "INF/{$year}{$month}{$day}/";

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(doc_no, '/', -1) AS UNSIGNED)) AS kode
            FROM internal_process
            WHERE doc_no LIKE 'INF/{$year}{$month}%'
        ");
        $row = $sql->row();

        if ($row && $row->kode) {
            $seq = sprintf('%03d', $row->kode + 1);
        } else {
            $seq = '001';
        }

        $autonumber = "{$prefix}{$seq}";

        echo $autonumber;
    }


    public function doc_no_cp()
    {
        $process_date = $this->input->post('process_date');

        $date = $process_date ? date("Y-m-d", strtotime($process_date)) : date("Y-m-d");
        $year = date("y", strtotime($date));
        $month = date("m", strtotime($date));
        $day = date("d", strtotime($date));

        $prefix = "CP/{$year}{$month}{$day}/";

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(doc_no, '/', -1) AS UNSIGNED)) AS kode
            FROM internal_process
            WHERE doc_no LIKE 'CP/{$year}{$month}%'
        ");
        $row = $sql->row();

        if ($row && $row->kode) {
            $seq = sprintf("%03d", $row->kode + 1);
        } else {
            $seq = "001";
        }

        $autonumber = "{$prefix}{$seq}";

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
            $filter_doc_no = @base64_decode($get['filter_doc_no']);
            $filter_process_name = @base64_decode($get['filter_process_name']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->from('internal_process a');

            if($filter_process_name == "") {
                $this->db->select("a.*");
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->where('a.process_name', 'Cutting Punch');
    
                if ($filter_from != "" && $filter_to != "") {
                    $this->db->where('a.process_date >=', $filter_from);
                    $this->db->where('a.process_date <=', $filter_to);
                }

                // if (!empty($filter_process_name)) {
                //     $this->db->where('a.process_name', $filter_process_name);
                // }

                if (!empty($filter_item_fg)) {
                    $this->db->where('a.item_fg_id', $filter_item_fg);
                }
                $this->db->like('a.doc_no', $filter_doc_no);
                $this->db->group_by('a.doc_no');
                $this->db->order_by('a.doc_no', 'ASC');

            } else {
                $this->db->select("a.*");
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    
                if ($filter_from != "" && $filter_to != "") {
                    $this->db->where('a.process_date >=', $filter_from);
                    $this->db->where('a.process_date <=', $filter_to);
                }

                if (!empty($filter_process_name)) {
                    $this->db->where('a.process_name', $filter_process_name);
                }
                if (!empty($filter_item_fg)) {
                    $this->db->where('a.item_fg_id', $filter_item_fg);
                }
                $this->db->like('a.doc_no', $filter_doc_no);
                $this->db->group_by('a.doc_no');
                $this->db->order_by('a.doc_no', 'ASC');
            }


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
            $doc_no = base64_decode($this->input->get('doc_no'));
            $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

            $this->db->select("
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,
                a.workorder,
                a.workorder_label,
                a.ok_press,
                a.internal,
                a.external,
                a.punch_process,
                a.ok_punch,
                a.ng_punch,
                a.os_cutting_punch
            ");

            $this->db->from('internal_process a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.doc_no', $doc_no);

            if ($filter_item_fg != "") {
                $this->db->where('b.id', $filter_item_fg);
            }

            $this->db->group_by([
                'a.item_fg_id',
                'a.workorder_label',
            ]);

            $this->db->order_by('a.workorder_label');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function datatableInternalDetails()
    {
        if ($this->input->get()) {
            $doc_no = base64_decode($this->input->get('doc_no'));
            $filter_item_fg = base64_decode($this->input->get('filter_item_fg'));

            $this->db->select("
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,
                a.workorder,
                a.ok_press,
                a.internal,
                a.external
            ");

            $this->db->from('internal_process a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.doc_no', $doc_no);

            if ($filter_item_fg != "") {
                $this->db->where('b.id', $filter_item_fg);
            }

            $this->db->group_by([
                'a.item_fg_id',
                'a.workorder',
            ]);

            $this->db->order_by('a.workorder');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $doc_no = base64_decode($this->input->get('doc_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('internal_process a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.doc_no', $doc_no);
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
                "process_date" => $post['process_date'],
                "process_name" => $post['process_name'],
                "doc_no" => $post['doc_no'],
                "workorder" => $post['workorder'],
                "ok_press" => $post['ok_press'],
                "internal" => $post['internal'],
                "external" => $post['external'],
            );

            if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                $post['workorder'] = null;
            }

            if (@$post['id'] != "") {
                $send = $this->crud->update('internal_process', ["id" => $post['id']], $dataFinal);
            } else {

                $send = $this->crud->create('internal_process', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create2()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $mp_punch = isset($post['mp_punch']) ? strtoupper($post['mp_punch']) : null;

            $post['mp_punch'] = $mp_punch;

            $dataFinal = array(
                "item_fg_id" => $post['item_fg_id'],
                "process_date" => $post['process_date'],
                "process_name" => $post['process_name'],
                "doc_no" => $post['doc_no'],
                "workorder" => $post['workorder'],
                "workorder_label" => $post['workorder_label'],
                "ok_press" => $post['ok_press'],
                "punch_process" => $post['punch_process'],
                "ok_punch" => $post['ok_punch'],
                "ng_punch" => $post['ng_punch'],
                "os_cutting_punch" => $post['os_cutting_punch'],
                "mp_punch" => $mp_punch,
            );

            if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                $post['workorder'] = null;
            }

            if (@$post['id'] != "") {
                $send = $this->crud->update('internal_process', ["id" => $post['id']], $dataFinal);
            } else {

                $send = $this->crud->create('internal_process', $post);
            }

            $send = $this->crud->update('output_production_press_detail', ["workorder_label" => $post['workorder_label']], ["status" => 2]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }


    //DELETE DATA
    // public function delete()
    // {
    //     $data = $this->input->post();
    //     $send = $this->crud->delete('internal_process', $data);
    //     echo $send;
    // }

    // DELETE DATA
    public function delete()
    {
        $data = $this->input->post();

        $internal_doc_no     = $data['internal_doc_no'];
        $item_fg_id = $data['item_fg_id'];
        $workorder  = $data['workorder'];
        $workorder_label  = $data['workorder_label'];

        if (!isset($workorder_label) || $workorder_label === '' || $workorder_label === 'null') {
            $workorder_label = null;
        }

        $this->db->where('internal_doc_no', $internal_doc_no);
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('workorder', $workorder);
        $used_count = $this->db->count_all_results('delivery_to_subconts');

        if ($used_count > 0) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Cannot delete data that is still in use'
            ]);
            return;
        }

        if($workorder_label == "" || $workorder_label == null) {
            $send = $this->crud->delete('internal_process', [
                'doc_no'           => $internal_doc_no,
                'item_fg_id'       => $item_fg_id,
                'workorder'        => $workorder
            ]);
        } else {
            $send = $this->crud->delete('internal_process', [
                'doc_no'           => $internal_doc_no,
                'item_fg_id'       => $item_fg_id,
                'workorder'        => $workorder,
                'workorder_label'  => $workorder_label
            ]);
        }

        $this->crud->update('output_production_press_detail', ["workorder_label" => $workorder_label], ["status" => 0]);

        echo $send;
    }


    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=internal_process_report_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_process_name = @base64_decode($get['filter_process_name']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_doc_no = @base64_decode($get['filter_doc_no']);

        if($filter_from == "" || $filter_to == "") {
            $filter_from = date("Y-m-01");
            $filter_to = date("Y-m-t");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if($filter_process_name == "") {

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('internal_process a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.process_name', 'Cutting Punch');
            
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.process_date >=', $filter_from);
                $this->db->where('a.process_date <=', $filter_to);
            }

            // if ($filter_process_name != "") {
            //     $this->db->where('a.process_name', $filter_process_name);
            // }

            if ($filter_doc_no != "") {
                $this->db->where('a.doc_no', $filter_doc_no);
            }
            if ($filter_item_fg != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }

            $this->db->group_by('a.doc_no');
            $this->db->order_by('a.doc_no', 'ASC');
            $records = $this->db->get()->result_array();
        } else {

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('internal_process a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.process_date >=', $filter_from);
                $this->db->where('a.process_date <=', $filter_to);
            }
            if ($filter_process_name != "") {
                $this->db->where('a.process_name', $filter_process_name);
            }
            if ($filter_doc_no != "") {
                $this->db->where('a.doc_no', $filter_doc_no);
            }
            if ($filter_item_fg != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }

            $this->db->group_by('a.doc_no');
            $this->db->order_by('a.doc_no', 'ASC');
            $records = $this->db->get()->result_array();
        }

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
            <h3 style="margin:0;">INTERNAL PROCESS REPORT</h3>
            <small>Process Date : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>';

        if($filter_process_name == "Internal Finishing") {

            $html .= '
                <div class="table-container">
                <table id="customers" border="1">
                        <tr>
                            <th style="width: 10px;">No</th>
                            <th style="width: 120px;">Process Name</th>
                            <th style="width: 80px;">Process Date</th>
                            <th style="width: 120px;">Document No</th>
                            <th style="width: 100px;">Product No</th>
                            <th style="width: 150px;">Product Name</th>
                            <th style="width: 80px;">WO No</th>
                            <th style="width: 80px;">OK Press</th>
                            <th style="width: 80px;">Internal</th>
                            <th style="width: 80px;">To External</th>
                        </tr>';

            $no = 1;
            foreach ($records as $row) {
                $html .= '<tr>
                            <td class="text-center">'.$no.'</td>
                            <td class="no-wrap">'.$row['process_name'].'</td>
                            <td class="no-wrap">'.date('Y-m-d', strtotime($row['process_date'])).'</td>
                            <td class="no-wrap">'.$row['doc_no'].'</td>
                            <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                            <td class="no-wrap">'.$row['item_fg_name'].'</td>
                            <td class="no-wrap">'.$row['workorder'].'</td>
                            <td style="text-align: center;">'.number_format($row['ok_press'],0,".",".").'</td>
                            <td style="text-align: center;">'.number_format($row['internal'],0,".",".").'</td>
                            <td style="text-align: center;">'.number_format($row['external'],0,".",".").'</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></div>';
            echo $html;

        } else {

            $html .= '
                <div class="table-container">
                <table id="customers" border="1">
                        <tr>
                            <th style="width: 10px;">No</th>
                            <th style="width: 120px;">Process Name</th>
                            <th style="width: 80px;">Process Date</th>
                            <th style="width: 120px;">Document No</th>
                            <th style="width: 100px;">Product No</th>
                            <th style="width: 150px;">Product Name</th>
                            <th style="width: 80px;">WO No</th>
                            <th style="width: 80px;">OK Press</th>
                            <th style="width: 80px;">Punch Process</th>
                            <th style="width: 80px;">OK Punch</th>
                            <th style="width: 80px;">NG Punch</th>
                            <th style="width: 80px;">OS Cutting Punch</th>
                        </tr>';

            $no = 1;
            foreach ($records as $row) {
                $html .= '<tr>
                            <td class="text-center">'.$no.'</td>
                            <td class="no-wrap">'.$row['process_name'].'</td>
                            <td class="no-wrap">'.date('Y-m-d', strtotime($row['process_date'])).'</td>
                            <td class="no-wrap">'.$row['doc_no'].'</td>
                            <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                            <td class="no-wrap">'.$row['item_fg_name'].'</td>
                            <td class="no-wrap">'.$row['workorder'].'</td>
                            <td style="text-align: center;">'.number_format($row['ok_press'],0,".",".").'</td>
                            <td style="text-align: center;">'.number_format($row['punch_process'],0,".",".").'</td>
                            <td style="text-align: center;">'.number_format($row['ok_punch'],0,".",".").'</td>
                            <td style="text-align: center;">'.number_format($row['ng_punch'],0,".",".").'</td>
                            <td style="text-align: center;">'.number_format($row['os_cutting_punch'],0,".",".").'</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></div>';
            echo $html;
        }
    }
}
