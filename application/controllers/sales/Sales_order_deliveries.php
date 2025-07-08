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
class Sales_order_deliveries extends CI_Controller
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
            $this->load->view('sales/sales_order_deliveries');
        } else {
            redirect('error_access');
        }
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readProductNo($customer_id)
    {
        $send = $this->crud->query("SELECT b.* FROM sales_orders a JOIN item_fg b ON a.item_fg_id = b.id WHERE a.customer_id = '$customer_id' GROUP BY a.item_fg_id");

        echo json_encode($send);
    }

    public function readCustomerOrderNo($customerId)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no, sales_order_date, qty, order_type FROM sales_orders WHERE customer_id = '$customerId' AND (status = 0 OR status = 2) group by customer_order_no");
        echo json_encode($send);
    }

    public function readDeliveryLists() {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $this->db->select('a.item_fg_id, a.sales_order_no, a.order_type, b.number as item_number, b.name as item_name, a.qty, c.trans_date, c.qty as qty_delivery');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b','a.item_fg_id = b.id',"left");
            $this->db->join('sales_order_deliveries c','a.sales_order_no = c.sales_order_no and a.customer_order_no = c.customer_order_no and a.item_fg_id = c.item_fg_id',"left");
            $this->db->where('a.customer_id', $post['customer']);
            $this->db->where('a.customer_order_no', $post['customer_order_no']);
            $this->db->where('a.deleted', 0);
            //$this->db->group_by('a.sales_order_no');
            //$this->db->group_by('a.item_fg_id');

            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function readSelectedItem() {
        $customer_order_no = $this->input->get('customer_order_no');
        $this->db->select('a.item_fg_id, b.number as item_number, a.qty, a.sales_order_no, b.name as item_name');
        $this->db->from('sales_orders a');
        $this->db->join('item_fg b','a.item_fg_id = b.id',"left");
        $this->db->where('a.customer_order_no', $customer_order_no);
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name");
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.sales_order_date >=', $filter_from);
                $this->db->where('a.sales_order_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->group_by('a.sales_order_no');
            $this->db->order_by('a.status', 'ASC');
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
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, COALESCE(c.qty_del, 0) as qty_del, (a.qty - COALESCE(c.qty_del, 0)) as qty_os');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(SELECT sales_order_no, item_fg_id, customer_id, SUM(qty) as qty_del 
            FROM sales_order_deliveries GROUP BY sales_order_no, item_fg_id, customer_id) c", "a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id", "left");
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //sales_order_deliveries
    public function datatables2($customer_id, $sales_order_no, $item_fg_id)
    {
        $customer_id = base64_decode($customer_id);
        $sales_order_no = base64_decode($sales_order_no);
        $item_fg_id = base64_decode($item_fg_id);

        //Select Query
        $this->db->select('a.*, b.qty as so_qty');
        $this->db->from('sales_order_deliveries a');
        $this->db->join('sales_orders b', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.sales_order_no', $sales_order_no);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->order_by('trans_date', 'asc');
        $records = $this->db->get()->result_array();

        $balance = 0;
        $qty = 0;
        $data = array();
        foreach ($records as $record) {
            $qty += $record['qty'];
            $balance = $record['so_qty'] - $qty;
            $data[] = array(
                "id" => $record['id'],
                "customer_id" => $customer_id,
                "sales_order_no" => $sales_order_no,
                "item_fg_id" => $item_fg_id,
                "trans_date" => $record['trans_date'],
                "so_qty" => $record['so_qty'],
                "qty" => $record['qty'],
                "remain_qty" => $balance,
                "status" => $record['status'],
                "created_by" => $record['created_by'],
                "created_date" => $record['created_date'],
            );
        }

        //Mapping Data
        $result['total'] = count(@$data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    //CREATE DATA
    
    // public function create_schedule()
    // {
    //     if ($this->input->post()) {
    //         $post   = $this->input->post();
    //         $sales_order_no =  $post['sales_order_no'];
    //         $item_fg_id =  $post['item_fg_id'];

    //         $data = $post['data'];
    //         if ($data == '[]') {
    //             show_error("Cannot Process your request");
    //         }else{
    //             if (!empty($data)) {
    //                 $decoded_data = json_decode($data);
    //                 // Loop through each date
    //                 $results = [];
    //                 foreach ($decoded_data as $trans_date) {
    //                     // Query to fetch data based on each trans_date
    //                     $sales_order_deliveries = $this->crud->read("sales_order_deliveries",[],["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id,"trans_date" => $trans_date->date]);
    //                     $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_order_no' and item_fg_id = '$item_fg_id' GROUP BY sales_order_no, item_fg_id");
    //                     $total = empty($sales_order_deliveries_total[0]->total)?0:$sales_order_deliveries_total[0]->total;
    //                     if ($post['qty_so'] >= (@$total + $trans_date->value)) {
    //                         if (empty($sales_order_deliveries->trans_date)) {
    //                             $data = array(
    //                                 "customer_id" => $post['customer_id'],
    //                                 "item_fg_id" => $item_fg_id,
    //                                 "sales_order_no" => $sales_order_no,
    //                                 "customer_order_no" => $post['customer_order_no'],
    //                                 "trans_date" => $trans_date->date,
    //                                 "qty" => $trans_date->value,
    //                             );
    //                             $send = $this->crud->create('sales_order_deliveries', $data);
    //                             $results[] = $send;
    //                         } else {
    //                             $results[] =json_encode(array("title" => "Failed", "message" => "Delivery Date Has Been Created Please Choose Another Date", "theme" => "error"));
    //                         }
    //                     } else {
    //                         $results[] =json_encode(array("title" => "Failed", "message" => "Qty is greater than the Sales Order", "theme" => "error"));
    //                     }
    //                 }
    //                 echo json_encode(array("title" => "Good Job", "message" => json_encode($results), "theme" => "success")); 
    //             }
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }
    public function create_schedule()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $sales_order_no =  $post['sales_order_no'];
            $item_fg_id =  $post['item_fg_id'];
            $data = $post['data'];
            if ($data == '[]') {
                echo json_encode(array("title" => "Failed", "message" => "Data is empty", "theme" => "success"));
            }else{
                if (!empty($data)) {
                    $decoded_data = json_decode($data);
                    // Loop through each date
                    $results = [];
                    foreach ($decoded_data as $trans_date) {
                        // Query to fetch data based on each trans_date
                        $sales_order_deliveries = $this->crud->read("sales_order_deliveries",[],["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id,"trans_date" => $trans_date->date]);
                        $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_order_no' and item_fg_id = '$item_fg_id' GROUP BY sales_order_no, item_fg_id");
                        $total = empty($sales_order_deliveries_total[0]->total)?0:$sales_order_deliveries_total[0]->total;
                        if ($trans_date->value!=="") {
                            if ($post['qty_so'] >= (@$total + $trans_date->value)) {
                                $data = array(
                                    "customer_id" => $post['customer_id'],
                                    "item_fg_id" => $item_fg_id,
                                    "sales_order_no" => $sales_order_no,
                                    "customer_order_no" => $post['customer_order_no'],
                                    "trans_date" => $trans_date->date,
                                    "qty" => $trans_date->value,
                                );
                                if (empty($sales_order_deliveries->trans_date)) {
                                    $send = $this->crud->create('sales_order_deliveries', $data);
                                    $results[] = $send;
                                } else {
                                    $send = $this->crud->update('sales_order_deliveries', ["id"=>$sales_order_deliveries->id,"sales_order_no" => $sales_order_deliveries->sales_order_no, "customer_id" => $sales_order_deliveries->customer_id, "item_fg_id" => $sales_order_deliveries->item_fg_id], $data);
                                    $results[] = $send;
                                }
                            } else {
                                $results[] =json_encode(array("title" => "Failed", "message" => "Qty is greater than the Sales Order", "theme" => "error"));
                            }
                        }else{
                            $results[] =json_encode(array("title" => "Failed", "message" => "Qty is empty", "theme" => "error"));
                        }
                    }
                    echo json_encode(array("title" => "Good Job", "message" => json_encode($results), "theme" => "success")); 
                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();

            $sales_order_no =  $post['sales_order_no'];
            $item_fg_id =  $post['item_fg_id'];
            $data = array(
                "customer_id" => $post['customer_id'],
                "customer_order_no" => $post['customer_order_no'],
                "sales_order_no" => $sales_order_no,
                "item_fg_id" => $item_fg_id,
                "trans_date" => $post['trans_date'],
                "qty" => $post['qty'],
                "id" => $post['id']
            );
            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id]);
            $sales_order_deliveries = $this->crud->read("sales_order_deliveries", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id, "trans_date" => $post['trans_date']]);
            $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_order_no' and item_fg_id = '$item_fg_id' GROUP BY sales_order_no, item_fg_id");
            $post['customer_order_no'] = $sales_orders->customer_order_no;
            $qty_so = $sales_orders->qty;
            if ($qty_so >= (@$sales_order_deliveries_total[0]->total + $post['qty'])) {
                if (empty($sales_order_deliveries->trans_date)) {
                    $send = $this->crud->create('sales_order_deliveries', $data);
                    echo $send;
                } else {
                    show_error("Delivery Date Has Been Created Please Choose Another Date");
                }
            } else {
                show_error("Qty is greater than the Sales Order");
            }
        } else {
            show_error("Cannot Process your request");
         }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $sales_order_no =  $post['sales_order_no'];
            $item_fg_id =  $post['item_fg_id'];
            $id = $post["id"];
            $data = array(
                "customer_id" => $post['customer_id'],
                "customer_order_no" => $post['customer_order_no'],
                "sales_order_no" => $sales_order_no,
                "item_fg_id" => $item_fg_id,
                "trans_date" => $post['trans_date'],
                "qty" => $post['qty'],
                "id" => $post['id']
            );
            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id]);
            $sales_order_deliveries = $this->crud->read("sales_order_deliveries", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id]);//, "trans_date" => $post['trans_date']
            $sales_order_deliveries_totalby_id = $this->crud->query("SELECT qty FROM sales_order_deliveries WHERE id='$id'");
            $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_order_no' and item_fg_id = '$item_fg_id' GROUP BY sales_order_no, item_fg_id");
            
            $qty_so = intval($sales_orders->qty);
            $total = intval($sales_order_deliveries_total[0]->total) - intval($sales_order_deliveries_totalby_id[0]->qty);
            if ($qty_so >= ($total + intval($post['qty']))) {
                if (!empty($sales_order_deliveries->id)) {
                    
                    $send = $this->crud->update('sales_order_deliveries', ["id"=>$id,"sales_order_no" => $post['sales_order_no'], "customer_id" => $post["customer_id"], "item_fg_id" => $post['item_fg_id']], $data);
                    echo $send;
                }
                // if (empty($sales_order_deliveries->trans_date)) {
                //     $send = $this->crud->update('sales_order_deliveries', ["sales_order_no" => $post['sales_order_no'], "customer_id" => $post["customer_id"], "item_fg_id" => $post['item_fg_id']], $post);
                //     echo $send;
                // } else {
                //     show_error("Delivery Date Has Been Created Please Choose Another Date");
                // }
            } else {
                show_error("Qty is greater than the Sales Order");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_order_deliveries', $data);
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
            $datas[] = array(
                'sales_order_no' => $sheet->getCell('B' . $i)->getValue(),
                'customer_id' => $sheet->getCell('C' . $i)->getValue(),
                'item_fg_id' => $sheet->getCell('D' . $i)->getValue(),
                'trans_date' => $sheet->getCell('E' . $i)->getValue(),
                'qty' => $sheet->getCell('F' . $i)->getValue(),
            );
        }
    
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($target);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/tmp_delivery_schedules.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/tmp_delivery_schedules.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/tmp_delivery_schedules.txt";
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
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_id']]);
            $customerid = $this->crud->read('customers', [], ["number" => $data['customer_id']]);
            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
                return;
            }
            if (empty($customerid->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Customer No. " . $data['customer_id'] . " is Not Found!", "theme" => "error"));
                return;
            }

            $custorderno = $this->crud->read('sales_orders', [], [
                "customer_order_no" => $data['sales_order_no'],
                "item_fg_id" => $item_fg->id,
            ]);
            
            if (empty($custorderno->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Customer Order No. " . $data['sales_order_no'] . " and Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
                return;
            }
            $data['customer_order_no'] = $data['sales_order_no'];
            $data['sales_order_no'] = $custorderno->sales_order_no;
            $data['customer_id'] = $customerid->id;
            $data['item_fg_id'] = $item_fg->id;
            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $data['sales_order_no'], "item_fg_id" => $data['item_fg_id']]);
            $sales_order_deliveries = $this->crud->read("sales_order_deliveries", [], ["sales_order_no" => $data['sales_order_no'], "item_fg_id" => $data['item_fg_id'], "trans_date" => $data['trans_date']]);
            $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$custorderno->sales_order_no' and item_fg_id = '$custorderno->item_fg_id' GROUP BY sales_order_no, item_fg_id");


            $qty_so = $sales_orders->qty;
            if ($qty_so >= (@$sales_order_deliveries_total[0]->total + $data['qty'])) {
                if (!empty($sales_order_deliveries->trans_date)) {
                    echo json_encode(array("title" => "Failed", "message" => "Delivery Date Has Been Created Please Choose Another Date", "theme" => "error"));
                } else {
                    $send = $this->crud->create('sales_order_deliveries', $data);
                    echo $send;
                }
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Qty is greater than the Sales Order", "theme" => "error"));
                return;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name, COALESCE(d.qty_del, 0) as qty_del, (a.qty - COALESCE(d.qty_del, 0)) as qty_os");
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join("(SELECT sales_order_no, item_fg_id, customer_id, SUM(qty) as qty_del 
            FROM sales_order_deliveries GROUP BY sales_order_no, item_fg_id, customer_id) d", "a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id and a.customer_id = d.customer_id", "left");
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.sales_order_date >=', $filter_from);
            $this->db->where('a.sales_order_date <=', $filter_to);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->order_by('a.sales_order_no', 'ASC');
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
                <h3>SALES ORDER SCHEDULE DELIVERY</h3>
            </div>
        </center>

        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Customer Order No</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Division</th>
                <th>Delivery Date</th>
                <th>Remarks</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Delivery</th>
                <th>Outstanding</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['sales_order_date'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['qty_del'] . '</td>
                        <td>' . $data['qty_os'] . '</td>
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
            'D3' => ['Isi dengan Product No dari Master Item Finish Good'],
        ];

        $templateSheet = $spreadsheet->getActiveSheet();
        $templateSheet->setTitle('OS SO');
        $templateSheet->mergeCells('A1:F1');
        $templateSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A1')->getFont()->setSize(16) ->setBold(true);
        $templateSheet->getColumnDimension('A')->setWidth(10);
        $templateSheet->getColumnDimension('B')->setWidth(25);
        $templateSheet->getColumnDimension('C')->setWidth(25);
        $templateSheet->getColumnDimension('D')->setWidth(25);
        $templateSheet->getColumnDimension('E')->setWidth(25);
        $templateSheet->getColumnDimension('F')->setWidth(25);
        $templateSheet->setCellValue('A1', 'TEMPLATE UPLOAD DELIVERY SCHEDULES');
        $templateSheet->setCellValue('A3', 'No');
        $templateSheet->setCellValue('B3', 'SALES ORDER NO');
        $templateSheet->setCellValue('C3', 'CUSTOMER CODE');
        $templateSheet->setCellValue('D3', 'PRODUCT NO');
        $templateSheet->setCellValue('E3', 'DELIVERY DATE');
        $templateSheet->setCellValue('F3', 'QTY');
        $templateSheet->getStyle('A3:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $templateSheet->getStyle('A3:B3')->getFont()->setBold(true);
        $templateSheet->getStyle('C3:F3')->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $templateSheet->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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

        $this->db->select('a.item_fg_customer as product_no_customer, a.item_fg_id as product_id, c.name as product_name,a.price, a.valid_to as valid_date, b.number as customer_code, b.name as customer_name, c.number as product_no');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id', 'left');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
        $this->db->order_by('b.name','asc');
        $this->db->order_by('a.item_fg_id','asc');
        $item_ref = $this->db->get()->result_array();
        $item_refSheet->getColumnDimension('A')->setWidth(10);
        $item_refSheet->getColumnDimension('B')->setWidth(20);
        $item_refSheet->getColumnDimension('C')->setWidth(25);
        $item_refSheet->getColumnDimension('D')->setWidth(20);
        $item_refSheet->getColumnDimension('E')->setWidth(20);
        $item_refSheet->getColumnDimension('F')->setWidth(25);
        $item_refSheet->getColumnDimension('G')->setWidth(25);

        $item_refSheet->setCellValue('A1', 'No');
        $item_refSheet->setCellValue('B1', 'Customer Code');
        $item_refSheet->setCellValue('C1', 'Customer Name');
        $item_refSheet->setCellValue('D1', 'Product ID');
        $item_refSheet->setCellValue('E1', 'Product No');
        $item_refSheet->setCellValue('F1', 'Product No Customer');
        $item_refSheet->setCellValue('G1', 'Product Name');
        $item_refSheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $item_refSheet->getStyle('A1:G1')->getFont()->setBold(true);
        $item_refSheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowItem_ref = 2;
        $rowNumItem_ref = 1;
        foreach ($item_ref as $itemref) {
            $item_refSheet->setCellValue('A' . $rowItem_ref, $rowNumItem_ref);
            $item_refSheet->setCellValue('B' . $rowItem_ref, $itemref['customer_code']);
            $item_refSheet->setCellValue('C' . $rowItem_ref, $itemref['customer_name']);
            $item_refSheet->setCellValue('D' . $rowItem_ref, $itemref['product_id']);
            $item_refSheet->setCellValue('E' . $rowItem_ref, $itemref['product_no']);
            $item_refSheet->setCellValue('F' . $rowItem_ref, $itemref['product_no_customer']);
            $item_refSheet->setCellValue('G' . $rowItem_ref, $itemref['product_name']);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $item_refSheet->getStyle('A' . $rowItem_ref . ':G' . $rowItem_ref)->getNumberFormat()->setFormatCode('@');
            $rowItem_ref++;
            $rowNumItem_ref++;
        }

        $spreadsheet->setActiveSheetIndex(0); 
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tmp_delivery_schedules.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit;
    }
}
