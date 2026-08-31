<!-- TABLE DATAGRID UTAMA (HEADER) -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'finishing_invoice_no',width:200,halign:'center',sortable:true">Invoice No</th>
            <th rowspan="2" data-options="field:'vendor_name',width:250,halign:'center'">Subcont / Teaching Factory</th>
            <th rowspan="2" data-options="field:'approved_to',width:120,halign:'center',align:'center',styler:styleApprovedStatus,formatter:formatApprovedStatus">Status Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:120,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:120,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'finishing_invoice_date',width:120,halign:'center',align:'center',sortable:true">Invoice Date</th>
            <th rowspan="2" data-options="field:'period_start',width:100,halign:'center',align:'center'">Period Start</th>
            <th rowspan="2" data-options="field:'period_end',width:100,halign:'center',align:'center'">Period End</th>
            <th rowspan="2" data-options="field:'total',width:120,halign:'center',align:'right'">Total</th>
            <th rowspan="2" data-options="field:'biaya_fee',width:120,halign:'center',align:'right'">Biaya Fee</th>
            <th rowspan="2" data-options="field:'grand_total',width:150,halign:'center',align:'right'">Grand Total</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 230px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Subcont</span>
                <input style="width:60%;" id="filter_subcont" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="print_pr()"><i class="fa fa-print"></i> Purchase Request</a> -->
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="printInvoice()"><i class="fa fa-print"></i> Print Invoice</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="printRecap()"><i class="fa fa-file-text-o"></i> Print Recap Invoice</a>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 900px; height: 100%; padding:10px; top: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox" data-options="prompt: 'Automatic'" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem f-expected-date">
                    <span style="width:35%; display:inline-block;">Expected Date</span>
                    <input style="width:60%;" name="expected_date" id="expected_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Category</span>
                    <input style="width:60%;" name="item_category_id" id="item_category_id" class="easyui-combobox" data-options="editable: false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_id" id="item_family_id" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" class="easyui-combobox" required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Purchase Request List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/finishing_invoices/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        
        editIndex = undefined;

        $("#item_category_id").combobox('enable');
        $("#request_date").datebox('enable');
        $("#expected_date").datebox('enable');
        $("#item_family_id").combobox('enable');
        $('#request_no').textbox('clear');
        $('#item_category_id').combobox('clear');
        $('#item_family_id').combobox('clear');
        $('#plant').combobox('clear');

        $('.f-expected-date').show();
        $('#frm_insert').data('mode', 'normal');
        url_save = '<?= base_url('warehouse/finishing_invoices/create') ?>';
    }

    editIndex = undefined;

    function addTable(item_family_id, link = "") {
        // var lastIndex;
        var dg = $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    readonly: true,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Part No External",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/supplier_items/readItems?item_family_id=') ?>' + item_family_id,
                            required: true,
                            panelWidth: 320,
                            idField: 'item_number',
                            textField: 'item_number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Part No External',
                            columns: [
                                [{
                                    field: 'item_number',
                                    title: 'Part No External',
                                    width: 150
                                }, {
                                    field: 'item_name',
                                    title: 'Part Name',
                                    width: 150
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                // var row = dg.datagrid('getSelected');
                                // var rowIndex = dg.datagrid('getRowIndex', row);

                                var rowIndex = editIndex;
                                if (rowIndex == undefined) {
                                    return;
                                }

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_rm_id'
                                });

                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });

                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'po'
                                });

                                $(ed.target).textbox('setValue', rows.item_rm_id);
                                $(ed2.target).textbox('setValue', rows.item_name);

                                // $.ajax({
                                //     type: "post",
                                //     url: "<?= base_url('warehouse/report_history_transactions/readEndingStock') ?>",
                                //     data: "item_rm_id=" + rows.id,
                                //     dataType: "json",
                                //     success: function(json) {
                                //         if (json != null) {
                                //             $(ed3.target).numberbox('setValue', json[0].end_stock);
                                //         } else {
                                //             $(ed3.target).numberbox('setValue', 0);
                                //         }
                                //     }
                                // });

                                $.ajax({
                                    type: "post",
                                    url: "<?= base_url('purchase/purchase_orders/readTotalPo') ?>",
                                    data: "item_rm_id=" + rows.id,
                                    dataType: "json",
                                    success: function(jsonpo) {
                                        if (jsonpo != null) {
                                            $(ed4.target).numberbox('setValue', jsonpo.qty);
                                        } else {
                                            $(ed4.target).numberbox('setValue', 0);
                                        }
                                    }
                                });
                            }
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    readonly: true,
                    halign: 'center',
                    title: "Part Name",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_rm_id',
                    hidden: true,
                    width: 100,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'stock',
                    width: 80,
                    halign: 'center',
                    title: "Stock",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 2
                        }
                    }
                }, {
                    field: 'po',
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
                }, {
                    field: 'remarks',
                    width: 200,
                    halign: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            // onClickRow: function(rowIndex) {
            //     if (lastIndex != rowIndex) {
            //         $(this).datagrid('endEdit', lastIndex);
            //         $(this).datagrid('beginEdit', rowIndex);
            //     }
            //     lastIndex = rowIndex;
            // },
            onClickRow: function(index) {

                if (editIndex !== index) {

                    if (!endEditing()) {
                        $(this).datagrid('selectRow', editIndex);
                        return;
                    }

                    $(this).datagrid('selectRow', index)
                        .datagrid('beginEdit', index);

                    editIndex = index;
                }
            },

            onBeginEdit: function(rowIndex, row) {
                var editors = $('#dg2').datagrid('getEditors', rowIndex);
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

        var item_family_id = $("#item_family_id").combobox('getValue');
        if (!item_family_id) {
            toastr.error("Please Choose Product Family first");
            return;
        }

        if (!endEditing()) {
            return;
        }

        $('#dg2').datagrid('appendRow', {
            qty: ''
        });

        editIndex = $('#dg2').datagrid('getRows').length - 1;
        $('#dg2')
            .datagrid('selectRow', editIndex)
            .datagrid('beginEdit', editIndex);
    }

    function removeit() {

        var dg = $('#dg2');

        if (editIndex == undefined) {

            var row = dg.datagrid('getSelected');

            if (!row) {
                toastr.warning("Please select one of the data in the table first!");
                return;
            }

            editIndex = dg.datagrid('getRowIndex', row);
        }

        var row = dg.datagrid('getRows')[editIndex];

        function finishDelete() {

            dg.datagrid('cancelEdit', editIndex);
            dg.datagrid('deleteRow', editIndex);

            var rows = dg.datagrid('getRows');

            if (rows.length > 0) {

                var nextIndex = editIndex;

                if (nextIndex >= rows.length) {
                    nextIndex = rows.length - 1;
                }

                dg.datagrid('selectRow', nextIndex);
                dg.datagrid('beginEdit', nextIndex);

                editIndex = nextIndex;

            } else {

                editIndex = undefined;
            }
        }

        if (!row.id || $('#frm_insert').data('mode') !== 'update') {
            finishDelete();
            return;
        }

        // $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r){

        //     if (!r) return;

            $.ajax({

                method:'post',
                url:'<?= base_url('warehouse/finishing_invoices/delete') ?>',
                data:{
                    id:row.id
                },
                dataType:'json',

                success:function(result){

                    finishDelete();

                    $('#dg').datagrid('reload');

                    toastr.success(result.message);

                },

                error:function(jqXHR){

                    toastr.error(jqXHR.statusText);

                }

            });

        // });

    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if (row.datatable == "1") {
                if (row.status == "0") {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    $('#frm_insert').data('mode', 'update');
                    // $("#item_family_id").combobox('disable');
                    $("#item_category_id").combobox('disable');
                    $("#request_date").datebox('disable');
                    $("#expected_date").datebox('disable');
                    $('.f-expected-date').show();


                    url_save = '<?= base_url('warehouse/finishing_invoices/update') ?>';

                    setTimeout(function() {
                        $('#request_no').textbox('setValue', row.request_no);
                        $("#item_category_id").combobox('setValue', row.category_id);
                        $("#item_family_id").combobox('setValue', row.item_family_id);
                        $("#plant").combobox('setValue', row.plant_id);
                        // $("#plant").combobox('setText', row.plant);
                    }, 500);

                    addTable(row.item_family_number, '<?= base_url('warehouse/finishing_invoices/datatable_updates?request_no=') ?>' + window.btoa(row.request_no));
                } else {
                    toastr.error("You cannot update this data, because status Purchase Request is CONVERTED");
                }
            } else {
                toastr.error("Please Select Header of PR <br>" + row.request_no);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.approved_to == "") {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('warehouse/finishing_invoices/delete') ?>',
                                data: {
                                    id: row.id
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
                                    $('#dg').datagrid('reload');
                                }
                            });
                        } else {
                            toastr.error("You cannot delete this data, because it already Approved");
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_finishing_invoices.xls') ?>');
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_subcont = $("#filter_subcont").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_invoice_no=" + filter_invoice_no + "&filter_subcont=" + filter_subcont;
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/finishing_invoices/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/finishing_invoices/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_subcont = $("#filter_subcont").combobox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_invoice_no=" + filter_invoice_no + "&filter_subcont=" + filter_subcont;
        window.location.assign('<?= base_url('warehouse/finishing_invoices/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to
        
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/finishing_invoices/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 20, 50, 100],
            pageSize: 10,
            view: detailview, 
            resizable: true,
            remoteSort: true,
            detailFormatter: function(index, row) {
                return '<div style="padding:5px;position:relative;"><table class="ddv" title="Detail Of ' + row.finishing_invoice_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('warehouse/finishing_invoices/datatableDetails?id=') ?>' + window.btoa(row.id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [[
                        {field: 'item_number', title: 'PART NUMBER', width: 150, halign: 'center'},
                        {field: 'item_name', title: 'PART NAME', width: 250, halign: 'center'},
                        {field: 'price', title: 'PRICE (Rp)', width: 100, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'qty', title: 'QTY FG', width: 80, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'price_fg', title: 'FG (Rp)', width: 120, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'qty_1', title: 'QTY DEFECT', width: 90, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'price_defect', title: 'DEFECT (Rp)', width: 120, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'sub_total', title: 'TOTAL PENDAPATAN', width: 130, halign: 'center', align: 'right', formatter: formatRupiah}
                    ]],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });
        $("#expected_date").datebox({
            onChange: function() {
                var request_date = $("#request_date").datebox('getValue');
                var expected_date = $("#expected_date").datebox('getValue');
                if (expected_date < request_date) {
                    $("#expected_date").datebox('clear');
                    toastr.warning("Request Date > Expected Date");
                }
            }
        });
        $("#request_date").datebox({
            onChange: function() {
                var request_date = $("#request_date").datebox('getValue');
                var expected_date = $("#expected_date").datebox('getValue');
                if (expected_date < request_date) {
                    $("#request_date").datebox('clear');
                    toastr.warning("Request Date < Expected Date");
                }
            }
        });
        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var expected_date = $("#expected_date").datebox('getValue');
                    var plant = $("#plant").combobox('getValue');

                    let mode = $('#frm_insert').data('mode') || 'normal';

                    $('#dg2').datagrid('acceptChanges');
                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        let expected_date_mode = (mode === 'additional') ? rows[i].expected_date : expected_date;
                        console.log('EXP : ', expected_date_mode);
                        if (rows[i].item_rm_id) {
                            $.ajax({
                                type: "post",
                                url: url_save,
                                data: {
                                    id: rows[i].id,
                                    item_rm_id: rows[i].item_rm_id,
                                    request_no: request_no,
                                    request_date: request_date,
                                    request_name: request_name,
                                    qty: rows[i].qty,
                                    expected_date: expected_date_mode,
                                    remarks: rows[i].remarks,
                                    division: plant
                                },
                                dataType: "json",
                                success: function(result) {
                                    Swal.fire({
                                        title: result.message,
                                        icon: result.theme,
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            });
                        }
                    }
                   
                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('warehouse/finishing_invoices/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('warehouse/finishing_invoices/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            $.ajax({
                                url: "<?= base_url('warehouse/finishing_invoices/uploadclearFailed') ?>"
                            });
                            
                            var json = eval('(' + result + ')');

                            if (json.theme == "error") {
                                $.messager.alert('Error', json.message, 'error');
                                $("#p_remarks").append("<b style='color: red;'>" + json.title + "</b> | " + json.message + "<br>");
                                return; 
                            }

                            var total_items = json.total_items;
                            $('#p_finish').html(total_items);
                            $('#p_start').html('Processing...');
                            $('#p_upload').progressbar('setValue', 50); 

                            $.ajax({
                                type: "POST",
                                async: true,
                                url: "<?= base_url('warehouse/finishing_invoices/uploadcreate') ?>",
                                data: {
                                    header: json.header,
                                    details: json.details
                                },
                                dataType: "json",
                                success: function(res) {
                                    $('#p_upload').progressbar('setValue', 100);
                                    $('#p_start').html(total_items);

                                    if (res.theme == "success") {
                                        $('#p_success').html(total_items);
                                        var title = "<b style='color: green;'>" + res.title + "</b> | " + res.message;
                                        $("#p_remarks").append(title + "<br>");
                                    } else {
                                        $('#p_failed').html(total_items);
                                        var title = "<b style='color: red;'>" + res.title + "</b> | " + res.message;
                                        $("#p_remarks").append(title + "<br>");
                                        
                                        $.ajax({
                                            type: "POST",
                                            url: "<?= base_url('warehouse/finishing_invoices/uploadcreateFailed') ?>",
                                            data: { message: res.message }
                                        });
                                    }
                                },
                                error: function() {
                                    $('#p_upload').progressbar('setValue', 100);
                                    $('#p_failed').html(total_items);
                                    $("#p_remarks").append("<b style='color: red;'>Error</b> | Terjadi kesalahan jaringan / server<br>");
                                }
                            });
                        }
                    });
                }
            }]
        });

        $("#filter_invoice_no").combobox({
            url: '<?= base_url('warehouse/finishing_invoices/readInvoice') ?>',
            valueField: 'finishing_invoice_no',
            textField: 'finishing_invoice_no',
            prompt: "Select Invoice No"
        });

        $("#filter_subcont").combobox({
            url: '<?= base_url('warehouse/finishing_invoices/readSubcont') ?>',
            valueField: 'subcont',
            textField: 'name',
            prompt: "Select Subcont"
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
        if (value == 0) {
            return "<b style='color:red;'>UNCONVERTED</b>";
        } else if (value == 1) {
            return "<b style='color:green;'>CONVERTED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFC8C8;';
        } else if (value == 1) {
            return 'background-color:#C8FFCC;';
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
    };


    function styleApprovedStatus(value, row) {
        value = formatApprovedStatus(value, row);

        switch (value) {
            case 'Checking':
                return 'background:#FF5F5F;color:#fff;';

            case 'Approved':
                return 'background:#53D636;color:#fff;';

            case 'Disapprove':
                return 'background:#FF0000;color:#fff;';
        }

        return '';
    }

    function formatApprovedStatus(value, row) {
        if (parseInt(row.deleted) === 2) {
            return 'Disapprove';
        }
        if (!row.approved_to || row.approved_to.trim() === '') {
            return 'Approved';
        }
        return 'Checking';
    }

    function formatRupiah(value, row, index) {
        if (value === null || value === undefined || value === '') {
            return '0';
        }
        var num = parseFloat(value);
        if (isNaN(num)) {
            return value;
        }

        return num.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function printInvoice() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            // Membuka halaman print Controller dengan melempar ID master invoice
            window.open('<?= base_url('warehouse/finishing_invoices/print_invoice/') ?>' + row.id, '_blank');
        } else {
            $.messager.alert('Warning', 'Pilih salah satu baris invoice yang ingin di-print!', 'warning');
        }
    }

    function printRecap() {
        var rows = $('#dg').datagrid('getSelections');
        
        if (rows.length === 0) {
            toastr.warning('Pilih minimal satu baris invoice yang ingin direkap!');
            return;
        }

        var ids = [];
        var hasTF = false;
        var hasSub = false;

        for (var i = 0; i < rows.length; i++) {
            ids.push(rows[i].id);
            
            var invoiceNo = rows[i].finishing_invoice_no.toUpperCase();
            if (invoiceNo.includes('TF')) {
                hasTF = true;
            } else if (invoiceNo.includes('SUB')) {
                hasSub = true;
            }
        }

        if (hasTF && hasSub) {
            toastr.error('Tidak boleh mencampur antara Invoice Teaching Factory dan Subcont/Koordinator dalam satu cetakan rekap!');
            return;
        }

        var url = '<?= base_url('warehouse/finishing_invoices/print_recap/') ?>?ids=' + ids.join(',');
        window.open(url, '_blank');
    }

</script>
