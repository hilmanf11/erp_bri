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
    public function readSettingMolds($item_fg)
    {
        $item_fg_id = base64_decode($item_fg);
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->query("SELECT * FROM setting_molds WHERE item_fg_id = '$item_fg_id' AND mold_id LIKE '%$post%' GROUP BY mold_id");

        echo json_encode($send);
    }

    public function readMachines($item_fg, $item_family_number)
    {
        $item_fg_id = base64_decode($item_fg);
        $item_family_number = base64_decode($item_family_number);

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        if($item_family_number == "CD") {
            $send = $this->crud->query("
                SELECT 
                    sm.*, 
                    m.number
                FROM setting_non_molds sm
                JOIN machines m ON sm.machine_id = m.id
                WHERE sm.item_fg_id = '$item_fg_id'
                AND m.number LIKE '%$post%'
            ");
        } else {
            $send = $this->crud->query("
                SELECT 
                    sm.*, 
                    m.number
                FROM setting_molds sm
                JOIN machines m ON sm.machine_id = m.id
                WHERE sm.item_fg_id = '$item_fg_id'
                AND m.number LIKE '%$post%'
            ");
        }

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
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, c.toonage as machine_toonage, d.model as mold_model, d.cavity_actual as mold_cavity_actual, d.cavity_standard as mold_cavity_standard');
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
            $send   = $this->crud->create('menu_loadings', $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('menu_loadings', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
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
                'cycle_time' => $data->val($i, 8),
                'manpower' => $data->val($i, 9),
                'runner' => $data->val($i, 10),
                'priority' => $data->val($i, 11),
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
                    empty($data['cycle_time']) ||
                    empty($data['manpower']) ||
                    $data['priority'] == "" || $data['priority'] == null ||
                    !is_numeric($data['shift']) ||
                    !is_numeric($data['shift_hour']) ||
                    !is_numeric($data['productcivity']) ||
                    !is_numeric($data['cycle_time'])
                   ) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Invalid or missing data"
                        ];
                        continue;
                }

                // $line = $index + 1;

                // if (empty($data['item_fg_id'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "item_fg_id kosong"
                //     ];
                //     continue;
                // }

                // if (empty($data['machine_id'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "machine_id kosong"
                //     ];
                //     continue;
                // }

                // if (empty($data['shift'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "shift kosong"
                //     ];
                //     continue;
                // }

                // if (!is_numeric($data['shift'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "shift harus angka"
                //     ];
                //     continue;
                // }

                // if (empty($data['shift_hour'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "shift_hour kosong"
                //     ];
                //     continue;
                // }

                // if (!is_numeric($data['shift_hour'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "shift_hour harus angka"
                //     ];
                //     continue;
                // }

                // if (empty($data['productcivity'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "productcivity kosong"
                //     ];
                //     continue;
                // }

                // if (!is_numeric($data['productcivity'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "productcivity harus angka"
                //     ];
                //     continue;
                // }

                // if (empty($data['cycle_time'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "cycle_time kosong"
                //     ];
                //     continue;
                // }

                // if (!is_numeric($data['cycle_time'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "cycle_time harus angka"
                //     ];
                //     continue;
                // }

                // if (empty($data['manpower'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "manpower kosong"
                //     ];
                //     continue;
                // }

                // if (empty($data['priority'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line $line",
                //         "message" => "priority kosong"
                //     ];
                //     continue;
                // }


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

                $rubberOrCD = $item_fg_id->item_family_number != "CD" ? $data['mold_id'] : null;

                $checkMenuLoading = $this->crud->read('menu_loadings', [], [
                    "item_fg_id" => $data['item_fg_id'],
                    "mold_id" => $rubberOrCD,
                    "machine_id" => $data['machine_id'],
                ]);

                $dataFinal = array(
                    //field
                    "item_fg_id" => $data['item_fg_id'],
                    "mold_id" => $rubberOrCD,
                    "machine_id" => $machine->id,
                    "shift" => $data['shift'],
                    "shift_hour" => $data['shift_hour'],
                    "productcivity" => $data['productcivity'],
                    "cycle_time" => $data['cycle_time'],
                    // "cycle_time_process" => $data['cycle_time_process'],
                    "manpower" => $data['manpower'],
                    "runner" => $data['runner'],
                    "priority" => $data['priority'],
                    // "remarks" => $data['remarks'],
                );

                try {
                    if (!empty($checkMenuLoading)) {
                        // Update
                        $this->db->update('menu_loadings', [
                            "shift" => $data['shift'],
                            "shift_hour" => $data['shift_hour'],
                            "productcivity" => $data['productcivity'],
                            "cycle_time" => $data['cycle_time'],
                            // "cycle_time_process" => $data['cycle_time_process'],
                            "manpower" => $data['manpower'],
                            "runner" => $data['runner'],
                            "priority" => $data['priority'],
                            // "remarks" => $data['remarks'],
                        ], [
                            "item_fg_id" => $data['item_fg_id'],
                            "mold_id" => $rubberOrCD,
                            "machine_id" => $machine->id
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
