<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class New_barcode_fg_uploads extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        // $this->load->library('Zend');
        // $this->zend->load('Zend/Barcode');
        $this->load->library('ciqrcode');
        $this->load->model('crud');
        //VALIDASI FORM
        // $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[purgings.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/new_barcode_fg_uploads');
        } else {
            redirect('error_access');
        }
    }
    
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            
            //Pagination 1-10
            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.serial_no as label_box, a.compound_lot as lot_no, a.qty_wip as qty, a.prod_date as prod_date, a.trans_date as packing_date, a.created_by as created_by, a.created_date as created_date, a.updated_by as updated_by, a.updated_date as updated_date, b.id as item_no, b.number as item_number, b.name as item_name, a.request_no');
            $this->db->from('label_packing a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->where('a.qty_packing', 0);
            $this->db->where('a.deleted', 0);
            
            // Terapkan filter jika ada
            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    if (isset($filter->field) && isset($filter->value)) {
                        // Gunakan nama kolom asli, bukan alias
                        switch ($filter->field) {
                            case 'label_box':
                                $this->db->like('a.serial_no', $filter->value);
                                break;
                            case 'lot_no':
                                $this->db->like('a.compound_lot', $filter->value);
                                break;
                            case 'qty':
                                $this->db->like('a.qty_wip', $filter->value);
                                break;
                            case 'prod_date':
                                $this->db->like('a.prod_date', $filter->value);
                                break;
                            case 'packing_date':
                                $this->db->like('a.trans_date', $filter->value);
                                break;
                            case 'item_no':
                                $this->db->like('b.id', $filter->value);
                                break;
                            case 'item_number':
                                $this->db->like('b.number', $filter->value);
                                break;
                            case 'item_name':
                                $this->db->like('b.name', $filter->value);
                                break;
                            case 'request_no':
                                $this->db->like('a.request_no', $filter->value);
                                break;
                            default:
                                // Untuk kolom lain, gunakan nama asli
                                $this->db->like($filter->field, $filter->value);
                                break;
                        }
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
   
    //UPLOAD DATA
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'label_box' => $data->val($i, 2),
                'item_number' => $data->val($i, 3),
                'prod_date' => $data->val($i, 4),
                'packing_date' => $data->val($i, 5),
                'lot_no' => $data->val($i, 6),
                'qty' => $data->val($i, 7)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/new_barcode_fg_uploads.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/new_barcode_fg_uploads.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/new_barcode_fg_uploads.txt";
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

            // Cek item_fg menggunakan ID langsung
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_number']]);
            
            // Cek duplikasi label_box di label_packing
            $existing_label = $this->crud->read('label_packing', [], ['serial_no' => $data['label_box']]);

            if (empty($item_fg->id)) {
                echo json_encode(array(
                    "title" => "Not Found", 
                    "message" => "Item FG ID " . $data['item_number'] . " Not Found", 
                    "theme" => "error"
                ));
            } else if (!empty($existing_label)) {
                echo json_encode(array(
                    "title" => "Duplicate", 
                    "message" => "Label Box " . $data['label_box'] . " already exists", 
                    "theme" => "error"
                ));
            } else {
                // Format tanggal
                $prod_date = date('Y-m-d', strtotime($data['prod_date']));
                $packing_date = date('Y-m-d', strtotime($data['packing_date']));
                
                // Generate request_no
                $trans_date = date("ymd", strtotime($data['prod_date']));
                $sqlGetID = $this->db->query("SELECT max(request_no) as kode FROM label_packing WHERE request_no like 'LP-$trans_date-%'");
                $rowID = $sqlGetID->row();
                $kode = $rowID->kode;

                if ($kode == NULL) {
                    $autoID = sprintf("%04s", 1);
                } else {
                    $urutan = (int) substr($kode, -4);
                    $urutan++;
                    $autoID = sprintf("%04s", $urutan);
                }
                $request_no = "LP-" . $trans_date . "-" . $autoID;

                // Data untuk label_packing
                $dataLabelPacking = [
                    "trans_date" => $data['prod_date'],
                    "item_fg_id" => $data['item_number'],
                    "qty_wip" => $data['qty'],
                    "qty_packing" => 0,
                    "qty_label" => 1, // Setiap request_no 1 label
                    "prod_date" => $prod_date,
                    "serial_no" => $data['label_box'],
                    "compound_lot" => $data['lot_no'],
                    "status" => 0,
                    "request_no" => $request_no
                ];

                // Simpan ke database
                $label_packing_id = $this->crud->create('label_packing', $dataLabelPacking);

                // Simpan ke label_packing_detail
                $detail_data = [
                    'created_by' => $this->session->username,
                    'created_date' => date('Y-m-d H:i:s'),
                    'serial_label' => $data['label_box'],
                    'serial_no' => $data['label_box'],
                    'item_fg_id' => $data['item_number'],
                    'qty_packing' => $data['qty'],
                    'request_no' => $request_no
                ];
                $this->crud->create('label_packing_detail', $detail_data);

                if ($label_packing_id) {
                    echo json_encode(array(
                        "title" => "Success",
                        "message" => "Label Box " . $data['label_box'] . " successfully created",
                        "theme" => "success"
                    ));
                } else {
                    echo json_encode(array(
                        "title" => "Error",
                        "message" => "Failed to create label",
                        "theme" => "error"
                    ));
                }
            }
        }
    }

    // private function createBarcode($label_no, $path)
    // {
    //     $barcodeOptions = array(
    //         'text' => $label_no,  // Nilai barcode
    //         'barHeight' => 30,    // Tinggi barcode
    //         'factor' => 2,        // Skala barcode
    //     );
    
    //     // Path lengkap file barcode
    //     $barcodeFile = $path . $label_no . ".png";
    
    //     // // Gunakan output buffer untuk menangkap hasil render
    //     // ob_start();
    //     // Zend_Barcode::render('code128', 'image', $barcodeOptions, []);
    //     // $barcodeImage = ob_get_clean();
    
    //     // Simpan hasil ke file
    //     file_put_contents($barcodeFile, $barcodeImage);
    
    //     // Return path file
    //     return $barcodeFile;
    // }

    public function delete()
    {
        $data = $this->input->post();

        if (!isset($data['serial_no']) || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing serial_no or id']);
            return;
        }

        $serial_no = $data['serial_no'];

        $this->crud->delete('label_packing_detail', ["serial_no" => $serial_no]);

        $deleteLabelPacking = $this->crud->delete('label_packing', ["id" => $data['id']]);
        
        if ($deleteLabelPacking) {
            echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete data']);
        }
    }

    //PRINT & EXCEL DATA
    public function print($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $id = base64_decode($id);

        $config_iso = $this->db->get('config_iso')->row();


        $this->db->select('a.*,d.number,d.number_customer as item_number_customer, d.name, e.location, e.area, d.color, d.uom, a.qty, a.cut_off_date, d.number as item_number, d.name as item_name, d.alias, d.logo, d.uom, e.location, a.packing');
        $this->db->from('new_barcode_fg a');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        $this->db->join('warehouse_location_items e', 'e.item_fg_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($records as $record) {
                $padding = "padding:3mm 5mm 3mm 3mm;";
                
                if ($record->logo == "0") {
                    $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                } else {
                    $img_bpi = '';
                }

                if($record->packing == "1" || $record->packing == "3"){
                    $label = 'LABEL PACKING';
                } else{
                    $label = 'LABEL BOXS';
                }

                $qc1 = substr($record->qc_1, 0, 3);
                $qcnumber1 = substr($record->qcnumber_1, -3);
                $qc2 = substr($record->qc_2, 0, 3);
                $qcnumber2 = substr($record->qcnumber_2, -3);
                $op1 = substr($record->op_1, 0, 3);
                $opnumber1 = substr($record->opnumber_1, -3);
                $op2 = substr($record->op_2, 0, 3);
                $opnumber2 = substr($record->opnumber_2, -3);

                $html .= '  <div style="width: 70mm; max-height:100mm; border:none; margin-bottom:5px;">
                                <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                    <tr>
                                        <th colspan="4" style="font-size: 6px; text-align: right; border: none;"><b>' . $config_iso->doc_barcode_fg . '</b></th>
                                    </tr>
                                    <tr>
                                    <th colspan="4" style="font-size: 12px; text-align: center; border: none;"><b>' . $label . '</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:10mm; height: 10mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                        <td colspan="3" style="text-align:center; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part No</small><br><b style="font-size:12px;">' . $record->item_number . '</b>
                                        </td>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $record->lot_no . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><b style="font-size:12px;">' . $record->item_name . '</b>
                                        </td>

                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $record->prod_date . '</b>
                                            <br>
                                            <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $record->packing_date . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">'. $record->item_number_customer . '</b>
                                        </td>
                                        <td style="text-align:left; border: none;">
                                            <small style="font-size:10px;">Shift.</small><br>
                                            <div style="text-align:center;">
                                                <b style="font-size:12px;">' . $record->shift . '</b>
                                            </div>
                                        </td>
                                        <td style="text-align:left; border: none;">
                                            <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: center; margin-right: 5px; margin-top: 5px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Qty</small><br><b style="font-size:12px;">' . number_format($record->qty, 2) . '</b>
                                        </td>

                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Unit</small><br><b style="font-size:12px;">'. $record->uom .'</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Operator</small>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">QC</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Equivalent</small><br><b style="font-size:12px;"></b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:8px;">' . $op1 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber1 . '</b>
                                            <br>
                                            <b style="font-size:8px;">' . $op2 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber2 . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:8px;">' . $qc1 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber1 . '</b>
                                            <br>
                                            <b style="font-size:8px;">' . $qc2 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber2 . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center; border: 1px solid black;"><small style="font-size:14px;"><b>' . $record->label_no . '</b></small>
                                        <br><small style="font-size:10px;"><b>' . $record->location . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center; border: 1px solid black; height:40;">
                                            <img src="' . base_url('assets/image/barcode/' . $record->label_no . '.png') . '" width="280"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function print_label() {
        $serial_no = $this->input->get('serial_no');
        $item_fg_id = $this->input->get('item_fg_id');
        
        if (empty($serial_no)) {
            show_error("Missing parameter: serial_no", 400);
        }
        
        $this->db->select('
            lp.trans_date, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            fg.number AS product_no, 
            fg.name AS product_name, 
            lpd.qty_packing,
            lp.specification
        ');
        $this->db->from('label_packing_detail lpd');
        $this->db->join('label_packing lp', 'lp.serial_no = lpd.serial_no', 'left');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->where('lpd.serial_no', $serial_no);
        
        if (!empty($item_fg_id)) {
            $this->db->where('lp.item_fg_id', $item_fg_id);
        }
        
        $this->db->group_by('lpd.serial_label');
        
        $label_packing_details = $this->db->get()->result();
        
        if (empty($label_packing_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_serial_label = $label_packing_details[0]->serial_label;
        
        foreach ($label_packing_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 2; }
                            table { border-collapse: collapse; width: 7.5cm; height: 8cm; font-size: 20px; border: 2px solid black; table-layout: fixed; }
                            th, td { border: 1px solid black; padding: 2px; text-align: left; }
                            th { text-align: center; font-size: 14px; font-weight: bold; }
                            .header { text-align: center; font-size: 15px; font-weight: bold; }
                            .logo { text-align: center; width: 100%; padding: 3px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 12px; text-align: center; height: 20mm; vertical-align: bottom; font-weight: bold; }
                            .qc-sign { text-align: center; height: 20mm; }
                            .qr-code img { width: 60px; height: 60px; display: block; margin: 0 auto; }
                            .serial-label { font-size: 11px; text-align: center; word-wrap: break-word; overflow: hidden; font-weight: bold; }
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
                                        font-size: 15px;
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
        
        foreach ($label_packing_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' PCS';
            $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                <span class="header" style="font-size: 20px; height: 20px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
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
    
    public function print_label_by_request() {
        $request_no = $this->input->get('request_no');
        
        if (empty($request_no)) {
            show_error("Missing parameter: request_no", 400);
        }
        
        $this->db->select('
            lp.trans_date, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            fg.number AS product_no, 
            fg.name AS product_name, 
            lpd.qty_packing,
            lp.specification
        ');
        $this->db->from('label_packing_detail lpd');
        $this->db->join('label_packing lp', 'lp.serial_no = lpd.serial_no', 'left');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->where('lpd.request_no', $request_no);
        
        $this->db->group_by('lpd.serial_label');
        
        $label_packing_details = $this->db->get()->result();
        
        if (empty($label_packing_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_serial_label = $label_packing_details[0]->serial_label;
        
        foreach ($label_packing_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 2; }
                            table { border-collapse: collapse; width: 7.5cm; height: 8cm; font-size: 20px; border: 2px solid black; table-layout: fixed; }
                            th, td { border: 1px solid black; padding: 2px; text-align: left; }
                            th { text-align: center; font-size: 14px; font-weight: bold; }
                            .header { text-align: center; font-size: 15px; font-weight: bold; }
                            .logo { text-align: center; width: 100%; padding: 3px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 12px; text-align: center; height: 20mm; vertical-align: bottom; font-weight: bold; }
                            .qc-sign { text-align: center; height: 20mm; }
                            .qr-code img { width: 60px; height: 60px; display: block; margin: 0 auto; }
                            .serial-label { font-size: 11px; text-align: center; word-wrap: break-word; overflow: hidden; font-weight: bold; }
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
                                        font-size: 15px;
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
        
        foreach ($label_packing_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' PCS';
            $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                <span class="header" style="font-size: 20px; height: 20px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
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
}
