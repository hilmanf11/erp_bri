<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Departments extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'required|min_length[1]|max_length[20]|is_unique[departments.supplier_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[departments.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/departments');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $q = $this->input->post('q');
        $plant_id = $this->input->post('plant_id');

        $this->db->select('id, name');
        $this->db->from('departments');

        if (!empty($plant_id)) {
            $this->db->where('plant_id', $plant_id);
        }

        if (!empty($q)) {
            $this->db->group_start();
            $this->db->like('id', $q);
            $this->db->or_like('name', $q);
            $this->db->group_end();
        }

        $this->db->order_by('id', 'ASC');

        echo json_encode($this->db->get()->result());
    }

    public function autoid()
    {
        $sql = $this->db->query("SELECT MAX(id) AS kode FROM departments");
        $row = $sql->row();

        if (empty($row->kode)) {
            $seq = 1;
        } else {
            $seq = (int) substr($row->kode, 5) + 1;
        }

        echo 'DEPT-' . sprintf('%04d', $seq);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_plant = @base64_decode($get['filter_plant']);
            $filter_department = @base64_decode($get['filter_department']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select('
                a.id as dept_id,
                a.name,
                a.description,
                a.created_by,
                a.created_date,
                a.updated_by,
                a.updated_date,
                b.id as plant,
                b.name as plant_name
            ');
            $this->db->from('departments a');
            $this->db->join('divisions b', 'a.plant_id = b.id');

            if (!empty($filter_plant)) {
                $this->db->where('a.plant_id', $filter_plant);
            }
            if (!empty($filter_department)) {
                $this->db->where('a.id', $filter_department);
            }
            $this->db->order_by('a.id', 'ASC');

            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);

            $records = $this->db->get()->result_array();
            $result['total'] = $totalRows;

            $result = array_merge($result, ['rows' => $records]);

            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $departments = $this->crud->read("departments", [], ["id" => $post['id']]);

            $post['name'] = strtoupper(trim($post['name']));
            $post['description'] = strtoupper(trim($post['description']));

            if (@$departments->id != "") {
                $send = $this->crud->update('departments', ["id" => $post['id']], $post);
            } else {
                $send = $this->crud->create('departments', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $id = $this->input->post('id');
        if ($id) {
            $send = $this->crud->delete('departments', ['id' => $id]);
            echo $send;
        } else {
            echo json_encode(['error' => 'ID is required']);
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=departments_$format.xls");
        }

        $get = $this->input->get();
        $filter_plant = @base64_decode($get['filter_plant']);
        $filter_department = @base64_decode($get['filter_department']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
    
        $this->db->select('
            a.id as dept_id,
            a.name,
            a.description,
            a.created_by,
            a.created_date,
            a.updated_by,
            a.updated_date,
            b.id as plant,
            b.name as plant_name
        ');
        $this->db->from('departments a');
        $this->db->join('divisions b', 'a.plant_id = b.id');

        if (!empty($filter_plant)) {
            $this->db->where('a.plant_id', $filter_plant);
        }
        if (!empty($filter_department)) {
            $this->db->where('a.id', $filter_department);
        }
        $this->db->order_by('a.id', 'ASC');

        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#departments {border-collapse: collapse;width: 100%;font-size: 12px;}#departments td, #departments th {border: 1px solid #ddd;padding: 2px;}#departments tr:nth-child(even){background-color: #f2f2f2;}#departments tr:hover {background-color: #ddd;}#departments th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
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
                <h3>MASTER SUPPLIER ITEM</h3>
            </div>
        </center>
        
        <table id="departments" border="1">
            <tr>
                <th width="20">No</th>
                <th>Dept ID</th>
                <th>Plant</th>
                <th>Name</th>
                <th>Description</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['dept_id'] . '</td>
                    <td>' . $data['plant_name'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['description'] . '</td></tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
