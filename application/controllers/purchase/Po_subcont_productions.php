<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Po_subcont_productions extends CI_Controller
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
            $this->load->view('purchase/po_subcont_productions');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('po_subcont_productions', ["name" => $post]);
        echo json_encode($send);
    }

    function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function readPRNo()
    {
        $user = $this->crud->currentUserDept();

        $this->db->select('a.doc_no as pr_no, a.subcont_id');
        $this->db->from('pr_subcont_productions a');
        $this->db->join('users u', 'u.username = a.created_by', 'left');
        $this->db->where('a.status', 0);
        // $this->db->where('a.approved_to', '');

        if (!empty($user->department_id) && !in_array($user->department, $this->crud->getIgnoreDept())) {
            $this->db->where('u.department_id', $user->department_id);
        }

        $this->db->group_by(['a.doc_no']);
        $this->db->order_by('a.doc_no', 'DESC');

        $records = $this->db->get()->result();

        echo json_encode($records);
    }

    public function readSubcontProduction()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $send = $this->crud->query("
            SELECT * FROM subconts 
            WHERE (number like '%$post%' or name like '%$post%' or id like '%$post%') 
            AND subcont_type_id = 'TS002'
            AND status = 0
            AND deleted = 0
        ");

        echo json_encode($send);
    }

    public function readSubcontProductionPR()
    {
        $pr_no = $this->input->post('pr_no');

        $this->db->select('
            s.id,
            s.number,
            s.name,
            p.order_type
        ');
        $this->db->from('pr_subcont_productions p');
        $this->db->join('subconts s','s.id=p.subcont_id');
        $this->db->where('p.doc_no',$pr_no);
        // $this->db->where('p.status', 0);

        if (!$this->input->post('edit')) {
            $this->db->where('p.status', 0);
        }

        $this->db->group_by('s.id');

        echo json_encode($this->db->get()->result());
    }

    public function readPoNos()
    {
        $filter_subcont_id   = $this->input->get('filter_subcont_id');

        $this->db->select('a.po_no');
        $this->db->from('po_subcont_productions a');

        if ($filter_subcont_id != '') {
            $this->db->where('a.subcont_id', $filter_subcont_id);
        }

        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');

        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function checkTotalSub()
    {
        $po_no = $this->input->post('po_no');
        $this->db->select('total_sub');
        $this->db->from('po_subcont_productions');
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
        if (!$this->input->post()) {
            return;
        }

        $filter_from    = $this->input->get('filter_from');
        $filter_to     = $this->input->get('filter_to');
        $filter_subcont_id      = $this->input->get('filter_subcont_id');
        $filter_order_type      = $this->input->get('filter_order_type');
        $filter_po_no           = $this->input->get('filter_po_no');
        $filter_status          = $this->input->get('filter_status');
        $filter_product_no      = $this->input->get('filter_product_no');

        $page = max(1, (int)$this->input->post('page'));
        $rows = max(10, (int)$this->input->post('rows'));
        $offset = ($page - 1) * $rows;

        $this->db->select("
            h.id,
            h.po_no,
            h.pr_no,
            h.po_date,
            h.due_date,
            h.order_type,
            h.notes,
            h.revision,
            h.total_amount,
            h.status,
            h.created_by,
            h.created_date,
            h.updated_by,
            h.updated_date,
            h.printed,

            s.id as subcont_id,
            s.name as subcont_name,

            COUNT(d.id) as total_detail,
            SUM(CASE WHEN h.approved = 1 THEN 1 ELSE 0 END) as total_approved,

            h.approved_by as approved_by,
            h.approved_date as approved_date,
            h.approved_to,
            h.status as status_po,
            h.deleted,
            '0' as status_si
        ");
        $this->db->from('po_subcont_productions h');
        $this->db->join(
            'subconts s',
            's.id = h.subcont_id',
            'left'
        );
        $this->db->join(
            'po_subcont_production_details d',
            'd.po_subcont_productions_id = h.id',
            'left'
        );

        if($filter_from != '' && $filter_to != ''){
            $this->db->where('h.po_date >=',$filter_from);
            $this->db->where('h.po_date <=',$filter_to);
        }

        if($filter_subcont_id != ''){
            $this->db->where('h.subcont_id',$filter_subcont_id);
        }
        if($filter_po_no != ''){
            $this->db->where('h.po_no',$filter_po_no);
        }
        if($filter_status != ''){
            $this->db->where('h.status',$filter_status);
        }
        if($filter_product_no != ''){
            $this->db->where('d.item_fg_id',$filter_product_no);
        }
        if($filter_order_type != ''){
            $this->db->where('h.order_type',$filter_order_type);
        }

        $this->db->group_by('h.id');
        $this->db->order_by('h.created_date','DESC');
        $totalRows = $this->db->count_all_results('',false);
        
        $this->db->limit($rows,$offset);
        $records = $this->db->get()->result_array();

        $arr = [];
        foreach($records as $record){

            $arr[] = [
                "id"            => $record['id'],
                "po_no"         => $record['po_no'],
                "pr_no"         => $record['pr_no'],
                "po_date"       => $record['po_date'],
                "due_date"      => $record['due_date'],
                "subcont_name"  => $record['subcont_name'],
                "currency"      => "IDR",
                "total"         => $record['total_amount'],
                "notes"         => $record['notes'],
                "revision"      => $record['revision'],
                "status"        => $record['status'],
                "approved_by"   => $record['approved_by'],
                "approved_date" => $record['approved_date'],
                "created_by"    => $record['created_by'],
                "created_date"  => $record['created_date'],
                "updated_by"    => $record['updated_by'],
                "updated_date"  => $record['updated_date'],
                "printed"       => $record['printed'],
                "subcont_id"    => $record['subcont_id'],
                "deleted"       => $record['deleted'],
                "status_po"     => $record['status_po'],
                "approved_to"   => $record['approved_to'],
                "status_si"     => $record['status_si'],
                "order_type"    => $record['order_type'],
            ];

        }

        echo json_encode([
            "total"=>$totalRows,
            "rows"=>$arr
        ]);

    }

    public function datatableDetails()
    {
        if ($this->input->get()) {

            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $po_no             = base64_decode($this->input->get('po_no'));

            $this->db->select("
                d.item_fg_id,
                f.number AS item_number,
                f.name AS item_name,
                f.uom,
                d.qty,
                d.unit_price,
                d.amount,
                h.status as status_po,
                '0' as status_si
            ");

            $this->db->from("po_subcont_productions h");
            $this->db->join(
                "po_subcont_production_details d",
                "d.po_subcont_productions_id = h.id"
            );
            $this->db->join(
                "item_fg f",
                "f.id = d.item_fg_id"
            );

            $this->db->where("h.po_no", $po_no);
            // $this->db->where("d.deleted",0);

            if($filter_product_no != ''){
                $this->db->where('d.item_fg_id',$filter_product_no);
            }
            $this->db->order_by("f.number","ASC");

            echo json_encode(
                $this->db->get()->result_array()
            );
        }
    }

    public function datatableUpdates()
    {
        $po_no = base64_decode($this->input->get('po_no'));

        $this->db->select("
            d.id,
            d.item_fg_id,
            i.number AS item_number,
            i.name AS item_name,
            i.uom,
            d.qty,
            d.unit_price AS price,
            d.amount as total,
            s.id as subcont_id,
            h.total_amount AS total_amount,
            h.po_no
        ");

        $this->db->from("po_subcont_production_details d");
        $this->db->join("po_subcont_productions h", "h.id = d.po_subcont_productions_id");
        $this->db->join("subconts s", "s.id = h.subcont_id");
        $this->db->join("item_fg i", "i.id = d.item_fg_id");

        $this->db->where("h.po_no", $po_no);
        // $this->db->where("d.deleted", 0);

        $this->db->order_by("i.number", "ASC");

        $rows = $this->db->get()->result_array();

        echo json_encode([
            "rows"         => $rows,
        ]);
    }

    // private function generatePoNo($pr_no, $subcont_id)
    // {
    //     $subcont = $this->crud->read('subconts', [], ['id' => $subcont_id]);

    //     if (!$subcont) {
    //         throw new Exception("Subcont not found.");
    //     }

    //     $creator = $this->db
    //         ->select('IFNULL(NULLIF(d.name, ""), "ADM") AS department')
    //         ->from('pr_subcont_productions p')
    //         ->join('users u', 'u.username = p.created_by', 'left')
    //         ->join('departments d', 'd.id = u.department_id', 'left')
    //         ->where('p.doc_no', $pr_no)
    //         ->limit(1)
    //         ->get()
    //         ->row();

    //     $department = strtoupper($creator->department ?? 'ADM');
    //     $subcontCode = strtoupper($subcont->number);

    //     $month = date('m');
    //     $year  = date('y');

    //     $last = $this->db->query("
    //         SELECT
    //             MAX(CAST(SUBSTRING_INDEX(po_no,'/',1) AS UNSIGNED)) AS seq
    //         FROM po_subcont_productions
    //         WHERE po_no LIKE '%/{$department}/PO/{$subcontCode}/%/{$year}'
    //     ")->row();

    //     $seq = ($last && $last->seq) ? $last->seq + 1 : 1;

    //     return sprintf("%02d/%s/PO/%s/%s/%s", $seq, $department, $subcontCode, $month, $year);
    // }

    private function generatePoNo($pr_no, $subcont_id, $order_type)
    {
        $subcont = $this->crud->read('subconts', [], ['id' => $subcont_id]);

        if (!$subcont) {
            throw new Exception("Subcont not found.");
        }

        $creator = $this->db
            ->select('IFNULL(NULLIF(d.name, ""), "ADM") AS department')
            ->from('pr_subcont_productions p')
            ->join('users u', 'u.username = p.created_by', 'left')
            ->join('departments d', 'd.id = u.department_id', 'left')
            ->where('p.doc_no', $pr_no)
            ->limit(1)
            ->get()
            ->row();

        $department  = strtoupper($creator->department ?? 'ADM');
        $subcontCode = strtoupper($subcont->number);

        // PO atau POA
        $poCode = strtolower($order_type) == 'additional' ? 'POA' : 'PO';

        $month = date('m');
        $year  = date('y');

        $last = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(po_no,'-',1) AS UNSIGNED)) AS seq
            FROM po_subcont_productions
            WHERE po_no LIKE '%-{$department}-{$poCode}-{$subcontCode}-%-{$year}'
        ")->row();

        $seq = ($last && $last->seq) ? $last->seq + 1 : 1;

        return sprintf("%02d-%s-%s-%s-%s-%s", $seq, $department, $poCode, $subcontCode, $month, $year);
    }

    public function create()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();
        $details = json_decode($post['details']);

        $this->db->trans_begin();

        try {

            $po_no = $this->generatePoNo($post['pr_no'], $post['subcont_id'], $post['order_type']);
            $header = [
                "subcont_id"    => $post['subcont_id'],
                "order_type"    => $post['order_type'],
                "pr_no"         => $post['pr_no'],
                "po_no"         => $po_no,
                "po_date"       => $post['po_date'],
                "due_date"      => $post['due_date'],
                "revision"      => $post['revision'],
                "notes"         => $post['notes'],
                "total_amount"  => $post['total_amount'],
                "status"        => 0
            ];

            $header_id = $this->crud->create_return_id("po_subcont_productions", $header);

            if (empty($header_id)) {
                throw new Exception("Failed to create Purchase Order header.");
            }

            foreach ($details as $row) {

                $detail = [
                    "po_subcont_productions_id" => $header_id,
                    "item_fg_id" => $row->item_fg_id,
                    "qty" => $row->qty,
                    "unit_price" => $row->price,
                    "amount" => $row->total
                ];

                $this->crud->createV2("po_subcont_production_details", "po_subcont_production_details", $detail);

            }

            $this->db
                ->where("doc_no",$post['pr_no'])
                ->where("subcont_id",$post['subcont_id'])
                ->update("pr_subcont_productions",[
                        "status"=>1
                    ]
                );

            if ($this->db->trans_status() === FALSE){
                throw new Exception();
            }
            $this->db->trans_commit();

            echo json_encode([
                "success" => true,
                "message" => "Purchase Order created successfully"
            ]);

        }catch(Exception $e){
            $this->db->trans_rollback();
            echo json_encode([
                "success"=>false
            ]);

        }

    }

    public function update()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();

        $header = $this->crud->read("po_subcont_productions", [], ["po_no" => $post["po_no"]]);

        if (!$header) {
            echo json_encode([
                "success" => false,
                "message" => "Purchase Order tidak ditemukan."
            ]);
            return;
        }

        $details = json_decode($post["details"], true);

        $this->db->trans_begin();

        $qtyChanged = false;
        $isApproved = false;

        $oldDetails = $this->db
            ->where("po_subcont_productions_id", $header->id)
            ->get("po_subcont_production_details")
            ->result_array();

        $oldMap = [];
        foreach ($oldDetails as $row) {
            $oldMap[$row["item_fg_id"]] = $row;
        }

        $newItemIds = array_column($details, "item_fg_id");
        $oldItemIds = array_column($oldDetails, "item_fg_id");

        $deletedItems = array_diff($oldItemIds, $newItemIds);
        if (!empty($deletedItems)) {

            $this->db
                ->where("po_subcont_productions_id", $header->id)
                ->where_in("item_fg_id", $deletedItems)
                ->delete("po_subcont_production_details");
        }

        if (!empty($deletedItems)) {
            $qtyChanged = true;
        }

        foreach ($details as $detail) {

            $old = $this->crud->read("po_subcont_production_details", [], [
                    "po_subcont_productions_id" => $header->id,
                    "item_fg_id" => $detail["item_fg_id"]
                ]
            );

            $checkHeader = $this->crud->read("po_subcont_productions", [], [
                    "id" => $header->id
                ]
            );

            if (!$old) {
                continue;
            }

            if ((float)$old->qty != (float)$detail["qty"]) {
                $qtyChanged = true;
            }

            if (empty($checkHeader->approved_to)) {
                $isApproved = true;
            }

            $this->crud->update("po_subcont_production_details",  [
                    "po_subcont_productions_id" => $header->id,
                    "item_fg_id"                => $detail["item_fg_id"]
                ], [
                    "qty"        => $detail["qty"],
                    "unit_price" => $detail["price"],
                    "amount"     => $detail["total"]
                ]
            );
        }

        if ($qtyChanged) {

            $revision = $header->revision;

            if ($isApproved) {
                $revision++;
            }

            $this->crud->updateV2("po_subcont_productions", "po_subcont_productions",
                ["id" => $header->id], [
                    "revision"     => $revision,
                    "notes"        => $post["notes"],
                    "total_amount" => $post["total_amount"],
                ]
            );

        } else {

            $this->crud->updateV2("po_subcont_productions", "po_subcont_productions",
                ["id" => $header->id], [
                    "notes"        => $post["notes"],
                    "total_amount" => $post["total_amount"],
                    "updated_by"   => $this->session->userdata("username"),
                    "updated_date" => date("Y-m-d H:i:s")
                ]
            );

        }


        if ($this->db->trans_status() == FALSE) {

            $this->db->trans_rollback();
            echo json_encode([
                "success" => false,
                "message" => "Update failed"
            ]);

        } else {

            $this->db->trans_commit();
            echo json_encode([
                "success" => true,
                "message" => "Purchase Order updated successfully"
            ]);
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        $send = $this->crud->delete('po_subcont_productions', $data);
        $update = $this->crud->update('pr_subcont_productions', ["doc_no" => $data['pr_no'], "subcont_id" => $data['subcont_id']], ["status" => 0]);

        echo $send;
    }


    public function print_po($po_no)
    {
        $purchase_order = $this->crud->read('po_subcont_productions', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $purchase_orders_total = $this->crud->reads('po_subcont_production_details', [], ["po_subcont_productions_id" => $purchase_order->id]);

        $subcont = $this->crud->read('subconts', [], ["id" => $purchase_order->subcont_id]);


        $this->db->select("
            a.*,
            b.name AS subcont_name,
            c.number AS item_fg_number,
            c.name AS item_fg_name
        ");

        $this->db->from('pr_subcont_productions a');
        $this->db->join('subconts b','b.id=a.subcont_id');
        $this->db->join('item_fg c','c.id=a.item_fg_id');

        $this->db->where('a.deleted',0);
        $this->db->where('a.doc_no',$purchase_order->pr_no);
        $this->db->where('a.subcont_id',$purchase_order->subcont_id);
        $this->db->where('a.order_type',$purchase_order->order_type);

        $this->db->order_by('c.number','ASC');

        $pr_attachment = $this->db->get()->result_array();


        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        
        $currentUser = $this->crud->currentUserDept();

        $creator = $this->db
            ->select('u.department_id, d.plant_id')
            ->from('users u')
            ->join('departments d', 'd.id = u.department_id', 'left')
            ->where('u.username', $purchase_order->created_by)
            ->get()
            ->row();

        $department_id = $creator->department_id ?? null;
        $plant_id = $creator->plant_id ?? null;

        $isAdmin = empty($currentUser->department_id);

        if (!$isAdmin && (
                $currentUser->department_id != @$creator->department_id ||
                $currentUser->plant_id != @$creator->plant_id
            ) &&
             !in_array($currentUser->department, $this->crud->getIgnoreDept())
        ) {
            show_error('Unauthorized', 403);
        }

        $table_approval = 'po_subcont_productions';

        $approval = $this->db->query("
            SELECT *,
            CASE
                WHEN user_approval_1 = '$purchase_order->approved_by' THEN '1'
                WHEN user_approval_2 = '$purchase_order->approved_by' THEN '2'
                WHEN user_approval_3 = '$purchase_order->approved_by' THEN '3'
                WHEN user_approval_4 = '$purchase_order->approved_by' THEN '4'
                WHEN user_approval_5 = '$purchase_order->approved_by' THEN '5'
                ELSE '0'
            END AS approved_by
            FROM approvals
            WHERE table_name = '$table_approval'
            AND department_id = '$department_id'
            AND plant_id = '$plant_id'
        ");

        $sqlApproval = $approval->row();
        if(empty($sqlApproval)){
            $sqlApproval = (object) [
                'approved_by' => 0,
                'user_approval_1' => '',
                'user_approval_2' => '',
                'user_approval_3' => '',
                'user_approval_4' => '',
                'user_approval_5' => ''
            ];
        }

        $approvedLevel = !empty($sqlApproval) ? (int)$sqlApproval->approved_by : 0;
        
        $approvalData = [];
        $buildApproval = function($title, $username, $show){

            $user = null;

            if(!empty($username)){
                $user = $this->crud->read(
                    'users',
                    [],
                    ['username'=>$username]
                );
            }

            if($show && $user){
                $this->createQrcode(
                    md5($user->name),
                    "assets/image/qrcode/"
                );
            }

            return [
                'title'    => $title,
                'show'     => $show,
                'username' => $username,
                'name'     => $show && $user ? $user->name : '',
                'position' => $show && $user ? $user->position : '',
                'barcode'  => $show && $user ? base_url('assets/image/qrcode/'. md5($user->name). '.png') : ''
            ];

        };

        $approvalUsers = [];

        for($i=1;$i<=5;$i++){
            $field="user_approval_".$i;

            if(!empty($sqlApproval->$field)){
                $approvalUsers[]=[
                    'level'=>$i,
                    'username'=>$sqlApproval->$field
                ];
            }
        }

        for($i=count($approvalUsers)-1;$i>=0;$i--){

            $row=$approvalUsers[$i];

            if($row['level']<=2){

                $title='Known';
            }else{

                $title='Approved';
            }

            $approvalData[]=$buildApproval(
                $title,
                $row['username'],
                $approvedLevel >= $row['level']
            );

        }

        $approvalData[]=$buildApproval(
            'Assigned',
            $purchase_order->created_by,
            true
        );

        $this->createQrcode($purchase_order->po_no, "assets/image/qrcode/");

        //Config Page
        $rows = $this->getRowsPerPage(1);
        $page = ceil(count($purchase_orders_total) / $rows);
        $html = '<html>
                    <head>
                        <title>' . $purchase_order->po_no . '</title>
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
            $this->db->select('a.*, b.qty, b.unit_price, b.item_fg_id, c.number as item_fg_number, c.name as item_fg_name, c.uom, "IDR" as currency, b.amount');
            $this->db->from('po_subcont_productions a');
            $this->db->join(
                'po_subcont_production_details b',
                'b.po_subcont_productions_id = a.id'
            );

            $this->db->join(
                'item_fg c',
                'c.id = b.item_fg_id'
            );

            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            // $this->db->order_by('b.number', 'asc');
            $this->db->limit($rows, ($i * $rows));
            $records = $this->db->get()->result_array();

            // if ($purchase_order->updated_date != null) {
            //     $revision_date = $purchase_order->updated_date;
            // } else {
            //     $revision_date = $purchase_order->created_date;
            // }

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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_order->po_no . '.png') . '" width="60"/></td>
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
                                    <small>NO : ' . @$purchase_order->po_no . '</small>
                                </center>';
            if ($hal == 1) {
                        $html .= '
                        <table style="width:100%; margin-bottom:10px;">
                            <tr>
                                <td width="65%" valign="top">
                                    <table style="width:100%; font-size:12px !important;">
                                        <tr>
                                            <td width="80">Subcont Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$subcont->name . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Address</td>
                                            <td>:</td>
                                            <td><b>' . @$subcont->address . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Phone</td>
                                            <td>:</td>
                                            <td><b>' . @$subcont->telp . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Attention</td>
                                            <td>:</td>
                                            <td><b>' . @$subcont->contact_person . '</b></td>
                                        </tr>
                                    </table>
                                </td>

                                <td width="5"></td>

                                <td width="30%" valign="top">
                                    <table style="width:100%; font-size:12px !important;">
                                        <tr>
                                            <td width="90">PO Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("Y-m-d", strtotime($purchase_order->po_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Due Date</td>
                                            <td>:</td>
                                            <td><b>' . date("Y-m-d", strtotime($purchase_order->due_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Revision</td>
                                            <td>:</td>
                                            <td><b>' . $purchase_order->revision . '</b></td>
                                        </tr>
                                    </table>
                                </td>
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
                                        <th width="150" style="text-align:center;">Product ID</th>
                                        <th width="150" style="text-align:center;">Product No</th>
                                        <th width="150" style="text-align:center;">Product Name</th>
                                        <th width="50" style="text-align:center;">Qty</th>
                                        <th width="50" style="text-align:center;">UOM</th>
                                        
                                        <th width="50" style="text-align:center;">Currency</th>
                                        <th width="50" style="text-align:center;">Unit<br>Price</th>
                                        <th width="50" style="text-align:center;">Amount</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                // $subtotal += ($record['qty'] * $record['unit_price']);
                // $total_qty += $record['qty'];
                // if ($record['currency'] != "IDR") {
                //     $digits = 4;
                // } else {
                //     $digits = 2;
                // }


                $total_qty += $record['qty'];
                $subtotal += $record['amount'];

                $digits = ($record['currency'] != "IDR") ? 4 : 2;

                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_fg_id'] . '</td>
                                <td>' . $record['item_fg_number'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty'], 0, ',', '.') . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['unit_price'], $digits, ',', '.') . '</td>
                                <td style="text-align:right;">' . number_format($record['amount'], 2, ',', '.') . '</td>
                            </tr>';
                $row++;
                $no++;
            }
            if (($i + 1) == $page) {

                $html .= '
                <tr>
                    <th colspan="4" style="text-align:right; padding-right:10px;">TOTAL</th>
                    <th style="text-align:right;">' . number_format($total_qty, 0, ',', '.') . '</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th style="text-align:right;">' . number_format($subtotal, 2, ',', '.') . '</th>
                </tr>
                </table>';

                if ((count($records) > ($page == 1 ? 20 : 25))) {
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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_order->po_no . '.png') . '" width="60"/></td>
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

                    $html .= '<div style="width:100%; display: grid; grid-template-columns: auto;">
                    <div style="width:100%; display: flex; justify-content: space-between; align-items: start; margin-top: 20px;">
                    ';

                        $html .= '
                            <table style="width:28%; margin-top:10px; font-size:12px;">
                                <tr>
                                    <td style="vertical-align:top;">
                                        <b>Notes :</b><br><br>
                                        ' . (!empty($purchase_order->notes) ? nl2br($purchase_order->notes) : '') . '
                                    </td>
                                </tr>
                            </table>';

                        $html .= '
                            <div style="width:70%; text-align:center;">
                                <table style="width:100%; border-collapse:collapse; table-layout:fixed; margin-top:10px;" border="1" cellpadding="3">';

                                
                            $html .= '<tr style="text-align:center;">';

                            foreach ($approvalData as $approval){

                                $html .= '<td>'.$approval['title'].'</td>';

                            }

                            $html .= '</tr>';


                            $html .= '<tr style="height:100px;text-align:center;vertical-align:middle;">';

                            foreach($approvalData as $approval){

                                $html.='<td>';

                                if($approval['show']){

                                    $html.='<img src="'.
                                        $approval['barcode'].
                                        '" width="80"/>';

                                }

                                $html.='</td>';

                            }

                            $html.='<tr style="text-align:center;">';

                            foreach($approvalData as $approval){

                                $html.='<td>'.$approval['name'].'</td>';

                            }

                            $html.='</tr>';

                            $html.='<tr style="text-align:center;">';

                            foreach($approvalData as $approval){

                                $html.='<td>'.$approval['position'].'</td>';

                            }

                            $html.='</tr>';

                            $html .= '</tr>';
                            
                            $html .= '

                                </table>
                            </div>
                        ';

                    $html .= '

                    </div>
                    </div>

                    </div>';


            if(!empty($pr_attachment) && $purchase_order->deleted != 2){

                $monthM1 = date('M Y', strtotime($pr_attachment[0]['p_year'].'-'.$pr_attachment[0]['p_month'].'-01'));

                $monthM2 = date('M Y', strtotime('+1 month', strtotime($pr_attachment[0]['p_year'].'-'.$pr_attachment[0]['p_month'].'-01')));

                $html .= '
                    <div style="page-break-after:always;"></div>

                    <center>
                        <h3>ATTACHMENT</h3>
                    </center>
                ';

                $html .= '
                <table id="customers">

                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2" width="140">DOC NO</th>
                    <th rowspan="2">ORDER TYPE</th>
                    <th rowspan="2">PRODUCT ID</th>
                    <th rowspan="2">PRODUCT NO</th>
                    <th rowspan="2">OST SO</th>
                    <th rowspan="2">STOCK FG</th>

                    <th colspan="3">FORECAST</th>

                    <th rowspan="2">MPQ</th>
                    <th rowspan="2">SHARE ORDER %</th>
                    <th rowspan="2">NEED QTY</th>
                    <th rowspan="2">ORDER QTY</th>
                    <th rowspan="2">SELL PRICE</th>
                    <th rowspan="2">COST PRICE</th>
                    <th rowspan="2">BALANCE</th>
                </tr>

                <tr>
                    <th>'.$monthM1.'</th>
                    <th>'.$monthM2.'</th>
                    <th>50% '.$monthM2.'</th>
                </tr>
                ';

                $noLampiran = 1;
                foreach($pr_attachment as $row){

                    $html .= '
                    <tr>
                        <td>'.$noLampiran++.'</td>
                        <td width="140">'.$row['doc_no'].'</td>
                        <td>'.$row['order_type'].'</td>
                        <td>'.$row['item_fg_id'].'</td>
                        <td>'.$row['item_fg_number'].'</td>
                        <td align="right">'.format_number($row['ost_so']).'</td>
                        <td align="right">'.format_number($row['total_stock']).'</td>
                        <td align="right">'.format_number($row['fc_m1']).'</td>
                        <td align="right">'.format_number($row['fc_m2']).'</td>
                        <td align="right">'.format_number($row['fc_m2_percent']).'</td>
                        <td align="right">'.$row['mpq'].'</td>
                        <td align="right">'.$row['share_order'].'</td>
                        <td align="right">'.format_number($row['need_qty']).'</td>
                        <td align="right">'.format_number($row['order_qty']).'</td>
                        <td align="right">'.format_number($row['selling_price']).'</td>
                        <td align="right">'.format_number($row['cost_price']).'</td>
                        <td align="right">'.format_number($row['balance']).'</td>
                    </tr>';
                }

                $html .= '</table>';
            }

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
            header("Content-Disposition: attachment; filename=po_subcont_productions_$format.xls");
        }


        $filter_from    = $this->input->get('filter_from');
        $filter_to     = $this->input->get('filter_to');
        $filter_subcont_id      = $this->input->get('filter_subcont_id');
        $filter_order_type      = $this->input->get('filter_order_type');
        $filter_po_no           = $this->input->get('filter_po_no');
        $filter_status          = $this->input->get('filter_status');
        $filter_product_no      = $this->input->get('filter_product_no');


        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();


        $this->db->select("
            h.id,
            h.po_no,
            h.pr_no,
            h.po_date,
            h.due_date,
            h.notes,
            h.revision,
            h.total_amount,
            h.status,
            h.created_by,
            h.created_date,
            h.updated_by,
            h.updated_date,
            h.printed,

            s.id as subcont_id,
            s.name as subcont_name,

            COUNT(d.id) as total_detail,
            SUM(CASE WHEN h.approved = 1 THEN 1 ELSE 0 END) as total_approved,
            
            h.approved_by as approved_by,
            h.approved_date as approved_date,
            h.approved_to,
            h.deleted,
            h.status as status_po
        ");
        $this->db->from('po_subcont_productions h');
        $this->db->join(
            'subconts s',
            's.id = h.subcont_id',
            'left'
        );
        $this->db->join(
            'po_subcont_production_details d',
            'd.po_subcont_productions_id = h.id',
            'left'
        );

        if($filter_from != '' && $filter_to != ''){
            $this->db->where('h.po_date >=',$filter_from);
            $this->db->where('h.po_date <=',$filter_to);
        }

        if($filter_subcont_id != ''){
            $this->db->where('h.subcont_id',$filter_subcont_id);
        }
        if($filter_po_no != ''){
            $this->db->where('h.po_no',$filter_po_no);
        }
        if($filter_status != ''){
            $this->db->where('h.status',$filter_status);
        }
        if($filter_product_no != ''){
            $this->db->where('d.item_fg_id',$filter_product_no);
        }
        if($filter_order_type != ''){
            $this->db->where('h.order_type',$filter_order_type);
        }

        $this->db->group_by('h.id');
        $this->db->order_by('h.created_date','DESC');
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
                    <th>PR No</th>
                    <th>PO Date</th>
                    <th>Due Date</th>
                    <th>Subcont Name</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Notes</th>
                    <th>Revision</th>
                    <th>Status Approve</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $data['currency'] = "IDR";

            if ($data['approved_to'] == '') {
                $status_approve = "Approved";
            } else if($data['deleted'] == 2) {
                $status_approve = "Disapprove";
            } else {
                $status_approve = "Checking";
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['pr_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['due_date'] . '</td>
                        <td>' . $data['subcont_name'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format(($data['total_amount']), 2, ",", ".") . '</td>
                        <td>' . $data['notes'] . '</td>
                        <td>' . $data['revision'] . '</td>
                        <td>' . $status_approve . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    function getRowsPerPage($pageNumber)
    {
        if ($pageNumber == 1) {
            return 20; // Set 20 rows for the first page
        } else {
            return 25; // Set 25 rows for subsequent pages
        }
    }
}