<style>
    .datagrid-body td[field="qty_receive"] .datagrid-cell {
        background-color: #d7ecff !important;
        margin: 0 !important;
    }

    .datagrid-body td[field="qty_receive"] .textbox,
    .datagrid-body td[field="qty_receive"] .numberbox {
        width: 100% !important;
        box-sizing: border-box;
        margin: 0 !important;
        padding: 0 !important;
    }

    .datagrid-body td[field="qty_receive"] .textbox-text {
        background-color: #d7ecff !important;
        width: 100% !important;
        box-sizing: border-box;
        padding: 3px !important;
        margin: 0 !important;
    }

    .datagrid-body td[field="qty_receive"] .textbox-text.validatebox-invalid {
        background-color: #fff3f3 !important;
    }

    .datagrid-body td[field="qty_receive"] .textbox-addon,
    .datagrid-body td[field="qty_receive"] .textbox-addon-right {
        display: none !important;
    }

    .datagrid-body td[field="qty_receive"] .textbox,
    .datagrid-body td[field="qty_receive"] .numberbox {
        border-right: 2px solid #6891c8 !important;
    }

    .swal2-container {
        z-index: 9999 !important;
    }
    .swal2-popup {
        z-index: 99999 !important;
    }
</style>

<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'receive_no',width:200,halign:'center'">Receive No</th>
            <th rowspan="2" data-options="field:'total_scan',width:110,align:'center',formatter:statusformat,styler:statusStyle">Status POR</th>
            <th rowspan="2" data-options="field:'status_invoice',width:110,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status Invoice</th>
            <th rowspan="2" data-options="field:'po_no',width:180,halign:'center'">Purchase Order No</th>
            <th rowspan="2" data-options="field:'receive_date',width:110,halign:'center'">Receive Date</th>

            <th rowspan="2" data-options="field:'subcont_id',width:110,halign:'center'">Subcont Code</th>
            <th rowspan="2" data-options="field:'subcont_name',width:200,halign:'center'">Subcont Name</th>
            <th rowspan="2" data-options="field:'subcont_dn_no',width:180,halign:'center'">Subcont DN No</th>
            <th rowspan="2" data-options="field:'subcont_dn_date',width:130,halign:'center'">Subcont DN Date</th>

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

<div id="toolbar" style="height: 240px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 65%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receive Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont Name</span>
                    <input style="width:60%;" id="filter_subcont_id" class="easyui-combogrid" data-options="editable: false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont DN No</span>
                    <input style="width:60%;" id="filter_subcont_dn_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Order No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receive No</span>
                    <input style="width:60%;" id="filter_receive" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.3%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Print Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt No</span>
                <input style="width:60%;" id="filter_receive_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="print_receiving_note()"><i class="fa fa-print"></i> Receiving Note</a>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1088px; height: 90%; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receive Date</span>
                    <input style="width:60%;" name="receive_date" id="receive_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receive No</span>
                    <input style="width:60%;" name="receive_no" id="receive_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont Name</span>
                    <input style="width:60%;" name="subcont_id" id="subcont_id" required="" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont DN Date</span>
                    <input style="width:60%;" name="subcont_dn_date" id="subcont_dn_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont DN No</span>
                    <input style="width:60%;" name="subcont_dn_no" id="subcont_dn_no" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Order No</span>
                    <input style="width:60%;" name="po_no" id="po_no" required="" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Order Receive List" toolbar="#toolbar2"></table>

    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/por_subcont_productions/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    $.extend($.fn.validatebox.defaults.rules, {
        greaterThanZero: {
            validator: function(value) {
                return parseFloat(value) > 0;
            },
            message: 'Nilai harus lebih dari 0'
        }
    });

    //Add Data
    function add() {

        $('#dlg_insert').dialog({
            title: 'Add New',
            modal: true,
            closed: false,
            maximized: true,
            resizable: true,
        }).dialog('open');

        $('#dg2').datagrid('loadData', []);
        editIndex = undefined;

        receive_no();

        $('#receive_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $('#subcont_dn_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#subcont_id").combogrid('enable');
        $("#po_no").combogrid('enable');
        $('#subcont_dn_no').textbox('enable');

        $('#receive_no').textbox('clear');
        $('#subcont_id').combogrid('clear');
        $('#po_no').combogrid('clear');
        $('#subcont_dn_no').textbox('clear');
        url_save = '<?= base_url('purchase/por_subcont_productions/create') ?>';
    }

    function addTable(po_no, link = "") {
        var lastIndex;
        editIndex = undefined;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            fitColumns: true,
            columns: [
                [
                    {
                        field: 'item_fg_id',
                        width: 200,
                        halign: 'center',
                        title: "Product ID",
                        editor: {
                            type: 'combogrid',
                            options: {
                                url: '<?= base_url('purchase/por_subcont_productions/readItems?po_no=') ?>' + po_no,
                                required: true,
                                panelWidth: 450,
                                idField: 'item_fg_id',
                                textField: 'item_fg_id',
                                mode: 'remote',
                                fitColumns: true,
                                prompt: 'Choose Product ID',
                                columns: [
                                    [
                                        {
                                            field: 'item_fg_id', 
                                            title: 'Product ID', 
                                            width: 150 
                                        },{
                                            field: 'item_fg_number', 
                                            title: 'Product No', 
                                            width: 200 
                                        },{
                                            field: 'item_fg_name', 
                                            title: 'Product Name', 
                                            width: 150 
                                        }
                                    ]
                                ],
                                onSelect: function (value, rows) {
                                    var dg = $('#dg2');
                                    var row = dg.datagrid('getSelected');
                                    var rowIndex = dg.datagrid('getRowIndex', row);

                                    var isDuplicate = false;
                                    var allRows = dg.datagrid('getRows');

                                    
                                    for (var i = 0; i < allRows.length; i++) {
                                        if (i !== rowIndex && allRows[i].item_fg_id === rows.item_fg_id) {
                                            isDuplicate = true;
                                            break;
                                        }
                                    }

                                    var editors = {
                                        item_fg_id: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'item_fg_id' 
                                        }),
                                        item_fg_number: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'item_fg_number' 
                                        }),
                                        item_fg_name: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'item_fg_name' 
                                        }),
                                        uom: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'uom' 
                                        }),
                                        qty_po: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'qty_po' 
                                        }),
                                        qty_os: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'qty_os' 
                                        }),
                                        qty_receive: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'qty_receive' 
                                        }),
                                        box_sub: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'box_sub'
                                        }),
                                        qty_label: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'qty_label' 
                                        }),
                                        material: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'material' 
                                        })
                                    };

                                    allRows[rowIndex]._initialized = false;

                                    $(editors.item_fg_id.target).textbox('setValue', rows.item_fg_id);
                                    $(editors.item_fg_number.target).textbox('setValue', rows.item_fg_number);
                                    $(editors.item_fg_name.target).textbox('setValue', rows.item_fg_name);
                                    $(editors.uom.target).textbox('setValue', rows.uom);
                                    $(editors.qty_po.target).numberbox('setValue', rows.qty_po);
                                    $(editors.qty_os.target).numberbox('setValue', rows.qty_os);
                                    $(editors.box_sub.target).numberbox('setValue', rows.box_sub);
                                    $(editors.material.target).textbox('setValue', rows.material);

                                    allRows[rowIndex].original_qty_os = parseFloat(rows.qty_os) || parseFloat(rows.qty_po) || 0;

                                    if (isDuplicate) {
                                        var totalReceipt = 0;
                                        for (var i = 0; i < allRows.length; i++) {
                                            if (i !== rowIndex && allRows[i].item_fg_id === rows.item_fg_id) {
                                                totalReceipt += parseFloat(allRows[i].qty_receive) || 0;
                                            }
                                        }

                                        var originalOS = parseFloat(rows.qty_os) || parseFloat(rows.qty_po) || 0;
                                        var sisa_os = originalOS - totalReceipt;
                                        if (sisa_os < 0) sisa_os = 0;

                                        $(editors.qty_os.target).numberbox('setValue', sisa_os);
                                        $(editors.qty_receive.target).numberbox('setValue', sisa_os);

                                        allRows[rowIndex]._initialized = true;

                                        var f_box_sub = parseInt(rows.box_sub) || 0;
                                        var label = f_box_sub > 0 ? Math.ceil(sisa_os / f_box_sub) : 0;
                                        $(editors.qty_label.target).numberbox('setValue', label);

                                        toastr.info("Duplicate Item, Qty Receipt set remaining : " + sisa_os, "Info");
                             
                                    } else {
                                        $(editors.qty_receive.target).numberbox('setValue', rows.qty_receive);
                                        allRows[rowIndex]._initialized = true;

                                        var f_box_sub = parseInt(rows.box_sub) || 0;
                                        var f_receive = parseInt(rows.qty_receive) || 0;
                                        var label = f_box_sub > 0 ? Math.ceil(f_receive / f_box_sub) : 0;
                                        $(editors.qty_label.target).numberbox('setValue', label);
                                    }
                                }
                            }
                        }
                    },
                    {
                        field: 'item_fg_number',
                        width: 200,
                        halign: 'center',
                        title: "Product No",
                        editor: { 
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'item_fg_name',
                        width: 150,
                        halign: 'center',
                        title: "Product Name",
                        editor: { 
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'material',
                        width: 150,
                        halign: 'center',
                        title: "Material",
                        editor: {
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'uom',
                        width: 80,
                        halign: 'center',
                        title: "UOM",
                        editor: {
                            type: 'textbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'qty_po',
                        width: 80,
                        halign: 'center',
                        title: "PO",
                        editor: {
                            type: 'numberbox',
                            options: {
                                readonly: true,
                                precision: 0
                            }
                        }
                    },
                    {
                        field: 'qty_os',
                        width: 80,
                        halign: 'center',
                        title: "OS PO",
                        editor: {
                            type: 'numberbox',
                            options: {
                                readonly: true,
                                precision: 0
                            }
                        }
                    },
                    {
                        field: 'qty_receive',
                        width: 80,
                        halign: 'center',
                        title: "Receive",
                        styler: cellStyler,
                        editor: {
                            type: 'numberbox',
                            options: { 
                                required: true,
                                precision: 0 ,
                                validType: 'greaterThanZero'
                            }
                        }
                    },
                    {
                        field: 'box_sub',
                        width: 80,
                        halign: 'center',
                        title: "Per Pack",
                        editor: {
                            type: 'numberbox',
                            options: { 
                                precision: 0,
                                editable: false,
                            }
                        }
                    },
                    {
                        field: 'qty_label',
                        width: 80,
                        halign: 'center',
                        title: "Label Packing",
                        editor: {
                            type: 'numberbox',
                            options: {
                                readonly: true
                            }
                        }
                    },
                    {
                        field: 'compound_lot_no',
                        width: 170,
                        halign: 'center',
                        title: "Compound Lot No",
                        editor: {
                            type: 'textbox',
                            options: { 
                                required: true,
                                validType: 'length[1,30]',
                                inputEvents: $.extend({}, $.fn.textbox.defaults.inputEvents, {
                                    input: function(e){
                                        let val = e.target.value.toUpperCase();
                                        e.target.value = val;
                                    }
                                })
                            }
                        }
                    },
                    {
                        field: 'production_date',
                        width: 140,
                        halign: 'center',
                        title: "Production Date",
                        editor: {
                            type: 'datebox',
                            options: { 
                                required: true,
                                formatter: myformatter,
                                parser: myparser
                            }
                        }
                    }, 
                    {
                        field: 'shift',
                        width: 70,
                        halign: 'center',
                        title: "Shift",
                        editor: {
                            type: 'combobox',
                            options: {
                                required: true,
                                editable: false,
                                valueField: 'value',
                                textField: 'text',
                                panelHeight: 'auto',
                                data: [
                                    { value: '1', text: '1' },
                                    { value: '2', text: '2' },
                                    { value: '3', text: '3' }
                                ]
                            }
                        }
                    },
                    {
                        field: 'packing_date',
                        width: 140,
                        halign: 'center',
                        title: "Packing Date",
                        editor: {
                            type: 'datebox',
                            options: { 
                                required: true,
                                formatter: myformatter,
                                parser: myparser
                            }
                        }
                    }, 
                    {
                        field: 'qc_name',
                        width: 120,
                        halign: 'center',
                        title: "QC",
                        editor: {
                            type: 'textbox',
                            options: {
                                inputEvents: $.extend({}, $.fn.textbox.defaults.inputEvents, {
                                    input: function(e){
                                        e.target.value = e.target.value.toUpperCase();
                                    }
                                })
                            }
                        }
                    },
                ]
            ],
            onClickRow: function (rowIndex) {
                var dg = $(this);

                if (editIndex !== rowIndex) {
                    if (endEditing()) {
                        dg.datagrid('selectRow', rowIndex).datagrid('beginEdit', rowIndex);
                        editIndex = rowIndex;
                        lastIndex = rowIndex;
                    } else {
                        dg.datagrid('selectRow', editIndex);
                    }
                }
            },
            onBeginEdit: function (rowIndex, row) {
                var dg = $('#dg2');
                var qty_receive_editor = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_receive' });
                var qty_receive = qty_receive_editor ? $(qty_receive_editor.target) : null;

                if (!qty_receive) return;

                qty_receive.numberbox({
                    onChange: function () {
                        var dg = $('#dg2');
                        var rows = dg.datagrid('getRows');
                        var currentIndex = rowIndex;

                        if (rows[currentIndex]._initialized === false) {
                            return;
                        }

                        var editor_item_fg_number = dg.datagrid('getEditor', { index: currentIndex, field: 'item_fg_number' });
                        var item_fg_number = editor_item_fg_number
                            ? $(editor_item_fg_number.target).textbox('getValue')
                            : (rows[currentIndex].item_fg_number || '');

                        if (!item_fg_number) return;

                        var qty_po_editor = dg.datagrid('getEditor', { index: currentIndex, field: 'qty_po' });
                        var qty_os_editor = dg.datagrid('getEditor', { index: currentIndex, field: 'qty_os' });
                        var qty_receive_editor = dg.datagrid('getEditor', { index: currentIndex, field: 'qty_receive' });
                        var box_sub_editor = dg.datagrid('getEditor', { index: currentIndex, field: 'box_sub' });
                        var qty_label_editor = dg.datagrid('getEditor', { index: currentIndex, field: 'qty_label' });

                        var qty_po = $(qty_po_editor.target);
                        var qty_os = $(qty_os_editor.target);
                        var qty_receive = $(qty_receive_editor.target);
                        var box_sub = $(box_sub_editor.target);
                        var qty_label = $(qty_label_editor.target);

                        var f_qty_po = parseFloat(qty_po.numberbox('getValue')) || 0;
                        var f_qty_os = parseFloat(qty_os.numberbox('getValue')) || 0;
                        var f_qty_receive = parseFloat(qty_receive.numberbox('getValue')) || 0;
                        var f_box_sub = parseFloat(box_sub.numberbox('getValue')) || 0;

                        if (f_qty_receive == 0 || f_qty_receive == "" || f_qty_receive == 0.00) {
                            qty_receive.numberbox('setValue', 0.00);
                            qty_label.numberbox('setValue', 0);
                            
                            qty_receive.numberbox('textbox').validatebox('validate');
                            return;
                        }

                        // Hitung total receive dari semua baris untuk item yang sama
                        var total_receive = 0;
                        for (var i = 0; i < rows.length; i++) {
                            var row_item_fg_number = rows[i].item_fg_number;

                            if (i === currentIndex) {
                                total_receive += f_qty_receive; // current edit value
                            } else if (row_item_fg_number === item_fg_number) {
                                total_receive += parseInt(rows[i].qty_receive) || 0;
                            }
                        }
                        
                        if (f_qty_os === 0) {
                            if (f_qty_receive > f_qty_po) {
                                toastr.warning("Qty Receipt > Qty PO", "Information");
                                qty_receive.numberbox('setValue', 0);
                                qty_label.numberbox('setValue', 0);
                                return;
                            }
                        } else {
                            if (f_qty_receive > f_qty_os) {
                                toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                qty_receive.numberbox('setValue', 0);
                                qty_label.numberbox('setValue', 0);
                                return;
                            }
                        }

                        var originalLimit = 0;
                        for (var i = 0; i < rows.length; i++) {
                            if (rows[i].item_fg_number === item_fg_number && rows[i].original_qty_os) {
                                originalLimit = rows[i].original_qty_os;
                                break;
                            }
                        }
                        if (originalLimit === 0) {
                            originalLimit = f_qty_po; // fallback
                        }

                        if (total_receive > originalLimit) {
                            toastr.warning("Total Qty Receipt for item " + item_fg_number + " exceeds the limit of " + originalLimit, "Warning");
                            qty_receive.numberbox('setValue', 0);
                            qty_label.numberbox('setValue', 0);
                            return;
                        }

                        // Hitung qty label jika valid
                        var label_qty = f_box_sub > 0 ? Math.ceil(f_qty_receive / f_box_sub) : 0;
                        qty_label.numberbox('setValue', label_qty);
                    }
                });

                var compound_lot_no_editor = dg.datagrid('getEditor', { index: rowIndex, field: 'compound_lot_no' });
                var compound_lot_no = compound_lot_no_editor ? $(compound_lot_no_editor.target) : null;

                if (!compound_lot_no) return;

                compound_lot_no.textbox({
                    onChange: function (newVal, oldVal) {
                        var dg = $('#dg2');
                        var rows = dg.datagrid('getRows');
                        var lot_value = (newVal || '').toString().trim();

                        var ed_item_fg = dg.datagrid('getEditor', { index: rowIndex, field: 'item_fg_number' });
                        var item_fg_number = ed_item_fg
                        ? $(ed_item_fg.target).textbox('getValue').toString().trim()
                        : (rows[rowIndex] && rows[rowIndex].item_fg_number ? rows[rowIndex].item_fg_number.toString().trim() : '');

                        if (!item_fg_number || !lot_value) return; // kalau kosong skip validasi

                        for (var i = 0; i < rows.length; i++) {
                            if (i === rowIndex) continue;

                            var ed_item_fg_i = dg.datagrid('getEditor', { index: i, field: 'item_fg_number' });
                            var compare_item = ed_item_fg_i
                                ? $(ed_item_fg_i.target).textbox('getValue').toString().trim()
                                : (rows[i].item_fg_number ? rows[i].item_fg_number.toString().trim() : '');

                            if (compare_item !== item_fg_number) continue;

                            var ed_lot_i = dg.datagrid('getEditor', { index: i, field: 'compound_lot_no' });
                            var compare_lot = ed_lot_i
                                ? $(ed_lot_i.target).textbox('getValue').toString().trim()
                                : (rows[i].compound_lot_no ? rows[i].compound_lot_no.toString().trim() : '');

                            if (!compare_lot) continue;

                            if (compare_lot === lot_value) {
                                toastr.warning("Compound Lot No must be unique for the same Product No", "Information");
                                compound_lot_no.textbox('setValue', ''); // clear input
                                return;
                            }
                        }
                    }
                });

                
            }
        });
    }

    var editIndex = undefined;

    function endAllEdit(dg) {
        var rowCount = dg.datagrid('getRows').length;
        for (var i = 0; i < rowCount; i++) {
            if (dg.datagrid('getEditors', i).length) {
                dg.datagrid('endEdit', i);
            }
        }
    }

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
        var receive_date = $("#receive_date").datebox('getValue');
        var receive_no = $("#receive_no").textbox('getValue');
        var subcont_dn_no = $("#subcont_dn_no").textbox('getValue');
        var subcont_dn_date = $("#subcont_dn_date").datebox('getValue');
        var subcont_id = $("#subcont_id").combogrid('getValue');
        var po_no = $("#po_no").combogrid('getValue');

        if (
            receive_date == "" || receive_no == "" | subcont_dn_no == "" || 
            subcont_dn_date == "" || subcont_id == "" || po_no == ""
        ) {

            toastr.warning("Please fill all required fields!", "Information");
            return;
        } else {

            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    receive: ''
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        }
    }

    function removeit() {
        if (endEditing()) {
            var row = $('#dg2').datagrid('getSelected');
            if (row) {
                var rowIndex = $('#dg2').datagrid('getRowIndex', row);
                $('#dg2').datagrid('deleteRow', rowIndex);
            }
            editIndex = undefined;
        } else {
            var dg = $('#dg2');
            var row = dg.datagrid('getSelected');
            var rowIndex = dg.datagrid('getRowIndex', row);

            $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
            editIndex = undefined;
        }
    }

    function receive_no(date = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/por_subcont_productions/generateReceiveNo/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#receive_no").textbox('setValue', result);
            }
        });
    }

    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('purchase/por_subcont_productions/delete') ?>',
                            data: {
                                id: row.id,
                                po_no: row.po_no,
                                subcont_id: row.subcont_id,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                toastr.success(result.message);
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
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
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_subcont_dn_no = $("#filter_subcont_dn_no").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_receive = $("#filter_receive").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_subcont_id=" + filter_subcont_id + "&filter_receive=" + filter_receive + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_subcont_dn_no=" + filter_subcont_dn_no;


        $('#dg').datagrid({
            url: '<?= base_url('purchase/por_subcont_productions/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.receive_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                var filter_item_fg_id = $('#filter_item_fg_id').combogrid('getValue');
                var encodedProductNo = filter_item_fg_id ? "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) : "";

                var urlDetail =
                    '<?= base_url('purchase/por_subcont_productions/datatableDetails?receive_no=') ?>'
                    + window.btoa(row.receive_no)
                    + encodedProductNo;

                ddv.datagrid({
                    url: urlDetail,
                    singleSelect: true,
                    columns: [[
                        {
                            field:'no',
                            title:'No',
                            width:50,
                            align:'center',
                            formatter:function(value,row,index){
                                return index + 1;
                            }
                        },
                        {
                            field:'print',
                            title:'Print',
                            width:80,
                            align:'center',
                            formatter:BtnPrintLabel,
                        },
                        {
                            field:'item_fg_id',
                            title:'Product ID',
                            width:150,
                            align:'left'
                        },
                        {
                            field:'item_fg_number',
                            title:'Product No',
                            width:180
                        },
                        {
                            field:'item_fg_name',
                            title:'Product Name',
                            width:220
                        },
                        {
                            field:'uom',
                            title:'UOM',
                            width:80,
                            align:'center'
                        },
                        {
                            field:'qty_receive_dt',
                            title:'Qty Receive',
                            width:120,
                            align:'center',
                            formatter:numberformatInteger
                        },
                        {
                            field:'box_sub',
                            title:'Qty Per Pack',
                            width:120,
                            align:'center',
                            formatter:numberformatInteger
                        },
                        {
                            field:'qty_label',
                            title:'Qty Label',
                            width:120,
                            align:'center',
                            formatter:numberformatInteger
                        },
                        {
                            field:'compound_lot_no',
                            title:'Compound Lot No',
                            width:150,
                            align:'left',
                        },
                        {
                            field:'production_date',
                            title:'Production Date',
                            width:120,
                            align:'center'
                        },
                        {
                            field:'packing_date',
                            title:'Packing Date',
                            width:120,
                            align:'center'
                        },
                        {
                            field:'qc_name',
                            title:'QC',
                            width:120,
                            align:'center'
                        },
                        {
                            field:'status',
                            title:'Status POR',
                            width:100,
                            align:'center',
                            formatter:statusformat,
                            styler:statusStyle
                        },
                        {
                            field:'status_invoice',
                            title:'Status Invoice',
                            width:100,
                            align:'center',
                            formatter:statusformatFinance,
                            styler:statusStyleFinance
                        }
                    ]],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function(data) {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });


        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/por_subcont_productions/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_subcont_dn_no = $("#filter_subcont_dn_no").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_receive = $("#filter_receive").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_subcont_id=" + filter_subcont_id + "&filter_receive=" + filter_receive + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_subcont_dn_no=" + filter_subcont_dn_no;
        window.location.assign('<?= base_url('purchase/por_subcont_productions/print/excel') ?>' + url);
    }

    function print_receiving_note() {
        var receive_no = $("#filter_receive_no").combobox('getValue');
        if (receive_no == "") {
            toastr.warning("Please select Receive No!", "Information");
        } else {
            window.open("<?= base_url('purchase/por_subcont_productions/print_receiving/') ?>" + window.btoa(receive_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    function readReceiveNo() {
        $("#filter_receive_no").combobox({
            url: '<?= base_url('purchase/por_subcont_productions/readReceiveNo') ?>',
            valueField: 'receive_no',
            textField: 'receive_no',
            prompt: "Select Receive No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    $(function() {
        filter();

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All & Print Receiving Note',
                iconCls: 'icon-ok',
                handler: function() {
                    var receive_date = $("#receive_date").datebox('getValue');
                    var receive_no = $("#receive_no").textbox('getValue');
                    var subcont_dn_no = $("#subcont_dn_no").textbox('getValue');
                    var subcont_dn_date = $("#subcont_dn_date").datebox('getValue');
                    var subcont_id = $("#subcont_id").combogrid('getValue');
                    var po_no = $("#po_no").combogrid('getValue');

                    if (subcont_dn_no == "" || subcont_dn_date == "") {
                        toastr.warning("Please input Doc No and Doc Date!", "Information");
                    } else {
                        let isProcessed;

                        var dg = $('#dg2');
                        if (!endEditing()) {
                            toastr.warning("Please complete all required fields", "Information");
                            return;
                        }
                        var rows = dg.datagrid('getRows');
                        var compoundLotKeys = {};

                        for (var i = 0; i < rows.length; i++) {

                            if (!dg.datagrid('validateRow', i)) {
                                toastr.warning("Please complete all required fields", "Information");
                                return;
                            }

                            var row = rows[i];
                            var item_fg_number = row.item_fg_number ? row.item_fg_number.toString().trim() : "";
                            var compound_lot_no = row.compound_lot_no ? row.compound_lot_no.toString().trim() : "";

                            if ((parseInt(row.qty_receive) === 0 && parseInt(row.qty_po) !== 0) ||
                                (parseInt(row.qty_receive) === 0 && parseInt(row.qty_os) !== 0)) {
                                toastr.warning("Qty Receipt cannot be 0", "Information");
                                return;
                            }

                            if (!compound_lot_no) {
                                toastr.warning("Compound Lot No is required", "Information");
                                return;
                            }

                            var compoundLotKey = item_fg_number + "|" + compound_lot_no;
                            if (compoundLotKeys[compoundLotKey]) {
                                toastr.warning("Compound Lot No must be unique for the same Product No", "Information");
                                return;
                            }
                            compoundLotKeys[compoundLotKey] = true;

                            if (parseInt(row.qty_os) < parseInt(row.qty_receive)) {
                                toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                return;
                            }

                            if (parseInt(row.qty_po) < parseInt(row.qty_receive)) {
                                toastr.warning("Qty Receipt > Qty PO", "Information");
                                return;
                            }
                        }

                        if (rows.length > 0) {
                            $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                                if (r) {
                                    for (var i = 0; i < rows.length; i++) {
                                        let row = rows[i];

                                        isProcessed = 1;
                                        $.ajax({
                                            type: "post",
                                            url: '<?= base_url('purchase/por_subcont_productions/create') ?>',
                                            data: {
                                                item_fg_id: row.item_fg_id,
                                                subcont_id: subcont_id,
                                                receive_date: receive_date,
                                                receive_no: receive_no,
                                                po_no: po_no,
                                                subcont_dn_no: subcont_dn_no,
                                                subcont_dn_date: subcont_dn_date,
                                                qty_po: row.qty_po,
                                                qty_os: row.qty_os,
                                                qty_receive: row.qty_receive,
                                                box_sub: row.box_sub,
                                                qty_label: row.qty_label,
                                                compound_lot_no: row.compound_lot_no,
                                                production_date: row.production_date,
                                                shift: row.shift,
                                                packing_date: row.packing_date,
                                                qc_name: row.qc_name
                                            },
                                            dataType: "json",
                                            async: false,
                                            success: function(result) {
                                                Swal.fire({
                                                    title: result.message,
                                                    icon: result.theme,
                                                    confirmButtonText: 'Ok',
                                                    allowOutsideClick: false,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        Swal.fire({
                                                            title: 'Are you going to print the label?',
                                                            icon: 'question',
                                                            showCancelButton: true,
                                                            confirmButtonText: 'Yes, Print',
                                                            cancelButtonText: 'Cancel'
                                                        }).then((printResult) => {
                                                            if (printResult.isConfirmed) {
                                                                var receive_no = $("#receive_no").textbox('getValue');
                                                                var qty_receive = row ? row.qty_receive : 0;
                                                                var qty_label = row ? row.qty_label : 0;
                                                                
                                                                var po = {
                                                                    receive_no: receive_no,
                                                                    qty_receive: qty_receive,
                                                                    qty_label: qty_label
                                                                };

                                                                if (printResult.isConfirmed) {
                                                                    print_po(po);
                                                                }
                                                                window.location.reload();
                                                            }
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                    if (isProcessed === 1) {
                                        $('#dg').datagrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    }
                                }
                            });
                        } else {
                            toastr.warning("Please select one of the data in the table first!", "Information");
                        }
                    }
                }
            }]
        });

        $("#receive_date").datebox({
            onSelect: function(date) {
                receive_no(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
            }
        });
        readReceiveNo();
        
        $('#filter_subcont_id').combogrid({
            url: '<?= base_url('purchase/po_subcont_productions/readSubcontProduction'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Subcont",
            columns: [
                [{
                    field: 'id',
                    title: 'Subcont ID',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Subcont Code',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Subcont Name',
                    width: 200
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(index, row) {
            
                $("#filter_receive").combobox({
                    url: '<?= base_url('purchase/por_subcont_productions/readReceive/') ?>' + window.btoa(row.id),
                    valueField: 'receive_no',
                    textField: 'receive_no',
                    prompt: "Select Receive No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_item_fg_id').combogrid({
                    url: '<?= base_url("purchase/por_subcont_productions/readProducts/") ?>' + window.btoa(row.id),
                    panelWidth: 400,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    filter: function(q, row){
                        var opts = $(this).combogrid('options');
                        return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) >= 0 || 
                            row['name'].toLowerCase().indexOf(q.toLowerCase()) >= 0;
                    },
                    fitColumns: true,
                    prompt: "Choose Product No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'id',
                            title: 'Product ID',
                            width: 150
                        },{
                            field: 'number',
                            title: 'Product No',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Product Name',
                            width: 200
                        }]
                    ],
                });
                $("#filter_subcont_dn_no").combobox({
                    url: '<?= base_url('purchase/por_subcont_productions/readSubcontDnNo/') ?>' + window.btoa(row.id),
                    valueField: 'subcont_dn_no',
                    textField: 'subcont_dn_no',
                    prompt: "Select Document No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/por_subcont_productions/readPoNo/') ?>' + window.btoa(row.id),
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: "Select PO No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });


        $('#subcont_id').combogrid({
            url: '<?= base_url('purchase/po_subcont_productions/readSubcontProduction'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Subcont",
            columns: [
                [{
                    field: 'id',
                    title: 'Subcont ID',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Subcont Code',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Subcont Name',
                    width: 200
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onSelect: function(val, row) {
                $('#po_no').combogrid({
                    url: '<?= base_url('purchase/por_subcont_productions/readPoNoOnAddPOR?subcont_id=') ?>' + row.id,
                    panelWidth: 500,
                    idField: 'po_no',
                    textField: 'po_no',
                    valueField: 'po_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Choose Purchase Order",
                    columns: [
                        [{
                            field: 'po_no',
                            title: 'PO No',
                            width: 120
                        }, {
                            field: 'po_date',
                            title: 'PO Date',
                            width: 150
                        }]
                    ],
                    onSelect: function(row) {
                        var selectedRows = $("#po_no").combobox('getValues');

                        addTable(selectedRows);
                    }
                });
            }
        });
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
        if (row.status == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (row.status == 1) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
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
        console.log('ROW : ', row);
        
        if (val != "closed") {
            return `
                <a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" href="javascript:void(0)" onclick="showPrintOptions('${row.receive_id}')">
                    <i class="fa fa-print"></i>
                </a>`;
        }
    }

    function showPrintOptions(receiveId) {
        Swal.fire({
            title: 'Print Options',
            text: "Are you going to print the label?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, Print',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('<?= base_url('purchase/por_subcont_productions/print_label_rfg_2/') ?>' + window.btoa(receiveId), '_blank');
            }
        });
    }

    function print_po(po) {
        console.log(po);
        var url = '<?= base_url('purchase/por_subcont_productions/print_label_rfg/') ?>' + window.btoa(po.receive_no);
        window.open(url, '_blank');
    }

    function cellStyler(value, row, index) {
        return 'background: #d7ecff';
    }

    function numberformats(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return formatter.format(value);
        }
    }

    function numberformatInteger(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    };
</script>
