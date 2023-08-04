<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Items extends CI_Controller
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
        $this->form_validation->set_rules('code', 'Code', 'required|min_length[1]|max_length[10]|is_unique[items.code]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/items');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('items', ["name" => $post]);
         echo json_encode($send);
     }
     //CODE OTOMATIS
     public function autoid(){
         $sql = $this->db->query("SELECT max(`number`) as kode From items");
         $row = $sql->row();
         $kode = substr($row->kode, 1);
         $autoid = "C". sprintf("%03s", $kode + 1);
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
             $this->db->select('a.*, b.name as item_category_name , c.name as item_familys_name, d.name as item_uom_name');
             $this->db->from('items a');
             $this->db->join('item_categories b', 'a.item_category_code = b.code');
             $this->db->join('item_familys c', 'a.item_family_code = c.code');
             $this->db->join('uom d', 'a.uom_number = d.number');
             $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_category_name"){
                        $this->db->like("b.code", $filter->value);

                    }elseif($filter->field == "item_familys_name"){
                        $this->db->like("c.code", $filter->value);
                    
                    }elseif($filter->field == "item_uom_name"){
                        $this->db->like("d.number", $filter->value);

                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
             $this->db->order_by('a.number', 'ASC');
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
                 $send   = $this->crud->create('items', $post);
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
              if ($this->form_validation->run() == TRUE) {
                  $id   = base64_decode($this->input->get('id'));
                  $post = $this->input->post();
                  $send = $this->crud->update('items', ["id" => $id], $post);
                  echo $send;
              } else {
                  show_error(validation_errors());
              }
          } else {
              show_error("Cannot Process your request");
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('items', $data);
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
                 'name' => $data->val($i, 2),
                 'code' => $data->val($i, 3),
                 'type' => $data->val($i, 4),
                 'address' => $data->val($i, 5),
                 'billing_address' => $data->val($i, 6),
                 'contact_person' => $data->val($i, 7),
                 'telp' => $data->val($i, 8),
                 'billing_telp' => $data->val($i, 9),
                 'email' => $data->val($i, 10),
                 'website' => $data->val($i, 11),
                 'currency' => $data->val($i, 12),
                 'payment_term' => $data->val($i, 13),
                 'bank_account' => $data->val($i, 14),
                 'bank_name' => $data->val($i, 15)
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('excel/failed/items.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('excel/failed/items.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "excel/failed/items.txt";
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
             //Cek Process Number
             $items = $this->crud->read('items', [], ["code" => $data['code']]);
 
             $sql = $this->db->query("SELECT max(`number`) as kode From items");
             $row = $sql->row();
             $kode = substr($row->kode, 1);
             $autoid = "C". sprintf("%03s", $kode + 1);
 
             if (!empty($main_process->id)) {
                 echo json_encode(array("title" => "Available", "message" => "Code " . $data['code'] . " has been Available", "theme" => "error"));
             } else {
                 $dataFinal = array(
                     //field
                     "number" => $autoid,
                     "name" => $data['name'],
                     "code" => $data['code'],
                     "type" => $data['type'],
                     "address" => $data['address'],
                     "address_billing" => $data['billing_address'],
                     "attention" => $data['contact_person'],
                     "telp" => $data['telp'],
                     "telp_billing" => $data['billing_telp'],
                     "email" => $data['email'],
                     "website" => $data['website'],
                     "currency" => $data['currency'],
                     "payment_term" => $data['payment_term'],
                     "bank_account" => $data['bank_account'],
                     "bank_name" => $data['bank_name'],
                 );
                 $send   = $this->crud->create('items', $dataFinal);
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
             header("Content-Disposition: attachment; filename=items_$format.xls");
         }
         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
         $config_iso = $this->db->get('config_iso')->row();
 
         $this->db->select('*');
         $this->db->from('items');
         $this->db->where('deleted', 0);
         $this->db->order_by('name', 'ASC');
         $records = $this->db->get()->result_array();
         
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#items {border-collapse: collapse;width: 100%;font-size: 12px;}#items td, #items th {border: 1px solid #ddd;padding: 2px;}#items tr:nth-child(even){background-color: #f2f2f2;}#items tr:hover {background-color: #ddd;}#items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER CUSTOMER</small>
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
         
         <table id="items" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>Id</th>
                 <th>Code</th>
                 <th>Name</th>
                 <th>Type</th>
                 <th>Address</th>
                 <th>Billing Address</th>
                 <th>Contact Person</th>
                 <th>Billing Telp</th>
                 <th>Email</th>
                 <th>Website</th>
                 <th>Currency</th>
                 <th>Payment Term</th>
                 <th>Account No</th>
                 <th>Account Name</th>
                 <th>Bank Account</th>
                 <th>Bank Name</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['number'] . '</td>
                         <td>' . $data['code'] . '</td>
                         <td>' . $data['name'] . '</td>
                         <td>' . $data['type'] . '</td>
                         <td>' . $data['address'] . '</td>
                         <td>' . $data['address_billing'] . '</td>
                         <td>' . $data['telp'] . '</td>
                         <td>' . $data['telp_billing'] . '</td>
                         <td>' . $data['email'] . '</td>
                         <td>' . $data['website'] . '</td>
                         <td>' . $data['currency'] . '</td>
                         <td>' . $data['payment_term'] . '</td>
                         <td>' . $data['account_number'] . '</td>
                         <td>' . $data['account_name'] . '</td>
                         <td>' . $data['bank_account'] . '</td>
                         <td>' . $data['bank_name'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
