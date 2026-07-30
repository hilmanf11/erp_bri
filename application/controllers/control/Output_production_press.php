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
        $this->load->library('Ciqrcode');
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

    public function readWp()
    {
        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to   = base64_decode($this->input->get('filter_to'));

        $send = $this->crud->query("
            SELECT wp
            FROM output_production_press
            WHERE deleted = 0
            AND trans_date >= '$filter_from'
            AND trans_date <= '$filter_to'
            GROUP BY wp
            ORDER BY (wp + 0) ASC, wp ASC
        ");

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
            a.workorder,
            a.mold_id
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
        $trans_date = $this->input->post('trans_date');
        // $ymd = date("ymd");

        $trans_date = $this->input->post('trans_date');
        if ($trans_date) {
            $ymd = date("ymd", strtotime($trans_date));
        } else {
            $ymd = date("ymd");
        }

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

    public function autonumber_excel($trans_date = "")
    {
        if ($trans_date) {
            $ymd = date("ymd", strtotime($trans_date));
        } else {
            $ymd = date("ymd");
        }

        $sql = $this->db->query("SELECT max(`number`) as kode FROM output_production_press where `number` like '%$ymd%'");
        $row = $sql->row();
        if ($row->kode == null) {
            $autonumber = "PPRS-" . $ymd . "001";
        } else {
            $kode = substr($row->kode, -3);
            $autonumber = "PPRS-" . $ymd . sprintf("%03s", $kode + 1);
        }

        return $autonumber;
    }

    public function readNumber()
    {
        $send = $this->crud->query("SELECT DISTINCT `number`
        FROM output_production_press
        WHERE `deleted` = 0
        ORDER BY `number` DESC");
        echo json_encode($send);
    }

    public function getDataPrint()
    {
        $number = $this->input->get('number');

        $records = $this->db
            ->select("
                a.*, 
                b.number as item_fg_number, 
                b.name as item_fg_name,
                b.mpq as qty_packing
            ")
            ->from('output_production_press a')
            ->join('item_fg b', 'a.item_fg_id = b.id', 'left')
            ->join('machines c', 'a.machine_id = c.id', 'left')
            ->where('a.number', $number)
            ->where('a.qty_ok > 0')
            ->order_by('c.id', 'ASC')
            ->get()
            ->result_array();

        echo json_encode([
            "total" => count($records),
            "rows"  => $records
        ]);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            // $get = $this->input->get();

            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            // $filter_division = $this->input->get('filter_division');
            
            // $filter_period = $this->input->get('filter_period');
            // $filter_trans_date = $this->input->get('filter_trans_date');
            $filter_number = $this->input->get('filter_number');
            $filter_shift = $this->input->get('filter_shift');
            $filter_wp = $this->input->get('filter_wp');
            $filter_workorder = $this->input->get('filter_workorder');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_status = $this->input->get('filter_status');

            // if (empty($filter_period)) {
            //     $filter_period = date('Ym');
            // }

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

            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            // if ($filter_division != "") {
            //     $this->db->where('b.division_id', $filter_division);
            // }

            // if ($filter_period != "") {
            //     $this->db->where('a.period', $filter_period);
            // }else{
            //     $this->db->where('a.trans_date', date('Y-m-d'));
            // }

            // if ($filter_trans_date != "") {
            //     $this->db->where('a.trans_date', $filter_trans_date);
            // }
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

            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->order_by('a.shift', 'ASC');
            $this->db->order_by('(a.wp + 0)', 'ASC', false);
            $this->db->order_by('a.wp', 'ASC');

            // $this->db->order_by('a.created_date', 'DESC');
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
            $filter_workorder = base64_decode($this->input->get('filter_workorder'));
            $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));

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
                g.standard_curing_time,
                FLOOR(COALESCE(a.shift_hour,0) * COALESCE(f.target_shoot_hour,0)) as target_shoot,

                CEILING(COALESCE(a.planning_qty,0) / 3) as planning_qty_shift, 
            
                CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) as total_qty,

                (CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) 
                - CEILING(COALESCE(a.planning_qty,0) / 3)) as minus_prod,

                COALESCE(g.cavity_standard,0) as standard_cavity,

                (COALESCE(a.actual_shoot,0) - FLOOR(COALESCE(a.shift_hour,0) * COALESCE(f.target_shoot_hour,0))) as shoot_deviation,

                COALESCE(ROUND((COALESCE(a.actual_shoot,0) / NULLIF(FLOOR(COALESCE(a.shift_hour,0) * COALESCE(f.target_shoot_hour,0)),0)) * 100, 2),0) as achievment,

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

            // $this->db->join("
            //     (
            //         SELECT 
            //             item_fg_id,
            //             machine_id,
            //             mold_id,
            //             target_shoot_hour
            //         FROM setting_molds
            //     ) f",
            //     "a.item_fg_id = f.item_fg_id 
            //     AND a.machine_id = f.machine_id 
            //     AND a.mold_id = f.mold_id",
            //     "left"
            // );

            $this->db->join(
                'setting_molds f',
                'a.item_fg_id = f.item_fg_id 
                AND a.machine_id = f.machine_id 
                AND a.mold_id = f.mold_id',
                'left'
            );

            // // $this->db->join('setting_molds f', 'a.item_fg_id = f.item_fg_id', 'left');
            // $this->db->join('molds g', 'f.mold_id = g.id', 'left');
            
            $this->db->join('molds g', 'a.mold_id = g.id', 'left');

            $this->db->where('a.number', $number);
            if ($filter_workorder != "") {
                $this->db->where('a.workorder', $filter_workorder);
            }
            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->order_by('c.id', 'ASC');
            $this->db->order_by('a.workorder', 'ASC');
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

            $this->db->where('a.machine_id IS NOT NULL');
            $this->db->where('a.item_fg_id != ""');

            $this->db->order_by('a.machine_id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdateByMachines()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            
            $this->db->select("a.*, b.number as item_fg_number, b.name as item_fg_name,  c.number as machine_number");
            $this->db->from('output_production_press a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('machines c', 'a.machine_id = c.id', 'left');
            $this->db->where('a.number', $number);

            $this->db->where('a.machine_id IS NOT NULL');
            $this->db->where('a.item_fg_id = ""');

            $this->db->order_by('a.machine_id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function checkMachinesWithoutItemFg()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('COUNT(*) as cnt');
            $this->db->from('output_production_press');
            $this->db->where('number', $number);
            $this->db->where('machine_id IS NOT NULL', null, false);
            $this->db->where('(item_fg_id IS NULL OR item_fg_id = "")', null, false);

            $count = $this->db->get()->row()->cnt;

            echo json_encode([
                'has_empty_machines' => $count > 0
            ]);
        }
    }

    public function readMachinesByNumber()
    {
        $number = base64_decode($this->input->get('number', true));

        $data = $this->db
            ->select('machine_id')
            ->from('output_production_press')
            ->where('number', $number)
            ->where('item_fg_id IS NOT NULL')
            ->where('item_fg_id = ""')
            ->order_by('machine_id', 'ASC')
            ->get()
            ->result_array();

        $machines = array_column($data, 'machine_id');

        echo json_encode($machines);
    }

    public function createv1()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $this->db->trans_begin();

            $dataFinal = array(
                "item_fg_id" => $post['item_fg_id'],
                "machine_id" => $post['machine_id'],
                "mold_id" => $post['mold_id'],
                "planning_qty" => $post['planning_qty'],
                "qty_ok" => $post['qty_ok'],
                "qty_ng" => $post['qty_ng'],
                "qty_ng_mold" => $post['qty_ng_mold'],
                "workorder" => !empty($post['workorder']) ? $post['workorder'] : null,
                "actual_cavity" => $post['actual_cavity'],
                "operator" => $post['operator'],
                "pic" => $post['pic'],
                "actual_curing_time" => $post['actual_curing_time'],
                "shift_hour" => $post['shift_hour'],
                "actual_shoot" => $post['actual_shoot'],
                "total_compound_used" => $post['total_compound_used'],
                "waste" => $post['waste'],
            );

            if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                $post['workorder'] = null;
            }

            if(!empty($post['item_fg_id']) && !empty($post['machine_id']) && !empty($post['mold_id'])) {
                $machine_id = $post['machine_id'];
                $item_fg_id = $post['item_fg_id'];
                $mold_id    = $post['mold_id'];
                $actual_cavity = $post['actual_cavity'];

                $dataFinalMold = [
                    "cavity_actual" => $post['actual_cavity'],
                ];

                $mold = $this->crud->read("molds", [], ["id" => $post['mold_id']]);
                if($mold && $mold->cavity_actual != $actual_cavity) {

                    if(!empty($post['mold_id']) || $post['mold_id'] !== '' || $post['mold_id'] !== 'null') {
                        $this->crud->update("molds", ["id" => $post['mold_id']], $dataFinalMold);
                    }

                    $checkMachine = $this->db->get_where('machines', ['id' => $machine_id])->row();
                    $checkItemFg = $this->db->get_where('item_fg', ['id' => $item_fg_id])->row();
                    $checkMold = $this->db->get_where('molds', ['id' => $mold_id])->row();

                    $dataItem = $this->crud->query("
                        SELECT DISTINCT
                            a.item_fg_id,
                            d.number AS item_fg_number,
                            d.name AS item_fg_name,
                            a.machine_id,
                            b.number AS machine_number,
                            a.cycle_time,
                            a.productcivity,
                            c.cavity_actual,
                            a.shift,
                            a.shift_hour,
                            d.item_family_number,
                            d.mpq,
                            c.id AS mold_id
                        FROM menu_loadings a 
                        JOIN machines b ON a.machine_id = b.id
                        JOIN item_fg d ON a.item_fg_id = d.id
                        JOIN molds c ON a.mold_id = c.id
                        WHERE a.machine_id = '$machine_id'
                        AND a.item_fg_id = '$item_fg_id'
                        AND a.mold_id = '$mold_id'
                    ");

                    if (empty($dataItem)) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            "theme" => "error",
                            "title" => "Invalid Combination",
                            "message" => "Menu Loading data not found for the selected Machine No. $checkMachine->number, Product No. $checkItemFg->number , and Mold ID $checkMold->id"
                        ]);
                        return;
                    }

                    $production_capacity = $this->crud->read("production_capacities", [], [
                        "machine_id" => $machine_id,
                        "item_fg_id" => $item_fg_id,
                    ]);

                    if(empty($production_capacity)) {
                        $this->db->trans_rollback();
                        echo json_encode([
                            "theme" => "error",
                            "title" => "Invalid Combination",
                            "message" => "Production Capacity data not found for the selected Machine No. $checkMachine->number, Product No. $checkItemFg->number , and Mold ID $checkMold->id"
                        ]);
                        return;
                    }

                    $capacity_hour = ceil((3600 / $dataItem[0]->cycle_time) * $actual_cavity * ($dataItem[0]->productcivity / 100));
                    $capacity_shift = ceil($capacity_hour * $dataItem[0]->shift_hour);
                    $capacity_day = ceil($capacity_shift * $dataItem[0]->shift);

                    $dataFinalCapacity = [
                        "capacity_hour" => $capacity_hour,
                        "capacity_shift" => $capacity_shift,
                        "capacity_day" => $capacity_day,
                    ];

                    $this->crud->update("production_capacities", [
                        "machine_id" => $machine_id,
                        "item_fg_id" => $item_fg_id,
                    ], $dataFinalCapacity);
                }
            }

            
            if (@$post['id'] != "") {

                // if(!empty($post['mold_id']) || $post['mold_id'] !== '' || $post['mold_id'] !== 'null') {
                //     $this->crud->update("molds", ["id" => $post['mold_id']], $dataFinalMold);
                // }

                $send = $this->crud->update('output_production_press', ["id" => $post['id']], $dataFinal);
            } else {
                // $checkOutputPress = $this->crud->read('output_production_press', [], [
                //     "period"     => $post['period'],
                //     // "trans_date" => $post['trans_date'],
                //     "wp"         => $post['wp'],
                //     "shift"      => $post['shift'],
                //     "machine_id" => $post['machine_id'],
                //     "item_fg_id" => $post['item_fg_id'],
                //     "workorder"  => $post['workorder'],
                // ]);

                // $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
                // $machine = $this->crud->read("machines", [], ["id" => $post['machine_id']]);

                // if (!empty($checkOutputPress)) {
                //     echo json_encode(array(
                //         "title"   => "Duplicate Data",
                //         "message" => "Duplicate Data for Product {$item_fg->number} on Machine {$machine->number} (Period: {$post['period']}, WP: {$post['wp']}, Shift: {$post['shift']}, Workorder: {$post['workorder']}).",
                //         "theme"   => "error"
                //     ));
                //     exit;
                // }

                $send = $this->crud->create('output_production_press', $post);

                // if(!empty($post['mold_id']) || $post['mold_id'] !== '' || $post['mold_id'] !== 'null') {
                //     $this->crud->update("molds", ["id" => $post['mold_id']], $dataFinalMold);
                // }
            }


            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode([
                    "title" => "Error",
                    "message" => "Failed to be create or update data",
                    "theme" => "error"
                ]);
                return;
            }

            $this->db->trans_commit();

            echo json_encode([
                "title" => "Success",
                "message" => "Data saved successfully",
                "theme" => "success"
            ]);

            // echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot process your request.");
        }

        try {

            $items = $this->input->post('items');
            $errors = [];
            $success_count = 0;

            $this->db->trans_begin();

            foreach ($items as $post) {
                $this->debugOutputPress('START PROCESS', $post);

                $dataFinal = [
                    "item_fg_id" => $post['item_fg_id'],
                    "machine_id" => $post['machine_id'],
                    "mold_id" => $post['mold_id'],
                    "planning_qty" => $post['planning_qty'],
                    "qty_ok" => $post['qty_ok'],
                    "qty_ng" => $post['qty_ng'],
                    "qty_ng_mold" => $post['qty_ng_mold'],
                    "workorder" => !empty($post['workorder']) ? $post['workorder'] : null,
                    "actual_cavity" => $post['actual_cavity'],
                    "operator" => $post['operator'],
                    "pic" => $post['pic'],
                    "actual_curing_time" => $post['actual_curing_time'],
                    "shift_hour" => $post['shift_hour'],
                    "actual_shoot" => $post['actual_shoot'],
                    "total_compound_used" => $post['total_compound_used'],
                    "waste" => $post['waste'],
                ];

                if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                    $post['workorder'] = null;
                }

                if (!empty($post['item_fg_id']) && !empty($post['machine_id']) && !empty($post['mold_id'])) {

                    $machine_id = $post['machine_id'];
                    $item_fg_id = $post['item_fg_id'];
                    $mold_id    = $post['mold_id'];
                    $actual_cavity = $post['actual_cavity'];

                    $mold = $this->crud->read("molds", [], ["id" => $mold_id]);

                    if ($mold && $mold->cavity_actual != $actual_cavity) {

                        // update cavity molds
                        $this->crud->update("molds", ["id" => $mold_id], [
                            "cavity_actual" => $actual_cavity
                        ]);

                        $this->debugOutputPress('UPDATE MOLD', $post);

                        $checkMachine = $this->db->get_where('machines', ['id' => $machine_id])->row();
                        $checkItemFg = $this->db->get_where('item_fg', ['id' => $item_fg_id])->row();
                        $checkMold = $this->db->get_where('molds', ['id' => $mold_id])->row();

                        if (!$checkMachine || !$checkItemFg || !$checkMold) {
                            $this->debugOutputPress('MASTER DATA NOT FOUND', $post);

                            if (!$checkMachine) {
                                $errors[] = "Machine not found. ID : {$machine_id}";
                            }
                            if (!$checkItemFg) {
                                $errors[] = "Product not found. ID : {$item_fg_id}";
                            }
                            if (!$checkMold) {
                                $errors[] = "Mold not found. ID : {$mold_id}";
                            }

                            continue;
                        }

                        // cek menu loading
                        $dataItem = $this->crud->query("
                            SELECT DISTINCT
                                a.item_fg_id,
                                d.number AS item_fg_number,
                                d.name AS item_fg_name,
                                a.machine_id,
                                b.number AS machine_number,
                                a.cycle_time,
                                a.productcivity,
                                c.cavity_actual,
                                a.shift,
                                a.shift_hour,
                                d.item_family_number,
                                d.mpq,
                                c.id AS mold_id
                            FROM menu_loadings a 
                            JOIN machines b ON a.machine_id = b.id
                            JOIN item_fg d ON a.item_fg_id = d.id
                            JOIN molds c ON a.mold_id = c.id
                            WHERE a.machine_id = '$machine_id'
                            AND a.item_fg_id = '$item_fg_id'
                            AND a.mold_id = '$mold_id'
                        ");

                        $this->debugOutputPress('READ MENU LOADING', $post);

                        if (empty($dataItem)) {
                            $errors[] = "Menu Loading data not found for Machine No. $checkMachine->number, Product No. $checkItemFg->number and Mold ID $checkMold->id";
                            $this->debugOutputPress('MENU LOADING NOT FOUND', $post);
                            continue;
                        }

                        $production_capacity = $this->crud->read("production_capacities", [], [
                            "machine_id" => $machine_id,
                            "item_fg_id" => $item_fg_id,
                        ]);

                        $this->debugOutputPress('READ PRODUCTION CAPACITY', $post);

                        if(empty($production_capacity)) {
                            $errors[] = "Production Capacity data not found for Machine No. $checkMachine->number, Product No. $checkItemFg->number , and Mold ID $checkMold->id";
                            $this->debugOutputPress('PRODUCTION CAPACITY NOT FOUND', $post);
                            continue;
                        }

                        // hitung kapasitas
                        $cycle = $dataItem[0]->cycle_time;
                        $productivity = $dataItem[0]->productcivity;
                        $shift_hour = $dataItem[0]->shift_hour;
                        $shift = $dataItem[0]->shift;

                        $capacity_hour = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
                        $capacity_shift = ceil($capacity_hour * $shift_hour);
                        $capacity_day = ceil($capacity_shift * $shift);

                        $this->crud->update("production_capacities", [
                            "machine_id" => $machine_id,
                            "item_fg_id" => $item_fg_id,
                        ], [
                            "capacity_hour" => $capacity_hour,
                            "capacity_shift" => $capacity_shift,
                            "capacity_day" => $capacity_day,
                        ]);

                        $this->debugOutputPress('UPDATE PRODUCTION CAPACITY', $post);
                    }
                }

                if (!empty($post['id'])) {
                    // UPDATE MODE
                    $result = $this->crud->update(
                        'output_production_press',
                        ["id" => $post['id']],
                        $dataFinal
                    );
                } else {
                    // INSERT MODE
                    $result = $this->crud->create('output_production_press', $post);
                }

                $this->debugOutputPress(
                    empty($post['id']) ? 'INSERT OUTPUT PRESS' : 'UPDATE OUTPUT PRESS',
                    $post
                );

                if ($result) {
                    $success_count++;
                } else {
                    $errors[] = "Failed saving output press for Machine ID {$post['machine_id']}";
                }
            }

            if ($this->db->trans_status() === FALSE || !empty($errors)) {
                $this->debugOutputPress('ROLLBACK');
                $this->db->trans_rollback();
                echo json_encode([
                    "title" => "Failed to Save",
                    "message" => implode("\n", array_unique($errors)),
                    "theme" => "error"
                ]);
                return;
            }
            
            $this->debugOutputPress('COMMIT');
            $this->db->trans_commit();

            echo json_encode([
                "title" => "Success",
                "message" => "Data saved successfully",
                "theme" => "success"
            ]);

        } catch (Throwable $e) {

            log_message('error', json_encode([
                'step' => 'EXCEPTION',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]));

            throw $e; // supaya error tetap muncul seperti biasa
        }
    }

    private function debugOutputPress($step, $post = [])
    {
        $dbError = $this->db->error();

        log_message('error', json_encode([
            'step'         => $step,
            'number'       => $post['number'] ?? null,
            'machine_id'   => $post['machine_id'] ?? null,
            'item_fg_id'   => $post['item_fg_id'] ?? null,
            'mold_id'      => $post['mold_id'] ?? null,
            'workorder'    => $post['workorder'] ?? null,
            'trans_status' => $this->db->trans_status(),
            'db_error'     => $dbError,
            'time'         => date('Y-m-d H:i:s')
        ]));
    }

    public function delete()
    {
        $id = $this->input->post('id');

        $press = $this->db
            ->where('id', $id)
            ->get('output_production_press')
            ->row();

        if (!$press) {
            echo json_encode([
                'theme' => 'error',
                'message' => 'Data Not Found'
            ]);
            return;
        }

        $number    = $press->number ?? null;
        $workorder = $press->workorder ?? null;

        $this->db->from('output_production_press_detail');
        $this->db->where('number_output', $number);
        $this->db->where('workorder', $workorder);
        $this->db->where('status', 1);
        $count = $this->db->count_all_results();

        if ($count > 0) {
            echo json_encode([
                'theme' => 'error',
                'message' => 'Cannot delete data with a label that is already in use'
            ]);
            return;
        }

        $this->db->trans_begin();

        $this->crud->delete('output_production_press_detail', [
            'number_output' => $number,
            'workorder' => $workorder
        ]);

        $this->crud->delete('output_production_press', [
            'id' => $id
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            echo json_encode([
                'theme' => 'error',
                'message' => 'Delete failed'
            ]);
        } else {
            $this->db->trans_commit();

            echo json_encode([
                'theme' => 'success',
                'message' => 'Data deleted successfully'
            ]);
        }
    }

    public function deleteAll()
    {
        $number = $this->input->post('number');

        $press = $this->db
            ->where('number', $number)
            ->get('output_production_press')
            ->row();

        if (!$press) {
            echo json_encode([
                'theme' => 'error',
                'message' => 'Data Not Found'
            ]);
            return;
        }

        $number    = $press->number ?? null;

        $this->db->from('output_production_press_detail');
        $this->db->where('number_output', $number);
        $this->db->where('status', 1);
        $count = $this->db->count_all_results();

        if ($count > 0) {
            echo json_encode([
                'theme' => 'error',
                'message' => 'Cannot delete data with a label that is already in use'
            ]);
            return;
        }

        $this->db->trans_begin();

        $this->crud->delete('output_production_press_detail', [
            'number_output' => $number,
        ]);

        $this->crud->delete('output_production_press', [
            'number' => $number
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            echo json_encode([
                'theme' => 'error',
                'message' => 'Delete failed'
            ]);
        } else {
            $this->db->trans_commit();

            echo json_encode([
                'theme' => 'success',
                'message' => 'Data deleted successfully'
            ]);
        }
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

                // 'standard_curing_time' => $data->val($i, 13),
                'actual_curing_time' => $data->val($i, 13),
                'shift_hour' => $data->val($i, 14),
                // 'target_shoot' => $data->val($i, 16),
                'actual_shoot' => $data->val($i, 15),
                'total_compound_used' => $data->val($i, 16),
                'waste' => $data->val($i, 17),
                // 'mold_cleaning' => $data->val($i, 18),
                // 'trial' => $data->val($i, 19),
                // 'mold_changing' => $data->val($i, 20),
                // 'machine_repair' => $data->val($i, 21),
                // 'mold_repair' => $data->val($i, 22),
                // 'others' => $data->val($i, 23),

                'operator' => $data->val($i, 18),
                'pic' => $data->val($i, 19),
                // 'remarks' => $data->val($i, 26),
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
    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $raw = file_get_contents("php://input");
    //         $postData = json_decode($raw, true);

    //         $data_list = $postData['data'];
            
    //         $total_expected = count($data_list);
    //         $processed_count = 0;

    //         $this->db->trans_begin();
    //         $results = [];

    //         $groupNumbers = [];
    //         foreach ($data_list as $index => $data) {
    //             $processed_count++;

    //             if (empty($data['period'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Period is required"
    //                 ];
    //                 continue;
    //             }
    //             if (!preg_match('/^\d{6}$/', $data['period'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Period format must be YYYYMM"
    //                 ];
    //                 continue;
    //             }

    //             if (empty($data['trans_date'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Production Date is required"
    //                 ];
    //                 continue;
    //             }
    //             if (!strtotime($data['trans_date'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Production Date is invalid format"
    //                 ];
    //                 continue;
    //             }
    //             if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $data['trans_date'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Production Date must be in format YYYY-MM-DD"
    //                 ];
    //                 continue;
    //             }

    //             if (empty($data['wp'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "WP No is required"
    //                 ];
    //                 continue;
    //             }

    //             if (empty($data['shift'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Shift is required"
    //                 ];
    //                 continue;
    //             }

    //             if (!in_array($data['shift'], [1, 2, 3])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Shift must be 1, 2, or 3"
    //                 ];
    //                 continue;
    //             }

    //             if (empty($data['machine_id'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Machine No is required"
    //                 ];
    //                 continue;
    //             }

    //             if (empty($data['item_fg_id'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Product No is required"
    //                 ];
    //                 continue;
    //             }

    //             if (empty($data['workorder'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Work Order is required"
    //                 ];
    //                 continue;
    //             }

    //             if ($data['qty_ok'] === "" || !is_numeric($data['qty_ok'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Qty OK must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             if ($data['qty_ng'] === "" || !is_numeric($data['qty_ng'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Qty NG must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             if ($data['qty_ng_mold'] === "" || !is_numeric($data['qty_ng_mold'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Qty NG Mold must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             if ($data['actual_cavity'] === "" || !is_numeric($data['actual_cavity'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Actual Cavity must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             // if ($data['standard_curing_time'] === "" || !is_numeric($data['standard_curing_time'])) {
    //             //     $results[] = [
    //             //         "status"  => "failed",
    //             //         "item"    => "Line " . ($index + 1),
    //             //         "message" => "Standard Curing Time must be numeric and not empty"
    //             //     ];
    //             //     continue;
    //             // }
    //             if ($data['actual_curing_time'] === "" || !is_numeric($data['actual_curing_time'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Actual Curing Time must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             if ($data['shift_hour'] === "" || !is_numeric($data['shift_hour'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Hour/Shift must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             // if ($data['target_shoot'] === "" || !is_numeric($data['target_shoot'])) {
    //             //     $results[] = [
    //             //         "status"  => "failed",
    //             //         "item"    => "Line " . ($index + 1),
    //             //         "message" => "Target Shoot must be numeric and not empty"
    //             //     ];
    //             //     continue;
    //             // }
    //             if ($data['actual_shoot'] === "" || !is_numeric($data['actual_shoot'])) {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Actual Shoot must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }
    //             if ($data['total_compound_used'] === "") {
    //                 $results[] = [
    //                     "status"  => "failed",
    //                     "item"    => "Line " . ($index + 1),
    //                     "message" => "Total Compound Used must be numeric and not empty"
    //                 ];
    //                 continue;
    //             }

    //             // $data['process_id'] = 'PC006'; // Pressing process
    //             // $wp = $data['wp'];

    //             // Validasi qty tidak boleh kosong atau nol
    //             // if (!isset($data['qty']) || $data['qty'] == "" || $data['qty'] == null) {
    //             //     $results[] = [
    //             //         "status" => "failed",
    //             //         "item" => "Line " . ($index + 1),
    //             //         "message" => "Quantity must be greater than 0"
    //             //     ];
    //             //     continue;
    //             // }

    //             $groupKey = $data['period'] . '|' . $data['trans_date'] . '|' . $data['wp'] . '|' . $data['shift'];

    //             if (!isset($groupNumbers[$groupKey])) {
    //                 $groupNumbers[$groupKey] = $this->autonumber_excel($data['trans_date']);
    //             }

    //             $groupNumber = $groupNumbers[$groupKey];

    //             $machine = $this->crud->read('machines', [], ["number" => $data['machine_id']]);
    //             if (empty($machine)) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => "Line " . ($index + 1),
    //                     "message" => "Machine No. " . $data['machine_id'] . " Not Found"
    //                 ];
    //                 continue;
    //             }

    //             $checkMachineProdSchPress = $this->crud->read('production_schedule_press', [], ["machine_id" => $machine->id]);

    //             if (empty($checkMachineProdSchPress)) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => "Line " . ($index + 1),
    //                     "message" => "Machine No. " . $data['machine_id'] . " not found in Production Schedule Press"
    //                 ];
    //                 continue;
    //             }

    //             $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
    //             if (empty($item_fg)) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => "Line " . ($index + 1),
    //                     "message" => "Product No. " . $data['item_fg_id'] . " Not Found"
    //                 ];
    //                 continue;
    //             }

    //             $checkItemProdSchPress = $this->crud->read('production_schedule_press', [], [
    //                 "machine_id" => $machine->id,
    //                 "item_fg_id" => $item_fg->id
    //             ]);

    //             if (empty($checkItemProdSchPress)) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => "Line " . ($index + 1),
    //                     "message" => "Product No. " . $data['item_fg_id'] . " not found in Production Schedule Press for Machine " . $data['machine_id']
    //                 ];
    //                 continue;
    //             }

    //             $checkWPProdSchPress = $this->crud->read('production_schedule_press', [], [
    //                 "wp" => $data['wp'],
    //                 "machine_id" => $machine->id,
    //                 "item_fg_id" => $item_fg->id,
    //             ]);

    //             if (empty($checkWPProdSchPress)) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => "Line " . ($index + 1),
    //                     "message" => "WP No. {$data['wp']} not found in Production Schedule Press ". "for Product No. {$item_fg->number} on Machine {$machine->number}."
    //                 ];
    //                 continue;
    //             }

    //             $checkWOProdSchPress = $this->crud->read('production_schedule_press', [], [
    //                 "machine_id" => $machine->id,
    //                 "item_fg_id" => $item_fg->id,
    //                 "workorder" => $data['workorder'],
    //             ]);

    //             if (empty($checkWOProdSchPress)) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => "Line " . ($index + 1),
    //                     "message" => "Work Order No. {$data['workorder']} not found in Production Schedule Press ". "for Product No. {$item_fg->number} on Machine {$machine->number}."
    //                 ];
    //                 continue;
    //             }

    //             $planning_qty = @$checkWOProdSchPress->qty;

    //             $checkData = $this->crud->read('output_production_press', [], [
    //                 "period"     => $data['period'],
    //                 "trans_date" => $data['trans_date'],
    //                 "wp"         => $data['wp'],
    //                 "shift"      => $data['shift'],
    //                 "machine_id" => $machine->id,
    //                 "item_fg_id" => $item_fg->id,
    //                 "mold_id"    => $checkWOProdSchPress->mold_id,
    //                 "workorder"  => $data['workorder'],
    //             ]);

    //             // if (!empty($checkData)) {
    //             //     $results[] = [
    //             //         "status" => "failed",
    //             //         "item" => "Line " . ($index + 1),
    //             //         "message" => "Duplicate Data: Period " . $data['period'] . 
    //             //                     ", Product No. " . $data['item_fg_id'] . 
    //             //                     ", Machine No. " . $data['machine_id'] . 
    //             //                     ", WP No. " . $wp . 
    //             //                     ", Trans Date " . $data['trans_date']
    //             //     ];
    //             //     continue;
    //             // }

    //             // // Generate workorder
    //             // $workorder = $data['workorder'];

    //             // // Generate month dan year dari trans_date
    //             // $dateObj = DateTime::createFromFormat('Y-m-d', $data['trans_date']);
    //             // $year = $dateObj->format('Y');
    //             // $month = $dateObj->format('m');

    //             $dataFinal = array(
    //                 "period"                => $data['period'],
    //                 "number"                => $groupNumber,
    //                 "trans_date"            => $data['trans_date'],
    //                 "wp"                    => $data['wp'],
    //                 "shift"                 => $data['shift'],
    //                 "machine_id"            => $machine->id,
    //                 "item_fg_id"            => $item_fg->id,
    //                 "mold_id"               => $checkWOProdSchPress->mold_id,
    //                 "workorder"             => $data['workorder'],
    //                 "planning_qty"          => $planning_qty,
    //                 "qty_ok"                => $data['qty_ok'],
    //                 "qty_ng"                => $data['qty_ng'],
    //                 "qty_ng_mold"           => $data['qty_ng_mold'],
    //                 "actual_cavity"         => $data['actual_cavity'],

    //                 // "standard_curing_time"  => $data['standard_curing_time'],
    //                 "actual_curing_time"    => $data['actual_curing_time'],
    //                 "shift_hour"            => $data['shift_hour'],
    //                 // "target_shoot"          => $data['target_shoot'],
    //                 "actual_shoot"          => $data['actual_shoot'],
    //                 "total_compound_used"   => $data['total_compound_used'],
    //                 "waste"                 => $data['waste'],
    //                 // "mold_cleaning"         => $data['mold_cleaning'],
    //                 // "trial"                 => $data['trial'],
    //                 // "mold_changing"         => $data['mold_changing'],
    //                 // "machine_repair"        => $data['machine_repair'],
    //                 // "mold_repair"           => $data['mold_repair'],
    //                 // "others"                => $data['others'],

    //                 "operator"              => $data['operator'],
    //                 "pic"                   => $data['pic'],
    //                 // "remarks"               => $data['remarks'],
    //             );

    //             try {
    //                 if (!empty($checkData)) {
    //                     // Update
    //                     $this->db->update('output_production_press', [
    //                         "qty_ok"                => $data['qty_ok'],
    //                         "qty_ng"                => $data['qty_ng'],
    //                         "qty_ng_mold"           => $data['qty_ng_mold'],
    //                         "actual_cavity"         => $data['actual_cavity'],
    //                         // "standard_curing_time"  => $data['standard_curing_time'],
    //                         "actual_curing_time"    => $data['actual_curing_time'],
    //                         "shift_hour"            => $data['shift_hour'],
    //                         // "target_shoot"          => $data['target_shoot'],
    //                         "actual_shoot"          => $data['actual_shoot'],
    //                         "total_compound_used"   => $data['total_compound_used'],
    //                         "waste"                 => $data['waste'],
    //                         // "mold_cleaning"         => $data['mold_cleaning'],
    //                         // "trial"                 => $data['trial'],
    //                         // "mold_changing"         => $data['mold_changing'],
    //                         // "machine_repair"        => $data['machine_repair'],
    //                         // "mold_repair"           => $data['mold_repair'],
    //                         // "others"                => $data['others'],
    //                         "operator"              => $data['operator'],
    //                         "pic"                   => $data['pic'],
    //                         // "remarks"               => $data['remarks'],
    //                     ], [
    //                         "period"     => $data['period'],
    //                         "trans_date" => $data['trans_date'],
    //                         "wp"         => $data['wp'],
    //                         "shift"      => $data['shift'],
    //                         "machine_id" => $machine->id,
    //                         "item_fg_id" => $item_fg->id,
    //                         "mold_id"    => $checkWOProdSchPress->mold_id,
    //                         "workorder"  => $data['workorder'],
    //                     ]);

    //                     $status = "update";
    //                 } else {
    //                     // Insert
    //                     $this->crud->create('output_production_press', $dataFinal);

    //                     $status = "insert";
    //                 }

    //                 if(!empty($checkWOProdSchPress->mold_id) || $checkWOProdSchPress->mold_id !== '' || $checkWOProdSchPress->mold_id !== 'null') {
    //                     $dataFinalMold = [
    //                         "cavity_actual" => $data['actual_cavity'],
    //                     ];
    //                     $this->crud->update("molds", ["id" => $checkWOProdSchPress->mold_id], $dataFinalMold);
    //                 }

    //                 $res_item = ($status === "insert" ? "Create" : "Update");
    //                 $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "WP {$data['wp']} Shift {$data['shift']} for Product {$item_fg->number} on Machine {$machine->number} updated");

    //                 $results[] = [
    //                     "status" => "success",
    //                     "item" => $res_item,
    //                     "message" => $res_msg
    //                 ];
    //             } catch (Exception $e) {
    //                 $results[] = [
    //                     "status" => "failed",
    //                     "item" => $item_fg->name,
    //                     "message" => $e->getMessage()
    //                 ];
    //                 continue;
    //             }
    //         }

    //         $failed = array_filter($results, fn($r) => $r['status'] === 'failed');
    //         $hasDbError = ($this->db->trans_status() === FALSE);

    //         if (count($failed) > 0 || $hasDbError) {
    //             $filePath = 'failed/output_production_press.xls';

    //             $html = '
    //             <html>
    //             <head>
    //                 <meta charset="UTF-8">
    //             </head>
    //             <body>
    //                 <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; font-family: Arial, sans-serif;">
    //                     <thead style="background-color: #f2f2f2;">
    //                         <tr>
    //                             <th style="width: 40px; text-align: center;">No</th>
    //                             <th style="width: 100px; text-align: left;">Line</th>
    //                             <th style="width: 450px; text-align: left;">Message</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>
    //             ';

    //             $no = 1;
    //             foreach ($failed as $row) {
    //                 $line = htmlspecialchars($row['item']);
    //                 $msg  = htmlspecialchars($row['message']);
    //                 $html .= "
    //                     <tr>
    //                         <td style='text-align: center;'>{$no}</td>
    //                         <td style='text-align: left;'>{$line}</td>
    //                         <td style='text-align: left;'>{$msg}</td>
    //                     </tr>";
    //                 $no++;
    //             }

    //             $html .= '
    //                     </tbody>
    //                 </table>
    //             </body>
    //             </html>';

    //             file_put_contents($filePath, $html);

    //             echo json_encode([
    //                 "theme" => "error",
    //                 "title" => "Upload Failed",
    //                 "message" => "Data failed to save",
    //                 "results" => $results,
    //                 "total_expected" => $total_expected,
    //                 "processed_count" => $processed_count,
    //                 "stopped_at" => $index + 1
    //             ]);
    //         } else {
    //             @unlink('failed/output_production_press.xls');

    //             $this->db->trans_commit();
    //             echo json_encode([
    //                 "theme" => "success",
    //                 "title" => "Upload Successfully",
    //                 "message" => "Data uploaded successfully",
    //                 "results" => $results,
    //                 "total_expected" => $total_expected,
    //                 "processed_count" => $processed_count,
    //                 "stopped_at" => $index + 1
    //             ]);
    //         }

    //     }
    // }

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
                if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $data['trans_date'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Production Date must be in format YYYY-MM-DD"
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
                // if ($data['standard_curing_time'] === "" || !is_numeric($data['standard_curing_time'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "Standard Curing Time must be numeric and not empty"
                //     ];
                //     continue;
                // }
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
                // if ($data['target_shoot'] === "" || !is_numeric($data['target_shoot'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "Target Shoot must be numeric and not empty"
                //     ];
                //     continue;
                // }
                if ($data['actual_shoot'] === "" || !is_numeric($data['actual_shoot'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Actual Shoot must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['total_compound_used'] === "") {
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
                    $groupNumbers[$groupKey] = $this->autonumber_excel($data['trans_date']);
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

                if (!empty($item_fg->id) && !empty($machine->id) && !empty($checkWOProdSchPress->mold_id)) {

                    $machine_id = $machine->id;
                    $item_fg_id = $item_fg->id;
                    $mold_id    = $checkWOProdSchPress->mold_id;
                    $actual_cavity = $data['actual_cavity'];

                    $mold = $this->crud->read("molds", [], ["id" => $mold_id]);

                    if ($mold && $mold->cavity_actual != $actual_cavity) {

                        // update cavity molds
                        $this->crud->update("molds", ["id" => $mold_id], [
                            "cavity_actual" => $actual_cavity
                        ]);

                        // cek menu loading
                        $dataItem = $this->crud->query("
                            SELECT DISTINCT
                                a.item_fg_id,
                                d.number AS item_fg_number,
                                d.name AS item_fg_name,
                                a.machine_id,
                                b.number AS machine_number,
                                a.cycle_time,
                                a.productcivity,
                                c.cavity_actual,
                                a.shift,
                                a.shift_hour,
                                d.item_family_number,
                                d.mpq,
                                c.id AS mold_id
                            FROM menu_loadings a 
                            JOIN machines b ON a.machine_id = b.id
                            JOIN item_fg d ON a.item_fg_id = d.id
                            JOIN molds c ON a.mold_id = c.id
                            WHERE a.machine_id = '$machine_id'
                            AND a.item_fg_id = '$item_fg_id'
                            AND a.mold_id = '$mold_id'
                        ");

                        if (empty($dataItem)) {
                            $results[] = [
                                "status" => "failed",
                                "item" => "Line " . ($index + 1),
                                "message" => "Menu Loading data not found for Machine No. $machine->number, Product No. $item_fg->number and Mold ID $mold->id"
                            ];
                            continue;
                        }

                        $production_capacity = $this->crud->read("production_capacities", [], [
                            "machine_id" => $machine_id,
                            "item_fg_id" => $item_fg_id,
                        ]);

                        if(empty($production_capacity)) {
                            $results[] = [
                                "status" => "failed",
                                "item" => "Line " . ($index + 1),
                                "message" => "Production Capacity data not found for Machine No. $machine->number, Product No. $item_fg->number and Mold ID $mold->id"
                            ];
                            continue;
                        }

                        // hitung kapasitas
                        $cycle = $dataItem[0]->cycle_time;
                        $productivity = $dataItem[0]->productcivity;
                        $shift_hour = $dataItem[0]->shift_hour;
                        $shift = $dataItem[0]->shift;

                        $capacity_hour = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
                        $capacity_shift = ceil($capacity_hour * $shift_hour);
                        $capacity_day = ceil($capacity_shift * $shift);

                        $this->crud->update("production_capacities", [
                            "machine_id" => $machine_id,
                            "item_fg_id" => $item_fg_id,
                        ], [
                            "capacity_hour" => $capacity_hour,
                            "capacity_shift" => $capacity_shift,
                            "capacity_day" => $capacity_day,
                        ]);
                    }
                }

                $planning_qty = @$checkWOProdSchPress->qty;

                $checkData = $this->crud->read('output_production_press', [], [
                    "period"     => $data['period'],
                    "trans_date" => $data['trans_date'],
                    "wp"         => $data['wp'],
                    "shift"      => $data['shift'],
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                    "mold_id"    => $checkWOProdSchPress->mold_id,
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
                    "mold_id"               => $checkWOProdSchPress->mold_id,
                    "workorder"             => $data['workorder'],
                    "planning_qty"          => $planning_qty,
                    "qty_ok"                => $data['qty_ok'],
                    "qty_ng"                => $data['qty_ng'],
                    "qty_ng_mold"           => $data['qty_ng_mold'],
                    "actual_cavity"         => $data['actual_cavity'],

                    "actual_curing_time"    => $data['actual_curing_time'],
                    "shift_hour"            => $data['shift_hour'],
                    "actual_shoot"          => $data['actual_shoot'],
                    "total_compound_used"   => $data['total_compound_used'],
                    "waste"                 => $data['waste'],

                    "operator"              => $data['operator'],
                    "pic"                   => $data['pic'],
                );

                try {
                    if (!empty($checkData)) {
                        // Update
                        $this->db->update('output_production_press', [
                            "qty_ok"                => $data['qty_ok'],
                            "qty_ng"                => $data['qty_ng'],
                            "qty_ng_mold"           => $data['qty_ng_mold'],
                            "actual_cavity"         => $data['actual_cavity'],
                            "actual_curing_time"    => $data['actual_curing_time'],
                            "shift_hour"            => $data['shift_hour'],
                            "actual_shoot"          => $data['actual_shoot'],
                            "total_compound_used"   => $data['total_compound_used'],
                            "waste"                 => $data['waste'],
                            "operator"              => $data['operator'],
                            "pic"                   => $data['pic'],
                        ], [
                            "period"     => $data['period'],
                            "trans_date" => $data['trans_date'],
                            "wp"         => $data['wp'],
                            "shift"      => $data['shift'],
                            "machine_id" => $machine->id,
                            "item_fg_id" => $item_fg->id,
                            "mold_id"    => $checkWOProdSchPress->mold_id,
                            "workorder"  => $data['workorder'],
                        ]);

                        $status = "update";
                    } else {
                        // Insert
                        $this->crud->create('output_production_press', $dataFinal);

                        $status = "insert";
                    }

                    // if(!empty($checkWOProdSchPress->mold_id) || $checkWOProdSchPress->mold_id !== '' || $checkWOProdSchPress->mold_id !== 'null') {
                    //     $dataFinalMold = [
                    //         "cavity_actual" => $data['actual_cavity'],
                    //     ];
                    //     $this->crud->update("molds", ["id" => $checkWOProdSchPress->mold_id], $dataFinalMold);
                    // }

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

    // public function checkCustomers() 
    // {
    //     $item_fg_id = $this->input->get('item_fg_id');

    //     $this->db->select('ci.customer_id, c.number as customer_code, c.name as customer_name');
    //     $this->db->from('customer_items ci');
    //     $this->db->join('customers c', 'c.id = ci.customer_id', 'left');
    //     $this->db->where('ci.item_fg_id', $item_fg_id);
    //     $customers = $this->db->get()->result();

    //     echo json_encode([
    //         'count' => count($customers),
    //         'customers' => $customers
    //     ]);
    // }

    // public function checkExistingCustomer()
    // {
    //     $number    = $this->input->get('number');
    //     $workorder = $this->input->get('workorder');
    //     $shift     = $this->input->get('shift');

    //     $this->db->select('customer_code');
    //     $this->db->from('output_production_press');
    //     $this->db->where('number', $number);
    //     $this->db->where('workorder', $workorder);
    //     $this->db->where('shift', $shift);
    //     $row = $this->db->get()->row();

    //     echo json_encode([
    //         'exists' => !empty($row->customer_code),
    //         'customer_code' => $row->customer_code ?? null
    //     ]);
    // }

    // BY CUSTOMER
    // public function print_output_press()
    // {
    //     $number       = base64_decode($this->input->get('number'));
    //     $workorder    = base64_decode($this->input->get('workorder'));
    //     $item_fg_id   = base64_decode($this->input->get('item_fg_id'));
    //     $qty_ok       = intval(base64_decode($this->input->get('qty_ok')));
    //     $qty_label    = intval(base64_decode($this->input->get('qty_label')));
    //     $qty_packing  = intval(base64_decode($this->input->get('qty_packing')));
    //     $customer_code   = base64_decode($this->input->get('customer_code'));


    //     if (!$number || !$workorder || !$item_fg_id || $qty_ok <= 0 || $qty_packing <= 0) {
    //         show_error("Missing or invalid parameters", 400);
    //         return;
    //     }

    //     if (!empty($customer_code)) {
    //         $this->db->select('customer_code');
    //         $this->db->where('number', $number);
    //         $this->db->where('workorder', $workorder);
    //         $existing_cust = $this->db->get('output_production_press')->row();

    //         if (empty($existing_cust->customer_code)) {
    //             $this->db->where('number', $number);
    //             $this->db->where('workorder', $workorder);
    //             $this->db->update('output_production_press', [
    //                 'customer_code' => $customer_code
    //             ]);
    //         }
    //     }

    //     // Format workorder: WOP251103-047 => WOP251103047
    //     $workorder_clean = str_replace('-', '', $workorder);

    //     $this->db->where('number_output', $number);
    //     $this->db->where('workorder', $workorder);
    //     $this->db->where('item_fg_id', $item_fg_id);
    //     $existing = $this->db->get('output_production_press_detail')->result();

    //     $existing_total_qty = 0;
    //     foreach ($existing as $r) {
    //         $existing_total_qty += intval($r->qty_packing);
    //     }

    //     if ($existing_total_qty < $qty_ok) {

    //         // Hitung remaining yang masih harus dibuat
    //         $remaining = $qty_ok - $existing_total_qty;

    //         // Ambil last sequence
    //         $this->db->select_max('workorder_label');
    //         $this->db->like('workorder_label', $workorder_clean . '-', 'after');
    //         $last_label = $this->db->get('output_production_press_detail')->row()->workorder_label;

    //         $last_sequence = 0;
    //         if ($last_label) {
    //             $last_sequence = intval(substr($last_label, -3));
    //         }

    //         $sequence = $last_sequence + 1;

    //         for ($i = 1; $i <= $qty_label; $i++) {

    //             // label terakhir ambil sisa
    //             if ($i == $qty_label) {
    //                 $current_qty = $remaining;
    //             } else {
    //                 $current_qty = min($qty_packing, $remaining);
    //             }

    //             if ($current_qty <= 0) break;

    //             $workorder_label = $workorder_clean . '-' . sprintf("%03d", $sequence);

    //             $detail = [
    //                 'number_output'    => $number,
    //                 'workorder'        => $workorder,
    //                 'workorder_label'  => $workorder_label,
    //                 'item_fg_id'       => $item_fg_id,
    //                 'qty_packing'      => $current_qty,
    //                 'status'           => 0
    //             ];

    //             $this->crud->create('output_production_press_detail', $detail);

    //             $remaining -= $current_qty;
    //             $sequence++;

    //             if ($remaining <= 0) break;
    //         }
    //     }

    //     $this->db->select('
    //         fg.number AS item_fg_number, 
    //         fg.name AS item_fg_name, 
    //         fg.uom,

    //         opp.trans_date, 
    //         opp.shift, 
    //         opp.workorder, 
    //         opp.operator, 
    //         opp.qty_ok, 

    //         opd.workorder_label, 
    //         opd.qty_packing,

    //         cs.number as cust_code
    //     ');
    //     $this->db->from('output_production_press_detail opd');
    //     $this->db->join('output_production_press opp', 'opp.number = opd.number_output and opp.workorder = opd.workorder');

    //     // $this->db->join(
    //     //     "(SELECT item_fg_id, MIN(customer_id) as customer_id
    //     //     FROM customer_items 
    //     //     WHERE type_item = 'Original'
    //     //     GROUP BY item_fg_id
    //     //     ) ci",
    //     //     "ci.item_fg_id = opp.item_fg_id",
    //     //     "left"
    //     // );

    //     // $this->db->join('customers cs', 'cs.id = ci.customer_id', 'left');

    //     $this->db->join('customers cs', "cs.number = '{$customer_code}'", 'left');

    //     $this->db->join('item_fg fg', 'opp.item_fg_id = fg.id', 'left');
    //     $this->db->where('opd.number_output', $number);
    //     $this->db->where('opd.workorder', $workorder);
        
    //     if (!empty($item_fg_id)) {
    //         $this->db->where('opp.item_fg_id', $item_fg_id);
    //     }
        
    //     $this->db->group_by('opd.workorder_label');
        
    //     $output_press_packing_details = $this->db->get()->result();
        
    //     if (empty($output_press_packing_details)) {
    //         echo "<center><h3>Data not found</h3></center>";
    //         return;
    //     }
        
    //     $first_workorder_label = $output_press_packing_details[0]->workorder_label;
        
    //     foreach ($output_press_packing_details as $detail) {
    //         $this->createQrcode($detail->workorder_label, "assets/image/qrcode/");
    //     }
        
    //     $html = '<html>
    //                 <head>
    //                     <title>Label Packing - ' . $first_workorder_label . '</title>
    //                     <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
    //                     <style>
    //                         body { 
    //                             font-family: Arial, Helvetica, sans-serif; 
    //                             margin: 2; 
    //                         }

    //                         table { 
    //                             border-collapse: collapse; 
    //                             width: 48mm; 
    //                             height: 41mm; 
    //                             font-size: 10px;
    //                             border: 1px solid black; 
    //                             table-layout: fixed; 
    //                         }

    //                         th, td { 
    //                             border: 1px solid black; 
    //                             /*padding: 1.5px;*/
    //                             padding: 1px;
    //                             text-align: left; 
    //                         }

    //                         th { 
    //                             text-align: center; 
    //                             font-size: 5px; 
    //                             font-weight: bold; 
    //                         }

    //                         .header { 
    //                             text-align: center; 
    //                             font-size: 11px; 
    //                             font-weight: bold; 
    //                         }

    //                         .logo { 
    //                             text-align: center; 
    //                             width: 100%; 
    //                             padding-left: 3px; 
    //                             padding-right: 3px; 
    //                         }

    //                         .operator-sign, .qc-sign, .qr-code { 
    //                             font-size: 7px; 
    //                             text-align: center; 
    //                             vertical-align: bottom; 
    //                             font-weight: bold; 
    //                         }
    //                         .qc-sign { 
    //                             text-align: center; 
    //                             height: 10mm; 
    //                         }
    //                         .qr-code img { 
    //                             width: 40px; 
    //                             height: 40px; 
    //                             display: block; 
    //                             margin: 0 auto;
    //                         }
    //                         .serial-label { 
    //                             font-size: 5px; 
    //                             text-align: center; 
    //                             word-wrap: break-word; 
    //                             overflow: hidden; 
    //                             font-weight: bold; 
    //                         }
    //                         @page {
    //                                 size: 48mm 41mm;
    //                                 margin: 0;
    //                                 }
    //                         @media print {
    //                                 .printLabel {
    //                                     page-break-after: auto:
    //                                     width: 48mm;
    //                                     height: 41mm;
    //                                     display: block;
    //                                     padding: 0;
    //                                     margin: 0;
    //                                 }

    //                                 table {
    //                                     width: 100%;
    //                                     font-size: 6px;
    //                                     margin: 0;
    //                                     padding: 0;
    //                                 }

    //                                 body {
    //                                     margin: 0;
    //                                     padding: 0;
    //                                 }
    //                             }
    //                     </style>
    //                 </head>
    //             <body>';
        
    //     foreach ($output_press_packing_details as $detail) {
    //         $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($output_press_packing_details[0]->uom);
    //         $html .= '<div class="printLabel">
    //                     <table style="width: 48mm; height: 41mm;">
    //                     <tr>
    //                         <th class="logo" colspan="6" style="text-align: center;">
    //                             <img src="' . base_url('assets/image/bri_logo.png') . '" width="10" align="left"/>
    //                             <span class="header" style="font-size: 10px; height: 10px;">LABEL PACKING PRESS</span>
    //                         </th>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Cust Code:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $detail->cust_code . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Prod No:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $detail->item_fg_number . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Prod Name:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $detail->item_fg_name . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Prod Date/Shift:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . ' / '. $detail->shift .'</td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>WO No:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $detail->workorder . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>MP Press:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $detail->operator . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Mix Date:</b></td>
    //                         <td colspan="4" style="font-weight: bold;"></td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Comp Lot No:</b></td>
    //                         <td colspan="4" style="font-weight: bold;"></td>
    //                     </tr>
    //                     <tr>
    //                         <td colspan="2" style="width: 30%;"><b>Qty:</b></td>
    //                         <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
    //                     </tr>
    //                     <tr>
    //                         <th colspan="2" style="font-size: 6px !important;">MP Finishing | QA</th>
    //                         <th colspan="4" style="font-size: 6px !important;">QR Code</th>
    //                     </tr>
    //                     <tr>
    //                         <td class="operator-sign" colspan="2"></td>
    //                         <td class="qr-code" colspan="4">
    //                             <img src="' . base_url('assets/image/qrcode/' . $detail->workorder_label . '.png') . '"/>
    //                             <div class="serial-label">' . $detail->workorder_label . '</div>
    //                         </td>
    //                     </tr>
    //                 </table>
            
    //         </div>';
    //     } 
    
    //     $html .= '<script>window.print()</script>
    //             </body>
    //         </html>';
    
    //     die($html);

    // }


    public function print_output_press()
    {
        $number       = base64_decode($this->input->get('number'));
        $workorder    = base64_decode($this->input->get('workorder'));
        $item_fg_id   = base64_decode($this->input->get('item_fg_id'));
        $qty_ok       = intval(base64_decode($this->input->get('qty_ok')));
        $qty_label    = intval(base64_decode($this->input->get('qty_label')));
        $qty_packing  = intval(base64_decode($this->input->get('qty_packing')));

        if (!$number || !$workorder || !$item_fg_id || $qty_ok <= 0 || $qty_packing <= 0) {
            show_error("Missing or invalid parameters", 400);
            return;
        }

        // Format workorder: WOP251103-047 => WOP251103047
        $workorder_clean = str_replace('-', '', $workorder);

        $this->db->where('number_output', $number);
        $this->db->where('workorder', $workorder);
        $this->db->where('item_fg_id', $item_fg_id);
        $existing = $this->db->get('output_production_press_detail')->result();

        $existing_total_qty = 0;
        foreach ($existing as $r) {
            $existing_total_qty += intval($r->qty_packing);
        }

        if ($existing_total_qty < $qty_ok) {

            // Hitung remaining yang masih harus dibuat
            $remaining = $qty_ok - $existing_total_qty;

            // Ambil last sequence
            $this->db->select_max('workorder_label');
            $this->db->like('workorder_label', $workorder_clean . '-', 'after');
            $last_label = $this->db->get('output_production_press_detail')->row()->workorder_label;

            $last_sequence = 0;
            if ($last_label) {
                $last_sequence = intval(substr($last_label, -3));
            }

            $sequence = $last_sequence + 1;

            for ($i = 1; $i <= $qty_label; $i++) {

                // label terakhir ambil sisa
                if ($i == $qty_label) {
                    $current_qty = $remaining;
                } else {
                    $current_qty = min($qty_packing, $remaining);
                }

                if ($current_qty <= 0) break;

                $workorder_label = $workorder_clean . '-' . sprintf("%03d", $sequence);

                $detail = [
                    'number_output'    => $number,
                    'workorder'        => $workorder,
                    'workorder_label'  => $workorder_label,
                    'item_fg_id'       => $item_fg_id,
                    'qty_packing'      => $current_qty,
                    'status'           => 0
                ];

                $this->crud->create('output_production_press_detail', $detail);

                $remaining -= $current_qty;
                $sequence++;

                if ($remaining <= 0) break;
            }
        }

        $this->db->select('
            fg.number AS item_fg_number, 
            fg.name AS item_fg_name, 
            fg.uom,

            opp.trans_date, 
            opp.shift, 
            opp.workorder, 
            opp.operator, 
            opp.qty_ok, 

            opd.workorder_label, 
            opd.qty_packing
        ');
        $this->db->from('output_production_press_detail opd');
        $this->db->join('output_production_press opp', 'opp.number = opd.number_output and opp.workorder = opd.workorder');

        $this->db->join('item_fg fg', 'opp.item_fg_id = fg.id', 'left');
        $this->db->where('opd.number_output', $number);
        $this->db->where('opd.workorder', $workorder);
        
        if (!empty($item_fg_id)) {
            $this->db->where('opp.item_fg_id', $item_fg_id);
        }
        
        $this->db->group_by('opd.workorder_label');
        
        $output_press_packing_details = $this->db->get()->result();
        
        if (empty($output_press_packing_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_workorder_label = $output_press_packing_details[0]->workorder_label;
        
        foreach ($output_press_packing_details as $detail) {
            $this->createQrcode($detail->workorder_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_workorder_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }

                            table { 
                                border-collapse: collapse; 
                                width: 48mm; 
                                height: 41mm; 
                                font-size: 10px;
                                border: 1px solid black; 
                                table-layout: fixed; 
                            }

                            th, td { 
                                border: 1px solid black; 
                                /*padding: 1.5px;*/
                                padding: 1px;
                                text-align: left; 
                            }

                            th { 
                                text-align: center; 
                                font-size: 5px; 
                                font-weight: bold; 
                            }

                            .header { 
                                text-align: center; 
                                font-size: 11px; 
                                font-weight: bold; 
                            }

                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding-left: 3px; 
                                padding-right: 3px; 
                            }

                            .operator-sign, .qc-sign, .qr-code { 
                                font-size: 7px; 
                                text-align: center; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 10mm; 
                            }
                            .qr-code img { 
                                width: 40px; 
                                height: 40px; 
                                display: block; 
                                margin: 0 auto;
                            }
                            .serial-label { 
                                font-size: 5px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                    size: 48mm 41mm;
                                    margin: 0;
                                    }
                            @media print {
                                    .printLabel {
                                        page-break-after: auto:
                                        width: 48mm;
                                        height: 41mm;
                                        display: block;
                                        padding: 0;
                                        margin: 0;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 6px;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    body {
                                        margin: 0;
                                        padding: 0;
                                    }
                                }
                        </style>
                    </head>
                <body>';
        
        foreach ($output_press_packing_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($output_press_packing_details[0]->uom);
            $html .= '<div class="printLabel">
                        <table style="width: 48mm; height: 41mm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="10" align="left"/>
                                <span class="header" style="font-size: 10px; height: 10px;">LABEL PACKING PRESS</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->item_fg_number . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->item_fg_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Status:</b></td>
                            <td colspan="4" style="font-weight: bold;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date/Shift:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . ' / '. $detail->shift .'</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>WO No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->workorder . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>MP Press:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->operator . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Mix Date:</b></td>
                            <td colspan="4" style="font-weight: bold;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Comp Lot No:</b></td>
                            <td colspan="4" style="font-weight: bold;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <th colspan="2" style="font-size: 6px !important;">MP Finishing | QA</th>
                            <th colspan="4" style="font-size: 6px !important;">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2"></td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->workorder_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->workorder_label . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);

    }


    public function print_label_press() 
    {
        $number       = base64_decode($this->input->get('number'));
        $workorder    = base64_decode($this->input->get('workorder'));
        $item_fg_id   = base64_decode($this->input->get('item_fg_id'));
        $qty_ok       = intval(base64_decode($this->input->get('qty_ok')));
        $qty_label    = intval(base64_decode($this->input->get('qty_label')));
        $qty_packing  = intval(base64_decode($this->input->get('qty_packing')));

        if (!$number || !$workorder || !$item_fg_id || $qty_ok <= 0 || $qty_packing <= 0) {
            show_error("Missing or invalid parameters", 400);
            return;
        }

        $this->db->query("UPDATE output_production_press SET printed=1 WHERE `workorder` = '$workorder' AND `number` = '$number' AND item_fg_id = '$item_fg_id' ");

        // Format workorder: WOP251103-047 => WOP251103047
        $workorder_clean = str_replace('-', '', $workorder);

        $this->db->where('number_output', $number);
        $this->db->where('workorder', $workorder);
        $this->db->where('item_fg_id', $item_fg_id);
        $existing = $this->db->get('output_production_press_detail')->result();

        $existing_total_qty = 0;
        foreach ($existing as $r) {
            $existing_total_qty += intval($r->qty_packing);
        }

        if ($existing_total_qty < $qty_ok) {

            // Hitung remaining yang masih harus dibuat
            $remaining = $qty_ok - $existing_total_qty;

            // Ambil last sequence
            $this->db->select_max('workorder_label');
            $this->db->like('workorder_label', $workorder_clean . '-', 'after');
            $last_label = $this->db->get('output_production_press_detail')->row()->workorder_label;

            $last_sequence = 0;
            if ($last_label) {
                $last_sequence = intval(substr($last_label, -3));
            }

            $sequence = $last_sequence + 1;

            for ($i = 1; $i <= $qty_label; $i++) {

                // label terakhir ambil sisa
                if ($i == $qty_label) {
                    $current_qty = $remaining;
                } else {
                    $current_qty = min($qty_packing, $remaining);
                }

                if ($current_qty <= 0) break;

                $workorder_label = $workorder_clean . '-' . sprintf("%03d", $sequence);

                $detail = [
                    'number_output'    => $number,
                    'workorder'        => $workorder,
                    'workorder_label'  => $workorder_label,
                    'item_fg_id'       => $item_fg_id,
                    'qty_packing'      => $current_qty,
                    'status'           => 0
                ];

                $this->crud->create('output_production_press_detail', $detail);

                $remaining -= $current_qty;
                $sequence++;

                if ($remaining <= 0) break;
            }
        }

        $this->db->select('
            fg.number AS item_fg_number, 
            fg.name AS item_fg_name, 
            fg.uom,

            opp.trans_date, 
            opp.shift, 
            opp.workorder, 
            opp.operator, 
            opp.qty_ok, 

            opd.workorder_label, 
            opd.qty_packing
        ');
        $this->db->from('output_production_press_detail opd');
        $this->db->join('output_production_press opp', 'opp.number = opd.number_output and opp.workorder = opd.workorder');

        $this->db->join('item_fg fg', 'opp.item_fg_id = fg.id', 'left');
        $this->db->where('opd.number_output', $number);
        $this->db->where('opd.workorder', $workorder);
        
        if (!empty($item_fg_id)) {
            $this->db->where('opp.item_fg_id', $item_fg_id);
        }
        
        $this->db->group_by('opd.workorder_label');
        
        $output_press_packing_details = $this->db->get()->result();
        
        if (empty($output_press_packing_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_workorder_label = $output_press_packing_details[0]->workorder_label;

        foreach ($output_press_packing_details as $detail) {
            $this->createQrcode($detail->workorder_label, "assets/image/qrcode/");
        }

        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_workorder_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }
                            table { 
                                border-collapse: collapse; 
                                width: 7.5cm; 
                                height: 8cm; 
                                font-size: 11px; 
                                border: 2px solid black; 
                                table-layout: fixed; 
                            }
                            th, td { 
                                border: 1px solid black; 
                                padding: 2px; 
                                text-align: left; 
                            }
                            th { 
                                text-align: center; 
                                font-size: 11px; 
                                font-weight: bold; 
                            }
                            .header { 
                                text-align: center; 
                                font-size: 15px; 
                                font-weight: bold; 
                            }
                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding: 3px; 
                            }
                            .operator-sign, 
                            .qc-sign, 
                            .qr-code { 
                                font-size: 12px; 
                                text-align: center; 
                                height: 20mm; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 20mm; 
                            }
                            .qr-code img { 
                                width: 60px; 
                                height: 60px; 
                                display: block; 
                                margin: 0 auto; 
                            }
                            .serial-label { 
                                font-size: 11px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                    size: 7.5cm 8cm;
                                    margin: 0;
                                    }
                            @media print {
                                    .printLabel {
                                        page-break-after: always;
                                        width: 7.5cm;
                                        height: 8cm;
                                        display: block;
                                        padding: 0mm;
                                        margin: 0;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 11px;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    body {
                                        margin: 0;
                                        padding: 0;
                                    }
                                }
                        </style>
                    </head>
                <body>';

        foreach ($output_press_packing_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($output_press_packing_details[0]->uom);
            $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="21" align="left"/>
                                <span class="header" style="font-size: 16px; height: 16px;">LABEL PACKING PRESS</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->item_fg_number . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->item_fg_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Status:</b></td>
                            <td colspan="4" style="font-weight: bold;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date/Shift:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . ' / '. $detail->shift .'</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>WO No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->workorder . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>MP Press:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->operator . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Mix Date:</b></td>
                            <td colspan="4" style="font-weight: bold;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Comp Lot No:</b></td>
                            <td colspan="4" style="font-weight: bold;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">MP Finishing | QA</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2"></td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->workorder_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->workorder_label . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);
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
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        // $filter_wo_no = $this->input->get('filter_wo_no');
        // $filter_number = $this->input->get('filter_number');
        // $filter_shift = $this->input->get('filter_shift');
        // $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        // $filter_division = $this->input->get('filter_division');

        // $filter_period = $this->input->get('filter_period');
        // $filter_trans_date = $this->input->get('filter_trans_date');
        $filter_number = $this->input->get('filter_number');
        $filter_shift = $this->input->get('filter_shift');
        $filter_wp = $this->input->get('filter_wp');
        $filter_workorder = $this->input->get('filter_workorder');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_status = $this->input->get('filter_status');

        // if (empty($filter_period)) {
        //     $filter_period = date('Ym');
        // }

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
            g.standard_curing_time,
            FLOOR(COALESCE(a.shift_hour,0) * COALESCE(f.target_shoot_hour,0)) as target_shoot,

            CEILING(COALESCE(a.planning_qty,0) / 3) as planning_qty_shift, 

            CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) as total_qty,

            (CEILING(COALESCE(a.qty_ok,0) + COALESCE(a.qty_ng,0) + COALESCE(a.qty_ng_mold,0)) 
            - CEILING(COALESCE(a.planning_qty,0) / 3)) as minus_prod,

            COALESCE(g.cavity_standard,0) as standard_cavity,

            (COALESCE(a.actual_shoot,0) - FLOOR(COALESCE(a.shift_hour,0) * COALESCE(f.target_shoot_hour,0))) as shoot_deviation,

            COALESCE(ROUND((COALESCE(a.actual_shoot,0) / NULLIF(FLOOR(COALESCE(a.shift_hour,0) * COALESCE(f.target_shoot_hour,0)),0)) * 100, 2),0) as achievment,

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

        // $this->db->join("
        //     (
        //         SELECT item_fg_id, item_rm_id
        //         FROM bom
        //         WHERE priority = 1
        //     ) d",
        //     "a.item_fg_id = d.item_fg_id",
        //     "left"
        // );

        $this->db->join('item_rm e', 'd.item_rm_id = e.id', 'left');

        // $this->db->join("
        //     (
        //         SELECT 
        //             item_fg_id,
        //             machine_id,
        //             mold_id,
        //             target_shoot_hour
        //         FROM setting_molds
        //     ) f",
        //     "a.item_fg_id = f.item_fg_id 
        //     AND a.machine_id = f.machine_id 
        //     AND a.mold_id = f.mold_id",
        //     "left"
        // );

        $this->db->join(
            'setting_molds f',
            'a.item_fg_id = f.item_fg_id 
            AND a.machine_id = f.machine_id 
            AND a.mold_id = f.mold_id',
            'left'
        );

        $this->db->join('molds g', 'a.mold_id = g.id', 'left');

        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }

        // if ($filter_period != "") {
        //     $this->db->where('a.period', $filter_period);
        // }
        // if ($filter_trans_date != "") {
        //     $this->db->where('a.trans_date', $filter_trans_date);
        // }
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

        $this->db->order_by('a.trans_date', 'ASC');
        $this->db->order_by('a.shift', 'ASC');
        $this->db->order_by('(a.wp + 0)', 'ASC', false);
        $this->db->order_by('a.wp', 'ASC');
        $this->db->order_by('c.id', 'ASC');

        // $this->db->order_by('a.id', 'ASC');
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
                <th rowspan="2" >PIC</th>
                <th rowspan="2" >Machine No</th>
                <th rowspan="2" >Product ID</th>
                <th rowspan="2" >Product No</th>
                <th rowspan="2" >Product Name</th>
                <th rowspan="2" >Compound Name Used</th>
                <th rowspan="2" >Planning/day (pcs)</th>
                <th rowspan="2" >Planning/shift (pcs)</th>
                <th rowspan="2" >Work Order No</th>
                <th rowspan="2" >Mold ID</th>
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
            </tr>

            <tr>
                <th>OK</th>
                <th>NG Produksi</th>
                <th>NG Mold</th>
                <th>Total</th>
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
                    <td>' . $data['pic'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['item_rm_number'] . '</td>
                    <td>' . format_number($data['planning_qty']) . '</td>
                    <td>' . format_number($data['planning_qty_shift']) . '</td>
                    <td>' . $data['workorder'] . '</td>
                    <td>' . $data['mold_id'] . '</td>
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
                    <td>' . number_format($data['achievment'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['ng_prod'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['ng_mold'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['total_compound_used'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['waste'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['waste_percen'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['total_used_shoot'], 2, ',', '.') . '</td>
                    <td>' . number_format($data['total_waste_shoot'], 2, ',', '.') . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
