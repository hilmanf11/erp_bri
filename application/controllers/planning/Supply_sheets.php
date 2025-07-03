<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supply_sheets extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/supply_sheets');
        } else {
            redirect('error_access');
        }
    }
    public function readPeriod()
    {
        $send = $this->crud->query("SELECT a.period
        FROM production_schedules a 
        WHERE a.status = 1
        GROUP BY a.period
        ORDER BY a.period DESC");

        echo json_encode($send);
    }

    public function readMPQ($item)
    {
        $item_id = base64_decode($item);
        $send = $this->crud->query("SELECT b.name, a.mpq FROM supplier_items a JOIN suppliers b ON a.supplier_id = b.id WHERE a.item_rm_id = '$item_id'");
        echo json_encode($send);
    }

    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));

        $send = $this->crud->query("SELECT a.wp
        FROM production_schedules a 
        WHERE a.status = 1 and a.period = '$period'
        GROUP BY a.wp
        ORDER BY a.wp DESC");

        echo json_encode($send);
    }

    public function readRequestNo()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        if ($period != "") {
            $w_period = "and b.period = '$period'";
        } else {
            $w_period = "";
        }
        if ($wp != "") {
            $w_wp = "and b.wp = '$wp'";
        } else {
            $w_wp = "";
        }
        $records = $this->crud->query("SELECT a.request_no FROM supply_sheets a JOIN production_schedules b ON a.workorder = b.workorder WHERE a.status = '0' $w_period $w_wp GROUP BY a.request_no");
        echo json_encode($records);
    }

    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM supply_sheets WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "SH-" . $datenow . "-" . $autoID;
    }

    public function readItems()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $workorder = base64_decode($this->input->get('workorder'));

        $send = $this->crud->query("SELECT a.workorder, b.id as item_fg_id, b.number as item_number, b.name as item_name  
            FROM production_schedules a
            JOIN item_fg b on a.item_fg_id = b.id
            WHERE a.status = 0 and a.period = '$period' and a.wp = '$wp' and a.workorder = '$workorder' 
            ORDER BY a.workorder DESC");
        echo json_encode($send);
    }

    public function datatables()
    {
        $filter_supply_type = $this->input->get('filter_supply_type');
        $filter_period = $this->input->get('filter_period');
        $filter_wp = $this->input->get('filter_wp');
        $filter_request_no = $this->input->get('filter_request_no');
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = $this->input->post('sort');
        $order = $this->input->post('order');

        // Pagination
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        $id = $_POST['id'];

        if ($id === "0") {
            $this->db->select('
                a.*, 
                e.period, 
                e.wp, 
                SUM(a.qty_req) as qty_req2, 
                SUM(a.qty_act) as qty_act2, 
                SUM(a.qty_bal) as qty_bal2, 
                b.number as item_number, 
                b.name as item_name, 
                b.uom,
                COUNT(a.id) as total_items,
                SUM(CASE WHEN (COALESCE(i.qty_issued, 0) - a.qty_req) < 0 THEN 1 ELSE 0 END) as open_item,
                 SUM(CASE WHEN(CASE WHEN COALESCE(i.qty_issued, 0) = 0
                THEN
                  ( COALESCE 
                                        (
                                            ( SELECT SUM(qty) FROM issued_material_details WHERE request_no= a.request_no and item_rm_id IN
                                                (
                                                  SELECT eq_1 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_2 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_3 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_4 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_5 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                )
                                              GROUP BY request_no, item_rm_id
                                            )
                                    , 0 ) - a.qty_req
                ) 
                ELSE
                 (COALESCE(i.qty_issued, 0) - a.qty_req)
                END) < 0 THEN 1 ELSE 0 END ) AS open_items
            ');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('production_schedules e', 'a.item_fg_id = e.item_fg_id and a.workorder = e.workorder');
            $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");

            $this->db->where('a.deleted', 0);
            
            if ($filter_supply_type == "OPEN") {
                $this->db->having('open_items >', 0);
            } elseif ($filter_supply_type == "CLOSE") {
                $this->db->having('open_items', 0);
            }elseif ($filter_supply_type == "ALL STATUS") {
                $this->db->having('open_items >=', 0);
            }
            if ($filter_period != "") {
                $this->db->where('e.period', $filter_period);
            } else {
                $this->db->where('e.period', date('Y-m-d'));
            }
            if ($filter_wp != "") {
                $this->db->where('e.wp', $filter_wp);
            }
            if ($filter_request_no != "") {
                $this->db->where('a.request_no', $filter_request_no);
            }
    
            $this->db->group_by('a.request_no');
            $this->db->order_by($sort, $order);
    
            // Total Data
            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();
    
            // foreach ($records as $record) {
            //     // if ($record['open_items'] > 0) {
            //     //     $supply_type = "OPEN";
            //     // } else {
            //     //     $supply_type = "CLOSE";
            //     // }
    
            //     $supply_type = "CLOSE";

            //     if ($record['open_items'] > 0) {
            //         $item_master_id = $record['item_rm_id'];
            //         $request_no = $record['request_no'];

            //         // Cek apakah ada pengganti
            //         $this->db->select('item_rm_id');
            //         $this->db->from('issued_materials');
            //         $this->db->where('eq_from', $item_master_id);
            //         $this->db->where('request_no', $request_no);
            //         $subs = $this->db->get()->result_array();

            //         $supply_type = "OPEN"; // default OPEN
            //         foreach ($subs as $sub) {
            //             $sub_id = $sub['item_rm_id'];
            //             $this->db->select('begin, need, issued');
            //             $this->db->from('wip_balances');
            //             $this->db->where('request_no', $request_no);
            //             $this->db->where('item_rm_id', $sub_id);
            //             $balance = $this->db->get()->row_array();

            //             if ($balance && ($balance['begin'] + $balance['issued'] - $balance['need']) >= 0) {
            //                 $supply_type = "CLOSE";
            //                 break;
            //             }
            //         }
            //     }

            //         $arr[] = array(
            //             "id" => $record['workorder'],
            //             "request_no" => $record['request_no'],
            //             "request_date" => $record['request_date'],
            //             "request_name" => $record['request_name'],
            //             "period" => $record['period'],
            //             "wp" => $record['wp'],
            //             "workorder" => $record['workorder'],
            //             "item_number" => $record['item_number'],
            //             "item_name" => $record['item_name'],
            //             "uom" => $record['uom'],
            //             "supply_type" => $supply_type,
            //             "state" => "closed"
            //         );
            // }
    
            foreach ($records as $record) {
                $supply_type = "CLOSE";

                if ($record['open_items'] > 0) {
                    // Ambil semua item_rm_id dari supply_sheets untuk request_no ini
                    $this->db->select('a.item_rm_id');
                    $this->db->from('supply_sheets a');
                    $this->db->where('a.request_no', $record['request_no']);
                    $this->db->where('a.deleted', 0);
                    $items = $this->db->get()->result_array();

                    foreach ($items as $item) {
                        $item_master_id = $item['item_rm_id'];
                        $request_no = $record['request_no'];

                        $this->db->select('begin, need, issued');
                        $this->db->from('wip_balances');
                        $this->db->where('request_no', $request_no);
                        $this->db->where('item_rm_id', $item_master_id);
                        $balance = $this->db->get()->row_array();

                        $remaining = ($balance['begin'] ?? 0) + ($balance['issued'] ?? 0) - ($balance['need'] ?? 0);
                        if ($remaining >= 0) {
                            continue; // item ini mencukupi
                        }

                        $this->db->select('item_rm_id');
                        $this->db->from('issued_materials');
                        $this->db->where('request_no', $request_no);
                        $this->db->where('eq_from', $item_master_id);
                        $subs = $this->db->get()->result_array();

                        $found = false;
                        foreach ($subs as $sub) {
                            $sub_id = $sub['item_rm_id'];
                            $this->db->select('begin, need, issued');
                            $this->db->from('wip_balances');
                            $this->db->where('request_no', $request_no);
                            $this->db->where('item_rm_id', $sub_id);
                            $sub_balance = $this->db->get()->row_array();

                            $remaining_sub = ($sub_balance['begin'] ?? 0) + ($sub_balance['issued'] ?? 0) - ($sub_balance['need'] ?? 0);
                            if ($remaining_sub >= 0) {
                                $found = true;
                                break;
                            }
                        }

                        // Jika tidak ada pengganti yang mencukupi, status tetap OPEN
                        if (!$found) {
                            $supply_type = "OPEN";
                            break;
                        }
                    }
                }

                $arr[] = array(
                    "id" => $record['workorder'],
                    "request_no" => $record['request_no'],
                    "request_date" => $record['request_date'],
                    "request_name" => $record['request_name'],
                    "period" => $record['period'],
                    "wp" => $record['wp'],
                    "workorder" => $record['workorder'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    "uom" => $record['uom'],
                    "supply_type" => $supply_type,
                    "state" => "closed"
                );
            }

            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, 
                e.period, 
                e.wp, 
                c.id as item_rm_id,
                c.number as item_rm_no, 
                c.name as item_rm_name,
                (CASE WHEN g.uom_soft is null THEN d.name ELSE h.name END) as uom,
                (CASE WHEN g.uom_soft is null THEN f.composition ELSE (f.composition * g.convertion) END) as composition,
                COALESCE(i.qty_issued, 0) as qty_issued,
                a.mpq as mpq,
                (COALESCE(i.qty_issued, 0) - a.qty_req) as qty_issued_bals,
                (CASE WHEN COALESCE(i.qty_issued, 0) = 0
                THEN
                            ( COALESCE 
                                        (
                                            ( SELECT SUM(qty) FROM issued_material_details WHERE request_no= a.request_no and item_rm_id IN
                                                (
                                                  SELECT eq_1 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_2 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_3 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_4 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                  UNION
                                                  SELECT eq_5 FROM equivalents WHERE item_rm_id = a.item_rm_id
                                                )
                                              GROUP BY request_no, item_rm_id
                                            )
                                    , 0 ) - a.qty_req
                            )

                ELSE
                  (COALESCE(i.qty_issued, 0) - a.qty_act)
                END) AS qty_issued_bal,
                f.composition,
                COALESCE(f.composition * g.convertion, f.composition) as qpa,
                e.qty');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('uom d', 'c.uom = d.name');
            $this->db->join('production_schedules e', 'a.item_fg_id = e.item_fg_id and a.workorder = e.workorder');
            $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
            $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
            $this->db->join('uom h', 'g.uom_soft = h.name', 'left');
            $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
            $this->db->where('a.deleted', 0);
            // $this->db->where('a.status', 0);
            $this->db->where('a.workorder', $id);
            if ($filter_period != "") {
                $this->db->where('e.period', $filter_period);
            }
            if ($filter_wp != "") {
                $this->db->where('e.wp', $filter_wp);
            }
            if ($filter_request_no != "") {
                $this->db->where('a.request_no', $filter_request_no);
            }
            // if ($filter_operation != "") {
            //     $this->db->where('f.operation', $filter_operation);
            // }
            $this->db->group_by('a.workorder');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by($sort, $order);
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {

                // if($record['qty_issued_bal'] < 0){
                //     $supply_type = "OPEN";
                // }else{
                //     $supply_type = "CLOSE";
                // }

                    $supply_type = "OPEN";

                    if ($record['qty_issued_bal'] >= 0) {
                        $supply_type = "CLOSE";
                    } else {
                        // Skema baru - cek item pengganti
                        $item_master_id = $record['item_rm_id'];
                        $request_no = $record['request_no'];

                        // Ambil item pengganti dari issued_material
                        $this->db->select('item_rm_id');
                        $this->db->from('issued_materials');
                        $this->db->where('request_no', $request_no);
                        $this->db->where('eq_from', $item_master_id);
                        $substitute_items = $this->db->get()->result_array();

                        foreach ($substitute_items as $sub_item) {
                            $sub_item_id = $sub_item['item_rm_id'];

                            // Cek wip_balances
                            $this->db->select('begin, need, issued');
                            $this->db->from('wip_balances');
                            $this->db->where('request_no', $request_no);
                            $this->db->where('item_rm_id', $sub_item_id);
                            $balance = $this->db->get()->row_array();

                            if ($balance) {
                                $balance_check = ($balance['begin'] + $balance['issued'] - $balance['need']);
                                if ($balance_check >= 0) {
                                    $supply_type = "CLOSE";
                                    break;
                                }
                            }
                        }
                    }                

                $arr[] = array(
                    "id" => $record['id'],
                    "request_no" => $record['request_no'],
                    "request_date" => $record['request_date'],
                    "request_name" => $record['request_name'],
                    "period" => $record['period'],
                    "wp" => $record['wp'],
                    "workorder" => $record['workorder'],
                    "item_fg_id" => $record['item_fg_id'],
                    "item_rm_id" => $record['item_rm_id'],
                    "item_number" => $record['item_rm_no'],
                    "item_name" => $record['item_rm_name'],
                    "mpq" => $record['mpq'],
                    "qpa" => $record['qpa'],
                    "qty_req" => $record['qty_req'],
                    "qty_act" => $record['qty_act'],
                    "qty_bal" => $record['qty_bal'],
                    "qty_issued" => $record['qty_issued'],
                    "qty_issued_bal" => $record['qty_issued_bal'],
                    "composition" => $record['composition'],
                    "qty" => $record['qty'],
                    "uom" => $record['uom'],
                    "supply_type" => $supply_type,
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date']
                );
            }
            $result = !empty($arr) ? $arr : [];
            echo json_encode($result);
        }
    }

    public function datatablesTemp()
    {
        $workorder = $this->input->get('workorder');
        $item_fg_id = $this->input->get('item_fg_id');
        $process_id = $this->input->get('process_id');
        
        if (empty($workorder)) {
            echo json_encode(['error' => 'Workorder is required']);
            return;
        }

        // Query utama
        $this->db->select('
            c.id as item_rm_id,
            c.number as item_rm_no, 
            c.name as item_rm_name, 
            h.mpq,
            COALESCE(f.uom_soft, b.uom) as uom,
            COALESCE(b.composition, 0) as qpa,
            a.qty,
            COALESCE((a.qty * b.composition),0) as qty_req,
            CASE 
                WHEN c.item_family_id = "P06" AND c.supply = 0 THEN ROUND(a.qty * b.composition, 2)
                ELSE CASE
                    WHEN d.qty_bal IS NOT NULL AND d.qty_bal >= 
                        CASE
                            WHEN f.uom_soft IS NULL THEN ROUND(a.qty * b.composition)
                            ELSE ROUND(a.qty * (b.composition * f.convertion))
                        END THEN d.qty_bal
                    ELSE CEIL(
                        CASE
                            WHEN f.uom_soft IS NULL THEN ROUND(a.qty * b.composition)
                            ELSE ROUND(a.qty * (b.composition * f.convertion))
                        END / h.mpq
                    ) * h.mpq
                END
            END as qty_act,
        ');

        // $this->db->select('
        //     c.id as item_rm_id,
        //     c.number as item_rm_no, 
        //     c.name as item_rm_name, 
        //     h.mpq,
        //     COALESCE(f.uom_soft, b.uom) as uom,
        //     CASE
        //         WHEN c.supply = "YES" AND c.item_family_id != "P06" THEN 
        //             COALESCE(b.composition * f.convertion, b.composition)
        //         ELSE 0
        //     END as qpa,
        //     a.qty,
        //     CASE
        //         WHEN c.supply = "YES" AND c.item_family_id != "P06" THEN 
        //             CASE
        //                 WHEN f.uom_soft IS NULL THEN ROUND(a.qty * b.composition, 2)
        //                 ELSE ROUND(a.qty * (b.composition * f.convertion), 2)
        //             END
        //         ELSE 0
        //     END as qty_req,
        //     CASE
        //         WHEN c.supply = "YES" AND c.item_family_id != "P06" THEN 
        //             CASE 
        //                 WHEN d.qty_bal IS NOT NULL AND d.qty_bal >= 
        //                     CASE
        //                         WHEN f.uom_soft IS NULL THEN ROUND(a.qty * b.composition)
        //                         ELSE ROUND(a.qty * (b.composition * f.convertion))
        //                     END THEN d.qty_bal
        //                 ELSE CEIL(
        //                     CASE
        //                         WHEN f.uom_soft IS NULL THEN ROUND(a.qty * b.composition)
        //                         ELSE ROUND(a.qty * (b.composition * f.convertion))
        //                     END / h.mpq
        //                 ) * h.mpq
        //             END
        //         ELSE 0
        //     END as qty_act,
        // ');
        $this->db->from('production_schedules a');
        $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('item_fg g', 'a.item_fg_id = g.id');
        $this->db->join('item_process e', 'a.process_id = e.id', 'left');
        $this->db->join('supply_sheets d', 'a.workorder = d.workorder AND c.id = d.item_rm_id', 'left');
        $this->db->join('convertions f', 'b.item_rm_id = f.item_rm_id', 'left');
        $this->db->join('supplier_items h', 'c.id = h.item_rm_id');
        $this->db->where([
            'a.deleted' => 0,
            'a.workorder' => $workorder,
            'a.item_fg_id' => $item_fg_id,
            'b.process_id' => $process_id,
        ]);
        $this->db->group_by('c.id');

        // Eksekusi query
        $records = $this->db->get()->result_object();

        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $filter_from="2025-01-01";
                $filter_to = date('Y-m-d');
                $request_no = $post['request_no'];
                $request_name = $post['request_name'];
                $request_date = $post['request_date'];
                $item_rm_id = $post['item_rm_id'];
                $item_fg_id = $post['item_fg_id'];
                $qty_need = floatval($post['qty_req']);
                $qty_wo = floatval(['qty']);
                $supply_sheets = $this->crud->reads("supply_sheets", [], ["request_no" => $request_no, "item_fg_id" => $item_fg_id, "item_rm_id" => $item_rm_id]);
                $supply_sheetsRM = $this->crud->reads("supply_sheets", [], ["item_rm_id" => $item_rm_id]);
                $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");
                $resultIM = $this->crud->read("issued_materials", [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id], "", "id", "desc");
                //$resultPOLabels = $this->crud->read("purchase_order_labels", [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id], "", "id", "desc");
                $item_rm = $this->crud->read("item_rm", [], ["id" => $item_rm_id], "", "id", "desc");
                
                
                $this->db->select('SUM(qty) as issued_qty');
                $this->db->from('issued_material_details');
                $this->db->where('request_no', $request_no);
                $this->db->where('item_rm_id', $item_rm_id);
                $this->db->group_by('request_no');
                $this->db->group_by('item_rm_id');
                $queryPOLabels = $this->db->get();
                $resultPOLabels = $queryPOLabels->row();

                $this->db->select("(
                                    COALESCE(SUM(e.qty),0) +
                                    COALESCE(g.return_qty, 0) +
                                    COALESCE(h.qty_stock_rm, 0) +
                                    COALESCE(i.qty_in, 0)
                                ) - (
                                    
                                    COALESCE(f.qty, 0) +
                                    COALESCE(j.qty_out, 0)
                                ) as begin_stock");
                $this->db->from('item_rm a');
                $this->db->join("purchase_order_receipts d","a.id = d.item_rm_id and d.receipt_date < '$filter_from'","left");
                $this->db->join("scan_item_receipts e","d.receipt_id = e.receipt_id","left");
                $this->db->join("(SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty
                                    FROM issued_material_details
                                    WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from'
                                    GROUP BY item_rm_id) f","a.id = f.item_rm_id","left");
                $this->db->join("(SELECT a.item_rm_id, SUM(c.qty) as return_qty
                                    FROM return_materials a 
                                    JOIN return_material_labels b ON a.return_id = b.return_id
                                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                                    WHERE a.return_date < '$filter_from'
                                    GROUP BY a.item_rm_id) g","a.id = g.item_rm_id","left");
                $this->db->join("(SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                                    FROM os_rm a
                                    JOIN item_rm b ON a.item_rm_id = b.id
                                    WHERE a.trans_date < '$filter_from'
                                    GROUP BY a.item_rm_id) h","a.id = h.item_rm_id","left");
                $this->db->join("(SELECT item_rm_id, SUM(qty) as qty_in 
                                    FROM transaction_rm 
                                    WHERE transaction_type LIKE 'RE%' 
                                    AND request_date < '$filter_from'
                                    GROUP BY item_rm_id) i","a.id = i.item_rm_id","left");
                $this->db->join("(SELECT item_rm_id, SUM(qty) as qty_out 
                                    FROM transaction_rm 
                                    WHERE transaction_type LIKE 'IS%' 
                                    AND request_date < '$filter_from'
                                    GROUP BY item_rm_id) j","a.id = j.item_rm_id","left");
                $this->db->where('a.id', $item_rm_id);
                $this->db->group_by('a.id');
                $queryBegin = $this->db->get();
                $resultBeginStock = $queryBegin->row();

                $this->db->select("(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty_in, 0)) as qty_in,
                                    (COALESCE(f.qty, 0) + COALESCE(j.qty_out, 0)) as qty_out");
                $this->db->from('item_rm a');
                $this->db->join("purchase_order_receipts d","a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'","left");
                $this->db->join("scan_item_receipts e","d.receipt_id = e.receipt_id","left");
                $this->db->join("(SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty
                                    FROM issued_material_details
                                    WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
                                    GROUP BY item_rm_id) f","a.id = f.item_rm_id","left");
                $this->db->join("(SELECT a.item_rm_id, SUM(c.qty) as return_qty
                                    FROM return_materials a 
                                    JOIN return_material_labels b ON a.return_id = b.return_id
                                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                                    WHERE a.return_date between '$filter_from' and '$filter_to'
                                    GROUP BY a.item_rm_id) g","a.id = g.item_rm_id","left");
                $this->db->join("(SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                                    FROM os_rm a
                                    JOIN item_rm b ON a.item_rm_id = b.id
                                    WHERE a.trans_date between '$filter_from' and '$filter_to'
                                    GROUP BY a.item_rm_id) h","a.id = h.item_rm_id","left");
                $this->db->join("(SELECT item_rm_id, SUM(qty) as qty_in 
                                    FROM transaction_rm 
                                    WHERE transaction_type LIKE 'RE%' 
                                    AND request_date between '$filter_from' and '$filter_to'
                                    GROUP BY item_rm_id) i","a.id = i.item_rm_id","left");
                $this->db->join("(SELECT item_rm_id, SUM(qty) as qty_out 
                                    FROM transaction_rm 
                                    WHERE transaction_type LIKE 'IS%' 
                                    AND request_date between '$filter_from' and '$filter_to'
                                    GROUP BY item_rm_id) j","a.id = j.item_rm_id","left");
                $this->db->where('a.id', $item_rm_id);
                $this->db->group_by('a.id');
                $queryInOut = $this->db->get();
                $resultInOut = $queryInOut->row();

                $EndingStock = (floatval($resultBeginStock->begin_stock) + floatval($resultInOut->qty_in)) - floatval($resultInOut->qty_out);

                if (count($supply_sheets) > 0) {
                    show_error("Duplicate");
                }else{
                        
                    $begin = 0;
                    $issued = 0;
                    $balance = 0;
                    $need = floatval($qty_need);
                    $qty_supply = 0;
                    if ($queryPOLabels->num_rows() > 0) {
                        $issued= $resultPOLabels->issued_qty;
                    }

                    if(count($supply_sheetsRM) > 0){
                            // var_dump('$wip_balances->balance');
                            // var_dump($wip_balances->balance);
                            if(floatval($wip_balances->balance)<floatval($qty_need)){
                                $roundingUpResult = ceil(floatval($qty_need) / floatval($post["mpq"]));
                                $qty_supply=floatval($post["mpq"]) * $roundingUpResult;
                                // var_dump('$qty_suuply');
                                // var_dump($qty_supply);
                            }
                            $begin = floatval($wip_balances->balance);
                            $issued = (floatval($qty_supply) == 0) ? 0 : $issued;
                            // var_dump('$beginss');
                            // var_dump($begin);

                    }

                    //  var_dump('$begin');
                    //  var_dump($begin);
                    //  var_dump('$issued');
                    //  var_dump($issued);

                    $balance = ($begin + $issued) - $need;

                    $post = [
                        'item_fg_id' => $item_fg_id,
                        'item_rm_id' => $item_rm_id,
                        'workorder' => $post["workorder"],
                        'request_date' => $request_date,
                        'request_no' => $request_no,
                        'request_name' => $request_name,
                        'mpq' => floatval($post["mpq"]),
                        'qty_req' => $qty_supply,
                        'qty_act' => $need,
                        'qty_issued' => $issued,
                        'qty_bal' => $balance
                    ]; //var_dump($post);

                    if($item_rm->item_family_id !='P06'){

                        $this->crud->create("wip_balances", [
                            "item_rm_id" => $item_rm_id,
                            "request_no" => $request_no,
                            "begin" => $begin,
                            "need" => $need,
                            "issued" => $issued,
                            "balance" => $balance,
                            "warehouse" => $EndingStock, // dari inventory berdasarkan stok terakhir saat supply sheet dibuat / historical transaction rm pada column ending stock dari rm id tersebut
                        ]);

                        $supplySheetId = $this->crud->create_return_id('supply_sheets', $post);
                    }else{
                        $supplySheetId = $this->crud->create_return_id('supply_chemical', $post);
                    }

                        $workorderExists = $this->db->get_where("production_schedules", ["workorder" => $post['workorder']])->num_rows();

                        if ($workorderExists > 0) {
                            $this->db->update("production_schedules", ["status" => 1], ["workorder" => $post['workorder']]);
                        }
                        echo $supplySheetId;

                }

            }







                //Stock kWarehouse RM
            //     $stockWarehouse = $this->crud->query("SELECT
            //         a.id,
            //         (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) + COALESCE(h.qty_stock_rm, 0) - COALESCE(f.qty, 0)) as end_stock
            //     FROM item_rm a 
            //     JOIN item_familys b ON a.item_family_id = b.id
            //     JOIN uom c ON a.uom = c.name
            //     LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$dateNow'
            //     LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            //     LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$dateNow' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            //     LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            //         FROM return_materials a 
            //         JOIN return_material_labels b ON a.return_id = b.return_id
            //         JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            //         WHERE a.return_date <= '$dateNow'
            //         GROUP BY a.id) g ON a.id = g.item_rm_id
            //     LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
            //     FROM os_rm a
            //     JOIN item_rm b ON a.item_rm_id = b.id
            //     WHERE a.trans_date < '$dateNow'
            //     GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
            //     WHERE a.id like '$item_rm_id'
            //     GROUP BY a.id
            //     ORDER BY a.number");

            //     if (count($supply_sheets) > 0) {
            //         show_error("Duplicate");
            //     } else {
            //         $qIssued = $this->db->query("SELECT COALESCE(SUM(a.qty), 0) as balance 
            //             FROM scan_item_receipts a 
            //             JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
            //             WHERE b.item_rm_id = '$item_rm_id' 
            //             GROUP BY b.item_rm_id");
            //         $dIssued = $qIssued->row();

            //         if (!empty($wip_balances->balance)) {
            //             if ($qty_act >= $wip_balances->balance) {
            //                 //kalo items di hitung mpq
            //                 if (@$supplier_items->calculate == 'YES') {
            //                     $begin = $wip_balances->balance;
            //                     $need = $qty_act;
            //                     $issued = 0;
            //                     $balance = 0;
            //                     $warehouse = !empty($wip_balances->warehouse) ? $wip_balances->warehouse : @$stockWarehouse[0]->end_stock;
            //                 } elseif (@$supplier_items->calculate == 0) {
            //                     $begin = $wip_balances->balance;
            //                     $need = $qty_act;
            //                     $issued = 0;
            //                     $balance = 0;
            //                     $warehouse = !empty($wip_balances->warehouse) ? $wip_balances->warehouse : @$stockWarehouse[0]->end_stock;
            //                 } else {
            //                     $begin = $wip_balances->balance;
            //                     $need = $qty_act;
            //                     $issued = 0;
            //                     $balance = 0;
            //                     $warehouse = !empty($wip_balances->warehouse) ? $wip_balances->warehouse : @$stockWarehouse[0]->end_stock;
            //                 }
            //             } else {
            //                 $begin = $wip_balances->balance;
            //                 $need = $qty_act;
            //                 $issued = 0;
            //                 $balance = 0;
            //                 $warehouse = !empty($wip_balances->warehouse) ? $wip_balances->warehouse : @$stockWarehouse[0]->end_stock;
            //             }
            //         } else {
            //             if ($qty_act >= @$post['mpq']) {
            //                 $begin = 0;
            //                 $need = $qty_act;
            //                 $issued = 0;
            //                 $balance = 0;
            //                 $warehouse = @$stockWarehouse[0]->end_stock;
            //             } else {
            //                 if (@$supplier_items->calculate == 'YES') {
            //                     $begin = 0;
            //                     $need = $qty_act;
            //                     $issued = 0;
            //                     $balance = 0;
            //                     $warehouse = @$stockWarehouse[0]->end_stock;
            //                 }else{
            //                     $begin = 0;
            //                     $need = $qty_act;
            //                     $issued = 0;
            //                     $balance = 0;
            //                     $warehouse = @$stockWarehouse[0]->end_stock;
            //                 }
            //             }
            //         }

            //         if ($post['qty_req'] == 0) {
            //             echo json_encode(array("title" => "Qty 0", "message" => $post['item_rm_no'] . " Qty 0", "theme" => "error"));
            //         } else {
            //             $balance = $this->crud->create("wip_balances", [
            //                 "item_rm_id" => $item_rm_id,
            //                 "request_no" => $post['request_no'],
            //                 "begin" => $begin,
            //                 "need" => $need,
            //                 "issued" => $issued,
            //                 "balance" => $balance,
            //                 "warehouse" => $warehouse,
            //             ]);

            //             // Ambil data dari tabel item_rm
            //             $item_rm = $this->crud->read("item_rm", [], ["id" => $item_rm_id]);

            //             // Logika untuk menyimpan ke supply_chemical atau supply_sheets
            //             if ($item_rm->supply == 0 && $item_rm->item_family_id == 'P06') {
            //                 $send = $this->crud->create_return_id('supply_chemical', array_merge($post, ["qty_issued" => $issued]));
            //                 $supplySheetId = $send;
            //             } else {
            //                 // Jika tidak, simpan ke `supply_sheets`
            //                 $send = $this->crud->create_return_id('supply_sheets', array_merge($post, ["qty_issued" => $issued]));
            //                 $supplySheetId = $send;
            //             }

            //             // Tambahkan notifikasi jika berhasil
            //             if ($supplySheetId) {
            //                 $notificationData = [
            //                     'created_by' => $this->session->userdata('username'),
            //                     'created_date' => date('Y-m-d H:i:s'),
            //                     'approvals_id' => null,
            //                     'users_id_from' => $this->session->userdata('username'),
            //                     'users_id_to' => 'scmbri04',
            //                     'table_id' => $supplySheetId,
            //                     'table_name' => 'supply_sheets',
            //                     'name' => 'Created',
            //                     'description' => 'Data in Module Supply Sheets has been created',
            //                     'status' => 0
            //                 ];
            //                 $this->crud->create('notifications', $notificationData);
            //             }
            //             // if ($post['qty_bal'] == 0) {
            //             //     $this->db->update("production_schedules", ["status" => 1], ["workorder" => $post['workorder']]);
            //             //     echo $send;
            //             // } else {
            //             //     $this->db->update("production_schedules", ["status" => 0], ["workorder" => $post['workorder']]);
            //             //     echo $send;
            //             // }
            //             $workorderExists = $this->db->get_where("production_schedules", ["workorder" => $post['workorder']])->num_rows();

            //             if ($workorderExists > 0) {
            //                 $this->db->update("production_schedules", ["status" => 1], ["workorder" => $post['workorder']]);
            //             }

            //             echo $supplySheetId;
            //         }
            //     }
            // } else {
            //     show_error(validation_errors());
            // }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('supply_sheets', ["id" => $data['id']]);
        $update = $this->crud->update('production_schedules', ["workorder" => $data['workorder'], "item_fg_id" => $data['item_fg_id']], ["status" => 0]);
        $delete = $this->crud->delete('wip_balances', ["request_no" => $data['request_no'], "item_rm_id" => $data['item_rm_id']]);
        echo $send;
    }

    public function print_kanban($request_no)//, $operation
    {
        $this->db->select('a.*');
        $this->db->from('supply_sheets a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('uom d', 'c.uom = d.name');
        $this->db->join('bom e', 'a.item_fg_id = e.item_fg_id and a.item_rm_id = e.item_rm_id');
        $this->db->join('warehouse_location_items f', "a.item_rm_id = f.item_rm_id", 'left');
        $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        // $this->db->where('e.operation', base64_decode($operation));
        $this->db->like('a.request_no', base64_decode($request_no));
        $this->db->group_by('c.number');
        $supply_sheet_total = $this->db->get()->result_array();
        $kanban = $this->crud->read('supply_sheets', [], ["request_no" => base64_decode($request_no)]);
        $production_schedule = $this->crud->read('production_schedules', [], ["workorder" => $kanban->workorder]);
        $product = $this->crud->read('item_rm', [], ["id" => $kanban->item_rm_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 8;
        $page = ceil(count($supply_sheet_total) / $rows);
        //Generate QRcode
        $this->createQrcode(@$kanban->request_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $kanban->request_no . '</title>
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
                            <p>Display pages for 8 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, (CASE WHEN a.mpq > 0 THEN a.mpq ELSE g.mpq END) as mpq, c.number as item_rm_no, c.name as item_rm_name, e.composition, f.location, c.uom');
            $this->db->from('supply_sheets a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('uom d', 'c.uom = d.name');
            $this->db->join('bom e', 'a.item_fg_id = e.item_fg_id and a.item_rm_id = e.item_rm_id');
            $this->db->join('warehouse_location_items f', "a.item_rm_id = f.item_rm_id", 'left');
            $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            // $this->db->where('e.operation', base64_decode($operation));
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->group_by('c.number');
            $this->db->order_by('c.number', 'ASC');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();
            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $kanban->request_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_supply_sheet . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_supply_sheet . '</td>
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
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>SUPPLY SHEET</u></h3>
                                </center>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Doc. No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Doc. Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($kanban->request_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Created By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->request_name . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">WO ID</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->workorder . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Product No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$product->number . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">SO. No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$production_schedule->so_number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">WP / Operation</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$production_schedule->wp . ' / ' . @base64_decode($operation) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">WO Qty</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$production_schedule->qty . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y") . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$this->session->name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Component No</th>
                                        <th>Component Name</th>
                                        <th>QPA</th>
                                        <th>Uom</th>
                                        <th>Qty Need</th>
                                        <th>MPQ</th>
                                        <th>Issued</th>
                                        <th>Balance WIP</th>
                                        <th>Stock WHS</th>
                                        <th width="10">WHS Location</th>
                                    </tr>';
            foreach ($records as $record) {
                $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $record['item_rm_id'], "request_no" => $record['request_no']], "", "id", "desc");
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_no'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td style="text-align:right;">' . $record['composition'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . @number_format(($wip_balances->need), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['mpq'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['qty_issued'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format(($wip_balances->balance), 2) . '</td>
                                <td style="text-align:right;">' . @number_format(($wip_balances->warehouse), 2) . '</td>
                                <td>' . $record['location'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>
                <br>
                <table id="customers">
                    <tr>
                        <th>Production</th>
                        <th>Warehouse</th>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <td style="height:20px;"></td>
                        <td style="height:20px;"></td>
                    </tr>
                </table>
                </div>
            </div>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }
        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=supply_sheets_$format.xls");
        }

        $filter_supply_type = $this->input->get('filter_supply_type');
        $filter_period = $this->input->get('filter_period');
        $filter_wp   = $this->input->get('filter_wp');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_operation = $this->input->get('filter_operation');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,
            b.number as item_number,
            e.period, 
            e.wp, 
            c.id as item_rm_id,
            c.number as item_rm_no, 
            c.name as item_rm_name, 
            (CASE WHEN g.uom_soft is null THEN d.name ELSE h.name END) as uom,
            (CASE WHEN g.uom_soft is null THEN f.composition ELSE (f.composition * g.convertion) END) as composition,
            i.qty_issued as qty_issued,
            (i.qty_issued - a.qty_req) as qty_issued_bal,
            f.composition, 
            e.qty');
        $this->db->from('supply_sheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('uom d', 'c.uom = d.name');
        $this->db->join('production_schedules e', 'a.item_fg_id = e.item_fg_id and a.workorder = e.workorder');
        $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
        $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->join('uom h', 'g.uom_soft = h.name', 'left');
        $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
        // $this->db->where('a.deleted', 0);
        if ($filter_supply_type == "OPEN") {
            $this->db->where('(i.qty_issued - a.qty_req) <', 0);
        }elseif ($filter_supply_type == "CLOSE") {
            $this->db->where('(i.qty_issued - a.qty_req) =', 0);
        }elseif ($filter_supply_type == "ALL STATUS") {
            $this->db->having('(i.qty_issued - a.qty_req) <=', 0);
        }
        if ($filter_period != "") {
            $this->db->where('e.period', $filter_period);
        }
        if ($filter_wp != "") {
            $this->db->where('e.wp', $filter_wp);
        }
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if ($filter_operation != "") {
            $this->db->where('f.operation', $filter_operation);
        }
        $this->db->group_by('a.workorder');
        $this->db->group_by('a.item_rm_id');
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
                                <small>SUPPLY SHEET</small>
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
                <th>No</th>
                <th>Supply ID</th>
                <th>Supply Date</th>
                <th>Requester</th>
                <th>Period</th>
                <th>WP</th>
                <th>Work Order</th>
                <th>Product No</th>
                <th>Component No</th>
                <th>Component Name</th>
                <th>Uom</th>
                <th>Qpa</th>
                <th>WO qty</th>
                <th>Actual Qty</th>
                <th>Issued</th>
                <th>Outstanding</th>
                <th>Supply Type</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            if($data['qty_issued_bal'] < 0){
                $supply_type = "OPEN";
            }else{
                $supply_type = "CLOSE";
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['period'] . '</td>
                        <td>' . $data['wp'] . '</td>
                        <td>' . $data['workorder'] . '</td>
                        <td>' . $data['item_number'] . '</td>
                        <td>' . $data['item_rm_no'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['composition'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['qty_act'] . '</td>
                        <td>' . $data['qty_issued'] . '</td>
                        <td>' . $data['qty_issued_bal'] . '</td>
                        <td>' . $supply_type . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}