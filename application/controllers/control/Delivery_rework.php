<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_rework extends CI_Controller
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
            $this->load->view('control/delivery_rework');
        } else {
            redirect('error_access');
        }
    }

    public function readSerialLabels()
    {
        $q = $this->input->get('q');
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");

        $this->db->select('DISTINCT(a.serial_label)');
        $this->db->from('scan_out_rework a');

        $this->db->join(
            'delivery_rework b',
            'a.scan_id = b.scan_id
            AND a.workorder = b.workorder
            AND a.dnr_no = b.dnr_no',
            'inner'
        );

        $this->db->where('a.type_status', 'completed');
        $this->db->where('a.serial_label IS NOT NULL', null, false);
        $this->db->where('a.serial_label !=', '');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('b.delivery_date >=', $filter_from);
            $this->db->where('b.delivery_date <=', $filter_to);
        }

        if ($q != '') {
            $this->db->like('a.serial_label', $q);
        }

        $this->db->order_by('a.serial_label', 'ASC');

        echo json_encode(
            $this->db->get()->result_array()
        );
    }

    public function read_dnr_no()
    {
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $filter_destination = $this->input->get("filter_destination");

        $this->db->distinct();
        $this->db->select('dnr_no');
        $this->db->from('delivery_rework');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('delivery_date >=', $filter_from);
            $this->db->where('delivery_date <=', $filter_to);
        }
        if (!empty($filter_destination)) {
            $this->db->where('destination', $filter_destination);
        }

        $this->db->order_by('dnr_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_destination = @base64_decode($get['filter_destination']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_dnr_no = @base64_decode($get['filter_dnr_no']);
            $filter_serial_label = @base64_decode($get['filter_serial_label']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            // SUM(a.qty_delivery) as total_qty_delivery,
            // CASE
            //     WHEN s.cnt_over > 0 THEN '3'
            //     WHEN s.cnt_ongoing > 0 THEN '2'
            //     WHEN s.cnt_closed = s.total_row THEN '1'
            //     ELSE '0'
            // END AS status_header
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

            // $this->db->select("
            //     a.*, 
            //     COALESCE(c.name, d.name) as destination_name,
            //     SUM(a.qty_delivery) as total_qty_delivery,

            //     '0' AS status_header
            // ", false);

            $this->db->from('delivery_rework a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.destination = c.number', 'left');
            $this->db->join('teaching_factory d', 'a.destination = d.number', 'left');

            $this->db->join("(
                SELECT
                    dnr_no,
                    SUM(qty_delivery) AS total_qty_delivery
                FROM delivery_rework
                GROUP BY dnr_no
            ) t", "t.dnr_no = a.dnr_no", "left");


            // $this->db->join(
            //     'scan_out_rework sow',
            //     'sow.scan_id = a.scan_id
            //     AND sow.workorder = a.workorder
            //     AND sow.dnr_no = a.dnr_no
            //     AND sow.type_status = "completed"',
            //     'left'
            // );

            $this->db->join("(
                SELECT
                    d.dnr_no,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) > d.qty_delivery THEN 1 ELSE 0 END) AS cnt_over,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) > 0 
                            AND COALESCE(i.qty_incoming,0) < d.qty_delivery THEN 1 ELSE 0 END) AS cnt_ongoing,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) = d.qty_delivery THEN 1 ELSE 0 END) AS cnt_closed,
                    SUM(CASE WHEN COALESCE(i.qty_incoming,0) = 0 THEN 1 ELSE 0 END) AS cnt_open,
                    COUNT(*) AS total_row
                FROM (
                    SELECT
                        dnr_no,
                        item_fg_id,
                        workorder,
                        SUM(qty_delivery) AS qty_delivery
                    FROM delivery_rework
                    GROUP BY dnr_no, item_fg_id, workorder
                ) d
                LEFT JOIN (
                    SELECT
                        dnr_no,
                        item_fg_id,
                        workorder,
                        SUM(qty) AS qty_incoming
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Rework'
                    AND type_status = 'completed'
                    GROUP BY dnr_no, item_fg_id, workorder
                ) i ON i.dnr_no = d.dnr_no
                AND i.item_fg_id = d.item_fg_id
                AND i.workorder = d.workorder
                GROUP BY d.dnr_no
            ) s", "s.dnr_no = a.dnr_no", "left");

            // if ($filter_serial_label != "") {
            //     $this->db->where('sow.serial_label', $filter_serial_label);
            // }

            if ($filter_serial_label != "") {

                $this->db->join(
                    'scan_out_rework sow',
                    'sow.scan_id = a.scan_id
                    AND sow.item_fg_id = a.item_fg_id
                    AND sow.workorder = a.workorder
                    AND sow.dnr_no = a.dnr_no
                    AND sow.type_status = "completed"',
                    'left'
                );

                $this->db->where('sow.serial_label', $filter_serial_label);
            }

            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_date >=', $filter_from);
                $this->db->where('a.delivery_date <=', $filter_to);
            }
            if ($filter_destination != "") {
                $this->db->where('a.destination', $filter_destination);
            }
            if ($filter_item_fg != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            $this->db->like('a.dnr_no', $filter_dnr_no);
            $this->db->group_by('a.dnr_no');

            $this->db->order_by('a.delivery_date', 'ASC');
            $this->db->order_by('a.dnr_no', 'ASC');
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

            $dnr_no = base64_decode($this->input->get('dnr_no'));
            $serial_label = base64_decode($this->input->get('serial_label'));

            $this->db->select("
                d.item_fg_id,
                d.workorder,
                d.prod_date,
                d.qty_delivery,
                f.number AS item_fg_number,
                f.name AS item_fg_name,
                f.uom,
                COALESCE(i.qty_incoming, 0) AS qty_incoming,
                (d.qty_delivery - COALESCE(i.qty_incoming, 0)) AS qty_outstanding,

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
                    dnr_no,
                    item_fg_id,
                    workorder,
                    MAX(prod_date) AS prod_date,
                    SUM(qty_delivery) AS qty_delivery
                FROM delivery_rework
                WHERE dnr_no = ".$this->db->escape($dnr_no)."
                AND qty_delivery > 0
                GROUP BY dnr_no, item_fg_id, workorder
            ) d");

            $this->db->join('item_fg f', 'd.item_fg_id = f.id');

            $this->db->join(
                "(
                    SELECT
                        dnr_no,
                        item_fg_id,
                        workorder,
                        SUM(qty) AS qty_incoming
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Rework'
                    AND type_status = 'completed'
                    GROUP BY dnr_no, item_fg_id, workorder
                ) i",
                "i.dnr_no = d.dnr_no
                AND i.item_fg_id = d.item_fg_id
                AND i.workorder = d.workorder",
                'left'
            );

            $this->db->join(
                'scan_out_rework sow',
                'sow.scan_id = d.scan_id
                AND sow.workorder = d.workorder
                AND sow.dnr_no = d.dnr_no
                AND sow.type_status = "completed"',
                'left'
            );

            if ($serial_label != '') {
                $this->db->where('sow.serial_label', $serial_label);
            }

            $this->db->group_by('d.scan_id, d.workorder');
            $this->db->order_by('d.prod_date', 'ASC');
            $this->db->order_by('d.workorder', 'ASC');

            echo json_encode($this->db->get()->result_array());
        }
    }

    public function deleteAll()
    {
        $data = $this->input->post();

        $dnr_no  = $data['dnr_no'];
        $scan_id = $data['scan_id'];
        // $item_fg_id          = $data['item_fg_id'];
        // $workorder          = $data['workorder'];

        if(empty($scan_id)) {
            echo json_encode([
                'title'   => 'Failed Delete',
                'message' => 'Scan ID is empty!',
                'theme'   => 'error'
            ]);
            return;

        }

        $this->db->select("a.item_fg_id, a.workorder, a.dnr_no");
        $this->db->from('delivery_rework a');
        $this->db->where('a.dnr_no', $dnr_no);
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
        $this->db->from('delivery_rework a');
        $this->db->join(
            'scan_out_rework b',
            'a.scan_id = b.scan_id and a.dnr_no = b.dnr_no'
        );
        $this->db->where('a.dnr_no', $dnr_no);
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

        $this->db->select("dnr_no");
        $this->db->from("scan_incoming_sctf");
        $this->db->where("dnr_no", $dnr_no);
        $checkScanIncomingRework = $this->db->get()->row_array();

        if (!empty($checkScanIncomingRework)) {
            echo json_encode([
                'title'     => 'Delete Failed',
                'message'   => 'This data has already been used in the Scan In From External Finishing',
                'theme'     => 'error'
            ]);
            return;
        }

        $this->db->trans_begin();

        $this->crud->delete('delivery_rework', [
            'dnr_no'    => $dnr_no,
            'scan_id'   => $scan_id
        ]);

        $this->db->select("a.item_fg_id, a.workorder, a.serial_label");
        $this->db->from('scan_out_rework a');
        $this->db->where('a.scan_id', $scan_id);
        $this->db->where('a.dnr_no', $dnr_no);

        $checkShippingToSB = $this->db->get()->result_array();

        foreach ($checkShippingToSB as $item) {
            $this->crud->update('scan_in_rework', [
                    'serial_label' => $item['serial_label'],
                    'status'          => 1
                ],
                [
                    'status' => 0
                ]
            );
        }

        $this->crud->delete('scan_out_rework', [
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

    public function print_dn_rework($dnr_no)
    {
        $dnr_no = base64_decode($dnr_no);

        $this->db->query("UPDATE delivery_rework SET printed=1 WHERE `dnr_no` = '$dnr_no'");

        // $delivery_reworks = $this->crud->reads('delivery_rework', [], ["dnr_no" => $dnr_no]);
        $delivery_rework = $this->crud->read('delivery_rework', [], ["dnr_no" => $dnr_no]);

        // $user = $this->crud->read('users', [], ["username" => $delivery_rework->created_by]);
        $table_approval = 'delivery_rework';

        $approval=$this->db->query("
            SELECT *, CASE 
                WHEN user_approval_1 = '$delivery_rework->approved_by' THEN '1'
                WHEN user_approval_2 = '$delivery_rework->approved_by' THEN '2'
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
        $created_date = $delivery_rework->created_date;

        if($user1 != null){
            // Cek status approval pertama
            $approval_1 = $this->crud->read('users', [], ["username" => $user1]);
        }

        if($user2 != null){
            // Cek status approval kedua
            $approval_2 = $this->crud->read('users', [], ["username" => $user2]);
        }

        $config = $this->db->get('config')->row();

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
        $html = '<html><head><title>' . $delivery_rework->dnr_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="14x14"></head>';
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
            $this->db->from('delivery_rework a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.destination = c.number', 'left');
            $this->db->join('teaching_factory d', 'a.destination = d.number', 'left');

            $this->db->join("(
                SELECT 
                    scan_id,
                    item_fg_id,
                    workorder,
                    COUNT(*) AS qty_label
                FROM scan_out_rework
                WHERE type_status = 'completed'
                GROUP BY scan_id, item_fg_id, workorder
            ) e", '
                e.scan_id = a.scan_id
                AND e.item_fg_id = a.item_fg_id
                AND e.workorder = a.workorder
            ', 'left');

            $this->db->where('a.dnr_no', $dnr_no);
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
                                                <td><b>' . @$records[0]['dnr_no'] . '</b></td>
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
            header("Content-Disposition: attachment; filename=delivery_rework_report_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_destination = @base64_decode($get['filter_destination']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_dnr_no = @base64_decode($get['filter_dnr_no']);
        $filter_serial_label = @base64_decode($get['filter_serial_label']);

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
        $this->db->from('delivery_rework a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.destination = c.number', 'left');
        $this->db->join('teaching_factory d', 'a.destination = d.number', 'left');

        $this->db->join("(
            SELECT 
                scan_id,
                item_fg_id,
                workorder,
                COUNT(*) AS qty_label
            FROM scan_out_rework
            WHERE type_status = 'completed'
            GROUP BY scan_id, item_fg_id, workorder
        ) e", '
            e.scan_id = a.scan_id
            AND e.item_fg_id = a.item_fg_id
            AND e.workorder = a.workorder
        ', 'left');

        // $this->db->join(
        //     'scan_out_rework sw',
        //     'sw.scan_id = a.scan_id
        //     AND sw.item_fg_id = a.item_fg_id
        //     AND sw.workorder = a.workorder
        //     AND sw.dnr_no = a.dnr_no
        //     AND sw.type_status = "completed"',
        //     'left'
        // );

        // $this->db->join(
        //     "(
        //         SELECT
        //             scan_id,
        //             item_fg_id,
        //             workorder,
        //             dnr_no,
        //             MAX(workorder_label) AS workorder_label
        //         FROM scan_out_rework
        //         WHERE type_status = 'completed'
        //         GROUP BY
        //             scan_id,
        //             item_fg_id,
        //             workorder,
        //             dnr_no
        //     ) sw",
        //     'sw.scan_id = a.scan_id
        //     AND sw.item_fg_id = a.item_fg_id
        //     AND sw.workorder = a.workorder
        //     AND sw.dnr_no = a.dnr_no',
        //     'left'
        // );

        $this->db->join(
            "(
                SELECT
                    dnr_no,
                    item_fg_id,
                    workorder,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Rework'
                AND type_status = 'completed'
                GROUP BY dnr_no, item_fg_id, workorder
            ) i",
            "i.dnr_no = a.dnr_no
            AND i.item_fg_id = a.item_fg_id
            AND i.workorder = a.workorder",
            'left'
        );

        $this->db->order_by('a.delivery_date', 'asc');
        $this->db->order_by('i.dnr_no', 'asc');
        $this->db->order_by('a.delivery_note_no', 'asc');
        $this->db->order_by('a.workorder', 'asc');

        // if ($filter_serial_label != "") {
        //     $this->db->where('sw.serial_label', $filter_serial_label);
        // }

        if ($filter_serial_label != "") {

            $this->db->join(
                'scan_out_rework sw',
                'sw.scan_id = a.scan_id
                AND sw.item_fg_id = a.item_fg_id
                AND sw.workorder = a.workorder
                AND sw.dnr_no = a.dnr_no
                AND sw.type_status = "completed"',
                'left'
            );

            $this->db->where('sw.serial_label', $filter_serial_label);
        }

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_date >=', $filter_from);
            $this->db->where('a.delivery_date <=', $filter_to);
        }
        if ($filter_destination != "") {
            $this->db->where('a.destination', $filter_destination);
        }
        if ($filter_dnr_no != "") {
            $this->db->where('a.dnr_no', $filter_dnr_no);
        }
        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }

        $this->db->group_by('a.scan_id, a.item_fg_id, a.workorder');
        //$this->db->order_by('a.status', 'ASC');
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
            <h3 style="margin:0;">DELIVERY NOTES REWORK REPORT</h3>
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
                        <th style="width: 120px;">DNR No</th>
                        <th style="width: 120px;">Delivery Note No</th>
                        <th style="width: 100px;">Product ID</th>
                        <th style="width: 100px;">Product No</th>
                        <th style="width: 150px;">Product Name</th>
                        <th style="width: 80px;">WO No</th>
                        <th style="width: 80px;">Qty Packing</th>
                        <th style="width: 80px;">Qty Delivery</th>
                        <th style="width: 80px;">Qty Incoming</th>
                        <th style="width: 80px;">Qty Outstanding</th>
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
                        <td class="no-wrap"  style="text-align: center;">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td class="no-wrap"  style="text-align: center;">'.$target_date.'</td>
                        <td class="no-wrap">'.$row['destination_name'].'</td>
                        <td class="no-wrap">'.$row['dnr_no'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td class="no-wrap">'.$row['workorder'].'</td>
                        <td style="text-align: center;">'.number_format($row['qty_packing'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_delivery'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_incoming'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_outstanding'],0,".",".").'</td>
                    </tr>';

            $total_qty_delivery += $row['qty_delivery'];
            $total_qty_incoming += $row['qty_incoming'];
            $total_qty_outstanding += $row['qty_outstanding'];
            $total_qty_packing += $row['qty_packing'];
            $no++;
        }

        $html .= '
                <tr>
                    <td colspan="10" style="text-align:right; font-weight:bold;">Total Qty</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_packing, 0, ",", ".") . '</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_delivery, 0, ",", ".") . '</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_incoming, 0, ",", ".") . '</td>
                    <td style="text-align:center; font-weight:bold;">' . number_format($total_qty_outstanding, 0, ",", ".") . '</td>
                </tr>
            </table></div>';

        // $html .= '</table></div>';
        echo $html;
    }

    //PRINT & EXCEL DATA
    public function print_label($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_rework_label_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_destination = @base64_decode($get['filter_destination']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_dnr_no = @base64_decode($get['filter_dnr_no']);
        $filter_serial_label = @base64_decode($get['filter_serial_label']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('
            a.*,
            b.number as item_fg_number,
            b.name as item_fg_name,
            b.uom,
            sw.workorder_label,
            sw.serial_label,
            COALESCE(c.number, d.number) as destination_code,
            COALESCE(c.name, d.name) as destination_name,
            COALESCE(sw.qty, 0) AS qty_delivery,
            COALESCE(i.qty_incoming, 0) AS qty_incoming,
            (COALESCE(sw.qty, 0) - COALESCE(i.qty_incoming, 0)) AS qty_outstanding
        ');
        $this->db->from('delivery_rework a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.destination = c.number', 'left');
        $this->db->join('teaching_factory d', 'a.destination = d.number', 'left');

        $this->db->join(
            'scan_out_rework sw',
            'sw.scan_id = a.scan_id
            AND sw.item_fg_id = a.item_fg_id
            AND sw.workorder = a.workorder
            AND sw.dnr_no = a.dnr_no
            AND sw.type_status = "completed"',
            'left'
        );

        $this->db->join(
            "(
                SELECT
                    dnr_no,
                    item_fg_id,
                    workorder,
                    workorder_label,
                    serial_label,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Rework'
                AND type_status = 'completed'
                GROUP BY
                    dnr_no,
                    item_fg_id,
                    workorder,
                    workorder_label,
                    serial_label
            ) i",
            "i.dnr_no = a.dnr_no
            AND i.item_fg_id = a.item_fg_id
            AND i.workorder = a.workorder
            AND i.workorder_label = sw.workorder_label
            AND i.serial_label = sw.serial_label",
            'left'
        );

        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.delivery_date', 'asc');
        $this->db->order_by('a.dnr_no', 'asc');
        $this->db->order_by('destination_code', 'asc');
        $this->db->order_by('a.delivery_note_no', 'asc');
        $this->db->order_by('a.workorder', 'asc');

        if ($filter_serial_label != "") {
            $this->db->where('sw.serial_label', $filter_serial_label);
        }
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_date >=', $filter_from);
            $this->db->where('a.delivery_date <=', $filter_to);
        }
        if ($filter_destination != "") {
            $this->db->where('a.destination', $filter_destination);
        }
        if ($filter_dnr_no != "") {
            $this->db->where('a.dnr_no', $filter_dnr_no);
        }
        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }

        $this->db->group_by('
            a.scan_id,
            a.item_fg_id,
            a.workorder,
            sw.workorder_label,
            sw.serial_label
        ');
        
        //$this->db->order_by('a.status', 'ASC');
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
            <h3 style="margin:0;">DATA DELIVERY REWORK</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px; style="text-align: center;">No</th>
                        <th style="width: 120px;">DNR NO</th>
                        <th style="width: 120px;">Delivery Note No</th>
                        <th style="width: 80px;">Delivery Date</th>
                        <th style="width: 80px;">Target Date</th>
                        <th style="width: 120px;">Destination Code</th>
                        <th style="width: 120px;">Destination Name</th>
                        <th style="width: 100px;">Workorder</th>
                        <th style="width: 100px;">Workorder Label</th>
                        <th style="width: 100px;">Serial Label</th>
                        <th style="width: 100px;">Product ID</th>
                        <th style="width: 100px;">Product No</th>
                        <th style="width: 80px;">Qty Delivery</th>
                        <th style="width: 80px;">Qty Incoming</th>
                        <th style="width: 80px;">Qty Outstanding</th>
                    </tr>';

        $no = 1;
        $total_qty_delivery = 0;

        foreach ($records as $row) {
            $target_date = (!empty($row['target_date']) ? date('Y-m-d', strtotime($row['target_date'])) : '-');

            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap">'.$row['dnr_no'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap"  style="text-align: center;">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td class="no-wrap"  style="text-align: center;">'.$target_date.'</td>
                        <td class="no-wrap">'.$row['destination_code'].'</td>
                        <td class="no-wrap">'.$row['destination_name'].'</td>
                        <td class="no-wrap">'.$row['workorder'].'</td>
                        <td class="no-wrap">'.$row['workorder_label'].'</td>
                        <td class="no-wrap">'.$row['serial_label'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td style="text-align: center;">'.number_format($row['qty_delivery'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_incoming'],0,".",".").'</td>
                        <td style="text-align: center;">'.number_format($row['qty_outstanding'],0,".",".").'</td>
                    </tr>';

            $total_qty_delivery += $row['qty_delivery'];
            $no++;
        }

        $html .= '</table></div>';
        echo $html;
    }
}
