<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipts extends CI_Controller
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
            $this->load->view('purchase/purchase_order_receipts');
        } else {
            redirect('error_access');
        }
    }

    public function generate_lotno($date = "") {
        if ($date == "") {
            $current_fullyear = date('Y');
            $current_year = date('y');
            $current_month = date('m');
        } else {
            $current_fullyear = date('Y', strtotime(base64_decode($date)));
            $current_year = date('y', strtotime(base64_decode($date)));
            $current_month = date('m', strtotime(base64_decode($date)));
        }
        
        $query = $this->db->query("SELECT COUNT(*) as total_group FROM (SELECT receipt_no FROM purchase_order_receipts WHERE YEAR(receipt_date) = ? AND MONTH(receipt_date) = ? GROUP BY receipt_no) as total", [$current_fullyear, $current_month]);
        $row = $query->row();
        if (intval($row->total_group) > 0) {
            $new_sequence_number = sprintf("%03d", intval($row->total_group) + 1);
        } else {
            $new_sequence_number = '001';
        }
        echo $new_sequence_number . $current_month . $current_year ;
    }
    public function reads()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, e.name as supplier_name, d.mpq, d.moq, d.price');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_number = c.number');
        $this->db->join('supplier_items d', 'a.item_rm_id = d.item_rm_id');
        $this->db->join('suppliers e', 'd.supplier_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.request_no', $request_no);
        $this->db->like('d.supplier_id', $supplier_id);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    public function readPoNo($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT a.po_no FROM purchase_order_receipts a LEFT JOIN purchase_orders b ON a.po_no = b.po_no WHERE a.supplier_id = '$supplier_id' and a.status = 0 GROUP BY a.po_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function readReceipt($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readPart($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        
        // $records = $this->crud->query("SELECT b.id, b.number, b.name FROM purchase_order_receipts a JOIN item_rm b ON a.item_rm_id = b.id WHERE a.supplier_id = '$supplier_id' and a.status = '0' GROUP BY a.receipt_no ORDER BY a.created_date desc");
        // echo json_encode($records);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->db->select('b.id, b.number, b.name');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.supplier_id', $supplier_id);
        if ($post != "") {
            $this->db->like('b.number', $post);
            $this->db->or_like('b.name', $post);
        }
        $this->db->group_by('b.number');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }
    public function readDocno($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readSupplier()
    {
        $records = $this->crud->query("SELECT b.id, b.number, b.name FROM purchase_order_receipts a JOIN suppliers b ON a.supplier_id = b.id WHERE a.status = '0' GROUP BY a.supplier_id ORDER BY a.created_date desc");
        echo json_encode($records);
    }
    public function receipt_no($date = "")
    {
        if ($date == "") {
            $datenow = date("Ymd");
        } else {
            $datenow = date("Ymd", strtotime(base64_decode($date)));
        }
        $sqlGetID   = $this->db->query("SELECT max(receipt_no) as kode FROM purchase_order_receipts WHERE receipt_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "POR-" . $datenow . "-" . $autoID;
    }
    public function receipt_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receipt_id) as kode FROM purchase_order_receipts WHERE receipt_id like '%$receipt_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $receipt_no . "-" . $autoID;
    }

    public function checkLabel($receipt_no)
    {
        $receipt_no = base64_decode($receipt_no);
        $sqlReceipt = $this->db->query("SELECT sum(qty_label) as qty_label FROM purchase_order_receipts WHERE receipt_no ='$receipt_no'");
        $rowReceipt = $sqlReceipt->row();

        $sqlLabel = $this->db->query("SELECT count(label_no) as label_no FROM scan_item_receipts WHERE receipt_no ='$receipt_no'");
        $rowLabel = $sqlLabel->row();

        if (empty(@$rowLabel->label_no)) {
            $label_no = 0;
        } else {
            $label_no = $rowLabel->label_no;
        }

        echo json_encode(["qty_label" => $rowReceipt->qty_label, "label_no" => $label_no]);
    }

    public function checkPassword()
    {
        $inputPassword = base64_decode($this->input->post('password'));
        $sessionPassword = $this->session->password;

        if ($inputPassword === $sessionPassword) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    // public function updateStatusHold()
    // {
    //     $receiptId  = $this->input->post('receipt_id');
    //     $statusHold = $this->input->post('status_hold');

    //     if (!$receiptId) {
    //         echo json_encode(['success' => false, 'message' => 'Receipt ID not found']);
    //         return;
    //     }

    //     $this->db->where('id', $receiptId);
    //     $update = $this->db->update('purchase_order_receipts', ['status_hold' => $statusHold]);

    //     if ($update) {
    //         echo json_encode(['success' => true]);
    //     } else {
    //         echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    //     }
    // }

    public function updateStatusHold()
    {
        $receiptId = $this->input->post('receipt_id');
        if (!$receiptId) {
            echo json_encode(['success' => false, 'message' => 'Receipt ID not found']);
            return;
        }

        // ambil status sekarang
        $row = $this->db->get_where('purchase_order_receipts', ['receipt_id' => $receiptId])->row();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Receipt not found']);
            return;
        }

        // toggle status_hold
        $newStatus = ($row->status_hold == 1) ? 0 : 1;

        $this->db->where('receipt_id', $receiptId);
        $update = $this->db->update('purchase_order_receipts', ['status_hold' => $newStatus]);

        if ($update) {
            $msg = $newStatus == 1 
                ? "Stock for this part no is locked, you can’t supply this part to production!!"
                : "Stock for this part no is unlocked, you can now supply this part to production!!";

            echo json_encode(['success' => true, 'message' => $msg, 'status_hold' => $newStatus]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    }

    //Read Items per Po_no
    public function readItems()
    {
        $po_no = $this->input->get('po_no');
        $search_query = isset($_POST['q']) ? $_POST['q'] : "";

        if (empty($po_no)) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter po_no diperlukan']);
            return;
        }

        $query = "
            SELECT po.po_no, po.po_date, po.item_rm_id, po.item_number, po.item_name, po.qty_po, po.mpq, po.supplier_id, po.uom, po.qty_os, po.qty_receipt, po.qty_label 
            FROM (
                SELECT 
                    a.po_no as po_no, 
                    a.po_date as po_date, 
                    b.id as item_rm_id, 
                    b.number as item_number, 
                    b.name as item_name, 
                    a.qty as qty_po, 
                    c.mpq as mpq, 
                    a.supplier_id as supplier_id, 
                    b.uom as uom, 
                    (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_os, 
                    (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_receipt, 
                    CEIL((a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) / c.mpq) as qty_label
                FROM purchase_orders a 
                LEFT JOIN item_rm b ON a.item_rm_id = b.id 
                LEFT JOIN supplier_items c ON a.item_rm_id = c.item_rm_id AND a.supplier_id = c.supplier_id 
                LEFT JOIN (
                    SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no 
                    FROM purchase_order_receipts 
                    GROUP BY item_rm_id, supplier_id, po_no
                ) d ON a.item_rm_id = d.item_rm_id AND a.supplier_id = d.supplier_id AND a.po_no = d.po_no
                WHERE a.po_no = '$po_no' 
                AND a.status = 0 
                AND a.deleted = 0
                UNION
                SELECT 
                    a.po_no as po_no, 
                    a.po_date as po_date, 
                    b.id as item_rm_id, 
                    b.number as item_number, 
                    b.name as item_name, 
                    a.qty as qty_po, 
                    c.mpq as mpq, 
                    a.supplier_id as supplier_id, 
                    b.uom as uom, 
                    (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_os, 
                    (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_receipt, 
                    CEIL((a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) / c.mpq) as qty_label
                FROM os_po a 
                LEFT JOIN item_rm b ON a.item_rm_id = b.id 
                LEFT JOIN supplier_items c ON a.item_rm_id = c.item_rm_id AND a.supplier_id = c.supplier_id 
                LEFT JOIN (
                    SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no 
                    FROM os_po 
                    GROUP BY item_rm_id, supplier_id, po_no
                ) d ON a.item_rm_id = d.item_rm_id AND a.supplier_id = d.supplier_id AND a.po_no = d.po_no
                WHERE a.po_no = '$po_no' 
                AND a.status = 0 
                AND a.deleted = 0
            ) AS po
        ";

        if (!empty($search_query)) {
            $query .= " WHERE po.item_number LIKE '%$search_query%' OR po.item_name LIKE '%$search_query%'";
        }

        // $query .= " ORDER BY po.po_no DESC";

        $query .= " ORDER BY po.item_name ASC";


        $records = $this->crud->query($query);
        echo json_encode($records);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_supplier = $this->input->get('filter_supplier');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_receipt = $this->input->get('filter_receipt');
            $filter_product_no = $this->input->get('filter_product_no');
            $filter_doc_no = $this->input->get('filter_doc_no');
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];
            if ($id === "0") {
                $this->db->select('a.po_no, a.receipt_no, a.receipt_date, a.awb_no, a.awb_date, a.bc_kind, a.bc_document, a.bc_aju, a.bc_date, b.number as supplier_id, b.name as supplier_name, a.total_receipt as qty_receipt_dt, a.total_label as qty_label, a.status, a.status_hold');
                $this->db->from('(SELECT *, sum(qty_label) as total_label, sum(qty_receipt) as total_receipt FROM purchase_order_receipts GROUP BY receipt_no ORDER BY status asc) a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left');
                $this->db->join('purchase_orders c', 'a.po_no = c.po_no and a.item_rm_id = c.item_rm_id', 'left');
                $this->db->join('item_rm d', 'a.item_rm_id = d.id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                if ($filter_supplier != "") {
                    $this->db->where('a.supplier_id', $filter_supplier);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_receipt != "") {
                    $this->db->where('a.receipt_no', $filter_receipt);
                }
                if ($filter_product_no != "") {
                    $this->db->where('d.name', $filter_product_no);
                }
                if ($filter_doc_no != "") {
                    $this->db->where('a.bc_document', $filter_doc_no);
                }
                $this->db->group_by('a.receipt_no');
                $this->db->order_by('a.created_date', 'DESC');
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.receipt_date', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $receipt_no = $record['receipt_no'];
                    $purchase_order_label = $this->crud->query("SELECT receipt_id, SUM(`status`) as total_scan FROM purchase_order_labels WHERE receipt_id like '%$receipt_no%'");

                    $arr[] = array(
                        "id" => $record['receipt_no'],
                        "po_no" => $record['po_no'],
                        "bc_document" => $record['bc_document'],
                        "bc_date" => $record['bc_date'],
                        "receipt_no" => $record['receipt_no'],
                        "receipt_date" => $record['receipt_date'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "qty_label" => $record['qty_label'],
                        "total_scan" => $purchase_order_label[0]->total_scan,
                        "status" => $record['status'],
                        "state" => "closed",
                        "locked" => "closed",
                    );
                }
                //Mapping Data
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    a.qty_receipt as qty_receipt_dt,
                    a.id as purchase_order_receipts_id, 
                    a.receipt_id as id, 
                    b.number as supplier_id, 
                    b.name as supplier_name, 
                    c.number as item_number, 
                    c.number_internal as item_number_internal, 
                    c.name as item_name, 
                    d.name as item_family_name, 
                    b.currency, 
                    c.uom,
                    e.mpq,
                    sum(g.status) as total_scan,
                    g.lot_no as label_lot_no,
                    a.lot_no as por_lot_no,
                    a.lot_no_internal as por_lot_no_bri,
                    h.name as transaction_type,
                    a.status_hold as locked');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                $this->db->join('purchase_order_labels g', 'g.receipt_id = a.receipt_id', 'left');
                $this->db->join('transaction_type h', 'a.transaction_type = h.type', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                $this->db->where('a.receipt_no', $id);
                $this->db->group_by('a.receipt_id');
                $this->db->order_by('a.receipt_id', 'ASC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }
    
    // public function datatablesTemp()
    // {
    //     $po_no = $this->input->get('po_no');
    //     $records = $this->crud->query("SELECT po.po_no, po.po_date, po.item_rm_id, po.item_number, po.item_name, po.qty_po, po.mpq, po.supplier_id, po.uom, po.qty_os, po.qty_receipt, po.qty_label FROM (
    //         SELECT a.po_no as po_no, po_date as po_date, b.id as item_rm_id, b.number as item_number, b.name as item_name, a.qty as qty_po, c.mpq as mpq, a.supplier_id as supplier_id, b.uom as uom, (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_os, (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_receipt, CEIL((a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) / c.mpq) as qty_label FROM purchase_orders a LEFT JOIN item_rm b ON a.item_rm_id = b.id LEFT JOIN supplier_items c on a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id LEFT JOIN (SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no FROM purchase_order_receipts GROUP BY item_rm_id, supplier_id, po_no) d ON a.item_rm_id = d.item_rm_id and a.supplier_id = d.supplier_id and a.po_no = d.po_no WHERE a.po_no = '$po_no' and a.status = 0 and a.deleted = 0 and a.approved_to = ''
    //         UNION
    //         SELECT a.po_no as po_no, a.po_date as po_date, b.id as item_rm_id, b.number as item_number, b.name as item_name, a.qty as qty_po, c.mpq as mpq, a.supplier_id as supplier_id, b.uom as uom, (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_os, (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_receipt, CEIL((a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) / c.mpq) as qty_label FROM os_po a LEFT JOIN item_rm b ON a.item_rm_id = b.id LEFT JOIN supplier_items c on a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id LEFT JOIN (SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no FROM os_po GROUP BY item_rm_id, supplier_id, po_no) d ON a.item_rm_id = d.item_rm_id and a.supplier_id = d.supplier_id and a.po_no = d.po_no WHERE a.po_no = '$po_no' and a.status = 0 and a.deleted = 0
    //         ) as po ORDER BY po.po_no DESC");
    //     echo json_encode($records);
    // }
    
    public function createv1()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $post['transaction_type'] = 'RE-0001';
                $send   = $this->crud->create('purchase_order_receipts', array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])]));
                if ($post['qty_os'] > $post['qty_receipt']) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $os_po = $this->crud->read("os_po", [], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);
                if (!empty($os_po->po_no)) {
                    if (($os_po->qty_receipt + $post['qty_receipt']) === $os_po->qty) {
                        $status = 1;
                    }
                    if (($os_po->qty_receipt + $post['qty_receipt']) < $os_po->qty) {
                        $status = 0;
                    }
                }
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_rm_id', $post['item_rm_id']);
                $this->db->update("purchase_orders", ["status" => $status]);
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_rm_id', $post['item_rm_id']);
                $this->db->update("os_po", ["status" => $status]);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $post['transaction_type'] = 'RE-0001';

                // Simpan data receipt baru
                $send = $this->crud->create(
                    'purchase_order_receipts',
                    array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])])
                );

                // Ambil total qty_receipt yang sudah ada untuk PO + item ini
                $this->db->select_sum('qty_receipt', 'total_receipt');
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_rm_id', $post['item_rm_id']);
                $totalRow = $this->db->get('purchase_order_receipts')->row();

                $totalReceipt = (float) $totalRow->total_receipt;

                $po = $this->crud->read("purchase_orders", [], [
                    "po_no" => $post['po_no'],
                    "item_rm_id" => $post['item_rm_id']
                ]);

                $status = 0;

                if (!empty($po->po_no)) {
                    $limitQty = $po->qty;

                    if ($totalReceipt >= $limitQty) {
                        $status = 1;
                    }
                }

                $os_po = $this->crud->read("os_po", [], [
                    "po_no" => $post['po_no'],
                    "item_rm_id" => $post['item_rm_id']
                ]);

                if (!empty($os_po->po_no)) {
                    if ($totalReceipt >= $os_po->qty) {
                        $status = 1;
                    } else {
                        $status = 0;
                    }
                }

                // Update status di purchase_orders dan os_po
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_rm_id', $post['item_rm_id']);
                $this->db->update("purchase_orders", ["status" => $status]);

                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_rm_id', $post['item_rm_id']);
                $this->db->update("os_po", ["status" => $status]);

                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }


    // public function delete()
    // {
    //     $data = $this->input->post();
    //     // Fetch the current status from the table you are checking
    //     $status = $this->crud->get('purchase_order_labels', ['receipt_id' => $data['receipt_id']], 'status');
    //     // Check if the status is 0 before proceeding
    //     if ($status === "0") {
    //         // Perform the delete operations
    //         $deletePurchaseOrderReceipts = $this->crud->delete('purchase_order_receipts', ["id" => $data['id']]);
    //         $deleteScanItemReceipts = $this->crud->delete('scan_item_receipts', ["receipt_id" => $data['receipt_id']]);
    //         $updatePurchaseOrders = $this->crud->update('purchase_orders', ["po_no" => $data['po_no'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);

    //         echo $deletePurchaseOrderReceipts;
    //     } else {
    //         // Return a response indicating the status was not 0
    //         //echo log_message('error', 'Serial already scan in. Can not delete data!');//
    //         echo json_encode(['error' => 'Serial already scan in. Can not delete data!']);
    //     }
    // }\

    public function delete()
    {
        $data = $this->input->post();
        $deletePurchaseOrderReceipts = $this->crud->delete('purchase_order_receipts', ["id" => $data['id']]);
        $deleteScanItemReceipts = $this->crud->delete('scan_item_receipts', ["receipt_id" => $data['receipt_id']]);
        $updatePurchaseOrders = $this->crud->update('purchase_orders', ["po_no" => $data['po_no'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);
        echo $deletePurchaseOrderReceipts;
    }

    // from create new label
    // public function print_label_pov1($receipt_no)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $receipt_no = base64_decode($receipt_no);
    //     $receipt_data = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => $receipt_no]);

    //     // if (!empty($receipt_data)) {
    //     //     $first_receipt = $receipt_data[0];
    //     //     $date = new DateTime($first_receipt->receipt_date);
    //     //     $p_month = $date->format('m');
    //     //     $p_year = $date->format('y');
    //     //     $datenow = $p_month . $p_year;

    //     //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
    //     //     $rowLot = $sqlGetLot->row();
    //     //     $lot_no = $rowLot->kode;

    //     //     if ($lot_no === NULL) {
    //     //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
    //     //     } else {
    //     //         $urutan = (int) substr($lot_no, 0, 3);
    //     //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
    //     //     }
    //     // }

    //     foreach ($receipt_data as $po_receipt) {
    //         $receipt_id = $po_receipt->receipt_id;
    //         $qty_receipt = $po_receipt->qty_receipt;

    //         $date = new DateTime($po_receipt->receipt_date);
    //         $p_month = $date->format('m');
    //         $p_year = $date->format('y');

    //         //Cek Label
    //         $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
    //         if (!$po_receipt_label) {
    //             for ($i = 0; $i < $po_receipt->qty_label; $i++) {
    //                 //Read Label ID
    //                 $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
    //                 $rowID = $sqlGetID->row();
    //                 $label = $rowID->kode;
    //                 if ($label == NULL) {
    //                     $autoID = $receipt_id . sprintf("%04s", $label + 1);
    //                 } else {
    //                     $urutan = (int) substr($label, -4);
    //                     $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
    //                 }
    //                 if ($qty_receipt > $po_receipt->qty_mpq) {
    //                     $qty = $po_receipt->qty_mpq;
    //                 } else {
    //                     $qty = $qty_receipt;
    //                 }

    //                 //Simpan Label
    //                 $arrLabel = [
    //                     "receipt_id" => $po_receipt->receipt_id,
    //                     "label_no" => $autoID,
    //                     "qty" => $qty,
    //                     "lot_no" => $po_receipt->lot_no,
    //                     "p_month" => $p_month,
    //                     "p_year" => $p_year
    //                 ];
    //                 $send = $this->crud->create('purchase_order_labels', $arrLabel);
    //                 $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
    //             }
    //         }
    //     }

    //     $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, d.location, d.area, c.color, c.uom, c.item_family_id, c.id as item_rm_id');
    //     $this->db->from('purchase_order_labels a');
    //     $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
    //     $this->db->join('item_rm c', 'b.item_rm_id = c.id');
    //     $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
    //     $this->db->join('item_familys e', 'c.item_family_id = e.id');
    //     $this->db->where('a.deleted', 0);
    //     //$this->db->where('a.status', 0);
    //     $this->db->where('b.receipt_no', $receipt_no);
    //     $this->db->order_by('a.label_no', 'asc');
    //     $records = $this->db->get()->result_object();
    //     $html = '<html>
    //                 <head>
    //                     <title>' . $receipt_no . '</title>
    //                     <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //                 </head>
    //                 <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
    //                 .lotno {
    //                     padding: 0;
    //                 }
    //                 .lotno-wrap {
    //                     display: table-cell;
    //                     width: 100%;
    //                     height: 100%;
    //                     vertical-align: middle;
    //                     text-align: center;
    //                 }
    //                 .lotno-text {
    //                     writing-mode: vertical-rl;
    //                     transform: rotate(180deg);
    //                     font-size: 7px;
    //                     white-space: nowrap;
    //                 }
    //                 </style><body>';
    //     if ($records) {
    //         $html .= '<div style="width: 60mm;">';
    //         $no = 1;
    //         $printed_labels = [];

    //         foreach ($records as $record) {
    //             if (in_array($record->label_no, $printed_labels)) {
    //                 continue;
    //             }

    //             $printed_labels[] = $record->label_no;

    //             if ($no == 2) {
    //                 $no = 1;
    //             }
    //             if ($no == 1) {
    //                 $padding = "margin:2mm 3mm 3mm 2mm;";
    //             } else {
    //                 $padding = "margin:2mm 0mm 3mm 4mm;";
    //             }
    //             //Generate QRcode
    //             $this->createQrcode($record->label_no, "assets/image/qrcode/");

    //             // Jika item_family_id = P06, generate QR tambahan
    //             if ($record->item_family_id == 'P06') {
    //                 $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
    //                 $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
    //                 $this->createQrcode($record->lot_no, "assets/image/qrcode/");
    //                 // Styling QR Lot No agar di pojok kanan atas area Quantity
    //                 $qr_lot_no = '<div style="position:absolute; top:7px; left:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no . '.png') . '" width="22.5" style="display:block;"/></div>';
    //             } else {
    //                 $qr_item_rm = "";
    //                 $qr_lot_no = "";
    //             }
    //             $html .= '  <div style="width: 48mm; max-height:41mm; float:left; box-sizing: border-box;' . $padding . '">
    //                             <table id="customers" border="1" style="margin-bottom:20px;">
    //                                 <tr>
    //                                     <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
    //                                 </tr>
    //                                 <tr>
    //                                     <th style="text-align:left; height: 35px;">
    //                                             <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
    //                                             <br>
    //                                             <b style="font-size:10px;">' . $record->name . " - " . $record->color . '</b>
    //                                     </th>
    //                                     <th style="text-align:right; height: 35px; position:relative;">

    //                                     ';

    //             if ($record->item_family_id == 'P06') {
    //                 $html .= '<div style="position: absolute; left: 4px; top: 1px;">
    //                             <small style="font-size:15px; display: block;"><b>'.$record->p_month.'</b><small style="font-size:15px;display: block;"><b>' . $record->p_year . '</b></small>
    //                           </div>';
    //             } else {
    //                 $html .= '<small style="font-size:15px;"><b>'.$record->p_month.'</b></small><small style="font-size:15px;"><b>' . "-" . $record->p_year . '</b></small>';
    //             }

    //             $html .= '
    //                                             ' . $qr_item_rm . '
    //                                     </th>
    //                                     <th rowspan="4" class="lotno">
    //                                         <div class="lotno-wrap">
    //                                             <span class="lotno-text">Lot No: '. $record->lot_no .'</span>
    //                                         </div>
    //                                     </th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left;">
    //                                         <small>Quantity</small><br>
    //                                         <div style="display:flex; align-items:center; margin-top:2px;">
    //                                             <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
    //                                             <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
    //                                             </div>
    //                                     </th>
    //                                     <th style="text-align:right; position:relative;">
    //                                         <span>' . $qr_lot_no . '</span>
    //                                         <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
    //                                     </th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left">
    //                                         <div style="display: inline-block;">
    //                                             <small>Date :</small><br> 
    //                                             <b style="font-size:8px;">' . $record->receipt_date . '</b>
    //                                         </div>
    //                                         <div style="display: inline-block;">
    //                                             <small>Label No :</small><br>
    //                                             <b style="font-size:8px;">' . $record->label_no . '</b>
    //                                         </div>
    //                                         <div style="display: inline-block;">
    //                                             <small>QC Passed</small><br>
    //                                             <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
    //                                         </div>
    //                                     </th>
    //                                     <th style="text-align:center;">
    //                                         <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50" style="padding: 0px 4px;"/>
    //                                     </th>
    //                                 </tr>
    //                             </table>
    //                         </div>';
    //             $no++;
    //         }
    //         $html .= '</div><script>window.print()</script>';
    //     } else {
    //         $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
    //     }
    //     die($html);
    // }

    // from create
    public function print_label_po($receipt_no)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_no = base64_decode($receipt_no);
        $receipt_data = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => $receipt_no]);

        // if (!empty($receipt_data)) {
        //     $first_receipt = $receipt_data[0];
        //     $date = new DateTime($first_receipt->receipt_date);
        //     $p_month = $date->format('m');
        //     $p_year = $date->format('y');
        //     $datenow = $p_month . $p_year;

        //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
        //     $rowLot = $sqlGetLot->row();
        //     $lot_no = $rowLot->kode;

        //     if ($lot_no === NULL) {
        //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
        //     } else {
        //         $urutan = (int) substr($lot_no, 0, 3);
        //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
        //     }
        // }

        foreach ($receipt_data as $po_receipt) {
            $receipt_id = $po_receipt->receipt_id;
            $qty_receipt = $po_receipt->qty_receipt;

            $date = new DateTime($po_receipt->receipt_date);
            $p_month = $date->format('m');
            $p_year = $date->format('y');

            //Cek Label
            $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
            if (!$po_receipt_label) {
                for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                    //Read Label ID
                    $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                    $rowID = $sqlGetID->row();
                    $label = $rowID->kode;
                    if ($label == NULL) {
                        $autoID = $receipt_id . sprintf("%04s", $label + 1);
                    } else {
                        $urutan = (int) substr($label, -4);
                        $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                    }
                    if ($qty_receipt > $po_receipt->qty_mpq) {
                        $qty = $po_receipt->qty_mpq;
                    } else {
                        $qty = $qty_receipt;
                    }

                    //Simpan Label
                    $arrLabel = [
                        "receipt_id" => $po_receipt->receipt_id,
                        "label_no" => $autoID,
                        "qty" => $qty,
                        "lot_no" => $po_receipt->lot_no,
                        "p_month" => $p_month,
                        "p_year" => $p_year
                    ];
                    $send = $this->crud->create('purchase_order_labels', $arrLabel);
                    $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
                }
            }
        }


        $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, d.location, d.area, c.color, c.uom, c.item_family_id, c.id as item_rm_id, b.lot_no_internal');
        // $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, d.location, d.area, c.color, c.uom');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('b.receipt_no', $receipt_no);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $receipt_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 60mm;">';
            $no = 1;
            $printed_labels = [];

            foreach ($records as $record) {
                if (in_array($record->label_no, $printed_labels)) {
                    continue;
                }

                $printed_labels[] = $record->label_no;

                if ($no == 2) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                } else {
                    $padding = "padding:2mm 0mm 3mm 4mm;";
                }
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");

                // Jika item_family_id = P06, generate QR tambahan
                if ($record->item_family_id == 'P06') {
                    $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
                    $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
                    $this->createQrcode($record->lot_no_internal, "assets/image/qrcode/");
                    // Styling QR Lot No agar di pojok kanan atas area Quantity
                    $qr_lot_no = '<div style="position:absolute; top:4px; right:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no_internal . '.png') . '" width="22" style="display:block;"/></div>';
                } else {
                    $qr_item_rm = "";
                    $qr_lot_no = "";
                }
                $html .= '  <div style="width: 48mm; max-height:41mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="height:35px; position:relative;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
                                                <br>
                                                <b style="font-size:7px;">' . $record->name . " - " . $record->color . '</b>
                                            </div>
                                            
                                            <div style="float:right;">
                                                <small style="font-size:15px;"><b>' . $record->p_month . '</b></small>
                                                <small style="font-size:15px;"><b>' . " - " . $record->p_year . '</b></small>
                                                ' . $qr_item_rm . '
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left; position:relative;">
                                            <small>Quantity</small>
                                            <div style="display:flex; align-items:center; margin-top:2px;">
                                                <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
                                                <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
                                                <span>' . $qr_lot_no . '</span>
                                            </div>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Lot No. </small><b style="font-size:10px;">' . $record->lot_no_internal . '</b>
                                            <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small>Date :</small><br> 
                                                <b style="font-size:8px;">' . $record->receipt_date . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>Label No :</small><br>
                                                <b style="font-size:8px;">' . $record->label_no . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>QC PASSED</small><br>
                                                <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    // from create
    public function print_label_po_multiple($receipt_no)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_no = base64_decode($receipt_no);
        $receipt_data = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => $receipt_no]);

        // if (!empty($receipt_data)) {
        //     $first_receipt = $receipt_data[0];
        //     $date = new DateTime($first_receipt->receipt_date);
        //     $p_month = $date->format('m');
        //     $p_year = $date->format('y');
        //     $datenow = $p_month . $p_year;

        //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
        //     $rowLot = $sqlGetLot->row();
        //     $lot_no = $rowLot->kode;

        //     if ($lot_no === NULL) {
        //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
        //     } else {
        //         $urutan = (int) substr($lot_no, 0, 3);
        //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
        //     }
        // }

        foreach ($receipt_data as $po_receipt) {
            $receipt_id = $po_receipt->receipt_id;
            $qty_receipt = $po_receipt->qty_receipt;

            $date = new DateTime($po_receipt->receipt_date);
            $p_month = $date->format('m');
            $p_year = $date->format('y');

            //Cek Label
            $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
            if (!$po_receipt_label) {
                for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                    //Read Label ID
                    $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                    $rowID = $sqlGetID->row();
                    $label = $rowID->kode;
                    if ($label == NULL) {
                        $autoID = $receipt_id . sprintf("%04s", $label + 1);
                    } else {
                        $urutan = (int) substr($label, -4);
                        $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                    }
                    if ($qty_receipt > $po_receipt->qty_mpq) {
                        $qty = $po_receipt->qty_mpq;
                    } else {
                        $qty = $qty_receipt;
                    }

                    //Simpan Label
                    $arrLabel = [
                        "receipt_id" => $po_receipt->receipt_id,
                        "label_no" => $autoID,
                        "qty" => $qty,
                        "lot_no" => $po_receipt->lot_no,
                        "p_month" => $p_month,
                        "p_year" => $p_year
                    ];
                    $send = $this->crud->create('purchase_order_labels', $arrLabel);
                    $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
                }
            }
        }

        $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, d.location, d.area, c.color, c.uom, c.item_family_id, c.id as item_rm_id, b.lot_no_internal');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('b.receipt_no', $receipt_no);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $receipt_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 100%;">';
            $no = 1;
            $printed_labels = [];

            foreach ($records as $record) {
                if (in_array($record->label_no, $printed_labels)) {
                    continue;
                }

                $printed_labels[] = $record->label_no;

                if ($no == 2) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "margin:2mm 3mm 3mm 2mm;";
                } else {
                    $padding = "margin:2mm 0mm 3mm 4mm;";
                }
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");

                // Jika item_family_id = P06, generate QR tambahan
                if ($record->item_family_id == 'P06') {
                    $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
                    $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
                    $this->createQrcode($record->lot_no_internal, "assets/image/qrcode/");
                    // Styling QR Lot No agar di pojok kanan atas area Quantity
                    $qr_lot_no = '<div style="position:absolute; top:4px; right:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no_internal . '.png') . '" width="22" style="display:block;"/></div>';
                } else {
                    $qr_item_rm = "";
                    $qr_lot_no = "";
                }
                $html .= '  <div style="width: 48mm; max-height:41mm; float:left; box-sizing: border-box; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="height:35px; position:relative;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
                                                <br>
                                                <b style="font-size:7px;">' . $record->name . " - " . $record->color . '</b>
                                            </div>
                                            <div style="float:right;">
                                                <small style="font-size:15px;"><b>' . $record->p_month . '</b></small>
                                                <small style="font-size:15px;"><b> - ' . $record->p_year . '</b></small>
                                                ' . $qr_item_rm . '
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="text-align:left; position:relative;">
                                            <small>Quantity</small>
                                            <div style="display:flex; align-items:center; margin-top:2px;">
                                                <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
                                                <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
                                                <span>' . $qr_lot_no . '</span>
                                            </div>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Lot No. </small><b style="font-size:10px;">' . $record->lot_no_internal . '</b>
                                            <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small>Date :</small><br> 
                                                <b style="font-size:8px;">' . $record->receipt_date . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>Label No :</small><br>
                                                <b style="font-size:8px;">' . $record->label_no . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>QC PASSED</small><br>
                                                <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    // from datatables new label
    // public function print_labelv1($receipt_id)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $receipt_id = base64_decode($receipt_id);
    //     $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);

    //     $date = new DateTime($po_receipt->receipt_date);
    //     $p_month = $date->format('m');
    //     $p_year = $date->format('y');

    //     if (!empty($po_receipt)) {
    //         // $receipt_no = $po_receipt->receipt_no;

    //         // $this->db->select('a.lot_no');
    //         // $this->db->from('purchase_order_labels a');
    //         // $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
    //         // $this->db->where('b.receipt_no', $receipt_no);
    //         // $this->db->where('a.deleted', 0);
    //         // $this->db->limit(1);
    //         // $rowExist = $this->db->get()->row();

    //         // if ($rowExist) {
    //         //     $autoLot = $rowExist->lot_no;
    //         // } else {
    //         //     $datenow = $p_month . $p_year;

    //         //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
    //         //     $rowLot = $sqlGetLot->row();
    //         //     $lot_no = $rowLot->kode;

    //         //     if ($lot_no === NULL) {
    //         //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
    //         //     } else {
    //         //         $urutan = (int) substr($lot_no, 0, 3);
    //         //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
    //         //     }
    //         // }

    //         $autoLot = !empty($po_receipt->lot_no) ? $po_receipt->lot_no : '0';
    //     }

    //     $qty_receipt = $po_receipt->qty_receipt;
    //     //Cek Label
    //     $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
    //     if (!$po_receipt_label) {
    //         for ($i = 0; $i < $po_receipt->qty_label; $i++) {
    //             //Read Label ID
    //             $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
    //             $rowID = $sqlGetID->row();
    //             $label = $rowID->kode;
    //             if ($label == NULL) {
    //                 $autoID = $receipt_id . sprintf("%04s", $label + 1);
    //             } else {
    //                 $urutan = (int) substr($label, -4);
    //                 $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
    //             }
    //             if ($qty_receipt > $po_receipt->qty_mpq) {
    //                 $qty = $po_receipt->qty_mpq;
    //             } else {
    //                 $qty = $qty_receipt;
    //             }

    //             //Simpan Label
    //             $arrLabel = [
    //                 "receipt_id" => $po_receipt->receipt_id,
    //                 "label_no" => $autoID,
    //                 "qty" => $qty,
    //                 "lot_no" => $autoLot,
    //                 "p_month" => $p_month,
    //                 "p_year" => $p_year
    //             ];
    //             $send = $this->crud->create('purchase_order_labels', $arrLabel);
    //             $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
    //         }
    //     }
    //     $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, c.item_family_id, d.location, d.area, c.color, c.uom, c.id as item_rm_id');
    //     $this->db->from('purchase_order_labels a');
    //     $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
    //     $this->db->join('item_rm c', 'b.item_rm_id = c.id');
    //     $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
    //     $this->db->join('item_familys e', 'c.item_family_id = e.id');
    //     $this->db->where('a.deleted', 0);
    //     //$this->db->where('a.status', 0);
    //     $this->db->where('a.receipt_id', $receipt_id);
    //     $this->db->order_by('a.label_no', 'asc');
    //     $records = $this->db->get()->result_object();

    //     $html = '<html>
    //                 <head>
    //                     <title>' . $receipt_id . '</title>
    //                     <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //                 </head>
    //                 <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
    //                 .lotno {
    //                     padding: 0;
    //                 }
    //                 .lotno-wrap {
    //                     display: table-cell;
    //                     width: 100%;
    //                     height: 100%;
    //                     vertical-align: middle;
    //                     text-align: center;
    //                 }
    //                 .lotno-text {
    //                     writing-mode: vertical-rl;
    //                     transform: rotate(180deg);
    //                     font-size: 7px;
    //                     white-space: nowrap;
    //                 }
    //                 </style><body>';
    //     if ($records) {
    //         $html .= '<div style="width: 60mm;">';
    //         $no = 1;
    //         $printed_labels = [];

    //         foreach ($records as $record) {
    //             if (in_array($record->label_no, $printed_labels)) {
    //                 continue;
    //             }

    //             $printed_labels[] = $record->label_no;

    //             if ($no == 2) {
    //                 $no = 1;
    //             }
    //             if ($no == 1) {
    //                 $padding = "margin:2mm 3mm 3mm 2mm;";
    //             } else {
    //                 $padding = "margin:2mm 0mm 3mm 4mm;";
    //             }
    //             //Generate QRcode
    //             $this->createQrcode($record->label_no, "assets/image/qrcode/");

    //             // Jika item_family_id = P06, generate QR tambahan
    //             if ($record->item_family_id == 'P06') {
    //                 $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
    //                 $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
    //                 $this->createQrcode($record->lot_no, "assets/image/qrcode/");
    //                 // Styling QR Lot No agar di pojok kanan atas area Quantity
    //                 $qr_lot_no = '<div style="position:absolute; top:7px; left:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no . '.png') . '" width="22.5" style="display:block;"/></div>';
    //             } else {
    //                 $qr_item_rm = "";
    //                 $qr_lot_no = "";
    //             }
                
    //             $html .= '  <div style="width: 48mm; max-height:41mm; float:left; box-sizing: border-box; ' . $padding . '">
    //                             <table id="customers" border="1" style="margin-bottom:20px;">
    //                                 <tr>
    //                                     <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left; height: 35px;">
    //                                             <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
    //                                             <br>
    //                                             <b style="font-size:10px;">' . $record->name . " - " . $record->color . '</b>
    //                                     </th>
    //                                     <th style="text-align:right; height: 35px; position:relative;">
    //                                     ';

    //             if ($record->item_family_id == 'P06') {
    //                 $html .= '<div style="position: absolute; left: 4px; top: 1px;">
    //                             <small style="font-size:15px; display: block;"><b>'.$record->p_month.'</b><small style="font-size:15px;display: block;"><b>' . $record->p_year . '</b></small>
    //                           </div>';
    //             } else {
    //                 $html .= '<small style="font-size:15px;"><b>'.$record->p_month.'</b></small><small style="font-size:15px;"><b>' . "-" . $record->p_year . '</b></small>';
    //             }

    //             $html .= '
    //                                             ' . $qr_item_rm . '
    //                                     </th>
    //                                     <th rowspan="4" class="lotno">
    //                                         <div class="lotno-wrap">
    //                                             <span class="lotno-text">Lot No: '. $record->lot_no .'</span>
    //                                         </div>
    //                                     </th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left;">
    //                                         <small>Quantity</small><br>
    //                                         <div style="display:flex; align-items:center; margin-top:2px;">
    //                                             <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
    //                                             <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
    //                                             </div>
    //                                     </th>
    //                                     <th style="text-align:right; position:relative;">
    //                                         <span>' . $qr_lot_no . '</span>
    //                                         <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
    //                                     </th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left">
    //                                         <div style="display: inline-block;">
    //                                             <small>Date :</small><br> 
    //                                             <b style="font-size:8px;">' . $record->receipt_date . '</b>
    //                                         </div>
    //                                         <div style="display: inline-block;">
    //                                             <small>Label No :</small><br>
    //                                             <b style="font-size:8px;">' . $record->label_no . '</b>
    //                                         </div>
    //                                         <div style="display: inline-block;">
    //                                             <small>QC Passed</small><br>
    //                                             <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
    //                                         </div>
    //                                     </th>
    //                                     <th style="text-align:center;">
    //                                         <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50" style="padding: 0px 4px;"/>
    //                                     </th>
    //                                 </tr>
    //                             </table>
    //                         </div>';
    //             $no++;
    //         }
    //         $html .= '</div><script>window.print()</script>';
    //     } else {
    //         $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
    //     }
    //     die($html);
    // }

    // from datatables
    public function print_label($receipt_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_id = base64_decode($receipt_id);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);

        $date = new DateTime($po_receipt->receipt_date);
        $p_month = $date->format('m');
        $p_year = $date->format('y');

        if (!empty($po_receipt)) {
            // $receipt_no = $po_receipt->receipt_no;

            // $this->db->select('a.lot_no');
            // $this->db->from('purchase_order_labels a');
            // $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            // $this->db->where('b.receipt_no', $receipt_no);
            // $this->db->where('a.deleted', 0);
            // $this->db->limit(1);
            // $rowExist = $this->db->get()->row();

            // if ($rowExist) {
            //     $autoLot = $rowExist->lot_no;
            // } else {
            //     $datenow = $p_month . $p_year;

            //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
            //     $rowLot = $sqlGetLot->row();
            //     $lot_no = $rowLot->kode;

            //     if ($lot_no === NULL) {
            //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
            //     } else {
            //         $urutan = (int) substr($lot_no, 0, 3);
            //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
            //     }
            // }

            $autoLot = !empty($po_receipt->lot_no) ? $po_receipt->lot_no : '0';
        }

        $qty_receipt = $po_receipt->qty_receipt;
        //Cek Label
        $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
        if (!$po_receipt_label) {
            for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $receipt_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                }
                if ($qty_receipt > $po_receipt->qty_mpq) {
                    $qty = $po_receipt->qty_mpq;
                } else {
                    $qty = $qty_receipt;
                }

                //Simpan Label
                $arrLabel = [
                    "receipt_id" => $po_receipt->receipt_id,
                    "label_no" => $autoID,
                    "qty" => $qty,
                    "lot_no" => $autoLot,
                    "p_month" => $p_month,
                    "p_year" => $p_year
                ];
                $send = $this->crud->create('purchase_order_labels', $arrLabel);
                $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
            }
        }
        $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, c.item_family_id, d.location, d.area, c.color, c.uom, c.id as item_rm_id, b.lot_no_internal');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('a.receipt_id', $receipt_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $receipt_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 60mm;">';
            $no = 1;
            $printed_labels = [];

            foreach ($records as $record) {
                if (in_array($record->label_no, $printed_labels)) {
                    continue;
                }

                $printed_labels[] = $record->label_no;

                if ($no == 2) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                } else {
                    $padding = "padding:2mm 0mm 3mm 4mm;";
                }
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");

                // Jika item_family_id = P06, generate QR tambahan
                if ($record->item_family_id == 'P06') {
                    $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
                    $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
                    $this->createQrcode($record->lot_no_internal, "assets/image/qrcode/");
                    // Styling QR Lot No agar di pojok kanan atas area Quantity
                    $qr_lot_no = '<div style="position:absolute; top:4px; right:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no_internal . '.png') . '" width="22" style="display:block;"/></div>';
                } else {
                    $qr_item_rm = "";
                    $qr_lot_no = "";
                }
                $html .= '  <div style="width: 48mm; max-height:41mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="height:35px; position:relative;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
                                                <br>
                                                <b style="font-size:7px;">' . $record->name . " - " . $record->color . '</b>
                                            </div>
                                            <div style="float:right;">
                                                <small style="font-size:15px;"><b>' . $record->p_month . '</b></small>
                                                <small style="font-size:15px;"><b> - ' . $record->p_year . '</b></small>
                                                ' . $qr_item_rm . '
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left; position:relative;">
                                            <small>Quantity</small>
                                            <div style="display:flex; align-items:center; margin-top:2px;">
                                                <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
                                                <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
                                                <span>' . $qr_lot_no . '</span>
                                            </div>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Lot No. </small><b style="font-size:10px;">' . $record->lot_no_internal . '</b>
                                            <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small>Date :</small><br> 
                                                <b style="font-size:8px;">' . $record->receipt_date . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>Label No :</small><br>
                                                <b style="font-size:8px;">' . $record->label_no . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>QC PASSED</small><br>
                                                <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function print_receiving($receipt_no)
    {
        $purchase_order_receipt_total = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);

        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => $po_receipt->po_no], "", "revision", "desc");

        $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $purchase_orders->request_no]);
        
        $plant = ($purchaseRequests->division==="DIV01")?'RUBBER PART':'EXTRUDER';

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        
        // Generate QR code for session name
        $this->createQrcode($this->session->name, "assets/image/qrcode/");
        
        // Config Page
        $rows = 12;
        $page = ceil(count($purchase_order_receipt_total) / $rows);
        
        // Generate QR code for receipt_no
        $this->createQrcode($po_receipt->receipt_no, "assets/image/qrcode/");
        
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_id, c.name as item_name, c.uom, d.name as item_familys_name, e.mpq, b.currency, g.location');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
        $this->db->join('warehouse_location_items g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.receipt_no', base64_decode($receipt_no));
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.supplier_id');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();
        
        if ($records) {
            $html = '<html>
                        <head>
                            <title>' . $po_receipt->receipt_no . '</title>
                            <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                        </head>
                        <style>
                            body {
                                font-family: Arial, Helvetica, sans-serif;
                            }
                            #customers {
                                border-collapse: collapse;width: 100%;
                                font-size: 12px;
                            }
                            #customers td, #customers th {
                                border: 1px solid black;padding: 2px;
                            }
                            #customers th {
                                padding-top: 2px;
                                padding-bottom: 2px;
                                text-align: center;color: black;
                            }
                            @media screen {
                                .print {
                                    display: none !important;
                                }
                            }

                            @media print {
                                .noprint {
                                    display: none !important;
                                }
                            }
                        </style>
                        <body>
                            <div style="margin:20%;" class="noprint">
                                <center>
                                    <h1>Press CTRL + P for Print</h1>
                                    <p>Display pages for ' . $rows . ' rows</p>
                                    <p>Paper Size A5, Layout Landscape</p>
                                    <p>Margin Default, Scale 98</p>
                                </center>
                            </div>
                            <div class="print">';
            $no = 1;
            $hal = 1;
            $subtotal = 0;
            for ($i = 0; $i < $page; $i++) {
                $this->db->select('a.*, SUM(a.qty_receipt) as qty_receipt, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_id, c.name as item_name, c.uom, d.name as item_categories_name, e.mpq, b.currency, g.location');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                //$this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('item_categories d', 'c.item_category_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                $this->db->join('warehouse_location_items g', 'a.item_rm_id = g.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                // $this->db->where('a.status', 0);
                $this->db->where('a.receipt_no', base64_decode($receipt_no));
                $this->db->group_by('a.po_no');
                $this->db->group_by('a.supplier_id');
                $this->db->group_by('a.item_rm_id');
                $records = $this->db->get()->result_array();

                $html .= '  <table style="width:100%;">
                                    <tr>
                                        <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                        <td width="250" style="padding:10px;">
                                            <b style="font-size:14px;">' . $config->name . '</b><br>
                                            <span style="font-size:10px;">' . $config->address . '</span><br>
                                        </td>
                                        <th width="100" style="text-align:right;">
                                            <table style="width:100%; font-size:10px;">
                                                <tr>
                                                    <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $po_receipt->receipt_no . '.png') . '" width="60"/></td>
                                                    <td width="60">Doc No</td>
                                                    <td width="5">:</td>
                                                    <td width="100">' . $config_iso->doc_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Form</td>
                                                    <td>:</td>
                                                    <td>' . $config_iso->form_receiving_note . '</td>
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
                                        </th>
                                    </tr>
                                </table>
                                <div style="border: 1px solid black; width:100%;">
                                    <div style="padding:10px;">
                                        <center>
                                            <h3><u>GOOD RECEIVING NOTE</u></h3>
                                        </center>
                                        <table style="width:40%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="100">Receipt No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Receipt Date</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_date . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:45%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="50">Supplier</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Doc. No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['bc_document'] . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:15%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="40">Plant</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$plant . '</b></td>
                                            </tr>
                                        </table>
    
                                        <table id="customers">
                                            <tr>
                                                <th>No</th>
                                                <th>PO No</th>
                                                <th>Part No</th>
                                                <th>Part Name</th>
                                                <th>Category</th>
                                                <th>Location</th>
                                                <th>MPQ</th>
                                                <th>Quantity</th>
                                                <th>Uom</th>
                                            </tr>';
                $no = 1;
                foreach ($records as $record) {
                    $html .= '  <tr>
                    <td style="text-align:center">' . $no . '</td>
                    <td>' . $record['po_no'] . '</td>
                    <td>' . $record['item_rm_id'] . '</td>
                    <td>' . $record['item_name'] . '</td>
                    <td>' . $record['item_categories_name'] . '</td>
                    <td>' . $record['location'] . '</td>
                    <td style="text-align:right">' . number_format($record['mpq'], 2) . '</td>
                    <td style="text-align:right">' . number_format($record['qty_receipt'], 2) . '</td>
                    <td>' . $record['uom'] . '</td>
                </tr>';
                    $no++;
                }
                $html .= '  </table>
                            <table id="customers" style="margin-top:20px; width:15%; margin-left: 85%;">
                                <tr>
                                    <th width="100" style="text-align:center;">Receipt By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;">
                                        <img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="60"/>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                <script>window.print()</script>';
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
                $hal++;
            }
            $html .= "</div></div><script>window.print()</script>";
            die($html);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_receipts_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_receipt = $this->input->get('filter_receipt');
        $filter_product_no = $this->input->get('filter_product_no');
        $filter_doc_no = $this->input->get('filter_doc_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_id, c.number_internal as item_rm_number_internal, c.name as item_name, d.name as item_family_name, b.currency, c.uom');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.receipt_date >=', $filter_from);
            $this->db->where('a.receipt_date <=', $filter_to);
        }
        if ($filter_supplier != "") {
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if ($filter_receipt != "") {
            $this->db->where('a.receipt_no', $filter_receipt);
        }
        if ($filter_product_no != "") {
            $this->db->where('c.name', $filter_product_no);
        }
        if ($filter_doc_no != "") {
            $this->db->where('a.bc_document', $filter_doc_no);
        }
        if ($filter_po_no != "") {
            $this->db->where('a.po_no', $filter_po_no);
        }
        $this->db->order_by('a.receipt_date', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PURCHASE ORDER RECEIPT</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">PO No</th>
                <th colspan="2" style="text-align:center;">Beacukai</th>
                <th rowspan="2">Receipt No</th>
                <th colspan="2" style="text-align:center;">Supplier</th>
                <th rowspan="2">Part No External</th>
                <th rowspan="2">Part No Internal</th>
                <th rowspan="2">Part Name</th>
                <th rowspan="2">Qty</th>
                <th rowspan="2">UoM</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Label</th>
            </tr>
            <tr>
                <th>Document</th>
                <th>Date</th>
                <th>ID</th>
                <th>Name</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['bc_document'] . '</td>
                        <td>' . $data['bc_date'] . '</td>
                        <td>' . $data['receipt_no'] . '</td>
                        <td>' . $data['supplier_id'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number_internal'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty_receipt'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['qty_label']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    // from datatables new label
    // public function print_label_multiplev1($receipt_id)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $receipt_id = base64_decode($receipt_id);
    //     $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);

    //     $date = new DateTime($po_receipt->receipt_date);
    //     $p_month = $date->format('m');
    //     $p_year = $date->format('y');

    //     if (!empty($po_receipt)) {
    //         // $receipt_no = $po_receipt->receipt_no;

    //         // $this->db->select('a.lot_no');
    //         // $this->db->from('purchase_order_labels a');
    //         // $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
    //         // $this->db->where('b.receipt_no', $receipt_no);
    //         // $this->db->where('a.deleted', 0);
    //         // $this->db->limit(1);
    //         // $rowExist = $this->db->get()->row();

    //         // if ($rowExist) {
    //         //     $autoLot = $rowExist->lot_no;
    //         // } else {
    //         //     $datenow = $p_month . $p_year;

    //         //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
    //         //     $rowLot = $sqlGetLot->row();
    //         //     $lot_no = $rowLot->kode;

    //         //     if ($lot_no === NULL) {
    //         //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
    //         //     } else {
    //         //         $urutan = (int) substr($lot_no, 0, 3);
    //         //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
    //         //     }
    //         // }

    //         $autoLot = !empty($po_receipt->lot_no) ? $po_receipt->lot_no : '0';
    //     }

    //     $qty_receipt = $po_receipt->qty_receipt;
    //     //Cek Label
    //     $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
    //     if (!$po_receipt_label) {
    //         for ($i = 0; $i < $po_receipt->qty_label; $i++) {
    //             //Read Label ID
    //             $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
    //             $rowID = $sqlGetID->row();
    //             $label = $rowID->kode;
    //             if ($label == NULL) {
    //                 $autoID = $receipt_id . sprintf("%04s", $label + 1);
    //             } else {
    //                 $urutan = (int) substr($label, -4);
    //                 $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
    //             }
    //             if ($qty_receipt > $po_receipt->qty_mpq) {
    //                 $qty = $po_receipt->qty_mpq;
    //             } else {
    //                 $qty = $qty_receipt;
    //             }

    //             //Simpan Label
    //             $arrLabel = [
    //                 "receipt_id" => $po_receipt->receipt_id,
    //                 "label_no" => $autoID,
    //                 "qty" => $qty,
    //                 "lot_no" => $autoLot,
    //                 "p_month" => $p_month,
    //                 "p_year" => $p_year
    //             ];
    //             $send = $this->crud->create('purchase_order_labels', $arrLabel);
    //             $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
    //         }
    //     }
    //     $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, c.item_family_id, d.location, d.area, c.color, c.uom, c.id as item_rm_id');
    //     $this->db->from('purchase_order_labels a');
    //     $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
    //     $this->db->join('item_rm c', 'b.item_rm_id = c.id');
    //     $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
    //     $this->db->join('item_familys e', 'c.item_family_id = e.id');
    //     $this->db->where('a.deleted', 0);
    //     //$this->db->where('a.status', 0);
    //     $this->db->where('a.receipt_id', $receipt_id);
    //     $this->db->order_by('a.label_no', 'asc');
    //     $records = $this->db->get()->result_object();
    //     $html = '<html>
    //                 <head>
    //                     <title>' . $receipt_id . '</title>
    //                     <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //                 </head>
    //                 <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
    //                 .lotno {
    //                     padding: 0;
    //                 }
    //                 .lotno-wrap {
    //                     display: table-cell;
    //                     width: 100%;
    //                     height: 100%;
    //                     vertical-align: middle;
    //                     text-align: center;
    //                 }
    //                 .lotno-text {
    //                     writing-mode: vertical-rl;
    //                     transform: rotate(180deg);
    //                     font-size: 7px;
    //                     white-space: nowrap;
    //                 }
    //                 </style><body>';
    //     if ($records) {
    //         $html .= '<div style="width: 100%;">';
    //         $no = 1;
    //         $printed_labels = [];

    //         foreach ($records as $record) {
    //             if (in_array($record->label_no, $printed_labels)) {
    //                 continue;
    //             }

    //             $printed_labels[] = $record->label_no;

    //             if ($no == 2) {
    //                 $no = 1;
    //             }
    //             if ($no == 1) {
    //                 $padding = "margin:2mm 3mm 3mm 2mm;";
    //             } else {
    //                 $padding = "margin:2mm 0mm 3mm 4mm;";
    //             }
    //             //Generate QRcode
    //             $this->createQrcode($record->label_no, "assets/image/qrcode/");

    //             // Jika item_family_id = P06, generate QR tambahan
    //             if ($record->item_family_id == 'P06') {
    //                 $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
    //                 $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
    //                 $this->createQrcode($record->lot_no, "assets/image/qrcode/");
    //                 // Styling QR Lot No agar di pojok kanan atas area Quantity
    //                 $qr_lot_no = '<div style="position:absolute; top:7px; left:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no . '.png') . '" width="22.5" style="display:block;"/></div>';
    //             } else {
    //                 $qr_item_rm = "";
    //                 $qr_lot_no = "";
    //             }
    //             $html .= '  <div style="width: 48mm; max-height:41mm; float:left; box-sizing: border-box; ' . $padding . '">
    //                             <table id="customers" border="1" style="margin-bottom:20px;">
    //                                 <tr>
    //                                     <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '<b></th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left; height: 35px;">
    //                                             <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
    //                                             <br>
    //                                             <b style="font-size:10px;">' . $record->name . " - " . $record->color . '</b>
    //                                     </th>
    //                                     <th style="text-align:right; height: 35px; position:relative;">

    //                                     ';

    //             if ($record->item_family_id == 'P06') {
    //                 $html .= '<div style="position: absolute; left: 4px; top: 1px;">
    //                             <small style="font-size:15px; display: block;"><b>'.$record->p_month.'</b><small style="font-size:15px;display: block;"><b>' . $record->p_year . '</b></small>
    //                           </div>';
    //             } else {
    //                 $html .= '<small style="font-size:15px;"><b>'.$record->p_month.'</b></small><small style="font-size:15px;"><b>' . "-" . $record->p_year . '</b></small>';
    //             }

    //             $html .= '
    //                                             ' . $qr_item_rm . '
    //                                     </th>
    //                                     <th rowspan="4" class="lotno">
    //                                         <div class="lotno-wrap">
    //                                             <span class="lotno-text">Lot No: '. $record->lot_no .'</span>
    //                                         </div>
    //                                     </th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left;">
    //                                         <small>Quantity</small><br>
    //                                         <div style="display:flex; align-items:center; margin-top:2px;">
    //                                             <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
    //                                             <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
    //                                             </div>
    //                                     </th>
    //                                     <th style="text-align:right; position:relative;">
    //                                         <span>' . $qr_lot_no . '</span>
    //                                         <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
    //                                     </th>
    //                                 </tr>

    //                                 <tr>
    //                                     <th style="text-align:left">
    //                                         <div style="display: inline-block;">
    //                                             <small>Date :</small><br> 
    //                                             <b style="font-size:8px;">' . $record->receipt_date . '</b>
    //                                         </div>
    //                                         <div style="display: inline-block;">
    //                                             <small>Label No :</small><br>
    //                                             <b style="font-size:8px;">' . $record->label_no . '</b>
    //                                         </div>
    //                                         <div style="display: inline-block;">
    //                                             <small>QC Passed</small><br>
    //                                             <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
    //                                         </div>
    //                                     </th>
    //                                     <th style="text-align:center;">
    //                                         <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50" style="padding: 0px 4px;"/>
    //                                     </th>
    //                                 </tr>
    //                             </table>
    //                         </div>';
    //             $no++;
    //         }
    //         $html .= '</div><script>window.print()</script>';
    //     } else {
    //         $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
    //     }
    //     die($html);
    // }

    // from datatables
    public function print_label_multiple($receipt_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_id = base64_decode($receipt_id);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);

        $date = new DateTime($po_receipt->receipt_date);
        $p_month = $date->format('m');
        $p_year = $date->format('y');

        if (!empty($po_receipt)) {
            // $receipt_no = $po_receipt->receipt_no;

            // $this->db->select('a.lot_no');
            // $this->db->from('purchase_order_labels a');
            // $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            // $this->db->where('b.receipt_no', $receipt_no);
            // $this->db->where('a.deleted', 0);
            // $this->db->limit(1);
            // $rowExist = $this->db->get()->row();

            // if ($rowExist) {
            //     $autoLot = $rowExist->lot_no;
            // } else {
            //     $datenow = $p_month . $p_year;

            //     $sqlGetLot = $this->db->query("SELECT max(lot_no) as kode FROM purchase_order_labels WHERE lot_no LIKE '%$datenow%'");
            //     $rowLot = $sqlGetLot->row();
            //     $lot_no = $rowLot->kode;

            //     if ($lot_no === NULL) {
            //         $autoLot = sprintf("%03s", 1) . $p_month . $p_year;
            //     } else {
            //         $urutan = (int) substr($lot_no, 0, 3);
            //         $autoLot = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
            //     }
            // }

            $autoLot = !empty($po_receipt->lot_no) ? $po_receipt->lot_no : '0';
        }

        $qty_receipt = $po_receipt->qty_receipt;
        //Cek Label
        $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
        if (!$po_receipt_label) {
            for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $receipt_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                }
                if ($qty_receipt > $po_receipt->qty_mpq) {
                    $qty = $po_receipt->qty_mpq;
                } else {
                    $qty = $qty_receipt;
                }

                //Simpan Label
                $arrLabel = [
                    "receipt_id" => $po_receipt->receipt_id,
                    "label_no" => $autoID,
                    "qty" => $qty,
                    "lot_no" => $autoLot,
                    "p_month" => $p_month,
                    "p_year" => $p_year
                ];
                $send = $this->crud->create('purchase_order_labels', $arrLabel);
                $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
            }
        }
        $this->db->select('a.*, b.receipt_date, c.number, c.number_internal, c.name, c.item_family_id, d.location, d.area, c.color, c.uom, c.id as item_rm_id, b.lot_no_internal');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('a.receipt_id', $receipt_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $receipt_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 100%;">';
            $no = 1;
            $printed_labels = [];

            foreach ($records as $record) {
                if (in_array($record->label_no, $printed_labels)) {
                    continue;
                }

                $printed_labels[] = $record->label_no;

                if ($no == 2) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "padding:2mm 3mm 3mm 2mm;";
                } else {
                    $padding = "padding:2mm 0mm 3mm 4mm;";
                }
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");

                // Jika item_family_id = P06, generate QR tambahan
                if ($record->item_family_id == 'P06') {
                    $this->createQrcode($record->item_rm_id, "assets/image/qrcode/");
                    $qr_item_rm = '<img src="' . base_url('assets/image/qrcode/' . $record->item_rm_id . '.png') . '" width="30"/>';
                    $this->createQrcode($record->lot_no_internal, "assets/image/qrcode/");
                    // Styling QR Lot No agar di pojok kanan atas area Quantity
                    $qr_lot_no = '<div style="position:absolute; top:4px; right:4px;"><img src="' . base_url('assets/image/qrcode/' . $record->lot_no_internal . '.png') . '" width="22" style="display:block;"/></div>';
                } else {
                    $qr_item_rm = "";
                    $qr_lot_no = "";
                }
                $html .= '  <div style="width: 48mm; max-height:41mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="3" style="font-size:7px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="height:35px; position:relative;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number_internal . '</b></small>
                                                <br>
                                                <b style="font-size:7px;">' . $record->name . " - " . $record->color . '</b>
                                            </div>
                                            <div style="float:right;">
                                                <small style="font-size:15px;"><b>' . $record->p_month . '</b></small>
                                                <small style="font-size:15px;"><b> - ' . $record->p_year . '</b></small>
                                                ' . $qr_item_rm . '
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left; position:relative;">
                                            <small>Quantity</small>
                                            <div style="display:flex; align-items:center; margin-top:2px;">
                                                <span style="font-size:20px; font-weight:bold; margin-right:8px;">' . number_format($record->qty, 2) . '</span>
                                                <span style="font-size:15px; font-weight:bold; margin-right:8px;">' . $record->uom . '</span>
                                                <span>' . $qr_lot_no . '</span>
                                            </div>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Lot No. </small><b style="font-size:10px;">' . $record->lot_no_internal . '</b>
                                            <small>Location</small><br><b style="font-size:10px;">' . $record->location . '</b><br>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small>Date :</small><br> 
                                                <b style="font-size:8px;">' . $record->receipt_date . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>Label No :</small><br>
                                                <b style="font-size:8px;">' . $record->label_no . '</b>
                                            </div>
                                            <div style="display: inline-block;">
                                                <small>QC PASSED</small><br>
                                                <b style="font-size:8px;">by:' . $this->session->name . '</b></small>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }
}
