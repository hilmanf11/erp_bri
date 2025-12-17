<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Menu_loadings extends CI_Controller
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
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/menu_loadings');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('menu_loadings', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATA
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->query("SELECT id as item_fg_id, number as item_fg_number, name as item_fg_name, item_family_number FROM item_fg WHERE (number LIKE '%$post%' OR name LIKE '%$post%' OR id LIKE '%$post%') AND status = 0");

        echo json_encode($send);
    }

    //GET DATA
    // public function readItemMachines($machine_id)
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";

    //     $send = $this->crud->query("
    //         SELECT 
    //             id as item_fg_id, 
    //             number as item_fg_number, 
    //             name as item_fg_name, 
    //             item_family_number 
    //         FROM item_fg
    //         WHERE (number LIKE '%$post%' OR name LIKE '%$post%' OR id LIKE '%$post%') 
    //         AND status = 0
    //     ");

    //     echo json_encode($send);
    // }


    public function readItemMachines($machine_id)
    {
        $machine_id = base64_decode($machine_id);
        $post = $this->input->post('q') ?? "";

        $send = $this->crud->query("
            SELECT 
                fg.id AS item_fg_id,
                fg.number AS item_fg_number,
                fg.name AS item_fg_name,
                fg.item_family_number,

                CASE 
                    WHEN fg.item_family_number = 'CD' THEN (
                        SELECT snm.cycle_time
                        FROM setting_non_molds snm
                        WHERE snm.item_fg_id = fg.id
                        AND snm.machine_id = '$machine_id'
                        LIMIT 1
                    )
                    ELSE (
                        SELECT sm.cycle_time
                        FROM setting_molds sm
                        WHERE sm.item_fg_id = fg.id
                        AND sm.machine_id = '$machine_id'
                        LIMIT 1
                    )
                END AS cycle_time

            FROM item_fg fg
            WHERE fg.status = 0
            AND (
                fg.number LIKE '%$post%' 
                OR fg.name LIKE '%$post%' 
                OR fg.id LIKE '%$post%'
            )
            AND (
                (fg.item_family_number = 'CD' 
                    AND EXISTS (
                        SELECT 1 
                        FROM setting_non_molds snm
                        WHERE snm.item_fg_id = fg.id
                        AND snm.machine_id = '$machine_id'
                    )
                )
                OR
                (fg.item_family_number <> 'CD'
                    AND EXISTS (
                        SELECT 1
                        FROM setting_molds sm
                        WHERE sm.item_fg_id = fg.id
                        AND sm.machine_id = '$machine_id'
                    )
                )
            )
            ORDER BY fg.number
        ");

        echo json_encode($send);
    }

    //GET DATA
    public function readSettingMolds($item_fg, $machine_id)
    {
        $item_fg_id = base64_decode($item_fg);
        $machine_id = base64_decode($machine_id);

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->query("
            SELECT * FROM setting_molds
            WHERE item_fg_id = '$item_fg_id'
            AND machine_id = '$machine_id'
            AND mold_id LIKE '%$post%' 
            GROUP BY mold_id
        ");

        echo json_encode($send);
    }

    // public function readMachines($item_fg, $item_family_number)
    // {
    //     $item_fg_id = base64_decode($item_fg);
    //     $item_family_number = base64_decode($item_family_number);

    //     $post = isset($_POST['q']) ? $_POST['q'] : "";

    //     if($item_family_number == "CD") {
    //         $send = $this->crud->query("
    //             SELECT 
    //                 sm.*, 
    //                 m.number
    //             FROM setting_non_molds sm
    //             JOIN machines m ON sm.machine_id = m.id
    //             WHERE sm.item_fg_id = '$item_fg_id'
    //             AND m.number LIKE '%$post%'
    //         ");
    //     } else {
    //         $send = $this->crud->query("
    //             SELECT 
    //                 sm.*, 
    //                 m.number
    //             FROM setting_molds sm
    //             JOIN machines m ON sm.machine_id = m.id
    //             WHERE sm.item_fg_id = '$item_fg_id'
    //             AND m.number LIKE '%$post%'
    //         ");
    //     }

    //     echo json_encode($send);
    // }


    public function readMachines()
    {
        $post = $this->input->post('q');

        $send = $this->crud->query("
            SELECT *
            FROM (
                SELECT 
                    sm.item_fg_id,
                    sm.machine_id,
                    fg.item_family_number,
                    m.number,
                    m.toonage,
                    tp.name as type_process_name,
                    0 AS priority
                FROM setting_non_molds sm
                JOIN item_fg fg ON sm.item_fg_id = fg.id
                JOIN machines m ON sm.machine_id = m.id
                JOIN type_process tp ON m.type_process_id = tp.id
                WHERE m.number LIKE '%$post%'

                UNION

                SELECT 
                    sm.item_fg_id,
                    sm.machine_id,
                    fg.item_family_number,
                    m.number,
                    m.toonage,
                    tp.name as type_process_name,
                    1 AS priority
                FROM setting_molds sm
                JOIN item_fg fg ON sm.item_fg_id = fg.id
                JOIN machines m ON sm.machine_id = m.id
                JOIN type_process tp ON m.type_process_id = tp.id
                WHERE m.number LIKE '%$post%'
            ) x
            GROUP BY machine_id
            ORDER BY
                priority ASC,
                CAST(SUBSTRING_INDEX(number, ' ', -1) AS UNSIGNED) ASC,
                SUBSTRING(
                    SUBSTRING_INDEX(number, ' ', -1),
                    LENGTH(CAST(SUBSTRING_INDEX(number, ' ', -1) AS UNSIGNED)) + 1
                ) ASC
        ");

        echo json_encode($send);
    }


    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, c.toonage as machine_toonage, d.model as mold_model, d.cavity_actual as mold_cavity_actual, d.cavity_standard as mold_cavity_standard, b.item_family_number');
            $this->db->from('menu_loadings a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('machines c', 'a.machine_id = c.id');
            $this->db->join('molds d', 'a.mold_id = d.id', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_fg_id"){
                        $this->db->like("b.id", $filter->value);
                    }elseif($filter->field == "item_fg_number"){
                        $this->db->like("b.number", $filter->value);
                    }elseif($filter->field == "item_fg_name"){
                        $this->db->like("b.name", $filter->value);
                    }elseif($filter->field == "machine_number"){
                        $this->db->like("c.number", $filter->value);
                    }elseif($filter->field == "machine_toonage"){
                        $this->db->like("c.toonage", $filter->value);
                    }elseif($filter->field == "mold_model"){
                        $this->db->like("d.model", $filter->value);
                    }elseif($filter->field == "mold_cavity_actual"){
                        $this->db->like("d.cavity_actual", $filter->value);
                    }elseif($filter->field == "mold_cavity_standard"){
                        $this->db->like("d.cavity_standard", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
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
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();

            if (!isset($post['mold_id']) || $post['mold_id'] === '' || $post['mold_id'] === 'null') {
                $post['mold_id'] = null;
            }

            $menu_loading = $this->crud->read('menu_loadings', [], [
                "item_fg_id" => $post['item_fg_id'],
                "machine_id" => $post['machine_id']
            ]);

            $machines = $this->crud->read('machines', [], ["id" => $post['machine_id']]);
            $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_fg_id']]);

            if(!empty($menu_loading)) {
               echo json_encode(
                    array(
                    "title" => "Duplicated", 
                    "message" => "Product No. " . $item_fg->number ." on Machine No. " . $machines->number . " already exists.", 
                    "theme" => "error"
               ));
            } else {
                $send   = $this->crud->create('menu_loadings', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    // public function update()
    // {
    //     if (!$this->input->post()) {
    //         show_error("Cannot Process your request");
    //     }

    //     $id   = base64_decode($this->input->get('id'));
    //     $post = $this->input->post();

    //     $existing_data = $this->crud->read('menu_loadings', [], ["id" => $id]);

    //     $oldMold = $existing_data->mold_id ?? '';
    //     $newMold = $post['mold_id'] ?? '';

    //     $isChanged =
    //         ($existing_data->item_fg_id != $post['item_fg_id']) ||
    //         ($existing_data->machine_id != $post['machine_id']) ||
    //         ($oldMold != $newMold);


    //     if ($isChanged) {

    //         $used = $this->crud->read('production_capacities', [], [
    //             "item_fg_id" => $existing_data->item_fg_id,
    //             "machine_id" => $existing_data->machine_id,
    //             "mold_id"    => $existing_data->mold_id,
    //         ]);

    //         if (!empty($used)) {
    //             echo json_encode([
    //                 "title"   => "Invalid Update",
    //                 "message" => "Product No on Machine No with MOLD ID data have been used in Production Capacity",
    //                 "theme"   => "error"
    //             ]);
    //             return;
    //         }
    //     }

    //     // $duplicate = $this->crud->read('menu_loadings', [], [
    //     //     "item_fg_id" => $post['item_fg_id'],
    //     //     "machine_id" => $post['machine_id'],
    //     //     // "id !="      => $id
    //     // ]);

    //     // $this->db->where('item_fg_id', $post['item_fg_id']);
    //     // $this->db->where('machine_id', $post['machine_id']);
    //     // $this->db->where('id', $id);
    //     // $this->db->query('menu_loadings');

    //     $this->db->where('item_fg_id', $post['item_fg_id']);
    //     $this->db->where('machine_id', $post['machine_id']);
    //     $this->db->where('id !=', $id);

    //     $query = $this->db->get('menu_loadings');
    //     $duplicate = $query->row();


    //     if (!empty($duplicate)) {
    //         $machine = $this->crud->read('machines', [], ["id" => $post['machine_id']]);
    //         $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_fg_id']]);

    //         echo json_encode([
    //             "title"   => "Duplicated",
    //             "message" => "Product No. {$item_fg->number} on Machine No. {$machine->number} already exists.",
    //             "theme"   => "error"
    //         ]);
    //         return;
    //     }

    //     if ($existing_data && $post['mold_id'] != null) {

    //         // UPDATE CYCLE TIME MENU LOADING
    //         // $this->crud->update('menu_loadings', [
    //         //     "id" => $id,
    //         // ], [
    //         //     "cycle_time" => $post['cycle_time']
    //         // ]);

    //         $pc = $this->crud->read("production_capacities", [], [
    //             "machine_id" => $post['machine_id'],
    //             "item_fg_id" => $post['item_fg_id'],
    //         ]);

    //         $mold = $this->crud->read("molds", [], [
    //             "id" => $post['mold_id'],
    //         ]);

    //         if ($pc && $mold) {

    //             // HITUNG ULANG KAPASITAS
    //             $cycle         = (float)$post['cycle_time'];
    //             $productivity  = (float)$post['productcivity'];
    //             $shift_hour    = (int)$post['shift_hour'];
    //             $shift         = (int)$post['shift'];
    //             $actual_cavity = (int)$mold->cavity_actual;

    //             $capacity_hour  = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
    //             $capacity_shift = ceil($capacity_hour * $shift_hour);
    //             $capacity_day   = ceil($capacity_shift * $shift);

    //             $this->crud->update("production_capacities", [
    //                 "machine_id" => $post['machine_id'],
    //                 "item_fg_id" => $post['item_fg_id'],
    //             ], [
    //                 "capacity_hour"  => $capacity_hour,
    //                 "capacity_shift" => $capacity_shift,
    //                 "capacity_day"   => $capacity_day,
    //             ]);
    //         }
    //     }

    //     $send = $this->crud->update('menu_loadings', ["id" => $id], $post);
    //     echo $send;
    // }

    public function update()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $id   = base64_decode($this->input->get('id'));
        $post = $this->input->post();

        if (empty($post['mold_id'])) {
            $post['mold_id'] = null;
        }

        $existing = $this->crud->read('menu_loadings', [], ["id" => $id]);
        if (!$existing) {
            show_error("Data not found");
        }

        $oldMold = $existing->mold_id;
        $newMold = $post['mold_id'] ?? null;

        $useMold = !empty($newMold);

        $isChanged =
            $existing->item_fg_id != $post['item_fg_id'] ||
            $existing->machine_id != $post['machine_id'] ||
            ($useMold && $oldMold != $newMold);

        if ($isChanged) {

            $used = $this->crud->read('production_capacities', [], [
                "item_fg_id" => $existing->item_fg_id,
                "machine_id" => $existing->machine_id,
            ]);

            if (!empty($used)) {
                echo json_encode([
                    "title"   => "Invalid Update",
                    "message" => "Data already used in Production Capacity",
                    "theme"   => "error"
                ]);
                return;
            }
        }

        $this->db->where('item_fg_id', $post['item_fg_id']);
        $this->db->where('machine_id', $post['machine_id']);
        $this->db->where('id !=', $id);

        if ($useMold) {
            $this->db->where('mold_id', $newMold);
        } else {
            $this->db->where('mold_id IS NULL', null, false);
        }

        $duplicate = $this->db->get('menu_loadings')->row();

        if ($duplicate) {
            $machine = $this->crud->read('machines', [], ["id" => $post['machine_id']]);
            $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_fg_id']]);

            echo json_encode([
                "title"   => "Duplicated",
                "message" => "Product No. {$item_fg->number} on Machine No. {$machine->number} already exists.",
                "theme"   => "error"
            ]);
            return;
        }

        $pc = $this->crud->read("production_capacities", [], [
            "machine_id" => $post['machine_id'],
            "item_fg_id" => $post['item_fg_id'],
        ]);

        $mold = $this->crud->read("molds", [], ["id" => $newMold]);

        if ($pc && $mold) {

            $cycle         = (float)$post['cycle_time'];
            $productivity  = (float)$post['productcivity'];
            $shift_hour    = (int)$post['shift_hour'];
            $shift         = (int)$post['shift'];
            $actual_cavity = (int)$mold->cavity_actual;

            $capacity_hour  = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
            $capacity_shift = ceil($capacity_hour * $shift_hour);
            $capacity_day   = ceil($capacity_shift * $shift);

            $this->crud->update("production_capacities", [
                "machine_id" => $post['machine_id'],
                "item_fg_id" => $post['item_fg_id'],
                // "mold_id"    => $newMold,
            ], [
                "capacity_hour"  => $capacity_hour,
                "capacity_shift" => $capacity_shift,
                "capacity_day"   => $capacity_day,
            ]);

        } else if($pc) {

            $cycle         = (float)$post['cycle_time'];
            $productivity  = (float)$post['productcivity'];
            $shift_hour    = (int)$post['shift_hour'];
            $shift         = (int)$post['shift'];

            $capacity_hour  = ceil((3600 / $cycle) * ($productivity / 100));
            $capacity_shift = ceil($capacity_hour * $shift_hour);
            $capacity_day   = ceil($capacity_shift * $shift);

            $this->crud->update("production_capacities", [
                "machine_id" => $post['machine_id'],
                "item_fg_id" => $post['item_fg_id'],
                // "mold_id"    => $newMold,
            ], [
                "capacity_hour"  => $capacity_hour,
                "capacity_shift" => $capacity_shift,
                "capacity_day"   => $capacity_day,
            ]);
        }

        $send = $this->crud->update('menu_loadings', ["id" => $id], $post);
        echo $send;
    }


    //DELETE DATA
    // public function delete()
    // {
    //     $data = $this->input->post();
    //     $send = $this->crud->delete('menu_loadings', $data);
    //     echo $send;
    // }

    public function delete()
    {
        $data = $this->input->post();

        if (empty($data['id'])) {
            echo json_encode(
                array(
                "title" => "Error", 
                "message" => "Invalid request", 
                "theme" => "error"
            ));
            return;
        }

        $menu = $this->crud->read('menu_loadings', [], ['id' => $data['id']]);

        if (empty($menu)) {
            echo json_encode(
                array(
                "title" => "Data Not Found", 
                "message" => "Menu Loading data not found", 
                "theme" => "error"
            ));
            return;
        }

        $used = $this->db
            ->where('item_fg_id', $menu->item_fg_id)
            ->where('machine_id', $menu->machine_id)
            ->where('deleted', 0)
            ->limit(1)
            ->get('production_capacities')
            ->num_rows();

        if ($used > 0) {
            echo json_encode(
                array(
                "title" => "Cannot Delete Data", 
                "message" => "Cannot delete data that is still in use",
                "theme" => "error"
            ));
            return;
        }

        $send = $this->crud->delete('menu_loadings', $data);
        echo $send;
    }

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

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'item_fg_id' => $data->val($i, 2),
                'mold_id' => $data->val($i, 3),
                'machine_id' => $data->val($i, 4),
                'shift' => $data->val($i, 5),
                'shift_hour' => $data->val($i, 6),
                'productcivity' => $data->val($i, 7),
                // 'cycle_time' => $data->val($i, 8),
                'manpower' => $data->val($i, 8),
                'runner' => $data->val($i, 9),
                'priority' => $data->val($i, 10),
                // 'remarks' => $data->val($i, 12),
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
        @unlink('failed/menu_loadings.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/menu_loadings.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_menu_loadings_" . date("Ymd_s") . ".xls";

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

            foreach ($data_list as $index => $data) {
                $processed_count++;
                if (
                    empty($data['item_fg_id']) ||
                    empty($data['machine_id']) ||
                    empty($data['shift']) ||
                    empty($data['shift_hour']) ||
                    empty($data['productcivity']) ||
                    empty($data['manpower']) ||
                    $data['priority'] == "" || $data['priority'] == null ||
                    !is_numeric($data['shift']) ||
                    !is_numeric($data['shift_hour']) ||
                    !is_numeric($data['productcivity'])
                   ) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Invalid or missing data"
                        ];
                        continue;
                }

                $item_fg_id = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
                if (empty($item_fg_id)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product No " . $data['item_fg_id'] . " Not Found"
                    ];
                    continue;
                }

                $molds = $this->crud->read('molds', [], ["id" => $data['mold_id']]);
                if (empty($molds) && $item_fg_id->item_family_number != "CD") {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Mold Model " . $data['mold_id'] . " Not Found"
                    ];
                    continue;
                }

                $machine = $this->crud->read('machines', [], ["id" => $data['machine_id']]);
                if (empty($machine)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Machine ID " . $data['machine_id'] . " Not Found"
                    ];
                    continue;
                }

                $cycle_time = "";

                if($item_fg_id->item_family_number != "CD") {

                    $setting_mold = $this->crud->read('setting_molds', [], [
                        "item_fg_id" => $data['item_fg_id'],
                        "machine_id" => $data['machine_id'],
                        "mold_id" => $data['mold_id'],
                    ]);

                    if(empty($setting_mold) && $item_fg_id->item_family_number != "CD") {
                        $results[] = [
                            "status"  => "failed",
                            "item"    => "Line " . ($index + 1),
                            "message" => "Setting Mold for Product ID " . $data['item_fg_id'] . " on Machine No " . $data['machine_id'] . " Mold ID ". $data['mold_id'] ." Not Found "
                        ];
                        continue;
                    }

                    $cycle_time = $setting_mold->cycle_time;

                    $menu = $this->crud->read('menu_loadings', [
                        'item_fg_id' => $data['item_fg_id'],
                        'machine_id' => $data['machine_id'],
                        'mold_id'    => $data['mold_id'],
                    ]);

                    if ($menu) {

                        $pc = $this->crud->read("production_capacities", [], [
                            "machine_id" => $data['machine_id'],
                            "item_fg_id" => $data['item_fg_id'],
                        ]);

                        if ($pc && $molds) {

                            $cycle         = (float)$cycle_time;
                            $productivity  = (float)$data['productcivity'];
                            $shift_hour    = (int)$data['shift_hour'];
                            $shift         = (int)$data['shift'];
                            $actual_cavity = (int)$molds->cavity_actual;

                            $capacity_hour  = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
                            $capacity_shift = ceil($capacity_hour * $shift_hour);
                            $capacity_day   = ceil($capacity_shift * $shift);

                            $this->crud->update("production_capacities", [
                                "machine_id" => $data['machine_id'],
                                "item_fg_id" => $data['item_fg_id'],
                            ], [
                                "capacity_hour"  => $capacity_hour,
                                "capacity_shift" => $capacity_shift,
                                "capacity_day"   => $capacity_day,
                            ]);
                        }
                    }

                } else if($item_fg_id->item_family_number == "CD") {

                    $setting_non_mold = $this->crud->read('setting_non_molds', [], [
                        "item_fg_id" => $data['item_fg_id'],
                        "machine_id" => $data['machine_id'],
                    ]);
                    if(empty($setting_non_mold)) {
                        $results[] = [
                            "status"  => "failes",
                            "item"    => "Line " . ($index + 1),
                            "message" => "Setting Non Mold for Product ID " . $data['item_fg_id'] . " on Machine No " . $data['machine_id'] . " Not Found "
                        ];
                        continue;
                    }

                    $cycle_time = $setting_non_mold->cycle_time;


                    $menu = $this->crud->read('menu_loadings', [
                        'item_fg_id' => $data['item_fg_id'],
                        'machine_id' => $data['machine_id'],
                    ]);

                    if ($menu) {

                        $pc = $this->crud->read("production_capacities", [], [
                            "machine_id" => $data['machine_id'],
                            "item_fg_id" => $data['item_fg_id'],
                        ]);

                        if ($pc) {

                            $cycle         = (float)$cycle_time;
                            $productivity  = (float)$data['productcivity'];
                            $shift_hour    = (int)$data['shift_hour'];
                            $shift         = (int)$data['shift'];

                            $capacity_hour  = ceil((3600 / $cycle) * ($productivity / 100));
                            $capacity_shift = ceil($capacity_hour * $shift_hour);
                            $capacity_day   = ceil($capacity_shift * $shift);

                            $this->crud->update("production_capacities", [
                                "machine_id" => $data['machine_id'],
                                "item_fg_id" => $data['item_fg_id'],
                            ], [
                                "capacity_hour"  => $capacity_hour,
                                "capacity_shift" => $capacity_shift,
                                "capacity_day"   => $capacity_day,
                            ]);
                        }
                    }

                }

                $rubberOrCD = $item_fg_id->item_family_number != "CD" ? $data['mold_id'] : null;

                $checkMenuLoading = $this->crud->read('menu_loadings', [], [
                    "item_fg_id" => $data['item_fg_id'],
                    "mold_id" => $rubberOrCD,
                    "machine_id" => $data['machine_id'],
                ]);

                // if (!empty($checkMenuLoading)) {
                //     $results[] = [
                //         "status" => "failed",
                //         "item" => "Line " . ($index + 1), 
                //         "message" => "Duplicate Data Product No. " . $item_fg_id->number ." on Machine No. " . $machine->number . " already exists",
                //     ];
                //     continue;
                // }

                $dataFinal = array(
                    //field
                    "item_fg_id" => $data['item_fg_id'],
                    "mold_id" => $rubberOrCD,
                    "machine_id" => $data['machine_id'],
                    "shift" => $data['shift'],
                    "shift_hour" => $data['shift_hour'],
                    "productcivity" => $data['productcivity'],
                    "cycle_time" => $cycle_time,
                    "manpower" => $data['manpower'],
                    "runner" => $data['runner'],
                    "priority" => $data['priority'],
                );

                try {
                    if (!empty($checkMenuLoading)) {
                        // Update
                        $this->crud->update('menu_loadings', [
                            "item_fg_id" => $data['item_fg_id'],
                            "mold_id" => $rubberOrCD,
                            "machine_id" => $data['machine_id']
                        ], [
                            "shift" => $data['shift'],
                            "shift_hour" => $data['shift_hour'],
                            "productcivity" => $data['productcivity'],
                            "cycle_time" => $cycle_time,
                            "manpower" => $data['manpower'],
                            "runner" => $data['runner'],
                            "priority" => $data['priority'],
                        ]);

                        $status = "update";
                    } else {
                        // Insert
                        $this->crud->create('menu_loadings', $dataFinal);

                        $status = "insert";
                    }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "Product No $item_fg_id->number Data Updated");

                    $results[] = [
                        "status" => "success",
                        "item" => $res_item,
                        "message" => $res_msg
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        "status" => "failed",
                        "item" => $item_fg_id->name,
                        "message" => $e->getMessage()
                    ];
                    continue;
                }
            }

            $failed = array_filter($results, fn($r) => $r['status'] === 'failed');
            $hasDbError = ($this->db->trans_status() === FALSE);

            if (count($failed) > 0 || $hasDbError) {
                $filePath = 'failed/menu_loadings.xls';

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
                @unlink('failed/menu_loadings.xls');

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
            header("Content-Disposition: attachment; filename=menu_loadings_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, c.toonage as machine_toonage, d.model as mold_model, d.cavity_actual as mold_cavity_actual, d.cavity_standard as mold_cavity_standard');
        $this->db->from('menu_loadings a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('machines c', 'a.machine_id = c.id');
        $this->db->join('molds d', 'a.mold_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#menu_loadings {border-collapse: collapse;width: 100%;font-size: 12px;}#menu_loadings td, #menu_loadings th {border: 1px solid #ddd;padding: 2px;}#menu_loadings tr:nth-child(even){background-color: #f2f2f2;}#menu_loadings tr:hover {background-color: #ddd;}#menu_loadings th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MENU LOADING</h3>
            </div>
        </center>
        
        <table id="menu_loadings" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Machine No.</th>
                <th>Toonage of Machine</th>
                <th>Mold ID</th>
                <th>Cavity Actual</th>
                <th>Cavity Standard</th>
                <th>Shift</th>
                <th>Hour/Shift</th>
                <th>Productivity Factor</th>
                <th>Cycle Time (Second)</th>
                <th>Man Power</th>
                <th>Runner/Shoot</th>
                <th>Priority</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['machine_toonage'] . '</td>
                    <td>' . $data['mold_id'] . '</td>
                    <td>' . $data['mold_cavity_actual'] . '</td>
                    <td>' . $data['mold_cavity_standard'] . '</td>
                    <td>' . $data['shift'] . '</td>
                    <td>' . $data['shift_hour'] . '</td>
                    <td>' . $data['productcivity'] . '</td>
                    <td>' . $data['cycle_time'] . '</td>
                    <td>' . $data['manpower'] . '</td>
                    <td>' . $data['runner'] . '</td>
                    <td>' . $data['priority'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
