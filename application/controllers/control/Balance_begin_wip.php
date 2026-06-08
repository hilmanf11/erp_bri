<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');

class Balance_begin_wip extends CI_Controller

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

        $this->form_validation->set_rules('item_fg_id', 'Item FG', 'required|min_length[1]|max_length[30]');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);

            $this->load->view('control/balance_begin_wip');

        } else {

            redirect('error_access');

        }

    }



    //GET DATA

    public function reads()

    {

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->reads('balance_begin_wip', ["item_fg_id" => $post]);

        echo json_encode($send);

    }

    private function getLabelTypes()
    {
        return [
            [
                'id' => 'R01',
                'name' => 'REGULAR',
                'description' => 'INCLUDE RETURN'
            ],
            [
                'id' => 'R02',
                'name' => 'REWORK',
                'description' => 'ONLY REWORK'
            ]
        ];
    }

    private function getLocations()
    {
        $data = [
            [
                'id' => 'WIP01',
                'name' => 'WIP PRESS',
                'description' => 'OUTPUT PRODUCTION PRESS'
            ],
            [
                'id' => 'WIP02',
                'name' => 'WIP STORE',
                'description' => 'WIP STORE INTERNAL'
            ]
        ];

        $subconts = $this->db
            ->select("
                id, 
                name,
                '-' as description
            ")
            ->from('subconts')
            ->where('deleted', 0)
            ->where('status', 0)
            ->where('subcont_type_id', 'TS001')
            ->get()
            ->result_array();

        $teaching_factory = $this->db
            ->select("
                id,
                name,
                '-' as description
            ")
            ->from('teaching_factory')
            ->where('deleted', 0)
            ->where('status', 0)
            ->get()
            ->result_array();

        return array_merge($data, $subconts, $teaching_factory);
    }

    public function readLabelTypes()
    {
        echo json_encode($this->getLabelTypes());
    }

    public function readLocations()
    {
        echo json_encode($this->getLocations());
    }

    //GET DATATABLES

    // public function datatables()

    // {

    //     if ($this->input->post()) {

    //         $get = $this->input->get();

    //         $filter_from = @base64_decode($get['filter_from']);

    //         $filter_to = @base64_decode($get['filter_to']);

    //         $filter_item_fg = @base64_decode($get['filter_item_fg']);



    //         $page = $this->input->post('page');

    //         $rows = $this->input->post('rows');

    //         //Pagination 1-10

    //         $page   = isset($page) ? intval($page) : 1;

    //         $rows   = isset($rows) ? intval($rows) : 10;

    //         $offset = ($page - 1) * $rows;

    //         $result = array();



    //         //Select Query

    //         $this->db->select('a.*, b.uom, b.number as item_fg_number, b.name as item_fg_name');

    //         $this->db->from('balance_begin_wip a');

    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');

    //         if($filter_from != "" && $filter_to != ""){

    //             $this->db->where('a.trans_date >=', $filter_from);

    //             $this->db->where('a.trans_date <=', $filter_to);

    //         }

    //         $this->db->like('b.id', $filter_item_fg);

    //         $this->db->order_by('a.trans_date', 'DESC');



    //         //Total Data

    //         $totalRows = $this->db->count_all_results('', false);

    //         //Limit 1 - 10

    //         $this->db->limit($rows, $offset);

    //         //Get Data Array

    //         $records = $this->db->get()->result_array();

    //         //Mapping Data

    //         $result['total'] = $totalRows;

    //         $result = array_merge($result, ['rows' => $records]);

    //         echo json_encode($result);

    //     }

    // }


    public function datatables()
    {
        if ($this->input->post()) {

            $get = $this->input->get();

            $filter_from    = @base64_decode($get['filter_from']);
            $filter_to      = @base64_decode($get['filter_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;

            $result = array();

            $this->db->select('
                a.*, 
                b.uom, 
                b.number as item_fg_number, 
                b.name as item_fg_name
            ');

            $this->db->from('balance_begin_wip a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }

            $this->db->like('b.id', $filter_item_fg);
            $this->db->order_by('a.item_fg_id', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');

            $totalRows = $this->db->count_all_results('', false);

            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();

            $label_types = [];
            foreach ($this->getLabelTypes() as $row) {
                $label_types[$row['id']] = $row['name'];
            }

            $locations = [];
            foreach ($this->getLocations() as $row) {
                $locations[$row['id']] = $row['name'];
            }

            foreach ($records as &$row) {
                $row['label_type_name'] = isset($label_types[$row['label_type']])
                    ? $label_types[$row['label_type']]
                    : '';

                $row['location_name'] = isset($locations[$row['location']])
                    ? $locations[$row['location']]
                    : '';
            }

            $result['total'] = $totalRows;
            $result['rows']  = $records;

            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $check = $this->db
                    ->from('balance_begin_wip')
                    ->where('deleted', 0)
                    ->where('item_fg_id', $post['item_fg_id'])
                    ->where('location', $post['location'])
                    ->where('label_type', $post['label_type'])
                    ->where('trans_date', $post['trans_date'])
                    ->count_all_results();

                if ($check > 0) {
                    show_error('Data already exists');
                }

                $post['transaction_type'] = 'AD-0001';

                $send   = $this->crud->create('balance_begin_wip', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
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

            $send = $this->crud->update('balance_begin_wip', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('balance_begin_wip', $data);
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
                'location' => $data->val($i, 2),
                'trans_date' => $data->val($i, 3),
                'label_type' => $data->val($i, 4),
                'item_fg_id' => $data->val($i, 5),
                'qty' => $data->val($i, 6),
                'transaction_type' => 'AD-0001'
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
        @unlink('failed/stock_wip.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/balance_begin_wip.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_balance_begin_wip_" . date("Ymd_s") . ".xls";

        header("Content-Description: File Upload Failed");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file);
    }

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
                        empty($data['location']) ||
                        empty($data['trans_date']) ||
                        empty($data['label_type']) ||
                        empty($data['item_fg_id']) ||
                        $data['qty'] == "" || $data['qty'] == null
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
                        "message" => "Product ID " . $data['item_fg_id'] . " not found"
                    ];
                    continue;
                }

                $data['item_fg_id'] = $item_fg_id->id;

                $balance_begin_wip = $this->crud->read('balance_begin_wip', [], [
                    "location"   => $data['location'],
                    "label_type" => $data['label_type'],
                    "item_fg_id" => $data['item_fg_id'],
                    "trans_date" => $data['trans_date'],
                ]);

                $dataFinal = array(
                    "location"   => $data["location"],
                    "label_type" => $data["label_type"],
                    "item_fg_id" => $data["item_fg_id"],
                    "trans_date" => $data["trans_date"],
                    "qty"        => $data["qty"],
                    "location"   => $data["location"],
                    "transaction_type" => "AD-0001"
                );

                try {
                    if (!empty($balance_begin_wip->item_fg_id)) {
                        $this->crud->update('balance_begin_wip', [
                                "location"   => $data['location'],
                                "label_type" => $data['label_type'],
                                "item_fg_id" => $data['item_fg_id'],
                                "trans_date" => $data['trans_date'],
                            ], [
                                "qty" => $data['qty'],
                        ]);
                        $status = "update";
                    } else {
                        $this->crud->create('balance_begin_wip', $dataFinal);
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
                $filePath = 'failed/balance_begin_wip.xls';

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
                @unlink('failed/balance_begin_wip.xls');

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
            header("Content-Disposition: attachment; filename=balance_begin_wip_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);

        //Config

        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.uom, b.number as item_fg_number, b.name as item_fg_name, b.uom');
        $this->db->from('balance_begin_wip a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');

        if($filter_from != "" && $filter_to != ""){
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }

        $this->db->like('b.id', $filter_item_fg);
        $this->db->order_by('a.item_fg_id', 'ASC');
        $this->db->order_by('a.trans_date', 'ASC');
        $records = $this->db->get()->result_array();

        $label_types = [];
        foreach ($this->getLabelTypes() as $row) {
            $label_types[$row['id']] = $row['name'];
        }

        $locations = [];
        foreach ($this->getLocations() as $row) {
            $locations[$row['id']] = $row['name'];
        }

        foreach ($records as &$row) {
            $row['label_type_name'] = isset($label_types[$row['label_type']])
                ? $label_types[$row['label_type']]
                : '';

            $row['location_name'] = isset($locations[$row['location']])
                ? $locations[$row['location']]
                : '';
        }


            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#balance_begin_wip {border-collapse: collapse;width: 100%;font-size: 12px;}#balance_begin_wip td, #balance_begin_wip th {border: 1px solid #ddd;padding: 2px;}#balance_begin_wip tr:nth-child(even){background-color: #f2f2f2;}#balance_begin_wip tr:hover {background-color: #ddd;}#balance_begin_wip th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>

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
                    <h3>DATA BALANCE BEGIN WIP</h3>
                </div>

                <div style="float: left; font-size: 12px; text-align: left; width:30%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>Cut Off</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_from . ' to ' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>

            <table id="balance_begin_wip" border="1">
                <tr>
                    <th width="20">No</th>
                    <th width="100">Location</th>
                    <th width="100">Label Type</th>
                    <th width="100">Cut Off</th>
                    <th width="100">Product ID</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>UOM</th>
                </tr>';

            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['location_name'] . '</td>
                            <td>' . $data['label_type_name'] . '</td>
                            <td style="text-align: left;">' . $data['trans_date'] . '</td>
                            <td>' . $data['item_fg_id'] . '</td>
                            <td style="mso-number-format:&quot;@&quot;">' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td style="text-align: right;">' . number_format($data['qty']) . '</td>
                            <td>' . $data['uom'] . '</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
    }
}