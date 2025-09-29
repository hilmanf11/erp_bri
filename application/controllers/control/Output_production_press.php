<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Output_production_press extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[output_production_press.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[output_production_press.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/output_production_press');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('output_production_press', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    // public function readItemFg($period="")
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $send = $this->crud->query("
    //     select distinct a.item_fg_id, a.workorder as wo_no, a.period, b.number, b.name, a.lot_no ,'Supply Sheets' as modul
    //     from supply_sheets a 
    //     join item_fg b on a.item_fg_id=b.id 
    //     where a.period='$period' and (b.number like '%$post%' or b.number_customer like '%$post%' or b.name like '%$post%' or a.workorder like '%$post%' or a.lot_no like '%$post%')
        
    //     UNION

    //     select distinct a.item_fg_id, a.wo_no, a.period, b.number, b.name, a.lot_no , 'Production Schedule' as modul
    //     from production_schedules a 
    //     join item_fg b on a.item_fg_id=b.id 
    //     where a.period='$period' and a.status_subcont = 'YES' and a.subcont_type = 'Jasa'
    //     and (b.number like '%$post%' or b.number_customer like '%$post%' or b.name like '%$post%' or a.wo_no like '%$post%' or a.lot_no like '%$post%') 
        
    //     order by modul,item_fg_id asc 
    //     ");  /** production_schedules hanya tampil Subcont Type Jasa (Bu Septi) */
        
    //     echo json_encode($send);
    // }

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $period   = base64_decode($this->input->post('period'));
        $wp   = base64_decode($this->input->post('wp'));
        $machine_id   = base64_decode($this->input->post('machine_id'));
        // $workorder   = base64_decode($this->input->post('workorder'));

        $query ="SELECT DISTINCT 
            a.item_fg_id, 
            b.number, 
            b.name, 
            a.qty as planning_qty,
            a.workorder
        FROM production_schedule_press a 
        JOIN item_fg b ON a.item_fg_id=b.id
        WHERE a.period='$period' 
        AND a.wp='$wp' 
        AND a.machine_id = '$machine_id'
        AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%' OR a.qty LIKE '%$post%') 
        ORDER BY b.number ASC
        ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function readItemSub()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_fg WHERE `type`='SA' and (number like '%$post%' or number_customer like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    public function readWoNos()
    {
        $send = $this->crud->query("SELECT DISTINCT workorder
        FROM output_production_press
        WHERE `deleted` = 0
        ORDER BY workorder DESC");
        echo json_encode($send);
    }

    public function readMachine()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM machines WHERE `status` = 0 and (number like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    //GET DATA
    public function readMachinePressByWP()
    {
        $post = $this->input->post('q'); // EasyUI default search param
        $period   = base64_decode($this->input->post('period'));
        $wp   = base64_decode($this->input->post('wp'));

        $send = $this->crud->query("
            SELECT 
                a.machine_id,
                b.number,
                b.name
            FROM production_schedule_press as a
            JOIN machines b ON a.machine_id = b.id
            WHERE a.period = '$period' 
            AND a.wp = '$wp'
            AND (b.number like '%$post%' OR b.name like '%$post%' OR b.id like '%$post%')
            GROUP BY b.id, b.number, b.name
        ");
        echo json_encode($send);
    }

    public function readWorkorder()
    {
        $machine = base64_decode($this->input->post('machine_id'));
        $period = base64_decode($this->input->post('period'));
        $wp = base64_decode($this->input->post('wp'));

        $send = $this->crud->query("SELECT DISTINCT workorder FROM production_schedule_press WHERE `status` = 0 AND `period` = '$period' AND `machine_id` = '$machine' AND wp = '$wp' ORDER BY `workorder` DESC");
        echo json_encode($send);
    }

    //GET DATA
    public function autonumber($type = "")
    {
        $ymd = date("ymd");
        $sql = $this->db->query("SELECT max(`number`) as kode FROM output_production_press where `number` like '%$ymd%'");
        $row = $sql->row();
        if ($row->kode == null) {
            $autonumber = "PPRS-" . $ymd . "001";
        } else {
            $kode = substr($row->kode, -3);
            $autonumber = "PPRS-" . $ymd . sprintf("%03s", $kode + 1);
        }

        if($type == "return") {
            return $autonumber;
        }

        echo $autonumber;
    }

    public function readNumber()
    {
        $send = $this->crud->query("SELECT DISTINCT `number`
        FROM output_production_press
        WHERE `deleted` = 0
        ORDER BY `number` DESC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            // $get = $this->input->get();

            // $filter_from = $this->input->get('filter_from');
            // $filter_to = $this->input->get('filter_to');
            // $filter_division = $this->input->get('filter_division');
            
            $filter_period = $this->input->get('filter_period');
            $filter_trans_date = $this->input->get('filter_trans_date');
            $filter_number = $this->input->get('filter_number');
            $filter_shift = $this->input->get('filter_shift');
            $filter_wp = $this->input->get('filter_wp');
            $filter_workorder = $this->input->get('filter_workorder');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select("a.*, b.number as item_number, b.name as item_name");
            $this->db->from('output_production_press a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            // if ($filter_from != "" or $filter_to != "") {
            //     $this->db->where('a.trans_date >=', $filter_from);
            //     $this->db->where('a.trans_date <=', $filter_to);
            // }
            // if ($filter_division != "") {
            //     $this->db->where('b.division_id', $filter_division);
            // }

            if ($filter_period != "") {
                $this->db->where('a.period', $filter_period);
            }else{
                $this->db->where('a.trans_date', date('Y-m-d'));
            }
            if ($filter_trans_date != "") {
                $this->db->where('a.trans_date', $filter_trans_date);
            }
            if ($filter_number != "") {
                $this->db->where('a.number', $filter_number);
            }
            if ($filter_shift != "") {
                $this->db->where('a.shift', $filter_shift);
            }
            if ($filter_wp != "") {
                $this->db->where('a.wp', $filter_wp);
            }
            if ($filter_workorder != "") {
                $this->db->where('a.workorder', $filter_workorder);
            }
            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            if ($filter_status != "") {
                $this->db->where('a.status', $filter_status);
            }
            $this->db->group_by('a.number,a.period,a.trans_date,a.shift');
            $this->db->order_by('a.created_date', 'DESC');
            // $this->db->order_by('b.number', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $filter_workorder = base64_decode($this->input->get('workorder'));
            $filter_item_fg_id = base64_decode($this->input->get('item_fg_id'));

            // $this->db->select("a.*, 
            //     CEILING(a.planning_qty / 3) as planning_qty_shift, 
            //     b.number as item_fg_number, 
            //     b.name as item_fg_name, 
            //     c.number as machine_number, 
            //     e.number as item_rm_number,
            //     CEILING(a.qty_ok + a.qty_ng + qty_ng_mold) as total_qty,
            //     (CEILING(a.qty_ok + a.qty_ng + qty_ng_mold) - CEILING(a.planning_qty / 3)) as minus_prod,
            //     g.cavity_standard as standard_cavity,
            //     COALESCE(a.actual_shoot, 0) - COALESCE(a.target_shoot, 0) as shoot_deviation,
            //     ROUND((COALESCE(a.actual_shoot, 0) / NULLIF(COALESCE(a.target_shoot, 0),0)) * 100, 2) as achievment,
            //     ROUND((COALESCE(a.qty_ng, 0) / NULLIF(a.qty_ok + a.qty_ng + qty_ng_mold,0)) * 100, 2) as ng_prod,
            //     ROUND((COALESCE(a.qty_ng_mold, 0) / NULLIF(a.qty_ok + a.qty_ng + qty_ng_mold,0)) * 100, 2) as ng_mold,
            //     COALESCE(ROUND((COALESCE(a.waste, 0) / NULLIF(COALESCE(a.total_compound_used, 0),0)) * 100, 2), 0) as waste_percen,
            //     ROUND((COALESCE(a.total_compound_used, 0) / COALESCE(a.actual_shoot, 0)) * 100, 2) as total_used_shoot,
            //     ROUND((COALESCE(a.waste, 0) / COALESCE(a.actual_shoot, 0)) * 100, 2) as total_waste_shoot

            // ");

            $this->db->select("a.*, 
                b.number as item_fg_number,
                b.name as item_fg_name, 
                c.number as machine_number, 
                e.number as item_rm_number,
                
                CEILING(COALESCE(a.planning_qty,0) / 3) as planning_qty_shift, 
            
                CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) as total_qty,

                (CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) 
                - CEILING(COALESCE(a.planning_qty,0) / 3)) as minus_prod,

                COALESCE(g.cavity_standard,0) as standard_cavity,

                (COALESCE(a.actual_shoot,0) - COALESCE(a.target_shoot,0)) as shoot_deviation,

                COALESCE(ROUND((COALESCE(a.actual_shoot,0) / NULLIF(COALESCE(a.target_shoot,0),0)) * 100, 2),0) as achievment,

                COALESCE(ROUND((COALESCE(a.qty_ng,0) / NULLIF(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0),0)) * 100, 2),0) as ng_prod,

                COALESCE(ROUND((COALESCE(a.qty_ng_mold,0) / NULLIF(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0),0)) * 100, 2),0) as ng_mold,

                COALESCE(ROUND((COALESCE(a.waste,0) / NULLIF(COALESCE(a.total_compound_used,0),0)) * 100, 2),0) as waste_percen,

                COALESCE(ROUND((COALESCE(a.total_compound_used,0) * 1000 / NULLIF(COALESCE(a.actual_shoot,0),0)), 2),0) as total_used_shoot,

                COALESCE(ROUND((COALESCE(a.waste,0) * 1000 / NULLIF(COALESCE(a.actual_shoot,0),0)), 2),0) as total_waste_shoot
            ");

            $this->db->from('output_production_press a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('machines c', 'a.machine_id = c.id', 'left');

            $this->db->join('bom d', 'a.item_fg_id = d.item_fg_id and d.priority = 1', 'left');
            $this->db->join('item_rm e', 'd.item_rm_id = e.id', 'left');

            $this->db->join("(SELECT item_fg_id, MIN(mold_id) AS mold_id
                  FROM setting_molds
                  GROUP BY item_fg_id
                 ) f", "a.item_fg_id = f.item_fg_id", "left");

            // $this->db->join('setting_molds f', 'a.item_fg_id = f.item_fg_id', 'left');
            $this->db->join('molds g', 'f.mold_id = g.id', 'left');

            $this->db->where('a.number', $number);
            if ($filter_workorder != "") {
                $this->db->where('a.workorder', $filter_workorder);
            }
            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $this->db->select("a.*, b.number as item_fg_number, b.name as item_fg_name,  c.number as machine_number");
            $this->db->from('output_production_press a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('machines c', 'a.machine_id = c.id', 'left');
            $this->db->where('a.number', $number);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $dataFinal = array(
                //field
                // "trans_date" => $post['trans_date'],
                // "number" => $post['number'],
                // "period" => $post['period'],
                // "wp" => $post['wp'],
                // "shift" => $post['shift'],
                "item_fg_id" => $post['item_fg_id'],
                "machine_id" => $post['machine_id'],
                "planning_qty" => $post['planning_qty'],
                "qty_ok" => $post['qty_ok'],
                "qty_ng" => $post['qty_ng'],
                "qty_ng_mold" => $post['qty_ng_mold'],
                "workorder" => $post['workorder'],
                "actual_cavity" => $post['actual_cavity'],
                "operator" => $post['operator'],
                "pic" => $post['pic'],
                "standard_curing_time" => $post['standard_curing_time'],
                "actual_curing_time" => $post['actual_curing_time'],
                "shift_hour" => $post['shift_hour'],
                "target_shoot" => $post['target_shoot'],
                "actual_shoot" => $post['actual_shoot'],
                "total_compound_used" => $post['total_compound_used'],
                "waste" => $post['waste'],
                "mold_cleaning" => $post['mold_cleaning'],
                "trial" => $post['trial'],
                "mold_changing" => $post['mold_changing'],
                "machine_repair" => $post['machine_repair'],
                "mold_repair" => $post['mold_repair'],
                "others" => $post['others'],
                "remarks" => $post['remarks'],
            );

            if (@$post['id'] != "") {
                $send = $this->crud->update('output_production_press', ["id" => $post['id']], $dataFinal);
            } else {
                $checkOutputPress = $this->crud->read('output_production_press', [], [
                    "period"     => $post['period'],
                    // "trans_date" => $post['trans_date'],
                    "wp"         => $post['wp'],
                    "shift"      => $post['shift'],
                    "machine_id" => $post['machine_id'],
                    "item_fg_id" => $post['item_fg_id'],
                    "workorder"  => $post['workorder'],
                ]);

                $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
                $machine = $this->crud->read("machines", [], ["id" => $post['machine_id']]);

                if (!empty($checkOutputPress)) {
                    echo json_encode(array(
                        "title"   => "Duplicate Data",
                        "message" => "Duplicate Data for Product {$item_fg->number} on Machine {$machine->number} (Period: {$post['period']}, WP: {$post['wp']}, Shift: {$post['shift']}, Workorder: {$post['workorder']}).",
                        "theme"   => "error"
                    ));
                    exit;
                }

                $send = $this->crud->create('output_production_press', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('output_production_press', $data);
        echo $send;
    }

    //UPLOAD DATA
    // public function upload()
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);
    //     for ($i = 3; $i <= $total_row; $i++) {
    //         $datas[] = array(
    //             //excel
    //             'trans_date' => $data->val($i, 2),
    //             'period' => $data->val($i, 3),
    //             'shift' => $data->val($i, 4),
    //             'item_number' => $data->val($i, 5),
    //             'wo_no' => $data->val($i, 6),
    //             'qty' => $data->val($i, 7),
    //             'qty_wip' => $data->val($i, 8),
    //             'machine_number' => $data->val($i, 9),
    //             'remarks' => $data->val($i, 10),
    //         );
    //     }
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777);

        $data = new Spreadsheet_Excel_Reader($target, false);
        $total_row = $data->rowcount($sheet_index = 0);
        $datas = [];

        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'period' => $data->val($i, 2),
                'trans_date' => $data->val($i, 3),
                'wp' => $data->val($i, 4),
                'shift' => $data->val($i, 5),
                'machine_id' => $data->val($i, 6),
                'item_fg_id' => $data->val($i, 7),
                'workorder' => $data->val($i, 8),
                'qty_ok' => $data->val($i, 9),
                'qty_ng' => $data->val($i, 10),
                'qty_ng_mold' => $data->val($i, 11),
                'actual_cavity' => $data->val($i, 12),

                'standard_curing_time' => $data->val($i, 13),
                'actual_curing_time' => $data->val($i, 14),
                'shift_hour' => $data->val($i, 15),
                'target_shoot' => $data->val($i, 16),
                'actual_shoot' => $data->val($i, 17),
                'total_compound_used' => $data->val($i, 18),
                'waste' => $data->val($i, 19),
                'mold_cleaning' => $data->val($i, 20),
                'trial' => $data->val($i, 21),
                'mold_changing' => $data->val($i, 22),
                'machine_repair' => $data->val($i, 23),
                'mold_repair' => $data->val($i, 24),
                'others' => $data->val($i, 25),

                'operator' => $data->val($i, 26),
                'pic' => $data->val($i, 27),
                'remarks' => $data->val($i, 28),
            );
        }

        echo json_encode([
            "total" => count($datas),
            "data" => $datas
        ]);

        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/output_production_press.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/output_production_press.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_output_production_press_" . date("Ymd_s") . ".xls";

        header("Content-Description: File Upload Failed");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file);
    }

    //UPLOAD CREATE DATA
    // public function uploadCreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         $item_fg = $this->crud->read('item_fg', [], array("number" => $data['item_number']));
    //         $machines = $this->crud->read('machines', [], array("number" => $data['machine_number']));
    //         $data_cek = array(
    //                 "item_fg_id" => $item_fg->id,
    //                 "trans_date" => $data['trans_date'],
    //                 "wo_no" => $data['wo_no'],
    //                 "period" => $data['period'],
    //                 "qty" => $data['qty'],
    //                 "qty_wip" => $data['qty_wip'],
    //                 "shift" => $data['shift'],
    //                 "remarks" => $data['remarks'],
    //                 "machine_number" => $data['machine_number'],
    //             );
    //         $output_production_press = $this->crud->read('output_production_press', [], $data_cek);
    //         $send = $this->crud->query("
    //             SELECT DISTINCT a.item_fg_id, a.workorder AS wo_no, a.period, b.number, b.name, a.lot_no, 'Supply Sheets' AS modul
    //             FROM supply_sheets a 
    //             JOIN item_fg b ON a.item_fg_id = b.id 
    //             WHERE a.period = '{$data['period']}'

    //             UNION

    //             SELECT DISTINCT a.item_fg_id, a.wo_no, a.period, b.number, b.name, a.lot_no, 'Production Schedule' AS modul
    //             FROM production_schedules a 
    //             JOIN item_fg b ON a.item_fg_id = b.id 
    //             WHERE a.period = '{$data['period']}' AND a.status_subcont = 'YES' AND a.subcont_type = 'Jasa'

    //             ORDER BY modul, item_fg_id ASC
    //         ");

    //         $item_fg_ids = array_column($send, 'item_fg_id');

    //         if (empty($item_fg) || empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not Found","message" => "Product number " . $data['item_number'] . " NOT FOUND","theme" => "error"));
    //             // return;
    //         } elseif (empty($machines)) {
    //         echo json_encode(array("title" => "Not Found","message" => "Machine number " . $data['machine_number'] . " NOT FOUND IN MODUL MACHINE","theme" => "error"));
    //         // return;
    //         } elseif ($output_production_press) {
    //         echo json_encode(array("title" => "Duplicate","message" => "Duplicate Product number " . $data['item_number'] . " FOUND","theme" => "error"));
    //         } elseif (!in_array($item_fg->id, $item_fg_ids)) {
    //             echo json_encode(array("title" => "Not Found","message" => "Product number " . $data['item_number'] . " NOT FOUND IN PERIOD " . $data['period'],"theme" => "error"));
    //             // return;
    //         }else{
    //             $lot_no = null;
    //             foreach ($send as $row) {
    //                 if ($row->item_fg_id == $item_fg->id) {
    //                     $lot_no = $row->lot_no;
    //                     break;
    //                 }
    //             }

    //             // AUTONUMBER
    //             $ymd = date("ymd");
    //             $sql = $this->db->query("SELECT MAX(`number`) AS kode FROM output_production_press WHERE `number` LIKE '%$ymd%'");
    //             $row = $sql->row();
    //             if ($row->kode == null) {
    //                 $autonumber = "PRD-" . $ymd . "0001";
    //             } else {
    //                 $kode = substr($row->kode, -4);
    //                 $autonumber = "PRD-" . $ymd . sprintf("%04s", $kode + 1);
    //             }

    //             $dataFinal = array(
    //                 "number" => $autonumber,
    //                 "item_fg_id" => $item_fg->id,
    //                 "trans_date" => $data['trans_date'],
    //                 "wo_no" => $data['wo_no'],
    //                 "period" => $data['period'],
    //                 "qty" => $data['qty'],
    //                 "qty_wip" => $data['qty_wip'],
    //                 "shift" => $data['shift'],
    //                 "remarks" => $data['remarks'],
    //                 "lot_no" => $lot_no,
    //                 "machine_number" => $data['machine_number'],
    //                 "type" => "Upload",
    //             );

    //             $send   = $this->crud->create('output_production_press', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $raw = file_get_contents("php://input");
            $postData = json_decode($raw, true);

            $data_list = $postData['data'];
            
            $total_expected = count($data_list);
            $processed_count = 0;

            $this->db->trans_begin();
            $results = [];

            $groupNumbers = [];
            foreach ($data_list as $index => $data) {
                $processed_count++;

                // if (
                //     empty($data['period']) ||
                //     empty($data['trans_date']) ||
                //     empty($data['wp']) ||
                //     empty($data['shift']) ||
                //     empty($data['machine_id']) ||
                //     empty($data['item_fg_id']) || 
                //     empty($data['workorder']) || 
                //     $data['qty_ok'] == "" || 
                //     $data['qty_ng'] == "" || 
                //     $data['qty_ng_mold'] == "" || 
                //     $data['actual_cavity'] == "" || 

                //     $data['standard_curing_time'] == "" || 
                //     $data['actual_curing_time'] == "" || 
                //     $data['shift_hour'] == "" || 
                //     $data['target_shoot'] == "" || 
                //     $data['actual_shoot'] == "" || 
                //     $data['total_compound_used'] == "" ||

                //     !strtotime($data['trans_date']) ||
                //     !is_numeric($data['qty_ok']) ||
                //     !is_numeric($data['qty_ng']) ||
                //     !is_numeric($data['qty_ng_mold']) ||
                //     !is_numeric($data['actual_cavity'])
                //    ) {
                //         $results[] = [
                //             "status" => "failed",
                //             "item" => "Line " . ($index + 1),
                //             "message" => "Invalid or missing data"
                //         ];
                //         continue;
                // }

                if (empty($data['period'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Period is required"
                    ];
                    continue;
                }
                if (!preg_match('/^\d{6}$/', $data['period'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Period format must be YYYYMM"
                    ];
                    continue;
                }

                if (empty($data['trans_date'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Production Date is required"
                    ];
                    continue;
                }
                if (!strtotime($data['trans_date'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Production Date is invalid format"
                    ];
                    continue;
                }

                if (empty($data['wp'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "WP No is required"
                    ];
                    continue;
                }

                if (empty($data['shift'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Shift is required"
                    ];
                    continue;
                }

                if (!in_array($data['shift'], [1, 2, 3])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Shift must be 1, 2, or 3"
                    ];
                    continue;
                }

                if (empty($data['machine_id'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Machine No is required"
                    ];
                    continue;
                }

                if (empty($data['item_fg_id'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Product No is required"
                    ];
                    continue;
                }

                if (empty($data['workorder'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Work Order is required"
                    ];
                    continue;
                }

                if ($data['qty_ok'] === "" || !is_numeric($data['qty_ok'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Qty OK must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['qty_ng'] === "" || !is_numeric($data['qty_ng'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Qty NG must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['qty_ng_mold'] === "" || !is_numeric($data['qty_ng_mold'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Qty NG Mold must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['actual_cavity'] === "" || !is_numeric($data['actual_cavity'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Actual Cavity must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['standard_curing_time'] === "" || !is_numeric($data['standard_curing_time'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Standard Curing Time must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['actual_curing_time'] === "" || !is_numeric($data['actual_curing_time'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Actual Curing Time must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['shift_hour'] === "" || !is_numeric($data['shift_hour'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Hour/Shift must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['target_shoot'] === "" || !is_numeric($data['target_shoot'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Target Shoot must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['actual_shoot'] === "" || !is_numeric($data['actual_shoot'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Actual Shoot must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['total_compound_used'] === "" || !is_numeric($data['total_compound_used'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Total Compound Used must be numeric and not empty"
                    ];
                    continue;
                }

                // $data['process_id'] = 'PC006'; // Pressing process
                // $wp = $data['wp'];

                // Validasi qty tidak boleh kosong atau nol
                // if (!isset($data['qty']) || $data['qty'] == "" || $data['qty'] == null) {
                //     $results[] = [
                //         "status" => "failed",
                //         "item" => "Line " . ($index + 1),
                //         "message" => "Quantity must be greater than 0"
                //     ];
                //     continue;
                // }

                $groupKey = $data['period'] . '|' . $data['trans_date'] . '|' . $data['wp'] . '|' . $data['shift'];

                if (!isset($groupNumbers[$groupKey])) {
                    $groupNumbers[$groupKey] = $this->autonumber("return");
                }

                $groupNumber = $groupNumbers[$groupKey];

                $machine = $this->crud->read('machines', [], ["number" => $data['machine_id']]);
                if (empty($machine)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Machine No. " . $data['machine_id'] . " Not Found"
                    ];
                    continue;
                }

                $checkMachineProdSchPress = $this->crud->read('production_schedule_press', [], ["machine_id" => $machine->id]);

                if (empty($checkMachineProdSchPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Machine No. " . $data['machine_id'] . " not found in Production Schedule Press"
                    ];
                    continue;
                }

                $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
                if (empty($item_fg)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. " . $data['item_fg_id'] . " Not Found"
                    ];
                    continue;
                }

                $checkItemProdSchPress = $this->crud->read('production_schedule_press', [], [
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id
                ]);

                if (empty($checkItemProdSchPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. " . $data['item_fg_id'] . " not found in Production Schedule Press for Machine " . $data['machine_id']
                    ];
                    continue;
                }

                $checkWPProdSchPress = $this->crud->read('production_schedule_press', [], [
                    "wp" => $data['wp'],
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                ]);

                if (empty($checkWPProdSchPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "WP No. {$data['wp']} not found in Production Schedule Press ". "for Product No. {$item_fg->number} on Machine {$machine->number}."
                    ];
                    continue;
                }

                $checkWOProdSchPress = $this->crud->read('production_schedule_press', [], [
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                    "workorder" => $data['workorder'],
                ]);

                if (empty($checkWOProdSchPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Work Order No. {$data['workorder']} not found in Production Schedule Press ". "for Product No. {$item_fg->number} on Machine {$machine->number}."
                    ];
                    continue;
                }

                $planning_qty = @$checkWOProdSchPress->qty;

                $checkData = $this->crud->read('output_production_press', [], [
                    "period"     => $data['period'],
                    "trans_date" => $data['trans_date'],
                    "wp"         => $data['wp'],
                    "shift"      => $data['shift'],
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                    "workorder"  => $data['workorder'],
                ]);

                // if (!empty($checkData)) {
                //     $results[] = [
                //         "status" => "failed",
                //         "item" => "Line " . ($index + 1),
                //         "message" => "Duplicate Data: Period " . $data['period'] . 
                //                     ", Product No. " . $data['item_fg_id'] . 
                //                     ", Machine No. " . $data['machine_id'] . 
                //                     ", WP No. " . $wp . 
                //                     ", Trans Date " . $data['trans_date']
                //     ];
                //     continue;
                // }

                // // Generate workorder
                // $workorder = $data['workorder'];

                // // Generate month dan year dari trans_date
                // $dateObj = DateTime::createFromFormat('Y-m-d', $data['trans_date']);
                // $year = $dateObj->format('Y');
                // $month = $dateObj->format('m');

                $dataFinal = array(
                    "period"                => $data['period'],
                    "number"                => $groupNumber,
                    "trans_date"            => $data['trans_date'],
                    "wp"                    => $data['wp'],
                    "shift"                 => $data['shift'],
                    "machine_id"            => $machine->id,
                    "item_fg_id"            => $item_fg->id,
                    "workorder"             => $data['workorder'],
                    "planning_qty"          => $planning_qty,
                    "qty_ok"                => $data['qty_ok'],
                    "qty_ng"                => $data['qty_ng'],
                    "qty_ng_mold"           => $data['qty_ng_mold'],
                    "actual_cavity"         => $data['actual_cavity'],

                    "standard_curing_time"  => $data['standard_curing_time'],
                    "actual_curing_time"    => $data['actual_curing_time'],
                    "shift_hour"            => $data['shift_hour'],
                    "target_shoot"          => $data['target_shoot'],
                    "actual_shoot"          => $data['actual_shoot'],
                    "total_compound_used"   => $data['total_compound_used'],
                    "waste"                 => $data['waste'],
                    "mold_cleaning"         => $data['mold_cleaning'],
                    "trial"                 => $data['trial'],
                    "mold_changing"         => $data['mold_changing'],
                    "machine_repair"        => $data['machine_repair'],
                    "mold_repair"           => $data['mold_repair'],
                    "others"                => $data['others'],

                    "operator"              => $data['operator'],
                    "pic"                   => $data['pic'],
                    "remarks"               => $data['remarks'],
                );

                try {
                    if (!empty($checkData)) {
                        // Update
                        $this->db->update('output_production_press', [
                            "qty_ok"                => $data['qty_ok'],
                            "qty_ng"                => $data['qty_ng'],
                            "qty_ng_mold"           => $data['qty_ng_mold'],
                            "actual_cavity"         => $data['actual_cavity'],
                            "standard_curing_time"  => $data['standard_curing_time'],
                            "actual_curing_time"    => $data['actual_curing_time'],
                            "shift_hour"            => $data['shift_hour'],
                            "target_shoot"          => $data['target_shoot'],
                            "actual_shoot"          => $data['actual_shoot'],
                            "total_compound_used"   => $data['total_compound_used'],
                            "waste"                 => $data['waste'],
                            "mold_cleaning"         => $data['mold_cleaning'],
                            "trial"                 => $data['trial'],
                            "mold_changing"         => $data['mold_changing'],
                            "machine_repair"        => $data['machine_repair'],
                            "mold_repair"           => $data['mold_repair'],
                            "others"                => $data['others'],
                            "operator"              => $data['operator'],
                            "pic"                   => $data['pic'],
                            "remarks"               => $data['remarks'],
                        ], [
                            "period"     => $data['period'],
                            "trans_date" => $data['trans_date'],
                            "wp"         => $data['wp'],
                            "shift"      => $data['shift'],
                            "machine_id" => $machine->id,
                            "item_fg_id" => $item_fg->id,
                            "workorder"  => $data['workorder'],
                        ]);

                        $status = "update";
                    } else {
                        // Insert
                        $this->crud->create('output_production_press', $dataFinal);

                        $status = "insert";
                    }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "WP {$data['wp']} Shift {$data['shift']} for Product {$item_fg->number} on Machine {$machine->number} updated");

                    $results[] = [
                        "status" => "success",
                        "item" => $res_item,
                        "message" => $res_msg
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        "status" => "failed",
                        "item" => $item_fg->name,
                        "message" => $e->getMessage()
                    ];
                    continue;
                }
            }

            $failed = array_filter($results, fn($r) => $r['status'] === 'failed');
            $hasDbError = ($this->db->trans_status() === FALSE);

            if (count($failed) > 0 || $hasDbError) {
                $filePath = 'failed/output_production_press.xls';

                $html = '
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body>
                    <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; font-family: Arial, sans-serif;">
                        <thead style="background-color: #f2f2f2;">
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 100px; text-align: left;">Line</th>
                                <th style="width: 450px; text-align: left;">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                ';

                $no = 1;
                foreach ($failed as $row) {
                    $line = htmlspecialchars($row['item']);
                    $msg  = htmlspecialchars($row['message']);
                    $html .= "
                        <tr>
                            <td style='text-align: center;'>{$no}</td>
                            <td style='text-align: left;'>{$line}</td>
                            <td style='text-align: left;'>{$msg}</td>
                        </tr>";
                    $no++;
                }

                $html .= '
                        </tbody>
                    </table>
                </body>
                </html>';

                file_put_contents($filePath, $html);

                echo json_encode([
                    "theme" => "error",
                    "title" => "Upload Failed",
                    "message" => "Data failed to save",
                    "results" => $results,
                    "total_expected" => $total_expected,
                    "processed_count" => $processed_count,
                    "stopped_at" => $index + 1
                ]);
            } else {
                @unlink('failed/output_production_press.xls');

                $this->db->trans_commit();
                echo json_encode([
                    "theme" => "success",
                    "title" => "Upload Successfully",
                    "message" => "Data uploaded successfully",
                    "results" => $results,
                    "total_expected" => $total_expected,
                    "processed_count" => $processed_count,
                    "stopped_at" => $index + 1
                ]);
            }

        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=output_production_press_$format.xls");
        }

        // $get = $this->input->get();
        // $filter_from = $this->input->get('filter_from');
        // $filter_to = $this->input->get('filter_to');
        // $filter_wo_no = $this->input->get('filter_wo_no');
        // $filter_number = $this->input->get('filter_number');
        // $filter_shift = $this->input->get('filter_shift');
        // $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        // $filter_division = $this->input->get('filter_division');

        $filter_period = $this->input->get('filter_period');
        $filter_trans_date = $this->input->get('filter_trans_date');
        $filter_number = $this->input->get('filter_number');
        $filter_shift = $this->input->get('filter_shift');
        $filter_wp = $this->input->get('filter_wp');
        $filter_workorder = $this->input->get('filter_workorder');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_status = $this->input->get('filter_status');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $this->db->select("a.*, b.number as item_fg_number, b.name as item_fg_name");

        $this->db->select("a.*,
            b.number as item_fg_number,
            b.name as item_fg_name,
            c.number as machine_number,
            e.number as item_rm_number,
            
            CEILING(COALESCE(a.planning_qty,0) / 3) as planning_qty_shift, 
        
            CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) as total_qty,

            (CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) 
            - CEILING(COALESCE(a.planning_qty,0) / 3)) as minus_prod,

            COALESCE(g.cavity_standard,0) as standard_cavity,

            (COALESCE(a.actual_shoot,0) - COALESCE(a.target_shoot,0)) as shoot_deviation,

            COALESCE(ROUND((COALESCE(a.actual_shoot,0) / NULLIF(COALESCE(a.target_shoot,0),0)) * 100, 2),0) as achievment,

            COALESCE(ROUND((COALESCE(a.qty_ng,0) / NULLIF(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0),0)) * 100, 2),0) as ng_prod,

            COALESCE(ROUND((COALESCE(a.qty_ng_mold,0) / NULLIF(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0),0)) * 100, 2),0) as ng_mold,

            COALESCE(ROUND((COALESCE(a.waste,0) / NULLIF(COALESCE(a.total_compound_used,0),0)) * 100, 2),0) as waste_percen,

            COALESCE(ROUND((COALESCE(a.total_compound_used,0) * 1000 / NULLIF(COALESCE(a.actual_shoot,0),0)), 2),0) as total_used_shoot,

            COALESCE(ROUND((COALESCE(a.waste,0) * 1000 / NULLIF(COALESCE(a.actual_shoot,0),0)), 2),0) as total_waste_shoot
        ");

        $this->db->from('output_production_press a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('machines c', 'a.machine_id = c.id', 'left');

        $this->db->join('bom d', 'a.item_fg_id = d.item_fg_id and d.priority = 1', 'left');
        $this->db->join('item_rm e', 'd.item_rm_id = e.id', 'left');

        $this->db->join("(SELECT item_fg_id, MIN(mold_id) AS mold_id
                FROM setting_molds
                GROUP BY item_fg_id
                ) f", "a.item_fg_id = f.item_fg_id", "left");

        // $this->db->join('setting_molds f', 'a.item_fg_id = f.item_fg_id', 'left');
        $this->db->join('molds g', 'f.mold_id = g.id', 'left');

        if ($filter_period != "") {
            $this->db->where('a.period', $filter_period);
        }
        if ($filter_trans_date != "") {
            $this->db->where('a.trans_date', $filter_trans_date);
        }
        if ($filter_number != "") {
            $this->db->where('a.number', $filter_number);
        }
        if ($filter_shift != "") {
            $this->db->where('a.shift', $filter_shift);
        }
        if ($filter_wp != "") {
            $this->db->where('a.wp', $filter_wp);
        }
        if ($filter_workorder != "") {
            $this->db->where('a.workorder', $filter_workorder);
        }
        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }
        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#output_production_press {border-collapse: collapse;width: 100%;font-size: 12px;}#output_production_press td, #output_production_press th {border: 1px solid #ddd;padding: 2px;}#output_production_press tr:nth-child(even){background-color: #f2f2f2;}#output_production_press tr:hover {background-color: #ddd;}#output_production_press th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>OUTPUT PRODUCTION PRESS</h3>
            </div>
        </center>
        
        <table id="output_production_press" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" >Period</th>
                <th rowspan="2" >Production Date</th>
                <th rowspan="2" >Document No</th>
                <th rowspan="2" >Shift</th>
                <th rowspan="2" >WP No</th>
                <th rowspan="2" >Machine No</th>
                <th rowspan="2" >Product ID</th>
                <th rowspan="2" >Product No</th>
                <th rowspan="2" >Product Name</th>
                <th rowspan="2" >Compound Name Used</th>
                <th rowspan="2" >Planning/day (pcs)</th>
                <th rowspan="2" >Planning/shift (pcs)</th>
                <th rowspan="2" >Work Order No</th>
                <th rowspan="2" >Operator Name</th>
                <th colspan="4" style="text-align: center;">Output</th>
                <th rowspan="2" >Minus Production</th>
                <th rowspan="2" >Standard Cavity</th>
                <th rowspan="2" >Actual Cavity</th>
                <th rowspan="2" >Standard Curing Time <br> (second)</th>
                <th rowspan="2" >Actual Curing Time <br> (second)</th>
                <th rowspan="2" >Hour/Shift</th>
                <th rowspan="2" >Target Shoot</th>
                <th rowspan="2" >Actual Shoot</th>
                <th rowspan="2" >Shoot Deviation</th>
                <th rowspan="2" >% Achievment</th>
                <th rowspan="2" >% NG Production</th>
                <th rowspan="2" >% NG Mold</th>
                <th rowspan="2" >Total Compound Used <br> (kg)</th>
                <th rowspan="2" >Waste <br> (kg)</th>
                <th rowspan="2" >% Waste</th>
                <th rowspan="2" >Total Used/shoot (gr)</th>
                <th rowspan="2" >Total Waste/shoot (gr)</th>
                <th colspan="6" style="text-align: center;">Downtime</th>
                <th rowspan="2" >Remarks</th>
            </tr>

            <tr>
                <th>OK</th>
                <th>NG Produksi</th>
                <th>NG Mold</th>
                <th>Total</th>
                <th>Mold Cleaning</th>
                <th>Trial</th>
                <th>Mold Changing</th>
                <th>Machine Repair</th>
                <th>Mold Repair</th>
                <th>Others</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['period'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['shift'] . '</td>
                    <td>' . $data['wp'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['item_rm_number'] . '</td>
                    <td>' . format_number($data['planning_qty']) . '</td>
                    <td>' . format_number($data['planning_qty_shift']) . '</td>
                    <td>' . $data['workorder'] . '</td>
                    <td>' . $data['operator'] . '</td>
                    <td>' . format_number($data['qty_ok']) . '</td>
                    <td>' . format_number($data['qty_ng']) . '</td>
                    <td>' . format_number($data['qty_ng_mold']) . '</td>
                    <td>' . format_number($data['total_qty']) . '</td>
                    <td>' . format_number($data['minus_prod']) . '</td>
                    <td>' . format_number($data['standard_cavity']) . '</td>
                    <td>' . format_number($data['actual_cavity']) . '</td>
                    <td>' . format_number($data['standard_curing_time']) . '</td>
                    <td>' . format_number($data['actual_curing_time']) . '</td>
                    <td>' . format_number($data['shift_hour']) . '</td>
                    <td>' . format_number($data['target_shoot']) . '</td>
                    <td>' . format_number($data['actual_shoot']) . '</td>
                    <td>' . format_number($data['shoot_deviation']) . '</td>
                    <td>' . $data['achievment'] . '</td>
                    <td>' . $data['ng_prod'] . '</td>
                    <td>' . $data['ng_mold'] . '</td>
                    <td>' . number_format($data['total_compound_used'], 2, '.', '.') . '</td>
                    <td>' . number_format($data['waste'], 2, '.', '.') . '</td>
                    <td>' . $data['waste_percen'] . '</td>
                    <td>' . number_format($data['total_used_shoot'], 2, '.', '.') . '</td>
                    <td>' . number_format($data['total_waste_shoot'], 2, '.', '.') . '</td>
                    <td>' . format_number($data['mold_cleaning']) . '</td>
                    <td>' . format_number($data['trial']) . '</td>
                    <td>' . format_number($data['mold_changing']) . '</td>
                    <td>' . format_number($data['machine_repair']) . '</td>
                    <td>' . format_number($data['mold_repair']) . '</td>
                    <td>' . format_number($data['others']) . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
