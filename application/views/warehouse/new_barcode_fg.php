<style>
.swal2-deny-custom {
    background-color: #4CAF50 !important;
    color: white !important;
}

.swal2-cancel-custom {
    /* background-color: #FABC3F !important; */
    background-color: #FFB200 !important;
    color: white !important;
}
</style>

<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar" pagination="true" rownumbers="true" idField="id" treeField="id" fit="true" singleSelect="false">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'trans_date',width:120,halign:'center'">Transaction Date</th>
            <th rowspan="2" data-options="field:'item_number',width:230,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'specification',width:100,halign:'center',align:'center'">Material</th>
            <th rowspan="2" data-options="field:'qty_wip',width:100,halign:'center',align:'center'">Qty WIP</th>
            <!-- <th rowspan="2" data-options="field:'qty_packing',width:100,halign:'center',align:'right'">Qty Packing</th>
            <th rowspan="2" data-options="field:'qty_label',width:100,halign:'center',align:'right'">Qty <br>Label Packing</th>
            <th rowspan="2" data-options="field:'shift',width:100,halign:'center',align:'center'">Shift</th>
            <th rowspan="2" data-options="field:'packing_size',width:120,halign:'center',align:'right'">Packing Size</th>-->
            <th rowspan="2" data-options="field:'compound_lot',width:120,halign:'center',align:'right'">Compound LOT</th>
            <th rowspan="2" data-options="field:'prod_date',width:120,halign:'center'">Production Date</th>
            <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Print</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 200px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 80%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <input style="width:60%;" id="filter_shift" class="easyui-combobox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 70%; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:50%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 100%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:60%;" name="transaction_date" id="transaction_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leader</span>
                    <input style="width:60%;" name="leader" id="leader" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <input style="width:60%;" name="shift" id="shift" required="" class="easyui-combobox">
                </div> -->
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Label Packing List" toolbar="#toolbar2"></table>

    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/new_barcode_fg/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#transaction_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        url_save = '<?= base_url('warehouse/new_barcode_fg/create') ?>';
        request_no();

        addTable();
    }
    
    function request_no(reqDate = "") {
        if (reqDate == "") {
            var request_date = $("#transaction_date").datebox('getValue');
        } else {
            var request_date = reqDate;
        }
        $.ajax({
            type: "post",
            url: "<?= base_url('warehouse/new_barcode_fg/request_no') ?>/" + window.btoa(request_date),
            dataType: "html",
            success: function(result) {
                $("#request_no").textbox('setValue', result);
            }
        });
    }

    function addTable() {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            singleSelect: true,
            columns: [
                [
                    {
                        field: 'item_fg_id',
                        width: 150,
                        hidden: true,
                        halign: 'center',
                        title: "ID",
                        editor: { type: 'textbox', options: { hidden: true } }
                    },
                    {
                        field: 'item_number',
                        width: 250,
                        halign: 'center',
                        title: "Product No",
                        editor: {
                            type: 'combogrid',
                            options: {
                                url: '<?= base_url('warehouse/new_barcode_fg/readitemsFG/') ?>',
                                required: true,
                                panelWidth: 320,
                                idField: 'item_number',
                                textField: 'item_number',
                                mode: 'remote',
                                fitColumns: true,
                                prompt: 'Choose Product',
                                columns: [
                                    [
                                        { field: 'item_number', title: 'Product No', width: 150 },
                                        { field: 'item_name', title: 'Product Name', width: 150 },
                                    ]
                                ],
                                // onSelect: function (index, row) {
                                //     console.log("Selected Row:", row);

                                //     var dg = $('#dg2');
                                //     var rowIndex = dg.datagrid('getRowIndex', dg.datagrid('getSelected'));

                                //     var edItemFgId = dg.datagrid('getEditor', { index: rowIndex, field: 'item_fg_id' });
                                //     $(edItemFgId.target).textbox('setValue', row.id);

                                //     var edItemNumber = dg.datagrid('getEditor', { index: rowIndex, field: 'item_number' });
                                //     $(edItemNumber.target).textbox('setValue', row.item_number);

                                //     var edItemName = dg.datagrid('getEditor', { index: rowIndex, field: 'item_name' });
                                //     $(edItemName.target).textbox('setValue', row.item_name);

                                //     var edSpecification = dg.datagrid('getEditor', { index: rowIndex, field: 'specification' });
                                //     $(edSpecification.target).textbox('setValue', row.specification);

                                //     var edEndStock = dg.datagrid('getEditor', { index: rowIndex, field: 'end_stock' });
                                //     $(edEndStock.target).textbox('setValue', row.end_stock);

                                //     var edQtyPacking = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_packing' });
                                //     $(edQtyPacking.target).numberbox('setValue', row.box_sub);

                                //     // Ambil data material dari specification di item_fg
                                //     $(edItemRmNumber.target).textbox('setValue', row.specification || '-');
                                //     $(edItemRmId.target).textbox('setValue', null);
                                // }
                                onSelect: function (index, row) {
                                    console.log("Selected Row:", row);

                                    var dg = $('#dg2');
                                    var selectedRow = dg.datagrid('getSelected');
                                    var rowIndex = dg.datagrid('getRowIndex', selectedRow);

                                    if (rowIndex < 0) {
                                        console.error('Row index tidak ditemukan');
                                        return;
                                    }

                                    var editors = {
                                        item_fg_id: dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_fg_id'
                                        }),
                                        item_number: dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_number'
                                        }),
                                        item_name: dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_name'
                                        }),
                                        specification: dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'specification'
                                        }),
                                        end_stock: dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'end_stock'
                                        })
                                    };

                                    if (editors.item_fg_id) {
                                        $(editors.item_fg_id.target).textbox('setValue', row.id);
                                    }

                                    if (editors.item_number) {
                                        $(editors.item_number.target).textbox('setValue', row.item_number);
                                    }

                                    if (editors.item_name) {
                                        $(editors.item_name.target).textbox('setValue', row.item_name);
                                    }

                                    if (editors.specification) {
                                        $(editors.specification.target)
                                            .textbox('setValue', row.specification || '');
                                    }

                                    if (editors.end_stock) {
                                        $(editors.end_stock.target)
                                            .textbox('setValue', row.end_stock || 0);
                                    }
                                }
                            }
                        }
                    },
                    {
                        field: 'item_name',
                        width: 150,
                        halign: 'center',
                        title: "Product Name",
                        editor: { 
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'specification',
                        width: 200,
                        halign: 'center',
                        title: "Material",
                        editor: {
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'end_stock',
                        width: 100,
                        halign: 'center',
                        title: "Stock",
                        editor: { 
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'qty_wip',
                        width: 100,
                        halign: 'center',
                        title: "Qty WIP",
                        editor: {
                            type: 'numberbox',
                            options: {
                                required: true,
                                onChange: function(newVal) {
                                    var rowIndex = dg.datagrid('getRowIndex', dg.datagrid('getSelected'));
                                    var qtyPackingEditor = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_packing' });
                                    var qtyLabelPackingEditor = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_label_packing' });


                                    var endStockEditor = dg.datagrid('getEditor', { index: rowIndex, field: 'end_stock' });

                                    var qtyWip = parseFloat(newVal) || 0;
                                    var endStock = parseFloat($(endStockEditor.target).textbox('getValue')) || 0;

                                    if (qtyWip > endStock) {
                                        toastr.error("Qty WIP must not be greater than End Stock!");
                                        $(this).numberbox('setValue', '');
                                        if (qtyLabelPackingEditor) {
                                            $(qtyLabelPackingEditor.target).numberbox('setValue', 0);
                                        }
                                        return;
                                    }


                                    if (qtyPackingEditor && qtyLabelPackingEditor) {
                                        var qtyPacking = parseFloat($(qtyPackingEditor.target).numberbox('getValue')) || 0;
                                        if (qtyPacking > 0) {
                                            var qtyWip = parseFloat(newVal) || 0;
                                            var qtyLabelPacking = Math.ceil(qtyWip / qtyPacking); // Pembulatan ke atas
                                            $(qtyLabelPackingEditor.target).numberbox('setValue', qtyLabelPacking);
                                        } else {
                                            $(qtyLabelPackingEditor.target).numberbox('setValue', 0);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    // {
                    //     field: 'qty_packing',
                    //     width: 100,
                    //     halign: 'center',
                    //     title: "Qty Packing",
                    //     editor: {
                    //         type: 'numberbox',
                    //         options: { readonly: true }
                    //     }
                    // },
                    // {
                    //     field: 'qty_label_packing',
                    //     width: 100,
                    //     halign: 'center',
                    //     title: "Qty Label Packing",
                    //     editor: {
                    //         type: 'numberbox',
                    //         options: { readonly: true }
                    //     }
                    // },
                    // {
                    //     field: 'packing_size',
                    //     width: 150,
                    //     halign: 'center',
                    //     title: "Packing Size",
                    //     editor: {
                    //         type: 'combogrid',
                    //         options: {
                    //             panelWidth: 200,
                    //             idField: 'size',
                    //             textField: 'size',
                    //             data: [
                    //                 { size: '12 x 25 cm' },
                    //                 { size: '20 x 45 cm' },
                    //                 { size: '25 x 45 cm' },
                    //                 { size: '40 x 60 cm' }
                    //             ],
                    //             columns: [[
                    //                 { field: 'size', title: 'Packing Size', width: 180 }
                    //             ]],
                    //             fitColumns: true
                    //         }
                    //     }
                    // },
                    {
                        field: 'compound_lot',
                        width: 120,
                        halign: 'center',
                        title: "Compound Lot",
                        editor: {
                            type: 'textbox',
                            options: { required: true }
                        }
                    },
                    {
                        field: 'prod_date',
                        width: 120,
                        halign: 'center',
                        title: "Production Date",
                        editor: {
                            type: 'datebox',
                            options: { required: true }
                        }
                    },
                    {
                        field: 'operator',
                        width: 100,
                        hidden: true,
                        halign: 'center',
                        title: "Operator",
                        editor: {
                            type: 'textbox'
                        }
                    },
                    {
                        field: 'qc',
                        width: 100,
                        halign: 'center',
                        title: "FG PIC",
                        editor: {
                            type: 'textbox'
                        }
                    }
                ]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            }
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        var leader = $("#leader").textbox('getValue');
        if (leader != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty_wip: ''
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please input Leader first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }
        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('warehouse/new_barcode_fg/delete') ?>',
                            data: {
                                id: row.id,
                                serial_no: row.serial_no
                            },
                            dataType: 'json',
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.message);
                                    $('#dg').treegrid('reload');
                                } else {
                                    toastr.error(result.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            }
                        });
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
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        if (!filter_from || !filter_to) {
            toastr.warning("Please select both From and To dates!", "Information");
            return;
        }

        $('#dg').treegrid({
            url: '<?= base_url('warehouse/new_barcode_fg/datatables') ?>',
            method: 'post',
            queryParams: {
                filter_from: filter_from,
                filter_to: filter_to,
                filter_product_no: filter_product_no
            }
        });
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_product_no = $("#filter_product_no").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_product_no=" + filter_product_no;
        window.location.assign('<?= base_url('warehouse/new_barcode_fg/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    function update() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            var row = rows[0];
            $('#dlg_insert').dialog('open');
            $('#dlg_insert').dialog('setTitle', 'Update Data');
            
            // Set nilai form
            $('#transaction_date').datebox('setValue', row.trans_date);
            $('#leader').textbox('setValue', row.leader);
            $('#request_no').textbox('setValue', row.request_no);
            
            // Load data ke datagrid
            $('#dg2').datagrid('loadData', [{
                item_fg_id: row.item_fg_id,
                item_number: row.item_number,
                item_name: row.item_name,
                specification: row.specification,
                qty_wip: row.qty_wip,
                compound_lot: row.compound_lot,
                prod_date: row.prod_date,
                operator: row.operator,
                qc: row.qc
            }]);
            
            url_save = '<?= base_url('warehouse/new_barcode_fg/update') ?>';
            addTable();
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    $(function() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to
        $('#dg').treegrid({
            url: '<?= base_url('warehouse/new_barcode_fg/datatables') ?>',
            method: 'post',
            queryParams: {
                filter_from: filter_from,
                filter_to: filter_to
            },
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'id',
            fit: true,
            singleSelect: false,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
            // rowStyler: function(row) {
            //     if (row.state != "closed") {
            //         return 'background-color:#CFE6FF;';
            //     }
            // },
        });

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All & Print Label',
                iconCls: 'icon-ok',
                handler: function () {
                    var transaction_date = $("#transaction_date").datebox('getValue');
                    var leader = $("#leader").textbox('getValue');
                    var request_no = $("#request_no").textbox('getValue');

                    if (transaction_date == "" || leader == "") {
                        toastr.warning("Please fill all required fields!", "Information");
                    } else {
                        $('#dg2').datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');

                        if (rows.length > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function (r) {
                                if (r) {
                                    var serialNos = [];
                                    var ajaxRequests = [];

                                    $.each(rows, function (index, row) {
                                        var deferred = $.Deferred();
                                        ajaxRequests.push(deferred);

                                        $.ajax({
                                            type: "POST",
                                            url: '<?= base_url('warehouse/new_barcode_fg/create') ?>',
                                            data: {
                                                trans_date: transaction_date,
                                                shift: '1',
                                                leader: leader,
                                                item_fg_id: row.item_fg_id,
                                                request_no: request_no,
                                                qty_wip: row.qty_wip,
                                                qty_packing: row.qty_packing,
                                                qty_label: row.qty_label_packing,
                                                packing_size: row.packing_size,
                                                compound_lot: row.compound_lot,
                                                prod_date: row.prod_date,
                                                operator: row.operator,
                                                specification: row.specification,
                                                qc: row.qc
                                            },
                                            dataType: "json"
                                        }).done(function (result) {
                                            if (result.success) {
                                                toastr.success(result.message);
                                                serialNos.push(result.serial_no);
                                            } else {
                                                toastr.error(result.message);
                                            }
                                            deferred.resolve();
                                        }).fail(function () {
                                            toastr.error("Error saving data.");
                                            deferred.reject();
                                        });
                                    });

                                    $.when.apply($, ajaxRequests).done(function () {
                                        if (serialNos.length > 0) {
                                            $('#dg').treegrid('reload');
                                            $('#dlg_insert').dialog('close');
                                            showPrintOptions(request_no);
                                        }
                                    });
                                }
                            });
                        } else {
                            toastr.warning("Please add at least one item!", "Information");
                        }
                    }
                }
            }]
        });

        $('#filter_product_no').combogrid({
            panelWidth: 320,
            idField: 'item_number',
            textField: 'item_number',
            mode: 'remote',
            fitColumns: true,
            prompt: 'Choose Product',
            columns: [
                [
                    { field: 'item_number', title: 'Product No', width: 150 },
                    { field: 'item_name', title: 'Product Name', width: 150 }
                ]
            ],
            url: '<?= base_url('warehouse/new_barcode_fg/readitemsFG/') ?>'
        });

        // $('#filter_shift').combobox({
        //     valueField: 'value',
        //     textField: 'label',
        //     prompt: 'Choose Shift',
        //     data: [
        //         { value: '1', label: '1' },
        //         { value: '2', label: '2' },
        //         { value: '3', label: '3' }
        //     ]
        // });

        // $('#shift').combobox({
        //     valueField: 'value',
        //     textField: 'label',
        //     prompt: 'Choose Shift',
        //     data: [
        //         { value: '1', label: '1' },
        //         { value: '2', label: '2' },
        //         { value: '3', label: '3' }
        //     ]
        // });
    });
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

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return "<b style='color:red;'>CLOSED</b>";
            } else {
                return "<b style='color:green;'>OPEN</b>";
            }
        }
    }

    function statusStyle(value, row, index) {
        if (value != null) {
            if (row.total_scan == row.qty_label) {
                return 'background-color:#FFC8C8;';
            } else {
                return 'background-color:#C8FFCC;';
            }
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

    function BtnPrintLabel(val, row) {
        if (val != "closed") {
            return `
                <a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" href="javascript:void(0)" onclick="showPrintLabel('${encodeURIComponent(row.serial_no)}', '${encodeURIComponent(row.item_fg_id)}')">
                    <i class="fa fa-print"></i> Print
                </a>`;
        }
    }

    function showPrintLabel(serial_no, item_fg_id = null) {
        console.log("Serial No: " + serial_no);
        Swal.fire({
            title: 'Print Options',
            text: "Select Print Barcode Mode!",
            icon: 'info',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Print RP/Com',
            denyButtonText: 'Print Ext',
            cancelButtonText: 'Cancel',
            width: '420px',
            customClass: {
                denyButton: 'swal2-deny-custom',
                cancelButton: 'swal2-cancel-custom'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let url = '<?= base_url('warehouse/new_barcode_fg/print_label?serial_no=') ?>' + serial_no;
                if (item_fg_id) {
                    url += '&item_fg_id=' + item_fg_id;
                }
                window.open(url, '_blank');

            } else if (result.isDenied) {
                let url = '<?= base_url('warehouse/new_barcode_fg/print_label_ext?serial_no=') ?>' + serial_no;
                if (item_fg_id) {
                    url += '&item_fg_id=' + item_fg_id;
                }
                window.open(url, '_blank');
            }
        });
    }    

    // function showPrintLabel(serial_no, item_fg_id = null) {
    //     console.log("Serial No: " + serial_no);
    //     Swal.fire({
    //         title: 'Print Options',
    //         text: "Select Print Barcode Mode!",
    //         icon: 'info',
    //         showCancelButton: true,
    //         confirmButtonText: 'Print Label',
    //         cancelButtonText: 'Cancel'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             let url = '<?= base_url('warehouse/new_barcode_fg/print_label?serial_no=') ?>' + serial_no;
    //             if (item_fg_id) {
    //                 url += '&item_fg_id=' + item_fg_id;
    //             }
    //             window.open(url, '_blank');
    //         }
    //     });
    // }

    // function showPrintOptions(request_no) {
    //     console.log("Request No: " + request_no);
    //     Swal.fire({
    //         title: 'Print Options',
    //         text: "Select Print Barcode Mode!",
    //         icon: 'info',
    //         showCancelButton: true,
    //         confirmButtonText: 'Print Label',
    //         cancelButtonText: 'Cancel'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             let url = '<?= base_url('warehouse/new_barcode_fg/print_label_by_request?request_no=') ?>' + request_no;
    //             window.open(url, '_blank');
    //         }
    //     });
    // }


    function showPrintOptions(request_no) {
        console.log("Request No: " + request_no);
        Swal.fire({
            title: 'Print Options',
            text: "Select Print Barcode Mode!",
            icon: 'info',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Print RP/Com',
            denyButtonText: 'Print Ext',
            cancelButtonText: 'Cancel',
            width: '420px',
            customClass: {
                denyButton: 'swal2-deny-custom',
                cancelButton: 'swal2-cancel-custom'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let url = '<?= base_url('warehouse/new_barcode_fg/print_label_by_request?request_no=') ?>' + request_no;
                window.open(url, '_blank');

            } else if (result.isDenied) {
                let url = '<?= base_url('warehouse/new_barcode_fg/print_label_ext_by_request?request_no=') ?>' + request_no;
                window.open(url, '_blank');
            }
        });
    }
</script>