<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class man_powers extends CI_Controller
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
        // $this->form_validation->set_rules('number', 'Lost Time Code', 'required|min_length[1]|max_length[20]|is_unique[man_powers.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/man_powers');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->query("
            SELECT DISTINCT name 
            FROM man_powers 
            WHERE name LIKE '%$post%' 
            AND status = 0
            ORDER BY name ASC
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
            $this->db->select('*');
            $this->db->from('man_powers a');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    // if ($filter->field == "subcont_type_name") {
                    //     $this->db->like("b.id", $filter->value);
                    // } elseif ($filter->field == "delivery_area_name") {
                    //     $this->db->like("c.id", $filter->value);
                    // } else {
                    // }
                    $this->db->like("a." . $filter->field, $filter->value);
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
    public function autoid(){
        $sql = $this->db->query("SELECT max(id) as kode FROM man_powers");
        $row = $sql->row();
        $kode = substr($row->kode,3);
        $autoid ="S". sprintf("%03s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
                $post   = $this->input->post();

                $exists = $this->db->get_where('man_powers', [
                    'nik' => $post['nik'],
                    'deleted' => 0
                ])->num_rows();

                if ($exists > 0) {
                    echo json_encode([
                        "theme"   => "error",
                        "title"   => "Duplicate NIK",
                        "message" => "NIK already exists, please use a different one."
                    ]);
                    return;
                }

                $send   = $this->crud->create('man_powers', $post);
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
            $send = $this->crud->update('man_powers', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('man_powers', $data);
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
                'nik' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'position' => $data->val($i, 4),
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
        @unlink('failed/man_powers.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/man_powers.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_man_powers_" . date("Ymd_s") . ".xls";

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

                if (empty($data['nik'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "NIK is required"
                    ];
                    continue;
                }

                // if (!preg_match('/^[0-9]+$/', $data['nik'])) {
                //     $results[] = [
                //         "status"  => "failed",
                //         "item"    => "Line " . ($index + 1),
                //         "message" => "NIK must contain only numbers (0-9)"
                //     ];
                //     continue;
                // }

                if (empty($data['name'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Name is required"
                    ];
                    continue;
                }

                if (empty($data['position'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Position is required"
                    ];
                    continue;
                }

                $allowed_positions = ["Press", "Internal Process", "Visual Checker"];
                if (!in_array($data['position'], $allowed_positions)) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Invalid position value. Allowed: Press, Internal Process, Visual Checker"
                    ];
                    continue;
                }

                $checkData = $this->crud->read('man_powers', [], [
                    "nik" => $data['nik'],
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

                $dataFinal = array(
                    "nik"             => $data['nik'],
                    "name"            => $data['name'],
                    "position"        => $data['position'],
                );

                try {
                    if (!empty($checkData)) {
                        // Update
                        $this->db->update('man_powers', [
                            "name"     => $data['name'],
                            "position" => $data['position'],
                        ], [
                            "nik"     => $data['nik'],
                        ]);

                        $status = "update";
                    } else {
                        // Insert
                        $this->crud->create('man_powers', $dataFinal);

                        $status = "insert";
                    }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "Data for NIK {$data['nik']} updated");

                    $results[] = [
                        "status" => "success",
                        "item" => $res_item,
                        "message" => $res_msg
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        "status" => "failed",
                        "item" => $data['nik'],
                        "message" => $e->getMessage()
                    ];
                    continue;
                }
            }

            $failed = array_filter($results, fn($r) => $r['status'] === 'failed');
            $hasDbError = ($this->db->trans_status() === FALSE);

            if (count($failed) > 0 || $hasDbError) {
                $filePath = 'failed/man_powers.xls';

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
                @unlink('failed/man_powers.xls');

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
            header("Content-Disposition: attachment; filename=man_powers_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('man_powers a');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#man_powers {border-collapse: collapse;width: 100%;font-size: 12px;}#man_powers td, #man_powers th {border: 1px solid #ddd;padding: 2px;}#man_powers tr:nth-child(even){background-color: #f2f2f2;}#man_powers tr:hover {background-color: #ddd;}#man_powers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MAN POWER</h3>
            </div>
        </center>
        
        <table id="man_powers" border="1">
            <tr>
                <th width="20">No</th>
                <th>NIK</th>
                <th>Name</th>
                <th>Position</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            $status = $data['status'] == 0 ? "Active" : "Not Active";

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td style="mso-number-format:\'@\';">' . $data['nik'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['position'] . '</td>
                    <td>' . $status . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
