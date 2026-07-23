<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Production_schedule_mixing extends CI_Controller
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

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/production_schedule_mixing');
        } else {
            redirect('error_access');
        }
    }

    public function readPeriods()
    {
        $rows = $this->db
            ->select('period')
            ->from('production_schedule_mixing')
            ->where('deleted', 0)
            ->group_by('period')
            ->order_by('period', 'DESC')
            ->get()
            ->result_array();

        echo json_encode($rows);
    }

    public function readBy()
    {
        $period = $this->input->post('period');
        $item_fg_id = $this->input->post('item_fg_id');
    }

    public function read()
    {
        $period     = $this->input->post('period');
        $item_fg_id = $this->input->post('item_fg_id');

        $mapped_item_rm_id = '';
        if (!empty($item_fg_id)) {

            $alias = $this->db
                ->select('item_rm_id')
                ->from('compound_alias')
                ->where('item_fg_id', $item_fg_id)
                ->where('deleted', 0)
                ->get()
                ->row_array();

            $mapped_item_rm_id = $alias['item_rm_id'] ?? '';
        }

        $this->db->select("
            psm.id,
            psm.period,
            ca.item_rm_id,
            irm.number AS compound_no,
            psm.total_qty_kg,
            COALESCE(psmc.mpq, ifg.mpq) as mpq,
            COALESCE(psmc.lifetime, ifg.lifetime) as lifetime,

            COUNT(psmd.id) total_detail,
            COUNT(CASE WHEN psmd.status = 1 THEN 1 ELSE 0 END) total_closed,
            COUNT(CASE WHEN psmd.status = 2 THEN 1 END) total_released
        ");

        $this->db->from('compound_alias ca');
        $this->db->join('item_rm irm', 'irm.id = ca.item_rm_id');
        $this->db->join('item_fg ifg', 'ifg.id = ca.item_fg_id', 'left');

        $join = "psm.item_rm_id = ca.item_rm_id AND psm.deleted = 0";
        if (!empty($period)) {
            $join .= " AND psm.period = '".$this->db->escape_str(str_replace('-', '', $period))."'";
        }

        $this->db->join('production_schedule_mixing psm', $join, 'left');
        $this->db->join('production_schedule_mixing_detail psmd',
            'psmd.production_schedule_mixing_id = psm.id
            AND psmd.deleted = 0',
            'left'
        );
        $this->db->join('production_schedule_mixing_convert psmc',
            'psmc.id = psmd.production_schedule_mixing_convert_id',
            'left'
        );

        if (!empty($mapped_item_rm_id)) {
            $this->db->where('psm.item_rm_id', $mapped_item_rm_id);
        }

        $this->db->where('ifg.status', 0);
        $this->db->group_by('ca.item_rm_id');
        $this->db->order_by('ca.item_rm_id','ASC');
        $headers = $this->db->get()->result_array();

        $ids = [];

        foreach ($headers as $header) {
            if (!empty($header['id'])) {
                $ids[] = $header['id'];
            }
        }

        $details = [];
        if (!empty($ids)) {
            $details = $this->db
                ->select("
                    psmd.production_schedule_mixing_id,
                    psmd.production_schedule_mixing_convert_id,

                    psmd.item_rm_id,
                    psmd.wp_mix_date,
                    psmd.wp_mix_compound,
                    psmd.qty_need_kg,
                    psmd.status,

                    psmc.workorder_mix_compound,
                    psmc.planning_qty,
                    psmc.wp_mix_date_from,
                    psmc.wp_mix_date_to,
                    psmc.mpq,
                    psmc.moq
                ")
                ->from('production_schedule_mixing_detail psmd')
                ->join(
                    'production_schedule_mixing_convert psmc',
                    'psmc.id = psmd.production_schedule_mixing_convert_id',
                    'left'
                )
                ->where_in('psmd.production_schedule_mixing_id',$ids)
                ->where('psmd.deleted',0)
                ->order_by('psmd.wp_mix_date','ASC')
                ->order_by('psmd.id','ASC')
                ->get()
                ->result_array();


            $groupedDetails = [];
            foreach($details as $detail){

                if (!empty($detail['production_schedule_mixing_convert_id'])) {
                    $key = $detail['production_schedule_mixing_id'] .'|'. $detail['production_schedule_mixing_convert_id'];

                } else {

                    $key = $detail['production_schedule_mixing_id'] .'|'. $detail['wp_mix_date'];
                }

                if(!isset($groupedDetails[$key])){
                    $groupedDetails[$key] = $detail;

                    $groupedDetails[$key]['qty_need_kg'] = 0;
                    $groupedDetails[$key]['total_detail'] = 0;
                    $groupedDetails[$key]['total_closed'] = 0;
                    $groupedDetails[$key]['total_released'] = 0;
                }

                $groupedDetails[$key]['qty_need_kg'] += (float)$detail['qty_need_kg'];

                
                $day = (int) date('j', strtotime($detail['wp_mix_date']));

                if (!isset($groupedDetails[$key]['start_day'])) {
                    $groupedDetails[$key]['start_day'] = $day;
                }
                $offset = $day - $groupedDetails[$key]['start_day'];


                if (!empty($detail['production_schedule_mixing_convert_id'])) {
                    $groupedDetails[$key]['start_day'] =
                        (int) date('j', strtotime($detail['wp_mix_date_from']));

                    $groupedDetails[$key]['end_day'] =
                        (int) date('j', strtotime($detail['wp_mix_date_to']));

                } else {

                    $day = (int) date('j', strtotime($detail['wp_mix_date']));

                    $groupedDetails[$key]['start_day'] = $day;
                    $groupedDetails[$key]['end_day'] = $day;
                }


                if (!isset($groupedDetails[$key]['daily_qty'][$offset])) {

                    $groupedDetails[$key]['daily_qty'][$offset] = [
                        'offset' => $offset,
                        'day'    => $day,
                        'qty'    => 0
                    ];

                }
                $groupedDetails[$key]['daily_qty'][$offset]['qty'] += (float)$detail['qty_need_kg'];


                $groupedDetails[$key]['total_detail']++;
                if($detail['status']==1){
                    $groupedDetails[$key]['total_closed']++;
                } 
                
                if($detail['status']==2) {
                    $groupedDetails[$key]['total_released']++;
                }
            }
            $details = array_values($groupedDetails);
        }
        
        $rows = [];

        foreach ($headers as $k => $header) {
            $row = [
                'no'          => $k + 1,
                'id'          => $header['id'],
                'compound_id' => $header['item_rm_id'],
                'compound_no' => $header['compound_no'],
                'qty'         => $header['total_qty_kg'] ?? 0,
                'mpq'         => $header['mpq'] ?? 0,
                'lifetime'    => $header['lifetime'] ?? 0,
                'schedule'    => []
            ];

            foreach ($details as $detail) {
                if ($detail['total_released'] == $detail['total_detail']) {
                    $detail_status = 'released';
                } elseif ($detail['total_closed'] == $detail['total_detail']) {
                    $detail_status = 'closed';
                } elseif ($detail['total_closed'] > 0 || $detail['total_released'] > 0) {
                    $detail_status = 'progress';

                } else {

                    $detail_status = 'open';
                }

                if ($detail['production_schedule_mixing_id'] != $header['id']) {
                    continue;
                }

                if (!empty($detail['production_schedule_mixing_convert_id'])) {
                    $qty = (float)$detail['planning_qty'];

                } else {

                    $qty = (float)$detail['qty_need_kg'];
                }

                if (!empty($detail['production_schedule_mixing_convert_id'])) {
                    $day = (int) date('j', strtotime($detail['wp_mix_date_from']));

                } else {

                    $day = (int) date('j', strtotime($detail['wp_mix_date']));
                }

                $wp_date = !empty($detail['production_schedule_mixing_convert_id'])
                    ? $detail['wp_mix_date_from']
                    : $detail['wp_mix_date'];

                ksort($detail['daily_qty']);

                $row['schedule'][] = [
                    'day' => $day,
                    'start_day' => $detail['start_day'],
                    'end_day' => $detail['end_day'],
                    'span' => $detail['end_day'] - $detail['start_day'] + 1,
                    'wp' => $detail['wp_mix_compound'],
                    'wp_date' => $wp_date,
                    'code' => $detail['workorder_mix_compound'] ?? '',
                    'qty' => $qty,
                    'status' => $detail_status,
                    'convert_id'=>$detail['production_schedule_mixing_convert_id'],
                    'daily_qty' => array_values($detail['daily_qty'])
                ];
            }

            $rows[] = $row;
        }

        echo json_encode([
            'rows' => $rows
        ]);
    }

    private function getPlanningType($need, $mpq, $moq)
    {
        if ($need <= $mpq) {
            return 'MPQ';
        }

        if ($need <= $moq) {
            return 'MOQ';
        }

        $planMpq = ceil($need / $mpq) * $mpq;
        $planMoq = ceil($need / $moq) * $moq;

        return (($planMpq - $need) <= ($planMoq - $need))
            ? 'MPQ'
            : 'MOQ';
    }

    public function readDetail()
    {
        $header_id = $this->input->post('header_id');
        $item_rm_id = $this->input->post('item_rm_id');
        $wp_date    = $this->input->post('wp_date');
        $convert_id    = $this->input->post('convert_id');

        $header = $this->db
            ->select("
                psmd.item_rm_id,
                irm.number compound_no,

                COALESCE(psmc.mpq, ifg.mpq) as mpq,
                COALESCE(psmc.moq, ifg.moq) as moq,
                COALESCE(psmc.wp_mix_date_from, psmd.wp_mix_date) as wp_mix_date,
                COALESCE(psmc.workorder_mix_compound, '') as workorder_mix_compound,
                COALESCE(psmc.planning_qty, SUM(psmd.qty_need_kg)) as total_round_qty_kg,

                SUM(psmd.qty_need_kg) total_qty_kg,

                MIN(psmd.created_by) created_by,
                MIN(psmd.created_date) created_date,

                COUNT(*) total_detail,
                COUNT(CASE WHEN psmd.status = 1 THEN 1 END) total_closed,
                COUNT(CASE WHEN psmd.status = 2 THEN 1 END) total_released
            ")
            ->from('production_schedule_mixing_detail psmd')
            ->join('item_rm irm','irm.id = psmd.item_rm_id')
            ->join('compound_alias ca', 'irm.id = ca.item_rm_id', 'left')
            ->join('item_fg ifg', 'ifg.id = ca.item_fg_id', 'left')
            ->join(
                'production_schedule_mixing_convert psmc',
                'psmc.id = psmd.production_schedule_mixing_convert_id',
                'left'
            )
            ->where('psmd.deleted',0)
            ->where('psmd.production_schedule_mixing_id', $header_id)
            ->where('psmd.item_rm_id',$item_rm_id);

            if (!empty($convert_id)) {
                $this->db->where('psmd.production_schedule_mixing_convert_id', $convert_id);
            } else {
                $this->db->where('psmd.wp_mix_date', $wp_date);
            }

        $header = $this->db->get()->row_array();
        if(!$header){
            echo json_encode([]);
            return;
        }

        $header['round_type'] = $this->getPlanningType(
            (float)$header['total_qty_kg'],
            (float)$header['mpq'],
            (float)$header['moq']
        );

        if ($header['total_released'] == $header['total_detail']) {
            $status = 'released';
        } elseif ($header['total_closed'] == $header['total_detail']) {
            $status = 'closed';
        } elseif ($header['total_closed'] == 0 && $header['total_released'] == 0) {
            $status = 'open';
        } else {
            $status = 'progress';
        }

        $details = $this->db
            ->select("
                psmd.wp_press_date,
                psmd.workorder_press,
                ifg.number product_no,
                psmd.composition,
                psmd.qty_press,
                psmd.qty_need_kg
            ")
            ->from('production_schedule_mixing_detail psmd')
            ->join('item_fg ifg','ifg.id = psmd.item_fg_id')
            ->where('psmd.deleted',0)
            ->where('psmd.production_schedule_mixing_id', $header_id)
            ->where('psmd.item_rm_id',$item_rm_id);

            if (!empty($convert_id)) {
                $this->db->where('psmd.production_schedule_mixing_convert_id', $convert_id);
            } else {
                $this->db->where('psmd.wp_mix_date', $wp_date);
            }

        $details = $this->db
            ->order_by('psmd.wp_press_date','ASC')
            ->order_by('psmd.workorder_press','ASC')
            ->get()
            ->result_array();

        echo json_encode([
            'header'  => $header,
            'status'  => $status,
            'details' => $details
        ]);
    }

    public function exportRecap()
    {
        $period     = $this->input->post('period');
        $item_fg_id = $this->input->post('item_fg_id');

        $mapped_item_rm_id = '';
        if (!empty($item_fg_id)) {

            $alias = $this->db
                ->select('item_rm_id')
                ->from('compound_alias')
                ->where('item_fg_id', $item_fg_id)
                ->where('deleted', 0)
                ->get()
                ->row_array();

            $mapped_item_rm_id = $alias['item_rm_id'] ?? '';
        }

        $this->db->select("
            psm.id,
            psm.period,
            psm.item_rm_id,
            irm.number AS compound_no,
            psm.total_qty_kg,
            ifg.mpq,

            COUNT(psmd.id) total_detail,
            SUM(CASE WHEN psmd.status = 1 THEN 1 ELSE 0 END) total_closed
        ");
        $this->db->from('production_schedule_mixing psm');
        $this->db->join('item_rm irm', 'irm.id = psm.item_rm_id');
        $this->db->join('compound_alias ca', 'irm.id = ca.item_rm_id', 'left');
        $this->db->join('item_fg ifg', 'ifg.id = ca.item_fg_id', 'left');
        $this->db->join(
            'production_schedule_mixing_detail psmd',
            'psmd.production_schedule_mixing_id = psm.id
            AND psmd.deleted = 0',
            'left'
        );

        $this->db->where('psm.deleted', 0);

        if (!empty($period)) {
            $this->db->where('psm.period', str_replace('-', '', $period));
        }

        if (!empty($mapped_item_rm_id)) {
            $this->db->where('psm.item_rm_id', $mapped_item_rm_id);
        }

        $this->db->group_by('psm.id');
        $this->db->order_by('psm.item_rm_id', 'ASC');

        $headers = $this->db->get()->result_array();

        $ids = array_column($headers, 'id');

        $details = [];

        if (!empty($ids)) {

            // $details = $this->db
            //     ->select("
            //         production_schedule_mixing_id,
            //         item_rm_id,
            //         wp_mix_date,
            //         wp_mix_compound,
            //         workorder_mix_compound,

            //         SUM(qty_need_kg) qty_need_kg,

            //         COUNT(*) total_detail,
            //         SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) total_closed
            //     ")
            //     ->from('production_schedule_mixing_detail')
            //     ->where_in('production_schedule_mixing_id', $ids)
            //     ->where('deleted', 0)
            //     ->group_by('production_schedule_mixing_id')
            //     ->group_by('item_rm_id')
            //     ->group_by('workorder_mix_compound')
            //     ->group_by('wp_mix_date')
            //     ->get()
            //     ->result_array();

            $details = $this->db
                ->select("
                    psmd.production_schedule_mixing_id,
                    psmd.item_rm_id,
                    psmd.wp_mix_date,
                    psmd.wp_mix_compound,
                    psmd.workorder_mix_compound,

                    SUM(psmd.qty_need_kg) qty_need_kg,

                    ifg.mpq,

                    COUNT(*) total_detail,
                    SUM(CASE WHEN psmd.status = 1 THEN 1 ELSE 0 END) total_closed
                ")
                ->from('production_schedule_mixing_detail psmd')
                ->join('compound_alias ca', 'ca.item_rm_id = psmd.item_rm_id', 'left')
                ->join('item_fg ifg', 'ifg.id = ca.item_fg_id', 'left')
                ->where_in('psmd.production_schedule_mixing_id', $ids)
                ->where('psmd.deleted', 0)
                ->group_by('psmd.production_schedule_mixing_id')
                ->group_by('psmd.item_rm_id')
                ->group_by('psmd.workorder_mix_compound')
                ->group_by('psmd.wp_mix_date')
                ->get()
                ->result_array();
        }

        $rows = [];
        foreach ($headers as $k => $header) {
            $row = [
                'no'          => $k + 1,
                'id'          => $header['id'],
                'compound_id' => $header['item_rm_id'],
                'compound_no' => $header['compound_no'],
                'qty'         => $header['total_qty_kg'],
                'mpq'         => $header['mpq'] ?? 0,
                'schedule'    => []
            ];

            foreach ($details as $detail) {

                if ((int)$detail['total_closed'] === 0) {
                    $detail_status = 'open';
                } elseif ((int)$detail['total_closed'] === (int)$detail['total_detail']) {
                    $detail_status = 'closed';
                } else {
                    $detail_status = 'progress';
                }

                if ($detail['production_schedule_mixing_id'] != $header['id']) {
                    continue;
                }

                $qty = ($detail['qty_need_kg']);

                $qty = (float)$detail['qty_need_kg'];
                $mpq = (int)$detail['mpq'];

                if ($mpq > 0) {
                    $qty = ceil($qty / $mpq) * $mpq;
                }

                $row['schedule'][] = [
                    'day'    => (int)date('j', strtotime($detail['wp_mix_date'])),
                    'wp'     => $detail['wp_mix_compound'],
                    'wp_date'  => $detail['wp_mix_date'],
                    'code'   => $detail['workorder_mix_compound'],
                    'qty'    => $qty,
                    'status' => $detail_status
                ];
            }

            $rows[] = $row;
        }
    }

    private function convert_group()
    {
        $prefix = 'CVT' . date('ymd');

        $last = $this->db
            ->select('convert_group')
            ->like('convert_group', $prefix, 'after')
            ->order_by('convert_group', 'DESC')
            ->limit(1)
            ->get('production_schedule_mixing_convert')
            ->row();

        $seq = 1;

        if ($last) {
            $seq = (int) substr($last->convert_group, -4) + 1;
        }

        return $prefix . sprintf('%04d', $seq);
    }

    private function getConvertData()
    {
        $item_fg_id = $this->input->post('item_fg_id');
        $from       = $this->input->post('from');
        $to         = $this->input->post('to');
        $method     = $this->input->post('method');
        $period = date('Ym', strtotime($from));

        $item = $this->db
            ->select('mpq, moq, leadtime, lifetime')
            ->where('id', $item_fg_id)
            ->get('item_fg')
            ->row();

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Compound not found.'
            ];
        }

        $mpq       = (int)$item->mpq;
        $moq       = (int)$item->moq;
        $leadtime  = (int)$item->leadtime;
        $lifetime  = (int)$item->lifetime;

        $maxMergeDays = max(1, $lifetime - $leadtime);

        $item_rm_id = '';

        if (!empty($item_fg_id)) {

            $alias = $this->db
                ->select('item_rm_id')
                ->from('compound_alias')
                ->where('item_fg_id', $item_fg_id)
                ->where('deleted', 0)
                ->get()
                ->row_array();

            $item_rm_id = $alias['item_rm_id'] ?? '';
        }

        $alreadyConverted = $this->db
            ->select('psc.wp_mix_date_from, psc.wp_mix_date_to, ir.number AS compound_no')
            ->from('production_schedule_mixing_convert psc')
            ->join('item_rm ir', 'ir.id = psc.item_rm_id')
            ->where('psc.period', $period)
            ->where('psc.item_rm_id', $item_rm_id)
            ->where('psc.deleted', 0)
            ->where('psc.wp_mix_date_from <=', $to)
            ->where('psc.wp_mix_date_to >=', $from)
            ->order_by('psc.wp_mix_date_from', 'ASC')
            ->limit(1)
            ->get()
            ->row();

        if ($alreadyConverted) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Compound %s has already been converted for WP Mix Date %s to %s.',
                    $alreadyConverted->compound_no,
                    date('d M Y', strtotime($alreadyConverted->wp_mix_date_from)),
                    date('d M Y', strtotime($alreadyConverted->wp_mix_date_to))
                )
            ];

        }

        // $rows = $this->db
        //     ->where('period', $period)
        //     ->where('item_rm_id', $item_rm_id)
        //     ->where('deleted', 0)
        //     ->where('wp_mix_date >=', $from)
        //     ->where('wp_mix_date <=', $to)
        //     ->order_by('wp_mix_date', 'ASC')
        //     ->order_by('id', 'ASC')
        //     ->get('production_schedule_mixing_detail')
        //     ->result();

        $rows = $this->db
            ->select('psmd.*, ifg.number AS item_fg_number')
            ->from('production_schedule_mixing_detail psmd')
            ->join('item_fg ifg', 'ifg.id = psmd.item_fg_id')
            ->where('psmd.period', $period)
            ->where('psmd.item_rm_id', $item_rm_id)
            ->where('psmd.deleted', 0)
            ->where('psmd.wp_mix_date >=', $from)
            ->where('psmd.wp_mix_date <=', $to)
            ->order_by('psmd.wp_mix_date', 'ASC')
            ->order_by('psmd.id', 'ASC')
            ->get()
            ->result();

        $groups = [];

        foreach ($rows as $row) {
            $groups[$row->wp_mix_date][] = $row;
        }

        return [
            'success'        => true,

            'period'         => $period,
            'item_fg_id'     => $item_fg_id,
            'item_rm_id'     => $item_rm_id,

            'from'           => $from,
            'to'             => $to,

            'method'         => $method,

            'mpq'            => $mpq,
            'moq'            => $moq,
            'leadtime'       => $leadtime,
            'lifetime'       => $lifetime,
            'maxMergeDays'   => $maxMergeDays,

            'rows'           => $rows,
            'groups'         => $groups
        ];
    }

    private function buildConvertSimulation($data) 
    {
        $method        = $data['method'];
        $groups        = $data['groups'];
        $mpq           = $data['mpq'];
        $moq           = $data['moq'];
        $leadtime      = $data['leadtime'];
        $lifetime      = $data['lifetime'];
        $maxMergeDays  = $data['maxMergeDays'];

        $simulation = [];

        $simulation = [
            'summary' => [
                'total_need'        => 0,
                'total_planning'    => 0,
                'total_waste'       => 0,
                'batch_count'       => 0
            ],
            'batches' => []
        ];

        if($method == 'round_mpq_moq'){
            $dates = array_keys($groups);
            $totalDates = count($dates);

            $batch = [];
            $batchQty = 0;
            $batchStartDate = null;
            $convertSeq = 1;

            foreach ($dates as $index => $mixDate) {
                $dayRows = $groups[$mixDate];
                if ($batchStartDate === null) {
                    $batchStartDate = $mixDate;
                }

                $dayQty = 0;
                foreach ($dayRows as $row) {
                    $dayQty += (float)$row->qty_need_kg;
                }

                foreach ($dayRows as $row) {
                    $batch[] = $row;
                }

                $batchQty += $dayQty;
                $nextDate = ($index + 1 < $totalDates) ? $dates[$index + 1] : null;

                $closeBatch = false;

                if ($nextDate !== null) {
                    $nextDiffDays = floor(
                        (strtotime($nextDate) - strtotime($batchStartDate)) / 86400
                    );

                    if ($nextDiffDays > $maxMergeDays) {
                        $closeBatch = true;
                    }
                }

                if (!$closeBatch && $batchQty >= $mpq && $batchQty <= $moq) {
                    $closeBatch = true;
                }

                if (!$closeBatch && $batchQty > $moq && $nextDate !== null) {

                    $currentPlanning = $this->getPlanningQty(
                        $batchQty,
                        $mpq,
                        $moq
                    );

                    $nextQty = 0;
                    foreach ($groups[$nextDate] as $row) {
                        $nextQty += (float)$row->qty_need_kg;
                    }

                    $nextPlanning = $this->getPlanningQty(
                        $batchQty + $nextQty,
                        $mpq,
                        $moq
                    );

                    if ($nextPlanning > $currentPlanning) {
                        $closeBatch = true;
                    }
                }

                if ($nextDate === null && !empty($batch)) {
                    $closeBatch = true;
                }

                if ($closeBatch) {

                    $first = reset($batch);
                    $last  = end($batch);

                    $totalNeed = 0;
                    foreach($batch as $r){
                        $totalNeed += $r->qty_need_kg;
                    }

                    $planning = $this->getPlanningQty(
                        $totalNeed,
                        $mpq,
                        $moq
                    );

                    $dailyQty = [];
                    foreach ($batch as $row) {
                        if (!isset($dailyQty[$row->wp_mix_date])) {
                            $dailyQty[$row->wp_mix_date] = 0;
                        }
                        $dailyQty[$row->wp_mix_date] += $row->qty_need_kg;
                    }

                    $simulation['batches'][] = [
                        'from' => $first->wp_mix_date,
                        'to'   => $last->wp_mix_date,
                        'rows' => $batch,
                        'mpq' => $mpq,
                        'moq' => $moq,
                        'leadtime' => $leadtime,
                        'lifetime' => $lifetime,
                        'need' => $totalNeed,
                        'planning' => $planning,
                        'waste' => $planning - $totalNeed,
                        'daily_qty' => $dailyQty,
                    ];

                    $convertSeq++;

                    $batch = [];
                    $batchQty = 0;
                    $batchStartDate = null;
                }
            }

        } else if($method == 'merge_wp_round'){

            $dates = array_keys($groups);

            $batch = [];
            $batchStartDate = null;
            $convertSeq = 1;

            foreach($dates as $index => $mixDate){

                if($batchStartDate === null){
                    $batchStartDate = $mixDate;
                }

                foreach($groups[$mixDate] as $row){
                    $batch[] = $row;
                }

                $nextDate = $dates[$index + 1] ?? null;

                $closeBatch = false;
                if($nextDate){
                    $diff = floor((strtotime($nextDate) - strtotime($batchStartDate))/ 86400);
                    if($diff > $maxMergeDays){
                        $closeBatch = true;
                    }

                }else{

                    $closeBatch = true;
                }

                if($closeBatch){

                    $first = reset($batch);
                    $last  = end($batch);

                    $totalNeed = 0;
                    foreach($batch as $r){
                        $totalNeed += $r->qty_need_kg;
                    }

                    $planning = $this->getPlanningQty(
                        $totalNeed,
                        $mpq,
                        $moq
                    );

                    $dailyQty = [];
                    foreach ($batch as $row) {
                        if (!isset($dailyQty[$row->wp_mix_date])) {
                            $dailyQty[$row->wp_mix_date] = 0;
                        }
                        $dailyQty[$row->wp_mix_date] += $row->qty_need_kg;
                    }

                    $simulation['batches'][] = [
                        'from' => $first->wp_mix_date,
                        'to'   => $last->wp_mix_date,
                        'rows' => $batch,
                        'mpq' => $mpq,
                        'moq' => $moq,
                        'leadtime' => $leadtime,
                        'lifetime' => $lifetime,
                        'need' => $totalNeed,
                        'planning' => $planning,
                        'waste' => $planning - $totalNeed,
                        'daily_qty' => $dailyQty,
                    ];

                    $convertSeq++;

                    $batch = [];
                    $batchStartDate = null;
                }

            }

        }

        foreach($simulation['batches'] as $batch){
            $simulation['summary']['total_need'] += $batch['need'];
            $simulation['summary']['total_planning'] += $batch['planning'];
            $simulation['summary']['total_waste'] += $batch['waste'];
        }

        $simulation['summary']['batch_count'] = count($simulation['batches']);
        return $simulation;
    }

    private function saveSimulation($simulation)
    {
        foreach($simulation['batches'] as $seq => $batch){
            $this->_saveConvertBatch(
                $batch['rows'],
                $seq+1,
                $batch['mpq'],
                $batch['moq'],
                $batch['leadtime'],
                $batch['lifetime']
            );
        }
    }

    public function convert()
    {
        $data = $this->getConvertData();
        if (!$data['success']) {
            echo json_encode($data);
            return;
        }

        $simulation = $this->buildConvertSimulation($data);
        $this->saveSimulation($simulation);

        echo json_encode([
            'success'=>true
        ]);
    }

    public function previewConvert()
    {
        $data = $this->getConvertData();
        if(!$data['success']){
            echo json_encode($data);
            return;
        }

        $simulation = $this->buildConvertSimulation($data);

        echo json_encode([
            'success'    => true,
            'simulation' => $simulation
        ]);
    }

    private function _saveConvertBatch($rows, $seq, $mpq, $moq, $leadtime, $lifetime) 
    {
        $first = reset($rows);
        $last  = end($rows);
        $period = date('Ym', strtotime($first->wp_mix_date));

        $convertId = date('YmdHis') . sprintf('%04d', $seq);

        $workorder = $this->workorder_mix_compound(
            $first->wp_mix_date
        );

        $convertGroup = $this->convert_group();

        $totalQtyPress = 0;
        $totalQtyGram  = 0;
        $totalQtyKg    = 0;

        foreach ($rows as $row) {

            $totalQtyPress += $row->qty_press;
            $totalQtyGram  += $row->qty_need_gram;
            $totalQtyKg    += $row->qty_need_kg;
        }

        $planningQty = $this->getPlanningQty(
            $totalQtyKg,
            $mpq,
            $moq
        );

        $exists = $this->db
            ->where('item_rm_id', $first->item_rm_id)
            ->where('period', $period)
            ->where('wp_mix_date_from', $first->wp_mix_date)
            ->where('wp_mix_date_to', $last->wp_mix_date)
            ->where('deleted', 0)
            ->get('production_schedule_mixing_convert')
            ->row();

        if ($exists) {

            $convertId = $exists->id;

            $this->db
                ->where('id', $convertId)
                ->update(
                    'production_schedule_mixing_convert',
                    [
                        'total_qty_press'     => $totalQtyPress,
                        'total_qty_need_gram' => $totalQtyGram,
                        'total_qty_need_kg'   => $totalQtyKg,
                        'planning_qty'        => $planningQty,
                        'updated_by'          => $this->session->userdata('username'),
                        'updated_date'        => date('Y-m-d H:i:s')
                    ]
                );

        } else {
            $this->db->insert('production_schedule_mixing_convert', [

                'id'                      => $convertId,
                'created_by'              => $this->session->userdata('username'),
                'created_date'            => date('Y-m-d H:i:s'),
                'deleted'                 => 0,

                'convert_group'           => $convertGroup,
                'workorder_mix_compound'  => $workorder,

                'period'                  => $period,
                'item_rm_id'              => $first->item_rm_id,

                'mpq'                     => $mpq,
                'moq'                     => $moq,
                'leadtime'                => $leadtime,
                'lifetime'                => $lifetime,

                'wp_mix_date_from'        => $first->wp_mix_date,
                'wp_mix_date_to'          => $last->wp_mix_date,

                'total_qty_press'         => $totalQtyPress,
                'total_qty_need_gram'     => $totalQtyGram,
                'total_qty_need_kg'       => $totalQtyKg,
                'planning_qty'            => $planningQty

            ]);
        }

        foreach ($rows as $row) {

            $this->db
                ->where('id', $row->id)
                ->update(
                    'production_schedule_mixing_detail',
                    [
                        'production_schedule_mixing_convert_id' => $convertId,
                        'status' => 2,
                    ]
                );
        }
    }

    public function workorder_mix_compound($mix_date)
    {
        $date = date('ymd',strtotime($mix_date));
        $prefix = 'WOM'.$date.'-';

        $last = $this->db
            ->select('workorder_mix_compound')
            ->like('workorder_mix_compound',$prefix,'after')
            ->order_by('workorder_mix_compound','DESC')
            ->limit(1)
            ->get('production_schedule_mixing_convert')
            ->row();

        $seq = 1;
        if($last){
            $seq = (int)substr($last->workorder_mix_compound,-3) + 1;
        }

        return $prefix.sprintf('%03d',$seq);
    }

    private function getPlanningQty($need, $mpq, $moq)
    {
        if ($need <= $mpq) {
            return $mpq;
        }
        if ($need <= $moq) {
            return $moq;
        }

        $planMpq = ceil($need / $mpq) * $mpq;
        $planMoq = ceil($need / $moq) * $moq;

        if (($planMpq - $need) <= ($planMoq - $need)) {
            return $planMpq;
        }
        return $planMoq;
    }
}
