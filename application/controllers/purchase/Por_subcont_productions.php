<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Por_subcont_productions extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/por_subcont_productions');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, e.name as supplier_name, d.mpq, d.moq, d.price');
        $this->db->from('por_subcont_productions a');
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

    public function readPoNoOnAddPOR()
    {
        $post = $this->input->post('q');
        $subcont_id = $this->input->get('subcont_id');

        $records = $this->crud->query("
            SELECT
                p.po_no,
                p.po_date
            FROM po_subcont_productions p
            INNER JOIN po_subcont_production_details d
                ON d.po_subcont_productions_id = p.id
                AND d.deleted = 0
            WHERE p.subcont_id = '$subcont_id'
                AND p.deleted = 0
                AND p.approved_to = ''
                AND d.status = 0
                AND (
                    p.po_no LIKE '%$post%'
                    OR DATE_FORMAT(p.po_date, '%Y-%m-%d') LIKE '%$post%'
                )
            GROUP BY p.id, p.po_no, p.po_date
            ORDER BY p.created_date DESC
        ");

        echo json_encode($records);
    }

    public function readItems()
    {
        $po_no = $this->input->get('po_no');
        $search_query = isset($_POST['q']) ? $_POST['q'] : "";

        if (empty($po_no)) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter po_no diperlukan']);
            return;
        }

        
        $query = "
        SELECT
            po.po_no,
            po.po_date,
            po.item_fg_id,
            po.item_fg_number,
            po.item_fg_name,
            po.qty_po,
            po.box_sub,
            po.subcont_id,
            po.uom,
            po.qty_os,
            po.qty_receive,
            po.qty_label,
            po.material
        FROM (
            SELECT
                p.po_no,
                p.po_date,
                d.item_fg_id,
                fg.number AS item_fg_number,
                fg.name AS item_fg_name,
                d.qty AS qty_po,
                fg.box_sub,
                p.subcont_id,
                fg.uom,

                (d.qty - IFNULL(r.qty_receive,0)) AS qty_os,
                (d.qty - IFNULL(r.qty_receive,0)) AS qty_receive,

                CEIL(
                    (d.qty - IFNULL(r.qty_receive,0))
                    / fg.box_sub
                ) AS qty_label,

                rm.number AS material

            FROM po_subcont_productions p

            INNER JOIN po_subcont_production_details d
                ON d.po_subcont_productions_id = p.id
                AND d.deleted = 0

            INNER JOIN item_fg fg
                ON fg.id = d.item_fg_id

            LEFT JOIN bom bm
                ON bm.item_fg_id = d.item_fg_id
                AND bm.priority = 1

            LEFT JOIN item_rm rm
                ON rm.id = bm.item_rm_id

            LEFT JOIN (
                SELECT
                    po_no,
                    item_fg_id,
                    subcont_id,
                    SUM(qty_receive) qty_receive
                FROM por_subcont_productions
                WHERE deleted = 0
                GROUP BY
                    po_no,
                    item_fg_id,
                    subcont_id
            ) r
                ON r.po_no = p.po_no
                AND r.item_fg_id = d.item_fg_id
                AND r.subcont_id = p.subcont_id

            WHERE
                p.po_no = '$po_no'
                AND p.deleted = 0
                AND p.approved_to = ''
                AND d.status = 0

        ) po
        ";

        if (!empty($search_query)) {
            $query .= "
            WHERE
                po.item_fg_number LIKE '%$search_query%'
                OR po.item_fg_name LIKE '%$search_query%'
            ";
        }

        $query .= " ORDER BY po.item_fg_number ASC";

        $records = $this->crud->query($query);
        echo json_encode($records);
    }

    public function readReceive($subcont_id)
    {
        $subcont_id = base64_decode($subcont_id);
        $records = $this->crud->query("SELECT receive_no FROM por_subcont_productions WHERE subcont_id = '$subcont_id' and status = '0' GROUP BY receive_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readProducts($subcont_id)
    {
        $subcont_id = base64_decode($subcont_id);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->db->select('c.id, c.number, c.name');
        $this->db->from('po_subcont_productions a');
        $this->db->join('po_subcont_production_details b', 'a.id = b.po_subcont_productions_id');
        $this->db->join('item_fg c', 'b.item_fg_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.subcont_id', $subcont_id);
        if ($post != "") {
            $this->db->like('c.number', $post);
            $this->db->or_like('c.name', $post);
        }
        $this->db->group_by('c.number');
        $this->db->order_by('c.number', 'asc');
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }

    public function readSubcontDnNo($subcont_id)
    {
        $subcont_id = base64_decode($subcont_id);
        $records = $this->crud->query("SELECT subcont_dn_no FROM por_subcont_productions WHERE subcont_id = '$subcont_id' GROUP BY subcont_dn_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readPoNo($subcont_id)
    {
        $subcont_id = base64_decode($subcont_id);
        $records = $this->crud->query("SELECT a.po_no FROM por_subcont_productions a LEFT JOIN po_subcont_productions b ON a.po_no = b.po_no WHERE a.subcont_id = '$subcont_id' and a.status = 0 GROUP BY a.po_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function generateReceiveNo($date = "")
    {
        if ($date == "") {
            $dateObj = new DateTime();
        } else {
            $dateObj = new DateTime(base64_decode($date));
        }

        $month = $dateObj->format('m');
        $year = $dateObj->format('y');

        // Ambil sequence terakhir pada bulan & tahun yang sama
        $last = $this->db
            ->select('receive_no')
            ->like('receive_no', '-BRI-GR-' . $month . '-' . $year, 'before')
            ->order_by('receive_no', 'DESC')
            ->limit(1)
            ->get('por_subcont_productions')
            ->row();

        if ($last) {
            $sequence = (int) substr($last->receive_no, 0, 3) + 1;
        } else {
            $sequence = 1;
        }

        $receiveNo = sprintf('%03d', $sequence) . '-BRI-GR-' . $month . '-' . $year;

        echo $receiveNo;
    }

    public function readReceiveNo()
    {
        $records = $this->crud->query("SELECT receive_no FROM por_subcont_productions WHERE status = '0' GROUP BY receive_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function receive_no($date = "")
    {
        if ($date == "") {
            $datenow = date("Ymd");
        } else {
            $datenow = date("Ymd", strtotime(base64_decode($date)));
        }
        $sqlGetID   = $this->db->query("SELECT max(receive_no) as kode FROM purchase_order_receives WHERE receive_no like '%$datenow%'");
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
    public function receive_id($receive_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receive_id) as kode FROM por_subcont_productions WHERE receive_id like '%$receive_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $receive_no . "-" . $autoID;
    }

    public function checkLabel($receive_no)
    {
        $receive_no = base64_decode($receive_no);
        $sqlReceive = $this->db->query("SELECT sum(qty_label) as qty_label FROM por_subcont_productions WHERE receive_no ='$receive_no'");
        $rowReceive = $sqlReceive->row();

        $sqlLabel = $this->db->query("SELECT count(label_no) as label_no FROM scan_item_receive WHERE receive_no ='$receive_no'");
        $rowLabel = $sqlLabel->row();

        if (empty(@$rowLabel->label_no)) {
            $label_no = 0;
        } else {
            $label_no = $rowLabel->label_no;
        }

        echo json_encode(["qty_label" => $rowReceive->qty_label, "label_no" => $label_no]);
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

        $filter_from            = $this->input->get('filter_from');
        $filter_to              = $this->input->get('filter_to');
        $filter_subcont_id      = $this->input->get('filter_subcont_id');
        $filter_subcont_dn_no   = $this->input->get('filter_subcont_dn_no');
        $filter_po_no           = $this->input->get('filter_po_no');
        $filter_receive         = $this->input->get('filter_receive');
        $filter_item_fg_id      = $this->input->get('filter_item_fg_id');

        $page   = max(1, (int)$this->input->post('page'));
        $rows   = max(10, (int)$this->input->post('rows'));
        $offset = ($page - 1) * $rows;

        $this->db->select("
            a.receive_no,
            a.receive_id,
            a.receive_date,
            a.po_no,
            a.subcont_dn_no,
            a.subcont_dn_date,
            a.status,
            a.created_by,
            a.created_date,
            a.updated_by,
            a.updated_date,

            b.id as subcont_id,
            b.number as subcont_code,
            b.name as subcont_name,

            SUM(a.qty_receive) as qty_receive,
            SUM(a.qty_label) as qty_label,
            '0' as status_invoice
        ");

        $this->db->from('por_subcont_productions a');
        $this->db->join('subconts b', 'b.id = a.subcont_id', 'left');

        // filter product
        if ($filter_item_fg_id != "") {
            $this->db->join('item_fg c', 'c.id = a.item_fg_id', 'left');
            $this->db->where('c.id', $filter_item_fg_id);
        }

        $this->db->where('a.deleted',0);

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.receive_date >=',$filter_from);
            $this->db->where('a.receive_date <=',$filter_to);
        }

        if ($filter_subcont_id != "") {
            $this->db->where('a.subcont_id',$filter_subcont_id);
        }

        if ($filter_po_no != "") {
            $this->db->where('a.po_no',$filter_po_no);
        }

        if ($filter_receive != "") {
            $this->db->where('a.receive_no',$filter_receive);
        }

        if ($filter_subcont_dn_no != "") {
            $this->db->where('a.subcont_dn_no',$filter_subcont_dn_no);
        }

        $this->db->group_by('a.receive_no');

        $this->db->order_by('a.created_date','DESC');
        $this->db->order_by('a.receive_date','DESC');

        $total = $this->db->count_all_results('', false);

        $this->db->limit($rows,$offset);
        $records = $this->db->get()->result_array();

        $data = [];

        foreach ($records as $row){

            // $scan = $this->db
            //     ->select_sum('status','total_scan')
            //     ->where('receive_id',$row['receive_no'])
            //     ->get('po_subcont_production_labels')
            //     ->row();

            $data[] = [
                'id'              => $row['receive_no'],
                'receive_no'      => $row['receive_no'],
                'receive_date'    => $row['receive_date'],
                'po_no'           => $row['po_no'],
                'subcont_id'      => $row['subcont_id'],
                'subcont_name'    => $row['subcont_name'],
                'subcont_dn_no'   => $row['subcont_dn_no'],
                'subcont_dn_date' => $row['subcont_dn_date'],
                'created_by'      => $row['created_by'],
                'created_date'    => $row['created_date'],
                'updated_by'      => $row['updated_by'],
                'updated_date'    => $row['updated_date'],
                'status'          => $row['status'],
                'status_invoice'  => $row['status_invoice'],
                'qty_label'       => $row['qty_label'],
                // 'total_scan'      => $scan->total_scan ?? 0,
                'total_scan'      => 0,
            ];
        }

        echo json_encode([
            'total' => $total,
            'rows'  => $data
        ]);
    }

    public function datatableDetails()
    {
        $receive_no = base64_decode($this->input->get('receive_no'));
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');

        $this->db->select("
            a.id,
            a.receive_no,
            a.receive_id,
            a.item_fg_id,
            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom,

            a.qty_receive as qty_receive_dt,
            a.box_sub,
            a.qty_label,

            a.compound_lot_no,
            a.production_date,
            a.packing_date,
            a.qc_name,

            a.status,
            '0' as status_invoice
        ");

        $this->db->from('por_subcont_productions a');
        $this->db->join('item_fg b', 'b.id = a.item_fg_id', 'left');

        $this->db->where('a.deleted', 0);
        $this->db->where('a.receive_no', $receive_no);

        if (!empty($filter_item_fg_id)) {
            $this->db->where('b.id', base64_decode($filter_item_fg_id));
        }

        $this->db->order_by('a.receive_id', 'ASC');
        $this->db->order_by('b.number', 'ASC');

        $records = $this->db->get()->result_array();

        $data = [];

        $no = 1;
        foreach ($records as $row) {

            $data[] = [
                'no'               => $no++,
                'receive_id'       => $row['receive_id'],
                'item_fg_id'       => $row['item_fg_id'],
                'item_fg_number'   => $row['item_fg_number'],
                'item_fg_name'     => $row['item_fg_name'],
                'uom'              => $row['uom'],
                'qty_receive_dt'   => $row['qty_receive_dt'],
                'box_sub'          => $row['box_sub'],
                'qty_label'        => $row['qty_label'],
                'compound_lot_no'  => $row['compound_lot_no'],
                'production_date'  => $row['production_date'],
                'packing_date'     => $row['packing_date'],
                'qc_name'          => $row['qc_name'],
                'status'           => $row['status'],
                'status_invoice'   => $row['status_invoice'],
            ];
        }

        echo json_encode($data);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $post['receive_no'] = !empty($post['receive_no']) ? $post['receive_no'] : @$post['receive_no'];
                $post['receive_date'] = !empty($post['receive_date']) ? $post['receive_date'] : @$post['receive_date'];
                $post['qty_receive'] = !empty($post['qty_receive']) ? $post['qty_receive'] : @$post['qty_receive'];
                $post['box_sub'] = !empty($post['box_sub']) ? $post['box_sub'] : @$post['box_sub'];
                $post['compound_lot_no'] = !empty($post['compound_lot_no']) ? $post['compound_lot_no'] : @$post['compound_lot_no'];

                $send = $this->crud->create(
                    'por_subcont_productions',
                    array_merge($post, ["receive_id" => $this->receive_id($post['receive_no'])])
                );

                $this->db->select_sum('qty_receive', 'total_receive');
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_fg_id', $post['item_fg_id']);
                $this->db->where('subcont_id', $post['subcont_id']);
                $this->db->where('deleted', 0);
                $totalRow = $this->db->get('por_subcont_productions')->row();

                $totalReceive = (int) $totalRow->total_receive;

                $po = $this->db
                    ->select('p.id, d.qty')
                    ->from('po_subcont_productions p')
                    ->join('po_subcont_production_details d', 'd.po_subcont_productions_id = p.id AND d.deleted = 0')
                    ->where('p.po_no', $post['po_no'])
                    ->where('p.subcont_id', $post['subcont_id'])
                    ->where('d.item_fg_id', $post['item_fg_id'])
                    ->where('p.deleted', 0)
                    ->get()
                    ->row();

                if (!empty($po)) {
                    $status = ($totalReceive >= (int) $po->qty) ? 1 : 0;

                    $this->db
                        ->where('po_subcont_productions_id', $po->id)
                        ->where('item_fg_id', $post['item_fg_id'])
                        ->update('po_subcont_production_details', ['status' => $status]);
                }


                $this->db->select_sum('qty_receive', 'total_receive');
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('subcont_id', $post['subcont_id']);
                $this->db->where('deleted', 0);

                $totalReceiveHeader = (int)$this->db
                    ->get('por_subcont_productions')
                    ->row()
                    ->total_receive;

                $poHeader = $this->db
                    ->select('
                        p.id,
                        SUM(d.qty) AS total_qty
                    ')
                    ->from('po_subcont_productions p')
                    ->join(
                        'po_subcont_production_details d',
                        'd.po_subcont_productions_id = p.id
                        AND d.deleted = 0'
                    )
                    ->where('p.po_no', $post['po_no'])
                    ->where('p.subcont_id', $post['subcont_id'])
                    ->where('p.deleted', 0)
                    ->group_by('p.id')
                    ->get()
                    ->row();

                if ($poHeader) {

                    $status = ($totalReceiveHeader >= (int)$poHeader->total_qty) ? 1 : 0;

                    $this->db
                        ->where('id', $poHeader->id)
                        ->update('po_subcont_productions', [
                            'status' => $status
                        ]);
                }

                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        if (!$this->input->post()) {
            show_error("Cannot Process your request");
        }

        $post = $this->input->post();

        $this->db->trans_begin();

        try {

            $details = $this->db
                ->select('po_no, subcont_id, item_fg_id')
                ->where('receive_no', $post['id'])
                ->where('deleted', 0)
                ->get('por_subcont_productions')
                ->result();

            $delete = $this->crud->delete('por_subcont_productions', [
                'receive_no' => $post['id']
            ]);

            $this->crud->delete('po_subcont_production_labels', [
                'receive_no' => $post['id']
            ]);

            foreach ($details as $detail) {

                $totalReceive = $this->db
                    ->select_sum('qty_receive', 'total_receive')
                    ->where('po_no', $detail->po_no)
                    ->where('subcont_id', $detail->subcont_id)
                    ->where('item_fg_id', $detail->item_fg_id)
                    ->where('deleted', 0)
                    ->get('por_subcont_productions')
                    ->row()
                    ->total_receive;

                $totalReceive = (int) $totalReceive;

                $po = $this->db
                    ->select('p.id, d.qty')
                    ->from('po_subcont_productions p')
                    ->join(
                        'po_subcont_production_details d',
                        'd.po_subcont_productions_id = p.id AND d.deleted = 0'
                    )
                    ->where('p.po_no', $detail->po_no)
                    ->where('p.subcont_id', $detail->subcont_id)
                    ->where('d.item_fg_id', $detail->item_fg_id)
                    ->where('p.deleted', 0)
                    ->get()
                    ->row();

                if ($po) {

                    $status = ($totalReceive >= (int) $po->qty) ? 1 : 0;

                    $this->db
                        ->where('po_subcont_productions_id', $po->id)
                        ->where('item_fg_id', $detail->item_fg_id)
                        ->update('po_subcont_production_details', [
                            'status' => $status
                        ]);
                }
            }


            if (!empty($details)) {

                $poNo = $details[0]->po_no;
                $subcontId = $details[0]->subcont_id;

                $totalReceiveHeader = (int) $this->db
                    ->select_sum('qty_receive', 'total_receive')
                    ->where('po_no', $poNo)
                    ->where('subcont_id', $subcontId)
                    ->where('deleted', 0)
                    ->get('por_subcont_productions')
                    ->row()
                    ->total_receive;

                $poHeader = $this->db
                    ->select('
                        p.id,
                        SUM(d.qty) AS total_qty
                    ')
                    ->from('po_subcont_productions p')
                    ->join(
                        'po_subcont_production_details d',
                        'd.po_subcont_productions_id = p.id
                        AND d.deleted = 0'
                    )
                    ->where('p.po_no', $poNo)
                    ->where('p.subcont_id', $subcontId)
                    ->where('p.deleted', 0)
                    ->group_by('p.id')
                    ->get()
                    ->row();

                if ($poHeader) {

                    $status = ($totalReceiveHeader >= (int) $poHeader->total_qty) ? 1 : 0;

                    $this->db
                        ->where('id', $poHeader->id)
                        ->update('po_subcont_productions', [
                            'status' => $status
                        ]);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed delete receive.');
            }

            $this->db->trans_commit();

            echo $delete;

        } catch (Exception $e) {

            $this->db->trans_rollback();

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function acquireRfgLabelSequenceLock()
    {
        $lock = $this->db
            ->query("SELECT GET_LOCK('rfg_label_sequence_lock', 10) AS locked")
            ->row();

        return !empty($lock) && (int) $lock->locked === 1;
    }

    private function releaseRfgLabelSequenceLock()
    {
        $this->db->query("SELECT RELEASE_LOCK('rfg_label_sequence_lock')");
    }

    private function generateRfgSerialLabel($prod_date, $shift, $item_fg_id, $prefix, $productNo)
    {
        while (true) {
            $last = $this->db
                ->query("
                    SELECT MAX(sequence_no) AS sequence_no
                    FROM (
                        SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(serial_label, '|', 4), '|', -1) AS UNSIGNED) AS sequence_no
                        FROM po_subcont_production_labels
                        WHERE deleted = 0
                            AND prod_date = ?
                            AND shift = ?
                            AND item_fg_id = ?
                            AND serial_label LIKE ?

                        UNION ALL

                        SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(serial_label, '|', 4), '|', -1) AS UNSIGNED) AS sequence_no
                        FROM fg_visual_checker_label
                        WHERE deleted = 0
                            AND prod_date = ?
                            AND shift = ?
                            AND item_fg_id = ?
                            AND serial_label LIKE ?
                    ) labels
                ", [
                    $prod_date,
                    $shift,
                    $item_fg_id,
                    $prefix . '|%',
                    $prod_date,
                    $shift,
                    $item_fg_id,
                    $prefix . '|%'
                ])
                ->row();

            $sequence = !empty($last->sequence_no) ? ((int) $last->sequence_no + 1) : 1;
            $serial_label = implode('|', [$prefix, sprintf('%03d', $sequence), $productNo]);

            $exists_po = $this->db
                ->where('serial_label', $serial_label)
                ->where('deleted', 0)
                ->get('po_subcont_production_labels')
                ->row();

            $exists_fg = $this->db
                ->where('serial_label', $serial_label)
                ->where('deleted', 0)
                ->get('fg_visual_checker_label')
                ->row();

            if (!$exists_po && !$exists_fg) {
                return $serial_label;
            }
        }
    }

    public function print_label_rfg_2($receive_id, $item_fg_id = "")
    {
        $this->print_label_rfg($receive_id, $item_fg_id, "receive_id");
    }

    public function print_label_rfg($receive_no, $item_fg_id = "", $filter_by = "receive_no")
    {
        $receive_no = base64_decode($receive_no);
        $item_fg_id = !empty($item_fg_id) ? base64_decode($item_fg_id) : "";
        $filter_column = $filter_by === "receive_id" ? "receive_id" : "receive_no";

        if (empty($receive_no)) {
            show_error("Missing parameter", 400);
        }

        $this->db->trans_begin();

        $this->db->select("
            a.receive_no,
            a.receive_id,
            a.item_fg_id,
            a.qty_receive AS qty_packing,
            a.box_sub AS std_packing,
            a.compound_lot_no,
            a.production_date AS prod_date,
            a.packing_date AS pack_date,
            a.shift,
            a.qc_name AS qc,

            b.number AS product_no,
            b.name AS product_name,
            b.uom,

            d.number AS material_no
        ");
        $this->db->from("por_subcont_productions a");
        $this->db->join("item_fg b", "b.id = a.item_fg_id", "left");
        $this->db->join("bom c", "c.item_fg_id = a.item_fg_id AND c.priority = 1", "left");
        $this->db->join("item_rm d", "d.id = c.item_rm_id", "left");
        $this->db->where("a." . $filter_column, $receive_no);
        $this->db->where("a.deleted", 0);

        if (!empty($item_fg_id)) {
            $this->db->where("a.item_fg_id", $item_fg_id);
        }

        $this->db->order_by("a.receive_id", "ASC");
        $label_packing_details = $this->db->get()->result();

        if (empty($label_packing_details)) {
            $this->db->trans_rollback();
            echo "<center><h3>Data not foundss</h3></center>";
            return;
        }

        $existing_labels_query = $this->db
            ->select("
                l.*,
                a.receive_no,
                a.receive_id,
                a.box_sub AS std_packing,
                a.qc_name AS qc,
                b.number AS product_no,
                b.name AS product_name,
                b.uom,
                d.number AS material_no
            ")
            ->from("po_subcont_production_labels l")
            ->join("por_subcont_productions a", "a.receive_id = l.receive_id AND a.item_fg_id = l.item_fg_id", "left")
            ->join("item_fg b", "b.id = l.item_fg_id", "left")
            ->join("bom c", "c.item_fg_id = l.item_fg_id AND c.priority = 1", "left")
            ->join("item_rm d", "d.id = c.item_rm_id", "left")
            ->where("l." . $filter_column, $receive_no)
            ->where("l.deleted", 0);

        if (!empty($item_fg_id)) {
            $existing_labels_query->where("l.item_fg_id", $item_fg_id);
        }

        $existing_print_labels = $existing_labels_query
            ->order_by("l.created_date", "ASC")
            ->order_by("l.id", "ASC")
            ->get()
            ->result();

        if (empty($existing_print_labels)) {
            $shiftCode = [
                '1' => 'A',
                '2' => 'B',
                '3' => 'C'
            ];
            $created_count = 0;

            if (!$this->acquireRfgLabelSequenceLock()) {
                $this->db->trans_rollback();
                show_error("Failed to acquire label sequence lock", 500);
            }

            foreach ($label_packing_details as $detail) {
                $std_packing = (int) $detail->std_packing;
                $remaining_qty = (int) $detail->qty_packing;

                if ($std_packing <= 0 || $remaining_qty <= 0) {
                    continue;
                }

                while ($remaining_qty > 0) {
                    $qty = min($std_packing, $remaining_qty);
                    $shift = isset($shiftCode[$detail->shift]) ? $shiftCode[$detail->shift] : $detail->shift;
                    $dateCode = date('dmY', strtotime($detail->prod_date));
                    $productNo = !empty($detail->product_no) ? $detail->product_no : $detail->item_fg_id;
                    $prefix = implode('|', [$dateCode, $shift, (int) $std_packing]);
                    $serial_label = $this->generateRfgSerialLabel($detail->prod_date, $shift, $detail->item_fg_id, $prefix, $productNo);

                    $insert = [
                        'receive_no'       => $detail->receive_no,
                        'receive_id'       => $detail->receive_id,
                        'item_fg_id'       => $detail->item_fg_id,
                        'prod_date'        => $detail->prod_date,
                        'shift'            => $shift,
                        'pack_date'        => $detail->pack_date,
                        'qty'              => $qty,
                        'compound_lot_no'  => $detail->compound_lot_no,
                        'serial_label'     => $serial_label,
                        'status'           => 0
                    ];

                    $this->crud->create('po_subcont_production_labels', $insert);
                    $this->createQrcode($serial_label, "assets/image/qrcode/");
                    $created_count++;

                    $remaining_qty -= $qty;
                }
            }

            if ($created_count === 0) {
                $this->db->trans_rollback();
                $this->releaseRfgLabelSequenceLock();
                show_error("No label created", 400);
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $this->releaseRfgLabelSequenceLock();
                show_error("Failed to create label", 500);
            }

            $this->db->trans_commit();
            $this->releaseRfgLabelSequenceLock();

            if ($filter_column === "receive_id") {
                redirect('purchase/por_subcont_productions/print_label_rfg_2/' . base64_encode($receive_no) . (!empty($item_fg_id) ? '/' . base64_encode($item_fg_id) : ''));
                return;
            }

            redirect('purchase/por_subcont_productions/print_label_rfg/' . base64_encode($receive_no) . (!empty($item_fg_id) ? '/' . base64_encode($item_fg_id) : ''));
            return;
        }

        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $receive_no . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }
                            table { 
                                border-collapse: collapse; 
                                width: 7.5cm; 
                                height: 8cm; 
                                font-size: 11px;
                                border: 2px solid black; 
                                table-layout: fixed; 
                            }
                            th, td { 
                                border: 1px solid black; 
                                padding: 2px; 
                                text-align: left; 
                            }
                            th { 
                                text-align: center; 
                                font-size: 14px; 
                                font-weight: bold; 
                            }
                            .header { 
                                text-align: center; 
                                font-size: 15px; 
                                font-weight: bold; 
                            }
                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding: 3px; 
                            }
                            .operator-sign, 
                            .qc-sign, 
                            .qr-code { 
                                font-size: 12px; 
                                text-align: center; 
                                height: 20mm; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 20mm; 
                            }
                            .qr-code img { 
                                width: 60px; 
                                height: 60px; 
                                display: block; 
                                margin: 0 auto; 
                            }
                            .serial-label { 
                                font-size: 11px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                    size: 7.5cm 8cm;
                                    margin: 0;
                            }
                            @media print {
                                    .printLabel {
                                        page-break-after: always;
                                        width: 7.5cm;
                                        height: 8cm;
                                        display: block;
                                        padding: 0mm;
                                        margin: 0;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 12px;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    body {
                                        margin: 0;
                                        padding: 0;
                                    }
                                }
                        </style>
                    </head>
                <body>';

        foreach ($existing_print_labels as $label) {
            $serial_label = $label->serial_label;

            if (!file_exists(FCPATH . 'assets/image/qrcode/' . $serial_label . '.png')) {
                $this->createQrcode($serial_label, "assets/image/qrcode/");
            }

            $material = $label->material_no;
            $qty_packing_formatted = number_format($label->qty, 0, ',', '.') . ' ' . strtoupper($label->uom);
            $serial_label_display = preg_replace('/^((?:[^|]*\|){3}[^|]*).*/', '$1', $serial_label);

            $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                <span class="header" style="font-size: 20px; height: 20px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $label->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $label->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $material . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $label->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $label->pack_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $label->compound_lot_no . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $label->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $serial_label . '.png') . '"/>
                                <div class="serial-label">' . $serial_label_display . '</div>
                            </td>
                        </tr>
                    </table>
            </div>';
        }

        $this->db->trans_commit();

        $html .= '<script>window.print()</script>
                </body>
            </html>';

        die($html);
    }

    public function print_receiving($receive_no)
    {
        $receive_no = base64_decode($receive_no);

        $purchase_order_receive_total = $this->crud->reads('por_subcont_productions', [], ["receive_no" => $receive_no]);
        $po_receive = $this->crud->read('por_subcont_productions', [], ["receive_no" => $receive_no]);

        $plant = 'RUBBER PART';

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->createQrcode($po_receive->created_by, "assets/image/qrcode/");
        
        $rows = 12;
        $page = ceil(count($purchase_order_receive_total) / $rows);

        $this->createQrcode($po_receive->receive_no, "assets/image/qrcode/");

        $this->db->select("
            a.*,
            b.name subcont_name,
            c.number item_fg_number,
            c.name item_fg_name,
            d.name item_category_name,
            c.mpq,
            c.uom,
            SUM(a.qty_receive) qty_receive
        ");

        $this->db->from("por_subcont_productions a");
        $this->db->join("subconts b", "b.id=a.subcont_id", "left");
        $this->db->join("item_fg c", "c.id=a.item_fg_id", "left");
        $this->db->join('item_categories d', 'c.item_category_number = d.number', 'left');

        $this->db->where("a.deleted",0);
        $this->db->where("a.receive_no",$receive_no);

        $this->db->group_by("a.po_no");
        $this->db->group_by("a.subcont_id");
        $this->db->group_by("a.item_fg_id");

        $records=$this->db->get()->result_array();

        if ($records) {
            $html = '<html>
                        <head>
                            <title>' . $po_receive->receive_no . '</title>
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

                $this->db->select("
                    a.*,
                    b.name subcont_name,
                    c.number item_fg_number,
                    c.name item_fg_name,
                    d.name item_category_name,
                    c.mpq,
                    c.uom,
                    SUM(a.qty_receive) qty_receive
                ");

                $this->db->from("por_subcont_productions a");
                $this->db->join("subconts b", "b.id=a.subcont_id", "left");
                $this->db->join("item_fg c", "c.id=a.item_fg_id", "left");
                $this->db->join('item_categories d', 'c.item_category_number = d.number', 'left');

                $this->db->where("a.deleted",0);
                $this->db->where("a.receive_no",$receive_no);

                $this->db->group_by("a.po_no");
                $this->db->group_by("a.subcont_id");
                $this->db->group_by("a.item_fg_id");

                $records=$this->db->get()->result_array();

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
                                                    <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $po_receive->receive_no . '.png') . '" width="60"/></td>
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
                                                <td width="100">Receive No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receive->receive_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Receive Date</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receive->receive_date . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:45%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="50">Supplier</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['subcont_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Doc. No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['subcont_dn_no'] . '</b></td>
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
                                                <th>Product No</th>
                                                <th>Product Name</th>
                                                <th>Category</th>
                                                <th>MPQ</th>
                                                <th>Quantity</th>
                                                <th>Uom</th>
                                            </tr>';
                $no = 1;
                foreach ($records as $record) {
                    $html .= '  <tr>
                    <td style="text-align:center">' . $no . '</td>
                    <td>' . $record['po_no'] . '</td>
                    <td>' . $record['item_fg_number'] . '</td>
                    <td>' . $record['item_fg_name'] . '</td>
                    <td>' . $record['item_category_name'] . '</td>
                    <td style="text-align:right">' . number_format($record['mpq'], 0, ',', '.') . '</td>
                    <td style="text-align:right">' . number_format($record['qty_receive'], 0, ',', '.') . '</td>
                    <td>' . $record['uom'] . '</td>
                </tr>';
                    $no++;
                }
                $html .= '  </table>
                            <table id="customers" style="margin-top:20px; width:15%; margin-left: 85%;">
                                <tr>
                                    <th width="100" style="text-align:center;">Receive By</th>
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
            header("Content-Disposition: attachment; filename=por_subcont_productions_$format.xls");
        }

        $filter_from            = $this->input->get('filter_from');
        $filter_to              = $this->input->get('filter_to');
        $filter_subcont_id      = $this->input->get('filter_subcont_id');
        $filter_subcont_dn_no   = $this->input->get('filter_subcont_dn_no');
        $filter_po_no           = $this->input->get('filter_po_no');
        $filter_receive         = $this->input->get('filter_receive');
        $filter_item_fg_id      = $this->input->get('filter_item_fg_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("
            a.receive_no,
            a.receive_date,
            a.po_no,
            a.subcont_dn_no,
            a.subcont_dn_date,
            a.compound_lot_no,
            a.status,
            a.created_by,
            a.created_date,
            a.updated_by,
            a.updated_date,

            b.id as subcont_id,
            b.number as subcont_code,
            b.name as subcont_name,

            c.id as item_fg_id,
            c.number as item_fg_number,
            c.name as item_fg_name,
            c.uom,

            'IDR' as currency,

            SUM(a.qty_receive) as qty_receive,
            SUM(a.qty_label) as qty_label
        ");

        $this->db->from('por_subcont_productions a');
        $this->db->join('subconts b', 'b.id = a.subcont_id', 'left');
        $this->db->join('item_fg c', 'c.id = a.item_fg_id', 'left');

        $this->db->where('a.deleted', 0);

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.receive_date >=', $filter_from);
            $this->db->where('a.receive_date <=', $filter_to);
        }

        if ($filter_subcont_id != "") {
            $this->db->where('a.subcont_id', $filter_subcont_id);
        }

        if ($filter_po_no != "") {
            $this->db->where('a.po_no', $filter_po_no);
        }

        if ($filter_receive != "") {
            $this->db->where('a.receive_no', $filter_receive);
        }

        if ($filter_subcont_dn_no != "") {
            $this->db->where('a.subcont_dn_no', $filter_subcont_dn_no);
        }

        $this->db->group_by('a.receive_no');
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.subcont_id');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.compound_lot_no');

        $this->db->order_by('a.receive_no', 'ASC');
        $this->db->order_by('c.number', 'ASC');

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
                                <small>PURCHASE ORDER RECEIVE</small>
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
                <th>Receive No</th>
                <th style="text-align:center;">Subcont Code</th>
                <th style="text-align:center;">Subcont Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Compound Lot No</th>
                <th>Qty</th>
                <th>UoM</th>
                <th>Currency</th>
                <th>Label</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['receive_no'] . '</td>
                        <td>' . $data['subcont_id'] . '</td>
                        <td>' . $data['subcont_name'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['compound_lot_no'] . '</td>
                        <td>' . number_format($data['qty_receive'], 0, ',', '.') . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['qty_label'], 0, ',', '.') . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
