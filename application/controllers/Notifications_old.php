<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function notificationCount()
    {
        $this->db->select('a.*, b.name as fullname, b.avatar');
        $this->db->from('notifications a');
        $this->db->join('users b', 'a.users_id_to = b.username');
        $this->db->where('a.users_id_to', $this->session->username);
        $this->db->where('a.status', 0);
        $this->db->group_by('a.name');
        $this->db->group_by('a.users_id_from');
        $this->db->group_by('a.users_id_to');
        $this->db->group_by('a.table_name');
        $this->db->order_by('a.created_date', 'DESC');
        $totalRows = $this->db->count_all_results('', false);

        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function notificationList()
    {
        $this->db->select('a.*, c.name as fullname, c.avatar');
        $this->db->from('notifications a');
        $this->db->join('users b', 'a.users_id_to = b.username');
        $this->db->join('users c', 'a.users_id_from = c.username');
        $this->db->where('a.users_id_to', $this->session->username);
        $this->db->where('a.status', 0);
        $this->db->group_by('a.name');
        $this->db->group_by('a.users_id_from');
        $this->db->group_by('a.users_id_to');
        $this->db->group_by('a.table_name');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_object();

        if (count($records) > 0) {
            foreach ($records as $record) {
                if ($record->avatar == "") {
                    $avatar = base_url('assets/image/users/default.png');
                } else {
                    $avatar = $record->avatar;
                }
                // $tablenya = ($record->table_name=='purchase_orders_2')?'purchase_orders':$record->table_name;

                // $link = "notificationDetail('" . $record->table_name . "')";
                $link = "notificationDetail('$record->users_id_from','$record->table_name','$record->name')";
                echo '<li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . $record->fullname . '</b><br>
                                    <small>' . $record->description . '</small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
            }
        } else {
            echo '  <div class="alert alert-info" role="alert">
                        Notification Not Found
                    </div>';
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->db->delete('notifications', $data);
        echo $send;
    }

    public function purchase_orders($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_orders";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/purchase_orders');
        }
    }
    

    public function purchase_orders_2($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_orders_2";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/purchase_orders_2');
        }
    }

    public function purchase_requests($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_requests";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/purchase_requests');
        }
    }

    public function supplier_items($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "supplier_items";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/supplier_items');
        }
    }

    public function suppliers($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "suppliers";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/suppliers');
        }
    }

    public function forecasts($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "forecasts";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/forecasts');
        }
    }

    public function users($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "users";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/users');
        }
    }

    public function os_mpp($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "os_mpp";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/os_mpp');
        }
    }

    public function os_so($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "os_so";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/os_so');
        }
    }

    public function stock_wip($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "stock_wip";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/stock_wip');
        }
    }

    public function stock_fg($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "stock_fg";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/stock_fg');
        }
    }

    public function supply_materials($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "supply_materials";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/supply_materials');
        }
    }

    public function supplier_items_2($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "supplier_items_2";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/supplier_items_2');
        }
    }

    public function delivery_notes($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_notes";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_notes');
        }
    }

    public function delivery_orders($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_orders";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_orders');
        }
    }
    
    public function notification_data($table = "", $user = "", $name = "")
    {
        $user = base64_decode($user);
        $name = base64_decode($name);
        if($table=='purchase_orders'){
            $this->db->select('a.id,a.po_no, a.request_no, a.total_dp, a.delivery_date as eta,
                    a.po_date, a.remarks, b.number as item_number, b.name as item_name,
                    c.name as item_family_name, d.name as supplier_name, d.currency, e.mpq, e.moq, b.uom, a.month_1, a.month_2,
                    a.month_3,a.discount, a.qty, a.price, a.total, a.total_sub, h.id as id_notification');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
            $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }
        if($table=='purchase_orders_2'){
            $this->db->select('a.id,a.po_no, a.request_no, a.total_dp, a.delivery_date as eta,
                    a.po_date, a.remarks, b.number as item_number, b.name as item_name,
                    c.name as item_family_name, d.name as supplier_name, d.currency, e.mpq, e.moq, b.uom, a.month_1, a.month_2,
                    a.month_3,a.discount, a.qty, a.price, a.total, a.total_sub, h.id as id_notification');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
            $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }
        
        if($table=='purchase_requests'){
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom, d.po_no, c.name as category_name, h.id as id_notification');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('purchase_orders d', 'a.request_no = d.request_no and a.item_rm_id = d.item_rm_id', 'left');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='forecasts'){
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, h.id as id_notification');
            $this->db->from('forecasts a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }
        
        if($table=='users'){
            $this->db->select('a.*, h.id as id_notification');
            $this->db->from('users a');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='stock_wip'){
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, h.id as id_notification');
            $this->db->from('stock_wip a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='stock_fg'){
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, h.id as id_notification');
            $this->db->from('stock_fg a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='os_so'){
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, h.id as id_notification');
            $this->db->from('os_so a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='os_mpp'){
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, h.id as id_notification');
            $this->db->from('os_mpp a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }
        if($table=='suppliers'){
            $this->db->select('a.*, h.id as id_notification');
            $this->db->from('suppliers a');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='supplier_items'){
            $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, b.currency, b.type, b.status, h.id as id_notification');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('supplier_item_histories c', 'a.id = c.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }
        if($table=='supplier_items_2'){
            $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, b.currency, b.type, b.status, h.id as id_notification');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('supplier_item_histories c', 'a.id = c.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='supply_materials'){
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom, h.id as id_notification');
            $this->db->from('supply_materials a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->order_by('h.created_date', 'DESC');
        }

        if($table=='delivery_notes'){
            $this->db->select('a.delivery_note_no, a.delivery_note_date, c.name as customer_name, 
                              d.name as created_by_name, e.name as approved_by_name, h.id as id_notification');
            $this->db->from('delivery_notes a');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('users d', 'a.created_by = d.username', 'left');
            $this->db->join('users e', 'a.approved_by = e.username', 'left'); 
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->group_by('a.delivery_note_no');
            $this->db->order_by('h.created_date', 'DESC');
        }
        if($table=='delivery_orders'){
            $this->db->select('a.*, c.name as customer_name, h.id as id_notification');
            $this->db->from('delivery_orders a');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->group_by('a.delivery_order_no');
            $this->db->order_by('h.created_date', 'DESC');
        }
            $records = $this->db->get()->result_array();
            $this->crud->update('notifications', ["users_id_to" => $this->session->username, "table_name" => $table, "users_id_from" => $user, "name" => $name, "status" => 0], ["status" => 1]);
            echo json_encode($records);
    }
}
