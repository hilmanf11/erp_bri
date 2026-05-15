<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->database();
    }

    /**
     * 
     * Range
     * ?date_from=2026-04-01&date_to=2026-04-30
     * 
     * Dari tanggal tertentu
     * ?date_from=2026-04-01
     * 
     * Sampai tanggal tertentu
     * ?date_to=2026-04-30
     * 
     * By PO NO
     * ?po_no=PORM2605-0002
     * 
     */

    public function readPoSita()
    {
        $date_from = $this->input->get('date_from');
        $date_to   = $this->input->get('date_to');
        $po_no     = $this->input->get('po_no');

        if (!empty($date_from)) {
            $date_from = date('Y-m-d', strtotime($date_from));
        }

        if (!empty($date_to)) {
            $date_to = date('Y-m-d', strtotime($date_to));
        }

        $this->db->select("
            'BRI' as company,
            po.po_no,
            po.po_date,
            po.qty,
            s.name as supplier_name,

            CASE 
                WHEN ic.number = 'RM' THEN 'RM'
                ELSE 'NRM'
            END as category,

            irm.id as part_id,
            irm.name as part_no,
            irm.number_internal as part_name,
            irm.number as supplier_product,
            irm.uom,

            po.delivery_date as eta,
            '' as etd,

            si.price,

            po.month_1,
            po.month_2,
            po.month_3,
            po.month_4,

            po.revision,
            po.remarks,
            po.remark_revision
        ");

        $this->db->from('purchase_orders po');
        $this->db->join('suppliers s', 's.id = po.supplier_id');
        $this->db->join('item_rm irm', 'irm.id = po.item_rm_id');
        $this->db->join('item_categories ic', 'ic.id = irm.item_category_id', 'left');
        $this->db->join('supplier_items si', 'si.supplier_id = po.supplier_id AND si.item_rm_id = po.item_rm_id', 'left');

        $this->db->where('s.number', 'AII');
        $this->db->where('po.deleted', 0);
        $this->db->where('po.status', 0); // active

        if (!empty($date_from) && !empty($date_to)) {
            $this->db->where('po.po_date >=', $date_from);
            $this->db->where('po.po_date <=', $date_to);
        } elseif (!empty($date_from)) {
            $this->db->where('po.po_date >=', $date_from);
        } elseif (!empty($date_to)) {
            $this->db->where('po.po_date <=', $date_to);
        }

        if(!empty($po_no)) {
            $this->db->where('po_no', $po_no);
        }

        $query = $this->db->get()->result();

        // mapping forecast
        $results = [];
        foreach ($query as $row) {
            $results[] = [
                "company"           => $row->company,
                "po_no"             => $row->po_no,
                "po_date"           => $row->po_date,
                "supplier_name"     => $row->supplier_name,
                "category"          => $row->category,
                "part_id"           => $row->part_id,
                "part_name"         => $row->part_no,
                "part_number"       => $row->part_name,
                "uom_name"          => $row->uom,
                "supplier_product"  => $row->supplier_product,
                "qty"               => (float)$row->qty,
                "price"             => (float)$row->price,
                "forecast_1"        => (int)$row->month_1,
                "forecast_2"        => (int)$row->month_2,
                "forecast_3"        => (int)$row->month_3,
                "forecast_4"        => (int)$row->month_4,
                "eta"               => $row->eta,
                "etd"               => "",
                "revision"          => (int)$row->revision,
                "remarks"           => $row->remarks,
                "remark_revision"   => $row->remark_revision ?? ""
            ];
        }

        $response = [
            "status"     => "success",
            "total_data" => count($results),
            "data"       => $results
        ];

        echo json_encode($response);
    }

    // public function readCountPoSita()
    // {
    //     $po_no = $this->input->get('po_no');

    //     $this->db->from('purchase_orders po');
    //     $this->db->join('suppliers s', 's.id = po.supplier_id');

    //     $this->db->where('s.number', 'AII');
    //     $this->db->where('po.deleted', 0);
    //     $this->db->where('po.status', 0);

    //     if (!empty($po_no)) {
    //         $this->db->where('po.po_no', $po_no);
    //     }

    //     $count = (int) $this->db->count_all_results();

    //     echo json_encode([
    //         "status" => "success",
    //         "data"   => [
    //             "count" => $count
    //         ]
    //     ]);
    // }

    public function readCountPoSita()
    {
        $this->db->select('COUNT(DISTINCT po.po_no) as total');
        $this->db->from('purchase_orders po');
        $this->db->join('suppliers s', 's.id = po.supplier_id');

        $this->db->where('s.number', 'AII');
        $this->db->where('po.deleted', 0);
        $this->db->where('po.status', 0);

        $row = $this->db->get()->row();

        $count = (int) ($row->total ?? 0);

        echo json_encode([
            "status" => "success",
            "data"   => [
                "count" => $count
            ]
        ]);
    }

    public function readAllPoSita()
    {
        $this->db->select("
            po.po_no,
            po.po_date,

            CASE 
                WHEN ic.number = 'RM' THEN 'RM'
                ELSE 'NRM'
            END as category,

            '' as sub_category,
            'NON KB' as po_type,
            CASE 
                WHEN po.po_no REGEXP '-[A-Z]' THEN 'ADDITIONAL'
                ELSE 'REGULER'
            END as type_part
        ");

        $this->db->from('purchase_orders po');
        $this->db->join('suppliers s', 's.id = po.supplier_id');
        $this->db->join('item_rm irm', 'irm.id = po.item_rm_id');
        $this->db->join('item_categories ic', 'ic.id = irm.item_category_id', 'left');

        $this->db->where('s.number', 'AII');
        $this->db->where('po.deleted', 0);
        $this->db->where('po.status', 0);

        $this->db->group_by('po.po_no');

        $query = $this->db->get()->result();

        $data = [];
        foreach ($query as $row) {
            $data[] = [
                "po_no"        => $row->po_no,
                "po_date"      => $row->po_date,
                "category"     => $row->category,
                "sub_category" => $row->sub_category,
                "po_type"      => $row->po_type,
                "type_part"    => $row->type_part
            ];
        }

        echo json_encode([
            "status" => "success",
            "data"   => $data
        ]);
    }

}