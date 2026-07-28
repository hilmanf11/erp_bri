<style>
    .swal2-container {
        z-index: 9999 !important;
    }
    .swal2-popup {
        z-index: 99999 !important;
    }
</style>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'printed',width:90,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'po_no',width:180,halign:'center',resizable:true">PO No</th>
            <th rowspan="2" data-options="field:'status_po',width:100,align:'center',formatter:statusformat,styler:statusStyle">Status PO</th>
            <th rowspan="2" data-options="field:'status_si',width:100,align:'center',formatter:statusformatFinance,styler:statusStyleFinance">Status Invoice</th>
            <th rowspan="2" data-options="field:'approved_to',width:110,halign:'center',formatter:formatApprovedStatus,styler:styleApprovedStatus">Status Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:140,halign:'center'">Approve Date</th>
            <th rowspan="2" data-options="field:'pr_no',width:150,halign:'center'">PR No</th>
            <th rowspan="2" data-options="field:'po_date',width:100,align:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Due Date</th>
            <th rowspan="2" data-options="field:'subcont_name',width:200,halign:'center'">Subcont Name</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total',width:120,halign:'center',align:'right',formatter:numberformat">Total Amount</th>
            <th rowspan="2" data-options="field:'notes',width:100,halign:'center'">Notes</th>
            <th rowspan="2" data-options="field:'revision',width:80,align:'center'">Revision</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 232px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:28%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:28%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Order Type</span>
                    <select style="width:60%;" id="filter_order_type" panelHeight="auto" class="easyui-combobox" data-options="editable: false">
                        <option value="">Choose All</option>
                        <option value="regular">Regular</option>
                        <option value="additional">Additional</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont Name</span>
                    <input style="width:60%;" id="filter_subcont_id" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Po No</span>
                    <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox" data-options="editable: false">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSED</option>
                    </select>
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.3%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Convert Purchase Request to Purchase Order" data-options="closed: true,modal:true" style="width: 100%; height: 100%; padding:10px; top: 0; left: 0;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; margin-top: 10px; border-radius:4px;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PR No</span>
                    <input style="width:60%;" id="pr_no" name="pr_no" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont Name</span>
                    <input style="width:60%;" id="subcont_id" name="subcont_id" class="easyui-combogrid" required>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Order Type</span>
                    <input style="width:60%;" id="order_type" name="order_type" class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO Date</span>
                    <input style="width:60%;" name="po_date" id="po_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Due Date</span>
                    <input style="width:60%;" name="due_date" id="due_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>

            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" id="revision" class="easyui-textbox" readonly value="0">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Notes</span>
                    <input class="easyui-textbox" id="notes" multiline="true" style="width:60%;height:65px">
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.3%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </div>
        </fieldset>

        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Purchase Request Data" data-options="fitColumns: false, rownumbers: true" idField="item_number">
        </table>

        <div id="frm_calculate" style="width: 30%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex; float: right; margin-top:20px;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px;">
                <div style="width: 100%; float: right; margin-top: 10px;">
                    <div class="fitem">
                        <b style="width:35%; display:inline-block;">Total Amount</b>
                        <input style="width:60%; text-align:right;" id="total_amount" name="total_amount" readonly class="easyui-numberbox" value="0" readonly data-options="precision:2,groupSeparator:'.',decimalSeparator:','">
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('purchase/po_subcont_productions/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function add() {
        $.messager.prompt('Convert PR to PO', 'Please input Password to Convert', function(r) {
            if (r) {
                var encodedPassword = window.btoa(r);
                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('purchase/po_subcont_productions/checkPassword') ?>',
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

                            setTimeout(() => {
                                $('#dlg_insert').dialog('open');
                                                    
                                $("#pr_no").combobox("enable");
                                $("#subcont_id").combogrid("enable");
                                $("#po_date").datebox("enable");
                                $("#due_date").datebox("enable");
                                $("#revision").textbox("enable");

                                $("#pr_no").combobox("setValue", "");
                                $("#subcont_id").combogrid("setValue", "");
                                $("#po_date").datebox("setValue", "<?= date('Y-m-d') ?>");
                                $("#due_date").datebox("setValue", "<?= date('Y-m-d') ?>");
                                $("#order_type").textbox("setValue", "");
                                $("#notes").textbox("setValue", "");

                                $("#btnPreview").linkbutton('enable');
                                $('#dg_request').datagrid('loadData', []);
                                $("#pr_no").combobox({
                                    url: '<?= base_url('purchase/po_subcont_productions/readPRNo') ?>',
                                    valueField: 'pr_no',
                                    textField: 'pr_no',
                                    prompt: "Select Purchase Request No",
                                    icons: [{
                                        iconCls: 'icon-clear',
                                        handler: function(e) {
                                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                                        }
                                    }]
                                });

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


    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {

            if (row.deleted == 2) {
                toastr.error("This purchase order cannot be updated because the PO No was Disapprove.");
                return;
            }

            $('#dlg_insert').dialog('open');
            
            $('#frm_insert').form('load', row);

            $("#pr_no").combobox("setValue", row.pr_no);
            $("#po_date").datebox("setValue", row.po_date);
            $("#due_date").datebox("setValue", row.due_date);
            $("#order_type").textbox("setValue", row.order_type);
            $("#revision").textbox("setValue", row.revision);
            $("#notes").textbox("setValue", row.notes);

            // $("#subcont_id").combogrid("setValue", row.subcont_id);
            // $("#subcont_id").combogrid("setText", row.subcont_name);

            $("#subcont_id").combogrid("grid").datagrid("load", {
                pr_no: row.pr_no,
                edit: 1
            });

            $("#subcont_id").combogrid("setValue", row.subcont_id);

            $("#pr_no").combobox("disable");
            $("#subcont_id").combogrid("disable");
            $("#po_date").datebox("disable");
            $("#due_date").datebox("disable");
            $("#revision").textbox("disable");

            $("#btnPreview").linkbutton('disable');

            // preview("<?= base_url('purchase/po_subcont_productions/datatableUpdates?po_no=') ?>" + btoa(row.po_no));
            var url = "<?= base_url('purchase/po_subcont_productions/datatableUpdates?po_no=') ?>" + btoa(row.po_no);
            console.log(url);
            preview(url);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }


    function preview(url = "") {
        if (url == "") {

            var pr_no = $("#pr_no").combobox('getValue');
            var subcont_id = $("#subcont_id").combogrid('getValue');
            var po_date = $("#po_date").datebox('getValue');
            var due_date = $("#due_date").datebox('getValue');
            var revision = $("#revision").textbox('getValue');

            if (pr_no == "" || subcont_id == "" || po_date == "" || due_date == "" || revision == "") {
                toastr.warning('Please complete all required fields');
                return;
            }

            url = '<?= base_url('planning/pr_subcont_productions/reads') ?>?pr_no=' + pr_no + '&subcont_id=' + subcont_id;
        }

        if (pr_no == "" || subcont_id == "" || po_date == "" || due_date == "" || revision == "") {
            toastr.warning('Please complete all required fields', 'Required');
        } else {
            $('#dg_request').datagrid({
                singleSelect: true,
                fitColumns: true,
                url: url,
                columns: [
                    [{
                        field: 'action',
                        title: 'Action',
                        width: 120,
                        align: 'center',
                        formatter: buttonEdit
                    },{
                        field: 'item_fg_id',
                        title: 'Product ID',
                        width: 120,
                        align: 'center'
                    },{
                        field: 'item_number',
                        title: 'Product No',
                        width: 180
                    },{
                        field: 'item_name',
                        title: 'Product Name',
                        width: 220
                    },
                    {
                        field: 'qty',
                        title: 'Qty',
                        width: 90,
                        align: 'right',
                        formatter: numberformatInteger,
                        editor: {
                            type: 'numberbox',
                            options: {
                                precision: 0,
                                groupSeparator: ','
                            }
                        }
                    },
                    {
                        field: 'uom',
                        title: 'UOM',
                        width: 70,
                        align: 'center'
                    },{
                        field: 'price',
                        title: 'Unit Price',
                        width: 120,
                        align: 'right',
                        formatter:numberformats
                    },{
                        field: 'total',
                        title: 'Amount',
                        width: 150,
                        align: 'right',
                        formatter:numberformats
                    }]
                ],
                onBeforeEdit: function(index, row) {
                    row.editing = true;
                    $(this).datagrid('refreshRow', index);
                },
                onAfterEdit:function(index,row){
                    row.editing=false;
                    row.total = Number(row.qty) * Number(row.price);
                    $(this).datagrid('refreshRow',index);
                    calculateGrandTotal();
                },
                onCancelEdit: function(index, row) {
                    row.editing = false;
                    $(this).datagrid('refreshRow', index);
                },
                onBeginEdit:function(index,row){
                    var editors = $('#dg_request').datagrid('getEditors',index);
                    var qtyEditor = editors.find(e=>e.field=="qty");

                    if(qtyEditor){
                        $(qtyEditor.target).numberbox({
                            onChange:function(value){
                                value = Number(value);
                                if(isNaN(value)){
                                    value = 0;
                                }

                                row.qty = value;
                                row.total = value * Number(row.price);
                            }
                        });
                    }
                },
                onLoadSuccess:function(data){
                    if (data.rows.length > 0) {
                        $("#total_amount").numberbox(
                            "setValue",
                            data.rows[0].total_amount
                        );
                    }

                    calculateGrandTotal();
                }
            });
                    
        }
    }

    function calculateGrandTotal(){
        var rows = $('#dg_request').datagrid('getRows');

        var total=0;
        rows.forEach(function(row){
            total += Number(row.total||0);
        });

        $("#total_amount").numberbox("setValue",total);
    }

    function getRowIndex(target) {
        var tr = $(target).closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'));
    }

    function editrow(target) {
        $('#dg_request').datagrid('selectRow', getRowIndex(target));
        $('#dg_request').datagrid('beginEdit', getRowIndex(target));
    }

    function removerow(target){

        var index = getRowIndex(target);

        $.messager.confirm(
            "Confirmation",
            "Remove this product from Purchase Order?",
            function(r){

                if(!r) return;

                $('#dg_request').datagrid('deleteRow', index);

                calculateGrandTotal();
            }
        );
    }

    function saverow(target){
        var index = getRowIndex(target);
        $('#dg_request').datagrid('endEdit', index);
        calculateGrandTotal();
    }

    function buttonEdit(value, row, index) {

        if (row.editing) {
            return `
                <div style="display:flex;gap:4px;">
                    <a href="javascript:void(0)"
                        class="btn btn-success btn-sm w-50"
                        style="pointer-events:auto;opacity:1;"
                        onclick="event.stopPropagation(); saverow(this);">
                        Save
                    </a>

                    <a href="javascript:void(0)"
                        class="btn btn-danger btn-sm w-50"
                        style="pointer-events:auto;opacity:1;"
                        onclick="event.stopPropagation(); removerow(this);">
                        Delete
                    </a>
                </div>
            `;
        }

        return `
            <div style="display:flex;gap:4px;">
                <a href="javascript:void(0)"
                    class="btn btn-primary btn-sm w-50"
                    style="pointer-events:auto;opacity:1;"
                    onclick="event.stopPropagation(); editrow(this);">
                    Edit
                </a>

                <a href="javascript:void(0)"
                    class="btn btn-danger btn-sm w-50"
                    style="pointer-events:auto;opacity:1;"
                    onclick="event.stopPropagation(); removerow(this);">
                    Delete
                </a>
            </div>
        `;
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
                            url: '<?= base_url('purchase/po_subcont_productions/delete') ?>',
                            data: {
                                pr_no: row.pr_no,
                                po_no: row.po_no,
                                subcont_id: row.subcont_id
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
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to  = $("#filter_to").datebox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_order_type = $("#filter_order_type").combobox('getValue');

        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_subcont_id=" + filter_subcont_id + "&filter_order_type=" + filter_order_type + "&filter_po_no=" + filter_po_no + "&filter_status=" + filter_status + "&filter_product_no=" + filter_product_no;

        $('#dg').datagrid({
            url: '<?= base_url('purchase/po_subcont_productions/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.po_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                var filter_product_no = $('#filter_product_no').combogrid('getValue');
                var encodedProductNo = filter_product_no ? "&filter_product_no=" + window.btoa(filter_product_no) : "";

                ddv.datagrid({
                    url: '<?= base_url('purchase/po_subcont_productions/datatableDetails?po_no=') ?>' + window.btoa(row.po_no) + encodedProductNo,
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
                            field:'item_fg_id',
                            title:'Product ID',
                            width:150,
                            align:'center'
                        },
                        {
                            field:'item_number',
                            title:'Product No',
                            width:180
                        },
                        {
                            field:'item_name',
                            title:'Product Name',
                            width:220
                        },
                        {
                            field:'qty',
                            title:'Qty',
                            width:100,
                            align:'right',
                            formatter:numberformatInteger
                        },
                        {
                            field:'uom',
                            title:'UOM',
                            width:80,
                            align:'center'
                        },
                        {
                            field:'unit_price',
                            title:'Unit Price',
                            width:130,
                            align:'right',
                            formatter:numberformats
                        },
                        {
                            field:'amount',
                            title:'Amount',
                            width:150,
                            align:'right',
                            formatter:numberformats
                        },
                        {
                            field:'status_po',
                            title:'Status PO',
                            width:100,
                            align:'center',
                            formatter:statusformat,
                            styler:statusStyle
                        },
                        {
                            field:'status_si',
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
                        console.log(data);
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('purchase/po_subcont_productions/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to  = $("#filter_to").datebox('getValue');
        var filter_subcont_id = $("#subcont_id").combogrid('getValue');
        var filter_order_type = $("#filter_order_type").combobox('getValue');

        var filter_po_no = $("#filter_po_no").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_subcont_id=" + filter_subcont_id + "&filter_order_type=" + filter_order_type + "&filter_po_no=" + filter_po_no + "&filter_status=" + filter_status + "&filter_product_no=" + filter_product_no;

        window.location.assign('<?= base_url('purchase/po_subcont_productions/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
        reloadPoNo()

        $("#add").html("Convert PR to PO");

        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function () {

                    var pr_no       = $("#pr_no").combobox("getValue");
                    var subcont_id  = $("#subcont_id").combogrid("getValue");
                    var order_type  = $("#order_type").textbox("getValue");
                    var po_date     = $("#po_date").datebox("getValue");
                    var due_date    = $("#due_date").datebox("getValue");
                    var revision    = $("#revision").textbox("getValue");
                    var notes       = $("#notes").textbox("getValue");
                    var totalAmount = $("#total_amount").numberbox("getValue");

                    if (pr_no == "" || subcont_id == "" || order_type == "" || po_date == "" || due_date == "" || revision == "") {
                        toastr.warning('Please complete all required fields', 'Required');
                        return;
                    }

                    var rows = $("#dg_request").datagrid("getRows");

                    if(rows.length == 0){
                        toastr.warning("Please preview data first.");
                        return;
                    }

                    var editing = rows.some(x => x.editing);

                    if(editing){
                        toastr.warning("Please save edited row first.");
                        return;
                    }

                    $.messager.confirm("Confirmation","Save Purchase Order?",function(r){

                        if(!r) return;

                        let po_no = rows[0].po_no || "";
                        let url_save = "";
                        if (po_no == "") {
                            url_save = "<?= base_url('purchase/po_subcont_productions/create') ?>";
                        } else {
                            url_save = "<?= base_url('purchase/po_subcont_productions/update') ?>";
                        }

                        $.ajax({
                            url : url_save,
                            type:"POST",
                            dataType:"json",
                            data:{
                                pr_no:pr_no,
                                po_no:po_no,
                                subcont_id:subcont_id,
                                order_type:order_type,
                                po_date:po_date,
                                due_date:due_date,
                                revision:revision,
                                notes:notes,
                                total_amount:totalAmount,
                                details:JSON.stringify(rows)
                            },

                            success:function(result){

                                if(result.success){

                                    Swal.fire({
                                        icon:"success",
                                        title:"Success",
                                        text: result.message
                                    }).then(()=>{
                                        window.location.reload();
                                    });

                                }else{

                                    toastr.error(result.message);

                                }

                            }

                        });

                    });

                }
            }]
        });
    });


    $("#pr_no").combobox({
        url: '<?= base_url('purchase/po_subcont_productions/readPRNo') ?>',
        valueField: 'pr_no',
        textField: 'pr_no',
        prompt: "Select Purchase Request No",
        onSelect: function(row) {
            $('#subcont_id').combogrid('clear');
            $('#subcont_id').combogrid('grid').datagrid('load', {
                pr_no: row.pr_no,
            });
        },
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }]
    });

    $('#subcont_id').combogrid({
        url: '<?= base_url('purchase/po_subcont_productions/readSubcontProductionPR'); ?>',
        method: 'post',
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Subcont",
        editable: false,
        columns: [[
            {field:'id',title:'Subcont ID',width:150},
            {field:'number',title:'Subcont Code',width:150},
            {field:'name',title:'Subcont Name',width:200}
        ]],
        onSelect: function(index, row) {
            $('#order_type').textbox('setValue', row.order_type);
        }
    });


    function btnPrint(val, row) {
        var print = "print_po('" + row.po_no + "')"; 
        if(row.printed == 0){
            return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }else{
            return '<a class="btn btn-secondary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }
    }

    function print_po(po_no) {
        var printUrl = "<?= base_url('purchase/po_subcont_productions/print_po/') ?>" + window.btoa(po_no);

        window.open(printUrl, "_blank");
    }

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
                reloadPoNo();
            }
        }],
        onChange:function(){
            reloadPoNo();
        }
    });

    $("#filter_po_no").combobox({
        // url: '<?= base_url('purchase/po_subcont_productions/readPoNos/') ?>',
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

    $('#filter_product_no').combogrid({
        url: '<?= base_url("master/item_fg/readRubberParts") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        valueField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
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

    function reloadPoNo() {
        var filter_subcont_id = $("#filter_subcont_id").combogrid("getValue");

        $("#filter_po_no").combobox("clear");

        $("#filter_po_no").combobox("reload",
            "<?= base_url('purchase/po_subcont_productions/readPoNos') ?>" +
            "?filter_subcont_id=" + filter_subcont_id
        );
    }

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