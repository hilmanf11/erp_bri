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

class Production_schedule_press extends CI_Controller
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
        //Validasi Form
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/production_schedule_press');
        } else {
            redirect('error_access');
        }
    }

    public function get_wp_number()
    {
        $trans_date = $this->input->post('trans_date'); // frontend kirim Y-m-d
        $result_wp = $this->_calculate_wp($trans_date);

        echo json_encode([
            'wp' => $result_wp
        ]);
    }

    // Core function yang bisa dipakai ulang
    private function _calculate_wp($trans_date)
    {
        $year  = date('Y', strtotime($trans_date));
        $month = date('m', strtotime($trans_date));

        $firstDate = date('Y-m-01', strtotime($year . "-" . $month . "-01"));
        $endDate   = date('Y-m-t', strtotime($year . "-" . $month . "-01"));

        $wp = 0;
        $alfabet = "z"; // penanda awal
        $firstDate_loop = $firstDate;
        $result_wp = "";

        while (strtotime($firstDate_loop) <= strtotime($endDate)) {
            $working_date = date('Y-m-d', strtotime($firstDate_loop));

            // cek hari libur
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            $isWeekend = (date('w', strtotime($firstDate_loop)) == '0' || date('w', strtotime($firstDate_loop)) == '6');
            $isHoliday = (!empty($holiday) && $holiday->remarks != "");

            if (!$isWeekend && !$isHoliday) {
                // hari kerja normal
                if ($wp == 0) $wp = 1;
                $wpp = $wp; 
                $alfabet = "z"; // reset alfabet
                $wp++;
            } else {
                // weekend / libur
                $alfabet = $this->_getNextAlphabet($alfabet);
                // $wpp = ($wp > 0 ? $wp - 1 : 0) . $alfabet;

                if ($firstDate_loop == $firstDate && $wp == 0) {
                    $wpp = "1" . $alfabet;
                } else {
                    $wpp = ($wp > 0 ? $wp - 1 : 1) . $alfabet;
                }
            }

            if ($working_date == $trans_date) {
                $result_wp = $wpp;
                break;
            }

            $firstDate_loop = date("Y-m-d", strtotime("+1 day", strtotime($firstDate_loop)));
        }

        return $result_wp;
    }

    private function _getNextAlphabet($alfabet)
    {
        $list = range('A','O');
        if ($alfabet == "z") return "A";
        $key = array_search($alfabet, $list);
        if ($key !== false && isset($list[$key+1])) {
            return $list[$key+1];
        }
        return "";
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_schedule_press', ["name" => $post]);
        echo json_encode($send);
    }

    // public function getCapacity()
    // {
    //     $item_fg_id = $this->input->post('item_fg_id');
    //     $machine_id = $this->input->post('machine_id');
    //     $mold_id    = $this->input->post('mold_id');

    //     $sql = "
    //         SELECT 
    //             pc.capacity_day
    //         FROM setting_molds sm
    //         JOIN menu_loadings ml 
    //             ON ml.item_fg_id = sm.item_fg_id 
    //             AND ml.machine_id = sm.machine_id 
    //             AND ml.mold_id = sm.mold_id
    //         JOIN production_capacities pc
    //             ON pc.item_fg_id = sm.item_fg_id 
    //             AND pc.machine_id = sm.machine_id
    //         WHERE sm.item_fg_id = '$item_fg_id'
    //         AND sm.machine_id = '$machine_id'
    //         AND sm.mold_id = '$mold_id'
    //         LIMIT 1
    //     ";

    //     $data = $this->db->query($sql)->row();

    //     echo json_encode([
    //         "capacity_day" => $data->capacity_day ?? 0
    //     ]);
    // }

    //GET DATA
    // public function readSettingMolds($item_fg, $machine)
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";

    //     $item_fg_id = base64_decode($item_fg);
    //     $machine_id = base64_decode($machine);

    //     // $send = $this->crud->query("SELECT * FROM setting_molds 
    //     //     WHERE item_fg_id = '$item_fg_id' 
    //     //     AND machine_id = '$machine_id'
    //     //     AND mold_id LIKE '%$post%' 
    //     //     GROUP BY mold_id
    //     // ");

    //     $send = $this->crud->query("
    //         SELECT 
    //             sm.mold_id,
    //             pc.capacity_day
    //         FROM setting_molds sm
    //         JOIN production_capacities pc
    //             ON pc.item_fg_id = sm.item_fg_id
    //             AND pc.machine_id = sm.machine_id
    //         WHERE sm.item_fg_id = '$item_fg_id'
    //         AND sm.machine_id = '$machine_id'
    //         AND sm.mold_id LIKE '%$post%'
    //         GROUP BY sm.mold_id
    //     ");

    //     echo json_encode($send);
    // }

    // public function getCapacity()
    // {
    //     $item_fg_id = $this->input->post('item_fg_id');
    //     $machine_id = $this->input->post('machine_id');
    //     $mold_id    = $this->input->post('mold_id');

    //     $sql = "
    //         SELECT 
    //             pc.capacity_day
    //         FROM menu_loadings ml
    //         JOIN production_capacities pc
    //             ON pc.item_fg_id = ml.item_fg_id
    //         AND pc.machine_id = ml.machine_id
    //         WHERE ml.item_fg_id = '$item_fg_id'
    //         AND ml.machine_id = '$machine_id'
    //         AND ml.mold_id = '$mold_id'
    //         LIMIT 1
    //     ";

    //     $data = $this->db->query($sql)->row();

    //     echo json_encode([
    //         'capacity_day' => $data->capacity_day ?? 0
    //     ]);
    // }

    // public function readSettingMolds($item_fg, $machine)
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";

    //     $item_fg_id = base64_decode($item_fg);
    //     $machine_id = base64_decode($machine);

    //     $sql = "
    //         SELECT 
    //             ml.mold_id,
    //             pc.capacity_day
    //         FROM menu_loadings ml
    //         JOIN production_capacities pc
    //             ON pc.item_fg_id = ml.item_fg_id
    //         AND pc.machine_id = ml.machine_id
    //         WHERE ml.item_fg_id = '$item_fg_id'
    //         AND ml.machine_id = '$machine_id'
    //         AND ml.mold_id LIKE '%$post%'
    //         GROUP BY ml.mold_id
    //     ";

    //     $send = $this->db->query($sql)->result();

    //     echo json_encode($send);
    // }

    public function getCapacity()
    {
        $item_fg_id = $this->input->post('item_fg_id');
        $machine_id = $this->input->post('machine_id');
        $mold_id    = $this->input->post('mold_id');

        $sql = "
            SELECT 
                pc.capacity_day
            FROM setting_molds sm
            JOIN menu_loadings ml 
                ON ml.item_fg_id = sm.item_fg_id 
                AND ml.machine_id = sm.machine_id 
                AND ml.mold_id = sm.mold_id
            JOIN production_capacities pc
                ON pc.item_fg_id = ml.item_fg_id
                AND pc.machine_id = ml.machine_id
            WHERE ml.item_fg_id = '$item_fg_id'
                AND ml.machine_id = '$machine_id'
                AND ml.mold_id = '$mold_id'
                LIMIT 1
        ";

        $data = $this->db->query($sql)->row();

        if(empty($data)) {

            $sql2 = "SELECT * FROM setting_molds 
                WHERE item_fg_id = '$item_fg_id' 
                AND machine_id = '$machine_id'
                AND mold_id LIKE '%$mold_id%' 
                GROUP BY mold_id
            ";

            $data = $this->db->query($sql2)->row();
        }

        echo json_encode([
            'capacity_day' => $data->capacity_day ?? 0
        ]);
    }

    public function readSettingMolds($item_fg, $machine)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $item_fg_id = base64_decode($item_fg);
        $machine_id = base64_decode($machine);

        $sql = "
            SELECT 
                sm.mold_id,
                pc.capacity_day
            FROM setting_molds sm
            JOIN menu_loadings ml 
                ON ml.item_fg_id = sm.item_fg_id 
                AND ml.machine_id = sm.machine_id 
                AND ml.mold_id = sm.mold_id
            JOIN production_capacities pc
                ON pc.item_fg_id = sm.item_fg_id
                AND pc.machine_id = sm.machine_id
            WHERE sm.item_fg_id = '$item_fg_id'
                AND sm.machine_id = '$machine_id'
                AND sm.mold_id LIKE '%$post%'
            GROUP BY sm.mold_id
        ";

        $send = $this->db->query($sql)->result();

        if (empty($send)) {
                
            $sql2 = "SELECT * FROM setting_molds 
                WHERE item_fg_id = '$item_fg_id' 
                AND machine_id = '$machine_id'
                AND mold_id LIKE '%$post%' 
                GROUP BY mold_id
            ";

            $send = $this->db->query($sql2)->result();
        }

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
            FROM setting_molds as a 
            JOIN machines b ON a.machine_id = b.id 
            AND (b.number like '%$post%' or b.name like '%$post%' or b.id like '%$post%')
            GROUP BY b.id, b.number, b.name
        ");
        echo json_encode($send);
    }

    // public function readItemPressMolds($machine_id)
    // {
    //     $machine_id = json_decode($machine_id);
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";

    //     $send = $this->crud->query("SELECT 
    //         a.item_fg_id as id,
    //         b.number,
    //         b.name
    //         FROM setting_molds as a 
    //         JOIN item_fg b ON a.item_fg_id = b.id
    //         WHERE a.machine_id = '$machine_id'
    //         AND (b.number like '%$post%' or b.name like '%$post%' or b.id like '%$post%')
    //         GROUP BY b.id, b.number, b.name
    //     ");
    //     echo json_encode($send);
    // }

    public function readItemPressMolds($machine_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $machine_id = base64_decode($machine_id);

        $send = $this->crud->query("SELECT 
            b.id as id,
            b.number,
            b.name,
            b.item_family_number,
            a.machine_id
            FROM setting_molds a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.machine_id = '$machine_id'
            AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%' OR b.id LIKE '%$post%')
            GROUP BY b.id, b.number, b.name, b.item_family_number
        ");
        echo json_encode($send);
    }


    public function readPeriodAll()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedule_press ORDER BY `period` DESC");
        echo json_encode($send);
    }
    public function readWpAll()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp, workorder, so_number, item_fg_id FROM production_schedule_press WHERE `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }
    public function readPeriod()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedule_press WHERE `status` = 0 ORDER BY `period` DESC");
        echo json_encode($send);
    }

    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp FROM production_schedule_press WHERE `status` = 0 and `period` = '$period' ORDER BY (wp + 0) ASC, wp ASC");
        echo json_encode($send);
    }

    public function readWorkorder()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $send = $this->crud->query("SELECT DISTINCT workorder FROM production_schedule_press WHERE `status` = 0 and `period` = '$period' and wp = '$wp' ORDER BY `workorder` DESC");
        echo json_encode($send);
    }

    public function readCustomer()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $workorder = base64_decode($this->input->get('workorder'));

        $send = $this->crud->query("SELECT a.customer_id, b.number as customer_number, b.name as customer_name 
            FROM production_schedule_press a
            JOIN customers b on a.customer_id = b.id
            WHERE a.status = 0 and a.period = '$period' and a.wp = '$wp' and a.workorder = '$workorder' ORDER BY a.workorder DESC");
        echo json_encode($send);
    }

    public function readItems()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $workorder = base64_decode($this->input->get('workorder'));

        $send = $this->crud->query("SELECT a.workorder, b.id as item_fg_id, b.number as item_number, b.name as item_name  
            FROM production_schedule_press a
            JOIN item_fg b on a.item_fg_id = b.id
            WHERE a.status = 0 and a.period = '$period' and a.wp = '$wp' and a.workorder = '$workorder' 
            ORDER BY a.workorder DESC");
        echo json_encode($send);
    }


    public function readItems2()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $query = "SELECT a.id, a.number, a.name, a.item_family_number
                FROM item_fg a
                WHERE a.status = 0";
        if (!empty($post)) {
            $query .= " AND (a.number LIKE '%$post%' OR a.name LIKE '%$post%')";
        }
        $data = $this->crud->query($query);
        echo json_encode($data);
    }

    public function readProcess()
    {
        $item_family_number = $this->input->get('item_family_number');

        if($item_family_number == "RP") {
            $query = "SELECT id, name
                    FROM item_process
                    WHERE id IN ('PC006') AND status = 0";
        }else{
            $query = "SELECT id, name
                    FROM item_process
                    WHERE id IN ('PC002', 'PC003') AND status = 0";
        }

        $data = $this->crud->query($query);
        echo json_encode($data);
    }

    public function readMonth()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array(
                "number" => $key,
                "name" => $value
            );
        }
        die(json_encode($arr));
    }

    public function readYear()
    {
        $tahun_before = date('Y', strtotime('-5 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_before; $i <= $tahun_next; $i++) {
            $arr[] = array(
                "number" => "$i"
            );
        }
        die(json_encode($arr));
    }

    // public function workorderv1($process_id, $trans_date)
    // {
    //     $datenow = date("ymd", strtotime($trans_date));
    //     $sqlGetID = $this->db->query("SELECT max(workorder) as kode FROM production_schedule_press WHERE workorder like '%$datenow%'");
    //     $rowID = $sqlGetID->row();
    //     $kode = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%05s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%05s", $urutan);
    //     }

    //     $workOrderNo = "WO" . $datenow . "-" . $autoID;

    //     return $workOrderNo;
    // }

    public function workorder($process_id, $trans_date)
    {
        $datenow = date("ymd", strtotime($trans_date));

        $prefix = "WOP"; // Default prefix

        $sqlGetID = $this->db->query("
            SELECT MAX(workorder) as kode
            FROM production_schedule_press
            WHERE workorder LIKE '{$prefix}{$datenow}-%'
        ");

        $rowID = $sqlGetID->row();
        $kode = $rowID ? $rowID->kode : null;

        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }

        $workOrderNo = $prefix . $datenow . "-" . $autoID;

        return $workOrderNo;
    }

    // public function datatables()
    // {
    //     if ($this->input->post()) {
    //         // $filter_month = $this->input->get('filter_month');
    //         // $filter_year = $this->input->get('filter_year');
    //         $filter_period = $this->input->get('filter_period');
    //         $filter_machine_no = $this->input->get('filter_machine_no');
    //         $filter_wp = $this->input->get('filter_wp');
    //         $filter_item_fg_id = $this->input->get('filter_item_fg_id');
    //         $filter_status = $this->input->get('filter_status');

    //         if (empty($filter_period)) {
    //             $filter_period = date('Ym');
    //         }

    //         $page = $this->input->post('page');
    //         $rows = $this->input->post('rows');
    //         // Pagination 1-10
    //         $page   = isset($page) ? intval($page) : 1;
    //         $rows   = isset($rows) ? intval($rows) : 10;
    //         $offset = ($page - 1) * $rows;
    //         $result = array();
    //         // Select Query
    //         $this->db->select("a.*, 
    //             c.number as item_number, c.name as item_name, c.uom, 
    //             d.name as machine_name, 
    //             d.number as machine_number, 
    //             e.name as process_name,
    //             g.id as mold_id,
    //             (CASE WHEN f.id != '' THEN 2 ELSE a.status END) as status_wo");
    //         $this->db->from('production_schedule_press a');
    //         $this->db->join('item_fg c', 'a.item_fg_id = c.id');
    //         // $this->db->join('line_productions d', 'a.line_id = d.id');
    //         $this->db->join('machines d', 'a.machine_id = d.id');
    //         $this->db->join('item_process e', 'a.process_id = e.id', 'left');
    //         // $this->db->join('scan_item_receipts_fg f', 'a.so_number = f.so_number and a.workorder = f.workorder', 'left');
    //         $this->db->join('scan_item_receipts_fg f', 'a.workorder = f.workorder', 'left');
    //         $this->db->join('molds g', 'a.mold_id = g.id', 'left');
    //         $this->db->where('a.deleted', 0);

    //         // Filter berdasarkan status
    //         if ($filter_status == "0") {
    //             $this->db->where("a.status", 0);
    //         } elseif ($filter_status == "1") {
    //             $this->db->where("f.id is NULL");
    //         } elseif ($filter_status == "2") {
    //             $this->db->where("f.id != ''");
    //         }

    //         // Filter berdasarkan inputan
    //         // $this->db->like('a.month', $filter_month);
    //         // $this->db->like('a.year', $filter_year);
    //         $this->db->like('a.period', $filter_period);
    //         $this->db->like('a.machine_id', $filter_machine_no);
    //         // $this->db->like('a.wp', $filter_wp);
    //         $this->db->like('a.item_fg_id', $filter_item_fg_id);

    //         if(!empty($filter_wp)) {
    //             $this->db->where('a.wp', $filter_wp);
    //         }

    //         $this->db->order_by('a.wp', 'ASC');
    //         $this->db->order_by('a.machine_id', 'ASC');
    //         $this->db->order_by('a.trans_date', 'ASC');
    //         $this->db->order_by('a.id', 'ASC');
    //         // $this->db->order_by('a.item_fg_id', 'ASC');

    //         // Total Data
    //         $totalRows = $this->db->count_all_results('', false);

    //         // Limit 1 - 10
    //         $this->db->limit($rows, $offset);

    //         // Get Data Array
    //         $records = $this->db->get()->result_array();

    //         // Ambil semua data per machine, urut sesuai WP dan tanggal
    //         $records_grouped = [];
    //         foreach ($records as $r) {
    //             $records_grouped[$r['machine_id']][] = $r;
    //         }

    //         // Loop per mesin untuk cek status_mold
    //         foreach ($records_grouped as $machine_id => &$list) {
    //             $prev_item = null;
    //             $prev_wp = null;

    //             foreach ($list as &$row) {
    //                 // Default pertama pasti "Change"
    //                 if ($prev_item === null) {
    //                     $row['status_mold'] = 'Change';
    //                 } else {
    //                     // Jika item sama → Continue
    //                     if ($prev_item == $row['item_fg_id']) {
    //                         $row['status_mold'] = 'Continue';
    //                     } else {
    //                         $row['status_mold'] = 'Change';
    //                     }
    //                 }

    //                 // Update previous reference
    //                 $prev_item = $row['item_fg_id'];
    //                 $prev_wp = $row['wp'];
    //             }
    //         }

    //         // Flatten hasil akhir
    //         $records = [];
    //         foreach ($records_grouped as $machine_list) {
    //             foreach ($machine_list as $r) {
    //                 $records[] = $r;
    //             }
    //         }

    //         // Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_period = $this->input->get('filter_period');
            $filter_machine_no = $this->input->get('filter_machine_no');
            $filter_wp = $this->input->get('filter_wp');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_status = $this->input->get('filter_status');
            $filter_status_mold = $this->input->get('filter_status_mold');

            if (empty($filter_period)) {
                $filter_period = date('Ym');
            }

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = [];

            // --- base select / joins ---
            $this->db->select("a.*, 
                c.number as item_number, c.name as item_name, c.uom, 
                d.name as machine_name, d.number as machine_number, 
                e.name as process_name,
                g.id as mold_id,
                (CASE WHEN f.id != '' THEN 2 ELSE a.status END) as status_wo");
            $this->db->from('production_schedule_press a');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('machines d', 'a.machine_id = d.id');
            $this->db->join('item_process e', 'a.process_id = e.id', 'left');
            $this->db->join('scan_item_receipts_fg f', 'a.workorder = f.workorder', 'left');
            $this->db->join('molds g', 'a.mold_id = g.id', 'left');
            $this->db->where('a.deleted', 0);

            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }

            // status filter
            if ($filter_status == "0") {
                $this->db->where("a.status", 0);
            } elseif ($filter_status == "1") {
                $this->db->where("f.id is NULL");
            } elseif ($filter_status == "2") {
                $this->db->where("f.id != ''");
            }

            // other filters
            $this->db->like('a.period', $filter_period);
            $this->db->like('a.machine_id', $filter_machine_no);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            if (!empty($filter_wp)) {
                $this->db->where('a.wp', $filter_wp);
            }
            // ORDER BY (wp + 0) DESC, wp DESC
            // $this->db->order_by('a.wp', 'ASC');

            $this->db->order_by('(a.wp + 0)', 'ASC', false);
            $this->db->order_by('a.wp', 'ASC');


            $this->db->order_by('a.machine_id', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->order_by('a.id', 'ASC');

            // count + get paginated rows
            // $totalRows = $this->db->count_all_results('', false);
            // $this->db->limit($rows, $offset);
            // $records = $this->db->get()->result_array();

            $records = $this->db->get()->result_array();
            $totalRows = count($records);

            $first_row_by_machine = [];
            foreach ($records as $r) {
                if (!isset($first_row_by_machine[$r['machine_id']])) {
                    $first_row_by_machine[$r['machine_id']] = $r;
                }
            }

            $prev_item_by_machine = [];
            foreach ($first_row_by_machine as $machine_id => $first) {
                // Ambil record terakhir sebelum baris pertama di hasil (dalam periode yang sama)
                $sql = "SELECT item_fg_id, period
                        FROM production_schedule_press
                        WHERE machine_id = ?
                        AND deleted = 0
                        AND (
                                (period = ? AND (wp < ? OR (wp = ? AND (trans_date < ? OR (trans_date = ? AND id < ?))))
                            )
                            OR (period < ?)
                        )
                        ORDER BY period DESC, wp DESC, trans_date DESC, id DESC
                        LIMIT 1";

                $q = $this->db->query($sql, [
                    $machine_id,
                    $first['period'],
                    $first['wp'],
                    $first['wp'],
                    $first['trans_date'],
                    $first['trans_date'],
                    $first['id'],
                    $first['period']
                ]);
                
                $prev = $q->row_array();
                $prev_item_by_machine[$machine_id] = $prev ? $prev['item_fg_id'] : null;
            }

            foreach ($records as &$row) {
                $machine_id = $row['machine_id'];
                $current_item = $row['item_fg_id'];

                // jika belum ada prev (dan tidak ditemukan di DB) => Change (awal)
                if (!array_key_exists($machine_id, $prev_item_by_machine) || $prev_item_by_machine[$machine_id] === null) {
                    $row['status_mold'] = 'Change';
                } else {
                    $row['status_mold'] = ($prev_item_by_machine[$machine_id] == $current_item) ? 'Continue' : 'Change';
                }

                // update prev untuk mesin ini untuk baris berikutnya
                $prev_item_by_machine[$machine_id] = $current_item;
            }
            unset($row);

            if (!empty($filter_status_mold)) {
                $records = array_filter($records, function ($r) use ($filter_status_mold) {
                    return $r['status_mold'] === $filter_status_mold;
                });
                $records = array_values($records);
            }

            $totalRows = count($records);
            $records = array_slice($records, $offset, $rows);

            // output
            $result['total'] = $totalRows;
            $result['rows'] = $records;
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $workorder = $this->workorder($post['process_id'], $post['trans_date']);
                $production_schedule_press = $this->crud->read('production_schedule_press', [], [
                    "item_fg_id" => $post['item_fg_id'], 
                    "mold_id" => $post['mold_id'],
                    "wp" => $post['wp'], 
                    "trans_date" => $post['trans_date'],
                    "process_id" => $post['process_id'],
                    "machine_id" => $post['machine_id']
                ]);
                // $sales_orders = $this->crud->query("SELECT 
                //     a.item_fg_id, b.number as item_number, 
                //     b.name as item_name, 
                //     (a.qty - coalesce(SUM(c.qty), 0)) as qty
                // FROM sales_orders a 
                // JOIN item_fg b on a.item_fg_id = b.id
                // LEFT JOIN production_schedule_press c ON a.sales_order_no = c.so_number and a.item_fg_id = c.item_fg_id
                // WHERE a.item_fg_id = '$post[item_fg_id]'
                // GROUP BY a.item_fg_id");

                if (@$production_schedule_press->id) {
                    show_error("Duplicate Data");
                } 
                // elseif (!empty($sales_orders) && $post['qty'] > $sales_orders[0]->qty) {
                //     show_error("qty is bigger than available quantity");
                // } 
                else {
                    // unset($post['customer_id'], $post['so_number']);
                    $postFinal = array_merge($post, array("workorder" => $workorder, "period" => $post['year'] . $post['month']));
                    $send = $this->crud->create('production_schedule_press', $postFinal);
                }
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('production_schedule_press', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('production_schedule_press', ["id" => $data['id']]);
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
        for ($i = 3; $i <= $total_row; $i++) {
            $wp_trans_date_raw = $sheet->getCellByColumnAndRow(4, $i)->getValue();
            
            if (is_numeric($wp_trans_date_raw)) {
                $wp_trans_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($wp_trans_date_raw)->format('Y-m-d');
            } else {
                $wp_trans_date = date('Y-m-d', strtotime($wp_trans_date_raw));
            }

            // Menambahkan data ke array
            $datas[] = array(
                'period' => $sheet->getCellByColumnAndRow(2, $i)->getValue(),
                'machine_id' => $sheet->getCellByColumnAndRow(3, $i)->getValue(),
                'trans_date' => $wp_trans_date,
                'item_fg_id' => $sheet->getCellByColumnAndRow(5, $i)->getValue(),
                'mold_id' => $sheet->getCellByColumnAndRow(6, $i)->getValue(),
                // 'qty' => $sheet->getCellByColumnAndRow(7, $i)->getValue(),
            );
        }

        // $datas['total'] = count($datas);
        // echo json_encode($datas);

        echo json_encode([
            "total" => count($datas),
            "data" => $datas
        ]);

        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/production_schedule_press.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/production_schedule_press.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_production_schedule_press_" . date("Ymd_s") . ".xls";

        header("Content-Description: File Upload Failed");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file);
    }

    //UPLOAD CREATE DATA
    // public function uploadcreatev1()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         // Validasi qty tidak boleh kosong atau nol
    //         if (!isset($data['qty']) || empty($data['qty']) || $data['qty'] <= 0) {
    //             echo json_encode(["title" => "Error", "message" => "Quantity must be greater than 0", "theme" => "error"]);
    //             return;
    //         }

    //         // Validasi keberadaan item, line, dan process
    //         $items = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
    //         $line = $this->crud->read('line_productions', [], ["number" => $data['line_id']]);
    //         $process = $this->crud->read('item_process', [], ["id" => $data['process_id']]);

    //         if (empty($items->id)) {
    //             echo json_encode(["title" => "Not Found", "message" => "Product No " . $data['item_fg_id'] . " Not Found", "theme" => "error"]);
    //             return;
    //         }
    //         if (empty($line->id)) {
    //             echo json_encode(["title" => "Not Found", "message" => "Line Production " . $data['line_id'] . " Not Found", "theme" => "error"]);
    //             return;
    //         }
    //         if (empty($process->id)) {
    //             echo json_encode(["title" => "Not Found", "message" => "Process Id " . $data['process_id'] . " Not Found", "theme" => "error"]);
    //             return;
    //         }

    //         // Periksa duplikasi data berdasarkan item_fg_id, process_id, line_id, wp, trans_date
    //         $existing_data = $this->crud->read('production_schedule_press', [], [
    //             "item_fg_id" => $items->id,
    //             "process_id" => $data['process_id'],
    //             "line_id" => $line->id,
    //             "wp" => $data['wp'],
    //             "trans_date" => $data['trans_date']
    //         ]);

    //         if (!empty($existing_data)) {
    //             echo json_encode([
    //                 "title" => "Duplicated",
    //                 "message" => "Duplicate Data: Product Id " . $data['item_fg_id'] . 
    //                             ", Process Id " . $data['process_id'] . 
    //                             ", Line ID " . $data['line_id'] . 
    //                             ", WP " . $data['wp'] . 
    //                             ", Trans Date " . $data['trans_date'],
    //                 "theme" => "error"
    //             ]);
    //             return;
    //         }

    //         // Ambil sisa qty dari sales_orders
    //         $sales_orders = $this->crud->query("SELECT 
    //             a.item_fg_id, b.number as item_number, 
    //             b.name as item_name, 
    //             (a.qty - COALESCE(SUM(c.qty), 0)) as qty
    //         FROM sales_orders a 
    //         JOIN item_fg b ON a.item_fg_id = b.id
    //         LEFT JOIN production_schedule_press c ON a.sales_order_no = c.so_number AND a.item_fg_id = c.item_fg_id
    //         WHERE a.item_fg_id = '{$items->id}'
    //         GROUP BY a.item_fg_id");

    //         if (!empty($sales_orders) && $data['qty'] > $sales_orders[0]->qty) {
    //             echo json_encode(["title" => "Error", "message" => "Quantity is bigger than available quantity", "theme" => "error"]);
    //             return;
    //         }

    //         // Generate workorder
    //         $workorder = $this->workorder($data['process_id'], $data['trans_date']);

    //         // Generate month dan year dari trans_date
    //         $dateObj = DateTime::createFromFormat('Y-m-d', $data['trans_date']);
    //         if ($dateObj === false) {
    //             echo json_encode(["title" => "Error", "message" => "Invalid transaction date format", "theme" => "error"]);
    //             return;
    //         }
    //         $year = $dateObj->format('Y');
    //         $month = $dateObj->format('m');

    //         // Data final yang akan dimasukkan
    //         $dataFinal = [
    //             "workorder" => $workorder,
    //             "item_fg_id" => $items->id,
    //             "process_id" => $data['process_id'],
    //             "line_id" => $line->id,
    //             "trans_date" => $data['trans_date'],
    //             "period" => $year . $month,
    //             "year" => $year,
    //             "month" => $month,
    //             "wp" => $data['wp'],
    //             "qty" => $data['qty']
    //         ];

    //         $send = $this->crud->create('production_schedule_press', $dataFinal);
    //         echo $send;
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

            foreach ($data_list as $index => $data) {
                $processed_count++;
                if (
                    empty($data['period']) ||
                    empty($data['machine_id']) ||
                    empty($data['trans_date']) ||
                    empty($data['item_fg_id']) ||

                    // empty($data['mold_id']) ||
                    // empty($data['qty']) ||
                    // !is_numeric($data['qty'])

                    !strtotime($data['trans_date'])
                   ) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Invalid or missing data"
                        ];
                        continue;
                }

                if (!preg_match('/^\d{6}$/', $data['period'])) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Invalid period format (must be YYYYMM)"
                    ];
                    continue;
                }

                $year  = substr($data['period'], 0, 4);
                $month = substr($data['period'], 4, 2);
                if (!checkdate((int)$month, 1, (int)$year)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Invalid period value"
                    ];
                    continue;
                }

                $data['process_id'] = 'PC006'; // Pressing process
                $wp = $this->_calculate_wp($data['trans_date']);

                // Validasi qty tidak boleh kosong atau nol
                // if (!isset($data['qty']) || empty($data['qty']) || $data['qty'] <= 0) {
                //     $results[] = [
                //         "status" => "failed",
                //         "item" => "Line " . ($index + 1),
                //         "message" => "Quantity must be greater than 0"
                //     ];
                //     continue;
                // }

                $machine = $this->crud->read('machines', [], ["number" => $data['machine_id']]);
                if (empty($machine)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Machine No. " . $data['machine_id'] . " Not Found"
                    ];
                    continue;
                }

                $checkMachineSettingMolds = $this->crud->read('setting_molds', [], ["machine_id" => $machine->id]);

                if (empty($checkMachineSettingMolds)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Machine No. " . $data['machine_id'] . " not registered in Setting Molds"
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

                $checkItemSettingMolds = $this->crud->read('setting_molds', [], [
                    "machine_id" => $machine->id,
                    "item_fg_id" => $item_fg->id
                ]);

                if (empty($checkItemSettingMolds)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. " . $data['item_fg_id'] . " not registered in Setting Molds for Machine " . $data['machine_id']
                    ];
                    continue;
                }

                $availableMold = $this->db
                    ->select("mold_id")
                    ->where('item_fg_id', $item_fg->id)
                    ->where('machine_id', $machine->id)
                    ->get('menu_loadings')
                    ->row();

                if(!empty($availableMold)) {
                    // if (count($availableMold) === 1) {
                    $mold_id = $availableMold->mold_id;
                    // } 
                    
                    // else {
                    //     if (empty($data['mold_id'])) {
                    //         $results[] = [
                    //             "status" => "failed",
                    //             "item" => "Line " . ($index + 1),
                    //             "message" => "Product No. {$data['item_fg_id']} on Machine No. {$data['machine_id']} has multiple molds in Menu Loadings, please specify mold_id."
                    //         ];
                    //         continue;
                    //     }
                    //     $mold_id = $data['mold_id'];
                    // }

                } else {

                    // if(count($availableMolds) < 1) {
                    $availableMolds = $this->db
                        ->select("DISTINCT(mold_id)")
                        ->where('item_fg_id', $item_fg->id)
                        ->where('machine_id', $machine->id)
                        ->get('setting_molds')
                        ->result();

                    if (count($availableMolds) === 1) {
                        $mold_id = $availableMolds[0]->mold_id;
                    } else {
                        if (empty($data['mold_id'])) {
                            $results[] = [
                                "status" => "failed",
                                "item" => "Line " . ($index + 1),
                                "message" => "Product No. {$data['item_fg_id']} has multiple molds, please specify mold_id."
                            ];
                            continue;
                        }
                        $mold_id = $data['mold_id'];
                    }

                    $qty = 0;
                }

                $mold = $this->db->get_where('molds', ['id' => $mold_id])->row();
                if (empty($mold)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Mold ID {$mold_id} Not Found"
                    ];
                    continue;
                }

                $checkMoldSettingMolds = $this->db
                    ->where('machine_id', $machine->id)
                    ->where('item_fg_id', $item_fg->id)
                    ->where('mold_id', $mold_id)
                    ->get('setting_molds')
                    ->row();

                if (empty($checkMoldSettingMolds)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Mold {$mold_id} for Product {$data['item_fg_id']} not found in Machine {$data['machine_id']} settings."
                    ];
                    continue;
                }

                $checkMenuLoading = $this->db
                    ->where('item_fg_id', $item_fg->id)
                    ->where('machine_id', $machine->id)
                    ->where('mold_id', $mold_id)
                    ->get('menu_loadings')
                    ->row();

                if (empty($checkMenuLoading)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. {$data['item_fg_id']} on Machine No. {$data['machine_id']} with Mold {$mold_id} unsetting in Menu Loadings and Production Capacities."
                    ];
                    continue;
                }

                $checkCapacity = $this->db
                    ->where('item_fg_id', $item_fg->id)
                    ->where('machine_id', $machine->id)
                    ->get('production_capacities')
                    ->row();

                if (empty($checkCapacity)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No. {$data['item_fg_id']} on Machine {$data['machine_id']} unsetting in Production Capacities."
                    ];
                    continue;
                }

                $qty = $checkCapacity->capacity_day ?? 0;

                $checkData = $this->crud->read('production_schedule_press', [], [
                    "item_fg_id" => $item_fg->id,
                    "mold_id"    => $mold->id,
                    "process_id" => $data['process_id'],
                    "machine_id" => $machine->id,
                    "wp"         => $wp,
                    "trans_date" => $data['trans_date'],
                    "period" => $data['period'],
                ]);

                if (!empty($checkData)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Duplicate Data: Period " . $data['period'] . 
                                    ", Product No. " . $data['item_fg_id'] . 
                                    ", Machine No. " . $data['machine_id'] . 
                                    ", WP No. " . $wp . 
                                    ", Trans Date " . $data['trans_date']
                    ];
                    continue;
                }

                // Generate workorder
                $workorder = $this->workorder($data['process_id'], $data['trans_date']);

                // Generate month dan year dari trans_date
                $dateObj = DateTime::createFromFormat('Y-m-d', $data['trans_date']);
                $year = $dateObj->format('Y');
                $month = $dateObj->format('m');

                $dataFinal = array(
                    "workorder" => $workorder,
                    "item_fg_id" => $item_fg->id,
                    "process_id" => $data['process_id'],
                    "machine_id" => $machine->id,
                    "mold_id" => $mold->id,
                    "trans_date" => $data['trans_date'],
                    "period" => $data['period'],
                    "year" => $year,
                    "month" => $month,
                    "wp" => $wp,
                    "qty" => $qty
                );

                try {
                    // if (!empty($checkData)) {
                    //     // Update
                    //     $this->db->update('production_schedule_press', [
                    //         "wp" => $data['wp'],
                    //         "trans_date" => $data['trans_date'],
                    //         "qty" => $data['qty']
                    //     ], [
                    //         "period" => $checkData->period,
                    //         "workorder" => $checkData->workorder,
                    //         "machine_id" => $machine->id,
                    //         "item_fg_id" => $item_fg->id,
                    //     ]);

                    //     $status = "update";
                    // } else {
                        // Insert
                        $this->crud->create('production_schedule_press', $dataFinal);

                        $status = "insert";
                    // }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "Product No $item_fg->number Data Updated");

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
                $filePath = 'failed/production_schedule_press.xls';

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
                @unlink('failed/production_schedule_press.xls');

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

    public function print_job_order($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.lot');
        $this->db->from('production_schedule_press a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $label = $this->db->get()->row();
        $amountQty = ceil($label->qty / $label->lot);
        for ($i = 1; $i <= $amountQty; $i++) {
            $lots = sprintf("%03s", $i);
            $this->db->select('b.circuit');
            $this->db->from('production_schedule_press a');
            $this->db->join('job_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id and b.customer_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.id', $id);
            $this->db->order_by('b.circuit', 'asc');
            $totalRows = $this->db->count_all_results('', false);
            $job_orders = $this->db->get()->result_array();
            $no = 1;
            $qty = $label->qty;
            foreach ($job_orders as $job_order) {
                $sequence = sprintf("%03s", $no);
                $label_no = $label->workorder . $lots . $sequence;
                if ($no == $totalRows) {
                    $finalQty = $qty;
                } else {
                    $finalQty = $label->lot;
                }
                $dataJobOrderLabel = array(
                    "workorder" => $label->workorder,
                    "label_no" => $label_no,
                    "circuit" => $job_order['circuit'],
                    "qty" => $finalQty,
                );
                $jobOrderLabel = $this->crud->read("job_order_labels", [], ["label_no" => $label_no]);
                if (empty($jobOrderLabel->id)) {
                    $this->crud->create("job_order_labels", $dataJobOrderLabel);
                }
                $qty -= $label->lot;
                $no++;
            }
        }
        $this->db->select('b.*, a.so_number, a.workorder, a.so_date, a.trans_date, a.qty, c.label_no, c.circuit, d.number as item_number, d.lot');
        $this->db->from('production_schedule_press a');
        $this->db->join('job_order_labels c', 'a.workorder = c.workorder');
        $this->db->join('job_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id and b.customer_id and c.circuit = b.circuit', 'left');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $this->db->group_by('c.circuit');
        $this->db->group_by('c.label_no');
        $this->db->order_by('c.label_no', 'asc');
        $records = $this->db->get()->result_object();
        if ($records) {
            $html = '<html>
                    <head>
                        <title>' . $label->workorder . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 20cm;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
            foreach ($records as $record) {
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                $html .= '  <table id="customers" border="1" style="margin-bottom:20px;">
                                <tr>
                                    <th colspan="4" style="font-size:16px; padding:10px; text-align:center;"><b>JOB ORDER ' . $config->name . '</b></th>
                                    <th width="150">
                                        <table style="width:100%; font-size:10px; border:0;">
                                            <tr style="border:0;">
                                                <td width="60">Doc No</td>
                                                <td width="100">' . $config_iso->doc_job_order . '</td>
                                            </tr>
                                            <tr style="border:0;">
                                                <td>Form</td>
                                                <td>' . $config_iso->form_job_order . '</td>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">MODEL</th>
                                    <th style="text-align:center;">PLAN QTY</th>
                                    <th style="text-align:center;">LOT</th>
                                    <th style="text-align:center;">START DATE</th>
                                    <th style="text-align:center;">ISSUE DATE</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">' . $record->item_number . '</td>
                                    <td style="text-align:center;">' . $record->qty . '</td>
                                    <td style="text-align:center;">' . $record->lot . '</td>
                                    <td style="text-align:center;">' . $record->trans_date . '</td>
                                    <td style="text-align:center;">' . $record->so_date . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">WIRE CODE</th>
                                    <th style="text-align:center;">TYPE & SIZE</th>
                                    <th style="text-align:center;">COLOR</th>
                                    <th style="text-align:center;">LENGTH</th>
                                    <th style="text-align:center;">M/C NO</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">' . $record->wire . '</td>
                                    <td style="text-align:center;">' . $record->type . ' ' . $record->size . '</td>
                                    <td style="text-align:center;">' . $record->color . '</td>
                                    <td style="text-align:center;">' . $record->length . '</td>
                                    <td style="text-align:center;"></td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">TERMINAL SIDE A</th>
                                    <th style="text-align:center;">TERMINAL SIDE B</th>
                                    <th colspan="3" style="text-align:center;">WO. No</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_terminal . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_terminal . '</td>
                                    <td rowspan="7" colspan="3" style="text-align:center;">' . $record->workorder . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_seal . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_seal . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_chi . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_chi . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_chc . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_chc . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_stripping . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_stripping . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_process . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_process . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_note . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_note . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">CIRCUIT NO</th>
                                    <th style="text-align:center;">SERIAL NO</th>
                                    <th style="text-align:center;">OPERATOR</th>
                                    <th style="text-align:center;">CHECK BY</th>
                                    <th style="text-align:center;">INSPECT BY</th>
                                </tr>
                                <tr>
                                    <th rowspan="3" style="text-align:center; height:50px; font-size:40px;">' . $record->circuit . '</th>
                                    <td rowspan="3" style="text-align:center; height:50px;">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="80"/>
                                        <br>
                                        <span>' . $record->label_no . '</span>
                                    </td>
                                    <th style="text-align:center; height:80px;"></th>
                                    <th style="text-align:center; height:80px;"></th>
                                    <th style="text-align:center; height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="text-align:left;">Name :</th>
                                    <th style="text-align:left;">Name :</th>
                                    <th style="text-align:left;">Name :</th>
                                </tr>
                                <tr>
                                    <th style="text-align:left;">Date :</th>
                                    <th style="text-align:left;">Date :</th>
                                    <th style="text-align:left;">Date :</th>
                                </tr>
                            </table>';
            }
            $html .= "<script>window.print()</script>";
            die($html);
        } else {
            echo "<h1>NOT FOUND JOB ORDER</h1>";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=production_schedule_press_$format.xls");
        }
        // $filter_month = $this->input->get('filter_month');
        // $filter_year = $this->input->get('filter_year');
        // $filter_customers = $this->input->get('filter_customers');
        // $filter_sales_order = $this->input->get('filter_sales_order');

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_period = $this->input->get('filter_period');
        $filter_machine_no = $this->input->get('filter_machine_no');
        $filter_wp = $this->input->get('filter_wp');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        // $filter_status = $this->input->get('filter_status');
        $filter_status_mold = $this->input->get('filter_status_mold');

        if (empty($filter_period)) {
            $filter_period = date('Ym');
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, c.number as item_number, c.name as item_name, c.uom, d.number as machine_number, e.cavity_standard');
        $this->db->from('production_schedule_press a');
        // $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('machines d', 'a.machine_id = d.id');
        $this->db->join('molds e', 'a.mold_id = e.id', 'left');

        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }

        $this->db->where('a.deleted', 0);
        // $this->db->like('a.month', $filter_month);
        // $this->db->like('a.year', $filter_year);
        $this->db->like('a.period', $filter_period);
        $this->db->like('a.machine_id', $filter_machine_no);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);

        if(!empty($filter_wp)) {
            $this->db->like('a.wp', $filter_wp);
        }

        // $this->db->like('a.wp', $filter_wp);
        // $this->db->like('a.customer_id', $filter_customers);
        // $this->db->like('a.so_number', $filter_sales_order);

        // $this->db->order_by('a.trans_date', 'ASC');
        // $this->db->order_by('c.number', 'ASC');


            // $this->db->order_by('a.wp', 'ASC');

            $this->db->order_by('(a.wp + 0)', 'ASC', false);
            $this->db->order_by('a.wp', 'ASC');

            $this->db->order_by('a.machine_id', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->order_by('a.id', 'ASC');

            $records = $this->db->get()->result_array();

            $first_row_by_machine = [];
            foreach ($records as $r) {
                if (!isset($first_row_by_machine[$r['machine_id']])) {
                    $first_row_by_machine[$r['machine_id']] = $r;
                }
            }

            $prev_item_by_machine = [];
            foreach ($first_row_by_machine as $machine_id => $first) {
                // Ambil record terakhir sebelum baris pertama di hasil (dalam periode yang sama)
                $sql = "SELECT item_fg_id, period
                        FROM production_schedule_press
                        WHERE machine_id = ?
                        AND deleted = 0
                        AND (
                                (period = ? AND (wp < ? OR (wp = ? AND (trans_date < ? OR (trans_date = ? AND id < ?))))
                            )
                            OR (period < ?)
                        )
                        ORDER BY period DESC, wp DESC, trans_date DESC, id DESC
                        LIMIT 1";

                $q = $this->db->query($sql, [
                    $machine_id,
                    $first['period'],
                    $first['wp'],
                    $first['wp'],
                    $first['trans_date'],
                    $first['trans_date'],
                    $first['id'],
                    $first['period']
                ]);
                
                $prev = $q->row_array();
                $prev_item_by_machine[$machine_id] = $prev ? $prev['item_fg_id'] : null;
            }

            foreach ($records as &$row) {
                $machine_id = $row['machine_id'];
                $current_item = $row['item_fg_id'];

                // jika belum ada prev (dan tidak ditemukan di DB) => Change (awal)
                if (!array_key_exists($machine_id, $prev_item_by_machine) || $prev_item_by_machine[$machine_id] === null) {
                    $row['status_mold'] = 'Change';
                } else {
                    $row['status_mold'] = ($prev_item_by_machine[$machine_id] == $current_item) ? 'Continue' : 'Change';
                }

                // update prev untuk mesin ini untuk baris berikutnya
                $prev_item_by_machine[$machine_id] = $current_item;
            }
            unset($row);

            if (!empty($filter_status_mold)) {
                $records = array_filter($records, function ($r) use ($filter_status_mold) {
                    return $r['status_mold'] === $filter_status_mold;
                });
                $records = array_values($records);
            }

        // $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: center; text-align: center; margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <br><br>
                <div style="text-align: center;">
                    <small style="font-size: 20px; font-weight: bold;">PRODUCTION SCHEDULE PRESS</small>
                </div>

                <div style="font-size: 12px; text-align: left;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Period</th>
                    <th>WP No</th>
                    <th>WP Date</th>
                    <th>Work Order</th>
                    <th>Machine No</th>
                    <th>Mold Id</th>
                    <th>Cavity Standard</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>UoM</th>
                    <th>Status Mold</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {

                if ($data['status_mold'] == 'Change') {
                    $style = 'background-color:#FFDFBD;color:orange;';
                } else if($data['status_mold'] == 'Continue'){
                    $style = 'background-color:#c3f8f1;color:#2fa192;';
                }

            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="text-align:center">' . $data['period'] . '</td>
                            <td style="text-align:center">' . $data['wp'] . '</td>
                            <td style="text-align:center">' . $data['trans_date'] . '</td>
                            <td style="text-align:center">' . $data['workorder'] . '</td>
                            <td style="text-align:center">' . $data['machine_number'] . '</td>
                            <td style="text-align:center">' . $data['mold_id'] . '</td>
                            <td style="text-align:center">' . $data['cavity_standard'] . '</td>
                            <td style="text-align:left">' . $data['item_number'] . '</td>
                            <td style="text-align:left">' . $data['item_name'] . '</td>
                            <td style="text-align:center">' . format_number($data['qty']) . '</td>
                            <td style="text-align:center">' . $data['uom'] . '</td>
                            <td style="text-align:center;'. $style .'"><b>' . $data['status_mold'] . '</b></td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'B2' => ['ISI DENGAN YYYYMM'],
            'C2' => ['ISI DENGAN MACHINE NO (LIHAT DI SHEET Machines)'],
            'D2' => ['ISI DENGAN YYYY-MM-DD'],
            'E2' => ['ISI DENGAN PRODUCT NO (LIHAT DI SHEET Machines)'],
            'F2' => ['ISI DENGAN MOLD ID, JIKA ITEM MEMPUNYAI MOLD LEBIH DARI 1 (LIHAT DI SHEET Machines)'],
            // 'G2' => ['ISI DENGAN ANGKA'],
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('PRODUCTION SCHEDULES PRESS');
        $templateSheet->mergeCells('A1:F1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(20);
        $templateSheet->getColumnDimension('C')->setWidth(20);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(20);
        $templateSheet->getColumnDimension('F')->setWidth(30);
        $templateSheet->getColumnDimension('G')->setWidth(30);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD PRODUCTION SCHEDULES PRESS');
        $templateSheet->setCellValue('A2', 'NO');
        $templateSheet->setCellValue('B2', 'PERIOD');
        $templateSheet->setCellValue('C2', 'MACHINE');
        $templateSheet->setCellValue('D2', 'WP DATE');
        $templateSheet->setCellValue('E2', 'PRODUCT NO');
        $templateSheet->setCellValue('F2', 'MOLD ID');
        // $templateSheet->setCellValue('G2', 'PLANNING PCS/DAY');
        $templateSheet->getStyle('A2:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A2')->getFont()->setBold(true);
        $templateSheet->getStyle('B2:F2')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A2:F2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $templateSheet->getStyle('D:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);
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
        $item_refSheet->setTitle('Machines');

        $this->db->select('a.mold_id, b.number as machine_number, c.number as item_fg_number, c.name as item_fg_name, d.cavity_standard');
        $this->db->from('setting_molds a');
        $this->db->join('machines b', 'a.machine_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('molds d', 'a.mold_id = d.id');

        $this->db->order_by("
        CAST(
            SUBSTRING_INDEX(b.number, ' ', -1) 
            AS UNSIGNED
        )
        ", "asc", false);

        $this->db->order_by("
        SUBSTRING(
            SUBSTRING_INDEX(b.number, ' ', -1),
            LENGTH(CAST(SUBSTRING_INDEX(b.number, ' ', -1) AS UNSIGNED)) + 1
        )
        ", "asc", false);

        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(5);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(25);
        $item_refSheet->getColumnDimension('D')->setWidth(30);
        $item_refSheet->getColumnDimension('E')->setWidth(25);
        $item_refSheet->getColumnDimension('F')->setWidth(25);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Machine No');
        $item_refSheet->setCellValue('C1', 'Product No');
        $item_refSheet->setCellValue('D1', 'Product Name');
        $item_refSheet->setCellValue('E1', 'Mold ID');
        $item_refSheet->setCellValue('F1', 'Cavity Standard');
        $item_refSheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:F1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['machine_number']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['item_fg_number']);
            $item_refSheet->setCellValue('D' . $rowItem_ref, $itemref['item_fg_name']);
            $item_refSheet->setCellValue('E' . $rowItem_ref, $itemref['mold_id']);
            $item_refSheet->setCellValue('F' . $rowItem_ref, $itemref['cavity_standard']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('H' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('I' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':F' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':F' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        $spreadsheet->setActiveSheetIndex(0); 

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_prod_sch_press.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
