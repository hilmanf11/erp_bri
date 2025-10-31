<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_to_subconts extends CI_Controller
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
            $this->load->view('control/delivery_to_subconts');
        } else {
            redirect('error_access');
        }
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

    public function readDelivery_note_no()
    {
        $filter_from = $this->input->get("filter_from");
        $filter_to   = $this->input->get("filter_to");
        $delivery_to = $this->input->get("delivery_to");

        $this->db->distinct();
        $this->db->select('delivery_note_no');
        $this->db->from('delivery_to_subconts');
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

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $delivery_date = $this->input->post('delivery_date');
        $delivery_date = $delivery_date ?: date('Y-m-d');
        $destination_id = $this->input->post('destination');

        // $period = date('Ym', strtotime($delivery_date));

        $query = "
            SELECT
                a.item_fg_id,
                b.number,
                b.name,
                a.workorder,
                COALESCE(SUM(a.qty_ok), 0) AS total_qty_ok,
                COALESCE(d.qty_delivery_total, 0) AS total_qty_delivery,
                (COALESCE(SUM(a.qty_ok), 0) - COALESCE(d.qty_delivery_total, 0)) AS qty_output,
                MIN(a.trans_date) AS trans_date,
                MIN(a.wp) AS wp,
                b.uom
            FROM output_production_press a
            JOIN item_fg b ON a.item_fg_id = b.id
            LEFT JOIN (
                SELECT 
                    dt.item_fg_id,
                    dt.workorder,
                    SUM(dt.qty_delivery) AS qty_delivery_total
                FROM delivery_to_subconts dt
                WHERE dt.deleted = 0
                GROUP BY dt.item_fg_id, dt.workorder
            ) d ON d.item_fg_id = a.item_fg_id AND d.workorder = a.workorder

            JOIN setting_subconts sc ON sc.item_fg_id = a.item_fg_id  
                AND (sc.subcont_id = '$destination_id' OR sc.teaching_factory_id = '$destination_id')
                AND sc.deleted = 0

            WHERE (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
            GROUP BY a.item_fg_id, a.workorder, b.number, b.name, b.uom, d.qty_delivery_total
            HAVING (COALESCE(SUM(a.qty_ok), 0) - COALESCE(d.qty_delivery_total, 0)) > 0
            ORDER BY (MIN(a.wp) + 0) ASC, MIN(a.wp) ASC, MIN(a.trans_date) ASC, a.workorder ASC, b.number ASC
        ";

        // WHERE a.period = '$period'

        $send = $this->crud->query($query);
        echo json_encode($send);
    }

    // public function readItemFgv1()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
        
    //     // $period     = base64_decode($this->input->post('period'));
    //     // $wp         = base64_decode($this->input->post('wp'));
    //     // $machine_id = base64_decode($this->input->post('machine_id'));

    //     $delivery_date = $this->input->post('delivery_date');

    //     $delivery_date = $delivery_date ?: date('Y-m-d');

    //     // $date_min = date('Y-m-d', strtotime("$delivery_date -7 days"));
    //     // $date_max = date('Y-m-d', strtotime("$delivery_date +7 days"));

    //     // $date_min = date('Y-m-d', strtotime("$delivery_date -30 days"));
    //     // $date_max = date('Y-m-d', strtotime("$delivery_date +30 days"));

    //     // $period = date('Ym', strtotime("$delivery_date"));

    //     $query = "
    //         SELECT DISTINCT 
    //             a.item_fg_id, 
    //             b.number, 
    //             b.name, 
    //             a.workorder,
    //             a.mold_id,
    //             a.operator,
    //             a.wp,
    //             a.shift,
    //             a.period,
    //             a.number as number_output,
    //             SUM(a.qty_ok) as qty_output,
    //             a.trans_date,
    //             b.uom
    //         FROM output_production_press a 
    //         JOIN item_fg b ON a.item_fg_id = b.id
    //             AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%')
    //         GROUP BY a.workorder
    //         ORDER BY (a.wp + 0) ASC, a.wp ASC, a.trans_date ASC, a.workorder ASC, b.number ASC
    //         ";
            
    //     // WHERE a.period = '$period'
    //     // WHERE a.trans_date BETWEEN '$date_min' AND '$date_max'
    //     // AND (b.number LIKE '%$post%' OR b.name LIKE '%$post%')

    //     $send = $this->crud->query($query);
    //     echo json_encode($send);
    // }

    // public function delivery_note_no($type = "")
    // {
    //     $trans_date = $this->input->post('trans_date');
    //     $destination_code = $this->input->post('destination_code');

    //     $ym = $trans_date ? date("ym", strtotime($trans_date)) : date("ym");
    //     $month = date("m", strtotime($trans_date ?: date("Y-m-d")));
    //     $year = date("y", strtotime($trans_date ?: date("Y-m-d")));

    //     $sql = $this->db->query("
    //         SELECT MAX(SUBSTRING_INDEX(delivery_note_no, '/', 1)) AS kode
    //         FROM delivery_to_subconts 
    //         WHERE delivery_note_no LIKE '%/{$destination_code}/{$month}/{$year}'
    //     ");
    //     $row = $sql->row();

    //     if ($row->kode == null) {
    //         $seq = "001";
    //     } else {
    //         $seq = sprintf("%03s", intval($row->kode) + 1);
    //     }

    //     $autonumber = "{$seq}/{$destination_code}/{$month}/{$year}";

    //     if($type == "return") {
    //         return $autonumber;
    //     }

    //     echo $autonumber;
    // }


    public function delivery_note_no($type = "")
    {
        $trans_date = $this->input->post('trans_date');
        $destination_code = $this->input->post('destination_code');

        $date = $trans_date ? date("Y-m-d", strtotime($trans_date)) : date("Y-m-d");
        $month = date("m", strtotime($date));
        $year = date("y", strtotime($date));
        $day = date("d", strtotime($date));

        // Tentukan periode reset berdasarkan tanggal 16
        if ($day < 16) {
            $period_start = date("Y-m-16", strtotime("-1 month", strtotime($date)));
            $period_end   = date("Y-m-15", strtotime($date));
        } else {
            $period_start = date("Y-m-16", strtotime($date));
            $period_end   = date("Y-m-15", strtotime("+1 month", strtotime($date)));
        }

        $sql = $this->db->query("
            SELECT MAX(SUBSTRING_INDEX(delivery_note_no, '/', 1)) AS kode
            FROM delivery_to_subconts
            WHERE delivery_note_no LIKE '%/{$destination_code}/{$month}/{$year}'
            AND delivery_date BETWEEN '{$period_start}' AND '{$period_end}'
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

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_delivery_to = @base64_decode($get['filter_delivery_to']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select("a.*, COALESCE(c.name, d.name) as destination_name");
            $this->db->from('delivery_to_subconts a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.destination = c.id', 'left');
            $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');
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

    //GET DATATABLES DETAILS
    // public function datatableDetails()
    // {
    //     if ($this->input->get()) {
    //         $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));
    //         // $product_family = base64_decode($this->input->get('product_family'));

    //         $subquery = "(SELECT workorder, item_fg_id, MAX(trans_date) AS prod_date
    //                   FROM output_production_press
    //                   GROUP BY workorder, item_fg_id) c";

    //         $this->db->select("a.*, b.number as item_fg_number, b.name as item_fg_name, c.trans_date as prod_date, b.uom");
    //         $this->db->from('delivery_to_subconts a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //         $this->db->join($subquery, 'a.workorder = c.workorder AND a.item_fg_id = c.item_fg_id', 'left');

    //         // $this->db->where('a.delivery_note_no', $delivery_note_no);
    //         // if ($product_family != "") {
    //         //     $this->db->where('b.item_family_number', $product_family);
    //         // }
    //         $this->db->order_by('a.workorder');
    //         $records = $this->db->get()->result_array();

    //         echo json_encode($records);
    //     }
    // }

    public function datatableDetails()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));
            // $product_family = base64_decode($this->input->get('product_family'));

            // $this->db->select("a.*, b.number AS item_fg_number, b.name AS item_fg_name, b.uom");

            $this->db->select("
                a.item_fg_id,
                a.workorder,
                a.prod_date,
                SUM(a.qty_delivery) AS qty_delivery,
                a.remarks,
                b.number AS item_fg_number,
                b.name AS item_fg_name,
                b.uom
            ");

            $this->db->from('delivery_to_subconts a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            // if ($product_family != "") {
            //     $this->db->where('b.item_family_number', $product_family);
            // }

            $this->db->group_by([
                'a.item_fg_id',
                'a.workorder',
            ]);

            $this->db->order_by('a.workorder');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_note_no = base64_decode($this->input->get('delivery_note_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('delivery_to_subconts a');
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
                "prod_date" => $post['prod_date'],
                "workorder" => $post['workorder'],
                "qty_output" => $post['qty_output'],
                "qty_delivery" => $post['qty_delivery'],
                "remarks" => $post['remarks'],
            );

            if (!isset($post['workorder']) || $post['workorder'] === '' || $post['workorder'] === 'null') {
                $post['workorder'] = null;
            }

            if (@$post['id'] != "") {
                $send = $this->crud->update('delivery_to_subconts', ["id" => $post['id']], $dataFinal);
            } else {
                // $checkOutputPress = $this->crud->read('delivery_to_subconts', [], [
                //     "period"     => $post['period'],
                //     // "trans_date" => $post['trans_date'],
                //     "wp"         => $post['wp'],
                //     "shift"      => $post['shift'],
                //     "machine_id" => $post['machine_id'],
                //     "item_fg_id" => $post['item_fg_id'],
                //     "workorder"  => $post['workorder'],
                // ]);

                // $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
                // $machine = $this->crud->read("machines", [], ["id" => $post['machine_id']]);

                // if (!empty($checkOutputPress)) {
                //     echo json_encode(array(
                //         "title"   => "Duplicate Data",
                //         "message" => "Duplicate Data for Product {$item_fg->number} on Machine {$machine->number} (Period: {$post['period']}, WP: {$post['wp']}, Shift: {$post['shift']}, Workorder: {$post['workorder']}).",
                //         "theme"   => "error"
                //     ));
                //     exit;
                // }

                $send = $this->crud->create('delivery_to_subconts', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('delivery_to_subconts', $data);
        echo $send;
    }

    public function print_dn_to_sc($delivery_note_no)
    {
        $delivery_note_no = base64_decode($delivery_note_no);

        $this->db->query("UPDATE delivery_to_subconts SET printed=1 WHERE `delivery_note_no` = '$delivery_note_no'");

        $delivery_to_subconts = $this->crud->reads('delivery_to_subconts', [], ["delivery_note_no" => $delivery_note_no]);

        $delivery_to_subcont = $this->crud->read('delivery_to_subconts', [], ["delivery_note_no" => $delivery_note_no]);

        $config = $this->db->get('config')->row();

        // Tentukan ukuran kertas dan jumlah baris per halaman
        if (count($delivery_to_subconts) <= 5) {
            //$paper_size = '9.5in 11in';
            // $paper_width = '40cm';
            // $paper_height = '14cm';
            $rows_per_page = 5; // Maksimal 5 produk per halaman
        } else {
            //$paper_size = '9.5in 11in';
            // $paper_width = '40cm';
            // $paper_height = '28cm';
            $rows_per_page = 10; // Maksimal 10 produk per halaman
        }

        // Hapus atau ganti karakter '/' dari nomor Delivery Note
        // $clean_delivery_order_no = str_replace('/', '-', $delivery_to_subconts->delivery_note_no);

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
        $page = ceil(count($delivery_to_subconts) / $rows_per_page);
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom, COALESCE(c.name, d.name) as destination_name, COALESCE(c.address, d.address) as address');
            $this->db->from('delivery_to_subconts a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('subconts c', 'a.destination = c.id', 'left');
            $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');
            $this->db->where('a.delivery_note_no', $delivery_note_no);
            $this->db->order_by('a.workorder', 'asc');
            $this->db->order_by('b.number', 'asc');
            // $this->db->limit($rows_per_page, ($i * $rows_per_page));
            $records = $this->db->get()->result_array();


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
                                            <th>Qty Delivery</th>
                                            <th>Remarks</th>
                                        </tr>';
            foreach ($records as $record) {
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record['item_fg_number'] . '</td>
                            <td>' . $record['item_fg_name'] . '</td>
                            <td>' . $record['workorder'] . '</td>
                            <td style="text-align:center">' . number_format($record['qty_delivery'], 0, ",", ".") . '</td>
                            <td style="text-align:center">' . $record['remarks'] ?? '' . '</td>
                        </tr>';
                $no++;
            }
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
        $html .= '</td>
                                            <td style="height:35pt"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;"></td>
                                            <td style="text-align:center;border:none;"></td>
                                            <td style="text-align:center;">';

        $html .= '</td>
                                            <td style="text-align:center; height: 13pt;"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_to_subconts_report_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_delivery_to = @base64_decode($get['filter_delivery_to']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_delivery_note_no = @base64_decode($get['filter_delivery_note_no']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom, COALESCE(c.name, d.name) as destination_name, COALESCE(c.address, d.address) as address');
        $this->db->from('delivery_to_subconts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('subconts c', 'a.destination = c.id', 'left');
        $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');
        
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

        //$this->db->group_by('a.delivery_note_no');
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
            <h3 style="margin:0;">DELIVERY NOTES REPORT</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <div class="table-container">
            <table id="customers" border="1">
                    <tr>
                        <th style="width: 10px;">No</th>
                        <th style="width: 80px;">Delivery Date</th>
                        <th style="width: 120px;">Delivery Note No</th>
                        <th style="width: 100px;">Product ID</th>
                        <th style="width: 100px;">Product No</th>
                        <th style="width: 150px;">Product Name</th>
                        <th style="width: 80px;">WO No</th>
                        <th style="width: 80px;">Qty Delivery</th>
                        <th style="width: 100px;">Remarks</th>
                    </tr>';

        $no = 1;
        foreach ($records as $row) {
            $html .= '<tr>
                        <td class="text-center">'.$no.'</td>
                        <td class="no-wrap">'.date('Y-m-d', strtotime($row['delivery_date'])).'</td>
                        <td class="no-wrap">'.$row['delivery_note_no'].'</td>
                        <td class="no-wrap">'.$row['item_fg_id'].'</td>
                        <td class="no-wrap" style="mso-number-format:&quot;@&quot;">'.$row['item_fg_number'].'</td>
                        <td class="no-wrap">'.$row['item_fg_name'].'</td>
                        <td class="no-wrap">'.$row['workorder'].'</td>
                        <td style="text-align: center;">'.number_format($row['qty_delivery'],0,".",".").'</td>
                        <td class="no-wrap" style="text-align: center;">'.$row['remarks'].'</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></div>';
        echo $html;
    }
}
