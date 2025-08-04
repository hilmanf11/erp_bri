<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_orders extends CI_Controller

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
        $this->load->helper('date');

    }



    //HALAMAN UTAMA

    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);

            $this->load->view('sales/delivery_orders');
        } else {

            redirect('error_access');
        }
    }

    public function readDeliveryOrders()
    {
        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to   = base64_decode($this->input->get('filter_to'));

        $this->db->select('delivery_order_no');
        $this->db->from('delivery_orders');
        
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('delivery_order_date >=', $filter_from);
            $this->db->where('delivery_order_date <=', $filter_to);
        }

        $this->db->where('delivery_order_no IS NOT NULL', null, false);
        $this->db->group_by('delivery_order_no');
        $this->db->order_by('delivery_order_no', 'DESC');

        $result = $this->db->get()->result();
        echo json_encode($result);
    }

    public function readSalesOrder($delivery_order_no)
    {
        $delivery_order_no = base64_decode($delivery_order_no);

        $this->db->select('sales_order_no');
        $this->db->from('delivery_orders');
        $this->db->where('delivery_order_no', $delivery_order_no);
        $this->db->where('sales_order_no IS NOT NULL', null, false);
        $this->db->group_by('sales_order_no');
        $this->db->order_by('sales_order_no', 'DESC');

        $result = $this->db->get()->result();
        echo json_encode($result);
    }

    public function readCustomerOrder()
    {
        // $delivery_order_no = base64_decode($delivery_order_no);
        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to   = base64_decode($this->input->get('filter_to'));

        $this->db->select('customer_order_no');
        $this->db->from('delivery_orders');
        // $this->db->where('delivery_order_no', $delivery_order_no);

        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('delivery_order_date >=', $filter_from);
            $this->db->where('delivery_order_date <=', $filter_to);
        }

        $this->db->where('customer_order_no IS NOT NULL', null, false);
        $this->db->group_by('customer_order_no');
        $this->db->order_by('customer_order_no', 'DESC');

        $result = $this->db->get()->result();
        echo json_encode($result);
    }

    public function readSalesOrderDeliveries($sales_order,$customer_order_no)
    {
        $cust_order_no = base64_decode($customer_order_no);
        if ($sales_order == "FG") {
            $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_deliveries WHERE `status` = '0' and customer_order_no='".$cust_order_no."' ORDER BY trans_date ASC");
            echo json_encode($send);
        } else {
            $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_delivery_rm WHERE `status` = '0' and customer_order_no='".$cust_order_no."'");
            echo json_encode($send);
        }
    }
    // public function readSalesOrderDeliveries($sales_order)

    // {

    //     if($sales_order == "FG"){

    //         $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_deliveries WHERE `status` = '0'");

    //         echo json_encode($send);

    //     }else{

    //         $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_delivery_rm WHERE `status` = '0'");

    //         echo json_encode($send);

    //     }

    // }


    public function readsC($sales_order, $delivery_date)
    {
        $delivery_date = base64_decode($delivery_date);
        if ($sales_order == "FG") {
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_orders a
                JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                -- JOIN production_schedules d ON a.sales_order_no = d.so_number
                WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");
            echo json_encode($send);
        } else {
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_order_rm a
                JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");
            echo json_encode($send);
        }
    }
    public function readsCust($sales_order, $customer_id)
    {
        //$delivery_date = base64_decode($delivery_date);
        if ($sales_order == "FG") {
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_orders a
                JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                -- JOIN production_schedules d ON a.sales_order_no = d.so_number
                WHERE a.status != 1 and c.id = '$customer_id' GROUP BY c.id");
            echo json_encode($send);
        } else {
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_order_rm a
                JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                WHERE a.status = 0 and c.id = '$customer_id' GROUP BY c.id");
            echo json_encode($send);
        }
    }

    // public function readsC($sales_order, $delivery_date)

    // {

    //     $delivery_date = base64_decode($delivery_date);



    //     if ($sales_order == "FG") {

    //         $send = $this->crud->query("SELECT c.id, c.name, c.number

    //             FROM sales_orders a

    //             JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0

    //             JOIN customers c ON a.customer_id = c.id

    //             JOIN production_schedules d ON a.sales_order_no = d.so_number

    //             WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");

    //         echo json_encode($send);
    //     } else {

    //         $send = $this->crud->query("SELECT c.id, c.name, c.number

    //             FROM sales_order_rm a

    //             JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0

    //             JOIN customers c ON a.customer_id = c.id

    //             WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");

    //         echo json_encode($send);
    //     }
    // }



    public function readsCustOrderNo($sales_order)//, $customer_id, $delivery_date)

    {

        // $delivery_date = base64_decode($delivery_date);

        // $customer_id = base64_decode($customer_id);



        if ($sales_order == "FG") {

            $send = $this->crud->query("SELECT a.customer_order_no, a.customer_id as id, c.name as customer_name

                    FROM sales_orders a 

                    JOIN customers c ON a.customer_id = c.id

                    JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0

                    WHERE a.status != 1 GROUP BY a.customer_order_no");
                    //customer_id= '$customer_id' and a.status = 0 and b.trans_date = '$delivery_date' GROUP BY a.customer_order_no");

            echo json_encode($send);
        } else {

            $send = $this->crud->query("SELECT a.customer_order_no, a.customer_id as id, c.name as customer_name

                    FROM sales_order_rm a 

                    JOIN customers c ON a.customer_id = c.id

                    JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0

                    WHERE a.status = 0 GROUP BY a.customer_order_no");

                    //WHERE a.customer_id= '$customer_id' and a.status = 0 and b.trans_date = '$delivery_date' GROUP BY a.customer_order_no");

            echo json_encode($send);
        }
    }



    public function number($delivery_order_date, $customer_id, $customer_no)

    {
        $current_date = date('Y-m-d');
        $current_year = date('y'); // Last two digits of current year
        $current_month = date('m');
        $date_year = date('Y');
        
        // Format part of the sequence
        $prefix = $customer_no . '/' . $current_month . '/' . $current_year;
        // $query = $this->db->query("SELECT count(id) as kode,CAST(SUBSTRING_INDEX(delivery_order_no, '/', 1) AS UNSIGNED) AS first FROM delivery_orders WHERE customer_id='".$customer_id."' and YEAR(delivery_date) = '".$date_year."' group by delivery_order_no order by created_date DESC");

        $query = $this->db->query("
            SELECT 
                CAST(SUBSTRING_INDEX(delivery_order_no, '/', 1) AS UNSIGNED) AS first
            FROM delivery_orders 
            WHERE customer_id = '".$customer_id."'
            AND YEAR(delivery_date) = '".$date_year."'
            ORDER BY first DESC 
            LIMIT 1
        ");

        if ($query->num_rows() > 0) {
            // Sequence exists for this year, get the latest sequence number
            $row = $query->row();//$query->num_rows();//$query->row();
            $new_sequence_number =sprintf("%04d", intval($row->first) + 1);// "0006";//sprintf("%04d", intval($row) + 1);
            //$new_sequence_number = explode('/', $row->delivery_order_no)[0];
        } else {
            // No sequence exists for this year, insert a new one starting from 1
            $new_sequence_number = '0001';
        }
        // Final sequence number
        $final_sequence = $new_sequence_number . '/' . $prefix;
        echo $final_sequence;

        // $datenow    = "DO" . $customer_no . date("ym", strtotime(base64_decode($delivery_order_date)));

        // $sqlGetID   = $this->db->query("SELECT max(`delivery_order_no`) as kode FROM delivery_orders WHERE `delivery_order_no` like '%$datenow%'");

        // $rowID      = $sqlGetID->row();

        // $kode       = $rowID->kode;

        // if ($kode == NULL) {

        //     $autoID = sprintf("%04s", $kode + 1);
        // } else {

        //     $urutan = (int) substr($kode, -3);

        //     $urutan++;

        //     $autoID = sprintf("%04s", $urutan);
        // }

        // echo $datenow . $autoID;
    }



    public function datatablesTemp($sales_order, $delivery_date, $customer_id, $customer_order_no)

    {

        $delivery_date = base64_decode($delivery_date);

        $customer_id = base64_decode($customer_id);

        $customer_order_no = explode(",", base64_decode($customer_order_no));

        $queryEarlierDate = $this->db->query("SELECT item_fg_id, MIN(actual_delivery_date) AS earliest_date FROM delivery_orders WHERE status=1");
        
        if ($queryEarlierDate->num_rows() > 0) {
            $row = $queryEarlierDate->row();
            $earlierDate = $row->earliest_date;
        } else {
            $earlierDate = date('Y-m-d');
        }

        $filter_from = date('2025-01-01');
        $filter_to = date('Y-m-d');
        $today = date('Y-m-d');


        $query_qty_in_fg_scan_in = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        AND a.scan_date = '$today'
        GROUP BY a.item_fg_id";

        $query_qty_os_fg = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        AND a.trans_date = '$today'
        GROUP BY a.item_fg_id";

        $query_transaction_fg_in = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date = '$today'
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";

        $query_qty_out = "SELECT a.item_fg_id, IFNULL(SUM(a.qty), 0) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date = '$today'
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

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
        AND DATE(dn.delivery_note_date) = '$today'
        GROUP BY item_fg_id";



        $query_qty_in_fg_scan_in2 = "SELECT a.item_fg_id, SUM(a.qty) as fg_scan_in
        FROM fg_scan_in_label a
        WHERE a.deleted = 0
        AND a.scan_date < '$today'
        GROUP BY a.item_fg_id";

        $query_qty_os_fg2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_os_fg
        FROM os_fg a
        WHERE a.deleted = 0
        AND a.trans_date < '$today'
        GROUP BY a.item_fg_id";
                    
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date < '$today'
        AND LEFT(a.transaction_type, 2) = 'RE'
        GROUP BY a.item_fg_id";

        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.deleted = 0
        AND a.request_date < '$today'
        AND LEFT(a.transaction_type, 2) = 'IS'
        GROUP BY a.item_fg_id";

        $query_delivery_notes2 = "SELECT dn.item_fg_id, SUM(dn.qty) as initial_out_g
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
        AND dn.delivery_note_date < '$today'
        GROUP BY dn.item_fg_id";


        $subquery_end_stock = "
            SELECT 
                a.id as item_fg_id,
                (
                    COALESCE(x.begin_stock, 0) + 
                    COALESCE(qc.fg_scan_in, 0) + 
                    COALESCE(qnc.qty_os_fg, 0) + 
                    COALESCE(qi.initial_in, 0) -
                    (
                        COALESCE(qo.qty_out, 0) + 
                        COALESCE(qg.initial_out_g, 0)
                    )
                ) as end_stock
            FROM item_fg a
            LEFT JOIN (
                SELECT a.id,
                    (
                        COALESCE(qc.fg_scan_in, 0) + 
                        COALESCE(qi.initial_in, 0) + 
                        COALESCE(qnc.qty_os_fg, 0) - 
                        (
                            COALESCE(qo.qty_out, 0) + 
                            COALESCE(qg.initial_out_g, 0)
                        )
                    ) AS begin_stock
                FROM item_fg a
                LEFT JOIN ($query_qty_in_fg_scan_in2) qc ON a.id = qc.item_fg_id
                LEFT JOIN ($query_qty_os_fg2) qnc ON a.id = qnc.item_fg_id
                LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
                LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
                LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
                GROUP BY a.id
            ) x ON a.id = x.id
            LEFT JOIN ($query_qty_in_fg_scan_in) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_os_fg) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        ";

        if ($sales_order == "FG") {
            $this->db->select("
                b.item_fg_id,
                d.number as item_fg_number,
                d.name as item_fg_name,
                b.customer_order_no,
                b.sales_order_no,
                d.uom,
                b.qty as qty_so,
                (b.qty - COALESCE(SUM(c.qty_del), 0)) as qty_remain,
                COALESCE(SUM(c.qty_del), 0) as qty_do,
                (
                    CASE
                        WHEN EXISTS (
                            SELECT 1 FROM delivery_orders do 
                            WHERE do.item_fg_id = a.item_fg_id 
                            AND do.customer_order_no = a.customer_order_no 
                            AND do.delivery_date = a.trans_date
                            AND do.partial = 1
                            AND do.deleted = 0
                        )
                        THEN (
                            a.qty - COALESCE((
                                SELECT SUM(do2.qty_del) 
                                FROM delivery_orders do2 
                                WHERE do2.item_fg_id = a.item_fg_id 
                                AND do2.customer_order_no = a.customer_order_no 
                                AND do2.delivery_date = a.trans_date 
                                AND do2.deleted = 0
                            ), 0)
                        )
                        ELSE COALESCE(a.qty, 0)
                    END
                ) as qty_del,
                
                es.end_stock as stock,

                ((b.qty - COALESCE(SUM(c.qty_del), 0)) - COALESCE(a.qty, 0)) as stock_bal,

                (
                    CASE
                        WHEN EXISTS(
                            SELECT 1 FROM delivery_orders do2
                            WHERE do2.item_fg_id = a.item_fg_id
                                AND do2.customer_order_no = a.customer_order_no
                                AND do2.delivery_date = a.trans_date
                                AND do2.partial = 1
                                AND do2.deleted = 0
                        )
                        THEN '1'
                        ELSE '0'
                    END
                ) AS partial

            ");
            
            $this->db->from('sales_orders b');
            
            $this->db->join("($subquery_end_stock) es", "es.item_fg_id = b.item_fg_id", "left");
            
            //$this->db->join('sales_order_deliveries a', 'a.customer_order_no = b.customer_order_no and a.trans_date = b.delivery_date');
            $this->db->join('sales_order_deliveries a', 'a.customer_order_no = b.customer_order_no and a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');

            //$this->db->join('sales_order_deliveries a', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');

            //$this->db->join('delivery_orders c', 'b.sales_order_no = c.sales_order_no and b.item_fg_id = c.item_fg_id and b.customer_id = c.customer_id', 'left');
            $this->db->join('delivery_orders c', 'b.item_fg_id = c.item_fg_id and b.customer_order_no = c.customer_order_no', 'left');

            $this->db->join('item_fg d', 'b.item_fg_id = d.id');

            // $this->db->join('scan_item_receipts_fg e', 'a.sales_order_no = e.so_number', 'left');
            // $this->db->join('fg_scan_in_label f', 'b.item_fg_id = f.item_fg_id', 'left');
            // $this->db->join('os_fg g', 'b.item_fg_id = g.item_fg_id', 'left');
            // $this->db->join('shipping_orders h', 'b.item_fg_id = h.item_fg_id', 'left');

            $this->db->where('b.customer_id', $customer_id);
            $this->db->where('a.trans_date', $delivery_date);
            $this->db->where('a.status', 0);

            $this->db->where_in('b.customer_order_no', $customer_order_no);

            $this->db->group_by('b.item_fg_id');

            $this->db->group_by('b.sales_order_no');

            $this->db->order_by('b.item_fg_id', 'asc');
        } else {

            $this->db->select("
                b.item_fg_id, 
                d.number as item_fg_number, 
                d.name as item_fg_name, 
                b.customer_order_no,
                b.sales_order_no,
                d.uom,
                b.qty as qty_so, 
                (b.qty - COALESCE(SUM(c.qty_del), 0)) as qty_remain,
                COALESCE(SUM(c.qty_del), 0) as qty_do,
                COALESCE(a.qty, 0) as qty_del,
                
                /* Perhitungan stok akhir */
                (
                    /* Begin Stock */
                    (
                        (SELECT COALESCE(SUM(f1.qty),0) FROM fg_scan_in_label f1 
                        WHERE f1.deleted = 0 AND f1.scan_date < '$filter_from' AND f1.item_fg_id = b.item_fg_id)
                        +
                        (SELECT COALESCE(SUM(g1.qty),0) FROM os_fg g1 
                        WHERE g1.deleted = 0 AND g1.trans_date < '$filter_from' AND g1.item_fg_id = b.item_fg_id)
                        -
                        (SELECT COALESCE(SUM(h1.qty),0) FROM shipping_orders h1 
                        WHERE h1.deleted = 0 AND h1.created_date < '$filter_from' AND h1.item_fg_id = b.item_fg_id)
                    )
                    +
                    /* Qty In (selama periode) */
                    (
                        (SELECT COALESCE(SUM(f2.qty),0) FROM fg_scan_in_label f2 
                        WHERE f2.deleted = 0 AND f2.scan_date BETWEEN '$filter_from' AND '$filter_to' AND f2.item_fg_id = b.item_fg_id)
                        +
                        (SELECT COALESCE(SUM(g2.qty),0) FROM os_fg g2 
                        WHERE g2.deleted = 0 AND g2.trans_date BETWEEN '$filter_from' AND '$filter_to' AND g2.item_fg_id = b.item_fg_id)
                    )
                    -
                    /* Qty Out (selama periode) */
                    (
                        (SELECT COALESCE(SUM(h2.qty),0) FROM shipping_orders h2 
                        WHERE h2.deleted = 0 AND h2.created_date BETWEEN '$filter_from' AND '$filter_to' AND h2.item_fg_id = b.item_fg_id)
                    )
                ) AS stock,

                ((b.qty - COALESCE(SUM(c.qty_del), 0)) - a.qty) as stock_bal
            ");

            $this->db->from('sales_order_rm b');

            $this->db->join('sales_order_delivery_rm a', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');

            $this->db->join('delivery_orders c', 'b.item_fg_id = c.item_fg_id', 'left');

            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            
            // $this->db->join('scan_item_receipts_fg e', 'a.sales_order_no = e.so_number', 'left');
            // $this->db->join('fg_scan_in_label f', 'b.item_fg_id = f.item_fg_id', 'left');
            // $this->db->join('os_fg g', 'b.item_fg_id = g.item_fg_id', 'left');
            // $this->db->join('shipping_orders h', 'b.item_fg_id = h.item_fg_id', 'left');

            $this->db->where('b.customer_id', $customer_id);

            $this->db->where_in('b.customer_order_no', $customer_order_no);

            $this->db->group_by('b.item_fg_id');

            $this->db->group_by('b.sales_order_no');

            $this->db->order_by('b.item_fg_id', 'asc');
        }



        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }



    //GET DATATABLES

    public function datatables()

    {

        if ($this->input->post()) {

            $get = $this->input->get();

            $filter_from = @base64_decode($get['filter_from']);

            $filter_to = @base64_decode($get['filter_to']);

            $filter_customer_id = @base64_decode($get['filter_customer_id']);

            $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);

            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);

            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);

            $filter_item_fg = @base64_decode($get['filter_item_fg']);

            $filter_status = @base64_decode($get['filter_status']);



            $page = $this->input->post('page');

            $rows = $this->input->post('rows');

            //Pagination 1-10

            $page   = isset($page) ? intval($page) : 1;

            $rows   = isset($rows) ? intval($rows) : 10;

            $offset = ($page - 1) * $rows;

            $result = array();

            //Select Query

            $this->db->select("a.*, b.name as customer_name");

            $this->db->from('delivery_orders a');

            $this->db->join('customers b', 'a.customer_id = b.id');

            if ($filter_from != "" && $filter_to != "") {

                $this->db->where('a.delivery_order_date >=', $filter_from);

                $this->db->where('a.delivery_order_date <=', $filter_to);
            }

            $this->db->like('a.customer_id', $filter_customer_id);

            $this->db->like('a.delivery_order_no', $filter_delivery_order_no);

            $this->db->like('a.sales_order_no', $filter_sales_order_no);

            $this->db->like('a.item_fg_id', $filter_item_fg);

            $this->db->like('a.customer_order_no', $filter_customer_order_no);

            $this->db->like('a.status', $filter_status);

            $this->db->group_by('a.delivery_order_no');

            $this->db->order_by('a.status', 'ASC');

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

    public function datatableDetails()

    {

        if ($this->input->get()) {

            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));



            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');

            $this->db->from('delivery_orders a');

            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            $this->db->where('a.delivery_order_no', $delivery_order_no);

            $this->db->order_by('b.number', 'ASC');

            $records = $this->db->get()->result_array();



            echo json_encode($records);
        }
    }



    // GET DATATABLES UPDATE

    public function datatableUpdates()

    {

        if ($this->input->get()) {

            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));



            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');

            $this->db->from('delivery_orders a');

            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            $this->db->where('a.delivery_order_no', $delivery_order_no);

            $this->db->order_by('b.number', 'ASC');

            $records = $this->db->get()->result_array();



            echo json_encode($records);
        }
    }



    //CREATE DATA

    private function to_boolean($value) {
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

    public function create()
    {
        if (!$this->input->post('items')) {
            show_error("Cannot Process your request");
        }

        $items = $this->input->post('items');
        $errors = [];
        $success_count = 0;

        $this->db->trans_begin();

        foreach ($items as $post) {
            $delivery_orders = $this->crud->read("delivery_orders", [], [
                "delivery_order_no" => $post['delivery_order_no'],
                "item_fg_id"        => $post['item_fg_id'],
                "sales_order_no"    => $post['sales_order_no']
            ]);

            $send = false;

            // Cek apakah sudah ada pengiriman sebelumnya untuk item ini di tanggal yang sama dengan partial = 1
            $existing_partial_do = $this->db->from('delivery_orders')
                ->where([
                    'item_fg_id'     => $post['item_fg_id'],
                    'sales_order_no' => $post['sales_order_no'],
                    'delivery_date'  => $post['delivery_date'],
                    'partial'        => 1
                ])
                ->get()->row();

            $current_is_partial = $this->to_boolean($post['partial']) ? 1 : 0;

            if ($existing_partial_do && $current_is_partial === 0) {
                $errors[] = "Item {$post['item_fg_id']} must be marked as partial because there was a previous partial delivery on {$post['delivery_date']}.";
                continue;
            }

            if (@$delivery_orders->delivery_order_no != "") {
                $update_data = [
                    "remarks" => $post['remarks'],
                    "qty_del" => $post['qty_del'],
                    "partial" => $this->to_boolean($post['partial']) ? 1 : 0
                ];

                $send = $this->crud->update('delivery_orders', [
                    "delivery_order_no" => $post['delivery_order_no'],
                    "item_fg_id"        => $post['item_fg_id'],
                    "sales_order_no"    => $post['sales_order_no']
                ], $update_data);
            } else {
                // if (intVal($post['stock']) < intVal($post['qty_del'])) {
                //     $errors[] = "Qty delivery for item ID {$post['item_fg_id']} exceeds stock.";
                //     continue;
                // }
                // $send = $this->crud->create('delivery_orders', $post);

                $post['partial'] = $this->to_boolean($post['partial']) ? 1 : 0;

                if (intVal($post['stock']) < intVal($post['qty_del'])) {
                    $send = $this->crud->createDO('delivery_orders', $post);
                } else {
                    $send = $this->crud->create('delivery_orders', $post);
                }
            }

            if ($send) {
                // Ambil data sales_order_deliveries untuk trans_date saat ini
                $current_delivery = $this->crud->read("sales_order_deliveries", [], [
                    "sales_order_no"    => $post['sales_order_no'],
                    "customer_order_no" => $post['customer_order_no'],
                    "item_fg_id"        => $post['item_fg_id'],
                    "customer_id"       => $post['customer_id'],
                    "trans_date"        => $post['delivery_date']
                ]);

                if ($current_delivery) {
                    $old_qty = (int) $current_delivery->qty;
                    $new_qty = (int) $post['qty_del'];


                    $is_partial = $this->to_boolean($post['partial']) ? 1 : 0;

                    if ($is_partial === 1 && $current_delivery) {
                        // Ambil total qty_del DO untuk item & tanggal yang sama (kecuali jika update DO sendiri)
                        $this_do_no = $post['delivery_order_no'] ?? null;

                        $this_do_filter = [];
                        if ($this_do_no != "") {
                            $this_do_filter['delivery_order_no !='] = $this_do_no;
                        }

                        $existing_qty = $this->db->select_sum('qty_del')
                            ->from('delivery_orders')
                            ->where([
                                'item_fg_id'     => $post['item_fg_id'],
                                'sales_order_no' => $post['sales_order_no'],
                                'delivery_date'  => $post['delivery_date']
                            ])
                            ->where($this_do_filter)
                            ->get()->row()->qty_del ?? 0;

                        $new_total = $existing_qty + $post['qty_del'];

                        if ($new_total > $current_delivery->qty) {
                            $errors[] = "Total delivery qty for item {$post['item_fg_id']} on {$post['delivery_date']} exceeds allocation\n";
                            continue;
                        }
                    }

                    if ($new_qty > $old_qty) {
                        $selisih = $new_qty - $old_qty;

                        $sod_list = $this->db
                            ->select("a.*, b.number AS item_fg_number")
                            ->from("sales_order_deliveries a")
                            ->join("item_fg b", "b.id = a.item_fg_id", "left")
                            ->where("a.sales_order_no", $post['sales_order_no'])
                            ->where("a.item_fg_id", $post['item_fg_id'])
                            ->where("a.customer_order_no", $post['customer_order_no'])
                            ->where("a.customer_id", $post['customer_id'])
                            ->where("a.trans_date !=", $post['delivery_date'])
                            ->where("a.status", 0)
                            ->order_by("a.trans_date", "DESC")
                            ->get()
                            ->result();

                        $remaining_to_take = $selisih;

                        $item_fg_number = $post['item_fg_number'] ?? $post['item_fg_id'];

                        foreach ($sod_list as $sod_item) {
                            if ($remaining_to_take <= 0) break;

                            $available = $sod_item->qty;

                            if ($available >= $remaining_to_take) {
                                $sisa = $available - $remaining_to_take;
                                if ($sisa > 0) {
                                    $this->crud->update("sales_order_deliveries", ["id" => $sod_item->id], ["qty" => $sisa]);
                                } else {
                                    $this->crud->delete("sales_order_deliveries", ["id" => $sod_item->id]);
                                }
                                $remaining_to_take = 0;
                            } else {
                                $this->crud->delete("sales_order_deliveries", ["id" => $sod_item->id]);
                                $remaining_to_take -= $available;
                            }
                        }

                        if ($remaining_to_take > 0) {
                            $errors[] = "Warning! Cannot process delivery qty exceeds allocation";
                            continue;
                        }

                        if ($is_partial === 0) {
                            // update qty only when increased
                            $this->crud->update("sales_order_deliveries", [
                                "sales_order_no"    => $post['sales_order_no'],
                                "customer_order_no" => $post['customer_order_no'],
                                "item_fg_id"        => $post['item_fg_id'],
                                "customer_id"       => $post['customer_id'],
                                "trans_date"        => $post['delivery_date']
                            ], ["qty" => $new_qty]);
                        }

                    } elseif ($new_qty < $old_qty) {

                        // Jika TIDAK partial, maka dianggap adjust
                        $is_partial = $this->to_boolean($post['partial']) ? 1 : 0;

                        if ($is_partial === 0) {
                            $this->crud->update("sales_order_deliveries", [
                                "sales_order_no"    => $post['sales_order_no'],
                                "customer_order_no" => $post['customer_order_no'],
                                "item_fg_id"        => $post['item_fg_id'],
                                "customer_id"       => $post['customer_id'],
                                "trans_date"        => $post['delivery_date']
                            ], ["qty" => $new_qty]);

                            $total_qty_do = $this->db->select_sum('qty_del')
                                ->from('delivery_orders')
                                ->where([
                                    'item_fg_id'     => $post['item_fg_id'],
                                    'sales_order_no' => $post['sales_order_no'],
                                    'delivery_date'  => $post['delivery_date']
                                ])
                                ->get()->row()->qty_del ?? 0;

                            if ($total_qty_do < $current_delivery->qty) {
                                $this->crud->update("sales_order_deliveries", [
                                    "item_fg_id"     => $post['item_fg_id'],
                                    "sales_order_no" => $post['sales_order_no'],
                                    "trans_date"     => $post['delivery_date']
                                ], ["status" => 1]);
                            }
                        }
                    }

                    // calculate total qty_del for the date to determine if delivery is complete
                    $total_qty_do = $this->db->select_sum('qty_del')
                        ->from('delivery_orders')
                        ->where([
                            'item_fg_id'     => $post['item_fg_id'],
                            'sales_order_no' => $post['sales_order_no'],
                            'delivery_date'  => $post['delivery_date']
                        ])
                        ->get()->row()->qty_del ?? 0;

                    if ($total_qty_do >= $current_delivery->qty) {
                        $this->crud->update("sales_order_deliveries", [
                            "item_fg_id"     => $post['item_fg_id'],
                            "sales_order_no" => $post['sales_order_no'],
                            "trans_date"     => $post['delivery_date']
                        ], ["status" => 1]);
                    } else {
                        $is_partial = $this->to_boolean($post['partial']) ? 1 : 0;
                        if ($is_partial === 1) {
                            // Belum lunas, update status ke 0 (open)
                            $this->crud->update("sales_order_deliveries", [
                                "item_fg_id"     => $post['item_fg_id'],
                                "sales_order_no" => $post['sales_order_no'],
                                "trans_date"     => $post['delivery_date']
                            ], ["status" => 0]);
                        }
                    }
                }


                $success_count++;
            } else {
                $errors[] = "Failed to save item_fg_id: {$post['item_fg_id']}";
            }
        }

        // Setelah proses looping selesai
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                "title" => "Error",
                "message" => "An error occurred while processing the data. Please try again",
                "theme" => "error"
            ]);
            return;
        }

        if (!empty($errors)) {
            $this->db->trans_rollback();
            echo json_encode([
                "title" => "Failed to Save",
                "message" => implode("\n", array_unique($errors)),
                "theme" => "error"
            ]);
            return;
        }

        $this->db->trans_commit();
        echo json_encode([
            "title" => "Success",
            "message" => "$success_count items have been saved successfully",
            "theme" => "success"
        ]);
    }


    //DELETE DATA

    public function delete()
    {
        $delivery_nos = $this->input->post('delivery_order_no');

        if (!is_array($delivery_nos) || empty($delivery_nos)) {
            echo json_encode([
                "title" => "Delete Failed",
                "message" => "No delivery order selected.",
                "theme" => "error"
            ]);
            return;
        }

        $this->db->trans_begin();

        // Ambil semua data DO yang akan dihapus
        $this->db->where_in('delivery_order_no', $delivery_nos);
        $delivery_orders = $this->db->get('delivery_orders')->result();

        // Simpan daftar unik kombinasi item_fg_id, sales_order_no, delivery_date
        $sod_keys = [];

        foreach ($delivery_orders as $do) {
            $key = $do->item_fg_id . '|' . $do->sales_order_no . '|' . $do->delivery_date;
            $sod_keys[$key] = [
                'item_fg_id' => $do->item_fg_id,
                'sales_order_no' => $do->sales_order_no,
                'delivery_date' => $do->delivery_date,
            ];
        }

        // Hapus semua DO
        $this->db->where_in('delivery_order_no', $delivery_nos);
        $this->db->delete('delivery_orders');

        // Cek dan update status untuk setiap sales_order_deliveries terkait
        foreach ($sod_keys as $key => $data) {
            // Hitung total qty_del tersisa untuk kombinasi ini
            $total_qty_do = $this->db->select_sum('qty_del')
                ->from('delivery_orders')
                ->where([
                    'item_fg_id' => $data['item_fg_id'],
                    'sales_order_no' => $data['sales_order_no'],
                    'delivery_date' => $data['delivery_date']
                ])
                ->get()
                ->row()
                ->qty_del;

            $total_qty_do = $total_qty_do ?: 0;

            $sod = $this->crud->read("sales_order_deliveries", [], [
                "item_fg_id" => $data['item_fg_id'],
                "sales_order_no" => $data['sales_order_no'],
                "trans_date" => $data['delivery_date']
            ]);

            if ($sod && $total_qty_do < $sod->qty) {
                $this->crud->update("sales_order_deliveries", [
                    "item_fg_id" => $data['item_fg_id'],
                    "sales_order_no" => $data['sales_order_no'],
                    "trans_date" => $data['delivery_date']
                ], ["status" => 0]);
            }
        }

        // Commit / Rollback
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                "title" => "Delete Failed",
                "message" => "Some data failed to delete. Transaction rolled back.",
                "theme" => "error"
            ]);
        } else {
            $this->db->trans_commit();
            echo json_encode([
                "title" => "Success",
                "message" => "Selected delivery orders deleted successfully.",
                "theme" => "success"
            ]);
        }
    }



    public function print_do($delivery_order_no)

    {

        $delivery_order_no = base64_decode($delivery_order_no);



        $delivery_orders = $this->crud->reads('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);

        $delivery_order = $this->crud->read('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);



        $config = $this->db->get('config')->row();

        $config_iso = $this->db->get('config_iso')->row();

        //Config Page

        $rows = 10;

        $page = ceil(count($delivery_orders) / $rows);

        //Generate QRcode
        $cleaned_no = str_replace('/', '', $delivery_order_no);

        $this->createQrcode($cleaned_no, "assets/image/qrcode/");

        //Header Print

        $html = '<html><head><title>' . $delivery_order->delivery_order_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';

        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';

        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 8px;}#customers {border-collapse: collapse;width: 100%;font-size: 6px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';

        $html .= '@page { size: 80mm auto ; margin: 0; } @media screen {.print {display: none !important;}}@media print { body { padding: 0; margin: 0;} .noprint {display: none !important;} .print {page-break-after: always; max-width: 72mm; margin-left: 4mm; margin-right:0; margin-top: 0; margin-bottom: 0; padding: 1mm; justify-content: center;} table{max-width:72mm !important;}}</style>';

        $html .= '<body><div style="margin:20%;" class="noprint"><center>

                    <h1>Press CTRL + P for Print</h1>

                    <p>Display pages for 10 rows</p>

                    <p>Paper Size A4, Layout Landscape</p>

                    <p>Margin Default, Scale 98</p>

                </center></div><div class="print">';

        //Loop Page

        $no = 1;

        // for ($i = 0; $i < $page; $i++) {

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');

            $this->db->from('delivery_orders a');

            $this->db->join('item_fg b', 'a.item_fg_id = b.id');

            $this->db->join('customers c', 'a.customer_id = c.id');

            $this->db->where('a.delivery_order_no', $delivery_order_no);

            $this->db->order_by('b.number', 'asc');

            //$this->db->limit(10, ($i * 10));

            $records = $this->db->get()->result_array();



            $html .= '  <table style="width:100%;">

                            <tr>

                                <th style="width:3%"><img src="' . $config->favicon . '" width="20" /></th>

                                <td style="padding:1px;width:62%;">

                                    <b style="font-size:8px;">' . $config->name . '</b><br>

                                    <span style="font-size:7px;">' . $config->description . '</span><br>

                                </td>

                                <th style="text-align:right;width:35%;">

                                    <table style="width:100%; font-size:4px;">

                                        <tr>

                                            <td style="width:37%">Print Date</td>

                                            <td style="width:1%">:</td>

                                            <td style="width:62%">' . date("Y-m-d H:i") . '</td>

                                        </tr>

                                        <tr>

                                            <td style="width:39%">Print By</td>

                                            <td style="width:1%">:</td>

                                            <td style="width:60%">' . $this->session->name . '</td>

                                        </tr>

                                    </table>

                                </th>

                            </tr>

                        </table>

                        <div style=" width:100%; height:73%;">

                            <div style="padding:5px;">

                                <center>

                                    <b style="font-size:7px;">DELIVERY ORDER</b>

                                </center>

                                <div style="float:left; width:80%;">

                                    <table style="width:100%; font-size:6px; margin-bottom:10px;">

                                        <tr>

                                            <td style="width:37%;">Customer Order No</td>

                                            <td style="width:1%;">:</td>

                                            <td style="width:62%;"><b>' . @$records[0]['customer_order_no'] . '</b></td>

                                        </tr>

                                        <tr>

                                            <td style="width:37%;">Customer Name</td>

                                            <td style="width:1%;">:</td>

                                            <td style="width:62%;"><b>' . @$records[0]['customer_name'] . '</b></td>

                                        </tr>

                                        <tr>

                                            <td style="width:37%;">DO Date</td>

                                            <td style="width:1%;">:</td>

                                            <td style="width:62%;"><b>' . mdate('%d/%m/%Y', strtotime(@$delivery_order->delivery_order_date)) . '</b></td>

                                        </tr>

                                        <tr>

                                            <td style="width:37%;">Act Delivery Date</td>

                                            <td style="width:1%;">:</td>

                                            <td style="width:62%;"><b>' . mdate('%d/%m/%Y', strtotime(@$records[0]['actual_delivery_date'])) . '</b></td>

                                        </tr>

                                    </table>

                                </div>

                                <div style="float:left; width:20%; text-align:right;">

                                    <img style="margin-right:5px;" src="' . base_url('assets/image/qrcode/' . $cleaned_no. '.png') . '" width="40"/><br>

                                    <small style="font-size:6px; margin-right:5px;">' . $delivery_order->delivery_order_no . '</small><br><br>

                                </div>

                                <table id="customers" style="margin-bottom: 10px;font-size:6px;">

                                    <tr style="font-size:8px;">

                                        <th width="20">No</th>

                                        <th width="160">Product No</th>

                                        <th width="160">Product Name</th>

                                        <th width="30">Qty</th>

                                    </tr>';

            foreach ($records as $record) {

                $html .= '  <tr>

                                <td style="text-align:center">' . $no . '</td>

                                <td style="font-size:8px;">' . $record['item_fg_number'] . '</td>

                                <td>' . $record['item_fg_name'] . '</td>


                                <td style="text-align:right;font-size:8px;">' . $record['qty_del'] . '</td>

                            </tr>';

                $no++;
            }

            $html .= '</table>
                        <table style="width:100%; border: 1px solid black; font-size:6px;">
                            <tr>
                                <td style="text-align:left; vertical-align:top; padding:2px;" rowspan="4">Notes :<br/>
                                <span>'.$records[0]['remarks'].'</span>
                                </td>
                            </tr>
                        </table>';

           // if ($i + 1 != $page) {

            //    $html .= '<div style="page-break-after:always;"></div>';
           // }



            $html .= '</div></div>';



            // if (($i + 1) == $page) {

            //     $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">

            //                     <table id="customers" style="margin-top:10px;">
            //                     </table>

            //                 </div>';
            // }
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

            header("Content-Disposition: attachment; filename=delivery_orders_$format.xls");
        }



        $get = $this->input->get();

        $filter_from = @base64_decode($get['filter_from']);

        $filter_to = @base64_decode($get['filter_to']);

        $filter_customer_id = @base64_decode($get['filter_customer_id']);

        $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);

        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);

        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);

        $filter_item_fg = @base64_decode($get['filter_item_fg']);

        $filter_status = @base64_decode($get['filter_status']);



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name");

        $this->db->from('delivery_orders a');

        $this->db->join('customers b', 'a.customer_id = b.id');

        $this->db->join('item_fg c', 'a.item_fg_id = c.id');

        if ($filter_from != "" && $filter_to != "") {

            $this->db->where('a.delivery_order_date >=', $filter_from);

            $this->db->where('a.delivery_order_date <=', $filter_to);
        }

        $this->db->like('a.customer_id', $filter_customer_id);

        $this->db->like('a.delivery_order_no', $filter_delivery_order_no);

        $this->db->like('a.sales_order_no', $filter_sales_order_no);

        $this->db->like('a.item_fg_id', $filter_item_fg);

        $this->db->like('a.customer_order_no', $filter_customer_order_no);

        $this->db->like('a.status', $filter_status);

        $this->db->order_by('a.delivery_order_no', 'ASC');

        $records = $this->db->get()->result_array();



        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>

        <center>

            <div style="float: left; font-size: 12px; text-align: left;">

                <table style="width: 100%;">

                    <tr>

                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">

                            <img src="' . $config->favicon . '" width="30">

                        </td>

                        <td style="font-size: 14px; text-align: left; margin:2px;">

                            <b>' . $config->name . '</b><br>

                            <small>' . $config->description . '</small>

                        </td>

                    </tr>

                </table>

            </div>

            <div style="float: right; font-size: 12px; text-align: right;">

                Print Date ' . date("d M Y H:m:s") . ' <br>

                Print By ' . $this->session->username . '  

            </div>

            <br><br>

            <div style="float: centet; font-size: 16px; text-align: center;">

                <h3>DELIVERY ORDER</h3>

            </div>

        </center>

        

        <table id="customer_items" border="1">

            <tr>

                <th width="20">No</th>

                <th>Customer Name</th>

                <th>Delivery Order No</th>

                <th>Delivery Order Date</th>

                <th>Delivery Date</th>

                <th>Trans Type</th>

                <th>Sales Order No</th>

                <th>Customer Order No</th>

                <th>Remarks</th>

                <th>Product ID</th>

                <th>Product No</th>

                <th>Product Name</th>

                <th>Uom</th>

                <th>Qty SO</th>

                <th>Qty Remain</th>

                <th>Qty DO</th>

                <th>Qty Delivery</th>

                <th>Stock</th>

                <th>Stock Balance</th>

            </tr>';

        $no = 1;

        foreach ($records as $data) {

            $html .= '<tr>

                        <td>' . $no . '</td>

                        <td>' . $data['customer_name'] . '</td>

                        <td>' . $data['delivery_order_no'] . '</td>

                        <td>' . $data['delivery_order_date'] . '</td>

                        <td>' . $data['delivery_date'] . '</td>

                        <td>' . $data['trans_type'] . '</td>

                        <td>' . $data['sales_order_no'] . '</td>

                        <td>' . $data['customer_order_no'] . '</td>

                        <td>' . $data['remarks'] . '</td>

                        <td>' . $data['item_fg_id'] . '</td>

                        <td>' . $data['item_fg_number'] . '</td>

                        <td>' . $data['item_fg_name'] . '</td>

                        <td>' . $data['uom'] . '</td>

                        <td>' . $data['qty_so'] . '</td>

                        <td>' . $data['qty_remain'] . '</td>

                        <td>' . $data['qty_do'] . '</td>

                        <td>' . $data['qty_del'] . '</td>

                        <td>' . $data['stock'] . '</td>

                        <td>' . $data['stock_bal'] . '</td>

                    </tr>';

            $no++;
        }

        $html .= '</table></body></html>';

        echo $html;
    }
}
