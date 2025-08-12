<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_orders extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[100]'); //item_number
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_orders');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('purchase_orders', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPono()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = $this->input->get('supplier_id');

        $this->db->select('a.po_no, a.po_date, a.po_name, b.number as supplier_number, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.approved_to', '');
        if ($supplier_id != "") {
            $this->db->where('a.supplier_id', $supplier_id);
        }
        // $this->db->where('a.approved', '');
        // $this->db->like('a.supplier_id', $supplier_id);
        // $this->db->like('a.po_no', $post);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');

        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readPonoOnAddPOR()
    {

        $supplier_id = $this->input->get('supplier_id');
        $records = $this->crud->query("SELECT po.po_no, po.po_date FROM (
            SELECT po_no as po_no, po_date as po_date, created_date as created_date FROM purchase_orders WHERE supplier_id = '$supplier_id' and status = 0 and deleted = 0 and approved_to = '' GROUP BY po_no
            UNION
            SELECT po_no as po_no, po_date as po_date, created_date as created_date FROM os_po WHERE supplier_id = '$supplier_id' and status = 0 and deleted = 0 GROUP BY po_no 
            ) as po ORDER BY po.created_date desc");
        echo json_encode($records);
    }

    public function readPonos()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select('a.po_no, a.po_date, a.po_name, b.number as supplier_number, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        // $this->db->like('a.po_no', $post);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');

        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->db->select('b.id as item_rm_id, b.number as item_number, b.name as item_name');
        $this->db->from('purchase_orders a');
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

    public function readTotalPo()
    {
        $item_id = $this->input->post('item_rm_id');
        $this->db->select('item_rm_id, SUM(qty) as qty');
        $this->db->from('purchase_orders');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where('item_rm_id', $item_id);
        $this->db->group_by('item_rm_id');
        $records = $this->db->get()->row();

        echo json_encode($records);
    }

    public function completePo()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $update = $this->db->update('purchase_orders', ["status" => 2], ["id" => $id]); // , "qty" => 0
        echo $update;
    }

    public function uncompletePo()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $update = $this->db->update('purchase_orders', ["status" => 2], ["id" => $id]); // , "qty" => 0
        echo $update;
    }

    public function checkStatus()
    {
        $po_no = $this->input->post('po_no');
        $this->db->select('status');
        $this->db->from('purchase_orders');
        $this->db->where('po_no', $po_no);
        // $this->db->where('status', 1);
        $record = $this->db->get()->row_array();

        echo json_encode($record);
    }

    public function checkTotalSub()
    {
        $po_no = $this->input->post('po_no');
        $this->db->select('total_sub');
        $this->db->from('purchase_orders');
        $this->db->where('po_no', $po_no);
        $record = $this->db->get()->row_array();

        echo json_encode($record);
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

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_suppliers = $this->input->get('filter_suppliers');
            $filter_status = $this->input->get('filter_status');
            $filter_product_no = $this->input->get('filter_product_no');
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $id = $_POST['id'];
            if ($id === "0") {
                //Select Query
                $this->db->select('a.po_no, a.request_no, a.total_dp,
                    a.po_date,
                    d.name as supplier_name,
                    b.uom,
                    a.month_1,
                    a.month_2,
                    a.month_3,
                    a.month_4,
                    a.discount,
                    d.currency, 
                    SUM(a.qty) as qty, 
                    SUM(a.price) as price, 
                    SUM(a.total) as total_price,
                    a.status,
                    COUNT(a.status) as total_status,
                    f.max_status as status_pi,
                    a.total_sub,
                    a.approved_by, 
                    a.approved_date, 
                    h.total_status_complete,
                    g.total_status_close,
                    CASE WHEN COUNT(CASE WHEN a.approved_to <> "" THEN 1 END) > 0 THEN "Checking" ELSE "" END AS approved_to');
                $this->db->from('purchase_orders a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                $this->db->join('(SELECT po_no, MIN(status) AS max_status FROM purchase_order_receipts GROUP BY po_no) f', 'a.po_no = f.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_complete FROM purchase_orders WHERE status = 2 GROUP BY po_no) h', 'a.po_no = h.po_no', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_suppliers != "") {
                    $this->db->where('d.id', $filter_suppliers);
                }
                if ($filter_status != "") {
                    $this->db->where('a.status', $filter_status);
                }
                if ($filter_product_no != "") {
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                $this->db->group_by('a.po_no');


                $this->db->order_by('a.created_date', 'DESC');
                $this->db->order_by('a.po_no', 'DESC');
                $this->db->order_by('a.po_date', 'DESC');
                $this->db->order_by('a.status', 'ASC');

                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                //Mapping Data
                foreach ($records as $record) {
                    if ($record['status'] == 2) {
                        $status = "2";
                    } elseif ($record['total_status_complete'] >= 1) {
                        $status = "2";
                    } elseif ($record['total_status'] == $record['total_status_close']) {
                        $status = "1";
                    } else {
                        $status = "0";
                    }

                    if ($record['approved_to'] == "" || $record['approved_to'] == null) {
                        $approved_to = "";
                    } else {
                        $approved_to = "Checking";
                    }

                    // if ($record['status_pi'] == 1) {
                    //     $status_pi = "1";
                    // } else {
                    //     $status_pi = "0";
                    // }



                    $arr[] = array(
                        "id" => $record['po_no'],
                        "po_no" => $record['po_no'],
                        "request_no" => $record['request_no'],
                        "po_date" => $record['po_date'],
                        "uom" => $record['uom'],
                        "currency" => $record['currency'],
                        "supplier_name" => $record['supplier_name'],
                        "status" => $status,
                        "status_pi" => $record['status_pi'],
                        "status1" => $record['total_status'],
                        "status2" => $record['total_status_close'],
                        "total_dp" => $record['total_dp'],
                        "total_sub" => $record['total_sub'],
                        "total_grand" => ($record['total_price'] - $record['total_dp']),
                        "state" => "closed",
                        "approved_to" => $record['approved_to'], //$approved_to,
                        "approved_by" => $record['approved_by'],
                        "approved_date" => $record['approved_date'],
                        "total_sub" => $record['total_sub'],
                        "datatable" => 1
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    b.number as item_number,
                    b.name as item_name,
                    b.uom,
                    c.name as item_family_name, 
                    d.name as supplier_name, 
                    d.currency, 
                    e.mpq, 
                    e.moq,
                    f.max_status as status_pi,
                    a.price,
                    a.status, 
                    (a.qty * a.price) as total_price,
                    (CASE WHEN a.approved = (SELECT (CASE WHEN i.user_approval_1 IS NOT NULL AND i.user_approval_1 != "" THEN 1 ELSE 0 END +
                    CASE WHEN i.user_approval_2 IS NOT NULL AND i.user_approval_2 != "" THEN 1 ELSE 0 END +
                    CASE WHEN i.user_approval_3 IS NOT NULL AND i.user_approval_3 != "" THEN 1 ELSE 0 END +
                    CASE WHEN i.user_approval_4 IS NOT NULL AND i.user_approval_4 != "" THEN 1 ELSE 0 END +
                    CASE WHEN i.user_approval_5 IS NOT NULL AND i.user_approval_5 != "" THEN 1 ELSE 0 END) as status_approval FROM approvals i WHERE table_name = "purchase_orders") THEN 1 ELSE 0 END) as status_approval');
                $this->db->from('purchase_orders a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id= c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                $this->db->join('(SELECT po_no, item_rm_id, MAX(status) AS max_status FROM purchase_order_receipts GROUP BY po_no, item_rm_id) f', 'a.po_no = f.po_no and a.item_rm_id = f.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                if ($id != "") {
                    $this->db->where('a.po_no', $id);
                }
                if ($filter_suppliers != "") {
                    $this->db->where('d.id', $filter_suppliers);
                }
                if ($filter_status != "") {
                    $this->db->where('a.status', $filter_status);
                }
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.po_no', 'DESC');
                $records = $this->db->get()->result_array();

                echo json_encode($records);
            }
        }
    }

    public function datatable_updates()
    {
        $po_no = base64_decode($this->input->get('po_no'));
        $this->db->select('a.*,  
            b.number as item_number, 
            b.name as item_name,
            b.uom,
            d.id as supplier_id, 
            d.number as supplier_number, 
            d.name as supplier_name,
            c.name as category_name,
            e.mpq, 
            e.moq,
            d.vat_status,
            ((a.qty * a.price)-(a.qty * a.price * (a.discount/100))) as amount,
            d.currency');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.po_no', $po_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        $obj = array();
        foreach ($records as $record) {
            $total_sub += $record['amount'];
            array_push($obj, $record);
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 2);
        die(json_encode($arr));

        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $items = $this->crud->read('item_rm', [], ['id' => $post['item_rm_id']]);
                $categorys = $this->crud->read('item_categories', [], ['id' => $items->item_category_id]);
                $suppliers = $this->crud->read('suppliers', [], ["id" => $post['supplier_id']]);
                $supplier_items = $this->crud->read('supplier_items', [], ["item_rm_id" => $items->id, "supplier_id" => $post['supplier_id']]);
                $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $post['request_no']]);
                $config = $this->crud->read("config");

                // Ambil division dan kategori
                $divisions = $purchaseRequests->division;
                $datenow = $categorys->number . date("y");
                $datenow2 = $categorys->number . date("ym");
                $table_approval = ($purchaseRequests->division==="DIV01")?'purchase_orders':'purchase_orders_2';
                // Deteksi pola request_no apakah memiliki tambahan seperti "-A01"
                $request_no = $post['request_no'];
                $has_additional_code = preg_match('/-A\d+$/', $request_no);

                if ($has_additional_code) {
                    // Jika request_no memiliki tambahan, langsung konversi formatnya
                    $po_no = str_replace("PR", "PO", $request_no);
                } else {
                    // Jika tidak ada tambahan, buat po_no baru dengan urutan otomatis
                    $sqlGetID = $this->db->query("SELECT max(po_no) as kode FROM purchase_orders WHERE po_no LIKE 'PO$datenow%' AND po_no NOT LIKE '%-A%'");
                    $rowID = $sqlGetID->row();
                    $kode = $rowID->kode;

                    if ($kode == NULL) {
                        $autoID = "0001";
                        $po_no = "PO" . $datenow2 . "-" . $autoID;
                    } else {
                        $purchaseOrder = $this->crud->read('purchase_orders', [], ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id']]);
                        if ($purchaseOrder) {
                            $po_no = $purchaseOrder->po_no;
                        } else {
                            $urutan = (int)substr($kode, -4);
                            $urutan++;
                            $autoID = sprintf("%04s", $urutan);
                            $po_no = "PO" . $datenow2 . "-" . $autoID;
                        }
                    }
                }

                // Tentukan pajak
                $taxes = $suppliers->vat_status == "VAT" ? $config->tax : 0;

                // Persiapkan data
                $data = array(
                    "supplier_id" => $post['supplier_id'],
                    "item_rm_id" => $items->id,
                    "request_no" => $post['request_no'],
                    "request_date" => $post['request_date'],
                    "request_name" => $post['request_name'],
                    "po_date" => $post['po_date'],
                    "po_no" => $po_no,
                    "po_name" => $this->session->name,
                    "delivery_date" => $post['delivery_date'],
                    "qty" => $post['qty'],
                    "discount" => $post['discount'],
                    "price" => $post['price'],
                    "total" => $post['total'],
                    "taxes" => $taxes,
                    "remarks" => $post['remarks'],
                    "month_1" => $post['month_1'],
                    "month_2" => $post['month_2'],
                    "month_3" => $post['month_3'],
                    "month_4" => $post['month_4'],
                    "total_sub" => $post['total_sub'],
                );

                // Simpan purchase order baru
                $send = $this->crud->createPO('purchase_orders',$table_approval, $data);

                // Update status purchase request
                $this->db->where('request_no', $post['request_no']);
                $this->db->where('item_rm_id', $items->id);
                $this->db->update("purchase_requests", ["status" => 1]);

                echo json_encode($send);
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
            $post = $this->input->post();

            $items = $this->crud->read('item_rm', [], ['id' => $post['item_rm_id']]);
            $purchaseOrder = $this->crud->read('purchase_orders', [], ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id'], "item_rm_id" => $items->id]);
            $purchase_orders = $this->db->update('purchase_orders', [
                "supplier_id" => $post['supplier_id'],
                "qty" => $post['qty'],
                "discount" => $post['discount'],
                "po_date" => $post['po_date'],
                "price" => $post['price'],
                "total" => $post['total'],
                "delivery_date" => $post['delivery_date'],
                "remarks" => $post['remarks'],
                "month_1" => $post['month_1'],
                "month_2" => $post['month_2'],
                "month_3" => $post['month_3'],
                "month_4" => $post['month_4'],
                "total_sub" => $post['total_sub'],
                "disc_pr" => $post['disc_pr'],
                "discount_total" => $post['discount_total'],
                "income_tax" => $post['income_tax'],
                "income_total" => $post['income_total'],
                "total_dp" => $post['total_dp'],
                "total_grand" => $post['total_grand'],
                "total_vat" => $post['total_vat'],
                "revision" => (@$purchaseOrder->revision + 1)
            ], ["request_no" => $post['request_no'], "item_rm_id" => $items->id]);

            $purchase_requests = $this->db->update('purchase_requests', ["qty" => $post['qty']], ["request_no" => $post['request_no'], "item_rm_id" => $items->id]);

            echo $purchase_orders;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update_approval()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('signatures', [], $post);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_orders', $data);
        $update = $this->crud->update('purchase_requests', ["request_no" => $data['request_no'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]);
        echo $send;
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $po_date = $this->input->post('po_date');
        $p_date_start = date("Y-m-d", strtotime($po_date . "+1 month"));
        $p_date_to = date('Y-m-d', strtotime('+4 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    public function print_po($po_no)
    {
        $purchase_orders_total = $this->crud->reads('purchase_orders', [], ["po_no" => base64_decode($po_no)]);
        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => $purchase_orders->supplier_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();
        $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $purchase_orders->request_no]);
        $table_approval = ($purchaseRequests->division==="DIV01")?'purchase_orders':'purchase_orders_2';
        $plant = ($purchaseRequests->division==="DIV01")?'RUBBER PART':'EXTRUDER';
        $approval=$this->db->query("SELECT *, CASE 
            WHEN user_approval_1 = '$purchase_orders->approved_by' THEN '1'
            WHEN user_approval_2 = '$purchase_orders->approved_by' THEN '2'
            WHEN user_approval_3 = '$purchase_orders->approved_by' THEN '3'
            WHEN user_approval_4 = '$purchase_orders->approved_by' THEN '4'
            WHEN user_approval_5 = '$purchase_orders->approved_by' THEN '5'
            ELSE '0' END AS approved_by FROM approvals WHERE table_name = '$table_approval'");
            $sqlApproval = $approval->row();

        if(intval($sqlApproval->approved_by)==5){
            $user1=$sqlApproval->user_approval_4;
            $user2=$sqlApproval->user_approval_5;
        }
        if(intval($sqlApproval->approved_by)==4){
            $user1=$sqlApproval->user_approval_3;
            $user2=$sqlApproval->user_approval_4;
        }
        if(intval($sqlApproval->approved_by)==3){
            $user1=$sqlApproval->user_approval_2;
            $user2=$sqlApproval->user_approval_3;
        }
        if(intval($sqlApproval->approved_by)==2){
            $user1=$sqlApproval->user_approval_1;
            $user2=$sqlApproval->user_approval_2;
        }
        if(intval($sqlApproval->approved_by)==1){
            $user1=null;
            $user2=$sqlApproval->user_approval_1;
        }

        if($user1!==null){
            $user_1 = $this->crud->read('users', [], ["username" => $user1]);
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/>';
        }
        $user_2 = $this->crud->read('users', [], ["username" => $user2]);
        $users_2 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/>';

        $po_period = $purchase_orders->po_date;
        $month = date('m', strtotime($po_period));
        $year = date('y', strtotime($po_period));

        $bulan_array = array(
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec"
        );

        $month_1 = $bulan_array[(($month + 1 - 1) % 12) + 1] . "-" . (($month + 1 > 12) ? $year + 1 : $year);
        $month_2 = $bulan_array[(($month + 2 - 1) % 12) + 1] . "-" . (($month + 2 > 12) ? $year + 1 : $year);
        $month_3 = $bulan_array[(($month + 3 - 1) % 12) + 1] . "-" . (($month + 3 > 12) ? $year + 1 : $year);
        $month_4 = $bulan_array[(($month + 4 - 1) % 12) + 1] . "-" . (($month + 4 > 12) ? $year + 1 : $year);

        //Generate QRcode
        $this->createQrcode($purchase_orders->po_no, "assets/image/qrcode/");
        if($user1!==null){
            $this->createQrcode(md5($user_1->name), "assets/image/qrcode/");
        }
        $this->createQrcode(md5($user_2->name), "assets/image/qrcode/");

        //Config Page
        $rows = $this->getRowsPerPage(1);
        $page = ceil(count($purchase_orders_total) / $rows);
        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->po_no . '</title>
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
                            </center>
                        </div>
                        <div class="print">
                        <div style="width:100%; display:flex, flex-direction:column">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $total_qty = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_id, b.name as item_name, b.uom, c.currency, a.price, b.description, a.month_1, a.month_2, a.month_3, a.month_4');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit($rows, ($i * $rows));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->po_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_order . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_order . '</td>
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
                            <div style="border: 1px solid black; padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>PURCHASE ORDER</u></h3>
                                    <small>NO : ' . @$purchase_orders->po_no . '</small>
                                </center>';
            if ($hal == 1) {
                $html .= '<table style="width:100%; font-size:12px; margin-bottom:10px;">
                                            <tr>
                                                <td width="80">Supplier</td>
                                                <td width="10">:</td>
                                                <td width="30%"><b>' . @$supplier->name . '</b></td>
                                                <td style="text-align:right;" rowspan="7">
                                                    <div style="display:flex;flex-direction:column;">
                                                        <div style="text-align:left;align-self:flex-end;">
                                                            <h4>Plant : '. @$plant .'</h4>
                                                            Page <span><b>' . $hal . '</b> of <b>' . $page . '</b></span><br><br>
                                                            PO Periode: <b>' . date("F Y", strtotime($purchase_orders->po_date)) . '</b><br>
                                                            Revision: <b>' . $purchase_orders->revision . '</b><br>
                                                            Revision Date: <b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                                            Payment Terms: <b>' . $supplier->payment_term . ' Days</b>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50">Address</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->address . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Reff No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$purchase_orders->request_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Attention</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->attention . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Phone</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->telp . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Fax</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->fax . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Email</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->email . '</b></td>
                                            </tr>
                                    </table>';
            }
            if ($hal > 1) {
                $html .= '<table style="width:100%; font-size:12px; margin-bottom:10px;">
                                            <tr>
                                                <td width="80"></td>
                                                <td width="10"></td>
                                                <td width="30%"></td>
                                                <td style="text-align:right;" rowspan="7">
                                                    <div style="display:flex;flex-direction:column;">
                                                        <div style="text-align:left;align-self:flex-end;">
                                                            Page <span><b>' . $hal . '</b> of <b>' . $page . '</b></span><br><br>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    </table>';
            }
            $html .= '<table id="customers">
                                    <tr>
                                        <th rowspan="2" width="30" style="text-align:center;">No</th>
                                        <th rowspan="2" width="150" style="text-align:center;">Part No</th>
                                        <th rowspan="2" width="150" style="text-align:center;">Part Name</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Specification</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Uom</th>
                                        
                                        <th rowspan="2" width="50" style="text-align:center;">Unit<br>Price</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Currency</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Amount</th>
                                        <th rowspan="2" width="80" style="text-align:center;">Delivery<br>Date</th>
                                        <th colspan="4" width="80" style="text-align:center;">Forecast</th>

                                        <tr>
                                            <th width="80" style="text-align:center;">' . $month_1 . '</th>
                                            <th width="80" style="text-align:center;">' . $month_2 . '</th>
                                            <th width="80" style="text-align:center;">' . $month_3 . '</th>
                                            <th width="80" style="text-align:center;">' . $month_4 . '</th>
                                        </tr>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $subtotal += ($record['qty'] * $record['price']);
                $total_qty += $record['qty'];
                if ($record['currency'] != "IDR") {
                    $digits = 4;
                } else {
                    $digits = 2;
                }

                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:center;">' . $record['description'] . '</td>
                                <td style="text-align:right;">' . number_format(round($record['qty']), 0, ',', '.') . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                
                                <td style="text-align:right;">' . number_format($record['price'], $digits) . '</td>
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], 2) . '</td>
                                <td style="text-align:center;">' . $record['delivery_date'] . '</td>
                                <td style="text-align:right;">' . $record['month_1'] . '</td>
                                <td style="text-align:right;">' . $record['month_2'] . '</td>
                                <td style="text-align:right;">' . $record['month_3'] . '</td>
                                <td style="text-align:right;">' . $record['month_4'] . '</td>
                            </tr>';
                $row++;
                $no++;
            }
            if (($i + 1) == $page) {

                $html .= '
                <tr>
                    <th style="text-align:center;" colspan="4">Sub Total</th>
                    <th style="text-align:right;">' . number_format($total_qty, 0, ',', '.') . '</th>
                    <th style="text-align:right;" colspan="3"></th>
                    <th style="text-align:right;">' . number_format($record['total_sub'], 2) . '</th>
                    <th style="text-align:right;" colspan="5"></th> 
                </tr>
                </table>';
                if ((count($records) > ($page == 1 ? 7 : 12))) {
                    // $html .= '<div style="page-break-after:always;"/></div>';

                    $html .= '<div style="page-break-after:always;"/></div></div>
                            <table style="width:100%;" class="headecop">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->po_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_order . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_order . '</td>
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
                        </table><div style="border: 1px solid black; padding:10px;" class="containerpo">';
                }
                // Memindahkan informasi approval ke sini
                if($user1!==null){
                    $html .= '<div style="width:100%; display: grid; grid-template-columns: auto;">
                        <div style="width:100%; display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <div style="text-align: center;">
                            <div style="margin-top: 30px;">Supplier Approval</div>
                            <div></div>
                            <div style="margin-top: 100px;">(.........................)</div>
                        </div>
                        <div style="text-align: center; display: flex; flex-direction:row;">
                            <div style="text-align: center; margin-right:10px">
                                <div style="margin-top: 30px;">Approved By</div>
                                <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/></div>
                                <div style="margin-top: 10px;">' . $user_1->name . '</div>
                                <div>' . $user_1->position . '</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="margin-top: 30px;">Approved By</div>
                                <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/></div>
                                <div style="margin-top: 10px;">' . $user_2->name . '</div>
                                <div>' . $user_2->position . '</div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; margin-top: 45px; font-style: italic; font-size:10px;">
                        Electronic Auto Generating Approval No Need Signature
                    </div>
                        </div>
    
                    <div style="font-size:12px; margin-top:20px;">
                        <tr>
                            <td>Term & Condition</td>
                        </tr>
                    </div>
    
                    <table style="width:100%; font-size:12px; margin-top:20px;">
                        <tr>
                            <td width="20">1.</td>
                            <td>Please sign, stamp & reply email to : mcl@banshu-rubber.com. Maximum one day after PO received.</td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>Please mention the Purchase Order Number in the Shipping & Billing Document.</td>
                        </tr>
                        <tr>
                            <td>3.</td>
                            <td>Please make sure delivery date is same with Purchase Order.</td>
                        </tr>
                    </table>
    
                    </div>';

                }
                if($user1==null){
                    
                $html .= '<div style="width:100%; display: grid; grid-template-columns: auto;">
                <div style="width:100%; display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                <div style="text-align: center;">
                    <div style="margin-top: 30px;">Supplier Approval</div>
                    <div></div>
                    <div style="margin-top: 100px;">(.........................)</div>
                </div>
                <div style="text-align: center;">
                    <div style="margin-top: 30px;">Approved By</div>
                    <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/></div>
                    <div style="margin-top: 10px;">' . $user_2->name . '</div>
                    <div>' . $user_2->position . '</div>
                </div>
                </div>
                <div style="text-align: right; margin-top: 45px; font-style: italic; font-size:10px;">
                    Electronic Auto Generating Approval No Need Signature
                </div>
                    </div>

                <div style="font-size:12px; margin-top:20px;">
                    <tr>
                        <td>Term & Condition</td>
                    </tr>
                </div>

                <table style="width:100%; font-size:12px; margin-top:20px;">
                    <tr>
                        <td width="20">1.</td>
                        <td>Please sign, stamp & reply email to : mcl@banshu-rubber.com. Maximum one day after PO received.</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Please mention the Purchase Order Number in the Shipping & Billing Document.</td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>Please make sure delivery date is same with Purchase Order.</td>
                    </tr>
                </table>

                </div>';
                }
            } else {
                $html .= '</table></div><div style="page-break-after:always;"/></div>';
            }
            $hal++;
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print_po_additional($po_no)
    {
        $purchase_orders_total = $this->crud->reads('purchase_orders', [], ["po_no" => base64_decode($po_no)]);
        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => $purchase_orders->supplier_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();
        $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $purchase_orders->request_no]);
        $table_approval = ($purchaseRequests->division==="DIV01")?'purchase_orders':'purchase_orders_2';
        $plant = ($purchaseRequests->division==="DIV01")?'RUBBER PART':'EXTRUDER';
        // $approval = $this->crud->read('approvals', [], ["table_name" => "purchase_orders"]);
        // $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);
        // $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);
        // $user_1 = $this->crud->read('users', [], ["username" => $purchase_orders->approved_by]);
        $approval=$this->db->query("SELECT *, CASE 
        WHEN user_approval_1 = '$purchase_orders->approved_by' THEN '1'
        WHEN user_approval_2 = '$purchase_orders->approved_by' THEN '2'
        WHEN user_approval_3 = '$purchase_orders->approved_by' THEN '3'
        WHEN user_approval_4 = '$purchase_orders->approved_by' THEN '4'
        WHEN user_approval_5 = '$purchase_orders->approved_by' THEN '5'
        ELSE '0' END AS approved_by FROM approvals WHERE table_name = '$table_approval'");
        $sqlApproval = $approval->row();

    if(intval($sqlApproval->approved_by)==5){
        $user1=$sqlApproval->user_approval_4;
        $user2=$sqlApproval->user_approval_5;
    }
    if(intval($sqlApproval->approved_by)==4){
        $user1=$sqlApproval->user_approval_3;
        $user2=$sqlApproval->user_approval_4;
    }
    if(intval($sqlApproval->approved_by)==3){
        $user1=$sqlApproval->user_approval_2;
        $user2=$sqlApproval->user_approval_3;
    }
    if(intval($sqlApproval->approved_by)==2){
        $user1=$sqlApproval->user_approval_1;
        $user2=$sqlApproval->user_approval_2;
    }
    if(intval($sqlApproval->approved_by)==1){
        $user1=null;
        $user2=$sqlApproval->user_approval_1;
    }

    if($user1!==null){
        $user_1 = $this->crud->read('users', [], ["username" => $user1]);
        // $users_1 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/>';
    }
    $user_2 = $this->crud->read('users', [], ["username" => $user2]);
    // $users_2 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/>';

        $po_period = $purchase_orders->po_date;
        $month = date('m', strtotime($po_period));
        $currentYear = date("y");

        $bulan_array = array(
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec"
        );

        // $month_1 = $bulan_array[($month + 1)] . "-" . $currentYear;
        // $month_2 = $bulan_array[($month + 2)] . "-" . $currentYear;
        // $month_3 = $bulan_array[($month + 3)] . "-" . $currentYear;
        // $month_4 = $bulan_array[($month + 4)] . "-" . $currentYear;

        $month_1 = $bulan_array[(($month + 1 - 1) % 12) + 1] . "-" . $currentYear;
        $month_2 = $bulan_array[(($month + 2 - 1) % 12) + 1] . "-" . $currentYear;
        $month_3 = $bulan_array[(($month + 3 - 1) % 12) + 1] . "-" . $currentYear;
        $month_4 = $bulan_array[(($month + 4 - 1) % 12) + 1] . "-" . $currentYear;

        // if (!empty($approval->user_approval_2)) {
        //     $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_2]);
        // } else {
        //     $user_2 = (object) ["name" => ""];
        // }

        // if (!empty($approval->user_approval_3)) {
        //     $user_3 = $this->crud->read('users', [], ["username" => $approval->user_approval_3]);
        // } else {
        //     $user_3 = (object) ["name" => ""];
        // }


        //Generate QRcode
        $this->createQrcode($purchase_orders->po_no, "assets/image/qrcode/");
        if($user1!==null){
            $this->createQrcode(md5($user_1->name), "assets/image/qrcode/");
        }
        $this->createQrcode(md5($user_2->name), "assets/image/qrcode/");

        // if ($purchase_orders->approved == 0) {
        //     $users_1 = '';
        //     $users_2 = '';
        //     $users_3 = '';
        // } elseif ($purchase_orders->approved == 1) {
        //     $users_1 = '';
        //     $users_2 = '';
        //     $users_3 = '';
        // } elseif ($purchase_orders->approved == 2) {
        //     $users_1 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/>';
        //     $users_2 = '';
        //     $users_3 = '';
        // } elseif ($purchase_orders->approved == 3) {
        //     $users_1 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/>';
        //     $users_2 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/>';
        //     $users_3 = '';
        // } else {
        //     $users_1 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/>';
        //     $users_2 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/>';
        //     // $users_3 = '<img src="' . base_url('assets/image/qrcode/' . md5($user_3->name) . '.png') . '" width="80"/>';
        // }


        //Config Page
        $rows = $this->getRowsPerPage(1);
        $page = ceil(count($purchase_orders_total) / $rows);
        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->po_no . '</title>
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
                            </center>
                        </div>
                        <div class="print">
                        <div style="width:100%; display:flex, flex-direction:column">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $total_qty = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_id, b.name as item_name, b.uom, c.currency, a.price, b.description, a.month_1, a.month_2, a.month_3, a.month_4');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit($rows, ($i * $rows));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->po_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_order_additional . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_order . '</td>
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
                            <div style="border: 1px solid black; padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>ADDITIONAL PURCHASE ORDER</u></h3>
                                    <small>NO : ' . @$purchase_orders->po_no . '</small>
                                </center>';
            if ($hal == 1) {
                $html .= '<table style="width:100%; font-size:12px; margin-bottom:10px;">
                                            <tr>
                                                <td width="80">Supplier</td>
                                                <td width="10">:</td>
                                                <td width="30%"><b>' . @$supplier->name . '</b></td>
                                                <td style="text-align:right;" rowspan="7">
                                                    <div style="display:flex;flex-direction:column;">
                                                        <div style="text-align:left;align-self:flex-end;">
                                                            <h4>Plant : '. @$plant .'</h4>
                                                            Page <span><b>' . $hal . '</b> of <b>' . $page . '</b></span><br><br>
                                                            PO Periode: <b>' . date("F Y", strtotime($purchase_orders->po_date)) . '</b><br>
                                                            Revision: <b>' . $purchase_orders->revision . '</b><br>
                                                            Revision Date: <b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                                            Payment Terms: <b>' . $supplier->payment_term . ' Days</b>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50">Address</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->address . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Reff No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$purchase_orders->request_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Attention</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->attention . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Phone</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->telp . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Fax</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->fax . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Email</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$supplier->email . '</b></td>
                                            </tr>
                                    </table>';
            }
            if ($hal > 1) {
                $html .= '<table style="width:100%; font-size:12px; margin-bottom:10px;">
                                            <tr>
                                                <td width="80"></td>
                                                <td width="10"></td>
                                                <td width="30%"></td>
                                                <td style="text-align:right;" rowspan="7">
                                                    <div style="display:flex;flex-direction:column;">
                                                        <div style="text-align:left;align-self:flex-end;">
                                                            Page <span><b>' . $hal . '</b> of <b>' . $page . '</b></span><br><br>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    </table>';
            }
            $html .= '<table id="customers">
            <tr>
                <th width="30" style="text-align:center;">No</th>
                <th width="150" style="text-align:center;">Part No</th>
                <th width="150" style="text-align:center;">Part Name</th>
                <th width="50" style="text-align:center;">Specification</th>
                <th width="50" style="text-align:center;">Qty</th>
                <th width="50" style="text-align:center;">Uom</th>
                <th width="50" style="text-align:center;">Unit<br>Price</th>
                <th width="50" style="text-align:center;">Currency</th>
                <th width="50" style="text-align:center;">Amount</th>
                <th width="80" style="text-align:center;">Delivery<br>Date</th>
                <th width="80" style="text-align:center;">Remarks</th>
            </tr>';
            foreach ($records as $record) {
                $subtotal += ($record['qty'] * $record['price']);
                $total_qty += $record['qty'];
                if ($record['currency'] != "IDR") {
                    $digits = 4;
                } else {
                    $digits = 2;
                }

                $html .= '<tr>    
                <td style="text-align:center;">' . $no . '</td>
                <td>' . $record['item_id'] . '</td>
                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                <td style="text-align:center;">' . $record['description'] . '</td>
                <td style="text-align:right;">' . number_format(round($record['qty']), 0, ',', '.') . '</td>
                <td style="text-align:center;">' . $record['uom'] . '</td>
                <td style="text-align:right;">' . number_format($record['price'], $digits) . '</td>
                <td style="text-align:center;">' . $record['currency'] . '</td>
                <td style="text-align:right;">' . number_format($record['total'], 2) . '</td>
                <td style="text-align:center;">' . $record['delivery_date'] . '</td>
                <td style="text-align:right;">' . $record['remarks'] . '</td>
              </tr>';
                $no++;
            }

            if (($i + 1) == $page) {

                $html .= '
                <tr>    
                    <th style="text-align:center;" colspan="4">Sub Total</th>
                    <th style="text-align:right;">' . number_format($total_qty, 0, ',', '.') . '</th>
                    <th style="text-align:right;" colspan="3"></th>
                    <th style="text-align:right;">' . number_format($record['total_sub'], 2) . '</th>
                    <th style="text-align:right;" colspan="2"></th> 
                </tr>
                </table>';
                $html .= '</table>';
                if ((count($records) > ($page == 1 ? 7 : 12))) {
                    // $html .= '<div style="page-break-after:always;"/></div>';

                    $html .= '<div style="page-break-after:always;"/></div></div>
                            <table style="width:100%;" class="headecop">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->po_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_order . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_order . '</td>
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
                        </table><div style="border: 1px solid black; padding:10px;" class="containerpo">';
                }
                // Memindahkan informasi approval ke sini
                if($user1!==null){
                    $html .= '<div style="width:100%; display: grid; grid-template-columns: auto;">
                        <div style="width:100%; display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <div style="text-align: center;">
                            <div style="margin-top: 30px;">Supplier Approval</div>
                            <div></div>
                            <div style="margin-top: 100px;">(.........................)</div>
                        </div>
                        <div style="text-align: center; display: flex; flex-direction:row;">
                            <div style="text-align: center; margin-right:10px">
                                <div style="margin-top: 30px;">Approved By</div>
                                <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/></div>
                                <div style="margin-top: 10px;">' . $user_1->name . '</div>
                                <div>' . $user_1->position . '</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="margin-top: 30px;">Approved By</div>
                                <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/></div>
                                <div style="margin-top: 10px;">' . $user_2->name . '</div>
                                <div>' . $user_2->position . '</div>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; margin-top: 45px; font-style: italic; font-size:10px;">
                        Electronic Auto Generating Approval No Need Signature
                    </div>
                        </div>
    
                    <div style="font-size:12px; margin-top:20px;">
                        <tr>
                            <td>Term & Condition</td>
                        </tr>
                    </div>
    
                    <table style="width:100%; font-size:12px; margin-top:20px;">
                        <tr>
                            <td width="20">1.</td>
                            <td>Please sign, stamp & reply email to : mcl@banshu-rubber.com. Maximum one day after PO received.</td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>Please mention the Purchase Order Number in the Shipping & Billing Document.</td>
                        </tr>
                        <tr>
                            <td>3.</td>
                            <td>Please make sure delivery date is same with Purchase Order.</td>
                        </tr>
                    </table>
    
                    </div>';

                }
                if($user1==null){
                    
                $html .= '<div style="width:100%; display: grid; grid-template-columns: auto;">
                <div style="width:100%; display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                <div style="text-align: center;">
                    <div style="margin-top: 30px;">Supplier Approval</div>
                    <div></div>
                    <div style="margin-top: 100px;">(.........................)</div>
                </div>
                <div style="text-align: center;">
                    <div style="margin-top: 30px;">Approved By</div>
                    <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/></div>
                    <div style="margin-top: 10px;">' . $user_2->name . '</div>
                    <div>' . $user_2->position . '</div>
                </div>
                </div>
                <div style="text-align: right; margin-top: 45px; font-style: italic; font-size:10px;">
                    Electronic Auto Generating Approval No Need Signature
                </div>
                    </div>

                <div style="font-size:12px; margin-top:20px;">
                    <tr>
                        <td>Term & Condition</td>
                    </tr>
                </div>

                <table style="width:100%; font-size:12px; margin-top:20px;">
                    <tr>
                        <td width="20">1.</td>
                        <td>Please sign, stamp & reply email to : mcl@banshu-rubber.com. Maximum one day after PO received.</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>Please mention the Purchase Order Number in the Shipping & Billing Document.</td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>Please make sure delivery date is same with Purchase Order.</td>
                    </tr>
                </table>

                </div>';
                }
                // $html .= '<div style="width:100%; display: grid; grid-template-columns: auto;">
                //     <div style="width:100%; display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                //     <div style="text-align: center;">
                //         <div style="margin-top: 30px;">Supplier Approval</div>
                //         <div></div>
                //         <div style="margin-top: 100px;">(.........................)</div>
                //     </div>
                //     <div style="text-align: center; display: flex; flex-direction:row;">
                //         <div style="text-align: center; margin-right:10px">
                //             <div style="margin-top: 30px;">Approved By</div>
                //             <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_1->name) . '.png') . '" width="80"/></div>
                //             <div style="margin-top: 10px;">' . $user_1->name . '</div>
                //             <div>' . $user_1->position . '</div>
                //         </div>
                //         <div style="text-align: center;">
                //             <div style="margin-top: 30px;">Approved By</div>
                //             <div style="margin-top: 10px;"><img src="' . base_url('assets/image/qrcode/' . md5($user_2->name) . '.png') . '" width="80"/></div>
                //             <div style="margin-top: 10px;">' . $user_2->name . '</div>
                //             <div>' . $user_2->position . '</div>
                //         </div>
                //     </div>
                // </div>
                // <div style="text-align: right; margin-top: 45px; font-style: italic; font-size:10px;">
                //     Electronic Auto Generating Approval No Need Signature
                // </div>
                //     </div>

                // <div style="font-size:12px; margin-top:20px;">
                //     <tr>
                //         <td>Term & Condition</td>
                //     </tr>
                // </div>

                // <table style="width:100%; font-size:12px; margin-top:20px;">
                //     <tr>
                //         <td width="20">1.</td>
                //         <td>Please sign, stamp & reply email to : mcl@banshu-rubber.com. Maximum one day after PO received.</td>
                //     </tr>
                //     <tr>
                //         <td>2.</td>
                //         <td>Please mention the Purchase Order Number in the Shipping & Billing Document.</td>
                //     </tr>
                //     <tr>
                //         <td>3.</td>
                //         <td>Please make sure delivery date is same with Purchase Order.</td>
                //     </tr>
                // </table>

                // </div>';
            } else {
                $html .= '</table></div><div style="page-break-after:always;"/></div>';
            }
            $hal++;
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_orders_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_suppliers = $this->input->get('filter_suppliers');
        $filter_status = $this->input->get('filter_status');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, 
            b.number as item_id, 
            b.name as item_name,
            b.uom,
            c.name as item_family_name, 
            d.name as supplier_name, 
            d.currency, 
            e.mpq, 
            e.moq');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.po_date >=', $filter_from);
            $this->db->where('a.po_date <=', $filter_to);
        }
        if ($filter_po_no != "") {
            $this->db->where('a.po_no', $filter_po_no);
        }
        if ($filter_suppliers != "") {
            $this->db->where('d.id', $filter_suppliers);
        }
        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        $this->db->order_by('a.po_date', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PURCHASE ORDER</small>
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
                    <th width="20">No</th>
                    <th>PO No</th>
                    <th>PO Period</th>
                    <th>PO Name</th>
                    <th>Supplier</th>
                    <th>Part No External</th>
                    <th>Part Name</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Currency</th>
                    <th>Uom</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }
            if ($data['currency'] != "IDR") {
                $digits = 4;
            } else {
                $digits = 2;
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['po_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . intval($data['mpq']) . '</td>
                        <td>' . intval($data['moq']) . '</td>
                        <td>' . number_format(round($data['qty']), 0, ',', '.') . '</td>
                        <td>' . number_format($data['price'], 4, ",", ".") . '</td>
                        <td>' . number_format(($data['qty'] * $data['price']), 2, ",", ".") . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    function getRowsPerPage($pageNumber)
    {
        if ($pageNumber == 1) {
            return 18; // Set 20 rows for the first page
        } else {
            return 25; // Set 25 rows for subsequent pages
        }
    }
}
