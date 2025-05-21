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

class Equivalent extends CI_Controller
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
            $this->load->view('master/equivalent');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function readRmId()
    {
        $this->db->select('a.id, a.number, a.name');
        $this->db->from('item_rm a');
        $this->db->join('item_familys b','a.item_family_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.number !=', 'CD');
        $this->db->order_by('a.name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            //$get = $this->input->get();

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query

            $this->db->select('a.*,b.id as item_rm_id, b.name as item_rm_name, b.number as item_rm_number, a.eq_1, a.eq_2, a.eq_3, a.eq_4, a.eq_5, c.name as eq_1_name, d.name as eq_2_name, e.name as eq_3_name, f.name as eq_4_name, g.name as eq_5_name');
            $this->db->from('equivalents a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_rm c', 'a.eq_1 = c.id');
            $this->db->join('item_rm d', 'a.eq_2 = d.id','left');
            $this->db->join('item_rm e', 'a.eq_3 = e.id','left');
            $this->db->join('item_rm f', 'a.eq_4 = f.id','left');
            $this->db->join('item_rm g', 'a.eq_5 = g.id','left');
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
            $post = $this->input->post();

            $data = array(
                //field
                "item_rm_id" => $post['item_rm_id'],
                "eq_1" => $post['eq_1'],
                "eq_2" => $post['eq_2'],
                "eq_3" => $post['eq_3'],
                "eq_4" => $post['eq_4'],
                "eq_5" => $post['eq_5'],
            );
            // $eq = $this->crud->read("equivalents", [], ["item_rm_id" => $post['item_rm_id']]);
            // if (@$eq->item_rm_id != "") {
            //     $send = $this->crud->update('equivalents', ["item_rm_id" => $post['item_rm_id']], $data);
            // } else {
                $send = $this->crud->create('equivalents', $data);
            //}
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
            $data = array(
                //field
                "item_rm_id" => $post['item_rm_id'],
                "eq_1" => $post['eq_1'],
                "eq_2" => $post['eq_2'],
                "eq_3" => $post['eq_3'],
                "eq_4" => $post['eq_4'],
                "eq_5" => $post['eq_5'],
            );
            $send = $this->crud->update('equivalents', ["id" => $id], $data);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('equivalents', $data);
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
                'item_rm_id' => $sheet->getCell('B' . $i)->getValue(),
                'eq_1' => $sheet->getCell('C' . $i)->getValue(),
                'eq_2' => $sheet->getCell('D' . $i)->getValue(),
                'eq_3' => $sheet->getCell('E' . $i)->getValue(),
                'eq_4' => $sheet->getCell('F' . $i)->getValue(),
                'eq_5' => $sheet->getCell('G' . $i)->getValue()
            );
        }
    
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/equivalent.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/equivalent.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/equivalent.txt";
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
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);

            $eq = $this->crud->read('equivalents', [], ["item_rm_id" => $data['item_rm_id']]);

            if (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($eq->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part ID" . $data['item_rm_id'] . " is Duplicate Data", "theme" => "error"));
            } else {

                $dataFinal = array(
                    //field
                    "item_rm_id" => $data['item_rm_id'],
                    "eq_1" => $data['eq_1'],
                    "eq_2" => $data['eq_2'],
                    "eq_3" => $data['eq_3'],
                    "eq_4" => $data['eq_4'],
                    "eq_5" => $data['eq_5'],
                );

                $send   = $this->crud->create('equivalents', $dataFinal);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=master_equivalents_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name,a.eq_1,a.eq_2,a.eq_3,a.eq_4,a.eq_5');
        $this->db->from('equivalents a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->order_by('a.id', 'ASC');
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
                <th>Part ID</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Equivalent 1</th>
                <th>Equivalent 2</th>
                <th>Equivalent 3</th>
                <th>Equivalent 4</th>
                <th>Equivalent 5</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td>' . $data['item_rm_number'] . '</td>
                    <td>' . $data['item_rm_name'] . '</td>
                    <td>' . $data['eq_1'] . '</td>
                    <td>' . $data['eq_2'] . '</td>
                    <td>' . $data['eq_3'] . '</td>
                    <td>' . $data['eq_4'] . '</td>
                    <td>' . $data['eq_5'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'B2' => ['admin:','Fill with ID Raw Material'],
        ]; 
        // Add template to the first sheet
        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('EQUIVALENT');
        $templateSheet->mergeCells('A1:G1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16) ->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(25);
        $templateSheet->getColumnDimension('C')->setWidth(25);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(25);
        $templateSheet->getColumnDimension('F')->setWidth(25);
        $templateSheet->getColumnDimension('G')->setWidth(25);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD EQUIVALENT');
        $templateSheet->setCellValue('A2', 'No');
        $templateSheet->setCellValue('B2', 'PART ID');
        $templateSheet->setCellValue('C2', 'EQUIVALENT 1');
        $templateSheet->setCellValue('D2', 'EQUIVALENT 2');
        $templateSheet->setCellValue('E2', 'EQUIVALENT 3');
        $templateSheet->setCellValue('F2', 'EQUIVALENT 4');
        $templateSheet->setCellValue('G2', 'EQUIVALENT 5');
        $templateSheet->getStyle('A2:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A2')->getFont()->setBold(true);
        $templateSheet->getStyle('B2:C2')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A2:G2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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

        // Second Sheet: Part (RM)
        $item_rmSheet = $spreadsheet->createSheet(2);
        $item_rmSheet->setTitle('Part (RM)');

        // Fetch data from the item_rm table
        $this->db->where('number !=', 'CD');
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

        $spreadsheet->setActiveSheetIndex(0); 
        
        // Set the header for the download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_equivalent.xls"');
        header('Cache-Control: max-age=0');
        // Create the writer and output the file
        // $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
