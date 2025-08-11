<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mps_2 extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }
    private function format_number($input) {
        $numeric_value = str_replace(',', '', $input);
        return number_format($numeric_value, 0, '.', '.');
    }
    public function get_final_classification($arr) {
        // Initialize counters for the ranges
        $countS = 0;
        $countM = 0;
        $countF = 0;

        // Loop through the array and classify each value
        foreach ($arr as $value) {
            $num = (int) $value; // Convert to integer

            if ($num < 1000) {
                $countS++;
            } elseif ($num >= 1000 && $num <= 5000) {
                $countM++;
            } elseif ($num > 5000) {
                $countF++;
            }
        }

        // Determine the majority class
        if ($countS > $countM && $countS > $countF) {
            return 'SM'; // Majority are < 1000
        } elseif ($countM > $countS && $countM > $countF) {
            return 'MM'; // Majority are >= 1000 and <= 5000
        } elseif ($countF > $countS && $countF > $countM) {
            return 'FM'; // Majority are > 5000
        }

        return 'SM'; // In case of a tie or empty array
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mps_2');
        } else {
            redirect('error_access');
        }
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
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readRevisions()
    {
        $arr = array(
            ["id" => "0", "name" => "Revision 0"],
            ["id" => "1", "name" => "Revision 1"],
            ["id" => "2", "name" => "Revision 2"],
            ["id" => "3", "name" => "Revision 3"],
            ["id" => "4", "name" => "Revision 4"],
            ["id" => "5", "name" => "Revision 5"],
        );

        echo json_encode($arr);
    }

    public function cutOff($filter_month, $filter_year)
    {
        if (empty($filter_month) || empty($filter_year)) {
            return false;
        }

        $generate_date = date('Y-m-d', strtotime("{$filter_year}-{$filter_month}-25 -1 month"));

        while (true) {
            $day_of_week = date('w', strtotime($generate_date));
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $generate_date);
            $holiday = $this->db->get()->row();

            $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
            $is_holiday = !empty($holiday) && !empty($holiday->remarks);

            if (!$is_weekend && !$is_holiday) {
                // Setelah dapat hari kerja, cari 1 hari kerja sebelumnya
                $cutoff_date = $generate_date;
                do {
                    $cutoff_date = date('Y-m-d', strtotime($cutoff_date . ' -1 day'));
                    $day_of_week = date('w', strtotime($cutoff_date));
                    $this->db->select('remarks');
                    $this->db->from('calendars');
                    $this->db->where('working_date', $cutoff_date);
                    $holiday = $this->db->get()->row();
                } while ($day_of_week == 0 || $day_of_week == 6 || (!empty($holiday) && !empty($holiday->remarks)));

                return $cutoff_date;
            }

            $generate_date = date('Y-m-d', strtotime($generate_date . ' -1 day'));
        }

    }

    public function isGenerateDay($filter_month, $filter_year)
    {
        if (empty($filter_month) || empty($filter_year)) {
            return false;
        }

        $generate_date = date('Y-m-d', strtotime("{$filter_year}-{$filter_month}-25 -1 month"));

        while (true) {
            $day_of_week = date('w', strtotime($generate_date));
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $generate_date);
            $holiday = $this->db->get()->row();

            $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
            $is_holiday = !empty($holiday) && !empty($holiday->remarks);

            if (!$is_weekend && !$is_holiday) {
                break;
            }

            $generate_date = date('Y-m-d', strtotime($generate_date . ' -1 day'));
        }

        $today = date('Y-m-d');
        return true;
        return $generate_date == $today;
    }

    public function isGenerateDate($filter_month, $filter_year)
    {
        if (empty($filter_month) || empty($filter_year)) {
            return false;
        }

        $generate_date = date('Y-m-d', strtotime("{$filter_year}-{$filter_month}-25 -1 month"));

        while (true) {
            $day_of_week = date('w', strtotime($generate_date));
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $generate_date);
            $holiday = $this->db->get()->row();

            $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
            $is_holiday = !empty($holiday) && !empty($holiday->remarks);

            if (!$is_weekend && !$is_holiday) {
                break;
            }

            $generate_date = date('Y-m-d', strtotime($generate_date . ' -1 day'));
        }

        return $generate_date;
    }

    // public function getData()
    // {
    //     if ($this->input->get()) {
    //         //Filter Data
    //         $filter_month = base64_decode($this->input->get('filter_month'));
    //         $filter_year = base64_decode($this->input->get('filter_year'));
    //         $filter_customer = base64_decode($this->input->get('filter_customer'));
    //         $filter_product_no = base64_decode($this->input->get('filter_product_no'));
    //         $filter_revision = base64_decode($this->input->get('filter_revision'));

    //         $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
    //         $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
    //         $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

    //         //Configuration Planning
    //         $this->db->select('*');
    //         $this->db->from("config");
    //         $config = $this->db->get()->row();

    //         //Select Query
    //         $this->db->select('a.id, a.number, a.name, a.leadtime, b.customer_id,
    //         COALESCE(c.pp, 0) as pp, 
    //         COALESCE(c.p1, 0) as p1, 
    //         COALESCE(c.p2, 0) as p2, 
    //         COALESCE(c.p3, 0) as p3,
    //         COALESCE(c.pp + c.p1 + c.p2 + c.p3, 0) as total_wip, 
    //         COALESCE(d.qty, 0) as fg, 
    //         COALESCE(e.qty, 0) as os_mpp, 
    //         COALESCE(f.qty, 0) as os_so');
    //         $this->db->from('item_fg a');
    //         $this->db->join('customer_items b', 'a.id = b.item_fg_id');
    //         $this->db->join('stock_wip c', "a.id = c.item_fg_id and c.p_month = '" . $filter_month . "' and c.p_year = '" . $filter_year . "' and c.revision = '" . intval($filter_revision) . "'", "left");
    //         $this->db->join('stock_fg d', "a.id = d.item_fg_id and d.p_month = '" . $filter_month . "' and d.p_year = '" . $filter_year . "' and d.revision = '" . intval($filter_revision) . "'", "left");
    //         $this->db->join('os_mpp e', "a.id = e.item_fg_id and b.customer_id = e.customer_id and e.p_month = '" . $filter_month . "' and e.p_year = '" . $filter_year . "' and e.revision = '" . intval($filter_revision) . "'", "left");
    //         $this->db->join('os_so f', "a.id = f.item_fg_id and b.customer_id = f.customer_id and f.p_month = '" . $filter_month . "' and f.p_year = '" . $filter_year . "' and f.revision = '" . intval($filter_revision) . "'", "left");
    //         if ($filter_customer != "") {
    //             $this->db->where('b.customer_id', $filter_customer);
    //         }
    //         if ($filter_product_no != "") {
    //             $this->db->where('a.id', $filter_product_no);
    //         }
    //         $this->db->group_by('b.customer_id');
    //         $this->db->group_by('b.item_fg_id');
    //         $this->db->order_by('a.number', 'asc');
    //         $records = $this->db->get()->result_array();

    //         foreach ($records as $data) {
    //             $totalStock = ($data['total_wip'] + $data['fg'] + $data['os_mpp']);
    //             if ($data['fg'] == null) {
    //                 $fg = "0";
    //             } else {
    //                 $fg = $data['fg'];
    //             }

    //             $i = 1;
    //             $beginBalance = 0;
    //             $forecast = 0;
    //             $deliveryRate = 0;
    //             $ito = 0;
    //             $safetyStock = 0;
    //             $prodPlan = 0;
    //             $arrMonth = array();

    //             $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
    //             $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

    //             //Cek Forecasts
    //             // $forecast = $this->crud->read('forecasts', [], ["item_fg_id" => $data['id'], "customer_id" => $data['customer_id'], "p_month" => $filter_month, "p_year" => $filter_year, "revision" => intval($filter_revision)]);
    //             $forecast = $this->crud->read("forecasts", [], ["customer_id" => $filter_customer, "item_fg_id" => $filter_product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => intval($filter_revision)]);
    //             while ($monthStart < $monthEnd) {
    //                 $monthName = date('F Y', $monthStart);
    //                 $monthName2 = date('Y-m-01', $monthStart);
    //                 // $monthsPlus = strtolower(date('F', strtotime('+1 month', $monthStart)));
    //                 $start = strtotime(date('Y-m-01', $monthStart));
    //                 $finish = strtotime(date('Y-m-t', $monthStart));

    //                 $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
    //                 $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));

    //                 //HKW 1
    //                 $hkw = 0;
    //                 for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
    //                     $working_date = date('Y-m-d', $z);

    //                     $this->db->select('remarks');
    //                     $this->db->from('calendars');
    //                     $this->db->where('working_date', $working_date);
    //                     $holiday = $this->db->get()->row();

    //                     if (date('w', $z) !== '0' && date('w', $z) !== '6') {
    //                         if (@$holiday->remarks != null or @$holiday->remarks != "") {
    //                             $hkw += 0;
    //                         } else {
    //                             $hkw += 1;
    //                         }
    //                     } else {
    //                         $hkw += 0;
    //                     }
    //                 }

    //                 //HKW 2
    //                 $hkw2 = 0;
    //                 for ($x = $start2; $x <= $finish2; $x += (60 * 60 * 24)) {
    //                     $working_date2 = date('Y-m-d', $x);

    //                     $this->db->select('remarks');
    //                     $this->db->from('calendars');
    //                     $this->db->where('working_date', $working_date2);
    //                     $holiday2 = $this->db->get()->row();

    //                     if (date('w', $x) !== '0') {
    //                         if (@$holiday2->remarks != null or @$holiday2->remarks != "") {
    //                             $hkw2 += 0;
    //                         } else {
    //                             $hkw2 += 1;
    //                         }
    //                     } else {
    //                         $hkw2 += 0;
    //                     }
    //                 }

    //                 //Bulan Pertama - Keenam
    //                 if ($i == 1) {
    //                     $beginBalance = $totalStock - $data['os_so'];
    //                     $forecastData = @round($forecast->month_1);
    //                     $deliveryRate = @round($forecastData / $hkw);
    //                     $ito = @round($beginBalance / $deliveryRate);
    //                     $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_2);
    //                     $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
    //                 } else if ($i == 2) {
    //                     $beginBalance = (($prodPlan + $beginBalance) - $forecast);
    //                     $forecastData = @round($forecast->month_2);
    //                     $deliveryRate = @round($forecastData / $hkw);
    //                     $ito = @round($beginBalance / $deliveryRate);
    //                     $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_3);
    //                     $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
    //                 } elseif ($i == 3) {
    //                     $beginBalance = (($prodPlan + $beginBalance) - $forecast);
    //                     $forecastData = @round($forecast->month_3);
    //                     $deliveryRate = @round($forecastData / $hkw);
    //                     $ito = @round($beginBalance / $deliveryRate);
    //                     $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_4);
    //                     $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
    //                 } elseif ($i == 4) {
    //                     $beginBalance = (($prodPlan + $beginBalance) - $forecast);
    //                     $forecastData = @round($forecast->month_4);
    //                     $deliveryRate = @round($forecastData / $hkw);
    //                     $ito = @round($beginBalance / $deliveryRate);
    //                     $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_5);
    //                     $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
    //                 } elseif ($i == 5) {
    //                     $beginBalance = (($prodPlan + $beginBalance) - $forecast);
    //                     $forecastData = @round($forecast->month_5);
    //                     $deliveryRate = @round($forecastData / $hkw);
    //                     $ito = @round($beginBalance / $deliveryRate);
    //                     $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_6);
    //                     $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
    //                 } elseif ($i == 6) {
    //                     $beginBalance = (($prodPlan + $beginBalance) - $forecast);
    //                     $forecastData = @round($forecast->month_6);
    //                     $deliveryRate = @round($forecastData / $hkw);
    //                     $ito = @round($beginBalance / $deliveryRate);
    //                     $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * $forecast->month_6);
    //                     $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
    //                 }

    //                 if ($prodPlan <= 0) {
    //                     $prodPlanFinal = 0;
    //                 } else {
    //                     $prodPlanFinal = $prodPlan;
    //                 }

    //                 $arrMonth[] = array(
    //                     "p_month" => $filter_month,
    //                     "p_year" => $filter_year,
    //                     "revision" => $filter_revision,
    //                     "customer_id" => $data['customer_id'],
    //                     "item_fg_id" => $data['id'],
    //                     "ltpp_month" => strtoupper($monthName),
    //                     "ltpp_month2" => $monthName2,
    //                     "hkw" => $hkw2,
    //                     "begin_balance" => $beginBalance,
    //                     "ito" => $ito,
    //                     "forecast" => $forecastData,
    //                     "delivery_rate" => $deliveryRate,
    //                     "safety_stock" => $safetyStock,
    //                     "prod_plan" => $prodPlanFinal

    //                 );

    //                 $monthStart = strtotime("+1 month", $monthStart);
    //                 $i++;

    //                 $beginBalance = $beginBalance;
    //                 $forecast = $forecastData;
    //                 $deliveryRate = $deliveryRate;
    //                 $ito = $ito;
    //                 $safetyStock = $safetyStock;
    //                 $prodPlan = $prodPlanFinal;
    //                 //print_r($prodPlan);
    //                 //print_r($beginBalance);
    //                 // print_r($forecast);
    //                 $balance = (($prodPlan + $beginBalance) - $forecast);
    //             }

    //             $arr[] = array(
    //                 "p_month" => $filter_month,
    //                 "p_year" => $filter_year,
    //                 "revision" => $filter_revision,
    //                 "customer_id" => $data['customer_id'],
    //                 "item_fg_id" => $data['id'],
    //                 "wip_month" => strtoupper($monthBack),
    //                 "pp" => $data['pp'],
    //                 "p1" => $data['p1'],
    //                 "p2" => $data['p2'],
    //                 "p3" => $data['p3'],
    //                 "fg" => $fg,
    //                 "os_mpp" => $data['os_mpp'],
    //                 "total_stock" => $totalStock,
    //                 "os_so" => $data['os_so'],
    //                 "balance" => $balance,
    //                 "details" => $arrMonth
    //             );
    //         }

    //         // $arr['total'] = @count($arr);
    //         // die(json_encode($arr));
    //          die(json_encode($arr, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            // $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            //* get bulan sebelumnya
            $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            
            //* get bulan dan tahun berikutnya sbg batas looping
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            //* digunakan untuk memfilter SO (Sales Order) sebelum dan sesudahnya
            // $cutoff= "$filter_year-$filter_month-01";

            $cutoff = $this->cutOff($filter_month, $filter_year);
            $cutoff_to= "$filter_year-$filter_month-01";

            $isGeneratedDate = $this->isGenerateDate($filter_month, $filter_year);


            //Configuration Planning
            $this->db->select('*');
            $this->db->from("config");
            $config = $this->db->get()->row();

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
                a.leadtime, 
                COALESCE(c.pp, 0) as pp, 
                COALESCE(c.p1, 0) as p1, 
                COALESCE(c.p2, 0) as p2, 
                COALESCE(c.p3, 0) as p3,
                COALESCE(c.pp + c.p1 + c.p2 + c.p3, 0) as total_wip, 
                COALESCE(d.fg, 0) as fg, 
                COALESCE(e.qty, 0) as os_mpp, 
                COALESCE(f.qty, 0) as os_so'
            );
            $this->db->from('item_fg a');
            // $this->db->join('customer_items b', 'a.id = b.item_fg_id');
            $this->db->join('stock_wip c', "a.id = c.item_fg_id 
                and c.p_month = '" . $filter_month . "' 
                and c.p_year = '" . $filter_year . "' 
                and c.revision = '" . intval($filter_revision) . "'", 
                "left"
            );

            // $this->db->join('stock_fg d', "a.id = d.item_fg_id 
            //     and d.p_month = '" . $filter_month . "' 
            //     and d.p_year = '" . $filter_year . "' 
            //     and d.revision = '" . intval($filter_revision) . "'", 
            //     "left"
            // );

            $this->db->join("($subquery_end_stock) d", 'a.id = d.item_fg_id', 'left');


            $this->db->join('os_mpp e', "a.id = e.item_fg_id 
                and e.p_month = '" . $filter_month . "' 
                and e.p_year = '" . $filter_year . "' 
                and e.revision = '" . intval($filter_revision) . "'", 
                "left"
            );
            $this->db->join('os_so f', "a.id = f.item_fg_id 
                and f.p_month = '" . $filter_month . "' 
                and f.p_year = '" . $filter_year . "' 
                and f.revision = '" . intval($filter_revision) . "'", 
                "left"
            );
            
            // if ($filter_customer != "") {
            //     $this->db->where('b.customer_id', $filter_customer);
            // }

            if ($filter_product_no != "") {
                $this->db->where('a.id', $filter_product_no);
            }

            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->group_by('a.id');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            foreach ($records as $data) {
                
                $itmid = $data['id'];
                // $custid = $data['customer_id'];
                $fg = 0;
                $i = 1;
                $beginBalance = 0;
                // $forecast = 0;
                $forecastData= 0;
                $deliveryRate = 0;
                $ito = 0;
                $soOut = 0;
                $safetyStock = 0;
                $safetyStockWIP = 0;
                $safetyStockFG = 0;
                $need = 0;
                $prodPlan = 0;
                $arrMonth = array();

                $prevProdPlan = 0;
                $prevForecast = 0;
                $prevBeginBalance = 0;

                // $os_so = $this->crud->read('os_so', [], ["customer_id" => $data['customer_id'],"item_fg_id" => $data['id'],"p_month" => $filter_month,"p_year" => $filter_year,"revision" => intval($filter_revision)]);
                // $so_so_qty = 0;
                // if(!empty($os_so)){
                //     $so_so_qty = floatval($os_so->qty);
                // }
                if ($data['fg'] !== null) {
                    $fg = intval($data['fg']);
                }

                $os_so = $this->crud->query("SELECT sum(outstanding) as so 
                    FROM sales_orders 
                    WHERE delivery_date >= '".$isGeneratedDate."'
                    AND delivery_date < '".$cutoff_to."' 
                    AND item_fg_id = '".$itmid."'
                    AND closing_reason IS NULL
                    AND type_closing IS NULL
                    GROUP BY item_fg_id
                ");

                $so_so_qty = 0;
                if(!empty($os_so)){
                    $so_so_qty = floatval($os_so[0]->so);
                }

                $so = $this->crud->query("SELECT sum(qty) as so 
                    FROM sales_orders 
                    WHERE delivery_date >= '".$cutoff_to."'
                    AND item_fg_id = '".$itmid."'
                    AND closing_reason IS NULL
                    AND type_closing IS NULL
                    GROUP BY item_fg_id
                ");

                if(!empty($so)){
                    $soOut = $so[0]->so;
                }

                $totalStock = (intval($data['total_wip']) + intval($data['fg']) + intval($data['os_mpp']));

                // $currentDate = new DateTime("$filter_year-$filter_month-01");
                // $previousMonths = [];
                // $forecastMonth1 = [];
                
                // for ($m = 0; $m < 3; $m++) {
                //     $date = clone $currentDate;
                //     $date->modify("-$m month");
                //     $previousMonths[] = $date->format('Y-m');
                // }

                // foreach($previousMonths as $fcmonth){
                //     $queryMonth1=$this->db->query("SELECT p_year, p_month, month_1 
                //         FROM forecasts 
                //         where item_fg_id='$itmid' 
                //         and CONCAT(p_year, '-', p_month) = '$fcmonth'
                //     ");

                //     $resultMonth = $queryMonth1->row();
                //     $forecastMonth1[] = empty($resultMonth) ? 0 : $resultMonth->month_1;
                // }

                // $product_class = $this->get_final_classification($forecastMonth1);
                // $persentase = ($product_class === "FM")?20:6;

                $currentDate = new DateTime("$filter_year-$filter_month-01");
                $previousMonths = [];
                $deliverySums = [];

                // Ambil 3 bulan terakhir termasuk bulan sekarang
                for ($m = 0; $m < 3; $m++) {
                    $date = clone $currentDate;
                    $date->modify("-$m month");
                    $previousMonths[] = $date->format('Y-m');
                }

                // Ambil total qty delivery dari tiap bulan
                foreach ($previousMonths as $month) {
                    $startDate = $month . '-01';
                    $endDate = date('Y-m-t', strtotime($startDate)); // akhir bulan

                    $query = $this->db->query("
                        SELECT SUM(qty) as total_qty 
                        FROM sales_order_deliveries 
                        WHERE item_fg_id = '$itmid' 
                        AND deleted = 0
                        AND trans_date BETWEEN '$startDate' AND '$endDate'
                    ");

                    $row = $query->row();
                    $deliverySums[] = empty($row->total_qty) ? 0 : (int)$row->total_qty;
                }

                // Hitung rata-rata dari total delivery qty selama 3 bulan
                $avg_delivery = count($deliverySums) > 0 ? array_sum($deliverySums) / count($deliverySums) : 0;

                // Kirim nilai rata-rata ke fungsi klasifikasi dalam array
                $product_class = $this->get_final_classification([$avg_delivery]);

                // Gunakan persentase sesuai klasifikasi
                $persentase = ($product_class === "FM") ? 20 : 6;


                // Cek Forecasts
                // $max_rev = $this->db->select_max('revision')
                // ->where('item_fg_id', $itmid)
                // ->where('p_month', $filter_month)
                // ->where('p_year', $filter_year)
                // ->get('forecasts')
                // ->row()->revision;

                // $forecast = $this->crud->read('forecasts', [], [
                //     "item_fg_id" => $itmid, 
                //     "p_month" => $filter_month, 
                //     "p_year" => $filter_year, 
                //     "revision" => intval($max_rev)
                // ]);

                $this->db->from('forecasts');
                $this->db->where([
                    'item_fg_id' => $itmid,
                    'p_month' => $filter_month,
                    'p_year' => $filter_year
                ]);
                $allForecasts = $this->db->get()->result();

                // Kelompokkan forecast per customer_id
                $groupedForecasts = [];
                foreach ($allForecasts as $f) {
                    $custId = $f->customer_id;
                    $rev = intval($f->revision);
                    if (!isset($groupedForecasts[$custId]) || $rev > $groupedForecasts[$custId]->revision) {
                        $groupedForecasts[$custId] = $f; // Simpan hanya revision tertinggi
                    }
                }

                // Cek apakah semua forecast hanya revision = 0
                $allZeroRevision = true;
                foreach ($allForecasts as $f) {
                    if (intval($f->revision) > 0) {
                        $allZeroRevision = false;
                        break;
                    }
                }

                // Jika semua revisi = 0, maka ambil semua data
                $finalForecasts = $allZeroRevision ? $allForecasts : array_values($groupedForecasts);

                // Inisialisasi penjumlahan forecast per bulan
                $sum_forecast = [
                    'month_1' => 0,
                    'month_2' => 0,
                    'month_3' => 0,
                    // 'month_4' => 0,
                ];

                // Proses penjumlahan
                foreach ($finalForecasts as $f) {
                    $sum_forecast['month_1'] += floatval($f->month_1);
                    $sum_forecast['month_2'] += floatval($f->month_2);
                    $sum_forecast['month_3'] += floatval($f->month_3);
                    // $sum_forecast['month_4'] += floatval($f->month_4);
                }

                $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
                $monthEnd =  strtotime(date('Y-m-d', strtotime('+2 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

                while ($monthStart < $monthEnd) {
                    $monthName = date('F Y', $monthStart);
                    $monthName2 = date('Y-m-01', $monthStart);
                    // $monthsPlus = strtolower(date('F', strtotime('+1 month', $monthStart)));
                    $start = strtotime(date('Y-m-01', $monthStart));
                    $finish = strtotime(date('Y-m-t', $monthStart));

                    $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
                    $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));

                    //HKW 1
                    $hkw = 0;
                    for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                        $working_date = date('Y-m-d', $z);

                        $this->db->select('remarks');
                        $this->db->from('calendars');
                        $this->db->where('working_date', $working_date);
                        $holiday = $this->db->get()->row();

                        if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                            if (@$holiday->remarks != null or @$holiday->remarks != "") {
                                $hkw += 0;
                            } else {
                                $hkw += 1;
                            }
                        } else {
                            $hkw += 0;
                        }
                    }

                    //HKW 2
                    $hkw2 = 0;
                    for ($x = $start2; $x <= $finish2; $x += (60 * 60 * 24)) {
                        $working_date2 = date('Y-m-d', $x);

                        $this->db->select('remarks');
                        $this->db->from('calendars');
                        $this->db->where('working_date', $working_date2);
                        $holiday2 = $this->db->get()->row();

                        if (date('w', $x) !== '0') {
                            if (@$holiday2->remarks != null or @$holiday2->remarks != "") {
                                $hkw2 += 0;
                            } else {
                                $hkw2 += 1;
                            }
                        } else {
                            $hkw2 += 0;
                        }
                    }

                    //Bulan Pertama - Ketiga
                    if ($i == 1) {
                        $beginBalance = $totalStock - floatval($so_so_qty);

                        $forecastData = @round($sum_forecast['month_1']);
                        $compareFCSO = max($forecastData, $soOut);

                        $deliveryRate = @round($compareFCSO / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($sum_forecast['month_2']));
                        $safetyStockWIP = (10 / 100) * @round($sum_forecast['month_2']);
                        $safetyStockFG = ($persentase / 100) * @round($sum_forecast['month_2']);
                        $need = (($compareFCSO + $safetyStockWIP + $safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } else if ($i == 2) {
                        // Begin balance M2 dari hasil M1
                        $beginBalance = ($prevProdPlan - $prevForecast) + $prevBeginBalance;
                        
                        $forecastData = @round($sum_forecast['month_2']);
                        $deliveryRate = @round($forecastData / $hkw);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($sum_forecast['month_3']));
                        $safetyStockWIP = (10 / 100) * @round($sum_forecast['month_3']);
                        $safetyStockFG = ($persentase / 100) * @round($sum_forecast['month_3']);
                        // $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $ito = @round($beginBalance / $deliveryRate);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } elseif ($i == 3) {
                        // Begin balance M3 dari hasil M2
                        $beginBalance = ($prevProdPlan - $prevForecast) + $prevBeginBalance;

                        $forecastData = @round($sum_forecast['month_3']);
                        $deliveryRate = @round($forecastData / $hkw);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($sum_forecast['month_3']));
                        $safetyStockWIP = (10 / 100) * @round($sum_forecast['month_3']);
                        $safetyStockFG = ($persentase / 100) * @round($sum_forecast['month_3']);
                        // $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $ito = @round($beginBalance / $deliveryRate);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                    }

                    $arrMonth[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "item_fg_id" => $itmid,
                        "ltpp_month" => strtoupper($monthName),
                        "ltpp_month2" => $monthName2,
                        "hkw" => $hkw2,
                        "begin_balance" => $beginBalance,
                        "ito" => $ito,
                        "so" => $soOut,
                        "forecast" => $forecastData,
                        "delivery_rate" => $deliveryRate,
                        "safety_stock" => $safetyStock,
                        "safety_stock_wip" => $safetyStockWIP,
                        "safety_stock_fg" => $safetyStockFG,
                        "need" => $need,
                        "prod_plan" => $prodPlan

                    );

                    $monthStart = strtotime("+1 month", $monthStart);
                    $balance = (($prodPlan + $beginBalance) - $forecastData);
                    $prevBeginBalance = $beginBalance;
                    $prevProdPlan = $prodPlan;
                    $prevForecast = $forecastData;
                    $i++;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "item_fg_id" => $itmid,
                    "wip_month" => strtoupper($monthBack),
                    "pp" => $data['pp'],
                    "p1" => $data['p1'],
                    "p2" => $data['p2'],
                    "p3" => $data['p3'],
                    "fg" => $fg,
                    "os_mpp" => $data['os_mpp'],
                    "total_stock" => $totalStock,
                    "os_so" => $so_so_qty,
                    "balance" => $balance,
                    "item_class" => $product_class,
                    "details" => $arrMonth
                );
            }
            //$arr['total'] = @count($arr);
           die(json_encode($arr, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function revision()
    {
        $filter_month = $this->input->post('filter_month');
        $filter_year = $this->input->post('filter_year');

        $this->db->select('revision');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('deleted', 0);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $this->db->limit(1);
        $record = $this->db->get()->row();
        echo @$record->revision ? $record->revision : 0;
    }

    public function checkForecast()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('forecasts');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('revision', intval($filter_revision));
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

    public function checkFg()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        if ($this->isGenerateDay($filter_month, $filter_year)) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOs()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $period= "$filter_year-$filter_month-01";
        $this->db->select('*');
        $this->db->from('sales_orders');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" && $filter_year != "") {
            $this->db->where('delivery_date >= ', $period);
        }
        $this->db->where('delivery', '0');
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOstSo()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        $cutOff = $this->cutOff($filter_month, $filter_year);
        $this->db->select('*');
        $this->db->from('sales_orders');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" && $filter_year != "") {
            $this->db->where('delivery_date >= ', $cutOff);
            // $this->db->where('delivery_date < ', $cutoff);
        }
        $this->db->where('outstanding !=', '0');
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkStockWip()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('stock_wip');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('revision', intval($filter_revision));
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    // public function checkOstMpp()
    // {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));

    //     // Step 1: Cari revision terbesar
    //     $this->db->select_max('revision');
    //     $this->db->where('p_month', $filter_month);
    //     $this->db->where('p_year', $filter_year);
    //     $max_revision = $this->db->get('os_mpp')->row()->revision;

    //     // Step 2: Ambil data os_mpp berdasarkan revision terbesar
    //     $this->db->select('*');
    //     $this->db->from('os_mpp');
    //     $this->db->where('p_month', $filter_month);
    //     $this->db->where('p_year', $filter_year);
    //     $this->db->where('revision', $max_revision);
    //     $records = $this->db->get()->result_array();

    //     if (count($records) > 0) {
    //         echo json_encode(array("theme" => "success"));
    //     } else {
    //         echo json_encode(array("theme" => "error"));
    //     }
    // }

    public function checkOstMpp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('os_mpp');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('revision', intval($filter_revision));
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkCalendar()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        // Hitung bulan ke-1 setelah bulan yg dipilih
        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        // Hitung rentang waktu dari bulan input -> +6 bulan ke depan
        // Input January 2025 -> Juni 2025
        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

        $html = "";
        $no = 1;
        // Hitung Hari Kerja (HKW)
        while ($monthStart < $monthEnd) {
            $monthName = date('m/Y', $monthStart);
            $start = strtotime(date('Y-m-01', $monthStart));
            $finish = strtotime(date('Y-m-t', $monthStart));

            //HKW 1
            $hkw = 0;
            for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $z);

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                // Abaikan Sabtu (w=6) dan Minggu (w=0)
                if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                    // Hari Senin–Jumat
                    if (@$holiday->remarks != null or @$holiday->remarks != "") {
                        $hkw += 0; // Libur nasional
                    } else {
                        $hkw += 1; // Hari kerja
                    }
                } else {
                    $hkw += 0;
                }
            }

            $html .= '  <div style="margin:15px;">
                            HKW ' . $no . ' : ' . $monthName . ' : <b>' . $hkw . '</b>
                        </div>';

            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        echo $html;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $postDetails = $post['details'];

            foreach ($postDetails as $postDetail) {
                $generateMpsDetails = $this->crud->reads('generate_mps_details', [], [
                    "p_month" => $postDetail['p_month'],
                    "p_year" => $postDetail['p_year'],
                    "revision" => intval($postDetail['revision']),
                    "item_fg_id" => $postDetail['item_fg_id'],
                    "ltpp_month" => $postDetail['ltpp_month']
                ]);

                $postFinalDetail = array(
                    "p_month" => $postDetail['p_month'],
                    "p_year" => $postDetail['p_year'],
                    "revision" => $postDetail['revision'],
                    // "customer_id" => $postDetail['customer_id'],
                    "item_fg_id" => $postDetail['item_fg_id'],
                    "ltpp_month" => $postDetail['ltpp_month'],
                    "ltpp_month2" => $postDetail['ltpp_month2'],
                    "hkw" => $postDetail['hkw'],
                    "begin_balance" => $postDetail['begin_balance'],
                    "ito" => $postDetail['ito'],
                    "so" => $postDetail['so'],
                    "forecast" => $postDetail['forecast'],
                    "delivery_rate" => $postDetail['delivery_rate'],
                    "safety_stock" => $postDetail['safety_stock'],
                    "safety_stock_wip" => $postDetail['safety_stock_wip'],
                    "safety_stock_fg" => $postDetail['safety_stock_fg'],
                    "need" => $postDetail['need'],
                    "prod_plan" => $postDetail['prod_plan']
                );

                if (count($generateMpsDetails) > 0) {
                    $send = $this->crud->update('generate_mps_details', [
                        "p_month" => $postDetail['p_month'],
                        "p_year" => $postDetail['p_year'],
                        "revision" => $postDetail['revision'],
                        // "customer_id" => $postDetail['customer_id'],
                        "item_fg_id" => $postDetail['item_fg_id'],
                        "ltpp_month" => $postDetail['ltpp_month']
                    ], $postFinalDetail);
                } else {
                    $this->crud->create('generate_mps_details', $postFinalDetail);
                }
            }

            $generateMps = $this->crud->reads('generate_mps', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => intval($post['revision']),
                // "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "wip_month" => $post['wip_month']
            ]);

            $postFinal = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                // "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "wip_month" => $post['wip_month'],
                "pp" => $post['pp'],
                "p1" => $post['p1'],
                "p2" => $post['p2'],
                "p3" => $post['p3'],
                "fg" => $post['fg'],
                "os_mpp" => $post['os_mpp'],
                "os_so" => $post['os_so'],
                "total_stock" => $post['total_stock'],
                "balance" => $post['balance'],
                "item_class" => $post['item_class']
            );

            if (count($generateMps) > 0) {
                $send   = $this->crud->update('generate_mps', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    // "customer_id" => $post['customer_id'],
                    "item_fg_id" => $post['item_fg_id'],
                    "wip_month" => $post['wip_month']
                ], $postFinal);
                echo $send;
            } else {
                $send = $this->crud->create('generate_mps', $postFinal);
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/generate_mps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/generate_mps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/generate_mps.txt";

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
            header("Content-Disposition: attachment; filename=generate_mps_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $header = "";
        $headerDetails = "";

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+2 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));
        // $monthNameStart = date('F Y', $monthStart);
        $monthNameStart = date('F Y', strtotime('-1 month', $monthStart));

        $no = 1;
        while ($monthStart < $monthEnd) {
            $monthName = date('F Y', $monthStart);
            $colspan = $no==1?'7':'6';

            $header .= '<th style="text-align:center;" colspan="' . $colspan . '" width="50">' . strtoupper($monthName) . '</th>';
            if($no==1){
                $headerDetails .= ' <th style="text-align:center;" rowspan="2">BALANCE <br> AWAL</th>
                <th style="text-align:center;" rowspan="2">SO</th>
                <th style="text-align:center;" rowspan="2">FC</th>
                <th style="text-align:center;" rowspan="2">SAFETY STOCK <br> WIP</th>
                <th style="text-align:center;" rowspan="2">SAFETY STOCK <br> FG</th>
                <th style="text-align:center;" rowspan="2">NEED</th>
                <th style="text-align:center;" rowspan="2">PROD <br> PLAN '.$no.'</th>';

            }else{

                $headerDetails .= ' <th style="text-align:center;" rowspan="2">BALANCE <br> AWAL</th>
                <th style="text-align:center;" rowspan="2">FC</th>
                <th style="text-align:center;" rowspan="2">SAFETY STOCK <br> WIP</th>
                <th style="text-align:center;" rowspan="2">SAFETY STOCK <br> FG</th>
                <th style="text-align:center;" rowspan="2">NEED</th>
                <th style="text-align:center;" rowspan="2">PROD <br> PLAN '.$no.'</th>';
            }

            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        //Select Full
        $this->db->select('a.*, 
            COALESCE(a.pp + a.p1 + a.p2 + a.p3, 0) as total_wip, 
            e.id as item_fg_id, 
            e.number as item_fg_number, 
            e.name as item_fg_name
        ');
        $this->db->from('generate_mps a');
        $this->db->join('generate_mps_details b', 'a.p_month = b.p_month 
            and a.p_year = b.p_year 
            and a.revision = b.revision 
            and a.item_fg_id = b.item_fg_id','left'
        );
        // $this->db->join('customers c', 'a.customer_id = c.id','left');
        // $this->db->join('customer_items d', 'a.customer_id = d.customer_id 
        //     and a.item_fg_id = d.item_fg_id','left'
        // );
        $this->db->join('item_fg e', 'a.item_fg_id = e.id','left');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('a.p_month', ltrim($filter_month, '0'));
            $this->db->where('a.p_year', $filter_year);
        }
        // $this->db->where('a.customer_id', $customerid);
        $this->db->where("
            (
                a.pp > 0 or a.p1 > 0 or 
                a.p2 > 0 or a.p3 > 0 or 
                a.fg > 0 or a.os_mpp > 0 or 
                a.os_so > 0 or a.total_stock > 0 or 
                a.balance > 0 or b.begin_balance > 0 or 
                b.ito > 0 or b.forecast > 0 or 
                b.delivery_rate > 0 or b.safety_stock > 0 or 
                b.prod_plan > 0
            )
        ");

        if ($filter_revision != "") {
            $this->db->where('a.revision', intval($filter_revision));
        }

        if ($filter_product_no != "") {
            $this->db->where('a.item_fg_id', $filter_product_no);
        }

        $this->db->group_by('a.item_fg_id');
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
        <div style="width:3500px;">
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
            <b style="font-size:18px;">MASTER PRODUCTION SCHEDULES</b>
        </center>
        <p style="font-size:12px; margin:0;">PERIOD ' . $this->monthName($filter_month) . ' ' . $filter_year . '</p>
        <p style="font-size:12px; margin:0;">REVISION ' . $filter_revision . '</p>
        <p style="font-size:12px; margin:0;">PRINT DATE ' . date("d M Y H:m:s") . '</p>
        <p style="font-size:12px; margin:0;">PRINT BY ' . $this->session->username . '</p>
        <br>
        <table id="customers" border="1">';
        $html .= '<tr>
                <th style="text-align:center;" rowspan="3" width="50">No</th>
                <th style="text-align:center;" rowspan="3" width="150">PRODUCT ID</th>
                <th style="text-align:center;" rowspan="3" width="150">PRODUCT NO</th>
                <th style="text-align:center;" rowspan="3" width="150">PRODUCT NAME</th>
                <th style="text-align:center;" rowspan="3" width="50">CLASS</th>
                <th style="text-align:center;" colspan="3">' . strtoupper($monthNameStart) . '</th>
                <th style="text-align:center;" rowspan="3" width="50">TOTAL<br>STOCK</th>
                <th style="text-align:center;" rowspan="3" width="50">OST<br>SO</th>';
        $html .= $header;
        $html .= '<th style="text-align:center;" rowspan="3" width="50">BAL</th></tr>';
        $html .= '<tr>
                <th style="text-align:center;"  rowspan="2" width="50">STOCK WIP</th>
                <th style="text-align:center;"  rowspan="2" width="50">FG</th>
                <th style="text-align:center;"  rowspan="2" width="50">OS MPP</th>';
        $html .= $headerDetails;
        $html .= '</tr><tr>';

        if(!empty($records)){

            $no = 1;
            
            // $cutoff = $this->cutOff($filter_month, $filter_year);
            // $cutoff_to= "$filter_year-$filter_month-01";
            // $isGeneratedDate = $this->isGenerateDate($filter_month, $filter_year);

            foreach ($records as $data) {

                // $os_so = $this->crud->query("SELECT sum(outstanding) as so 
                //     FROM sales_orders 
                //     -- WHERE delivery_date BETWEEN '$cutoff' AND '$cutoff_to'
                //     WHERE delivery_date >= '".$isGeneratedDate."'
                //     AND delivery_date < '".$cutoff_to."' 
                //     -- AND customer_id = '".$data['customer_id']."' 
                //     AND item_fg_id = '".$data['item_fg_id']."' 
                //     -- AND delivery = '0' 
                //     GROUP BY item_fg_id
                // ");

                // $so_so_qty = 0;
                // if(!empty($os_so)){
                //     $so_so_qty = floatval($os_so[0]->so);
                // }

                // $so = $this->crud->query("SELECT sum(qty) as so 
                //     FROM sales_orders 
                //     WHERE delivery_date > '".$cutoff_to."' 
                //     -- AND customer_id = '".$data['customer_id']."' 
                //     AND item_fg_id = '".$data['item_fg_id']."' 
                //     -- AND delivery = '0' 
                //     GROUP BY item_fg_id
                // ");

                // $soOut = 0;
                // if(!empty($so)){
                //     $soOut = $so[0]->so;
                // }

                $totalStock = (intval($data['total_wip']) + $data['fg'] + $data['os_mpp']);

                $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td style="text-align:center;">' . $data['item_class'] . '</td>
                            <td style="text-align:center;">' . $this->format_number($data['total_wip']). '</td>
                            <td style="text-align:center;">' . $this->format_number($data['fg']) . '</td>
                            <td style="text-align:center;">' . $this->format_number($data['os_mpp']) . '</td>
                            <td style="text-align:center;">' . $this->format_number($totalStock) . '</td>
                            <td style="text-align:center;">' . $this->format_number($data['os_so']) . '</td>';

                $this->db->select('
                    MAX(forecast) as forecast,
                    MAX(begin_balance) as begin_balance,
                    MAX(so) as so,
                    MAX(safety_stock_wip) as safety_stock_wip,
                    MAX(safety_stock_fg) as safety_stock_fg,
                    MAX(need) as need,
                    MAX(prod_plan) as prod_plan,
                ');
                $this->db->from('generate_mps_details');
                $this->db->where('p_month', $data['p_month']);
                $this->db->where('p_year', $data['p_year']);
                $this->db->where('revision', $data['revision']);
                $this->db->where('item_fg_id', $data['item_fg_id']);
                $this->db->where('deleted', 0);
                $this->db->group_by('ltpp_month');
                $this->db->order_by('id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;

                foreach ($details2 as $detail2) {
                    if($nodetail==1){
                        $html .= '<td style="text-align:right;">' . $this->format_number($detail2['begin_balance']) . '</td>
                            <td style="text-align:center;">' . $this->format_number($detail2['so']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['forecast']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['safety_stock_wip']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['safety_stock_fg']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['need']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['prod_plan']) . '</td>';

                    }else{
                        $html .= '<td style="text-align:right;">' . $this->format_number($detail2['begin_balance']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['forecast']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['safety_stock_wip']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['safety_stock_fg']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['need']) . '</td>
                            <td style="text-align:right;">' . $this->format_number($detail2['prod_plan']) . '</td>';
                    }

                    $nodetail++;
                }
                $html .= '<td style="text-align:center;">' . $this->format_number($data['balance']) . '</td></tr>';
                $no++;
            }
        }

        $html .= '</tr></table></div></body></html>';
        echo $html;
    }
}
