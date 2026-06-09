<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Grn_subconts extends CI_Controller
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
            $this->load->view('control/grn_subconts');
        } else {
            redirect('error_access');
        }
    }

    public function readSourceName()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sql = "
            SELECT 
                id,
                number,
                name,
                'Subcont' AS type
            FROM subconts
            WHERE status = 0
            AND subcont_type_id = 'TS001'
            AND deleted = 0
            AND (number LIKE '%$post%' OR name LIKE '%$post%' OR id LIKE '%$post%')

            UNION ALL

            SELECT 
                id,
                number,
                name,
                'Teaching Factory' AS type
            FROM teaching_factory
            WHERE status = 0
            AND subcont_type_id = 'TS001'
            AND deleted = 0
            AND (number LIKE '%$post%' OR name LIKE '%$post%' OR id LIKE '%$post%')
        ";

        $send = $this->crud->query($sql);

        echo json_encode($send);
    }

    public function readWorkorderLabels()
    {
        $q = $this->input->get('q');
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $filter_source_name   = $this->input->get("filter_source_name");

        $this->db->select('a.workorder_label');
        $this->db->from('scan_incoming_sctf a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');

        $this->db->join(
            'delivery_to_subconts d',
            'd.delivery_note_no = a.delivery_note_no
            AND d.item_fg_id = a.item_fg_id
            AND d.workorder = a.workorder
            AND d.deleted = 0',
            'left'
        );

        $this->db->where('a.type_status', 'completed');
        $this->db->where('a.incoming_type', 'Regular');

        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('a.incoming_date >=', $filter_from);
            $this->db->where('a.incoming_date <=', $filter_to);
        }

        if (!empty($filter_source_name)) {
            $this->db->where('a.incoming_from', $filter_source_name);
        }

        if ($q != '') {
            $this->db->like('a.workorder_label', $q);
        }

        $this->db->order_by('a.workorder_label', 'ASC');

        echo json_encode(
            $this->db->get()->result_array()
        );
    }

    public function number($delivery_note_date, $divison_number)
    {
        $divison_number = base64_decode($divison_number);
        $customer_number = base64_decode($this->input->post('customer_number'));

        $numberCust = $customer_number;
        $divisions  = "DN". $divison_number;
        $datenow    = date("my", strtotime(base64_decode($delivery_note_date)));
        $dn_no      = $numberCust . "-" . $datenow;
        $sqlGetID   = $this->db->query("SELECT SUBSTR(delivery_note_no, 7, 4) as kode FROM delivery_to_subconts WHERE `delivery_note_no` like '%$dn_no%'");
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
        $filter_source_name   = $this->input->get("filter_source_name");

        $this->db->distinct();
        $this->db->select('delivery_note_no');
        $this->db->from('scan_incoming_sctf');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('incoming_date >=', $filter_from);
            $this->db->where('incoming_date <=', $filter_to);
        }

        if (!empty($filter_source_name)) {
            $this->db->where('incoming_from', $filter_source_name);
        }

        $this->db->where('incoming_type', 'Regular');
        $this->db->order_by('delivery_note_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }

    public function readIncomingDocNo()
    {
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $filter_source_name   = $this->input->get("filter_source_name");

        $this->db->distinct();
        $this->db->select('incoming_doc_no');
        $this->db->from('scan_incoming_sctf');
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where('incoming_date >=', $filter_from);
            $this->db->where('incoming_date <=', $filter_to);
        }

        // $this->db->where('incoming_from', "$filter_source_name");
        if (!empty($filter_source_name)) {
            $this->db->where('incoming_from', $filter_source_name);
        }
        $this->db->where('incoming_type', 'Regular');
        $this->db->order_by('incoming_doc_no', 'ASC');
        $query = $this->db->get();

        echo json_encode($query->result_array());
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
            $filter_incoming_doc_no = @base64_decode($get['filter_incoming_doc_no']);
            $filter_source_name = @base64_decode($get['filter_source_name']);
            $filter_workorder_label = @base64_decode($get['filter_workorder_label']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;

            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select("
                a.printed,
                a.created_by,
                a.created_date,
                a.updated_by,
                a.updated_date,
                a.incoming_doc_no,
                a.incoming_date,
                SUM(a.qty) as total_qty_incoming,
                COALESCE(c.name, d.name) as source_name,
                SUM(a.qty * COALESCE(sc.price,0)) AS grand_total_price
            ", false);

            $this->db->from('scan_incoming_sctf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.incoming_from = c.id', 'left');
            $this->db->join('teaching_factory d', 'a.incoming_from = d.id', 'left');

            $join_sc = "sc.item_fg_id = a.item_fg_id
                AND sc.valid_date >= CURDATE()
                AND (
                    sc.subcont_id = a.incoming_from
                    OR sc.teaching_factory_id = a.incoming_from
                )
            ";

            $this->db->join('setting_subconts sc', $join_sc, 'left', false);
            $this->db->where('a.incoming_type', 'Regular');

            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.incoming_date >=', $filter_from);
                $this->db->where('a.incoming_date <=', $filter_to);
            }
            if ($filter_item_fg != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg);
            }
            if ($filter_source_name != "") {
                $this->db->where('a.incoming_from', $filter_source_name);
            }
            if ($filter_workorder_label != "") {
                $this->db->where('a.workorder_label', $filter_workorder_label);
            }

            $this->db->like('a.delivery_note_no', $filter_delivery_note_no);
            $this->db->like('a.incoming_doc_no', $filter_incoming_doc_no);
            $this->db->group_by('a.incoming_doc_no');

            $this->db->order_by('a.incoming_date', 'ASC');
            $this->db->order_by('a.delivery_note_no', 'ASC');

            $totalRows = $this->db->count_all_results('', false);

            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();

            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails()
    {
        if ($this->input->get()) {

            $incoming_doc_no = base64_decode($this->input->get('incoming_doc_no'));
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));
            $workorder_label = base64_decode($this->input->get('workorder_label'));

            $this->db->select("
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,

                a.delivery_note_no,
                d.delivery_date,
                d.target_date,

                a.workorder,
                a.workorder_label,

                a.qty AS qty_incoming,

                COALESCE(sc.price,0) AS price_pcs,
                a.qty * COALESCE(sc.price,0) AS total_price
            ", false);

            $this->db->from('scan_incoming_sctf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            $this->db->join(
                'delivery_to_subconts d',
                'd.delivery_note_no = a.delivery_note_no
                AND d.item_fg_id = a.item_fg_id
                AND d.workorder = a.workorder
                AND d.deleted = 0',
                'left'
            );
            $join_sc = "
                sc.item_fg_id = a.item_fg_id
                AND sc.valid_date >= CURDATE()
                AND (
                    sc.subcont_id = a.incoming_from
                    OR sc.teaching_factory_id = a.incoming_from
                )
            ";

            $this->db->join(
                'setting_subconts sc',
                $join_sc,
                'left',
                false
            );

            $this->db->where('a.incoming_type', 'Regular');
            $this->db->where('a.incoming_doc_no', $incoming_doc_no);
            // $this->db->where('a.delivery_note_no', $delivery_note_no);
            if (!empty($delivery_note_no)) {
                $this->db->where('a.delivery_note_no', $delivery_note_no);
            }

            if (!empty($workorder_label)) {
                $this->db->where('a.workorder_label', $workorder_label);
            }

            $this->db->group_by([
                'a.item_fg_id',
                'a.delivery_note_no',
                'a.workorder',
                'a.workorder_label'
            ]);

            $this->db->order_by('d.delivery_date', 'ASC');
            $this->db->order_by('a.workorder', 'ASC');
            $this->db->order_by('a.workorder_label', 'ASC');

            echo json_encode(
                $this->db->get()->result_array()
            );
        }
    }

    public function print_grn_subcont($incoming_doc_no)
    {
        $incoming_doc_no = base64_decode($incoming_doc_no);

        $this->db->query("UPDATE scan_incoming_sctf SET printed = 1 WHERE `incoming_doc_no` = '$incoming_doc_no'");
        $scan_incoming_sctf = $this->crud->read('scan_incoming_sctf', [], ["incoming_doc_no" => $incoming_doc_no]);

        $config = $this->db->get('config')->row();

        $html = '<html><head><title>' . $scan_incoming_sctf->incoming_doc_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="14x14"></head>';
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
                .text-center {
                    text-align: center;
                }
            }
        </style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Margin 0.5 inch, Scale 100%</p>
                </center></div><div class="print">';

        //Loop Page
        $no = 1;

        $this->db->select("
            a.incoming_doc_no,
            a.incoming_date,
            a.item_fg_id,
            b.number AS item_fg_number,
            b.name AS item_fg_name,

            a.delivery_note_no,
            d.delivery_date,
            d.target_date,

            a.incoming_from,
            a.workorder,
            a.workorder_label,

            a.qty AS qty_incoming,
            COALESCE(c.name, e.name) as source_name,

            COALESCE(sc.price,0) AS price_pcs,
            a.qty * COALESCE(sc.price,0) AS total_price
        ", false);

        $this->db->from('scan_incoming_sctf a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.incoming_from = c.id', 'left');
        $this->db->join('teaching_factory e', 'a.incoming_from = e.id', 'left');

        $this->db->join(
            'delivery_to_subconts d',
            'd.delivery_note_no = a.delivery_note_no
            AND d.item_fg_id = a.item_fg_id
            AND d.workorder = a.workorder
            AND d.deleted = 0',
            'left'
        );
        $join_sc = "
            sc.item_fg_id = a.item_fg_id
            AND sc.valid_date >= CURDATE()
            AND (
                sc.subcont_id = a.incoming_from
                OR sc.teaching_factory_id = a.incoming_from
            )
        ";

        $this->db->join(
            'setting_subconts sc',
            $join_sc,
            'left',
            false
        );

        $this->db->where('a.incoming_type', 'Regular');
        $this->db->where('a.incoming_doc_no', $incoming_doc_no);

        $this->db->group_by([
            'a.item_fg_id',
            'a.delivery_note_no',
            'a.workorder',
            'a.workorder_label'
        ]);

        $this->db->order_by('a.incoming_date', 'ASC');
        $this->db->order_by('a.workorder', 'ASC');
        $this->db->order_by('a.workorder_label', 'ASC');
        $records = $this->db->get()->result_array();

        $html .= '<div class="page">
                    <div class="content">
                        <table style="width:100%; margin-bottom: 10px;">
                            <tr>
                                <th width="10px"><img src="' . $config->favicon . '" width="30px" /></th>
                                <td width="150px" style="padding:10px;">
                                    <b style="font-size:9pt;">' . $config->name . '</b><br>
                                    <span style="font-size:8pt;">' . $config->description . '</span><br>
                                </td>
                                <th width="230px" style="padding-right: 0px;"><center><h3>GOOD RECEIVING NOTE</h3></center></th>
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
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:9pt; font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; margin-bottom:10px;font-weight: bold;">
                                        <tr>
                                            <td width="150px">Incoming Doc No</td>
                                            <td width="10px">:</td>
                                            <td><b>' . @$records[0]['incoming_doc_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="150px">Incoming Date</td>
                                            <td width="10px">:</td>
                                            <td><b>' . date("Y-m-d", strtotime(@$records[0]['incoming_date'])) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;">
                                    <table style="width:100%; font-size:9pt; font-family:"Arial Unicode MS", "Lucida Sans Unicode", "DejaVu Sans", "Segoe UI"; margin-bottom:100px;font-weight: bold;">
                                        <tr>
                                            <td width="150px">Source Name</td>
                                            <td width="10px">:</td>
                                            <td><b>' . @$records[0]['source_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="150px">Category</td>
                                            <td width="10px">:</td>
                                            <td><b>Regular</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="clear: both; height: 20px;"></div>
                                <table id="customers">
                                    <tr>
                                        <th style="width: 10px;">No</th>
                                        <th style="width: 150px;">Product No</th>
                                        <th style="width: 120px;">Product Name</th>
                                        <th style="width: 100px;">Delivery Note No</th>
                                        <th style="width: 100px;">Delivery Date</th>
                                        <th style="width: 100px;">Target Date</th>
                                        <th style="width: 150px;">Workorder Label</th>
                                        <th style="width: 80px;">Qty Incoming</th>
                                        <th style="width: 80px;">Price/pcs </br> (Rp)</th>
                                        <th style="width: 80px;">Total Price </br> (Rp)</th>
                                    </tr>';

        $total_qty_incoming = 0;
        $total_price = 0;
        $grand_total_price = 0;

        foreach ($records as $row) {
            $target_date = (!empty($row['target_date']) ? date('Y-m-d', strtotime($row['target_date'])) : '-');

            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td style="width: 150px;" class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td style="width: 120px;" class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td style="width: 100px;" class="no-wrap" style="text-align: center;">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td style="width: 100px;" class="no-wrap" style="text-align: center;">'.$target_date.'</td>
                        <td style="width: 150px;" class="no-wrap">'.$row['workorder_label'].'</td>
                        <td style="text-align: right;">'.number_format($row['qty_incoming'], 0, ",", ".").'</td>
                        <td style="text-align: right;">'.number_format($row['price_pcs'], 2, ",", ".").'</td>
                        <td style="text-align: right;">'.number_format($row['total_price'], 2, ",", ".").'</td>
                        </tr>';

            $total_qty_incoming += $row['qty_incoming'];
            $total_price += $row['price_pcs'];
            $grand_total_price += $row['total_price'];
            $no++;
        }

        $html .= '
                <tr>
                    <td colspan="7" style="text-align:right; font-weight:bold;">Grand Total</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($total_qty_incoming, 0, ",", ".") . '</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($total_price, 2, ",", ".") . '</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($grand_total_price, 2, ",", ".") . '</td>
                </tr>
            </table></div>';

            $html .= '</table>';

        $html .= '<script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=grn_subconts_report_recap_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
        $filter_incoming_doc_no = @base64_decode($get['filter_incoming_doc_no']);
        $filter_source_name = @base64_decode($get['filter_source_name']);
        $filter_workorder_label = @base64_decode($get['filter_workorder_label']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

            $this->db->select("
                a.incoming_doc_no,
                a.incoming_date,
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,

                a.delivery_note_no,
                d.delivery_date,
                d.target_date,

                a.incoming_from,
                a.workorder,
                a.workorder_label,

                SUM(a.qty) AS qty_incoming,
                COALESCE(c.name, e.name) as source_name,

                COALESCE(sc.price,0) AS price_pcs,
                SUM(a.qty) * COALESCE(sc.price,0) AS total_price
            ", false);

            $this->db->from('scan_incoming_sctf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.incoming_from = c.id', 'left');
            $this->db->join('teaching_factory e', 'a.incoming_from = e.id', 'left');

            $this->db->join(
                'delivery_to_subconts d',
                'd.delivery_note_no = a.delivery_note_no
                AND d.item_fg_id = a.item_fg_id
                AND d.workorder = a.workorder
                AND d.deleted = 0',
                'left'
            );
            $join_sc = "
                sc.item_fg_id = a.item_fg_id
                AND sc.valid_date >= CURDATE()
                AND (
                    sc.subcont_id = a.incoming_from
                    OR sc.teaching_factory_id = a.incoming_from
                )
            ";

            $this->db->join(
                'setting_subconts sc',
                $join_sc,
                'left',
                false
            );

            $this->db->where('a.incoming_type', 'Regular');

            $this->db->group_by([
                'a.item_fg_id',
                'a.delivery_note_no'
            ]);

            $this->db->order_by('a.incoming_date', 'ASC');

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.incoming_date >=', $filter_from);
            $this->db->where('a.incoming_date <=', $filter_to);
        }
        if ($filter_source_name != "") {
            $this->db->where('a.incoming_from', $filter_source_name);
        }
        if ($filter_incoming_doc_no != "") {
            $this->db->where('a.incoming_doc_no', $filter_incoming_doc_no);
        }
        if ($filter_delivery_note_no != "") {
            $this->db->where('a.delivery_note_no', $filter_delivery_note_no);
        }
        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }
        if ($filter_workorder_label != "") {
            $this->db->where('a.workorder_label', $filter_workorder_label);
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
            <h3 style="margin:0;">RECAP GOOD RECEIVING NOTE</h3>
            <br>

            <table style="width: 93%; font-size:12px;">
                <tr>
                    <th style="width:10px; text-align:left;">Incoming Date</th>
                    <td style="width:10px;">:</td>
                    <td style="width:200px;"><b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></td>
                </tr>
                <tr>
                    <th style="width:10px; text-align:left;">Source</th>
                    <td style="width:10px;">:</td>
                    <td style="width:200px;"><b>'. $records[0]["source_name"] .'</b></td>
                </tr>
                <tr>
                    <th style="width:10px; text-align:left;">Category</th>
                    <td style="width:10px;">:</td>
                    <td style="width:200px;"><b>Regular</b></td>
                </tr>
            </table>
        </center>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px !important;">No</th>
                        <th style="width: 120px;">Product ID</th>
                        <th style="width: 120px;">Product No</th>
                        <th style="width: 150px;">Product Name</th>
                        <th style="width: 80px;">Qty Incoming</th>
                        <th style="width: 80px;">Price/pcs </br> (Rp)</th>
                        <th style="width: 80px;">Total Price </br> (Rp)</th>
                    </tr>';

        $no = 1;
        $total_qty_incoming = 0;
        $total_price = 0;
        $grand_total_price = 0;

        foreach ($records as $row) {
            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td style="text-align: right;">'.number_format($row['qty_incoming'], 0, ",", ".").'</td>
                        <td style="text-align: right;">'.number_format($row['price_pcs'], 2, ",", ".").'</td>
                        <td style="text-align: right;">'.number_format($row['total_price'], 2, ",", ".").'</td>
                    </tr>';

            $total_qty_incoming += $row['qty_incoming'];
            $total_price += $row['price_pcs'];
            $grand_total_price += $row['total_price'];
            $no++;
        }

        $html .= '
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold;">Grand Total</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($total_qty_incoming, 0, ",", ".") . '</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($total_price, 2, ",", ".") . '</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($grand_total_price, 2, ",", ".") . '</td>
                </tr>
            </table></div>';

        echo $html;
    }

    //PRINT & EXCEL DATA
    public function print_detail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=grn_subconts_report_detail_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);
        $filter_incoming_doc_no = @base64_decode($get['filter_incoming_doc_no']);
        $filter_source_name = @base64_decode($get['filter_source_name']);
        $filter_workorder_label = @base64_decode($get['filter_workorder_label']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

            $this->db->select("
                a.incoming_doc_no,
                a.incoming_date,
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,

                a.delivery_note_no,
                d.delivery_date,
                d.target_date,

                a.incoming_from,
                a.workorder,
                a.workorder_label,

                a.qty AS qty_incoming,
                COALESCE(c.name, e.name) as source_name,

                COALESCE(sc.price,0) AS price_pcs,
                a.qty * COALESCE(sc.price,0) AS total_price
            ", false);

            $this->db->from('scan_incoming_sctf a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.incoming_from = c.id', 'left');
            $this->db->join('teaching_factory e', 'a.incoming_from = e.id', 'left');

            $this->db->join(
                'delivery_to_subconts d',
                'd.delivery_note_no = a.delivery_note_no
                AND d.item_fg_id = a.item_fg_id
                AND d.workorder = a.workorder
                AND d.deleted = 0',
                'left'
            );
            $join_sc = "
                sc.item_fg_id = a.item_fg_id
                AND sc.valid_date >= CURDATE()
                AND (
                    sc.subcont_id = a.incoming_from
                    OR sc.teaching_factory_id = a.incoming_from
                )
            ";

            $this->db->join(
                'setting_subconts sc',
                $join_sc,
                'left',
                false
            );

            $this->db->where('a.incoming_type', 'Regular');

            $this->db->group_by([
                'a.item_fg_id',
                'a.delivery_note_no',
                'a.workorder',
                'a.workorder_label'
            ]);

            $this->db->order_by('a.incoming_date', 'ASC');
            $this->db->order_by('a.workorder', 'ASC');
            $this->db->order_by('a.workorder_label', 'ASC');

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.incoming_date >=', $filter_from);
            $this->db->where('a.incoming_date <=', $filter_to);
        }
        if ($filter_source_name != "") {
            $this->db->where('a.incoming_from', $filter_source_name);
        }
        if ($filter_incoming_doc_no != "") {
            $this->db->where('a.incoming_doc_no', $filter_incoming_doc_no);
        }
        if ($filter_delivery_note_no != "") {
            $this->db->where('a.delivery_note_no', $filter_delivery_note_no);
        }
        if ($filter_item_fg != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }
        if ($filter_workorder_label != "") {
            $this->db->where('a.workorder_label', $filter_workorder_label);
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
            <br><br>
            <h3 style="margin:0;">DETAIL GOOD RECEIVING NOTE</h3>
            <br>

            <table style="width: 93%; font-size:12px;">
                <tr>
                    <th style="width:10px; text-align:left;">Incoming Date</th>
                    <td style="width:10px;">:</td>
                    <td style="width:200px;"><b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></td>
                </tr>
                <tr>
                    <th style="width:10px; text-align:left;">Category</th>
                    <td style="width:10px;">:</td>
                    <td style="width:200px;"><b>Regular</b></td>
                </tr>
            </table>
        </center>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px !important;">No</th>
                        <th style="width: 100px;">Incoming Doc No</th>
                        <th style="width: 80px;">Incoming Date</th>
                        <th style="width: 120px;">Source Name</th>
                        <th style="width: 120px;">Product ID</th>
                        <th style="width: 120px;">Product No</th>
                        <th style="width: 150px;">Product Name</th>
                        <th style="width: 100px;">Delivery Note No</th>
                        <th style="width: 80px;">Delivery Date</th>
                        <th style="width: 80px;">Target Date</th>
                        <th style="width: 100px;">Workorder</th>
                        <th style="width: 120px;">Workorder Label</th>
                        <th style="width: 80px;">Qty Incoming</th>
                        <th style="width: 80px;">Price/pcs </br> (Rp)</th>
                        <th style="width: 80px;">Total Price </br> (Rp)</th>
                    </tr>';

        $no = 1;
        $total_qty_incoming = 0;
        $total_price = 0;
        $grand_total_price = 0;

        foreach ($records as $row) {
            $target_date = (!empty($row['target_date']) ? date('Y-m-d', strtotime($row['target_date'])) : '-');

            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap">'.$row['incoming_doc_no'].'</td>
                        <td class="no-wrap" style="text-align: center;">'.date('Y-m-d', strtotime($row['incoming_date'])).'</td>
                        <td class="no-wrap">'.$row['source_name'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap" style="text-align: center;">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td class="no-wrap" style="text-align: center;">'.$target_date.'</td>
                        <td class="no-wrap">'.$row['workorder'].'</td>
                        <td class="no-wrap">'.$row['workorder_label'].'</td>
                        <td style="text-align: right;">'.number_format($row['qty_incoming'], 0, ",", ".").'</td>
                        <td style="text-align: right;">'.number_format($row['price_pcs'], 2, ",", ".").'</td>
                        <td style="text-align: right;">'.number_format($row['total_price'], 2, ",", ".").'</td>
                    </tr>';

            $total_qty_incoming += $row['qty_incoming'];
            $total_price += $row['price_pcs'];
            $grand_total_price += $row['total_price'];
            $no++;
        }

        $html .= '
                <tr>
                    <td colspan="12" style="text-align:right; font-weight:bold;">Grand Total</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($total_qty_incoming, 0, ",", ".") . '</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($total_price, 2, ",", ".") . '</td>
                    <td style="text-align:right; font-weight:bold;">' . number_format($grand_total_price, 2, ",", ".") . '</td>
                </tr>
            </table></div>';

        echo $html;
    }
}
