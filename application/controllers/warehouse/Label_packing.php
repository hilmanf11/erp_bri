<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Label_packing extends CI_Controller
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
            $this->load->view('control/label_packing');
        } else {
            redirect('error_access');
        }
    }
    
    public function readitemsFG()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT id, number as item_number, name as item_name, box_sub FROM item_fg WHERE item_family_number = 'RP' AND (number like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    public function readitemsRM()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name FROM bom a JOIN item_rm b ON a.item_rm_id = b.id WHERE a.item_fg_id = '$post'");
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $filter_from = $post['filter_from'] ?? "";
            $filter_to = $post['filter_to'] ?? "";
            $filter_shift = $post['filter_shift'] ?? "";
            $filter_product_no = $post['filter_product_no'] ?? "";
    
            $page = intval($post['page'] ?? 1);
            $rows = intval($post['rows'] ?? 10);
            $offset = ($page - 1) * $rows;
            $result = [];
    
            // Mulai membangun query
            $this->db->select('lp.*, 
                               fg.number AS item_number, 
                               fg.name AS item_name, 
                               rm.number AS item_rm_number, 
                               rm.name AS material, 
                               GROUP_CONCAT(lpd.serial_label ORDER BY lpd.serial_label SEPARATOR ", ") AS serial_labels');
            $this->db->from('label_packing lp');
            $this->db->join('item_fg fg', 'lp.item_fg_id = fg.id', 'left');
            $this->db->join('item_rm rm', 'lp.item_rm_id = rm.id', 'left');
            $this->db->join('label_packing_detail lpd', 'lp.serial_no = lpd.serial_no', 'left');
    
            // Terapkan filter
            if (!empty($filter_from)) {
                $this->db->where('lp.trans_date >=', $filter_from);
            }
            if (!empty($filter_to)) {
                $this->db->where('lp.trans_date <=', $filter_to);
            }
            if (!empty($filter_shift)) {
                $this->db->where('lp.shift', $filter_shift);
            }
            if (!empty($filter_product_no)) {
                $this->db->like('fg.number', $filter_product_no);
            }
    
            // Group by untuk menggabungkan serial label
            $this->db->group_by('lp.id');
    
            // Hitung total data sebelum pagination
            $totalRowsQuery = clone $this->db;
            $totalRows = $totalRowsQuery->count_all_results('', false);
    
            // Terapkan pagination
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();
    
            // Siapkan hasil
            $result['total'] = $totalRows;
            $result['rows'] = $records;
    
            // Kembalikan hasil dalam format JSON
            echo json_encode($result);
        }
    }
    


    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $date = new DateTime($post['trans_date']);
                $formatted_date = $date->format('ymd');
                $product_id = $post['item_fg_id'];
                $qty_label = intval($post['qty_label']); // Ambil jumlah qty_label
                $serial_no = $formatted_date . $product_id;

                // Simpan satu entri ke tabel label_packing
                $data = [
                    'trans_date' => $post['trans_date'],
                    'shift' => $post['shift'],
                    'leader' => $post['leader'],
                    'item_fg_id' => $post['item_fg_id'],
                    'item_rm_id' => $post['item_rm_id'],
                    'qty_wip' => $post['qty_wip'],
                    'qty_packing' => $post['qty_packing'],
                    'qty_label' => $qty_label, // Total qty_label
                    'packing_size' => $post['packing_size'],
                    'compound_lot' => $post['compound_lot'],
                    'prod_date' => date('Y-m-d', strtotime($post['prod_date'])),
                    'operator' => $post['operator'] ?: null,
                    'serial_no' => $serial_no,
                    'qc' => $post['qc'] ?: null,
                    'created_by' => $this->session->username,
                    'created_date' => date('Y-m-d H:i:s')
                ];
                $label_packing_id = $this->crud->create('label_packing', $data);

                // Simpan beberapa entri ke tabel label_packing_detail
                for ($i = 1; $i <= $qty_label; $i++) {
                    $sequence = sprintf("%04d", $i); // Sequence sesuai dengan iterasi
                    $serial_label = $formatted_date . $product_id . $sequence;

                    $detail_data = [
                        'created_by' => $this->session->username,
                        'created_date' => date('Y-m-d H:i:s'),
                        'serial_label' => $serial_label,
                        'serial_no' => $serial_no,
                        'item_fg_id' => $post['item_fg_id'],
                        'qty_packing' => $post['qty_packing']
                    ];
                    $this->crud->create('label_packing_detail', $detail_data);
                }

                echo json_encode(['success' => true, 'message' => 'Data saved successfully', 'serial_no' => $serial_no]); // Tambahkan serial_no ke response
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

        // Pastikan serial_no dan id ada dalam data
        if (!isset($data['serial_no']) || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing serial_no or id']);
            return;
        }

        $serial_no = $data['serial_no']; // Ambil serial_no dari data yang dikirim

        // Hapus dari tabel label_packing_detail berdasarkan serial_no
        $this->crud->delete('label_packing_detail', ["serial_no" => $serial_no]);

        // Hapus dari tabel label_packing berdasarkan id
        $deleteLabelPacking = $this->crud->delete('label_packing', ["id" => $data['id']]);
        
        if ($deleteLabelPacking) {
            echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete data']);
        }
    }

    public function print_label() {
        $serial_nos = $this->input->get('serial_nos');
    
        if (empty($serial_nos)) {
            show_error("Missing parameter: serial_nos", 400);
        }
    
        // Pecah data serial_nos dari URL menjadi array
        $serial_nos_array = explode(',', $serial_nos);
    
        // Query untuk mengambil data berdasarkan serial_label
        $this->db->select('
            lp.trans_date, 
            lp.shift, 
            lp.leader, 
            lp.packing_size, 
            lp.compound_lot, 
            lp.prod_date, 
            lp.operator, 
            lp.qc, 
            lpd.serial_label, 
            item_fg.number AS product_no, 
            item_fg.name AS product_name, 
            item_rm.name AS material_name,
            lpd.qty_packing
        ');
        $this->db->from('label_packing lp');
        $this->db->join('label_packing_detail lpd', 'lpd.serial_no = lp.serial_no', 'left');
        $this->db->join('item_fg', 'lp.item_fg_id = item_fg.id', 'left');
        $this->db->join('item_rm', 'lp.item_rm_id = item_rm.id', 'left');
        $this->db->where_in('lpd.serial_no', $serial_nos_array);
    
        $label_packing_details = $this->db->get()->result();
    
        if (empty($label_packing_details)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }
    
        // Ambil serial_label pertama untuk title
        $first_serial_label = $label_packing_details[0]->serial_label;
    
        // Generate QR Code untuk setiap serial_label
        foreach ($label_packing_details as $detail) {
            $this->createQrcode($detail->serial_label, "assets/image/qrcode/");
        }
    
        // HTML Template
        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 2px; }
                            table { border-collapse: collapse; width: 50mm; height: 50mm; font-size: 9px; border: 2px solid black; table-layout: fixed; }
                            th, td { border: 2px solid black; padding: 1px; text-align: left; }
                            th { text-align: center; font-size: 10px; font-weight: bold; }
                            .header { text-align: center; font-size: 11px; font-weight: bold; }
                            .logo { text-align: center; width: 100%; padding: 2px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 9px; text-align: center; height: 12mm; vertical-align: bottom; }
                            .qc-sign { text-align: center; height: 10mm; }
                            .qr-code img { width: 30px; height: 30px; display: block; margin: 0 auto; }
                            .serial-label { font-size: 8px; text-align: center; word-wrap: break-word; overflow: hidden; }
                        </style>
                    </head>
                    <body>';
    
        foreach ($label_packing_details as $detail) {
            $html .= '<table>
                        <tr>
                            <td class="logo" colspan="3">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25"/><br>
                                <span class="header">LABEL PACKING</span>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Product No:</b></td>
                            <td colspan="2">' . $detail->product_no . '</td>
                        </tr>
                        <tr>
                            <td><b>Product Name:</b></td>
                            <td colspan="2">' . $detail->product_name . '</td>
                        </tr>
                        <tr>
                            <td><b>Material:</b></td>
                            <td colspan="2">' . $detail->material_name . '</td>
                        </tr>
                        <tr>
                            <td><b>Prod Date:</b></td>
                            <td colspan="2">' . $detail->prod_date . '</td>
                        </tr>
                        <tr>
                            <td><b>Pack Date:</b></td>
                            <td colspan="2">' . $detail->trans_date . '</td>
                        </tr>
                        <tr>
                            <td><b>Shift:</b></td>
                            <td colspan="2">' . $detail->shift . '</td>
                        </tr>
                        <tr>
                            <td><b>Qty/Pack:</b></td>
                            <td colspan="2">' . $detail->qty_packing . '</td>
                        </tr>
                        <tr>
                            <td><b>No LOT:</b></td>
                            <td colspan="2">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th>Operator</th>
                            <th>QC</th>
                            <th>QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign">' . $detail->operator . '</td>
                            <td class="qc-sign">
                                <img src="' . base_url('assets/image/qc_passed.png') . '" width="35"/><br>' . $detail->qc . '
                            </td>
                            <td class="qr-code">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->serial_label . '.png') . '"/>
                                <div class="serial-label">' . $detail->serial_label . '</div>
                            </td>
                        </tr>
                    </table><br>';
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
            header("Content-Disposition: attachment; filename=label_packing_$format.xls");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_shift = $this->input->get('filter_shift');
        $filter_product_no = $this->input->get('filter_product_no');

        // Config
        $config = $this->db->select('*')->from('config')->get()->row();
        if (!$config) {
            echo "Konfigurasi tidak ditemukan.";
            return;
        }

        // Fetch data
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.number as item_rm_number, c.name as material');
        $this->db->from('label_packing a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        if ($filter_from) $this->db->where('a.trans_date >=', $filter_from);
        if ($filter_to) $this->db->where('a.trans_date <=', $filter_to);
        if ($filter_shift) $this->db->where('a.shift', $filter_shift);
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
                <th>Packing Size</th>
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
                <td>' . htmlspecialchars($data['packing_size']) . '</td>
                <td>' . htmlspecialchars($data['compound_lot']) . '</td>
                <td>' . htmlspecialchars($data['prod_date']) . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';

        echo $html;

        // Jika mode excel, pastikan tidak ada output lain
        if ($option == "excel") {
            exit;
        }
    }
}
