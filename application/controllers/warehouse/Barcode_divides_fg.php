<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Barcode_divides_fg extends CI_Controller
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
        //Validasi Form
        $this->form_validation->set_rules('label_no', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/barcode_divides_fg');
        } else {
            redirect('error_access');
        }
    }

    public function getSerial()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');
            $check_label = $this->crud->read('fg_scan_in_label', [], ['serial_label' => $label_no]);
            if ($check_label) {
                if ($check_label->status == '1') {
                    echo json_encode(array("title" => "Error", "message" => "Label Already Delivered", "theme" => "error"));
                    return;
                }
            }
            
            $this->db->select('a.*, a.scan_date as receipt_date, c.number, c.name');
            $this->db->from('fg_scan_in_label a');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('a.serial_label', $label_no);
            $records = $this->db->get()->result_array();
            
            if (!$records) {
                $this->db->select('a.*, b.scan_date as receipt_date, c.number, c.name');
                $this->db->from('barcode_divides_fg a');
                $this->db->join('fg_scan_in_label b', 'b.serial_label = a.reff');
                $this->db->join('item_fg c', 'c.id = b.item_fg_id');
                $this->db->where('a.type', 'BALANCE');
                $this->db->where('a.status', 0);
                $this->db->where('a.label_divided', $label_no);
                $this->db->group_by('a.label_divided');
                $records = $this->db->get()->result_array();
            }

            $result = array();
            $result['total'] = count($records);
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                if ($post['bal'] >= 0) {
                    if ($post['bal'] == 0) {
                        $message = json_encode(array("title" => "Qty Zero", "message" => "Qty Balance is 0, cannot save.", "theme" => "error"));
                        echo $message;
                        return;
                    }
                    $label = $post['label_no'];
                    $label_sub = explode("-", $label);
                    $reff = isset($label_sub[0]) ? $label_sub[0] : '';
                    $reff .= isset($label_sub[1]) ? "-" . $label_sub[1] : '';
                    $reff .= isset($label_sub[2]) ? "-" . $label_sub[2] : '';
                    $reff .= isset($label_sub[3]) ? "-" . $label_sub[3] : '';
                    $kode = isset($label_sub[4]) ? $label_sub[4] : 0;

                    // Get the highest autoID from existing label_divided
                    $this->db->select_max('label_divided');
                    $this->db->like('label_divided', $post['label_no'] . '-', 'after');
                    $max_label_divided = $this->db->get('barcode_divides_fg')->row()->label_divided;

                    if ($max_label_divided) {
                        $max_autoID = (int)substr($max_label_divided, strrpos($max_label_divided, '-') + 1);
                        $autoID = sprintf("%02s", $max_autoID + 1);
                    } else {
                        $autoID = sprintf("%02s", $kode + 1);
                    }

                    $barcode_divides = false; // Reset barcode_divides to allow new creation

                    $checkLabel = $this->crud->read("fg_scan_in_label", [], ["serial_label" => $reff]);
                    if (!$checkLabel) {
                        $this->db->select('label_divided as label_no');
                        $this->db->from('barcode_divides_fg');
                        $this->db->where('deleted', 0);
                        $this->db->where('status', 0);
                        $this->db->where('label_no', $reff);
                        $this->db->group_by('label_no');
                        $checkLabel = $this->db->get()->row();
                    }

                    for ($i = 0; $i < 2; $i++) {
                        $label_divided = $post['label_no'] . '-' . $autoID;
                        if ($i == 0) {
                            $qty = $post['qty'];
                            $type = "SUPPLY";
                        } else {
                            $qty = $post['bal'];
                            $type = "BALANCE";
                        }
                        $arrLabel = [
                            "reff" => !empty($checkLabel->label_no) ? $checkLabel->label_no : $reff,
                            "label_no" => $post['label_no'],
                            "label_divided" => $label_divided,
                            "qty" => $qty,
                            "type" => $type,
                        ];
                        if (!$barcode_divides) {
                            $send   = $this->crud->create('barcode_divides_fg', $arrLabel);
                            $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                            //Generate QRcode
                            $this->createQrcode($label_divided, "assets/image/qrcode/");
                        } else {
                            $message = json_encode(array("title" => "Available", "message" => "Barcode Divided has been created ", "theme" => "error"));
                        }

                        // if ($i == 1) {
                        //     // Update the qty in fg_scan_in_label with the BALANCE qty
                        //     $this->db->set('qty', $qty);
                        //     $this->db->where('serial_label', $reff);
                        //     $this->db->update('fg_scan_in_label');
                        // }

                        $autoID = sprintf("%02s", $autoID + 1);
                    }
                } else {
                    $message = json_encode(array("title" => "Not Balance", "message" => "Qty Balance <= 0 ", "theme" => "error"));
                }
                echo $message;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print()
    {
        $label_no = base64_decode($this->input->get('label_no'));

        $this->db->select('a.*, b.scan_date as receipt_date, c.number, c.name, c.specification, d.location, d.area, e.compound_lot, e.qc');
        $this->db->from('barcode_divides_fg a');
        $this->db->join('fg_scan_in_label b', 'a.reff = b.serial_label');
        $this->db->join('item_fg c', 'b.item_fg_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_fg_id = c.id', 'left');
        $this->db->join('label_packing e', 'e.serial_no = a.label_divided', 'left');
        $this->db->where('a.label_no', $label_no);
        $this->db->group_by('a.label_divided');
        $records = $this->db->get()->result_object();

        if (empty($records)) {
            echo "<center><h3>Data not found</h3></center>";
            return;
        }

        $first_serial_label = $records[0]->label_divided;

        foreach ($records as $record) {
            $this->createQrcode($record->label_divided, "assets/image/qrcode/");
        }

        $html = '<html>
                    <head>
                        <title>Label Packing - ' . $first_serial_label . '</title>
                        <link rel="icon" type="image/png" href="' . base_url('assets/image/icon.png') . '">
                        <style>
                            body { font-family: Arial, Helvetica, sans-serif; margin: 2; }
                            table { border-collapse: collapse; width: 7.5cm; height: 8cm; font-size: 20px; border: 2px solid black; table-layout: fixed; }
                            th, td { border: 1px solid black; padding: 2px; text-align: left; }
                            th { text-align: center; font-size: 14px; font-weight: bold; }
                            .header { text-align: center; font-size: 15px; font-weight: bold; }
                            .logo { text-align: center; width: 100%; padding: 3px; }
                            .operator-sign, .qc-sign, .qr-code { font-size: 12px; text-align: center; height: 20mm; vertical-align: bottom; font-weight: bold; }
                            .qc-sign { text-align: center; height: 20mm; }
                            .qr-code img { width: 60px; height: 60px; display: block; margin: 0 auto; }
                            .serial-label { font-size: 11px; text-align: center; word-wrap: break-word; overflow: hidden; font-weight: bold; }
                            @page {
                                    size: 7.5cm 8cm;
                                    margin: auto;
                                    }
                            @media print {
                                    .printLabel {
                                        page-break-after: always;
                                        width: 7.5cm;
                                        height: 8cm;
                                        display: block;
                                        padding: 0;
                                        margin: auto;
                                        margin-top: 50px;
                                    }

                                    table {
                                        width: 100%;
                                        font-size: 15px;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    body {
                                        margin: 0;
                                        padding: 0;
                                    }
                                }
                        </style>
                    </head>
                <body>';
        
        foreach ($records as $detail) {
            $qty_packing_formatted = number_format($detail->qty, 0, ',', '.') . ' PCS';
            $html .= '<div class="printLabel">
                        <table style="max-width: 7.5cm; max-height:8cm;">
                        <tr>
                            <th class="logo" colspan="6" style="text-align: center;">
                                <img src="' . base_url('assets/image/bri_logo.png') . '" width="25" align="left"/>
                                <span class="header" style="font-size: 20px; height: 20px;">LABEL PACKING</span>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->number . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Part Name:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->name . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Qty/pack:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $qty_packing_formatted . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Material:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->specification . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Prod Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->receipt_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>Pack Date:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->receipt_date . '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 30%;"><b>LOT No:</b></td>
                            <td colspan="4" style="font-weight: bold;">' . $detail->compound_lot . '</td>
                        </tr>
                        <tr>
                            <th colspan="2">QC</th>
                            <th colspan="4">QR Code</th>
                        </tr>
                        <tr>
                            <td class="operator-sign" colspan="2">' . $detail->qc . '</td>
                            <td class="qr-code" colspan="4">
                                <img src="' . base_url('assets/image/qrcode/' . $detail->label_divided . '.png') . '"/>
                                <div class="serial-label">' . $detail->label_divided . '</div>
                            </td>
                        </tr>
                    </table>
            
            </div>';
        } 
    
        $html .= '<script>window.print()</script>
                </body>
            </html>';
    
        die($html);
    }
}
