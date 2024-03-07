<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mps extends CI_Controller
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

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mps');
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
            $period = $filter_year . "-" . $filter_month;

            //Configuration Planning
            $this->db->select('*');
            $this->db->from("config");
            $config = $this->db->get()->row();

            //Select Query
            $this->db->select('a.id, a.number, a.name, a.leadtime, b.customer_id,
                COALESCE(c.pp, 0) as pp,  
                COALESCE(c.p3, 0) as p3,
                COALESCE(c.pp + c.p3, 0) as total_wip, 
                COALESCE(d.qty, 0) as fg, 
                COALESCE(a.safety_stock, 0) as safetystockfg, 
                COALESCE(e.qty, 0) as os_mpp, 
                COALESCE(SUM(f.qty), 0) as os_so');
            $this->db->from('item_fg a');
            $this->db->join('customer_items b', 'a.id = b.item_fg_id');
            $this->db->join('stock_wip c', "a.id = c.item_fg_id and c.p_month = '" . $filter_month . "' and c.p_year = '" . $filter_year . "' and c.revision = '" . $filter_revision . "'", "left");
            $this->db->join('stock_fg d', "a.id = d.item_fg_id and d.p_month = '" . $filter_month . "' and d.p_year = '" . $filter_year . "' and d.revision = '" . $filter_revision . "'", "left");
            $this->db->join('os_mpp e', "a.id = e.item_fg_id and b.customer_id = e.customer_id and e.p_month = '" . $filter_month . "' and e.p_year = '" . $filter_year . "' and e.revision = '" . $filter_revision . "'", "left");
            $this->db->join('sales_orders f', "a.id = f.item_fg_id and b.customer_id = f.customer_id and YEAR(f.sales_order_date) = '" . $filter_year . "' and MONTH(f.sales_order_date) = '" . $filter_month . "'", "left"); // Membandingkan tahun dan bulan terpisah dari tanggal
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
                $totalStock = ($data['total_wip'] + $data['fg'] + $data['os_mpp']);
                if ($data['fg'] == null) {
                    $fg = "0";
                } else {
                    $fg = $data['fg'];
                }

                $i = 1;
                $beginBalance = 0;
                $forecast = 0;
                // $deliveryRate = 0;
                $os_so_qty = 0;
                $safetyStock = 0;
                $prodPlan = 0;
                $arrMonth = array();

                $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
                $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

                
                //Cek Forecasts
                $forecastread = $this->crud->read('forecasts', [], ["item_fg_id" => $data['id'], "customer_id" => $data['customer_id'], "p_month" => $filter_month, "p_year" => $filter_year]);

                while ($monthStart < $monthEnd) {
                    $monthName = date('F Y', $monthStart);
                    $monthName2 = date('Y-m-01', $monthStart);
                    // $monthsPlus = strtolower(date('F', strtotime('+1 month', $monthStart)));
                    $start = strtotime(date('Y-m-01', $monthStart));
                    $finish = strtotime(date('Y-m-t', $monthStart));

                    $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
                    $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));


                    //Cek sales orders
                    $os_so_qty =$this->db->select_sum('qty'); 
                                $this->db->from('sales_orders');
                                $this->db->where('item_fg_id', $data['id']);
                                $this->db->where('YEAR(sales_order_date)', $filter_year);
                                $this->db->where('MONTH(sales_order_date)', $filter_month);
                                
                    
                    $result = $this->db->get()->row();
                    $os_so_qty = $result->qty;

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
                        $beginBalance = $totalStock - $os_so_qty;
                        $forecastData = @round($forecastread->month_1);
                        // $deliveryRate = @round($forecastData / $hkw);
                        // $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round($forecastread->month_2 * ($data['safetystockfg'] / 100));
                        $prodPlan = @round($beginBalance - ($forecastData + $safetyStock));
                    } else if ($i == 2) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecastData = @round($forecastread->month_2);
                        // $deliveryRate = @round($forecastData / $hkw);
                        // $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round($forecastread->month_3 * ($data['safetystockfg'] / 100));
                        $prodPlan = @round($beginBalance - ($forecastData + $safetyStock));
                    } elseif ($i == 3) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecastData = @round($forecastread->month_3);
                        // $deliveryRate = @round($forecastData / $hkw);
                        // $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round($forecastread->month_4 * ($data['safetystockfg'] / 100));
                        $prodPlan = @round($beginBalance - ($forecastData + $safetyStock));
                    } elseif ($i == 4) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecastData = @round($forecastread->month_4);
                        // $deliveryRate = @round($forecastData / $hkw);
                        // $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round($forecastread->month_5 * ($data['safetystockfg'] / 100));
                        $prodPlan = @round($beginBalance - ($forecastData + $safetyStock));
                    } elseif ($i == 5) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecastData = @round($forecastread->month_5);
                        // $deliveryRate = @round($forecastData / $hkw);
                        // $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round($forecastread->month_6 * ($data['safetystockfg'] / 100));
                        $prodPlan = @round($beginBalance - ($forecastData + $safetyStock));
                    } elseif ($i == 6) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecastData = @round($forecastread->month_6);
                        // $deliveryRate = @round($forecastData / $hkw);
                        // $ito = @round($beginBalance / $deliveryRate);
                        $safetyStock = @round($forecastread->month_6 * ($data['safetystockfg'] / 100));
                        $prodPlan = @round($beginBalance - ($forecastData + $safetyStock));
                    }

                    if ($prodPlan <= 0) {
                        $prodPlanFinal = 0;
                    } else {
                        $prodPlanFinal = $prodPlan;
                    }

                    $arrMonth[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "customer_id" => $data['customer_id'],
                        "item_fg_id" => $data['id'],
                        "ltpp_month" => strtoupper($monthName),
                        "ltpp_month2" => $monthName2,
                        "hkw" => "$hkw2",
                        "begin_balance" => "$beginBalance",
                        "os_so" => $os_so_qty,
                        // "ito" => "$ito",
                        "forecast" => $forecastData,
                        // "delivery_rate" => "$deliveryRate",
                        "safety_stock" => "$safetyStock",
                        "prod_plan" => "$prodPlanFinal"

                    );

                    $monthStart = strtotime("+1 month", $monthStart);
                    $i++;

                    $beginBalance = $beginBalance;
                    $forecast = $forecastData;
                    // $deliveryRate = $deliveryRate;
                    // $ito = $ito;
                    $safetyStock = $safetyStock;
                    $prodPlan = $prodPlanFinal;
                    $balance = (($prodPlan + $beginBalance) - $forecast);
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "customer_id" => $data['customer_id'],
                    "item_fg_id" => $data['id'],
                    "wip_month" => strtoupper($monthBack),
                    "pp" => $data['pp'],
                    "p3" => $data['p3'],
                    "fg" => $fg,
                    "os_mpp" => $data['os_mpp'],
                    "total_stock" => "$totalStock",
                    "os_so" => $data['os_so'],
                    "balance" => "$balance",
                    "details" => $arrMonth
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
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
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('forecasts');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        // $this->db->like('revision', $filter_revision);
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
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOs()//sales order
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_orders');
        //$this->db->where('approved_to', '');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('MONTH(sales_order_date)', $filter_month);
            $this->db->where('YEAR(sales_order_date)', $filter_year);
        }
        // $this->db->like('revision', $filter_revision);
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
        $this->db->like('revision', $filter_revision);
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
        $this->db->like('revision', $filter_revision);
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
                    "revision" => $postDetail['revision'],
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
                    "os_so" => $postDetail['os_so'],
                    // "ito" => $postDetail['ito'],
                    "forecast" => $postDetail['forecast'],
                    // "delivery_rate" => $postDetail['delivery_rate'],
                    "safety_stock" => $postDetail['safety_stock'],
                    "need" => $postDetail['prod_plan'],
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
                "revision" => $post['revision'],
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
                "p3" => $post['p3'],
                "fg" => $post['fg'],
                "os_mpp" => $post['os_mpp'],
                "os_so" => $post['os_so'],
                "total_stock" => $post['total_stock'],
                "balance" => $post['balance']
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
        $this->db->select('a.customer_id, b.name as customer_name');
        $this->db->from('generate_mps a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
        }
        $this->db->where('a.balance >', 0);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.item_fg_id', $filter_product_no);
        $this->db->group_by('a.customer_id');
        $this->db->order_by('b.name', 'asc');
        $customers = $this->db->get()->result_array();

        $header = "";
        $headerDetails = "";

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));
        $monthNameStart = date('F Y', $monthStart);

        $no = 1;
        while ($monthStart < $monthEnd) {
            $monthName = date('F Y', $monthStart);

            if ($no == 1) {
                // $xbar = '<th style="text-align:center;" rowspan="2">XBAR</th>';
                $colspan = '6';
            } else {
                // $xbar = "";
                $colspan = '6';
            }

            $header .= '<th style="text-align:center;" colspan="' . $colspan . '" width="50">' . strtoupper($monthName) . '</th>';
            $headerDetails .= ' <th style="text-align:center;" rowspan="2">BALANCE <br> AWAL</th>
                                    <th style="text-align:center;" rowspan="2">SALES <br> ORDER</th>
                                    <th style="text-align:center;" rowspan="2">FC</th>
                                    <th style="text-align:center;" rowspan="2">SAFETY <br> STOCK</th>
                                    <th style="text-align:center;" rowspan="2">NEED</th>
                                    <th style="text-align:center;" rowspan="2">PROD <br> PLAN</th>';
            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <div style="width:3500px;">
        <table style="width: 100%;">
            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                <img src="' . $config->logo . '" width="30">
            </td>
            <td style="font-size: 14px; text-align: left; margin:2px;">
                <b>' . $config->name . '</b><br>
                <small>PPIC DEPARTEMENT</small>
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
                <th style="text-align:center;" rowspan="3" width="50">NO</th>
                <th style="text-align:center;" rowspan="3" width="150">PRODUCT NO</th>
                <th style="text-align:center;" rowspan="3" width="150">PRODUCT NAME</th>
                
                <th style="text-align:center;" colspan="2">' . strtoupper($monthNameStart) . '</th>
                <th style="text-align:center;" rowspan="3" width="50">FG</th>
                <th style="text-align:center;" rowspan="3" width="50">OST<br>MPP</th>
                <th style="text-align:center;" rowspan="3" width="50">TOTAL<br>STOCK</th>
                <th style="text-align:center;" rowspan="3" width="50">OST<br>SO</th>';
                
        $html .= $header;
        
        $html .= '
                <th style="text-align:center;" rowspan="3" width="50">BAL</th>
            </tr>
            <tr>
                <th style="text-align:center;" colspan="2">STOCK</th>';

        $html .= $headerDetails;
        $html .= '</tr>

        
            <tr>
                <th style="text-align:center;" width="50">STOCK<br>WIP</th>
                <th style="text-align:center;" width="50">SUBCONT</th>
            </tr>';

        foreach ($customers as $customer) {
            if ($customer['customer_name'] == "") {
                $customer_name = "No Customer";
            } else {
                $customer_name = $customer['customer_name'];
            }

            $html .= '  <tr>
                            <th colspan="100" style="text-align:left;"><b>' . $customer_name . '</b></th>
                        </tr>';
            //Select Full
            $this->db->select('a.*, c.name as customer_name, e.number as item_fg_number, e.name as item_fg_name');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_details b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('customer_items d', 'a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id');
            $this->db->join('item_fg e', 'a.item_fg_id = e.id');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->where('a.customer_id', $customer['customer_id']);                        //a.os_so field di table generate_mps
            $this->db->where("(a.pp > 0 or a.p3 > 0 or a.fg > 0 or a.os_mpp > 0 or a.os_so > 0 or a.total_stock > 0 or a.balance > 0 or b.begin_balance > 0 or b.forecast > 0 or b.safety_stock > 0 or b.prod_plan > 0 or b.os_so > 0)");
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.item_fg_id', $filter_product_no);
            $this->db->group_by('a.item_fg_id');
            $records = $this->db->get()->result_array();

            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>

                            <td style="text-align:center;">' . $data['pp'] . '</td>
                            <td style="text-align:center;">' . $data['p3'] . '</td>
                            
                            <td style="text-align:center;">' . $data['fg'] . '</td>
                            <td style="text-align:center;">' . $data['os_mpp'] . '</td>
                            <td style="text-align:center;">' . $data['total_stock'] . '</td>
                            <td style="text-align:center;">' . $data['os_so'] . '</td>';

                $this->db->select('a.*, b.qty');
                $this->db->from('generate_mps_details a');
                $this->db->join('stock_so b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.item_fg_id = b.item_fg_id', 'left');                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                $this->db->where('a.customer_id', $data['customer_id']);
                $this->db->where('a.item_fg_id', $data['item_fg_id']);
                $this->db->where('a.deleted', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                foreach ($details2 as $detail2) {
                    // if ($nodetail == 1) {
                    //     if ($detail2['qty'] == "") {
                    //         $xbarQty = 0;
                    //     } else {
                    //         $xbarQty = $detail2['qty'];
                    //     }
                    //     $xbar2 = '<td style="text-align:center;">' . $xbarQty . '</td>';
                    // } else {
                    //     $xbar2 = "";
                    // }

                    $html .= '  <td style="text-align:center;">' . $detail2['begin_balance'] . '</td>
                            <td style="text-align:center;">' . $detail2['os_so'] . '</td>
                            <td style="text-align:center;">' . $detail2['forecast'] . '</td>
                            <td style="text-align:center;">' . $detail2['safety_stock'] . '</td>
                            <td style="text-align:center;">' . $detail2['need'] . '</td>
                            <td style="text-align:center;">' . $detail2['prod_plan'] . '</td>';
                    $nodetail++;
                }
                $html .= '<td style="text-align:center;">' . $data['balance'] . '</td>
            </tr>';
                $no++;
            }
        }

        $html .= '</table></div></body></html>';
        echo $html;
    }
}
