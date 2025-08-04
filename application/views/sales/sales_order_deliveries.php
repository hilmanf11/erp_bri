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
            <th rowspan="2" data-options="field:'sales_order_no',width:150,halign:'center',sortable:true">Sales Order No</th>
            <th rowspan="2" data-options="field:'customer_order_no',width:150,halign:'center',sortable:true">Customer Order No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center',sortable:true">Customer Name</th>
            <th rowspan="2" data-options="field:'sales_order_date',width:150,halign:'center',sortable:true">Sales Order Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:150,halign:'center',sortable:true">Delivery Date</th>
            <!-- <th rowspan="2" data-options="field:'currency',width:80,align:'center',sortable:true">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:100,halign:'center',align:'right',formatter: numberFormat,sortable:true">Sub Total</th>
            <th rowspan="2" data-options="field:'total_tax',width:100,halign:'center',align:'right',formatter: numberFormat,sortable:true">Taxes</th>
            <th rowspan="2" data-options="field:'total_pph',width:100,halign:'center',align:'right',formatter: numberFormat,sortable:true">PPh</th>
            <th rowspan="2" data-options="field:'total_grand',width:100,halign:'center',align:'right',formatter: numberFormat,sortable:true">Grand Total</th> -->
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center',sortable:true">Remarks</th>
            <th rowspan="2" data-options="field:'attachment',width:150,halign:'center',sortable:true">Attachment</th>
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

<!-- DIALOG DELIVERY -->
<div id="dlg_delivery" class="easyui-dialog" title="Sales Delivery Order" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Delivery date</th>
                <th rowspan="2" data-options="field:'so_qty',width:100,align:'center'">Order Qty</th>
                <th rowspan="2" data-options="field:'qty',width:100,align:'center'">Delivery Qty</th>
                <th rowspan="2" data-options="field:'remain_qty',width:100,align:'center'">Remain Qty</th>
                <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
                <th colspan="2" data-options="field:'',width:100,align:'center'"> Created</th>
            </tr>
            <tr>
                <th data-options="field:'created_by',width:100,align:'center'"> By</th>
                <th data-options="field:'created_date',width:150,align:'center'"> Date</th>

            </tr>
        </thead>
    </table>
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
<!-- DIALOG SAVE AND UPDATE CUSTOMER ADDRESS -->
<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Id</span>
                <input style="width:60%;" name="customer_id" id="customer_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Sales Order No</span>
                <input style="width:60%;" name="sales_order_no" id="sales_order_no" readonly class="easyui-textbox">
            </div>
            <div style="display:none;" class="fitem">
                <input style="display:none;" name="item_fg_id" id="item_fg_id" readonly class="easyui-textbox">
                <input style="display:none;" name="customer_order_no" id="customer_order_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_fg_number" id="item_fg_number" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:30%;" name="qty" id="qty" class="easyui-textbox">
            </div>

        </fieldset>
    </form>
</div>
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 100%; height: 98%; padding:10px; top: 20px; left: 5px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer" id="customer" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" name="customer_order_no2" id="customer_order_no2" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Order Date</span>
                    <input style="width:60%;" name="order_date" id="order_date" value="<?= date("Y-m-d") ?>" required="" readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"></table>
                </div>
            <div class="fitem" style="display:none">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="preview()"><i class="fa fa-search"></i> Preview</a>
            </div>
        </fieldset>
        <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Product Order List" toolbar="#toolbar2" data-options="fitColumns: false, rownumbers: true" idField="item_number">
    </form>
</div>


<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 235px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order Date</span>
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
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- <div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div> -->

<div id="toolbar3">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="addDelivery()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update()"><i class="fa fa-edit"></i> Update</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted()"><i class="fa fa-times"></i> Remove</a>
</div>


<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/sales_order_deliveries/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        $("#customer_order_no2").combobox('enable');
        id_update ='';
        url_save = '<?= base_url('sales/sales_order_deliveries/create_schedule') ?>';
        editIndex = undefined;
    }
    function addDelivery() {
        $('#dlg_update').dialog('open');
        $('#dlg_update').dialog('setTitle', 'Add New');
        var customer_id = $("#customer_id").textbox('getValue');
        var sales_order_no = $("#sales_order_no").textbox('getValue');
        var item_fg_id = $("#item_fg_id").textbox('getValue');
        var item_fg_number = $("#item_fg_number").textbox('getValue');
        var customer_order_no = $("#customer_order_no").textbox('getValue');
        var qty = $("#qty").textbox('getValue');
        $("#customer_id").textbox('setValue', customer_id);
        $("#sales_order_no").textbox('setValue', sales_order_no);
        $("#item_fg_id").textbox('setValue', item_fg_id);
        $("#item_fg_number").textbox('setValue', item_fg_number);
        $("#customer_order_no").textbox('setValue', customer_order_no);
        $("#trans_date").datebox('setValue', '<?= date("Y-m-d") ?>');
        $("#qty").textbox('setValue', '');
        url_save = '<?= base_url('sales/sales_order_deliveries/create') ?>';
    }

    function update() {
        url_save = '<?= base_url('sales/sales_order_deliveries/update') ?>';
        $('#dlg_update').dialog('setTitle', 'Update');
        
        var rows = $('#dg2').datagrid('getSelections');
        if (rows.length > 0) {
            //if(rows[0].status!='CLOSE'){
                $('#dlg_update').dialog('open');
                $('#frm_update').form('load', rows);
                id_update = rows[0].id;

                            editIndex = undefined;
                            setTimeout(function() {
                                $('#customer_id').textbox('setValue', rows[0].customer_id);
                                $("#sales_order_no").textbox('setValue', rows[0].sales_order_no);
                                $('#item_fg_id').textbox('setValue', rows[0].item_fg_id);
                                $('#trans_date').datebox('setValue', rows[0].trans_date);
                                $('#qty').textbox('setValue', rows[0].qty);
                            }, 500);
            //    }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }


    function btnDelivery(val, row) {
        var delivery = "delivery('" + row.customer_id + "','" + row.sales_order_no + "','" + row.customer_order_no + "','" + row.item_fg_id + "', '"+row.item_fg_number+"')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + delivery + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-truck"></i></a>';
    }

    function delivery(customer_id, sales_order_no, customer_order_no, item_fg_id, item_fg_number) {
        $("#dlg_delivery").dialog('open');
        $("#customer_id").textbox('setValue', customer_id);// id customer di simpan di textbox customer_id sekaligus saat add id tersimpan
        $("#sales_order_no").textbox('setValue', sales_order_no);
        $("#item_fg_id").textbox('setValue', item_fg_id);
        $("#item_fg_number").textbox('setValue', item_fg_number);
        $("#customer_order_no").textbox('setValue', customer_order_no);
        $('#dg2').datagrid({
            url: '<?= base_url("sales/sales_order_deliveries/datatables2/") ?>' + btoa(customer_id) + '/' + btoa(sales_order_no) + '/' + btoa(item_fg_id),
            singleSelect: true,
            onBeforeCheck: function(rowIndex, rowData) {
                if (rowData.status === '1') {
                    return false;
                }
            },
            onBeforeSelect: function (index, rowData) {
                if (rowData.status === '1') {
                    return false;
                }
            }
        });
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg2').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];

                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('sales/sales_order_deliveries/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                            },
                            complete: function(data) {
                                $('#dg2').datagrid('reload');
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
        window.location.assign('<?= base_url('sales/sales_order_deliveries/exportTemplate') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        $('#dg').datagrid({
            url: '<?= base_url('sales/sales_order_deliveries/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.sales_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('sales/sales_order_deliveries/datatableDetails?sales_order_no=') ?>' + window.btoa(row.sales_order_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'btn',
                            title: 'Delivery',
                            halign: 'center',
                            formatter: btnDelivery,
                            width: 80
                        }, {
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty SO',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_del',
                            title: 'Sch. Del',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'qty_os',
                            title: 'OS SO',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80
                        }]
                    ],
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

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('sales/sales_order_deliveries/print') ?>' + url);
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
        var filter_item_fg = $("#filter_item_fg").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

        window.location.assign('<?= base_url('sales/sales_order_deliveries/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        filter();
        id_update = '';

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {

                    var customer_id = $("#customer").combobox('getValue');
                    var customer_order_no = $("#customer_order_no2").combobox('getValue');
                    var order_date = $("#order_date").datebox('getValue');
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
                        return;
                    }

                    if (totalrows === 0) {
                        toastr.warning("Please select one of the data in the table first!", "Information");
                        return;
                    }

                    endEditing();

                    $.messager.confirm('Warning', 'Are you sure you want to process this data?', function(r) {
                        if (r) {
                            let dataToSend = [];

                            for (let i = 0; i < totalrows; i++) {
                                let row = rows[i];

                                let dateKeys = Object.keys(row).filter(key => /\d{4}-\d{2}-\d{2}/.test(key));

                                // let resultVal = dateKeys.map(key => ({
                                //     date: key,
                                //     value: row[key]
                                // })).filter(obj => obj.value !== "");

                                let resultVal = Object.keys(row)
                                .filter(key => /^\d{4}-\d{2}-\d{2}$/.test(key))
                                .map(key => ({
                                    date: key,
                                    value: row[key]
                                }))
                                .filter(obj => obj.value !== "" && obj.value !== null && obj.value !== undefined);

                                // Tambahkan data baris jika ada value untuk tanggal
                                if (resultVal.length > 0) {
                                    dataToSend.push({
                                        item_fg_id: row.item_fg_id,
                                        sales_order_no: row.sales_order_no,
                                        qty_so: row.qty,
                                        data: resultVal
                                    });
                                }
                            }

                            if (dataToSend.length === 0) {
                                toastr.warning("No valid schedule data to submit!", "Warning");
                                return;
                            }

                            // Kirim hanya sekali
                            $.ajax({
                                type: "POST",
                                url: url_save,
                                data: {
                                    customer_id: customer_id,
                                    customer_order_no: customer_order_no,
                                    details: JSON.stringify(dataToSend)
                                },
                                dataType: "json",
                                success: function(result) {
                                    if (result.theme === 'error') {
                                        $('#dg_request').datagrid('clearSelections');
                                        toastr.clear();
                                        toastr.error(result.message, result.title || 'error');
                                    } else {
                                        $('#dg_request').datagrid('clearSelections');
                                        Swal.fire({
                                            title: result.title || 'Success',
                                            text: result.message || '',
                                            icon: result.theme || 'success',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            $('#dg2').datagrid('reload');
                                            $('#dlg_delivery').dialog('close');
                                            $('#dlg_insert').dialog('close');
                                            window.location.reload();
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    $('#dg_request').datagrid('clearSelections');
                                    toastr.clear();
                                    toastr.error('An error occurred while processing the data. Please try again');

                                    $('#dg').datagrid('reload');
                                    $('#dlg_insert').dialog('close');
                                }
                            });
                        }
                    });
                }
            }]
        });

        //UPDATE DATA
        $('#dlg_update').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            $(this).append('<input type="hidden" name="id" value="' + id_update + '" />');
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                                $('#dlg_update').dialog('close');
                                $('#dg2').datagrid('reload');
                                if(id_update!==''){
                                    id_update = '';
                                }
                            } else {
                                toastr.error(result.message, result.title);
                            }
                        }
                    });
                }
            }]
        });
    });

    let editIndex = undefined;

    function onClickCell(index, field) {
        var dg = $('#dg_request');
        var row = dg.datagrid('getRows')[index];

        // Jika field terkunci, cegah edit
        if (field.match(/^\d{4}-\d{2}-\d{2}$/) && row[`_lock_${field}`] == 1) {
            toastr.info('This date is closed. Editing is disabled.', 'Locked');
            return;
        }

        if (editIndex !== index) {
            if (endEditing()) {
                dg.datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;

                // Setelah baris masuk edit, disable field yang dikunci
                Object.keys(row).forEach(f => {
                    if (f.match(/^\d{4}-\d{2}-\d{2}$/) && row[`_lock_${f}`] == 1) {
                        let ed = dg.datagrid('getEditor', { index: index, field: f });
                        if (ed && ed.target) {
                            $(ed.target).numberbox('disable');
                            // Tambahkan styling merah di mode edit
                            $(ed.target).parent().css({
                                'background-color': 'rgba(255, 151, 151, 1)',
                                'font-weight': 'bold'
                            });

                            $(ed.target).css({
                                'font-weight': 'bold',
                                'color': '#000'
                            });
                        }
                    }
                });

                // Fokus ke field yang diklik
                let ed = dg.datagrid('getEditor', { index: index, field: field });
                if (ed && ed.target) {
                    $(ed.target).focus();
                }
            } else {
                setTimeout(() => {
                    dg.datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    // function endEditing() {
    //     if (editIndex === undefined) return true;
    //     if ($('#dg_request').datagrid('validateRow', editIndex)) {
    //         $('#dg_request').datagrid('endEdit', editIndex);
    //         editIndex = undefined;
    //         return true;
    //     }
    //     return false;
    // }

    function endEditing() {
        if (editIndex === undefined) return true;
        if ($('#dg_request').datagrid('validateRow', editIndex)) {
            // Reset styling merah sebelumnya
            var editors = $('#dg_request').datagrid('getEditors', editIndex);
            editors.forEach(ed => {
                $(ed.target).parent().removeAttr('style');
            });

            $('#dg_request').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        }
        return false;
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

    function getDaysInMonth(year, month) {
        // Adjust for 1-based month input (1 = January, 12 = December)
        const daysInMonth = new Date(year, month, 0).getDate();
        const days = [];
        
        for (let day = 1; day <= daysInMonth; day++) {
        // const formattedDay = String(day).padStart(2, '0');
            days.push(String(day));
        }

        return days;
    }

    function preview() {
        var customer = $("#customer").combobox('getValue');
        var customer_order_no = $("#customer_order_no2").combobox('getValue');
        var order_date = $("#order_date").datebox('getValue');
        if (customer_order_no == "") {
            toastr.warning('Please select Customer Order No', 'Required');
        } else {
            var lastIndex;

            $.ajax({
                type: "post",
                url: "<?= base_url('sales/sales_order_deliveries/readDeliveryLists') ?>",
                data: {
                        customer: customer,
                        customer_order_no: customer_order_no,
                        },
                dataType: "json",
                success: function(result) {
                    let transformedData = [];
            //         result.forEach(row => {
            //     let groupName = row.item_number;
            //     let date = row.trans_date;
            //     let groupRow = transformedData.find(r => r.item_number === groupName);

            //     // If group does not exist, create a new one
            //     if (!groupRow) {
            //         groupRow = {item_number: groupName, item_name: row.item_name, item_fg_id: row.item_fg_id,qty: row.qty, sales_order_no:row.sales_order_no};
            //         transformedData.push(groupRow);
            //     }

            //     // Add the row's value under the correct date column
            //     //groupRow[date] = row.field2;  // Or any other value you'd like to display
            // });

                    result.forEach(row => {
                        let groupName = row.item_number;
                        let date = row.trans_date;
                        let groupRow = transformedData.find(r => r.item_number === groupName);

                        if (!groupRow) {
                            groupRow = {
                                item_number: groupName,
                                item_name: row.item_name,
                                item_fg_id: row.item_fg_id,
                                qty: row.qty,
                                sales_order_no: row.sales_order_no
                            };
                            transformedData.push(groupRow);
                        }

                        if (date) {
                            // Masukkan nilai qty_delivery ke field tanggal
                            groupRow[date] = row.qty_delivery;

                            // Simpan juga statusnya untuk keperluan lock
                            groupRow[`_lock_${date}`] = row.status_delivery;
                        }
                    });

                    let date = new Date(order_date);//console.log(result[0].order_type)
                            let year = date.getFullYear();
                            let month = String(date.getMonth() + 1).padStart(2, '0');
                            //console.log(month)
                            if(result[0].order_type==1){
                                date.setMonth(date.getMonth() + 1);
                                year = date.getFullYear();
                                month = String(date.getMonth()+1).padStart(2, '0');
                            //  console.log(month,"i")
                            }

                            const monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
                            let monthIndex = result[0].order_type==1? date.getMonth()+1 : date.getMonth();
                            let monthName = result[0].order_type==1?monthNames[monthIndex-1]:monthNames[monthIndex];
                            let days = getDaysInMonth(year, month);
                                let dayColumns = days.map(day => ({
                                    field: `${year}-${month}-${parseInt(day)<10?'0'+day:day}`,
                                    title: day,
                                    halign: 'center',
                                    width: 80,
                                    editor: {
                                        type: 'numberbox',
                                    }
                                }));
                    $('#dg_request').datagrid({
                        singleSelect: true,
                        data: transformedData,//result,
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
                                rowspan: 2,
                                title: "Product No",
                                editor: {
                                    type: 'combogrid',
                                    options: {
                                        url: '<?= base_url('sales/sales_order_deliveries/readSelectedItem?customer_order_no=') ?>'+ customer_order_no,
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
                                                field: 'qty'
                                            });
                                            var ed4 = dg.datagrid('getEditor', {
                                                index: rowIndex,
                                                field: 'sales_order_no'
                                            });
                                            var ed5 = dg.datagrid('getEditor', {
                                                index: rowIndex,
                                                field: 'item_fg_id'
                                            });

                                            $(ed2.target).textbox('setValue', rows.item_name);
                                            $(ed3.target).textbox('setValue', rows.qty);
                                            $(ed4.target).textbox('setValue', rows.sales_order_no);
                                            $(ed5.target).textbox('setValue', rows.item_fg_id);
                                        }
                                    }
                                }
                            }, {
                                field: 'item_name',
                                width: 200,
                                readonly: true,
                                halign: 'center',
                                rowspan: 2,
                                title: "Product Name",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {
                                field: 'item_fg_id',
                                width: 200,
                                hidden: true,
                                halign: 'center',
                                rowspan: 2,
                                title: "Product ID",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            }, {
                                field: 'qty',
                                width: 100,
                                readonly: true,
                                halign: 'center',
                                rowspan: 2,
                                title: "Qty SO",
                                editor: {
                                    type: 'numberbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            },{
                                field: 'sales_order_no',
                                width: 120,
                                readonly: true,
                                halign: 'center',
                                rowspan: 2,
                                title: "Sales Order No",
                                editor: {
                                    type: 'textbox',
                                    options: {
                                        readonly: true
                                    }
                                }
                            },{
                                field: 'monthName',
                                title: monthName,
                                colspan: days.length,
                                align: 'center'
                            },
                        ],
                        [
                            ...dayColumns
                        ]
                        ],
                        onClickCell: onClickCell,
                        groupField: 'item_number',
                        onBeginEdit: function(rowIndex, row) {
                            var editors = $('#dg_request').datagrid('getEditors', rowIndex);
                        },
                        onLoadSuccess: function() {
                            var rows = $('#dg_request').datagrid('getRows');
                            endEditing();

                            result.forEach(item => {
                                if (item.trans_date !== null) {
                                    let rowIndex = rows.findIndex(row => row.item_number === item.item_number);
                                    
                                    if (rowIndex !== -1) {
                                        let row = rows[rowIndex];

                                        // Simpan qty_delivery ke field tanggal
                                        row[item.trans_date] = item.qty_delivery;

                                        // Simpan status delivery ke field khusus supaya bisa dicek di onClickCell
                                        row[`_lock_${item.trans_date}`] = item.status_delivery;

                                        $('#dg_request').datagrid('updateRow', {
                                            index: rowIndex,
                                            row: row
                                        });
                                    } else {
                                        console.error("Invalid rowIndex:", rowIndex);
                                    }
                                }
                            });
                        },

                        // onLoadSuccess: function() {
                        //     var rows = $('#dg_request').datagrid('getRows');
                        //     endEditing();
                        //     result.forEach(item => {
                        //         if(item.trans_date!==null){
                        //                 let rowIndex = $('#dg_request').datagrid('getRows').findIndex(row => row.item_number === item.item_number);
                                        
                        //                 if (rowIndex !== -1) {
                        //                     try {
                        //                         $('#dg_request').datagrid('updateRow', {
                        //                             index: rowIndex, 
                        //                             row: {
                        //                                 [item.trans_date]: item.qty_delivery
                        //                             }
                        //                         });
                                            
                        //                     } catch (error) {
                        //                         console.error("Error accessing editor for field:", item.trans_date, error);
                        //                     }
                        //                 } else {
                        //                     console.error("Invalid rowIndex:", rowIndex);
                        //                 }
                        //             }
                        //     });
                        // }

                    });

                }
            });
        }
    }


    $('#filter_customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose All',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onSelect: function(customer) {
            $('#filter_sales_order_no').combobox({
                url: '<?= base_url('sales/sales_order_deliveries/readSalesOrder/'); ?>' + customer.id,
                valueField: 'sales_order_no',
                textField: 'sales_order_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });

            $('#filter_customer_order_no').combobox({
                url: '<?= base_url('sales/sales_order_deliveries/readCustomerOrder/'); ?>' + customer.id,
                valueField: 'customer_order_no',
                textField: 'customer_order_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });

            $('#filter_item_fg').combogrid({
                url: '<?= base_url('sales/sales_order_deliveries/readProductNo/'); ?>' + customer.id,
                panelWidth: 400,
                idField: 'id',
                textField: 'number',
                mode: 'remote',
                fitColumns: true,
                prompt: "Choose All",
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
        }
    });


    $('#division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Division',
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
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

    $('#customer').combobox({
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
        $("#customer_order_no2").combobox('enable');
        const customerId = customer.id;
        if (customerId) {
            $('#customer_order_no2').combobox({
                url: '<?= base_url('sales/sales_order_deliveries/readCustomerOrderNo/'); ?>' + customerId,
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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('sales/sales_order_deliveries/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('sales/sales_order_deliveries/upload') ?>',
                    onSubmit: function () {
                        if (!$(this).form('validate')) return false;

                        $.messager.progress({
                            title: 'Please Wait',
                            msg: 'Importing Excel to Database'
                        });
                    },
                    success: function (result) {
                        $.messager.progress('close');
                        // Clear File
                        $.ajax({ 
                            url: "<?= base_url('sales/sales_order_deliveries/uploadclearFailed') ?>" 
                        });

                        let res = JSON.parse(result);
                        let dataList = res.data ?? [];

                        console.log(dataList);

                        if (dataList.length === 0) {
                            $.messager.alert("Upload Failed", "Data not found from Excel file", "error");
                            return;
                        }

                        // Reset UI
                        $('#p_upload').progressbar('setValue', 0);
                        $('#p_start').html(0);
                        $('#p_finish').html(dataList.length);
                        $('#p_success').html(0);
                        $('#p_failed').html(0);
                        $('#p_remarks').html('');

                        let totalExpected = dataList.length;

                        // Kirim semua data
                        $.ajax({
                            type: "POST",
                            url: "<?= base_url('sales/sales_order_deliveries/uploadCreate') ?>",
                            data: { data: dataList },
                            dataType: "json",
                            success: function (response) {
                                // if (response.theme === 'error') {
                                //     $.messager.alert(response.title ?? "Upload Failed", response.message ?? "Some data failed to save", "error");
                                // }

                                $('#p_upload').progressbar('setValue', 0);
                                let successCount = 0;
                                let failedCount = 0;
                                let progressCount = 0;
                                let total = response.total_expected ?? response.results.length;

                                function updateProgress() {
                                    let percent = Math.floor((progressCount / total) * 100);
                                    $('#p_upload').progressbar('setValue', percent);
                                    $('#p_start').html(progressCount);
                                    $('#p_success').html(successCount);
                                    $('#p_failed').html(failedCount);
                                }

                                if (response.results && response.results.length > 0) {
                                    let delayPerItem = 50;
                                    response.results.forEach(function (r, i) {
                                        setTimeout(function () {
                                            let color = r.status === "success" ? "green" : "red";

                                            if (r.status === "success") successCount++;
                                            else failedCount++;

                                            $('#p_remarks').append(
                                                `<b style="color: ${color};">${r.item}</b> | ${r.message}<br>`
                                            );

                                            progressCount++;
                                            updateProgress();

                                            // Jika sudah item terakhir
                                            if (progressCount === total) {
                                                if (response.theme === 'error') {
                                                    $.messager.alert(response.title ?? "Upload Failed", response.message ?? "Some data failed to save", "error");
                                                }
                                            }

                                        }, i * delayPerItem);
                                    });
                                }

                                $('#dg').datagrid('reload');
                            },

                            error: function (xhr, status, error) {
                                $.messager.alert("Upload Error", "An error occurred while saving the data", "error");
                            }
                        });
                    }
                });
            }
        }]
    });
</script>