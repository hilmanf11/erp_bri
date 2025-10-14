<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class item_fg extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[item_fg.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_fg');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        //$send = $this->crud->query("SELECT * FROM item_fg WHERE number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%'");
        $send = $this->crud->query("SELECT * FROM item_fg WHERE (number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%') AND status = 0");
        echo json_encode($send);
    }

    //GET DATA
    public function readItemFG()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT id, number as item_number, name as item_name, box_sub, specification FROM item_fg WHERE (number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%') AND status = 0");
        echo json_encode($send);
    }

    //GET DATA
    public function readCompounds()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        //$send = $this->crud->query("SELECT * FROM item_fg WHERE number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%'");
        $send = $this->crud->query("SELECT * FROM item_fg WHERE item_family_number = 'CD' AND (number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%') AND status = 0");
        echo json_encode($send);
    }

    //GET DATA
    public function readRubberParts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        //$send = $this->crud->query("SELECT * FROM item_fg WHERE number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%'");
        $send = $this->crud->query("SELECT * FROM item_fg WHERE item_family_number = 'RP' AND (number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%') AND status = 0");
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
            $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name,  d.name as item_family_sub_name, count(e.item_fg_id) as total_mold, h.min, h.max');
            $this->db->from('item_fg a');
            $this->db->join('item_categories b', 'a.item_category_number = b.number');
            $this->db->join('item_familys c', 'a.item_family_number = c.number');
            $this->db->join('item_family_subs d', 'a.item_family_sub_number = d.number', 'left');
            $this->db->join('mold_items e', 'a.id = e.item_fg_id', 'left');
            $this->db->join('customer_items f', 'f.item_fg_id = a.id', 'left');
            $this->db->join('customers g', 'f.customer_id = g.id', 'left');
            $this->db->join('setting_stocks h', "g.type = h.kind AND h.item_category_id = 'C01'", 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "total_mold") {
                        $this->db->having("count(e.item_fg_id)", $filter->value);
                    } elseif ($filter->field == "min") {
                        $this->db->like("h.min", $filter->value);
                    } elseif ($filter->field == "max") {
                        $this->db->like("h.max", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "item_family_sub_name") {
                        $this->db->like("d.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
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

    //CODE OTOMATIS
    public function autoid($item_category_number, $item_family_number, $item_family_sub_number = "NA")
    {

        $code = $item_category_number . $item_family_number . $item_family_sub_number;
        $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From item_fg where id like '%$code%'");
        $row = $sql->row();
        $kode = substr($row->kode, -4);
        $autoid = $code . "-" . sprintf("%04s", $kode + 1);
        echo $autoid;
    }
    //MIN STOCK
    public function min_stock()
    {
        $sql = $this->db->query("SELECT min FROM setting_stocks WHERE item_category_id = 'FINISHED GOOD' GROUP BY item_category_id ASC");
        $row = $sql->row();
        echo $row;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $attachment = $this->crud->upload('attachment', ["pdf", "jpg", "jpeg", "png"], 'assets/image/item_fg/', ["id" => $post['id']], "item_fg", "attachment");
                $postFinal = array_merge($post, ["attachment" => $attachment]);
                $send   = $this->crud->create('item_fg', $postFinal);
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

            $dataFinal = array(
                'number' =>  $post['number'],
                'name' => $post['name'],
                'specification' => $post['specification'],
                'process' => $post['process'],
                'product_type' => $post['product_type'],
                'item_category_number' => $post['item_category_number'],
                'item_family_number' => $post['item_family_number'],
                'item_family_sub_number' => $post['item_family_sub_number'],
                'division_id' => $post['division_id'],
                'lot' => $post['lot'],
                'weight' => $post['weight'],
                'leadtime' => $post['leadtime'],
                'lifetime' => $post['lifetime'],
                'mpq' => $post['mpq'],
                'moq' => $post['moq'],
                'safety_stock' => $post['safety_stock'],
                'uom' => $post['uom'],
                'qty_box' => $post['qty_box'],
                'box_sub' => $post['box_sub'],
                'status' => $post['status']
            );
            // cek apakah data uploadan di isi

            $file = $_FILES['attachment']["name"];
            if ($file == null && $file == '') {
                $send = $this->crud->update('item_fg', ["id" => $id], $dataFinal);
            } else {
                $attachment = $this->crud->upload('attachment', ["pdf", "jpg", "jpeg", "png"], 'assets/image/item_fg/', ["id" => $post['id']], "item_fg", "attachment");
                $postFinal = array_merge($dataFinal, ["attachment" => $attachment]);
                $send = $this->crud->update('item_fg', ["id" => $id], $postFinal);
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
        //Table //Like //Field       //Where
        $file = $this->crud->read('item_fg', [], ["id" => $data['id']]);
        $send = $this->crud->delete('item_fg', $data);
        // $attachment = @$file->attachment;
        // $file_path = @$file->attachment;
        // $absolute_path = realpath($file_path);
        // if ($absolute_path !== false) {
        //     unlink($absolute_path);
        // } else {
        //     echo "Path file tidak valid.";
        // }
        // if (@unlink("$attachment")) {
        //     echo ("Success deleting $attachment");
        // } else {
        //     echo ("Error deleting $attachment");
        // }
        // @unlink($attachment);
        echo $send;
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
                'product_no' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'specification' => $data->val($i, 4),
                'process' => $data->val($i, 5),
                'product_type' => $data->val($i, 6),
                'category' => $data->val($i, 7),
                'product_family' => $data->val($i, 8),
                'sub_product_family' => $data->val($i, 9),
                'lot' => $data->val($i, 10),
                'weight' => $data->val($i, 11),
                'lead_time_production' => $data->val($i, 12),
                'lifetime' => $data->val($i, 13),
                'mpq' => $data->val($i, 14),
                'moq' => $data->val($i, 15),
                'safety_stock' => $data->val($i, 16),
                'unit_of_measure' => $data->val($i, 17),
                'qty_box' => $data->val($i, 18),
                'box_sub' => $data->val($i, 19),
                'status' => $data->val($i, 20)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/item_fg.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_fg.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_fg.txt";
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

            //Cek Process Number        //table                   //field           //field excel
            $category = $this->crud->read('item_categories', [], ["number" => $data['category']]);
            $prod_fam = $this->crud->read('item_familys', [], ["number" => $data['product_family']]);
            $prod_sub_fam = $this->crud->read('item_family_subs', [], ["number" => $data['sub_product_family']]);
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['product_no']]);

            if (empty($category->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category Code " . $data['category'] . " Not Found", "theme" => "error"));
            } elseif (empty($prod_fam->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family Code " . $data['product_family'] . " Not Found", "theme" => "error"));
                // } elseif (empty($prod_sub_fam->number)) {
                //     echo json_encode(array("title" => "Not Found", "message" => "Sub Product Family Code " . $data['sub_product_family'] . " Not Found", "theme" => "error"));
            } elseif (!empty($item_fg->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['product_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                //autoid
                if (empty($prod_sub_fam->number)) {
                    $code = $data['category'] . $data['product_family'] . "NA";
                } else {
                    $code = $data['category'] . $data['product_family'] . $data['sub_product_family'];
                }
                $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From item_fg where id like '%$code%'");
                $row = $sql->row();
                $kode = substr($row->kode, -4);
                $autoid = $code . "-" . sprintf("%04s", $kode + 1);


                $dataFinal = array(
                    //field        //excel
                    "id" => $autoid,
                    "number" => $data['product_no'],
                    "name" => $data['name'],
                    "specification" => $data['specification'],
                    "process" => $data['process'],
                    "product_type" => $data['product_type'],
                    "item_category_number" => $data['category'],
                    "item_family_number" => $data['product_family'],
                    "item_family_sub_number" => $data['sub_product_family'],
                    "lot" => $data['lot'],
                    "weight" => $data['weight'],
                    "leadtime" => $data['lead_time_production'],
                    "lifetime" => $data['lifetime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "safety_stock" => $data['safety_stock'],
                    "uom" => $data['unit_of_measure'],
                    "qty_box" => $data['qty_box'],
                    "box_sub" => $data['box_sub'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('item_fg', $dataFinal);
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
            header("Content-Disposition: attachment; filename=item_fg_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name,  d.name as item_family_sub_name, count(e.item_fg_id) as total_mold, h.min, h.max');
        $this->db->from('item_fg a');
        $this->db->join('item_categories b', 'a.item_category_number = b.number');
        $this->db->join('item_familys c', 'a.item_family_number = c.number');
        $this->db->join('item_family_subs d', 'a.item_family_sub_number = d.number', 'left');
        $this->db->join('mold_items e', 'a.id = e.item_fg_id', 'left');
        $this->db->join('customer_items f', 'f.item_fg_id = a.id', 'left');
        $this->db->join('customers g', 'f.customer_id = g.id', 'left');
        $this->db->join('setting_stocks h', "g.type = h.kind AND h.item_category_id = 'C01'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#item_fg td, #item_fg th {border: 1px solid #ddd;padding: 2px;}#item_fg tr:nth-child(even){background-color: #f2f2f2;}#item_fg tr:hover {background-color: #ddd;}#item_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM FINISH GOOD</h3>
            </div>
        </center>
        
        <table id="item_fg" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Total Mold</th>
                <th>Process Type</th>
                <th>Product Type</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Sub Product Family</th>
                <th>Lot</th>
                <th>Weight (Gram)</th>
                <th>Leadtime (Day)</th>
                <th>Lifetime (Day)</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Uom</th>
                <th>Qty/Box</th>
                <th>Qty/Sub Box</th>
                <th>Min</th>
                <th>Max</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['name'] . '</td>
                        <td>' . $data['total_mold'] . '</td>
                        <td>' . $data['process'] . '</td>
                        <td>' . $data['product_type'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['item_family_sub_name'] . '</td>
                        <td>' . $data['lot'] . '</td>
                        <td>' . $data['weight'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['lifetime'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['moq'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty_box'] . '</td>
                        <td>' . $data['box_sub'] . '</td>
                        <td>' . $data['min'] . '</td>
                        <td>' . $data['max'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
