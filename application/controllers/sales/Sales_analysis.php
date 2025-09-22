<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_analysis extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_analysis');
        } else {
            redirect('error_access');
        }
    }

    private function format_number($input)
    {
        // Remove commas from the input
        $numeric_value = str_replace(',', '', $input);

        // Format the number to '15.000'
        return number_format($numeric_value, 0, '.', '.');
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_analysis_$format.xls");
        }

        $filter_period_year = base64_decode($this->input->get("filter_period_year"));
        $filter_month_from = base64_decode($this->input->get("filter_period_month_from"));
        $filter_month_to = base64_decode($this->input->get("filter_period_month_to"));
        $filter_customer_name = $this->input->get("filter_customer_name");
        $filter_item_fg = $this->input->get("filter_item_fg");
        $filter_division = base64_decode($this->input->get("filter_division"));
        $filter_product_family = base64_decode($this->input->get("filter_product_family"));

        $is_same_month = ($filter_month_from == $filter_month_to);
        
        $columns = [];
        $string_columns = [];
        if ($is_same_month) {
            // Hanya 1 bulan saja
            $columns[] = (int)$filter_month_from;
            $string_columns[] = str_pad($filter_month_from, 2, '0', STR_PAD_LEFT);
        } else {
            for ($m = (int)$filter_month_from; $m <= (int)$filter_month_to; $m++) {
                $columns[] = $m;
                $string_columns[] = str_pad($m, 2, '0', STR_PAD_LEFT);
            }
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $this->db->select('
        //     b.number as item_fg_number, 
        //     b.name as item_fg_name, 
        //     c.name as customer_name
        // ');

        // if ($is_same_month) {
        //     $this->db->select('
        //         a.month_1 as qty_month_1, 
        //         a.month_2 as qty_month_2, 
        //         a.month_3 as qty_month_3, 
        //         SUM(a.month_1 * IFNULL(d.price, 0)) as amount_month_1, 
        //         SUM(a.month_2 * IFNULL(d.price, 0)) as amount_month_2, 
        //         SUM(a.month_3 * IFNULL(d.price, 0)) as amount_month_3, 
        //         (CASE WHEN a.month_1 > a.month_2 THEN 0 ELSE 1 END) as bg_month_2, 
        //         (CASE WHEN a.month_1 > a.month_3 THEN 0 ELSE 1 END) as bg_month_3
        //     ');
        // } else {
        //     $i = 0;
        //     foreach ($string_columns as $month) {
        //         $i++;
        //         $this->db->select("
        //             SUM(IF(a.p_month = '$month', a.month_1, 0)) as qty_month_$i,
        //             SUM(IF(a.p_month = '$month', a.month_1 * IFNULL(d.price, 0), 0)) as amount_month_$i
        //         ");
        //     }
        // }

        // $this->db->from('forecasts a');
        // $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        // $this->db->join('customers c', 'a.customer_id = c.id', 'left');
        // $this->db->join('customer_items d', 'a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id', 'left');
        // $this->db->where('a.deleted', 0);
        // $this->db->where('a.p_year', $filter_period_year);

        // if ($is_same_month) {
        //     $this->db->where('a.p_month', $filter_month_from);
        // } else {
        //     $this->db->where("a.p_month >=", $filter_month_from);
        //     $this->db->where("a.p_month <=", $filter_month_to);
        // }

        $today = date('Y-m-d');
        
        $this->db->select('
            b.number as item_fg_number, 
            b.name as item_fg_name, 
            c.name as customer_name
        ');

        $i = 0;
        foreach ($string_columns as $month) {
            $i++;
            // $this->db->select("
            //     SUM(CASE WHEN DATE_FORMAT(a.sales_order_date, '%m') = '$month' 
            //             THEN a.qty ELSE 0 END) as qty_month_$i,
            //     SUM(CASE WHEN DATE_FORMAT(a.sales_order_date, '%m') = '$month' 
            //             THEN a.qty * IFNULL(d.price, 0) ELSE 0 END) as amount_month_$i
            // ");

            $this->db->select("
                SUM(CASE 
                        WHEN DATE_FORMAT(a.sales_order_date, '%Y-%m') = '{$filter_period_year}-$month'
                        THEN (
                            a.qty 
                            - IF(
                                (a.type_closing IS NOT NULL OR a.closing_reason IS NOT NULL) 
                                AND a.status = 1,
                                a.outstanding, 
                                0
                            )
                        )
                        ELSE 0 
                    END
                ) as qty_month_$i,

                SUM(CASE 
                        WHEN DATE_FORMAT(a.sales_order_date, '%Y-%m') = '{$filter_period_year}-$month'
                        THEN (
                            (a.qty 
                            - IF(
                                (a.type_closing IS NOT NULL OR a.closing_reason IS NOT NULL) 
                                AND a.status = 1,
                                a.outstanding, 
                                0
                            )
                            ) * IFNULL(d.price, 0)
                        )
                        ELSE 0 
                    END
                ) as amount_month_$i
            ", false);
        }

        $this->db->from('sales_orders a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('customers c', 'a.customer_id = c.id', 'left');

        $this->db->join(
            'customer_items d', 
            "a.customer_id = d.customer_id 
            AND a.item_fg_id = d.item_fg_id 
            AND d.valid_from <= a.sales_order_date
            AND d.valid_to >= a.sales_order_date",
            'left'
        );

        // $this->db->join(
        //     "(SELECT ci1.*
        //     FROM customer_items ci1
        //     WHERE NOT EXISTS (
        //         SELECT 1 
        //         FROM customer_items ci2 
        //         WHERE ci2.customer_id = ci1.customer_id
        //             AND ci2.item_fg_id = ci1.item_fg_id
        //             AND ci2.valid_from <= ci1.valid_from
        //             AND ci2.id <> ci1.id
        //     )
        //     ) d",
        //     "a.customer_id = d.customer_id 
        //     AND a.item_fg_id = d.item_fg_id
        //     AND (
        //         (d.valid_from <= a.sales_order_date AND d.valid_to >= a.sales_order_date)
        //         OR (
        //             NOT EXISTS (
        //                 SELECT 1 
        //                 FROM customer_items x
        //                 WHERE x.customer_id = a.customer_id
        //                 AND x.item_fg_id = a.item_fg_id
        //                 AND x.valid_from <= a.sales_order_date
        //                 AND x.valid_to >= a.sales_order_date
        //             )
        //             AND d.id = (
        //                 SELECT ci.id 
        //                 FROM customer_items ci
        //                 WHERE ci.customer_id = a.customer_id
        //                 AND ci.item_fg_id = a.item_fg_id
        //                 ORDER BY ci.valid_to DESC 
        //                 LIMIT 1
        //             )
        //         )
        //     )",
        //     'left',
        //     false
        // );

        $this->db->where('a.deleted', 0);
        $this->db->like('a.division', $filter_division);
        $this->db->where('YEAR(a.sales_order_date)', $filter_period_year);

        if ($is_same_month) {
            $this->db->where('MONTH(a.sales_order_date)', $filter_month_from);
        } else {
            $this->db->where('MONTH(a.sales_order_date) >=', $filter_month_from);
            $this->db->where('MONTH(a.sales_order_date) <=', $filter_month_to);
        }

        if ($filter_customer_name != '') {
            $this->db->where('a.customer_id', $filter_customer_name);
        }
        if ($filter_item_fg != '') {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }
        if ($filter_product_family != "") {
            $this->db->where('b.item_family_number', $filter_product_family);
        }
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.item_fg_id');
        // $this->db->group_by('a.sales_order_date');
        $this->db->order_by('c.name', 'ASC');
        $records = $this->db->get()->result_array();

        $customer_name = 'ALL';
        $part_name = 'ALL';
        if ($filter_customer_name != '') {
            $customer = $this->crud->read('customers', [], ["id" => $filter_customer_name]);
            if (!empty($customer->name)) {
                $customer_name = $customer->name;
            }
        }
        if ($filter_item_fg != '') {
            $part_name = $filter_item_fg;
        }

        $long_months = array('01' => 'JANUARY', '02' => 'FEBRUARY', '03' => 'MARCH', '04' => 'APRIL', '05' => 'MAY', '06' => 'JUNE', '07' => 'JULY', '08' => 'AUGUST', '09' => 'SEPTEMBER', '10' => 'OCTOBER', '11' => 'NOVEMBER', '12' => 'DECEMBER');

        $months = array('1' => 'Jan', '2' => 'Feb', '3' => 'Mar', '4' => 'Apr', '5' => 'May', '6' => 'Jun', '7' => 'Jul', '8' => 'Aug', '9' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');

        $count_column = count($columns);
        $body_style = ($count_column > 5) ? 'style="width: max-content;"' : 'style="width: 99.5%;"';

        $period_column = $long_months[$filter_month_from];

        if(!$is_same_month) {
            $period_column = "$long_months[$filter_month_from] - $long_months[$filter_month_to]";
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#sales_analysis {border-collapse: collapse;width: 100%;font-size: 12px;}#sales_analysis td, #sales_analysis th {border: 1px solid #ddd;padding: 2px;}#sales_analysis tr:nth-child(even){background-color: #f2f2f2;}#sales_analysis tr:hover {background-color: #ddd;}#sales_analysis th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
            <body '. $body_style .'>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:m:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: center; font-size: 16px; text-align: center;">
                    <h3>SALES ANALYSIS</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small><br>
                                <small>CUSTOMER NAME</small><br>
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $period_column . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small><b>' . $customer_name . '</b></small><br>
                                <small><b>' . $part_name . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br>    
            <div style="width: 100%; overflow-x: auto;"> 
            <table id="sales_analysis" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" style="text-align:center;" width="300";>Customer</th>
                <th rowspan="2" style="text-align:center;">Part No.</th>
                <th rowspan="2" style="text-align:center;" width="250">Part Name</th>
                <th colspan="' . count($columns) . '" style="text-align:center;">QTY</th>
                <th colspan="' . count($columns) . '" style="text-align:center;">AMOUNT</th>
            </tr><tr>';

            foreach ($columns as $m) {
                $html .= '<th>' . $months[$m] . '</th>';
            }
            foreach ($columns as $m) {
                $html .= '<th>' . $months[$m] . '</th>';
            }
            $html .= '</tr>';


            $no = 1;
            $current_customer = '';
            $total_qty = array_fill(0, count($columns), 0);
            $total_amount = array_fill(0, count($columns), 0);
            $grand_total_qty = array_fill(0, count($columns), 0);
            $grand_total_amount = array_fill(0, count($columns), 0);

            // Loop data record hasil query
            foreach ($records as $data) {
                if ($current_customer !== $data['customer_name']) {
                    if ($current_customer !== '') {
                        // Cetak subtotal
                        $html .= '<tr style="background-color:#FFFF00;"><td colspan="4" style="text-align:center;font-weight:bold;">Total</td>';
                        foreach ($total_qty as $qty) {
                            $html .= '<td style="text-align:right;font-weight:bold;">' . $this->format_number($qty) . '</td>';
                        }
                        foreach ($total_amount as $amt) {
                            $html .= '<td style="text-align:right;font-weight:bold;">Rp.' . $this->format_number($amt) . '</td>';
                        }
                        $html .= '</tr>';
                    }

                    // Reset untuk customer berikutnya
                    $current_customer = $data['customer_name'];
                    $total_qty = array_fill(0, count($columns), 0);
                    $total_amount = array_fill(0, count($columns), 0);
                }

                $html .= '<tr>';
                $html .= '<td>' . $no++ . '</td>';
                $html .= '<td>' . $data['customer_name'] . '</td>';
                $html .= '<td>' . $data['item_fg_number'] . '</td>';
                $html .= '<td>' . $data['item_fg_name'] . '</td>';

                // Cetak sales tiap bulan (qty)
                $qty_month_1 = $data['qty_month_1'] ?? 0;
                foreach ($columns as $i => $month) {
                    $qty = $data['qty_month_' . ($i + 1)] ?? 0;

                    $backgroundColor = 'transparent';
                    if ($i > 0 && $qty < $qty_month_1) {
                        $backgroundColor = '#F4B084';
                    }

                    $html .= '<td style="text-align:right;background-color:' . $backgroundColor . ';">' . $this->format_number($qty) . '</td>';

                    $total_qty[$i] += $qty;
                    $grand_total_qty[$i] += $qty;
                }

                // Cetak sales tiap bulan (amount)
                foreach ($columns as $i => $month) {
                    $amount = $data['amount_month_' . ($i + 1)] ?? 0;
                    $html .= '<td style="text-align:right;">Rp.' . $this->format_number($amount) . '</td>';
                    $total_amount[$i] += $amount;
                    $grand_total_amount[$i] += $amount;
                }

                $html .= '</tr>';
            }


            // Cetak subtotal customer terakhir
            if ($current_customer !== '') {
                $html .= '<tr style="background-color:#FFFF00;"><td colspan="4" style="text-align:center;font-weight:bold;">Total</td>';
                foreach ($total_qty as $qty) {
                    $html .= '<td style="text-align:right;font-weight:bold;min-width: 62px;">' . $this->format_number($qty) . '</td>';
                }
                foreach ($total_amount as $amt) {
                    $html .= '<td style="text-align:right;font-weight:bold;min-width: 62px;">Rp.' . $this->format_number($amt) . '</td>';
                }
                $html .= '</tr>';
            }


            // Grand total
            $html .= '<tr style="background-color:#C6EFCE;"><td colspan="4" style="text-align:center;font-weight:bold;">Grand Total</td>';
            foreach ($grand_total_qty as $qty) {
                $html .= '<td style="text-align:right;font-weight:bold;padding-left: 8px;">' . $this->format_number($qty) . '</td>';
            }
            foreach ($grand_total_amount as $amt) {
                $html .= '<td style="text-align:right;font-weight:bold;padding-left: 8px;">Rp.' . $this->format_number($amt) . '</td>';
            }
            $html .= '</tr>';


            // Akhiri tabel dan dokumen
            $html .= '</table></div></body></html>';
            echo $html;
    }
}
