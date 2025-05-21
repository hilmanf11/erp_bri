<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Display_monitor extends CI_Controller
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
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/display_monitor');
        } else {
            redirect('error_access');
        }
    }
    //GET DATATABLES
    public function datatables()
    {
            $this->db->select('RIGHT(location, 2) as box, sensor_status as sensor');
            $this->db->from('chem_item_loc');
            $this->db->where('deleted', 0);
            $this->db->order_by('id', 'asc');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
    }

}
