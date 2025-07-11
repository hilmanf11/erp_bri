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
            header("Content-Disposition: attachment; filename=report_outstanding_so_$format.xls");
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

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <br>';

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
                COALESCE(SUM(e.qty), 0) as qty_del'
            );
            
            $this->db->from('sales_order_deliveries a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.customer_id = c.customer_id and a.item_fg_id = c.item_fg_id');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join('delivery_notes e', 'a.sales_order_no = e.sales_order_no and a.item_fg_id = e.item_fg_id and a.customer_id = e.customer_id and a.trans_date = e.delivery_note_date', 'left');

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

            // $this->db->order_by('a.sales_order_no', 'ASC');

            $records = $this->db->get()->result_array();

            $html .= '<table id="customers" border="1">
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
                            <th>Status</th>
                        </tr>';

            $no = 1;

            foreach ($records as $data) {

                // $status = ($data['qty_plan'] == $data['qty_del']) ? 'CLOSE' : 'OPEN';
                // $color = ($status == 'CLOSE') ? 'red' : 'green';

                if ($data['qty_del'] < $data['qty_plan'] && $data['qty_del'] > 0) {
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
                            <td align="center">' . $data['qty_plan'] . '</td>
                            <td align="center">' . $data['qty_del'] . '</td>
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

                // $filter_so_date_from = $dates['min_date'];
                // $filter_so_date_to = $dates['max_date'];

                $filter_so_date_from = date('Y-m-01', strtotime($dates['min_date']));
                $filter_so_date_to = date('Y-m-t', strtotime($dates['max_date']));                
            }

            $this->db->select('a.sales_order_no, a.trans_date, a.qty, a.customer_id, b.number as customer_number, b.name as customer_name, c.customer_order_no, d.id as item_fg_id, d.name as item_fg_name, d.number as item_fg_number, d.uom as item_fg_uom');
            $this->db->from('sales_order_deliveries a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('sales_orders c', 'a.customer_id = c.customer_id and a.sales_order_no = c.sales_order_no');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');

            if($filter_type != "WITHOUT_SCHEDULE") {
                $this->db->where("a.trans_date BETWEEN '$filter_so_date_from' AND '$filter_so_date_to'");
            }

            // $this->db->where("a.trans_date between '$filter_so_date_from' and '$filter_so_date_to'");

            if($filter_customer_name!=""){
                $this->db->where('a.customer_id', $filter_customer_name);
            }
            if($filter_item_fg!=""){
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            if($filter_customer_order_no!=""){
                $this->db->where('c.customer_order_no', $filter_customer_order_no);
            }
            //$this->db->like('a.sales_order_no', $filter_sales_order_no);
           $this->db->group_by('a.item_fg_id');
            $this->db->order_by('a.trans_date', 'ASC');
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

            $html .= '<table id="customers" border="1">
                <tr>
                    <th rowspan="3" width="20">No</th>
                    <th rowspan="3">Product ID</th>
                    <th rowspan="3">Product No</th>
                    <th rowspan="3">Product Name</th>
                    <th rowspan="3">UoM</th>
                    <th rowspan="3">Desc</th>
                </tr><tr>';

            // Convert strings to DateTime objects
            $start = new DateTime($filter_so_date_from);
            $end = new DateTime($filter_so_date_to);

            // Array to store the results
            $month_days = [];

            // Loop through each month between start and end date
            while ($start <= $end) {
                // Get the year and month
                $month = $start->format('M Y'); // e.g., 2024-11
                $year = $start->format('Y');   // e.g., 2024
                $month_num = $start->format('m'); // e.g., 11 for November

                // Get the last day of the current month
                $last_day_of_month = $start->format('Y-m-t'); // 'Y-m-t' gives the last day of the month

                // Create a DateTime object for the last day of the current month
                $last_day = new DateTime($last_day_of_month);

                // Get the total number of days in the current month
                $days_in_month = $last_day->format('d'); // Get the 'day' part of the date, which will be the total number of days

                // Store the result
                $month_days[$month] = $days_in_month;

                // Move to the first day of the next month
                $start->modify('first day of next month');
            }
            // Print the result
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
            $html .= '</tr>';

            $no = 1;

            foreach ($records as $data) {
                $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
                $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
                $plan_delivery_qty=0;
                $actual_delivery_qty=0;
                $balance_qty=0;
                $html .= '<tr>
                            <td rowspan="4">' . $no . '</td>
                            <td rowspan="4">' . $data['item_fg_id'] . '</td>
                            <td rowspan="4">' . $data['item_fg_number'] . '</td>
                            <td rowspan="4">' . $data['item_fg_name'] . '</td>
                            <td rowspan="4">' . $data['item_fg_uom'] . '</td>
                        </tr>
                <tr>
                    <td>Plan</td>';
                    while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                        $trans_date = date("Y-m-d", strtotime($p_date_start));

                $this->db->select('qty as qty_del');
                $this->db->from('sales_order_deliveries a');
                //$this->db->join('delivery_reports b', 'b.sales_order_no = a.sales_order_no and b.item_fg_id = a.item_fg_id and b.customer_id = a.customer_id','left');
                $this->db->where('a.trans_date', $trans_date);
                $this->db->where('a.customer_id', $data['customer_id']);
                //$this->db->where('sales_order_no', $data['sales_order_no']);
                $this->db->where('a.customer_order_no', $data['customer_order_no']);
                $this->db->where('a.item_fg_id', $data['item_fg_id']);
                //$this->db->where('a.customer_order_no', $data['customer_order_no']);
                //$this->db->group_by('sales_order_no, item_fg_id, customer_id, trans_date');
                $this->db->order_by('a.customer_id', 'ASC');
                //$delivery = $this->db->get()->result_array();
                $delivery = $this->db->get()->row_array(); // gets the first row only

                if ($delivery) {
                    $plan_delivery_qty = $delivery['qty_del'];
                    if(@$plan_delivery_qty > 0){
                        $html .='<td style="background-color: #00ff00; color:black; font-weight:bold;">'.@$plan_delivery_qty.'</td>';
                    }else{
                        $html.='<td>0</td>';
                    }
                } else {
                    $html.='<td>0</td>';
                }

                        //$delivery = $this->crud->read('sales_order_deliveries', [], ["trans_date" => $trans_date,"item_fg_id" => $data['item_fg_id']]);

                        
                        $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                    }

                $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
                $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
                
                $html .= '</tr>

                <tr>
                    <td>Actual</td>';
                    while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                        $trans_date = date("Y-m-d", strtotime($p_date_start));

                        $this->db->select('qty as qty_del');
                        $this->db->from('delivery_notes');
                        //$this->db->join('sales_orders a', 'd.sales_order_no = a.sales_order_no and d.item_fg_id = a.item_fg_id and d.customer_id = a.customer_id','left');
                        $this->db->where('delivery_note_date', $trans_date);
                        $this->db->where('customer_id', $data['customer_id']);
                        //$this->db->where('sales_order_no', $data['sales_order_no']);
                        $this->db->where('customer_order_no', $data['customer_order_no']);
                        $this->db->where('item_fg_id', $data['item_fg_id']);
                        //$this->db->group_by('sales_order_no, item_fg_id, customer_id, delivery_report_date');
                        $this->db->order_by('customer_id', 'ASC');
                        //$delivery = $this->db->get()->result_array();
                        $delivery = $this->db->get()->result_array(); // get all rows

                        $actual_delivery_qty = 0;
                        foreach ($delivery as $del) {
                            $actual_delivery_qty += $del['qty_del'];
                        }

                        if ($delivery) {
                            //$balance_qty = @$actual_delivery_qty - @$plan_delivery_qty;
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
                    <td>Balance</td>';
                    while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                        $trans_date = date("Y-m-d", strtotime($p_date_start));
                        $this->db->select('qty as qty_del');
                        $this->db->from('delivery_notes');
                        $this->db->where('delivery_note_date', $trans_date);
                        $this->db->where('customer_id', $data['customer_id']);
                        $this->db->where('customer_order_no', $data['customer_order_no']);
                        $this->db->where('item_fg_id', $data['item_fg_id']);
                        $this->db->order_by('customer_id', 'ASC');
                        $actual = $this->db->get()->result_array();

                        $this->db->select('qty as qty_del');
                        $this->db->from('sales_order_deliveries a');
                        $this->db->where('a.trans_date', $trans_date);
                        $this->db->where('a.customer_id', $data['customer_id']);
                        $this->db->where('a.customer_order_no', $data['customer_order_no']);
                        $this->db->where('a.item_fg_id', $data['item_fg_id']);
                        $this->db->order_by('a.customer_id', 'ASC');
                        $plan = $this->db->get()->row_array();

                        $actual_delivery_qty = 0;
                        foreach ($actual as $del) {
                            $actual_delivery_qty += $del['qty_del'];
                        }                    

                        $valActual = !empty($actual) ? $actual_delivery_qty : 0;
                        $valPlan = !empty($plan) ? $plan['qty_del'] : 0;

                        $balance_qty = intval($valActual) - intval($valPlan) ;
                                // $html .='<td >'.@$balance_qty.'</td>';
                        if ($balance_qty != 0) {
                            $html .= '<td style="background-color: #ff8b8b; color: black; font-weight: bold;">' . $balance_qty . '</td>';
                        } else {
                            $html .= '<td>0</td>';
                        }

                        $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                    }

                $html .= '</tr>';

                $no++;
            }

            $html .= '</table>';
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
