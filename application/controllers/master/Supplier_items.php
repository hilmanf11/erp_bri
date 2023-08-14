<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supplier_items extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Code', 'required|min_length[1]|max_length[30]|is_unique[supplier_items.item_id]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/supplier_items');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('supplier_items', ["name" => $post]);
         echo json_encode($send);
     }

    //CODE OTOMATIS
    public function autoid() {
        $currentYear = date('Y');
        $sql = $this->db->query("SELECT max(`id`) as kode From supplier_items");
        $row = $sql->row();
        
        // Extract year and number from the existing code
        $existingYear = substr($row->kode, 2, 4);
        $existingNumber = substr($row->kode, -4);
    
        // Check if the year has changed
        if ($existingYear != $currentYear) {
            // Reset the number to 1
            $newNumber = 1;
        } else {
            // Increment the existing number
            $newNumber = intval($existingNumber) + 1;
        }
    
        // Format the new number with leading zeros
        $newNumberFormatted = sprintf("%04s", $newNumber);
    
        // Construct the new auto-generated ID
        $autoid = "SI" . $currentYear . $newNumberFormatted;
        
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
             $this->db->select('a.*, b.name as suppliers_name, c.number as item_number, c.name as item_name, c.mpq as mpq, 
             c.moq as moq, c.leadtime as leadtime, c.safety_stock as safety_stock');
             $this->db->from('supplier_items a');
             $this->db->join('suppliers b', 'a.supplier_id = b.id');
             $this->db->join('items c', 'a.item_id = c.id');
             $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "suppliers_name"){
                        $this->db->like("b.name", $filter->value);
                    }elseif($filter->field == "item_number"){
                        $this->db->like("c.number", $filter->value);

                    }elseif($filter->field == "item_name"){
                        $this->db->like("c.name", $filter->value);
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
                 $send   = $this->crud->create('supplier_items', $post);
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
                  $send = $this->crud->update('supplier_items', ["id" => $id], $post);
                  echo $send;
              } else {
                show_error("Cannot Process your request");
              
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('supplier_items', $data);
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
                 'supplier_id' => $data->val($i, 2),
                 'product_no' => $data->val($i, 3),
                 'product_supplier' => $data->val($i, 4),
                 'currency' => $data->val($i, 5),
                 'price' => $data->val($i, 6),
                 'calculate_mpq' => $data->val($i, 7),
                 'description' => $data->val($i, 8)
                 
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('excel/failed/supplier_items.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('excel/failed/supplier_items.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "excel/failed/supplier_items.txt";
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
            $data       = $this->input->post('data');

            $currentYear = date('Y');
            $sql = $this->db->query("SELECT max(`id`) as kode From supplier_items");
            $row = $sql->row();
            
            // Extract year and number from the existing code
            $existingYear = substr($row->kode, 2, 4);
            $existingNumber = substr($row->kode, -4);
        
            // Check if the year has changed
            if ($existingYear != $currentYear) {
                // Reset the number to 1
                $newNumber = 1;
            } else {
                // Increment the existing number
                $newNumber = intval($existingNumber) + 1;
            }
        
            // Format the new number with leading zeros
            $newNumberFormatted = sprintf("%04s", $newNumber);
        
            // Construct the new auto-generated ID
            $autoid = "SI" . $currentYear . $newNumberFormatted;

             //Cek Process Number     //table        //field           //field excel
             $item = $this->crud->read('items', [], ["number" => $data['product_no']]);
             $supplier = $this->crud->read('suppliers', [], ["id" => $data['supplier_id']]);
             $supplier_items = $this->crud->read('supplier_items', [], ["item_id" => @$item->id]);

            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['supplier_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($supplier_items->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['product_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                 $dataFinal = array(
                     //field        //excel
                     "id" => $autoid,
                     "supplier_id" => $data['supplier_id'],
                     "item_id" => $item->id,
                     "item_supplier" => $data['product_supplier'],
                     "currency" => $data['currency'],
                     "price" => $data['price'],
                     "calculate" => $data['calculate_mpq'],
                     "description" => $data['description'],
                 );
                 $send= $this->crud->create('supplier_items', $dataFinal);
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
             header("Content-Disposition: attachment; filename=supplier_items_$format.xls");
         }
         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
         $config_iso = $this->db->get('config_iso')->row();
 
         $this->db->select('a.*, b.name as suppliers_name, c.number as item_number, c.name as item_name, c.mpq as mpq, 
             c.moq as moq, c.leadtime as leadtime, c.safety_stock as safety_stock');
         $this->db->from('supplier_items a');
         $this->db->join('suppliers b', 'a.supplier_id = b.id');
         $this->db->join('items c', 'a.item_id = c.id');
         $this->db->where('a.deleted', 0);
         $this->db->order_by('id', 'ASC');
         $records = $this->db->get()->result_array();
         
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER supplier_items</small>
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
         
         <table id="supplier_items" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>ID</th>
                 <th>Supplier Name</th>
                 <th>Product Number</th>
                 <th>Supplier Product</th>
                 <th>MPQ</th>
                 <th>MOQ</th>
                 <th>Leadtime</th>
                 <th>Currency</th>
                 <th>Price</th>
                 <th>Safety Stock</th>
                 <th>Calculate MPQ</th>
                 <th>Description</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['id'] . '</td>
                         <td>' . $data['suppliers_name'] . '</td>
                         <td>' . $data['item_number'] . '</td>
                         <td>' . $data['item_supplier'] . '</td>
                         <td>' . $data['mpq'] . '</td>
                         <td>' . $data['moq'] . '</td>
                         <td>' . $data['leadtime'] . '</td>
                         <td>' . $data['currency'] . '</td>
                         <td>' . $data['price'] . '</td>
                         <td>' . $data['safety_stock'] . '</td>
                         <td>' . $data['calculate'] . '</td>
                         <td>' . $data['description'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
