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
use PhpOffice\PhpSpreadsheet\Shared\Date;
class Delivery_reports extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[50]|is_unique[os_so.item_fg_id]');
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]|is_unique[os_so.customer_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/delivery_reports');
        } else {
            redirect('error_access');
        }
    }

    public function readSalesOrder($customerOrderNo)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM delivery_reports WHERE customer_order_no = '$customerOrderNo'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customerId)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM delivery_reports WHERE customer_id = '$customerId'");
        echo json_encode($send);
    }
    public function readCustomerOrderNo($customerId)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no, sales_order_date FROM sales_orders WHERE customer_id = '$customerId' AND status=0");
        echo json_encode($send);
    }
    
    public function readInvoice($salesOrderNo) {
        $this->db->select('invoice_no');
        $this->db->from('delivery_reports');
        $this->db->where('sales_order_no', $salesOrderNo);
        $this->db->where('deleted', 0);
        $this->db->group_by('invoice_no');
        $query = $this->db->get();
        echo json_encode($query->result());
    }
    
    public function readItems($salesOrderNo) {
        $this->db->select('item_fg_id');
        $this->db->from('delivery_reports');
        $this->db->where('sales_order_no', $salesOrderNo);
        $this->db->where('deleted', 0);
        $this->db->group_by('item_fg_id');
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    public function readDeliveryLists() {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $this->db->select('a.item_fg_id, a.sales_order_no, a.outstanding as qty_os_so, b.number as item_number, b.name as item_name, c.delivery_report_date, c.invoice_no, c.qty');
            $this->db->from('sales_orders a');
            $this->db->join('delivery_reports c','a.sales_order_no = c.sales_order_no and a.customer_id = c.customer_id and a.customer_order_no = c.customer_order_no and a.item_fg_id = c.item_fg_id',"left");
            $this->db->join('item_fg b','a.item_fg_id = b.id',"left");
            $this->db->where('a.customer_id', $post['customer']);
            $this->db->where('a.customer_order_no', $post['customer_order_no']);
            $this->db->where('a.deleted', 0);
            //$this->db->group_by('a.item_fg_id');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function readSelectedItem() {
        $customer_order_no = $this->input->get('customer_order_no');
        $this->db->select('a.item_fg_id, b.number as item_number, a.outstanding as qty_os_so, b.name as item_name');
        $this->db->from('sales_orders a');
        $this->db->join('item_fg b','a.item_fg_id = b.id',"left");
        $this->db->where('a.customer_order_no', $customer_order_no);
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $customer_id = base64_decode($this->input->get("filter_customer_id"));
        $customer_order_no = base64_decode($this->input->get("filter_customer_order_no"));
        $sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $invoice_no = base64_decode($this->input->get("filter_invoice_no"));
        $item_fg = base64_decode($this->input->get("filter_item_fg"));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select('a.*, b.name as customer_name, c.name as item_fg_name, c.number as item_fg_number');
        $this->db->from('delivery_reports a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->where('a.delivery_report_date >=', $filter_from);
        $this->db->where('a.delivery_report_date <=', $filter_to);
        $this->db->like('a.customer_id', $customer_id);
        $this->db->like('a.customer_order_no', $customer_order_no);
        $this->db->like('a.sales_order_no', $sales_order_no);
        $this->db->like('a.invoice_no', $invoice_no);
        $this->db->like('a.item_fg_id', $item_fg);
        $this->db->group_by('a.delivery_report_date');
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.sales_order_no');
        $this->db->group_by('a.customer_order_no');
        $this->db->group_by('a.invoice_no');
        $this->db->order_by('a.created_date', 'DESC');

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

    //ADD DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if (!empty($post['invoice_no']) && !empty($post['delivery_report_date']) && !empty($post['qty'])) {
                $item_fg = $this->crud->read("item_fg", [], ["number" => $post['item_number']]);

                $sales_orders = $this->crud->read("sales_orders", [], ["customer_id" => $post['customer_id'], "customer_order_no" => $post['customer_order_no'], "item_fg_id" => $item_fg->id]);

                $qty = $post['qty'];

                $newDelivery = intval($sales_orders->delivery) + intval($qty);
                $newOutstanding = intval($sales_orders->qty) - $newDelivery;
    
                $updateData = [
                    "delivery" => $newDelivery,
                    "outstanding" => $newOutstanding,
                ];

                $data = array(
                    "customer_id" => $post['customer_id'],
                    "item_fg_id" => $item_fg->id,
                    "sales_order_no" => $sales_orders->sales_order_no,
                    "customer_order_no" => $post['customer_order_no'],
                    "delivery_report_date" => $post['delivery_report_date'],
                    "qty" => $post['qty'],
                    "invoice_no" => $post['invoice_no'],
                );
                $delivery_reports = $this->crud->read("delivery_reports", [], ["customer_id" => $post['customer_id'], "customer_order_no" => $post['customer_order_no'],"sales_order_no" => $sales_orders->sales_order_no, "item_fg_id" => $item_fg->id,"invoice_no" => $post['invoice_no']]);
                if (!empty($delivery_reports->id)) {
                    $send = json_encode(array("title" => "Good Job", "message" => "Data already exists", "theme" => "success"));
                }else{
                    $this->crud->update('sales_orders', [
                        "sales_order_no" => $sales_orders->sales_order_no,
                        "item_fg_id" =>  $sales_orders->item_fg_id
                    ], $updateData);

                    $send = $this->crud->create('delivery_reports', $data);
                   // echo json_encode($send);
                }
                echo json_encode($send);
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
            $send = $this->crud->update('delivery_reports', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('delivery_reports', $data);
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
        for ($i = 4; $i <= $totalRows; $i++) {
            $deliveryDate = $sheet->getCell('B' . $i)->getValue();
            // Mengonversi format tanggal dari serial Excel ke YYYY-MM-DD
            if (Date::isDateTime($sheet->getCell('B' . $i))) {
                $deliveryDate = date('Y-m-d', Date::ExcelToDateTimeObject($deliveryDate)->getTimestamp());
            }

            $datas[] = array(
                'delivery_report_date' => $deliveryDate,
                'customer_id' => $sheet->getCell('C' . $i)->getValue(),
                'customer_order_no' => $sheet->getCell('D' . $i)->getValue(),
                'invoice_no' => $sheet->getCell('E' . $i)->getValue(),
                'item_fg_id' => $sheet->getCell('F' . $i)->getValue(),
                'qty' => $sheet->getCell('G' . $i)->getValue()
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/delivery_reports.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/delivery_reports.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/delivery_reports.txt";
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
            $delivery_report_date = $data['delivery_report_date'];
            $invoice_no = $data['invoice_no'];
            $qty = $data['qty'];

            // Cek format Delivery Date
            if (!DateTime::createFromFormat('Y-m-d', $delivery_report_date)) {
                echo json_encode(array(
                    "title" => "Format Error", 
                    "message" => "Delivery Date format is incorrect! It must be in YYYY-MM-DD format.", 
                    "theme" => "error"
                ));
                return;
            }

            // Validasi Customer Code
            $customer = $this->crud->read('customers', [], ["number" => $data['customer_id']]);
            if (empty($customer->id)) {
                echo json_encode(array(
                    "title" => "Not Found", 
                    "message" => "Customer Code " . $data['customer_id'] . " is not found in Sales Orders!", 
                    "theme" => "error"
                ));
                return;
            }

            // Validasi Product No / Item FG ID
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
                return;
            }

            // Validasi Customer Order No
            $custorderno = $this->crud->read('sales_orders', [], [
                "customer_order_no" => $data['customer_order_no'],
                "item_fg_id" => $item_fg->id,
            ]);
            
            if (empty($custorderno->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Customer Order No. " . $data['customer_order_no'] . " and Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
                return;
            }

            // Pastikan item_fg_id terkait dengan customer_order_no
            $salesOrder = $this->crud->read('sales_orders', [], [
                "customer_id" => $customer->id, 
                "customer_order_no" => $data['customer_order_no'], 
                "item_fg_id" => $item_fg->id
            ]);
            if (empty($salesOrder)) {
                echo json_encode(array(
                    "title" => "Not Found", 
                    "message" => "Product No. " . $data['item_fg_id'] . " is not associated with Customer Order No. " . $data['customer_order_no'] . "!", 
                    "theme" => "error"
                ));
                return;
            }

            // Proses update berdasarkan sales_order_no dan item_fg_id
            $newDelivery = $salesOrder->delivery + $qty;
            $newOutstanding = $salesOrder->qty - $newDelivery;

            $updateData = [
                "delivery" => $newDelivery,
                "outstanding" => $newOutstanding,
            ];

            $this->crud->update('sales_orders', [
                "sales_order_no" => $salesOrder->sales_order_no,
                "item_fg_id" => $item_fg->id
            ], $updateData);

            // Proses simpan jika semua validasi lulus
            $data['sales_order_no'] = $salesOrder->sales_order_no;
            $data['customer_order_no'] = $custorderno->customer_order_no;
            $data['customer_id'] = $salesOrder->customer_id;
            $data['item_fg_id'] = $item_fg->id;
            $data['delivery_report_date'] = $delivery_report_date;
            $data['invoice_no'] = $invoice_no;
            $data['qty'] = $qty;

            $send = $this->crud->create('delivery_reports', $data);
            if ($send) {
                echo json_encode(array(
                    "title" => "Success", 
                    "message" => "Data uploaded successfully!", 
                    "theme" => "success"
                ));
            } else {
                echo json_encode(array(
                    "title" => "Failed", 
                    "message" => "Failed to upload data!", 
                    "theme" => "error"
                ));
            }
        }
    }
    
    // //UPLOAD CREATE DATA
    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');
    //         $delivery_report_date = $data['delivery_report_date'];
    //         $invoice_no = $data['invoice_no'];
    //         $qty = $data['qty'];

    //         // Validasi Customer Code
    //         $customer = $this->crud->read('customers', [], ["number" => $data['customer_id']]);
    //         if (empty($customer->id)) {
    //             echo json_encode(array(
    //                 "title" => "Not Found", 
    //                 "message" => "Customer Code " . $data['customer_id'] . " is not found in Sales Orders!", 
    //                 "theme" => "error"
    //             ));
    //             return;
    //         }

    //         // Validasi Product No / Item FG ID
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
    //         if (empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not found", "message" => " Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
    //             return;
    //         }

    //         // Validasi Customer Order No
    //         $custorderno = $this->crud->read('sales_orders', [], [
    //             "customer_order_no" => $data['customer_order_no'],
    //             "item_fg_id" => $item_fg->id,
    //         ]);
            
    //         if (empty($custorderno->id)) {
    //             echo json_encode(array("title" => "Not found", "message" => " Customer Order No. " . $data['customer_order_no'] . " and Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
    //             return;
    //         }

    //         // Pastikan item_fg_id terkait dengan customer_order_no
    //         $salesOrder = $this->crud->read('sales_orders', [], [
    //             "customer_id" => $customer->id, 
    //             "customer_order_no" => $data['customer_order_no'], 
    //             "item_fg_id" => $item_fg->id
    //         ]);
    //         if (empty($salesOrder)) {
    //             echo json_encode(array(
    //                 "title" => "Not Found", 
    //                 "message" => "Product No. " . $data['item_fg_id'] . " is not associated with Customer Order No. " . $data['customer_order_no'] . "!", 
    //                 "theme" => "error"
    //             ));
    //             return;
    //         }

    //         // Proses simpan jika semua validasi lulus
    //         $data['sales_order_no'] = $salesOrder->sales_order_no;
    //         $data['customer_order_no'] = $custorderno->customer_order_no;
    //         $data['customer_id'] = $salesOrder->customer_id;
    //         $data['item_fg_id'] = $item_fg->id;
    //         $data['delivery_report_date'] = $delivery_report_date;
    //         $data['invoice_no'] = $invoice_no;
    //         $data['qty'] = $qty;

    //         $send = $this->crud->create('delivery_reports', $data);
    //         if ($send) {
    //             echo json_encode(array(
    //                 "title" => "Success", 
    //                 "message" => "Data uploaded successfully!", 
    //                 "theme" => "success"
    //             ));
    //         } else {
    //             echo json_encode(array(
    //                 "title" => "Failed", 
    //                 "message" => "Failed to upload data!", 
    //                 "theme" => "error"
    //             ));
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_reports_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $customer_id = base64_decode($this->input->get("filter_customer_id"));
        $customer_order_no = base64_decode($this->input->get("filter_customer_order_no"));
        $sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $invoice_no = base64_decode($this->input->get("filter_invoice_no"));
        $item_fg = base64_decode($this->input->get("filter_item_fg"));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as customer_name, c.name as item_fg_name, c.number as item_fg_number');
        $this->db->from('delivery_reports a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->where('a.delivery_report_date >=', $filter_from);
        $this->db->where('a.delivery_report_date <=', $filter_to);
        $this->db->like('a.customer_id', $customer_id);
        $this->db->like('a.customer_order_no', $customer_order_no);
        $this->db->like('a.sales_order_no', $sales_order_no);
        $this->db->like('a.invoice_no', $invoice_no);
        $this->db->like('a.item_fg_id', $item_fg);
        $this->db->group_by('a.delivery_report_date');
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.sales_order_no');
        $this->db->group_by('a.customer_order_no');
        $this->db->group_by('a.invoice_no');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
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
                <h3>DELIVERY REPORT</h3>
            </div>
        </center>

        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Delivery Date</th>
                <th>Customer</th>
                <th>Customer Order No</th>
                <th>Sales Order No</th>
                <th>Invoice No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Quantity Delivery</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['delivery_report_date'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['invoice_no'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['qty'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    
    public function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $comments = [
            'C3' => ['Isi dengan Customer Code dari Master Customer'],
            'B3' => ['Format Date YYYY-MM-DD'],
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('TMP DELIVERY REPORTS');
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
        $templateSheet->setCellValue('A1', 'TEMPLATE DELIVERY REPORTS');
        $templateSheet->setCellValue('A3', 'No');
        $templateSheet->setCellValue('B3', 'DELIVERY DATE');
        $templateSheet->setCellValue('C3', 'CUSTOMER CODE');
        $templateSheet->setCellValue('D3', 'CUSTOMER ORDER NO');
        $templateSheet->setCellValue('E3', 'DELIVERY NOTE NO');
        $templateSheet->setCellValue('F3', 'PRODUCT NO');
        $templateSheet->setCellValue('G3', 'QTY DELIVERY');
        $templateSheet->getStyle('A3:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A3:G3')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A3:G3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
        // Second Sheet: Reference
        $item_refSheet = $spreadsheet->createSheet(1);
        $item_refSheet->setTitle('REFERENCE');

        $this->db->select('number as customer_code, name as customer_name');
        $this->db->from('customers');
        $this->db->order_by('name','asc');
        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(10);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(25);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Customer Code');
        $item_refSheet->setCellValue('C1', 'Customer Name');
        $item_refSheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:C1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:C1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['customer_code']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['customer_name']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':C' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':C' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':C' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        $spreadsheet->setActiveSheetIndex(0); 
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_delivery_reports.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}