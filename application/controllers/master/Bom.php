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

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Settings;

class Bom extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/bom');
        } else {
            redirect('error_access');
        }
    }

    // private function format_number($number) {
    //     //Check if the number contains a comma or period as decimal separator
    //     if (strpos($number, '.') !== false) {
    //         $formatted_number = str_replace(',', '.', $number);
    //         $formatted_number = str_replace('.', ',', $formatted_number);
    //     } elseif (strpos($number, ',') !== false) {
    //         $formatted_number = str_replace('.', ',', $number);
    //         $formatted_number = str_replace(',', '.', $formatted_number);
    //     } else {
    //         $formatted_number = $number;
    //     }
    
    //     return $formatted_number;
    // }

    private function format_number($number)
    {
        if ($number === null || $number === '') {
            return '';
        }

        $number = (float) $number;

        if ($number == (int) $number) {
            return (string) (int) $number;
        }

        return number_format($number, 4, ',', '');
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('bom', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    //GET DATA
    public function readWeight()
    {
        $post = $this->input->post();
        $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
        echo json_encode($item_fg);
    }
    
    //GET DATA
    public function readUoM()
    {
        $post = $this->input->post();
        $uom = $this->crud->reads("uom", [], ["deleted"=>0]);
        echo json_encode($uom);
    }

    //GET DATA
    public function readRunner()
    {
        $post = $this->input->post();
        $item_fg_id = $post['item_fg_id'];
        $menu_loading = $this->crud->query("SELECT SUM(a.runner) as runner, b.cavity_standard
       FROM menu_loadings a JOIN molds b on a.mold_id = b.id
       WHERE a.item_fg_id = '$item_fg_id' group by a.item_fg_id");
        echo json_encode($menu_loading);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('b.number');
            $this->db->order_by('b.number', 'ASC');
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
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as item_rm_number, c.number_internal as item_rm_number_internal, c.name as item_rm_name, e.name as process_name, a.uom as uom, c.item_family_id as product_family, d.name as product_family_name, (CASE WHEN a.type = "1" THEN "ORIGINAL" WHEN a.type = "2" THEN "RECYCLE" WHEN a.type = "3" THEN "BOTH" ELSE "INVALID" END) as type_name, a.composition as formatted_composition, c.cas_no');

            // CASE 
            //     WHEN a.composition = FLOOR(a.composition) 
            //         THEN FORMAT(CAST(FLOOR(a.composition) AS CHAR),0) 
            // ELSE 
            //     FORMAT(CAST(a.composition AS CHAR),4) 
            // END AS formatted_composition

            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys d', 'c.item_family_id = d.id');
            $this->db->join('item_process e', 'a.process_id = e.id');
            $this->db->join('uom f', 'a.uom = f.name','left');
            $this->db->where('b.number', $number);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->group_by('a.id');
            $this->db->order_by('e.name', 'ASC');
            $this->db->order_by('d.name', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            foreach ($records as &$record) {
                $record['formatted_composition'] = $this->format_number($record['formatted_composition']);
            }

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            $this->db->select('a.*, c.number as item_rm_number, c.number_internal as item_rm_number_internal, c.name as item_rm_name, a.uom, d.name as item_family_name, (CASE WHEN a.type = "1" THEN "ORIGINAL" WHEN a.type = "2" THEN "RECYCLE" WHEN a.type = "3" THEN "BOTH" ELSE "INVALID" END) as type_name, CASE WHEN a.composition = FLOOR(a.composition) THEN FORMAT(CAST(FLOOR(a.composition) AS CHAR),0) ELSE FORMAT(CAST(a.composition AS CHAR),2) END AS formatted_composition');
            $this->db->from('bom a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys d', 'c.item_family_id = d.id');
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            
            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            // Simpan data ke database
            $bom = $this->crud->read("bom", [], ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
            if (@$bom->item_fg_id != "") {
                $send = $this->crud->update('bom', ["item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
            } else {
                $send = $this->crud->create('bom', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('bom', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777);
        $spreadsheet = IOFactory::load($target);
        $sheet = $spreadsheet->getActiveSheet();
        $totalRows = $sheet->getHighestDataRow();
        
        $datas = [];
        for ($i = 3; $i <= $totalRows; $i++) {
            $datas[] = array(
                'item_fg_id' => $sheet->getCell('B' . $i)->getValue(),
                'item_rm_id' => $sheet->getCell('C' . $i)->getValue(),
                'item_process_id' => $sheet->getCell('D' . $i)->getValue(),
                'type' => $sheet->getCell('E' . $i)->getValue(),
                'uom' => $sheet->getCell('F' . $i)->getValue(),
                'recyle' => $sheet->getCell('G' . $i)->getValue(),
                'composition' => $sheet->getCell('H' . $i)->getValue(),
                'priority' => $sheet->getCell('I' . $i)->getValue(),
            );
        }
    
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($target);
        // error_reporting(0);
        // require_once 'assets/vendors/excel_reader2.php';
        // $target = basename($_FILES['file_upload']['name']);
        // move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        // chmod($_FILES['file_upload']['name'], 0777);
        // $file = $_FILES['file_upload']['name'];
        // $data = new Spreadsheet_Excel_Reader($file, false);
        // $total_row = $data->rowcount($sheet_index = 0);
        // for ($i = 3; $i <= $total_row; $i++) {
        //     $datas[] = array(
        //         //excel
        //         'item_fg_id' => $data->val($i, 2),
        //         'item_rm_id' => $data->val($i, 3),
        //         'item_process_id' => $data->val($i, 4),
        //         'type' => $data->val($i, 5),
        //         'recyle' => $data->val($i, 6),
        //         'composition' => $data->val($i, 7),
        //         'priority' => $data->val($i, 8)
        //     );
        // }
        // $datas['total'] = count($datas);
        // echo json_encode($datas);
        // unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/bom.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/bom.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/bom.txt";
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
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $item_process = $this->crud->read('item_process', [], ["id" => $data['item_process_id']]);

            $item_fg_id = $data['item_fg_id'];
            // $menu_loading = $this->crud->query("SELECT a.item_fg_id, SUM(a.runner) as runner, b.cavity_standard
            // FROM menu_loadings a JOIN molds b on a.mold_id = b.id
            // WHERE a.item_fg_id = '$item_fg_id' group by a.item_fg_id");


            $bom = $this->crud->read('bom', [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);
            $uom = $this->crud->read('uom', [], ["name" => $data['uom'], "deleted" => 0]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_process->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Flow ID" . $data['item_process_id'] . " Not Found", "theme" => "error"));
                // } elseif (empty($menu_loading[0]->item_fg_id)) {
                //     echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] . " in Menu Loading Not Found", "theme" => "error"));
                // } elseif ($item_rm->item_family_id == 'P06' && $data['composition'] != "") {
                //     echo json_encode(array("title" => "Alert", "message" => "Part ID" . $data['item_rm_id'] . " Product Family is VIRGIN ", "theme" => "error"));
            } elseif (!empty($bom->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part ID" . $data['item_rm_id'] . " is Duplicate Data", "theme" => "error"));
            } elseif (empty($uom->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "UoM" . $data['uom'] . " Not Found", "theme" => "error"));
            } else {
                // Hitung nilai untuk field composition
                // $weight = $item_fg->weight;
                // $runner = $menu_loading[0]->runner;
                // $cavity_standard = $menu_loading[0]->cavity_standard;

                $weight = $item_fg->weight;
                $runner = 0;
                $cavity_standard = 0;

                // if ($item_rm->item_family_id == 'P06') {
                //     $dataFinal['composition'] = (floatval($weight) + floatval($runner / $cavity_standard));
                // } elseif ($item_rm->item_family_id != 'P06') {
                //     $dataFinal['composition'] = $data['composition'];
                // }

                $dataFinal = array(
                    //field
                    "item_fg_id" => $data['item_fg_id'],
                    "item_rm_id" => $data['item_rm_id'],
                    "process_id" => $data['item_process_id'],
                    "type" => $data['type'],
                    "uom" => $data['uom'],
                    "recyle" => $data['recyle'],
                    "priority" => $data['priority'],
                    "composition" => $data['composition'],
                );

                // if ($item_rm->item_family_id == 'P06') {
                //     if ($runner == 0) {
                //         $dataFinal['composition'] = 0;
                //     } else {
                //         $dataFinal['composition'] = (floatval($weight) + floatval($runner / $cavity_standard));
                //     }
                // } elseif ($item_rm->item_family_id != 'P06') {
                //     $dataFinal['composition'] = $data['composition'];
                // }

                $send   = $this->crud->create('bom', $dataFinal);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            // $format  = date("Ymd");
            // header("Content-type: application/vnd-ms-excel");
            // header("Content-Disposition: attachment; filename=bom_$format.xls");

            return $this->export_excel();
        }

        $is_excel = ($option == 'excel');

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as item_rm_number, c.number_internal as item_rm_number_internal, c.name as item_rm_name, e.name as process_name, c.item_family_id as product_family, a.uom as uom, , d.name as product_family_name, a.composition as formatted_composition, c.cas_no');

        // CASE 
        //     WHEN a.composition = FLOOR(a.composition) 
        //         THEN FORMAT(CAST(FLOOR(a.composition) AS CHAR),0) 
        // ELSE 
        //     FORMAT(CAST(a.composition AS CHAR),2) 
        // END AS formatted_composition

        $this->db->from('bom a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('item_process e', 'a.process_id = e.id');
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#bom {border-collapse: collapse;width: 100%;font-size: 12px;}#bom td, #bom th {border: 1px solid #ddd;padding: 2px;}#bom tr:nth-child(even){background-color: #f2f2f2;}#bom tr:hover {background-color: #ddd;}#bom th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER BILL OF MATERIAL</h3>
            </div>
        </center>
        
        <table id="bom" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Part ID</th>
                <th>Part No Internal</th>
                <th>Part Name</th>
                <th>CAS No</th>
                <th>Process Name</th>
                <th>Type of Product</th>
                <th>% Recycle Part</th>
                <th>Product Family</th>
                <th>Unit Of Measure</th>
                <th>Composition</th>
                <th>Priority</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            $composition_value = '';

            if ($is_excel) {

                // $composition_value = '
                //     <td style="text-align:right; mso-number-format:&quot;@&quot;">
                //         ' . str_replace('.', ',', $data['composition']) . '
                //     </td>';

                $composition_value = '
                    <td style="
                        text-align:right;
                        mso-number-format:\'#,##0.0000\';
                    ">
                        ' . number_format((float)$data['composition'], 4, '.', '') . '
                    </td>';

            } else {

                $composition_value = '
                    <td>
                        ' . $this->format_number($data['composition']) . '
                    </td>';
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td>' . $data['item_rm_number_internal'] . '</td>
                    <td>' . $data['item_rm_name'] . '</td>
                    <td>' . $data['cas_no'] . '</td>
                    <td>' . $data['process_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['recyle'] . '</td>
                    <td>' . $data['product_family_name'] . '</td>
                    <td>' . $data['uom'] . '</td>
                        ' . $composition_value . '
                    <td>' . $data['priority'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }


    public function export_excel()
    {
        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        Settings::setLocale('id_ID');
        $config = $this->db->get('config')->row();

        $this->db->select('
            a.*,
            b.number as item_fg_number,
            b.name as item_fg_name,
            c.number_internal as item_rm_number_internal,
            c.name as item_rm_name,
            e.name as process_name,
            d.name as product_family_name
        ');
        $this->db->from('bom a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('item_process e', 'a.process_id = e.id');
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BOM');

        $logoPath = FCPATH . 'assets/image/bri_favicon.png';

        if (file_exists($logoPath)) {

            $sheet->mergeCells('A1:A2');
            $sheet->getRowDimension(1)->setRowHeight(15);
            $sheet->getRowDimension(2)->setRowHeight(15);
            $sheet->getColumnDimension('A')->setWidth(6);

            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(30);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        $sheet->mergeCells('B1:D2');
        $sheet->setCellValue('B1', $config->name);
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B1')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('L1:N1');
        $sheet->mergeCells('L2:N2');
        $sheet->setCellValue('L1', 'Print Date : '.date('d M Y H:i:s'));
        $sheet->setCellValue('L2', 'Print By   : '.$this->session->username);
        $sheet->getStyle('L1:L2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('L1:L2')->getFont()->setSize(10);

        $sheet->mergeCells('A4:N4');
        $sheet->setCellValue('A4', 'MASTER BILL OF MATERIAL');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 6;
        $headers = [
            'No','Product ID','Product No','Product Name',
            'Part ID','Part No Internal','Part Name',
            'Process Name','Type of Product','% Recycle Part',
            'Product Family','UOM','Composition','Priority'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$row, $header);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $sheet->getStyle($col.$row)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(22);
        $sheet->getColumnDimension('L')->setWidth(14);
        $sheet->getColumnDimension('M')->setWidth(16);
        $sheet->getColumnDimension('N')->setWidth(12);

        $sheet->getStyle('C:C')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle("A$row:N$row")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        $row++;
        $no = 1;

        foreach ($records as $data) {

            $sheet->setCellValue("A$row", $no++);
            $sheet->setCellValue("B$row", $data['item_fg_id']);
            $sheet->setCellValue("C$row", $data['item_fg_number']);
            $sheet->setCellValue("D$row", $data['item_fg_name']);
            $sheet->setCellValue("E$row", $data['item_rm_id']);
            $sheet->setCellValue("F$row", $data['item_rm_number_internal']);
            $sheet->setCellValue("G$row", $data['item_rm_name']);
            $sheet->setCellValue("H$row", $data['process_name']);
            $sheet->setCellValue("I$row", $data['type']);
            $sheet->setCellValue("J$row", $data['recyle']);
            $sheet->setCellValue("K$row", $data['product_family_name']);
            $sheet->setCellValue("L$row", $data['uom']);

            $sheet->setCellValueExplicit(
                "M$row",
                (float)$data['composition'],
                DataType::TYPE_NUMERIC
            );

            $sheet->setCellValueExplicit(
                "C$row",
                $data['item_fg_number'],
                DataType::TYPE_STRING
            );

            // $sheet->getStyle("M$row")
            //     ->getNumberFormat()
            //     ->setFormatCode('#,##0.0000');

            $sheet->getStyle("M$row")
                ->getNumberFormat()
                ->setFormatCode('0.0000');


            $sheet->setCellValue("N$row", $data['priority']);

            $row++;
        }

        $sheet->getStyle("A6:N".($row-1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->freezePane('A7');

        $filename = 'bom_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportTemplate() {
        // $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet = new Spreadsheet();
        $comments = [
            'B2' => ['admin:','Fill with ID Finish Good'],
            'C2' => ['admin:','Fill with ID Raw Material'],
            'D2' => ['admin:','Fill with ID Process from Master Process'],
            'E2' => ['admin:','1 = Original', '2 = Recycle', '3 = Both'],
            'F2' => ['admin:','Fill with Name from Master Unit Of Measure'],
            'G2' => ['admin:','If','Type = 1, Recycle = 0','Type = 2, Recycle = 100','Type = 3, Recycle = x'],                           // Comment for F2
            'H2' => ['admin:','Fill wih qty need per item (only number)'],
            'I2' => ['admin:','1 = main priority', '2 = optional'],
        ]; 
        // Add template to the first sheet
        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('BOM');
        $templateSheet->mergeCells('A1:I1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16) ->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(25);
        $templateSheet->getColumnDimension('C')->setWidth(25);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(25);
        $templateSheet->getColumnDimension('F')->setWidth(25);
        $templateSheet->getColumnDimension('G')->setWidth(25);
        $templateSheet->getColumnDimension('H')->setWidth(25);
        $templateSheet->getColumnDimension('I')->setWidth(25);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD BOM');
        $templateSheet->setCellValue('A2', 'No');
        $templateSheet->setCellValue('B2', 'PRODUCT ID');
        $templateSheet->setCellValue('C2', 'PART ID');
        $templateSheet->setCellValue('D2', 'ID PROCESS');
        $templateSheet->setCellValue('E2', 'TYPE');
        $templateSheet->setCellValue('F2', 'UOM');
        $templateSheet->setCellValue('G2', 'RECYCLE');
        $templateSheet->setCellValue('H2', 'COMPOSITION');
        $templateSheet->setCellValue('I2', 'PRIORITY');
        $templateSheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A2')->getFont()->setBold(true);
        $templateSheet->getStyle('B2:G2')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('H2')->getFont()->setBold(true);
        $templateSheet->getStyle('I2')->getFont()->setBold(true);
        $templateSheet->getStyle('A2:I2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
        // Second Sheet: Product (FG)
        $item_fgSheet = $spreadsheet->createSheet(1);
        $item_fgSheet->setTitle('Product (FG)');

        // Fetch data from the item_fg table
        $item_fg = $this->db->get('item_fg')->result_array();
        $item_fgSheet->getColumnDimension('A')->setWidth(10);
        $item_fgSheet->getColumnDimension('B')->setWidth(25);
        $item_fgSheet->getColumnDimension('C')->setWidth(25);
        $item_fgSheet->getColumnDimension('D')->setWidth(25);

        // Set header
        $item_fgSheet->setCellValue('A1', 'No');
        $item_fgSheet->setCellValue('B1', 'Product ID');
        $item_fgSheet->setCellValue('C1', 'Product No');
        $item_fgSheet->setCellValue('D1', 'Product Name');
        $item_fgSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_fgSheet->getStyle('A1:D1')->getFont()->setBold(true);
        $item_fgSheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        // Populate the data
        $rowItem_fg = 2;
        $rowNumItem_fg = 1;
        foreach ($item_fg as $itemfg) {
            $item_fgSheet->setCellValue('A' . $rowItem_fg, $rowNumItem_fg);
            $item_fgSheet->setCellValue('B' . $rowItem_fg, $itemfg['id']);
            $item_fgSheet->setCellValue('C' . $rowItem_fg, $itemfg['number']);
            $item_fgSheet->setCellValue('D' . $rowItem_fg, $itemfg['name']);
            $item_fgSheet->getStyle('A' . $rowItem_fg . ':C' . $rowItem_fg)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_fgSheet->getStyle('D' . $rowItem_fg)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_fgSheet->getStyle('A' . $rowItem_fg . ':D' . $rowItem_fg)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rowItem_fg++;
            $rowNumItem_fg++;
        }

        // Third Sheet: Part (RM)
        $item_rmSheet = $spreadsheet->createSheet(2);
        $item_rmSheet->setTitle('Part (RM)');

        // Fetch data from the item_rm table
        $item_rm = $this->db->get('item_rm')->result_array();
        $item_rmSheet->getColumnDimension('A')->setWidth(10);
        $item_rmSheet->getColumnDimension('B')->setWidth(25);
        $item_rmSheet->getColumnDimension('C')->setWidth(25);
        $item_rmSheet->getColumnDimension('D')->setWidth(50);

        // Set header
        $item_rmSheet->setCellValue('A1', 'No');
        $item_rmSheet->setCellValue('B1', 'Part ID');
        $item_rmSheet->setCellValue('C1', 'Part No');
        $item_rmSheet->setCellValue('D1', 'Part Name');
        $item_rmSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_rmSheet->getStyle('A1:D1')->getFont()->setBold(true);
        $item_rmSheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Populate the data
        $rowItem_rm = 2;
        $rowNumItem_rm = 1;
        foreach ($item_rm as $itemrm) {
            $item_rmSheet->setCellValue('A' . $rowItem_rm, $rowNumItem_rm);
            $item_rmSheet->setCellValue('B' . $rowItem_rm, $itemrm['id']);
            $item_rmSheet->setCellValue('C' . $rowItem_rm, $itemrm['number']);
            $item_rmSheet->setCellValue('D' . $rowItem_rm, $itemrm['name']);
            $item_rmSheet->getStyle('A' . $rowItem_rm . ':C' . $rowItem_rm)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $item_rmSheet->getStyle('D' . $rowItem_rm)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_rmSheet->getStyle('A' . $rowItem_rm . ':D' . $rowItem_rm)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rowItem_rm++;
            $rowNumItem_rm++;
        }

        // Fourth Sheet: Process
        $itemprocessSheet = $spreadsheet->createSheet(3);
        $itemprocessSheet->setTitle('Process');

        // Fetch data from the item_process table
        $item_process = $this->db->get('item_process')->result_array();
        $itemprocessSheet->getColumnDimension('A')->setWidth(10);
        $itemprocessSheet->getColumnDimension('B')->setWidth(25);
        $itemprocessSheet->getColumnDimension('C')->setWidth(25);

        // Set header
        $itemprocessSheet->setCellValue('A1', 'No');
        $itemprocessSheet->setCellValue('B1', 'ID Process');
        $itemprocessSheet->setCellValue('C1', 'Process Name');
        $itemprocessSheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $itemprocessSheet->getStyle('A1:C1')->getFont()->setBold(true);
        $itemprocessSheet->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Populate the data
        $rowItem_process = 2;
        $rowNumItem_process = 1;
        foreach ($item_process as $itemprocess) {
            $itemprocessSheet->setCellValue('A' . $rowItem_process, $rowNumItem_process);
            $itemprocessSheet->setCellValue('B' . $rowItem_process, $itemprocess['id']);
            $itemprocessSheet->setCellValue('C' . $rowItem_process, $itemprocess['name']);
            $itemprocessSheet->getStyle('A' . $rowItem_process . ':B' . $rowItem_process)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $itemprocessSheet->getStyle('C' . $rowItem_process)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $itemprocessSheet->getStyle('A' . $rowItem_process . ':C' . $rowItem_process)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rowItem_process++;
            $rowNumItem_process++;
        }

        $spreadsheet->setActiveSheetIndex(0); 
        
        // Set the header for the download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_bom.xls"');
        header('Cache-Control: max-age=0');
        // Create the writer and output the file
        // $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
