<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_delivery_schedules extends CI_Controller
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
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        // $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/report_delivery_schedules');
        } else {
            redirect('error_access');
        }
    }

    // public function readCustomerOrder()
    // {
    //     $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
    //     $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
    //     $customer_id = $this->input->get("customer_id");

    //     // $customer_orders = $this->crud->query("SELECT customer_order_no, sales_order_no
    //     //     FROM sales_orders
    //     //     WHERE sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
    //     //     AND customer_id = '$customer_id'
    //     //     GROUP BY sales_order_no
    //     //     ORDER BY sales_order_no ASC");
    //     $customer_orders = $this->crud->query("SELECT a.customer_id, 
    //     b.customer_order_no, 
    //     b.sales_order_no, 
    //     b.sales_order_date, 
    //     a.trans_date
    //     FROM sales_order_deliveries a
    //     JOIN sales_orders b 
    //     ON a.customer_id = b.customer_id 
    //     AND a.sales_order_no = b.sales_order_no
    //     WHERE a.customer_id = '$customer_id'
        
    //     AND (
    //     b.sales_order_date BETWEEN '$filter_so_date_from' and '$filter_so_date_to'
    //     OR a.trans_date BETWEEN '$filter_so_date_from' and '$filter_so_date_to'
        
    //     ) GROUP BY a.sales_order_no  ORDER BY a.sales_order_no ASC");
    //     echo json_encode($customer_orders);
    // }


    public function readCustomerOrder()
    {
        $filter_type = base64_decode($this->input->get("filter_type"));
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $customer_id = $this->input->get("customer_id");

        $where_date = '';
        if($filter_type != "WITHOUT_SCHEDULE") {
            if (!empty($filter_so_date_from) && !empty($filter_so_date_to)) {
                $where_date = "AND (
                b.sales_order_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to'
                OR a.trans_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to'
            )";
            }
        
        }

        // $customer_orders = $this->crud->query("SELECT customer_order_no, sales_order_no
        //     FROM sales_orders
        //     WHERE sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
        //     AND customer_id = '$customer_id'
        //     GROUP BY sales_order_no
        //     ORDER BY sales_order_no ASC");
        $customer_orders = $this->crud->query("SELECT a.customer_id,
        b.customer_order_no, 
        b.sales_order_no, 
        b.sales_order_date, 
        a.trans_date
        FROM sales_order_deliveries a
        JOIN sales_orders b 
        ON a.customer_id = b.customer_id 
        AND a.sales_order_no = b.sales_order_no
        WHERE a.customer_id = '$customer_id'
        $where_date
        GROUP BY a.sales_order_no 
        ORDER BY a.sales_order_no ASC");
        echo json_encode($customer_orders);
    }

    public function readItems()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        //$filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT b.id, b.number, b.name
            FROM sales_orders a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND a.customer_id = '$customer_id'
            GROUP BY a.item_fg_id");
        echo json_encode($customer_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_delivery_schedule$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_customer_name = base64_decode($this->input->get("filter_customer_name"));
        $filter_customer_order_no = base64_decode($this->input->get("filter_customer_order_no"));
        //$filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $filter_item_fg = base64_decode($this->input->get("filter_item_fg"));
        $filter_item_fg_name = base64_decode($this->input->get("filter_item_fg_name"));
        $filter_display = base64_decode($this->input->get("filter_display"));
        $filter_status = base64_decode($this->input->get("filter_status"));

        $customer = $this->crud->read("customers", [], ["id" => $filter_customer_name]);
        $customer_name = empty($filter_customer_name)?"ALL":@$customer->name;
        $customer_order_no = empty($filter_customer_order_no)?"ALL":$filter_customer_order_no;
        $item_fg = empty($filter_item_fg)?"ALL":$filter_item_fg;
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $schedule_display = $filter_so_date_from . ' To ' . $filter_so_date_to;
        $periode_display = $filter_type == "WITHOUT_SCHEDULE" ? "ALL" : $schedule_display;

        $overflow = $filter_display === "RECAP" ? '':'overflow-y: hidden;';

        $html = '<html>
                 <head>
                    <title>Print Data</title>
                </head>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }
                    #customers tr:hover {
                        background-color: #ddd;
                    }

                    .table-container {
                        overflow: auto;
                    }
                    table#customers {
                        border-collapse: separate;
                        border-spacing: 0;
                        width: max-content;
                        font-size: 12px;
                    }
                    table#customers {
                        border: none;
                    }
                    table#customers th, table#customers td {
                        border: 1px solid #ddd;
                        border-right: none;
                        border-bottom: none;
                        text-align: center;
                    }
                    table#customers th:last-child, table#customers td:last-child {
                        border-right: 1px solid #ddd;
                    }
                    table#customers tr:last-child td {
                        border-bottom: 1px solid #ddd;
                    }
                    
                    .sticky-col-0 { position: sticky; left: 0px; background: #f2f2f2; z-index: 5; }
                    .sticky-col-1 { position: sticky; left: 40px; background: #f2f2f2; z-index: 5; }
                    .sticky-col-2 { position: sticky; left: 290px; background: #f2f2f2; z-index: 5; }
                    .sticky-col-3 { position: sticky; left: 390px; background: #f2f2f2; z-index: 5; }
                    .sticky-col-4 { position: sticky; left: 490px; background: #f2f2f2; z-index: 5; }
                    .sticky-col-5 { position: sticky; left: 640px; background: #f2f2f2; z-index: 5; }
                    .sticky-col-6 { position: sticky; left: 690px; background: #f2f2f2; z-index: 5; }

                    .header {
                        position: sticky;
                        top: 0;
                        background-color: white;
                        z-index: 10;
                        padding-bottom: 20px !important;
                    }
                    #customers thead th {
                        position: sticky;
                        top: 0;
                        z-index: 100;
                    }
                    #customers thead tr:nth-child(1) th {
                        position: sticky;
                        top: 0px;
                        z-index: 30;
                    }
                    #customers thead tr:nth-child(2) th {
                        position: sticky;
                        top: 0px;
                        z-index: 20;
                        background: #f2f2f2;
                    }
                    #customers thead tr:nth-child(3) th {
                        position: sticky;
                        top: 15px;
                        z-index: 10;
                        background: #f2f2f2;
                        border-top: 3px solid #ddd;
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
                        <h3>' . $filter_display . ' REPORT DELIVERY SCHEDULES</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $periode_display . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Name</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $customer_name . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $customer_order_no . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Product No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $item_fg . '</td>
                    </tr>
                </table>
                </div>';

        if ($filter_display == "RECAP") {
            $this->db->select('
                a.trans_date, 
                a.customer_id,
                b.number as customer_number, 
                b.name as customer_name, 
                c.customer_order_no, 
                a.item_fg_id, 
                d.name as item_fg_name, 
                d.number as item_fg_number, 
                d.uom as item_fg_uom, 
                a.sales_order_no,
                COALESCE(SUM(a.qty), 0) as qty_plan, 
                COALESCE(dn.qty_delivered, 0) as qty_del,
                SUM(c.qty) as qty_order, 
                SUM(c.delivery) as qty_delivery, 
                SUM(c.outstanding) as qty_outstanding, 
                c.closing_reason, 
                c.type_closing
            ');

            $this->db->from('sales_order_deliveries a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.customer_id = c.customer_id and a.item_fg_id = c.item_fg_id');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            // $this->db->join('delivery_notes e', 'a.sales_order_no = e.sales_order_no and a.item_fg_id = e.item_fg_id and a.customer_id = e.customer_id and a.trans_date = e.delivery_note_date', 'left');
            $this->db->join('(SELECT sales_order_no, customer_id, item_fg_id, SUM(qty) AS qty_delivered FROM delivery_notes GROUP BY sales_order_no, customer_id, item_fg_id) dn', 
            'a.sales_order_no = dn.sales_order_no AND a.customer_id = dn.customer_id AND a.item_fg_id = dn.item_fg_id', 
            'left');

            if($filter_type != "WITHOUT_SCHEDULE") {
                $this->db->where("a.trans_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to'");
            }

            $this->db->like('a.customer_id', $filter_customer_name);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('c.customer_order_no', $filter_customer_order_no);
            
            // $this->db->group_by('a.trans_date');
            $this->db->group_by('a.customer_id');
            $this->db->group_by('a.customer_order_no');
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.sales_order_no');

            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('c.customer_order_no', 'ASC');
            $this->db->order_by('d.number', 'ASC');

            $records = $this->db->get()->result_array();

            if (!empty($filter_status)) {
                $records = array_filter($records, function ($data) use ($filter_status) {
                    $calculated_status = '';

                    if($data['qty_outstanding'] != 0 && ($data['closing_reason'] != '' || $data['type_closing'] != '') ){
                        $calculated_status = 'CLOSE';
                    } else if ($data['qty_del'] < $data['qty_plan'] && $data['qty_del'] > 0) {
                        $calculated_status = 'ON GOING';
                    } else if ($data['qty_del'] == $data['qty_plan']) {
                        $calculated_status = 'CLOSE';
                    } else {
                        $calculated_status = 'OPEN';
                    }

                    return strtoupper($filter_status) === $calculated_status;
                });
            }

            $html .= '<table id="customers" style="width: 100%; padding: 0 18px;" border="1">
                        <thead style="position: sticky; z-index: 100; top: 198px; background: #f2f2f2;">
                        <tr>
                            <th width="20">No</th>
                            <th>Customer Name</th>
                            <th>Sales Order No.</th>
                            <th>Customer Order No.</th>
                            <th>Product ID</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>UoM</th>
                            <th>Plan</th>
                            <th>Actual</th>
                            <th>Ost</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        ';

            $no = 1;

            foreach ($records as $data) {
                
                // $status = ($data['qty_plan'] == $data['qty_del']) ? 'CLOSE' : 'OPEN';
                // $color = ($status == 'CLOSE') ? 'red' : 'green';

                if($data['qty_outstanding'] != 0 && ($data['closing_reason'] != '' || $data['type_closing'] != '') ){
                    $status = 'CLOSE';
                    $color = 'red';
                } else if ($data['qty_del'] < $data['qty_plan'] && $data['qty_del'] > 0) {
                    $status = 'ON GOING';
                    $color = '#FF9B17';
                } elseif ($data['qty_del'] == $data['qty_plan']) {
                    $status = 'CLOSE';
                    $color = 'red';
                } else {
                    $status = 'OPEN';
                    $color = 'green';
                }


                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['sales_order_no'] . '</td>
                            <td align="center">' . $data['customer_order_no'] . '</td>
                            <td>' . $data['item_fg_id'] . '</td>
                            <td>' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['item_fg_uom'] . '</td>
                            <td align="center">' . number_format($data['qty_plan'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format($data['qty_del'], 0, '.', '.') . '</td>
                            <td align="center">' . number_format(($data['qty_plan'] - $data['qty_del']), 0, '.', '.') . '</td>
                            <td align="center" style="color:' . $color . '; font-weight:bold;">' . $status . '</td>
                        </tr>';
                $no++;
            }
        } else {

            if($filter_type == "WITHOUT_SCHEDULE") {
                $this->db->select_min('trans_date', 'min_date');
                $this->db->select_max('trans_date', 'max_date');
                $this->db->from('sales_order_deliveries');
                
                if ($filter_customer_order_no != "") {
                    $this->db->where('customer_order_no', $filter_customer_order_no);
                }
                if ($filter_customer_name != "") {
                    $this->db->where('customer_id', $filter_customer_name);
                }
                $dates = $this->db->get()->row_array();

                $filter_so_date_from = date('Y-m-01', strtotime($dates['min_date']));
                $filter_so_date_to = date('Y-m-t', strtotime($dates['max_date']));                
            }

            $this->db->select('
                a.sales_order_no, 
                a.trans_date, 
                a.qty, 
                a.customer_id, 
                b.number as customer_number, 
                b.name as customer_name, 
                c.customer_order_no, 
                d.id as item_fg_id, 
                d.name as item_fg_name, 
                d.number as item_fg_number, 
                d.uom as item_fg_uom
            ');
            $this->db->from('sales_order_deliveries a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('sales_orders c', 'a.customer_id = c.customer_id and a.sales_order_no = c.sales_order_no and a.customer_order_no = c.customer_order_no');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');

            if($filter_type != "WITHOUT_SCHEDULE") {
                $this->db->where("a.trans_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to'");
            }

            if($filter_customer_name!=""){
                $this->db->where('a.customer_id', $filter_customer_name);
            }
            if($filter_item_fg!=""){
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            if($filter_customer_order_no!=""){
                $this->db->where('c.customer_order_no', $filter_customer_order_no);
            }
            $this->db->group_by('a.customer_id, a.item_fg_id');
            $this->db->order_by('b.name', 'ASC');
            // $this->db->limit(25);
            
            
            $records = $this->db->get()->result_array();

            $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
            $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));

            $colspan = 1;
            while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                $colspan += 1;
                $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
            }

            $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
            $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));

            $html .= '
            <div class="table-container" id="table-detail">
                <table id="customers" style="min-width: 100%; background: white;">
                <thead>
                <tr>
                    <th class="sticky-col-0" rowspan="3" style="width: 40px;">No</th>
                    <th class="sticky-col-1" rowspan="3" style="width: 250px;">Customer</th>
                    <th class="sticky-col-2" rowspan="3" style="width: 100px;">Product ID</th>
                    <th class="sticky-col-3" rowspan="3" style="width: 100px;">Product No</th>
                    <th class="sticky-col-4" rowspan="3" style="width: 150px;">Product Name</th>
                    <th class="sticky-col-5" rowspan="3" style="width: 50px;">UoM</th>
                    <th class="sticky-col-6" rowspan="3" style="width: 80px;">Desc</th>
                </tr>
                <tr>';

            $start = new DateTime($filter_so_date_from);
            $end = new DateTime($filter_so_date_to);

            $month_days = [];

            while ($start <= $end) {
                $month = $start->format('M Y'); // e.g., 2024-11
                $year = $start->format('Y');   // e.g., 2024
                $month_num = $start->format('m'); // e.g., 11 for November

                $last_day_of_month = $start->format('Y-m-t');
                $last_day = new DateTime($last_day_of_month);
                $days_in_month = $last_day->format('d');

                $month_days[$month] = $days_in_month;
                $start->modify('first day of next month');
            }
            foreach ($month_days as $month => $days) {
                //echo "Month: $month - Total days: $days\n";
                $html .= '<th align="center" colspan="'.$days.'">'.$month .'</th>';
            }

            $html .= '</tr><tr>';
            while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                $start_date = date("d", strtotime($p_date_start));
                $html .='<th>'.$start_date.'</th>';
                $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
            }
            $html .= '</tr></thead>';

            $no = 1;

            foreach ($records as $data) {
                $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
                $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
                $plan_delivery_qty=0;
                $actual_delivery_qty=0;
                $balance_qty=0;
                $html .= '<tr>
                            <td class="sticky-col-0" rowspan="5">' . $no . '</td>
                            <td class="sticky-col-1" rowspan="5">' . $data['customer_name'] . '</td>
                            <td class="sticky-col-2" rowspan="5">' . $data['item_fg_id'] . '</td>
                            <td class="sticky-col-3" rowspan="5">' . $data['item_fg_number'] . '</td>
                            <td class="sticky-col-4" rowspan="5">' . $data['item_fg_name'] . '</td>
                            <td class="sticky-col-5" rowspan="5">' . $data['item_fg_uom'] . '</td>
                        </tr>
                <tr>
                    <td class="sticky-col-6" style="border-right: 1px solid #ddd;">Plan</td>';
                    while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                        $trans_date = date("Y-m-d", strtotime($p_date_start));

                        $this->db->select('SUM(qty) as qty_del');
                        $this->db->from('sales_order_deliveries a');
                        //$this->db->join('delivery_reports b', 'b.sales_order_no = a.sales_order_no and b.item_fg_id = a.item_fg_id and b.customer_id = a.customer_id','left');
                        $this->db->where('a.trans_date', $trans_date);
                        $this->db->where('a.customer_id', $data['customer_id']);
                        $this->db->where('a.item_fg_id', $data['item_fg_id']);
                        //$this->db->where('sales_order_no', $data['sales_order_no']);
                        // $this->db->where('a.customer_order_no', $data['customer_order_no']);
                        //$this->db->where('a.customer_order_no', $data['customer_order_no']);
                        //$this->db->group_by('sales_order_no, item_fg_id, customer_id, trans_date');
                        // $this->db->order_by('a.customer_id', 'ASC');
                        //$delivery = $this->db->get()->result_array();
                        $delivery = $this->db->get()->row_array(); // gets the first row only

                        if ($delivery) {
                            $plan_delivery_qty = $delivery['qty_del'];
                            if(@$plan_delivery_qty > 0){
                                $html .='<td style="background-color: #00ff00; color:black; font-weight:bold; min-width: 25px !important;">'.@$plan_delivery_qty.'</td>';
                            }else{
                                $html.='<td style="min-width: 25px !important;">0</td>';
                            }
                        } else {
                            $html.='<td style="min-width: 25px !important;">0</td>';
                        }

                        $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                    }

                $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
                $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
                
                $html .= '</tr>

                <tr>
                    <td class="sticky-col-6" style="border-right: 1px solid #ddd;">Actual</td>';
                    while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                        $trans_date = date("Y-m-d", strtotime($p_date_start));

                        $this->db->select('SUM(qty) as qty_del');
                        $this->db->from('delivery_notes');
                        //$this->db->join('sales_orders a', 'd.sales_order_no = a.sales_order_no and d.item_fg_id = a.item_fg_id and d.customer_id = a.customer_id','left');
                        $this->db->where('delivery_note_date', $trans_date);
                        $this->db->where('customer_id', $data['customer_id']);
                        $this->db->where('item_fg_id', $data['item_fg_id']);
                        //$this->db->where('sales_order_no', $data['sales_order_no']);
                        // $this->db->where('customer_order_no', $data['customer_order_no']);
                        //$this->db->group_by('sales_order_no, item_fg_id, customer_id, delivery_report_date');
                        // $this->db->order_by('customer_id', 'ASC');
                        $delivery = $this->db->get()->row_array(); // gets the first row only
                        // $delivery = $this->db->get()->result_array(); // get all rows

                        // $actual_delivery_qty = 0;
                        // foreach ($delivery as $del) {
                        //     $actual_delivery_qty += $del['qty_del'];
                        // }

                        if ($delivery) {
                            //$balance_qty = @$actual_delivery_qty - @$plan_delivery_qty;
                            $actual_delivery_qty = $delivery['qty_del'];
                            if(@$actual_delivery_qty > 0){
                                $html .='<td style="background-color: yellow; color:black; font-weight:bold;">'.@$actual_delivery_qty.'</td>';
                            }else{
                                $html.='<td>0</td>';
                            }
                        } else {
                            $html.='<td>0</td>';
                        }
                        
                        $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                    }
                    $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
                    $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
                $html .= '</tr>
                
                <tr>
                    <td class="sticky-col-6" style="border-right: 1px solid #ddd;">Ost</td>';
                    while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                        $trans_date = date("Y-m-d", strtotime($p_date_start));
                        $this->db->select('SUM(qty) as qty_del');
                        $this->db->from('delivery_notes');
                        $this->db->where('delivery_note_date', $trans_date);
                        $this->db->where('customer_id', $data['customer_id']);
                        // $this->db->where('customer_order_no', $data['customer_order_no']);
                        $this->db->where('item_fg_id', $data['item_fg_id']);
                        // $this->db->order_by('customer_id', 'ASC');
                        // $actual = $this->db->get()->result_array();
                        $actual = $this->db->get()->row_array();

                        $this->db->select('SUM(qty) as qty_del');
                        $this->db->from('sales_order_deliveries a');
                        $this->db->where('a.trans_date', $trans_date);
                        $this->db->where('a.customer_id', $data['customer_id']);
                        // $this->db->where('a.customer_order_no', $data['customer_order_no']);
                        $this->db->where('a.item_fg_id', $data['item_fg_id']);
                        // $this->db->order_by('a.customer_id', 'ASC');
                        $plan = $this->db->get()->row_array();

                        // $actual_delivery_qty = 0;
                        // foreach ($actual as $del) {
                        //     $actual_delivery_qty += $del['qty_del'];
                        // }                    

                        $valActual = !empty($actual) ? $actual['qty_del'] : 0;
                        $valPlan = !empty($plan) ? $plan['qty_del'] : 0;

                        $balance_qty = intval($valActual) - intval($valPlan) ;
                                // $html .='<td >'.@$balance_qty.'</td>';
                        if ($balance_qty < 0) {
                            $html .= '<td style="background-color: #ff8b8b; color: black; font-weight: bold;">' . $balance_qty . '</td>';
                        } else if($balance_qty > 0) {
                            $html .= '<td style="background-color: #8bc1ffff; color: black; font-weight: bold;">' . $balance_qty . '</td>';
                        } else {
                            $html .= '<td>0</td>';
                        }

                        $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                    }

                    $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
                    $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
                $html .= '</tr>';

                
                // //* start_from transaksi
                // $this->db->select_min('trans_date', 'min_date');
                // $this->db->from('sales_order_deliveries');
                // $this->db->where('customer_id', $data['customer_id']);
                // $this->db->where('item_fg_id', $data['item_fg_id']);
                // $min_date_row = $this->db->get()->row_array();
                // $start_from = $min_date_row && $min_date_row['min_date'] ? $min_date_row['min_date'] : $p_date_start;

                // //* 1. Array balance akumulatif dari start_from → p_date_to
                // $balance_by_date = [];
                // $total_balance_qty = 0;
                // $loop_date = $start_from;

                // while (strtotime($loop_date) <= strtotime($p_date_to)) {
                //     $trans_date = date("Y-m-d", strtotime($loop_date));

                //     //* Actual delivery
                //     $this->db->select('SUM(qty) as qty_del');
                //     $this->db->from('delivery_notes');
                //     $this->db->where('delivery_note_date', $trans_date);
                //     $this->db->where('customer_id', $data['customer_id']);
                //     $this->db->where('item_fg_id', $data['item_fg_id']);
                //     $actual = $this->db->get()->row_array();

                //     // Ambil plan delivery
                //     $this->db->select('SUM(qty) as qty_del');
                //     $this->db->from('sales_order_deliveries');
                //     $this->db->where('trans_date', $trans_date);
                //     $this->db->where('customer_id', $data['customer_id']);
                //     $this->db->where('item_fg_id', $data['item_fg_id']);
                //     $plan = $this->db->get()->row_array();

                //     $valActual = $actual['qty_del'] ?? 0;
                //     $valPlan = $plan['qty_del'] ?? 0;

                //     $daily_balance = intval($valPlan) - intval($valActual);
                //     $total_balance_qty += $daily_balance;

                //     $balance_by_date[$trans_date] = $total_balance_qty;

                //     $loop_date = date("Y-m-d", strtotime("+1 day", strtotime($loop_date)));
                // }

                // // 2. Render hanya dari tanggal filter ($p_date_start → $p_date_to)
                // $html .= '<tr>
                //     <td class="sticky-col-6" style="border-right: 1px solid #ddd;">Balance</td>';

                // $loop_render = $p_date_start;
                // while (strtotime($loop_render) <= strtotime($p_date_to)) {
                //     $render_date = date("Y-m-d", strtotime($loop_render));

                //     if (isset($balance_by_date[$render_date])) {
                //         $val = $balance_by_date[$render_date];

                //         if($val == 0) {
                //             $html .= '<td>0</td>';
                //         }else{
                //             $html .= '<td style="background-color: #ffa500; color: black; font-weight: bold;">' . -$val . '</td>';
                //         }
                        
                //     } else {
                //         // belum ada transaksi saat itu, jadi 0
                //         $html .= '<td>0</td>';
                //     }

                //     $loop_render = date("Y-m-d", strtotime("+1 day", strtotime($loop_render)));
                // }

                // $html .= '</tr>';


                //* Step 1: start_from
                $this->db->select_min('trans_date', 'min_date');
                $this->db->from('sales_order_deliveries');
                $this->db->where('customer_id', $data['customer_id']);
                $this->db->where('item_fg_id', $data['item_fg_id']);
                $min_date_row = $this->db->get()->row_array();
                $start_from = $min_date_row && $min_date_row['min_date'] ? $min_date_row['min_date'] : $p_date_start;

                //* Step 2: get all data actual delivery
                $actuals = $this->db->select('delivery_note_date as date, SUM(qty) as qty')
                    ->from('delivery_notes')
                    ->where('customer_id', $data['customer_id'])
                    ->where('item_fg_id', $data['item_fg_id'])
                    ->where('delivery_note_date >=', $start_from)
                    ->where('delivery_note_date <=', $p_date_to)
                    ->group_by('delivery_note_date')
                    ->get()->result_array();
                $actual_map = array_column($actuals, 'qty', 'date');

                //* Step 3: get all data plan delivery
                $plans = $this->db->select('sod.trans_date as date, SUM(sod.qty) as qty')
                    ->from('sales_order_deliveries sod')
                    ->join('sales_orders so', 'so.item_fg_id = sod.item_fg_id 
                        AND so.sales_order_no = sod.sales_order_no 
                        AND so.customer_order_no = sod.customer_order_no 
                        AND so.customer_id = sod.customer_id', 'left')
                    ->where('sod.customer_id', $data['customer_id'])
                    ->where('sod.item_fg_id', $data['item_fg_id'])
                    ->where('sod.trans_date >=', $start_from)
                    ->where('sod.trans_date <=', $p_date_to)
                    ->group_by('sod.trans_date')
                    ->get()->result_array();
                $plan_map = array_column($plans, 'qty', 'date');

                //* Step 4: get all tanggal yang mengandung closing
                $closings = $this->db->select('sod.trans_date as date')
                    ->from('sales_order_deliveries sod')
                    ->join('sales_orders so', 'so.item_fg_id = sod.item_fg_id 
                        AND so.sales_order_no = sod.sales_order_no 
                        AND so.customer_order_no = sod.customer_order_no 
                        AND so.customer_id = sod.customer_id', 'left')
                    ->where('sod.customer_id', $data['customer_id'])
                    ->where('sod.item_fg_id', $data['item_fg_id'])
                    ->where('sod.trans_date >=', $start_from)
                    ->where('sod.trans_date <=', $p_date_to)
                    ->where("(so.closing_reason IS NOT NULL OR so.type_closing IS NOT NULL)")
                    ->get()->result_array();
                $closing_dates = array_column($closings, 'date');
                $closing_map = array_flip($closing_dates);

                //* Step 5: Looping per hari dari start_from → p_date_to
                $balance_by_date = [];
                $is_closing_by_date = [];
                $total_balance_qty = 0;
                $reset_on_next_day = false;

                $loop_date = $start_from;
                while (strtotime($loop_date) <= strtotime($p_date_to)) {
                    $trans_date = date("Y-m-d", strtotime($loop_date));

                    $valActual = isset($actual_map[$trans_date]) ? (int) $actual_map[$trans_date] : 0;
                    $valPlan = isset($plan_map[$trans_date]) ? (int) $plan_map[$trans_date] : 0;
                    $daily_balance = $valPlan - $valActual;

                    //* add balance hari ini
                    $total_balance_qty += $daily_balance;
                    $balance_by_date[$trans_date] = $total_balance_qty;

                    //* jika hari ini closing
                    if (isset($closing_map[$trans_date])) {
                        $is_closing_by_date[$trans_date] = true;
                        $reset_on_next_day = true;
                    } elseif ($reset_on_next_day) {
                        //* reset pada hari setelah closing
                        $total_balance_qty = 0;
                        $balance_by_date[$trans_date] = 0;
                        $reset_on_next_day = false;
                    }

                    $loop_date = date("Y-m-d", strtotime("+1 day", strtotime($loop_date)));
                }

                // //* Step 5: Looping per hari dari start_from → p_date_to
                // $balance_by_date = [];
                // $is_closing_by_date = [];
                // $total_balance_qty = 0;

                // $loop_date = $start_from;
                // while (strtotime($loop_date) <= strtotime($p_date_to)) {
                //     $trans_date = date("Y-m-d", strtotime($loop_date));

                //     $valActual = isset($actual_map[$trans_date]) ? (int) $actual_map[$trans_date] : 0;
                //     $valPlan = isset($plan_map[$trans_date]) ? (int) $plan_map[$trans_date] : 0;
                //     $daily_balance = $valPlan - $valActual;

                //     //* Tambahkan balance hari ini
                //     $total_balance_qty += $daily_balance;

                //     //* Jika hari ini closing → langsung reset jadi 0
                //     if (isset($closing_map[$trans_date])) {
                //         $is_closing_by_date[$trans_date] = true;
                //         $total_balance_qty = 0;
                //     }

                //     $balance_by_date[$trans_date] = $total_balance_qty;
                //     $loop_date = date("Y-m-d", strtotime("+1 day", strtotime($loop_date)));
                // }

                //* Step 6: render balance
                $html .= '<tr><td class="sticky-col-6" style="border-right: 1px solid #ddd;">Balance</td>';
                $loop_render = $p_date_start;

                while (strtotime($loop_render) <= strtotime($p_date_to)) {
                    $render_date = date("Y-m-d", strtotime($loop_render));
                    $val = $balance_by_date[$render_date] ?? 0;

                    if ($val === 0) {
                        $html .= '<td>0</td>';
                    } else {
                        $style = isset($is_closing_by_date[$render_date])
                            ? 'background-color: #dc73e5; color: white; font-weight: bold;'
                            : 'background-color: #ffa500; color: black; font-weight: bold;';
                        $html .= "<td style=\"$style\">" . -$val . '</td>';
                    }

                    $loop_render = date("Y-m-d", strtotime("+1 day", strtotime($loop_render)));
                }

                $html .= '</tr>';




                // $html .= '<tr>
                //     <td class="sticky-col-6" style="border-right: 1px solid #ddd;">Balance</td>';
                //     $total_balance_qty = 0; // initialize akumulasi balance
                //     while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                //         $trans_date = date("Y-m-d", strtotime($p_date_start));

                //         // Get actual delivery
                //         $this->db->select('SUM(qty) as qty_del');
                //         $this->db->from('delivery_notes');
                //         $this->db->where('delivery_note_date', $trans_date);
                //         $this->db->where('customer_id', $data['customer_id']);
                //         $this->db->where('item_fg_id', $data['item_fg_id']);
                //         $actual = $this->db->get()->row_array();

                //         // Get planned delivery
                //         $this->db->select('SUM(qty) as qty_del');
                //         $this->db->from('sales_order_deliveries a');
                //         $this->db->where('a.trans_date', $trans_date);
                //         $this->db->where('a.customer_id', $data['customer_id']);
                //         $this->db->where('a.item_fg_id', $data['item_fg_id']);
                //         $plan = $this->db->get()->row_array();

                //         $valActual = !empty($actual) ? $actual['qty_del'] : 0;
                //         $valPlan = !empty($plan) ? $plan['qty_del'] : 0;

                //         // Hitung ost hari ini
                //         $daily_balance = intval($valPlan) - intval($valActual);

                //         // Update akumulatif
                //         $total_balance_qty += $daily_balance;

                //         if ($total_balance_qty != 0) {
                //             $html .= '<td style="background-color: #ffa500; color: black; font-weight: bold;">' .- $total_balance_qty . '</td>';
                //         } else {
                //             $html .= '<td>0</td>';
                //         }

                //         $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                //     }
                // $html .= '</tr>';

                $no++;
            }

            $html .= '</table>';
        }

        $html .= '</table></div>';

        $html .='</body></html>';
        echo $html;
    }
}
