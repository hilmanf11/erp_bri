<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'po_no',width:180,halign:'center',resizable:true">PO No</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status PO</th>
            <th rowspan="2" data-options="field:'status_pi',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Invoice</th>
            <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:130,halign:'center'">Approve Date</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Request No</th>
            <th rowspan="2" data-options="field:'po_date',width:100,align:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:100,align:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:200,halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatInteger">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatInteger">MOQ</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatInteger">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th rowspan="2" data-options="field:'discount',width:80,halign:'center',align:'right',formatter:numberformatDefault">Disc %</th>
            <th rowspan="2" data-options="field:'total',width:120,halign:'center',align:'right',formatter:numberformat">Total Price</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th rowspan="2" data-options="field:'remarks',width:100,halign:'center'">Remarks</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center'"> Forecast</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'month_1',width:100,halign:'center'">Month 1</th>
            <th data-options="field:'month_2',width:100,halign:'center'">Month 2</th>
            <th data-options="field:'month_3',width:100,halign:'center'">Month 3</th>
            <th data-options="field:'month_4',width:100,halign:'center'">Month 4</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_suppliers" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Po No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSED</option>
                        <option value="2">COMPLETE</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print_po()"><i class="fa fa-print"></i> Purchase Order</a>
                    <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="complete_po()"><i class="fa fa-check"></i> Complete</a> -->
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="complete_po()"><i class="fa fa-check"></i> Complete</a>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Convert Purchase Request to Purchase Order" data-options="closed: true,modal:true" style="width: 100%; height: 100%; padding:10px; top: 0; left: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:60%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO Period</span>
                <input style="width:28%;" name="po_date" id="po_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PR No</span>
                <input style="width:60%;" name="request_no" id="request_no" required class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
            </div>
        </fieldset>

        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Request Data" data-options="fitColumns: false, rownumbers: true" idField="item_number">
        </table>

        <div id="frm_calculate" style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: right; margin-top: 10px;">
                    <!-- <a style="width: 100%;" class="easyui-linkbutton c2" onclick="calculate()">Calculate</a> -->
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%; text-align:right;" id="total_sub" name="total_sub" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DISC %</b>
                        <input style="width:10%;" id="disc_pr" name="disc_pr" value="0" class="easyui-numberbox">
                        <input style="width:50%; text-align:right;" id="discount_total" name="discount_total" readonly class="easyui-numberbox" readonly value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">VAT</b>
                        <input style="width:60%; text-align:right;" id="total_vat" name="total_vat" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">INCOME TAX</b>
                        <input style="width:10%;" id="income_tax" name="income_tax" value="0" class="easyui-numberbox">
                        <input style="width:50%; text-align:right;" id="income_total" name="income_total" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DOWN PAYMENT</b>
                        <input style="width:60%; text-align:right;" id="total_dp" name="total_dp" required class="easyui-numberbox" value="0" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%; text-align:right;" id="total_grand" name="total_grand" class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- UPDATE SIGNATURE -->
<div id="dlg_approval" class="easyui-dialog" title="Edit Signature" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_approval" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Approved By</span>
                <input style="width:60%;" name="po_approved" id="po_approved" value="<?= $approval->po_approved ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Checked By</span>
                <input style="width:60%;" name="po_checked" id="po_checked" value="<?= $approval->po_checked ?>" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Prepared By</span>
                <input style="width:60%;" name="po_prepared" id="po_prepared" value="<?= $approval->po_prepared ?>" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_orders/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $.messager.prompt('Convert PR to PO', 'Please input Password to Convert', function(r) {
            if (r) {
                var encodedPassword = window.btoa(r);
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('purchase/purchase_orders/checkPassword') ?>', // Ganti dengan URL endpoint Anda
                    data: {
                        password: encodedPassword
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            // Buka dialog setelah sedikit penundaan untuk memastikan loading ditampilkan dengan benar
                            setTimeout(() => {
                                $('#dlg_insert').dialog('open');
                                $('#frm_calculate').hide();
                                $("#btnPreview").linkbutton('enable');
                                $('#dg_request').datagrid('loadData', []);
                                $("#request_no").combobox({
                                    url: '<?= base_url('purchase/purchase_requests/readRequestno') ?>',
                                    valueField: 'request_no',
                                    textField: 'request_no',
                                    prompt: "Select Purchase Request No",
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                                        }
                                    }]
                                });

                                // Menghilangkan loading setelah dialog terbuka
                                Swal.close();
                            }, 500);
                        } else {
                            toastr.warning("Please Input Correct Password!", "Information");
                        }
                    },
                    error: function() {
                        toastr.error("There was an error processing your request.", "Error");
                    }
                });
            }
        });

        // Menggunakan setTimeout untuk menunggu elemen input dibuat oleh $.messager.prompt
        setTimeout(function() {
            var inputField = $('.messager-input');
            inputField.attr('type', 'password');
        }, 100);
    }




    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            console.log(row);
            if (row.datatable == "1") {

                if (row.is_all_closed == 1) {
                    toastr.error("This purchase order cannot be updated because all items are already closed.");
                    return;
                }

                if (row.status_pi == "0" || row.status_pi == null) {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    $('#frm_calculate').show();
                    $("#btnPreview").linkbutton('disable');

                    preview('<?= base_url('purchase/purchase_orders/datatable_updates') ?>?po_no=' + btoa(row.po_no));
                } else {
                    toastr.error("You cannot update this data, because status PI in POR is closed");
                }
            } else {
                toastr.error("Please Select Header of PO <br>" + row.po_no);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }


    function preview(url = "") {
        var request_no = $("#request_no").combobox('getValue');
        var po_date = $("#po_date").datebox('getValue');

        if (url == "") {
            var url = '<?= base_url('purchase/purchase_requests/reads') ?>?request_no=' + request_no;
        }

        if (request_no == "") {
            toastr.warning('Please select Purchase Request No', 'Required');
        } else {
            var lastIndex;

            $.ajax({
                type: "post",
                url: "<?= base_url('purchase/purchase_orders/readPeriodLists/') ?>",
                data: "po_date=" + po_date,
                dataType: "json",
                success: function(result) {
                    $('#dg_request').datagrid({
                        singleSelect: true,
                        url: url,
                        columns: [
                            [{
                                field: 'action',
                                width: 80,
                                halign: 'center',
                                title: "Action",
                                formatter: buttonEdit
                            }, {
                                field: 'item_number',
                                width: 150,
                                readonly: true,
                                halign: 'center',
                                title: "Part No",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {
                                field: 'po_no',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "PO No",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {
                                field: 'item_name',
                                width: 200,
                                readonly: true,
                                halign: 'center',
                                title: "Part Name"
                            }, {
                                field: 'category_name',
                                width: 150,
                                readonly: true,
                                halign: 'center',
                                title: "Product <br>Family"
                            }, {
                                field: 'uom',
                                width: 80,
                                readonly: true,
                                halign: 'center',
                                title: "UOM"
                            }, {
                                field: 'supplier_name',
                                width: 250,
                                halign: 'center',
                                title: "Supplier",
                                editor: {
                                    type: 'combogrid'
                                }
                            }, {
                                field: 'supplier_id',
                                hidden: true,
                                width: 250,
                                halign: 'center',
                                title: "Supplier Id",
                                editor: {
                                    type: 'textbox',
                                }
                            }, {
                                field: 'mpq',
                                width: 80,
                                halign: 'center',
                                title: "MPQ",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        required: true,
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }, {
                                field: 'moq',
                                width: 80,
                                halign: 'center',
                                title: "MOQ",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        required: true,
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }, {
                                field: 'qty',
                                width: 80,
                                halign: 'center',
                                title: "Qty",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        precision: 2
                                    }
                                }
                            }, {
                                field: 'currency',
                                width: 80,
                                halign: 'center',
                                title: "Currency",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true,
                                    }
                                }
                                // }, {
                                //     field: 'discount',
                                //     width: 80,
                                //     halign: 'center',
                                //     title: "Disc %",
                                //     editor: {
                                //         type: 'numberbox',
                                //     }
                            }, {
                                field: 'price',
                                width: 100,
                                halign: 'center',
                                align: 'right',
                                title: "Price",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        formatter: numberformats,
                                        readonly: true,
                                    }
                                }
                            }, {
                                field: 'total',
                                width: 100,
                                halign: 'center',
                                align: 'right',
                                title: "Amount",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        formatter: numberformats,
                                        readonly: true,
                                        precision: 2
                                    }
                                }
                            }, {
                                field: 'expected_date',
                                width: 120,
                                halign: 'center',
                                title: "ETA",
                                editor: {
                                    type: 'datebox',
                                    options: {
                                        formatter: myformatter,
                                        parser: myparser,
                                        editable: true,
                                        // required: true
                                    }
                                }
                            }, {
                                field: 'remarks',
                                width: 200,
                                halign: 'center',
                                title: "Remarks",
                                editor: {
                                    type: 'textbox'
                                }
                            }, {
                                field: 'month_1',
                                width: 80,
                                align: 'center',
                                title: result[0].name,
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        required: true,
                                    }
                                }
                            }, {
                                field: 'month_2',
                                width: 80,
                                align: 'center',
                                title: result[1].name,
                                editor: {
                                    type: 'numberbox',
                                }
                            }, {
                                field: 'month_3',
                                width: 80,
                                align: 'center',
                                title: result[2].name,
                                editor: {
                                    type: 'numberbox',
                                }
                            }, {
                                field: 'month_4',
                                width: 80,
                                align: 'center',
                                title: result[3].name,
                                editor: {
                                    type: 'numberbox',
                                }
                            }, {
                                field: 'revision',
                                width: 80,
                                align: 'center',
                                title: 'Revision',
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        readonly: true,
                                    }
                                }
                            }, {
                                field: 'item_rm_id',
                                width: 150,
                                hidden: true,
                                halign: 'center',
                                title: "Part ID",
                                editor: {
                                    type: 'textbox',
                                }
                            }]
                        ],

                        // onBeforeEdit: function(index, row) {
                        //     row.remark_revision = '';

                        //     if (row.status == 1) {
                        //         return false;
                        //     }

                        //     row.editing = true;

                        //     if (row.po_no && row.approved_to == "") {
                        //         row._old_qty = row.qty;
                        //         row._old_expected_date = row.expected_date;
                        //     }

                        //     $(this).datagrid('refreshRow', index);
                        // },

                        // onAfterEdit: function(index, row) {
                        //     row.editing = false;

                        //     if (row.po_no && row.approved_to == "") {
                        //         // update phase 1
                        //         var oldQty = parseFloat(row._old_qty) || 0;
                        //         var newQty = parseFloat(row.qty) || 0;

                        //         if (oldQty !== newQty) {
                        //             row.revision = Number(row.revision || 0) + 1;
                        //             row.remark_revision = "Qty"
                        //         }
                        //     } else if(row.po_no && row.approved_to != "" && row.revision != 0) {
                        //         // update phase 2
                        //         var oldQty = parseFloat(row._old_qty) || 0;
                        //         var newQty = parseFloat(row.qty) || 0;

                        //         if (oldQty !== newQty) {
                        //             row.revision = Number(row.revision || 0);
                        //             row.remark_revision = "Qty"
                        //         }
                        //     } else {
                        //         // add
                        //         row.revision = 0;
                        //     }

                        //     $(this).datagrid('refreshRow', index);
                        // },

                        onBeforeEdit: function(index, row) {
                            // row.remark_revision = '';
                            if (!row.remark_revision) {
                                row.remark_revision = '';
                            }

                            if (row.status == 1) {
                                return false;
                            }

                            row.editing = true;

                            // if (row.po_no && row.approved_to == "") {

                            // set baseline phase 1 & 2
                            if (row._base_qty === undefined) {
                                row._base_qty = row.qty;
                            }

                            if (row._base_expected_date === undefined) {
                                row._base_expected_date = row.expected_date;
                            }

                            row._old_qty = row.qty;
                            row._old_expected_date = row.expected_date;
                            
                            // }

                            $(this).datagrid('refreshRow', index);
                        },

                        onAfterEdit: function(index, row) {
                            row.editing = false;

                            var oldQty = row._base_qty !== undefined ? parseFloat(row._base_qty) : parseFloat(row.qty);
                            var newQty = parseFloat(row.qty) || 0;

                            var oldDate = row._base_expected_date !== undefined ? row._base_expected_date : row.expected_date;
                            var newDate = row.expected_date || '';

                            let isQtyChanged = oldQty !== newQty;
                            let isDateChanged = oldDate !== newDate;

                            let remarks = [];
                            
                            // if (isQtyChanged) {
                            //     remarks.push(`Change Qty from ${oldQty} to ${newQty}`);
                            // }

                            // if (isDateChanged) {
                            //     remarks.push(`Change ETA from ${oldDate} to ${newDate}`);
                            // }


                            if (isQtyChanged) {
                                row.remark_revision = upsertRemark(row.remark_revision, `Change Qty from ${oldQty} to ${newQty}`, 'Qty');
                            }

                            if (isDateChanged) {
                                row.remark_revision = upsertRemark(row.remark_revision, `Change ETA from ${oldDate} to ${newDate}`, 'ETA');
                            }

                            if (row.po_no && row.approved_to == "") {
                                // phase 1
                                if (isQtyChanged || isDateChanged) {

                                    if (!row._is_revised) {
                                        row.revision = Number(row.revision || 0) + 1;
                                        row._is_revised = true;
                                    }

                                    // row.remark_revision = remarks.join(", ");
                                    // row.remark_revision = (row.remark_revision ? row.remark_revision + ', ' : '') + remarks.join(", ");
                                }

                            } else if (row.po_no && row.approved_to != "" && row.revision != 0) {
                                // phase 2
                                if (isQtyChanged || isDateChanged) {
                                    row.revision = Number(row.revision || 0);
                                    // row.remark_revision = remarks.join(", ");
                                    // row.remark_revision = (row.remark_revision ? row.remark_revision + ', ' : '') + remarks.join(", ");
                                }

                            } else {
                                // add
                                row.revision = 0;
                            }

                            $(this).datagrid('refreshRow', index);
                        },

                        onCancelEdit: function(index, row) {
                            row.editing = false;
                            $(this).datagrid('refreshRow', index);
                        },
                        onBeginEdit: function(rowIndex, row) {
                            var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                            editors.forEach(function(editor) {
                                var field = editor.field;
                                var po_date = $("#po_date").datebox('getValue');
                                var total_sub = $("#total_sub").numberbox('getValue');
                                // if (editor.field === 'delivery_date') {
                                //     $(editor.target).datebox('setValue', row.expected_date);
                                // }
                                if (editor.field === 'qty') {
                                    $(editor.target).textbox({
                                        onChange: function(newValue) {
                                            var qty = $(editors.find(e => e.field === 'qty').target).numberbox('getValue') || 0;
                                            var discount = $(editors.find(e => e.field === 'discount').target).numberbox('getValue') || 0;
                                            var price = $(editors.find(e => e.field === 'price').target).numberbox('getValue') || 0;

                                            // Calculate total
                                            var total = ((qty * price) - ((qty * price) * (discount / 100)));

                                            // Set total value
                                            var totalEditor = editors.find(e => e.field === 'total');
                                            if (totalEditor) {
                                                $(totalEditor.target).numberbox('setValue', total);
                                            }
                                        }
                                    });
                                }
                                if (editor.field === 'discount') {
                                    $(editor.target).textbox({
                                        onChange: function(newValue) {
                                            var qty = $(editors.find(e => e.field === 'qty').target).numberbox('getValue') || 0;
                                            var discount = $(editors.find(e => e.field === 'discount').target).numberbox('getValue') || 0;
                                            var price = $(editors.find(e => e.field === 'price').target).numberbox('getValue') || 0;

                                            // Calculate total
                                            var total = ((qty * price) - ((qty * price) * (discount / 100)));

                                            // Set total value
                                            var totalEditor = editors.find(e => e.field === 'total');
                                            if (totalEditor) {
                                                $(totalEditor.target).numberbox('setValue', total);
                                            }
                                        }
                                    });
                                }
                                if (editor.field === 'supplier_name') {
                                    var $combogrid = $(editor.target);
                                    $combogrid.combogrid({
                                        url: '<?= base_url('master/supplier_items/readSuppliers?item_rm_id=') ?>' + $(editors.find(e => e.field === 'item_rm_id').target).textbox('getValue'),
                                        required: true,
                                        panelWidth: 400,
                                        idField: 'name',
                                        textField: 'name',
                                        mode: 'remote',
                                        fitColumns: true,
                                        prompt: 'Choose Supplier',
                                        columns: [
                                            [{
                                                    field: 'number',
                                                    title: 'Supplier No',
                                                    width: 100
                                                },
                                                {
                                                    field: 'name',
                                                    title: 'Supplier Name',
                                                    width: 250
                                                }
                                            ]
                                        ],
                                        onLoadSuccess: function(data) {
                                            var row = data.rows[0];
                                            var supplierValue = row.name;
                                            if (supplierValue) {
                                                $combogrid.combogrid('setValue', supplierValue);
                                            }
                                            if (row && row.share_order === "100") {
                                                var editors = $('#dg_request').datagrid('getEditors', index);

                                                var supplierValue = row.name;
                                                if (supplierValue) {
                                                    $combogrid.combogrid('setValue', supplierValue);
                                                }

                                                var supplier_id = editors.find(e => e.field === 'supplier_id');
                                                if (supplier_id) {
                                                    $(supplier_id.target).textbox('setValue', row.id);
                                                }
                                                var mpq = editors.find(e => e.field === 'mpq');
                                                if (mpq) {
                                                    $(mpq.target).textbox('setValue', row.mpq);
                                                }
                                                var moq = editors.find(e => e.field === 'moq');
                                                if (moq) {
                                                    $(moq.target).textbox('setValue', row.moq);
                                                }
                                                var currency = editors.find(e => e.field === 'currency');
                                                if (currency) {
                                                    $(currency.target).textbox('setValue', row.currency);
                                                }
                                                var discount = editors.find(e => e.field === 'discount');
                                                if (discount) {
                                                    $(discount.target).textbox('setValue', 0);
                                                }
                                                var price = editors.find(e => e.field === 'price');
                                                if (price) {
                                                    $(price.target).textbox('setValue', row.price);
                                                }

                                                var qty = parseFloat($(editors.find(e => e.field === 'qty').target).textbox('getValue')) || 0;
                                                var price = parseFloat($(editors.find(e => e.field === 'price').target).textbox('getValue')) || 0;
                                                var discount = parseFloat($(editors.find(e => e.field === 'discount').target).textbox('getValue')) || 0;

                                                var totalDiscountedPrice = (qty * price) - ((qty * price) * (discount / 100));
                                                var totalEditor = editors.find(e => e.field === 'total');
                                                if (totalEditor) {
                                                    $(totalEditor.target).numberbox('setValue', totalDiscountedPrice);
                                                }
                                            } else {
                                                toastr.warning("Please Input Part No in Supplier Items");
                                            }
                                        },
                                        onSelect: function(index, row) {
                                            var editors = $('#dg_request').datagrid('getEditors', index);
                                            var supplier_id = editors.find(e => e.field === 'supplier_id');
                                            if (supplier_id) {
                                                $(supplier_id.target).textbox('setValue', row.id);
                                            }
                                            var mpq = editors.find(e => e.field === 'mpq');
                                            if (mpq) {
                                                $(mpq.target).textbox('setValue', row.mpq);
                                            }
                                            var moq = editors.find(e => e.field === 'moq');
                                            if (moq) {
                                                $(moq.target).textbox('setValue', row.moq);
                                            }
                                            var currency = editors.find(e => e.field === 'currency');
                                            if (currency) {
                                                $(currency.target).textbox('setValue', row.currency);
                                            }
                                            var discount = editors.find(e => e.field === 'discount');
                                            if (discount) {
                                                $(discount.target).textbox('setValue', 0);
                                            }
                                            var price = editors.find(e => e.field === 'price');
                                            if (price) {
                                                $(price.target).textbox('setValue', row.price);
                                            }

                                            var qty = parseFloat($(editors.find(e => e.field === 'qty').target).textbox('getValue')) || 0;
                                            var price = parseFloat($(editors.find(e => e.field === 'price').target).textbox('getValue')) || 0;
                                            var discount = parseFloat($(editors.find(e => e.field === 'discount').target).textbox('getValue')) || 0;

                                            var totalDiscountedPrice = (qty * price) - ((qty * price) * (discount / 100));
                                            var totalEditor = editors.find(e => e.field === 'total');
                                            if (totalEditor) {
                                                $(totalEditor.target).numberbox('setValue', totalDiscountedPrice);
                                            }
                                        }
                                    });
                                }
                                if (editor.field === 'expected_date') {
                                    $(editor.target).datebox({
                                        onChange: function() {
                                            var f_delivery_date = $(editors.find(e => e.field === 'expected_date').target).datebox('getValue');
                                            if (f_delivery_date < po_date) {
                                                $(editor.target).datebox('clear');
                                                toastr.warning("PO Date > Expected Date");
                                            }
                                        }
                                    });
                                    var expectedDate = row.expected_date;
                                    if (expectedDate) {
                                        $(editor.target).datebox('setValue', expectedDate);
                                    }
                                }
                            });
                        },
                        onLoadSuccess: function() {
                            var rows = $('#dg_request').datagrid('getRows');
                            endEditing();
                            var totalrows = rows.length;

                            if (totalrows > 0) {
                                var total_subs = 0;
                                for (let i = 0; i < totalrows; i++) {
                                    total_subs += parseFloat(rows[i].total);
                                }

                                $("#total_sub").numberbox('setValue', total_subs);
                                calculateTotal(total_subs);

                                $("#disc_pr").numberbox({
                                    onChange: function() {
                                        var disc_pr = $("#disc_pr").numberbox('getValue');
                                        var income_tax = $("#income_tax").numberbox('getValue');
                                        var total_dp = $("#total_dp").numberbox('getValue');

                                        calculateTotal(total_subs, disc_pr, income_tax, total_dp);
                                    }
                                });

                                $("#income_tax").numberbox({
                                    onChange: function() {
                                        var disc_pr = $("#disc_pr").numberbox('getValue');
                                        var income_tax = $("#income_tax").numberbox('getValue');
                                        var total_dp = $("#total_dp").numberbox('getValue');

                                        calculateTotal(total_subs, disc_pr, income_tax, total_dp);
                                    }
                                });

                                $("#total_dp").numberbox({
                                    onChange: function() {
                                        var disc_pr = $("#disc_pr").numberbox('getValue');
                                        var income_tax = $("#income_tax").numberbox('getValue');
                                        var total_dp = $("#total_dp").numberbox('getValue');

                                        calculateTotal(total_subs, disc_pr, income_tax, total_dp);
                                    }
                                });

                            } else {
                                toastr.error("Data in Sales order List Empty");
                            }
                        }
                    });
                }
            });
        }
    }

    function calculateTotal(total_subs, disc_pr = 0, income_tax = 0, total_dp = 0) {
        var discount_total = (total_subs * (disc_pr / 100));
        $("#discount_total").numberbox('setValue', discount_total);

        $.ajax({
            type: "post",
            url: "<?= base_url('admin/config/read') ?>",
            dataType: "json",
            success: function(config) {
                var taxes = config.tax;
                var total_vat = Math.floor((total_subs - discount_total) * (taxes / 100));
                $("#total_vat").numberbox('setValue', total_vat);

                var income_total = ((total_subs - discount_total) * (income_tax / 100));
                $("#income_total").numberbox('setValue', income_total);

                var total_grand = ((total_subs - discount_total) + total_vat - income_total - total_dp);
                $("#total_grand").numberbox('setValue', total_grand);
            }
        });
    }

    function upsertRemark(existing, newText, keyword) {
        if (!existing) return newText;

        let parts = existing.split(', ').filter(r => !r.includes(keyword));
        parts.push(newText);
        return parts.join(', ');
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg_request').datagrid('selectRow', getRowIndex(target));
        $('#dg_request').datagrid('beginEdit', getRowIndex(target));
    }

    function saverow(target) {
        $('#dg_request').datagrid('endEdit', getRowIndex(target));
    }

    function changePrice(target) {
        var editors = $('#dg_request').datagrid('getEditors', getRowIndex(target));
        var rows = $('#dg_request').datagrid('getRows');

        var item_rm_id = rows[getRowIndex(target)].item_rm_id; //item_number
        var supplier_id = rows[getRowIndex(target)].supplier_id;

        $.ajax({
            type: "post",
            url: "<?= base_url('master/supplier_items/readItem') ?>",
            data: "supplier_id=" + supplier_id + "&item_rm_id=" + item_rm_id, //item_number
            dataType: "json",
            success: function(json) {
                toastr.success("Price Changed!");
                $(editors[1].target).textbox('setValue', json.price);
            }
        });
    }

    //Add Signature
    function signatures() {
        $('#dlg_approval').dialog('open');
    }

    function readPo() {
        $("#filter_suppliers").combobox({
            url: '<?= base_url('master/suppliers/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(supp) {
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_orders/readPono?supplier_id=') ?>' + supp.id,
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Select Purchase Order No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $("#filter_po_no").combobox({
            url: '<?= base_url('purchase/purchase_orders/readPonos/') ?>',
            valueField: 'po_no',
            textField: 'po_no',
            prompt: "Select Purchase Order No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $("#filter_product_no").combogrid({
            url: '<?= base_url('purchase/purchase_orders/readItems') ?>',
            idField: 'item_rm_id',
            textField: 'item_number',
            panelWidth: 400,
            mode: 'remote',
            filter: function(q, row){
                var opts = $(this).combogrid('options');
                return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) >= 0 || 
                       row['item_name'].toLowerCase().indexOf(q.toLowerCase()) >= 0;
            },
            prompt: "Select Part No",
            columns: [[
                {field:'item_number',title:'Part No',width:150},
                {field:'item_name',title:'Part Name',width:250}
            ]],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }]
        });
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.datatable == "1") {
                            toastr.error("Please Select Detail of PO <br>" + row.po_no);
                        } else {
                            if (row.status == "0") {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('purchase/purchase_orders/delete') ?>',
                                    data: {
                                        id: row.id,
                                        request_no: row.request_no,
                                        item_rm_id: row.item_rm_id
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        toastr.success(result.message);
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').treegrid('reload');
                                    }
                                });
                            } else {
                                toastr.error("You cannot update this data, because status PO is closed");
                            }
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_suppliers=" + filter_suppliers + "&filter_status=" + filter_status + "&filter_product_no=" + filter_product_no;
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_orders/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_orders/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_suppliers = $("#filter_suppliers").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_suppliers=" + filter_suppliers + "&filter_status=" + filter_status + "&filter_product_no=" + filter_product_no;
        window.location.assign('<?= base_url('purchase/purchase_orders/print/excel') ?>' + url);
    }

    function complete_po() {
        var rows = $('#dg').treegrid('getSelections');

        if (rows.length > 0) {
            // $.messager.confirm('Warning', 'Are you sure you want to completed this data?', function(r) {
            //     if (r) {
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row.datatable == "1") {
                    toastr.error("Please Select Detail of PO <br>" + row.po_no);
                } else {
                    if (row.status == "0") {
                        Swal.fire({
                            title: "Are you sure?",
                            text: "You want to Complete this data!",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Yes",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('purchase/purchase_orders/completePo') ?>',
                                    data: {
                                        id: row.id,
                                    },
                                    success: function(result) {
                                        var result = eval('(' + result + ')');
                                        toastr.success(result.message);
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        toastr.error(jqXHR.statusText);
                                        $.messager.alert("Error", jqXHR.statusText, 'error');
                                    },
                                    complete: function(data) {
                                        $('#dg').treegrid('reload');
                                    }
                                });
                            }
                        });
                    } else {
                        toastr.error("this data has been Completed");
                        // Swal.fire({
                        //     title: "Are you sure?",
                        //     text: "You want to Unompleted this data!",
                        //     icon: "warning",
                        //     showCancelButton: true,
                        //     confirmButtonColor: "#3085d6",
                        //     cancelButtonColor: "#d33",
                        //     confirmButtonText: "Yes",
                        // }).then((result) => {
                        //     if (result.isConfirmed) {
                        //         $.ajax({
                        //             method: 'post',
                        //             url: '<?= base_url('purchase/purchase_orders/uncompletePo') ?>',
                        //             data: {
                        //                 id: row.id,
                        //             },
                        //             success: function(result) {
                        //                 var result = eval('(' + result + ')');
                        //                 toastr.success(result.message);
                        //             },
                        //             error: function(jqXHR, textStatus, errorThrown) {
                        //                 toastr.error(jqXHR.statusText);
                        //                 $.messager.alert("Error", jqXHR.statusText, 'error');
                        //             },
                        //             complete: function(data) {
                        //                 $('#dg').treegrid('reload');
                        //             }
                        //         });
                        //     }
                        // });
                    }
                }
            }
            //     }
            // });
        }
    }

    function print_po() {
        var po_no = $("#filter_po_no").combogrid('getValue');
        console.log(po_no);
        if (po_no == "") {
            toastr.warning("Please select Purchase Order No!", "Information");
        } else {
            $.ajax({
                type: "POST",
                url: "<?= base_url('purchase/purchase_orders/checkTotalSub') ?>",
                data: {
                    po_no: po_no
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    // Hilangkan validasi tetapi tetap lanjutkan proses cetak
                    // if (response.total_sub == 0) {
                    //     toastr.warning("Total Sub , VAT and Grand Total is 0 for selected PO no, Please Update first for Calculated", "Information");
                    // }

                    if (response.has_unapproved) {
                        toastr.warning("There are still unapproved items, please approve all items first.", "Warning");
                        return;
                    }

                    // Menentukan jenis cetak berdasarkan jenis po_no
                    var printUrl = "";
                    if (po_no.includes("-A")) { // Jika po_no mengandung '-A08'
                        printUrl = "<?= base_url('purchase/purchase_orders/print_po_additional/') ?>" + window.btoa(po_no);
                    } else { // Jika tidak, gunakan fungsi cetak standar
                        printUrl = "<?= base_url('purchase/purchase_orders/print_po/') ?>" + window.btoa(po_no);
                    }
                    window.open(printUrl, "_blank");
                },
                error: function() {
                    toastr.error("An error occurred while checking Total Sub for selected Purchase Order!", "Error");
                }
            });
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        readPo();
        $("#add").html("Convert PR to PO");

        $("#delivery_date").datebox({
            onChange: function() {
                var po_date = $("#po_date").datebox('getValue');
                var delivery_date = $("#delivery_date").datebox('getValue');
                if (delivery_date < po_date) {
                    $("#delivery_date").datebox('clear');
                    toastr.warning("Po Date > Delivery Date");
                }
            }
        });
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_orders/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'po_no',
            singleSelect: false,
            fit: true,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;font-weight:bold;';
            //     }
            // },
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var po_date = $("#po_date").datebox('getValue');
                    if (po_date == "") {
                        toastr.warning('Please select Po Date', 'Required');
                    } else {
                        var rows = $('#dg_request').datagrid('getRows');
                        var totalrows = rows.length;

                        var inEditMode = false;
                        for (var i = 0; i < totalrows; i++) {
                            if (rows[i].editing) {
                                inEditMode = true;
                                break;
                            }
                        }

                        if (inEditMode) {
                            toastr.warning("Please save all edited rows before next Process!", "Information");
                        } else {
                            // endEditing();
                            if (totalrows > 0) {
                                $.messager.confirm('Warning', 'Are you sure you want Process this Data?', function(r) {
                                    if (r) {
                                        // Menyimpan hasil dari setiap operasi dalam array
                                        var results = [];

                                        for (var i = 0; i < totalrows; i++) {
                                            var row = rows[i];

                                            var item_rm_id = row.item_rm_id; //item_number
                                            var po_no = row.po_no;
                                            var supplier_id = row.supplier_id;
                                            var qty = row.qty;
                                            // var discount = row.discount;
                                            // var price = row.price;
                                            // var total = row.total;
                                            var delivery_date = row.expected_date;
                                            var remarks = row.remarks;
                                            var month_1 = row.month_1;
                                            var month_2 = row.month_2;
                                            var month_3 = row.month_3;
                                            var month_4 = row.month_4;
                                            var revision = row.revision;
                                            var remark_revision = '';

                                            // var total_sub = $("#total_sub").numberbox('getValue');
                                            // var disc_pr = $("#disc_pr").numberbox('getValue');
                                            // var discount_total = $("#discount_total").numberbox('getValue');
                                            // var total_vat = $("#total_vat").numberbox('getValue');
                                            // var income_tax = $("#income_tax").numberbox('getValue');
                                            // var income_total = $("#income_total").numberbox('getValue');
                                            // var total_dp = $("#total_dp").numberbox('getValue');
                                            // var total_grand = $("#total_grand").numberbox('getValue');


                                            if (po_no == "") {
                                                var url_save = "<?= base_url('purchase/purchase_orders/create') ?>";

                                                var discount = 0;
                                                var price = 0;
                                                var total = 0;

                                                var total_sub = 0;
                                                var disc_pr = 0;
                                                var discount_total = 0;
                                                var total_vat = 0;
                                                var income_tax = 0;
                                                var income_total = 0;
                                                var total_dp = 0;
                                                var total_grand = 0;

                                            } else {

                                                var url_save = "<?= base_url('purchase/purchase_orders/update') ?>";

                                                var discount = row.discount;
                                                var price = row.price;
                                                var total = row.total;

                                                var total_sub = $("#total_sub").numberbox('getValue');
                                                var disc_pr = $("#disc_pr").numberbox('getValue');
                                                var discount_total = $("#discount_total").numberbox('getValue');
                                                var total_vat = $("#total_vat").numberbox('getValue');
                                                var income_tax = $("#income_tax").numberbox('getValue');
                                                var income_total = $("#income_total").numberbox('getValue');
                                                var total_dp = $("#total_dp").numberbox('getValue');
                                                var total_grand = $("#total_grand").numberbox('getValue');

                                                remark_revision = row.remark_revision || '';
                                            }

                                            $.ajax({
                                                type: "post",
                                                url: url_save,
                                                data: 'item_rm_id=' + item_rm_id +
                                                    '&po_no=' + po_no +
                                                    '&supplier_id=' + supplier_id +
                                                    '&request_no=' + row.request_no +
                                                    '&request_date=' + row.request_date +
                                                    '&request_name=' + row.request_name +
                                                    '&po_date=' + po_date +
                                                    '&qty=' + qty +
                                                    '&discount=' + discount +
                                                    '&price=' + price +
                                                    '&total=' + total +
                                                    '&delivery_date=' + delivery_date +
                                                    '&remarks=' + remarks +
                                                    '&revision=' + revision +
                                                    '&remark_revision=' + remark_revision +
                                                    '&month_1=' + month_1 +
                                                    '&month_2=' + month_2 +
                                                    '&month_3=' + month_3 +
                                                    '&month_4=' + month_4 +
                                                    '&total_sub=' + total_sub +
                                                    '&disc_pr=' + disc_pr +
                                                    '&total_vat=' + total_vat +
                                                    '&income_tax=' + income_tax +
                                                    '&income_total=' + income_total +
                                                    '&total_grand=' + total_grand +
                                                    '&total_dp=' + total_dp +
                                                    '&discount_total=' + discount_total,
                                                dataType: "json",
                                                success: function(result) {
                                                    results.push(result);
                                                },
                                                complete: function() {
                                                    // Cek jika semua request telah selesai
                                                    if (results.length === totalrows) {
                                                        // Menampilkan satu Swal.fire untuk semua hasil
                                                        Swal.fire({
                                                            title: 'Data Saved Successfully',
                                                            icon: 'success',
                                                            confirmButtonText: 'Ok',
                                                            allowOutsideClick: false
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                window.location.reload();
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                        // setTimeout(window.open("<?= base_url('purchase/purchase_orders/print_po/') ?>" + window.btoa(po_no), "_blank"), 3000);
                                        readPo();
                                        $('#dg').treegrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    }
                                });
                            } else {
                                toastr.warning("Please select one of the data in the table first!", "Information");
                            }
                        }
                    }
                }
            }]
        });

        //Update Data
        $('#dlg_approval').dialog({
            buttons: [{
                text: 'Update Data',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_approval').form('submit', {
                        url: '<?= base_url('purchase/purchase_orders/update_approval') ?>',
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_approval').dialog('close');
                        }
                    });
                }
            }]
        });
    });

    function buttonEdit(value, row, index) {
        if (row.status == 1) {
            return '<a href="javascript:void(0)" class="btn btn-danger btn-sm w-100" style="pointer-events:none; opacity:0.5;">Closed</a>';
        }

        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a>';
            return s;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a>';
            return e;
        }
    }

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }
    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }
    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function numberformatDefault(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformats(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return formatter.format(value);
        }
    }

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else if (value == 2) {
            return "<b style='color:white;'>COMPLETE</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else if (value == 2) {
            return 'background-color:#4B54E7;';
        }
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    }

    function numberformatInteger(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0, // Tidak menampilkan desimal
                maximumFractionDigits: 0 // Tidak menampilkan desimal
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    };
</script>