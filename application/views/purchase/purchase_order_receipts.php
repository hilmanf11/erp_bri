<style>
.datagrid-body td[field="qty_receipt"] .datagrid-cell {
    background-color: #d7ecff !important;
    margin: 0 !important;
}

.datagrid-body td[field="qty_receipt"] .textbox,
.datagrid-body td[field="qty_receipt"] .numberbox {
    width: 100% !important;
    box-sizing: border-box;
    margin: 0 !important;
    padding: 0 !important;
}

.datagrid-body td[field="qty_receipt"] .textbox-text {
    background-color: #d7ecff !important;
    width: 100% !important;
    box-sizing: border-box;
    padding: 3px !important;
    margin: 0 !important;
}

.datagrid-body td[field="qty_receipt"] .textbox-text.validatebox-invalid {
    background-color: #fff3f3 !important;
}

.datagrid-body td[field="qty_receipt"] .textbox-addon,
.datagrid-body td[field="qty_receipt"] .textbox-addon-right {
    display: none !important;
}

.datagrid-body td[field="qty_receipt"] .textbox,
.datagrid-body td[field="qty_receipt"] .numberbox {
    border-right: 2px solid #6891c8 !important;
}
</style>

<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:230,halign:'center'">Receipt No</th>
            <th rowspan="2" data-options="field:'total_scan',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status<br>POR</th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status<br>Invoice</th>
            <th rowspan="2" data-options="field:'po_no',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'receipt_date',width:100,halign:'center'">Receipt Date</th>
            <th colspan="4" data-options="field:'coslpan',halign:'center'">Supplier</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Part No External</th>
            <th rowspan="2" data-options="field:'item_number_internal',width:150,halign:'center'">Part No Internal</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'qty_receipt_dt',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,halign:'center',align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'currency',width:80,halign:'center',align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right'">MPQ</th>
            <th rowspan="2" data-options="field:'qty_label',width:80,halign:'center',align:'right'">Qty <br> Label</th>
            <th rowspan="2" data-options="field:'por_lot_no',width:200,halign:'center',align:'right'">Lot No</th>
            <th rowspan="2" data-options="field:'transaction_type',width:80,halign:'center',align:'right'">Trans Type</th>
            <th rowspan="2" data-options="field:'state',width:80,align:'center',formatter:BtnPrintLabel">Label</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'supplier_id',width:80,halign:'center'">ID</th>
            <th data-options="field:'supplier_name',width:200,halign:'center'">Name</th>
            <th data-options="field:'bc_document',width:200,halign:'center'">Document</th>
            <th data-options="field:'bc_date',width:80,halign:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 240px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 65%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" id="filter_receipt" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Print Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt No</span>
                <input style="width:60%;" id="filter_receipt_no" class="easyui-combobox">
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
                    <span style="width:35%; display:inline-block;">Receipt Date</span>
                    <input style="width:60%;" name="receipt_date" id="receipt_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Receipt No</span>
                    <input style="width:60%;" name="receipt_no" id="receipt_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" name="bc_document" id="bc_document" required="" class="easyui-textbox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div> -->
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc Date</span>
                    <input style="width:60%;" name="bc_date" id="bc_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" name="po_no" id="po_no" required="" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <!-- <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Order List" idField="item_number">
            <thead>
                <tr>
                    <th field="ck" checkbox="true"></th>
                    <th data-options="field:'item_number',width:150">Part No</th>
                    <th data-options="field:'item_name',width:200">Part Name</th>
                    <th data-options="field:'uom',width:80">UoM</th>
                    <th data-options="field:'qty_po',width:80,editor:{type:'numberbox', options:{readonly:true}}">PO</th>
                    <th data-options="field:'qty_os',width:80,editor:{type:'numberbox', options:{readonly:true}}">OS PO</th>
                    <th data-options="field:'qty_receipt',width:80,editor:{type:'numberbox'}">Receipt</th>
                    <th data-options="field:'mpq',width:80,editor:{type:'numberbox', options:{readonly:true}}">MPQ</th>
                    <th data-options="field:'qty_label',width:80,editor:{type:'numberbox', options:{readonly:true}}">Label</th>
                </tr>
            </thead>
        </table> -->

        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Request List" toolbar="#toolbar2"></table>

    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/purchase_order_receipts/print') ?>" style="width: 100%;" hidden></iframe>
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
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        receipt_no();
        $('#receipt_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#supplier_id").combobox('enable');
        $("#po_no").combobox('enable');
        $('#bc_document').textbox('enable');
        $('#receipt_no').textbox('clear');
        $('#bc_document').textbox('clear');
        $('#supplier_id').combobox('clear');
        $('#po_no').combobox('clear');
        url_save = '<?= base_url('purchase/purchase_order_receipts/create') ?>';
    }

    function addTable(po_no, link = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [
                    {
                        field: 'item_rm_id',
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
                        title: "Part No External",
                        editor: {
                            type: 'combogrid',
                            options: {
                                url: '<?= base_url('purchase/purchase_order_receipts/readItems?po_no=') ?>' + po_no,
                                required: true,
                                panelWidth: 320,
                                idField: 'item_number',
                                textField: 'item_number',
                                mode: 'remote',
                                fitColumns: true,
                                prompt: 'Choose Part No External',
                                columns: [
                                    [
                                        { field: 'item_number', title: 'Part No External', width: 150 },
                                        { field: 'item_name', title: 'Part Name', width: 150 }
                                    ]
                                ],
                                onSelect: function (value, rows) {
                                    var dg = $('#dg2');
                                    var row = dg.datagrid('getSelected');
                                    var rowIndex = dg.datagrid('getRowIndex', row);

                                    var isDuplicate = false;
                                    var allRows = dg.datagrid('getRows');

                                    
                                    for (var i = 0; i < allRows.length; i++) {
                                        if (i !== rowIndex && allRows[i].item_number === rows.item_number) {
                                            isDuplicate = true;
                                            break;
                                        }
                                    }

                                    // Mapping editor fields
                                    var editors = {
                                        item_rm_id: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'item_rm_id' 
                                        }),
                                        item_number: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'item_number' 
                                        }),
                                        item_name: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'item_name' 
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
                                        qty_receipt: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'qty_receipt' 
                                        }),
                                        mpq: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'mpq' 
                                        }),
                                        qty_label: dg.datagrid('getEditor', { 
                                            index: rowIndex, 
                                            field: 'qty_label' 
                                        })
                                    };

                                    // Set values from selected row
                                    $(editors.item_rm_id.target).textbox('setValue', rows.item_rm_id);
                                    $(editors.item_number.target).textbox('setValue', rows.item_number);
                                    $(editors.item_name.target).textbox('setValue', rows.item_name);
                                    $(editors.uom.target).textbox('setValue', rows.uom);
                                    $(editors.qty_po.target).numberbox('setValue', rows.qty_po);
                                    $(editors.qty_os.target).numberbox('setValue', rows.qty_os);
                                    $(editors.mpq.target).numberbox('setValue', rows.mpq);

                                    allRows[rowIndex].original_qty_os = parseFloat(rows.qty_os) || parseFloat(rows.qty_po) || 0;
                                    
                                    // $(editors.qty_receipt.target).numberbox('setValue', rows.qty_receipt);
                                    // $(editors.qty_label.target).numberbox('setValue', rows.qty_label);

                                    if (isDuplicate) {
                                        // Hitung total qty_receipt dari item yang sama (selain current row)
                                        var totalReceipt = 0;
                                        for (var i = 0; i < allRows.length; i++) {
                                            if (i !== rowIndex && allRows[i].item_number === rows.item_number) {
                                                totalReceipt += parseFloat(allRows[i].qty_receipt) || 0;
                                            }
                                        }

                                        var originalOS = parseFloat(rows.qty_os) || parseFloat(rows.qty_po) || 0;
                                        var sisa_os = originalOS - totalReceipt;
                                        if (sisa_os < 0) sisa_os = 0;

                                        $(editors.qty_os.target).numberbox('setValue', sisa_os);
                                        $(editors.qty_receipt.target).numberbox('setValue', sisa_os);

                                        var f_mpq = parseInt(rows.mpq) || 0;
                                        var label = f_mpq > 0 ? Math.ceil(sisa_os / f_mpq) : 0;
                                        $(editors.qty_label.target).numberbox('setValue', label);

                                        toastr.info("Duplicate Item, Qty Receipt set remaining : " + sisa_os, "Info");
                             
                                    } else {
                                        // Bukan duplikat, ambil qty_receipt asli dari hasil pencarian
                                        $(editors.qty_receipt.target).numberbox('setValue', rows.qty_receipt);

                                        var f_mpq = parseInt(rows.mpq) || 0;
                                        var f_receipt = parseInt(rows.qty_receipt) || 0;
                                        var label = f_mpq > 0 ? Math.ceil(f_receipt / f_mpq) : 0;
                                        $(editors.qty_label.target).numberbox('setValue', label);
                                    }
                                }
                            }
                        }
                    },
                    {
                        field: 'item_name',
                        width: 150,
                        halign: 'center',
                        title: "Part Name",
                        editor: { type: 'textbox' }
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
                                precision: 2
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
                                precision: 2
                            }
                        }
                    },
                    {
                        field: 'qty_receipt',
                        width: 80,
                        halign: 'center',
                        title: "Receipt",
                        styler: cellStyler,
                        editor: {
                            type: 'numberbox',
                            options: { 
                                required: true,
                                precision: 2 ,
                                validType: 'greaterThanZero'
                            }
                        }
                    },
                    {
                        field: 'mpq',
                        width: 80,
                        halign: 'center',
                        title: "MPQ",
                        editor: {
                            type: 'numberbox',
                            options: { precision: 2 }
                        }
                    },
                    {
                        field: 'qty_label',
                        width: 80,
                        halign: 'center',
                        title: "Label",
                        editor: {
                            type: 'numberbox',
                            options: { readonly: true }
                        }
                    },
                    {
                        field: 'lot_no',
                        width: 170,
                        halign: 'center',
                        title: "Lot No",
                        editor: {
                            type: 'textbox',
                            options: { 
                                required: true,
                                validType: 'length[1,30]'
                            }
                        }
                    }
                ]
            ],
            onClickRow: function (rowIndex) {
                var dg = $(this);
                endAllEdit(dg);
                var rowCount = dg.datagrid('getRows').length;

                for (var i = 0; i < rowCount; i++) {
                    if (dg.datagrid('getEditors', i).length) {
                        dg.datagrid('endEdit', i);
                    }
                }

                dg.datagrid('beginEdit', rowIndex);
                lastIndex = rowIndex;
            },
            onBeginEdit: function (rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);

                var qty_po = $(editors[4].target); // PO
                var qty_os = $(editors[5].target); // OS PO
                var qty_receipt = $(editors[6].target); // Receipt
                var mpq = $(editors[7].target); // MPQ
                var qty_label = $(editors[8].target); // Label

                qty_receipt.numberbox({
                    onChange: function () {
                        var dg = $('#dg2');
                        var rows = dg.datagrid('getRows');
                        var currentRow = dg.datagrid('getSelected');
                        var currentIndex = dg.datagrid('getRowIndex', currentRow);
                        var editors = dg.datagrid('getEditors', currentIndex);

                        // Ambil value langsung dari editor baris saat ini
                        var editor_item_number = dg.datagrid('getEditor', { index: currentIndex, field: 'item_number' });
                        var item_number = $(editor_item_number.target).textbox('getValue');

                        // Jika tetap kosong, abaikan validasi
                        if (!item_number) return;

                        var qty_po = $(editors[4].target);     // PO
                        var qty_os = $(editors[5].target);     // OS PO
                        var qty_receipt = $(editors[6].target); // Receipt
                        var mpq = $(editors[7].target);        // MPQ
                        var qty_label = $(editors[8].target);  // Label

                        var f_qty_po = parseInt(qty_po.numberbox('getValue')) || 0;
                        var f_qty_os = parseInt(qty_os.numberbox('getValue')) || 0;
                        var f_qty_receipt = parseInt(qty_receipt.numberbox('getValue')) || 0;
                        var f_mpq = parseInt(mpq.numberbox('getValue')) || 0;

                        if (f_qty_receipt == 0 || f_qty_receipt == "" || f_qty_receipt == 0.00) {
                            qty_receipt.numberbox('setValue', 0.00);
                            qty_label.numberbox('setValue', 0);
                            
                            qty_receipt.numberbox('textbox').validatebox('validate');
                            return;
                        }

                        // Hitung total receipt dari semua baris untuk item yang sama
                        var total_receipt = 0;
                        for (var i = 0; i < rows.length; i++) {
                            var row_item_number = rows[i].item_number;

                            if (i === currentIndex) {
                                total_receipt += f_qty_receipt; // current edit value
                            } else if (row_item_number === item_number) {
                                total_receipt += parseInt(rows[i].qty_receipt) || 0;
                            }
                        }
                        
                        if (f_qty_os === 0) {
                            if (f_qty_receipt > f_qty_po) {
                                toastr.warning("Qty Receipt > Qty PO", "Information");
                                qty_receipt.numberbox('setValue', 0);
                                qty_label.numberbox('setValue', 0);
                                return;
                            }
                        } else {
                            if (f_qty_receipt > f_qty_os) {
                                toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                qty_receipt.numberbox('setValue', 0);
                                qty_label.numberbox('setValue', 0);
                                return;
                            }
                        }

                        var originalLimit = 0;
                        for (var i = 0; i < rows.length; i++) {
                            if (rows[i].item_number === item_number && rows[i].original_qty_os) {
                                originalLimit = rows[i].original_qty_os;
                                break;
                            }
                        }
                        if (originalLimit === 0) {
                            originalLimit = f_qty_po; // fallback
                        }

                        if (total_receipt > originalLimit) {
                            toastr.warning("Total Qty Receipt for item " + item_number + " exceeds the limit of " + originalLimit, "Warning");
                            qty_receipt.numberbox('setValue', 0);
                            qty_label.numberbox('setValue', 0);
                            return;
                        }

                        // Hitung qty label jika valid
                        var label_qty = f_mpq > 0 ? Math.ceil(f_qty_receipt / f_mpq) : 0;
                        qty_label.numberbox('setValue', label_qty);
                    }
                });

                // var lot_no = $(editors[9].target);
                // lot_no.textbox({
                //     onChange: function () {
                //         var dg = $('#dg2');
                //         var rows = dg.datagrid('getRows');
                //         var currentRow = dg.datagrid('getSelected');
                //         var currentIndex = dg.datagrid('getRowIndex', currentRow);
                //         var editors = dg.datagrid('getEditors', currentIndex);

                //         var editor_item_number = dg.datagrid('getEditor', { index: currentIndex, field: 'item_number' });
                //         var item_number = $(editor_item_number.target).textbox('getValue');
                //         var lot_value = lot_no.textbox('getValue').trim();

                //         if (!item_number || !lot_value) return;

                //         for (var i = 0; i < rows.length; i++) {
                //             if (i !== currentIndex && rows[i].item_number === item_number && rows[i].lot_no === lot_value) {
                //                 toastr.warning("Lot No must be unique for the same Part No External", "Information");
                //                 lot_no.textbox('setValue', '');
                //                 return;
                //             }
                //         }
                //     }
                // });

                var thisIndex = rowIndex;
                var lot_no = $(editors[9].target);

                lot_no.textbox({
                    onChange: function (newVal, oldVal) {
                        var dg = $('#dg2');
                        var rows = dg.datagrid('getRows');
                        var lot_value = (newVal || '').toString().trim();

                        var ed_item = dg.datagrid('getEditor', { index: thisIndex, field: 'item_number' });
                        var item_number = ed_item
                        ? $(ed_item.target).textbox('getValue').toString().trim()
                        : (rows[thisIndex] && rows[thisIndex].item_number ? rows[thisIndex].item_number.toString().trim() : '');

                        if (!item_number || !lot_value) return; // kalau kosong skip validasi

                        for (var i = 0; i < rows.length; i++) {
                        if (i === thisIndex) continue;

                        // ambil item_number baris i (editor jika sedang diedit, else rows)
                        var ed_item_i = dg.datagrid('getEditor', { index: i, field: 'item_number' });
                        var compare_item = ed_item_i
                            ? $(ed_item_i.target).textbox('getValue').toString().trim()
                            : (rows[i].item_number ? rows[i].item_number.toString().trim() : '');

                        if (compare_item !== item_number) continue;

                        // ambil lot_no baris i (editor jika sedang diedit, else rows)
                        var ed_lot_i = dg.datagrid('getEditor', { index: i, field: 'lot_no' });
                        var compare_lot = ed_lot_i
                            ? $(ed_lot_i.target).textbox('getValue').toString().trim()
                            : (rows[i].lot_no ? rows[i].lot_no.toString().trim() : '');

                        if (!compare_lot) continue; // kosong dianggap tidak ada

                        if (compare_lot === lot_value) {
                            toastr.warning("Lot No must be unique for the same Part No External", "Information");
                            lot_no.textbox('setValue', ''); // clear input
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
        var po_no = $("#po_no").combobox('getValue');
        if (po_no != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    receipt: ''
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose PO No first");
        }
    }

    // function removeit() {
    //     if (editIndex == undefined) {
    //         return true;
    //     }
    //     $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
    //     editIndex = undefined;
    // }

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

    function receipt_no(date = "") {
        $.ajax({
            type: "post",
            url: "<?= base_url('purchase/purchase_order_receipts/receipt_no/') ?>" + window.btoa(date),
            dataType: "html",
            success: function(result) {
                $("#receipt_no").textbox('setValue', result);
            }
        });
    }

    // function preview() {
    //     var po_no = $("#po_no").combogrid('getValue');
    //     if (po_no === "") {
    //         toastr.warning('Please select PO No', 'Required');
    //     } else {
    //         var lastIndex;
    //         var dg = $('#dg_request').datagrid({
    //             url: '<?= base_url('purchase/purchase_order_receipts/datatablesTemp') ?>?po_no=' + po_no,
    //             fitColumns: true,
    //             onClickRow: function(rowIndex) {
    //                 if (lastIndex !== rowIndex) {
    //                     $(this).datagrid('endEdit', lastIndex);
    //                     $(this).datagrid('beginEdit', rowIndex);
    //                 }
    //                 lastIndex = rowIndex;
    //             },
    //             onBeginEdit: function(rowIndex, row) {
    //                 var editors = $('#dg_request').datagrid('getEditors', rowIndex);
    //                 var qty_po = $(editors[0].target);
    //                 var qty_os = $(editors[1].target);
    //                 var qty_receipt = $(editors[2].target);
    //                 var qty_mpq = $(editors[3].target);
    //                 var qty_label = $(editors[4].target);

    //                 qty_receipt.add(qty_mpq).numberbox({
    //                     onChange: function() {
    //                         var f_qty_po = parseInt(qty_po.numberbox('getValue')) || 0;
    //                         var f_qty_os = parseInt(qty_os.numberbox('getValue')) || 0;
    //                         var f_qty_receipt = parseInt(qty_receipt.numberbox('getValue')) || 0;
    //                         var f_qty_mpq = parseInt(qty_mpq.numberbox('getValue')) || 0;

    //                         if (f_qty_os === 0) {
    //                             if (f_qty_po >= f_qty_receipt) {
    //                                 var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
    //                                 qty_label.numberbox('setValue', cost);
    //                             } else {
    //                                 qty_receipt.numberbox('setValue', 0);
    //                                 toastr.warning("Qty Receipt > Qty PO", "Information");
    //                             }
    //                         } else {
    //                             if (f_qty_os >= f_qty_receipt) {
    //                                 var cost = Math.ceil(f_qty_receipt / f_qty_mpq);
    //                                 qty_label.numberbox('setValue', cost);
    //                             } else {
    //                                 qty_receipt.numberbox('setValue', 0);
    //                                 toastr.warning("Qty Receipt > Qty OS PO", "Information");
    //                             }
    //                         }
    //                     }
    //                 });
    //             }
    //         }).datagrid('enableFilter', [{
    //             field: 'item_number',
    //             type: 'text',
    //             options: {
    //                 onChange: function(value) {
    //                     if (value) {
    //                         dg.datagrid('load', {
    //                             item_number: value,
    //                             po_no: po_no
    //                         });
    //                     }
    //                 }
    //             }
    //         }, {
    //             field: 'item_name',
    //             type: 'text',
    //             options: {
    //                 onChange: function(value) {
    //                     if (value) {
    //                         dg.datagrid('load', {
    //                             item_name: value,
    //                             po_no: po_no
    //                         });
    //                     }
    //                 }
    //             }
    //         }, {
    //             field: 'uom',
    //             type: 'text',
    //             options: {
    //                 onChange: function(value) {
    //                     if (value) {
    //                         dg.datagrid('load', {
    //                             uom: value,
    //                             po_no: po_no
    //                         });
    //                     }
    //                 }
    //             }
    //         }, {
    //             field: 'qty_po',
    //             type: 'numberbox',
    //             options: {
    //                 precision: 1,
    //                 onChange: function(value) {
    //                     dg.datagrid('load', {
    //                         qty_po: value,
    //                         po_no: po_no
    //                     });
    //                 }
    //             }
    //         }, {
    //             field: 'qty_os',
    //             type: 'numberbox',
    //             options: {
    //                 precision: 1,
    //                 onChange: function(value) {
    //                     dg.datagrid('load', {
    //                         qty_os: value,
    //                         po_no: po_no
    //                     });
    //                 }
    //             }
    //         }, {
    //             field: 'qty_receipt',
    //             type: 'numberbox',
    //             options: {
    //                 precision: 1,
    //                 onChange: function(value) {
    //                     dg.datagrid('load', {
    //                         qty_receipt: value,
    //                         po_no: po_no
    //                     });
    //                 }
    //             }
    //         }, {
    //             field: 'mpq',
    //             type: 'numberbox',
    //             options: {
    //                 precision: 1,
    //                 onChange: function(value) {
    //                     dg.datagrid('load', {
    //                         mpq: value,
    //                         po_no: po_no
    //                     });
    //                 }
    //             }
    //         }, {
    //             field: 'qty_label',
    //             type: 'numberbox',
    //             options: {
    //                 precision: 1,
    //                 onChange: function(value) {
    //                     dg.datagrid('load', {
    //                         qty_label: value,
    //                         po_no: po_no
    //                     });
    //                 }
    //             }
    //         }]);
    //     }
    // }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of POR <br>" + row.id);
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('purchase/purchase_order_receipts/delete') ?>',
                                data: {
                                    id: row.purchase_order_receipts_id,
                                    receipt_id: row.id,
                                    po_no: row.po_no,
                                    item_rm_id: row.item_rm_id,
                                    qty_receipt: row.qty_receipt
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    if (result.error) {
                                        toastr.error(result.error);
                                    }
                                    readReceiptNo();
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
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + "&filter_product_no=" + filter_product_no + "&filter_doc_no=" + filter_doc_no;
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_receipts/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/purchase_order_receipts/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_receipt = $("#filter_receipt").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_po_no=" + filter_po_no + "&filter_supplier=" + filter_supplier + "&filter_receipt=" + filter_receipt + "&filter_product_no=" + filter_product_no + "&filter_doc_no=" + filter_doc_no;
        window.location.assign('<?= base_url('purchase/purchase_order_receipts/print/excel') ?>' + url);
    }

    function print_receiving_note() {
        var receipt_no = $("#filter_receipt_no").combobox('getValue');
        if (receipt_no == "") {
            toastr.warning("Please select Receipt No!", "Information");
        } else {
            // $.ajax({
            //     type: "post",
            //     url: "<?= base_url('purchase/purchase_order_receipts/checkLabel/') ?>" + window.btoa(receipt_no),
            //     dataType: "json",
            //     success: function(response) {
            //         if (response.qty_label == response.label_no) {
            //             window.open("<?= base_url('purchase/purchase_order_receipts/print_receiving/') ?>" + window.btoa(receipt_no), "_blank");
            //         } else {
            //             toastr.error("The labels haven't been scanned yet");
            //         }
            //     }
            // });
            window.open("<?= base_url('purchase/purchase_order_receipts/print_receiving/') ?>" + window.btoa(receipt_no), "_blank");
        }
    }

    function reload() {
        window.location.reload();
    }

    function readReceiptNo() {
        $("#filter_receipt_no").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readReceiptNo') ?>',
            valueField: 'receipt_no',
            textField: 'receipt_no',
            prompt: "Select Receipt No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    }

    $(function() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to
        $('#dg').treegrid({
            url: '<?= base_url('purchase/purchase_order_receipts/datatables') ?>' + url,
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

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All & Print Receiving Note',
                iconCls: 'icon-ok',
                handler: function() {
                    var receipt_date = $("#receipt_date").datebox('getValue');
                    var receipt_no = $("#receipt_no").textbox('getValue');
                    var bc_document = $("#bc_document").textbox('getValue');
                    var bc_date = $("#bc_date").datebox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var po_no = $("#po_no").combogrid('getValue');

                    if (bc_document == "" || bc_date == "") {
                        toastr.warning("Please input Doc No and Doc Date!", "Information");
                    } else {
                        let isProcessed;
                        // $('#dg2').datagrid('acceptChanges');
                        // var rows = $('#dg2').datagrid('getRows');

                        var dg = $('#dg2');
                        endAllEdit(dg);
                        var rows = dg.datagrid('getRows');

                        for (var i = 0; i < rows.length; i++) {

                            if (!dg.datagrid('validateRow', i)) {
                                toastr.warning("Please complete all required fields", "Information");
                                return;
                            }

                            var row = rows[i];

                            if ((parseInt(row.qty_receipt) === 0 && parseInt(row.qty_po) !== 0) ||
                                (parseInt(row.qty_receipt) === 0 && parseInt(row.qty_os) !== 0)) {
                                toastr.warning("Qty Receipt cannot be 0", "Information");
                                return;
                            }

                            if (!row.lot_no || row.lot_no.trim() === "") {
                                toastr.warning("Lot No is required", "Information");
                                return;
                            }

                            if (parseInt(row.qty_os) < parseInt(row.qty_receipt)) {
                                toastr.warning("Qty Receipt > Qty OS PO", "Information");
                                return;
                            }

                            if (parseInt(row.qty_po) < parseInt(row.qty_receipt)) {
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
                                            url: '<?= base_url('purchase/purchase_order_receipts/create') ?>',
                                            data: 'item_rm_id=' + row.item_rm_id +
                                                '&supplier_id=' + supplier_id +
                                                '&receipt_date=' + receipt_date +
                                                '&receipt_no=' + receipt_no +
                                                '&po_no=' + po_no +
                                                '&bc_document=' + bc_document +
                                                '&bc_date=' + bc_date +
                                                '&qty_po=' + row.qty_po +
                                                '&qty_os=' + row.qty_os +
                                                '&qty_receipt=' + row.qty_receipt +
                                                '&qty_mpq=' + row.mpq +
                                                '&qty_label=' + row.qty_label +
                                                '&lot_no=' + row.lot_no,
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
                                                            title: 'Select print barcode mode!',
                                                            icon: 'question',
                                                            showCancelButton: true,
                                                            showDenyButton: true,
                                                            confirmButtonText: 'Single',
                                                            denyButtonText: 'Multiple',
                                                            cancelButtonText: 'Cancel'
                                                        }).then((printResult) => {
                                                            if (printResult.isConfirmed || printResult.isDenied) {
                                                                var receipt_no = $("#receipt_no").textbox('getValue');
                                                                var qty_receipt = row ? row.qty_receipt : 0;
                                                                var qty_label = row ? row.qty_label : 0;
                                                                
                                                                var po = {
                                                                    receipt_no: receipt_no,
                                                                    qty_receipt: qty_receipt,
                                                                    qty_label: qty_label
                                                                };
                                                                // CEK
                                                                if (printResult.isConfirmed) {
                                                                    print_po(po);
                                                                } else if (printResult.isDenied) {
                                                                    print_po_multiple(po);
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
                                        $('#dg').treegrid('reload');
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

        $("#receipt_date").datebox({
            onSelect: function(date) {
                receipt_no(date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate());
            }
        });
        readReceiptNo();
        $("#filter_supplier").combobox({
            url: '<?= base_url('purchase/purchase_order_receipts/readSupplier') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Select All",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(supp) {
                $("#filter_receipt").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readReceipt/') ?>' + window.btoa(supp.id),
                    valueField: 'receipt_no',
                    textField: 'receipt_no',
                    prompt: "Select Receipt No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_product_no').combogrid({
                    url: '<?= base_url("purchase/purchase_order_receipts/readPart/") ?>' + window.btoa(supp.id),
                    panelWidth: 400,
                    idField: 'name',
                    textField: 'name',
                    mode: 'remote',
                    filter: function(q, row){
                        var opts = $(this).combogrid('options');
                        return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) >= 0 || 
                            row['name'].toLowerCase().indexOf(q.toLowerCase()) >= 0;
                    },
                    fitColumns: true,
                    prompt: "Select Part Name",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'number',
                            title: 'Part No External',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Part Name',
                            width: 200
                        }]
                    ],
                });
                $("#filter_doc_no").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readDocno/') ?>' + window.btoa(supp.id),
                    valueField: 'bc_document',
                    textField: 'bc_document',
                    prompt: "Select Document No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
                $("#filter_po_no").combobox({
                    url: '<?= base_url('purchase/purchase_order_receipts/readPoNo/') ?>' + window.btoa(supp.id),
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

        $("#supplier_id").combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'number',
                    title: 'Supplier No',
                    width: 50
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            onSelect: function(val, row) {
                $('#po_no').combogrid({
                    url: '<?= base_url('purchase/purchase_orders/readPonoOnAddPOR?supplier_id=') ?>' + row.id,
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
                <a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" href="javascript:void(0)" onclick="showPrintOptions('${row.id}')">
                    <i class="fa fa-print"></i> Print
                </a>`;
        }
    }

    function showPrintOptions(receiptId) {
        Swal.fire({
            title: 'Print Options',
            text: "Select Print Barcode Mode!",
            icon: 'info',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Single',
            denyButtonText: 'Multiple',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('<?= base_url('purchase/purchase_order_receipts/print_label/') ?>' + window.btoa(receiptId), '_blank');
            } else if (result.isDenied) {
                window.open('<?= base_url('purchase/purchase_order_receipts/print_label_multiple/') ?>' + window.btoa(receiptId), '_blank');
            }
        });
    }

    function print_po(po) {
        console.log(po);
        var url = '<?= base_url('purchase/purchase_order_receipts/print_label_po/') ?>' + window.btoa(po.receipt_no);
        window.open(url, '_blank');
    }

    function print_po_multiple(po) {
        console.log(po);
        var url = '<?= base_url('purchase/purchase_order_receipts/print_label_po_multiple/') ?>' + window.btoa(po.receipt_no);
        window.open(url, '_blank');
    }

    function cellStyler(value, row, index) {
        return 'background: #d7ecff';
    }
</script>
