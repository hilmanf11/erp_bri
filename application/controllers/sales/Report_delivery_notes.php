<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_delivery_notes extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
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
            $this->load->view('sales/report_delivery_notes');
        } else {
            redirect('error_access');
        }
    }

    public function readsapproval()
    {
        $send = $this->crud->query("SELECT id, name, username FROM users WHERE deleted = 0");
        echo json_encode($send);
    }

    public function datatablesTemp($delivery_order_no, $delivery_note_date)
    {
        $delivery_order_no = explode(",", base64_decode($delivery_order_no));
        $delivery_note_date = base64_decode($delivery_note_date);

        $this->db->select("a.delivery_order_no, 
            b.id as item_fg_id, 
            b.number as item_fg_number, 
            b.name as item_fg_name,
            c.customer_order_no, 
            c.sales_order_no,
            a.qty_del as qty,
            (CASE
            WHEN a.actual_delivery_date < a.delivery_date THEN 1
            WHEN a.actual_delivery_date = a.delivery_date THEN 0
            ELSE 0
            END) as status_delivery,
            b.uom");
        $this->db->from('delivery_orders a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no 
                                          AND a.item_fg_id = c.item_fg_id 
                                          AND a.customer_id = c.customer_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.delivery_order_no', $delivery_order_no);
        $this->db->order_by('a.delivery_order_no, b.number');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readDivision($customer_id)
    {
        $send = $this->crud->query("SELECT a.plant as division
        FROM sales_orders a
        JOIN customers b ON a.customer_id = b.id
        WHERE a.customer_id = '$customer_id'
        GROUP BY a.division");
        echo json_encode($send);
    }

    public function readShipping($customer_id , $division)
    {
        $division = base64_decode($division);
        
        $send = $this->crud->query("SELECT b.address as address_name, b.id
        FROM sales_orders a 
        JOIN customer_address b ON a.customer_address_id = b.id
        WHERE a.customer_id = '$customer_id' and a.plant = '$division' 
        GROUP BY b.address");
        echo json_encode($send);
    }

    public function readDo($customer_id, $division, $customer_address)
    {
        $division = base64_decode($division);
        $customer_address = base64_decode($customer_address);

        $send = $this->crud->query("SELECT DISTINCT a.delivery_order_no, a.delivery_date
        FROM delivery_orders a 
        JOIN item_fg b ON a.item_fg_id = b.id 
        JOIN sales_orders c ON a.item_fg_id = c.item_fg_id
        JOIN customers d ON c.customer_id = d.id
        JOIN customer_address e ON c.customer_address_id = e.id
        WHERE a.customer_id = '$customer_id' and c.plant = '$division' and e.id = '$customer_address'");
        echo json_encode($send);
    }
 
    public function readDelivery_note_no($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_note_no, delivery_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readDelivery_order_no($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT delivery_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM delivery_notes WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function number($delivery_note_date, $divison_number)
    {
        $divison_number = base64_decode($divison_number);
        $customer_number = base64_decode($this->input->post('customer_number'));

        $numberCust = $customer_number;
        $divisions  = "DN". $divison_number;
        $datenow    = date("my", strtotime(base64_decode($delivery_note_date)));
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 7, 4) as kode FROM delivery_notes WHERE `delivery_note_no` like '%$dn_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = @$rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $divisions. "-" . $autoID . "-" . $numberCust . "-" . $datenow;
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
            // $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
            // $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_status_delivery = @base64_decode($get['filter_status_delivery']);
            $filter_status = @base64_decode($get['filter_status']);
            $filter_product_family = @base64_decode($get['filter_product_family']);
            $filter_plant = @base64_decode($get['filter_plant']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name, d.address as shipping_address, e.actual_delivery_date as actual_delivery_order_date");
            $this->db->from('delivery_notes a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('customer_address d', 'b.id = d.customer_id');
            $this->db->join('delivery_orders e', 'a.delivery_order_no = e.delivery_order_no');
            $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id');
            $this->db->join('item_fg f', 'a.item_fg_id = f.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('e.actual_delivery_date >=', $filter_from);
                $this->db->where('e.actual_delivery_date <=', $filter_to);
            }
            if ($filter_product_family != "") {
                $this->db->where('f.item_family_number', $filter_product_family);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            // $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
            // $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('c.customer_order_no', $filter_customer_order_no);
            $this->db->like('c.division', $filter_plant);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.status_delivery', $filter_status_delivery);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.delivery_note_no');
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
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));
            $product_family = base64_decode($this->input->get('product_family'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name,COALESCE(e.shipping, 0) as qty_shipping');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(SELECT 
                                a.delivery_order_no, 
                                a.item_fg_id, 
                                SUM(a.qty) AS shipping
                            FROM shipping_orders a
                            WHERE a.delivery_order_no = '$delivery_note_no'
                            GROUP BY a.delivery_order_no, a.item_fg_id
                            ) e", 'a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id', 'left');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            // $this->db->order_by('b.number', 'ASC');
            if ($product_family != "") {
                $this->db->where('b.item_family_number', $product_family);
            }
            $this->db->order_by('a.delivery_order_no');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, a.address_id');
            $this->db->from('delivery_notes a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customer_address d', 'a.address_id = d.id');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $delivery_notes = $this->crud->read("delivery_notes", [], ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']]);

            if (@$delivery_notes->delivery_order_no != "") {
                $send = $this->crud->update('delivery_notes', ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']], $post);
            } else {
                $send = $this->crud->create('delivery_notes', $post);

                //Ubah Status Sales Order Delivery
                // $this->crud->update("sales_order_deliveries", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no'], "trans_date" => $post['delivery_date']], ["status" => 1]);
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
        $send = $this->crud->delete('delivery_notes', $data);
        echo $send;
    }
    public function print_do($delivery_order_no)
    {
        $delivery_order_no = base64_decode($delivery_order_no);
        $printed   = $this->db->query("UPDATE delivery_notes SET printed=1 WHERE `delivery_note_no` = '$delivery_order_no'");
        $delivery_orders = $this->crud->reads('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);
        $delivery_order = $this->crud->read('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);

        // Tambahkan query untuk mendapatkan data delivery_notes dan approval settings
        $delivery_notes = $this->crud->read('delivery_notes', [], ["delivery_order_no" => $delivery_order_no]);
        $approval_settings = $this->crud->read('approvals', [], ["table_name" => "delivery_notes"]);
        // Inisialisasi variabel approval
        $approval_1 = null;
        $approval_2 = null;
        $created_date = $delivery_notes->created_date;
        
        $date_only = date('Y-m-d', strtotime($created_date));
        $once = ($date_only >= '2025-05-15')? true : false;
        
        if($once==false){
            // Cek status approval kedua
            if ($delivery_notes->approved >= 2) {
                $approval_2 = $this->crud->read('users', [], ["username" => $delivery_notes->approved_by]);
               // $approval_2 = $this->crud->read('users', [], ["username" => $approval_settings->user_approval_2]);
            }
        }
            // Cek status approval pertama
            if ($delivery_notes->approved >= 1) {
                $approval_1 = $this->crud->read('users', [], ["username" => $approval_settings->user_approval_1]);
            }

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        // Tentukan ukuran kertas dan jumlah baris per halaman
        if (count($delivery_orders) <= 5) {
            //$paper_size = '9.5in 11in';
            // $paper_width = '40cm';
            // $paper_height = '14cm';
            $rows_per_page = 5; // Maksimal 5 produk per halaman
        } else {
            //$paper_size = '9.5in 11in';
            // $paper_width = '40cm';
            // $paper_height = '28cm';
            $rows_per_page = 10; // Maksimal 10 produk per halaman
        }

        // Hapus atau ganti karakter '/' dari nomor Delivery Note
        $clean_delivery_order_no = str_replace('/', '-', $delivery_notes->delivery_order_no);

        //Generate QRcode untuk dokumen
        $this->createQrcode($clean_delivery_order_no, "assets/image/qrcode/");
        
        // Generate QR code untuk approval 1 jika sudah diapprove
        if ($approval_1) {
            $this->createQrcode($approval_1->name, "assets/image/qrcode/");
        }
         if($once==false){
            // Generate QR code untuk approval 2 jika sudah diapprove
            if ($approval_2) {
                $this->createQrcode($approval_2->name, "assets/image/qrcode/");
            }
         }

        //Header Print
        $html = '<html><head><title>' . $delivery_order->delivery_order_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="14x14"></head>';
        $html .= '<style>
            body {
                font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI";
                margin: 0;
                padding: 0;
                font-size: 10pt;
            }
            #customers {
                border-collapse: collapse;
                width: 100%;
                margin-bottom: 0;
                font-size: 9pt;
                font-weight: bold;
            }
            #customers td, #customers th {
                border: 0.1mm solid black;
                padding: 2px;
                font-weight: bold;
            }
            .signature-container {
                width: 100%;
                display: flex;
                justify-content: space-between;
                margin-top: 0;
            }
            .customer-signature {
                width: 20%;
            }
            .supplier-signature {
                width: 100%;
            }
            .signature-header {
                border-bottom: 0.1mm solid black;
                text-align: center;
                padding: 5px;
                height: 45px;
            }
            .signature-content {
                height: 100px;
                text-align: center;
            }
            .signature-name {
                border-top: 0.1mm solid black;
                text-align: center;
                padding: 5px;
            }
            .supplier-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
                font-weight: bold;
            }
            .supplier-table th, .supplier-table td {
                border: 0.1mm solid black;
                padding: 2px;
                text-align: center;
                font-size: 9pt;
                font-weight: bold;
            }
            @media screen {
                .print {display: none !important;}
            }
            @media print {
                .noprint {display: none !important;}
                @page {
                    size: 21.59cm 27.97cm;
                    margin: 0;
                    padding:0;
                }
                body { width:100% !important ; height:100%; font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; padding: 0; margin: 0; }
                .print { font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI";width:100% !important; height:100%;margin:0; padding-top:5mm;padding-left:8mm}
                .page {
                    page-break-after: always;max-width:20cm !important; max-height: 27cm !important; margin-left: 0; margin-right:0; margin-top: 0;
                    margin-bottom: 0; padding: 0; justify-content: center; box-sizing: border-box;
                }
                .content {
                    width: 100% !important;
                    padding:0;
                    margin:0;
                }
                table {
                    width: 100% !important;

                }
            }
        </style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Margin 0.5 inch, Scale 100%</p>
                </center></div><div class="print">';

        //Loop Page
        $no = 1;
        $page = ceil(count($delivery_orders) / $rows_per_page);
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, d.customer_order_no, d.attention_to, h.name as created_by_name, i.address, i.contact_person, COALESCE(f.shipping, 0) as qty_shipping');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('sales_orders d', 'a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id and a.customer_id = d.customer_id');
            $this->db->join('delivery_notes e', 'a.delivery_order_no = e.delivery_order_no and a.item_fg_id = e.item_fg_id and a.customer_id = e.customer_id');
            $this->db->join('(SELECT 
                                delivery_order_no, 
                                item_fg_id,
                                SUM(qty) as shipping 
                            FROM shipping_orders 
                            GROUP BY delivery_order_no, item_fg_id
                            ) f', 
                            'a.delivery_order_no = f.delivery_order_no AND a.item_fg_id = f.item_fg_id', 
                            'left');
            $this->db->join('users h', 'e.created_by = h.username');
            $this->db->join('customer_address i', 'd.customer_address_id = i.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'asc');
            // $this->db->limit($rows_per_page, ($i * $rows_per_page));
            $records = $this->db->get()->result_array();

            // Pastikan QR code untuk approval dan created by sudah dibuat
            $this->createQrcode($records[0]['created_by_name'], "assets/image/qrcode/");

            $html .= '<div class="page">
                        <div class="content">
                            <table style="width:100%; margin-bottom: 10px;">
                                <tr>
                                    <th width="10px"><img src="' . $config->favicon . '" width="30px" /></th>
                                    <td width="150px" style="padding:10px;">
                                        <b style="font-size:9pt;">' . $config->name . '</b><br>
                                        <span style="font-size:8pt;">' . $config->description . '</span><br>
                                    </td>
                                    <th width="150px"><center><h3>DELIVERY NOTES</h3></center></th>
                                    <th width="150px" style="text-align:right;">
                                        <table style="width:100%; font-size:8pt;font-weight: bold;">
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
                                    </th>
                                </tr>
                            </table>

                           
                            <div style="width:100%; min-height:25%; position: relative;">
                                <div>
                                    <div style="float:left; width:75%;">
                                        <table style="width:100%; font-size:9pt; font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; margin-bottom:10px;font-weight: bold;">
                                            <tr>
                                                <td width="150px">Delivery Date</td>
                                                <td width="10px">:</td>
                                                <td><b>' . date("d F Y", strtotime(@$records[0]['actual_delivery_date'])) . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="150px">Customer Order No</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['customer_order_no'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="150px">Customer Name</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="150px">Address</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['address'] . '</b></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div style="float:left; width:25%; text-align:right;display:flex;flex-direction:column;text-align:center;align-items: center;">
                                        <img style="margin-right:10px;" src="' . base_url('assets/image/qrcode/' . $clean_delivery_order_no . '.png') . '" width="45"/>
                                        <small style="font-size:9pt;font-weight: bold;">DN No : ' . $delivery_notes->delivery_order_no . '</small><br><br>
                                    </div>
                                    <table id="customers">
                                        <tr>
                                            <th width="20px">No</th>
                                            <th>Product No</th>
                                            <th>Product Name</th>
                                            <th width="60">Qty</th>
                                            <th>UoM</th>
                                        </tr>';
            foreach ($records as $record) {
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record['item_fg_number'] . '</td>
                            <td>' . $record['item_fg_name'] . '</td>
                            <td style="text-align:right">' . number_format($record['qty_shipping'], 0, ",", ".") . '</td>
                            <td>' . $record['uom'] . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table>';
            // if ($i + 1 != $page) {
            //     $html .= '<div style="page-break-after:always;"></div>';
            // }

            if($once==true){
                $html .= '</div></div>
                        </div>
                        <div class="footer" style="margin-top:10pt; font-size:9pt;">
                            <div class="signature-container">
                                
                                <!-- Tabel Supplier -->
                                <div class="supplier-signature">
                                    <table class="supplier-table">
                                        <tr>
                                            <th style="width:15%;padding:2pt;" rowspan="2">Customer</th>
                                            <th style="width:25%;padding:2pt;border:none" rowspan="2"></th>
                                            <th style="width:15%;padding:2pt;" rowspan="2">Transporter</th>
                                            <th style="width:45%;padding:2pt;" colspan="2">Supplier</th>
                                        </tr>
                                        <tr style="width:100%;">
                                            <th style="padding:2pt;">Checked & Approved By</th>
                                            <th style="padding:2pt;">Prepared By</th>
                                        </tr>
                                        <tr>
                                        <td></td>
                                        <td style="border:none"></td>
                                        <td></td>';
        $html .= '
                                            <td>';
                                            // First Approval
                                            if ($delivery_notes->approved >= 1 && $approval_settings->user_approval_1) {
                                                $html .= '<img src="' . base_url('assets/image/qrcode/' . $approval_1->name . '.png') . '" style="width:35pt"/>';
                                            }
        $html .= '</td>
                                            <td>
                                                <img src="' . base_url('assets/image/qrcode/' . @$records[0]['created_by_name'] . '.png') . '" style="width:35pt"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;"></td>
                                            <td style="text-align:center;border:none;"></td>
                                            <td style="text-align:center;"></td>';
        $html .= '
                                            <td style="text-align:center;">';
                                            // Tampilkan nama First Approval
                                            if ($approval_settings->user_approval_1) {
                                                $html .= $approval_1->name;
                                            } else if ($approval_settings->user_approval_1) {
                                                $first_approval_user = $this->crud->read('users', [], ["username" => $approval_settings->user_approval_1]);
                                                $html .= $first_approval_user ? $first_approval_user->name : '';
                                            }
        $html .= '</td>
                                            <td style="text-align:center;">' . @$records[0]['created_by_name'] . '</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>';
            }else{
                
            $html .= '</div></div>
                        </div>
                        <div class="footer" style="margin-top:10pt; font-size:9pt;">
                            <div class="signature-container">
                                
                                <!-- Tabel Supplier -->
                                <div class="supplier-signature">
                                    <table class="supplier-table">
                                        <tr>
                                            <th style="width:15%;padding:2pt;" rowspan="2">Customer</th>
                                            <th style="width:25%;padding:2pt;border:none" rowspan="2"></th>
                                            <th style="width:15%;padding:2pt;" rowspan="2">Transporter</th>
                                            <th style="width:45%;padding:2pt;" colspan="3">Supplier</th>
                                        </tr>
                                        <tr style="width:100%;">
                                            <th style="padding:2pt;">Approved By</th>
                                            <th style="padding:2pt;">Checked By</th>
                                            <th style="padding:2pt;">Prepared By</th>
                                        </tr>
                                        <tr>
                                        <td></td>
                                        <td style="border:none"></td>
                                        <td></td><td>';
                                            // Second Approval
                                            if ($delivery_notes->approved >= 2) {
                                                $html .= '<img src="' . base_url('assets/image/qrcode/' . $approval_2->name . '.png') . '" style="width:35pt"/>';
                                            }
        $html .= '</td>
                                            <td>';
                                            // First Approval
                                            if ($delivery_notes->approved >= 1 && $approval_settings->user_approval_1) {
                                                $html .= '<img src="' . base_url('assets/image/qrcode/' . $approval_1->name . '.png') . '" style="width:35pt"/>';
                                            }
        $html .= '</td>
                                            <td>
                                                <img src="' . base_url('assets/image/qrcode/' . @$records[0]['created_by_name'] . '.png') . '" style="width:35pt"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;"></td>
                                            <td style="text-align:center;border:none;"></td>
                                            <td style="text-align:center;"></td>
                                            <td style="text-align:center;">';
                                            // Tampilkan nama Second Approval
                                            //if ($delivery_notes->approved >= 2 && $delivery_notes->approved_to == "") {
                                                $html .= $approval_2->name;
                                           // } else if ($approval_settings->user_approval_2) {
                                          //      $second_approval_user = $this->crud->read('users', [], ["username" => $delivery_notes->approved_by]);
                                           //     $html .= $second_approval_user ? $second_approval_user->name : '';
                                           // }
        $html .= '</td>
                                            <td style="text-align:center;">';
                                            // Tampilkan nama First Approval
                                            if ($delivery_notes->user_approval_1) {
                                                $html .= $approval_1->name;
                                            } else if ($approval_settings->user_approval_1) {
                                                $first_approval_user = $this->crud->read('users', [], ["username" => $approval_settings->user_approval_1]);
                                                $html .= $first_approval_user ? $first_approval_user->name : '';
                                            }
        $html .= '</td>
                                            <td style="text-align:center;">' . @$records[0]['created_by_name'] . '</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_notes_report_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_status_delivery = @base64_decode($get['filter_status_delivery']);
        $filter_status = @base64_decode($get['filter_status']);
        $filter_product_family = @base64_decode($get['filter_product_family']);
        $filter_plant = @base64_decode($get['filter_plant']);

        $customer = $this->crud->read("customers", [], ["id" => $filter_customer_id]);
        $customer_name = empty($filter_customer_id)?"ALL":@$customer->name;
        $customer_order_no = empty($filter_customer_order_no)?"ALL":$filter_customer_order_no;
        $item_fg = empty($filter_item_fg)?"ALL":$filter_item_fg;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, d.address as shipping_address, 
            f.number as item_fg_number, f.name as item_fg_name, f.id as item_fg_id,
            e.actual_delivery_date as actual_delivery_order_date, f.uom,
            e.qty_del as qty_delivery");
        $this->db->from('delivery_notes a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address d', 'b.id = d.customer_id');
        $this->db->join('delivery_orders e', 'a.delivery_order_no = e.delivery_order_no AND a.item_fg_id = e.item_fg_id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id');
        $this->db->join('item_fg f', 'a.item_fg_id = f.id');
        
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('e.actual_delivery_date >=', $filter_from);
            $this->db->where('e.actual_delivery_date <=', $filter_to);
        }
        $this->db->where('e.status', 1);
        if ($filter_customer_id != "") {
            $this->db->where('a.customer_id', $filter_customer_id);
        }
        if ($filter_delivery_note_no != "") {
            $this->db->where('a.delivery_note_no', $filter_delivery_note_no);
        }
        // if ($filter_delivery_order_no != "") {
        //     $this->db->where('a.delivery_order_no', $filter_delivery_order_no);
        // }
        // if ($filter_sales_order_no != "") {
        //     $this->db->where('a.sales_order_no', $filter_sales_order_no);
        // }
        if ($filter_customer_order_no != "") {
            $this->db->where('c.customer_order_no', $filter_customer_order_no);
        }
        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }
        if ($filter_status_delivery != "") {
            $this->db->where('a.status_delivery', $filter_status_delivery);
        }
        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        if ($filter_product_family != "") {
            $this->db->where('f.item_family_number', $filter_product_family);
        }
        if ($filter_plant != "") {
            $this->db->where('c.division', $filter_plant);
        }
        
        //$this->db->group_by('a.delivery_note_no');
        //$this->db->order_by('a.status', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head>
        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
            }
            #customers {
                border-collapse: collapse;
                width: 100%;
                font-size: 12px;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .no-wrap {
                white-space: nowrap;
            }
            .text-right {
                text-align: right;
            }

            #customers td, 
            #customers th {
                border: 1px solid #ddd;
                padding: 2px;
            }
            #customers tr:nth-child(even){
                background-color: #f2f2f2;
            }
            #customers tr:hover {
                background-color: #ddd;
            }
            #customers th {
                padding-top: 2px;
                padding-bottom: 2px;
                text-align: left;
                color: black;
            }
        </style>
        <body>
            <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>'.$config->description.'</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <h3 style="margin:0;">REPORT DELIVERY NOTES</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <table style="width: 40%; font-size:12px;">
            <tr>
                <th style="width:100px; text-align:left;">Period</th>
                <td style="width:10px;">:</td>
                <td style="width:200px;">' . $filter_from . ' To ' . $filter_to . '</td>
            </tr>
            <tr>
                <th style="width:100px; text-align:left;">Customer Name</th>
                <td style="width:10px;">:</td>
                <td style="width:200px;">' . $customer_name . '</td>
            </tr>
            <tr>
                <th style="width:100px; text-align:left;">Customer Order No</th>
                <td style="width:10px;">:</td>
                <td style="width:200px;">' . $customer_order_no . '</td>
            </tr>
            <tr>
                <th style="width:100px; text-align:left;">Product No</th>
                <td style="width:10px;">:</td>
                <td style="width:200px;">' . $item_fg . '</td>
            </tr>
        </table>
        <br>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px; text-align: center;">No</th>
                        <th style="width: 80px; text-align: center;">Delivery Date</th>
                        <th style="width: 250px; text-align: center;">Customer Name</th>
                        <th style="width: 120px; text-align: center;">Delivery Note No</th>
                        <th style="width: 120px; text-align: center;">Customer Order No</th>
                        <th style="width: 100px; text-align: center;">Product ID</th>
                        <th style="width: 100px; text-align: center;">Product No</th>
                        <th style="width: 150px; text-align: center;">Product Name</th>
                        <th style="width: 80px; text-align: center;">Qty Delivery</th>
                        <th style="width: 50px; text-align: center;">UOM</th>
                        <th style="width: 80px; text-align: center;">Status Delivery</th>
                        <th style="width: 80px; text-align: center;">Status Invoices</th>
                    </tr>';
        
        $no = 1;
        $totalQtyDel = 0;
        foreach ($records as $row) {
            if ($row['status_delivery'] == 0) {
                $status_delivery = 'ON SCHEDULE';
                $color = 'green';
            } else if($row['status_delivery'] == 1) {
                $status_delivery = 'DELAY';
                $color = 'red';
            }else {
                $status_delivery = 'EARLY';
                $color = '#FF9B17';
            }

            if($row['status'] == 0){
                $status = 'OPEN';
                $colorS = 'green';
            } else {
                $status = 'CLOSE';
                $colorS = 'red';
            }

            $totalQtyDel += $row['qty'];

            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap">'.date('Y-m-d', strtotime($row['actual_delivery_order_date'])).'</td>
                        <td class="no-wrap">'.$row['customer_name'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['customer_order_no'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td style="text-align: right;">'.number_format($row['qty'],0,".",".").'</td>
                        <td class="no-wrap">'.$row['uom'].'</td>
                        <td class="no-wrap" style="font-weight: bold; color: '.$color.'; text-align: center;">'.$status_delivery.'</td>
                        <td class="no-wrap" style="font-weight: bold; color: '.$colorS.'; text-align: center;">'.$status.'</td>
                    </tr>';
            $no++;
        }


        $html .= '<tr>
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalQtyDel, 0, '.', '.') . '</td>
            <td colspan="3"></td>
        </tr>';
      
        
        $html .= '</table></div>';
        echo $html;
    }
}
