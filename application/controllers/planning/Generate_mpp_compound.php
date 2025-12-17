<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mpp_compound extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);
        //Validasi Form
        // $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mpp_compound.product_no]');
    }

    public function index(){
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mpp_compound');
        } else {
            redirect('error_access');
        }
    }

    public function readRevisions($month, $year){
        $this->db->select('revision');
        $this->db->from('generate_mps');
        $this->db->where('p_month', (int)$month);
        $this->db->where('p_year', $year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        if(!$revisions) {
            $revisions = 0;
        }

        die(json_encode($revisions));
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
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

    public function checkMppRp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('generate_mpp');
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

    public function datatableNotMps(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $ltppMonth = $filter_year . "-" . $filter_month . "-01";

        // $this->db->select('a.prod_plan, a.product_no, a.product_name, b.name as customer_name');
        $this->db->select('a.prod_plan, a.item_fg_id, a.ltpp_month2, b.number, b.name as product_name');
        $this->db->from('generate_mps_details a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        // $this->db->join('mst_customer b', 'a.customer_id = b.number');
        $this->db->where('b.item_family_number =', 'CD');
        $this->db->where('a.p_month', (int)$filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.prod_plan >', 0);
        $this->db->where('a.ltpp_month2', $ltppMonth);
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('b.name', 'asc');
        $records = $this->db->get()->result_array();

        $data = array();
        foreach ($records as $record) {
            $this->db->select('a.*, b.number, b.name as product_name');
            $this->db->from('generate_mpp_compound a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('b.item_family_number =', 'CD');
            $this->db->where('a.item_fg_id', $record['item_fg_id']);
            $this->db->group_by('a.revision');
            $this->db->order_by('a.revision', 'desc');
            $mpp = $this->db->get()->row();

            if(empty($mpp->item_fg_id)){
                $data[] = array(
                    "product_no" => $record['item_fg_id'],
                    "product_name" => $record['product_name'],
                    // "customer_name" => $record['customer_name'],
                    "customer_name" => '',
                    "prod_plan" => $record['prod_plan'],
                );
            }
        }
        

        die(json_encode($data));
    }

    // public function datatables(){
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));
    //     $filter_product_no = base64_decode($this->input->get('filter_product_no'));

    //     $this->db->select('revision');
    //     $this->db->from('generate_mpp_compound');
    //     $this->db->where('p_month', $filter_month);
    //     $this->db->where('p_year', $filter_year);
    //     $this->db->group_by('revision');
    //     $this->db->order_by('revision', 'desc');
    //     $revisions = $this->db->get()->row();

    //     $page = $this->input->post('page');
    //     $rows = $this->input->post('rows');

    //     //Pagination 1-10
    //     $page   = isset($page) ? intval($page) : 1;
    //     $rows   = isset($rows) ? intval($rows) : 10;
    //     $offset = ($page - 1) * $rows;
    //     $result = array();

    //     $this->db->select('a.*,
    //         e.number as product_no, 
    //         e.name as product_name, 
    //         COALESCE(ir.name, e.name) as compound_name,
    //         COALESCE(ir.number, e.number) as compound_no,
    //         e.lot, 
    //         ml.cycle_time, 
    //         (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as floating, a.prod_plan as mpsprod
    //     ');

    //     $this->db->from('generate_mpp_compound a');

    //     // $this->db->join(
    //     //     "generate_mpp d",
    //     //     "a.p_month = d.p_month AND a.p_year = d.p_year AND a.revision = d.revision AND a.item_fg_id = d.item_fg_id",
    //     //     "left"
    //     // );

    //     $this->db->join("item_fg e", "a.item_fg_id = e.id", "left");
    //     $this->db->join("item_rm ir", "a.item_rm_id = ir.id", "left");
    //     $this->db->join("menu_loadings ml", "ml.item_fg_id = a.item_fg_id", "left");
    //     $this->db->where('a.p_month', $filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     // $this->db->where('e.item_family_number =', 'CD');
    //     $this->db->where('a.revision', $revisions->revision);
    //     // $this->db->like('e.number', $filter_product_no);
    //     // $this->db->group_by('e.number');
    //     $this->db->order_by('compound_no', 'ASC');

    //     //Total Data
    //     $totalRows = $this->db->count_all_results('', false);

    //     //Limit 1 - 10
    //     $this->db->limit($rows, $offset);

    //     //Get Data Array
    //     $records = $this->db->get()->result_array();

    //     foreach ($records as $record) {
    //         // $periode = $record['p_year'] . $record['p_month'];
    //         // $revision = $record['revision'];
    //         // $assy_no = $record['item_fg_id'];
    //         // $line = $record['line_no'];

    //         $firstDate = date('Y-m-01', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
    //         $endDate = date('Y-m-t', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));

    //         $no = 1;
    //         $arr = array();
    //         $arr_date = array();

    //         while (strtotime($firstDate) <= strtotime($endDate)) {
    //             $working_date = date('Y-m-d', strtotime($firstDate));
    //             $day = date('j', strtotime($firstDate));
    //             $weekday = date('w', strtotime($firstDate));

    //             $this->db->select('remarks');
    //             $this->db->from('working_calendar');
    //             $this->db->where('working_date', $working_date);
    //             $holiday = $this->db->get()->row();

    //             $status_wds = $record["date_".$day];

    //             // $arr = [
    //             //     "wds_".$no => $status_wds,
    //             //     "log_".$no => null
    //             // ];

    //             $cycle_time = (!empty($record['cycle_time']) && $record['cycle_time'] > 0) ? $record['cycle_time'] : 0;

    //             if ($weekday == 0 || $weekday == 6 || !empty($holiday->remarks)) {
    //                 $arr = [
    //                     "wds_".$no => $status_wds
    //                 ];
    //             } else {
    //                 $hasil = round($status_wds * $cycle_time / 3600);
    //                 $arr = [
    //                     "wds_".$no => $status_wds,
    //                     "log_".$no => $hasil
    //                 ];
    //             }

    //             // $arr_date = array_merge($arr, $arr_date);

    //             $arr_date = array_merge($arr_date, $arr);

    //             $no++;
    //             $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //         }

    //         $finals[] = array_merge($arr_date, $record);
    //     }
    //     //Mapping Data
    //     $result['total'] = $totalRows;
    //     $result = array_merge($result, ['rows' => @$finals]);
    //     echo json_encode($result);
    // }

    public function datatables(){
        $filter_month = base64_decode($this->input->get('filter_month'));  
        $filter_year = base64_decode($this->input->get('filter_year'));  
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));  

        // Ambil revision terbaru
        $this->db->select('revision');  
        $this->db->from('generate_mpp_compound');  
        $this->db->where('p_month', $filter_month);  
        $this->db->where('p_year', $filter_year);  
        $this->db->group_by('revision');  
        $this->db->order_by('revision', 'desc');  
        $revisions = $this->db->get()->row();  

        $page = $this->input->post('page');  
        $rows = $this->input->post('rows');  

        //Pagination 1-10  
        $page   = isset($page) ? intval($page) : 1;  
        $rows   = isset($rows) ? intval($rows) : 10;  
        $offset = ($page - 1) * $rows;  
        $result = array();  

        $this->db->select("  
            a.id as mpp_id,  
            d.id as detail_id,  
            a.*, d.shift, d.plan_qty,  
            d.date_1, d.date_2, d.date_3, d.date_4, d.date_5, d.date_6, d.date_7, d.date_8, d.date_9, d.date_10,  
            d.date_11, d.date_12, d.date_13, d.date_14, d.date_15, d.date_16, d.date_17, d.date_18, d.date_19, d.date_20,  
            d.date_21, d.date_22, d.date_23, d.date_24, d.date_25, d.date_26, d.date_27, d.date_28, d.date_29, d.date_30, d.date_31,  
            (d.date_1 + d.date_2 + d.date_3 + d.date_4 + d.date_5 + d.date_6 + d.date_7 + d.date_8 + d.date_9 + d.date_10 +  
             d.date_11 + d.date_12 + d.date_13 + d.date_14 + d.date_15 + d.date_16 + d.date_17 + d.date_18 + d.date_19 + d.date_20 +  
             d.date_21 + d.date_22 + d.date_23 + d.date_24 + d.date_25 + d.date_26 + d.date_27 + d.date_28 + d.date_29 + d.date_30 + d.date_31) as floating,
            e.number as product_no, e.name as product_name, e.lot,
            COALESCE(ir.name, e.name) as compound_name,
            COALESCE(ir.number, e.number) as compound_no,
            COALESCE(ml.cycle_time,0) as cycle_time,
            mch.number as machine_no,
            COALESCE(pc.capacity_shift,0) as cap_shift,
            a.prod_plan as mpsprod,
            a.max_prod_plan as maxprodplan,
            COALESCE(e.mpq, fg_alias.mpq) as mpq,
            COALESCE(ml.productcivity, 85) as productcivity
        ");
        $this->db->from('generate_mpp_compound a');
        $this->db->join(
            "generate_mpp_compound_details d",  
            "a.p_month = d.p_month 
            AND a.p_year = d.p_year 
            AND a.revision = d.revision 
            AND (
                    (a.item_fg_id IS NOT NULL AND a.item_fg_id = d.item_fg_id) 
                    OR 
                    (a.item_rm_id IS NOT NULL AND a.item_rm_id = d.item_rm_id)
            )",
            "inner"
        );
        $this->db->join("item_fg e", "a.item_fg_id = e.id", "left");  
        $this->db->join("item_rm ir", "a.item_rm_id = ir.id", "left");

        $this->db->join("compound_alias ca", "a.item_rm_id = ca.item_rm_id", "left");
        $this->db->join("item_fg fg_alias", "ca.item_fg_id = fg_alias.id", "left");

        $this->db->join("menu_loadings ml", "ml.item_fg_id = a.item_fg_id", "left");
        $this->db->join("production_capacities pc", "pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id", "left");
        $this->db->join("machines mch", "pc.machine_id = mch.id");

        $this->db->where('a.p_month', $filter_month);  
        $this->db->where('a.p_year', $filter_year);  
        $this->db->where('a.revision', $revisions->revision);  
        $this->db->like('e.number', $filter_product_no);  
        $this->db->order_by('mch.number', 'ASC');  
        $this->db->order_by('compound_no', 'ASC');
        $this->db->order_by('d.shift', 'ASC');

        //Total Data  
        $totalRows = $this->db->count_all_results('', false);  

        //Limit 1 - 10  
        $this->db->limit($rows, $offset);  

        //Get Data Array  
        $records = $this->db->get()->result_array();  

        // foreach ($records as $record) {
        //     $firstDate = date('Y-m-d', strtotime($record['p_year'].'-'.$record['p_month'].'-01 -3 days'));
        //     $endDate   = date('Y-m-d', strtotime($firstDate . ' +30 days')); 

        //     $no = 1;  
        //     $arr_date = array();  

        //     while (strtotime($firstDate) <= strtotime($endDate)) {  
        //         $working_date = date('Y-m-d', strtotime($firstDate));  
        //         $day = date('j', strtotime($firstDate));  
        //         $weekday = date('w', strtotime($firstDate));  

        //         $this->db->select('remarks');  
        //         $this->db->from('working_calendar');  
        //         $this->db->where('working_date', $working_date);  
        //         $holiday = $this->db->get()->row();  

        //         $status_wds = $record["date_".$day];  

        //         if ($weekday == 0 || $weekday == 6 || !empty($holiday->remarks)) {  
        //             $arr = [  
        //                 "wds_".$no => $status_wds  
        //             ];  
        //         } else {  
        //             if (is_numeric($status_wds)) {  
        //                 $hasil = round(($status_wds));  
        //             } else {  
        //                 $hasil = 0;  
        //             }  
        //             $arr = [  
        //                 "wds_".$no => $status_wds,  
        //                 "log_".$no => $hasil  
        //             ];  
        //         }  

        //         $arr_date = array_merge($arr_date, $arr);  

        //         $no++;  
        //         $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));  
        //     }  

        //     $finals[] = array_merge($arr_date, $record);  
        // }  

        foreach ($records as $record) {
            $firstDate = date('Y-m-d', strtotime($record['p_year'].'-'.$record['p_month'].'-01 -3 days'));
            $endDate   = date('Y-m-t', strtotime($record['p_year'].'-'.$record['p_month'].'-01'));

            $no = 1;  
            $arr_date = array();  
            $dayIndex = 1; //

            while (strtotime($firstDate) <= strtotime($endDate) && $dayIndex <= 31) {
                $working_date = $firstDate;
                $weekday = date('w', strtotime($working_date));

                $holiday = $this->db->select('remarks')
                                    ->from('working_calendar')
                                    ->where('working_date', $working_date)
                                    ->get()
                                    ->row();

                $status_wds = $record["date_".$dayIndex] ?? 0;

                if ($weekday == 0 || $weekday == 6 || !empty($holiday->remarks)) {
                    $arr = [
                        "wds_".$no => $status_wds
                    ];
                } else {
                    // if (is_numeric($status_wds) && $record['cycle_time'] > 0) {
                    //     $hasil = round(($status_wds * $record['cycle_time']) / 3600);
                    // } else {
                    //     $hasil = 0;
                    // }

                    if (is_numeric($status_wds) && $record['cycle_time'] > 0) {
                        $cycle_per_hour = 3600 / $record['cycle_time'];
                        // $capacity_hour  = $cycle_per_hour * $record['mpq'] * ($record['productcivity'] / 100);
                        $capacity_hour = ceil(($cycle_per_hour * $record['mpq'] * ($record['productcivity'] / 100)) / $record['mpq']) * $record['mpq'];

                        if ($capacity_hour > 0) {
                            $hasil = round($status_wds / $capacity_hour, 2); // jam kerja yang dibutuhkan
                        } else {
                            $hasil = 0;
                        }

                    } else {
                        $hasil = 0;
                    }


                    $arr = [
                        "wds_".$no => $status_wds,
                        "log_".$no => $hasil
                    ];
                }

                $arr_date = array_merge($arr_date, $arr);

                $no++;
                $dayIndex++;
                $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
            }

            $finals[] = array_merge($arr_date, $record);
        }

        //Mapping Data  
        $result['total'] = $totalRows;  
        $result = array_merge($result, ['rows' => @$finals]);  
        echo json_encode($result);  
    }

    // function getdata(){
    //     //Filter Data
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));
    //     // $filter_line_no = base64_decode($this->input->get('filter_line_no'));
    //     // $filter_product_no = base64_decode($this->input->get('filter_product_no'));
    //     // $ltppMonth = $filter_year . "-" . $filter_month . "-01";

    //     $hkw = 0;
    //     $ltppMonth = $filter_year . "-" . $filter_month . "-01";
    //     $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
    //     $start = strtotime(date('Y-m-01', $monthStart));
    //     $finish = strtotime(date('Y-m-t', $monthStart));
    //     for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
    //         $working_date = date('Y-m-d', $z);

    //         $this->db->select('remarks');
    //         $this->db->from('working_calendar');
    //         $this->db->where('working_date', $working_date);
    //         $holiday = $this->db->get()->row();

    //         if (date('w', $z) !== '0') {
    //             if (@$holiday->remarks != null or @$holiday->remarks != "") {
    //                 $hkw += 0;
    //             } else {
    //                 $hkw += 1;
    //             }
    //         } else {
    //             $hkw += 0;
    //         }
    //     }

    //     // $this->db->select("a.product_no, a.product_name, a.circuit_no, a.prod_plan, a.line_no, b.lot, b.id_customer");
    //     $this->db->select("a.item_fg_id, b.number as product_name, a.prod_plan, b.lot");
    //     $this->db->from('generate_mps_details a');
    //     // $this->db->join('mst_item b', 'a.product_no = b.item_id');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->where('b.item_family_number =', 'CD');
    //     $this->db->where('a.p_month', (int)$filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     $this->db->where('a.revision', $filter_revision);
    //     $this->db->where('a.ltpp_month2', $ltppMonth);
    //     $this->db->where('a.prod_plan > 0');
    //     // $this->db->like('a.line_no', $filter_line_no);
    //     // $this->db->like('a.item_fg_id', $filter_product_no);
    //     $this->db->group_by("a.item_fg_id");
    //     $this->db->order_by("a.item_fg_id", "asc");
    //     $recordDetails = $this->db->get()->result_array();

    //     $mpp = array();
    //     foreach ($recordDetails as $detail) {
    //         $rows = array(
    //             "p_month" => $filter_month,
    //             "p_year" => $filter_year,
    //             "revision" => 0,
    //             "item_fg_id" => $detail['item_fg_id'],
    //             "prod_plan" => $detail['prod_plan'],
    //         );
    //         $prodplan = $detail['prod_plan'];
    //         $prodplanHkw = ($prodplan / $hkw);

    //         if($detail['lot'] > 0) {
    //             $lots = @(ceil($prodplanHkw / $detail['lot']) * $detail['lot']);
    //         } else {
    //             $lots = ceil($prodplanHkw);
    //         }

    //         $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //         $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
    //         $no = 1;
    //         while (strtotime($firstDate2) <= strtotime($endDate2)) {
    //             $working_date = date('Y-m-d', strtotime($firstDate2));

    //             $this->db->select('remarks');
    //             $this->db->from('working_calendar');
    //             $this->db->where('working_date', $working_date);
    //             $holiday = $this->db->get()->row();

    //             if ($prodplan >= $lots) {
    //                 $qty = is_nan($lots) ? 0 : $lots;
    //             } elseif ($prodplan < 0) {
    //                 $qty = 0;
    //             } else {
    //                 $qty = $prodplan;
    //             }

    //             if (date('w', strtotime($firstDate2)) !== '0') {
    //                 if (@$holiday->remarks != null or @$holiday->remarks != "") {
    //                     $rows = array_merge($rows, array("date_".$no => "W"));
    //                 } else {
    //                     $rows = array_merge($rows, array("date_".$no => "$qty"));
    //                     $prodplan = ($prodplan - $lots);
    //                 }
    //             } else {
    //                 $rows = array_merge($rows, array("date_".$no => "W"));
    //             }

    //             $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
    //             $no++;
    //         }
    //         $mpp[] = $rows;
    //     }
    //     echo json_encode($mpp);
    // }

    // public function getdata() {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));
    //     // $filter_product_no = base64_decode($this->input->get('filter_product_no'));

    //     $ltppMonth = $filter_year . "-" . $filter_month . "-01";

    //     $hkw = 0;
    //     $monthStart = strtotime($ltppMonth);
    //     $start = strtotime(date('Y-m-01', $monthStart));
    //     $finish = strtotime(date('Y-m-t', $monthStart));
    //     for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
    //         $working_date = date('Y-m-d', $z);

    //         $this->db->select('remarks');
    //         $this->db->from('working_calendar');
    //         $this->db->where('working_date', $working_date);
    //         $holiday = $this->db->get()->row();

    //         if (date('w', $z) !== '0') {
    //             if (@$holiday->remarks != null or @$holiday->remarks != "") {
    //                 $hkw += 0;
    //             } else {
    //                 $hkw += 1;
    //             }
    //         }
    //     }

    //     //? Compound sebagai FG
    //     $this->db->select("
    //         a.item_fg_id,
    //         NULL as item_rm_id,
    //         b.number as compound_no,
    //         b.lot,
    //         SUM(a.prod_plan) as prod_plan
    //     ", false);
    //     $this->db->from('generate_mps_details a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->where('b.item_family_number', 'CD'); // hanya compound sbg FG
    //     $this->db->where('a.p_month', (int)$filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     $this->db->where('a.revision', $filter_revision);
    //     // $this->db->like('a.item_fg_id', $filter_product_no);
    //     $this->db->where('a.ltpp_month2', $ltppMonth);
    //     $this->db->where('a.prod_plan >', 0);
    //     $this->db->group_by(['a.item_fg_id', 'b.number', 'b.lot']);
    //     $compound_fg = $this->db->get()->result_array();

    //     //? Compound sebagai RM
    //     $this->db->select("
    //         NULL as item_fg_id,
    //         d.id as item_rm_id,
    //         d.number as compound_no,
    //         0 as lot,
    //         SUM(b.prod_plan * c.composition / 1000) as prod_plan
    //     ", false);
    //     $this->db->from('generate_mpp b');
    //     $this->db->join('item_fg a', 'b.item_fg_id = a.id');
    //     $this->db->join('bom c', 'a.id = c.item_fg_id AND c.priority = 1');
    //     $this->db->join('item_rm d', 'c.item_rm_id = d.id');
    //     $this->db->where('b.p_month', $filter_month);
    //     $this->db->where('b.p_year', $filter_year);
    //     $this->db->where('b.revision', $filter_revision);
    //     $this->db->where('d.item_family_id', 'P03'); // hanya compound
    //     $this->db->group_by(['d.id', 'd.number']);
    //     $compound_rm = $this->db->get()->result_array();

    //     $combined = [];
    //     foreach (array_merge($compound_fg, $compound_rm) as $row) {
    //         $key = $row['compound_no'];

    //         if (!isset($combined[$key])) {
    //             $combined[$key] = $row;
    //         } else {
    //             $combined[$key]['prod_plan'] += $row['prod_plan'];

    //             if (!empty($row['item_fg_id'])) {
    //                 $combined[$key]['item_fg_id'] = $row['item_fg_id'];
    //             }
    //             if (!empty($row['item_rm_id'])) {
    //                 $combined[$key]['item_rm_id'] = $row['item_rm_id'];
    //             }
    //         }
    //     }
    //     $records = array_values($combined);

    //     $mpp = [];
    //     foreach ($records as $detail) {
    //         $rows = [
    //             "p_month" => $filter_month,
    //             "p_year" => $filter_year,
    //             "revision" => $filter_revision,
    //             "item_fg_id" => $detail['item_fg_id'],
    //             "item_rm_id" => isset($detail['item_rm_id']) ? $detail['item_rm_id'] : null,
    //             "prod_plan" => $detail['prod_plan'],
    //         ];

    //         $prodplan = $detail['prod_plan'];
    //         $prodplanHkw = ($hkw > 0) ? ($prodplan / $hkw) : 0;

    //         if ($detail['lot'] > 0) {
    //             $lots = @(ceil($prodplanHkw / $detail['lot']) * $detail['lot']);
    //         } else {
    //             $lots = ceil($prodplanHkw);
    //         }

    //         $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //         $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

    //         $no = 1;
    //         while (strtotime($firstDate2) <= strtotime($endDate2)) {
    //             $working_date = date('Y-m-d', strtotime($firstDate2));

    //             $this->db->select('remarks');
    //             $this->db->from('working_calendar');
    //             $this->db->where('working_date', $working_date);
    //             $holiday = $this->db->get()->row();

    //             if ($prodplan >= $lots) {
    //                 $qty = is_nan($lots) ? 0 : $lots;
    //             } elseif ($prodplan < 0) {
    //                 $qty = 0;
    //             } else {
    //                 $qty = $prodplan;
    //             }

    //             if (date('w', strtotime($firstDate2)) !== '0') {
    //                 if (@$holiday->remarks != null or @$holiday->remarks != "") {
    //                     $rows["date_".$no] = "W";
    //                 } else {
    //                     $rows["date_".$no] = max(0, round($qty));
    //                     $prodplan = ($prodplan - $lots);
    //                 }
    //             } else {
    //                 $rows["date_".$no] = "W";
    //             }

    //             $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
    //             $no++;
    //         }
    //         $mpp[] = $rows;
    //     }

    //     echo json_encode($mpp);
    // }

    // public function getdata() {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));

    //     $ltppMonth = $filter_year . "-" . $filter_month . "-01";
    //     $monthStart = strtotime($ltppMonth);
    //     $start = strtotime(date('Y-m-01', $monthStart));
    //     $finish = strtotime(date('Y-m-t', $monthStart));

    //     // Buat daftar tanggal & status working
    //     $dateList = [];
    //     for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
    //         $working_date = date('Y-m-d', $z);
    //         $holiday = $this->db->select('remarks')->from('working_calendar')
    //                     ->where('working_date', $working_date)->get()->row();
    //         $is_working = (date('w', $z) != 0 && empty(@$holiday->remarks));
    //         $dateList[] = [
    //             'date' => $working_date,
    //             'is_working' => $is_working
    //         ];
    //     }
    //     $totalDays = count($dateList);

    //     // Compound sebagai FG + join machine
    //     $this->db->select("
    //         a.item_fg_id,
    //         NULL as item_rm_id,
    //         b.number as compound_no,
    //         b.lot,
    //         a.prod_plan as prod_plan,
    //         ml.machine_id,
    //         ml.shift as total_shift,
    //         COALESCE(pc.capacity_shift, 0) as cap_shift,
    //         COALESCE(b.mpq, 25) as mpq
    //     ", false);
    //     $this->db->from('generate_mps_details a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->join('menu_loadings ml', 'ml.item_fg_id = a.item_fg_id', 'left');
    //     $this->db->join('production_capacities pc', 'pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id', 'left');
    //     $this->db->where('b.item_family_number', 'CD');
    //     $this->db->where('a.p_month', (int)$filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     $this->db->where('a.revision', $filter_revision);
    //     $this->db->where('a.ltpp_month2', $ltppMonth);
    //     $this->db->where('a.prod_plan >', 0);
    //     $this->db->group_by(['a.item_fg_id','b.number','b.lot','ml.machine_id']);
    //     $compound_fg = $this->db->get()->result_array();


    //     $this->db->select("
    //         ca.item_fg_id,  
    //         d.id as item_rm_id,  
    //         d.number as compound_no,  
    //         0 as lot,  
    //         SUM(b.prod_plan * c.composition / 1000) as prod_plan,  
    //         ml.machine_id,  
    //         ml.shift as total_shift,  
    //         COALESCE(pc.capacity_shift, 0) as cap_shift,
    //         COALESCE(ifg.mpq, 25) as mpq
    //     ", false);
    //     $this->db->from('generate_mpp b');  
    //     $this->db->join('item_fg a', 'b.item_fg_id = a.id');  
    //     $this->db->join('bom c', 'a.id = c.item_fg_id AND c.priority = 1');  
    //     $this->db->join('item_rm d', 'c.item_rm_id = d.id');  
    //     $this->db->join('compound_alias ca', 'd.id = ca.item_rm_id', 'left');  
    //     $this->db->join('item_fg ifg', 'ca.item_fg_id = ifg.id', 'left');
    //     $this->db->join('menu_loadings ml', 'ml.item_fg_id = ca.item_fg_id', 'left');  
    //     $this->db->join('production_capacities pc', 'pc.item_fg_id = ca.item_fg_id AND pc.machine_id = ml.machine_id', 'left');  
    //     $this->db->where('b.p_month', $filter_month);  
    //     $this->db->where('b.p_year', $filter_year);  
    //     $this->db->where('b.revision', $filter_revision);  
    //     $this->db->where('d.item_family_id', 'P03');  
    //     $this->db->group_by(['ca.item_fg_id','d.id','d.number','ml.machine_id','ml.shift','pc.capacity_shift']);  
    //     $compound_rm = $this->db->get()->result_array();

    //     // Gabungkan FG dan RM
    //     $combined = [];
    //     foreach (array_merge($compound_fg, $compound_rm) as $row) {
    //         $key = $row['compound_no'];

    //         $mpq = isset($row['mpq']) && $row['mpq'] > 0 ? $row['mpq'] : 25;

    //         $row['prod_plan'] = ceil($row['prod_plan'] / $mpq) * $mpq; // bulatkan ke kelipatan mpq

    //         if (!isset($combined[$key])) {
    //             $combined[$key] = $row;
    //         } else {
    //             $combined[$key]['prod_plan'] += $row['prod_plan'];
    //             if (!empty($row['item_fg_id'])) $combined[$key]['item_fg_id'] = $row['item_fg_id'];
    //             if (!empty($row['item_rm_id'])) $combined[$key]['item_rm_id'] = $row['item_rm_id'];
    //             if (!empty($row['machine_id'])) $combined[$key]['machine_id'] = $row['machine_id'];
    //         }
    //     }
    //     $records = array_values($combined);

    //     // Kelompokkan per machine
    //     $machines = [];
    //     foreach ($records as $d) {
    //         $machineId = $d['machine_id'] ?? 'NO_MACHINE';
    //         if (!isset($machines[$machineId])) $machines[$machineId] = ['items'=>[], 'machine_total_shift'=>0];

    //         $item = [
    //             "item_fg_id" => $d['item_fg_id'],
    //             "item_rm_id" => $d['item_rm_id'],
    //             "compound_no"=> $d['compound_no'],
    //             "prod_plan"  => (int)$d['prod_plan'],
    //             "remaining"  => (int)$d['prod_plan'],
    //             "lot"        => $d['lot'],
    //             "cap_shift"  => max(1,(int)$d['cap_shift']),
    //             "total_shift"=> max(1,(int)$d['total_shift']),
    //             "has_relation"=> ($d['cap_shift'] > 0 || $d['total_shift'] > 0) ? 1 : 0,
    //         ];
    //         $machines[$machineId]['items'][] = $item;
    //         $machines[$machineId]['machine_total_shift'] = max($machines[$machineId]['machine_total_shift'],$item['total_shift']);
    //     }

    //     $mpp = [];

    //     // Distribusi per mesin
    //     foreach ($machines as $machineId => $mc) {
    //         $items = $mc['items'];
    //         $machineShiftCount = max(1,$mc['machine_total_shift']);

    //         // Inisialisasi rows
    //         $rowsPerItem = [];
    //         foreach ($items as $it) {
    //             $header = [
    //                 "p_month" => $filter_month,
    //                 "p_year"  => $filter_year,
    //                 "revision"=> $filter_revision,
    //                 "item_fg_id"=> $it['item_fg_id'],
    //                 "item_rm_id"=> $it['item_rm_id'],
    //                 "prod_plan"=> $it['prod_plan'],
    //                 "shift"    => null,
    //                 "plan_qty" => 0
    //             ];
    //             for ($s=1;$s<=$it['total_shift'];$s++){
    //                 $r=$header;
    //                 $r['shift']=$s;
    //                 for($d=1;$d<=$totalDays;$d++){
    //                     $r["date_$d"]=$dateList[$d-1]['is_working']?0:"W";
    //                 }
    //                 $rowsPerItem[$it['compound_no']][$s]=$r;
    //             }
    //         }

    //         $currentIndex=0;
    //         $currentItem=($currentIndex<count($items))?$items[$currentIndex]:null;

    //         for($d=1;$d<=$totalDays;$d++){
    //             if(!$dateList[$d-1]['is_working']) continue;

    //             for($s=1;$s<=$machineShiftCount;$s++){
    //                 if($currentItem===null) continue;

    //                 if ($currentItem['has_relation'] == 0) {
    //                     // skip semua alokasi, langsung pindah ke item berikutnya
    //                     $currentIndex++;
    //                     $currentItem = ($currentIndex < count($items)) ? $items[$currentIndex] : null;
    //                     continue;
    //                 }

    //                 if($s>$currentItem['total_shift']) continue;

    //                 if($currentItem['remaining']>0){
    //                     $allocate=min($currentItem['cap_shift'],$currentItem['remaining']);
    //                     $rowsPerItem[$currentItem['compound_no']][$s]["date_$d"]=$allocate;
    //                     $rowsPerItem[$currentItem['compound_no']][$s]['plan_qty']+=$allocate;
    //                     $currentItem['remaining']-=$allocate;
    //                     $items[$currentIndex]['remaining']=$currentItem['remaining'];
    //                     if($currentItem['remaining']<=0){
    //                         $currentIndex++;
    //                         $currentItem=($currentIndex<count($items))?$items[$currentIndex]:null;
    //                     }
    //                 }else{
    //                     $currentIndex++;
    //                     $currentItem=($currentIndex<count($items))?$items[$currentIndex]:null;
    //                 }
    //             }
    //         }

    //         foreach($rowsPerItem as $itemRows){
    //             foreach($itemRows as $r){ $mpp[]=$r; }
    //         }
    //     }

    //     echo json_encode($mpp);
    // }

    public function getdata() {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $ltppMonth = $filter_year . "-" . $filter_month . "-01";
        $monthStart = strtotime($ltppMonth);
        $start = strtotime(date('Y-m-01', $monthStart));
        $finish = strtotime(date('Y-m-t', $monthStart));

        // Buat daftar tanggal & status working (awal: hanya bulan filter)
        $dateList = [];
        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $z);
            $holiday = $this->db->select('remarks')->from('working_calendar')
                        ->where('working_date', $working_date)->get()->row();
            $is_working = (date('w', $z) != 0 && empty(@$holiday->remarks));
            $dateList[] = [
                'date' => $working_date,
                'is_working' => $is_working
            ];
        }
        $totalDays = count($dateList);

        // Compound sebagai FG + join machine
        $this->db->select("
            a.item_fg_id,
            NULL as item_rm_id,
            b.number as compound_no,
            b.lot,
            a.prod_plan as prod_plan,
            ml.machine_id,
            ml.shift as total_shift,
            COALESCE(pc.capacity_shift, 0) as cap_shift,
            COALESCE(b.mpq, 25) as mpq
        ", false);
        $this->db->from('generate_mps_details a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('menu_loadings ml', 'ml.item_fg_id = a.item_fg_id', 'left');
        $this->db->join('production_capacities pc', 'pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id', 'left');
        $this->db->where('b.item_family_number', 'CD');
        $this->db->where('a.p_month', (int)$filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.ltpp_month2', $ltppMonth);
        $this->db->where('a.prod_plan >', 0);
        $this->db->group_by(['a.item_fg_id','b.number','b.lot','ml.machine_id']);
        $compound_fg = $this->db->get()->result_array();

        // Compound sebagai RM
        $this->db->select("
            ca.item_fg_id,  
            d.id as item_rm_id,  
            d.number as compound_no,  
            0 as lot,  
            SUM(b.prod_plan * c.composition / 1000) as prod_plan,  
            ml.machine_id,  
            ml.shift as total_shift,  
            COALESCE(pc.capacity_shift, 0) as cap_shift,
            COALESCE(ifg.mpq, 25) as mpq
        ", false);
        $this->db->from('generate_mpp b');  
        $this->db->join('item_fg a', 'b.item_fg_id = a.id');  
        $this->db->join('bom c', 'a.id = c.item_fg_id AND c.priority = 1');  
        $this->db->join('item_rm d', 'c.item_rm_id = d.id');  
        $this->db->join('compound_alias ca', 'd.id = ca.item_rm_id', 'left');  
        $this->db->join('item_fg ifg', 'ca.item_fg_id = ifg.id', 'left');
        $this->db->join('menu_loadings ml', 'ml.item_fg_id = ca.item_fg_id', 'left');  
        $this->db->join('production_capacities pc', 'pc.item_fg_id = ca.item_fg_id AND pc.machine_id = ml.machine_id', 'left');  
        $this->db->where('b.p_month', $filter_month);  
        $this->db->where('b.p_year', $filter_year);  
        $this->db->where('b.revision', $filter_revision);  
        $this->db->where('d.item_family_id', 'P03');  
        $this->db->group_by(['ca.item_fg_id','d.id','d.number','ml.machine_id','ml.shift','pc.capacity_shift']);  
        $compound_rm = $this->db->get()->result_array();

        // Gabungkan FG dan RM -> records (TIDAK DIUBAH)
        $combined = [];
        foreach (array_merge($compound_fg, $compound_rm) as $row) {
            $key = $row['compound_no'];
            $row['prod_plan'] = $row['prod_plan'];
            $mpq = isset($row['mpq']) && $row['mpq'] > 0 ? $row['mpq'] : 25;
            $row['max_prod_plan'] = ceil($row['prod_plan'] / $mpq) * $mpq; // bulatkan ke kelipatan mpq

            if (!isset($combined[$key])) {
                $combined[$key] = $row;
            } else {
                $combined[$key]['prod_plan'] += $row['prod_plan'];
                $combined[$key]['max_prod_plan'] += $row['max_prod_plan'];
                if (!empty($row['item_fg_id'])) $combined[$key]['item_fg_id'] = $row['item_fg_id'];
                if (!empty($row['item_rm_id'])) $combined[$key]['item_rm_id'] = $row['item_rm_id'];
                if (!empty($row['machine_id'])) $combined[$key]['machine_id'] = $row['machine_id'];
            }
        }
        $records = array_values($combined);

        $compound_lead_days = 3; // berapa hari sebelum rubber part mulai

        // mapping compound -> item_rm_id (tetap)
        $compoundToRm = [];
        foreach ($records as $r) {
            if (!empty($r['item_rm_id'])) {
                $compoundToRm[$r['compound_no']] = $r['item_rm_id'];
            }
        }

        // Temukan earliest rubber production date (absolute Y-m-d) untuk setiap compound
        $earliestRubberByCompound = []; // compound_no => 'YYYY-mm-dd' (rubber earliest)
        if (!empty($compoundToRm)) {
            foreach ($compoundToRm as $compoundNo => $item_rm_id) {
                $this->db->select('gd.*');
                $this->db->from('generate_mpp_details gd');
                $this->db->join('bom b', 'gd.item_fg_id = b.item_fg_id');
                $this->db->where('b.item_rm_id', $item_rm_id);
                $this->db->where('gd.p_month', $filter_month);
                $this->db->where('gd.p_year', $filter_year);
                $this->db->where('gd.revision', $filter_revision);
                $rows = $this->db->get()->result_array();

                $minDate = null;
                foreach ($rows as $row) {
                    // scan columns date_1..date_N berdasarkan $dateList (bulan filter)
                    for ($d=1; $d <= $totalDays; $d++) {
                        $col = "date_$d";
                        if (isset($row[$col]) && $row[$col] > 0) {
                            $rubberDate = $dateList[$d-1]['date']; // Y-m-d di bulan filter
                            if ($minDate === null || strtotime($rubberDate) < strtotime($minDate)) {
                                $minDate = $rubberDate;
                            }
                            break; // ambil first date di row ini
                        }
                    }
                }
                if ($minDate !== null) {
                    $earliestRubberByCompound[$compoundNo] = $minDate;
                }
            }
        }

        // Hitung compoundStartDate = earliestRubberDate - lead_days, dan cari global earliest
        $compoundStartByCompound = []; // compound_no => 'YYYY-mm-dd'
        $globalEarliestCompoundStart = null;
        foreach ($earliestRubberByCompound as $cno => $rubberDate) {
            $cs = date('Y-m-d', strtotime($rubberDate . " -{$compound_lead_days} days"));
            $compoundStartByCompound[$cno] = $cs;
            if ($globalEarliestCompoundStart === null || strtotime($cs) < strtotime($globalEarliestCompoundStart)) {
                $globalEarliestCompoundStart = $cs;
            }
        }

        // Jika ada compoundStart sebelum $start (awal bulan), maka prepend tanggal ke $dateList
        if ($globalEarliestCompoundStart !== null && strtotime($globalEarliestCompoundStart) < $start) {
            $startDt = new DateTime(date('Y-m-d', $start));
            $globalStartDt = new DateTime($globalEarliestCompoundStart);
            $interval = $globalStartDt->diff($startDt);
            $daysToPrepend = (int)$interval->format('%a'); // jumlah hari yang perlu ditambahkan di depan

            // prepend dari oldest ke day before $start
            $prependDates = [];
            for ($i = $daysToPrepend; $i >= 1; $i--) {
                $dts = clone $startDt;
                $dts->modify("-{$i} days");
                $d_str = $dts->format('Y-m-d');
                $holiday = $this->db->select('remarks')->from('working_calendar')
                            ->where('working_date', $d_str)->get()->row();
                $is_working = (date('w', strtotime($d_str)) != 0 && empty(@$holiday->remarks));
                $prependDates[] = [
                    'date' => $d_str,
                    'is_working' => $is_working
                ];
            }
            // merge prepend + existing
            $dateList = array_merge($prependDates, $dateList);
            $totalDays = count($dateList);
        }

        // Sekarang tentukan start_day untuk tiap compound berdasarkan compoundStartByCompound
        $earliestDayByCompound = []; // compound_no => start_day index (1-based)
        foreach ($records as $r) {
            $compoundNo = $r['compound_no'];
            $cs = $compoundStartByCompound[$compoundNo] ?? null;
            if ($cs !== null) {
                // cari posisi di dateList
                $idx = null;
                foreach ($dateList as $i => $dd) {
                    if ($dd['date'] === $cs) { $idx = $i + 1; break; }
                }
                // Jika compoundStartDate tidak ada di dateList (shouldn't happen), fallback ke earliest rubber date pos minus lead
                if ($idx === null && isset($earliestRubberByCompound[$compoundNo])) {
                    $rub = $earliestRubberByCompound[$compoundNo];
                    // fallback: cari rubber pos
                    foreach ($dateList as $i => $dd) {
                        if ($dd['date'] === $rub) { $idx = $i + 1 - $compound_lead_days; break; }
                    }
                    if ($idx === null) $idx = 1;
                    if ($idx < 1) $idx = 1;
                } elseif ($idx === null) {
                    $idx = 1;
                }
                $earliestDayByCompound[$compoundNo] = $idx;
            } else {
                // tidak ada rubber terkait -> default 1
                $earliestDayByCompound[$compoundNo] = 1;
            }
        }

        // Set start_day untuk setiap record
        foreach ($records as &$d) {
            $compoundNo = $d['compound_no'];
            $earliestIdx = $earliestDayByCompound[$compoundNo] ?? 1;
            $d['start_day'] = max(1, $earliestIdx);
        }
        unset($d);

        // Kelompokkan per machine
        $machines = [];
        foreach ($records as $d) {
            $machineId = $d['machine_id'] ?? 'NO_MACHINE';
            if (!isset($machines[$machineId])) $machines[$machineId] = ['items'=>[], 'machine_total_shift'=>0];

            $item = [
                "item_fg_id" => $d['item_fg_id'],
                "item_rm_id" => $d['item_rm_id'],
                "compound_no"=> $d['compound_no'],
                "prod_plan"  => (int)$d['prod_plan'],
                "max_prod_plan"  => (int)$d['max_prod_plan'],
                "remaining"  => (int)$d['max_prod_plan'],
                "lot"        => $d['lot'],
                "cap_shift"  => max(1,(int)$d['cap_shift']),
                "total_shift"=> max(1,(int)$d['total_shift']),
                "has_relation"=> ($d['cap_shift'] > 0 || $d['total_shift'] > 0) ? 1 : 0,
                "start_day"  => $d['start_day'],
            ];
            $machines[$machineId]['items'][] = $item;
            $machines[$machineId]['machine_total_shift'] = max($machines[$machineId]['machine_total_shift'],$item['total_shift']);
        }

        // Urutkan items per machine berdasarkan start_day (TIDAK DIUBAH)
        foreach ($machines as $mid => &$mc) {
            usort($mc['items'], function($a,$b){
                if ($a['start_day'] == $b['start_day']) return strcmp($a['compound_no'],$b['compound_no']);
                return $a['start_day'] < $b['start_day'] ? -1 : 1;
            });
        }
        unset($mc);

        $mpp = [];

        // Distribusi per mesin (TIDAK DIUBAH, namun sekarang $dateList dapat meluas ke belakang)
        foreach ($machines as $machineId => $mc) {
            $items = $mc['items'];
            $machineShiftCount = max(1,$mc['machine_total_shift']);

            // Inisialisasi rows
            $rowsPerItem = [];
            foreach ($items as $it) {
                $header = [
                    "p_month" => $filter_month,
                    "p_year"  => $filter_year,
                    "revision"=> $filter_revision,
                    "item_fg_id"=> $it['item_fg_id'],
                    "item_rm_id"=> $it['item_rm_id'],
                    "prod_plan"=> $it['prod_plan'],
                    "max_prod_plan"=> $it['max_prod_plan'],
                    "shift"    => null,
                    "plan_qty" => 0
                ];
                for ($s=1;$s<=$it['total_shift'];$s++){
                    $r=$header;
                    $r['shift']=$s;
                    for($d=1;$d<=$totalDays;$d++){
                        $r["date_$d"]=$dateList[$d-1]['is_working']?0:"W";
                    }
                    $rowsPerItem[$it['compound_no']][$s]=$r;
                }
            }

            $currentIndex=0;
            $currentItem=($currentIndex<count($items))?$items[$currentIndex]:null;

            for($d=1;$d<=$totalDays;$d++){
                if(!$dateList[$d-1]['is_working']) continue;

                for($s=1;$s<=$machineShiftCount;$s++){
                    if($currentItem===null) continue;

                    // pastikan tidak mulai sebelum start_day
                    if ($d < $currentItem['start_day']) continue;

                    if ($currentItem['has_relation'] == 0) {
                        $currentIndex++;
                        $currentItem = ($currentIndex < count($items)) ? $items[$currentIndex] : null;
                        continue;
                    }

                    if($s>$currentItem['total_shift']) continue;

                    if($currentItem['remaining']>0){
                        $allocate=min($currentItem['cap_shift'],$currentItem['remaining']);
                        $rowsPerItem[$currentItem['compound_no']][$s]["date_$d"]=$allocate;
                        $rowsPerItem[$currentItem['compound_no']][$s]['plan_qty']+=$allocate;
                        $currentItem['remaining']-=$allocate;
                        $items[$currentIndex]['remaining']=$currentItem['remaining'];
                        if($currentItem['remaining']<=0){
                            $currentIndex++;
                            $currentItem=($currentIndex<count($items))?$items[$currentIndex]:null;
                        }
                    }else{
                        $currentIndex++;
                        $currentItem=($currentIndex<count($items))?$items[$currentIndex]:null;
                    }
                }
            }

            foreach($rowsPerItem as $itemRows){
                foreach($itemRows as $r){ $mpp[]=$r; }
            }
        }

        echo json_encode($mpp);
    }


    public function push_data(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $this->db->select('a.*, b.line_nick');
        $this->db->from('generate_mpp a');
        $this->db->join('mst_line b', 'a.line_no = b.line_no');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        //$this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->order_by('a.line_no', 'ASC');
        $this->db->order_by('a.product_no', 'ASC');
        $totalRows = $this->db->count_all_results('', false);

        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $this->dummy = $this->load->database('dummy', TRUE);
            $this->pg2 = $this->load->database('pg2', TRUE);

            $periode = $filter_year . $filter_month;

            // if($filter_line_no == "" && $filter_product_no == ""){
            //     $this->dummy->query("DELETE FROM wip_trx_mpp where periode = '$periode' and rev = '$filter_revision'");
            //     $this->db->query("DELETE FROM generate_mpp_detail where periode = '$periode' and rev = '$filter_revision'");
            // }

            $mpp_final = array();
            $last_line_no = "";
            $last_assy_no = "";
            $serial_no = 1;
            $pos_assy = 1;
            foreach ($records as $record) {
                $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
                $no = 1;
                $wp = 1;
                $alfabet = "z";
                $wp_weekend = 0;
                $mpp = array();

                $line_no = $record['line_no'];
                $assy_no = $record['product_no'];

                $array_assy[] = array(
                    "assy_no" => $assy_no
                );

                $this->dummy->select('max(pos_assy::int) as pos_assy, max(serial_mpp) as serial');
                $this->dummy->from("wip_trx_mpp");
                $this->dummy->where("periode", $periode);
                // $this->dummy->where("rev", $filter_revision);
                $this->dummy->where("line", $record['line_no']);
                //$this->dummy->where("assy_no", $record['product_no']);
                //$this->dummy->group_by('assy_no');
                $wip_trx_mpp = $this->dummy->get()->row();

                $this->dummy->select('pos_assy');
                $this->dummy->from("wip_trx_mpp");
                $this->dummy->where("periode", $periode);
                $this->dummy->where("line", $record['line_no']);
                $this->dummy->where("assy_no", $record['product_no']);
                // $this->dummy->order_by("pos_assy", "desc");
                $wip_trx_mpp_pos_assy = $this->dummy->get()->result_array();
                $count_wip_mpp = count($wip_trx_mpp_pos_assy);

                if($wip_trx_mpp){
                    if($last_line_no != $line_no){
                        $serial_no = substr($wip_trx_mpp->serial, 9) + 1;
                    }
                }else{
                    if($last_line_no != $line_no){
                        $serial_no = 1;
                    }
                }

                //Jika by assy no lebih dari 1 
                if($count_wip_mpp > 0){
                    $pos_assy = rtrim(@$wip_trx_mpp_pos_assy[0]['pos_assy']);
                }else{
                    if(!empty($wip_trx_mpp->pos_assy)){
                        // if($count_wip_mpp == 0){
                        //     $pos_assy = rtrim(@$wip_trx_mpp->pos_assy + 1);
                        // }else{
                        //     $pos_assy = rtrim(@$wip_trx_mpp->pos_assy);
                        // }
                        $pos_assy = rtrim(@$wip_trx_mpp->pos_assy + 1);
                    }else{
                        if($last_line_no != $line_no){
                            $pos_assy = 1;
                        }
                    }
                }

                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $woc = date('my', strtotime($firstDate));
                    $serial = date('ym', strtotime($firstDate));
                    $working_date = date('Y-m-d', strtotime($firstDate));
                    $wp_date = date('d', strtotime($firstDate));
                    $serial_tgl = "01";

                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date);
                    $holiday = $this->db->get()->row();

                    if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            if($record["date_".$no] != "W" && $record["date_".$no] != "" && $record["date_".$no] != "0"){
                                if($alfabet == "z"){
                                    $alfabets = "A";
                                }elseif($alfabet == "A"){
                                    $alfabets = "B";
                                }elseif($alfabet == "B"){
                                    $alfabets = "C";
                                }elseif($alfabet == "C"){
                                    $alfabets = "D";
                                }elseif($alfabet == "D"){
                                    $alfabets = "E";
                                }elseif($alfabet == "E"){
                                    $alfabets = "F";
                                }elseif($alfabet == "F"){
                                    $alfabets = "G";
                                }elseif($alfabet == "G"){
                                    $alfabets = "H";
                                }elseif($alfabet == "H"){
                                    $alfabets = "I";
                                }elseif($alfabet == "I"){
                                    $alfabets = "J";
                                }elseif($alfabet == "J"){
                                    $alfabets = "K";
                                }elseif($alfabet == "K"){
                                    $alfabets = "L";
                                }elseif($alfabet == "L"){
                                    $alfabets = "M";
                                }elseif($alfabet == "M"){
                                    $alfabets = "N";
                                }elseif($alfabet == "N"){
                                    $alfabets = "O";
                                }else{  
                                    $alfabets = "";
                                }

                                $wpp = sprintf("%02s", $wp);
                                $rows[] = array(
                                    "serial_mpp" => "1".$serial.$record['line_no'].$serial_tgl."2".sprintf("%05s", $serial_no),
                                    "periode" => $filter_year . $filter_month,
                                    "assy_no" => $record['product_no'],
                                    "rev" => $filter_revision2,
                                    "color" => "2",
                                    "line" => $record['line_no'],
                                    "qty1" => "0",
                                    "rev1" => "0",
                                    "qty2" => "0",
                                    "rev2" => "0",
                                    "qty3" => "0",
                                    "rev3" => "0",
                                    "wp_date" => $working_date,
                                    "trx_user" => "1",
                                    "plant" => "2",
                                    "trx_date" => date("Y-m-d H:i:s"),
                                    "pos_assy" => sprintf("%02s", $pos_assy),
                                    "temp_woc_id" => sprintf("%02s", $pos_assy) . "W/P".$wpp.$alfabets.$woc."-".$record['line_nick'],
                                    "status_dt" => "f",
                                    "set_wp" => "f",
                                    "wp" => $wpp.$alfabets.$filter_month,
                                    "qty" => empty($record["date_".$no]) ? 0 : $record["date_".$no],
                                    "wp_id" => $wpp.$alfabets,
                                );

                                $alfabet = $alfabets;
                                $serial_no++;
                            }else{
                                $wpp = sprintf("%02s", $wp);

                                if($alfabet == "z"){
                                    $alfabets = "A";
                                }elseif($alfabet == "A"){
                                    $alfabets = "B";
                                }elseif($alfabet == "B"){
                                    $alfabets = "C";
                                }elseif($alfabet == "C"){
                                    $alfabets = "D";
                                }elseif($alfabet == "D"){
                                    $alfabets = "E";
                                }elseif($alfabet == "E"){
                                    $alfabets = "F";
                                }elseif($alfabet == "F"){
                                    $alfabets = "G";
                                }elseif($alfabet == "G"){
                                    $alfabets = "H";
                                }elseif($alfabet == "H"){
                                    $alfabets = "I";
                                }elseif($alfabet == "I"){
                                    $alfabets = "J";
                                }elseif($alfabet == "J"){
                                    $alfabets = "K";
                                }elseif($alfabet == "K"){
                                    $alfabets = "L";
                                }elseif($alfabet == "L"){
                                    $alfabets = "M";
                                }elseif($alfabet == "M"){
                                    $alfabets = "N";
                                }elseif($alfabet == "N"){
                                    $alfabets = "O";
                                }else{  
                                    $alfabets = "";
                                }

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_mpp");
                                $this->dummy->where("periode", $filter_year . $filter_month);
                                $this->dummy->where("line", $record['line_no']);
                                $this->dummy->where("assy_no", $record['product_no']);
                                $this->dummy->where("wp", $wpp.$alfabets.$filter_month);
                                $wip_trx_mpp = $this->dummy->get()->row();

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_wds");
                                $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
                                $wip_trx_wds = $this->dummy->get()->result_array();

                                if(count($wip_trx_wds) == 0){
                                    if($wip_trx_mpp){
                                        $this->dummy->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp.$alfabets.$filter_month,
                                        ));

                                        $this->pg2->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp.$alfabets.$filter_month,
                                        ));
                                    }
                                }

                                $alfabet = $alfabets;
                            }
                        } else {
                            if($record["date_".$no] != "W" && $record["date_".$no] != "" && $record["date_".$no] != "0"){
                                $wpp = sprintf("%02s", $wp);

                                $rows[] = array(
                                    "serial_mpp" => "1".$serial.$record['line_no'].$serial_tgl."2".sprintf("%05s", $serial_no),
                                    "periode" => $filter_year . $filter_month,
                                    "assy_no" => $record['product_no'],
                                    "rev" => $filter_revision2,
                                    "color" => "2",
                                    "line" => $record['line_no'],
                                    "qty1" => "0",
                                    "rev1" => "0",
                                    "qty2" => "0",
                                    "rev2" => "0",
                                    "qty3" => "0",
                                    "rev3" => "0",
                                    "wp_date" => $working_date,
                                    "trx_user" => "1",
                                    "plant" => "2",
                                    "trx_date" => date("Y-m-d H:i:s"),
                                    "pos_assy" => sprintf("%02s", $pos_assy),
                                    "temp_woc_id" => sprintf("%02s", $pos_assy) . "W/P".$wpp.$woc."-".$record['line_nick'],
                                    "status_dt" => "f",
                                    "set_wp" => "f",
                                    "wp" => $wpp."/".$filter_month,
                                    "qty" => empty($record["date_".$no]) ? 0 : $record["date_".$no],
                                    "wp_id" => $wpp,
                                );

                                $alfabet = "z";
                                $serial_no++;
                                $wp++;
                            }else{
                                $wpp = sprintf("%02s", $wp);

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_mpp");
                                $this->dummy->where("periode", $filter_year . $filter_month);
                                $this->dummy->where("line", $record['line_no']);
                                $this->dummy->where("assy_no", $record['product_no']);
                                $this->dummy->where("wp", $wpp."/".$filter_month);
                                $wip_trx_mpp = $this->dummy->get()->row();

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_wds");
                                $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
                                $wip_trx_wds = $this->dummy->get()->result_array();

                                if(count($wip_trx_wds) == 0){
                                    if($wip_trx_mpp){
                                        $this->dummy->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp."/".$filter_month,
                                        ));

                                        $this->pg2->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp."/".$filter_month,
                                        ));
                                    }
                                }
                                // $serial_no++;
                                $wp++;
                            }
                        }
                    } else {
                        if($record["date_".$no] != "W" && $record["date_".$no] != "" && $record["date_".$no] != "0"){
                            $wpp = sprintf("%02s", $wp);

                            if($alfabet == "z"){
                                $alfabets = "A";
                            }elseif($alfabet == "A"){
                                $alfabets = "B";
                            }elseif($alfabet == "B"){
                                $alfabets = "C";
                            }elseif($alfabet == "C"){
                                $alfabets = "D";
                            }elseif($alfabet == "D"){
                                $alfabets = "E";
                            }elseif($alfabet == "E"){
                                $alfabets = "F";
                            }elseif($alfabet == "F"){
                                $alfabets = "G";
                            }elseif($alfabet == "G"){
                                $alfabets = "H";
                            }elseif($alfabet == "H"){
                                $alfabets = "I";
                            }elseif($alfabet == "I"){
                                $alfabets = "J";
                            }elseif($alfabet == "J"){
                                $alfabets = "K";
                            }elseif($alfabet == "K"){
                                $alfabets = "L";
                            }elseif($alfabet == "L"){
                                $alfabets = "M";
                            }elseif($alfabet == "M"){
                                $alfabets = "N";
                            }elseif($alfabet == "N"){
                                $alfabets = "O";
                            }else{  
                                $alfabets = "";
                            }

                            $rows[] = array(
                                "serial_mpp" => "1".$serial.$record['line_no'].$serial_tgl."2".sprintf("%05s", $serial_no),
                                "periode" => $filter_year . $filter_month,
                                "assy_no" => $record['product_no'],
                                "rev" => $filter_revision2,
                                "color" => "2",
                                "line" => $record['line_no'],
                                "qty1" => "0",
                                "rev1" => "0",
                                "qty2" => "0",
                                "rev2" => "0",
                                "qty3" => "0",
                                "rev3" => "0",
                                "wp_date" => $working_date,
                                "trx_user" => "1",
                                "plant" => "2",
                                "trx_date" => date("Y-m-d H:i:s"),
                                "pos_assy" => sprintf("%02s", $pos_assy),
                                "temp_woc_id" => sprintf("%02s", $pos_assy) . "W/P".$wpp.$alfabets.$woc."-".$record['line_nick'],
                                "status_dt" => "f",
                                "set_wp" => "f",
                                "wp" => $wpp.$alfabets.$filter_month,
                                "qty" => empty($record["date_".$no]) ? 0 : $record["date_".$no],
                                "wp_id" => $wpp.$alfabets,
                            );

                            $alfabet = $alfabets;
                            $serial_no++;
                        }else{
                            $wpp = sprintf("%02s", $wp);

                            if($alfabet == "z"){
                                $alfabets = "A";
                            }elseif($alfabet == "A"){
                                $alfabets = "B";
                            }elseif($alfabet == "B"){
                                $alfabets = "C";
                            }elseif($alfabet == "C"){
                                $alfabets = "D";
                            }elseif($alfabet == "D"){
                                $alfabets = "E";
                            }elseif($alfabet == "E"){
                                $alfabets = "F";
                            }elseif($alfabet == "F"){
                                $alfabets = "G";
                            }elseif($alfabet == "G"){
                                $alfabets = "H";
                            }elseif($alfabet == "H"){
                                $alfabets = "I";
                            }elseif($alfabet == "I"){
                                $alfabets = "J";
                            }elseif($alfabet == "J"){
                                $alfabets = "K";
                            }elseif($alfabet == "K"){
                                $alfabets = "L";
                            }elseif($alfabet == "L"){
                                $alfabets = "M";
                            }elseif($alfabet == "M"){
                                $alfabets = "N";
                            }elseif($alfabet == "N"){
                                $alfabets = "O";
                            }else{  
                                $alfabets = "";
                            }

                            $this->dummy->select('*');
                            $this->dummy->from("wip_trx_mpp");
                            $this->dummy->where("periode", $filter_year . $filter_month);
                            $this->dummy->where("line", $record['line_no']);
                            $this->dummy->where("assy_no", $record['product_no']);
                            $this->dummy->where("wp", $wpp.$alfabets.$filter_month);
                            $wip_trx_mpp = $this->dummy->get()->row();

                            $this->dummy->select('*');
                            $this->dummy->from("wip_trx_wds");
                            $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
                            $wip_trx_wds = $this->dummy->get()->result_array();

                            if(count($wip_trx_wds) == 0){
                                if($wip_trx_mpp){
                                    $this->dummy->delete('wip_trx_mpp', array(
                                        'periode' => $filter_year . $filter_month,
                                        'line' => $record['line_no'],
                                        'assy_no' => $record['product_no'],
                                        'wp' => $wpp.$alfabets.$filter_month,
                                    ));

                                    $this->pg2->delete('wip_trx_mpp', array(
                                        'periode' => $filter_year . $filter_month,
                                        'line' => $record['line_no'],
                                        'assy_no' => $record['product_no'],
                                        'wp' => $wpp.$alfabets.$filter_month,
                                    ));
                                }
                            }

                            $alfabet = $alfabets;
                        }
                    }

                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $no++;
                }

                $last_assy_no = $assy_no;
                $last_line_no = $line_no;
                $pos_assy++;
            }

            //Nanti ini buat delete per assy jika tidak ada di wip_trx_mpp
            // $this->dummy->where("periode", $periode);
            // $this->dummy->where("line", $filter_line_no);
            // $this->dummy->where_not_in('assy_no', $excluded_ids);
            // $this->dummy->delete('wip_trx_mpp');

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_mpp_generate(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();
        $filter_revision = $revisions->revision;

        $this->db->select('a.*');
        $this->db->from('(SELECT * FROM generate_mpp order by prod_plan desc) a');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.line_no');
        $this->db->order_by('a.line_no', 'ASC');
        $totalRows = $this->db->count_all_results('', false);

        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $this->dummy = $this->load->database('dummy', TRUE);
            $periode = $filter_year . $filter_month;

            foreach ($records as $record) {
                $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
                $line = $record['line_no'];

                $no = 1;
                $hk = 0;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    if($record["date_".$no] != "W"){
                        if($record["date_".$no] != 0){
                            $hk += 1;
                        }else{
                            $hk += 0;
                        }
                    }else{
                        $hk += 0;
                    }

                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $no++;
                }

                $planConfig = $this->crud->read('planning_config');
                $doc_mpp = date('Ym', strtotime($filter_year . "-" . $filter_month ."-". $planConfig->wp_day_from));
                $wp_dt_fr = date('Y-m-d', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month ."-". $planConfig->wp_day_from)));
                $wp_dt_to = date('Y-m-d', strtotime($filter_year . "-" . $filter_month ."-". $planConfig->wp_day_to));

                $rows[] = array(
                    "doc_mpp" => $doc_mpp . $record['line_no'],
                    "rev" => $filter_revision2,
                    "period" => $filter_year . $filter_month,
                    "line" => $record['line_no'],
                    "wp_dt_fr" => $wp_dt_fr,
                    "wp_dt_to" => $wp_dt_to,
                    "total_wp" => $hk,
                    "user_update" => $this->session->name,
                    "time_update" => date("Y-m-d H:i:s"),
                    "mpp_notes" => "",
                );
            }

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_plan_schedule(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $this->db->select('product_no, SUM(date_1 + date_2 + date_3 + date_4 + date_5 + date_6 + date_7 + date_8 + date_9 + date_10 + 
            date_11 + date_12 + date_13 + date_14 + date_15 + date_16 + date_17 + date_18 + date_19 + date_20 +
            date_21 + date_22 + date_23 + date_24 + date_25 + date_26 + date_27 + date_28 + date_29 + date_30 + date_31) as qty');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->like('line_no', $filter_line_no);
        $this->db->like('product_no', $filter_product_no);
        $this->db->group_by('product_no');
        $this->db->order_by('product_no', 'ASC');

        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $this->dummy = $this->load->database('dummy', TRUE);
            $periode = $filter_year . $filter_month;

            foreach ($records as $record) {
                $product_no = $record['product_no'];

                $this->dummy->query("DELETE FROM plansys_schedule_detail where period = '$periode' and assy_no = '$product_no'");
                $this->dummy->query("DELETE FROM plansys_schedule where period = '$periode' and assy_no = '$product_no'");

                $rows[] = array(
                    "period" => $periode,
                    "date_period" => $filter_year . "-" . $filter_month . "-01",
                    "assy_no" => $record['product_no'],
                    "total_qty" => $record['qty'],
                    "input_user" => $this->session->name,
                    "input_time" => date("Y-m-d H:i:s")
                );
            }

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_plan_schedule_detail(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $this->db->select('a.*');
        $this->db->from('generate_mpp a');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.product_no');
        $this->db->order_by('a.product_no', 'ASC');
        $totalRows = $this->db->count_all_results('', false);

        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $periode = $filter_year . $filter_month;
            foreach ($records as $record) {
                $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

                $no = 1;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $qty = $record["date_".$no];
                    $date_schedule = date('Y-m-d', strtotime($firstDate));

                    if($record["date_".$no] != "W"){
                        if($qty > 0){
                            $rows[] = array(
                                "period" => $periode,
                                "date_period" => $filter_year . "-" . $filter_month . "-01",
                                "assy_no" => $record['product_no'],
                                "date_schedule" => $date_schedule,
                                "qty" => $qty,
                            );
                        }
                    }

                    // }else{

                    //     $rows[] = array(

                    //         "period" => $periode,

                    //         "date_period" => $filter_year . "-" . $filter_month . "-01",

                    //         "assy_no" => $record['product_no'],

                    //         "date_schedule" => $date_schedule,

                    //         "qty" => "0",

                    //     );

                    // }

                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $no++;
                }
            }

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_create(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            // $this->pg2 = $this->load->database('pg2', TRUE);

            $post = $this->input->post('data');

            // $this->dummy->select('*');
            // $this->dummy->from("mst_item");
            // $this->dummy->where("item_id", $post['assy_no']);
            // $mst_item = $this->dummy->get()->result_array();

            $this->dummy->select('*');
            $this->dummy->from("wip_trx_mpp");
            $this->dummy->where("periode", $post['periode']);
            $this->dummy->where("line", $post['line']);
            $this->dummy->where("assy_no", $post['assy_no']);
            $this->dummy->where("wp", $post['wp']);
            $wip_trx_mpp = $this->dummy->get()->row();

            $this->dummy->select('*');
            $this->dummy->from("wip_trx_wds");
            $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
            $wip_trx_wds = $this->dummy->get()->result_array();

            //if(count($mst_item) > 0){
            if(count($wip_trx_wds) == 0){
                if($wip_trx_mpp){
                    $this->dummy->where("periode", $post['periode']);
                    $this->dummy->where("line", $post['line']);
                    $this->dummy->where("assy_no", $post['assy_no']);
                    $this->dummy->where("wp", $post['wp']);
                    if ($this->dummy->update('wip_trx_mpp', ["qty" => $post['qty'], "rev" => $post['rev']])) {
                        echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | Data Update Successfully", "theme" => "success"));

                        // $this->pg2->where("periode", $post['periode']);
                        // $this->pg2->where("line", $post['line']);
                        // $this->pg2->where("assy_no", $post['assy_no']);
                        // $this->pg2->where("wp", $post['wp']);
                        // $this->pg2->update('wip_trx_mpp', ["qty" => $post['qty'], "rev" => $post['rev']]);
                    } else {
                        echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Data Unsaved", "theme" => "error"));
                    }
                }else{
                    if ($this->dummy->insert('wip_trx_mpp', $post)) {
                        // $this->api->create('generate_mpp_detail', $post, "MPPD", "MPPD");
                        echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | Data Saved Successfully", "theme" => "success"));
                        // $this->pg2->insert('wip_trx_mpp', $post);
                    } else {
                        echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Data Unsaved", "theme" => "error"));
                    }
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => $post['serial_mpp'] . " | Already in WDS", "theme" => "error"));
            }
            // }else{

            //     echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Not Found in mst_item", "theme" => "error"));

            // }
        }
    }

    public function push_data_create_mpp_generate(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mpp_generate");
            $this->dummy->where("doc_mpp", $post['doc_mpp']);
            $mpp_generate = $this->dummy->get()->result_array();

            if(count($mpp_generate) <= 0){
                if ($this->dummy->insert('mpp_generate', $post)) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['doc_mpp'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['doc_mpp'] . " | Data Unsaved", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Duplicate", "message" => $post['doc_mpp'] . " | Duplicate Doc MPP in table mpp_generate", "theme" => "error"));
            }
        }
    }

    public function push_data_create_plan_schedule(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", $post['assy_no']);
            $mst_item = $this->dummy->get()->result_array();

            if(count($mst_item) > 0){
                if ($this->dummy->insert('plansys_schedule', $post)) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Data Unsaved", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Not Found in mst_item", "theme" => "error"));
            }
        }
    }

    public function push_data_create_plan_schedule_detail(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", $post['assy_no']);
            $mst_item = $this->dummy->get()->result_array();

            $this->dummy->select('*');
            $this->dummy->from("plansys_schedule_detail");
            $this->dummy->where("period", $post['period']);
            $this->dummy->where("assy_no", $post['assy_no']);
            $this->dummy->where("date_schedule", $post['date_schedule']);
            $plansys_schedule_detail = $this->dummy->get()->result_array();

            if(count($mst_item) > 0){
                if(count($plansys_schedule_detail) > 0){
                    echo json_encode(array("title" => "Duplicate", "message" => $post['assy_no'] . " | ".$post['period']." | Duplicate in table plansys_schedule_detail", "theme" => "error"));
                }else{
                    if ($this->dummy->insert('plansys_schedule_detail', $post)) {
                        echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | ".$post['date_schedule']." | Success", "theme" => "success"));
                    } else {
                        echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | ".$post['date_schedule']." | Unsaved", "theme" => "error"));
                    }
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | ".$post['date_schedule']." | Not Found", "theme" => "error"));
            }
        }
    }

    public function createv1()
    {
        if ($this->input->post('data')) {
            $post = $this->input->post('data');
            $post['p_month'] = str_pad($post['p_month'], 2, '0', STR_PAD_LEFT);

            $read = $this->crud->read("generate_mpp_compound", [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_fg_id" => $post['item_fg_id'],
                "item_rm_id" => $post['item_rm_id'],
            ]);

            if ($read) {
                $send = $this->crud->update('generate_mpp_compound', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_fg_id" => $post['item_fg_id'],
                    "item_rm_id" => $post['item_rm_id'],
                ], $post);
            } else {
                $send = $this->crud->create('generate_mpp_compound', $post, "MPP", "MPP");
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function create()
    // {
    //     if ($this->input->post('data')) {
    //         $post = $this->input->post('data');

    //         $post['p_month'] = str_pad($post['p_month'], 2, '0', STR_PAD_LEFT);

    //         // simpan header
    //         $headerExist = $this->crud->read("generate_mpp_compound", [], [
    //             "p_month" => $post['p_month'],
    //             "p_year" => $post['p_year'],
    //             "revision" => $post['revision'],
    //             "item_fg_id" => $post['item_fg_id'],
    //             "item_rm_id" => $post['item_rm_id'],
    //         ]);

    //         if (!$headerExist) {
    //             $this->crud->create('generate_mpp_compound', [
    //                 "p_month" => $post['p_month'],
    //                 "p_year" => $post['p_year'],
    //                 "revision" => $post['revision'],
    //                 "item_fg_id" => $post['item_fg_id'],
    //                 "item_rm_id" => $post['item_rm_id'],
    //                 "prod_plan" => $post['prod_plan'],
    //             ], "MPP", "MPP");
    //         }

    //         $dataDetail = [
    //             "p_month"    => $post['p_month'],
    //             "p_year"     => $post['p_year'],
    //             "revision"   => $post['revision'],
    //             "item_fg_id" => $post['item_fg_id'],
    //             "item_rm_id" => $post['item_rm_id'],
    //             "shift"      => $post['shift'],
    //             "plan_qty"   => $post['plan_qty'],
    //         ];

    //         for ($i = 1; $i <= 31; $i++) {
    //             $field = "date_$i";
    //             if(isset($post[$field])){
    //                 $dataDetail[$field] = $post[$field];
    //             }
    //         }

    //         $existDetail = $this->crud->read("generate_mpp_compound_details", [], [
    //             "p_month"    => $post['p_month'],
    //             "p_year"     => $post['p_year'],
    //             "revision"   => $post['revision'],
    //             "item_fg_id" => $post['item_fg_id'],
    //             "item_rm_id" => $post['item_rm_id'],
    //             "shift"      => $post['shift']
    //         ]);

    //         if ($existDetail) {
    //             $send = $this->crud->update(
    //                 'generate_mpp_compound_details',
    //                 [
    //                     "p_month"    => $post['p_month'],
    //                     "p_year"     => $post['p_year'],
    //                     "revision"   => $post['revision'],
    //                     "item_fg_id" => $post['item_fg_id'],
    //                     "item_rm_id" => $post['item_rm_id'],
    //                     "shift"      => $post['shift']
    //                 ],
    //                 $dataDetail
    //             );
    //         } else {
    //             $send = $this->crud->create('generate_mpp_compound_details', $dataDetail, "MPPD", "MPPD");
    //         }

    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post('data')) {
            $post = $this->input->post('data');

            // normalisasi bulan
            $post['p_month'] = str_pad($post['p_month'], 2, '0', STR_PAD_LEFT);

            // ambil input (bisa datang sebagai '' atau 0) -> jadikan null jika kosong
            $item_fg_id = !empty($post['item_fg_id']) ? $post['item_fg_id'] : null;
            $item_rm_id = !empty($post['item_rm_id']) ? $post['item_rm_id'] : null;

            // Aturan: kalau keduanya ada -> prioritaskan FG, kosongkan RM
            if ($item_fg_id && $item_rm_id) {
                $item_rm_id = null;
            }

            // Pastikan setidaknya satu ID ada
            if (!$item_fg_id && !$item_rm_id) {
                show_error("Invalid data: item_fg_id or item_rm_id must be provided");
                return;
            }

            // Build where dasar untuk header (cek berdasarkan ID yang dipilih)
            $whereHeader = [
                "p_month"  => $post['p_month'],
                "p_year"   => $post['p_year'],
                "revision" => $post['revision'],
            ];
            if ($item_fg_id) {
                $whereHeader["item_fg_id"] = $item_fg_id;
                // pastikan item_rm_id tetap null di data yang disimpan
                $saveItemFgId = $item_fg_id;
                $saveItemRmId = null;
            } else {
                $whereHeader["item_rm_id"] = $item_rm_id;
                $saveItemFgId = null;
                $saveItemRmId = $item_rm_id;
            }

            // cek header existence (berdasarkan ID yang relevan)
            $headerExist = $this->crud->read("generate_mpp_compound", [], $whereHeader);

            if (!$headerExist) {
                $this->crud->create('generate_mpp_compound', [
                    "p_month"    => $post['p_month'],
                    "p_year"     => $post['p_year'],
                    "revision"   => $post['revision'],
                    "item_fg_id" => $saveItemFgId,
                    "item_rm_id" => $saveItemRmId,
                    "prod_plan"  => isset($post['prod_plan']) ? $post['prod_plan'] : 0,
                    "max_prod_plan"  => isset($post['max_prod_plan']) ? $post['max_prod_plan'] : 0,
                ], "MPP", "MPP");
            }

            // siapkan data detail sesuai aturan ID yang sama
            $dataDetail = [
                "p_month"    => $post['p_month'],
                "p_year"     => $post['p_year'],
                "revision"   => $post['revision'],
                "item_fg_id" => $saveItemFgId,
                "item_rm_id" => $saveItemRmId,
                "shift"      => $post['shift'],
                "plan_qty"   => $post['plan_qty'],
            ];

            for ($i = 1; $i <= 31; $i++) {
                $field = "date_$i";
                if (isset($post[$field])) {
                    $dataDetail[$field] = $post[$field];
                }
            }

            // cek detail existence berdasarkan same key (ID yang relevan + shift)
            $whereDetail = [
                "p_month"    => $post['p_month'],
                "p_year"     => $post['p_year'],
                "revision"   => $post['revision'],
                "shift"      => $post['shift'],
            ];
            if ($saveItemFgId) {
                $whereDetail['item_fg_id'] = $saveItemFgId;
            } else {
                $whereDetail['item_rm_id'] = $saveItemRmId;
            }

            $existDetail = $this->crud->read("generate_mpp_compound_details", [], $whereDetail);

            if ($existDetail) {
                $send = $this->crud->update(
                    'generate_mpp_compound_details',
                    $whereDetail,
                    $dataDetail
                );
            } else {
                $send = $this->crud->create('generate_mpp_compound_details', $dataDetail, "MPPD", "MPPD");
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }


    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('generate_mpp_compound_details', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/generate_mpp_compound.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/generate_mpp_compound.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/generate_mpp_compound.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    // public function printv1($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_mpp_compound_$format.xls");
    //     }
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     //Filter Data
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision2 = base64_decode($this->input->get('filter_revision'));
    //     // $filter_line_no = base64_decode($this->input->get('filter_line_no'));
    //     $filter_product_no = base64_decode($this->input->get('filter_product_no'));

    //     $this->db->select('revision');
    //     $this->db->from('generate_mpp_compound');
    //     $this->db->where('p_month', $filter_month);
    //     $this->db->where('p_year', $filter_year);
    //     $this->db->group_by('revision');
    //     $this->db->order_by('revision', 'desc');
    //     $revisions = $this->db->get()->row();

    //     $filter_revision = $revisions->revision;

    //     $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

    //     //MPS
    //     // $this->db->select("a.line_no, b.remarks");
    //     // $this->db->select("a.*");
    //     // $this->db->from('generate_mpp a');
    //     // // $this->db->join('mst_line b', 'a.line_no = b.line_no');
    //     // $this->db->where('a.p_month', $filter_month);
    //     // $this->db->where('a.p_year', $filter_year);
    //     // $this->db->where('a.revision', $filter_revision);
    //     // // $this->db->like('a.line_no', $filter_line_no);
    //     // $this->db->like('a.item_fg_id', $filter_product_no);
    //     // $this->db->group_by('a.item_fg_id');
    //     // // $this->db->limit(1);
    //     // $records = $this->db->get()->result_array();

    //     //Total UMH CUtting
    //     // $qTotalCutting = $this->crud->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity
    //     //     FROM `generate_loadcap_line` 
    //     //     WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 
    //     //     and process_sub_number IN ('CUT100','CUT200','CUT300','CUT400','CUT500')");

    //     $hkw = 0;

    //     $ltppMonth = $filter_year . "-" . $filter_month . "-01";

    //     $monthStart = strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01"));

    //     $start = strtotime(date('Y-m-01', $monthStart));

    //     $finish = strtotime(date('Y-m-t', $monthStart));

    //     for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {

    //         $working_date = date('Y-m-d', $z);



    //         $this->db->select('remarks');

    //         $this->db->from('working_calendar');

    //         $this->db->where('working_date', $working_date);

    //         $holiday = $this->db->get()->row();



    //         if (date('w', $z) !== '0') {

    //             if (@$holiday->remarks != null or @$holiday->remarks != "") {

    //                 $hkw += 0;

    //             } else {

    //                 $hkw += 1;

    //             }

    //         } else {

    //             $hkw += 0;

    //         }

    //     }



    //     //Setting Header
    //     $styles = "";

    //     // $header = '<tr>
    //     //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
    //     //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

    //     // $tmpDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     // $endDate = date('Y-m-t',  strtotime($filter_year . "-" . $filter_month . "-01"));

    //     // // Row-1: judul "WP" per hari (colspan 2)
    //     // while (strtotime($tmpDate) <= strtotime($endDate)) {
    //     //     $header .= '<th colspan="2" style="text-align:center;">WP</th>';
    //     //     $tmpDate = date("Y-m-d", strtotime("+1 day", strtotime($tmpDate)));
    //     // }
    //     // $header .= '</tr>';
        
    //     // $header .= '<tr>';

    //     // while (strtotime($firstDate) <= strtotime($endDate)) {
    //     //     $header .= '<th width="30" colspan="2" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';
    //     //     $header .= '<th style="text-align:center;">CT (0)</th>';
    //     //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //     // }

    //     // $header .= '</tr>';


    //     // $header  = '<tr>
    //     //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
    //     //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

    //     // // Row-1: judul "WP" per hari (colspan 2)
    //     // while (strtotime($firstDate) <= strtotime($endDate)) {
    //     //     $header .= '<th colspan="2" style="text-align:center;">WP</th>';
    //     //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //     // }
    //     // $header .= '</tr>';

    //     // // Row-2: subkolom (tanggal) dan CT(0)
    //     // $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     // $day     = 1;
    //     // $header .= '<tr>';
    //     // while (strtotime($firstDate) <= strtotime($endDate)) {
    //     //     // jika mau full tanggal, ganti $day dengan date("Y-m-d", strtotime($firstDate))
    //     //     $header .= '<th style="text-align:center;">' . $day . '</th>';
    //     //     $header .= '<th style="text-align:center;">CT</th>';
    //     //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //     //     $day++;
    //     // }
    //     // $header .= '</tr>';

    //     // $qty = '<tr><th width="50" style="text-align:center;">PRODPLAN</th>';
    //     // $plotting = '<tr><th width="50" style="text-align:center;">PLOTTING</th>';

    //     // while (strtotime($firstDate) <= strtotime($endDate)) {
    //     //     //Setting Header
    //     //     $qty .= '<th width="30" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';
    //     //     $plotting .= '<th width="30" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';
    //     //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //     // }

    //     // //Setting Header
    //     // $qty .= '</tr>';
    //     // $plotting .= '</tr>';


    //     // Inisialisasi header
    //     // $header  = '<tr>
    //     //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
    //     //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

    //     $header  = '<tr>
    //         <th style="text-align:center;" rowspan="2" width="50">NO</th>
    //         <th style="text-align:center;" rowspan="2" width="200">PRODUCT NO</th>
    //         <th style="text-align:center;" rowspan="2" width="150">PRODUCT NAME</th>
    //         <th style="text-align:center;" rowspan="2" width="80">PRODPLAN</th>
    //         <th style="text-align:center;" rowspan="2" width="80">PLOTTING</th>';


    //     $wp = 0;
    //     $tgl = 1;
    //     $alfabet = "z";
    //     $firstDate_loop = $firstDate; // simpan pointer asli
    //     while (strtotime($firstDate_loop) <= strtotime($endDate)) {
    //         $working_date = date('Y-m-d', strtotime($firstDate_loop));

    //         $this->db->select('remarks');
    //         $this->db->from('working_calendar');
    //         $this->db->where('working_date', $working_date);
    //         $holiday = $this->db->get()->row();

    //         // LOGIKA WP
    //         if (date('w', strtotime($firstDate_loop)) !== '0' && date('w', strtotime($firstDate_loop)) !== '6') {
    //             if (@$holiday->remarks != null && @$holiday->remarks != "") {
    //                 // hari libur
    //                 if($alfabet == "z") $alfabets = "A";
    //                 elseif($alfabet == "A") $alfabets = "B";
    //                 elseif($alfabet == "B") $alfabets = "C";
    //                 elseif($alfabet == "C") $alfabets = "D";
    //                 elseif($alfabet == "D") $alfabets = "E";
    //                 elseif($alfabet == "E") $alfabets = "F";
    //                 elseif($alfabet == "F") $alfabets = "G";
    //                 elseif($alfabet == "G") $alfabets = "H";
    //                 elseif($alfabet == "H") $alfabets = "I";
    //                 elseif($alfabet == "I") $alfabets = "J";
    //                 elseif($alfabet == "J") $alfabets = "K";
    //                 elseif($alfabet == "K") $alfabets = "L";
    //                 elseif($alfabet == "L") $alfabets = "M";
    //                 elseif($alfabet == "M") $alfabets = "N";
    //                 elseif($alfabet == "N") $alfabets = "O";
    //                 else $alfabets = "";

    //                 $wpp = "WP ".$wp.$alfabets;
    //                 $alfabet = $alfabets;

    //                 // cek hari besok, kalau bukan libur/weekend → naik WP
    //                 $next_date = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
    //                 $this->db->select('remarks');
    //                 $this->db->from('working_calendar');
    //                 $this->db->where('working_date', $next_date);
    //                 $holiday_check = $this->db->get()->row();
    //                 if (date('w', strtotime($next_date)) !== '0' && date('w', strtotime($next_date)) !== '6') {
    //                     if (@$holiday_check->remarks == null || @$holiday_check->remarks == "") {
    //                         $wp++;
    //                     }
    //                 }
    //             } else {
    //                 // hari kerja normal
    //                 if($wp == 0) $wp = 1;
    //                 $wpp = "WP ".$wp;
    //                 $alfabet = "z";

    //                 $next_date = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
    //                 $this->db->select('remarks');
    //                 $this->db->from('working_calendar');
    //                 $this->db->where('working_date', $next_date);
    //                 $holiday_check = $this->db->get()->row();
    //                 if (date('w', strtotime($next_date)) !== '0' && date('w', strtotime($next_date)) !== '6') {
    //                     if (@$holiday_check->remarks == null || @$holiday_check->remarks == "") {
    //                         $wp++;
    //                     }
    //                 }
    //             }
    //         } else {
    //             // weekend → logika sama seperti libur
    //             if($alfabet == "z") $alfabets = "A";
    //             elseif($alfabet == "A") $alfabets = "B";
    //             elseif($alfabet == "B") $alfabets = "C";
    //             elseif($alfabet == "C") $alfabets = "D";
    //             elseif($alfabet == "D") $alfabets = "E";
    //             elseif($alfabet == "E") $alfabets = "F";
    //             elseif($alfabet == "F") $alfabets = "G";
    //             elseif($alfabet == "G") $alfabets = "H";
    //             elseif($alfabet == "H") $alfabets = "I";
    //             elseif($alfabet == "I") $alfabets = "J";
    //             elseif($alfabet == "J") $alfabets = "K";
    //             elseif($alfabet == "K") $alfabets = "L";
    //             elseif($alfabet == "L") $alfabets = "M";
    //             elseif($alfabet == "M") $alfabets = "N";
    //             elseif($alfabet == "N") $alfabets = "O";
    //             else $alfabets = "";

    //             $wpp = "WP ".$wp.$alfabets;
    //             $alfabet = $alfabets;

    //             $next_date = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
    //             $this->db->select('remarks');
    //             $this->db->from('working_calendar');
    //             $this->db->where('working_date', $next_date);
    //             $holiday_check = $this->db->get()->row();
    //             if (date('w', strtotime($next_date)) !== '0' && date('w', strtotime($next_date)) !== '6') {
    //                 if (@$holiday_check->remarks == null || @$holiday_check->remarks == "") {
    //                     $wp++;
    //                 }
    //             }
    //         }

    //         $header .= '<th colspan="2" style="text-align:center;">'.$wpp.'</th>';

    //         $tgl++;
    //         $firstDate_loop = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
    //     }
    //     $header .= '</tr>';

    //     $firstDate_loop = $firstDate;
    //     $header .= '<tr>';
    //     $day = 1;
    //     while (strtotime($firstDate_loop) <= strtotime($endDate)) {
    //         $header .= '<th style="text-align:center;">'.$day.'</th>';
    //         $header .= '<th style="text-align:center;">CT</th>';
    //         $firstDate_loop = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
    //         $day++;
    //     }
    //     $header .= '</tr>';


    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>

    //     <center>

    //     <div style="float: left; font-size: 12px; text-align: left;">

    //     <table style="width: 100%;">

    //     <tr>

    //     <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

    //     <img src="' . $config->logo . '" width="30">

    //     </td>

    //     <td style="font-size: 14px; text-align: left; margin:2px;">

    //     <b>' . $config->name . '</b><br>

    //     <small>Generate MPP - Compound</small>

    //     </td>

    //     </tr>

    //     </table>

    //     </div>

    //     <div style="float: right; font-size: 12px; text-align: right;">

    //     Print Date ' . date("Y-m-d Y H:m:s") . ' <br>

    //     Print By ' . $this->session->username . '  

    //     </div>

    //     </center>

    //     <br>

    //     <table id="customers" border="1">

    //     <tr>

    //     <th style="text-align:center;">DISETUJUI</th>

    //     <th style="text-align:center;">DIKETAHUI</th>

    //     <th style="text-align:center;">DIPERIKSA</th>

    //     <th style="text-align:center;">DIBUAT</th>

    //     </tr>

    //     <tr>

    //     <th style="height:70px;"></th>

    //     <th style="height:70px;"></th>

    //     <th style="height:70px;"></th>

    //     <th style="height:70px;"></th>

    //     </tr>

    //     <tr>

    //     <th style="text-align:center; height:20px;"></th>

    //     <th style="text-align:center; height:20px;"></th>

    //     <th style="text-align:center; height:20px;"></th>

    //     <th style="text-align:center; height:20px;">'.$this->session->name.'</th>

    //     </tr>

    //     </table>

    //     <br>

    //     <table id="customers" border="1">';

    //     $html .= $header;


    //     $no = 1;



    //     // $total_mpp_prodplan = 0;



    //     // $total_cct_prodplan = 0;

    //     // $total_cct_plotting = 0;

    //     // $total_cct_cutting = 0;


    //     //Total MPP

    //     // $total_mpp_date_1 = 0;

    //     // $total_mpp_date_2 = 0;

    //     // $total_mpp_date_3 = 0;

    //     // $total_mpp_date_4 = 0;

    //     // $total_mpp_date_5 = 0;

    //     // $total_mpp_date_6 = 0;

    //     // $total_mpp_date_7 = 0;

    //     // $total_mpp_date_8 = 0;

    //     // $total_mpp_date_9 = 0;

    //     // $total_mpp_date_10 = 0;

    //     // $total_mpp_date_11 = 0;

    //     // $total_mpp_date_12 = 0;

    //     // $total_mpp_date_13 = 0;

    //     // $total_mpp_date_14 = 0;

    //     // $total_mpp_date_15 = 0;

    //     // $total_mpp_date_16 = 0;

    //     // $total_mpp_date_17 = 0;

    //     // $total_mpp_date_18 = 0;

    //     // $total_mpp_date_19 = 0;

    //     // $total_mpp_date_20 = 0;

    //     // $total_mpp_date_21 = 0;

    //     // $total_mpp_date_22 = 0;

    //     // $total_mpp_date_23 = 0;

    //     // $total_mpp_date_24 = 0;

    //     // $total_mpp_date_25 = 0;

    //     // $total_mpp_date_26 = 0;

    //     // $total_mpp_date_27 = 0;

    //     // $total_mpp_date_28 = 0;

    //     // $total_mpp_date_29 = 0;

    //     // $total_mpp_date_30 = 0;

    //     // $total_mpp_date_31 = 0;



    //     //Total Press

    //     // $total_press_date_1 = 0;

    //     // $total_press_date_2 = 0;

    //     // $total_press_date_3 = 0;

    //     // $total_press_date_4 = 0;

    //     // $total_press_date_5 = 0;

    //     // $total_press_date_6 = 0;

    //     // $total_press_date_7 = 0;

    //     // $total_press_date_8 = 0;

    //     // $total_press_date_9 = 0;

    //     // $total_press_date_10 = 0;

    //     // $total_press_date_11 = 0;

    //     // $total_press_date_12 = 0;

    //     // $total_press_date_13 = 0;

    //     // $total_press_date_14 = 0;

    //     // $total_press_date_15 = 0;

    //     // $total_press_date_16 = 0;

    //     // $total_press_date_17 = 0;

    //     // $total_press_date_18 = 0;

    //     // $total_press_date_19 = 0;

    //     // $total_press_date_20 = 0;

    //     // $total_press_date_21 = 0;

    //     // $total_press_date_22 = 0;

    //     // $total_press_date_23 = 0;

    //     // $total_press_date_24 = 0;

    //     // $total_press_date_25 = 0;

    //     // $total_press_date_26 = 0;

    //     // $total_press_date_27 = 0;

    //     // $total_press_date_28 = 0;

    //     // $total_press_date_29 = 0;

    //     // $total_press_date_30 = 0;

    //     // $total_press_date_31 = 0;



    //     // foreach ($records as $record) {

    //         // $html .= "<tr>

    //         // <th style='text-align:left;' colspan='48'>Remarks</th>

    //         // </tr>";



    //         // $line_no = $record['line_no'];
    //         // $line_no = '1';

    //         $this->db->select("a.*, e.number as product_no, e.name as product_name, e.lot, COALESCE(ml.cycle_time, 0) as cycle_time, d.prod_plan as prodplan, (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as plotting");

    //         $this->db->from('generate_mpp_compound a');

    //         // $this->db->join('item_fg b', 'a.item_fg_id = b.id');

    //         // $this->db->join('wip_mst_wos_cct c', 'a.item_fg_id = c.mstwos_assyno', 'left');

    //         $this->db->join("(SELECT * FROM generate_mps_details ORDER BY ltpp_month2 ASC) d", "LPAD(d.p_month, 2, '0') = '$filter_month' and d.p_year = '$filter_year' and d.revision = '$filter_revision2' and a.item_fg_id = d.item_fg_id");

    //         $this->db->join("item_fg e", "a.item_fg_id = e.id");

    //         $this->db->join("menu_loadings ml", "a.item_fg_id = ml.item_fg_id", "left");

    //         $this->db->where('a.p_month', $filter_month);

    //         $this->db->where('a.p_year', $filter_year);

    //         $this->db->where('a.revision', $filter_revision);

    //         // $this->db->where('a.line_no', $line_no);

    //         $this->db->like('e.number', $filter_product_no);
            
    //         // $this->db->limit(6);

    //         $this->db->group_by('a.item_fg_id');

    //         $recordDetails = $this->db->get()->result_array();


    //         $total_ct_per_date = [];
    //         $arr_total_mpp   = [];
    //         $arr_total_press = [];

    //         for ($i = 1; $i <= 31; $i++) {
    //             $arr_total_mpp["date_$i"]   = 0;
    //             $arr_total_press["date_$i"] = 0;
    //             $total_ct_per_date[$i] = 0;
    //         }

    //         foreach ($recordDetails as $detail) {

    //             // $total_mpp_prodplan += $detail['prodplan'];

    //             // $total_cct_cutting += 1;

    //             // $total_cct_prodplan += ($detail['prodplan'] * $detail['circuit_no']);
    //             // $total_cct_prodplan += ($detail['prodplan'] * $detail['cycle_time'] / 3600);
    //             // $total_cct_plotting += $detail['plotting'];

    //             $html .= "<tr>

    //             <td>" . $no . "</td>

    //             <td style='mso-number-format:\@;'>" . $detail['product_no'] . "</td>


    //             <td>" . $detail['product_name'] . "</td>

    //             <td>" . $detail['prodplan'] . "</td>

    //             <td>" . $detail['plotting'] . "</td>";


    //             //Detail Data

    //             $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //             $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
    //             $day = 1;

    //             // $total_mpp_day = 0;

    //             while (strtotime($firstDate2) <= strtotime($endDate2)) {

    //                 $field_day = "date_".$day;

    //                 $dayValue = isset($detail[$field_day]) ? $detail[$field_day] : 0;


    //                 if (@$detail[$field_day] == "W") {
    //                     $ctValue = 0;
    //                     $qtyField = "";

    //                     $html .= "<td style='background:#FFC2C2;'>" . $qtyField . "</td>";
    //                     $html .= "<td style='background:#FFC2C2;'>" . $qtyField . "</td>";

    //                     $arr_total_press[$field_day] += $ctValue;
    //                     $arr_total_mpp[$field_day] += 0;
    //                 } else {

    //                     $html .= "<td>" . $detail[$field_day] . "</td>";
                        
    //                     $ctValue = 0;
    //                     if(is_numeric($dayValue)) {
    //                         $ctValue = round(($dayValue * $detail['cycle_time']) / 3600);
    //                     }

    //                     $html .= "<td>" . $ctValue . "</td>";

    //                     $arr_total_press[$field_day] += $ctValue;
    //                     $arr_total_mpp[$field_day] += is_numeric($dayValue) ? round($dayValue) : 0;
    //                 }

    //                 $total_ct_per_date[$day] += $ctValue;

    //                 $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

    //                 $day++;

    //             }


    //             //Total MPP Day

    //             // $total_mpp_date_1 += is_numeric($detail["date_1"]) ? $detail["date_1"] : 0;

    //             // $total_mpp_date_2 += is_numeric($detail["date_2"]) ? $detail["date_2"] : 0;

    //             // $total_mpp_date_3 += is_numeric($detail["date_3"]) ? $detail["date_3"] : 0;

    //             // $total_mpp_date_4 += is_numeric($detail["date_4"]) ? $detail["date_4"] : 0;

    //             // $total_mpp_date_5 += is_numeric($detail["date_5"]) ? $detail["date_5"] : 0;

    //             // $total_mpp_date_6 += is_numeric($detail["date_6"]) ? $detail["date_6"] : 0;

    //             // $total_mpp_date_7 += is_numeric($detail["date_7"]) ? $detail["date_7"] : 0;

    //             // $total_mpp_date_8 += is_numeric($detail["date_8"]) ? $detail["date_8"] : 0;

    //             // $total_mpp_date_9 += is_numeric($detail["date_9"]) ? $detail["date_9"] : 0;

    //             // $total_mpp_date_10 += is_numeric($detail["date_10"]) ? $detail["date_10"] : 0;

    //             // $total_mpp_date_11 += is_numeric($detail["date_11"]) ? $detail["date_11"] : 0;

    //             // $total_mpp_date_12 += is_numeric($detail["date_12"]) ? $detail["date_12"] : 0;

    //             // $total_mpp_date_13 += is_numeric($detail["date_13"]) ? $detail["date_13"] : 0;

    //             // $total_mpp_date_14 += is_numeric($detail["date_14"]) ? $detail["date_14"] : 0;

    //             // $total_mpp_date_15 += is_numeric($detail["date_15"]) ? $detail["date_15"] : 0;

    //             // $total_mpp_date_16 += is_numeric($detail["date_16"]) ? $detail["date_16"] : 0;

    //             // $total_mpp_date_17 += is_numeric($detail["date_17"]) ? $detail["date_17"] : 0;

    //             // $total_mpp_date_18 += is_numeric($detail["date_18"]) ? $detail["date_18"] : 0;

    //             // $total_mpp_date_19 += is_numeric($detail["date_19"]) ? $detail["date_19"] : 0;

    //             // $total_mpp_date_20 += is_numeric($detail["date_20"]) ? $detail["date_20"] : 0;

    //             // $total_mpp_date_21 += is_numeric($detail["date_21"]) ? $detail["date_21"] : 0;

    //             // $total_mpp_date_22 += is_numeric($detail["date_22"]) ? $detail["date_22"] : 0;

    //             // $total_mpp_date_23 += is_numeric($detail["date_23"]) ? $detail["date_23"] : 0;

    //             // $total_mpp_date_24 += is_numeric($detail["date_24"]) ? $detail["date_24"] : 0;

    //             // $total_mpp_date_25 += is_numeric($detail["date_25"]) ? $detail["date_25"] : 0;

    //             // $total_mpp_date_26 += is_numeric($detail["date_26"]) ? $detail["date_26"] : 0;

    //             // $total_mpp_date_27 += is_numeric($detail["date_27"]) ? $detail["date_27"] : 0;

    //             // $total_mpp_date_28 += is_numeric($detail["date_28"]) ? $detail["date_28"] : 0;

    //             // $total_mpp_date_29 += is_numeric($detail["date_29"]) ? $detail["date_29"] : 0;

    //             // $total_mpp_date_30 += is_numeric($detail["date_30"]) ? $detail["date_30"] : 0;

    //             // $total_mpp_date_31 += is_numeric($detail["date_31"]) ? $detail["date_31"] : 0;



    //             //Total Press Day
    //             // $total_press_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail["cycle_time"] / 3600) : 0;

    //             // $total_press_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail["cycle_time"] / 3600) : 0;

    //             $no++;

    //         }

    //         $html .= "</tr>";

    //     // }

    //     // $arr_total_mpp = array(
    //     //     "date_1" => $total_mpp_date_1,
    //     //     "date_2" => $total_mpp_date_2,
    //     //     "date_3" => $total_mpp_date_3,
    //     //     "date_4" => $total_mpp_date_4,
    //     //     "date_5" => $total_mpp_date_5,
    //     //     "date_6" => $total_mpp_date_6,
    //     //     "date_7" => $total_mpp_date_7,
    //     //     "date_8" => $total_mpp_date_8,
    //     //     "date_9" => $total_mpp_date_9,
    //     //     "date_10" => $total_mpp_date_10,
    //     //     "date_11" => $total_mpp_date_11,
    //     //     "date_12" => $total_mpp_date_12,
    //     //     "date_13" => $total_mpp_date_13,
    //     //     "date_14" => $total_mpp_date_14,
    //     //     "date_15" => $total_mpp_date_15,
    //     //     "date_16" => $total_mpp_date_16,
    //     //     "date_17" => $total_mpp_date_17,
    //     //     "date_18" => $total_mpp_date_18,
    //     //     "date_19" => $total_mpp_date_19,
    //     //     "date_20" => $total_mpp_date_20,
    //     //     "date_21" => $total_mpp_date_21,
    //     //     "date_22" => $total_mpp_date_22,
    //     //     "date_23" => $total_mpp_date_23,
    //     //     "date_24" => $total_mpp_date_24,
    //     //     "date_25" => $total_mpp_date_25,
    //     //     "date_26" => $total_mpp_date_26,
    //     //     "date_27" => $total_mpp_date_27,
    //     //     "date_28" => $total_mpp_date_28,
    //     //     "date_29" => $total_mpp_date_29,
    //     //     "date_30" => $total_mpp_date_30,
    //     //     "date_31" => $total_mpp_date_31
    //     // );

    //     // $arr_total_press = array(
    //     //     "date_1" => $total_press_date_1,
    //     //     "date_2" => $total_press_date_2,
    //     //     "date_3" => $total_press_date_3,
    //     //     "date_4" => $total_press_date_4,
    //     //     "date_5" => $total_press_date_5,
    //     //     "date_6" => $total_press_date_6,
    //     //     "date_7" => $total_press_date_7,
    //     //     "date_8" => $total_press_date_8,
    //     //     "date_9" => $total_press_date_9,
    //     //     "date_10" => $total_press_date_10,
    //     //     "date_11" => $total_press_date_11,
    //     //     "date_12" => $total_press_date_12,
    //     //     "date_13" => $total_press_date_13,
    //     //     "date_14" => $total_press_date_14,
    //     //     "date_15" => $total_press_date_15,
    //     //     "date_16" => $total_press_date_16,
    //     //     "date_17" => $total_press_date_17,
    //     //     "date_18" => $total_press_date_18,
    //     //     "date_19" => $total_press_date_19,
    //     //     "date_20" => $total_press_date_20,
    //     //     "date_21" => $total_press_date_21,
    //     //     "date_22" => $total_press_date_22,
    //     //     "date_23" => $total_press_date_23,
    //     //     "date_24" => $total_press_date_24,
    //     //     "date_25" => $total_press_date_25,
    //     //     "date_26" => $total_press_date_26,
    //     //     "date_27" => $total_press_date_27,
    //     //     "date_28" => $total_press_date_28,
    //     //     "date_29" => $total_press_date_29,
    //     //     "date_30" => $total_press_date_30,
    //     //     "date_31" => $total_press_date_31
    //     // );


    //     //TOTAL CUTTING

    //     // $persenCutting = @round(($qTotalCutting[0]->total / $qTotalCutting[0]->total_capacity) * 100, 2);
    //     $persenCutting = 0;

    //     $style = ""; 

    //     if($persenCutting >= 100){

    //         $style = "style='background:#FFD8D8;'";

    //     }



    //     $html .= "  <tr>

    //     <th colspan='5' style='text-align:center;'><b>TOTAL MPP</b></th>";

    //     $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

    //     $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

    //     $day = 1;

    //     while (strtotime($firstDate2) <= strtotime($endDate2)) {

    //         $field_day = "date_".$day;

    //         $working_date = date('Y-m-d', strtotime($firstDate2));
    //         $dayOfWeek    = date('w', strtotime($firstDate2));

    //         $holiday = $this->db->select('remarks')
    //                             ->from('working_calendar')
    //                             ->where('working_date', $working_date)
    //                             ->get()
    //                             ->row();

    //         $isHoliday = ($dayOfWeek == 0 || $dayOfWeek == 6 || !empty($holiday->remarks));

    //         $symbol = $isHoliday ? "0" : "<center>-</center>";

    //         if($option == "excel") {
    //             $html .= "<th style='text-align:right;'>".number_format($arr_total_mpp[$field_day])."</th>";
    //             $html .= "<th style='text-align:right;'>{$symbol}</th>";
    //         } else {   
    //             $html .= "<th>".number_format($arr_total_mpp[$field_day])."</th>";
    //             $html .= "<th>{$symbol}</th>";
    //         }

    //         $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

    //         $day++;

    //     }

    //     $html .= "  </tr>";


    //     // LOADING
    //     $total_ct_all = array_sum($total_ct_per_date);
    //     $html .= "<tr ".$style.">
    //         <td rowspan='2' colspan='2' style='text-align:left; vertical-align:middle;'><b>TOTAL PRESS</b></td>
    //         <td colspan='2'><b>LOADING</b></td>
    //         <td><b>". number_format($total_ct_all) ."</b></td>";

    //     $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     $day = 1;

    //     while (strtotime($firstDate2) <= strtotime($endDate2)) {
    //         $field_day = "date_".$day;
    //         $totalCt = isset($total_ct_per_date[$day]) ? $total_ct_per_date[$day] : 0;

    //         $working_date = date('Y-m-d', strtotime($firstDate2));
    //         $dayOfWeek    = date('w', strtotime($firstDate2));

    //         // cek libur
    //         $holiday = $this->db->select('remarks')
    //                             ->from('working_calendar')
    //                             ->where('working_date', $working_date)
    //                             ->get()
    //                             ->row();

    //         // tentukan apakah hari ini libur/weekend
    //         $isHoliday = ($dayOfWeek == 0 || $dayOfWeek == 6 || !empty($holiday->remarks));

    //         // pilih simbol
    //         $symbol = $isHoliday ? "0" : "<center>-</center>";

    //         if ($option == "excel") {
    //             $html .= "<th style='text-align:right;'>{$symbol}</th>";
    //             $html .= "<th style='text-align:right;'>" . number_format($totalCt) . "</th>";
    //         } else {
    //             $html .= "<th>{$symbol}</th>";
    //             $html .= "<th>" . number_format($totalCt) . "</th>";
    //         }


    //         $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
    //         $day++;
    //     }

    //     // CAPACITY
    //     $html .= "<tr ".$style.">
    //         <td colspan='2'><b>CAPACITY</b></td>
    //         <td><b>0</b></td>";

    //     $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
    //     $day = 1;

    //     while (strtotime($firstDate2) <= strtotime($endDate2)) {
    //         $html .= "<td><b>0</b></td>";
    //         $html .= "<td><b>0</b></td>";
    //         $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
    //         $day++;
    //     }

    //     $html .= "</tr>";


    //     $html .= "</tr>";




    //     $html .= "</table>";

    //     echo $html;

    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_mpp_compound_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        // $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp_compound');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        // $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        // $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));


        $firstDate = date('Y-m-d', strtotime($filter_year.'-'.$filter_month.'-01 -3 days'));
        $endDate   = date('Y-m-t', strtotime($filter_year.'-'.$filter_month.'-01'));

        //MPS
        // $this->db->select("a.line_no, b.remarks");
        // $this->db->select("a.*");
        // $this->db->from('generate_mpp a');
        // // $this->db->join('mst_line b', 'a.line_no = b.line_no');
        // $this->db->where('a.p_month', $filter_month);
        // $this->db->where('a.p_year', $filter_year);
        // $this->db->where('a.revision', $filter_revision);
        // // $this->db->like('a.line_no', $filter_line_no);
        // $this->db->like('a.item_fg_id', $filter_product_no);
        // $this->db->group_by('a.item_fg_id');
        // // $this->db->limit(1);
        // $records = $this->db->get()->result_array();

        //Total UMH CUtting
        // $qTotalCutting = $this->crud->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity
        //     FROM `generate_loadcap_line` 
        //     WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 
        //     and process_sub_number IN ('CUT100','CUT200','CUT300','CUT400','CUT500')");

        $hkw = 0;

        // $ltppMonth = $filter_year . "-" . $filter_month . "-01";

        $monthStart = strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01"));

        $start = strtotime(date('Y-m-01', $monthStart));

        $finish = strtotime(date('Y-m-t', $monthStart));

        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {

            $working_date = date('Y-m-d', $z);



            $this->db->select('remarks');

            $this->db->from('working_calendar');

            $this->db->where('working_date', $working_date);

            $holiday = $this->db->get()->row();



            if (date('w', $z) !== '0') {

                if (@$holiday->remarks != null or @$holiday->remarks != "") {

                    $hkw += 0;

                } else {

                    $hkw += 1;

                }

            } else {

                $hkw += 0;

            }

        }


        // Inisialisasi header
        // $header  = '<tr>
        //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
        //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

        $header  = '<tr>
            <th style="text-align:center;" rowspan="2" width="50">NO</th>
            <th style="text-align:center;" rowspan="2" width="200">MACHINE NO</th>
            <th style="text-align:center;" rowspan="2" width="80">SHIFT</th>
            <th style="text-align:center;" rowspan="2" width="200">COMPOUND NO</th>
            <th style="text-align:center;" rowspan="2" width="150">PRODUCT NAME</th>
            <th style="text-align:center;" rowspan="2" width="80">UOM</th>
            <th style="text-align:center;" rowspan="2" width="80">PRODPLAN</th>
            <th style="text-align:center;" rowspan="2" width="80">PLOTTING</th>
            <th style="text-align:center;" rowspan="2" width="100">CAP/SHIFT</th>';

        $wp = 0;
        $tgl = 1;
        $alfabet = "z";
        $firstDate_loop = $firstDate;
        while (strtotime($firstDate_loop) <= strtotime($endDate) && $tgl <= 31) {
            $working_date = date('Y-m-d', strtotime($firstDate_loop));

            $this->db->select('remarks');
            $this->db->from('working_calendar');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($firstDate_loop)) !== '0' && date('w', strtotime($firstDate_loop)) !== '6') {
                if (@$holiday->remarks != null && @$holiday->remarks != "") {
                    // hari libur
                    if($alfabet == "z") $alfabets = "A";
                    elseif($alfabet == "A") $alfabets = "B";
                    elseif($alfabet == "B") $alfabets = "C";
                    elseif($alfabet == "C") $alfabets = "D";
                    elseif($alfabet == "D") $alfabets = "E";
                    elseif($alfabet == "E") $alfabets = "F";
                    elseif($alfabet == "F") $alfabets = "G";
                    elseif($alfabet == "G") $alfabets = "H";
                    elseif($alfabet == "H") $alfabets = "I";
                    elseif($alfabet == "I") $alfabets = "J";
                    elseif($alfabet == "J") $alfabets = "K";
                    elseif($alfabet == "K") $alfabets = "L";
                    elseif($alfabet == "L") $alfabets = "M";
                    elseif($alfabet == "M") $alfabets = "N";
                    elseif($alfabet == "N") $alfabets = "O";
                    else $alfabets = "";

                    $wpp = "WP ".$wp.$alfabets;
                    $alfabet = $alfabets;

                    // cek hari besok, kalau bukan libur/weekend → naik WP
                    $next_date = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $next_date);
                    $holiday_check = $this->db->get()->row();
                    if (date('w', strtotime($next_date)) !== '0' && date('w', strtotime($next_date)) !== '6') {
                        if (@$holiday_check->remarks == null || @$holiday_check->remarks == "") {
                            $wp++;
                        }
                    }
                } else {
                    // hari kerja normal
                    if($wp == 0) $wp = 1;
                    $wpp = "WP ".$wp;
                    $alfabet = "z";

                    $next_date = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $next_date);
                    $holiday_check = $this->db->get()->row();
                    if (date('w', strtotime($next_date)) !== '0' && date('w', strtotime($next_date)) !== '6') {
                        if (@$holiday_check->remarks == null || @$holiday_check->remarks == "") {
                            $wp++;
                        }
                    }
                }
            } else {
                // weekend → logika sama seperti libur
                if($alfabet == "z") $alfabets = "A";
                elseif($alfabet == "A") $alfabets = "B";
                elseif($alfabet == "B") $alfabets = "C";
                elseif($alfabet == "C") $alfabets = "D";
                elseif($alfabet == "D") $alfabets = "E";
                elseif($alfabet == "E") $alfabets = "F";
                elseif($alfabet == "F") $alfabets = "G";
                elseif($alfabet == "G") $alfabets = "H";
                elseif($alfabet == "H") $alfabets = "I";
                elseif($alfabet == "I") $alfabets = "J";
                elseif($alfabet == "J") $alfabets = "K";
                elseif($alfabet == "K") $alfabets = "L";
                elseif($alfabet == "L") $alfabets = "M";
                elseif($alfabet == "M") $alfabets = "N";
                elseif($alfabet == "N") $alfabets = "O";
                else $alfabets = "";

                $wpp = "WP ".$wp.$alfabets;
                $alfabet = $alfabets;

                $next_date = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $next_date);
                $holiday_check = $this->db->get()->row();
                if (date('w', strtotime($next_date)) !== '0' && date('w', strtotime($next_date)) !== '6') {
                    if (@$holiday_check->remarks == null || @$holiday_check->remarks == "") {
                        $wp++;
                    }
                }
            }

            $header .= '<th colspan="2" style="text-align:center;">'.$wpp.'</th>';

            $tgl++;
            $firstDate_loop = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
        }
        $header .= '</tr>';

        $firstDate_loop = $firstDate;
        $header .= '<tr>';
        $day = 1;
        while (strtotime($firstDate_loop) <= strtotime($endDate) && $day <= 31) {

            $tglTampil = date("d/m", strtotime("+".($day-1)." days", strtotime($firstDate)));
            $header .= '<th style="text-align:center;">'.$tglTampil.'</th>';
            $header .= '<th style="text-align:center;">CT (hr)</th>';
            $firstDate_loop = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
            $day++;
        }
        $header .= '</tr>';

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>

        <center>

        <div style="float: left; font-size: 12px; text-align: left;">

        <table style="width: 100%;">

        <tr>

        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

        <img src="' . $config->logo . '" width="30">

        </td>

        <td style="font-size: 14px; text-align: left; margin:2px;">

        <b>' . $config->name . '</b><br>

        <small>Generate MPP - Compound</small>

        </td>

        </tr>

        </table>

        </div>

        <div style="float: right; font-size: 12px; text-align: right;">

        Print Date ' . date("Y-m-d Y H:m:s") . ' <br>

        Print By ' . $this->session->username . '  

        </div>

        </center>

        <br>

        <table id="customers" border="1">

        <tr>

        <th style="text-align:center;">DISETUJUI</th>

        <th style="text-align:center;">DIKETAHUI</th>

        <th style="text-align:center;">DIPERIKSA</th>

        <th style="text-align:center;">DIBUAT</th>

        </tr>

        <tr>

        <th style="height:70px;"></th>

        <th style="height:70px;"></th>

        <th style="height:70px;"></th>

        <th style="height:70px;"></th>

        </tr>

        <tr>

        <th style="text-align:center; height:20px;"></th>

        <th style="text-align:center; height:20px;"></th>

        <th style="text-align:center; height:20px;"></th>

        <th style="text-align:center; height:20px;">'.$this->session->name.'</th>

        </tr>

        </table>

        <br>

        <table id="customers" border="1">';

        $html .= $header;
        $no = 1;

            $this->db->select("
                a.id as mpp_id,
                d.id as detail_id,
                a.*, d.shift, d.plan_qty,
                a.prod_plan as prodplan,
                d.date_1, d.date_2, d.date_3, d.date_4, d.date_5, d.date_6, d.date_7, d.date_8, d.date_9, d.date_10, d.date_11, d.date_12, d.date_13, d.date_14, d.date_15, d.date_16, d.date_17, d.date_18, d.date_19, d.date_20, d.date_21, d.date_22, d.date_23, d.date_24, d.date_25, d.date_26, d.date_27, d.date_28, d.date_29, d.date_30, d.date_31,
                (d.date_1 + d.date_2 + d.date_3 + d.date_4 + d.date_5 + d.date_6 + d.date_7 + d.date_8 + d.date_9 + d.date_10 + d.date_11 + d.date_12 + d.date_13 + d.date_14 + d.date_15 + d.date_16 + d.date_17 + d.date_18 + d.date_19 + d.date_20 + d.date_21 + d.date_22 + d.date_23 + d.date_24 + d.date_25 + d.date_26 + d.date_27 + d.date_28 + d.date_29 + d.date_30 + d.date_31) as plotting,
                e.number as product_no, e.name as product_name, e.lot,
                COALESCE(ir.name, e.name) as compound_name,
                COALESCE(ir.number, e.number) as compound_no,
                COALESCE(ml.cycle_time,0) as cycle_time,
                mch.number as machine_no,
                COALESCE(pc.capacity_shift,0) as cap_shift,
                COALESCE(e.uom, fg_alias.uom) as uom
            ");

            $this->db->from('generate_mpp_compound a');
            $this->db->join(
                "generate_mpp_compound_details d",  
                "a.p_month = d.p_month 
                AND a.p_year = d.p_year 
                AND a.revision = d.revision 
                AND (
                        (a.item_fg_id IS NOT NULL AND a.item_fg_id = d.item_fg_id) 
                        OR 
                        (a.item_rm_id IS NOT NULL AND a.item_rm_id = d.item_rm_id)
                )",
                "inner"
            );

            $this->db->join("item_fg e", "a.item_fg_id = e.id", 'left');
            $this->db->join("item_rm ir", "a.item_rm_id = ir.id", 'left');

            $this->db->join("compound_alias ca", "a.item_rm_id = ca.item_rm_id", "left");
            $this->db->join("item_fg fg_alias", "ca.item_fg_id = fg_alias.id", "left");

            $this->db->join("menu_loadings ml", "ml.item_fg_id = a.item_fg_id", "left");
            $this->db->join("production_capacities pc", "pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id", "left");
            $this->db->join('machines mch', 'pc.machine_id = mch.id', "left");

            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('e.number', $filter_product_no);

            // $this->db->group_by('a.item_fg_id');
            // $this->db->order_by('a.item_fg_id, d.shift');

            $this->db->order_by('mch.number', 'ASC');
            $this->db->order_by('a.item_fg_id', 'ASC');
            $this->db->order_by('d.shift', 'ASC');

            $recordDetails = $this->db->get()->result_array();

            $total_ct_per_date = [];
            $arr_total_mpp   = [];
            $arr_total_press = [];

            for ($i = 1; $i <= 31; $i++) {
                $arr_total_mpp["date_$i"]   = 0;
                $arr_total_press["date_$i"] = 0;
                $total_ct_per_date[$i] = 0;
            }

            // $grouped = [];
            // foreach ($recordDetails as $d) {
            //     $grouped[$d['machine_no']][] = $d;
            // }

            // $no = 1;
            // foreach ($grouped as $item_fg_id => $details) {
            //     $rowspan = count($details);

            //     $firstRow = true;
            //     foreach ($details as $detail) {
            //         $html .= "<tr>";

            //         // hanya tulis sekali dengan rowspan
            //         if ($firstRow) {
            //             $html .= "<td rowspan='{$rowspan}'>" . $no . "</td>";
            //             $html .= "<td rowspan='{$rowspan}'>" . $detail['machine_no'] . "</td>";
            //             $firstRow = false;
            //         }

            //         $html .= "<td>" . $detail['shift'] . "</td>";
            //         $html .= "<td style='mso-number-format:\@;'>" . $detail['product_no'] . "</td>";
            //         $html .= "<td>" . $detail['product_name'] . "</td>";
            //         $html .= "<td>" . $detail['prodplan'] . "</td>";
            //         $html .= "<td>" . $detail['plotting'] . "</td>";

            //         // === Detail tanggal ===
            //         $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
            //         $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
            //         $day = 1;

            //         while (strtotime($firstDate2) <= strtotime($endDate2)) {
            //             $field_day = "date_".$day;
            //             $dayValue = isset($detail[$field_day]) ? $detail[$field_day] : 0;

            //             if ($dayValue === "W") {
            //                 $ctValue = 0;
            //                 $qtyField = "";
            //                 $html .= "<td style='background:#FFC2C2;'>" . $ctValue . "</td>";
            //                 $html .= "<td style='background:#FFC2C2;'>" . $qtyField . "</td>";
            //                 $arr_total_press[$field_day] += $ctValue;
            //                 $arr_total_mpp[$field_day]   += 0;
            //             } else {
            //                 $html .= "<td>" . $dayValue . "</td>";

            //                 if (is_numeric($dayValue) && $detail['cavity_actual'] > 0) {
            //                     $ctValue = round(($dayValue * $detail['cycle_time'] / $detail['cavity_actual']) / 3600);
            //                 } else {
            //                     $ctValue = 0;
            //                 }

            //                 $html .= "<td>" . $ctValue . "</td>";

            //                 $arr_total_press[$field_day] += $ctValue;
            //                 $arr_total_mpp[$field_day]   += is_numeric($dayValue) ? round($dayValue) : 0;
            //             }

            //             $total_ct_per_date[$day] += $ctValue;

            //             $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
            //             $day++;
            //         }

            //         $html .= "</tr>";
            //     }

            //     $no++;
            // }

            $groupedByItem = [];
            foreach ($recordDetails as $d) {
                $groupedByItem[$d['item_fg_id']][] = $d;
            }

            $no = 1;
            foreach ($groupedByItem as $item_fg_id => $records) {

                // GROUP BY machine_no di dalam item_fg_id
                $groupedByMachine = [];
                foreach ($records as $rec) {
                    $groupedByMachine[$rec['machine_no']][] = $rec;
                }

                foreach ($groupedByMachine as $machine_no => $details) {
                    $rowspan = count($details);
                    $firstRow = true;

                    foreach ($details as $detail) {
                        $html .= "<tr>";

                        // tampilkan sekali untuk setiap machine
                        if ($firstRow) {
                            $html .= "<td style='text-align: center;' rowspan='{$rowspan}'>" . $no . "</td>";
                            $machineDisplay = !empty($machine_no) ? $machine_no : "";
                            $html .= "<td style='text-align: center;' rowspan='{$rowspan}'>" . $machineDisplay . "</td>";
                            $firstRow = false;
                        }

                        $html .= "<td style='text-align: center;'>" . $detail['shift'] . "</td>";
                        $html .= "<td style='mso-number-format:\@;'>" . $detail['product_no'] . "</td>";
                        $html .= "<td>" . $detail['product_name'] . "</td>";
                        $html .= "<td>" . $detail['uom'] . "</td>";
                        $html .= "<td>" . format_number($detail['prodplan']) . "</td>";
                        $html .= "<td>" . format_number($detail['plotting']) . "</td>";
                        $html .= "<td>" . format_number($detail['cap_shift']) . "</td>";

                        // Detail tanggal
                        $firstDate2 = date("Y-m-d", strtotime("$filter_year-$filter_month-01 -3 days"));
                        $endDate2   = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));

                        $day = 1;

                        while (strtotime($firstDate2) <= strtotime($endDate2) && $day <= 31) {
                            $field_day = "date_".$day;
                            $dayValue = isset($detail[$field_day]) ? $detail[$field_day] : 0;

                            if ($dayValue === "W") {
                                $ctValue = 0;
                                $qtyField = "";
                                $html .= "<td style='background:#FFC2C2;'></td>";
                                $html .= "<td style='background:#FFC2C2;'>" . $qtyField . "</td>";
                                $arr_total_press[$field_day] += $ctValue;
                                $arr_total_mpp[$field_day]   += 0;
                            } else {
                                $html .= "<td>" . $dayValue . "</td>";

                                if (is_numeric($dayValue)) {
                                    $ctValue = round(($dayValue * $detail['cycle_time']) / 3600);
                                } else {
                                    $ctValue = 0;
                                }

                                $html .= "<td>" . $ctValue . "</td>";

                                $arr_total_press[$field_day] += $ctValue;
                                $arr_total_mpp[$field_day]   += is_numeric($dayValue) ? round($dayValue) : 0;
                            }

                            $total_ct_per_date[$day] += $ctValue;

                            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
                            $day++;
                        }

                        $html .= "</tr>";
                    }

                    $no++;
                }
            }


            $html .= "</tr>";

        // }


        //TOTAL CUTTING

        // $persenCutting = @round(($qTotalCutting[0]->total / $qTotalCutting[0]->total_capacity) * 100, 2);
        $persenCutting = 0;

        $style = ""; 

        if($persenCutting >= 100){

            $style = "style='background:#FFD8D8;'";

        }


        $html .= "  <tr>

        <th colspan='9' style='text-align:center;'><b>TOTAL MPP</b></th>";

        $firstDate2 = date("Y-m-d", strtotime("$filter_year-$filter_month-01 -3 days"));
        $endDate2   = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2) && $day <= 31) {

            $field_day = "date_".$day;

            $working_date = date('Y-m-d', strtotime($firstDate2));
            $dayOfWeek    = date('w', strtotime($firstDate2));

            $holiday = $this->db->select('remarks')
                                ->from('working_calendar')
                                ->where('working_date', $working_date)
                                ->get()
                                ->row();

            $isHoliday = ($dayOfWeek == 0 || $dayOfWeek == 6 || !empty($holiday->remarks));

            $symbol = $isHoliday ? "0" : "<center>-</center>";

            if($option == "excel") {
                $html .= "<th style='text-align:right;'>".format_number($arr_total_mpp[$field_day])."</th>";
                $html .= "<th style='text-align:right;'>{$symbol}</th>";
            } else {   
                $html .= "<th>".format_number($arr_total_mpp[$field_day])."</th>";
                $html .= "<th>{$symbol}</th>";
            }

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "  </tr>";


        // LOADING
        $total_ct_all = array_sum($total_ct_per_date);
        $html .= "<tr ".$style.">
            <td rowspan='2' colspan='2' style='text-align:left; vertical-align:middle;'><b>TOTAL</b></td>
            <td colspan='6'><b>LOADING</b></td>
            <td><b>". format_number($total_ct_all) ."</b></td>";

        $firstDate2 = date("Y-m-d", strtotime("$filter_year-$filter_month-01 -3 days"));
        $endDate2   = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));

        $day = 1;
 
        while (strtotime($firstDate2) <= strtotime($endDate2) && $day <= 31) {
            $field_day = "date_".$day;
            $totalCt = isset($total_ct_per_date[$day]) ? $total_ct_per_date[$day] : 0;
            
            $working_date = date('Y-m-d', strtotime($firstDate2));
            $dayOfWeek    = date('w', strtotime($firstDate2));

            // cek libur
            $holiday = $this->db->select('remarks')
                                ->from('working_calendar')
                                ->where('working_date', $working_date)
                                ->get()
                                ->row();

            // tentukan apakah hari ini libur/weekend
            $isHoliday = ($dayOfWeek == 0 || $dayOfWeek == 6 || !empty($holiday->remarks));

            // pilih simbol
            $symbol = $isHoliday ? "0" : "<center>-</center>";

            if ($option == "excel") {
                $html .= "<th style='text-align:right;'>{$symbol}</th>";
                $html .= "<th style='text-align:right;'>" . format_number($totalCt) . "</th>";
            } else {
                $html .= "<th>{$symbol}</th>";
                $html .= "<th>" . format_number($totalCt) . "</th>";
            }

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
            $day++;
        }

        // CAPACITY
        $html .= "<tr ".$style.">
            <td colspan='6'><b>CAPACITY</b></td>
            <td><b>0</b></td>";

        $firstDate2 = date("Y-m-d", strtotime("$filter_year-$filter_month-01 -3 days"));
        $endDate2   = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2) && $day <= 31) {
            $field_day = "date_".$day;

            // $working_date = date('Y-m-d', strtotime($firstDate2));
            // $dayOfWeek    = date('w', strtotime($firstDate2));

            // // cek libur
            // $holiday = $this->db->select('remarks')
            //                     ->from('working_calendar')
            //                     ->where('working_date', $working_date)
            //                     ->get()
            //                     ->row();

            // // tentukan apakah hari ini libur/weekend
            // $isHoliday = ($dayOfWeek == 0 || $dayOfWeek == 6 || !empty($holiday->remarks));

            // // pilih simbol
            // $symbol = $isHoliday ? "0" : "<center>0</center>";

            if ($option == "excel") {
                $html .= "<td style='text-align:right;'><b>0</b></td>";
                $html .= "<td style='text-align:right;'><b>0</b></td>";
            } else {
                $html .= "<td><b>0</b></td>";
                $html .= "<td><b>0</b></td>";
            }

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
            $day++;
        }

        $html .= "</tr>";


        $html .= "</tr>";




        $html .= "</table>";

        echo $html;

    }

}

