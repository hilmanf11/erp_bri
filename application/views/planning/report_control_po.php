<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Supplier is taken from <b>Master Data > Material Control > Suppliers</b></li>
                <li>The Data Part No is taken from <b>Master Data > Engineering > Item Raw Materials</b></li>
                <li>The Data Currency is taken from <b>Master Data > Material Control > Suppliers > Currency</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'customer_name',width:220,halign:'center'">Customer Name</th>
            <th data-options="field:'product_no',width:150,halign:'center'">Product No</th>
            <th data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
            <th data-options="field:'ost_so',width:110,halign:'center',formatter:numberFormat">Outstanding SO</th>
            <th data-options="field:'so_m0',width:200,align:'center',formatter:numberFormat">SO M0</th>
            <th data-options="field:'total_so',width:100,halign:'center',formatter:numberFormat">Total SO</th>
            <th data-options="field:'delivery',width:100,halign:'center',formatter:numberFormat">Delivery</th>
            <th data-options="field:'balance',width:220,halign:'center',formatter:numberFormat">Balance</th>
            <th data-options="field:'forecast',width:100,halign:'center',formatter:numberFormat">Forecast</th>
            <th data-options="field:'bal_fc',width:100,halign:'center',formatter:formatPercentage,styler:stylePercentage">Bal Forecast</th>
            <th data-options="field:'total_sales',width:100,halign:'center',formatter:formatRupiah">Total Sales</th>
            <th data-options="field:'bal_sales',width:100,halign:'center',formatter:numberFormat">Bal Sales</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 240px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox"> 
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customer_name" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/report_control_po/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function numberFormat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function formatRupiah(angka) {
        if (!angka || isNaN(angka)) return "Rp 0";
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });
        return formatter.format(angka);
    }

    // Formatter untuk format persentase
    function formatPercentage(value, row) {
        if (value == null || isNaN(value)) return "0.00%";
        // Ubah rumus bal_forecast
        // var so_m0 = row.so_m0 || 0;
        // var forecast = row.forecast || 1; // Hindari pembagian dengan nol
        // var bal_forecast = ((so_m0 - forecast) / forecast) * 100;
        return value + "%";
    }

    // Styler untuk menentukan warna berdasarkan nilai
    function stylePercentage(value, row) {
        if (value == null || isNaN(value)) return '';
        if (value < 50) { // Kurang dari 50%
            return 'background-color:#FF0000;color:white;';
        }
        if (value > 100) { // Lebih dari 100%
            return 'background-color:#FFFF00;color:black;';
        }
        return '';
    }

    //FILTER DATA
    function filter() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month = $("#filter_period_month").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combobox("getValue");

        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month);

        if (filter_customer_name !== "") {
            url += "&filter_customer_name=" + filter_customer_name;
        }

        if (filter_item_fg !== "") {
            url += "&filter_item_fg=" + filter_item_fg;
        }

        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode, Customer, and Product No.!");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/report_control_po/datatables') ?>' + url,
                pagination: true,
                clientPaging: false,
                remoteFilter: true,
                rownumbers: true,
                fit: true,
                pageList: [10, 50, 100, 500, 1000],
                pageSize: 10,
                onLoadSuccess: function(data) {
                    // Format kolom Total Sales setelah data dimuat
                    $('#dg').datagrid('getPanel').find('.datagrid-view2 .datagrid-body tr').each(function(index) {
                        var row = data.rows[index];
                        var totalSalesCell = $(this).find('td[field="total_sales"] div');
                        totalSalesCell.text(formatRupiah(row.total_sales));
                    });
                }
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/report_control_po/print') ?>' + url);
        }
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month = $("#filter_period_month").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combogrid("getValue");
        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_customer_name=" + filter_customer_name +
            "&filter_item_fg=" + filter_item_fg;
        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode, Customer, and Part No.!");
        } else {
            window.location.assign('<?= base_url('planning/report_control_po/print/excel') ?>' + url);
        }
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $('#filter_period_year').combobox({
        url: '<?= base_url('planning/report_control_po/readPeriod/year'); ?>',
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

    $('#filter_period_month').combobox({
        url: '<?= base_url('planning/report_control_po/readPeriod/month'); ?>',
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

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product No.",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'number',
                title: 'Part No',
                width: 200
            }, {
                field: 'name',
                title: 'Part Name',
                width: 200
            }]
        ],
    });

    $('#filter_customer_name').combogrid({
        url: '<?= base_url("master/customers/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Customer",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'number',
                title: 'Code',
                width: 100
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 300
            }]
        ],
    });

    $(function() {
        //SETTING DATAGRID EASYUI
        var filter_supplier_id = $("#filter_supplier_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        url = "?filter_supplier_id=" + window.btoa(filter_supplier_id) + "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);
        $('#dg').datagrid({
            url: '<?= base_url('master/supplier_items/datatables') ?>' + url,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });
       
    });

</script>