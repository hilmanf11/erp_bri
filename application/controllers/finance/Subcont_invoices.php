<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Subcont_invoices extends CI_Controller
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
            $this->load->view('finance/subcont_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function readPeriod($select)
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

    public function readWorkorderLabels()
    {
        $q = $this->input->get('q');
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");

        $this->db->select('a.workorder_label');
        $this->db->from('shipping_to_subconts a');

        $this->db->join(
            'subcont_invoices b',
            'a.scan_id = b.scan_id
            AND a.workorder = b.workorder',
            'inner'
        );

        $this->db->where('a.type_status', 'completed');
        $this->db->where('a.workorder_label IS NOT NULL', null, false);
        $this->db->where('a.workorder_label !=', '');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('b.delivery_date >=', $filter_from);
            $this->db->where('b.delivery_date <=', $filter_to);
        }

        if ($q != '') {
            $this->db->like('a.workorder_label', $q);
        }

        $this->db->order_by('a.workorder_label', 'ASC');

        echo json_encode(
            $this->db->get()->result_array()
        );
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

    public function number($delivery_note_date, $divison_number)
    {
        $divison_number = base64_decode($divison_number);
        $customer_number = base64_decode($this->input->post('customer_number'));

        $numberCust = $customer_number;
        $divisions  = "DN". $divison_number;
        $datenow    = date("my", strtotime(base64_decode($delivery_note_date)));
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 7, 4) as kode FROM subcont_invoices WHERE `delivery_note_no` like '%$dn_no%'");
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

    public function readDeliveryNoteNo()
    {
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $delivery_to = $this->input->get("delivery_to");

        $this->db->distinct();
        $this->db->select('delivery_note_no');
        $this->db->from('subcont_invoices');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('delivery_date >=', $filter_from);
            $this->db->where('delivery_date <=', $filter_to);
        }
        if (!empty($delivery_to)) {
            $this->db->where('delivery_to', $delivery_to);
        }

        $this->db->order_by('delivery_note_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }

    public function delivery_note_no($type = "")
    {
        $trans_date = $this->input->post('trans_date');
        $destination_code = $this->input->post('destination_code');

        $date = $trans_date ? date("Y-m-d", strtotime($trans_date)) : date("Y-m-d");
        $month = date("m", strtotime($date));
        $year = date("y", strtotime($date));

        $period_start = date("Y-m-01", strtotime($date));
        $period_end   = date("Y-m-t", strtotime($date));

        $sql = $this->db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(delivery_note_no, '/', 1) AS UNSIGNED)) AS kode
            FROM subcont_invoices
            WHERE delivery_date BETWEEN '{$period_start}' AND '{$period_end}'
            AND delivery_note_no LIKE '%/{$destination_code}/BRI/{$month}/{$year}'
        ");

        $row = $sql->row();

        if ($row->kode == null) {
            $seq = "001";
        } else {
            $seq = sprintf("%03s", intval($row->kode) + 1);
        }

        $autonumber = "{$seq}/{$destination_code}/BRI/{$month}/{$year}";

        if ($type == "return") {
            return $autonumber;
        }

        echo $autonumber;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_delivery_to = @base64_decode($get['filter_delivery_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
            $filter_workorder_label = @base64_decode($get['filter_workorder_label']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select("
                a.*, 
                COALESCE(c.name, d.name) as destination_name,
                t.total_qty_delivery,
                CASE
                    WHEN s.cnt_over > 0 THEN '3'
                    WHEN s.cnt_closed = s.total_row THEN '1'
                    WHEN s.cnt_open = s.total_row THEN '0'
                    ELSE '2'
                END AS status_header
            ", false);

            $this->db->from('subcont_invoices a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.destination = c.id', 'left');
            $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');

            // Jika ingin header tetap
            $this->db->join("(
                SELECT
                    delivery_note_no,
                    SUM(qty_delivery) AS total_qty_delivery
                FROM subcont_invoices
                GROUP BY delivery_note_no
            ) t", "t.delivery_note_no = a.delivery_note_no", "left");


            $this->db->join("(
                SELECT
                    d.delivery_note_no,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) > d.qty_delivery THEN 1 ELSE 0 END) AS cnt_over,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) > 0 
                            AND COALESCE(i.qty_incoming,0) < d.qty_delivery THEN 1 ELSE 0 END) AS cnt_ongoing,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) = d.qty_delivery THEN 1 ELSE 0 END) AS cnt_closed,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) = 0 THEN 1 ELSE 0 END) AS cnt_open,
                    COUNT(*) AS total_row
                FROM (
                    SELECT
                        delivery_note_no,
                        item_fg_id,
                        workorder,
                        SUM(qty_delivery) AS qty_delivery
                    FROM subcont_invoices
                    GROUP BY delivery_note_no, item_fg_id, workorder
                ) d
                LEFT JOIN (
                    SELECT
                        delivery_note_no,
                        item_fg_id,
                        workorder,
                        SUM(qty) AS qty_incoming
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Regular'
                    AND type_status = 'completed'
                    GROUP BY delivery_note_no, item_fg_id, workorder
                ) i ON i.delivery_note_no = d.delivery_note_no
                AND i.item_fg_id = d.item_fg_id
                AND i.workorder = d.workorder
                GROUP BY d.delivery_note_no
            ) s", "s.delivery_note_no = a.delivery_note_no", "left");

            if ($filter_workorder_label != "") {

                $this->db->join(
                    'shipping_to_subconts sw',
                    'sw.scan_id = a.scan_id
                    AND sw.item_fg_id = a.item_fg_id
                    AND sw.workorder = a.workorder
                    AND sw.type_status = "completed"',
                    'left'
                );

                $this->db->where('sw.workorder_label', $filter_workorder_label);
            }

            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_date >=', $filter_from);
                $this->db->where('a.delivery_date <=', $filter_to);
            }
            if ($filter_delivery_to != "") {
                $this->db->where('a.delivery_to', $filter_delivery_to);
            }
            if ($filter_item_fg != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            $this->db->group_by('a.delivery_note_no');

            $this->db->order_by('a.delivery_date', 'ASC');
            $this->db->order_by('a.delivery_note_no', 'ASC');
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

    public function datatableDetails()
    {
        if ($this->input->get()) {

            // $footer = [[
            //     'debit_credit'       => 'SUB TOTAL',
            //     'total_qty_incoming' => $total_qty,
            //     'total_amount'       => $total_amount
            // ]];

            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));
            $workorder_label = base64_decode($this->input->get('workorder_label'));

            $this->db->select("
                d.item_fg_id,
                d.workorder,
                d.prod_date,
                d.qty_delivery,
                COALESCE(i.qty_incoming, 0) AS qty_incoming,
                (d.qty_delivery - COALESCE(i.qty_incoming, 0)) AS qty_outstanding,
                d.remarks,
                f.number AS item_fg_number,
                f.name AS item_fg_name,
                f.uom,

                CASE
                    WHEN COALESCE(i.qty_incoming, 0) = 0 THEN '0'
                    WHEN COALESCE(i.qty_incoming, 0) > 0 
                        AND COALESCE(i.qty_incoming, 0) < d.qty_delivery THEN '2'
                    WHEN COALESCE(i.qty_incoming, 0) = d.qty_delivery THEN '1'
                    WHEN COALESCE(i.qty_incoming, 0) > d.qty_delivery THEN '3'
                END AS status_incoming
            ", false);

            $this->db->from("(
                SELECT
                    scan_id,
                    delivery_note_no,
                    item_fg_id,
                    workorder,
                    MAX(prod_date) AS prod_date,
                    SUM(qty_delivery) AS qty_delivery,
                    MAX(remarks) AS remarks
                FROM subcont_invoices
                WHERE delivery_note_no = ".$this->db->escape($delivery_note_no)."
                AND deleted = 0
                AND qty_delivery > 0
                GROUP BY delivery_note_no, item_fg_id, workorder
            ) d");

            $this->db->join('item_fg f', 'd.item_fg_id = f.id');

            $this->db->join(
                "(
                    SELECT
                        delivery_note_no,
                        item_fg_id,
                        workorder,
                        SUM(qty) AS qty_incoming
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Regular'
                    AND type_status = 'completed'
                    GROUP BY delivery_note_no, item_fg_id, workorder
                ) i",
                "i.delivery_note_no = d.delivery_note_no
                AND i.item_fg_id = d.item_fg_id
                AND i.workorder = d.workorder",
                'left'
            );

            $this->db->join(
                'shipping_to_subconts s',
                's.scan_id = d.scan_id
                AND s.workorder = d.workorder
                AND s.type_status = "completed"',
                'left'
            );

            if ($workorder_label != '') {
                $this->db->where('s.workorder_label', $workorder_label);
            }

            $this->db->group_by('d.scan_id, d.workorder');
            $this->db->order_by('d.prod_date', 'ASC');
            $this->db->order_by('d.workorder', 'ASC');

            echo json_encode($this->db->get()->result_array());
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('subcont_invoices a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $dataFinal = array(
                "item_fg_id" => $post['item_fg_id'],
                "internal_doc_no" => $post['internal_doc_no'],
                "prod_date" => $post['prod_date'],
                "workorder" => $post['workorder'],
                "qty_output" => $post['qty_output'],
                "qty_delivery" => $post['qty_delivery'],
                "source_type" => $post['source_type'],
                "remarks" => $post['remarks'],
            );

            if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                $post['workorder'] = null;
            }

            if (@$post['id'] != "") {
                $send = $this->crud->update('subcont_invoices', ["id" => $post['id']], $dataFinal);
            } else {
                $send = $this->crud->create('subcont_invoices', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        $send = $this->crud->delete('subcont_invoices', $data);
        echo $send;
    }


    public function deleteAll()
    {
        $data = $this->input->post();

        $delivery_note_no = $data['delivery_note_no'];
        $scan_id          = $data['scan_id'];

        if(empty($scan_id)) {
            echo json_encode([
                'title'   => 'Failed Delete',
                'message' => 'Scan ID is empty!',
                'theme'   => 'error'
            ]);
            return;

        }

        $this->db->select("a.item_fg_id, a.workorder, a.delivery_note_no");
        $this->db->from('subcont_invoices a');
        $this->db->where('a.delivery_note_no', $delivery_note_no);
        $this->db->where('a.scan_id', $scan_id);
        $this->db->where('a.approved_to', ''); // sudah di-approve

        $checkApproval = $this->db->get()->row_array();

        if (!empty($checkApproval)) {
            echo json_encode([
                'title'   => 'Failed Delete',
                'message' => 'Cannot be deleted because it has been approved',
                'theme'   => 'error'
            ]);
            return;
        }

        $this->db->select("a.scan_id");
        $this->db->from('subcont_invoices a');
        $this->db->join(
            'shipping_to_subconts b',
            'a.scan_id = b.scan_id'
        );
        $this->db->where('a.delivery_note_no', $delivery_note_no);
        $this->db->where('a.scan_id', $scan_id);

        $row = $this->db->get()->row_array();

        if (empty($row)) {
            echo json_encode([
                'title'   => 'Failed Delete',
                'message' => 'Data not found',
                'theme'   => 'error'
            ]);
            return;
        }

        $this->db->trans_begin();

        $this->crud->delete('subcont_invoices', [
            'delivery_note_no' => $delivery_note_no,
            'scan_id'          => $scan_id
        ]);

        $this->db->select("a.item_fg_id, a.workorder, a.workorder_label");
        $this->db->from('shipping_to_subconts a');
        $this->db->where('a.scan_id', $scan_id);

        $checkShippingToSB = $this->db->get()->result_array();

        foreach ($checkShippingToSB as $item) {
            if($item['item_fg_id'] == "FGRPNA-0207") {

                $this->crud->update(
                    'output_production_press_detail',
                    [
                        'workorder_label' => $item['workorder_label'],
                        'status'          => 1
                    ],
                    [
                        'status' => 2
                    ]
                );

            } else {
                $this->crud->update(
                    'output_production_press_detail',
                    [
                        'workorder_label' => $item['workorder_label'],
                        'status'          => 1
                    ],
                    [
                        'status' => 0
                    ]
                );
            }
        }

        $this->crud->delete('shipping_to_subconts', [
            'scan_id'         => $scan_id,
            'type_status'     => 'completed'
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            echo json_encode([
                'title'   => 'Failed',
                'message' => 'Delete failed, transaction rolled back',
                'theme'   => 'error'
            ]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode([
            'title'   => 'Success',
            'message' => 'Data deleted successfully',
            'theme'   => 'success'
        ]);
    }

    public function print_dn_to_sc($delivery_note_no)
    {
        $delivery_note_no = base64_decode($delivery_note_no);

        $this->db->query("UPDATE subcont_invoices SET printed=1 WHERE `delivery_note_no` = '$delivery_note_no'");

        $subcont_invoices = $this->crud->reads('subcont_invoices', [], ["delivery_note_no" => $delivery_note_no]);

        $delivery_to_subcont = $this->crud->read('subcont_invoices', [], ["delivery_note_no" => $delivery_note_no]);

        // $user = $this->crud->read('users', [], ["username" => $delivery_to_subcont->created_by]);
        $table_approval = 'subcont_invoices';

        $approval=$this->db->query("
            SELECT *, CASE 
                WHEN user_approval_1 = '$delivery_to_subcont->approved_by' THEN '1'
                WHEN user_approval_2 = '$delivery_to_subcont->approved_by' THEN '2'
                ELSE '0' 
                END AS approved_by 
            FROM approvals 
            WHERE table_name = '$table_approval'
        ");

        $sqlApproval = $approval->row();

        $user1 = null;
        $user2 = null;

        if(intval($sqlApproval->approved_by) == 2){
            $user1 = $sqlApproval->user_approval_1;
            $user2 = $sqlApproval->user_approval_2;
        }

        if(intval($sqlApproval->approved_by) == 1){
            $user1 = $sqlApproval->user_approval_1;
        }

        // Inisialisasi variabel approval
        $approval_1 = null;
        $approval_2 = null;
        $created_date = $delivery_to_subcont->created_date;

        if($user1 != null){
            // Cek status approval pertama
            $approval_1 = $this->crud->read('users', [], ["username" => $user1]);
        }

        if($user2 != null){
            // Cek status approval kedua
            $approval_2 = $this->crud->read('users', [], ["username" => $user2]);
        }

        $config = $this->db->get('config')->row();

        // Tentukan ukuran kertas dan jumlah baris per halaman
        if (count($subcont_invoices) <= 5) {
            $rows_per_page = 5; // Maksimal 5 produk per halaman
        } else {
            $rows_per_page = 10; // Maksimal 10 produk per halaman
        }


        $month = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];


        // Generate QR code untuk approval 1 jika sudah diapprove
        if ($approval_1) {
            $this->createQrcode($approval_1->name, "assets/image/qrcode/");
        }

        if ($approval_2) {
            $this->createQrcode($approval_2->name, "assets/image/qrcode/");
        }

        //Header Print
        $html = '<html><head><title>' . $delivery_to_subcont->delivery_note_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="14x14"></head>';
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
        // $page = ceil(count($subcont_invoices) / $rows_per_page);
        // for ($i = 0; $i < $page; $i++) {

            $this->db->select('
                a.*, 
                b.number as item_fg_number, 
                b.name as item_fg_name, 
                b.uom, 
                COALESCE(c.name, d.name) as destination_name, 
                COALESCE(c.address, d.address) as address, 
                h.name as created_by_name,
                COALESCE(e.qty_label, 0) AS qty_packing
            ');
            $this->db->from('subcont_invoices a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.destination = c.id', 'left');
            $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');

            $this->db->join("(
                SELECT 
                    scan_id,
                    item_fg_id,
                    workorder,
                    COUNT(*) AS qty_label
                FROM shipping_to_subconts
                WHERE type_status = 'completed'
                GROUP BY scan_id, item_fg_id, workorder
            ) e", '
                e.scan_id = a.scan_id
                AND e.item_fg_id = a.item_fg_id
                AND e.workorder = a.workorder
            ', 'left');

            $this->db->where('a.delivery_note_no', $delivery_note_no);
            $this->db->order_by('a.workorder', 'asc');
            $this->db->join('users h', 'a.created_by = h.username');
            $this->db->order_by('b.number', 'asc');
            // $this->db->limit($rows_per_page, ($i * $rows_per_page));
            $records = $this->db->get()->result_array();

            // Pastikan QR code untuk approval dan created by sudah dibuat
            $this->createQrcode($records[0]['created_by_name'], "assets/image/qrcode/");

            $delivery_date = @$records[0]['delivery_date'] ?? date('Y-m-d');
            $timestamp = strtotime($delivery_date);

            $day = date('d', $timestamp);
            $monthIndo = $month[(int)date('m', $timestamp)];
            $year = date('Y', $timestamp);

            $date_delivery = "$day $monthIndo $year";


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
                                    <div style="float:left; width:50%;">
                                        <table style="width:100%; font-size:9pt; font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; margin-bottom:10px;font-weight: bold;">
                                            <tr>
                                                <td width="150px">Category</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['delivery_category'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="150px">Delivery Note No</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['delivery_note_no'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="150px">Delivery Date</td>
                                                <td width="10px">:</td>
                                                <td><b>' . date("Y-m-d", strtotime(@$records[0]['delivery_date'])) . '</b></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div style="float:left; width:50%;">
                                        <table style="width:100%; font-size:9pt; font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; margin-bottom:100px;font-weight: bold;">
                                            <tr>
                                                <td width="150px">Destination</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['destination_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="150px">Address</td>
                                                <td width="10px">:</td>
                                                <td><b>' . @$records[0]['address'] . '</b></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div style="clear: both; height: 20px;"></div>
                                    <table id="customers">
                                        <tr>
                                            <th width="20px">No</th>
                                            <th>Product No</th>
                                            <th>Product Name</th>
                                            <th>WO No</th>
                                            <th>Qty Packing</th>
                                            <th>Qty Delivery</th>
                                            <th>Remarks</th>
                                        </tr>';
            $total_qty_delivery = 0;
            $total_qty_packing = 0;

            foreach ($records as $record) {
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record['item_fg_number'] . '</td>
                            <td>' . $record['item_fg_name'] . '</td>
                            <td>' . $record['workorder'] . '</td>
                            <td style="text-align:center">' . number_format($record['qty_packing'], 0, ",", ".") . '</td>
                            <td style="text-align:center">' . number_format($record['qty_delivery'], 0, ",", ".") . '</td>
                            <td style="text-align:center">' . $record['remarks'] ?? '' . '</td>
                        </tr>';
                $total_qty_delivery += $record['qty_delivery'];
                $total_qty_packing += $record['qty_packing'];
                $no++;
            }

            $html .= '
                    <tr>
                        <td colspan="4" style="text-align:right; font-weight:bold;">Total Qty</td>
                        <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_packing, 0, ",", ".") . '</td>
                        <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_delivery, 0, ",", ".") . '</td>
                        <td></td>
                    </tr>
                </table>';

            $html .= '</table>';

            $html .= '</div></div>
                    </div>
                    <div style="text-align:right; margin-right: 40pt;">
                        <p>Purwakarta, '. $date_delivery . '</p>
                    </div>
                    <div class="footer" style="margin-top:10pt; font-size:9pt;">
                        <div class="signature-container">
                            
                            <!-- Tabel Supplier -->
                            <div class="supplier-signature">
                                <table class="supplier-table">
                                    <tr>
                                        <th style="width:15%;padding:2pt;">Diterima</th>
                                        <th style="width:45%;padding:2pt;border:none"></th>
                                        <th style="padding:2pt; width: 20%;">Diketahui</th>
                                        <th style="padding:2pt; width: 20%;">Dibuat</th>
                                    </tr>
                                    <tr>
                                    <td></td>
                                    <td style="border:none"></td>
                                    <td>';

                                    if($user2 != null){
                                        $html .= '<img src="' . base_url('assets/image/qrcode/' . $approval_2->name . '.png') . '" style="width:35pt"/>';
                                    } else if($user1 != null) {
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
                                        
                                        <td style="text-align:center;">';
                                        if($user2 != null){
                                            $html .= $approval_2->name;
                                        }else if($user1 != null) {
                                            $html .= $approval_1->name;
                                        }

        $html .= '</td>
                                        <td style="text-align:center;">' . @$records[0]['created_by_name'] . '</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>';

            // }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=subcont_invoices_report_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_delivery_to = @base64_decode($get['filter_delivery_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
        $filter_workorder_label = @base64_decode($get['filter_workorder_label']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('
            a.*, 
            b.number as item_fg_number, 
            b.name as item_fg_name, 
            b.uom, 
            COALESCE(c.name, d.name) as destination_name, 
            COALESCE(c.address, d.address) as address,
            COALESCE(e.qty_label, 0) AS qty_packing,
            COALESCE(i.qty_incoming, 0) AS qty_incoming,
            (a.qty_delivery - COALESCE(i.qty_incoming, 0)) AS qty_outstanding
        ');
        $this->db->from('subcont_invoices a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.destination = c.id', 'left');
        $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');

        $this->db->join("(
            SELECT 
                scan_id,
                item_fg_id,
                workorder,
                COUNT(*) AS qty_label
            FROM shipping_to_subconts
            WHERE type_status = 'completed'
            GROUP BY scan_id, item_fg_id, workorder
        ) e", '
            e.scan_id = a.scan_id
            AND e.item_fg_id = a.item_fg_id
            AND e.workorder = a.workorder
        ', 'left');

        $this->db->join(
            "(
                SELECT
                    delivery_note_no,
                    item_fg_id,
                    workorder,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Regular'
                AND type_status = 'completed'
                GROUP BY delivery_note_no, item_fg_id, workorder
            ) i",
            "i.delivery_note_no = a.delivery_note_no
            AND i.item_fg_id = a.item_fg_id
            AND i.workorder = a.workorder",
            'left'
        );

        $this->db->order_by('a.delivery_date', 'asc');
        $this->db->order_by('a.delivery_note_no', 'asc');
        $this->db->order_by('a.workorder', 'asc');

        if ($filter_workorder_label != "") {

            $this->db->join(
                'shipping_to_subconts sw',
                'sw.scan_id = a.scan_id
                AND sw.item_fg_id = a.item_fg_id
                AND sw.workorder = a.workorder
                AND sw.type_status = "completed"',
                'left'
            );

            $this->db->where('sw.workorder_label', $filter_workorder_label);
        }

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_date >=', $filter_from);
            $this->db->where('a.delivery_date <=', $filter_to);
        }
        if ($filter_delivery_to != "") {
            $this->db->where('a.delivery_to', $filter_delivery_to);
        }
        if ($filter_delivery_note_no != "") {
            $this->db->where('a.delivery_note_no', $filter_delivery_note_no);
        }
        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }

        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                margin: 20px;
            }
            #customers {
                border-collapse: collapse;
                width: 100%;
                font-size: 11px;
                margin: 15px 0;
            }
            .table-container {
                margin: 20px;
            }
            #customers td, #customers th {
                border: 1px solid black;
                padding: 4px;
                text-align: left;
                white-space: nowrap;
            }
            #customers th {
                background-color: white;
                color: black;
                font-weight: bold;
                text-align: center;
                border-bottom: 1px solid black;
            }
            #customers tr:hover {
                background-color: #f5f5f5;
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
            <h3 style="margin:0;">DELIVERY NOTES REGULAR REPORT</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px;">No</th>
                        <th style="width: 80px;">Delivery Date</th>
                        <th style="width: 80px;">Target Date</th>
                        <th style="width: 120px;">Destination</th>
                        <th style="width: 120px;">Delivery Note No</th>
                        <th style="width: 100px;">Product ID</th>
                        <th style="width: 100px;">Product No</th>
                        <th style="width: 150px;">Product Name</th>
                        <th style="width: 80px;">WO No</th>
                        <th style="width: 80px;">Qty Packing</th>
                        <th style="width: 80px;">Qty Delivery</th>
                        <th style="width: 80px;">Qty Incoming</th>
                        <th style="width: 80px;">Qty Outstanding</th>
                        <th style="width: 100px;">Remarks</th>
                    </tr>';

        $no = 1;
        $total_qty_delivery = 0;
        $total_qty_incoming = 0;
        $total_qty_outstanding = 0;
        $total_qty_packing = 0;

        foreach ($records as $row) {
            $target_date = (!empty($row['target_date']) ? date('Y-m-d', strtotime($row['target_date'])) : '-');

            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap" style="text-align: center;">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td class="no-wrap" style="text-align: center;">'.$target_date.'</td>
                        <td class="no-wrap">'.$row['destination_name'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td class="no-wrap">'.$row['workorder'].'</td>
                        <td style="text-align: center;">'.number_format($row['qty_packing'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_delivery'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_incoming'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_outstanding'],0,".",".").'</td>
                        <td class="no-wrap" style="text-align: center;">'.$row['remarks'].'</td>
                    </tr>';

            $total_qty_delivery += $row['qty_delivery'];
            $total_qty_incoming += $row['qty_incoming'];
            $total_qty_outstanding += $row['qty_outstanding'];
            $total_qty_packing += $row['qty_packing'];
            $no++;
        }

        $html .= '
                <tr>
                    <td colspan="9" style="text-align:right; font-weight:bold;">Total Qty</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_packing, 0, ",", ".") . '</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_delivery, 0, ",", ".") . '</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_incoming, 0, ",", ".") . '</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_outstanding, 0, ",", ".") . '</td>
                    <td></td>
                </tr>
            </table></div>';

        // $html .= '</table></div>';
        echo $html;
    }

}
