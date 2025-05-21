<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'serial_box',width:270,halign:'center',sortable:true">Serial Label Box</th>
            <th rowspan="2" data-options="field:'item_number',width:200,halign:'center',sortable:true">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center',sortable:true">Product Name</th>
            <th rowspan="2" data-options="field:'qty_box',width:100,halign:'center',sortable:true">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:100,halign:'center',sortable:true">UoM</th>
            <th rowspan="2" data-options="field:'print',width:100,align:'center', formatter:btnPrint">Re-Print</th>
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
<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 90%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Trans Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- Insert -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" 
    style="width: 1600px; height: 600px; padding:10px; top: 20px; display: flex; flex-direction: column;">

    <form id="frm_insert" method="post" novalidate style="display: flex; gap: 20px; justify-content: center;">

        <!-- Form Add -->
        <div style="width: 45%; display: flex; flex-direction: column;">
            <fieldset style="border:1px solid #d0d0d0; border-radius:4px; padding: 10px;">
                <legend><b>Form Add</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_number" id="item_fg_number" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_fg_name" id="item_fg_name" class="easyui-textbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Packing</span>
                    <input style="width:40%;" id="qty_packing" name="qty_packing" class="easyui-numberbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Box</span>
                    <input style="width:40%;" id="qty_box" name="qty_box" class="easyui-numberbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="preview()"><i class="fa fa-search"></i> Preview Data</a>
                </div>
            </fieldset>

            <!-- Table dg2 -->
            <table id="dg_request" class="easyui-datagrid" style="width:100%;" title="Supply Sheet List" data-options="rownumbers: true, singleSelect: false" idField="component_number">
            </table>
        </div>

        <!-- Tombol Transfer -->
        <div style="display: flex; flex-direction: column; justify-content: center; gap: 10px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" style="background: green; color: white; padding: 8px 15px; text-align: center; font-size: 20px;" 
            onclick="moveToLabelBox()">>>>
            </a>
            <a href="javascript:void(0)" class="easyui-linkbutton" style="background: red; color: white; padding: 8px 15px; text-align: center; font-size: 20px;" 
            onclick="moveToRequest()"><<<
            </a>
        </div>

        <!-- Create Label Box -->
        <div style="width: 45%; display: flex; flex-direction: column;">
            <fieldset style="border:1px solid #d0d0d0; border-radius:4px; padding: 10px;">
                <legend><b>Create Label Box</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Serial Label Box</span>
                    <input style="width:60%;" name="serial_box" id="serial_box" class="easyui-textbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_number2" id="item_fg_number2" class="easyui-textbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_fg_name2" id="item_fg_name2" class="easyui-textbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Box</span>
                    <input style="width:40%;" id="qty_box2" name="qty_box2" class="easyui-numberbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Input Qty/Box</span>
                    <input style="width:40%;" id="input_qty_box2" name="input_qty_box2" class="easyui-numberbox" required readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="save()"><i class="fa fa-save"></i> Save</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="print()"><i class="fa fa-print"></i> Print</a>
                </div>
            </fieldset>

            <!-- Table dg3 -->
            <table id="dg_request2" class="easyui-datagrid" style="width:100%;" title="Supply Sheet List" data-options="rownumbers: true, singleSelect: false" idField="component_number">
                <thead>
                    <tr>
                        <th field="ck" checkbox="true"></th>
                        <th field="serial_label" width="200">Serial Label Packing</th>
                        <th field="item_fg_number" width="200">Product No</th>
                        <th field="item_fg_name" width="200">Product Name</th>
                        <th field="qty" width="100">Qty</th>
                        <th field="uom" width="75">UoM</th>
                        <th field="compound_lot" width="200">LOT</th>
                        <th field="trans_date" width="100">Packing Date</th>
                    </tr>
                </thead>
            </table>
        </div>

        <input type="hidden" id="item_fg_id" name="item_fg_id">
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('sales/delivery_orders/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg_request').datagrid('loadData', []);
        $('#frm_insert').form('clear');
        generateSerialBox();

        $('#item_fg_number').combogrid({
            url: '<?= base_url("control/label_box/readitems") ?>',
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
                    $('#item_fg_name').textbox('setValue', '');
                    $('#qty_packing').numberbox('setValue', '');
                    $('#qty_box').numberbox('setValue', '');
                }
            }],
            columns: [[
                { field: 'number', title: 'Product No', width: 200 },
                { field: 'name', title: 'Product Name', width: 200 }
            ]],
            onSelect: function(index, row) {
                console.log(row); // Debug: Lihat data yang dipilih
                $('#item_fg_name').textbox('setValue', row.name);
                $('#item_fg_name2').textbox('setValue', row.name);
                $('#item_fg_number2').textbox('setValue', row.number);
                $('#item_fg_id').val(row.id); // Simpan item_fg_id ke dalam input tersembunyi
                $('#qty_packing').numberbox('setValue', row.qty_packing);
                $('#qty_box').numberbox('setValue', row.qty_box);
                $('#qty_box2').numberbox('setValue', row.qty_box);
            }
        });
    }

    function preview() {
        var item_fg_id = $("#item_fg_id").val();
        var item_fg_number = $("#item_fg_number").val();

        if (item_fg_number === "") {
            toastr.warning('Please select Product No', 'Required');
        } else {
            $('#dg_request').datagrid({
                url: '<?= base_url('control/label_box/datatablesTemp') ?>?&item_fg_id=' + item_fg_id,
                singleSelect: false,
                idField: 'item_fg_id',
                columns: [
                    [{
                        field: 'ck',
                        checkbox: true,
                    }, {
                        field: 'serial_label',
                        width: 200,
                        halign: 'center',
                        title: "Serial Label Packing",
                    }, {
                        field: 'item_fg_number',
                        width: 200,
                        halign: 'center',
                        title: "Product No",
                    }, {
                        field: 'item_fg_name',
                        width: 200,
                        halign: 'center',
                        title: "Product Name",
                    }, {
                        field: 'qty',
                        width: 100,
                        halign: 'center',
                        title: "Qty",
                    }, {
                        field: 'uom',
                        width: 75,
                        halign: 'center',
                        title: "UoM",
                    }, {
                        field: 'compound_lot',
                        width: 200,
                        halign: 'center',
                        title: "LOT",
                    }, {
                        field: 'trans_date',
                        width: 100,
                        halign: 'center',
                        title: "Packing Date",
                    }]
                ],
                onLoadSuccess: function(data) {
                    if (data.length === 0) {
                        toastr.warning("No data found for the selected item.", "Information");
                    }
                }
            });
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#delivery_order_date").datebox('disable');
            $("#delivery_date").combobox('disable');
            $("#customer_id").combobox('disable');
            $("#customer_order_no").combobox('disable');
            $("#btnPreview").linkbutton('disable');

            preview("<?= base_url('sales/delivery_orders/datatableUpdates?delivery_order_no=') ?>" + btoa(row.delivery_order_no));
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
                            url: '<?= base_url('sales/delivery_orders/delete') ?>',
                            data: {
                                delivery_order_no: row.delivery_order_no
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
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

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg);

                $('#dg').datagrid({
                        url: '<?= base_url('control/label_box/datatables') ?>' + url,
                        pagination: true,
                        rownumbers: true,
                        fit: true,
                        pageList: [10, 50, 100, 500, 1000],
                        pageSize: 10,
                        resizable: true,
                        remoteSort: false,
                        view: detailview,
                        detailFormatter: function(index, row) {
                            return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.serial_label + '"></table></div>';
                        },
                        onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                console.log("Loading details for serial_box: ", row.serial_box); // Debug

                ddv.datagrid({
                    url: '<?= base_url('control/label_box/datatableDetails?serial_box=') ?>' + window.btoa(row.serial_box),
                    singleSelect: true,
                    rownumbers: true,
                    loadMsg: 'Loading...',
                    columns: [[
                        { field: 'serial_label', title: 'Serial Label Packing', halign: 'center', width: 250 },
                        { field: 'item_number', title: 'Product No.', halign: 'center', width: 200 },
                        { field: 'item_name', title: 'Product Name', halign: 'center', width: 200 },
                        { field: 'qty_packing', title: 'Qty', halign: 'center', width: 150 },
                        { field: 'uom', title: 'UoM', halign: 'center', width: 80 },
                        { field: 'compound_lot', title: 'LOT', align: 'center', width: 200 },
                        { field: 'trans_date', title: 'Production Date', halign: 'center', align: 'right', width: 150, formatter: date },
                    ]],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function(data) {
                        if (data.length === 0) {
                            console.warn("No details found for serial_label: ", row.serial_label);
                        }
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/label_box/print') ?>' + url);
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
        var filter_delivery_order_no = $("#filter_delivery_order_no").combobox('getValue');
        var filter_sales_order_no = $("#filter_sales_order_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_order_no=" + window.btoa(filter_delivery_order_no) +
            "&filter_sales_order_no=" + window.btoa(filter_sales_order_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status=" + window.btoa(filter_status);

        window.location.assign('<?= base_url('sales/delivery_orders/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        // Nonaktifkan tombol save saat inisialisasi
        $('.easyui-linkbutton[onclick="save()"]').linkbutton('disable');
    });

    function generateSerialBox() {
        var today = new Date();
        var year = today.getFullYear().toString().slice(-2);
        var month = (today.getMonth() + 1).toString().padStart(2, '0');
        var day = today.getDate().toString().padStart(2, '0');

        var lastRow = $('#dg_request2').datagrid('getData').total;
        var order = (lastRow + 1).toString().padStart(4, '0');

        var serialBox = 'BOX' + year + month + day + '-' + order;
        $('#serial_box').textbox('setValue', serialBox);
    }

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

    function save() {
        var rows = $('#dg_request2').datagrid('getRows');
        if (rows.length > 0) {
            var serialBox = $('#serial_box').textbox('getValue');
            var qtyBox = $('#qty_box').numberbox('getValue');
            var qtyPacking = $('#qty_packing').numberbox('getValue');

            rows.forEach(function(row) {
                row.serial_box = serialBox;
                row.qty_box = qtyBox;
                row.qty_packing = qtyPacking;
            });

            $.messager.confirm('Konfirmasi', 'Apakah Anda yakin ingin menyimpan data ini?', function(r) {
                if (r) {
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('control/label_box/create') ?>",
                        data: { data: rows },
                        dataType: "json",
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                $('#dlg_insert').dialog('close');
                                $('#dg').datagrid('reload');
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Terjadi kesalahan saat menyimpan data.');
                        }
                    });
                }
            });
        } else {
            toastr.warning('Tidak ada data untuk disimpan.');
        }
    }

    function calculateTotalQty() {
        var rows = $('#dg_request2').datagrid('getRows');
        var totalQty = 0;
        for (var i = 0; i < rows.length; i++) {
            totalQty += parseFloat(rows[i].qty) || 0;
        }
        $('#input_qty_box2').numberbox('setValue', totalQty);

        var qtyBox = parseFloat($('#qty_box2').numberbox('getValue')) || 0;
        if (totalQty === qtyBox) {
            toastr.info('Input Qty/Box sudah sesuai dengan Qty/Box.');
            $('.easyui-linkbutton[onclick="save()"]').linkbutton('enable');
        } else {
            $('.easyui-linkbutton[onclick="save()"]').linkbutton('disable');
        }
    }

    function moveToLabelBox() {
        var selectedRows = $('#dg_request').datagrid('getSelections');
        if (selectedRows.length > 0) {
            for (var i = 0; i < selectedRows.length; i++) {
                $('#dg_request2').datagrid('appendRow', {
                    serial_box: selectedRows[i].serial_box,
                    serial_label: selectedRows[i].serial_label,
                    item_fg_number: selectedRows[i].item_fg_number,
                    item_fg_name: selectedRows[i].item_fg_name,
                    item_fg_id: selectedRows[i].item_fg_id,
                    qty: selectedRows[i].qty,
                    uom: selectedRows[i].uom,
                    compound_lot: selectedRows[i].compound_lot,
                    trans_date: selectedRows[i].trans_date,
                    serial_no: selectedRows[i].serial_no
                });
            }
            for (var i = 0; i < selectedRows.length; i++) {
                var index = $('#dg_request').datagrid('getRowIndex', selectedRows[i]);
                $('#dg_request').datagrid('deleteRow', index);
            }
            calculateTotalQty();
        } else {
            alert("Please select at least one row to move.");
        }
    }

    function moveToRequest() {
        var selectedRows = $('#dg_request2').datagrid('getSelections');
        if (selectedRows.length > 0) {
            for (var i = 0; i < selectedRows.length; i++) {
                $('#dg_request').datagrid('appendRow', {
                    serial_label: selectedRows[i].serial_label,
                    item_fg_number: selectedRows[i].item_fg_number,
                    item_fg_name: selectedRows[i].item_fg_name,
                    qty: selectedRows[i].qty,
                    uom: selectedRows[i].uom,
                    compound_lot: selectedRows[i].compound_lot,
                    trans_date: selectedRows[i].trans_date
                });
            }
            for (var i = 0; i < selectedRows.length; i++) {
                var index = $('#dg_request2').datagrid('getRowIndex', selectedRows[i]);
                $('#dg_request2').datagrid('deleteRow', index);
            }
            calculateTotalQty();
        } else {
            alert("Please select at least one row to move.");
        }
    }

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

    function btnPrint(val, row) {
        var print = "print_do('" + row.serial_box + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function print_do(delivery_order_no) {
        window.open("<?= base_url('sales/delivery_orders/print_do/') ?>" + window.btoa(delivery_order_no), "_blank", "width=1200,height=600");
    }

    function checkValue(newValue, oldValue) {
        if (newValue > 0) {
            $(this).numberbox('readonly', true);
        } else {
            $(this).numberbox('readonly', false);
        }
    }
</script>