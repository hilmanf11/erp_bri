<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mpp extends CI_Controller{
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
        // $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mpp.product_no]');
    }

    public function index(){
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mpp');
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
        $this->db->where('b.item_family_number !=', 'CD');
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
            $this->db->from('generate_mpp a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('b.item_family_number !=', 'CD');
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

    // public function datatablesv1(){
    //     $this->dummy = $this->load->database('dummy', TRUE);

    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));
    //     $filter_line_no = base64_decode($this->input->get('filter_line_no'));
    //     $filter_product_no = base64_decode($this->input->get('filter_product_no'));

    //     $this->db->select('revision');
    //     $this->db->from('generate_mpp');
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

    //     //Select Query
    //     $this->db->select('a.*, e.item_alias, e.capacity, e.lot, (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as floating,  b.name as customer_name, d.prod_plan as mpsprod, f.circuit_no as cct');
    //     $this->db->from('generate_mpp a');
    //     $this->db->join('mst_customer b', 'a.customer_id = b.number');
    //     $this->db->join('generate_mps c', "a.p_month = c.p_month and a.p_year = c.p_year and c.revision = '$filter_revision' and a.product_no = c.product_no");
    //     $this->db->join("(SELECT * FROM generate_mps_detail ORDER BY ltpp_month2 ASC) d", "d.p_month = '$filter_month' and d.p_year = '$filter_year' and d.revision = '$filter_revision' and a.product_no = d.product_no");
    //     $this->db->join("mst_item e", "a.product_no = e.item_id");
    //     $this->db->join("wip_mst_wos_cct f", "e.item_id = f.mstwos_assyno", "left");
    //     $this->db->where('a.p_month', $filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     $this->db->where('a.revision', $revisions->revision);
    //     $this->db->like('a.line_no', $filter_line_no);
    //     $this->db->like('a.product_no', $filter_product_no);
    //     $this->db->group_by('a.product_no', 'ASC');
    //     $this->db->order_by('a.product_no', 'ASC');

    //     //Total Data
    //     $totalRows = $this->db->count_all_results('', false);

    //     //Limit 1 - 10
    //     $this->db->limit($rows, $offset);

    //     //Get Data Array
    //     $records = $this->db->get()->result_array();

    //     foreach ($records as $record) {
    //         $periode = $record['p_year'] . $record['p_month'];
    //         $revision = $record['revision'];
    //         $assy_no = $record['product_no'];
    //         $line = $record['line_no'];

    //         $firstDate = date('Y-m-01', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
    //         $endDate = date('Y-m-t', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));

    //         $no = 1;
    //         $arr = array();
    //         $arr_date = array();
    //         while (strtotime($firstDate) <= strtotime($endDate)) {
    //             $working_date = date('Y-m-d', strtotime($firstDate));
    //             $day = date('j', strtotime($firstDate));

    //             $this->db->select('remarks');
    //             $this->db->from('working_calendar');
    //             $this->db->where('working_date', $working_date);
    //             $holiday = $this->db->get()->row();

    //             $this->dummy->select('a.*');
    //             $this->dummy->from("wip_trx_mpp a");
    //             $this->dummy->join("wip_trx_wds b", "a.serial_mpp = b.serial_mpp");
    //             $this->dummy->where("a.periode", $periode);
    //             $this->dummy->where("a.assy_no", $assy_no);
    //             $this->dummy->where("a.line", $line);
    //             $this->dummy->where("a.wp_date", $working_date);
    //             $wip_trx_mpp = $this->dummy->get()->result_array();

    //             if(count($wip_trx_mpp) > 0){
    //                 $status_wds = "F";
    //             }else{
    //                 $status_wds = $record["date_".$day];
    //             }

    //             $arr = array("wds_".$no => $status_wds, "log_".$no => json_encode($wip_trx_mpp));
    //             $arr_date = array_merge($arr, $arr_date);

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

    // public function datatablesv2(){
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));
    //     // $filter_line_no = base64_decode($this->input->get('filter_line_no'));
    //     $filter_product_no = base64_decode($this->input->get('filter_product_no'));

    //     $this->db->select('revision');
    //     $this->db->from('generate_mpp');
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

    //     $monthh = (int)$filter_month;

    //     $ltpp_month2 = "$filter_year-$monthh-01";

    //     //Select Query
    //     // $this->db->select('a.*, e.item_alias, e.capacity, e.lot, (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as floating,  b.name as customer_name, d.prod_plan as mpsprod, f.circuit_no as cct');
    //     // $this->db->from('generate_mpp a');

    //     $this->db->select('a.*, e.number as product_no, e.name as product_name, e.lot, 
    //     COALESCE(ml.cycle_time, 0) as cycle_time, 
    //     COALESCE(mo.cavity_actual, 0) as cavity_actual,
    //     ml.shift,
    //     mch.number as machine_no,
    //     COALESCE(pc.capacity_shift, 0) as cap_shift,
    //     (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as floating, d.prod_plan as mpsprod');
    //     $this->db->from('generate_mpp a');

    //     // $this->db->join('generate_mps c', 
    //     //     "a.p_month = LPAD(c.p_month, 2, '0')
    //     //     and a.p_year = c.p_year 
    //     //     and c.revision = '$filter_revision' 
    //     //     and a.item_fg_id = c.item_fg_id"
    //     // );

    //     $this->db->join("(SELECT * FROM generate_mps_details WHERE ltpp_month2 = '$ltpp_month2') d", "LPAD(d.p_month, 2, '0') = '$filter_month' and d.p_year = '$filter_year' and d.revision = '$filter_revision' and a.item_fg_id = d.item_fg_id");
    //     $this->db->join("item_fg e", "a.item_fg_id = e.id");

    //     $this->db->join("menu_loadings ml", "ml.item_fg_id = a.item_fg_id");
    //     $this->db->join("molds mo", "ml.mold_id = mo.id");
    //     $this->db->join("production_capacities pc", "pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id");
    //     $this->db->join('machines mch', 'pc.machine_id = mch.id', 'left');

    //     $this->db->where('a.p_month', $filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     $this->db->where('e.item_family_number !=', 'CD');
    //     $this->db->where('a.revision', $revisions->revision);
    //     // $this->db->like('a.line_no', $filter_line_no);
    //     $this->db->like('e.number', $filter_product_no);
    //     // $this->db->group_by('a.item_fg_id');
    //     $this->db->order_by('a.item_fg_id', 'ASC');
    //     // $this->db->order_by('ml.machine_id', 'ASC');
    //     // $this->db->order_by('ml.shift', 'ASC');

    //     // $this->db->order_by('a.item_fg_id','ASC');
    //     // $this->db->order_by('ml.shift','ASC');

    //     //Total Data
    //     $totalRows = $this->db->count_all_results('', false);

    //     //Limit 1 - 10
    //     $this->db->limit($rows, $offset);

    //     //Get Data Array
    //     $records = $this->db->get()->result_array();

    //     foreach ($records as $record) {
    //         $periode = $record['p_year'] . $record['p_month'];
    //         $revision = $record['revision'];
    //         $assy_no = $record['item_fg_id'];
    //         // $line = $record['line_no'];

    //         $firstDate = date('Y-m-01', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
    //         $endDate = date('Y-m-t', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));

    //         $no = 1;
    //         $arr = array();
    //         $arr_date = array();

    //         while (strtotime($firstDate) <= strtotime($endDate)) {
    //             // $working_date = date('Y-m-d', strtotime($firstDate));
    //             $day = date('j', strtotime($firstDate));
    //             $weekday = date('w', strtotime($firstDate));

    //             // $this->db->select('remarks');
    //             // $this->db->from('working_calendar');
    //             // $this->db->where('working_date', $working_date);
    //             // $holiday = $this->db->get()->row();

    //             $status_wds = $record["date_".$day];

    //             if ($weekday == 0 || $weekday == 6) {
    //                 $arr = [
    //                     "wds_".$no => $status_wds
    //                 ];
    //             } else {
    //                 // $hasil = round($status_wds * $cycle_time / 3600);

    //                 if (is_numeric($status_wds) && $record['cavity_actual'] > 0) {
    //                     $hasil = round(($status_wds * $record['cycle_time'] / $record['cavity_actual']) / 3600);
    //                 } else {
    //                     $hasil = 0;
    //                 }


    //                 $arr = [
    //                     "wds_".$no => $status_wds,
    //                     "log_".$no => $hasil
    //                 ];
    //             }

    //             $arr_date = array_merge($arr_date, $arr);

    //             $no++;
    //             $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //         }

    //         // $finals = [];            

    //         // foreach ($records as $record) {
    //         //     for ($s = 1; $s <= (int)$record['shift']; $s++) {
    //         //         $newRecord = $record;
    //         //         $newRecord['shift'] = $s;
    //         //         $finals[] = $newRecord;
    //         //     }
    //         // }

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

        $this->db->select('revision');
        $this->db->from('generate_mpp');
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
            d.date_1, d.date_2, d.date_3, d.date_4, d.date_5, d.date_6, d.date_7, d.date_8, d.date_9, d.date_10, d.date_11, d.date_12, d.date_13, d.date_14, d.date_15, d.date_16, d.date_17, d.date_18, d.date_19, d.date_20, d.date_21, d.date_22, d.date_23, d.date_24, d.date_25, d.date_26, d.date_27, d.date_28, d.date_29, d.date_30, d.date_31,
            (d.date_1 + d.date_2 + d.date_3 + d.date_4 + d.date_5 + d.date_6 + d.date_7 + d.date_8 + d.date_9 + d.date_10 + d.date_11 + d.date_12 + d.date_13 + d.date_14 + d.date_15 + d.date_16 + d.date_17 + d.date_18 + d.date_19 + d.date_20 + d.date_21 + d.date_22 + d.date_23 + d.date_24 + d.date_25 + d.date_26 + d.date_27 + d.date_28 + d.date_29 + d.date_30 + d.date_31) as floating,
            e.number as product_no, e.name as product_name, e.lot,
            COALESCE(ml.cycle_time,0) as cycle_time,
            COALESCE(mo.cavity_actual,0) as cavity_actual,
            mch.number as machine_no,
            COALESCE(pc.capacity_shift,0) as cap_shift,
            a.prod_plan as mpsprod
        ");
        $this->db->from('generate_mpp a');

        $this->db->join(
            "generate_mpp_details d",
            "a.p_month = d.p_month AND a.p_year = d.p_year AND a.revision = d.revision AND a.item_fg_id = d.item_fg_id",
            "inner"
        );
        $this->db->join("item_fg e", "a.item_fg_id = e.id");
        $this->db->join("menu_loadings ml", "ml.item_fg_id = a.item_fg_id", "left");
        $this->db->join("molds mo", "ml.mold_id = mo.id", "left");
        $this->db->join("production_capacities pc", "pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id", "left");
        $this->db->join('machines mch', 'pc.machine_id = mch.id', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $revisions->revision);
        $this->db->where('e.item_family_number !=', 'CD');
        $this->db->like('e.number', $filter_product_no);
        $this->db->order_by('mch.number', 'ASC');
        $this->db->order_by('a.item_fg_id', 'ASC');
        $this->db->order_by('d.shift', 'ASC');

        //Total Data
        $totalRows = $this->db->count_all_results('', false);

        //Limit 1 - 10
        $this->db->limit($rows, $offset);

        //Get Data Array
        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            // $periode = $record['p_year'] . $record['p_month'];
            // $revision = $record['revision'];
            // $assy_no = $record['item_fg_id'];
            // $line = $record['line_no'];

            $firstDate = date('Y-m-01', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
            $endDate = date('Y-m-t', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));

            $no = 1;
            $arr = array();
            $arr_date = array();

            while (strtotime($firstDate) <= strtotime($endDate)) {
                $working_date = date('Y-m-d', strtotime($firstDate));
                $day = date('j', strtotime($firstDate));
                $weekday = date('w', strtotime($firstDate));

                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                $status_wds = $record["date_".$day];

                if ($weekday == 0 || $weekday == 6 || !empty($holiday->remarks)) {
                    $arr = [
                        "wds_".$no => $status_wds
                    ];
                } else {
                    // $hasil = round($status_wds * $cycle_time / 3600);

                    if (is_numeric($status_wds) && $record['cavity_actual'] > 0) {
                        $hasil = round(($status_wds * $record['cycle_time'] / $record['cavity_actual']) / 3600);
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
    //     $this->db->select("
    //         a.item_fg_id,
    //         b.number as product_name,
    //         a.prod_plan,
    //         b.lot,
    //         COALESCE(ml.cycle_time, 0) as cycle_time,
    //         COALESCE(mo.cavity_actual, 0) as cavity_actual,
    //         ml.shift as total_shift,
    //         COALESCE(pc.capacity_shift, 0) as cap_shift
    //     ");
    //     $this->db->from('generate_mps_details a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->join('menu_loadings ml', 'ml.item_fg_id = a.item_fg_id');
    //     $this->db->join('molds mo', 'ml.mold_id = mo.id');
    //     $this->db->join('production_capacities pc', 'pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id');
    //     $this->db->where('b.item_family_number !=', 'CD');
    //     $this->db->where('a.p_month', (int)$filter_month);
    //     $this->db->where('a.p_year', $filter_year);
    //     $this->db->where('a.revision', $filter_revision);
    //     $this->db->where('a.ltpp_month2', $ltppMonth);
    //     $this->db->where('a.prod_plan > 0');
    //     // $this->db->like('a.item_fg_id', $filter_product_no);
    //     $this->db->group_by("a.item_fg_id");
    //     $this->db->order_by("a.item_fg_id", "asc");
    //     $recordDetails = $this->db->get()->result_array();

    //     $mpp = array();

    //     foreach ($recordDetails as $detail) {
    //         $prodplan = $detail['prod_plan'];
    //         $totalShift = max(1, (int)$detail['total_shift']);
    //         $capShift = max(1, (int)$detail['cap_shift']);

    //         // header info per item_fg_id (tidak menyertakan prod_plan ke detail)
    //         $header = [
    //             "p_month" => $filter_month,
    //             "p_year"  => $filter_year,
    //             "revision"=> $filter_revision,
    //             "item_fg_id"=> $detail['item_fg_id'],
    //             "prod_plan" => $prodplan
    //         ];

    //         // for ($s = 1; $s <= $totalShift; $s++) {
    //         //     $rows = $header;
    //         //     $rows['shift'] = $s;
    //         //     $planQtyShift = ($s == $totalShift) ? $prodplanPerShift + $sisaShift : $prodplanPerShift;
    //         //     $rows['plan_qty'] = $planQtyShift;

    //         //     $sisaPlan = $planQtyShift;

    //         //     // tanggal awal & akhir
    //         //     $firstDate = date('Y-m-01', strtotime("$filter_year-$filter_month-01"));
    //         //     $endDate   = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));
    //         //     $no = 1;

    //         //     while (strtotime($firstDate) <= strtotime($endDate)) {
    //         //         $working_date = date('Y-m-d', strtotime($firstDate));
    //         //         $holiday = $this->db->select('remarks')->from('working_calendar')
    //         //                     ->where('working_date', $working_date)->get()->row();

    //         //         if (date('w', strtotime($firstDate)) != 0 && empty($holiday->remarks)) {
    //         //             // alokasikan sesuai cap_shift
    //         //             $qty = min($capShift, $sisaPlan);
    //         //             $rows["date_$no"] = $qty;
    //         //             $sisaPlan -= $qty;
    //         //         } else {
    //         //             $rows["date_$no"] = "W";
    //         //         }

    //         //         $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //         //         $no++;
    //         //     }

    //         //     $mpp[] = $rows;
    //         // }


    //         // Siapkan baris (rows) untuk setiap shift terlebih dahulu (header + inisialisasi)
    //         $rowsPerShift = [];
    //         for ($s = 1; $s <= $totalShift; $s++) {
    //             $rowsPerShift[$s] = $header;
    //             $rowsPerShift[$s]['shift'] = $s;
    //             $rowsPerShift[$s]['plan_qty'] = 0; // akan diisi kemudian dari jumlah tanggal
    //         }

    //         // Gunakan sisa plan total (bukan sisa per-shift)
    //         $remainingPlan = $prodplan;

    //         // tanggal awal & akhir
    //         $firstDate = date('Y-m-01', strtotime("$filter_year-$filter_month-01"));
    //         $endDate   = date('Y-m-t', strtotime("$filter_year-$filter_month-01"));
    //         $no = 1;

    //         while (strtotime($firstDate) <= strtotime($endDate)) {
    //             $working_date = date('Y-m-d', strtotime($firstDate));
    //             $holiday = $this->db->select('remarks')->from('working_calendar')
    //                         ->where('working_date', $working_date)->get()->row();

    //             if (date('w', strtotime($firstDate)) != 0 && empty(@$holiday->remarks)) {
    //                 // Hari kerja: alokasikan per shift berurutan sampai remainingPlan habis
    //                 for ($s = 1; $s <= $totalShift; $s++) {
    //                     if ($remainingPlan > 0) {
    //                         $qty = min($capShift, $remainingPlan);
    //                         $rowsPerShift[$s]["date_$no"] = $qty;
    //                         $rowsPerShift[$s]['plan_qty'] += $qty;
    //                         $remainingPlan -= $qty;
    //                     } else {
    //                         $rowsPerShift[$s]["date_$no"] = 0;
    //                     }
    //                 }
    //             } else {
    //                 // Libur / weekend: tandai "W" untuk semua shift
    //                 for ($s = 1; $s <= $totalShift; $s++) {
    //                     $rowsPerShift[$s]["date_$no"] = "W";
    //                 }
    //             }

    //             $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
    //             $no++;
    //         }

    //         // Masukkan setiap shift ke hasil akhir
    //         foreach ($rowsPerShift as $r) {
    //             $mpp[] = $r;
    //         }

    //     }

    //     echo json_encode($mpp);
    // }

    public function getdata(){
        // Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $ltppMonth = $filter_year . "-" . $filter_month . "-01";
        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $start = strtotime(date('Y-m-01', $monthStart));
        $finish = strtotime(date('Y-m-t', $monthStart));

        // Building date list (tanggal + is_working)
        $dateList = [];
        $no = 1;
        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $z);
            $holiday = $this->db->select('remarks')->from('working_calendar')
                        ->where('working_date', $working_date)->get()->row();
            $is_working = (date('w', $z) != 0 && empty(@$holiday->remarks));
            $dateList[] = [
                'date' => $working_date,
                'is_working' => $is_working
            ];
            $no++;
        }
        $totalDays = count($dateList);

        // Ambil data (tambahkan machine_id)
        $this->db->select("
            a.item_fg_id,
            b.number as product_name,
            a.prod_plan,
            b.lot,
            COALESCE(ml.cycle_time, 0) as cycle_time,
            COALESCE(mo.cavity_actual, 0) as cavity_actual,
            ml.shift as total_shift,
            COALESCE(pc.capacity_shift, 0) as cap_shift,
            ml.machine_id,
            MIN(sod.trans_date) as earliest_trans_date
        ");
        $this->db->from('generate_mps_details a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('menu_loadings ml', 'ml.item_fg_id = a.item_fg_id', 'left');
        $this->db->join('molds mo', 'ml.mold_id = mo.id', 'left');
        $this->db->join('production_capacities pc', 'pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id', 'left');
        $this->db->join('sales_order_deliveries sod', 
            "sod.item_fg_id = a.item_fg_id 
            AND sod.status = 0 
            AND MONTH(sod.trans_date) = " . $filter_month . " 
            AND YEAR(sod.trans_date) = " . $filter_year, 
            'left'
        );  
        $this->db->where('b.item_family_number !=', 'CD');
        $this->db->where('a.p_month', (int)$filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.ltpp_month2', $ltppMonth);
        $this->db->where('a.prod_plan > 0');
        // group by machine + item supaya dapat machine_id tiap baris
        $this->db->group_by(["ml.machine_id", "a.item_fg_id"]);
        $this->db->order_by("ml.machine_id", "asc");
        $this->db->order_by("a.item_fg_id", "asc");
        $recordDetails = $this->db->get()->result_array();

        // Kelompokkan data per machine_id
        $machines = [];
        foreach ($recordDetails as $d) {
            $machineId = $d['machine_id'] ?? 'NO_MACHINE';
            if (!isset($machines[$machineId])) $machines[$machineId] = ['items'=>[], 'machine_total_shift' => 0];

            $item = [
                'item_fg_id' => $d['item_fg_id'],
                'product_name'=> $d['product_name'],
                'prod_plan' => (int)$d['prod_plan'],
                'remaining' => (int)$d['prod_plan'],
                'cap_shift' => max(1, (int)$d['cap_shift']),
                'total_shift' => max(1, (int)$d['total_shift']),
                'lot' => $d['lot'],
                "has_relation"=> ($d['cap_shift'] > 0 || $d['total_shift'] > 0) ? 1 : 0,
                "earliest_trans_date" => $d['earliest_trans_date'] ?? null
            ];
            $machines[$machineId]['items'][] = $item;
            // tentukan jumlah shift mesin sebagai max dari item-itemnya
            $machines[$machineId]['machine_total_shift'] = max($machines[$machineId]['machine_total_shift'], $item['total_shift']);
        }

        $mpp = [];

        // Untuk setiap mesin, jadwalkan item secara sekuensial per shift
        foreach ($machines as $machineId => $mc) {
            $items = $mc['items'];
            usort($items, function($a, $b) {
                // Kalau dua2nya tidak ada trans_date maka urutkan berdasarkan item_fg_id
                if (empty($a['earliest_trans_date']) && empty($b['earliest_trans_date'])) {
                    return $a['item_fg_id'] <=> $b['item_fg_id'];
                }
                if (empty($a['earliest_trans_date'])) return 1; // tanpa tanggal → di belakang
                if (empty($b['earliest_trans_date'])) return -1;

                $dateA = strtotime($a['earliest_trans_date']);
                $dateB = strtotime($b['earliest_trans_date']);

                if ($dateA == $dateB) {
                    return $a['item_fg_id'] <=> $b['item_fg_id'];
                }
                return $dateA <=> $dateB; // yang lebih awal duluan
            });

            // usort($items, function($a, $b) use ($monthStart) {
            //     // jika salah satu tidak ada trans_date, taruh di belakang
            //     if (empty($a['earliest_trans_date']) && empty($b['earliest_trans_date'])) {
            //         return $a['item_fg_id'] <=> $b['item_fg_id'];
            //     };
            //     if (empty($a['earliest_trans_date'])) return 1;
            //     if (empty($b['earliest_trans_date'])) return -1;

            //     $aTime = strtotime($a['earliest_trans_date']);
            //     $bTime = strtotime($b['earliest_trans_date']);

            //     $aDiff = abs($aTime - $monthStart);
            //     $bDiff = abs($bTime - $monthStart);

            //     return $aDiff <=> $bDiff;
            // });

            $machineShiftCount = max(1, $mc['machine_total_shift']);

            // inisialisasi rows per item per shift, dan pre-populate date_x dengan default 0 atau 'W'
            $rowsPerItem = [];
            foreach ($items as $it) {
                $header = [
                    "p_month" => $filter_month,
                    "p_year"  => $filter_year,
                    "revision"=> $filter_revision,
                    "item_fg_id"=> $it['item_fg_id'],
                    "prod_plan" => $it['prod_plan'],
                    "shift" => null,
                    "plan_qty" => 0
                ];
                for ($s = 1; $s <= $it['total_shift']; $s++) {
                    $r = $header;
                    $r['shift'] = $s;
                    // pre-populate date columns: default 0 or 'W' if holiday
                    for ($d = 1; $d <= $totalDays; $d++) {
                        $r["date_$d"] = $dateList[$d-1]['is_working'] ? 0 : "W";
                    }
                    $rowsPerItem[$it['item_fg_id']][$s] = $r;
                }
            }

            // pointer ke item yang sedang diproses
            $currentIndex = 0;
            $currentItem = ($currentIndex < count($items)) ? $items[$currentIndex] : null;

            // iterasi tanggal dan shift — untuk tiap slot shift, alokasikan ke currentItem (jika working day)
            for ($d = 1; $d <= $totalDays; $d++) {
                $isWorking = $dateList[$d-1]['is_working'];
                if (!$isWorking) {
                    // sudah diisi 'W' saat pre-populate, lanjut
                    continue;
                }

                // untuk setiap shift pada hari itu
                for ($s = 1; $s <= $machineShiftCount; $s++) {
                    // jika tidak ada item tersisa -> biarkan 0
                    if ($currentItem === null) {
                        // nothing to allocate
                        continue;
                    }

                    if ($currentItem['has_relation'] == 0) {
                        // skip semua alokasi, langsung pindah ke item berikutnya
                        $currentIndex++;
                        $currentItem = ($currentIndex < count($items)) ? $items[$currentIndex] : null;
                        continue;
                    }

                    // Jika shift index melebihi total shift item (kemungkinan item punya shift < machineShiftCount),
                    // maka item tidak punya shift itu -> skip assign (biarkan 0).
                    if ($s > $currentItem['total_shift']) {
                        // tidak bisa diproses pada shift ini untuk item ini
                        continue;
                    }

                    if ($currentItem['remaining'] > 0) {
                        // satu slot shift hanya satu item dan tidak boleh diisi item lain
                        $allocate = min($currentItem['cap_shift'], $currentItem['remaining']);

                        // tulis ke baris item untuk shift $s pada tanggal $d
                        $rowsPerItem[$currentItem['item_fg_id']][$s]["date_$d"] = $allocate;
                        $rowsPerItem[$currentItem['item_fg_id']][$s]['plan_qty'] += $allocate;

                        // kurangi sisa
                        $currentItem['remaining'] -= $allocate;

                        // update juga di array items (agar pointer mengambil remaining yang terbaru)
                        $items[$currentIndex]['remaining'] = $currentItem['remaining'];

                        // jika item habis -> pindah ke item berikutnya (bisa mulai pada shift selanjutnya)
                        if ($currentItem['remaining'] <= 0) {
                            $currentIndex++;
                            $currentItem = ($currentIndex < count($items)) ? $items[$currentIndex] : null;
                        }
                    } else {
                        // item sudah habis, pindah
                        $currentIndex++;
                        $currentItem = ($currentIndex < count($items)) ? $items[$currentIndex] : null;
                        // jika masih ada item dan shift masih berjalan pada hari yang sama, ulangi alokasi pada item baru
                        if ($currentItem !== null && $s <= $currentItem['total_shift'] && $currentItem['remaining'] > 0) {
                            $allocate = min($currentItem['cap_shift'], $currentItem['remaining']);
                            $rowsPerItem[$currentItem['item_fg_id']][$s]["date_$d"] = $allocate;
                            $rowsPerItem[$currentItem['item_fg_id']][$s]['plan_qty'] += $allocate;
                            $items[$currentIndex]['remaining'] -= $allocate;
                            $currentItem = ($items[$currentIndex]['remaining'] <= 0) ? (($currentIndex+1 < count($items))?$items[$currentIndex+1]:null) : $items[$currentIndex];
                            if ($items[$currentIndex]['remaining'] <= 0) $currentIndex++;
                        }
                    }
                } // end foreach shift
            } // end foreach date

            // append hasil rowsPerItem ke mpp
            foreach ($rowsPerItem as $itemRows) {
                foreach ($itemRows as $r) {
                    $mpp[] = $r;
                }
            }
        } // end foreach machine

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

    // public function createv1()
    // {
    //     if ($this->input->post('data')) {
    //         $post = $this->input->post('data');

    //         $post['p_month'] = str_pad($post['p_month'], 2, '0', STR_PAD_LEFT);

    //         $read = $this->crud->read("generate_mpp", [], [
    //             "p_month" => $post['p_month'],
    //             "p_year" => $post['p_year'],
    //             "revision" => $post['revision'],
    //             "item_fg_id" => $post['item_fg_id']
    //         ]);

    //         if ($read) {
    //             $send = $this->crud->update('generate_mpp', [
    //                 "p_month" => $post['p_month'],
    //                 "p_year" => $post['p_year'],
    //                 "revision" => $post['revision'],
    //                 "item_fg_id" => $post['item_fg_id']
    //             ], $post);
    //         } else {
    //             $send = $this->crud->create('generate_mpp', $post, "MPP", "MPP");
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

            $post['p_month'] = str_pad($post['p_month'], 2, '0', STR_PAD_LEFT);

            // simpan header
            $headerExist = $this->crud->read("generate_mpp", [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_fg_id" => $post['item_fg_id']
            ]);

            if (!$headerExist) {
                $this->crud->create('generate_mpp', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_fg_id" => $post['item_fg_id'],
                    "prod_plan" => $post['prod_plan'],
                ], "MPP", "MPP");
            }

            $dataDetail = [
                "p_month"    => $post['p_month'],
                "p_year"     => $post['p_year'],
                "revision"   => $post['revision'],
                "item_fg_id" => $post['item_fg_id'],
                "shift"      => $post['shift'],
                "plan_qty"   => $post['plan_qty'],
            ];

            for ($i = 1; $i <= 31; $i++) {
                $field = "date_$i";
                if(isset($post[$field])){
                    $dataDetail[$field] = $post[$field];
                }
            }

            $existDetail = $this->crud->read("generate_mpp_details", [], [
                "p_month"    => $post['p_month'],
                "p_year"     => $post['p_year'],
                "revision"   => $post['revision'],
                "item_fg_id" => $post['item_fg_id'],
                "shift"      => $post['shift']
            ]);

            if ($existDetail) {
                $send = $this->crud->update(
                    'generate_mpp_details',
                    [
                        "p_month"    => $post['p_month'],
                        "p_year"     => $post['p_year'],
                        "revision"   => $post['revision'],
                        "item_fg_id" => $post['item_fg_id'],
                        "shift"      => $post['shift']
                    ],
                    $dataDetail
                );
            } else {
                $send = $this->crud->create('generate_mpp_details', $dataDetail, "MPPD", "MPPD");
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
            $send = $this->crud->update('generate_mpp_details', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/generate_mpp.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/generate_mpp.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/generate_mpp.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_mpp_$format.xls");
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
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

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



        //Setting Header
        $styles = "";

        // $header = '<tr>
        //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
        //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

        // $tmpDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        // $endDate = date('Y-m-t',  strtotime($filter_year . "-" . $filter_month . "-01"));

        // // Row-1: judul "WP" per hari (colspan 2)
        // while (strtotime($tmpDate) <= strtotime($endDate)) {
        //     $header .= '<th colspan="2" style="text-align:center;">WP</th>';
        //     $tmpDate = date("Y-m-d", strtotime("+1 day", strtotime($tmpDate)));
        // }
        // $header .= '</tr>';
        
        // $header .= '<tr>';

        // while (strtotime($firstDate) <= strtotime($endDate)) {
        //     $header .= '<th width="30" colspan="2" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';
        //     $header .= '<th style="text-align:center;">CT (0)</th>';
        //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        // }

        // $header .= '</tr>';


        // $header  = '<tr>
        //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
        //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

        // // Row-1: judul "WP" per hari (colspan 2)
        // while (strtotime($firstDate) <= strtotime($endDate)) {
        //     $header .= '<th colspan="2" style="text-align:center;">WP</th>';
        //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        // }
        // $header .= '</tr>';

        // // Row-2: subkolom (tanggal) dan CT(0)
        // $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        // $day     = 1;
        // $header .= '<tr>';
        // while (strtotime($firstDate) <= strtotime($endDate)) {
        //     // jika mau full tanggal, ganti $day dengan date("Y-m-d", strtotime($firstDate))
        //     $header .= '<th style="text-align:center;">' . $day . '</th>';
        //     $header .= '<th style="text-align:center;">CT</th>';
        //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        //     $day++;
        // }
        // $header .= '</tr>';

        // $qty = '<tr><th width="50" style="text-align:center;">PRODPLAN</th>';
        // $plotting = '<tr><th width="50" style="text-align:center;">PLOTTING</th>';

        // while (strtotime($firstDate) <= strtotime($endDate)) {
        //     //Setting Header
        //     $qty .= '<th width="30" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';
        //     $plotting .= '<th width="30" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';
        //     $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        // }

        // //Setting Header
        // $qty .= '</tr>';
        // $plotting .= '</tr>';


        // Inisialisasi header
        // $header  = '<tr>
        //     <th rowspan="2" width="50" style="text-align:center;">PRODPLAN</th>
        //     <th rowspan="2" width="50" style="text-align:center;">PLOTTING</th>';

        $header  = '<tr>
            <th style="text-align:center;" rowspan="2" width="50">NO</th>
            <th style="text-align:center;" rowspan="2" width="200">MACHINE NO</th>
            <th style="text-align:center;" rowspan="2" width="80">SHIFT</th>
            <th style="text-align:center;" rowspan="2" width="200">PRODUCT NO</th>
            <th style="text-align:center;" rowspan="2" width="150">PRODUCT NAME</th>
            <th style="text-align:center;" rowspan="2" width="80">PRODPLAN</th>
            <th style="text-align:center;" rowspan="2" width="80">PLOTTING</th>
            <th style="text-align:center;" rowspan="2" width="80">CAP/SHIFT</th>';

        $wp = 0;
        $tgl = 1;
        $alfabet = "z";
        $firstDate_loop = $firstDate;
        while (strtotime($firstDate_loop) <= strtotime($endDate)) {
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
        while (strtotime($firstDate_loop) <= strtotime($endDate)) {
            $header .= '<th style="text-align:center;">'.$day.'</th>';
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

        <small>Generate MPP - Rubber Part</small>

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



        // $total_mpp_prodplan = 0;



        // $total_cct_prodplan = 0;

        // $total_cct_plotting = 0;

        // $total_cct_cutting = 0;


        //Total MPP

        // $total_mpp_date_1 = 0;

        // $total_mpp_date_2 = 0;

        // $total_mpp_date_3 = 0;

        // $total_mpp_date_4 = 0;

        // $total_mpp_date_5 = 0;

        // $total_mpp_date_6 = 0;

        // $total_mpp_date_7 = 0;

        // $total_mpp_date_8 = 0;

        // $total_mpp_date_9 = 0;

        // $total_mpp_date_10 = 0;

        // $total_mpp_date_11 = 0;

        // $total_mpp_date_12 = 0;

        // $total_mpp_date_13 = 0;

        // $total_mpp_date_14 = 0;

        // $total_mpp_date_15 = 0;

        // $total_mpp_date_16 = 0;

        // $total_mpp_date_17 = 0;

        // $total_mpp_date_18 = 0;

        // $total_mpp_date_19 = 0;

        // $total_mpp_date_20 = 0;

        // $total_mpp_date_21 = 0;

        // $total_mpp_date_22 = 0;

        // $total_mpp_date_23 = 0;

        // $total_mpp_date_24 = 0;

        // $total_mpp_date_25 = 0;

        // $total_mpp_date_26 = 0;

        // $total_mpp_date_27 = 0;

        // $total_mpp_date_28 = 0;

        // $total_mpp_date_29 = 0;

        // $total_mpp_date_30 = 0;

        // $total_mpp_date_31 = 0;



        //Total Press

        // $total_press_date_1 = 0;

        // $total_press_date_2 = 0;

        // $total_press_date_3 = 0;

        // $total_press_date_4 = 0;

        // $total_press_date_5 = 0;

        // $total_press_date_6 = 0;

        // $total_press_date_7 = 0;

        // $total_press_date_8 = 0;

        // $total_press_date_9 = 0;

        // $total_press_date_10 = 0;

        // $total_press_date_11 = 0;

        // $total_press_date_12 = 0;

        // $total_press_date_13 = 0;

        // $total_press_date_14 = 0;

        // $total_press_date_15 = 0;

        // $total_press_date_16 = 0;

        // $total_press_date_17 = 0;

        // $total_press_date_18 = 0;

        // $total_press_date_19 = 0;

        // $total_press_date_20 = 0;

        // $total_press_date_21 = 0;

        // $total_press_date_22 = 0;

        // $total_press_date_23 = 0;

        // $total_press_date_24 = 0;

        // $total_press_date_25 = 0;

        // $total_press_date_26 = 0;

        // $total_press_date_27 = 0;

        // $total_press_date_28 = 0;

        // $total_press_date_29 = 0;

        // $total_press_date_30 = 0;

        // $total_press_date_31 = 0;



        // foreach ($records as $record) {

            // $html .= "<tr>

            // <th style='text-align:left;' colspan='48'>Remarks</th>

            // </tr>";



            // $line_no = $record['line_no'];
            // $line_no = '1';

            // $this->db->select("a.*, e.number as product_no, e.name as product_name, e.lot, COALESCE(ml.cycle_time, 0) as cycle_time, COALESCE(mo.cavity_actual, 0) as cavity_actual, d.prod_plan as prodplan, (d.date_1 + d.date_2 + d.date_3 + d.date_4 + d.date_5 + d.date_6 + d.date_7 + d.date_8 + d.date_9 + d.date_10 + d.date_11 + d.date_12 + d.date_13 + d.date_14 + d.date_15 + d.date_16 + d.date_17 + d.date_18 + d.date_19 + d.date_20 + d.date_21 + d.date_22 + d.date_23 + d.date_24 + d.date_25 + d.date_26 + d.date_27 + d.date_28 + d.date_29 + d.date_30 + d.date_31) as plotting");


            $this->db->select("
                a.id as mpp_id,
                d.id as detail_id,
                a.*, d.shift, d.plan_qty,
                a.prod_plan as prodplan,
                d.date_1, d.date_2, d.date_3, d.date_4, d.date_5, d.date_6, d.date_7, d.date_8, d.date_9, d.date_10, d.date_11, d.date_12, d.date_13, d.date_14, d.date_15, d.date_16, d.date_17, d.date_18, d.date_19, d.date_20, d.date_21, d.date_22, d.date_23, d.date_24, d.date_25, d.date_26, d.date_27, d.date_28, d.date_29, d.date_30, d.date_31,
                (d.date_1 + d.date_2 + d.date_3 + d.date_4 + d.date_5 + d.date_6 + d.date_7 + d.date_8 + d.date_9 + d.date_10 + d.date_11 + d.date_12 + d.date_13 + d.date_14 + d.date_15 + d.date_16 + d.date_17 + d.date_18 + d.date_19 + d.date_20 + d.date_21 + d.date_22 + d.date_23 + d.date_24 + d.date_25 + d.date_26 + d.date_27 + d.date_28 + d.date_29 + d.date_30 + d.date_31) as plotting,
                e.number as product_no, e.name as product_name, e.lot,
                COALESCE(ml.cycle_time,0) as cycle_time,
                COALESCE(mo.cavity_actual,0) as cavity_actual,
                mch.number as machine_no,
                COALESCE(pc.capacity_shift,0) as cap_shift,
            ");

            $this->db->from('generate_mpp a');
            $this->db->join(
                "generate_mpp_details d",
                "a.p_month = d.p_month AND a.p_year = d.p_year AND a.revision = d.revision AND a.item_fg_id = d.item_fg_id",
                "inner"
            );
            $this->db->join("item_fg e", "a.item_fg_id = e.id");
            $this->db->join("menu_loadings ml", "ml.item_fg_id = a.item_fg_id", "left");
            $this->db->join("molds mo", "ml.mold_id = mo.id", "left");
            $this->db->join("production_capacities pc", "pc.item_fg_id = a.item_fg_id AND pc.machine_id = ml.machine_id", "left");
            $this->db->join('machines mch', 'pc.machine_id = mch.id', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('e.number', $filter_product_no);
            
            // $this->db->limit(6);
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
                        $html .= "<td>" . format_number($detail['prodplan']) . "</td>";
                        $html .= "<td>" . format_number($detail['plotting']) . "</td>";
                        $html .= "<td>" . format_number($detail['cap_shift']) . "</td>";

                        // Detail tanggal
                        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                        $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
                        $day = 1;

                        while (strtotime($firstDate2) <= strtotime($endDate2)) {
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

                                if (is_numeric($dayValue) && $detail['cavity_actual'] > 0) {
                                    $ctValue = round(($dayValue * $detail['cycle_time'] / $detail['cavity_actual']) / 3600);
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

        // $arr_total_mpp = array(
        //     "date_1" => $total_mpp_date_1,
        //     "date_2" => $total_mpp_date_2,
        //     "date_3" => $total_mpp_date_3,
        //     "date_4" => $total_mpp_date_4,
        //     "date_5" => $total_mpp_date_5,
        //     "date_6" => $total_mpp_date_6,
        //     "date_7" => $total_mpp_date_7,
        //     "date_8" => $total_mpp_date_8,
        //     "date_9" => $total_mpp_date_9,
        //     "date_10" => $total_mpp_date_10,
        //     "date_11" => $total_mpp_date_11,
        //     "date_12" => $total_mpp_date_12,
        //     "date_13" => $total_mpp_date_13,
        //     "date_14" => $total_mpp_date_14,
        //     "date_15" => $total_mpp_date_15,
        //     "date_16" => $total_mpp_date_16,
        //     "date_17" => $total_mpp_date_17,
        //     "date_18" => $total_mpp_date_18,
        //     "date_19" => $total_mpp_date_19,
        //     "date_20" => $total_mpp_date_20,
        //     "date_21" => $total_mpp_date_21,
        //     "date_22" => $total_mpp_date_22,
        //     "date_23" => $total_mpp_date_23,
        //     "date_24" => $total_mpp_date_24,
        //     "date_25" => $total_mpp_date_25,
        //     "date_26" => $total_mpp_date_26,
        //     "date_27" => $total_mpp_date_27,
        //     "date_28" => $total_mpp_date_28,
        //     "date_29" => $total_mpp_date_29,
        //     "date_30" => $total_mpp_date_30,
        //     "date_31" => $total_mpp_date_31
        // );

        // $arr_total_press = array(
        //     "date_1" => $total_press_date_1,
        //     "date_2" => $total_press_date_2,
        //     "date_3" => $total_press_date_3,
        //     "date_4" => $total_press_date_4,
        //     "date_5" => $total_press_date_5,
        //     "date_6" => $total_press_date_6,
        //     "date_7" => $total_press_date_7,
        //     "date_8" => $total_press_date_8,
        //     "date_9" => $total_press_date_9,
        //     "date_10" => $total_press_date_10,
        //     "date_11" => $total_press_date_11,
        //     "date_12" => $total_press_date_12,
        //     "date_13" => $total_press_date_13,
        //     "date_14" => $total_press_date_14,
        //     "date_15" => $total_press_date_15,
        //     "date_16" => $total_press_date_16,
        //     "date_17" => $total_press_date_17,
        //     "date_18" => $total_press_date_18,
        //     "date_19" => $total_press_date_19,
        //     "date_20" => $total_press_date_20,
        //     "date_21" => $total_press_date_21,
        //     "date_22" => $total_press_date_22,
        //     "date_23" => $total_press_date_23,
        //     "date_24" => $total_press_date_24,
        //     "date_25" => $total_press_date_25,
        //     "date_26" => $total_press_date_26,
        //     "date_27" => $total_press_date_27,
        //     "date_28" => $total_press_date_28,
        //     "date_29" => $total_press_date_29,
        //     "date_30" => $total_press_date_30,
        //     "date_31" => $total_press_date_31
        // );


        //TOTAL CUTTING

        // $persenCutting = @round(($qTotalCutting[0]->total / $qTotalCutting[0]->total_capacity) * 100, 2);
        $persenCutting = 0;

        $style = ""; 

        if($persenCutting >= 100){

            $style = "style='background:#FFD8D8;'";

        }


        $html .= "  <tr>

        <th colspan='8' style='text-align:center;'><b>TOTAL MPP</b></th>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

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
            <td rowspan='2' colspan='3' style='text-align:left; vertical-align:middle;'><b>TOTAL PRESS</b></td>
            <td colspan='4'><b>LOADING</b></td>
            <td><b>". format_number($total_ct_all) ."</b></td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {
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
            <td colspan='5'><b>CAPACITY</b></td>
            <td><b>0</b></td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate2   = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {
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

