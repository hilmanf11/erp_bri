<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Transaction_type extends CI_Controller
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
        $this->form_validation->set_rules('name', 'Transaction Name', 'required|min_length[1]|max_length[100]|is_unique[transaction_type.name]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/transaction_type');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('transaction_type', ["type" => $post]);
         $newItem = [
            'type' => 'ALL',
            'name' => 'ALL'
         ];
         array_unshift($send, $newItem);
         echo json_encode($send);
     }
     
     //CODE OTOMATIS
     public function autoid($transname){
    
        $code = $transname; 
        $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From transaction_type where name like '%$code'");
        $row = $sql->row();
        $kode = substr($row->kode, -4);
        $autoid = substr($code, 0, 2) . "-" . sprintf("%04s", $kode + 1);
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
            $this->db->select('id, type, name, description, status, created_by, created_date, updated_by, updated_date');
            $this->db->from('transaction_type');
            $this->db->where('deleted', 0);

            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "type"){
                        $this->db->like("type", $filter->value);
                    }else if($filter->field == "name"){
                        $this->db->like("name", $filter->value);
                    }else if($filter->field == "description"){
                        $this->db->like("description", $filter->value);
                    }elseif($filter->field == "status"){
                        $this->db->like("status", $filter->value);
                    }else{
                        $this->db->like($filter->field, $filter->value);
                    }
                }
            }

            $this->db->order_by('name', 'ASC');
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
                    $code = $post['name']; 
                    $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From transaction_type where name like '%$code'");
                    $row = $sql->row();
                    $kode = substr($row->kode, -4);
                    $autoid = substr($code, 0, 2) . "-" . sprintf("%04s", $kode + 1);
                    $post['type'] = $autoid;
                    $transtype = $this->crud->read('transaction_type', [], ["name" => $post['name']]);
                    if (!empty($transtype->name)) {
                        echo json_encode(array("title" => "Duplicated", "message" => "Transaction Type " . $post['name'] . " Duplicate Data", "theme" => "error"));
                    } else {
                        $send   = $this->crud->create('transaction_type', $post);
                        echo $send;
                    }
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
                  $type   = base64_decode($this->input->get('type'));
                  $post = $this->input->post();
                  $send = $this->crud->update('transaction_type', ["type" => $type], $post);
                  echo $send;
              } else {
                show_error("Cannot Process your request");
        
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('transaction_type', $data);
         echo $send;
     }
 
     //PRINT & EXCEL DATA
     public function print($option = "")
     {
         if ($option == "excel") {
             $format  = date("Ymd");
             header("Content-type: application/vnd-ms-excel");
             header("Content-Disposition: attachment; filename=transaction_type_$format.xls");
         }
         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
         $config_iso = $this->db->get('config_iso')->row();
 
         $this->db->select('type, name, description, status');
         $this->db->from('transaction_type');
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
                             <small>MASTER TRANSACTION TYPE</small>
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
                 <th>Transaction Type</th>
                 <th>Transaction Name</th>
                 <th>Description</th>
                 <th>Status</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['type'] . '</td>
                         <td>' . $data['name'] . '</td>
                         <td>' . $data['description'] . '</td>
                         <td>' . $data['status'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
