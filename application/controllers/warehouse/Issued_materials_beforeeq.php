<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_materials extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        // $this->form_validation->set_rules('item_fg_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/issued_materials');
        } else {
            redirect('error_access');
        }
    }
    public function getSupplySheet()
    {
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');
            $this->db->select('a.*, b.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, d.period, d.wp');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('production_schedules d', 'a.workorder = d.workorder and a.item_fg_id = d.item_fg_id');
            // $this->db->join('uom e', 'c.uom_id = e.id');
            $this->db->join('bom f', 'a.item_fg_id = f.item_fg_id and a.item_rm_id = f.item_rm_id');
            $this->db->where('a.request_no', $request_no);
            if (strpos($request_no, 'SH') === 0) {
                $this->db->not_like('a.item_rm_id', 'RMCH', 'after');
            }
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            if ($totalRows <= 0) {
                $this->db->select("a.*, '-' as period, '-' as wp, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as item_rm_id, b.number as item_rm_no, b.name as item_rm_name, b.uom");
                $this->db->from('supply_materials a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                // $this->db->join('uom e', 'b.uom_id = e.id');
                $this->db->where('a.request_no', $request_no);
                $totalRows = $this->db->count_all_results('', false);
                //Get Data Array
                $records = $this->db->get()->result_array();
                if ($totalRows <= 0) {
                    $this->db->select("a.*, '-' as period, '-' as wp, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as item_rm_id, b.number as item_rm_no, b.name as item_rm_name, b.uom");
                    $this->db->from('supply_requestions a');
                    $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                    // $this->db->join('uom e', 'b.uom_id = e.id');
                    $this->db->where('a.request_no', $request_no);
                    $totalRows = $this->db->count_all_results('', false);
                    //Get Data Array
                    $records = $this->db->get()->result_array();
                }
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $receipt_id = $this->input->post('receipt_id');
            $request_no = $this->input->post('request_no');
            $query = $this->db->query("SELECT b.item_rm_id FROM purchase_order_labels a join purchase_order_receipts b on a.receipt_id = b.receipt_id join supply_sheets c on b.item_rm_id = c.item_rm_id WHERE a.label_no='".$receipt_id."' and c.request_no='".$request_no."' and a.status=1");
            $totalRows=0;
            $records=[];
            if ($query->num_rows() > 0) { // ada datanya di supply sheet
                $rows = $query->result();
                $this->db->select('a.label_no, b.item_rm_id, a.qty, b.item_rm_id as eq_item_rm_id, a.qty as qty_po');
                $this->db->from('purchase_order_labels a');
                $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
                $this->db->where('a.label_no', $receipt_id);
                $this->db->where('a.status', 1);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select('a.label_divided as label_no, b.item_rm_id, a.qty, b.item_rm_id as eq_item_rm_id, a.qty as qty_po');
                    $this->db->from('barcode_divides a');
                    $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                    $this->db->where('a.label_divided', $receipt_id);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();
                }

                if (!$records) {
                    $this->db->select('label_no, item_rm_id, qty, item_rm_id as eq_item_rm_id, qty as qty_po');
                    $this->db->from('new_barcode');
                    $this->db->where('label_no', $receipt_id);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();
                }
            }else{ // gak ada datanya di supply sheet

                $querypo = $this->db->query("SELECT b.item_rm_id, a.qty FROM purchase_order_labels a join purchase_order_receipts b on a.receipt_id = b.receipt_id WHERE a.label_no='".$receipt_id."' and a.status=1");
               
                if ($querypo->num_rows() > 0) { // ada datanya di supply sheet
                    //$rows = $query->result();
                     $rowspo = $querypo->row();

                    if (strpos($request_no, 'SH') === 0) {
    
                        $this->db->select('item_rm_id');
                        $this->db->where('eq_1', $rowspo->item_rm_id); // where item rm id = RMPLNA-0031
                        $this->db->or_where('eq_2', $rowspo->item_rm_id);
                        $this->db->or_where('eq_3', $rowspo->item_rm_id);
                        $this->db->or_where('eq_4', $rowspo->item_rm_id);
                        $this->db->or_where('eq_5', $rowspo->item_rm_id);
                        $queryEq = $this->db->get('equivalents');
                        $resultEq = $queryEq->row();
    
    
    
                        $this->db->select('item_rm_id');
                        $this->db->where('item_rm_id', $resultEq->item_rm_id);  // $resultEq->item_rm_id = RMPLNA-0027
                        $this->db->where('request_no', $request_no);
                        $querySS = $this->db->get('supply_sheets');
                        $supplySheets = $querySS->result();
                        if ($querySS->num_rows() > 0) {
                            $this->db->select("a.label_no, b.item_rm_id, a.qty, '$resultEq->item_rm_id' as eq_item_rm_id, '$rowspo->qty' as qty_po");
                            $this->db->from('purchase_order_labels a');
                            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
                            $this->db->where('a.label_no', $receipt_id);
                            $this->db->where('a.status', 1);
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
    
                            if (!$records) {
                                $this->db->select("a.label_divided as label_no, b.item_rm_id, a.qty, '$resultEq->item_rm_id' as eq_item_rm_id, '$rowspo->qty' as qty_po");
                                $this->db->from('barcode_divides a');
                                $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                                $this->db->where('a.label_divided', $receipt_id);
                                $totalRows = $this->db->count_all_results('', false);
                                $records = $this->db->get()->result_array();
                            }
            
                            if (!$records) {
                                $this->db->select("label_no, item_rm_id, qty, '$resultEq->item_rm_id' as eq_item_rm_id, '$rowspo->qty' as qty_po");
                                $this->db->from('new_barcode');
                                $this->db->where('label_no', $receipt_id);
                                $totalRows = $this->db->count_all_results('', false);
                                $records = $this->db->get()->result_array();
                            }
    
                        }
    
                    }else{
                        
                        $this->db->select("a.label_no, b.item_rm_id, a.qty, '$rowspo->item_rm_id' as eq_item_rm_id, '$rowspo->qty' as qty_po");
                        $this->db->from('purchase_order_labels a');
                        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
                        $this->db->where('a.label_no', $receipt_id);
                        $this->db->where('a.status', 1);
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();
    
                        if (!$records) {
                            $this->db->select("a.label_divided as label_no, b.item_rm_id, a.qty, '$rowspo->item_rm_id' as eq_item_rm_id, '$rowspo->qty' as qty_po");
                            $this->db->from('barcode_divides a');
                            $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                            $this->db->where('a.label_divided', $receipt_id);
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
                        }
        
                        if (!$records) {
                            $this->db->select("label_no, item_rm_id, qty, '$rowspo->item_rm_id' as eq_item_rm_id, '$rowspo->qty' as qty_po");
                            $this->db->from('new_barcode');
                            $this->db->where('label_no', $receipt_id);
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
                        }
                    }
                }else{
                     $querynew_barcode = $this->db->query("SELECT item_rm_id, qty FROM new_barcode WHERE label_no='".$receipt_id."' and status=0");
                      $rowsnew_barcode = $querynew_barcode->row();
                     
                        $this->db->select("a.label_no, b.item_rm_id, a.qty, '$rowsnew_barcode->item_rm_id' as eq_item_rm_id, '$rowsnew_barcode->qty' as qty_po");
                        $this->db->from('purchase_order_labels a');
                        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
                        $this->db->where('a.label_no', $receipt_id);
                        $this->db->where('a.status', 1);
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();
    
                        if (!$records) {
                            $this->db->select("a.label_divided as label_no, b.item_rm_id, a.qty, '$rowsnew_barcode->item_rm_id' as eq_item_rm_id, '$rowsnew_barcode->qty' as qty_po");
                            $this->db->from('barcode_divides a');
                            $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                            $this->db->where('a.label_divided', $receipt_id);
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
                        }
        
                        if (!$records) {
                            $this->db->select("label_no, item_rm_id, qty, '$rowsnew_barcode->item_rm_id' as eq_item_rm_id, '$rowsnew_barcode->qty' as qty_po");
                            $this->db->from('new_barcode');
                            $this->db->where('label_no', $receipt_id);
                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
                        }
               
                    
                }
    

            }


            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    
    public function datatables()
    {
        if ($this->input->get()) {
            $request_no = base64_decode($this->input->get('request_no'));

            //Select Query
            $this->db->select('a.*, c.number as item_number, c.number as item_rm_no, c.name as item_rm_name, c.uom, COALESCE(d.qty_req, 0) as qty_req, 0 as qty_req_crusher, f.warehouse, (COALESCE(d.qty_req,0) - a.qty) as balance, g.mpq, h.qty_issued, h.qty_act');
            $this->db->from('issued_materials a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('(SELECT item_rm_id, request_no, SUM(qty) as qty_req FROM issued_material_details GROUP BY request_no, item_rm_id) d', 'd.request_no = a.request_no and d.item_rm_id = a.item_rm_id', 'left');
            $this->db->join('wip_balances f', 'a.item_rm_id = f.item_rm_id and a.request_no = f.request_no', 'left');
            $this->db->join('supplier_items g', 'a.item_rm_id = g.item_rm_id', 'left');
            $this->db->join('supply_sheets h', 'a.item_rm_id = h.item_rm_id and a.request_no=h.request_no', 'left');
            $this->db->where('a.eq_from !=', '-');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if ($request_no != "") {
                $this->db->where('a.request_no', $request_no);
                if (strpos($request_no, 'SH') === 0) {
                    $this->db->not_like('a.item_rm_id', 'RMCH', 'after');
                }
            }
            $this->db->group_by('a.request_no');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.item_rm_id', 'ASC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            // Jika request_no null, tambahkan data dari new_barcode
            if ($request_no == "") {
                $this->db->select('label_no, item_rm_id, qty, uom');
                $this->db->from('new_barcode');
                $this->db->where('status', 0);
                $newBarcodeRecords = $this->db->get()->result_array();
                $records = array_merge($records, $newBarcodeRecords);
                $totalRows += count($newBarcodeRecords);
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    public function create()
    {
        if ($this->input->post()) {
            // if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $post['transaction_type'] = 'IS-0001';

                if (empty($post['item_fg_id'])) {
                    $post['item_fg_id'] = null;
                }

                $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']]);
                if (!$issued_materials) {
                    $send   = $this->crud->create('issued_materials', $post);
                    echo $send;
                } else {
                    $send   = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_fg_id" => $post['item_fg_id'], "item_rm_id" => $post['item_rm_id']], $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        // } else {
        //     show_error("Cannot Process your request");
        // }
    }

    
    public function create_label()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $post['transaction_type'] = 'IS-0001';
            $request_no = $post['request_no'];
            $item_rm_id = $post['item_rm_id'];
            $eq_item_rm_id = $post['eq_item_rm_id'];
            $qty_po = intval($post['qty_po']);
            $label_no = $post['label_no'];
            if($item_rm_id != $eq_item_rm_id){
                $this->db->select('*');
                $this->db->from('issued_materials');
                $this->db->where('item_rm_id', $eq_item_rm_id);
                $this->db->where('request_no', $request_no); 
                $queryIM = $this->db->get();
                $resultIM = $queryIM->row();
                $this->db->select('*');
                $this->db->from('supply_sheets');
                $this->db->where('item_rm_id', $eq_item_rm_id);
                $this->db->where('request_no', $request_no); 
                $querySup = $this->db->get();
                $resultSup = $querySup->row();
                $this->db->select('*');
                $this->db->from('supplier_items');
                $this->db->where('item_rm_id', $item_rm_id);
                $querySI = $this->db->get();
                $resultSI = $querySI->row();
                $qty_act=$resultSI->mpq;

                if(intval($resultIM->qty) > intval($resultSI->mpq)){
                    $roundingUpResult = ceil(intval($resultIM->qty) / intval($resultSI->mpq));
                    $qty_act=intval($resultSI->mpq) * $roundingUpResult;
                }

                $this->crud->update('issued_materials',["request_no" => $request_no,"item_rm_id" => $eq_item_rm_id],['eq_from'=>'-']);
                //$this->crud->update('supply_sheets',["request_no" => $request_no,"item_rm_id" => $eq_item_rm_id ],['qty_act'=> 'qty_act - '.$qty_po]);

                $this->crud->create('issued_materials', ["request_no" => $request_no, "item_rm_id" => $item_rm_id, "period"=> $resultIM->period, "wp"=> $resultIM->wp,"workorder"=>$resultIM->workorder,"qty"=>$resultIM->qty, "transaction_type"=>$resultIM->transaction_type, "eq_from"=>$eq_item_rm_id]);

                $this->crud->create('supply_sheets', ["request_no" => $request_no, "item_fg_id"=>$resultSup->item_fg_id, "item_rm_id" => $item_rm_id, "request_date"=> $resultSup->request_date, "request_name"=> $resultSup->request_name,"workorder"=>$resultSup->workorder, "mpq"=>$resultSI->mpq, "qty_req"=>$resultSup->qty_req, "qty_act"=> $qty_act, "qty_issued"=>$resultSup->qty_issued, "qty_bal"=>$resultSup->qty_bal]);
                
            }

            $post = [
                "request_no" => $request_no,
                "label_no" => $label_no,
                "item_rm_id" => $item_rm_id,
                "qty" => $post['qty'],
            ];


            $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_rm_id='$item_rm_id'");
            $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $label_no]);
            $purchase_order_labels = $this->crud->read("purchase_order_labels", [], ["label_no" => $label_no, "status" => 1]);
            $barcode_divides = $this->crud->read("barcode_divides", [], ["label_divided" => $label_no, "status" => 0]);
            $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);

            // Cek apakah label_no ada di tabel new_barcode dengan status 0
            $new_barcode = $this->crud->read("new_barcode", [], ["label_no" => $label_no, "status" => 0]);

            if (!$issued_material_details) {
                if ($purchase_order_labels) {
                    if ($issued_materials) {
                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $purchase_order_labels->receipt_id]);
                        $receipt_date_current = $purchase_order_receipts->receipt_date;
                        
                        // Validasi FIFO berdasarkan tanggal lebih tua
                        $checkOlder = $this->crud->query("
                            SELECT b.label_no, a.receipt_date
                            FROM purchase_order_receipts a
                            JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                            LEFT JOIN barcode_divides c ON b.label_no = c.label_no
                            LEFT JOIN issued_material_details d ON b.label_no = d.label_no
                            WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id'
                            AND DATE(a.receipt_date) < DATE('$receipt_date_current')
                            AND c.label_no IS NULL
                            AND d.label_no IS NULL
                            ORDER BY a.receipt_date ASC
                        ");

                        if (count($checkOlder) > 0) {
                            echo json_encode([
                                "title" => "FIFO Violation",
                                "message" => "There are old labels that have not been processed",
                                "theme" => "error"
                            ]);
                            return;
                        }

                        $this->db->select('
                            COUNT(a.id) as total_items,
                            SUM(CASE WHEN a.qty = COALESCE(d.qty_actual, 0) THEN 1 ELSE 0 END) as closed_items
                        ');
                        $this->db->from('supply_materials a');
                        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                        $this->db->join('(
                            SELECT request_no, item_rm_id, COALESCE(SUM(qty), 0) as qty_actual 
                            FROM issued_material_details 
                            GROUP BY request_no, item_rm_id
                        ) d', 'a.request_no = d.request_no AND a.item_rm_id = d.item_rm_id', 'left');

                        $this->db->where('a.deleted', 0);
                        $this->db->where('a.request_no', $request_no);
                        $this->db->where('a.item_rm_id', $item_rm_id);

                        $checkSt = $this->db->get()->row();

                        if ($checkSt && isset($checkSt->total_items) && isset($checkSt->closed_items)) {
                            $status = ($checkSt->total_items == $checkSt->closed_items) ? "1" : "0";
                            if ($status === "0") {
                                if (!empty($purchase_order_labels->lot_no)) {
                                    $this->crud->update('supply_materials', [
                                        "request_no" => $request_no,
                                        "item_rm_id" => $item_rm_id
                                    ], ['lot_no' => $purchase_order_labels->lot_no]);
                                }
                            } else {
                                echo json_encode([
                                    "title" => "Label Already Scan",
                                    "message" => "The label has already been scanned",
                                    "theme" => "error"
                                ]);
                                return;
                            }
                        }

                        $this->crud->create('issued_material_details', $post);
                        $this->update_wip_balances($request_no, $item_rm_id, $post['qty']);
                        echo json_encode([
                            "title" => "Success",
                            "message" => "Label processed successfully",
                            "theme" => "success"
                        ]);
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }
                } elseif ($barcode_divides) {
                    if ($issued_materials) {
                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $barcode_divides->reff]);
                        $checkItems = $this->crud->query("SELECT a.receipt_date, c.label_divided, c.label_no, a.receipt_id, b.receipt_id, d.label_no
                        FROM purchase_order_receipts a
                        LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                        LEFT JOIN barcode_divides c ON b.label_no = c.label_no and c.type = 'SUPPLY'
                        LEFT JOIN issued_material_details d ON a.item_rm_id = d.item_rm_id and (b.label_no = d.label_no or c.label_divided = d.label_no)
                        WHERE a.item_rm_id = '$purchase_order_receipts->item_rm_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND c.status = 0 AND d.label_no is null
                        ORDER BY receipt_date ASC");

                        if (count($checkItems) <= 0) {
                            $send = $this->crud->create('issued_material_details', $post);
                            $update = $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);
                            // Update wip_balances table
                            $this->update_wip_balances($request_no, $item_rm_id, $post['qty']);
                            echo $send;
                        } else {
                            echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }
                } elseif ($new_barcode) {
                        // Tidak perlu cek FIFO karena new_barcode tidak terkait dengan receipt_date
                        // if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                            // Tambahkan data ke issued_materials jika belum ada
                            if (!$issued_materials) {
                                $issued_material_data = [
                                    'request_no' => $request_no,
                                    'item_rm_id' => $item_rm_id,
                                    'qty' => $post['qty'],
                                    'transaction_type' => $post['transaction_type']
                                ];
                                $this->crud->create('issued_materials', $issued_material_data);
                            }

                            // Tambahkan data ke issued_material_details
                            $issued_detail_data = [
                                'request_no' => $request_no,
                                'item_rm_id' => $item_rm_id,
                                'label_no' => $label_no,
                                'qty' => $post['qty']
                            ];
                            $this->crud->create('issued_material_details', $issued_detail_data);

                            // Update status di new_barcode
                            $this->crud->update('new_barcode', ["label_no" => $post['label_no']], ["status" => 1]);

                            // Update status di supply_materials
                            $this->crud->update('supply_materials', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], ["status" => 1]);

                            // Update wip_balances table
                            $this->update_wip_balances($request_no, $item_rm_id, $post['qty']);

                            echo json_encode(array("title" => "Success", "message" => "Label processed and details created successfully", "theme" => "success"));
                        // } else {
                        //     // echo json_encode(array("title" => "More Than Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                        // }
                } else {
                    echo json_encode(array("title" => "Not Scanned In", "message" => "This label has not been scanned in", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    private function update_wip_balances($request_no, $item_rm_id, $qty)
    {
        $wip_balance = $this->crud->read("wip_balances", [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);
        if ($wip_balance) {
            $new_issued = $wip_balance->issued + $qty;
            $new_balance = $new_issued + $wip_balance->begin - $wip_balance->need;
            $new_warehouse = $wip_balance->warehouse - $qty; // Update warehouse column
            $this->crud->update('wip_balances', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], ["begin"=>$wip_balance->balance, "issued" => $new_issued, "balance" => $new_balance, "warehouse" => $new_warehouse]);
        }
    }
}