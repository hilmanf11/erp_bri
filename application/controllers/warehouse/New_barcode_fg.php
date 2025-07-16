<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class New_barcode_fg extends CI_Controller
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
            $this->load->view('warehouse/new_barcode_fg');
        } else {
            redirect('error_access');
        }
    }

    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM new_barcode_fg WHERE request_no like 'LP-$datenow-%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%04s", 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "LP-" . $datenow . "-" . $autoID;
    }
    
    // public function readitemsFG()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $send = $this->crud->query("SELECT id, number as item_number, name as item_name, box_sub, specification FROM item_fg WHERE item_family_number IN ('RP','CD','TB') AND (number like '%$post%' or name like '%$post%')");
    //     echo json_encode($send);
    // }

    public function readitemsFG()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        // Step 1: Hitung qty_in dari fg_scan_in_label
        $query_qty_in_fg_scan_in = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        GROUP BY a.item_fg_id";

        // Step 2: Hitung qty_in dari os_fg
        $query_qty_os_fg = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        GROUP BY a.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT dn.item_fg_id, SUM(dn.qty) as initial_out_g
        FROM delivery_notes dn
        JOIN (
            SELECT 
                delivery_order_no, 
                item_fg_id, 
                customer_order_no,
                SUM(qty) AS total_shipping_qty
            FROM shipping_orders
            WHERE deleted = 0
            GROUP BY delivery_order_no, item_fg_id, customer_order_no
        ) s ON dn.delivery_order_no = s.delivery_order_no
            AND dn.item_fg_id = s.item_fg_id
            AND dn.customer_order_no = s.customer_order_no
            AND dn.qty = s.total_shipping_qty
        WHERE dn.deleted = 0
        GROUP BY item_fg_id";


        $query = "
            SELECT 
                a.id, 
                a.number AS item_number, 
                a.name AS item_name, 
                a.box_sub, 
                a.specification,
                (
                    (
                        COALESCE(qc.fg_scan_in, 0) + 
                        COALESCE(qnc.qty_os_fg, 0) + 
                        COALESCE(qi.initial_in, 0)
                    ) - 
                    (
                        COALESCE(qo.qty_out, 0) + 
                        COALESCE(qg.initial_out_g, 0)
                    )
                ) AS end_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_in_fg_scan_in) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_os_fg) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
            WHERE a.item_family_number IN ('RP','CD','TB') 
                AND (a.number LIKE '%$post%' OR a.name LIKE '%$post%')
        ";

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    public function datatables()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to = $this->input->post('filter_to');
        $filter_product_no = $this->input->post('filter_product_no');
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = $this->input->post('sort');
        $order = $this->input->post('order');

        // Jika tidak ada filter tanggal, gunakan tanggal hari ini
        if (empty($filter_from) && empty($filter_to)) {
            $filter_from = date('Y-m-d');
            $filter_to = date('Y-m-d');
        }

        // Pagination
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        $this->db->select('lp.*, 
                           fg.number AS item_number, 
                           fg.name AS item_name, 
                           rm.number AS item_rm_number, 
                           rm.number AS material, 
                    (SELECT GROUP_CONCAT(serial_label ORDER BY serial_label SEPARATOR ", ") 
                        FROM new_barcode_fg_detail 
                        WHERE serial_no = lp.serial_no) AS serial_labels');
        $this->db->from('new_barcode_fg lp');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->join('item_rm rm', 'lp.item_rm_id = rm.id', 'left');
        // $this->db->where('lp.qty_packing !=', 0);
        // $this->db->where('lp.qty_label !=', 0);
        $this->db->where('lp.deleted', 0);

        if (!empty($filter_from)) {
            $this->db->where('lp.trans_date >=', $filter_from);
        }
        if (!empty($filter_to)) {
            $this->db->where('lp.trans_date <=', $filter_to);
        }
        if (!empty($filter_product_no)) {
            $this->db->where('fg.number', $filter_product_no);
        }

        $this->db->group_by('lp.id');
        $this->db->order_by($sort, $order);

        // Total Data
        $totalRows = $this->db->count_all_results('', false);
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();

        echo json_encode([
            'total' => $totalRows,
            'rows' => $records
        ]);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $date = new DateTime($post['trans_date']);
                $formatted_date = $date->format('ymd');
                $product_id = $post['item_fg_id'];
                $qty_wip = intval($post['qty_wip']);
                
                // Set qty_packing sama dengan qty_wip (1:1)
                $qty_packing = $qty_wip;
                $qty_label = 1; // 1 product = 1 label

                // Ambil sequence terakhir berdasarkan tanggal di new_barcode_fg
                $this->db->select_max('serial_no');
                $this->db->like('serial_no', $formatted_date, 'after');
                $last_serial_no = $this->db->get('new_barcode_fg')->row()->serial_no;

                // Jika ada serial_no sebelumnya, ambil sequence terakhir, jika tidak mulai dari 0001
                $sequence = 1;
                if ($last_serial_no) {
                    $last_sequence = intval(substr($last_serial_no, -4));
                    $sequence = $last_sequence + 1;
                }
                $serial_no = $formatted_date . sprintf("%04d", $sequence);

                // Simpan data ke tabel new_barcode_fg
                $data = [
                    'trans_date' => $post['trans_date'],
                    'shift' => $post['shift'],
                    'leader' => $post['leader'],
                    'item_fg_id' => $post['item_fg_id'],
                    'qty_wip' => $qty_wip,
                    'operator' => $post['operator'],
                    'serial_no' => $serial_no,
                    'specification' => $post['specification'],
                    'compound_lot' => $post['compound_lot'],
                    'prod_date' => !empty($post['prod_date']) ? date('Y-m-d', strtotime($post['prod_date'])) : date('Y-m-d'),
                    'qc' => $post['qc'],
                    'created_by' => $this->session->username,
                    'created_date' => date('Y-m-d H:i:s'),
                    'request_no' => $post['request_no']
                ];
                $new_barcode_fg_id = $this->crud->create('new_barcode_fg', $data);

                // Ambil sequence terakhir dari kedua tabel
                $this->db->select_max('serial_label');
                $this->db->like('serial_label', $formatted_date . $product_id, 'after');
                $last_serial_label_packing = $this->db->get('label_packing_detail')->row()->serial_label;

                $this->db->select_max('serial_label');
                $this->db->like('serial_label', $formatted_date . $product_id, 'after');
                $last_serial_label_barcode = $this->db->get('new_barcode_fg_detail')->row()->serial_label;

                // Tentukan sequence terakhir dari kedua tabel
                $last_sequence_packing = 0;
                $last_sequence_barcode = 0;

                if ($last_serial_label_packing) {
                    $last_sequence_packing = intval(substr($last_serial_label_packing, -4));
                }
                if ($last_serial_label_barcode) {
                    $last_sequence_barcode = intval(substr($last_serial_label_barcode, -4));
                }

                // Gunakan sequence terbesar + 1
                $detail_sequence = max($last_sequence_packing, $last_sequence_barcode) + 1;

                $serial_label = $formatted_date . $product_id . sprintf("%04d", $detail_sequence);

                $detail_data = [
                    'created_by' => $this->session->username,
                    'created_date' => date('Y-m-d H:i:s'),
                    'serial_label' => $serial_label,
                    'serial_no' => $serial_no,
                    'item_fg_id' => $post['item_fg_id'],
                    'qty_packing' => $qty_packing,
                    'request_no' => $post['request_no']
                ];
                $this->crud->create('new_barcode_fg_detail', $detail_data);

                echo json_encode([
                    'success' => true, 
                    'message' => 'Data saved successfully', 
                    'serial_no' => $serial_no, 
                    'request_no' => $post['request_no']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => validation_errors()]);
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        if (!isset($data['serial_no']) || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing serial_no or id']);
            return;
        }

        $serial_no = $data['serial_no'];

        // Hapus data di tabel new_barcode_fg_detail berdasarkan serial_no
        $this->crud->delete('new_barcode_fg_detail', ["serial_no" => $serial_no]);

        // Hapus data di tabel new_barcode_fg berdasarkan id
        $deleteNewBarcodeFg = $this->crud->delete('new_barcode_fg', ["id" => $data['id']]);
        
        if ($deleteNewBarcodeFg) {
            echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete data']);
        }
    }

    public function print_label() {
        $serial_no = $this->input->get('serial_no');
        $item_fg_id = $this->input->get('item_fg_id');
        
        if (empty($serial_no)) {
            show_error("Missing parameter: serial_no", 400);
        }
        
        $this->db->select('
            lp.trans_date, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            fg.number AS product_no, 
            fg.name AS product_name, 
            lpd.qty_packing,
            lp.specification,
            fg.uom
        ');
        $this->db->from('new_barcode_fg_detail lpd');
        $this->db->join('new_barcode_fg lp', 'lp.serial_no = lpd.serial_no', 'left');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->where('lpd.serial_no', $serial_no);
        
        if (!empty($item_fg_id)) {
            $this->db->where('lp.item_fg_id', $item_fg_id);
        }
        
        $this->db->group_by('lpd.serial_label');
        
        $new_barcode_fg_details = $this->db->get()->result();
        
        if (empty($new_barcode_fg_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_serial_label = $new_barcode_fg_details[0]->serial_label;
        
        foreach ($new_barcode_fg_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 2; }
                            table { border-collapse: collapse; width: 7.5cm; height: 8cm; font-size: 20px; border: 2px solid black; table-layout: fixed; }
                            th, td { border: 1px solid black; padding: 2px; text-align: left; }
                            th { text-align: center; font-size: 14px; font-weight: bold; }
                            .header { text-align: center; font-size: 15px; font-weight: bold; }
                            .logo { text-align: center; width: 100%; padding: 3px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 12px; text-align: center; height: 20mm; vertical-align: bottom; font-weight: bold; }
                            .qc-sign { text-align: center; height: 20mm; }
                            .qr-code img { width: 60px; height: 60px; display: block; margin: 0 auto; }
                            .serial-label { font-size: 11px; text-align: center; word-wrap: break-word; overflow: hidden; font-weight: bold; }
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
                                        font-size: 15px;
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
        
        foreach ($new_barcode_fg_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($new_barcode_fg_details[0]->uom);
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
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">FG PIC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);
    }

    public function print_label_ext() {

        $serial_no = $this->input->get('serial_no');
        $item_fg_id = $this->input->get('item_fg_id');
        
        if (empty($serial_no)) {
            show_error("Missing parameter: serial_no", 400);
        }
        
        $this->db->select('
            lp.trans_date, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            fg.number AS product_no, 
            fg.name AS product_name, 
            lpd.qty_packing,
            lp.specification,
            fg.uom
        ');
        $this->db->from('new_barcode_fg_detail lpd');
        $this->db->join('new_barcode_fg lp', 'lp.serial_no = lpd.serial_no', 'left');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->where('lpd.serial_no', $serial_no);
        
        if (!empty($item_fg_id)) {
            $this->db->where('lp.item_fg_id', $item_fg_id);
        }
        
        $this->db->group_by('lpd.serial_label');
        
        $new_barcode_fg_details = $this->db->get()->result();
        
        if (empty($new_barcode_fg_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_serial_label = $new_barcode_fg_details[0]->serial_label;
        
        foreach ($new_barcode_fg_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }

                            table { 
                                border-collapse: collapse; 
                                width: 48mm; 
                                height: 41mm; 
                                font-size: 10px; 
                                border: 1px solid black; 
                                table-layout: fixed; 
                            }

                            th, td { 
                                border: 1px solid black; 
                                padding: 1.5px; 
                                text-align: left; 
                            }

                            th { 
                                text-align: center; 
                                font-size: 5px; 
                                font-weight: bold; 
                            }

                            .header { 
                                text-align: center; 
                                font-size: 11px; 
                                font-weight: bold; 
                            }

                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding-left: 3px; 
                                padding-right: 3px; 
                            }

                            .operator-sign, .qc-sign, .qr-code { 
                                font-size: 7px; 
                                text-align: center; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 10mm; 
                            }
                            .qr-code img { 
                                width: 40px; 
                                height: 40px; 
                                display: block; 
                                margin: 0 auto;
                            }
                            .serial-label { 
                                font-size: 5px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                    size: 48mm 41mm;
                                    margin: 0;
                                    }
                            @media print {
                                    .printLabel {
                                        page-break-after: auto:
                                        width: 48mm;
                                        height: 41mm;
                                        display: block;
                                        padding: 0;
                                        margin: 0;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 6px;
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
        
        foreach ($new_barcode_fg_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($new_barcode_fg_details[0]->uom);
            $html .= '<div class="printLabel">
                        <table style="width: 48mm; height: 41mm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="10" align="left"/>
                                <span class="header" style="font-size: 10px; height: 10px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);
    }    

    public function print_label_by_request() {
        $request_no = $this->input->get('request_no');
        
        if (empty($request_no)) {
            show_error("Missing parameter: request_no", 400);
        }
        
        $this->db->select('
            lp.trans_date, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            fg.number AS product_no, 
            fg.name AS product_name, 
            lpd.qty_packing,
            lp.specification,
            fg.uom
        ');
        $this->db->from('new_barcode_fg_detail lpd');
        $this->db->join('new_barcode_fg lp', 'lp.serial_no = lpd.serial_no', 'left');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->where('lpd.request_no', $request_no);
        
        $this->db->group_by('lpd.serial_label');
        
        $new_barcode_fg_details = $this->db->get()->result();
        
        if (empty($new_barcode_fg_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_serial_label = $new_barcode_fg_details[0]->serial_label;
        
        foreach ($new_barcode_fg_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 2; }
                            table { border-collapse: collapse; width: 7.5cm; height: 8cm; font-size: 20px; border: 2px solid black; table-layout: fixed; }
                            th, td { border: 1px solid black; padding: 2px; text-align: left; }
                            th { text-align: center; font-size: 14px; font-weight: bold; }
                            .header { text-align: center; font-size: 15px; font-weight: bold; }
                            .logo { text-align: center; width: 100%; padding: 3px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 12px; text-align: center; height: 20mm; vertical-align: bottom; font-weight: bold; }
                            .qc-sign { text-align: center; height: 20mm; }
                            .qr-code img { width: 60px; height: 60px; display: block; margin: 0 auto; }
                            .serial-label { font-size: 11px; text-align: center; word-wrap: break-word; overflow: hidden; font-weight: bold; }
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
                                        font-size: 15px;
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
        
        foreach ($new_barcode_fg_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($new_barcode_fg_details[0]->uom);
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
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">FG PIC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);
    }
    
    public function print_label_ext_by_request() {
        $request_no = $this->input->get('request_no');
        
        if (empty($request_no)) {
            show_error("Missing parameter: request_no", 400);
        }
        
        $this->db->select('
            lp.trans_date, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            fg.number AS product_no, 
            fg.name AS product_name, 
            lpd.qty_packing,
            lp.specification,
            fg.uom
        ');
        $this->db->from('new_barcode_fg_detail lpd');
        $this->db->join('new_barcode_fg lp', 'lp.serial_no = lpd.serial_no', 'left');
        $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
        $this->db->where('lpd.request_no', $request_no);
        
        $this->db->group_by('lpd.serial_label');
        
        $new_barcode_fg_details = $this->db->get()->result();
        
        if (empty($new_barcode_fg_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
        
        $first_serial_label = $new_barcode_fg_details[0]->serial_label;
        
        foreach ($new_barcode_fg_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
        
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                margin: 2; 
                            }

                            table { 
                                border-collapse: collapse; 
                                width: 48mm; 
                                height: 41mm; 
                                font-size: 10px; 
                                border: 1px solid black; 
                                table-layout: fixed; 
                            }

                            th, td { 
                                border: 1px solid black; 
                                padding: 1.5px; 
                                text-align: left; 
                            }

                            th { 
                                text-align: center; 
                                font-size: 5px; 
                                font-weight: bold; 
                            }

                            .header { 
                                text-align: center; 
                                font-size: 11px; 
                                font-weight: bold; 
                            }

                            .logo { 
                                text-align: center; 
                                width: 100%; 
                                padding-left: 3px; 
                                padding-right: 3px; 
                            }

                            .operator-sign, .qc-sign, .qr-code { 
                                font-size: 7px; 
                                text-align: center; 
                                vertical-align: bottom; 
                                font-weight: bold; 
                            }
                            .qc-sign { 
                                text-align: center; 
                                height: 10mm; 
                            }
                            .qr-code img { 
                                width: 40px; 
                                height: 40px; 
                                display: block; 
                                margin: 0 auto;
                            }
                            .serial-label { 
                                font-size: 5px; 
                                text-align: center; 
                                word-wrap: break-word; 
                                overflow: hidden; 
                                font-weight: bold; 
                            }
                            @page {
                                    size: 48mm 41mm;
                                    margin: 0;
                                    }
                            @media print {
                                    .printLabel {
                                        page-break-after: auto:
                                        width: 48mm;
                                        height: 41mm;
                                        display: block;
                                        padding: 0;
                                        margin: 0;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 6px;
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
        
        foreach ($new_barcode_fg_details as $detail) {
            $qty_packing_formatted = number_format($detail->qty_packing, 0, ',', '.') . ' ' .strtoupper($new_barcode_fg_details[0]->uom);
            $html .= '<div class="printLabel">
                        <table style="width: 48mm; height: 41mm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="10" align="left"/>
                                <span class="header" style="font-size: 10px; height: 10px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);
    }    
    

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=new_barcode_fg_$format.xls");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_product_no = $this->input->get('filter_product_no');

        // Config
        $config = $this->db->select('*')->from('config')->get()->row();
        if (!$config) {
            echo "Konfigurasi tidak ditemukan.";
            return;
        }

        // Fetch data
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.number as item_rm_number, c.number as material');
        $this->db->from('new_barcode_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        if ($filter_from) $this->db->where('a.trans_date >=', $filter_from);
        if ($filter_to) $this->db->where('a.trans_date <=', $filter_to);
        if ($filter_product_no) $this->db->like('b.number', $filter_product_no);

        $records = $this->db->get()->result_array();

        if (empty($records)) {
            echo "Data tidak ditemukan.";
            return;
        }

        $html = '<html><head><title>Print Data</title></head>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;}
            #customers {border-collapse: collapse;width: 100%;font-size: 12px;}
            #customers td, #customers th {border: 1px solid #ddd;padding: 5px;}
            #customers tr:nth-child(even) {background-color: #f2f2f2;}
            #customers tr:hover {background-color: #ddd;}
            #customers th {padding: 8px;text-align: center;background-color: #f4f4f4;color: black;}
        </style>
        <body>
        <center>
            <table style="width: 100%;">
                <tr>
                    <td width="50" style="text-align: center;">
                        <img src="' . htmlspecialchars($config->favicon) . '" width="30">
                    </td>
                    <td style="text-align: left;">
                        <b>' . htmlspecialchars($config->name) . '</b><br>
                        <small>LABEL PACKING</small>
                    </td>
                    <td style="text-align: right;">
                        Print Date: ' . date("d M Y H:i:s") . ' <br>
                        Print By: ' . htmlspecialchars($this->session->username) . '  
                    </td>
                </tr>
            </table>
        </center>
        <br><br>
        
        <table id="customers">
            <tr>
                <th>No</th>
                <th>Transaction Date</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Material</th>
                <th>Qty WIP</th>
                <th>Qty Packing</th>
                <th>Qty Label Packing</th>
                <th>Compound Lot</th>
                <th>Production Date</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                <td style="text-align:center">' . $no . '</td>
                <td>' . htmlspecialchars($data['trans_date']) . '</td>
                <td>' . htmlspecialchars($data['item_number']) . '</td>
                <td>' . htmlspecialchars($data['item_name']) . '</td>
                <td>' . htmlspecialchars($data['material']) . '</td>
                <td>' . htmlspecialchars($data['qty_wip']) . '</td>
                <td>' . htmlspecialchars($data['qty_packing']) . '</td>
                <td>' . htmlspecialchars($data['qty_label']) . '</td>
                <td>' . htmlspecialchars($data['compound_lot']) . '</td>
                <td>' . htmlspecialchars($data['prod_date']) . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';

        echo $html;

        if ($option == "excel") {
            exit;
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $id = $post['id'];
            
            $data = [
                'trans_date' => $post['trans_date'],
                'shift' => $post['shift'],
                'leader' => $post['leader'],
                'item_fg_id' => $post['item_fg_id'],
                'qty_wip' => $post['qty_wip'],
                'operator' => $post['operator'],
                'specification' => $post['specification'],
                'compound_lot' => $post['compound_lot'],
                'prod_date' => !empty($post['prod_date']) ? date('Y-m-d', strtotime($post['prod_date'])) : date('Y-m-d'),
                'qc' => $post['qc'],
                'updated_by' => $this->session->username,
                'updated_date' => date('Y-m-d H:i:s')
            ];

            $update = $this->crud->update('new_barcode_fg', $data, ['id' => $id]);
            
            if ($update) {
                echo json_encode(['success' => true, 'message' => 'Data updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update data']);
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
