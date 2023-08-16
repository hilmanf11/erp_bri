<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Machines extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[30]|is_unique[machines.number]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/machines');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('machines', ["name" => $post]);
         echo json_encode($send);
     }

     //CODE OTOMATIS
     public function autoid($item_category_number, $item_family_number, $type_process_id){
         $code = $item_category_number.$item_family_number.$type_process_id; 
         $sql = $this->db->query("SELECT max(`id`) as kode From machines where id like '%$code%'");
         $row = $sql->row();
         $kode = substr($row->kode, 7);
         $autoid = $code."-". sprintf("%04s", $kode + 1);
         echo $autoid;
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
             $this->db->select('a.*, b.name as item_type_process_name , c.name as item_type_name , d.name as item_category_name , e.name as item_familys_name');
             $this->db->from('machines a');
             $this->db->join('type_process b', 'a.type_process_id = b.id');
             $this->db->join('types c', 'a.type_id = c.id');
             $this->db->join('item_categories d', 'a.item_category_number = d.number');
             $this->db->join('item_familys e', 'a.item_family_number = e.number');
             $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_type_process_name"){
                        $this->db->like("b.name", $filter->value);

                    }elseif($filter->field == "item_type_name"){
                        $this->db->like("c.name", $filter->value);

                    }elseif($filter->field == "item_category_name"){
                            $this->db->like("d.name", $filter->value);
    
                    }elseif($filter->field == "item_familys_name"){
                            $this->db->like("e.name", $filter->value);
    
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
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
     //CREATE DATA
     public function create()
     {
         if ($this->input->post()) {
             if ($this->form_validation->run() == TRUE) {
                 $post   = $this->input->post();
                 $send   = $this->crud->create('machines', $post);
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
                  $send = $this->crud->update('machines', ["id" => $id], $post);
                  echo $send;
              } else {
                show_error("Cannot Process your request");
              
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('machines', $data);
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
                 'asset_no' => $data->val($i, 2),
                 'machine_no' => $data->val($i, 3),
                 'name_of_machine' => $data->val($i, 4),
                 'category' => $data->val($i, 5),
                 'product_family' => $data->val($i, 6),
                 'process_type' => $data->val($i, 7),
                 'specification' => $data->val($i, 8),
                 'purchase_date' => $data->val($i, 9),
                 'manufacturing_date' => $data->val($i, 10),
                 'maker' => $data->val($i, 11),
                 'tonage_of_machine' => $data->val($i, 12),
                 'uom' => $data->val($i, 13),
                 'vacum' => $data->val($i, 14),
                 'rt' => $data->val($i, 15),
                 'type' => $data->val($i, 16),
                 'brand' => $data->val($i, 17),
                 'status' => $data->val($i, 18)
                 
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('excel/failed/machines.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('excel/failed/machines.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "excel/failed/machines.txt";
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
             $data = $this->input->post('data');//field excel

             //AUTOID
             $code = $data['category'].$data['product_family'].$data['process_type']; 
             $sql = $this->db->query("SELECT max(`id`) as kode From machines where id like '%$code%'");
             $row = $sql->row();
             $kode = substr($row->kode, 7);
             $autoid = $code."-". sprintf("%04s", $kode + 1);

            //Cek Process Number                //table             //field           //field excel
            $type_process = $this->crud->read('type_process', [], ["number" => $data['process_type']]);
            $types = $this->crud->read('types', [], ["number" => $data['type']]);
            $category = $this->crud->read('item_categories', [], ["number" => $data['category']]);
            $prod_fam = $this->crud->read('item_familys', [], ["number" => $data['product_family']]);
            $machines = $this->crud->read('machines', [], ["number" => $data['machine_no']]);

          if (empty($type_process->name)) {
              echo json_encode(array("title" => "Not Found", "message" => "Process Type " . $data['process_type'] . " is Not Found", "theme" => "error"));
            } elseif (empty($types->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Type " . $data['type'] . " is Not Found", "theme" => "error"));
            } elseif (empty($category->name)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Category " . $data['category'] . " is Not Found", "theme" => "error"));
            } elseif (empty($prod_fam->name)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['product_family'] . " is Not Found", "theme" => "error"));
            } elseif (!empty($machines->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Machine No " . $data['machine_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                 $dataFinal = array(
                     //field        //excel
                     "id" => $autoid,
                     "asset_id" => $data['asset_no'],
                     "number" => $data['machine_no'],
                     "name" => $data['name_of_machine'],
                     "item_category_number" => $data['category'],
                     "item_family_number" => $data['product_family'],
                     "type_process_id" => $type_process->id,
                     "specification" => $data['specification'],
                     "purchase_date" => $data['purchase_date'],
                     "manufactur_date" => $data['manufacturing_date'],
                     "maker" => $data['maker'],
                     "toonage" => $data['tonage_of_machine'],
                     "uom" => $data['uom'],
                     "vacum" => $data['vacum'],
                     "rt" => $data['rt'],
                     "type_id" => $types->id,
                     "brand" => $data['brand'],
                     "status" => $data['status'],
                 );
                 $send   = $this->crud->create('machines', $dataFinal);
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
             header("Content-Disposition: attachment; filename=machines_$format.xls");
         }
         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
         $config_iso = $this->db->get('config_iso')->row();
 
         $this->db->select('a.*, b.name as item_type_process_name , c.name as item_type_name , d.name as item_category_name , e.name as item_familys_name');
         $this->db->from('machines a');
         $this->db->join('type_process b', 'a.type_process_id = b.id');
         $this->db->join('types c', 'a.type_id = c.id');
         $this->db->join('item_categories d', 'a.item_category_number = d.number');
         $this->db->join('item_familys e', 'a.item_family_number = e.number');
         $this->db->where('a.deleted', 0);
         $this->db->order_by('id', 'ASC');
         $records = $this->db->get()->result_array();
         
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#machines {border-collapse: collapse;width: 100%;font-size: 12px;}#machines td, #machines th {border: 1px solid #ddd;padding: 2px;}#machines tr:nth-child(even){background-color: #f2f2f2;}#machines tr:hover {background-color: #ddd;}#machines th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER machines</small>
                         </td>
                     </tr>
                 </table>
             </div>
             <div style="float: right; font-size: 12px; text-align: right;">
                 <table style="width:100%; font-size:10px;">
                     <tr>
                         <td width="60">Doc No</td>
                         <td width="5">:</td>
                         <td width="100">' . $config_iso->doc_customer . '</td>
                     </tr>
                     <tr>
                         <td>Form</td>
                         <td>:</td>
                         <td>' . $config_iso->form_customer . '</td>
                     </tr>
                     <tr>
                         <td>Print Date</td>
                         <td>:</td>
                         <td>' . date("Y-m-d H:i") . '</td>
                     </tr>
                     <tr>
                         <td>Print By</td>
                         <td>:</td>
                         <td>' . $this->session->name . '</td>
                     </tr>
                 </table> 
             </div>
         </center>
         <br><br><br><br>
         
         <table id="machines" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>Machine Id</th>
                 <th>Asset No</th>
                 <th>Machine No</th>
                 <th>Name of Machine</th>
                 <th>Category</th>
                 <th>Product Family</th>
                 <th>Process Type</th>
                 <th>Specification</th>
                 <th>Purchase Date</th>
                 <th>Manufacturing Date</th>
                 <th>Maker</th>
                 <th>Tonage of Machine</th>
                 <th>Uom</th>
                 <th>Vacum</th>
                 <th>RT</th>
                 <th>Type</th>
                 <th>Brand</th>
                 <th>Status</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['id'] . '</td>
                         <td>' . $data['asset_id'] . '</td>
                         <td>' . $data['number'] . '</td>
                         <td>' . $data['name'] . '</td>
                         <td>' . $data['item_category_name'] . '</td>
                         <td>' . $data['item_familys_name'] . '</td>
                         <td>' . $data['item_type_process_name'] . '</td>
                         <td>' . $data['specification'] . '</td>
                         <td>' . $data['purchase_date'] . '</td>
                         <td>' . $data['manufactur_date'] . '</td>
                         <td>' . $data['maker'] . '</td>
                         <td>' . $data['toonage'] . '</td>
                         <td>' . $data['uom'] . '</td>
                         <td>' . $data['vacum'] . '</td>
                         <td>' . $data['rt'] . '</td>
                         <td>' . $data['item_type_name'] . '</td>
                         <td>' . $data['brand'] . '</td>
                         <td>' . $data['status'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
