<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_check_serialno extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_check_serialno');
        } else {
            redirect('error_access');
        }
    }
    public function readReceiptNo()
    {
        $supplier = $this->input->get('supplier');
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    // public function readItems()
    // {
    //     $supplier = $this->input->get('supplier');
    //     $receipt_no = base64_decode($this->input->get('receipt_no'));
    //     $search = $this->input->get('q');
        
    //     $sql = "SELECT DISTINCT b.id as item_rm_id, b.number as item_number, b.name as item_name
    //         FROM purchase_order_receipts a
    //         JOIN item_rm b on a.item_rm_id = b.id
    //         WHERE a.supplier_id = '$supplier'";
            
    //     if (!empty($receipt_no)) {
    //         $sql .= " AND a.receipt_no = '$receipt_no'";
    //     }
        
    //     if (!empty($search)) {
    //         $sql .= " AND (b.number LIKE '%$search%' OR b.name LIKE '%$search%')";
    //     }
        
    //     $sql .= " ORDER BY b.number ASC";
        
    //     $send = $this->crud->query($sql);
    //     echo json_encode($send);
    // }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->db->select('b.id as item_rm_id, b.number as item_number, b.name as item_name');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($post != "") {
            $this->db->like('b.number', $post);
            $this->db->or_like('b.name', $post);
        }
        $this->db->group_by('b.number');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }
        
    

    // public function readItems()
    // {
    //     $supplier = $this->input->get('supplier');
    //     $receipt_no = base64_decode($this->input->get('receipt_no'));
    //     $send = $this->crud->query("SELECT b.id as item_rm_id, b.number as item_number, b.name as item_name
    //         FROM purchase_order_receipts a
    //         JOIN item_rm b on a.item_rm_id = b.id
    //         WHERE a.supplier_id = '$supplier' and a.receipt_no = '$receipt_no' ORDER BY a.receipt_no DESC");
    //     echo json_encode($send);
    // }
    public function print($option = "")
    {
        $filter_display_by = $this->input->get("filter_display_by");
        if ($filter_display_by == "nbc") {
            return $this->print_nbc($option);
        } else {
            return $this->print_po($option);
        }
    }

    public function print_po($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_serial_no = base64_decode($this->input->get("filter_serial_no"));
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_receipt = base64_decode($this->input->get("filter_receipt"));
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status_in = $this->input->get("filter_status_in");
        $filter_status_out = $this->input->get("filter_status_out");
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.label_no, c.status as status_label, c.qty, IF(d.id IS NULL, 0, 1) as status_out');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('purchase_order_labels c', 'a.receipt_id = c.receipt_id');
        $this->db->join('issued_material_details d', 'c.label_no = d.label_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.receipt_date between '$filter_from' and '$filter_to'");
        $this->db->like('c.label_no', $filter_serial_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.receipt_no', $filter_receipt);
        $this->db->like('a.item_rm_id', $filter_product_no);
        if ($filter_status_in != "-") {
            $this->db->like('c.status', $filter_status_in);
        }
        if ($filter_status_out != "-") {
            if ($filter_status_out == 0) {
                $this->db->where('d.id IS NULL');
            } else {
                $this->db->where('d.id IS NOT NULL');
            }
        }
        $this->db->order_by('a.receipt_no', 'ASC');
        $this->db->order_by('c.label_no', 'ASC');
        $records = $this->db->get()->result_array();
        $supplier = $this->crud->read("suppliers", [], ["id" => @$records[0]['supplier_id']]);
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body><center><div style="float: left; font-size: 12px; text-align: left;"><table style="width: 100%;"><tr><td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;"><img src="' . $config->favicon . '" width="30"></td><td style="font-size: 14px; text-align: left; margin:2px;"><b>' . $config->name . '</b><br><small>' . $config->description . '</small></td></tr></table></div><div style="float: right; font-size: 12px; text-align: right;">Print Date ' . date("d M Y H:i:s") . ' <br>Print By ' . $this->session->username . '  </div><br><br><br><h3 style="margin:0;">CHECK SERIAL NO (RM)</h3><small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small></center><br><table id="customers" border="1"><tr><th width="20">No</th><th>Receipt No</th><th>Receipt Date</th><th>Serial No</th><th>Label No</th><th>Part No</th><th>Part Name</th><th>Quantity</th><th>Status IN</th><th>Status OUT</th><th>Created By</th></tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status_label'] == 0) {
                $status_in = "<b style='color:green;'>OPEN</b>";
            } else {
                $status_in = "<b style='color:red;'>CLOSE</b>";
            }
            if ($data['status_out'] == 0) {
                $status_out = "<b style='color:green;'>OPEN</b>";
            } else {
                $status_out = "<b style='color:red;'>CLOSE</b>";
            }
            $html .= '  <tr><td style="text-align:center">' . $no . '</td><td>' . $data['receipt_no'] . '</td><td>' . $data['receipt_date'] . '</td><td>' . $data['receipt_id'] . '</td><td>' . $data['label_no'] . '</td><td>' . $data['item_number'] . '</td><td>' . $data['item_name'] . '</td><td style="text-align:right">' . number_format($data['qty'], 2) . '</td><td>' . $status_in . '</td><td>' . $status_out . '</td><td>' . $data['created_by'] . '</td></tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_nbc($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=new_barcode_$format.xls");
        }
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_status_out = $this->input->get("filter_status_out");
        $this->db->select('a.*, b.number as item_number, b.name as item_name');
        $this->db->from('new_barcode a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        if (!empty($filter_product_no)) {
            $this->db->where('a.item_rm_id', $filter_product_no);
        }
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('a.cut_off_date >=', $filter_from);
            $this->db->where('a.cut_off_date <=', $filter_to);
        }
        if (isset($filter_status_out) && $filter_status_out != "-") {
            if ($filter_status_out == "0") {
                $this->db->where('a.status', 0);
            } else if ($filter_status_out == "1") {
                $this->db->where('a.status', 1);
            }
        }
        $this->db->order_by('a.cut_off_date', 'ASC');
        $this->db->order_by('a.label_no', 'ASC');
        $records = $this->db->get()->result_array();
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body><center><div style="float: left; font-size: 12px; text-align: left;"><table style="width: 100%;"><tr><td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;"><img src="' . $config->favicon . '" width="30"></td><td style="font-size: 14px; text-align: left; margin:2px;"><b>' . $config->name . '</b><br><small>' . $config->description . '</small></td></tr></table></div><div style="float: right; font-size: 12px; text-align: right;">Print Date ' . date("d M Y H:i:s") . ' <br>Print By ' . $this->session->username . '  </div><br><br><br><h3 style="margin:0;">CHECK SERIAL NO (NEW BARCODE)</h3><small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small></center><br><table id="customers" border="1"><tr><th width="20">No</th><th>Created Date</th><th>Label No</th><th>Part No</th><th>Part Name</th><th>Quantity</th><th>UOM</th><th>Cut Off Date</th><th>Status Out</th><th>Created By</th></tr>';
        $no = 1;
        foreach ($records as $data) {
            $status = $data['status'] == 0 ? '<b style="color:green;">OPEN</b>' : '<b style="color:red;">CLOSE</b>';
            $html .= '<tr>';
            $html .= '<td style="text-align:center">' . $no . '</td>';
            $html .= '<td>' . $data['created_date'] . '</td>';
            $html .= '<td>' . $data['label_no'] . '</td>';
            $html .= '<td>' . $data['item_number'] . '</td>';
            $html .= '<td>' . $data['item_name'] . '</td>';
            $html .= '<td style="text-align:right">' . number_format($data['qty'], 2) . '</td>';
            $html .= '<td style="text-align:center">' . $data['uom'] . '</td>';
            $html .= '<td>' . $data['cut_off_date'] . '</td>';
            $html .= '<td style="text-align:center">' . $status . '</td>';
            $html .= '<td>' . $data['created_by'] . '</td>';
            $html .= '</tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    // Endpoint untuk filter Part No khusus New Barcode
    public function readItemsNBC()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('b.id as item_rm_id, b.number as item_number, b.name as item_name');
        $this->db->from('new_barcode a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($post != "") {
            $this->db->like('b.number', $post);
            $this->db->or_like('b.name', $post);
        }
        $this->db->group_by('b.number');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
}
