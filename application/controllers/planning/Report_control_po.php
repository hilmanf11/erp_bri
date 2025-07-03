<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_control_po extends CI_Controller
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

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/report_control_po');
        } else {
            redirect('error_access');
        }
    }

    private function format_number($input) {
        if($input==null){
            $input=0;
        }
        $numeric_value = str_replace(',', '', $input);
        return number_format($numeric_value, 0, '.', '.');
    }
    private function format_float($input){
        $formattedAmount = str_replace('.', ',', $input); // Replace dot with comma
        return $formattedAmount; // Output: 157,75
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
            // $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            // $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            // for ($i = $year_now; $i >= $year_before; $i--) {
            //     $years[] = array("id" => $i, "name" => $i);
            // }
            $years = array(
                array("id" => "2024", "name" => "2024"),
                array("id" => "2025", "name" => "2025")
            );

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_period_year = base64_decode($this->input->get("filter_period_year"));
            $filter_period_month = base64_decode($this->input->get("filter_period_month"));
            $filter_customer_name = $this->input->get("filter_customer_name");
            $filter_item_fg = $this->input->get("filter_item_fg");
            $filter_product_family = base64_decode($this->input->get("filter_product_family"));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*, b.number as customer_number, b.name as customer_name, c.number as product_no, c.name as product_name');

            $this->db->select('(
                SUM(CASE 
                    WHEN (
                        (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                        OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ')
                    )
                    AND (s.closing_reason IS NULL OR s.closing_reason = "")
                    AND (s.status = 0 OR s.status = 2)
                    THEN s.outstanding 
                    ELSE 0 
                END)
            ) as ost_so', false);

            // $this->db->select('(
            //     SUM(CASE 
            //         WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
            //             OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
            //         THEN s.qty ELSE 0 END)
            //     - 
            //     COALESCE((SELECT SUM(d.qty) 
            //               FROM delivery_reports d 
            //               WHERE d.customer_id = a.customer_id 
            //                 AND d.item_fg_id = a.item_fg_id 
            //                 AND (YEAR(d.delivery_report_date) < ' . $filter_period_year . ' 
            //                     OR (YEAR(d.delivery_report_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_report_date) < ' . $filter_period_month . '))), 0)
            // ) as ost_so', false); 

            $this->db->select('SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) as so_m0', false);

            $this->db->select('(
                (
                    SUM(CASE 
                        WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                            OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                        THEN s.qty ELSE 0 END)
                    - 
                    COALESCE((SELECT SUM(d.qty_so) 
                              FROM delivery_orders d 
                              WHERE d.customer_id = a.customer_id 
                                AND d.item_fg_id = a.item_fg_id 
                                AND d.status = 1
                                AND (
                                    YEAR(d.delivery_order_date) < ' . $filter_period_year . ' 
                                    OR (YEAR(d.delivery_order_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_order_date) < ' . $filter_period_month . ')
                                )
                            ), 0)
                )
                +
                SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END)
            ) as total_so', false);

            // $this->db->select('(
            //     SELECT COALESCE(SUM(sod.qty), 0)
            //         FROM sales_order_deliveries sod
            //     WHERE sod.customer_id = s.customer_id
            //         AND sod.item_fg_id = s.item_fg_id
            //         AND (
            //             YEAR(sod.trans_date) < ' . $filter_period_year . ' 
            //             OR (
            //                 YEAR(sod.trans_date) = ' . $filter_period_year . ' 
            //                 AND MONTH(sod.trans_date) <= ' . $filter_period_month . '
            //             )
            //         )
            //         AND (s.closing_reason IS NULL OR s.closing_reason = "")
            // ) as delivery_schedule', false);

            $this->db->select('(
                SELECT 
                    CASE 
                        WHEN s.closing_reason IS NOT NULL AND TRIM(s.closing_reason) <> "" THEN 
                            LEAST(COALESCE(SUM(sod.qty), 0), s.delivery)
                        ELSE 
                            COALESCE(SUM(sod.qty), 0)
                    END
                FROM sales_order_deliveries sod
                WHERE sod.customer_id = s.customer_id
                    AND sod.item_fg_id = s.item_fg_id
                    AND (
                        YEAR(sod.trans_date) > ' . $filter_period_year . ' 
                        OR (
                            YEAR(sod.trans_date) = ' . $filter_period_year . ' 
                            AND MONTH(sod.trans_date) >= ' . $filter_period_month . '
                        )
                )
            ) as delivery_schedule', false);


            $this->db->select('(
                SELECT SUM(d.qty_del)
                FROM delivery_orders d
                WHERE d.customer_id = a.customer_id
                  AND d.item_fg_id = a.item_fg_id
                  AND YEAR(d.actual_delivery_date) = ' . $filter_period_year . '
                  AND MONTH(d.actual_delivery_date) = ' . $filter_period_month . '
            ) as delivery', false);

            $this->db->select('(
                COALESCE((SELECT SUM(d.qty) 
                          FROM delivery_reports d 
                          WHERE d.customer_id = a.customer_id 
                            AND d.item_fg_id = a.item_fg_id 
                            AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' 
                            AND MONTH(d.delivery_report_date) = ' . $filter_period_month . '), 0)
                -
                (
                    SUM(CASE 
                        WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                            OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                        THEN s.qty ELSE 0 END)
                    +
                    SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END)
                    -
                    COALESCE((SELECT SUM(d.qty) 
                              FROM delivery_reports d 
                              WHERE d.customer_id = a.customer_id 
                                AND d.item_fg_id = a.item_fg_id 
                                AND (YEAR(d.delivery_report_date) < ' . $filter_period_year . ' 
                                    OR (YEAR(d.delivery_report_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_report_date) < ' . $filter_period_month . '))), 0)
                )
            ) as balance', false);            
                       
            $this->db->select('a.month_1 as forecast', false);

            // $this->db->select('ROUND((CASE 
            // WHEN a.month_1 = 0 THEN 0 ELSE ((SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) - a.month_1) / a.month_1) * 100
            // END), 2) as bal_forecast', false);

            $this->db->select('
                ROUND((
                    CASE 
                        WHEN a.month_1 = 0 THEN 0
                        ELSE ((SUM(CASE 
                            WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' 
                            AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' 
                            THEN s.qty ELSE 0 END) - a.month_1) / a.month_1) * 100
                    END
                ), 2) as bal_forecast
            ', false);


            // $this->db->select('ROUND((CASE 
            // WHEN a.month_1 = 0 AND SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) > 0 THEN 100 ELSE ((SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) - a.month_1) / a.month_1) * 100
            // END), 2) as bal_fc', false);

            $this->db->select('ROUND((
                CASE 
                    WHEN a.month_1 = 0 
                        AND SUM(CASE 
                            WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' 
                                AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' 
                            THEN s.qty ELSE 0 
                        END) > 0 
                    THEN 100 
                    ELSE (
                        (SUM(CASE 
                            WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' 
                                AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' 
                            THEN s.qty ELSE 0 
                        END) - a.month_1) / a.month_1
                    ) * 100 
                END
            ), 2) AS bal_fc', false);


            //$this->db->select('ROUND((CASE WHEN a.month_1 != 0 THEN ((SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) - a.month_1) / a.month_1) * 100 ELSE 100 END), 2) as bal_forecast', false);

            // $this->db->select('((SELECT SUM(d.qty) FROM delivery_reports d WHERE d.customer_id = a.customer_id AND d.item_fg_id = a.item_fg_id AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_report_date) = ' . $filter_period_month . ') * ci.price) as total_sales', false);

            $this->db->select('(
                (SELECT SUM(d.qty) 
                FROM delivery_reports d 
                WHERE d.customer_id = a.customer_id 
                    AND d.item_fg_id = a.item_fg_id 
                    AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' 
                    AND MONTH(d.delivery_report_date) = ' . $filter_period_month . ')
                    * ci.price
            ) as total_sales', false);


            $this->db->select('(
                (
                    COALESCE((SELECT SUM(d.qty) 
                              FROM delivery_reports d 
                              WHERE d.customer_id = a.customer_id 
                                AND d.item_fg_id = a.item_fg_id 
                                AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' 
                                AND MONTH(d.delivery_report_date) = ' . $filter_period_month . '), 0)
                    - 
                    (
                        COALESCE(SUM(CASE 
                            WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                                OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                            THEN s.qty ELSE 0 END), 0)
                        + 
                        COALESCE(SUM(CASE 
                            WHEN (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ') 
                            THEN s.qty ELSE 0 END), 0)
                    )
                ) * ci.price
            ) as bal_sales', false);

            //? Mengambil hanya 1 baris forecast terbaru by customer dan item_fg (revision tertinggi)
            $this->db->from('(SELECT * FROM forecasts f1 
                            WHERE f1.revision = (
                                SELECT MAX(f2.revision) 
                                FROM forecasts f2 
                                WHERE f2.customer_id = f1.customer_id 
                                    AND f2.item_fg_id = f1.item_fg_id 
                                    AND f2.p_month = f1.p_month 
                                    AND f2.p_year = f1.p_year
                            )) a');

            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('customer_items ci', 'a.customer_id = ci.customer_id AND a.item_fg_id = ci.item_fg_id', 'left');
            $this->db->join('sales_orders s', 'a.customer_id = s.customer_id AND a.item_fg_id = s.item_fg_id', 'left');
            $this->db->where('a.p_month', $filter_period_month);
            $this->db->where('a.p_year', $filter_period_year);
            if($filter_customer_name !=""){
                $this->db->where('a.customer_id', $filter_customer_name);
            }
            if($filter_item_fg !=""){
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            if ($filter_product_family != "") {
                $this->db->where('c.item_family_number', $filter_product_family);
            }
            $this->db->order_by('a.created_date', 'DESC');
            $this->db->group_by('a.id, b.number, b.name, c.number, c.name, a.month_1');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1-10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }


    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_control_po_$format.xls");
        }

        $get = $this->input->get();
        $filter_period_year = base64_decode($this->input->get("filter_period_year"));
        $filter_period_month = base64_decode($this->input->get("filter_period_month"));
        $filter_customer_name = $this->input->get("filter_customer_name");
        $filter_item_fg = $this->input->get("filter_item_fg");
        $filter_product_family = base64_decode($this->input->get("filter_product_family"));

       
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, c.number as product_no, c.name as product_name');
        $this->db->select('(
            SUM(CASE 
                WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                    OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                THEN s.qty ELSE 0 END)
            - 
            COALESCE((SELECT SUM(d.qty_so) 
                      FROM delivery_orders d 
                      WHERE d.customer_id = a.customer_id 
                        AND d.item_fg_id = a.item_fg_id 
                        AND d.status = 1
                        AND (
                            YEAR(d.delivery_order_date) < ' . $filter_period_year . ' 
                            OR (YEAR(d.delivery_order_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_order_date) < ' . $filter_period_month . ')
                        )
                    ), 0)
        ) as ost_so', false); 
        
        // $this->db->select('(
        //     SUM(CASE 
        //         WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
        //             OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
        //         THEN s.qty ELSE 0 END)
        //     - 
        //     COALESCE((SELECT SUM(d.qty) 
        //               FROM delivery_reports d 
        //               WHERE d.customer_id = a.customer_id 
        //                 AND d.item_fg_id = a.item_fg_id 
        //                 AND (YEAR(d.delivery_report_date) < ' . $filter_period_year . ' 
        //                     OR (YEAR(d.delivery_report_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_report_date) < ' . $filter_period_month . '))), 0)
        // ) as ost_so', false); 

        $this->db->select('SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) as so_m0', false);
        $this->db->select('(
            (
            SUM(CASE 
                WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                    OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                THEN s.qty ELSE 0 END)
            - 
            COALESCE((SELECT SUM(d.qty_so) 
                      FROM delivery_orders d 
                      WHERE d.customer_id = a.customer_id 
                        AND d.item_fg_id = a.item_fg_id 
                        AND d.status = 1
                        AND (
                            YEAR(d.delivery_order_date) < ' . $filter_period_year . ' 
                            OR (YEAR(d.delivery_order_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_order_date) < ' . $filter_period_month . ')
                        )
                    ), 0)
            )
            +
            SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END)
        ) as total_so', false);
        
        
        $this->db->select('(
            SELECT SUM(d.qty_del)
            FROM delivery_orders d
            WHERE d.customer_id = a.customer_id
              AND d.item_fg_id = a.item_fg_id
              AND YEAR(d.actual_delivery_date) = ' . $filter_period_year . '
              AND MONTH(d.actual_delivery_date) = ' . $filter_period_month . '
        ) as delivery', false);
        $this->db->select('(
            COALESCE((SELECT SUM(d.qty) 
                      FROM delivery_reports d 
                      WHERE d.customer_id = a.customer_id 
                        AND d.item_fg_id = a.item_fg_id 
                        AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' 
                        AND MONTH(d.delivery_report_date) = ' . $filter_period_month . '), 0)
            -
            (
                SUM(CASE 
                    WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                        OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                    THEN s.qty ELSE 0 END)
                +
                SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END)
                -
                COALESCE((SELECT SUM(d.qty) 
                          FROM delivery_reports d 
                          WHERE d.customer_id = a.customer_id 
                            AND d.item_fg_id = a.item_fg_id 
                            AND (YEAR(d.delivery_report_date) < ' . $filter_period_year . ' 
                                OR (YEAR(d.delivery_report_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_report_date) < ' . $filter_period_month . '))), 0)
            )
        ) as balance', false);            
                   
        $this->db->select('a.month_1 as forecast', false);
        $this->db->select('ROUND((CASE 
            WHEN a.month_1 = 0 THEN 0 ELSE ((SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) - a.month_1) / a.month_1) * 100
            END), 2) as bal_forecast', false);
         
        $this->db->select('ROUND((CASE 
            WHEN a.month_1 = 0 AND SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) > 0 THEN 100 ELSE ((SUM(CASE WHEN YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ' THEN s.qty ELSE 0 END) - a.month_1) / a.month_1) * 100
            END), 2) as bal_fc', false);   
        $this->db->select('((SELECT SUM(d.qty) FROM delivery_reports d WHERE d.customer_id = a.customer_id AND d.item_fg_id = a.item_fg_id AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' AND MONTH(d.delivery_report_date) = ' . $filter_period_month . ') * ci.price) as total_sales', false);
        $this->db->select('(
            (
                COALESCE((SELECT SUM(d.qty) 
                          FROM delivery_reports d 
                          WHERE d.customer_id = a.customer_id 
                            AND d.item_fg_id = a.item_fg_id 
                            AND YEAR(d.delivery_report_date) = ' . $filter_period_year . ' 
                            AND MONTH(d.delivery_report_date) = ' . $filter_period_month . '), 0)
                - 
                (
                    COALESCE(SUM(CASE 
                        WHEN (YEAR(s.sales_order_date) < ' . $filter_period_year . ') 
                            OR (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) < ' . $filter_period_month . ') 
                        THEN s.qty ELSE 0 END), 0)
                    + 
                    COALESCE(SUM(CASE 
                        WHEN (YEAR(s.sales_order_date) = ' . $filter_period_year . ' AND MONTH(s.sales_order_date) = ' . $filter_period_month . ') 
                        THEN s.qty ELSE 0 END), 0)
                )
            ) * ci.price
        ) as bal_sales', false);

        $this->db->from('(SELECT * FROM forecasts f1 
                        WHERE f1.revision = (
                            SELECT MAX(f2.revision) 
                            FROM forecasts f2 
                            WHERE f2.customer_id = f1.customer_id 
                                AND f2.item_fg_id = f1.item_fg_id 
                                AND f2.p_month = f1.p_month 
                                AND f2.p_year = f1.p_year
                        )) a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('customer_items ci', 'a.customer_id = ci.customer_id AND a.item_fg_id = ci.item_fg_id', 'left');
        $this->db->join('sales_orders s', 'a.customer_id = s.customer_id AND a.item_fg_id = s.item_fg_id', 'left');
        $this->db->where('a.p_month', $filter_period_month);
        $this->db->where('a.p_year', $filter_period_year);
        if($filter_customer_name !=""){
            $this->db->where('a.customer_id', $filter_customer_name);
        }
        if($filter_item_fg !=""){
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }
        if ($filter_product_family != "") {
            $this->db->where('c.item_family_number', $filter_product_family);
        }
        $this->db->order_by('a.created_date', 'DESC');
        $this->db->group_by('a.id, b.number, b.name, c.number, c.name, a.month_1');

        $records = $this->db->get()->result_array();
        $customer_name = 'ALL';
        if($filter_customer_name !=''){
            $customer = $this->crud->read('customers', [], ["id" => $filter_customer_name]);
            if (!empty($customer->name)) {
                $customer_name = $customer->name;
            }
        }

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#report_control_po {border-collapse: collapse;width: 100%;font-size: 12px;}#report_control_po td, #report_control_po th {border: 1px solid #ddd;padding: 2px;}#report_control_po tr:nth-child(even){background-color: #f2f2f2;}#report_control_po tr:hover {background-color: #ddd;}#report_control_po th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b>
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
                    <h3>Report Control PO</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>Period Year</small><br>
                                    <small>Customer Name</small><br>
                                    <small>Part No</small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>: </small><br>
                                    <small>: </small><br>
                                    <small>: </small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small><b>' . $filter_period_year . ' Period Month ' . $filter_period_month . '</b></small><br>
                                    <small><b>'. $customer_name .'</b></small><br>
                                    <small><b>'. $filter_item_fg .'</b></small>
                                </td>
                            </tr>
                        </table>
                    </div>
            </center>
            
            <table id="report_control_po">
            <tr>
                <th width="20" style="text-align: center;">No</th>
                <th style="text-align: center;">Customer Name</th>
                <th style="text-align: center;">Part No</th>
                <th style="text-align: center;">Part Name</th>
                <th style="text-align: center;">Outstanding PO</th>
                <th style="text-align: center;">SO M0</th>
                <th style="text-align: center;">Total SO</th>
                <th style="text-align: center;">Delivery</th>
                <th style="text-align: center;">Balance</th>
                <th style="text-align: center;">Forecast</th>
                <th style="text-align: center;">Bal Forecast</th>
                <th style="text-align: center;">Total Sales</th>
                <th style="text-align: center;">Bal Sales</th>
            </tr>';

            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['product_no'] . '</td>
                        <td>' . $data['product_name'] . '</td>
                        <td>' . $this->format_number($data['ost_so']) . '</td>
                        <td>' . $this->format_number($data['so_m0']) . '</td>
                        <td>' . $this->format_number($data['total_so']) . '</td>
                        <td>' . $this->format_number($data['delivery']) . '</td>
                        <td>' . $this->format_number($data['balance']) . '</td>
                        <td>' . $this->format_number($data['forecast']) . '</td>
                        <td>' . $this->format_float($data['bal_fc']) . '</td>
                        <td>' . $this->format_number($data['total_sales']) . '</td>
                        <td>' . $this->format_number($data['bal_sales']) . '</td>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
    }
}
