<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Molds extends CI_Controller
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
        // $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[molds.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/molds');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('molds', ["mold_name" => $post]);
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
            $this->db->select('a.*,c.name as customer_name');
            $this->db->from('molds a');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "customer_name") {
                        $this->db->like("c.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
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
    //AUTO ID
    public function autoid($type, $model)
    {
        $code = $type . $model;
        $sql = $this->db->query("SELECT coalesce(max(`id`),0) as kode From molds where id like '%$code%'");
        $row = $sql->row();
        $kode = substr($row->kode, -3);
        $autoid = "MD" . $code . "-" . sprintf("%03s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $send   = $this->crud->create('molds', $data);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function updatev1()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('molds', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $this->db->trans_begin();

        $id   = base64_decode($this->input->get('id'));
        $post = $this->input->post();

        $old = $this->crud->read('molds', [], ['id' => $id]);

        unset($post['id']);
        $this->crud->update('molds', ['id' => $id], $post);

        if ((int)$old->cavity_actual !== (int)$post['cavity_actual']) {

            $settings = $this->db->query("
                SELECT 
                    sm.item_fg_id,
                    sm.machine_id,
                    sm.mold_id,
                    sm.cycle_time
                FROM setting_molds sm
                WHERE sm.mold_id = '{$id}'
            ")->result();

            foreach ($settings as $sm) {

                if(empty($sm->cycle_time) || $sm->cycle_time == 0 || $sm->cycle_time == 0.00){
                    continue;
                }

                $menu = $this->crud->read('menu_loadings', [], [
                    'item_fg_id' => $sm->item_fg_id,
                    'machine_id' => $sm->machine_id,
                    'mold_id'    => $sm->mold_id
                ]);

                if (!$menu) {
                    continue;
                }

                // UPDATE CYCLE TIME MENU LOADING
                $this->crud->update('menu_loadings', [
                    'item_fg_id' => $sm->item_fg_id,
                    'machine_id' => $sm->machine_id,
                    'mold_id'    => $sm->mold_id
                ], [
                    'cycle_time' => $sm->cycle_time
                ]);

                $pc = $this->crud->read('production_capacities', [], [
                    'item_fg_id' => $sm->item_fg_id,
                    'machine_id' => $sm->machine_id
                ]);

                if (!$pc) {
                    continue;
                }

                $cycle         = (float)$sm->cycle_time;
                $productivity  = (float)$menu->productcivity;
                $shift_hour    = (int)$menu->shift_hour;
                $shift         = (int)$menu->shift;
                $actual_cavity = (int)$post['cavity_actual'];

                $capacity_hour  = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
                $capacity_shift = ceil($capacity_hour * $shift_hour);
                $capacity_day   = ceil($capacity_shift * $shift);

                // UPDATE PRODUCTION CAPACITIES
                $this->crud->update('production_capacities', [
                    'item_fg_id' => $sm->item_fg_id,
                    'machine_id' => $sm->machine_id
                ], [
                    'capacity_hour'  => $capacity_hour,
                    'capacity_shift' => $capacity_shift,
                    'capacity_day'   => $capacity_day
                ]);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                "theme"   => "error",
                "title"   => "Failed",
                "message" => "Update failed"
            ]);
        } else {
            $this->db->trans_commit();
            echo json_encode([
                "theme"   => "success",
                "title"   => "Success",
                "message" => "Data updated successfully"
            ]);
        }
    }

    //DELETE DATA
    // public function delete()
    // {
    //     $data = $this->input->post();
    //     $send = $this->crud->delete('molds', $data);
    //     echo $send;
    // }

    public function delete()
    {
        $data = $this->input->post();
        $mold = $this->crud->read('molds', [], ['id' => $data['id'], 'deleted' => 0]);

        if (empty($mold)) {
            echo json_encode(
                array(
                "title" => "Data Not Found", 
                "message" => "Mold data not found", 
                "theme" => "error"
            ));
            return;
        }

        $used = $this->db
            ->where('mold_id', $mold->id)
            ->limit(1)
            ->get('setting_molds')
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

        $send = $this->crud->delete('molds', $data);
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
                'mold_name' => $data->val($i, 2),
                'type' => $data->val($i, 3),
                'customer_number' => $data->val($i, 4),
                'project_year' => $data->val($i, 5),
                'total_mold' => $data->val($i, 6),
                'mold_no' => $data->val($i, 7),
                'mold_year' => $data->val($i, 8),
                'mold_size' => $data->val($i, 9),
                'cavity_standard' => $data->val($i, 10),
                'cavity_actual' => $data->val($i, 11),
                'shoot_standard' => $data->val($i, 12),
                'shoot_actual' => $data->val($i, 13),
                'model' => $data->val($i, 14),
                'mold_type' => $data->val($i, 15),
                'remark' => $data->val($i, 16),
                'status' => $data->val($i, 17)
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
        @unlink('failed/molds.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/molds.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_molds_" . date("Ymd_s") . ".xls";

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
    //         $data = $this->input->post('data');

    //         //Cek Process Number          //table       //field        //field excel
    //         // $molds = $this->crud->read('molds', [], ["number" => $data['number']]);
    //         // $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
    //         $customer = $this->crud->read('customers', [], ["number" => $data['customer_number']]);

    //         //AUTOID
    //         $code = $data['type'] . $data['model'];
    //         $sql = $this->db->query("SELECT coalesce(max(`id`),0) as kode From molds where id like '%$code%'");
    //         $row = $sql->row();
    //         $kode = substr($row->kode, -3);
    //         $autoid = "MD" . $code . "-" . sprintf("%03s", $kode + 1);


    //         if (empty($customer->number)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['customer_number'] . " Not Found", "theme" => "error"));
    //         } else {
    //             $dataFinal = array(
    //                 //field
    //                 "id" => $autoid,
    //                 "mold_name" => $data['mold_name'],
    //                 "type" => $data['type'],
    //                 "customer_id" => @$customer->id,
    //                 "model" => $data['model'],
    //                 "mold_size" => $data['mold_size'],
    //                 "project_year" => $data['project_year'],
    //                 "cavity_standard" => $data['cavity_standard'],
    //                 "cavity_actual" => $data['cavity_actual'],
    //                 "shoot_standard" => $data['shoot_standard'],
    //                 "shoot_actual" => $data['shoot_actual'],
    //                 "mold_type" => $data['mold_type'],
    //                 "remark" => $data['remark'],
    //                 "status" => $data['status'],
    //                 "total_mold" => $data['total_mold'],
    //                 "mold_no" => $data['mold_no'],
    //                 "mold_year" => $data['mold_year'],
    //                 "mold_size" => $data['mold_size'],
    //             );
    //             $send   = $this->crud->create('molds', $dataFinal);
    //             echo $send;
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

            foreach ($data_list as $index => $data) {
                $processed_count++;
                if (
                    empty($data['mold_name']) ||
                    empty($data['type']) ||
                    empty($data['customer_number'])
                   ) {
                        $results[] = [
                            "status" => "failed",
                            "item" => "Line " . ($index + 1),
                            "message" => "Invalid or missing data"
                        ];
                        continue;
                }

                $customer = $this->crud->read('customers', [], ["number" => $data['customer_number']]);
                if (empty($customer)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Customer " . $data['customer_number'] . " Not Found"
                    ];
                    continue;
                }

                $checkMold = $this->crud->read('molds', [], [
                    "mold_name" => $data['mold_name'],
                    "type" => $data['type'],
                    "customer_id" => $customer->id,
                ]);

                //AUTOID
                $code = $data['type'] . $data['model'];
                $sql = $this->db->query("SELECT coalesce(max(`id`),0) as kode From molds where id like '%$code%'");
                $row = $sql->row();
                $kode = substr($row->kode, -3);
                $autoid = "MD" . $code . "-" . sprintf("%03s", $kode + 1);

                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "mold_name" => $data['mold_name'],
                    "type" => $data['type'],
                    "customer_id" => $customer->id,
                    "model" => $data['model'],
                    "mold_size" => $data['mold_size'],
                    "project_year" => $data['project_year'],
                    "cavity_standard" => $data['cavity_standard'],
                    "cavity_actual" => $data['cavity_actual'],
                    "shoot_standard" => $data['shoot_standard'],
                    "shoot_actual" => $data['shoot_actual'],
                    "mold_type" => $data['mold_type'],
                    "remark" => $data['remark'],
                    "status" => $data['status'],
                    "total_mold" => $data['total_mold'],
                    "mold_no" => $data['mold_no'],
                    "mold_year" => $data['mold_year'],
                    "mold_size" => $data['mold_size'],
                );

                try {
                    if (!empty($checkMold)) {

                        $old = $this->crud->read('molds', [], [
                            "mold_name" => $data['mold_name'],
                            "type" => $data['type'],
                            "customer_id" => $customer->id,
                        ]);

                        // Update
                        $this->db->update('molds', [
                            "model" => $data['model'],
                            "mold_size" => $data['mold_size'],
                            "project_year" => $data['project_year'],
                            "cavity_standard" => $data['cavity_standard'],
                            "cavity_actual" => $data['cavity_actual'],
                            "shoot_standard" => $data['shoot_standard'],
                            "shoot_actual" => $data['shoot_actual'],
                            "mold_type" => $data['mold_type'],
                            "remark" => $data['remark'],
                            "status" => $data['status'],
                            "total_mold" => $data['total_mold'],
                            "mold_no" => $data['mold_no'],
                            "mold_year" => $data['mold_year'],
                            "mold_size" => $data['mold_size'],
                        ], [
                            "mold_name" => $data['mold_name'],
                            "type" => $data['type'],
                            "customer_id" => $customer->id,
                        ]);

                        if ((int)$old->cavity_actual !== (int)$data['cavity_actual']) {

                            $settings = $this->db->query("
                                SELECT 
                                    sm.item_fg_id,
                                    sm.machine_id,
                                    sm.mold_id,
                                    sm.cycle_time
                                FROM setting_molds sm
                                WHERE sm.mold_id = '{$old->id}'
                            ")->result();

                            foreach ($settings as $sm) {

                                if(empty($sm->cycle_time) || $sm->cycle_time == 0 || $sm->cycle_time == 0.00){
                                    continue;
                                }

                                $menu = $this->crud->read('menu_loadings', [], [
                                    'item_fg_id' => $sm->item_fg_id,
                                    'machine_id' => $sm->machine_id,
                                    'mold_id'    => $sm->mold_id
                                ]);

                                if (!$menu) {
                                    continue;
                                }

                                // UPDATE CYCLE TIME MENU LOADING
                                $this->crud->update('menu_loadings', [
                                    'item_fg_id' => $sm->item_fg_id,
                                    'machine_id' => $sm->machine_id,
                                    'mold_id'    => $sm->mold_id
                                ], [
                                    'cycle_time' => $sm->cycle_time
                                ]);

                                $pc = $this->crud->read('production_capacities', [], [
                                    'item_fg_id' => $sm->item_fg_id,
                                    'machine_id' => $sm->machine_id
                                ]);

                                if (!$pc) {
                                    continue;
                                }

                                $cycle         = (float)$sm->cycle_time;
                                $productivity  = (float)$menu->productcivity;
                                $shift_hour    = (int)$menu->shift_hour;
                                $shift         = (int)$menu->shift;
                                $actual_cavity = (int)$data['cavity_actual'];

                                $capacity_hour  = ceil((3600 / $cycle) * $actual_cavity * ($productivity / 100));
                                $capacity_shift = ceil($capacity_hour * $shift_hour);
                                $capacity_day   = ceil($capacity_shift * $shift);

                                // UPDATE PRODUCTION CAPACITIES
                                $this->crud->update('production_capacities', [
                                    'item_fg_id' => $sm->item_fg_id,
                                    'machine_id' => $sm->machine_id
                                ], [
                                    'capacity_hour'  => $capacity_hour,
                                    'capacity_shift' => $capacity_shift,
                                    'capacity_day'   => $capacity_day
                                ]);
                            }
                        }

                        $status = "update";
                    } else {
                        // Insert
                        $this->crud->create('molds', $dataFinal);

                        $status = "insert";
                    }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "Data Updated Successfully");

                    $results[] = [
                        "status" => "success",
                        "item" => $res_item,
                        "message" => $res_msg
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        "status" => "failed",
                        "item" => $data['mold_name'],
                        "message" => $e->getMessage()
                    ];
                    continue;
                }
            }

            $failed = array_filter($results, fn($r) => $r['status'] === 'failed');
            $hasDbError = ($this->db->trans_status() === FALSE);

            if (count($failed) > 0 || $hasDbError) {
                $filePath = 'failed/setting_molds.xls';

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
                @unlink('failed/setting_molds.xls');

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
            header("Content-Disposition: attachment; filename=molds_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,c.name as customer_name');
        $this->db->from('molds a');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#molds {border-collapse: collapse;width: 100%;font-size: 12px;}#molds td, #molds th {border: 1px solid #ddd;padding: 2px;}#molds tr:nth-child(even){background-color: #f2f2f2;}#molds tr:hover {background-color: #ddd;}#molds th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM MOLD</h3>
            </div>
        </center>
        
        <table id="molds" border="1">
            <tr>
                <th width="20">No</th>
                <th>Mold ID</th>
                <th>Mold Name</th>
                <th>Property of</th>
                <th>Customer Name</th>
                <th>Project Year</th>
                <th>Total Mold</th>
                <th>Mold No</th>
                <th>Mold Year</th>
                <th>Mold Size</th>
                <th>Standard Curing Time</th>
                <th>Standard Cavity</th>
                <th>Actual Cavity</th>
                <th>Standard Shoot</th>
                <th>Actual Shoot</th>
                <th>Target Shoot (7 WH)</th>
                <th>Mold Type</th>
                <th>Remarks</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td style="mso-number-format:\'@\'; text-align: left;">' . $data['mold_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td>' . $data['project_year'] . '</td>
                    <td>' . $data['total_mold'] . '</td>
                    <td>' . $data['mold_no'] . '</td>
                    <td>' . $data['mold_year'] . '</td>
                    <td>' . $data['mold_size'] . '</td>
                    <td>' . $data['standard_curing_time'] . '</td>
                    <td>' . $data['cavity_standard'] . '</td>
                    <td>' . $data['cavity_actual'] . '</td>
                    <td>' . $data['shoot_standard'] . '</td>
                    <td>' . $data['shoot_actual'] . '</td>
                    <td>' . $data['target_shoot'] . '</td>
                    <td>' . $data['mold_type'] . '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
