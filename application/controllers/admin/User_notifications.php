<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class User_notifications extends CI_Controller
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
        $this->form_validation->set_rules('menus_id', 'Module', 'required|min_length[2]|max_length[50]|is_unique[user_notifications.menus_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('admin/user_notifications');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function getMenuModule()
    {
        $search = $this->input->post('q');

        $this->db->select("
            a.id,
            a.name,
            parent.name as parent_name
        ");

        $this->db->from('menus a');
        $this->db->join(
            'menus parent',
            'parent.id = a.menus_id',
            'inner'
        );
        $this->db->where('a.menus_id IS NOT NULL', null, false);
        $this->db->where('parent.state', 'closed');
        $this->db->where('a.state !=', 'closed');
        if (!empty($search)) {
            $this->db->group_start();
                $this->db->like('a.name', $search);
                $this->db->or_like('parent.name', $search);
            $this->db->group_end();
        }

        $this->db->order_by('parent.name', 'ASC');
        $this->db->order_by('a.name', 'ASC');

        $menus = $this->db->get()->result_array();

        echo json_encode($menus);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, 
                b.name as user_notification_name_1,
                c.name as user_notification_name_2,
                d.name as user_notification_name_3,
                e.name as user_notification_name_4,
                f.name as user_notification_name_5
                ');
            $this->db->from('user_notifications a');
            $this->db->join('users b', 'a.user_notification_1 = b.username', 'left');
            $this->db->join('users c', 'a.user_notification_2 = c.username', 'left');
            $this->db->join('users d', 'a.user_notification_3 = d.username', 'left');
            $this->db->join('users e', 'a.user_notification_4 = e.username', 'left');
            $this->db->join('users f', 'a.user_notification_5 = f.username', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "table_name") {
                        $this->db->like("a.table_name", $filter->value);
                    } elseif ($filter->field == "user_notification_name_1") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "user_notification_name_2") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "user_notification_name_3") {
                        $this->db->like("d.name", $filter->value);
                    } elseif ($filter->field == "user_notification_name_4") {
                        $this->db->like("e.name", $filter->value);
                    } elseif ($filter->field == "user_notification_name_5") {
                        $this->db->like("f.name", $filter->value);
                    }
                }
            }
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
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {

            if ($this->form_validation->run() == TRUE) {

                $post = $this->input->post();
                $menus_id = $post['menus_id'];

                $menu = $this->db->select("SUBSTRING_INDEX(link, '/', -1) as table_name")
                    ->where('id', $menus_id)
                    ->get('menus')
                    ->row();

                if ($menu) {
                    $post['table_name'] = $menu->table_name;
                }

                $post['user_notification_1'] = $post['user_notification_1'] ?: null;
                $post['user_notification_2'] = $post['user_notification_2'] ?: null;
                $post['user_notification_3'] = $post['user_notification_3'] ?: null;
                $post['user_notification_4'] = $post['user_notification_4'] ?: null;
                $post['user_notification_5'] = $post['user_notification_5'] ?: null;

                $send = $this->crud->create('user_notifications', $post);
                echo $send;

            } else {
                show_error(validation_errors());
            }

        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();

            $menus_id = $post['menus_id'];
            $menu = $this->db->select("SUBSTRING_INDEX(link, '/', -1) as table_name")
                ->where('id', $menus_id)
                ->get('menus')
                ->row();

            if ($menu) {
                $post['table_name'] = $menu->table_name;
            }

            $post['user_notification_1'] = $post['user_notification_1'] ?: null;
            $post['user_notification_2'] = $post['user_notification_2'] ?: null;
            $post['user_notification_3'] = $post['user_notification_3'] ?: null;
            $post['user_notification_4'] = $post['user_notification_4'] ?: null;
            $post['user_notification_5'] = $post['user_notification_5'] ?: null;

            $send = $this->crud->update('user_notifications', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('user_notifications', $data);
        echo $send;
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=user_notifications_$format.xls");
        }
        //CONFIG
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, 
            b.name as user_notification_name_1,
            c.name as user_notification_name_2,
            d.name as user_notification_name_3,
            e.name as user_notification_name_4,
            f.name as user_notification_name_5,
            ');
        $this->db->from('user_notifications a');
        $this->db->join('users b', 'a.user_notification_1 = b.username', 'left');
        $this->db->join('users c', 'a.user_notification_2 = c.username', 'left');
        $this->db->join('users d', 'a.user_notification_3 = d.username', 'left');
        $this->db->join('users e', 'a.user_notification_4 = e.username', 'left');
        $this->db->join('users f', 'a.user_notification_5 = f.username', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
        <div style="float: left; font-size: 12px; text-align: left;">
        <table style="width: 100%;">
        <tr>
        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
        <img src="' . $config->favicon . '" width="30">
        </td>
        <td style="font-size: 14px; text-align: left; margin:2px;">
        <b>' . $config->name . '</b><br>
        <small>MASTER User Notification</small>
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
            <th style="text-align: center;" width="20">No</th>
            <th style="text-align: center;">Module</th>
            <th style="text-align: center;">Notification <br>Level 1</th>
            <th style="text-align: center;">Notification <br>Level 2</th>
            <th style="text-align: center;">Notification <br>Level 3</th>
            <th style="text-align: center;">Notification <br>Level 4</th>
            <th style="text-align: center;">Notification <br>Level 5</th>
        </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
            <td>' . $no . '</td>
            <td>' . $data['table_name'] . '</td>
            <td>' . $data['user_notification_name_1'] . '</td>
            <td>' . $data['user_notification_name_2'] . '</td>
            <td>' . $data['user_notification_name_3'] . '</td>
            <td>' . $data['user_notification_name_4'] . '</td>
            <td>' . $data['user_notification_name_5'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
