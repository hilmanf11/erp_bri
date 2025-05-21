<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_others extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_others');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $request_no = $this->input->get('request_no');
        //Select Query
        $this->db->select('a.*, 
            b.number as item_number, 
            b.name as item_name, 
            b.uom, 
            c.name as category_name');
        $this->db->from('purchase_order_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        // $this->db->join('uom d', 'b.uom_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.request_no', $request_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readPono()
    {
        $records = $this->crud->query("SELECT po_no, po_date FROM purchase_order_others WHERE `deleted` = '0' GROUP BY po_no ORDER BY `status` desc");
        echo json_encode($records);
    }

    public function po_no($supplier_number = "")
    {
        $datenow    = date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(po_no) as kode FROM purchase_order_others WHERE po_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "PO-MISC-" . $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_po_no = $this->input->get('filter_po_no');
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        $id = $_POST['id'];
        if ($id === "0") {
        //Select Query
            $this->db->select('a.*');
            $this->db->from('purchase_order_others a');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.po_date >=', $filter_from);
                $this->db->where('a.po_date <=', $filter_to);
            }
            if ($filter_po_no != "" ) {
                $this->db->where('a.po_no', $filter_po_no);
            }
            $this->db->group_by('a.po_no');
            $this->db->order_by('a.po_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            //Select Query
                $this->db->select('a.*');
                $this->db->from('purchase_order_others a');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.po_no', $filter_po_no);
                $this->db->order_by('a.item_id', 'ASC');
                //Get Data Array
                $records = $this->db->get()->result_array();
                echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $po_no = base64_decode($this->input->get('po_no'));
        $records = $this->crud->query("SELECT b.number as item_number, b.name as item_name, a.item_rm_id, d.mpq, d.moq, a.qty, c.currency, d.price, a.total as amount, a.delivery_date, a.remarks
            FROM purchase_order_others a
            JOIN item_rm b on a.item_rm_id = b.id
            JOIN suppliers c on a.supplier_id = c.id
            JOIN supplier_items d on a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id
            JOIN uom e on b.uom = e.name
            WHERE a.status = '0' and a.po_no = '$po_no'
            GROUP BY b.number");
        echo json_encode($records);
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();
    //             $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);
    //             if (@$purchase_order_others->id != "") {
    //                 $send = $this->crud->update('purchase_order_others', ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']], array_merge($post, array("revision" => (@$purchase_order_others->revision + 1))));
    //             } else {
    //                 $send = $this->crud->create('purchase_order_others', $post);
    //             }

    //             echo $send;
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_order_others', $data);
        echo $send;
    }

    public function upload()
    {
        
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'po_no' => $data->val($i, 4),
                'po_date' => $data->val($i, 9),
                'item_number' => $data->val($i, 12),
                'item_name' => $data->val($i, 13),
                'etd' => $data->val($i, 17),
                'specification' => $data->val($i, 19),
                'qty' => $data->val($i, 22),
                'uom' => $data->val($i, 23),
                'remarks' => $data->val($i, 28),
            );
        }
    
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_order_others.txt');
    }
    
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_order_others.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_order_others.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => $data['po_no'], "item_id" => $data['item_number']]);
            if (!empty($purchase_order_others->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_number'] . " and " . $data['po_no'] . " Duplicate Data", "theme" => "error"));
            }else{

                $finalPost = array(
                    "po_no" => $data['po_no'],
                    "po_date" => $data['po_date'],
                    "item_id" => $data['item_number'],
                    "item_name" => $data['item_name'],
                    "etd" => $data['etd'],
                    "specification" => $data['specification'],
                    "qty" => $data['qty'],
                    "uom" => $data['uom'],
                    "remarks" => $data['remarks'],
                );

                $send = $this->crud->create('purchase_order_others', $finalPost);
                echo $send;
            

            }
        }
    }

    public function print_po($po_no)
    {
        $purchase_order_others_total = $this->crud->reads('purchase_order_others', [], ["po_no" => base64_decode($po_no)]);
        $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => base64_decode($po_no)],"", "created_date", "desc");
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();
        // $po_period = $purchase_order_others->po_date;
        // $month = date('m', strtotime($po_period));
        // $year = date('y', strtotime($po_period));

        // $bulan_array = array(
        //     1 => "Jan",
        //     2 => "Feb",
        //     3 => "Mar",
        //     4 => "Apr",
        //     5 => "May",
        //     6 => "June",
        //     7 => "July",
        //     8 => "Aug",
        //     9 => "Sep",
        //     10 => "Oct",
        //     11 => "Nov",
        //     12 => "Dec"
        // );

        // $month_1 = $bulan_array[(($month + 1 - 1) % 12) + 1] . "-" . (($month + 1 > 12) ? $year + 1 : $year);
        // $month_2 = $bulan_array[(($month + 2 - 1) % 12) + 1] . "-" . (($month + 2 > 12) ? $year + 1 : $year);
        // $month_3 = $bulan_array[(($month + 3 - 1) % 12) + 1] . "-" . (($month + 3 > 12) ? $year + 1 : $year);
        // $month_4 = $bulan_array[(($month + 4 - 1) % 12) + 1] . "-" . (($month + 4 > 12) ? $year + 1 : $year);

        //Config Page
        $rows = $this->getRowsPerPage(1);
        $page = ceil(count($purchase_order_others_total) / $rows);
        $html = '<html>
                    <head>
                        <title>' . $purchase_order_others->po_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
            .supplier-signature {
                width: 100%;
            }
            .signature-header {
                border-bottom: 0.1mm solid black;
                text-align: center;
                padding: 5px;
                height: 45px;
            }
            .signature-content {
                height: 100px;
                text-align: center;
            }
            .signature-name {
                border-top: 0.1mm solid black;
                text-align: center;
                padding: 5px;
            }
            .supplier-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
                font-weight: bold;
            }
            .supplier-table th, .supplier-table td {
                border: 0.1mm solid black;
                padding: 2px;
                text-align: center;
                font-size: 9pt;
                font-weight: bold;
            }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                        <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>
                        <div class="print">
                        <div style="width:100%; display:flex, flex-direction:column">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $total_qty = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b. name');
            $this->db->from('purchase_order_others a');
            $this->db->join('users b', 'a.created_by = b.username');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->order_by('a.created_date', 'asc');
            $this->db->limit($rows, ($i * $rows));
            $records = $this->db->get()->result_array();
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td>Phone</td>
                                            <td>:</td>
                                            <td>(0264) 204444</td>
                                        </tr>
                                        <tr>
                                            <td>Fax</td>
                                            <td>:</td>
                                            <td>(0264) 214444</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                            <div style="border: 1px solid black; padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>PURCHASE ORDER OTHERS</u></h3>
                                </center>';
            if ($hal == 1) {
                $html .= '<div style="width:100%; display:flex; flex-direction:row; font-size:12px; margin-bottom:10px;"><table style="width:60%;">
                                            <tr style="width:100%">
                                                <td style="width:100%">Kepada Yth :</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:100%">PT. ASKARA INTERNAL</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:100%">Jl. K.H. Noer Alie Duta Permai Block CIV </td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td width="100">No 09-12 Jakasampurna, Bekasi Barat</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:100%">Bekasi - Indonesia</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:100%">Attention : Bapak Raise</td>
                                            </tr>
                                    </table>
                                    <table style="width:35%;">
                                            <tr style="width:100%">
                                                <td style="width:29%">Document No</td>
                                                <td style="width:1%">:</td>
                                                <td style="width:70%">'.$purchase_order_others->po_no.'</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:29%">PO Date</td>
                                                <td style="width:1%">:</td>
                                                <td style="width:70%">'.$purchase_order_others->po_date.'</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:29%">Lampiran</td>
                                                <td style="width:1%">:</td>
                                                <td style="width:70%">-</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:29%">Plan</td>
                                                <td style="width:1%">:</td>
                                                <td style="width:70%">-</td>
                                            </tr>
                                            <tr style="width:100%">
                                                <td style="width:29%">Revisi</td>
                                                <td style="width:1%">:</td>
                                                <td style="width:70%">-</td>
                                            </tr>
                                    </table></div>';
            }
            $html .= '<table id="customers">
                                    <tr>
                                        <th rowspan="1" width="30" style="text-align:center;">No</th>
                                        <th rowspan="1" width="150" style="text-align:center;">Part Number Customer</th>
                                        <th rowspan="1" width="150" style="text-align:center;">art Name Customer</th>
                                        <th rowspan="1" width="50" style="text-align:center;">Specification</th>
                                        <th rowspan="1" width="80" style="text-align:center;">ETD</th>
                                        <th rowspan="1" width="50" style="text-align:center;">Qty</th>
                                        <th rowspan="1" width="50" style="text-align:center;">Uom</th>
                                        <th rowspan="1" width="100" style="text-align:center;">Remarks</th>
                                        <th rowspan="1" width="50" style="text-align:center;">Dept</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {

                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:left;">' . $record['specification'] . '</td>
                                <td style="text-align:center;">' . $record['etd'] . '</td>
                                <td style="text-align:right;">' . number_format(round($record['qty']), 0, ',', '.') . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:left;">' . $record['remarks']. '</td>
                                <td style="text-align:center;">BRI</td>
                            </tr>';
                $row++;
                $no++;
            }
            $hal++;
        }
        $html .= '<div class="footer" style="margin-top:10pt; font-size:9pt;">
                            <div class="signature-container">
                                <div class="supplier-signature">
                                    <table class="supplier-table">
                                        <tr>
                                            <th style="width:15%;padding:2pt;" rowspan="3">Diterima</th>
                                            <th style="width:25%;padding:2pt;border:none" rowspan="3"></th>
                                            <th style="width:45%;padding:2pt;" colspan="4">Confirm OK</th>
                                        </tr>
                                        <tr style="width:100%;">
                                            <th style="padding:2pt;">Disetujui</th>
                                            <th style="padding:2pt;">Diketahui</th>
                                            <th style="padding:2pt;">Diperiksa</th>
                                            <th style="padding:2pt;">Dibuat</th>
                                        </tr>
                                        <tr>
                                        <td style="border:none"></td>
                                        <td style="border:none"></td>
                                        <td></td>
                                        <td></td>
                                        </tr>';
        $html .= '
                                        <tr>
                                            <td style="text-align:center;"></td>
                                            <td style="text-align:center;border:none;"></td>
                                            <td style="text-align:center;">Deddy H</td>
                                            <td style="text-align:center;">A. Rachman</td>
                                            <td style="text-align:center;">Heru P</td>
                                            <td style="text-align:center;">'.$records[0]['name'].'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;">PT. ASKARA INTERNAL</td>
                                            <td style="text-align:center;border:none;"></td>
                                            <td style="text-align:center;">Presdir</td>
                                            <td style="text-align:center;">BOD</td>
                                            <td style="text-align:center;">Plan Manager</td>
                                            <td style="text-align:center;">Staff</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    ';
        $html .= '<script>window.print()</script>';
        die($html);
    }
    
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_others_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_po_no = $this->input->get('filter_po_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*');
        $this->db->from('purchase_order_others a');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.po_date >=', $filter_from);
            $this->db->where('a.po_date <=', $filter_to);
        }
        if ($filter_po_no != "" ) {
            $this->db->where('a.po_no', $filter_po_no);
        }
        $this->db->order_by('a.item_id', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PURCHASE ORDER MISC</small>
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
                    <th>Status</th>
                    <th>PO No</th>
                    <th>PO Date</th>
                    <th>Part No Customer</th>
                    <th>Part Name Customer</th>
                    <th>ETD</th>
                    <th>SPECIFICATION</th>
                    <th>Qty</th>
                    <th>UOM</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . $data['etd'] . '</td>
                        <td>' . $data['specification'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    function getRowsPerPage($pageNumber)
    {
        if ($pageNumber == 1) {
            return 18; // Set 20 rows for the first page
        } else {
            return 25; // Set 25 rows for subsequent pages
        }
    }
}
