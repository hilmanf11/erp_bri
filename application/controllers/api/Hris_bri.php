<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Hris_bri extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        $this->load->library(['form_validation', 'session']);
        $this->load->model(['crud', 'emails']);
    }

    public function getOperatorName()
    {
        $q = $this->input->get('q');
        $base_url = 'http://hris.piranti-ind.com/hris-bri/api/employee_lists/list/';

        $url = $base_url . ($q !== '' ? urlencode($q) : '');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($response === false || $curl_error) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    ['name' => 'Connection Failure']
                ], JSON_PRETTY_PRINT));
        }

        $data = json_decode($response, true);
        $result = [];

        if (is_array($data)) {
            $unique = [];
            foreach ($data as $user) {
                $name = isset($user['name']) ? trim($user['name']) : '';
                if ($name !== '' && !isset($unique[$name])) {
                    $unique[$name] = true;
                    $result[] = ['name' => $name];
                }
            }

            usort($result, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result, JSON_PRETTY_PRINT));
    }
}
