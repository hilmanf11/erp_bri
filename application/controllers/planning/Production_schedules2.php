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

class Production_schedules2 extends CI_Controller
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
            $this->load->view('planning/production_schedules2');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_schedules', ["name" => $post]);
        echo json_encode($send);
    }
    public function readPeriodAll()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules ORDER BY `period` DESC");
        echo json_encode($send);
    }
    public function readWpAll()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp, workorder, so_number, item_fg_id FROM production_schedules WHERE `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }
    public function readPeriod()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules WHERE `status` = 0 ORDER BY `period` DESC");
        echo json_encode($send);
    }

    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp FROM production_schedules WHERE `status` = 0 and `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }

    public function readWorkorder()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $send = $this->crud->query("SELECT DISTINCT workorder FROM production_schedules WHERE `status` = 0 and `period` = '$period' and wp = '$wp' ORDER BY `workorder` DESC");
        echo json_encode($send);
    }

    public function readCustomer()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $workorder = base64_decode($this->input->get('workorder'));

        $send = $this->crud->query("SELECT a.customer_id, b.number as customer_number, b.name as customer_name 
            FROM production_schedules a
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
            FROM production_schedules a
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
        $query = "SELECT id, name
                FROM item_process
                WHERE id IN ('PC002', 'PC003') AND status = 0";
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

    public function workorder($wp, $trans_date)
    {
        $datenow = date("ymd", strtotime($trans_date));
        $sqlGetID = $this->db->query("SELECT max(workorder) as kode FROM production_schedules WHERE workorder like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "WO" . $datenow . "-" . $autoID;
        return $workOrderNo;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->get('filter_month');
            $filter_year = $this->input->get('filter_year');
            $filter_line_productions = $this->input->get('filter_line_productions');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            // Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            // Select Query
            $this->db->select("a.*, 
                c.number as item_number, c.name as item_name, c.uom, 
                d.name as line_name, 
                e.name as process_name,
                (CASE WHEN f.id != '' THEN 2 ELSE a.status END) as status_wo");
            $this->db->from('production_schedules a');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('line_productions d', 'a.line_id = d.id');
            $this->db->join('item_process e', 'a.process_id = e.id', 'left');
            $this->db->join('scan_item_receipts_fg f', 'a.so_number = f.so_number and a.workorder = f.workorder', 'left');
            $this->db->where('a.deleted', 0);
            
            // Filter berdasarkan status
            if ($filter_status == "0") {
                $this->db->where("a.status", 0);
            } elseif ($filter_status == "1") {
                $this->db->where("f.id is NULL");
            } elseif ($filter_status == "2") {
                $this->db->where("f.id != ''");
            }

            // Filter berdasarkan inputan
            $this->db->like('a.month', $filter_month);
            $this->db->like('a.year', $filter_year);
            $this->db->like('a.line_id', $filter_line_productions);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);

            $this->db->order_by('a.wp', 'ASC');

            // Total Data
            $totalRows = $this->db->count_all_results('', false);

            // Limit 1 - 10
            $this->db->limit($rows, $offset);

            // Get Data Array
            $records = $this->db->get()->result_array();

            // Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $workorder = $this->workorder($post['wp'], $post['trans_date']);
                $production_schedules = $this->crud->read('production_schedules', [], [
                    "item_fg_id" => $post['item_fg_id'], 
                    "wp" => $post['wp'], 
                    "trans_date" => $post['trans_date'],
                    "process_id" => $post['process_id'],
                    "line_id" => $post['line_id']
                ]);
                $sales_orders = $this->crud->query("SELECT 
                    a.item_fg_id, b.number as item_number, 
                    b.name as item_name, 
                    (a.qty - coalesce(SUM(c.qty), 0)) as qty
                FROM sales_orders a 
                JOIN item_fg b on a.item_fg_id = b.id
                LEFT JOIN production_schedules c ON a.sales_order_no = c.so_number and a.item_fg_id = c.item_fg_id
                WHERE a.item_fg_id = '$post[item_fg_id]'
                GROUP BY a.item_fg_id");

                if (@$production_schedules->id) {
                    show_error("Duplicate Data");
                } elseif (!empty($sales_orders) && $post['qty'] > $sales_orders[0]->qty) {
                    show_error("qty is bigger than available quantity");
                } else {
                    unset($post['customer_id'], $post['so_number']);
                    $postFinal = array_merge($post, array("workorder" => $workorder, "period" => $post['year'] . $post['month']));
                    $send = $this->crud->create('production_schedules', $postFinal);
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
            $send = $this->crud->update('production_schedules', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('production_schedules', ["id" => $data['id']]);
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
            $trans_date_raw = $sheet->getCellByColumnAndRow(5, $i)->getValue();
            
            if (is_numeric($trans_date_raw)) {
                $trans_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($trans_date_raw)->format('Y-m-d');
            } else {
                $trans_date = date('Y-m-d', strtotime($trans_date_raw));
            }

            // Menambahkan data ke array
            $datas[] = array(
                'period' => $sheet->getCellByColumnAndRow(2, $i)->getValue(),
                'line_id' => $sheet->getCellByColumnAndRow(3, $i)->getValue(),
                'wp' => $sheet->getCellByColumnAndRow(4, $i)->getValue(),
                'trans_date' => $trans_date,
                'item_fg_id' => $sheet->getCellByColumnAndRow(6, $i)->getValue(),
                'process_id' => $sheet->getCellByColumnAndRow(7, $i)->getValue(),
                'qty' => $sheet->getCellByColumnAndRow(8, $i)->getValue(),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($target);
    }


    public function uploadclearFailed()
    {
        @unlink('failed/production_schedules2.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/production_schedules2.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/production_schedules2.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            // Validasi qty tidak boleh kosong atau nol
            if (!isset($data['qty']) || empty($data['qty']) || $data['qty'] <= 0) {
                echo json_encode(["title" => "Error", "message" => "Quantity must be greater than 0", "theme" => "error"]);
                return;
            }

            // Validasi keberadaan item, line, dan process
            $items = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
            $line = $this->crud->read('line_productions', [], ["number" => $data['line_id']]);
            $process = $this->crud->read('item_process', [], ["id" => $data['process_id']]);

            if (empty($items->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Product No " . $data['item_fg_id'] . " Not Found", "theme" => "error"]);
                return;
            }
            if (empty($line->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Line Production " . $data['line_id'] . " Not Found", "theme" => "error"]);
                return;
            }
            if (empty($process->id)) {
                echo json_encode(["title" => "Not Found", "message" => "Process Id " . $data['process_id'] . " Not Found", "theme" => "error"]);
                return;
            }

            // Periksa duplikasi data berdasarkan item_fg_id, process_id, line_id, wp, trans_date
            $existing_data = $this->crud->read('production_schedules', [], [
                "item_fg_id" => $items->id,
                "process_id" => $data['process_id'],
                "line_id" => $line->id,
                "wp" => $data['wp'],
                "trans_date" => $data['trans_date']
            ]);

            if (!empty($existing_data)) {
                echo json_encode([
                    "title" => "Duplicated",
                    "message" => "Duplicate Data: Product Id " . $data['item_fg_id'] . 
                                ", Process Id " . $data['process_id'] . 
                                ", Line ID " . $data['line_id'] . 
                                ", WP " . $data['wp'] . 
                                ", Trans Date " . $data['trans_date'],
                    "theme" => "error"
                ]);
                return;
            }

            // Ambil sisa qty dari sales_orders
            $sales_orders = $this->crud->query("SELECT 
                a.item_fg_id, b.number as item_number, 
                b.name as item_name, 
                (a.qty - COALESCE(SUM(c.qty), 0)) as qty
            FROM sales_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id
            LEFT JOIN production_schedules c ON a.sales_order_no = c.so_number AND a.item_fg_id = c.item_fg_id
            WHERE a.item_fg_id = '{$items->id}'
            GROUP BY a.item_fg_id");

            if (!empty($sales_orders) && $data['qty'] > $sales_orders[0]->qty) {
                echo json_encode(["title" => "Error", "message" => "Quantity is bigger than available quantity", "theme" => "error"]);
                return;
            }

            // Generate workorder
            $workorder = $this->workorder($data['wp'], $data['trans_date']);

            // Generate month dan year dari trans_date
            $dateObj = DateTime::createFromFormat('Y-m-d', $data['trans_date']);
            if ($dateObj === false) {
                echo json_encode(["title" => "Error", "message" => "Invalid transaction date format", "theme" => "error"]);
                return;
            }
            $year = $dateObj->format('Y');
            $month = $dateObj->format('m');

            // Data final yang akan dimasukkan
            $dataFinal = [
                "workorder" => $workorder,
                "item_fg_id" => $items->id,
                "process_id" => $data['process_id'],
                "line_id" => $line->id,
                "trans_date" => $data['trans_date'],
                "period" => $year . $month,
                "year" => $year,
                "month" => $month,
                "wp" => $data['wp'],
                "qty" => $data['qty']
            ];

            $send = $this->crud->create('production_schedules', $dataFinal);
            echo $send;
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
        $this->db->from('production_schedules a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $label = $this->db->get()->row();
        $amountQty = ceil($label->qty / $label->lot);
        for ($i = 1; $i <= $amountQty; $i++) {
            $lots = sprintf("%03s", $i);
            $this->db->select('b.circuit');
            $this->db->from('production_schedules a');
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
        $this->db->from('production_schedules a');
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
            header("Content-Disposition: attachment; filename=production_schedules_$format.xls");
        }
        $filter_month = $this->input->get('filter_month');
        $filter_year = $this->input->get('filter_year');
        $filter_line_productions = $this->input->get('filter_line_productions');
        $filter_customers = $this->input->get('filter_customers');
        $filter_sales_order = $this->input->get('filter_sales_order');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, c.number as item_number, c.name as item_name, c.uom, d.name as line_name');
        $this->db->from('production_schedules a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('line_productions d', 'a.line_id = d.id');;
        $this->db->where('a.deleted', 0);
        $this->db->like('a.month', $filter_month);
        $this->db->like('a.year', $filter_year);
        $this->db->like('a.line_id', $filter_line_productions);
        $this->db->like('a.customer_id', $filter_customers);
        $this->db->like('a.so_number', $filter_sales_order);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->order_by('a.trans_date', 'ASC');
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('c.number', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PRODUCTION SCHEDULE</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Period</th>
                    <th>WP</th>
                    <th>WP Date</th>
                    <th>Work Order</th>
                    <th>Line Production</th>
                    <th>Customer No</th>
                    <th>Customer Name</th>
                    <th>Customer Type</th>
                    <th>Sales Order No</th>
                    <th>Sales Order Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['wp'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['line_name'] . '</td>
                            <td>' . $data['customer_number'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['customer_type'] . '</td>
                            <td>' . $data['so_number'] . '</td>
                            <td>' . $data['so_date'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'B2' => ['ISI DENGAN YYYY-MM'],
            'C2' => ['ISI DENGAN KODE LINE'],
            'E2' => ['ISI DENGAN YYYY-MM-DD'],
            'G2' => ['ISI DENGAN KODE PROSES'],
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('PRODUCTION SCHEDULES');
        $templateSheet->mergeCells('A1:H1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(20);
        $templateSheet->getColumnDimension('C')->setWidth(20);
        $templateSheet->getColumnDimension('D')->setWidth(20);
        $templateSheet->getColumnDimension('E')->setWidth(25);
        $templateSheet->getColumnDimension('F')->setWidth(30);
        $templateSheet->getColumnDimension('G')->setWidth(20);
        $templateSheet->getColumnDimension('H')->setWidth(20);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD PRODUCTION SCHEDULES');
        $templateSheet->setCellValue('A2', 'No');
        $templateSheet->setCellValue('B2', 'PERIOD');
        $templateSheet->setCellValue('C2', 'LINE');
        $templateSheet->setCellValue('D2', 'WP');
        $templateSheet->setCellValue('E2', 'WP DATE');
        $templateSheet->setCellValue('F2', 'PRODUCT NO');
        $templateSheet->setCellValue('G2', 'PROCESS');
        $templateSheet->setCellValue('H2', 'PLANNING LOT');
        $templateSheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A2')->getFont()->setBold(true);
        $templateSheet->getStyle('B2:C2')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A2:H2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $templateSheet->getStyle('E:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);
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
        $item_refSheet->setTitle('Line Productions');

        $this->db->select('a.number as code, a.name, a.description');
        $this->db->from('line_productions a');
        $this->db->order_by('a.name','asc');
        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(5);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(25);
        $item_refSheet->getColumnDimension('D')->setWidth(20);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Code');
        $item_refSheet->setCellValue('C1', 'Name');
        $item_refSheet->setCellValue('D1', 'Description');
        $item_refSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:D1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['code']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['name']);
            $item_refSheet->setCellValue('D' . $rowItem_ref, $itemref['description']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('H' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('I' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        // Second Sheet: Product
        $item_refSheet = $spreadsheet->createSheet(2);
        $item_refSheet->setTitle('Products');

        $this->db->select('a.number as product_no, a.name as product_name, a.status');
        $this->db->from('item_fg a');
        $this->db->order_by('a.number', 'asc');
        $item_ref = $this->db->get()->result_array();

        // Konversi status setelah pengambilan data
        foreach ($item_ref as &$item) {
            $item['status'] = ($item['status'] == 0) ? 'Active' : 'Inactive';
        }
        unset($item);
        $item_refSheet->getColumnDimension('A')->setWidth(5);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(25);
        $item_refSheet->getColumnDimension('D')->setWidth(10);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Product No');
        $item_refSheet->setCellValue('C1', 'Product Name');
        $item_refSheet->setCellValue('D1', 'Status');
        $item_refSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:D1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['product_no']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['product_name']);
            $item_refSheet->setCellValue('D' . $rowItem_ref, $itemref['status']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('H' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('I' . $rowItem_ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':D' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        $spreadsheet->setActiveSheetIndex(0); 
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_prod_sch.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
