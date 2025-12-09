<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Approvals extends CI_Controller
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

    public function approveall()
    {
        $approved_to = $this->input->post('approved_to');
        $created_by = $this->input->post('created_by');
        $table_name = $this->input->post('table_name');

        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

        foreach ($datas as $data) {
            $id = $data->id;
            $user = $this->crud->read('users', [], ["username" => $data->created_by]);
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_name]);
            $table_approval = $table_name;
            if($table_name==="purchase_orders"){
                $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $data->request_no]);
                $table_approval = ($purchaseRequests->division==="DIV01")?'purchase_orders':'purchase_orders_2';
                $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
            }
            if($table_name==="supplier_items"){
                $table_approval = (preg_match('/\bExtruder\b/i', $user->position))?'supplier_items_2':'supplier_items';
                $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
            }
            if($table_name==="delivery_notes"){
                $table_approval = (preg_match('/\bExtruder\b/i', $user->position))?'delivery_notes_2':'delivery_notes';
                $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
            }

            if ($data->approved == 1) {
                $users_id = @$approval->user_approval_2;
                $approved = 2;
                
                // Tambahkan update untuk user_approval_1
                if ($table_name == 'delivery_notes') {
                    $values['user_approval_1'] = $this->session->username;
                }
                
            } elseif ($data->approved == 2) {
                $users_id = @$approval->user_approval_3;
                $approved = 3;
                
                // Tambahkan update untuk user_approval_2
                if ($table_name == 'delivery_notes') {
                    $values['user_approval_2'] = $this->session->username;
                }
                
            } elseif ($data->approved == 3) {
                $users_id = @$approval->user_approval_4;
                $approved = 4;
            } elseif ($data->approved == 4) {
                $users_id = @$approval->user_approval_5;
                $approved = 5;
            } else {
                $users_id = "";
                $approved = 0;
            }

            $values = array(
                "approved_by" => $this->session->username,
                "approved_date" => date('Y-m-d H:i:s'),
                "approved_to" => $users_id,
                "approved" => $approved,
            );

            $send = $this->db->update($table_name, $values, ["id" => $id]);
            if ($send) {
                //notifnya
                $columns = ['user_approval_1', 'user_approval_2', 'user_approval_3', 'user_approval_4', 'user_approval_5'];
                $approval_column = null;
                $preceding_values = [];
                $this->db->select($columns);
                $this->db->from('approvals');
                $this->db->where('id', $approval->id);
                $this->db->where('status', 0);
                $query = $this->db->get();
    
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        foreach ($columns as $index => $column) {
                            if ($row[$column] === $this->session->username) {
                                $approval_column = $column;
                                for ($i = 0; $i < $index; $i++) {
                                    $preceding_values[$columns[$i]] = $row[$columns[$i]];
                                }
                                
                                break 2;
                            }
                        }
                    }
                    
                    if ($approval_column) {
                        $preceding_values['user_created_by'] = $data->created_by;
                        foreach ($preceding_values as $col_name => $value_approved) {
                        $this->crud->create("notifications", [
                            "users_id_from" => $this->session->username,
                            "users_id_to" => $value_approved,
                            "approvals_id" => $approval->id,
                            "table_id" => $id,
                            "table_name" => $table_approval,
                            "name" => "Approved",
                            "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $table_name)) . ' has been approved',
                            "status" => 0,
                        ]);
                        }
                        // echo log_message(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
                    }
                }
                //notifnya
            }
        }

        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
    }

    public function approve()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $data = $this->crud->read($tablename, [], ["id" => $id]);
        $user = $this->crud->read('users', [], ["username" => $data->created_by]);
        $approval = $this->crud->read('approvals', [], ["table_name" => $tablename]);
        $table_approval = $tablename;
        if($tablename==="purchase_orders"){
            $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $data->request_no]);
            $table_approval = ($purchaseRequests->division==="DIV01")?'purchase_orders':'purchase_orders_2';
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
        }
        if($tablename==="supplier_items"){
            $table_approval = (preg_match('/\bExtruder\b/i', $user->position))?'supplier_items_2':'supplier_items';
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
        }
        if($tablename==="delivery_notes"){
            $table_approval = (preg_match('/\bExtruder\b/i', $user->position))?'delivery_notes_2':'delivery_notes';
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
        }
        if($tablename==="delivery_to_subconts"){
            $table_approval = 'delivery_to_subconts';
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
        }

        if ($data->approved == 1) {
            $users_id = @$approval->user_approval_2;
            $approved = 2;
            
            // Tambahkan update untuk user_approval_1
            if ($tablename == 'delivery_notes') {
                $values['user_approval_1'] = $this->session->username;
            }
            
        } elseif ($data->approved == 2) {
            $users_id = @$approval->user_approval_3;
            $approved = 3;
            
            // Tambahkan update untuk user_approval_2
            if ($tablename == 'delivery_notes') {
                $values['user_approval_2'] = $this->session->username;
            }
            
        } elseif ($data->approved == 3) {
            $users_id = @$approval->user_approval_4;
            $approved = 4;
        } elseif ($data->approved == 4) {
            $users_id = @$approval->user_approval_5;
            $approved = 5;
        } else {
            $users_id = "";
            $approved = 0;
        }

        $values["approved_by"] = $this->session->username;
        $values["approved_date"] = date('Y-m-d H:i:s');
        $values["approved_to"] = $users_id;
        $values["approved"] = $approved;

        $send = $this->db->update($tablename, $values, ["id" => $id]);
        if ($send) {
            $columns = ['user_approval_1', 'user_approval_2', 'user_approval_3', 'user_approval_4', 'user_approval_5'];
            $approval_column = null;
            $preceding_values = [];
            $this->db->select($columns);
            $this->db->from('approvals');
            $this->db->where('id', $approval->id);
            $this->db->where('status', 0);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    foreach ($columns as $index => $column) {
                        if ($row[$column] === $this->session->username) {
                            $approval_column = $column;
                            for ($i = 0; $i < $index; $i++) {
                                $preceding_values[$columns[$i]] = $row[$columns[$i]];
                            }
                            
                            break 2;
                        }
                    }
                }
                
                if ($approval_column) {
                    $preceding_values['user_created_by'] = $data->created_by;
                    foreach ($preceding_values as $col_name => $value_approved) {
                    $this->crud->create("notifications", [
                        "users_id_from" => $this->session->username,
                        "users_id_to" => $value_approved,
                        "approvals_id" => $approval->id,
                        "table_id" => $id,
                        "table_name" => $table_approval,
                        "name" => "Approved",
                        "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been approved',
                        "status" => 0,
                    ]);
                    }
                    echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Approved", "message" => "User not found in any approvals column.", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Approved", "message" => "No records found.", "theme" => "error"));
            }
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function disapproveall()
    {
        $created_by = $this->input->post('created_by');
        $approved_to = $this->input->post('approved_to');
        $table_name = $this->input->post('table_name');
        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

        foreach ($datas as $data) {
            $id = $data->id;
            $read = $this->crud->read($table_name, [], ["id" => $id]);
            $data = json_decode($read->approved_data, false);

            if (empty($data)) {
                $send = $this->db->update($table_name, ["deleted" => 2], ["id" => $id]);
            } else {
                $data = array_merge($data, ["deleted" => 2]);
                $send = $this->db->update($table_name, $data, ["id" => $id]);
            }
        }

        echo json_encode(array("title" => "Disapproved", "message" => "All Data Disapproved Successfully", "theme" => "success"));
    }

    public function disapprove()
{
    $id = $this->input->post('id');
    $tablename = $this->input->post('tablename');
    $read = $this->crud->read($tablename, [], ["id" => $id]);
    $read_table_approvals = $this->crud->read('approvals', [], ["table_name" => $tablename]);
    $data = json_decode($read->approved_data, true); // Decode sebagai array
    if (!is_array($data)) {
        $data = []; // Pastikan selalu array
    }
    $table_approval = $tablename;

    if ($tablename === "purchase_orders") {
        $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $read->request_no]);
        $table_approval = ($purchaseRequests->division === "DIV01") ? 'purchase_orders' : 'purchase_orders_2';
        $read_table_approvals = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
    }
    if ($tablename === "supplier_items") {
        $user = $this->crud->read('users', [], ["username" => $read->created_by]);
        $table_approval = (preg_match('/\bExtruder\b/i', $user->position)) ? 'supplier_items_2' : 'supplier_items';
        $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
    }
    if ($tablename === "delivery_notes") {
        $user = $this->crud->read('users', [], ["username" => $read->created_by]);
        $table_approval = (preg_match('/\bExtruder\b/i', $user->position)) ? 'delivery_notes_2' : 'delivery_notes';
        $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
    }
    if ($tablename === "delivery_to_subconts") {
        $user = $this->crud->read('users', [], ["username" => $read->created_by]);
        $table_approval = 'delivery_to_subconts';
        $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
    }

    if (!empty($read_table_approvals->id)) {
        /* Default */
        if (empty($data)) {
            $send = $this->db->update($tablename, ["deleted" => 2], ["id" => $id]);
        } else {
            $data = array_merge($data, ["deleted" => 2]); // $data sudah pasti array
            $send = $this->db->update($tablename, $data, ["id" => $id]);
        }
        $columns = ['user_approval_1', 'user_approval_2', 'user_approval_3', 'user_approval_4', 'user_approval_5'];

        $approval_column = null;
        $preceding_values = [];

        $this->db->select($columns);
        $this->db->from('approvals');
        $this->db->where('id', $read_table_approvals->id);
        $this->db->where('status', 0);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                foreach ($columns as $index => $column) {
                    if ($row[$column] === $this->session->username) {
                        $approval_column = $column;
                        for ($i = 0; $i < $index; $i++) {
                            $preceding_values[$columns[$i]] = $row[$columns[$i]];
                        }
                        break 2;
                    }
                }
            }

            if ($approval_column) {
                $preceding_values['user_created_by'] = $read->created_by;
                foreach ($preceding_values as $col_name => $value) {
                    $this->crud->create("notifications", [
                        "users_id_from" => $this->session->username,
                        "users_id_to" => $value,
                        "approvals_id" => $read_table_approvals->id,
                        "table_id" => $id,
                        "table_name" => $table_approval,
                        "name" => "Disapprove",
                        "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
                        "status" => 0,
                    ]);
                }
                echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Disapproved", "message" => "User not found in any approvals column.", "theme" => "error"));
            }
        } else {
            echo json_encode(array("title" => "Disapproved", "message" => "No records found.", "theme" => "error"));
        }
    }
}


    // public function disapprove()
    // {
    //     $id = $this->input->post('id');
    //     $tablename = $this->input->post('tablename');
    //     $read = $this->crud->read($tablename, [], ["id" => $id]);
    //     $read_table_approvals = $this->crud->read('approvals', [], ["table_name" => $tablename]);
    //     $data = json_decode($read->approved_data, false);
    //     $table_approval = $tablename;

    //     if($tablename==="purchase_orders"){
    //         $purchaseRequests = $this->crud->read('purchase_requests', [], ["request_no" => $read->request_no]);
    //         $table_approval = ($purchaseRequests->division==="DIV01")?'purchase_orders':'purchase_orders_2';
    //         $read_table_approvals = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
    //     }
    //     if($tablename==="supplier_items"){
    //         $user = $this->crud->read('users', [], ["username" => $read->created_by]);
    //         $table_approval = (preg_match('/\bExtruder\b/i', $user->position))?'supplier_items_2':'supplier_items';
    //         $approval = $this->crud->read('approvals', [], ["table_name" => $table_approval]);
    //     }

    //     if(!empty($read_table_approvals->id)){
    //     /* Default */
    //         if (empty($data)) {
    //             $send = $this->db->update($tablename, ["deleted" => 2], ["id" => $id]);
    //             // $send = $this->crud->delete($tablename, ["id" => $id]);
    //         } else {
    //             $data = array_merge($data, ["deleted" => 2]);
    //             $send = $this->db->update($tablename, $data, ["id" => $id]);
    //         }
    //         $columns = ['user_approval_1', 'user_approval_2', 'user_approval_3', 'user_approval_4', 'user_approval_5'];

    //         // Initialize variables to hold the column containing the user and preceding column values
    //         $approval_column = null;
    //         $preceding_values = [];

    //         // Perform a query to find which column contains the current user
    //         $this->db->select($columns);
    //         $this->db->from('approvals');
    //         $this->db->where('id', $read_table_approvals->id);
    //         $this->db->where('status', 0);
    //         $query = $this->db->get();

    //         // Check if a result is found
    //         if ($query->num_rows() > 0) {
    //             // Loop through each row
    //             foreach ($query->result_array() as $row) {
    //                 // Check each column to find where the current user is located
    //                 foreach ($columns as $index => $column) {
    //                     if ($row[$column] === $this->session->username) {
    //                         // Column containing the current user found
    //                         $approval_column = $column;
    //                         for ($i = 0; $i < $index; $i++) {
    //                             $preceding_values[$columns[$i]] = $row[$columns[$i]];
    //                         }
                            
    //                         break 2; // Break out of both loops once the user is found
    //                     }
    //                 }
    //             }
                
    //             if ($approval_column) {
    //                 $preceding_values['user_created_by'] = $read->created_by;
    //                 foreach ($preceding_values as $col_name => $value) {
    //                 $this->crud->create("notifications", [
    //                     "users_id_from" => $this->session->username,
    //                     "users_id_to" => $value,
    //                     "approvals_id" => $read_table_approvals->id,
    //                     "table_id" => $id,
    //                     "table_name" => $table_approval,
    //                     "name" => "Disapprove",
    //                     "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
    //                     "status" => 0,
    //                 ]);
    //                 }
    //                 echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    //             } else {
    //                 echo json_encode(array("title" => "Disapproved", "message" => "User not found in any approvals column.", "theme" => "error"));
    //             }
    //         } else {
    //             echo json_encode(array("title" => "Disapproved", "message" => "No records found.", "theme" => "error"));
    //         }
    //     }

    //     // echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    // }


    // public function disapproveall()
    // {
    //     $created_by = $this->input->post('created_by');
    //     $approved_to = $this->input->post('approved_to');
    //     $table_name = $this->input->post('table_name');
    //     $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

    //     /* Default */
    //     foreach ($datas as $data) {
    //         $send = $this->crud->delete($table_name, ["id" => $data->id]);
    //     }

    //     echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    // }

    // public function disapprove()
    // {
    //     $id = $this->input->post('id');
    //     $tablename = $this->input->post('tablename');

    //     /* Default */
    //     $send = $this->crud->delete($tablename, ["id" => $id]);
    //     echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    // }

    public function approvalCount()
    {
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        // $forecasts = $this->crud->reads('forecasts', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_fg = $this->crud->reads('stock_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_wip = $this->crud->reads('stock_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_so = $this->crud->reads('os_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_mpp = $this->crud->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $suppliers = $this->crud->reads('suppliers', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $supplier_items = $this->crud->reads('supplier_items', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $delivery_orders = $this->crud->reads('delivery_orders', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);

        $delivery_to_subconts = $this->crud->reads('delivery_to_subconts', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (count($users) + count($purchase_orders) + count($suppliers) + count($supplier_items) + count($purchase_requests) + count($delivery_notes) + count($delivery_to_subconts)); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        //Users
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        // $forecasts = $this->crud->reads('forecasts', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_fg = $this->crud->reads('stock_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_wip = $this->crud->reads('stock_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_so = $this->crud->reads('os_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_mpp = $this->crud->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $suppliers = $this->crud->reads('suppliers', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $supplier_items = $this->crud->reads('supplier_items', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);
        $delivery_orders = $this->crud->reads('delivery_orders', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);

        $delivery_to_subconts = $this->crud->reads('delivery_to_subconts', [], ["approved_to" => $this->session->username,"deleted"=>0], "", "", "", ["approved_to", "approved_by"]);

        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, "users");
        }

        // foreach ($forecasts as $forecast) {
        //     $this->approvalMessage($forecast->approved_by, $forecast->approved_to, $forecast->created_by, "forecasts");
        // }

        // foreach ($stock_fg as $fg) {
        //     $this->approvalMessage($fg->approved_by, $fg->approved_to, $fg->created_by, "stock_fg");
        // }

        // foreach ($stock_wip as $wip) {
        //     $this->approvalMessage($wip->approved_by, $wip->approved_to, $wip->created_by, "stock_wip");
        // }

        // foreach ($os_so as $so) {
        //     $this->approvalMessage($so->approved_by, $so->approved_to, $so->created_by, "os_so");
        // }

        // foreach ($os_mpp as $mpp) {
        //     $this->approvalMessage($mpp->approved_by, $mpp->approved_to, $mpp->created_by, "os_mpp");
        // }

        foreach ($purchase_orders as $po) {
            $this->approvalMessage($po->approved_by, $po->approved_to, "purchase_orders");
        }
        foreach ($suppliers as $supplier) {
            $this->approvalMessage($supplier->approved_by, $supplier->approved_to, "suppliers");
        }
        foreach ($supplier_items as $supplier_item) {
            $this->approvalMessage($supplier_item->approved_by, $supplier_item->approved_to, "supplier_items");
        }
        foreach ($purchase_requests as $purchase_request) {
            $this->approvalMessage($purchase_request->approved_by, $purchase_request->approved_to, "purchase_requests");
        }
        foreach ($delivery_notes as $delivery_note) {
            $this->approvalMessage($delivery_note->approved_by, $delivery_note->approved_to, "delivery_notes");
        }
        foreach ($delivery_orders as $delivery_order) {
            $this->approvalMessage($delivery_order->approved_by, $delivery_order->approved_to, "delivery_orders");
        }
        foreach ($delivery_to_subconts as $delivery_note) {
            $this->approvalMessage($delivery_note->approved_by, $delivery_note->approved_to, "delivery_to_subconts");
        }
    }

    public function approvalMessage($approved_by, $approved_to, $table)
    {
        $user = $this->crud->read('users', [], ["username" => $approved_by]);

        if (empty($user->avatar)) {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $user->avatar;
        }

        $link = "approvalDetail('$table', '$approved_to', '$approved_by')";
        echo '  <li class="list-isi">
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
                                    <b>' . $user->name . '</b><br>
                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $table)) . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }


    public function users($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "users";
            $this->load->view('template/header', $data);
            $this->load->view('approval/users');
        }
    }

    public function purchase_requests($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_requests";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_requests');
        }
    }

    public function purchase_orders($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_orders";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_orders');
        }
    }

    public function purchase_orders_2($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_orders";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_orders');
        }
    }

    public function delivery_notes($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "delivery_notes";
            $this->load->view('template/header', $data);
            $this->load->view('approval/delivery_notes');
        }
    }

    public function sales_invoices($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sales_invoices";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sales_invoices');
        }
    }

    public function supplier_items($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supplier_items";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supplier_items');
        }
    }

    public function supplier_items2($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supplier_items";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supplier_items');
        }
    }

    public function delivery_to_subconts($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "delivery_to_subconts";
            $this->load->view('template/header', $data);
            $this->load->view('approval/delivery_to_subconts');
        }
    }

    public function approvalUsers($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        
        $this->db->select('*');
        $this->db->from('users a');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('approved_by', $approved_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseOrders($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.id,a.po_no, a.request_no, a.total_dp,
            a.po_date,
            a.remarks,
            b.number as item_number,
            b.name as item_name,
            c.name as item_family_name, 
            d.name as supplier_name, 
            d.currency, e.mpq, 
            e.moq,
            b.uom,
            a.month_1,
            a.month_2,
            a.month_3,
            a.month_4,
            a.discount,
            d.currency, 
            a.qty,
            a.price,
            a.total,
            a.total_sub');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id', 'left');
                // $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseRequests($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.id, a.request_no, a.request_date, a.expected_date, a.request_name, a.division, 
            a.qty, 
            b.number as item_number, 
            b.name as item_name, 
            b.uom,  
            c.name as category_name');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        // $this->db->group_by('request_no');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalDeliveryNotes($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*, b.name as customer_name,
            e.delivery_order_no, 
            f.id as item_fg_id, 
            f.number as item_fg_number, 
            f.name as item_fg_name,
            c.customer_order_no, 
            c.sales_order_no,
            e.trans_type, 
            f.uom");
        $this->db->from('delivery_notes a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no', 'left');
        $this->db->join('delivery_orders e', 'a.delivery_order_no = e.delivery_order_no', 'left');
        $this->db->join('item_fg f', 'a.item_fg_id = f.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.deleted', 0);
        $this->db->group_by('a.id');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSalesInvoices($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as gl_no, b.name as customer_name, a.delivery_note_no, a.sales_order_no, a.customer_order_no');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('journal_postings c', 'a.number = c.document_no', 'left');
        $this->db->join('delivery_notes d', 'a.delivery_note_no = d.delivery_note_no and a.item_fg_id = d.item_fg_id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplierItems($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, b.currency, b.type, b.status, c.number as item_rm_number, c.name as item_rm_name');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->order_by('b.id', 'ASC');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalDeliveryOrders($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.name as customer_name');
        $this->db->from('delivery_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalDeliveryToSubconts($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*,
            b.id as item_fg_id, 
            b.number as item_fg_number, 
            b.name as item_fg_name,
            b.uom,
            COALESCE(c.name, d.name) as destination_name,
        ");
        $this->db->from('delivery_to_subconts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');

        $this->db->join('subconts c', 'a.destination = c.id', 'left');
        $this->db->join('teaching_factory d', 'a.destination = d.id', 'left');

        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.deleted', 0);
        $this->db->group_by(['a.id', 'a.item_fg_id', 'a.workorder']);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    // public function purchase_orders($approved_to, $approved_by){
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } else {
    //         $data['approved_to'] = base64_decode($approved_to);
    //         $data['approved_by'] = base64_decode($approved_by);
    //         $data['table'] = "purchase_orders";
    //         $this->load->view('template/header', $data);
    //         $this->load->view('approval/purchase_orders');
    //     }
    // }
    
    // public function approvalUsers($approved_to, $created_by)
    // {
    //     $this->db->select('*');
    //     $this->db->from('users a');
    //     $this->db->where('deleted',0);
    //     $this->db->where('approved_to', $approved_to);
    //     $this->db->where('created_by', $created_by);
    //     $this->db->order_by('created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalStockFg($approved_to, $created_by)
    // {
    //     $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
    //     $this->db->from('stock_fg a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->where('a.deleted',0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalStockWip($approved_to, $created_by)
    // {
    //     $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
    //     $this->db->from('stock_wip a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->where('a.deleted',0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalOsSo($approved_to, $created_by)
    // {
    //     $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
    //     $this->db->from('os_so a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->join('customers c', 'a.customer_id = c.id');
    //     $this->db->where('a.deleted',0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalOsMpp($approved_to, $created_by)
    // {
    //     $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
    //     $this->db->from('os_mpp a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->join('customers c', 'a.customer_id = c.id');
    //     $this->db->where('a.deleted',0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalForecasts($approved_to, $created_by)
    // {
    //     $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
    //     $this->db->from('forecasts a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->join('customers c', 'a.customer_id = c.id');
    //     $this->db->where('a.deleted',0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalPO($approved_to, $created_by)
    // {
    //     $this->db->select('a.id,a.po_no, a.request_no, a.total_dp,
    //                 a.po_date,
    //                 a.remarks,
    //                 b.number as item_number,
    //                 b.name as item_name,
    //                 c.name as item_family_name, 
    //                 d.name as supplier_name, 
    //                 d.currency,e.mpq, 
    //                 e.moq,
    //                 b.uom,
    //                 a.month_1,
    //                 a.month_2,
    //                 a.month_3,
    //                 a.discount,
    //                 a.qty, 
    //                 a.price, 
    //                 a.total,
    //                 a.total_sub');
    //     $this->db->from('purchase_orders a');
    //     $this->db->join('item_rm b', 'a.item_rm_id = b.id');
    //     $this->db->join('item_familys c', 'b.item_family_id = c.id');
    //     $this->db->join('suppliers d', 'a.supplier_id = d.id');
    //     $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
    //     $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
    //     $this->db->where('a.deleted',0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('a.created_date', 'DESC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    // public function approvalSuppliers($approved_to, $created_by)
    // {
    //     $this->db->select('*');
    //     $this->db->from('suppliers');
    //     $this->db->where('deleted', 0);
    //     $this->db->where('approved_to', $approved_to);
    //     $this->db->where('created_by', $created_by);
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }

    

    // public function approvalPurchaseRequests($approved_to, $created_by)
    // {

    //     $this->db->select('a.*, 
    //             b.number as item_number, 
    //             b.name as item_name, 
    //             b.uom, 
    //             d.po_no, 
    //             c.name as category_name');
    //     $this->db->from('purchase_requests a');
    //     $this->db->join('item_rm b', 'a.item_rm_id = b.id');
    //     $this->db->join('item_familys c', 'b.item_family_id = c.id');
    //     $this->db->join('purchase_orders d', 'a.request_no = d.request_no and a.item_rm_id = d.item_rm_id', 'left');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.approved_to', $approved_to);
    //     $this->db->where('a.created_by', $created_by);
    //     $this->db->order_by('b.number', 'ASC');
    //     $records = $this->db->get()->result_array();

    //     die(json_encode($records));
    // }
}
