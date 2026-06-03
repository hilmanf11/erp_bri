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
        $this->load->model('Notification_model');
    }

    public function notificationCount()
    {
        $username = $this->session->username;

        // Normal notification
        $this->db->select('a.id');
        $this->db->from('notifications a');
        $this->db->where('a.users_id_to', $username);
        $this->db->where('a.status', 0);

        $this->db->group_by('a.name');
        $this->db->group_by('a.users_id_from');
        $this->db->group_by('a.users_id_to');
        $this->db->group_by('a.table_name');

        $notifCount = $this->db->count_all_results();

        // Delivery To Subcont
        $deliveryToSubcontCount = 0;
        $configSubcont = $this->Notification_model->getNotificationConfig('delivery_to_subconts');
        $allowedSubcontUsers = $this->Notification_model->getNotificationUsers($configSubcont);

        // if (in_array($username, ['scmbri01', 'asmbri', 'pmbri'])) {

        //     $this->getDeliveryToSubcontNotifQuery();

        //     if ($username == 'pmbri') {
        //         $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
        //     }

        //     $deliveryToSubcontCount = $this->db->count_all_results();
        // }

        if (in_array($username, $allowedSubcontUsers)) {
            $levelSubcont = $this->Notification_model->getNotificationLevel($configSubcont, $username);

            $this->getDeliveryToSubcontNotifQuery();

            if ($levelSubcont >= 3) {
                $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
            }

            $deliveryToSubcontCount = $this->db->count_all_results();
        }

        // Delivery Rework
        $deliveryReworkCount = 0;
        $configRework = $this->Notification_model->getNotificationConfig('delivery_rework');
        $allowedReworkUsers = $this->Notification_model->getNotificationUsers($configRework);

        // if (in_array($username, ['scmbri01', 'asmbri', 'pmbri'])) {

        //     $this->getDeliveryReworkNotifQuery();

        //     if ($username == 'pmbri') {
        //         $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
        //     }

        //     $deliveryReworkCount = $this->db->count_all_results();
        // }

        if (in_array($username, $allowedReworkUsers)) {
            $levelRework = $this->Notification_model->getNotificationLevel($configRework, $username);
            $this->getDeliveryReworkNotifQuery();

            if ($levelRework >= 3) {
                $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
            }

            $deliveryReworkCount = $this->db->count_all_results();
        }

        $totalNotif = $notifCount + $deliveryToSubcontCount + $deliveryReworkCount;

        if ($totalNotif > 0) {
            echo '<span class="badge">' . $totalNotif . '</span>';
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

        $records = $this->db->get()->result_array();

        // echo json_encode($records);

        $html = '';

        foreach ($records as $row) {

            $avatar = '';
            if (empty($row['avatar'])) {

                $avatar = base_url('assets/image/users/default.png');
            } else {

                $avatar = $row['avatar'];
            }

            $link = "notificationDetail(
                '{$row['users_id_from']}',
                '{$row['table_name']}',
                '{$row['name']}'
            )";

            $notifClass = 'notification-info';

            $html .= '
                <li class="notification-item ' . $notifClass . '">
                    <a class="notification-link"
                    onclick="' . $link . '">

                        <div class="notification-icon">
                            <img src="' . $avatar . '" style="width: 29px !important;" />
                        </div>

                        <div class="notification-content">
                            <div class="notification-title">
                                ' . $row['fullname'] . '
                            </div>

                            <div class="notification-message">
                                ' . $row['description'] . '
                            </div>
                        </div>

                    </a>
                </li>
            ';
        }

        echo $html;

    }

    private function getDeliveryToSubcontNotifQuery()
    {
        $this->db->select("
            DATE_FORMAT(a.target_date, '%Y-%m') as target_month,

            CASE
                WHEN a.target_date <= CURDATE() THEN 'overdue'
                ELSE 'reminder'
            END as notif_type
        ");

        $this->db->from('delivery_to_subconts a');
        $this->db->join(
            "
            (
                SELECT
                    delivery_note_no,
                    item_fg_id,
                    workorder,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Regular'
                AND type_status = 'completed'
                GROUP BY delivery_note_no, item_fg_id, workorder
            ) i",
            "
                i.delivery_note_no = a.delivery_note_no
                AND i.item_fg_id = a.item_fg_id
                AND i.workorder = a.workorder
            ",
            'left'
        );

        $this->db->where('DATEDIFF(a.target_date, CURDATE()) <= 2', null, false);
        $this->db->where('(a.qty_delivery - COALESCE(i.qty_incoming, 0)) >', 0);

        $this->db->group_by([
            "DATE_FORMAT(a.target_date, '%Y-%m')",
            "notif_type"
        ]);
    }

    public function deliveryToSubcontNotif()
    {
        $username = $this->session->username;
        // $allowedUsers = ['scmbri01', 'asmbri', 'pmbri'];

        // if (!in_array($username, $allowedUsers)) {
        //     return;
        // }

        $config = $this->Notification_model->getNotificationConfig('delivery_to_subconts');
        $allowedUsers = $this->Notification_model->getNotificationUsers($config);

        if (!in_array($username, $allowedUsers)) {
            return;
        }

        $level = $this->Notification_model->getNotificationLevel($config, $username);

        $this->db->select("
            DATE_FORMAT(a.target_date, '%Y-%m') as target_month,
            DATE_FORMAT(a.target_date, '%M %Y') as target_month_name,

            CASE
                WHEN a.target_date <= CURDATE() THEN 'overdue'
                ELSE 'reminder'
            END as notif_type,

            COUNT(DISTINCT a.delivery_note_no) as total_dn,
            COUNT(a.id) as total_product,
            MIN(a.target_date) as min_target_date,
            MAX(a.target_date) as max_target_date
        ");

        $this->db->from('delivery_to_subconts a');

        $this->db->join(
            "
            (
                SELECT
                    delivery_note_no,
                    item_fg_id,
                    workorder,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Regular'
                AND type_status = 'completed'
                GROUP BY delivery_note_no, item_fg_id, workorder
            ) i",
            "
                i.delivery_note_no = a.delivery_note_no
                AND i.item_fg_id = a.item_fg_id
                AND i.workorder = a.workorder
            ",
            'left'
        );

        $this->db->where('DATEDIFF(a.target_date, CURDATE()) <= 2', null, false);
        $this->db->where('(a.qty_delivery - COALESCE(i.qty_incoming, 0)) >', 0);

        // if ($username == 'pmbri') {
        if($level >= 3) {
            $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
        }

        $this->db->group_by([
            "DATE_FORMAT(a.target_date, '%Y-%m')",
            "notif_type"
        ]);

        $this->db->order_by('min_target_date', 'ASC');
        $result = $this->db->get()->result_array();

        // echo json_encode($result);

        $html = '';
        foreach ($result as $row) {

            $notifClass = '';
            $notifIcon = '';
            $title = '';
            $message = '';

            $link = "notificationDetail(
                'System',
                'delivery_to_subconts_notif',
                '{$row['target_month']}',
                'Delivery to Subconts',
                'subcont_{$row['notif_type']}'
            )";

            if ($row['notif_type'] == 'overdue') {

                $notifClass = 'notification-overdue';
                $notifIcon = '!';
                $title = 'Overdue Delivery to Subcont';

                $message = $row['total_dn'] . " delivery notes are overdue as of " . $row['target_month_name'] . ".";
            } else {

                $notifClass = 'notification-reminder';
                $notifIcon = '⏰';
                $title = 'Reminder Delivery to Subcont';

                $message = $row['total_dn'] . " delivery notes are approaching their deadlines in " . $row['target_month_name'] . ".";
            }

            $html .= '
                <li class="notification-item ' . $notifClass . '">
                    <a class="notification-link" onclick="' . $link . '">
                        <div class="notification-icon">
                            ' . $notifIcon . '
                        </div>

                        <div class="notification-content">
                            <div class="notification-title">
                                ' . $title . '
                            </div>

                            <div class="notification-message">
                                ' . $message . '
                            </div>

                            <div class="notification-product">
                                <span>Total Qty : ' . $row['total_product'] . ' items</span>
                            </div>
                        </div>
                    </a>
                </li>
            ';
        }

        echo $html;

    }

    private function getDeliveryReworkNotifQuery()
    {
        $this->db->select("
            DATE_FORMAT(a.target_date, '%Y-%m') as target_month,

            CASE
                WHEN a.target_date <= CURDATE() THEN 'overdue'
                ELSE 'reminder'
            END as notif_type
        ");

        $this->db->from('delivery_rework a');

        $this->db->join(
            "(
                SELECT
                    dnr_no,
                    item_fg_id,
                    workorder,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Rework'
                AND type_status = 'completed'
                GROUP BY dnr_no, item_fg_id, workorder
            ) i",
            "
                i.dnr_no = a.dnr_no
                AND i.item_fg_id = a.item_fg_id
                AND i.workorder = a.workorder
            ",
            'left'
        );

        $this->db->where('DATEDIFF(a.target_date, CURDATE()) <= 1', null, false);

        $this->db->where('(a.qty_delivery - COALESCE(i.qty_incoming, 0)) >', 0);

        $this->db->group_by([
            "DATE_FORMAT(a.target_date, '%Y-%m')",
            "notif_type"
        ]);
    }

    public function deliveryReworkNotif()
    {
        $username = $this->session->username;
        // $allowedUsers = ['scmbri01', 'asmbri', 'pmbri'];

        // if (!in_array($username, $allowedUsers)) {
        //     return;
        // }

        $config = $this->Notification_model->getNotificationConfig('delivery_rework');
        $allowedUsers = $this->Notification_model->getNotificationUsers($config);

        if (!in_array($username, $allowedUsers)) {
            return;
        }

        $level = $this->Notification_model->getNotificationLevel($config, $username);

        $this->db->select("
            DATE_FORMAT(a.target_date, '%Y-%m') as target_month,
            DATE_FORMAT(a.target_date, '%M %Y') as target_month_name,

            CASE
                WHEN a.target_date <= CURDATE() THEN 'overdue'
                ELSE 'reminder'
            END as notif_type,

            COUNT(DISTINCT a.dnr_no) as total_dnr,
            COUNT(a.id) as total_product,

            MIN(a.target_date) as min_target_date,
            MAX(a.target_date) as max_target_date,

            SUM(a.qty_delivery - COALESCE(i.qty_incoming, 0)) as outstanding_qty
        ");

        $this->db->from('delivery_rework a');

        $this->db->join('subconts b', 'a.destination = b.number', 'left');
        $this->db->join('teaching_factory c', 'a.destination = c.number', 'left');

        $this->db->join(
            "(
                SELECT
                    dnr_no,
                    item_fg_id,
                    workorder,
                    SUM(qty) AS qty_incoming
                FROM scan_incoming_sctf
                WHERE incoming_type = 'Rework'
                AND type_status = 'completed'
                GROUP BY dnr_no, item_fg_id, workorder
            ) i",
            "
                i.dnr_no = a.dnr_no
                AND i.item_fg_id = a.item_fg_id
                AND i.workorder = a.workorder
            ",
            'left'
        );

        $this->db->where('DATEDIFF(a.target_date, CURDATE()) <= 1', null, false);

        $this->db->where('(a.qty_delivery - COALESCE(i.qty_incoming, 0)) >', 0);

        // if ($username == 'pmbri') {
        if($level >= 3) {
            $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
        }

        $this->db->group_by([
            "DATE_FORMAT(a.target_date, '%Y-%m')",
            "notif_type"
        ]);

        $this->db->order_by('min_target_date', 'ASC');
        $deliveryReworkNotif = $this->db->get()->result_array();

        // echo json_encode($deliveryReworkNotif);

        $html = '';

        foreach ($deliveryReworkNotif as $row) {

            $notifClass = '';
            $notifIcon = '';
            $title = '';
            $message = '';

            $link = "notificationDetail(
                'System',
                'delivery_rework_notif',
                '{$row['target_month']}',
                'Delivery Rework',
                'rework_{$row['notif_type']}'
            )";

            if ($row['notif_type'] == 'overdue') {

                $notifClass = 'notification-overdue';
                $notifIcon = '!';
                $title = 'Overdue Delivery Rework';

                $message =
                    $row['total_dnr'] . " delivery notes are overdue as of " . $row['target_month_name'] . ".";

            } else {

                $notifClass = 'notification-reminder';
                $notifIcon = '⏰';
                $title = 'Reminder Delivery Rework';

                $message = $row['total_dnr'] . " delivery notes are approaching their deadlines in " . $row['target_month_name'] . ".";
            }

            $html .= '
                <li class="notification-item ' . $notifClass . '">
                    <a class="notification-link" onclick="' . $link . '">
                        <div class="notification-icon">
                            ' . $notifIcon . '
                        </div>

                        <div class="notification-content">

                            <div class="notification-title">
                                ' . $title . '
                            </div>

                            <div class="notification-message">
                                ' . $message . '
                            </div>

                            <div class="notification-product">
                                <span>Total Qty : ' . $row['total_product'] . ' items</span>
                            </div>
                        </div>
                    </a>
                </li>
            ';
        }

        echo $html;
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

    public function supply_sheets($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "supply_sheets";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/supply_sheets');
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
    public function delivery_notes_2($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_notes_2";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_notes_2');
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
    public function delivery_to_subconts($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_to_subconts";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_to_subconts');
        }
    }
    public function delivery_to_subconts_notif($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_to_subconts";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_to_subconts_notif');
        }
    }
    public function delivery_rework($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_rework";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_rework');
        }
    }
    public function delivery_rework_notif($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "delivery_rework";
            
            $this->load->view('template/header', $data);
            $this->load->view('notifications/delivery_rework_notif');
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

        if($table=='supply_sheets'){
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, 
                              c.number as item_rm_number, c.name as item_rm_name,
                              d.name as uom, e.period, e.wp, h.id as id_notification,
                              COALESCE(i.qty_issued, 0) as qty_issued,
                              (COALESCE(i.qty_issued, 0) - a.qty_req) as qty_issued_bal,
                              CASE 
                                  WHEN (COALESCE(i.qty_issued, 0) - a.qty_req) < 0 THEN "OPEN"
                                  ELSE "CLOSE"
                              END as supply_type');
            $this->db->from('supply_sheets a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('uom d', 'c.uom = d.name');
            $this->db->join('production_schedules e', 'a.item_fg_id = e.item_fg_id and a.workorder = e.workorder');
            $this->db->join('notifications h', 'a.id = h.table_id');
            $this->db->join("(SELECT request_no, item_rm_id, SUM(qty) as qty_issued FROM issued_material_details GROUP BY request_no, item_rm_id) i", "i.request_no = a.request_no and i.item_rm_id = c.id", "LEFT");
            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->group_by('a.id, a.request_no, a.item_fg_id, a.item_rm_id, a.workorder, a.qty_req, b.number, b.name, c.number, c.name, d.name, e.period, e.wp, h.id, i.qty_issued');
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
        if($table=='delivery_notes_2'){
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

        if($table=='delivery_to_subconts'){
            $this->db->select('
                a.delivery_note_no, 
                a.delivery_date, 
                d.name as created_by_name, 
                e.name as approved_by_name, 
                h.id as id_notification,
                COALESCE(sc.name, tf.name) as destination_name
            ');
            $this->db->from('delivery_to_subconts a');
            $this->db->join('users d', 'a.created_by = d.username', 'left');
            $this->db->join('users e', 'a.approved_by = e.username', 'left'); 
            $this->db->join('notifications h', 'a.id = h.table_id');

            $this->db->join('subconts sc', 'a.destination = sc.id', 'left');
            $this->db->join('teaching_factory tf', 'a.destination = tf.id', 'left');

            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->group_by('a.delivery_note_no');
            $this->db->order_by('h.created_date', 'DESC');
        }

        if ($table == 'delivery_to_subconts_notif') {

            $username = $this->session->username;
            // $allowedUsers = ['scmbri01', 'asmbri', 'pmbri'];

            // if (!in_array($username, $allowedUsers)) {
            //     return;
            // }

            $config = $this->Notification_model->getNotificationConfig('delivery_to_subconts');
            $allowedUsers = $this->Notification_model->getNotificationUsers($config);

            if (!in_array($username, $allowedUsers)) {
                return;
            }

            $level = $this->Notification_model->getNotificationLevel($config, $username);

            $this->db->select('
                a.delivery_note_no,
                a.delivery_date,
                a.target_date,
                a.qty_delivery,
                a.item_fg_id,
                a.prod_date,
                d.number as item_fg_number,
                d.name as item_fg_name,
                d.uom,
                COALESCE(b.name, c.name) as destination_name,
                COALESCE(i.qty_incoming, 0) AS qty_incoming,
                (a.qty_delivery - COALESCE(i.qty_incoming, 0)) as qty_outstanding
            ');

            $this->db->from('delivery_to_subconts a');
            $this->db->join('subconts b', 'a.destination = b.id', 'left');
            $this->db->join('teaching_factory c', 'a.destination = c.id', 'left');
            $this->db->join('item_fg d', 'd.id = a.item_fg_id');

            $this->db->join(
                "(
                    SELECT
                        delivery_note_no,
                        item_fg_id,
                        workorder,
                        SUM(qty) AS qty_incoming
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Regular'
                    AND type_status = 'completed'
                    GROUP BY delivery_note_no, item_fg_id, workorder
                ) i",
                "
                    i.delivery_note_no = a.delivery_note_no
                    AND i.item_fg_id = a.item_fg_id
                    AND i.workorder = a.workorder
                ",
                'left'
            );

            $this->db->where('(a.qty_delivery - COALESCE(i.qty_incoming, 0)) >', 0);

            if ($name == 'subcont_overdue') {
                $this->db->where('a.target_date <= CURDATE()', null, false);

            } elseif ($name == 'subcont_reminder') {
                $this->db->where('a.target_date > CURDATE()', null, false);
                $this->db->where('DATEDIFF(a.target_date, CURDATE()) <= 2', null, false);
            }

            // if ($username == 'pmbri') {
            if($level >= 3) {
                $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
            }

            $this->db->order_by('a.target_date', 'ASC');
            $this->db->order_by('a.delivery_note_no', 'ASC');
        }

        if($table=='delivery_rework'){
            $this->db->select('
                a.dnr_no, 
                a.delivery_note_no, 
                a.delivery_date, 
                d.name as created_by_name, 
                e.name as approved_by_name, 
                h.id as id_notification,
                COALESCE(sc.name, tf.name) as destination_name
            ');
            $this->db->from('delivery_rework a');
            $this->db->join('users d', 'a.created_by = d.username', 'left');
            $this->db->join('users e', 'a.approved_by = e.username', 'left'); 
            $this->db->join('notifications h', 'a.id = h.table_id');

            $this->db->join('subconts sc', 'a.destination = sc.number', 'left');
            $this->db->join('teaching_factory tf', 'a.destination = tf.number', 'left');

            $this->db->where('h.users_id_to', $this->session->username);
            $this->db->where('h.table_name', $table);
            $this->db->where('h.users_id_from', $user);
            $this->db->where('h.name', $name);
            $this->db->where('h.deleted', 0);
            $this->db->group_by('a.dnr_no');
            $this->db->order_by('h.created_date', 'DESC');
        }

        if ($table == 'delivery_rework_notif') {

            $username = $this->session->username;
            // $allowedUsers = ['scmbri01', 'asmbri', 'pmbri'];

            // if (!in_array($username, $allowedUsers)) {
            //     return;
            // }

            $config = $this->Notification_model->getNotificationConfig('delivery_rework');
            $allowedUsers = $this->Notification_model->getNotificationUsers($config);

            if (!in_array($username, $allowedUsers)) {
                return;
            }

            $level = $this->Notification_model->getNotificationLevel($config, $username);


            $this->db->select('
                a.dnr_no,
                a.delivery_date,
                a.target_date,
                a.qty_delivery,
                a.item_fg_id,
                a.prod_date,
                d.number as item_fg_number,
                d.name as item_fg_name,
                d.uom,
                COALESCE(b.name, c.name) as destination_name,
                COALESCE(i.qty_incoming, 0) AS qty_incoming,
                (a.qty_delivery - COALESCE(i.qty_incoming, 0)) as qty_outstanding
            ');

            $this->db->from('delivery_rework a');

            $this->db->join('subconts b', 'a.destination = b.number', 'left');
            $this->db->join('teaching_factory c', 'a.destination = c.number', 'left');
            $this->db->join('item_fg d', 'd.id = a.item_fg_id');

            $this->db->join(
                "
                (
                    SELECT
                        dnr_no,
                        item_fg_id,
                        workorder,
                        SUM(qty) AS qty_incoming
                    FROM scan_incoming_sctf
                    WHERE incoming_type = 'Rework'
                    AND type_status = 'completed'
                    GROUP BY dnr_no, item_fg_id, workorder
                ) i",
                "
                    i.dnr_no = a.dnr_no
                    AND i.item_fg_id = a.item_fg_id
                    AND i.workorder = a.workorder
                ",
                'left'
            );

            $this->db->where('(a.qty_delivery - COALESCE(i.qty_incoming, 0)) >', 0);

            if ($name == 'rework_overdue') {
                $this->db->where('a.target_date <= CURDATE()', null, false);

            } elseif ($name == 'rework_reminder') {
                $this->db->where('a.target_date > CURDATE()', null, false);
                $this->db->where('DATEDIFF(a.target_date, CURDATE()) <= 2', null, false);
            }

            // if ($username == 'pmbri') {
            if($level >= 3) {
                $this->db->where('DATEDIFF(CURDATE(), a.target_date) >=', 2, null, false);
            }

            // $this->db->group_by([
            //     'a.dnr_no',
            //     'a.item_fg_id'
            // ]);

            $this->db->order_by('a.target_date', 'ASC');
            $this->db->order_by('a.dnr_no', 'ASC');
        }

            $records = $this->db->get()->result_array();
            $this->crud->update('notifications', ["users_id_to" => $this->session->username, "table_name" => $table, "users_id_from" => $user, "name" => $name, "status" => 0], ["status" => 1]);
            echo json_encode($records);
    }
}
