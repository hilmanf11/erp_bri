<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Lost_time_transactions extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[lost_time_transactions.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[lost_time_transactions.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/lost_time_transactions');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('lost_time_transactions', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    // public function readItemFgv1()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
        
    //     $period   = base64_decode($this->input->post('period'));
    //     $wp   = base64_decode($this->input->post('wp'));
    //     $machine_id   = base64_decode($this->input->post('machine_id'));
    //     // $workorder   = base64_decode($this->input->post('workorder'));

    //     $query ="SELECT DISTINCT 
    //         a.item_fg_id, 
    //         b.number, 
    //         b.name, 
    //         a.workorder,
    //         a.mold_id,
    //         a.operator
    //     FROM output_production_press a 
    //     JOIN item_fg b ON a.item_fg_id=b.id
    //     WHERE a.period='$period' 
    //     AND a.wp='$wp' 
    //     AND a.machine_id = '$machine_id'
    //     AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%') 
    //     ORDER BY b.number ASC
    //     ";

    //     $send = $this->crud->query($query);
    //     echo json_encode($send);
    // }

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $period     = base64_decode($this->input->post('period'));
        $wp         = base64_decode($this->input->post('wp'));
        $machine_id = base64_decode($this->input->post('machine_id'));

        $query = "
            SELECT DISTINCT 
                a.item_fg_id, 
                b.number, 
                b.name, 
                a.workorder,
                a.mold_id,
                a.operator,
                a.wp,
                a.shift,
                a.period,
                a.number as number_output
            FROM output_production_press a 
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.period = '$period' 
            AND a.wp = '$wp' 
            AND a.machine_id = '$machine_id'
            AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
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
        FROM lost_time_transactions
        WHERE `deleted` = 0
        ORDER BY workorder DESC");
        echo json_encode($send);
    }

    public function readCategories()
    {
        $send = $this->crud->query("SELECT DISTINCT category
        FROM lost_times
        WHERE `deleted` = 0
        ORDER BY category ASC");
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
        $post = $this->input->post('q');
        $period   = base64_decode($this->input->post('period'));
        $wp   = base64_decode($this->input->post('wp'));
        $shift = base64_decode($this->input->post('shift'));

        $send = $this->crud->query("
            SELECT 
                a.machine_id,
                b.number,
                b.name
            FROM output_production_press as a
            JOIN machines b ON a.machine_id = b.id
            WHERE a.period = '$period' 
            AND a.wp = '$wp'
            AND a.shift = '$shift'
            AND a.workorder IS NOT NULL
            AND (b.number like '%$post%' OR b.name like '%$post%' OR b.id like '%$post%')
            GROUP BY b.id, b.number, b.name
        ");
        echo json_encode($send);
    }

    public function readLTFactors()
    {
        $q = isset($_POST['q']) ? $_POST['q'] : '';
        $factor = $this->input->post('factor');

        $sql = "
            SELECT DISTINCT id, detail, category
            FROM lost_times
            WHERE factor = '$factor'
            AND (detail LIKE '%$q%' OR category LIKE '%$q%')
            ORDER BY detail ASC
        ";

        $data = $this->crud->query($sql);

        echo json_encode($data);
    }

    public function readWorkorder()
    {
        $machine = base64_decode($this->input->post('machine_id'));
        $period = base64_decode($this->input->post('period'));
        $wp = base64_decode($this->input->post('wp'));

        $send = $this->crud->query("SELECT DISTINCT workorder FROM production_schedule_press WHERE `status` = 0 AND `period` = '$period' AND `machine_id` = '$machine' AND wp = '$wp' ORDER BY `workorder` DESC");
        echo json_encode($send);
    }

    public function readOutputDocNo()
    {
        $trans_date = base64_decode($this->input->get('trans_date'));
        $data = $this->crud->query("
            SELECT 
                DISTINCT number, period, wp, shift, pic
            FROM output_production_press 
                WHERE `status` = 0 
                AND `trans_date` = '$trans_date' 
            ORDER BY `number` DESC
        ");
        echo json_encode($data);
    }

    // public function readNextShift()
    // {
    //     $number_output = $this->input->get('number_output');

    //     $used_shifts = $this->crud->query("
    //         SELECT shift FROM lost_time_transactions 
    //         WHERE number_output = '$number_output'
    //     ");

    //     $used = array_column($used_shifts, 'shift');
    //     $available = [1, 2, 3];

    //     $next_shift = null;
    //     foreach ($available as $s) {
    //         if (!in_array($s, $used)) {
    //             $next_shift = $s;
    //             break;
    //         }
    //     }

    //     if ($next_shift === null) $next_shift = 1;

    //     echo json_encode(['next_shift' => $next_shift]);
    // }

    public function readPeriod()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM output_production_press WHERE `status` = 0 ORDER BY `period` DESC");
        echo json_encode($send);
    }

    //GET DATA
    public function readMachinePressMolds()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT 
            a.machine_id as id,
            b.number,
            b.name
            FROM output_production_press as a 
            JOIN machines b ON a.machine_id = b.id 
            AND (b.number like '%$post%' or b.name like '%$post%' or b.id like '%$post%')
            GROUP BY b.id, b.number, b.name
        ");
        echo json_encode($send);
    }

    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp FROM output_production_press WHERE `status` = 0 and `period` = '$period' ORDER BY (wp + 0) DESC, wp DESC");
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

        $sql = $this->db->query("SELECT max(`number`) as kode FROM lost_time_transactions where `number` like '%$ymd%'");
        $row = $sql->row();
        if ($row->kode == null) {
            $autonumber = "LT-PPRS-" . $ymd . "001";
        } else {
            $kode = substr($row->kode, -3);
            $autonumber = "LT-PPRS-" . $ymd . sprintf("%03s", $kode + 1);
        }

        if($type == "return") {
            return $autonumber;
        }

        echo $autonumber;
    }

    public function readNumber()
    {
        $send = $this->crud->query("SELECT DISTINCT `number`
        FROM lost_time_transactions
        WHERE `deleted` = 0
        ORDER BY `number` DESC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_period = $this->input->get('filter_period');
            $filter_number = $this->input->get('filter_number');
            $filter_shift = $this->input->get('filter_shift');
            $filter_wp = $this->input->get('filter_wp');
            $filter_workorder = $this->input->get('filter_workorder');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_category = $this->input->get('filter_category');
            $filter_status = $this->input->get('filter_status');

            if (empty($filter_period)) {
                $filter_period = date('Ym');
            }

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select("a.*, b.number as item_number, b.name as item_name");
            $this->db->from('lost_time_transactions a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            $this->db->join("lost_times lm", 'a.lt_machine_id = lm.id', 'left');
            $this->db->join("lost_times lmat", 'a.lt_material_id = lmat.id', 'left');
            $this->db->join("lost_times lmet", 'a.lt_methode_id = lmet.id', 'left');
            $this->db->join("lost_times lman", 'a.lt_man_id = lman.id', 'left');
            $this->db->join("lost_times lmtr", 'a.lt_trial_id = lmtr.id', 'left');

            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            // if ($filter_division != "") {
            //     $this->db->where('b.division_id', $filter_division);
            // }

            if ($filter_category != "") {
                $this->db->group_start();

                $this->db->where('lm.category', $filter_category);
                $this->db->or_where('lmat.category', $filter_category);
                $this->db->or_where('lmet.category', $filter_category);
                $this->db->or_where('lman.category', $filter_category);
                $this->db->or_where('lmtr.category', $filter_category);

                $this->db->group_end();
            }

            if ($filter_period != "") {
                $this->db->where('a.period', $filter_period);
            }else{
                $this->db->where('a.trans_date', date('Y-m-d'));
            }
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
            $filter_workorder = base64_decode($this->input->get('filter_workorder'));
            $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));
            $filter_category = base64_decode($this->input->get('filter_category'));

            $this->db->select("a.*, 
                b.number as item_fg_number,
                b.name as item_fg_name, 
                c.number as machine_number,
                e.mold_id,
                CEILING(COALESCE(e.total_planning_qty,0) / 3) as planning_qty_shift,

                lm.detail AS lt_machine,
                lm.category AS lt_machine_category,
                lmat.detail AS lt_material,
                lmat.category AS lt_material_category,
                lmet.detail AS lt_methode,
                lmet.category AS lt_methode_category,
                lman.detail AS lt_man,
                lman.category AS lt_man_category,

                lmtr.detail AS lt_trial,
                lmtr.category AS lt_trial_category
            ");

            // CEILING(COALESCE(e.planning_qty,0) / 3) as planning_qty_shift,
            // CEILING(COALESCE(e.total_planning_qty,0) / 3) as planning_qty_shift,

            $this->db->from('lost_time_transactions a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('machines c', 'a.machine_id = c.id', 'left');

            // $this->db->join('output_production_press e', 'a.workorder = e.workorder and a.item_fg_id = e.item_fg_id and a.machine_id = e.machine_id');

            $this->db->join("(
                SELECT 
                    workorder, 
                    item_fg_id, 
                    machine_id, 
                    SUM(planning_qty) AS total_planning_qty,
                    MIN(mold_id) AS mold_id
                FROM output_production_press
                GROUP BY workorder, item_fg_id, machine_id
            ) e", "a.workorder = e.workorder AND a.item_fg_id = e.item_fg_id AND a.machine_id = e.machine_id", "left");

            $this->db->join('bom d', 'a.item_fg_id = d.item_fg_id and d.priority = 1', 'left');
            $this->db->join("(SELECT item_fg_id, MIN(mold_id) AS mold_id
                  FROM setting_molds
                  GROUP BY item_fg_id
                 ) f", "a.item_fg_id = f.item_fg_id", "left");

            $this->db->join("lost_times lm", 'a.lt_machine_id = lm.id', 'left');
            $this->db->join("lost_times lmat", 'a.lt_material_id = lmat.id', 'left');
            $this->db->join("lost_times lmet", 'a.lt_methode_id = lmet.id', 'left');
            $this->db->join("lost_times lman", 'a.lt_man_id = lman.id', 'left');
            $this->db->join("lost_times lmtr", 'a.lt_trial_id = lmtr.id', 'left');

            $this->db->where('a.number', $number);
            if ($filter_workorder != "") {
                $this->db->where('a.workorder', $filter_workorder);
            }
            $this->db->where('a.number', $number);

            if ($filter_workorder != "") {
                $this->db->where('a.workorder', $filter_workorder);
            }

            if ($filter_category != "") {
                $this->db->group_start();

                $this->db->where('lm.category', $filter_category);
                $this->db->or_where('lmat.category', $filter_category);
                $this->db->or_where('lmet.category', $filter_category);
                $this->db->or_where('lman.category', $filter_category);
                $this->db->or_where('lmtr.category', $filter_category);

                $this->db->group_end();
            }

            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            $this->db->order_by('c.id', 'ASC');
            // $this->db->order_by('a.item_fg_id', 'ASC');
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
            
            $this->db->select("
                a.*, 
                b.number as item_fg_number, 
                b.name as item_fg_name, 
                c.number as machine_number,
                lm.detail AS lt_machine,
                lmat.detail AS lt_material,
                lmet.detail AS lt_methode,
                lman.detail AS lt_man,
                lmtr.detail AS lt_trial
            ");
            $this->db->from('lost_time_transactions a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('machines c', 'a.machine_id = c.id', 'left');

            $this->db->join("lost_times lm", 'a.lt_machine_id = lm.id', 'left');
            $this->db->join("lost_times lmat", 'a.lt_material_id = lmat.id', 'left');
            $this->db->join("lost_times lmet", 'a.lt_methode_id = lmet.id', 'left');
            $this->db->join("lost_times lman", 'a.lt_man_id = lman.id', 'left');
            $this->db->join("lost_times lmtr", 'a.lt_trial_id = lmtr.id', 'left');

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
            $this->db->from('lost_time_transactions a');
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
            $this->db->from('lost_time_transactions');
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
            ->from('lost_time_transactions')
            ->where('number', $number)
            ->where('item_fg_id IS NOT NULL')
            ->where('item_fg_id = ""')
            ->order_by('machine_id', 'ASC')
            ->get()
            ->result_array();

        $machines = array_column($data, 'machine_id');

        echo json_encode($machines);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $dataFinal = array(
                //field
                // "trans_date" => $post['trans_date'],
                // "number_output" => $post['number_output'],
                // "number" => $post['number'],
                // "period" => $post['period'],
                // "wp" => $post['wp'],
                // "shift" => $post['shift'],

                "pic" => $post['pic'],
                "item_fg_id" => $post['item_fg_id'],
                "machine_id" => $post['machine_id'],
                // "mold_id" => $post['mold_id'],
                "workorder" => !empty($post['workorder']) ? $post['workorder'] : null,
                "operator" => $post['operator'],
                "cleaning_mold" => $post['cleaning_mold'],
                "changing_mold" => $post['changing_mold'],

                "lt_trial_id" => !empty($post['lt_trial_id']) ? $post['lt_trial_id'] : null,
                "trial_duration" => $post['trial_duration'],

                "lt_machine_id" => !empty($post['lt_machine_id']) ? $post['lt_machine_id'] : null,
                "machine_duration" => $post['machine_duration'],
                "lt_material_id" => !empty($post['lt_material_id']) ? $post['lt_material_id'] : null,
                "material_duration" => $post['material_duration'],
                "lt_methode_id" => !empty($post['lt_methode_id']) ? $post['lt_methode_id'] : null,
                "methode_duration" => $post['methode_duration'],
                "lt_man_id" => !empty($post['lt_man_id']) ? $post['lt_man_id'] : null,
                "man_duration" => $post['man_duration'],
            );

            foreach (['lt_machine_id', 'lt_material_id', 'lt_methode_id', 'lt_man_id'] as $col) {
                if (empty($post[$col])) {
                    $post[$col] = null;
                }
            }

            if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                $post['workorder'] = null;
            }

            if (@$post['id'] != "") {
                $send = $this->crud->update('lost_time_transactions', ["id" => $post['id']], $dataFinal);
            } else {
                // $checkLTTrans = $this->crud->read('lost_time_transactions', [], [
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

                // if (!empty($checkLTTrans)) {
                //     echo json_encode(array(
                //         "title"   => "Duplicate Data",
                //         "message" => "Duplicate Data for Product {$item_fg->number} on Machine {$machine->number} (Period: {$post['period']}, WP: {$post['wp']}, Shift: {$post['shift']}, Workorder: {$post['workorder']}).",
                //         "theme"   => "error"
                //     ));
                //     exit;
                // }

                $send = $this->crud->create('lost_time_transactions', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('lost_time_transactions', $data);
        echo $send;
    }

    // UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777);

        // Load spreadsheet
        $spreadsheet = IOFactory::load($target);
        $sheet = $spreadsheet->getActiveSheet();
        $total_row = $sheet->getHighestDataRow();

        $datas = [];
        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'period' => $sheet->getCellByColumnAndRow(2, $i)->getValue(),
                'trans_date' => $sheet->getCellByColumnAndRow(3, $i)->getValue(),
                'number_output' => $sheet->getCellByColumnAndRow(4, $i)->getValue(),
                'machine_id' => $sheet->getCellByColumnAndRow(5, $i)->getValue(),
                'item_fg_id' => $sheet->getCellByColumnAndRow(6, $i)->getValue(),
                'cleaning_mold' => $sheet->getCellByColumnAndRow(7, $i)->getValue(),
                'changing_mold' => $sheet->getCellByColumnAndRow(8, $i)->getValue(),

                'lt_trial_id' => $sheet->getCellByColumnAndRow(9, $i)->getValue(),
                'trial_duration' => $sheet->getCellByColumnAndRow(10, $i)->getValue(),

                'lt_machine_id' => $sheet->getCellByColumnAndRow(11, $i)->getValue(),
                'machine_duration' => $sheet->getCellByColumnAndRow(12, $i)->getValue(),
                'lt_material_id' => $sheet->getCellByColumnAndRow(13, $i)->getValue(),
                'material_duration' => $sheet->getCellByColumnAndRow(14, $i)->getValue(),
                'lt_methode_id' => $sheet->getCellByColumnAndRow(15, $i)->getValue(),
                'methode_duration' => $sheet->getCellByColumnAndRow(16, $i)->getValue(),
                'lt_man_id' => $sheet->getCellByColumnAndRow(17, $i)->getValue(),
                'man_duration' => $sheet->getCellByColumnAndRow(18, $i)->getValue(),
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
        @unlink('failed/lost_time_transactions.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/lost_time_transactions.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_lost_time_transactions_" . date("Ymd_s") . ".xls";

        header("Content-Description: File Upload Failed");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file);
    }

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
                $lt_machine = null;
                $lt_material = null;
                $lt_method = null;
                $lt_man = null;


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

                if (empty($data['number_output'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Output Doc No is required"
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

                if ($data['cleaning_mold'] !== "" && !is_numeric($data['cleaning_mold'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Cleaning Mold must be numeric"
                    ];
                    continue;
                }

                if ($data['changing_mold'] !== "" && !is_numeric($data['changing_mold'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Changing Mold must be numeric"
                    ];
                    continue;
                }

                if($data['lt_trial_id'] !== "" && !empty($data['lt_trial_id'])) {

                    $lt_trial = $this->crud->read('lost_times', [], ["detail" => $data['lt_trial_id']]);
                    if (empty($lt_trial)) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Lost Time Detail " . $data['lt_trial_id'] . " Not Found"
                        ];
                        continue;
                    }
                }

                if($data['lt_machine_id'] !== "" && !empty($data['lt_machine_id'])) {

                    $lt_machine = $this->crud->read('lost_times', [], ["detail" => $data['lt_machine_id']]);
                    if (empty($lt_machine)) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Lost Time Detail " . $data['lt_machine_id'] . " Not Found"
                        ];
                        continue;
                    }
                }

                // if ($data['machine_duration'] !== "" && !is_numeric($data['machine_duration'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "Machine Duration must be numeric"
                //     ];
                //     continue;
                // }

                if($data['lt_material_id'] !== "" && !empty($data['lt_material_id'])) {

                    $lt_material = $this->crud->read('lost_times', [], ["detail" => $data['lt_material_id']]);
                    if (empty($lt_material)) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Lost Time Detail " . $data['lt_material_id'] . " Not Found"
                        ];
                        continue;
                    }
                }


                // if ($data['material_duration'] !== "" && !is_numeric($data['material_duration'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "Material Duration must be numeric"
                //     ];
                //     continue;
                // }


                if($data['lt_methode_id'] !== "" && !empty($data['lt_methode_id'])) {

                    $lt_method = $this->crud->read('lost_times', [], ["detail" => $data['lt_methode_id']]);
                    if (empty($lt_method)) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Lost Time Detail " . $data['lt_methode_id'] . " Not Found"
                        ];
                        continue;
                    }
                }


                // if ($data['methode_duration'] !== "" && !is_numeric($data['methode_duration'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "Methode Duration must be numeric"
                //     ];
                //     continue;
                // }


                if($data['lt_man_id'] !== "" && !empty($data['lt_man_id'])) {

                    $lt_man = $this->crud->read('lost_times', [], ["detail" => $data['lt_man_id']]);
                    if (empty($lt_man)) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Lost Time Detail " . $data['lt_man_id'] . " Not Found"
                        ];
                        continue;
                    }
                }


                // if ($data['man_duration'] !== "" && !is_numeric($data['man_duration'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "Man Duration must be numeric"
                //     ];
                //     continue;
                // }



                $this->db->distinct();
                $this->db->select('number, period, wp, shift, pic');
                $this->db->from('output_production_press');
                // $this->db->where('status', 0);
                // $this->db->where('period', $data['period']);
                $this->db->where('trans_date', $data['trans_date']);
                $this->db->where('number', $data['number_output']);
                // $this->db->where('machine_id', $data['machine_id']);
                $this->db->order_by('number', 'DESC');
                $checkCombination = $this->db->get()->row();

                if (empty($checkCombination)) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Output Press not found (No: {$data['number_output']} with the given Trans Date, and Machine No)"
                    ];
                    continue;
                }


                $wp = @$checkCombination->wp;
                $shift = @$checkCombination->shift;
                $pic = @$checkCombination->pic;

                $outputPeriod = @$checkCombination->period;


                $groupKey = $data['period'] . '|' . $data['trans_date'] . '|' . $data['number_output'] . '|' . $wp . '|' . $shift;

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

                $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);

                if (empty($item_fg)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. " . $data['item_fg_id'] . " Not Found"
                    ];
                    continue;
                }

                $checkMachineProdSchPress = $this->crud->read('output_production_press', [], ["machine_id" => $machine->id]);

                if (empty($checkMachineProdSchPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Machine No. " . $data['machine_id'] . " not found in Output Production Press"
                    ];
                    continue;
                }


                $checkWPOutputPress = $this->crud->read('output_production_press', [], [
                    "wp" => $wp,
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                ]);

                if (empty($checkWPOutputPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "WP No. {$wp} not found in Output Production Press ". "for Product No. {$item_fg->number} on Machine {$machine->number}."
                    ];
                    continue;
                }


                $this->db->distinct();
                $this->db->select('
                    a.item_fg_id,
                    b.number,
                    b.name,
                    a.workorder,
                    a.mold_id,
                    a.operator,
                    a.wp,
                    a.shift,
                    a.period,
                    a.number as number_output
                ');
                $this->db->from('output_production_press a');
                $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                $this->db->where('a.period', $outputPeriod);
                $this->db->where('a.wp', $wp);
                $this->db->where('a.shift', $shift);
                $this->db->where('a.machine_id', $machine->id);
                $this->db->where('a.item_fg_id', $item_fg->id);
                $checkCombinationItem = $this->db->get()->row();

                // $item_fg_id = @$checkCombinationItem->item_fg_id;
                $workorder = @$checkCombinationItem->workorder;
                $operator = @$checkCombinationItem->operator;

                $checkItemOutputPress = $this->crud->read('output_production_press', [], [
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id
                ]);

                if (empty($checkItemOutputPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. " . $item_fg->number . " not found in Output Production Press for Machine " . $data['machine_id']
                    ];
                    continue;
                }

                $checkWOOutputPress = $this->crud->read('output_production_press', [], [
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                    "workorder" => $workorder,
                ]);

                if (empty($checkWOOutputPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Work Order No. {$workorder} not found in Output Production Press ". "for Product No. {$item_fg->number} on Machine {$machine->number}."
                    ];
                    continue;
                }


                $checkOutputPress = $this->crud->read('output_production_press', [], [
                    "number" => $data['number_output'],
                    "trans_date" => $data['trans_date'],
                    "wp" => $wp,
                    "shift" => $shift,
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                    "workorder" => $workorder,
                ]);

                if (empty($checkOutputPress)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Output Press not found (No: {$data['number_output']} with the given Trans Date, WP, Shift, Machine No, Product No, WO No)"
                    ];
                    continue;
                }


                $checkData = $this->crud->read('lost_time_transactions', [], [
                    "period"     => $data['period'],
                    "trans_date" => $data['trans_date'],
                    "number_output" => $data['number_output'],
                    "wp"         => $wp,
                    "shift"      => $shift,
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id,
                    "workorder"  => $workorder,
                ]);

                // if (!empty($checkData)) {
                //     $results[] = [
                //         "status" => "failed",
                //         "item" => "Line " . ($index + 1),
                //         "message" => "Duplicate Data: Period " . $data['period'] . 
                //                     ", WP No. " . $wp . 
                //                     ", Prod Date " . $data['trans_date'] .
                //                     ", Machine No. " . $data['machine_id'] . 
                //                     ", Product No. " . $item_fg->number . 
                //                     ", WO No. " . $workorder,
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
                    "trans_date"            => $data['trans_date'],
                    "number_output"         => $data['number_output'],
                    "number"                => $groupNumber,
                    "wp"                    => $wp,
                    "shift"                 => $shift,
                    "pic"                   => $pic,

                    "machine_id"            => $machine->id,
                    "item_fg_id"            => $item_fg->id,
                    "workorder"             => $workorder,
                    "operator"              => $operator,

                    "cleaning_mold"         => $data['cleaning_mold'],
                    "changing_mold"         => $data['changing_mold'],

                    "lt_trial_id"           => @$lt_trial->id,
                    "trial_duration"        => $data['trial_duration'] ?? 0,

                    "lt_machine_id"         => @$lt_machine->id,
                    "machine_duration"      => $data['machine_duration'] ?? 0,
                    "lt_material_id"        => @$lt_material->id,
                    "material_duration"     => $data['material_duration'] ?? 0,
                    "lt_methode_id"         => @$lt_method->id,
                    "methode_duration"      => $data['methode_duration'] ?? 0,
                    "lt_man_id"             => @$lt_man->id,
                    "man_duration"          => $data['man_duration'] ?? 0,
                );

                try {
                    if (!empty($checkData)) {
                        // Update
                        $this->db->update('lost_time_transactions', [
                            "workorder"             => $workorder,
                            "operator"              => $operator,

                            "cleaning_mold"         => $data['cleaning_mold'],
                            "changing_mold"         => $data['changing_mold'],

                            "lt_trial_id"           => isset($lt_trial->id) ? $lt_trial->id : null,
                            "trial_duration"        => $data['trial_duration'] ?? 0,

                            "lt_machine_id"         => isset($lt_machine->id) ? $lt_machine->id : null,
                            "machine_duration"      => $data['machine_duration'] ?? 0,
                            "lt_material_id"        => isset($lt_material->id) ? $lt_material->id : null,
                            "material_duration"     => $data['material_duration'] ?? 0,
                            "lt_methode_id"         => isset($lt_method->id) ? $lt_method->id : null,
                            "methode_duration"      => $data['methode_duration'] ?? 0,
                            "lt_man_id"             => isset($lt_man->id) ? $lt_man->id : null,
                            "man_duration"          => $data['man_duration'] ?? 0,
                        ], [
                            "number"                => $checkData->number,
                            "period"                => $data['period'],
                            "trans_date"            => $data['trans_date'],
                            "number_output"         => $data['number_output'],
                            "wp"                    => $wp,
                            "shift"                 => $shift,
                            "machine_id"            => $machine->id,
                            "item_fg_id"            => $item_fg->id,
                            "workorder"             => $workorder,
                        ]);

                        $status = "update";
                    } else {
                        // Insert

                        $this->crud->create('lost_time_transactions', $dataFinal);

                        $status = "insert";
                    }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "WP {$wp} Shift {$shift} for Product {$item_fg->number} on Machine {$machine->number} updated");

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
                $filePath = 'failed/lost_time_transactions.xls';

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
                @unlink('failed/lost_time_transactions.xls');

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
            header("Content-Disposition: attachment; filename=lost_time_transactions_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_period = $this->input->get('filter_period');
        $filter_number = $this->input->get('filter_number');
        $filter_shift = $this->input->get('filter_shift');
        $filter_wp = $this->input->get('filter_wp');
        $filter_workorder = $this->input->get('filter_workorder');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_category = $this->input->get('filter_category');
        $filter_status = $this->input->get('filter_status');

        if (empty($filter_period)) {
            $filter_period = date('Ym');
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, 
            b.number as item_fg_number,
            b.name as item_fg_name, 
            c.number as machine_number,
            e.mold_id,
            CEILING(COALESCE(e.total_planning_qty,0) / 3) as planning_qty_shift,

            lm.detail AS lt_machine,
            lm.category AS lt_machine_category,
            lmat.detail AS lt_material,
            lmat.category AS lt_material_category,
            lmet.detail AS lt_methode,
            lmet.category AS lt_methode_category,
            lman.detail AS lt_man,
            lman.category AS lt_man_category,

            lmtr.detail AS lt_trial,
            lmtr.category AS lt_trial_category,
        ");

        $this->db->from('lost_time_transactions a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('machines c', 'a.machine_id = c.id', 'left');

        // $this->db->join('output_production_press e', 'a.workorder = e.workorder and a.item_fg_id = e.item_fg_id and a.machine_id = e.machine_id');

        $this->db->join("(
            SELECT 
                workorder, 
                item_fg_id, 
                machine_id, 
                SUM(planning_qty) AS total_planning_qty,
                MIN(mold_id) AS mold_id
            FROM output_production_press
            GROUP BY workorder, item_fg_id, machine_id
        ) e", "a.workorder = e.workorder AND a.item_fg_id = e.item_fg_id AND a.machine_id = e.machine_id", "left");

        $this->db->join('bom d', 'a.item_fg_id = d.item_fg_id and d.priority = 1', 'left');
        $this->db->join("(SELECT item_fg_id, MIN(mold_id) AS mold_id
                FROM setting_molds
                GROUP BY item_fg_id
                ) f", "a.item_fg_id = f.item_fg_id", "left");

        $this->db->join("lost_times lm", 'a.lt_machine_id = lm.id', 'left');
        $this->db->join("lost_times lmat", 'a.lt_material_id = lmat.id', 'left');
        $this->db->join("lost_times lmet", 'a.lt_methode_id = lmet.id', 'left');
        $this->db->join("lost_times lman", 'a.lt_man_id = lman.id', 'left');
        $this->db->join("lost_times lmtr", 'a.lt_trial_id = lmtr.id', 'left');

        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }

        if ($filter_category != "") {
            $this->db->group_start();

            $this->db->where('lm.category', $filter_category);
            $this->db->or_where('lmat.category', $filter_category);
            $this->db->or_where('lmet.category', $filter_category);
            $this->db->or_where('lman.category', $filter_category);
            $this->db->or_where('lmtr.category', $filter_category);

            $this->db->group_end();
        }

        if ($filter_period != "") {
            $this->db->where('a.period', $filter_period);
        }
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
        $this->db->order_by('c.id', 'ASC');
        // $this->db->order_by('a.item_fg_id', 'ASC');
        $this->db->order_by('a.workorder', 'ASC');

        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#lost_time_transactions {border-collapse: collapse;width: 100%;font-size: 12px;}#lost_time_transactions td, #lost_time_transactions th {border: 1px solid #ddd;padding: 2px;}#lost_time_transactions tr:nth-child(even){background-color: #f2f2f2;}#lost_time_transactions tr:hover {background-color: #ddd;}#lost_time_transactions th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>LOST TIME TRANSACTION</h3>
            </div>
        </center>
        
        <table id="lost_time_transactions" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" >Period</th>
                <th rowspan="2" >Production Date</th>
                <th rowspan="2" >Document No</th>
                <th rowspan="2" >Shift</th>
                <th rowspan="2" >WP No</th>
                <th rowspan="2" >PIC</th>
                <th rowspan="2" >Machine No</th>
                <th rowspan="2" >Mold Id</th>
                <th rowspan="2" >Product No</th>
                <th rowspan="2" >Product Name</th>
                <th rowspan="2" >Plan/shift (pcs)</th>
                <th rowspan="2" >WO No</th>
                <th rowspan="2" >Operator Name</th>
                <th colspan="5" style="text-align: center;">Planned Lost Time</th>
                <th colspan="12" style="text-align: center;">Unplanned Lost Time</th>
            </tr>

            <tr>
                <th>Cleaning Mold <br>(minutes)</th>
                <th>Changing Mold <br>(minutes)</th>
                <th>Trial Project</th>
                <th>Category</th>
                <th>Duration <br>(minutes)</th>
                <th>Machine</th>
                <th>Category</th>
                <th>Duration <br>(minutes)</th>
                <th>Material</th>
                <th>Category</th>
                <th>Duration <br>(minutes)</th>
                <th>Methode</th>
                <th>Category</th>
                <th>Duration <br>(minutes)</th>
                <th>Man</th>
                <th>Category</th>
                <th>Duration <br>(minutes)</th>
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
                    <td>' . $data['mold_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . format_number($data['planning_qty_shift']) . '</td>
                    <td>' . $data['workorder'] . '</td>
                    <td>' . $data['operator'] . '</td>
                    <td>' . $data['cleaning_mold'] . '</td>
                    <td>' . $data['changing_mold'] . '</td>
                    <td>' . $data['lt_trial'] . '</td>
                    <td>' . $data['lt_trial_category'] . '</td>
                    <td>' . $data['trial_duration'] . '</td>
                    <td>' . $data['lt_machine'] . '</td>
                    <td>' . $data['lt_machine_category'] . '</td>
                    <td>' . $data['machine_duration'] . '</td>
                    <td>' . $data['lt_material'] . '</td>
                    <td>' . $data['lt_material_category'] . '</td>
                    <td>' . $data['material_duration'] . '</td>
                    <td>' . $data['lt_methode'] . '</td>
                    <td>' . $data['lt_methode_category'] . '</td>
                    <td>' . $data['methode_duration'] . '</td>
                    <td>' . $data['lt_man'] . '</td>
                    <td>' . $data['lt_man_category'] . '</td>
                    <td>' . $data['man_duration'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'B2' => ['Isi Dengan YYYYMM'],
            'C2' => ['Isi Dengan YYYY-MM-DD'],
            'D2' => ['Isi Dengan Output Doc No'],
            'E2' => ['Isi Dengan MACHINE NO (LIHAT DI SHEET Master Machines)'],
            'F2' => ['Isi Dengan PRODUCT NO'],
            'G3' => ['Isi Dengan Angka (minutes)'],
            'H3' => ['Isi Dengan Angka (minutes)'],
            'I3' => ['Isi Dengan DETAIL (LIHAT DI SHEET Master Lost Time, Factor TRIAL PROJECT)'],
            'J3' => ['Isi Dengan Angka (minutes)'],
            'K3' => ['Isi Dengan DETAIL (LIHAT DI SHEET Master Lost Time, Factor MACHINE)'],
            'L3' => ['Isi Dengan Angka (minutes)'],
            'M3' => ['Isi Dengan DETAIL (LIHAT DI SHEET Master Lost Time, Factor MATERIAL)'],
            'N3' => ['Isi Dengan Angka (minutes)'],
            'O3' => ['Isi Dengan DETAIL (LIHAT DI SHEET Master Lost Time, Factor METHOD)'],
            'P3' => ['Isi Dengan Angka (minutes)'],
            'Q3' => ['Isi Dengan DETAIL (LIHAT DI SHEET Master Lost Time, Factor MAN)'],
            'R3' => ['Isi Dengan Angka (minutes)'],
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('LOST TIME TRANSACTION');
        $templateSheet->mergeCells('A1:R1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(20);
        $templateSheet->getColumnDimension('C')->setWidth(20);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(20);
        $templateSheet->getColumnDimension('F')->setWidth(30);
        $templateSheet->getColumnDimension('G')->setWidth(30);
        $templateSheet->getColumnDimension('H')->setWidth(25);

        $templateSheet->getColumnDimension('I')->setWidth(20);
        $templateSheet->getColumnDimension('J')->setWidth(20);

        $templateSheet->getColumnDimension('K')->setWidth(20);
        $templateSheet->getColumnDimension('L')->setWidth(20);
        $templateSheet->getColumnDimension('M')->setWidth(20);
        $templateSheet->getColumnDimension('N')->setWidth(20);
        $templateSheet->getColumnDimension('O')->setWidth(20);
        $templateSheet->getColumnDimension('P')->setWidth(20);
        $templateSheet->getColumnDimension('Q')->setWidth(20);
        $templateSheet->getColumnDimension('R')->setWidth(20);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD LOST TIME TRANSACTION');
        $templateSheet->setCellValue('A2', 'NO');
        $templateSheet->setCellValue('B2', 'PERIOD');
        $templateSheet->setCellValue('C2', 'PRODUCTION DATE');
        $templateSheet->setCellValue('D2', 'OUTPUT DOC NO');
        $templateSheet->setCellValue('E2', 'MACHINE NO');
        $templateSheet->setCellValue('F2', 'PRODUCT NO');
        $templateSheet->setCellValue('G2', 'PLANNED LOST TIME');
        $templateSheet->setCellValue('H2', 'PLANNED LOST TIME');

        $templateSheet->setCellValue('I2', 'PLANNED LOST TIME');
        $templateSheet->setCellValue('J2', 'PLANNED LOST TIME');

        $templateSheet->setCellValue('K2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('L2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('M2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('N2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('O2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('P2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('Q2', 'UNPLANNED LOST TIME');
        $templateSheet->setCellValue('R2', 'UNPLANNED LOST TIME');

        $templateSheet->setCellValue('G3', 'CLEANING MOLD')
            ->setCellValue('H3', 'CHANGING MOLD')

            ->setCellValue('I3', 'TRIAL PROJECT')
            ->setCellValue('J3', 'DURATION')

            ->setCellValue('K3', 'MACHINE')
            ->setCellValue('L3', 'DURATION')
            ->setCellValue('M3', 'MATERIAL')
            ->setCellValue('N3', 'DURATION')
            ->setCellValue('O3', 'METHODE')
            ->setCellValue('P3', 'DURATION')
            ->setCellValue('Q3', 'MAN')
            ->setCellValue('R3', 'DURATION');

        $templateSheet->mergeCells('A2:A3');
        $templateSheet->mergeCells('B2:B3');
        $templateSheet->mergeCells('C2:C3');
        $templateSheet->mergeCells('D2:D3');
        $templateSheet->mergeCells('E2:E3');
        $templateSheet->mergeCells('F2:F3');
        $templateSheet->mergeCells('G2:J2');
        $templateSheet->mergeCells('K2:R2');

        $templateSheet->getStyle('A2:R2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A3:R3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A2')->getFont()->setBold(true);
        $templateSheet->getStyle('B2:F2')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A2:R2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $templateSheet->getStyle('A3:R3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $templateSheet->getStyle('A2:R2')->getFont()->setBold(true);
        $templateSheet->getStyle('A3:R3')->getFont()->setBold(true);

        // $templateSheet->getStyle('D:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);

        foreach ($comments as $cell => $commentLines) {
            $richText = new RichText();
            foreach ($commentLines as $index => $line) {
                $run = new Run($line);
                $run->getFont()->setSize(9);
                $run->getFont()->setName('Times New Roman');

                if ($index === 0) {
                    $run->getFont()->setBold(true);
                }
        
                $richText->createText($line);
                if ($index < count($commentLines) - 1) {
                    $richText->createText("\n");
                }
            }

            $comment = $templateSheet->getComment($cell);
            $comment->setText($richText);
            $comment->setWidth('135px');
            $comment->setHeight('120px');
            $comment->setAuthor('Author Name');
        }

        // Second Sheet: Reference
        $item_refSheet = $spreadsheet->createSheet(1);
        $item_refSheet->setTitle('Master Machines');

        $this->db->select('a.id, a.number as machine_number, a.name as machine_name');
        $this->db->from('machines a');
        $this->db->where('a.type_process_id', 'PT01');
        $this->db->order_by("
        CAST(
            SUBSTRING_INDEX(a.number, ' ', -1) 
            AS UNSIGNED
        )
        ", "asc", false);

        $this->db->order_by("
        SUBSTRING(
            SUBSTRING_INDEX(a.number, ' ', -1),
            LENGTH(CAST(SUBSTRING_INDEX(a.number, ' ', -1) AS UNSIGNED)) + 1
        )
        ", "asc", false);

        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(5);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(30);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Machine No');
        $item_refSheet->setCellValue('C1', 'Machine Name');
        $item_refSheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:C1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['machine_number']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['machine_name']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('H' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('I' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':C' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':C' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        // Third Sheet: Reference
        $item_refSheet = $spreadsheet->createSheet(2);
        $item_refSheet->setTitle('Master Lost Time');

        $this->db->select('a.id, a.factor, a.detail, a.category');
        $this->db->from('lost_times a');
        $this->db->order_by("a.factor");

        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(5);
        $item_refSheet->getColumnDimension('B')->setWidth(25);
        $item_refSheet->getColumnDimension('C')->setWidth(40);
        $item_refSheet->getColumnDimension('D')->setWidth(25);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Factor');
        $item_refSheet->setCellValue('C1', 'Detail');
        $item_refSheet->setCellValue('D1', 'Category');
        $item_refSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:D1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['factor']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['detail']);
            $item_refSheet->setCellValue('D' . $rowItem_ref, $itemref['category']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('H' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('I' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        $spreadsheet->setActiveSheetIndex(0); 

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_lost_time_trans.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
