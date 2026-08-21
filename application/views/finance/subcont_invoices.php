<style>
    .datagrid-footer .datagrid-cell {
        font-weight: bold;
    }
    .datagrid-footer td{
        border-right:none !important;
        border-left:none !important;
    }
    .datagrid-footer .datagrid-cell{
        border:none !important;
    }
    .percent-group{
        display:inline-flex;
        width:29%;
        vertical-align:middle;
    }

    .percent-group .textbox{
        border-radius:4px 0 0 4px !important;
    }

    .percent-addon{
        width:55px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:#e9ecef;
        border:1px solid #ced4da;
        border-left:none;
        border-radius:0 4px 4px 0;
        font-weight:bold;
        font-size:20px;
        color:#555;
    }
</style>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'printed',width:100,align:'center', formatter:btnPrint">Option</th>
            <th rowspan="2" data-options="field:'payment_no',width:220,halign:'center',sortable:true">Payment No</th>
            <th rowspan="2" data-options="field:'payment_name',width:180,halign:'center',sortable:true">Payment Name</th>
            <th rowspan="2" data-options="field:'source_name',width:180,halign:'center',sortable:true">Source Name</th>
            <th rowspan="2" data-options="field:'bank_account_no',width:220,halign:'center',sortable:true">Bank Account</th>
            <th rowspan="2" data-options="field:'payment_by',width:180,halign:'center',sortable:true">Payment By</th>
            <th rowspan="2" data-options="field:'currency',width:100,align:'center',sortable:true">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:100,halign:'center',align:'right',formatter: numberFormat,sortable:true">Subtotal</th>
            <th rowspan="2" data-options="field:'fee_amount',width:100,halign:'center',align:'right',formatter: numberFormat,sortable:true">Fee Amount</th>
            <th rowspan="2" data-options="field:'total_payment',width:120,halign:'center',align:'right',formatter: numberFormat,sortable:true">Total Payment</th>
            <th rowspan="2" data-options="field:'payment_status',width:130,halign:'center',align:'center',formatter:formatStatus,styler:styleStatus">Payment Status</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:160,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:200,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:160,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:200,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment Period</span>
                    <input style="width:29.8%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                    <input style="width:29.8%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source Name</span>
                    <input style="width:60%;" id="filter_source_name" name="filter_source_name" class="easyui-combogrid" data-options="editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment No</span>
                    <input style="width:60%;" id="filter_payment_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Doc No</span>
                    <input style="width:60%;" id="filter_incoming_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment Status</span>
                    <select style="width:60%;" id="filter_payment_status" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                        <option value="" selected>Choose Status</option>
                        <option value="OPEN">OPEN</option>
                        <option value="CLOSE">CLOSE</option>
                    </select>
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>

        <?= $button ?>

    </div>
</div>


<div id="toolbar3">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit3()"><i class="fa fa-times"></i> Remove</a>
</div>


<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed:true,modal:true" style="width:1500px;height:700px;padding:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 20px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment No</span>
                    <input style="width:60%;" readonly id="payment_no" name="payment_no" class="easyui-textbox" data-options="prompt:'Autofill Generated'">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source Name</span>
                    <input style="width:60%;" id="source_name" name="source_name" class="easyui-combogrid" data-options="editable:false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment Period</span>
                    <input style="width:30%;" id="period_month" name="period_month" class="easyui-combobox" required>
                    <input style="width:30%;" id="period_year" name="period_year" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment Date</span>
                    <input style="width:60.2%;" name="payment_date" id="payment_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Payment By</span>
                    <select style="width:60.2%;" id="payment_by" name="payment_by" panelHeight="auto" class="easyui-combobox" data-options="editable:false" required>
                        <option value="TRANSFER">TRANSFER</option>
                        <option value="CASH">CASH</option>
                    </select>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note No.</span>
                    <input style="width:60%;" name="delivery_note_no" id="delivery_note_no" readonly class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account Name</span>
                    <input style="width:60%;" name="bank_account_name" id="bank_account_name" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account No</span>
                    <input style="width:60%;" name="bank_account_no" id="bank_account_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Bank Account Holder</span>
                    <input style="width:60%;" name="bank_account_holder" id="bank_account_holder" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Currency</span>
                    <input style="width:60%;" name="currency" id="currency" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:79.5%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()" id="preview"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
        </fieldset>
        <!-- <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product No List" toolbar="#toolbar2"></table> -->
        <table id="dg2" class="easyui-datagrid"
            style="width:100%;"
            title="Detail of Invoices"
            data-options="singleSelect:true">

            <thead>
                <tr>
                    <!-- <th data-options="field:'action',width:120,formatter:buttonEdit">Action</th> -->
                    <th data-options="field:'no',width:80,halign:'center',align:'center'">No</th>
                    <th data-options="field:'action',width:120,halign:'center',align:'center'">Action</th>
                    <th data-options="field:'incoming_doc_no',width:150,halign:'center',align:'center'">Incoming Doc No</th>
                    <th data-options="field:'incoming_date',width:150,halign:'center',align:'center'">Incoming Date</th>
                    <th data-options="field:'source_name',width:150,halign:'center',align:'center'">Source Name</th>
                    <th data-options="field:'total_qty_incoming',width:150,halign:'center',align:'right'">Total Qty Incoming</th>
                    <th data-options="field:'total_local',width:150,halign:'center',align:'right',formatter:numberFormat">Total Amount (Rp)</th>
                    <th data-options="field:'account_no',width:200,halign:'center',align:'center'">Account No</th>
                    <th data-options="field:'account_name',width:200,halign:'center',align:'center'">Account Name</th>
                    <th data-options="field:'debit_credit',width:120,halign:'center',align:'center'">Debit/Credit</th>
                </tr>
            </thead>
        </table>

        <div style="width: 65%; float: left; margin-top:20px;">
            <a style="width: 100%;" class="easyui-linkbutton c2" onclick="addJournal()">Add to Journal</a>
            <br><br>
            <table id="dg3" class="easyui-datagrid" title="Journal Lists" style="width: 100%;" data-options="singleSelect: true" toolbar="#toolbar3"></table>
        </div>

        <div style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; padding: 15px !important;">
                <div style="width: 100%; float: left;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SUB TOTAL</b>
                        <input style="width:60%;" id="total_sub" name="total_sub" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">DEDUCTION</b>
                        <input style="width:60%;" id="total_dpp" name="total_dpp" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">SERVICE FEE</b>

                        <div class="percent-group">
                            <input id="total_percent" name="total_percent" class="easyui-numberbox" data-options="precision:2,groupSeparator:',',decimalSeparator:','" disabled>
                            <span class="percent-addon">%</span>
                        </div>

                        <input style="width:30%;" id="total_percent_rp" name="total_percent_rp" class="easyui-numberbox" data-options="precision:2,groupSeparator:',',decimalSeparator:','">
                    </div>
                    <div style="margin: 10px 0; border-bottom: 1px solid #E2E8F0;"></div>
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">GRAND TOTAL</b>
                        <input style="width:60%;" id="total_grand" name="total_grand" readonly class="easyui-numberbox" data-options="precision:2,groupSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>

    </form>
</div>


<!-- PDF -->
<iframe id="printout" src="<?= base_url('finance/subcont_invoices/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog({
            title: 'Add New',
            modal: true,
            closed: false,
            maximized: true,
            resizable: true,
        }).dialog('open');

        // $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');   

        $('#period_month').combobox('setValue', '<?= date("m") ?>');
        $('#period_year').combobox('setValue', '<?= date("Y") ?>');
        $('#payment_by').combobox('setValue', 'TRANSFER');
        $('#currency').textbox('setValue', 'IDR');

        $("#payment_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                setTimeout(regeneratePaymentNo, 49);
            }
        });

        setTimeout(function(){
            $("#payment_date").datebox('enable');
            // $("#delivery_note_no").textbox('enable');
            // $("#delivery_category").combobox('enable');
            // $("#delivery_to_insert").combobox('enable');
            // $("#destination").combogrid('enable');
            // $("#delivery_note_no").textbox('clear');
            $('#payment_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        }, 50);

        url_save = '<?= base_url('finance/subcont_invoices/create') ?>';
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            setTimeout(function() {
                $('#delivery_to_insert').combobox('setValue', row.delivery_to);
                $('#delivery_category').combobox('setValue', row.delivery_category);

                $('#delivery_note_no').textbox('setValue', row.delivery_note_no);
                $('#destination').combogrid('setValue', row.destination_name);
                $('#destination_code').combogrid('setValue', row.destination_code);
            }, 200);

            $("#delivery_to_insert").combobox('disable');
            $("#delivery_category").combobox('disable');
            $("#payment_date").datebox('disable');
            $("#delivery_note_no").textbox('disable');
            $("#destination").combogrid('disable');

            addTable('<?= base_url('finance/subcont_invoices/datatableUpdates?delivery_note_no=') ?>' + window.btoa(row.delivery_note_no));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log('ROWS : ', rows);
        
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('finance/subcont_invoices/deleteAll') ?>',
                            data: {
                                delivery_note_no: row.delivery_note_no,
                                scan_id: row.scan_id,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');

                                if(result.theme == "success") {
                                    toastr.success(result.message);
                                } else {
                                    toastr.error(result.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                // toastr.error(jqXHR.statusText);

                                if (jqXHR.responseText && jqXHR.responseText.includes("Error Number: 1451")) {
                                    toastr.error("Cannot delete data that is still in use");
                                } else {
                                    toastr.error("Delete failed: " + jqXHR.statusText);
                                }
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

    //FILTER DATA
    function filter() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_payment_status = $("#filter_payment_status").combobox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_source_name=" + window.btoa(filter_source_name) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_payment_status=" + window.btoa(filter_payment_status) +
            "&filter_payment_no=" + window.btoa(filter_payment_no);

        $('#dg').datagrid({
            url: '<?= base_url('finance/subcont_invoices/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.incoming_doc_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('finance/subcont_invoices/datatableDetails?incoming_doc_no=') ?>' + encodeURIComponent(window.btoa(row.incoming_doc_no)),
                    singleSelect: true,
                    rownumbers: true,
                    showFooter: true,
                    columns: [
                        [{
                            field: 'incoming_doc_no',
                            title: 'Incoming Doc No',
                            halign: 'center',
                            width: 240
                        }, {
                            field: 'incoming_date',
                            title: 'Incoming Date',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'source_name',
                            title: 'Source Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'type',
                            title: 'Type',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'account_no',
                            title: 'Account No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'account_name',
                            title: 'Account Name',
                            align: 'center',
                            width: 200
                        }, {
                            field: 'debit_credit',
                            title: 'Debit/Credit',
                            align: 'center',
                            width: 150
                        }, {
                            field: 'total_qty_incoming',
                            title: 'Total Qty Incoming',
                            halign: 'center',
                            align: 'right',
                            width: 150,
                            formatter: numberFormat
                        }, {
                            field: 'total_amount',
                            title: 'Total Amount (Rp)',
                            halign: 'center',
                            align: 'right',
                            width: 150,
                            formatter: numberFormat
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function(data) {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);

                        console.log(data);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('finance/subcont_invoices/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_payment_status = $("#filter_payment_status").combobox('getValue');
        var filter_payment_no = $("#filter_payment_no").combobox('getValue');

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_source_name=" + window.btoa(filter_source_name) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_payment_status=" + window.btoa(filter_payment_status) +
            "&filter_payment_no=" + window.btoa(filter_payment_no);

        window.location.assign('<?= base_url('finance/subcont_invoices/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function reloadPaymentNo() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');

        var url = '<?= base_url('finance/subcont_invoices/readWorkorderLabels'); ?>'
                + '?filter_period_month=' + encodeURIComponent(filter_period_month)
                + '&filter_period_year=' + encodeURIComponent(filter_period_year)
                + '&filter_source_name=' + encodeURIComponent(filter_source_name);

        $('#filter_payment_no').combobox('reload', url);
    }

    function reloadIncomingDocNoCombo() {
        var filter_period_month = $("#filter_period_month").combobox('getValue');
        var filter_period_year = $("#filter_period_year").combobox('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');

        var url = '<?= base_url('control/grn_subconts/readIncomingDocNo'); ?>'
                + '?filter_period_month=' + encodeURIComponent(filter_period_month)
                + '&filter_period_year=' + encodeURIComponent(filter_period_year)
                + '&filter_source_name=' + encodeURIComponent(filter_source_name);

        $('#filter_incoming_doc_no').combobox('reload', url);
    }

    $(function() {
        filter();

        $('#filter_payment_no').combobox({
            valueField: 'payment_no',
            textField: 'payment_no',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_incoming_doc_no').combobox({
            valueField: 'incoming_doc_no',
            textField: 'incoming_doc_no',
            prompt: 'Choose All',
            editable: true,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        reloadPaymentNo();
        reloadIncomingDocNoCombo();

        $('#filter_period_month, #filter_period_year').combobox({
            onChange: function() {
                reloadPaymentNo();
                reloadIncomingDocNoCombo();
            }
        });

        $('#filter_source_name').combogrid({
            onChange: function(newValue, oldValue) {
                reloadPaymentNo();
                reloadIncomingDocNoCombo();
            }
        });

        console.log('DIALOG INIT');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    // var filter_period_month = $("#filter_period_month").combobox('getValue');
                    // var filter_period_year = $("#filter_period_year").combobox('getValue');
                    // var filter_source_name = $("#filter_source_name").combogrid('getValue');
                    // var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
                    // var filter_payment_status = $("#filter_payment_status").combobox('getValue');
                    // var filter_payment_no = $("#filter_payment_no").combobox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            console.log(rows[i].source_type);
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('finance/subcont_invoices/create') ?>',
                                data: {
                                    // item_fg_id: rows[i].item_fg_id,
                                    // internal_doc_no: rows[i].internal_doc_no,
                                    // payment_date: payment_date,
                                    // delivery_note_no: delivery_note_no,
                                    // delivery_category: delivery_category,
                                    // delivery_to: delivery_to_insert,
                                    // destination: destination,
                                    // prod_date: rows[i].trans_date,
                                    // workorder: rows[i].workorder,
                                    // qty_output: rows[i].qty_output,
                                    // qty_delivery: rows[i].qty_delivery,
                                    // source_type: rows[i].source_type,
                                    // remarks: rows[i].remarks,
                                },
                                dataType: "json",
                                success: function(result) {
                                    if (i == (totalrows - 1)) {
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
                                }
                            });
                        }
                    }

                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });
    });

    $('#dg3').datagrid({
        singleSelect: true,
        showFooter: true,
        columns: [[
            {
                field:'account_no',
                title:'Account No',
                width:180,
                align:'center',
            },
            {
                field:'account_name',
                title:'Account Name',
                width:250,
                align:'center',
            },
            {
                field:'debit',
                title:'Debit',
                width:180,
                align:'center',
                formatter:numberFormat
            },
            {
                field:'credit',
                title:'Credit',
                width:180,
                align:'center',
                formatter:numberFormat
            },
            {
                field:'order',
                title:'Order',
                width:90,
                align:'center',
            }
        ]]
    });

    $('#dg3').datagrid('reloadFooter', [{
        account_name: '<div style="text-align:right;"><b>BALANCE TOTAL</b></div>',
        debit: 0,
        credit: 0,
        order: ''
    }]);

    // function reloadBalanceFooter(){
    //     let total_debit = 0;
    //     let total_credit = 0;

    //     const rows = $('#dg3').datagrid('getRows');

    //     $.each(rows, function(i,row){
    //         total_debit += parseFloat(row.debit || 0);
    //         total_credit += parseFloat(row.credit || 0);
    //     });

    //     $('#dg3').datagrid('reloadFooter', [{
    //         account_name: '<b style="float:right;">BALANCE TOTAL</b>',
    //         debit: total_debit,
    //         credit: total_credit
    //     }]);
    // }

    $('#filter_source_name').combogrid({
        url: '<?= base_url('control/grn_subconts/readSourceName'); ?>',
        panelWidth: 440,
        idField: 'id',
        textField: 'name',
        valueField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Source",
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'number', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        editable: false,
        onSelect: function(index, row) {
            reloadPaymentNo();
            reloadIncomingDocNoCombo();
        },
        onChange: function(newValue, oldValue) {
            reloadPaymentNo();
            reloadIncomingDocNoCombo();
        }
    });

    $('#source_name').combogrid({
        url: '<?= base_url('control/grn_subconts/readSourceName'); ?>',
        panelWidth: 440,
        idField: 'id',
        textField: 'name',
        valueField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Source Name",
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'number', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        editable: false,
        onSelect: function(index, row) {
        },
        onChange: function(newValue, oldValue) {
        }
    });    

    function regeneratePaymentNo() {
        let trans_date = $('#payment_date').datebox('getValue');
        let dest_code = $('#destination_code').combogrid('getValue');

        if (trans_date && dest_code) {
            $.ajax({
                type: "post",
                url: "<?= base_url('finance/subcont_invoices/delivery_note_no') ?>",
                data: { trans_date: trans_date, destination_code: dest_code },
                dataType: "html",
                success: function(result) {
                    $("#delivery_note_no").textbox('setValue', result);
                }
            });
        }
    }

    $('#filter_period_month').combobox({
        url: '<?= base_url('finance/subcont_invoices/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_period_year').combobox({
        url: '<?= base_url('finance/subcont_invoices/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });


    $('#period_month').combobox({
        url: '<?= base_url('finance/subcont_invoices/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#period_year').combobox({
        url: '<?= base_url('finance/subcont_invoices/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else if(value == 2) {
            return 'background: #F3A26D; color: white';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };
    //FORMATTER DELIVERY STATUS 
    function cellFormatterDeliveryStatus(value) {
        if (value == 0) {
            return 'ON SCHEDULE';
        } else if(value == 1) {
            return 'DELAY';
        }else {
            return 'EARLY';
        }
    };

    // FORMAT tahun-bulan-tanggal
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

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

    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        var print = "print_dn_to_sc('" + row.delivery_note_no + "')"; 
        if(row.printed==0){
            return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }else{
            return '<a class="btn btn-secondary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';

        }
    }

    function print_dn_to_sc(delivery_note_no) {
        console.log(delivery_note_no);
        window.open("<?= base_url('finance/subcont_invoices/print_dn_to_sc/') ?>" + window.btoa(delivery_note_no), "_blank", "width=1200,height=600");
    }

    function styleStatus(value, row, index) {
        if(value == '0') {
            return 'background: #53D636; color: white;';
        } else if(value == '1') {
            return 'background: #FF5F5F; color: white;';
        } else if(value == '2') {
            return 'background: #F3A26D; color: white;';
        } else if(value == '3') {
            return 'background: #B2A5FF; color: white;';
        }
    }

    function formatStatus(value) {

        if(value == '0') {
            return '<b>OPEN</b>';
        } else if(value == '1') {
            return '<b>CLOSED</b>';
        } else if(value == '2') {
            return '<b>ON GOING</b>';
        } else if(value == '3') {
            return '<b>OVER</b>';
        }
    }

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }

</script>