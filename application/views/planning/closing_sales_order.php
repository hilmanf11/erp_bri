<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'sales_order_no',width:150,halign:'center'">Sales Order No</th>
            <th rowspan="2" data-options="field:'customer_order_no',width:150,halign:'center'">Customer Order No</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center'">Customer Name</th>
            <th rowspan="2" data-options="field:'sales_order_date',width:150,halign:'center'">Sales Order Date</th>
            <th rowspan="2" data-options="field:'delivery_date',width:150,halign:'center'">Delivery Date</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_sub',width:100,halign:'center',align:'right',formatter: numberFormat">Sub Total</th>
            <th rowspan="2" data-options="field:'total_tax',width:100,halign:'center',align:'right',formatter: numberFormat">Taxes</th>
            <th rowspan="2" data-options="field:'total_pph',width:100,halign:'center',align:'right',formatter: numberFormat">PPh</th>
            <th rowspan="2" data-options="field:'total_grand',width:100,halign:'center',align:'right',formatter: numberFormat">Grand Total</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center'">Remarks</th>
            <th rowspan="2" data-options="field:'attachment',width:150,halign:'center'">Attachment</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
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
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>


<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Sales Order No</span>
                    <input style="width:60%;" name="sales_order_no" id="sales_order_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="status" name="status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('planning/sales_orders/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('planning/sales_orders/create') ?>';
        $('#frm_insert').form('clear');

        $("#customer_id").combobox('enable');
        $("#sales_order_no").textbox('enable');
        $("#sales_order_date").datebox('enable');
        $("#customer_address_id").datebox('enable');
        $("#pph").numberbox('setValue', 0);

        $("#sales_order_date").datebox({
            onChange: function(sales_order_date) {
                var customer_id = $("#customer_id").combobox('getValue');
                if (customer_id == "") {
                    toastr.error("Please Choose Customer Name");
                    $("#sales_order_date").datebox('clear');
                } else {
                    number(customer_id, sales_order_date);
                    addTable(customer_id);
                }
            }
        });

        $('#customer_id').combobox({
            url: '<?= base_url('master/customers/reads/'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Customer Name',
            onSelect: function(customer) {
                var sales_order_date = $("#sales_order_date").datebox('getValue');
                $("#taxes").numberbox('setValue', customer.taxes);

                if (sales_order_date != "") {
                    number(customer.id, sales_order_date);
                }

                $('#customer_address_id').combobox({
                    url: '<?= base_url('master/customers/readAddress/'); ?>' + customer.id,
                    valueField: 'id',
                    textField: 'address',
                    panelHeight: 'panelHeight',
                    prompt: 'Choose Shipping Address',
                    onSelect: function(address) {
                        $("#attention_to").textbox('setValue', address.contact_person);
                    }
                });
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

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#customer_id").combobox('disable');
            $("#sales_order_no").textbox('disable');
            $("#sales_order_date").datebox('disable');
            $("#customer_address_id").datebox('disable');

            addTable(row.customer_id, '<?= base_url('planning/closing_sales_order/datatableUpdates?sales_order_no=') ?>' + window.btoa(row.sales_order_no));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }


    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_status=" + window.btoa(filter_status);

        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/sales_orders/print') ?>' + url);
    }


    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('planning/sales_orders/datatables') ?>',
            pagination: true,
            rownumbers: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.sales_order_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('planning/sales_orders/datatableDetails?sales_order_no=') ?>' + window.btoa(row.sales_order_no),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
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
                            title: 'Qty',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'delivery',
                            title: 'Delivery',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberFormat
                        }, {
                            field: 'outstanding',
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
                        }, {
                            field: 'total',
                            title: 'Total',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormat
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

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var sales_order_no = $("#sales_order_no").textbox('getValue');
                    var status = $("#status").combobox('getValue');

                    $.ajax({
                        type: "post",
                        url: '<?= base_url('planning/closing_sales_order/create') ?>',
                        data: {
                            sales_order_no: sales_order_no,
                            status: status,
                        },
                        dataType: "json",
                        success: function(result) {
                            $('#dlg_insert').dialog('close');

                            Swal.fire({
                                title: result.message,
                                icon: result.theme,
                                confirmButtonText: 'Ok',
                                allowOutsideClick: false,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#dg').datagrid('reload');
                    
                                }
                            });
                        }
                    });
                }
            }]
        });
    });

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
                url: '<?= base_url('planning/sales_orders/readSalesOrder/'); ?>' + customer.id,
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
                url: '<?= base_url('planning/sales_orders/readCustomerOrder/'); ?>' + customer.id,
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
</script>