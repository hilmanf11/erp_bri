<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_finishing_invoices extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/generate_finishing_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        $arr = array();
        
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            // Tambahkan (string) di sini
            $arr[] = array("id" => (string)$i, "name" => (string)$i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        $arr = array();
        
        foreach ($months as $key => $value) {
            // Tambahkan (string) agar ID pasti terbaca sebagai teks oleh EasyUI
            $arr[] = array("id" => (string)$key, "name" => $value);
        }

        echo json_encode($arr);
    }

    // public function readMonths()
    // {
    //     $months = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
    //     foreach ($months as $key => $value) {
    //         $arr[] = array("id" => $key, "name" => $value);
    //     }

    //     echo json_encode($arr);
    // }

    public function readInvoice()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT * FROM generate_finishing_invoices WHERE `status` = 0 and finishing_invoice_no LIKE '%$post%' ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readSubcont()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        if (!empty($post)) {
            $records = $this->crud->query("
                SELECT id AS subcont, name, 'Teaching Factory' as source FROM teaching_factory WHERE name LIKE '%$post%'
                UNION 
                SELECT id AS subcont, name, 'Subcont' as source FROM subconts WHERE name LIKE '%$post%'
                ORDER BY subcont ASC
            ");
        } else {
            $records = $this->crud->query("
                SELECT id AS subcont, name, 'Teaching Factory' as source FROM teaching_factory 
                UNION 
                SELECT id AS subcont, name, 'Subcont' as source FROM subconts 
                ORDER BY subcont ASC
            ");
        }

        echo json_encode($records);
    }

    // public function getData()
    // {
    //     if ($this->input->get()) {
    //         file_put_contents('failed/generate_finishing_invoices.txt', '');
            
    //         $filter_month = base64_decode($this->input->get('filter_month'));
    //         $filter_year = base64_decode($this->input->get('filter_year'));
    //         $filter_cutoff_from = base64_decode($this->input->get('filter_cutoff_from'));
    //         $filter_cutoff_to = base64_decode($this->input->get('filter_cutoff_to'));
    //         $filter_subcont = base64_decode($this->input->get('filter_subcont')); // ID Subcont / TF

    //         $subcont_number = "";
    //         if (!empty($filter_subcont)) {
    //             $q_source = $this->db->query("
    //                 SELECT number FROM subconts WHERE id = '$filter_subcont'
    //                 UNION 
    //                 SELECT number FROM teaching_factory WHERE id = '$filter_subcont'
    //             ")->row();
    //             if ($q_source) {
    //                 $subcont_number = $q_source->number;
    //             }
    //         }

    //         $this->db->select("a.item_fg_id, b.number AS part_number, b.name AS part_name, a.price");
    //         $this->db->from('setting_subconts a');
    //         $this->db->join('item_fg b', "a.item_fg_id = b.id");
    //         $this->db->where('a.deleted', 0);
    //         if ($filter_subcont != "") {
    //             $this->db->where("(a.subcont_id = '$filter_subcont' OR a.teaching_factory_id = '$filter_subcont')", NULL, FALSE);
    //         }
    //         $this->db->group_by('a.item_fg_id');
    //         $this->db->order_by('b.number', 'asc');
    //         $master_items = $this->db->get()->result_array();

    //         $this->db->select("item_fg_id, SUM(qty) AS total_qty_fg");
    //         $this->db->from("scan_incoming_sctf");
    //         $this->db->where('incoming_from', $filter_subcont);
    //         $this->db->where('incoming_type', 'Regular');
    //         $this->db->where('incoming_date >=', $filter_cutoff_from);
    //         $this->db->where('incoming_date <=', $filter_cutoff_to);
    //         $this->db->where('deleted', 0);
    //         $this->db->group_by('item_fg_id');
    //         $fg_result = $this->db->get()->result_array();
            
    //         $map_fg = [];
    //         foreach ($fg_result as $row) {
    //             $map_fg[$row['item_fg_id']] = floatval($row['total_qty_fg']);
    //         }

    //         // $this->db->select("
    //         //     b.item_fg_id, 
    //         //     SUM(CASE WHEN ng.ng_code = 'NG-13' THEN ng.qty_ng ELSE 0 END) AS total_qty_ng,
    //         //     SUM(b.qty_rework) AS total_qty_rework
    //         // ", false);
    //         // $this->db->from("scan_visual_checker_detail b");
    //         // $this->db->join("scan_visual_checker a", "a.id = b.visual_checker_id");
    //         // $this->db->join("scan_visual_checker_ng ng", "b.id = ng.detail_id", "left");
    //         // $this->db->where("a.check_date >=", $filter_cutoff_from);
    //         // $this->db->where("a.check_date <=", $filter_cutoff_to);
    //         // $this->db->where("b.source", $subcont_number); // Pakai nomor source yang dicari
    //         // $this->db->where('a.deleted', 0);
    //         // $this->db->where('b.deleted', 0);
    //         // $this->db->where('b.type_status', 'finished');
    //         // $this->db->group_by('b.item_fg_id');
    //         // $ng_result = $this->db->get()->result_array();

    //         // // Mapping NG dan Rework ke Array (Key = item_fg_id)
    //         // $map_ng = [];
    //         // $map_rework = [];
    //         // foreach ($ng_result as $row) {
    //         //     $map_ng[$row['item_fg_id']]     = floatval($row['total_qty_ng']);
    //         //     $map_rework[$row['item_fg_id']] = floatval($row['total_qty_rework']);
    //         // }

    //         $this->db->select("
    //             b.item_fg_id, 
    //             SUM(COALESCE(ng.ng_finishing, 0)) AS total_qty_ng,
    //             SUM(b.qty_rework) AS total_qty_rework
    //         ", false);
    //         $this->db->from("scan_visual_checker_detail b");
    //         $this->db->join("scan_visual_checker a", "a.id = b.visual_checker_id");
    //         $this->db->join("
    //             (SELECT 
    //                 detail_id, 
    //                 SUM(qty_ng) AS ng_finishing 
    //             FROM scan_visual_checker_ng 
    //             WHERE ng_code = 'NG-13' 
    //             GROUP BY detail_id
    //             ) ng", "b.id = ng.detail_id", "left");

    //         $this->db->where("a.check_date >=",$filter_cutoff_from);
    //         $this->db->where("a.check_date <=",$filter_cutoff_to);
    //         $this->db->where("b.source",$subcont_number); // Pakai nomor source yang dicari
    //         $this->db->where('a.deleted', 0);
    //         $this->db->where('b.deleted', 0);
    //         $this->db->where('b.type_status', 'finished');
    //         $this->db->group_by('b.item_fg_id');

    //         $ng_result =$this->db->get()->result_array();

    //         // Mapping NG dan Rework ke Array (Key = item_fg_id)
    //         $map_ng = [];
    //         $map_rework = [];
    //         foreach ($ng_result as$row) {
    //             $map_ng[$row['item_fg_id']]     = floatval($row['total_qty_ng']);
    //             $map_rework[$row['item_fg_id']] = floatval($row['total_qty_rework']);
    //         }

    //         $arr = [];
    //         foreach ($master_items as $data) {
    //             $item_id = $data['item_fg_id'];
    //             $price   = floatval($data['price']);
                
    //             // Ambil data QTY dari array mapping
    //             $qty_fg     = isset($map_fg[$item_id]) ? $map_fg[$item_id] : 0;
    //             $qty_ng     = isset($map_ng[$item_id]) ? $map_ng[$item_id] : 0;
    //             $qty_rework = isset($map_rework[$item_id]) ? $map_rework[$item_id] : 0;

    //             // Lewati (skip) jika ketiga QTY kosong semua
    //             if ($qty_fg == 0 && $qty_ng == 0 && $qty_rework == 0) {
    //                 continue;
    //             }

    //             // Kalkulasi nominal (Rp)
    //             $rp_fg     = $qty_fg * $price;
    //             $rp_ng     = $qty_ng * $price;
    //             $rp_rework = $qty_rework * $price;
                
    //             // Rumus terbaru: FG (Rp) - Rework (Rp) - NG (Rp)
    //             $total_pendapatan = $rp_fg - $rp_rework - $rp_ng;

    //             $arr[] = array(
    //                 // --- Data Header ---
    //                 "subcont_id"       => $filter_subcont,
    //                 "p_month"          => $filter_month,
    //                 "p_year"           => $filter_year,
    //                 "cutoff_from"      => $filter_cutoff_from,
    //                 "cutoff_to"        => $filter_cutoff_to,

    //                 // --- Data Detail ---
    //                 "item_fg_id"       => $item_id,
    //                 "part_number"      => $data['part_number'],
    //                 "part_name"        => $data['part_name'],
    //                 "price"            => $price,
                    
    //                 "qty_fg"           => $qty_fg,
    //                 "rp_fg"            => $rp_fg,
                    
    //                 "qty_ng"           => $qty_ng,
    //                 "rp_ng"            => $rp_ng,
                    
    //                 "qty_rework"       => $qty_rework,
    //                 "rp_rework"        => $rp_rework,
                    
    //                 "total_pendapatan" => $total_pendapatan
    //             );
    //         }

    //         // var_dump($arr);
    //         // return;

    //         die(json_encode($arr, JSON_NUMERIC_CHECK));

    //         } else {
    //             show_error("Cannot Process your request");
    //         }
    // }

    public function getData()
    {
        if ($this->input->get()) {
            file_put_contents('failed/generate_finishing_invoices.txt', '');
            
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_cutoff_from = base64_decode($this->input->get('filter_cutoff_from'));
            $filter_cutoff_to = base64_decode($this->input->get('filter_cutoff_to'));
            $filter_subcont = base64_decode($this->input->get('filter_subcont')); 

            $subcont_number = "";
            if (!empty($filter_subcont)) {
                $q_source = $this->db->query("
                    SELECT number FROM subconts WHERE id = '$filter_subcont'
                    UNION 
                    SELECT number FROM teaching_factory WHERE id = '$filter_subcont'
                ")->row();
                if ($q_source) {
                    $subcont_number = $q_source->number;
                }
            }

            $this->db->select("a.item_fg_id, b.number AS part_number, b.name AS part_name, a.price");
            $this->db->from('setting_subconts a');
            $this->db->join('item_fg b', "a.item_fg_id = b.id");
            $this->db->where('a.deleted', 0);
            if ($filter_subcont != "") {
                $this->db->where("(a.subcont_id = '$filter_subcont' OR a.teaching_factory_id = '$filter_subcont')", NULL, FALSE);
            }
            $this->db->group_by('a.item_fg_id');
            $this->db->order_by('b.number', 'asc');
            $master_items = $this->db->get()->result_array();

            $this->db->select("item_fg_id, SUM(qty) AS total_qty_fg");
            $this->db->from("scan_incoming_sctf");
            $this->db->where('incoming_from', $filter_subcont);
            $this->db->where('incoming_type', 'Regular');
            $this->db->where('incoming_date >=', $filter_cutoff_from);
            $this->db->where('incoming_date <=', $filter_cutoff_to);
            $this->db->where('deleted', 0);
            $this->db->group_by('item_fg_id');
            $fg_result = $this->db->get()->result_array();
            
            $map_fg = [];
            foreach ($fg_result as $row) {
                $map_fg[$row['item_fg_id']] = floatval($row['total_qty_fg']);
            }

            $this->db->select("
                b.item_fg_id, 
                SUM(COALESCE(ng.ng_finishing, 0)) AS total_qty_ng,
                SUM(b.qty_rework) AS total_qty_rework
            ", false);
            $this->db->from("scan_visual_checker_detail b");
            $this->db->join("scan_visual_checker a", "a.id = b.visual_checker_id");
            $this->db->join("
                (SELECT 
                    detail_id, 
                    SUM(qty_ng) AS ng_finishing 
                FROM scan_visual_checker_ng 
                WHERE ng_code = 'NG-13' 
                GROUP BY detail_id
                ) ng", "b.id = ng.detail_id", "left");

            $this->db->where("a.check_date >=",$filter_cutoff_from);
            $this->db->where("a.check_date <=",$filter_cutoff_to);
            $this->db->where("b.source",$subcont_number); 
            $this->db->where('a.deleted', 0);
            $this->db->where('b.deleted', 0);
            $this->db->where('b.type_status', 'finished');
            $this->db->group_by('b.item_fg_id');

            $ng_result =$this->db->get()->result_array();

            $map_ng = [];
            $map_rework = [];
            foreach ($ng_result as $row) {
                $map_ng[$row['item_fg_id']]     = floatval($row['total_qty_ng']);
                $map_rework[$row['item_fg_id']] = floatval($row['total_qty_rework']);
            }

            $arr = [];
            foreach ($master_items as $data) {
                $item_id = $data['item_fg_id'];
                $price   = floatval($data['price']);
                
                $qty_fg     = isset($map_fg[$item_id]) ? $map_fg[$item_id] : 0;
                $qty_ng     = isset($map_ng[$item_id]) ? $map_ng[$item_id] : 0;
                $qty_rework = isset($map_rework[$item_id]) ? $map_rework[$item_id] : 0;

                if ($qty_fg == 0 && $qty_ng == 0 && $qty_rework == 0) {
                    continue;
                }

                // Kalkulasi nominal dengan pembulatan 2 desimal Req Pa ARIF H
                $rp_fg     = round($qty_fg * $price, 2);
                $rp_ng     = round($qty_ng * $price, 2);
                $rp_rework = round($qty_rework * $price, 2);
                
                $total_pendapatan = round($rp_fg - $rp_rework - $rp_ng, 2);

                $arr[] = array(
                    "subcont_id"       => $filter_subcont,
                    "p_month"          => $filter_month,
                    "p_year"           => $filter_year,
                    "cutoff_from"      => $filter_cutoff_from,
                    "cutoff_to"        => $filter_cutoff_to,

                    "item_fg_id"       => $item_id,
                    "part_number"      => $data['part_number'],
                    "part_name"        => $data['part_name'],
                    "price"            => $price,
                    
                    "qty_fg"           => $qty_fg,
                    "rp_fg"            => $rp_fg,
                    
                    "qty_ng"           => $qty_ng,
                    "rp_ng"            => $rp_ng,
                    
                    "qty_rework"       => $qty_rework,
                    "rp_rework"        => $rp_rework,
                    
                    "total_pendapatan" => $total_pendapatan
                );
            }

            die(json_encode($arr, JSON_NUMERIC_CHECK));

        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function datatables()//pembatasan user
    // {
    //     $filter_cutoff_from = $this->input->get('filter_cutoff_from');
    //     $filter_cutoff_to   = $this->input->get('filter_cutoff_to');
        
    //     $filter_invoice_no  = $this->input->get_post('filter_invoice_no');
    //     $filter_subcont     = $this->input->get_post('filter_subcont');

    //     $user = $this->crud->currentUserDept();

    //     $page = $this->input->post('page');
    //     $rows = $this->input->post('rows');
        
    //     $page   = isset($page) ? intval($page) : 1;
    //     $rows   = isset($rows) ? intval($rows) : 10;
    //     $offset = ($page - 1) * $rows;
    //     $result = array();
        
    //     $this->db->select('
    //         a.*, 
    //         COALESCE(tf.name, sc.name) as vendor_name
    //     ');
    //     $this->db->from('generate_finishing_invoices a');
    //     $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
    //     $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
    //     $this->db->join('users u', 'u.username = a.created_by', 'left');

    //     if (!empty($user->department_id) && !in_array($user->department, $this->crud->getIgnoreDept())) {
    //         $this->db->where('u.department_id', $user->department_id);
    //     }

    //     $this->db->where_in('a.deleted', [0, 2]);

    //     if ($filter_cutoff_from != "" && $filter_cutoff_to != "") {
    //         $this->db->where('a.cutoff_from >=', $filter_cutoff_from);
    //         $this->db->where('a.cutoff_to <=', $filter_cutoff_to);
    //     }
    //     if ($filter_invoice_no != "") {
    //         $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
    //     }
    //     if ($filter_subcont != "") {
    //         $this->db->where('a.subcont', $filter_subcont);
    //     }

    //     $sort = $this->input->post('sort');
    //     $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
    //     if ($sort == "finishing_invoice_no") {
    //         $this->db->order_by('a.finishing_invoice_no', $order);
    //     } else {
    //         $this->db->order_by('a.created_date', 'DESC');
    //     }

    //     $totalRows = $this->db->count_all_results('', false);
    //     $this->db->limit($rows, $offset);
    //     $records = $this->db->get()->result_array();

    //     $arr = [];
    //     foreach ($records as $record) {
    //         $arr[] = array(
    //             "id"                   => $record['id'], 
    //             "finishing_invoice_no" => $record['finishing_invoice_no'],
                
    //             // Kolom baru sesuai database
    //             "p_month"              => $record['p_month'],
    //             "p_year"               => $record['p_year'],
    //             "cutoff_from"          => $record['cutoff_from'],
    //             "cutoff_to"            => $record['cutoff_to'],
                
    //             "vendor_name"          => $record['vendor_name'],
    //             "total"                => number_format($record['total'], 0, ',', '.'),
    //             "biaya_fee"            => number_format($record['biaya_fee'], 0, ',', '.'),
    //             "grand_total"          => number_format($record['grand_total'], 0, ',', '.'),
    //             "deleted"              => $record['deleted'],
    //             "status"               => $record['status'],
    //             "approved_to"          => $record['approved_to'],
    //             "approved_by"          => $record['approved_by'],
    //             "approved_date"        => $record['approved_date'],
    //             "created_by"           => $record['created_by'],
    //             "created_date"         => $record['created_date'],
    //             "updated_by"           => $record['updated_by'],
    //             "updated_date"         => $record['updated_date'],
    //         );
    //     }
        
    //     $result['total'] = $totalRows;
    //     $result = array_merge($result, ['rows' => $arr]);
    //     echo json_encode($result);
    // }

    public function datatables()
    {
        $filter_cutoff_from = $this->input->get('filter_cutoff_from');
        $filter_cutoff_to   = $this->input->get('filter_cutoff_to');
        
        $filter_invoice_no  = $this->input->get_post('filter_invoice_no');
        $filter_subcont     = $this->input->get_post('filter_subcont');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        
        $this->db->select('
            a.*, 
            COALESCE(tf.name, sc.name) as vendor_name
        ');
        $this->db->from('generate_finishing_invoices a');
        $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
        $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');

        // Menampilkan data yang aktif (0) dan status tertentu lainnya (2), tapi tidak yang dihapus (1)
        $this->db->where_in('a.deleted', [0, 2]);

        if ($filter_cutoff_from != "" && $filter_cutoff_to != "") {
            $this->db->where('a.cutoff_from >=', $filter_cutoff_from);
            $this->db->where('a.cutoff_to <=', $filter_cutoff_to);
        }
        if ($filter_invoice_no != "") {
            $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
        }
        if ($filter_subcont != "") {
            $this->db->where('a.subcont', $filter_subcont);
        }

        $sort = $this->input->post('sort');
        $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
        if ($sort == "finishing_invoice_no") {
            $this->db->order_by('a.finishing_invoice_no', $order);
        } else {
            $this->db->order_by('a.created_date', 'DESC');
        }

        $totalRows = $this->db->count_all_results('', false);
        $this->db->limit($rows, $offset);
        $records = $this->db->get()->result_array();

        $arr = [];
        foreach ($records as $record) {
            $arr[] = array(
                "id"                   => $record['id'], 
                "finishing_invoice_no" => $record['finishing_invoice_no'],
                "p_month"              => $record['p_month'],
                "p_year"               => $record['p_year'],
                "cutoff_from"          => $record['cutoff_from'],
                "cutoff_to"            => $record['cutoff_to'],
                "vendor_name"          => $record['vendor_name'],
                "total"       => number_format(round($record['total']), 0, ',', '.'),
                "biaya_fee"   => number_format(round($record['biaya_fee']), 0, ',', '.'),
                "grand_total" => number_format(round($record['grand_total']), 0, ',', '.'),
                "deleted"              => $record['deleted'],
                "status"               => $record['status'],
                "approved_to"          => $record['approved_to'],
                "approved_by"          => $record['approved_by'],
                "approved_date"        => $record['approved_date'],
                "created_by"           => $record['created_by'],
                "created_date"         => $record['created_date'],
                "updated_by"           => $record['updated_by'],
                "updated_date"         => $record['updated_date'],
            );
        }
        
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);
        echo json_encode($result);
    }

    public function datatableDetails()
    {
        $id = base64_decode($this->input->get('id'));

        $this->db->select('
            d.item_fg_id, 
            i.number as item_number, 
            i.name as item_name,
            d.price,
            SUM(d.qty) as qty,
            SUM(d.price_fg) as price_fg,
            SUM(d.qty_1) as qty_1,
            SUM(d.price_defect) as price_defect,
            SUM(d.qty_rework) as qty_rework,
            SUM(d.price_rework) as price_rework,
            SUM(d.sub_total) as sub_total
        ');
        $this->db->from('generate_finishing_invoice_details d');
        $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
        $this->db->where('d.finishing_invoice_id', $id);
        
        $this->db->group_by(['d.item_fg_id', 'i.number', 'i.name', 'd.price']);
        
        $this->db->order_by('i.number', 'ASC');
        
        $records = $this->db->get()->result_array();
        
        echo json_encode($records, JSON_NUMERIC_CHECK);
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $row = $this->input->post('data');
            
    //         if (empty($row)) {
    //             echo json_encode(["title" => "Error", "message" => "Data kosong, tidak dapat diproses.", "theme" => "error"]);
    //             return;
    //         }

    //         $vendor_id = $row['subcont_id'];
    //         $vendor_type = '';
    //         $fee_db = 0;

    //         $cek_tf = $this->crud->read('teaching_factory', [], ["id" => $vendor_id]);
    //         if (!empty($cek_tf)) {
    //             $vendor_type = 'TF';
    //             $vendor_id   = $cek_tf->id;
    //             $fee_db      = (float)$cek_tf->fee;
    //         } else {
    //             $cek_subcont = $this->crud->read('subconts', [], ["id" => $vendor_id]);
    //             if (!empty($cek_subcont)) {
    //                 $vendor_type = 'SB'; 
    //                 $vendor_id   = $cek_subcont->id;
    //                 $fee_db      = (float)$cek_subcont->fee;
    //             }
    //         }

    //         if (empty($vendor_type)) {
    //             echo json_encode(["title" => "Error", "message" => "Subcont/TF tidak ditemukan.", "theme" => "error"]);
    //             return;
    //         }

    //         $yy = substr($row['p_year'], -2); 
    //         $mm = str_pad($row['p_month'], 2, '0', STR_PAD_LEFT); 
    //         $period_ym = $yy . $mm;

    //         $prefix = ($vendor_type == 'TF') ? 'INV-TF' : 'INV-SB';
    //         $prefix_search = $prefix . $period_ym; 

    //         $this->db->trans_start();

    //         $existing_header = $this->db->get_where('generate_finishing_invoices', [
    //             'subcont'     => $vendor_id,
    //             'p_month'     => $row['p_month'],
    //             'p_year'      => $row['p_year'],
    //             'cutoff_from' => $row['cutoff_from'],
    //             'cutoff_to'   => $row['cutoff_to']
    //         ])->row();

    //         $current_row_total = (float)$row['total_pendapatan'];
    //         $header_id = '';
    //         $action_msg = '';

    //         if ($existing_header) {
    //             $header_id = $existing_header->id;

    //             $existing_detail = $this->db->get_where('generate_finishing_invoice_details', [
    //                 'finishing_invoice_id' => $header_id,
    //                 'item_fg_id'           => $row['item_fg_id']
    //             ])->row();

    //             if ($existing_detail) {
    //                 $old_sub_total = (float)$existing_detail->sub_total;
    //                 $new_total = ((float)$existing_header->total - $old_sub_total) + $current_row_total;
                    
    //                 $biaya_fee = ($vendor_type == 'TF') ? $new_total * ($fee_db / 100) : $fee_db;
    //                 $grand_total = $new_total + $biaya_fee;

    //                 $header_update = array(
    //                     'total'       => $new_total,
    //                     'biaya_fee'   => $biaya_fee,
    //                     'grand_total' => $grand_total
    //                 );
    //                 $this->crud->update('generate_finishing_invoices', ["id" => $header_id], $header_update);

    //                 $this->db->where('id', $existing_detail->id)->update('generate_finishing_invoice_details', [
    //                     "qty"          => (float)$row['qty_fg'],
    //                     "qty_1"        => (float)$row['qty_ng'],
    //                     "qty_rework"   => (float)$row['qty_rework'],
    //                     "price"        => (float)$row['price'],
    //                     "price_fg"     => (float)$row['rp_fg'],
    //                     "price_defect" => (float)$row['rp_ng'],
    //                     "price_rework" => (float)$row['rp_rework'],
    //                     "sub_total"    => $current_row_total
    //                 ]);

    //                 $action_msg = "berhasil diupdate!";

    //             } else {
    //                 $new_total = (float)$existing_header->total + $current_row_total;
                    
    //                 $biaya_fee = ($vendor_type == 'TF') ? $new_total * ($fee_db / 100) : $fee_db;
    //                 $grand_total = $new_total + $biaya_fee;

    //                 $header_update = array(
    //                     'total'       => $new_total,
    //                     'biaya_fee'   => $biaya_fee,
    //                     'grand_total' => $grand_total
    //                 );
    //                 $this->crud->update('generate_finishing_invoices', ["id" => $header_id], $header_update);

    //                 $detail_insert = array(
    //                     "id"                   => 'DTL-' . uniqid() . rand(10,99),
    //                     "finishing_invoice_id" => $header_id, 
    //                     "item_fg_id"           => $row['item_fg_id'], 
    //                     "qty"                  => (float)$row['qty_fg'],
    //                     "qty_1"                => (float)$row['qty_ng'],
    //                     "qty_rework"           => (float)$row['qty_rework'],
    //                     "price"                => (float)$row['price'],
    //                     "price_fg"             => (float)$row['rp_fg'],
    //                     "price_defect"         => (float)$row['rp_ng'],
    //                     "price_rework"         => (float)$row['rp_rework'],
    //                     "sub_total"            => $current_row_total
    //                 );
    //                 $this->db->insert('generate_finishing_invoice_details', $detail_insert);
                    
    //                 $action_msg = "berhasil ditambahkan!";
    //             }

    //         } else {
    //             $sql = $this->db->query("
    //                 SELECT MAX(CAST(SUBSTRING_INDEX(finishing_invoice_no, '-', -1) AS UNSIGNED)) AS nomor
    //                 FROM generate_finishing_invoices
    //                 WHERE finishing_invoice_no LIKE '{$prefix_search}-%'
    //             ");
                
    //             $db_row = $sql->row();
    //             $next = ($db_row && $db_row->nomor) ? $db_row->nomor + 1 : 1;
                
    //             $finishing_invoice_no = sprintf('%s-%03d', $prefix_search, $next);

    //             $header_id = 'INV-' . time() . rand(10, 99);
    //             $new_total = $current_row_total;
                
    //             $biaya_fee = ($vendor_type == 'TF') ? $new_total * ($fee_db / 100) : $fee_db;
    //             $grand_total = $new_total + $biaya_fee;

    //             $header_insert = array(
    //                 "id"                   => $header_id, 
    //                 "finishing_invoice_no" => $finishing_invoice_no,
    //                 "p_month"              => $row['p_month'],
    //                 "p_year"               => $row['p_year'],
    //                 "cutoff_from"          => $row['cutoff_from'],
    //                 "cutoff_to"            => $row['cutoff_to'],
    //                 "subcont"              => $vendor_id, 
    //                 "total"                => $new_total,
    //                 "biaya_fee"            => $biaya_fee,
    //                 "grand_total"          => $grand_total,
    //                 "status"               => 0,
    //                 "deleted"              => 0
    //             );
                
    //             $this->crud->create('generate_finishing_invoices', $header_insert); 

    //             $detail_insert = array(
    //                 "id"                   => 'DTL-' . uniqid() . rand(10,99),
    //                 "finishing_invoice_id" => $header_id, 
    //                 "item_fg_id"           => $row['item_fg_id'], 
    //                 "qty"                  => (float)$row['qty_fg'],
    //                 "qty_1"                => (float)$row['qty_ng'],
    //                 "qty_rework"           => (float)$row['qty_rework'],
    //                 "price"                => (float)$row['price'],
    //                 "price_fg"             => (float)$row['rp_fg'],
    //                 "price_defect"         => (float)$row['rp_ng'],
    //                 "price_rework"         => (float)$row['rp_rework'],
    //                 "sub_total"            => $current_row_total
    //             );
    //             $this->db->insert('generate_finishing_invoice_details', $detail_insert);
                
    //             $action_msg = "berhasil ditambahkan!";
    //         }

    //         $this->db->trans_complete();

    //         if ($this->db->trans_status() === FALSE) {
    //             echo json_encode(array("title" => "Error", "message" => "Gagal memproses Item: " . $row['part_name'], "theme" => "error"));
    //         } else {
    //             echo json_encode(array("title" => "Success", "message" => "Item: " . $row['part_name'] . " " . $action_msg, "theme" => "success"));
    //         }
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $row = $this->input->post('data');
            
            if (empty($row)) {
                echo json_encode(["title" => "Error", "message" => "Data kosong, tidak dapat diproses.", "theme" => "error"]);
                return;
            }

            $vendor_id = $row['subcont_id'];
            $vendor_type = '';
            $fee_db = 0;

            $cek_tf = $this->crud->read('teaching_factory', [], ["id" => $vendor_id]);
            if (!empty($cek_tf)) {
                $vendor_type = 'TF';
                $vendor_id   = $cek_tf->id;
                $fee_db      = (float)$cek_tf->fee;
            } else {
                $cek_subcont = $this->crud->read('subconts', [], ["id" => $vendor_id]);
                if (!empty($cek_subcont)) {
                    $vendor_type = 'SB'; 
                    $vendor_id   = $cek_subcont->id;
                    $fee_db      = (float)$cek_subcont->fee;
                }
            }

            if (empty($vendor_type)) {
                echo json_encode(["title" => "Error", "message" => "Subcont/TF tidak ditemukan.", "theme" => "error"]);
                return;
            }

            $yy = substr($row['p_year'], -2); 
            $mm = str_pad($row['p_month'], 2, '0', STR_PAD_LEFT); 
            $period_ym = $yy . $mm;

            $prefix = ($vendor_type == 'TF') ? 'INV-TF' : 'INV-SB';
            $prefix_search = $prefix . $period_ym; 

            $this->db->trans_start();

            $existing_header = $this->db->get_where('generate_finishing_invoices', [
                'subcont'     => $vendor_id,
                'p_month'     => $row['p_month'],
                'p_year'      => $row['p_year'],
                'cutoff_from' => $row['cutoff_from'],
                'cutoff_to'   => $row['cutoff_to']
            ])->row();

            // Tangkap row total dan bulatkan
            $current_row_total = round((float)$row['total_pendapatan'], 2);
            $header_id = '';
            $action_msg = '';

            if ($existing_header) {
                $header_id = $existing_header->id;

                $existing_detail = $this->db->get_where('generate_finishing_invoice_details', [
                    'finishing_invoice_id' => $header_id,
                    'item_fg_id'           => $row['item_fg_id']
                ])->row();

                if ($existing_detail) {
                    $old_sub_total = round((float)$existing_detail->sub_total, 2);
                    $new_total = round(((float)$existing_header->total - $old_sub_total) + $current_row_total, 2);
                    
                    $biaya_fee = round(($vendor_type == 'TF') ? $new_total * ($fee_db / 100) : $fee_db, 2);
                    $grand_total = round($new_total + $biaya_fee, 2);

                    $header_update = array(
                        'total'       => $new_total,
                        'biaya_fee'   => $biaya_fee,
                        'grand_total' => $grand_total
                    );
                    $this->crud->update('generate_finishing_invoices', ["id" => $header_id], $header_update);

                    $this->db->where('id', $existing_detail->id)->update('generate_finishing_invoice_details', [
                        "qty"          => (float)$row['qty_fg'],
                        "qty_1"        => (float)$row['qty_ng'],
                        "qty_rework"   => (float)$row['qty_rework'],
                        "price"        => (float)$row['price'],
                        "price_fg"     => round((float)$row['rp_fg'], 2),
                        "price_defect" => round((float)$row['rp_ng'], 2),
                        "price_rework" => round((float)$row['rp_rework'], 2),
                        "sub_total"    => $current_row_total
                    ]);

                    $action_msg = "berhasil diupdate!";

                } else {
                    $new_total = round((float)$existing_header->total + $current_row_total, 2);
                    
                    $biaya_fee = round(($vendor_type == 'TF') ? $new_total * ($fee_db / 100) : $fee_db, 2);
                    $grand_total = round($new_total + $biaya_fee, 2);

                    $header_update = array(
                        'total'       => $new_total,
                        'biaya_fee'   => $biaya_fee,
                        'grand_total' => $grand_total
                    );
                    $this->crud->update('generate_finishing_invoices', ["id" => $header_id], $header_update);

                    $detail_insert = array(
                        "id"                   => 'DTL-' . uniqid() . rand(10,99),
                        "finishing_invoice_id" => $header_id, 
                        "item_fg_id"           => $row['item_fg_id'], 
                        "qty"                  => (float)$row['qty_fg'],
                        "qty_1"                => (float)$row['qty_ng'],
                        "qty_rework"           => (float)$row['qty_rework'],
                        "price"                => (float)$row['price'],
                        "price_fg"             => round((float)$row['rp_fg'], 2),
                        "price_defect"         => round((float)$row['rp_ng'], 2),
                        "price_rework"         => round((float)$row['rp_rework'], 2),
                        "sub_total"            => $current_row_total
                    );
                    $this->db->insert('generate_finishing_invoice_details', $detail_insert);
                    
                    $action_msg = "berhasil ditambahkan!";
                }

            } else {
                $sql = $this->db->query("
                    SELECT MAX(CAST(SUBSTRING_INDEX(finishing_invoice_no, '-', -1) AS UNSIGNED)) AS nomor
                    FROM generate_finishing_invoices
                    WHERE finishing_invoice_no LIKE '{$prefix_search}-%'
                ");
                
                $db_row = $sql->row();
                $next = ($db_row && $db_row->nomor) ? $db_row->nomor + 1 : 1;
                
                $finishing_invoice_no = sprintf('%s-%03d', $prefix_search, $next);

                $header_id = 'INV-' . time() . rand(10, 99);
                $new_total = $current_row_total;
                
                $biaya_fee = round(($vendor_type == 'TF') ? $new_total * ($fee_db / 100) : $fee_db, 2);
                $grand_total = round($new_total + $biaya_fee, 2);

                $header_insert = array(
                    "id"                   => $header_id, 
                    "finishing_invoice_no" => $finishing_invoice_no,
                    "p_month"              => $row['p_month'],
                    "p_year"               => $row['p_year'],
                    "cutoff_from"          => $row['cutoff_from'],
                    "cutoff_to"            => $row['cutoff_to'],
                    "subcont"              => $vendor_id, 
                    "total"                => $new_total,
                    "biaya_fee"            => $biaya_fee,
                    "grand_total"          => $grand_total,
                    "status"               => 0,
                    "deleted"              => 0
                );
                
                $this->crud->create('generate_finishing_invoices', $header_insert); 

                $detail_insert = array(
                    "id"                   => 'DTL-' . uniqid() . rand(10,99),
                    "finishing_invoice_id" => $header_id, 
                    "item_fg_id"           => $row['item_fg_id'], 
                    "qty"                  => (float)$row['qty_fg'],
                    "qty_1"                => (float)$row['qty_ng'],
                    "qty_rework"           => (float)$row['qty_rework'],
                    "price"                => (float)$row['price'],
                    "price_fg"             => round((float)$row['rp_fg'], 2),
                    "price_defect"         => round((float)$row['rp_ng'], 2),
                    "price_rework"         => round((float)$row['rp_rework'], 2),
                    "sub_total"            => $current_row_total
                );
                $this->db->insert('generate_finishing_invoice_details', $detail_insert);
                
                $action_msg = "berhasil ditambahkan!";
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array("title" => "Error", "message" => "Gagal memproses Item: " . $row['part_name'], "theme" => "error"));
            } else {
                echo json_encode(array("title" => "Success", "message" => "Item: " . $row['part_name'] . " " . $action_msg, "theme" => "success"));
            }
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('generate_finishing_invoices', $data);
        echo $send;
    }

    public function uploadclearFailed()
    {
        @unlink('failed/generate_finishing_invoices.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/generate_finishing_invoices.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/generate_finishing_invoices.txt";
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }


    // public function print_invoice($id)
    // {
    //     // 1. Ambil Data Header Invoice
    //     $header = $this->db->get_where('generate_finishing_invoices', ['id' => $id])->row();
    //     if (empty($header)) {
    //         show_404();
    //     }

    //     // 2. Cek vendor (TF / Subcont)
    //     $vendor = $this->db->get_where('teaching_factory', ['id' => $header->subcont])->row();
    //     $is_tf = true;
    //     if (empty($vendor)) {
    //         $vendor = $this->db->get_where('subconts', ['id' => $header->subcont])->row();
    //         $is_tf = false;
    //     }

    //     // 3. Ambil Data Details (DENGAN GROUPING DAN SUM, TAMBAHAN REWORK)
    //     $this->db->select('
    //         d.item_fg_id, 
    //         i.number as item_number, 
    //         i.name as item_name,
    //         d.price,
    //         SUM(d.qty) as qty,
    //         SUM(d.price_fg) as price_fg,
    //         SUM(d.qty_rework) as qty_rework,
    //         SUM(d.price_rework) as price_rework,
    //         SUM(d.qty_1) as qty_1,
    //         SUM(d.price_defect) as price_defect,
    //         SUM(d.sub_total) as sub_total
    //     ');
    //     $this->db->from('generate_finishing_invoice_details d');
    //     $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
    //     $this->db->where('d.finishing_invoice_id', $id);
    //     $this->db->group_by(['d.item_fg_id', 'i.number', 'i.name', 'd.price']);
    //     $this->db->order_by('i.number', 'ASC');
    //     $details = $this->db->get()->result();

    //     $total_rows_count = count($details);

    //     if ($is_tf) {
    //         $text_fee = "Biaya fee 23 % Sekolah ( Perawatan gedung, Listrik, Pengawasan kualitas dan siswa, keamanan dan kebersihan )";
    //     } else {
    //         $text_fee = "Biaya Fee Koordinator ( Kontrol Kualitas dan Stok Inventory, BBM, Sewa Tempat, Mapping Produk, Training Anggota Finishing )";
    //     }

    //     $bank_name = !empty($vendor->bank_account_name) ? $vendor->bank_account_name : '';
    //     $bank_holder = !empty($vendor->bank_account_holder) ? $vendor->bank_account_holder : '';
    //     $bank_no = !empty($vendor->bank_account_no) ? $vendor->bank_account_no : '';
    //     $note_bank = "Note : " . trim("$bank_name a.n $bank_holder $bank_no");

    //     function format_money($amount) {
    //         $formatted = number_format(abs($amount), 0, ',', '.');
    //         if ($amount < 0) {
    //             return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
    //         }
    //         return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
    //     }

    //     // ========================================================================
    //     // 4. LOGIKA BARCODE & APPROVAL MAPPING
    //     // ========================================================================
        
    //     $approval_rule = $this->db->get_where('approvals', ['table_name' => 'generate_finishing_invoices'])->row();
    //     $approvedLevel = (int)$header->approved; 
    //     $is_fully_approved = (empty($header->approved_to) && !empty($header->approved_by));

    //     $buildApproval = function($username, $is_approved) {
    //         if (empty($username)) {
    //             return ['name' => '', 'position' => '', 'barcode' => ''];
    //         }

    //         $user = $this->db->get_where('users', ['username' => $username])->row();
            
    //         $barcode_img = '';
    //         if ($is_approved && !empty($user)) {
    //             $this->createQrcode(md5($user->name), "assets/image/qrcode/");
    //             $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
    //             $barcode_img = '<img src="' . $img_url . '" style="max-height: 65px;">';
    //         }

    //         return [
    //             'name'     => !empty($user) ? $user->name : $username,
    //             'position' => !empty($user) ? $user->position : '',
    //             'barcode'  => $barcode_img
    //         ];
    //     };

    //     $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
    //     $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
    //     $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
    //     $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
    //     $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
        
    //     $col_create = $buildApproval($header->created_by, true);

    //     // ========================================================================
    //     // 5. RENDER HTML
    //     // ========================================================================
    //     $html = '<!DOCTYPE html>
    //     <html lang="id">
    //     <head>
    //         <meta charset="UTF-8">
    //         <title>Print Invoice - ' . $header->finishing_invoice_no . '</title>
    //         <style>
    //             @page { size: A4 landscape; margin: 10mm; }
    //             body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; background-color: #fff; }
    //             .screen-instruction { text-align: center; margin-top: 150px; }
    //             .screen-instruction h2 { font-size: 18pt; font-weight: bold; margin: 0 0 10px 0; color: #000; }
    //             .screen-instruction p { font-size: 11pt; margin: 4px 0; color: #000; }
    //             .invoice-container { display: none; }
    //             @media print {
    //                 .screen-instruction { display: none !important; }
    //                 .invoice-container { display: block !important; }
    //                 body { padding: 0; }
    //             }
    //             .header-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px; }
    //             .sub-title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 5px; }
    //             .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 20px; }
    //             table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    //             table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9pt; }
    //             table.print-table th { text-align: center; background-color: #f2f2f2; }
    //             .text-right { text-align: right; }
    //             .text-center { text-align: center; }
    //             .bold { font-weight: bold; }
    //             .nowrap { white-space: nowrap; }
    //             .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    //             .money-box .rp { text-align: left; }
    //             .money-box .val { text-align: right; flex-grow: 1; }
    //             .signature-section { margin-top: 25px; width: 100%; page-break-inside: avoid; }
    //             table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
    //             table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
    //         </style>
    //     </head>
    //     <body onload="window.print()">

    //         <div class="screen-instruction">
    //             <h2>Press CTRL + P for Print</h2>
    //             <p>Display pages for ' . $total_rows_count . ' rows</p>
    //             <p>Paper Size A4, Layout Landscape</p>
    //             <p>Margin Default, Scale 98</p>
    //         </div>

    //         <div class="invoice-container">
    //             <div class="header-title">INVOICE</div>
    //             <div class="sub-title">' . strtoupper(@$vendor->name) . '</div>
    //             <div class="period">
    //                 PERIODE : ' . date('d F Y', strtotime($header->cutoff_from)) . ' - ' . date('d F Y', strtotime($header->cutoff_to)) . '
    //             </div>

    //             <table class="print-table">
    //                 <thead>
    //                     <tr>
    //                         <th rowspan="2" style="width: 35px;">NO</th>
    //                         <th rowspan="2">PART NUMBER</th>
    //                         <th rowspan="2">PART NAME</th>
    //                         <th rowspan="2" style="width: 90px;">PRICE</th>
    //                         <th colspan="2">FG</th>
    //                         <th colspan="2">DEFECT</th>
    //                         <th colspan="2">REWORK</th>
    //                         <th rowspan="2" style="width: 140px;">TOTAL PENDAPATAN</th>
    //                     </tr>
    //                     <tr>
    //                         <th style="width: 60px;">QTY</th>
    //                         <th style="width: 120px;">Rp.</th>
    //                         <th style="width: 60px;">QTY</th>
    //                         <th style="width: 120px;">Rp.</th>
    //                         <th style="width: 60px;">QTY</th>
    //                         <th style="width: 120px;">Rp.</th>
    //                     </tr>
    //                 </thead>
    //                 <tbody>';

    //     $no = 1;
    //     $tot_qty_fg = 0;
    //     $tot_rp_fg = 0;
    //     $tot_qty_rework = 0;
    //     $tot_rp_rework = 0;
    //     $tot_qty_def = 0;
    //     $tot_rp_def = 0;
        
    //     foreach ($details as $row) {
    //         $tot_qty_fg += $row->qty;
    //         $tot_rp_fg += $row->price_fg;
    //         $tot_qty_rework += $row->qty_rework;
    //         $tot_rp_rework += $row->price_rework;
    //         $tot_qty_def += $row->qty_1;
    //         $tot_rp_def += $row->price_defect;
            
    //         $html .= '<tr>
    //             <td class="text-center">' . $no++ . '</td>
    //             <td class="nowrap">' . $row->item_number . '</td>
    //             <td class="nowrap">' . $row->item_name . '</td>
    //             <td>' . format_money($row->price) . '</td>
    //             <td class="text-right">' . number_format($row->qty, 0, ',', '.') . '</td>
    //             <td>' . format_money($row->price_fg) . '</td>
    //             <td class="text-center">' . ($row->qty_1 > 0 ? number_format($row->qty_1, 0, ',', '.') : '-') . '</td>
    //             <td>' . format_money($row->price_defect) . '</td>
    //             <td class="text-center">' . ($row->qty_rework > 0 ? number_format($row->qty_rework, 0, ',', '.') : '-') . '</td>
    //             <td>' . format_money($row->price_rework) . '</td>
    //             <td>' . format_money($row->sub_total) . '</td>
    //         </tr>';
    //     }

    //     // Colspan disesuaikan menjadi 10 karena penambahan 2 kolom rework
    //     $html .= '<tr class="bold">
    //                     <td colspan="4" class="text-center">TOTAL</td>
    //                     <td class="text-right">' . number_format($tot_qty_fg, 0, ',', '.') . '</td>
    //                     <td>' . format_money($tot_rp_fg) . '</td>
    //                     <td class="text-right">' . number_format($tot_qty_def, 0, ',', '.') . '</td>
    //                     <td>' . format_money($tot_rp_def) . '</td>
    //                     <td class="text-right">' . number_format($tot_qty_rework, 0, ',', '.') . '</td>
    //                     <td>' . format_money($tot_rp_rework) . '</td>
    //                     <td>' . format_money($header->total) . '</td>
    //                 </tr>
    //                 <tr>
    //                     <td colspan="10" class="bold">' . $text_fee . '</td>
    //                     <td>' . format_money($header->biaya_fee) . '</td>
    //                 </tr>
    //                 <tr>
    //                     <td colspan="10" class="bold">Total Invoice yg dibayarkan</td>
    //                     <td>' . format_money($header->grand_total) . '</td>
    //                 </tr>
    //                 </tbody>
    //             </table>

    //             <div style="margin-top: 10px; font-weight: bold; font-size: 9pt;">
    //                 ' . $note_bank . '
    //             </div>

    //             <!-- TABEL TANDA TANGAN DINAMIS DENGAN BARCODE -->
    //             <table style="width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid;">
    //                 <tr>
    //                     <td style="width: 35%;"></td>
    //                     <td style="width: 65%; text-align: center;">
    //                         <div style="margin-bottom: 8px; font-size: 9pt;">
    //                             Purwakarta, ' . date('d F Y', strtotime($header->created_date)) . '
    //                         </div>
    //                         <table class="sign-table">
    //                             <tr>
    //                                 <td colspan="2" style="width: 33.33%;">Disetujui</td>
    //                                 <td style="width: 16.66%;">Diketahui</td>
    //                                 <td colspan="2" style="width: 33.33%;">Diperiksa</td>
    //                                 <td style="width: 16.66%;">Dibuat</td>
    //                             </tr>
                                
    //                             <!-- Baris Gambar Barcode -->
    //                             <tr>
    //                                 <td style="height: 75px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
    //                             </tr>
                                
    //                             <!-- Baris Nama Personil -->
    //                             <tr>
    //                                 <td>' . $col_app5['name'] . '</td>
    //                                 <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
    //                                 <td>' . $col_app3['name'] . '</td>
    //                                 <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
    //                                 <td>' . $col_app1['name'] . '</td>
    //                                 <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
    //                             </tr>
                                
    //                             <!-- Baris Jabatan / Posisi -->
    //                             <tr>
    //                                 <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
    //                                 <td>' . ($col_app4['position'] ?: 'COO') . '</td>
    //                                 <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
    //                                 <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
    //                                 <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
    //                                 <td>' . ($col_create['position'] ?: 'Staff') . '</td>
    //                             </tr>
    //                         </table>
    //                     </td>
    //                 </tr>
    //             </table>
    //         </div>
    //     </body>
    //     </html>';

    //     echo $html;
    // }

    // public function print_recap()
    // {
    //     $ids_param = $this->input->get('ids');

    //     if (empty($ids_param)) {
    //         show_error("Tidak ada data invoice yang dipilih untuk dicetak rekap.");
    //     }

    //     $id_array = explode(',', $ids_param);

    //     // Ambil data invoice berdasarkan ID yang dicentang saja
    //     $this->db->select('a.*, COALESCE(tf.name, sc.name) as vendor_name');
    //     $this->db->from('generate_finishing_invoices a');
    //     $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
    //     $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where_in('a.id', $id_array);
    //     $this->db->order_by('a.finishing_invoice_no', 'ASC');
    //     $all_invoices = $this->db->get()->result();

    //     if (empty($all_invoices)) {
    //         show_error("Data invoice yang dipilih tidak ditemukan di database.");
    //     }

    //     // VALIDASI 1: Cek apakah ada invoice yang approved_to nya masih ada isi (belum fully approved)
    //     foreach ($all_invoices as $inv) {
    //         if ($inv->approved_to !== null && $inv->approved_to !== '') {
    //             echo "<script>
    //                     alert('Gagal! Invoice nomor [ " . $inv->finishing_invoice_no . " ] belum fully approved.');
    //                     window.close();
    //                 </script>";
    //             return;
    //         }
    //     }

    //     // VALIDASI 2: Pastikan Cutoff From dan Cutoff To sama
    //     $first_cutoff_from = $all_invoices[0]->cutoff_from;
    //     $first_cutoff_to   = $all_invoices[0]->cutoff_to;

    //     foreach ($all_invoices as $inv) {
    //         if ($inv->cutoff_from != $first_cutoff_from || 
    //             $inv->cutoff_to != $first_cutoff_to) {
    //             echo "<script>
    //                     alert('Validasi Gagal! Tanggal Cutoff From dan Cutoff To dari data yang dipilih harus sama.');
    //                     window.close();
    //                 </script>";
    //             return;
    //         }
    //     }

    //     // Pisahkan otomatis berdasarkan teks 'TF' atau 'SB' pada nomor invoice
    //     $tf_invoices = [];
    //     $sub_invoices = [];

    //     foreach ($all_invoices as $inv) {
    //         if (strpos(strtoupper($inv->finishing_invoice_no), 'TF') !== false) {
    //             $tf_invoices[] = $inv;
    //         } else {
    //             $sub_invoices[] = $inv;
    //         }
    //     }

    //     $period_text = "PERIODE : " . date('d F Y', strtotime($first_cutoff_from)) . " - " . date('d F Y', strtotime($first_cutoff_to));
    //     $doc_date    = date('d F Y', strtotime($all_invoices[0]->created_date)); 

    //     // ========================================================================
    //     // LOGIKA BARCODE & APPROVAL MAPPING (Mengikuti Standar Invoice Utama)
    //     // ========================================================================
    //     $header_sample = $all_invoices[0];
    //     $approval_rule = $this->db->get_where('approvals', ['table_name' => 'generate_finishing_invoices'])->row();
    //     $approvedLevel = (int)$header_sample->approved; 
    //     $is_fully_approved = (empty($header_sample->approved_to) && !empty($header_sample->approved_by));

    //     $buildApproval = function($username, $is_approved) {
    //         if (empty($username)) {
    //             return ['name' => '', 'position' => '', 'barcode' => ''];
    //         }

    //         $user = $this->db->get_where('users', ['username' => $username])->row();
            
    //         $barcode_img = '';
    //         if ($is_approved && !empty($user)) {
    //             $this->createQrcode(md5($user->name), "assets/image/qrcode/");
    //             $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
    //             $barcode_img = '<img src="' . $img_url . '" style="max-height: 55px;">';
    //         }

    //         return [
    //             'name'     => !empty($user) ? $user->name : $username,
    //             'position' => !empty($user) ? $user->position : '',
    //             'barcode'  => $barcode_img
    //         ];
    //     };

    //     $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
    //     $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
    //     $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
    //     $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
    //     $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
    //     $col_create = $buildApproval($header_sample->created_by, true);

    //     function format_money_recap($amount) {
    //         $formatted = number_format(abs($amount), 0, ',', '.');
    //         if ($amount < 0) {
    //             return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
    //         }
    //         return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
    //     }

    //     $html = '<!DOCTYPE html>
    //     <html lang="id">
    //     <head>
    //         <meta charset="UTF-8">
    //         <title>Rekap Finishing Invoice</title>
    //         <style>
    //             body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 20px; background-color: #fff; }
    //             .screen-instruction { text-align: center; margin-top: 150px; }
    //             .invoice-container { display: none; }
    //             @media print {
    //                 .screen-instruction { display: none !important; }
    //                 .invoice-container { display: block !important; }
    //                 body { padding: 0; }
    //             }
    //             .header-title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 3px; }
    //             .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 15px; }
    //             table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    //             table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9.5pt; }
    //             table.print-table th { text-align: center; background-color: #f2f2f2; }
    //             .text-right { text-align: right; }
    //             .text-center { text-align: center; }
    //             .bold { font-weight: bold; }
    //             .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    //             .money-box .rp { text-align: left; }
    //             .money-box .val { text-align: right; flex-grow: 1; }
    //             table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
    //             table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
    //         </style>
    //     </head>
    //     <body onload="window.print()">

    //         <div class="screen-instruction">
    //             <h2>Press CTRL + P for Print</h2>
    //             <button onclick="window.print()" style="margin-top:15px; padding:8px 18px; cursor:pointer;">Print Rekap Terpilih</button>
    //         </div>

    //         <div class="invoice-container">';

    //     // ==========================================
    //     // TABEL 1: TEACHING FACTORY (TF)
    //     // ==========================================
    //     if (!empty($tf_invoices)) {
    //         $html .= '<div class="header-title">INVOICE TEACHING FACTORY</div>
    //                 <div class="period">' . strtoupper($period_text) . '</div>
    //                 <table class="print-table">
    //                     <thead>
    //                         <tr>
    //                             <th style="width: 35px;">NO</th>
    //                             <th>NAMA</th>
    //                             <th style="width: 150px;">INVOICE</th>
    //                             <th style="width: 150px;">FEE</th>
    //                             <th style="width: 160px;">TOTAL</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';
            
    //         $no = 1;
    //         $tf_total = 0; $tf_fee = 0; $tf_grand = 0;
    //         foreach ($tf_invoices as $row) {
    //             $tf_total += $row->total;
    //             $tf_fee += $row->biaya_fee;
    //             $tf_grand += $row->grand_total;

    //             $html .= '<tr>
    //                 <td class="text-center">' . $no++ . '</td>
    //                 <td>' . $row->vendor_name . '</td>
    //                 <td>' . format_money_recap($row->total) . '</td>
    //                 <td>' . format_money_recap($row->biaya_fee) . '</td>
    //                 <td>' . format_money_recap($row->grand_total) . '</td>
    //             </tr>';
    //         }
    //         $html .= '<tr class="bold">
    //                     <td colspan="2" class="text-center">Total</td>
    //                     <td>' . format_money_recap($tf_total) . '</td>
    //                     <td>' . format_money_recap($tf_fee) . '</td>
    //                     <td>' . format_money_recap($tf_grand) . '</td>
    //                 </tr>
    //                 <tr class="bold">
    //                     <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
    //                     <td>' . format_money_recap($tf_grand) . '</td>
    //                 </tr>
    //                 </tbody></table><br>';
    //     }

    //     // ==========================================
    //     // TABEL 2: SUBCONT / KOORDINATOR (SUB)
    //     // ==========================================
    //     if (!empty($sub_invoices)) {
    //         $html .= '<div class="header-title">INVOICE SUBCONT / KOORDINATOR</div>
    //                 <div class="period">' . strtoupper($period_text) . '</div>
    //                 <table class="print-table">
    //                     <thead>
    //                         <tr>
    //                             <th style="width: 35px;">NO</th>
    //                             <th>NAMA</th>
    //                             <th style="width: 150px;">INVOICE</th>
    //                             <th style="width: 150px;">FEE</th>
    //                             <th style="width: 160px;">TOTAL</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';
            
    //         $no = 1;
    //         $sub_total = 0; $sub_fee = 0; $sub_grand = 0;
    //         foreach ($sub_invoices as $row) {
    //             $sub_total += $row->total;
    //             $sub_fee += $row->biaya_fee;
    //             $sub_grand += $row->grand_total;

    //             $html .= '<tr>
    //                 <td class="text-center">' . $no++ . '</td>
    //                 <td>' . $row->vendor_name . '</td>
    //                 <td>' . format_money_recap($row->total) . '</td>
    //                 <td>' . format_money_recap($row->biaya_fee) . '</td>
    //                 <td>' . format_money_recap($row->grand_total) . '</td>
    //             </tr>';
    //         }
    //         $html .= '<tr class="bold">
    //                     <td colspan="2" class="text-center">Total</td>
    //                     <td>' . format_money_recap($sub_total) . '</td>
    //                     <td>' . format_money_recap($sub_fee) . '</td>
    //                     <td>' . format_money_recap($sub_grand) . '</td>
    //                 </tr>
    //                 <tr class="bold">
    //                     <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
    //                     <td>' . format_money_recap($sub_grand) . '</td>
    //                 </tr>
    //                 </tbody></table>';
    //     }

    //     // ==========================================
    //     // BAGIAN TANDA TANGAN DENGAN BARCODE DINAMIS
    //     // ==========================================
    //     $html .= '<table style="width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid;">
    //             <tr>
    //                 <td style="width: 35%;"></td>
    //                 <td style="width: 65%; text-align: center;">
    //                     <div style="margin-bottom: 8px; font-size: 9pt;">
    //                         Purwakarta, ' . $doc_date . '
    //                     </div>
    //                     <table class="sign-table">
    //                         <tr>
    //                             <td colspan="2" style="width: 33.33%;">Disetujui</td>
    //                             <td style="width: 16.66%;">Diketahui</td>
    //                             <td colspan="2" style="width: 33.33%;">Diperiksa</td>
    //                             <td style="width: 16.66%;">Dibuat</td>
    //                         </tr>
                            
    //                         <!-- Baris Gambar Barcode -->
    //                         <tr>
    //                             <td style="height: 65px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
    //                         </tr>
                            
    //                         <!-- Baris Nama Personil -->
    //                         <tr>
    //                             <td>' . $col_app5['name'] . '</td>
    //                             <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
    //                             <td>' . $col_app3['name'] . '</td>
    //                             <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
    //                             <td>' . $col_app1['name'] . '</td>
    //                             <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
    //                         </tr>
                            
    //                         <!-- Baris Jabatan / Posisi -->
    //                         <tr>
    //                             <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
    //                             <td>' . ($col_app4['position'] ?: 'COO') . '</td>
    //                             <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
    //                             <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
    //                             <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
    //                             <td>' . ($col_create['position'] ?: 'Staff') . '</td>
    //                         </tr>
    //                     </table>
    //                 </td>
    //             </tr>
    //         </table>

    //         </div>
    //     </body>
    //     </html>';

    //     echo $html;
    // }

    // public function print_invoice($id)
    // {
    //     // 1. Ambil Data Header Invoice
    //     $header = $this->db->get_where('generate_finishing_invoices', ['id' => $id])->row();
    //     if (empty($header)) {
    //         show_404();
    //     }

    //     // 2. Cek vendor (TF / Subcont)
    //     $vendor = $this->db->get_where('teaching_factory', ['id' => $header->subcont])->row();
    //     $is_tf = true;
    //     if (empty($vendor)) {
    //         $vendor = $this->db->get_where('subconts', ['id' => $header->subcont])->row();
    //         $is_tf = false;
    //     }

    //     // 3. Ambil Data Details (DENGAN GROUPING DAN SUM, TAMBAHAN REWORK)
    //     $this->db->select('
    //         d.item_fg_id, 
    //         i.number as item_number, 
    //         i.name as item_name,
    //         d.price,
    //         SUM(d.qty) as qty,
    //         SUM(d.price_fg) as price_fg,
    //         SUM(d.qty_rework) as qty_rework,
    //         SUM(d.price_rework) as price_rework,
    //         SUM(d.qty_1) as qty_1,
    //         SUM(d.price_defect) as price_defect,
    //         SUM(d.sub_total) as sub_total
    //     ');
    //     $this->db->from('generate_finishing_invoice_details d');
    //     $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
    //     $this->db->where('d.finishing_invoice_id', $id);
    //     $this->db->group_by(['d.item_fg_id', 'i.number', 'i.name', 'd.price']);
    //     $this->db->order_by('i.number', 'ASC');
    //     $details = $this->db->get()->result();

    //     $total_rows_count = count($details);

    //     if ($is_tf) {
    //         $text_fee = "Biaya fee 23 % Sekolah ( Perawatan gedung, Listrik, Pengawasan kualitas dan siswa, keamanan dan kebersihan )";
    //     } else {
    //         $text_fee = "Biaya Fee Koordinator ( Kontrol Kualitas dan Stok Inventory, BBM, Sewa Tempat, Mapping Produk, Training Anggota Finishing )";
    //     }

    //     $bank_name = !empty($vendor->bank_account_name) ? $vendor->bank_account_name : '';
    //     $bank_holder = !empty($vendor->bank_account_holder) ? $vendor->bank_account_holder : '';
    //     $bank_no = !empty($vendor->bank_account_no) ? $vendor->bank_account_no : '';
    //     $note_bank = "Note : " . trim("$bank_name a.n $bank_holder $bank_no");

    //     function format_money($amount) {
    //         $formatted = number_format(round(abs($amount), 2), 2, ',', '.');
    //         if ($amount < 0) {
    //             return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
    //         }
    //         return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
    //     }

    //     // ========================================================================
    //     // 4. LOGIKA BARCODE & APPROVAL MAPPING
    //     // ========================================================================
        
    //     $approval_rule = $this->db->get_where('approvals', ['table_name' => 'generate_finishing_invoices'])->row();
    //     $approvedLevel = (int)$header->approved; 
    //     $is_fully_approved = (empty($header->approved_to) && !empty($header->approved_by));

    //     $buildApproval = function($username, $is_approved) {
    //         if (empty($username)) {
    //             return ['name' => '', 'position' => '', 'barcode' => ''];
    //         }

    //         $user = $this->db->get_where('users', ['username' => $username])->row();
            
    //         $barcode_img = '';
    //         if ($is_approved && !empty($user)) {
    //             $this->createQrcode(md5($user->name), "assets/image/qrcode/");
    //             $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
    //             $barcode_img = '<img src="' . $img_url . '" style="max-height: 65px;">';
    //         }

    //         return [
    //             'name'     => !empty($user) ? $user->name : $username,
    //             'position' => !empty($user) ? $user->position : '',
    //             'barcode'  => $barcode_img
    //         ];
    //     };

    //     $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
    //     $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
    //     $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
    //     $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
    //     $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
        
    //     $col_create = $buildApproval($header->created_by, true);

    //     // ========================================================================
    //     // 5. RENDER HTML
    //     // ========================================================================
    //     $html = '<!DOCTYPE html>
    //     <html lang="id">
    //     <head>
    //         <meta charset="UTF-8">
    //         <title>Print Invoice - ' . $header->finishing_invoice_no . '</title>
    //         <style>
    //             @page { size: A4 landscape; margin: 10mm; }
    //             body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; background-color: #fff; }
    //             .screen-instruction { text-align: center; margin-top: 150px; }
    //             .screen-instruction h2 { font-size: 18pt; font-weight: bold; margin: 0 0 10px 0; color: #000; }
    //             .screen-instruction p { font-size: 11pt; margin: 4px 0; color: #000; }
    //             .invoice-container { display: none; }
    //             @media print {
    //                 .screen-instruction { display: none !important; }
    //                 .invoice-container { display: block !important; }
    //                 body { padding: 0; }
    //             }
    //             .header-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px; }
    //             .sub-title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 5px; }
    //             .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 20px; }
    //             table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    //             table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9pt; }
    //             table.print-table th { text-align: center; background-color: #f2f2f2; }
    //             .text-right { text-align: right; }
    //             .text-center { text-align: center; }
    //             .bold { font-weight: bold; }
    //             .nowrap { white-space: nowrap; }
    //             .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    //             .money-box .rp { text-align: left; }
    //             .money-box .val { text-align: right; flex-grow: 1; }
    //             .signature-section { margin-top: 25px; width: 100%; page-break-inside: avoid; }
    //             table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
    //             table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
    //         </style>
    //     </head>
    //     <body onload="window.print()">

    //         <div class="screen-instruction">
    //             <h2>Press CTRL + P for Print</h2>
    //             <p>Display pages for ' . $total_rows_count . ' rows</p>
    //             <p>Paper Size A4, Layout Landscape</p>
    //             <p>Margin Default, Scale 98</p>
    //         </div>

    //         <div class="invoice-container">
    //             <div class="header-title">INVOICE</div>
    //             <div class="sub-title">' . strtoupper(@$vendor->name) . '</div>
    //             <div class="period">
    //                 PERIODE : ' . date('d F Y', strtotime($header->cutoff_from)) . ' - ' . date('d F Y', strtotime($header->cutoff_to)) . '
    //             </div>

    //             <table class="print-table">
    //                 <thead>
    //                     <tr>
    //                         <th rowspan="2" style="width: 35px;">NO</th>
    //                         <th rowspan="2">PART NUMBER</th>
    //                         <th rowspan="2">PART NAME</th>
    //                         <th rowspan="2" style="width: 90px;">PRICE</th>
    //                         <th colspan="2">FG</th>
    //                         <th colspan="2">DEFECT</th>
    //                         <th colspan="2">REWORK</th>
    //                         <th rowspan="2" style="width: 140px;">TOTAL PENDAPATAN</th>
    //                     </tr>
    //                     <tr>
    //                         <th style="width: 60px;">QTY</th>
    //                         <th style="width: 120px;">Rp.</th>
    //                         <th style="width: 60px;">QTY</th>
    //                         <th style="width: 120px;">Rp.</th>
    //                         <th style="width: 60px;">QTY</th>
    //                         <th style="width: 120px;">Rp.</th>
    //                     </tr>
    //                 </thead>
    //                 <tbody>';

    //     $no = 1;
    //     $tot_qty_fg = 0;
    //     $tot_rp_fg = 0;
    //     $tot_qty_rework = 0;
    //     $tot_rp_rework = 0;
    //     $tot_qty_def = 0;
    //     $tot_rp_def = 0;
        
    //     foreach ($details as $row) {
    //         $tot_qty_fg += $row->qty;
    //         $tot_rp_fg += $row->price_fg;
    //         $tot_qty_rework += $row->qty_rework;
    //         $tot_rp_rework += $row->price_rework;
    //         $tot_qty_def += $row->qty_1;
    //         $tot_rp_def += $row->price_defect;
            
    //         $html .= '<tr>
    //             <td class="text-center">' . $no++ . '</td>
    //             <td class="nowrap">' . $row->item_number . '</td>
    //             <td class="nowrap">' . $row->item_name . '</td>
    //             <td>' . format_money($row->price) . '</td>
    //             <td class="text-right">' . number_format($row->qty, 0, ',', '.') . '</td>
    //             <td>' . format_money($row->price_fg) . '</td>
    //             <td class="text-center">' . ($row->qty_1 > 0 ? number_format($row->qty_1, 0, ',', '.') : '-') . '</td>
    //             <td>' . format_money($row->price_defect) . '</td>
    //             <td class="text-center">' . ($row->qty_rework > 0 ? number_format($row->qty_rework, 0, ',', '.') : '-') . '</td>
    //             <td>' . format_money($row->price_rework) . '</td>
    //             <td>' . format_money($row->sub_total) . '</td>
    //         </tr>';
    //     }

    //     $html .= '<tr class="bold">
    //                     <td colspan="4" class="text-center">TOTAL</td>
    //                     <td class="text-right">' . number_format($tot_qty_fg, 0, ',', '.') . '</td>
    //                     <td>' . format_money($tot_rp_fg) . '</td>
    //                     <td class="text-right">' . number_format($tot_qty_def, 0, ',', '.') . '</td>
    //                     <td>' . format_money($tot_rp_def) . '</td>
    //                     <td class="text-right">' . number_format($tot_qty_rework, 0, ',', '.') . '</td>
    //                     <td>' . format_money($tot_rp_rework) . '</td>
    //                     <td>' . format_money($header->total) . '</td>
    //                 </tr>
    //                 <tr>
    //                     <td colspan="10" class="bold">' . $text_fee . '</td>
    //                     <td>' . format_money($header->biaya_fee) . '</td>
    //                 </tr>
    //                 <tr>
    //                     <td colspan="10" class="bold">Total Invoice yg dibayarkan</td>
    //                     <td>' . format_money($header->grand_total) . '</td>
    //                 </tr>
    //                 </tbody>
    //             </table>

    //             <div style="margin-top: 10px; font-weight: bold; font-size: 9pt;">
    //                 ' . $note_bank . '
    //             </div>

    //             <!-- TABEL TANDA TANGAN DINAMIS DENGAN BARCODE -->
    //             <table style="width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid;">
    //                 <tr>
    //                     <td style="width: 35%;"></td>
    //                     <td style="width: 65%; text-align: center;">
    //                         <div style="margin-bottom: 8px; font-size: 9pt;">
    //                             Purwakarta, ' . date('d F Y', strtotime($header->created_date)) . '
    //                         </div>
    //                         <table class="sign-table">
    //                             <tr>
    //                                 <td colspan="2" style="width: 33.33%;">Disetujui</td>
    //                                 <td style="width: 16.66%;">Diketahui</td>
    //                                 <td colspan="2" style="width: 33.33%;">Diperiksa</td>
    //                                 <td style="width: 16.66%;">Dibuat</td>
    //                             </tr>
                                
    //                             <!-- Baris Gambar Barcode -->
    //                             <tr>
    //                                 <td style="height: 75px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
    //                                 <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
    //                             </tr>
                                
    //                             <!-- Baris Nama Personil -->
    //                             <tr>
    //                                 <td>' . $col_app5['name'] . '</td>
    //                                 <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
    //                                 <td>' . $col_app3['name'] . '</td>
    //                                 <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
    //                                 <td>' . $col_app1['name'] . '</td>
    //                                 <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
    //                             </tr>
                                
    //                             <!-- Baris Jabatan / Posisi -->
    //                             <tr>
    //                                 <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
    //                                 <td>' . ($col_app4['position'] ?: 'COO') . '</td>
    //                                 <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
    //                                 <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
    //                                 <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
    //                                 <td>' . ($col_create['position'] ?: 'Staff') . '</td>
    //                             </tr>
    //                         </table>
    //                     </td>
    //                 </tr>
    //             </table>
    //         </div>
    //     </body>
    //     </html>';

    //     echo $html;
    // }

    // public function print_recap()
    // {
    //     $ids_param = $this->input->get('ids');

    //     if (empty($ids_param)) {
    //         show_error("Tidak ada data invoice yang dipilih untuk dicetak rekap.");
    //     }

    //     $id_array = explode(',', $ids_param);

    //     // Ambil data invoice berdasarkan ID yang dicentang saja
    //     $this->db->select('a.*, COALESCE(tf.name, sc.name) as vendor_name');
    //     $this->db->from('generate_finishing_invoices a');
    //     $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
    //     $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where_in('a.id', $id_array);
    //     $this->db->order_by('a.finishing_invoice_no', 'ASC');
    //     $all_invoices = $this->db->get()->result();

    //     if (empty($all_invoices)) {
    //         show_error("Data invoice yang dipilih tidak ditemukan di database.");
    //     }

    //     // VALIDASI 1: Cek apakah ada invoice yang approved_to nya masih ada isi (belum fully approved)
    //     foreach ($all_invoices as $inv) {
    //         if ($inv->approved_to !== null && $inv->approved_to !== '') {
    //             echo "<script>
    //                     alert('Gagal! Invoice nomor [ " . $inv->finishing_invoice_no . " ] belum fully approved.');
    //                     window.close();
    //                 </script>";
    //             return;
    //         }
    //     }

    //     // VALIDASI 2: Pastikan Cutoff From dan Cutoff To sama
    //     $first_cutoff_from = $all_invoices[0]->cutoff_from;
    //     $first_cutoff_to   = $all_invoices[0]->cutoff_to;

    //     foreach ($all_invoices as $inv) {
    //         if ($inv->cutoff_from != $first_cutoff_from || 
    //             $inv->cutoff_to != $first_cutoff_to) {
    //             echo "<script>
    //                     alert('Validasi Gagal! Tanggal Cutoff From dan Cutoff To dari data yang dipilih harus sama.');
    //                     window.close();
    //                 </script>";
    //             return;
    //         }
    //     }

    //     // Pisahkan otomatis berdasarkan teks 'TF' atau 'SB' pada nomor invoice
    //     $tf_invoices = [];
    //     $sub_invoices = [];

    //     foreach ($all_invoices as $inv) {
    //         if (strpos(strtoupper($inv->finishing_invoice_no), 'TF') !== false) {
    //             $tf_invoices[] = $inv;
    //         } else {
    //             $sub_invoices[] = $inv;
    //         }
    //     }

    //     $period_text = "PERIODE : " . date('d F Y', strtotime($first_cutoff_from)) . " - " . date('d F Y', strtotime($first_cutoff_to));
    //     $doc_date    = date('d F Y', strtotime($all_invoices[0]->created_date)); 

    //     // ========================================================================
    //     // LOGIKA BARCODE & APPROVAL MAPPING (Mengikuti Standar Invoice Utama)
    //     // ========================================================================
    //     $header_sample = $all_invoices[0];
    //     $approval_rule = $this->db->get_where('approvals', ['table_name' => 'generate_finishing_invoices'])->row();
    //     $approvedLevel = (int)$header_sample->approved; 
    //     $is_fully_approved = (empty($header_sample->approved_to) && !empty($header_sample->approved_by));

    //     $buildApproval = function($username, $is_approved) {
    //         if (empty($username)) {
    //             return ['name' => '', 'position' => '', 'barcode' => ''];
    //         }

    //         $user = $this->db->get_where('users', ['username' => $username])->row();
            
    //         $barcode_img = '';
    //         if ($is_approved && !empty($user)) {
    //             $this->createQrcode(md5($user->name), "assets/image/qrcode/");
    //             $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
    //             $barcode_img = '<img src="' . $img_url . '" style="max-height: 55px;">';
    //         }

    //         return [
    //             'name'     => !empty($user) ? $user->name : $username,
    //             'position' => !empty($user) ? $user->position : '',
    //             'barcode'  => $barcode_img
    //         ];
    //     };

    //     $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
    //     $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
    //     $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
    //     $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
    //     $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
    //     $col_create = $buildApproval($header_sample->created_by, true);

    //     function format_money_recap($amount) {
    //         $formatted = number_format(round(abs($amount), 2), 2, ',', '.');
    //         if ($amount < 0) {
    //             return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
    //         }
    //         return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
    //     }

    //     $html = '<!DOCTYPE html>
    //     <html lang="id">
    //     <head>
    //         <meta charset="UTF-8">
    //         <title>Rekap Finishing Invoice</title>
    //         <style>
    //             body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 20px; background-color: #fff; }
    //             .screen-instruction { text-align: center; margin-top: 150px; }
    //             .invoice-container { display: none; }
    //             @media print {
    //                 .screen-instruction { display: none !important; }
    //                 .invoice-container { display: block !important; }
    //                 body { padding: 0; }
    //             }
    //             .header-title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 3px; }
    //             .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 15px; }
    //             table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    //             table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9.5pt; }
    //             table.print-table th { text-align: center; background-color: #f2f2f2; }
    //             .text-right { text-align: right; }
    //             .text-center { text-align: center; }
    //             .bold { font-weight: bold; }
    //             .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    //             .money-box .rp { text-align: left; }
    //             .money-box .val { text-align: right; flex-grow: 1; }
    //             table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
    //             table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
    //         </style>
    //     </head>
    //     <body onload="window.print()">

    //         <div class="screen-instruction">
    //             <h2>Press CTRL + P for Print</h2>
    //             <button onclick="window.print()" style="margin-top:15px; padding:8px 18px; cursor:pointer;">Print Rekap Terpilih</button>
    //         </div>

    //         <div class="invoice-container">';

    //     // ==========================================
    //     // TABEL 1: TEACHING FACTORY (TF)
    //     // ==========================================
    //     if (!empty($tf_invoices)) {
    //         $html .= '<div class="header-title">INVOICE TEACHING FACTORY</div>
    //                 <div class="period">' . strtoupper($period_text) . '</div>
    //                 <table class="print-table">
    //                     <thead>
    //                         <tr>
    //                             <th style="width: 35px;">NO</th>
    //                             <th>NAMA</th>
    //                             <th style="width: 150px;">INVOICE</th>
    //                             <th style="width: 150px;">FEE</th>
    //                             <th style="width: 160px;">TOTAL</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';
            
    //         $no = 1;
    //         $tf_total = 0; $tf_fee = 0; $tf_grand = 0;
    //         foreach ($tf_invoices as $row) {
    //             $tf_total += $row->total;
    //             $tf_fee += $row->biaya_fee;
    //             $tf_grand += $row->grand_total;

    //             $html .= '<tr>
    //                 <td class="text-center">' . $no++ . '</td>
    //                 <td>' . $row->vendor_name . '</td>
    //                 <td>' . format_money_recap($row->total) . '</td>
    //                 <td>' . format_money_recap($row->biaya_fee) . '</td>
    //                 <td>' . format_money_recap($row->grand_total) . '</td>
    //             </tr>';
    //         }
    //         $html .= '<tr class="bold">
    //                     <td colspan="2" class="text-center">Total</td>
    //                     <td>' . format_money_recap($tf_total) . '</td>
    //                     <td>' . format_money_recap($tf_fee) . '</td>
    //                     <td>' . format_money_recap($tf_grand) . '</td>
    //                 </tr>
    //                 <tr class="bold">
    //                     <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
    //                     <td>' . format_money_recap($tf_grand) . '</td>
    //                 </tr>
    //                 </tbody></table><br>';
    //     }

    //     // ==========================================
    //     // TABEL 2: SUBCONT / KOORDINATOR (SUB)
    //     // ==========================================
    //     if (!empty($sub_invoices)) {
    //         $html .= '<div class="header-title">INVOICE SUBCONT / KOORDINATOR</div>
    //                 <div class="period">' . strtoupper($period_text) . '</div>
    //                 <table class="print-table">
    //                     <thead>
    //                         <tr>
    //                             <th style="width: 35px;">NO</th>
    //                             <th>NAMA</th>
    //                             <th style="width: 150px;">INVOICE</th>
    //                             <th style="width: 150px;">FEE</th>
    //                             <th style="width: 160px;">TOTAL</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';
            
    //         $no = 1;
    //         $sub_total = 0; $sub_fee = 0; $sub_grand = 0;
    //         foreach ($sub_invoices as $row) {
    //             $sub_total += $row->total;
    //             $sub_fee += $row->biaya_fee;
    //             $sub_grand += $row->grand_total;

    //             $html .= '<tr>
    //                 <td class="text-center">' . $no++ . '</td>
    //                 <td>' . $row->vendor_name . '</td>
    //                 <td>' . format_money_recap($row->total) . '</td>
    //                 <td>' . format_money_recap($row->biaya_fee) . '</td>
    //                 <td>' . format_money_recap($row->grand_total) . '</td>
    //             </tr>';
    //         }
    //         $html .= '<tr class="bold">
    //                     <td colspan="2" class="text-center">Total</td>
    //                     <td>' . format_money_recap($sub_total) . '</td>
    //                     <td>' . format_money_recap($sub_fee) . '</td>
    //                     <td>' . format_money_recap($sub_grand) . '</td>
    //                 </tr>
    //                 <tr class="bold">
    //                     <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
    //                     <td>' . format_money_recap($sub_grand) . '</td>
    //                 </tr>
    //                 </tbody></table>';
    //     }

    //     // ==========================================
    //     // BAGIAN TANDA TANGAN DENGAN BARCODE DINAMIS
    //     // ==========================================
    //     $html .= '<table style="width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid;">
    //             <tr>
    //                 <td style="width: 35%;"></td>
    //                 <td style="width: 65%; text-align: center;">
    //                     <div style="margin-bottom: 8px; font-size: 9pt;">
    //                         Purwakarta, ' . $doc_date . '
    //                     </div>
    //                     <table class="sign-table">
    //                         <tr>
    //                             <td colspan="2" style="width: 33.33%;">Disetujui</td>
    //                             <td style="width: 16.66%;">Diketahui</td>
    //                             <td colspan="2" style="width: 33.33%;">Diperiksa</td>
    //                             <td style="width: 16.66%;">Dibuat</td>
    //                         </tr>
                            
    //                         <!-- Baris Gambar Barcode -->
    //                         <tr>
    //                             <td style="height: 65px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
    //                             <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
    //                         </tr>
                            
    //                         <!-- Baris Nama Personil -->
    //                         <tr>
    //                             <td>' . $col_app5['name'] . '</td>
    //                             <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
    //                             <td>' . $col_app3['name'] . '</td>
    //                             <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
    //                             <td>' . $col_app1['name'] . '</td>
    //                             <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
    //                         </tr>
                            
    //                         <!-- Baris Jabatan / Posisi -->
    //                         <tr>
    //                             <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
    //                             <td>' . ($col_app4['position'] ?: 'COO') . '</td>
    //                             <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
    //                             <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
    //                             <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
    //                             <td>' . ($col_create['position'] ?: 'Staff') . '</td>
    //                         </tr>
    //                     </table>
    //                 </td>
    //             </tr>
    //         </table>

    //         </div>
    //     </body>
    //     </html>';

    //     echo $html;
    // }

    public function print_invoice($id)
    {
        // 1. Ambil Data Header Invoice
        $header = $this->db->get_where('generate_finishing_invoices', ['id' => $id])->row();
        if (empty($header)) {
            show_404();
        }

        // 2. Cek vendor (TF / Subcont)
        $vendor = $this->db->get_where('teaching_factory', ['id' => $header->subcont])->row();
        $is_tf = true;
        if (empty($vendor)) {
            $vendor = $this->db->get_where('subconts', ['id' => $header->subcont])->row();
            $is_tf = false;
        }

        // 3. Ambil Data Details
        $this->db->select('
            d.item_fg_id, 
            i.number as item_number, 
            i.name as item_name,
            d.price,
            SUM(d.qty) as qty,
            SUM(d.price_fg) as price_fg,
            SUM(d.qty_rework) as qty_rework,
            SUM(d.price_rework) as price_rework,
            SUM(d.qty_1) as qty_1,
            SUM(d.price_defect) as price_defect,
            SUM(d.sub_total) as sub_total
        ');
        $this->db->from('generate_finishing_invoice_details d');
        $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
        $this->db->where('d.finishing_invoice_id', $id);
        $this->db->group_by(['d.item_fg_id', 'i.number', 'i.name', 'd.price']);
        $this->db->order_by('i.number', 'ASC');
        $details = $this->db->get()->result();

        $total_rows_count = count($details);

        if ($is_tf) {
            $text_fee = "Biaya fee 23 % Sekolah ( Perawatan gedung, Listrik, Pengawasan kualitas dan siswa, keamanan dan kebersihan )";
        } else {
            $text_fee = "Biaya Fee Koordinator ( Kontrol Kualitas dan Stok Inventory, BBM, Sewa Tempat, Mapping Produk, Training Anggota Finishing )";
        }

        $bank_name = !empty($vendor->bank_account_name) ? $vendor->bank_account_name : '';
        $bank_holder = !empty($vendor->bank_account_holder) ? $vendor->bank_account_holder : '';
        $bank_no = !empty($vendor->bank_account_no) ? $vendor->bank_account_no : '';
        $note_bank = "Note : " . trim("$bank_name a.n $bank_holder $bank_no");

        // Formatter untuk Detail (dengan 2 desimal)
        function format_money($amount) {
            $formatted = number_format(round(abs($amount), 2), 2, ',', '.');
            if ($amount < 0) {
                return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
            }
            return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
        }

        // Formatter BARU untuk Grand Total, Total, Fee (dibulatkan tanpa desimal)
        function format_money_round($amount) {
            $formatted = number_format(round(abs($amount)), 0, ',', '.');
            if ($amount < 0) {
                return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
            }
            return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
        }

        // ========================================================================
        // 4. LOGIKA BARCODE & APPROVAL MAPPING
        // ========================================================================
        
        $approval_rule = $this->db->get_where('approvals', ['table_name' => 'generate_finishing_invoices'])->row();
        $approvedLevel = (int)$header->approved; 
        $is_fully_approved = (empty($header->approved_to) && !empty($header->approved_by));

        $buildApproval = function($username, $is_approved) {
            if (empty($username)) {
                return ['name' => '', 'position' => '', 'barcode' => ''];
            }

            $user = $this->db->get_where('users', ['username' => $username])->row();
            
            $barcode_img = '';
            if ($is_approved && !empty($user)) {
                $this->createQrcode(md5($user->name), "assets/image/qrcode/");
                $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
                $barcode_img = '<img src="' . $img_url . '" style="max-height: 65px;">';
            }

            return [
                'name'     => !empty($user) ? $user->name : $username,
                'position' => !empty($user) ? $user->position : '',
                'barcode'  => $barcode_img
            ];
        };

        $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
        $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
        $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
        $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
        $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
        
        $col_create = $buildApproval($header->created_by, true);

        // ========================================================================
        // 5. RENDER HTML
        // ========================================================================
        $html = '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Print Invoice - ' . $header->finishing_invoice_no . '</title>
            <style>
                @page { size: A4 landscape; margin: 10mm; }
                body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; background-color: #fff; }
                .screen-instruction { text-align: center; margin-top: 150px; }
                .screen-instruction h2 { font-size: 18pt; font-weight: bold; margin: 0 0 10px 0; color: #000; }
                .screen-instruction p { font-size: 11pt; margin: 4px 0; color: #000; }
                .invoice-container { display: none; }
                @media print {
                    .screen-instruction { display: none !important; }
                    .invoice-container { display: block !important; }
                    body { padding: 0; }
                }
                .header-title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 5px; }
                .sub-title { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 5px; }
                .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 20px; }
                table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9pt; }
                table.print-table th { text-align: center; background-color: #f2f2f2; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .bold { font-weight: bold; }
                .nowrap { white-space: nowrap; }
                .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
                .money-box .rp { text-align: left; }
                .money-box .val { text-align: right; flex-grow: 1; }
                .signature-section { margin-top: 25px; width: 100%; page-break-inside: avoid; }
                table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
                table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
            </style>
        </head>
        <body onload="window.print()">

            <div class="screen-instruction">
                <h2>Press CTRL + P for Print</h2>
                <p>Display pages for ' . $total_rows_count . ' rows</p>
                <p>Paper Size A4, Layout Landscape</p>
                <p>Margin Default, Scale 98</p>
            </div>

            <div class="invoice-container">
                <div class="header-title">INVOICE</div>
                <div class="sub-title">' . strtoupper(@$vendor->name) . '</div>
                <div class="period">
                    PERIODE : ' . date('d F Y', strtotime($header->cutoff_from)) . ' - ' . date('d F Y', strtotime($header->cutoff_to)) . '
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 35px;">NO</th>
                            <th rowspan="2">PART NUMBER</th>
                            <th rowspan="2">PART NAME</th>
                            <th rowspan="2" style="width: 90px;">PRICE</th>
                            <th colspan="2">FG</th>
                            <th colspan="2">DEFECT</th>
                            <th colspan="2">REWORK</th>
                            <th rowspan="2" style="width: 140px;">TOTAL PENDAPATAN</th>
                        </tr>
                        <tr>
                            <th style="width: 60px;">QTY</th>
                            <th style="width: 120px;">Rp.</th>
                            <th style="width: 60px;">QTY</th>
                            <th style="width: 120px;">Rp.</th>
                            <th style="width: 60px;">QTY</th>
                            <th style="width: 120px;">Rp.</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        $tot_qty_fg = 0;
        $tot_rp_fg = 0;
        $tot_qty_rework = 0;
        $tot_rp_rework = 0;
        $tot_qty_def = 0;
        $tot_rp_def = 0;
        
        foreach ($details as $row) {
            $tot_qty_fg += $row->qty;
            $tot_rp_fg += $row->price_fg;
            $tot_qty_rework += $row->qty_rework;
            $tot_rp_rework += $row->price_rework;
            $tot_qty_def += $row->qty_1;
            $tot_rp_def += $row->price_defect;
            
            $html .= '<tr>
                <td class="text-center">' . $no++ . '</td>
                <td class="nowrap">' . $row->item_number . '</td>
                <td class="nowrap">' . $row->item_name . '</td>
                <td>' . format_money($row->price) . '</td>
                <td class="text-right">' . number_format($row->qty, 0, ',', '.') . '</td>
                <td>' . format_money($row->price_fg) . '</td>
                <td class="text-center">' . ($row->qty_1 > 0 ? number_format($row->qty_1, 0, ',', '.') : '-') . '</td>
                <td>' . format_money($row->price_defect) . '</td>
                <td class="text-center">' . ($row->qty_rework > 0 ? number_format($row->qty_rework, 0, ',', '.') : '-') . '</td>
                <td>' . format_money($row->price_rework) . '</td>
                <td>' . format_money($row->sub_total) . '</td>
            </tr>';
        }

        $html .= '<tr class="bold">
                        <td colspan="4" class="text-center">TOTAL</td>
                        <td class="text-right">' . number_format($tot_qty_fg, 0, ',', '.') . '</td>
                        <td>' . format_money($tot_rp_fg) . '</td>
                        <td class="text-right">' . number_format($tot_qty_def, 0, ',', '.') . '</td>
                        <td>' . format_money($tot_rp_def) . '</td>
                        <td class="text-right">' . number_format($tot_qty_rework, 0, ',', '.') . '</td>
                        <td>' . format_money($tot_rp_rework) . '</td>
                        <td>' . format_money_round($header->total) . '</td>
                    </tr>
                    <tr>
                        <td colspan="10" class="bold">' . $text_fee . '</td>
                        <td>' . format_money_round($header->biaya_fee) . '</td>
                    </tr>
                    <tr>
                        <td colspan="10" class="bold">Total Invoice yg dibayarkan</td>
                        <td>' . format_money_round($header->grand_total) . '</td>
                    </tr>
                    </tbody>
                </table>

                <div style="margin-top: 10px; font-weight: bold; font-size: 9pt;">
                    ' . $note_bank . '
                </div>

                <!-- TABEL TANDA TANGAN DINAMIS DENGAN BARCODE -->
                <table style="width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid;">
                    <tr>
                        <td style="width: 35%;"></td>
                        <td style="width: 65%; text-align: center;">
                            <div style="margin-bottom: 8px; font-size: 9pt;">
                                Purwakarta, ' . date('d F Y', strtotime($header->created_date)) . '
                            </div>
                            <table class="sign-table">
                                <tr>
                                    <td colspan="2" style="width: 33.33%;">Disetujui</td>
                                    <td style="width: 16.66%;">Diketahui</td>
                                    <td colspan="2" style="width: 33.33%;">Diperiksa</td>
                                    <td style="width: 16.66%;">Dibuat</td>
                                </tr>
                                
                                <!-- Baris Gambar Barcode -->
                                <tr>
                                    <td style="height: 75px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
                                    <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
                                </tr>
                                
                                <!-- Baris Nama Personil -->
                                <tr>
                                    <td>' . $col_app5['name'] . '</td>
                                    <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
                                    <td>' . $col_app3['name'] . '</td>
                                    <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
                                    <td>' . $col_app1['name'] . '</td>
                                    <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
                                </tr>
                                
                                <!-- Baris Jabatan / Posisi -->
                                <tr>
                                    <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
                                    <td>' . ($col_app4['position'] ?: 'COO') . '</td>
                                    <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
                                    <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
                                    <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
                                    <td>' . ($col_create['position'] ?: 'Staff') . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>';

        echo $html;
    }

    public function print_recap()
    {
        $ids_param = $this->input->get('ids');

        if (empty($ids_param)) {
            show_error("Tidak ada data invoice yang dipilih untuk dicetak rekap.");
        }

        $id_array = explode(',', $ids_param);

        // Ambil data invoice berdasarkan ID yang dicentang saja
        $this->db->select('a.*, COALESCE(tf.name, sc.name) as vendor_name');
        $this->db->from('generate_finishing_invoices a');
        $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
        $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where_in('a.id', $id_array);
        $this->db->order_by('a.finishing_invoice_no', 'ASC');
        $all_invoices = $this->db->get()->result();

        if (empty($all_invoices)) {
            show_error("Data invoice yang dipilih tidak ditemukan di database.");
        }

        // VALIDASI 1: Cek apakah ada invoice yang approved_to nya masih ada isi (belum fully approved)
        foreach ($all_invoices as $inv) {
            if ($inv->approved_to !== null && $inv->approved_to !== '') {
                echo "<script>
                        alert('Gagal! Invoice nomor [ " . $inv->finishing_invoice_no . " ] belum fully approved.');
                        window.close();
                    </script>";
                return;
            }
        }

        // VALIDASI 2: Pastikan Cutoff From dan Cutoff To sama
        $first_cutoff_from = $all_invoices[0]->cutoff_from;
        $first_cutoff_to   = $all_invoices[0]->cutoff_to;

        foreach ($all_invoices as $inv) {
            if ($inv->cutoff_from != $first_cutoff_from || 
                $inv->cutoff_to != $first_cutoff_to) {
                echo "<script>
                        alert('Validasi Gagal! Tanggal Cutoff From dan Cutoff To dari data yang dipilih harus sama.');
                        window.close();
                    </script>";
                return;
            }
        }

        // Pisahkan otomatis berdasarkan teks 'TF' atau 'SB' pada nomor invoice
        $tf_invoices = [];
        $sub_invoices = [];

        foreach ($all_invoices as $inv) {
            if (strpos(strtoupper($inv->finishing_invoice_no), 'TF') !== false) {
                $tf_invoices[] = $inv;
            } else {
                $sub_invoices[] = $inv;
            }
        }

        $period_text = "PERIODE : " . date('d F Y', strtotime($first_cutoff_from)) . " - " . date('d F Y', strtotime($first_cutoff_to));
        $doc_date    = date('d F Y', strtotime($all_invoices[0]->created_date)); 

        // ========================================================================
        // LOGIKA BARCODE & APPROVAL MAPPING (Mengikuti Standar Invoice Utama)
        // ========================================================================
        $header_sample = $all_invoices[0];
        $approval_rule = $this->db->get_where('approvals', ['table_name' => 'generate_finishing_invoices'])->row();
        $approvedLevel = (int)$header_sample->approved; 
        $is_fully_approved = (empty($header_sample->approved_to) && !empty($header_sample->approved_by));

        $buildApproval = function($username, $is_approved) {
            if (empty($username)) {
                return ['name' => '', 'position' => '', 'barcode' => ''];
            }

            $user = $this->db->get_where('users', ['username' => $username])->row();
            
            $barcode_img = '';
            if ($is_approved && !empty($user)) {
                $this->createQrcode(md5($user->name), "assets/image/qrcode/");
                $img_url = base_url('assets/image/qrcode/' . md5($user->name) . '.png');
                $barcode_img = '<img src="' . $img_url . '" style="max-height: 55px;">';
            }

            return [
                'name'     => !empty($user) ? $user->name : $username,
                'position' => !empty($user) ? $user->position : '',
                'barcode'  => $barcode_img
            ];
        };

        $col_app5   = $buildApproval(@$approval_rule->user_approval_5, $is_fully_approved || $approvedLevel > 5);
        $col_app4   = $buildApproval(@$approval_rule->user_approval_4, $is_fully_approved || $approvedLevel > 4);
        $col_app3   = $buildApproval(@$approval_rule->user_approval_3, $is_fully_approved || $approvedLevel > 3);
        $col_app2   = $buildApproval(@$approval_rule->user_approval_2, $is_fully_approved || $approvedLevel > 2);
        $col_app1   = $buildApproval(@$approval_rule->user_approval_1, $is_fully_approved || $approvedLevel > 1);
        $col_create = $buildApproval($header_sample->created_by, true);

        // Formatter BARU untuk nilai Total, Fee, dan Grand Total yang direkap agar genap
        function format_money_recap_round($amount) {
            $formatted = number_format(round(abs($amount)), 0, ',', '.');
            if ($amount < 0) {
                return '<span class="money-box"><span class="rp">Rp</span><span class="val">-' . $formatted . '</span></span>';
            }
            return '<span class="money-box"><span class="rp">Rp</span><span class="val">' . $formatted . '</span></span>';
        }

        $html = '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Rekap Finishing Invoice</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 20px; background-color: #fff; }
                .screen-instruction { text-align: center; margin-top: 150px; }
                .invoice-container { display: none; }
                @media print {
                    .screen-instruction { display: none !important; }
                    .invoice-container { display: block !important; }
                    body { padding: 0; }
                }
                .header-title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 3px; }
                .period { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 15px; }
                table.print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                table.print-table th, table.print-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9.5pt; }
                table.print-table th { text-align: center; background-color: #f2f2f2; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .bold { font-weight: bold; }
                .money-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
                .money-box .rp { text-align: left; }
                .money-box .val { text-align: right; flex-grow: 1; }
                table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8pt; }
                table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 4px; }
            </style>
        </head>
        <body onload="window.print()">

            <div class="screen-instruction">
                <h2>Press CTRL + P for Print</h2>
                <button onclick="window.print()" style="margin-top:15px; padding:8px 18px; cursor:pointer;">Print Rekap Terpilih</button>
            </div>

            <div class="invoice-container">';

        // ==========================================
        // TABEL 1: TEACHING FACTORY (TF)
        // ==========================================
        if (!empty($tf_invoices)) {
            $html .= '<div class="header-title">INVOICE TEACHING FACTORY</div>
                    <div class="period">' . strtoupper($period_text) . '</div>
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">NO</th>
                                <th>NAMA</th>
                                <th style="width: 150px;">INVOICE</th>
                                <th style="width: 150px;">FEE</th>
                                <th style="width: 160px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            $no = 1;
            $tf_total = 0; $tf_fee = 0; $tf_grand = 0;
            foreach ($tf_invoices as $row) {
                $tf_total += $row->total;
                $tf_fee += $row->biaya_fee;
                $tf_grand += $row->grand_total;

                $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . $row->vendor_name . '</td>
                    <td>' . format_money_recap_round($row->total) . '</td>
                    <td>' . format_money_recap_round($row->biaya_fee) . '</td>
                    <td>' . format_money_recap_round($row->grand_total) . '</td>
                </tr>';
            }
            $html .= '<tr class="bold">
                        <td colspan="2" class="text-center">Total</td>
                        <td>' . format_money_recap_round($tf_total) . '</td>
                        <td>' . format_money_recap_round($tf_fee) . '</td>
                        <td>' . format_money_recap_round($tf_grand) . '</td>
                    </tr>
                    <tr class="bold">
                        <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
                        <td>' . format_money_recap_round($tf_grand) . '</td>
                    </tr>
                    </tbody></table><br>';
        }

        // ==========================================
        // TABEL 2: SUBCONT / KOORDINATOR (SUB)
        // ==========================================
        if (!empty($sub_invoices)) {
            $html .= '<div class="header-title">INVOICE SUBCONT / KOORDINATOR</div>
                    <div class="period">' . strtoupper($period_text) . '</div>
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">NO</th>
                                <th>NAMA</th>
                                <th style="width: 150px;">INVOICE</th>
                                <th style="width: 150px;">FEE</th>
                                <th style="width: 160px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            $no = 1;
            $sub_total = 0; $sub_fee = 0; $sub_grand = 0;
            foreach ($sub_invoices as $row) {
                $sub_total += $row->total;
                $sub_fee += $row->biaya_fee;
                $sub_grand += $row->grand_total;

                $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . $row->vendor_name . '</td>
                    <td>' . format_money_recap_round($row->total) . '</td>
                    <td>' . format_money_recap_round($row->biaya_fee) . '</td>
                    <td>' . format_money_recap_round($row->grand_total) . '</td>
                </tr>';
            }
            $html .= '<tr class="bold">
                        <td colspan="2" class="text-center">Total</td>
                        <td>' . format_money_recap_round($sub_total) . '</td>
                        <td>' . format_money_recap_round($sub_fee) . '</td>
                        <td>' . format_money_recap_round($sub_grand) . '</td>
                    </tr>
                    <tr class="bold">
                        <td colspan="4" class="text-right">Invoice yang dibayarkan</td>
                        <td>' . format_money_recap_round($sub_grand) . '</td>
                    </tr>
                    </tbody></table>';
        }

        // ==========================================
        // BAGIAN TANDA TANGAN DENGAN BARCODE DINAMIS
        // ==========================================
        $html .= '<table style="width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid;">
                <tr>
                    <td style="width: 35%;"></td>
                    <td style="width: 65%; text-align: center;">
                        <div style="margin-bottom: 8px; font-size: 9pt;">
                            Purwakarta, ' . $doc_date . '
                        </div>
                        <table class="sign-table">
                            <tr>
                                <td colspan="2" style="width: 33.33%;">Disetujui</td>
                                <td style="width: 16.66%;">Diketahui</td>
                                <td colspan="2" style="width: 33.33%;">Diperiksa</td>
                                <td style="width: 16.66%;">Dibuat</td>
                            </tr>
                            
                            <!-- Baris Gambar Barcode -->
                            <tr>
                                <td style="height: 65px; width: 16.66%; vertical-align: middle;">' . $col_app5['barcode'] . '</td>
                                <td style="width: 16.66%; vertical-align: middle;">' . $col_app4['barcode'] . '</td>
                                <td style="width: 16.66%; vertical-align: middle;">' . $col_app3['barcode'] . '</td>
                                <td style="width: 16.66%; vertical-align: middle;">' . $col_app2['barcode'] . '</td>
                                <td style="width: 16.66%; vertical-align: middle;">' . $col_app1['barcode'] . '</td>
                                <td style="width: 16.66%; vertical-align: middle;">' . $col_create['barcode'] . '</td>
                            </tr>
                            
                            <!-- Baris Nama Personil -->
                            <tr>
                                <td>' . $col_app5['name'] . '</td>
                                <td style="font-size: 7.5pt;">' . $col_app4['name'] . '</td>
                                <td>' . $col_app3['name'] . '</td>
                                <td style="font-size: 7.5pt;">' . $col_app2['name'] . '</td>
                                <td>' . $col_app1['name'] . '</td>
                                <td style="font-size: 7.5pt;">' . $col_create['name'] . '</td>
                            </tr>
                            
                            <!-- Baris Jabatan / Posisi -->
                            <tr>
                                <td>' . ($col_app5['position'] ?: 'BOD') . '</td>
                                <td>' . ($col_app4['position'] ?: 'COO') . '</td>
                                <td>' . ($col_app3['position'] ?: 'Leader FAT') . '</td>
                                <td>' . ($col_app2['position'] ?: 'Plant Manager') . '</td>
                                <td>' . ($col_app1['position'] ?: 'Leader') . '</td>
                                <td>' . ($col_create['position'] ?: 'Staff') . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            </div>
        </body>
        </html>';

        echo $html;
    }

    // public function print($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_finishing_invoices_$format.xls");
    //     }
        
    //     $filter_cutoff_from = $this->input->get('filter_cutoff_from');
    //     $filter_cutoff_to   = $this->input->get('filter_cutoff_to');
    //     $filter_invoice_no  = $this->input->get('filter_invoice_no');
    //     $filter_subcont     = $this->input->get('filter_subcont');

    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     $this->db->select('
    //         a.*, 
    //         COALESCE(tf.name, sc.name) as subcont_name, 
    //         i.number as part_no, 
    //         i.name as part_name, 
    //         d.price, 
    //         SUM(d.qty) as qty_fg, 
    //         SUM(d.price_fg) as price_fg, 
    //         SUM(d.qty_1) as qty_defect, 
    //         SUM(d.price_defect) as price_defect, 
    //         SUM(d.qty_rework) as qty_rework, 
    //         SUM(d.price_rework) as price_rework, 
    //         SUM(d.sub_total) as total_pendapatan
    //     ');
    //     $this->db->from('generate_finishing_invoices a');
        
    //     $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
    //     $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
    //     $this->db->join('generate_finishing_invoice_details d', 'a.id = d.finishing_invoice_id', 'left');
    //     $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');

    //     // Menampilkan data yang aktif (0) dan status tertentu lainnya (2)
    //     $this->db->where_in('a.deleted', [0, 2]);

    //     if ($filter_cutoff_from != "" or $filter_cutoff_to != "") {
    //         $this->db->where('a.cutoff_from >=', $filter_cutoff_from);
    //         $this->db->where('a.cutoff_to <=', $filter_cutoff_to);
    //     }
    //     if ($filter_invoice_no != "") {
    //         $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
    //     }
    //     if ($filter_subcont != "") {
    //         $this->db->where('a.subcont', $filter_subcont);
    //     }

    //     $this->db->group_by(['a.id', 'd.item_fg_id', 'i.number', 'i.name', 'd.price']);

    //     $sort = $this->input->post('sort');
    //     $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
    //     if ($sort == "finishing_invoice_no") {
    //         $this->db->order_by('a.finishing_invoice_no', $order);
    //     } else {
    //         $this->db->order_by('a.created_date', 'DESC');
    //     }

    //     $records = $this->db->get()->result_array();
        
    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
    //         <center>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
    //                             <img src="' .  @$config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . @$config->name . '</b><br>
    //                             <small>FINISHING INVOICES</small>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 Print Date ' . date("d M Y H:i:s") . ' <br>
    //                 Print By ' . $this->session->username . '  
    //             </div>
    //         </center>
    //         <br><br><br>
            
    //         <table id="customers" border="1">
    //         <tr>
    //             <th width="20">No</th>
    //             <th>Finishing Invoice No</th>
    //             <th>Period (Bulan/Tahun)</th>
    //             <th>Cutoff From</th>
    //             <th>Cutoff To</th>
    //             <th>Subcont</th>
    //             <th>Part No</th>
    //             <th>Part Name</th>
    //             <th>Price</th>
    //             <th>Qty FG</th>
    //             <th>Price FG</th>
    //             <th>Qty Defect</th>
    //             <th>Price Defect</th>
    //             <th>Qty Rework</th>
    //             <th>Price Rework</th>
    //             <th>Total Pendapatan</th>
    //         </tr>';
            
    //     $no = 1;
    //     foreach ($records as $data) {
    //         $html .= '<tr>
    //                     <td style="text-align:center">' . $no . '</td>
    //                     <td>' . $data['finishing_invoice_no'] . '</td>
    //                     <td style="text-align:center">' . $data['p_month'] . ' / ' . $data['p_year'] . '</td>
    //                     <td style="text-align:center">' . date('Y-m-d', strtotime($data['cutoff_from'])) . '</td>
    //                     <td style="text-align:center">' . date('Y-m-d', strtotime($data['cutoff_to'])) . '</td>
    //                     <td>' . $data['subcont_name'] . '</td>
    //                     <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
    //                     <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
    //                     <td style="text-align:right;">' . (float)$data['price'] . '</td>
    //                     <td style="text-align:center;">' . (float)$data['qty_fg'] . '</td>
    //                     <td style="text-align:right;">' . (float)$data['price_fg'] . '</td>
    //                     <td style="text-align:center;">' . (float)$data['qty_defect'] . '</td>
    //                     <td style="text-align:right;">' . (float)$data['price_defect'] . '</td>
    //                     <td style="text-align:center;">' . (float)$data['qty_rework'] . '</td>
    //                     <td style="text-align:right;">' . (float)$data['price_rework'] . '</td>
    //                     <td style="text-align:right;">' . (float)$data['total_pendapatan'] . '</td>
    //                 </tr>';
    //         $no++;
    //     }
    //     $html .= '</table></body></html>';
        
    //     echo $html;
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_finishing_invoices_$format.xls");
        }
        
        $filter_cutoff_from = $this->input->get('filter_cutoff_from');
        $filter_cutoff_to   = $this->input->get('filter_cutoff_to');
        $filter_invoice_no  = $this->input->get('filter_invoice_no');
        $filter_subcont     = $this->input->get('filter_subcont');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('
            a.*, 
            COALESCE(tf.name, sc.name) as subcont_name, 
            i.number as part_no, 
            i.name as part_name, 
            d.price, 
            SUM(d.qty) as qty_fg, 
            SUM(d.price_fg) as price_fg, 
            SUM(d.qty_1) as qty_defect, 
            SUM(d.price_defect) as price_defect, 
            SUM(d.qty_rework) as qty_rework, 
            SUM(d.price_rework) as price_rework, 
            SUM(d.sub_total) as total_pendapatan
        ');
        $this->db->from('generate_finishing_invoices a');
        
        $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
        $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
        $this->db->join('generate_finishing_invoice_details d', 'a.id = d.finishing_invoice_id', 'left');
        $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');

        $this->db->where_in('a.deleted', [0, 2]);

        if ($filter_cutoff_from != "" or $filter_cutoff_to != "") {
            $this->db->where('a.cutoff_from >=', $filter_cutoff_from);
            $this->db->where('a.cutoff_to <=', $filter_cutoff_to);
        }
        if ($filter_invoice_no != "") {
            $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
        }
        if ($filter_subcont != "") {
            $this->db->where('a.subcont', $filter_subcont);
        }

        $this->db->group_by(['a.id', 'd.item_fg_id', 'i.number', 'i.name', 'd.price']);

        $sort = $this->input->post('sort');
        $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
        if ($sort == "finishing_invoice_no") {
            $this->db->order_by('a.finishing_invoice_no', $order);
        } else {
            $this->db->order_by('a.created_date', 'DESC');
        }

        $records = $this->db->get()->result_array();
        
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
                                <img src="' .  @$config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . @$config->name . '</b><br>
                                <small>FINISHING INVOICES</small>
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
                <th>Finishing Invoice No</th>
                <th>Period (Bulan/Tahun)</th>
                <th>Cutoff From</th>
                <th>Cutoff To</th>
                <th>Subcont</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Price</th>
                <th>Qty FG</th>
                <th>Price FG</th>
                <th>Qty Defect</th>
                <th>Price Defect</th>
                <th>Qty Rework</th>
                <th>Price Rework</th>
                <th>Total Pendapatan</th>
            </tr>';
            
        // Helper untuk merubah desimal format Inggris (titik) menjadi format Indonesia (koma)
        $format_num = function($val) {
            return str_replace('.', ',', (string)(float)$val);
        };

        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['finishing_invoice_no'] . '</td>
                        <td style="text-align:center">' . $data['p_month'] . ' / ' . $data['p_year'] . '</td>
                        <td style="text-align:center">' . date('Y-m-d', strtotime($data['cutoff_from'])) . '</td>
                        <td style="text-align:center">' . date('Y-m-d', strtotime($data['cutoff_to'])) . '</td>
                        <td>' . $data['subcont_name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                        
                        <!-- Gunakan helper agar desimal dikenali sempurna oleh Excel -->
                        <td style="text-align:right;">' . $format_num($data['price']) . '</td>
                        <td style="text-align:center;">' . $format_num($data['qty_fg']) . '</td>
                        <td style="text-align:right;">' . $format_num($data['price_fg']) . '</td>
                        <td style="text-align:center;">' . $format_num($data['qty_defect']) . '</td>
                        <td style="text-align:right;">' . $format_num($data['price_defect']) . '</td>
                        <td style="text-align:center;">' . $format_num($data['qty_rework']) . '</td>
                        <td style="text-align:right;">' . $format_num($data['price_rework']) . '</td>
                        <td style="text-align:right;">' . $format_num($data['total_pendapatan']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        
        echo $html;
    }

    // public function print($option = "")// PLUS TOTAL
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_finishing_invoices_$format.xls");
    //     }
        
    //     $filter_from = $this->input->get('filter_from');
    //     $filter_to   = $this->input->get('filter_to');
    //     $filter_invoice_no = $this->input->get('filter_invoice_no');
    //     $filter_subcont = $this->input->get('filter_subcont');

    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     // Ambil data user yang sedang login untuk keperluan filter departemen
    //     $user = $this->crud->currentUserDept();

    //     $this->db->select('
    //         a.*, 
    //         COALESCE(tf.name, sc.name) as subcont_name, 
    //         i.number as part_no, 
    //         i.name as part_name, 
    //         d.price, 
    //         SUM(d.qty) as qty_fg, 
    //         SUM(d.price_fg) as price_fg, 
    //         SUM(d.qty_1) as qty_defect, 
    //         SUM(d.price_defect) as price_defect, 
    //         SUM(d.sub_total) as total_pendapatan
    //     ');
    //     $this->db->from('generate_finishing_invoices a');
        
    //     // Hapus deklarasi ganda 'users u' di FROM, gabungkan semua lewat JOIN
    //     $this->db->join('teaching_factory tf', 'a.subcont = tf.id', 'left');
    //     $this->db->join('subconts sc', 'a.subcont = sc.id', 'left');
    //     $this->db->join('users u', 'u.username = a.created_by', 'left');
    //     $this->db->join('generate_finishing_invoice_details d', 'a.id = d.finishing_invoice_id', 'left');
    //     $this->db->join('item_fg i', 'd.item_fg_id = i.id', 'left');
    //     $this->db->join('departments dept', 'dept.id = u.department_id', 'left');
    //     $this->db->join('divisions div', 'div.id = dept.plant_id', 'left');

    //     // Filter User Department
    //     if (!empty($user->department_id) && !in_array($user->department, $this->crud->getIgnoreDept())) {
    //         $this->db->where('u.department_id', $user->department_id);
    //     }

    //     $this->db->where_in('a.deleted', [0, 2]);

    //     // Filter Pencarian
    //     if ($filter_from != "" or $filter_to != "") {
    //         $this->db->where('a.finishing_invoice_date >=', $filter_from);
    //         $this->db->where('a.finishing_invoice_date <=', $filter_to);
    //     }
    //     if ($filter_invoice_no != "") {
    //         $this->db->like('a.finishing_invoice_no', $filter_invoice_no);
    //     }
    //     if ($filter_subcont != "") {
    //         $this->db->where('a.subcont', $filter_subcont);
    //     }

    //     $this->db->group_by(['a.id', 'd.item_fg_id', 'i.number', 'i.name', 'd.price']);

    //     $sort = $this->input->post('sort');
    //     $order = $this->input->post('order') ? $this->input->post('order') : 'desc';
    //     if ($sort == "finishing_invoice_no") {
    //         $this->db->order_by('a.finishing_invoice_no', $order);
    //     } else {
    //         $this->db->order_by('a.created_date', 'DESC');
    //     }

    //     $records = $this->db->get()->result_array();

    //     // Pengelompokan data berdasarkan nomor invoice
    //     $grouped_invoices = [];
    //     foreach ($records as $row) {
    //         $inv_no = $row['finishing_invoice_no'];
    //         if (!isset($grouped_invoices[$inv_no])) {
    //             $grouped_invoices[$inv_no] = [
    //                 'header' => $row,
    //                 'items' => []
    //             ];
    //         }
    //         $grouped_invoices[$inv_no]['items'][] = $row;
    //     }

    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 4px;}#customers tr:nth-child(even){background-color: #f9f9f9;}#customers tr:hover {background-color: #f1f1f1;}#customers th {padding-top: 6px;padding-bottom: 6px;text-align: center;color: black; background-color: #e2e2e2;}</style><body>
    //         <center>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
    //                             <img src="' .  @$config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . @$config->name . '</b><br>
    //                             <small>FINISHING INVOICES</small>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 Print Date ' . date("d M Y H:i:s") . ' <br>
    //                 Print By ' . $this->session->username . '  
    //             </div>
    //         </center>
    //         <br><br><br>
            
    //         <table id="customers" border="1">
    //         <thead>
    //             <tr>
    //                 <th width="20">No</th>
    //                 <th>Finishing Invoice No</th>
    //                 <th>Finishing Invoice Date</th>
    //                 <th>Period Start</th>
    //                 <th>Period End</th>
    //                 <th>Subcont</th>
    //                 <th>Part No</th>
    //                 <th>Part Name</th>
    //                 <th>Price</th>
    //                 <th>Qty FG</th>
    //                 <th>Price FG</th>
    //                 <th>Qty Defect</th>
    //                 <th>Price Defect</th>
    //                 <th>Total Pendapatan</th>
    //             </tr>
    //         </thead>
    //         <tbody>';
            
    //     $no = 1;
    //     foreach ($grouped_invoices as $inv_no => $data) {
    //         $header = $data['header'];
            
    //         foreach ($data['items'] as $item) {
    //             $html .= '<tr>
    //                         <td style="text-align:center">' . $no++ . '</td>
    //                         <td>' . $item['finishing_invoice_no'] . '</td>
    //                         <td>' . $item['finishing_invoice_date'] . '</td>
    //                         <td>' . $item['period_start'] . '</td>
    //                         <td>' . $item['period_end'] . '</td>
    //                         <td>' . $item['subcont_name'] . '</td>
    //                         <td style="mso-number-format:\@;">' . $item['part_no'] . '</td>
    //                         <td style="mso-number-format:\@;">' . $item['part_name'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['price'] . '</td>
    //                         <td style="text-align:center;">' . (float)$item['qty_fg'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['price_fg'] . '</td>
    //                         <td style="text-align:center;">' . (float)$item['qty_defect'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['price_defect'] . '</td>
    //                         <td style="text-align:right;">' . (float)$item['total_pendapatan'] . '</td>
    //                     </tr>';
    //         }
            
    //         $html .= '<tr style="background-color: #fffbdd; font-weight: bold;">
    //                     <td colspan="13" style="text-align:right;">TOTAL INVOICE (' . $inv_no . ')</td>
    //                     <td style="text-align:right;">' . (float)$header['total'] . '</td>
    //                   </tr>
    //                   <tr style="background-color: #fffbdd; font-weight: bold;">
    //                     <td colspan="13" style="text-align:right;">BIAYA FEE</td>
    //                     <td style="text-align:right;">' . (float)$header['biaya_fee'] . '</td>
    //                   </tr>
    //                   <tr style="background-color: #e5f2cf; font-weight: bold;">
    //                     <td colspan="13" style="text-align:right;">GRAND TOTAL (' . $inv_no . ')</td>
    //                     <td style="text-align:right;">' . (float)$header['grand_total'] . '</td>
    //                   </tr>';
    //     }
        
    //     $html .= '</tbody></table></body></html>';
        
    //     echo $html;
    // }
}
