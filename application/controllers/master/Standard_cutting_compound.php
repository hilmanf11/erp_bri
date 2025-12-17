<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Standard_cutting_compound extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/standard_cutting_compound');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('standard_cutting_compound', ["number" => $post]);
         echo json_encode($send);
     }

    //GET DATA
    public function readMachinePress()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM standard_cutting_compound WHERE type_process_id = 'PT01' AND (number like '%$post%' or name like '%$post%' or id like '%$post%') AND status = 0");
        echo json_encode($send);
    }

    //GET DATA
    public function readMachineMixings()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM standard_cutting_compound WHERE type_process_id = 'PT05' AND (number like '%$post%' or name like '%$post%' or id like '%$post%') AND status = 0");
        echo json_encode($send);
    }

     //CODE OTOMATIS
     public function autoid($type_process_id){
         $code = $type_process_id; 
         $sql = $this->db->query("SELECT coalesce(max(`id`),0) as kode From standard_cutting_compound where id like '%$code%'");
         $row = $sql->row();
         $kode = substr($row->kode, -4);
         $autoid = "ASMC".$code."-". sprintf("%04s", $kode + 1);
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
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.id as compound_id, c.number_internal as compound_number_internal');
            $this->db->from('standard_cutting_compound a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_fg_id"){
                        $this->db->like("a.item_fg_id", $filter->value);

                    }elseif($filter->field == "item_fg_number"){
                        $this->db->like("b.number", $filter->value);

                    }elseif($filter->field == "item_fg_name"){
                        $this->db->like("a.name", $filter->value);
    
                    }elseif($filter->field == "compound_id"){
                        $this->db->like("a.item_rm_id", $filter->value);
                            
                    }elseif($filter->field == "compound_number_internal"){
                        $this->db->like("c.number_internal", $filter->value);
                            
                    }
                    // elseif($filter->field == "length"){
                    //     $this->db->like("a.length", $filter->value);
                            
                    // }elseif($filter->field == "width"){
                    //     $this->db->like("a.width", $filter->value);
                            
                    // }elseif($filter->field == "height"){
                    //     $this->db->like("a.height", $filter->value);
                            
                    // }elseif($filter->field == "weight"){
                    //     $this->db->like("a.weight", $filter->value);

                    // }elseif($filter->field == "tolerance_upper"){
                    //     $this->db->like("a.tolerance_upper", $filter->value);

                    // }elseif($filter->field == "tolerance_lower"){
                    //     $this->db->like("a.tolerance_lower", $filter->value);

                    // }
                    
                    // elseif($filter->field == "status" && strtolower($filter->value) === "active") {
                    //     $this->db->where("a.status", 0);

                    // } elseif($filter->field == "status" && strtolower($filter->value) === "inactive") {
                    //     $this->db->where("a.status", 1);

                    // }
                    
                    else{
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
                 $post   = $this->input->post();
                 $send   = $this->crud->create('standard_cutting_compound', $post);
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
                  $send = $this->crud->update('standard_cutting_compound', ["id" => $id], $post);
                  echo $send;
              } else {
                show_error("Cannot Process your request");
              
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('standard_cutting_compound', $data);
         echo $send;
     }
 
     //UPLOAD DATA
    //  public function upload()
    //  {
    //      error_reporting(0);
    //      require_once 'assets/vendors/excel_reader2.php';
    //      $target = basename($_FILES['file_upload']['name']);
    //      move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //      chmod($_FILES['file_upload']['name'], 0777);
    //      $file = $_FILES['file_upload']['name'];
    //      $data = new Spreadsheet_Excel_Reader($file, false);
    //      $total_row = $data->rowcount($sheet_index = 0);
    //      for ($i = 3; $i <= $total_row; $i++) {
    //          $datas[] = array(
    //              //excel
    //              'machine_no' => $data->val($i, 2),
    //              'name_of_machine' => $data->val($i, 3),
    //              'process_type' => $data->val($i, 4),
    //              'specification' => $data->val($i, 5),
    //              'purchase_date' => $data->val($i, 6),
    //              'manufacturing_date' => $data->val($i, 7),
    //              'maker' => $data->val($i, 8),
    //              'tonage_of_machine' => $data->val($i, 9),
    //              'uom' => $data->val($i, 10),
    //              'vacum' => $data->val($i, 11),
    //              'rt' => $data->val($i, 12),
    //              'type' => $data->val($i, 13),
    //              'brand' => $data->val($i, 14),
    //              'status' => $data->val($i, 15)
                 
    //          );
    //      }
    //      $datas['total'] = count($datas);
    //      echo json_encode($datas);
    //      unlink($_FILES['file_upload']['name']);
    //  }


    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($target, 0777);

        $data = new Spreadsheet_Excel_Reader($target, false);
        $total_row = $data->rowcount($sheet_index = 0);
        $datas = [];

        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_fg_id' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'length' => $data->val($i, 4),
                'width' => $data->val($i, 5),
                'height' => $data->val($i, 6),
                'weight' => $data->val($i, 7),
                'tolerance_upper' => $data->val($i, 8),
                'tolerance_lower' => $data->val($i, 9),
            );
        }

        echo json_encode([
            "total" => count($datas),
            "data" => $datas
        ]);

        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/standard_cutting_compound.xls');
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/standard_cutting_compound.xls";

        if (!file_exists($file)) {
            echo "No failed data to download";
            return;
        }

        $filename = "upload_failed_standard_cutting_compound_" . date("Ymd_s") . ".xls";

        header("Content-Description: File Upload Failed");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        readfile($file);
    }
 
    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $raw = file_get_contents("php://input");
            $postData = json_decode($raw, true);

            $data_list = $postData['data'];

            $total_expected = count($data_list);
            $processed_count = 0;

            $this->db->trans_begin();
            $results = [];

            foreach ($data_list as $index => $data) {
                $processed_count++;

                if (empty($data['item_fg_id'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Product ID is required"
                    ];
                    continue;
                }
                if (empty($data['item_rm_id'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Compound ID is required"
                    ];
                    continue;
                }
                if ($data['length'] === "" || !is_numeric($data['length'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Length must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['width'] === "" || !is_numeric($data['width'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Width must be numeric and not empty"
                    ];
                    continue;
                }
                if ($data['height'] === "" || !is_numeric($data['height'])) {
                    $results[] = [
                        "status"  => "failed",
                        "item"    => "Line " . ($index + 1),
                        "message" => "Height must be numeric and not empty"
                    ];
                    continue;
                }

                $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
                if (empty($item_fg)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product ID " . $data['item_fg_id'] . " Not Found"
                    ];
                    continue;
                }

                $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
                if (empty($item_rm)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Compound ID " . $data['item_rm_id'] . " Not Found"
                    ];
                    continue;
                }

                $checkItemUnique = $this->crud->read('standard_cutting_compound', [], [
                    "item_fg_id" => $item_fg->id,
                    "item_rm_id" => $item_rm->id,
                ]);

                if (!empty($checkItemUnique)) {
                    $results[] = [
                        "status" => "failed",
                        "item" => "Line " . ($index + 1),
                        "message" => "Product ID " . $item_fg->id . 
                                    " and Compound ID " . $item_rm->id . 
                                    " already exists"
                    ];
                    continue;
                }

                $checkData = $this->crud->read('standard_cutting_compound', [], [
                    "item_fg_id" => $item_fg->id,
                    "item_rm_id" => $item_rm->id,
                ]);

                // if (!empty($checkData)) {
                //     $results[] = [
                //         "status" => "failed",
                //         "item" => "Line " . ($index + 1),
                //         "message" => "Duplicate Data: Period " . $data['period'] . 
                //                     ", Product No. " . $data['item_fg_id'] . 
                //                     ", Machine No. " . $data['machine_id'] . 
                //                     ", WP No. " . $wp . 
                //                     ", Trans Date " . $data['trans_date']
                //     ];
                //     continue;
                // }

                $weight = (float) str_replace(",", ".", $data['weight']);
                $tolerance_upper = (float) str_replace(",", ".", $data['tolerance_upper']);
                $tolerance_lower = (float) str_replace(",", ".", $data['tolerance_lower']);

                $dataFinal = array(
                    "item_fg_id"        => $item_fg->id,
                    "item_rm_id"        => $item_rm->id,
                    "length"            => $data['length'],
                    "width"             => $data['width'],
                    "height"            => $data['height'],
                    "weight"            => $weight,
                    "tolerance_upper"   => $tolerance_upper,
                    "tolerance_lower"   => $tolerance_lower,
                );

                try {
                    if (!empty($checkData)) {
                        // Update
                        $this->db->update('standard_cutting_compound', [
                            
                            "length"            => $data['length'],
                            "width"             => $data['width'],
                            "height"            => $data['height'],
                            "weight"            => $weight,
                            "tolerance_upper"   => $tolerance_upper,
                            "tolerance_lower"   => $tolerance_lower,
                        ], [
                            "item_fg_id" => $item_fg->id,
                            "item_rm_id" => $item_rm->id,
                        ]);

                        $status = "update";
                    } else {
                        // Insert
                        $this->crud->create('standard_cutting_compound', $dataFinal);

                        $status = "insert";
                    }

                    $res_item = ($status === "insert" ? "Create" : "Update");
                    $res_msg  = ($status === "insert" ? "Data Saved Successfully" : "Product ID {$data['item_fg_id']} and Compound ID {$data['item_rm_id']} updated");

                    $results[] = [
                        "status" => "success",
                        "item" => $res_item,
                        "message" => $res_msg
                    ];
                } catch (Exception $e) {
                    $results[] = [
                        "status" => "failed",
                        "item" => $item_fg->name,
                        "message" => $e->getMessage()
                    ];
                    continue;
                }
            }

            $failed = array_filter($results, fn($r) => $r['status'] === 'failed');
            $hasDbError = ($this->db->trans_status() === FALSE);

            if (count($failed) > 0 || $hasDbError) {
                $filePath = 'failed/output_production_press.xls';

                $html = '
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body>
                    <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; font-family: Arial, sans-serif;">
                        <thead style="background-color: #f2f2f2;">
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 100px; text-align: left;">Line</th>
                                <th style="width: 450px; text-align: left;">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                ';

                $no = 1;
                foreach ($failed as $row) {
                    $line = htmlspecialchars($row['item']);
                    $msg  = htmlspecialchars($row['message']);
                    $html .= "
                        <tr>
                            <td style='text-align: center;'>{$no}</td>
                            <td style='text-align: left;'>{$line}</td>
                            <td style='text-align: left;'>{$msg}</td>
                        </tr>";
                    $no++;
                }

                $html .= '
                        </tbody>
                    </table>
                </body>
                </html>';

                file_put_contents($filePath, $html);

                echo json_encode([
                    "theme" => "error",
                    "title" => "Upload Failed",
                    "message" => "Data failed to save",
                    "results" => $results,
                    "total_expected" => $total_expected,
                    "processed_count" => $processed_count,
                    "stopped_at" => $index + 1
                ]);
            } else {
                @unlink('failed/output_production_press.xls');

                $this->db->trans_commit();
                echo json_encode([
                    "theme" => "success",
                    "title" => "Upload Successfully",
                    "message" => "Data uploaded successfully",
                    "results" => $results,
                    "total_expected" => $total_expected,
                    "processed_count" => $processed_count,
                    "stopped_at" => $index + 1
                ]);
            }

        }
    }

    public function upload_att_cutting()
    {
        $uploadDir = 'assets/image/standard_cutting_compound/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
                    exit;
                }

                $maxFileSize = 2 * 1024 * 1024;
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB yang diperbolehkan.']);
                    exit;
                }

                if ($file['error'] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . $file['name'];
                    $uploadPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }
 
     //PRINT & EXCEL DATA
     public function print($option = "")
     {
         if ($option == "excel") {
             $format  = date("Ymd");
             header("Content-type: application/vnd-ms-excel");
             header("Content-Disposition: attachment; filename=standard_cutting_compound_$format.xls");
         }
         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
         $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.id as compound_id, c.number_internal as compound_number_internal');
        $this->db->from('standard_cutting_compound a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('id', 'ASC');
        $records = $this->db->get()->result_array();
         

         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#standard_cutting_compound {border-collapse: collapse;width: 100%;font-size: 12px;}#standard_cutting_compound td, #standard_cutting_compound th {border: 1px solid #ddd;padding: 2px;}#standard_cutting_compound tr:nth-child(even){background-color: #f2f2f2;}#standard_cutting_compound tr:hover {background-color: #ddd;}#standard_cutting_compound th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER STANDARD CUTTING COMPOUND</small>
                         </td>
                     </tr>
                 </table>
             </div>
             <div style="float: right; font-size: 12px; text-align: right;">

             </div>
         </center>
         <br><br><br><br>
         
         <table id="standard_cutting_compound" border="1">
             <tr>
                 <th rowspan="2" width="20">No</th>
                 <th rowspan="2">Product ID</th>
                 <th rowspan="2">Product No</th>
                 <th rowspan="2">Product Name</th>
                 <th rowspan="2">Compound ID</th>
                 <th rowspan="2">Compound Name</th>
                 
                 <th colspan="3" style="text-align: center;">Dimension (mm)</th>
                 
                 <th rowspan="2">Weight (gr)</th>

                 <th colspan="2" style="text-align: center;">Weight Tolerance (gr)</th>

                 <th colspan="2" style="text-align: center;">Created</th>
                 <th colspan="2" style="text-align: center;">Updated</th>
             </tr>
             
             <tr>
                <th>Length (P)</th>
                <th>Width (L)</th>
                <th>Height (T)</th>

                <th>Upper</th>
                <th>Lower</th>

                <th>By</th>
                <th>Date</th>

                <th>By</th>
                <th>Date</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['item_fg_id'] . '</td>
                         <td>' . $data['item_fg_number'] . '</td>
                         <td>' . $data['item_fg_name'] . '</td>
                         <td>' . $data['compound_id'] . '</td>
                         <td>' . $data['compound_number_internal'] . '</td>
                         <td>' . $data['length'] . '</td>
                         <td>' . $data['width'] . '</td>
                         <td>' . $data['height'] . '</td>
                         <td>' . $data['weight'] . '</td>
                         <td>' . $data['tolerance_upper'] . '</td>
                         <td>' . $data['tolerance_lower'] . '</td>
                         <td>' . $data['created_by'] . '</td>
                         <td>' . $data['created_date'] . '</td>
                         <td>' . $data['updated_by'] . '</td>
                         <td>' . $data['updated_date'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
