<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Forecasts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // Validasi Form
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('dashboard/forecasts');
        } else {
            redirect('error_access');
        }
    }

    // GET Period (month/year)
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

    // Mapping bulan
    private $months = [
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December'
    ];

    public function chartByQty()
    {
        $filter_year = base64_decode($this->input->post('filter_period_year'));
        $filter_months = explode(",", base64_decode($this->input->post('filter_period_month')));
        $filter_customer = $this->input->post('filter_customer_name');
        $filter_item_fg = $this->input->post('filter_item_fg');

        // Inisialisasi formatted_data dan kategori bulan
        $formatted_data = [];
        $categories = [];
        foreach ($filter_months as $month) {
            $categories[] = $this->months[$month];
        }

        // Query data untuk total qty per bulan tanpa memisahkan item_fg
        $this->db->select('f.p_month, SUM(f.month_1) as total_qty, c.name as customer_name, i.number as item_number');
        $this->db->from('forecasts f');
        $this->db->join('customers c', 'c.id = f.customer_id', 'left');
        $this->db->join('item_fg i', 'i.id = f.item_fg_id', 'left');
        $this->db->where('f.p_year', $filter_year);
        $this->db->where_in('f.p_month', $filter_months);
        $this->db->where('f.customer_id', $filter_customer);

        // Kondisi tambahan untuk filter item_fg
        if (!empty($filter_item_fg)) {
            $this->db->where("(f.item_fg_id = '$filter_item_fg' OR f.item_fg_id IS NULL OR f.item_fg_id = '')");
        }

        $this->db->group_by('f.p_month'); // Mengelompokkan hanya berdasarkan bulan
        $query = $this->db->get();
        $result = $query->result();

        $total_data = array_fill(0, count($filter_months), 0); // Inisialisasi total qty per bulan
        $customer_name = ''; // Untuk menyimpan nama customer

        // Tentukan item label berdasarkan filter_item_fg
        $item_label = !empty($filter_item_fg) ? $filter_item_fg : 'Total Product No';

        // Proses hasil query untuk mengisi total qty per bulan
        if (!empty($result)) {
            foreach ($result as $row) {
                $customer_name = $row->customer_name; // Mengambil nama customer

                $month_index = array_search($this->months[$row->p_month], $categories);
                if ($month_index !== false) {
                    $total_data[$month_index] = (float)$row->total_qty; // Memasukkan total qty per bulan
                }
            }
        }

        // Format data untuk grafik
        $formatted_data[] = [
            'name' => $item_label, // Gunakan item_label yang ditentukan
            'data' => $total_data
        ];

        // Kirim data sebagai JSON untuk grafik
        echo json_encode([
            'categories' => $categories,
            'data' => $formatted_data,
            'item_number' => $filter_item_fg,
            'filter_display_by' => 'qty',
            'customer_name' => $customer_name ?: '' // Menampilkan nama customer jika ada data
        ]);
    }


    public function chartByAmount()
    {
        // Retrieve data from POST request
        $filter_year = base64_decode($this->input->post('filter_period_year'));
        $filter_months = explode(",", base64_decode($this->input->post('filter_period_month')));
        $filter_customer = $this->input->post('filter_customer_name');
        $filter_item_fg = $this->input->post('filter_item_fg');

        // Initialize categories and data structures
        $categories = [];
        $formatted_data = [];
        $product_data = []; // Ensure this array is initialized

        foreach ($filter_months as $month) {
            $categories[] = $this->months[$month];
        }

        // Build SQL query with condition for filter_item_fg
        if (empty($filter_item_fg)) {
            // Jika filter_item_fg kosong, jumlahkan semua amount per bulan
            $sql = "SELECT 
                    f.p_month,
                    COALESCE(SUM(f.month_1 * cih.price), 0) AS total_amount,
                    c.name as customer_name
                FROM 
                    forecasts f
                JOIN 
                    customer_item_histories cih ON cih.customer_id = f.customer_id AND cih.item_fg_id = f.item_fg_id
                JOIN
                    customers c ON c.id = f.customer_id
                WHERE 
                    f.p_year = ? AND
                    f.p_month IN ? AND
                    f.customer_id = ? AND
                    cih.valid_from <= LAST_DAY(STR_TO_DATE(CONCAT(f.p_year, '-', f.p_month, '-01'), '%Y-%m-%d')) AND
                    cih.valid_to >= STR_TO_DATE(CONCAT(f.p_year, '-', f.p_month, '-01'), '%Y-%m-%d')
                GROUP BY 
                    f.p_month
                ORDER BY 
                    f.p_month";

            $query = $this->db->query($sql, [
                $filter_year,
                $filter_months,
                $filter_customer
            ]);
        } else {
            // Jika filter_item_fg tidak kosong, filter berdasarkan item_fg_id
            $sql = "SELECT 
                    f.p_month,
                    COALESCE(SUM(f.month_1 * cih.price), 0) AS total_amount,
                    i.number as item_number,
                    c.name as customer_name
                FROM 
                    forecasts f
                JOIN 
                    customer_item_histories cih ON cih.customer_id = f.customer_id AND cih.item_fg_id = f.item_fg_id
                JOIN
                    item_fg i ON i.id = f.item_fg_id
                JOIN
                    customers c ON c.id = f.customer_id
                WHERE 
                    f.p_year = ? AND
                    f.p_month IN ? AND
                    f.customer_id = ? AND
                    f.item_fg_id = ? AND
                    cih.valid_from <= LAST_DAY(STR_TO_DATE(CONCAT(f.p_year, '-', f.p_month, '-01'), '%Y-%m-%d')) AND
                    cih.valid_to >= STR_TO_DATE(CONCAT(f.p_year, '-', f.p_month, '-01'), '%Y-%m-%d')
                GROUP BY 
                    f.p_month
                ORDER BY 
                    f.p_month";

            $query = $this->db->query($sql, [
                $filter_year,
                $filter_months,
                $filter_customer,
                $filter_item_fg
            ]);
        }

        $result = $query->result();

        if (empty($result)) {
            // If no data is returned, set all values to zero
            $formatted_data[] = [
                'name' => empty($filter_item_fg) ? 'Total Product No' : 'item_number',
                'data' => array_fill(0, count($filter_months), 0)
            ];
        } else {
            // Organize data per item and per month
            if (empty($filter_item_fg)) {
                // Jika tidak ada filter_item_fg, gabungkan semua jumlah sebagai total_amount per bulan
                $total_data = array_fill(0, count($filter_months), 0);
                foreach ($result as $row) {
                    $month_index = array_search($this->months[$row->p_month], $categories);
                    $total_data[$month_index] = (float)$row->total_amount;
                }
                $formatted_data[] = [
                    'name' => 'Total Product No',
                    'data' => $total_data
                ];
            } else {
                foreach ($result as $row) {
                    if (!isset($product_data[$row->item_number])) {
                        $product_data[$row->item_number] = array_fill(0, count($filter_months), 0);
                    }
                    $month_index = array_search($this->months[$row->p_month], $categories);
                    $product_data[$row->item_number][$month_index] = (float)$row->total_amount;
                }

                foreach ($product_data as $item_number => $data) {
                    $formatted_data[] = [
                        'name' => $item_number,
                        'data' => $data
                    ];
                }
            }
        }

        // Output JSON for the chart, with a check for available customer data
        echo json_encode([
            'categories' => $categories,
            'data' => $formatted_data,
            'filter_display_by' => 'amount',
            'customer_name' => (!empty($result) && isset($result[0]->customer_name)) ? $result[0]->customer_name : ''  // Use the first matched customer name if data exists
        ]);
    }
}
