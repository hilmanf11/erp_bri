<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'delivery_report_date',width:150,halign:'center',sortable:true">Delivery Date</th>
            <th rowspan="2" data-options="field:'customer_name',width:250,halign:'center',sortable:true">Customer</th>
            <th rowspan="2" data-options="field:'customer_order_no',width:200,halign:'center',sortable:true">Customer Order No</th>
            <th rowspan="2" data-options="field:'sales_order_no',width:200,halign:'center',sortable:true">Sales Order No</th>
            <th rowspan="2" data-options="field:'invoice_no',width:200,halign:'center',sortable:true">Invoice No</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:250,halign:'center',sortable:true">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center',sortable:true">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:150,halign:'center',formatter:numberFormat">Quantity Delivery</th>
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

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 240px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" id="filter_sales_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice Number</span>
                    <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>
<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 100%; height: 600px;padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" name="customer_order_no" id="customer_order_no" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Order Date</span>
                    <input style="width:60%;" name="order_date" id="order_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview</a>
            </div>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Product Order List" toolbar="#toolbar2" data-options="fitColumns: false, rownumbers: true" idField="item_number">
        </table>
        <!-- <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product Order List" toolbar="#toolbar2"></table> -->
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
<iframe id="printout" src="<?= base_url('sales/delivery_reports/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#customer_id').combobox('clear');
        $('#customer_order_no').combobox('clear');
        $("#customer_id").combobox('enable');
        $("#customer_order_no").combobox('enable');
        $("#order_date").datebox('disable');
        url_save = '<?= base_url('sales/delivery_reports/create') ?>';
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
                    $('#dlg_insert').dialog('open');
                    $('#frm_insert').form('load', row);
                    $("#customer_id").combobox('disable');
                    $("#customer_order_no").combobox('disable');
                    $("#order_date").datebox('disable');

                    url_save = '<?= base_url('sales/delivery_reports/create') ?>';
                    editIndex = undefined;
                    setTimeout(function() {
                        $('#customer_id').combobox('setValue', row.customer_id);
                        $("#customer_order_no").combobox('setValue', row.customer_order_no);
                    }, 1500);

                    addTable(row);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('sales/delivery_reports/delete') ?>',
                            data: {
                                id: row.id,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
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

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('sales/delivery_reports/exportTemplate') ?>');
        // window.location.assign('<?= base_url('template/tmp_delivery_reports.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

            $('#dg').datagrid({
            url: '<?= base_url('sales/delivery_reports/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            fitColumns: false,
            resizable: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            singleSelect: false,
            remoteSort: false,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('sales/delivery_reports/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        window.location.assign('<?= base_url('sales/delivery_reports/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();
    });

    $('#filter_customer_id').combobox({
    url: '<?= base_url('master/customers/reads'); ?>',
    valueField: 'id',
    textField: 'name',
    prompt: 'Choose All',
    icons: [{
        iconCls: 'icon-clear',
        handler: function (e) {
            $(e.data.target).combobox('clear').combobox('textbox').focus();
        }
    }],
    onSelect: function (customer) {
        const customerId = customer.id;
        if (customerId) {
            $('#filter_customer_order_no').combobox({
                url: '<?= base_url('sales/delivery_reports/readCustomerOrder/'); ?>' + customerId,
                valueField: 'customer_order_no',
                textField: 'customer_order_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function (e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
                onSelect: function (order) {
                    const customerOrderNo = order.customer_order_no;
                    if (customerOrderNo) {
                        $('#filter_sales_order_no').combobox({
                            url: '<?= base_url('sales/delivery_reports/readSalesOrder/'); ?>' + customerOrderNo,
                            valueField: 'sales_order_no',
                            textField: 'sales_order_no',
                            prompt: 'Choose All',
                            icons: [{
                                iconCls: 'icon-clear',
                                handler: function (e) {
                                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                                }
                            }],
                            onSelect: function (salesOrder) {
                                const salesOrderNo = salesOrder.sales_order_no;
                                if (salesOrderNo) {
                                    $('#filter_invoice_no').combobox({
                                        url: '<?= base_url('sales/delivery_reports/readInvoice/'); ?>' + salesOrderNo,
                                        valueField: 'invoice_no',
                                        textField: 'invoice_no',
                                        prompt: 'Choose All',
                                        icons: [{
                                            iconCls: 'icon-clear',
                                            handler: function (e) {
                                                $(e.data.target).combobox('clear').combobox('textbox').focus();
                                            }
                                        }],
                                    });
                                }
                            }
                        });
                    }
                }
            });
        }
    }
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
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

    $('#customer_id').combobox({
    url: '<?= base_url('master/customers/reads'); ?>',
    valueField: 'id',
    textField: 'name',
    prompt: 'Choose Customer',
    icons: [{
        iconCls: 'icon-clear',
        handler: function (e) {
            $(e.data.target).combobox('clear').combobox('textbox').focus();
        }
    }],
    onSelect: function (customer) {
        $("#customer_order_no").combobox('enable');
        const customerId = customer.id;
        if (customerId) {
            $('#customer_order_no').combobox({
                url: '<?= base_url('sales/delivery_reports/readCustomerOrderNo/'); ?>' + customerId,
                valueField: 'customer_order_no',
                textField: 'customer_order_no',
                prompt: 'Choose Customer Order No',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function (e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
                onSelect: function (customer) {
                    $('#order_date').datebox('setValue', customer.sales_order_date);
                    setTimeout(() => {
                        preview();
                    }, 500);
                }
            });
        }
    }
    });

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

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg_request').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg_request').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a>';
            return s;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a>';
            return e;
        }
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

function append() {
    var customer_id = $("#customer_id").combobox('getValue');
    var customer_order_no = $("#customer_order_no").combobox('getValue');
    let grid = $('#dg_request').datagrid('getRows')
    if (customer_id != "" && customer_order_no != "") {
        if (endEditing()) {
            $('#dg_request').datagrid('appendRow', {
                sales_order_no: ''
            });
            editIndex = grid.length - 1;
            $('#dg_request').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    } else {
        toastr.error("Please Choose Customer first");
    }
}

function removeit() {
    if (editIndex == undefined) {
        return true;
    }
    $('#dg_request').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
    editIndex = undefined;
}


function preview() {
    var customer = $("#customer_id").combobox('getValue');
    var customer_order_no = $("#customer_order_no").combobox('getValue');
    var order_date = $("#order_date").datebox('getValue');

    if (customer_order_no == "") {
        toastr.warning('Please select Customer Order No', 'Required');
    } else {
        var lastIndex;

        $.ajax({
            type: "post",
            url: "<?= base_url('sales/delivery_reports/readDeliveryLists') ?>",
            data: {
                    customer: customer,
                    customer_order_no: customer_order_no,
                    },
            dataType: "json",
            success: function(result) {
                $('#dg_request').datagrid({
                    singleSelect: true,
                    data: result,
                    columns: [
                        [
                        //     {
                        //     field: 'action',
                        //     width: 80,
                        //     halign: 'center',
                        //     title: "Action",
                        //     formatter: buttonEdit
                        // }, 
                        {
                            field: 'item_number',
                            width: 150,
                            halign: 'center',
                            title: "Product No",
                            editor: {
                                type: 'combogrid',
                                options: {
                                    url: '<?= base_url('sales/delivery_reports/readSelectedItem?customer_order_no=') ?>'+ customer_order_no,
                                    required: true,
                                    panelWidth: 320,
                                    idField: 'item_number',
                                    textField: 'item_number',
                                    valueField: 'item_number',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: 'Choose Product',
                                    columns: [
                                        [{
                                            field: 'item_number',
                                            title: 'Product No',
                                            width: 150
                                        }, {
                                            field: 'item_name',
                                            title: 'Product Name',
                                            width: 150
                                        }]
                                    ],
                                    onSelect: function(value, rows) {
                                        var dg = $('#dg_request');
                                        var row = dg.datagrid('getSelected');
                                        var rowIndex = dg.datagrid('getRowIndex', row);

                                        var ed2 = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'item_name'
                                        });
                                        var ed3 = dg.datagrid('getEditor', {
                                            index: rowIndex,
                                            field: 'qty_os_so'
                                        });

                                        $(ed2.target).textbox('setValue', rows.item_name);
                                        $(ed3.target).textbox('setValue', rows.qty_os_so);
                                    }
                                }
                            }
                        }, {
                            field: 'item_name',
                            width: 200,
                            readonly: true,
                            halign: 'center',
                            title: "Product Name",
                            editor: {
                                type: 'textbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'qty_os_so',
                            width: 100,
                            readonly: true,
                            halign: 'center',
                            title: "Qty OS SO",
                            editor: {
                                type: 'numberbox',
                                options: {
                                    readonly: true
                                }
                            }
                        }, {
                            field: 'invoice_no',
                            width: 150,
                            halign: 'center',
                            title: "Delivery Note No",
                            editor: {
                                type: 'textbox',
                                options: {
                                    required: true
                                }
                            }
                        }, {
                            field: 'delivery_report_date',
                            width: 100,
                            halign: 'center',
                            title: "Delivery Date",
                            editor: {
                                type: 'datebox',
                                options: {
                                    formatter: myformatter,
                                    parser: myparser,
                                    editable: true,
                                    required: true
                                }
                            }
                        }, {
                            field: 'qty',
                            width: 100,
                            halign: 'center',
                            title: "Deliver Qty",
                            editor: {
                                type: 'numberbox',
                                options: {
                                    required: true
                                }
                            }
                        },]
                    ],
                    onClickCell: onClickCell,
                    // onBeforeEdit: function(index, row) {
                    //     row.editing = true;
                    //     $(this).datagrid('refreshRow', index);
                    // },
                    // onAfterEdit: function(index, row) {
                    //     row.editing = false;
                    //     $(this).datagrid('refreshRow', index);
                    // },
                    // onCancelEdit: function(index, row) {
                    //     row.editing = false;
                    //     $(this).datagrid('refreshRow', index);
                    // },
                    onBeginEdit: function(rowIndex, row) {
                        var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                    },
                    onLoadSuccess: function() {
                        var rows = $('#dg_request').datagrid('getRows');
                        endEditing();
                    }
                });
            }
        });
    }
}

function addTable(row) {
    var lastIndex;
    $.ajax({
            type: "post",
            url: "<?= base_url('sales/delivery_reports/readDeliveryLists') ?>",
            data: {
                    customer: row.customer_id,
                    customer_order_no: row.customer_order_no
                    },
            dataType: "json",
            success: function(result) {
            var dg = $('#dg_request').datagrid({
                data: result,
                singleSelect: true,
                columns: [
                                [
                                //     {
                                //     field: 'action',
                                //     width: 80,
                                //     halign: 'center',
                                //     title: "Action",
                                //     formatter: buttonEdit
                                // },
                                {
                                    field: 'item_number',
                                    width: 150,
                                    halign: 'center',
                                    title: "Product No",
                                    editor: {
                                        type: 'combogrid',
                                        options: {
                                            url: '<?= base_url('sales/delivery_reports/readSelectedItem?customer_order_no=') ?>'+ row.customer_order_no,
                                            required: true,
                                            panelWidth: 320,
                                            idField: 'item_number',
                                            textField: 'item_number',
                                            valueField: 'item_number',
                                            mode: 'remote',
                                            fitColumns: true,
                                            prompt: 'Choose Product',
                                            columns: [
                                                [{
                                                    field: 'item_number',
                                                    title: 'Product No',
                                                    width: 150
                                                }, {
                                                    field: 'item_name',
                                                    title: 'Product Name',
                                                    width: 150
                                                }]
                                            ],
                                            onSelect: function(value, rows) {
                                                var dg = $('#dg_request');
                                                var row = dg.datagrid('getSelected');
                                                var rowIndex = dg.datagrid('getRowIndex', row);

                                                var ed2 = dg.datagrid('getEditor', {
                                                    index: rowIndex,
                                                    field: 'item_name'
                                                });
                                                var ed3 = dg.datagrid('getEditor', {
                                                    index: rowIndex,
                                                    field: 'qty_os_so'
                                                });

                                                $(ed2.target).textbox('setValue', rows.item_name);
                                                $(ed3.target).textbox('setValue', rows.qty_os_so);
                                            }
                                        }
                                    }
                                },{
                                    field: 'item_name',
                                    width: 200,
                                    readonly: true,
                                    halign: 'center',
                                    title: "Product Name",
                                    editor: {
                                        type: 'textbox',
                                        options: {
                                            readonly: true
                                        }
                                    }
                                }, {
                                    field: 'qty_os_so',
                                    width: 100,
                                    readonly: true,
                                    halign: 'center',
                                    title: "Qty OS SO",
                                    editor: {
                                        type: 'numberbox',
                                        options: {
                                            readonly: true
                                        }
                                    }
                                }, {
                                    field: 'invoice_no',
                                    width: 150,
                                    halign: 'center',
                                    title: "Delivery Note No",
                                    editor: {
                                        type: 'textbox',
                                        options: {
                                            required: true
                                        }
                                    }
                                }, {
                                    field: 'delivery_report_date',
                                    width: 100,
                                    halign: 'center',
                                    title: "Delivery Date",
                                    editor: {
                                        type: 'datebox',
                                        options: {
                                            formatter: myformatter,
                                            parser: myparser,
                                            editable: true,
                                            required: true
                                        }
                                    }
                                }, {
                                    field: 'qty',
                                    width: 100,
                                    halign: 'center',
                                    title: "Deliver Qty",
                                    editor: {
                                        type: 'numberbox',
                                        options: {
                                            required: true
                                        }
                                    }
                                },]
                ],
                onClickCell: onClickCell,
                onBeginEdit: function(rowIndex, row) {
                    var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                }
            });
            }
        });
}
    
    $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var customer_id = $("#customer_id").combobox('getValue');
                    var customer_order_no = $("#customer_order_no").combobox('getValue');
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
                            endEditing();
                            if (totalrows > 0) {
                                $.messager.confirm('Warning', 'Are you sure you want Process this Data?', function(r) {
                                    if (r) {
                                        // Menyimpan hasil dari setiap operasi dalam array
                                        var results = [];

                                        for (var i = 0; i < totalrows; i++) {
                                            var row = rows[i];
                                            if (row.invoice_no == "") {
                                                return toastr.warning('Please input Delivery Note No', 'Required');
                                            }
                                            if (row.delivery_report_date == "") {
                                                return toastr.warning('Please select Delivery Date', 'Required');
                                            }
                                            if (row.qty == "") {
                                                return toastr.warning('Please input Qty Delivery', 'Required');
                                            }

                                            var item_number = row.item_number; //item_number
                                            var invoice_no = row.invoice_no;
                                            var qty = row.qty;
                                            var delivery_report_date = row.delivery_report_date;

                                            $.ajax({
                                                type: "post",
                                                url: url_save,
                                                data: 'customer_id=' + customer_id +
                                                    '&item_number=' + item_number +
                                                    '&invoice_no=' + invoice_no +
                                                    '&delivery_report_date=' + delivery_report_date +
                                                    '&qty=' + qty +
                                                    '&customer_order_no=' + customer_order_no,
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
                                        $('#dg').datagrid('reload');
                                        $('#dlg_insert').dialog('close');
                                    }
                                });
                            } else {
                                toastr.warning("Please select one of the data in the table first!", "Information");
                            }
                        }
                    //}
                }
            }]
        });


    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('sales/delivery_reports/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('sales/delivery_reports/upload') ?>',
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
                        //Clear File
                        $.ajax({
                            url: "<?= base_url('sales/delivery_reports/uploadclearFailed') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('sales/delivery_reports/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            $('#p_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('sales/delivery_reports/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }]
    });
</script>