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
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $cutoff= "$filter_year-$filter_month-01";

            //Configuration Planning
            $this->db->select('*');
            $this->db->from("config");
            $config = $this->db->get()->row();

            //Select Query
            $this->db->select('a.id, a.number, a.name, a.leadtime, b.customer_id,
            COALESCE(c.pp, 0) as pp, 
            COALESCE(c.p1, 0) as p1, 
            COALESCE(c.p2, 0) as p2, 
            COALESCE(c.p3, 0) as p3,
            COALESCE(c.pp + c.p1 + c.p2 + c.p3, 0) as total_wip, 
            COALESCE(d.qty, 0) as fg, 
            COALESCE(e.qty, 0) as os_mpp, 
            COALESCE(f.qty, 0) as os_so');
            $this->db->from('item_fg a');
            $this->db->join('customer_items b', 'a.id = b.item_fg_id');
            $this->db->join('stock_wip c', "a.id = c.item_fg_id and c.p_month = '" . $filter_month . "' and c.p_year = '" . $filter_year . "' and c.revision = '" . intval($filter_revision) . "'", "left");
            $this->db->join('stock_fg d', "a.id = d.item_fg_id and d.p_month = '" . $filter_month . "' and d.p_year = '" . $filter_year . "' and d.revision = '" . intval($filter_revision) . "'", "left");
            $this->db->join('os_mpp e', "a.id = e.item_fg_id and b.customer_id = e.customer_id and e.p_month = '" . $filter_month . "' and e.p_year = '" . $filter_year . "' and e.revision = '" . intval($filter_revision) . "'", "left");
            $this->db->join('os_so f', "a.id = f.item_fg_id and b.customer_id = f.customer_id and f.p_month = '" . $filter_month . "' and f.p_year = '" . $filter_year . "' and f.revision = '" . intval($filter_revision) . "'", "left");
            if ($filter_customer != "") {
                $this->db->where('b.customer_id', $filter_customer);
            }
            if ($filter_product_no != "") {
                $this->db->where('a.id', $filter_product_no);
            }
            $this->db->group_by('b.customer_id');
            $this->db->group_by('b.item_fg_id');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            foreach ($records as $data) {
                
                $itmid = $data['id'];
                $custid = $data['customer_id'];
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

                $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
                $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

                $currentDate = new DateTime("$filter_year-$filter_month-01");
                $previousMonths = [];
                $forecastMonth1 = [];
                
                for ($m = 0; $m < 3; $m++) {
                    $date = clone $currentDate;
                    $date->modify("-$m month");
                    $previousMonths[] = $date->format('Y-m');
                }
                // $os_so = $this->crud->read('os_so', [], ["customer_id" => $data['customer_id'],"item_fg_id" => $data['id'],"p_month" => $filter_month,"p_year" => $filter_year,"revision" => intval($filter_revision)]);
                // $so_so_qty = 0;
                // if(!empty($os_so)){
                //     $so_so_qty = floatval($os_so->qty);
                // }
                if ($data['fg'] !== null) {
                    $fg = intval($data['fg']);
                }
                $os_so = $this->crud->query("SELECT sum(outstanding) as so FROM sales_orders WHERE delivery_date < '".$cutoff."' and customer_id = '".$custid."' and item_fg_id = '".$itmid."' and delivery = '0' group by item_fg_id");
                $so_so_qty = 0;
                if(!empty($os_so)){
                    $so_so_qty = floatval($os_so[0]->so);
                }
                //$so = $this->crud->read('sales_orders', [], ["delivery_date >" => $cutoff, "customer_id" => $custid, "item_fg_id" => $itmid, "delivery"=>"0"]);
                $so = $this->crud->query("SELECT sum(outstanding) as so FROM sales_orders WHERE delivery_date > '".$cutoff."' and customer_id = '".$custid."' and item_fg_id = '".$itmid."' and delivery = '0' group by item_fg_id");
                if(!empty($so)){
                    $soOut = $so[0]->so;
                }
                $totalStock = (intval($data['total_wip']) + intval($data['fg']) + intval($data['os_mpp']));


                foreach($previousMonths as $fcmonth){
                    $queryMonth1=$this->db->query("SELECT p_year, p_month, month_1 FROM forecasts WHERE customer_id = '$custid' and item_fg_id='$itmid' and CONCAT(p_year, '-', p_month) = '$fcmonth'");
                    $resultMonth = $queryMonth1->row();
                    $forecastMonth1[] = empty($resultMonth) ? 0 : $resultMonth->month_1;
                }
                $product_class = $this->get_final_classification($forecastMonth1);
                $persentase = ($product_class === "FM")?10:6;

                //Cek Forecasts
                $max_rev = $this->db->select_max('revision')->where('customer_id', $custid)->where('item_fg_id', $itmid)->where('p_month', $filter_month)->where('p_year', $filter_year)->get('forecasts')->row()->revision;  
                $forecast = $this->crud->read('forecasts', [], ["item_fg_id" => $itmid, "customer_id" => $custid, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => intval($max_rev)]);
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

                    //Bulan Pertama - Keenam
                    if ($i == 1) {
                        $beginBalance = $totalStock - floatval($so_so_qty);
                        $forecastData = @round($forecast->month_1);
                        $deliveryRate = @round($forecastData / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($forecast->month_2));
                        $safetyStockWIP = (20 / 100) * @round($forecast->month_2);
                        $safetyStockFG = ($persentase / 100) * @round($forecast->month_2);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } else if ($i == 2) {
                        $forecastData = @round($forecast->month_2);
                        $deliveryRate = @round($forecastData / $hkw);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($forecast->month_3));
                        $safetyStockWIP = (20 / 100) * @round($forecast->month_3);
                        $safetyStockFG = ($persentase / 100) * @round($forecast->month_3);
                        $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $ito = @round($beginBalance / $deliveryRate);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } elseif ($i == 3) {
                        $forecastData = @round($forecast->month_3);
                        $deliveryRate = @round($forecastData / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($forecast->month_4));
                        $safetyStockWIP = (20 / 100) * @round($forecast->month_4);
                        $safetyStockFG = ($persentase / 100) * @round($forecast->month_4);
                        $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } elseif ($i == 4) {
                        $forecastData = @round($forecast->month_4);
                        $deliveryRate = @round($forecastData / $hkw);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($forecast->month_5));
                        $safetyStockWIP = (20 / 100) * @round($forecast->month_5);
                        $safetyStockFG = ($persentase / 100) * @round($forecast->month_5);
                        $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $ito = @round($beginBalance / $deliveryRate);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } elseif ($i == 5) {
                        $forecastData = @round($forecast->month_5);
                        $deliveryRate = @round($forecastData / $hkw);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($forecast->month_6));
                        $safetyStockWIP = (20 / 100) * @round($forecast->month_6);
                        $safetyStockFG = ($persentase / 100) * @round($forecast->month_6);
                        $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $ito = @round($beginBalance / $deliveryRate);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    } elseif ($i == 6) {
                        $forecastData = @round($forecast->month_6);
                        $deliveryRate = @round($forecastData / $hkw);
                        $safetyStock = @round(((@$data['leadtime'] + $config->fg_ss) / $hkw2) * @round($forecast->month_6));
                        $safetyStockWIP = (20 / 100) * @round($forecast->month_6);
                        $safetyStockFG = ($persentase / 100) * @round($forecast->month_6);
                        $beginBalance = $safetyStockWIP + $safetyStockFG;
                        $ito = @round($beginBalance / $deliveryRate);
                        $need = (($forecastData+$safetyStockWIP+$safetyStockFG) - $beginBalance);
                        $prodPlan = ($need < 0) ? 0 : $need;
                        // $prodPlan = @round(($forecastData + $safetyStock) - $beginBalance);
                    }

                    $arrMonth[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "customer_id" => $custid,
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
                    $i++;

                    // $beginBalance = $beginBalance;
                    // $forecast = $forecastData;
                    // $deliveryRate = $deliveryRate;
                    // $ito = $ito;
                    // $safetyStock = $safetyStock;
                    // $prodPlan = $prodPlanFinal;
                    // $balance = (($prodPlan + $beginBalance) - $forecast);
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "customer_id" => $custid,
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

    public function checkFg()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('stock_fg');
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

    public function checkOs()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $cutoff= "$filter_year-$filter_month-01";
        $this->db->select('*');
        $this->db->from('sales_orders');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" && $filter_year != "") {
            $this->db->where('delivery_date > ', $cutoff);
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
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $cutoff= "$filter_year-$filter_month-01";
        $this->db->select('*');
        $this->db->from('sales_orders');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" && $filter_year != "") {
            $this->db->where('delivery_date < ', $cutoff);
        }
        $this->db->where('delivery', '0');
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

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

        $html = "";
        $no = 1;
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
                    "customer_id" => $postDetail['customer_id'],
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
                        "customer_id" => $postDetail['customer_id'],
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
                "customer_id" => $post['customer_id'],
                "item_fg_id" => $post['item_fg_id'],
                "wip_month" => $post['wip_month']
            ]);

            $postFinal = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "customer_id" => $post['customer_id'],
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
                    "customer_id" => $post['customer_id'],
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
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        //Select Customer
        $this->db->select('a.*, b.name as customer_name');
        $this->db->from('generate_mps a');
        $this->db->join('customers b', 'a.customer_id = b.id','left');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('a.p_month', ltrim($filter_month, '0'));
            $this->db->where('a.p_year', $filter_year);
        }
        if ($filter_customer != "") {
        $this->db->where('a.customer_id', $filter_customer);
        }
        if ($filter_product_no != "") {
        $this->db->where('a.item_fg_id', $filter_product_no);
        }
        $this->db->where('a.revision', intval($filter_revision));
        $this->db->group_by('a.customer_id');
        $this->db->order_by('b.name', 'asc');
        $customers = $this->db->get()->result_array();

        $header = "";
        $headerDetails = "";

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));
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

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
            if(!empty($customers)){

        foreach ($customers as $customer) {
            // if ($customer['customer_name'] == "") {
            //     $customer_name = "No Customer";
            // } else {
                $customer_name = $customer['customer_name'];
                $customerid = $customer['customer_id'];
                $html .= '<tr>
                        <th colspan="47" style="text-align:left;"><b>' . $customer_name . '</b></th>
                    </tr>';
            // }

            //Select Full
            $this->db->select('a.*, COALESCE(a.pp + a.p1 + a.p2 + a.p3, 0) as total_wip, c.name as customer_name, e.number as item_fg_number, e.name as item_fg_name');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_details b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id','left');
            $this->db->join('customers c', 'a.customer_id = c.id','left');
            $this->db->join('customer_items d', 'a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id','left');
            $this->db->join('item_fg e', 'a.item_fg_id = e.id','left');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', ltrim($filter_month, '0'));
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->where('a.customer_id', $customerid);
            $this->db->where("(a.pp > 0 or a.p1 > 0 or a.p2 > 0 or a.p3 > 0 or a.fg > 0 or a.os_mpp > 0 or a.os_so > 0 or a.total_stock > 0 or a.balance > 0 or b.begin_balance > 0 or b.ito > 0 or b.forecast > 0 or b.delivery_rate > 0 or b.safety_stock > 0 or b.prod_plan > 0)");
            if ($filter_revision != "") {
            $this->db->where('a.revision', intval($filter_revision));
            }
            if ($filter_product_no != "") {
            $this->db->where('a.item_fg_id', $filter_product_no);
            }
            $this->db->group_by('a.item_fg_id');
            $records = $this->db->get()->result_array();

            $no = 1;
            $cutoff= "$filter_year-$filter_month-01";//$filter_year+'-'+$filter_month+'-01';
            $prevPersentase = 6;
            foreach ($records as $data) {
                        
                //baru
       
        $custid=$data['customer_id'];
        $itmid=$data['item_fg_id'];
        
        
        //$os_so = $this->crud->read('os_so', [], ["customer_id" => $data['customer_id'],"item_fg_id" => $data['item_fg_id'],"p_month" => $data['p_month'],"p_year" => $data['p_year'],"revision" => intval($data['revision'])]);
        $os_so = $this->crud->query("SELECT sum(outstanding) as so FROM sales_orders WHERE delivery_date < '".$cutoff."' and customer_id = '".$data['customer_id']."' and item_fg_id = '".$data['item_fg_id']."' and delivery = '0' group by item_fg_id");
        $so_so_qty = 0;
        if(!empty($os_so)){
            $so_so_qty = floatval($os_so[0]->so);
        }
        $so = $this->crud->query("SELECT sum(outstanding) as so FROM sales_orders WHERE delivery_date > '".$cutoff."' and customer_id = '".$data['customer_id']."' and item_fg_id = '".$data['item_fg_id']."' and delivery = '0' group by item_fg_id");
        $soOut = 0;
        if(!empty($so)){
            $soOut = $so[0]->so;
        }
        $totalStock = (intval($data['total_wip']) + $data['fg'] + $data['os_mpp']);
                $balanceAwal = (intval($totalStock) -  $so_so_qty);
                
        //baru
                $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td style="text-align:center;">' . $data['item_class'] . '</td>
                            <td style="text-align:center;">' . $this->format_number($data['total_wip']). '</td>
                            <td style="text-align:center;">' . $this->format_number($data['fg']) . '</td>
                            <td style="text-align:center;">' . $this->format_number($data['os_mpp']) . '</td>
                            <td style="text-align:center;">' . $this->format_number($totalStock) . '</td>
                            <td style="text-align:center;">' . $this->format_number($so_so_qty) . '</td>';

                $this->db->select('a.*, b.qty');
                $this->db->from('generate_mps_details a');
                $this->db->join('stock_so b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id', 'left');
                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                // $this->db->where('a.line_no', $data['line_no']);
                // $this->db->where('a.product_no', $data['product_no']);
                $this->db->where('a.deleted', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                foreach ($details2 as $detail2) {
                    //baru
          
        $max_rev = $this->db->select_max('revision')->where('customer_id', $data['customer_id'])->where('item_fg_id', $data['item_fg_id'])->where('p_month', $filter_month)->where('p_year', $filter_year)->get('forecasts')->row()->revision;           
        $forecasts = $this->crud->read("forecasts", [], ["customer_id" => $data['customer_id'], "item_fg_id" => $data['item_fg_id'], "p_month" => $filter_month, "p_year" => $filter_year, "revision" => intval($max_rev)]);

        $forecastValue = 0;
        $fcNextMonth = 0;
        $prevSSWIP = 0;
        $prevSSFG = 0;
        $persentase = ($data['item_class'] === "FM")?10:6;
        if(!empty($forecasts)){
            $forecastValue = $forecasts->month_1;
            $fcNextMonth = $forecasts->month_2;
            if($nodetail==2){
                $forecastValue = $forecasts->month_2;
                $fcNextMonth = $forecasts->month_3;
                $prevSSWIP = (20 / 100) * $forecasts->month_2;
                $prevSSFG = ($prevPersentase / 100) * $forecasts->month_2;
            }
            if($nodetail==3){
                $forecastValue = $forecasts->month_3;
                $fcNextMonth = $forecasts->month_4;
                $prevSSWIP = (20 / 100) * $forecasts->month_3;
                $prevSSFG = ($prevPersentase / 100) * $forecasts->month_3;
            }
            if($nodetail==4){
                $forecastValue = $forecasts->month_4;
                $fcNextMonth = $forecasts->month_5;
                $prevSSWIP = (20 / 100) * $forecasts->month_4;
                $prevSSFG = ($prevPersentase / 100) * $forecasts->month_4;
            }
            if($nodetail==5){
                $forecastValue = $forecasts->month_5;
                $fcNextMonth = $forecasts->month_6;
                $prevSSWIP = (20 / 100) * $forecasts->month_5;
                $prevSSFG = ($prevPersentase / 100) * $forecasts->month_5;
            }
            if($nodetail==6){
                $forecastValue = $forecasts->month_6;
                $fcNextMonth = $forecasts->month_7;
                $prevSSWIP = (20 / 100) * $forecasts->month_6;
                $prevSSFG = ($prevPersentase / 100) * $forecasts->month_6;
            }

        }
        
                    $safetyStockWIP = (20 / 100) * $fcNextMonth;
                    $safetyStockFG = ($persentase / 100) * $fcNextMonth;
                    
                    if($nodetail>1){
                        $balanceAwal = $prevSSWIP + $prevSSFG;
                    }
                    $need = (($forecastValue+$safetyStockWIP+$safetyStockFG) - $balanceAwal);
                    $prodPlan = $need<0?0:$need;
                    $prevPersentase = $persentase;
                    if($nodetail==1){
                        $html .= '<td style="text-align:right;">' . $this->format_number($balanceAwal) . '</td>
                                <td style="text-align:right;">' . $this->format_number($soOut) . '</td>
                                <td style="text-align:right;">' . $this->format_number($forecastValue) . '</td>
                                <td style="text-align:right;">' . $this->format_number($safetyStockWIP) . '</td>
                                <td style="text-align:right;">' . $this->format_number($safetyStockFG) . '</td>
                                <td style="text-align:right;">' . $this->format_number($need) . '</td>
                                <td style="text-align:right;">' . $this->format_number($prodPlan) . '</td>';

                    }else{
                        
                    $html .= '<td style="text-align:right;">' . $this->format_number($balanceAwal) . '</td>
                    <td style="text-align:right;">' . $this->format_number($forecastValue) . '</td>
                    <td style="text-align:right;">' . $this->format_number($safetyStockWIP) . '</td>
                    <td style="text-align:right;">' . $this->format_number($safetyStockFG) . '</td>
                    <td style="text-align:right;">' . $this->format_number($need) . '</td>
                    <td style="text-align:right;">' . $this->format_number($prodPlan) . '</td>';
                    }

                    //baru
                    // if($nodetail==1){
                    //     $html .= '  <td style="text-align:center;">' . $detail2['begin_balance'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['so'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['forecast'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['safety_stock_wip'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['safety_stock_fg'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['need'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['prod_plan'] . '</td>';
                    // }else{
                    //     $html .= '  <td style="text-align:center;">' . $detail2['begin_balance'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['forecast'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['safety_stock_wip'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['safety_stock_fg'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['need'] . '</td>
                    //     <td style="text-align:center;">' . $detail2['prod_plan'] . '</td>';
                    // }
                    $nodetail++;
                }
                $html .= '<td style="text-align:center;">' . $this->format_number($data['balance']) . '</td>
            </tr>';
                $no++;
            }
        }
    }

        $html .= '</tr></table></div></body></html>';
        echo $html;
    }
}
