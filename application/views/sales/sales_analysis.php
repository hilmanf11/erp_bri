<style>
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }
    #p {
      display: flex;
      flex-direction: column;
      height: 84vh;
      overflow: hidden !important;
    }
    #p #printout {
      flex: 1;
      width: 100%;
      height: 100%;
      border: 0;
      overflow: hidden !important;
    }
</style>

<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="CONDITIONS" style="padding: 20px;">
            <ul>
                <li><b>Filter Data</b> is to show Forecast Analyst base on <b>Filter Customer</b> or <b>Filter Product No</b>, The data is based on <b>Order Management > Forecasting > Forecast Customer</b> that were previously input.</li>
                <li><b>The Data Forecast</b> is based on <b>Order Management > Forecasting > Forecast Customer</b> that were previously input.</li>
                <li><b>The Data 4 Month</b> is based on <b>Calculating</b></li>
                <ul>
                    <li><b>The Data 4 month table for the 4th month</b>: Data (1st Month + 2nd Month + 3rd Month + 4th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 5th month</b>: Data (2nd Month + 3rd Month + 4th Month + 5th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 6th month</b>: Data (3rd Month + 4th Month + 5th Month + 6th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 7th month</b>: Data (4th Month + 5th Month + 6th Month + 7th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 8th month</b>: Data (5th Month + 6th Month + 7th Month + 8th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 9th month</b>: Data (6th Month + 7th Month + 8th Month + 9th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 10th month</b>: Data (7th Month + 8th Month + 9th Month + 10th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 11th month</b>: Data (8th Month + 9th Month + 10th Month + 11th Month) / 4</b></li>
                    <li><b>The Data 4 month table for the 12th month</b>: Data (9th Month + 10th Month + 11th Month + 12th Month) / 4</b></li>
                </ul>
                <li><b>The Data 6 Month</b> is based on <b>Calculating</b></li>
                <ul>
                    <li><b>The Data 6 month table for the 6th month</b>: Data (1st Month + 2nd Month + 3rd Month + 4th Month + 5th Month + 6th Month) / 6</b></li>
                    <li><b>The Data 6 month table for the 7th month</b>: Data (2nd Month + 3rd Month + 4th Month + 5th Month + 6th Month + 7th Month) / 6</b></li>
                    <li><b>The Data 6 month table for the 8th month</b>: Data (3rd Month + 4th Month + 5th Month + 6th Month + 7th Month + 8th Month) / 6</b></li>
                    <li><b>The Data 6 month table for the 9th month</b>: Data (4th Month + 5th Month + 6th Month + 7th Month + 8th Month + 9th Month) / 6</b></li>
                    <li><b>The Data 6 month table for the 10th month</b>: Data (5th Month + 6th Month + 7th Month + 8th Month + 9th Month + 10th Month) / 6</b></li>
                    <li><b>The Data 6 month table for the 11th month</b>: Data (6th Month + 7th Month + 8th Month + 9th Month + 10th Month + 11th Month) / 6</b></li>
                    <li><b>The Data 6 month table for the 12th month</b>: Data (7th Month + 8th Month + 9th Month + 10th Month + 11th Month + 12th Month) / 6</b></li>
                </ul>
                <li><b>The Data 12 Month</b> is based on <b>Calculating</b></li>
                <ul>
                    <li><b>The Data 12 month table for the 12th month</b>: Data (1st Month + 2nd Month + 3rd Month + 4th Month + 5th Month + 6th Month + 7th Month + 8th Month + 9th Month + 10th Month + 11th Month + 12th Month) / 12</b></li>
                </ul>
            </ul>
        </div>
    </div>
</div>

<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="filter_division" id="filter_division" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                    <!-- <input style="width:29.6%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox"> -->
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Month</span>

                    <input style="width:26.6%;" id="filter_period_month_from" class="easyui-combobox" value="<?= date("m") ?>">
                    <span style="width:6%; display:inline-block; text-align:center;">to</span>
                    <input style="width:26.62%;" id="filter_period_month_to" class="easyui-combobox" value="<?= date("m") ?>">

                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_name" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>

<script>
    function filter() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month_from = $("#filter_period_month_from").datebox("getValue");
        var filter_period_month_to = $("#filter_period_month_to").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combobox("getValue");
        var filter_division = $("#filter_division").textbox("getValue");
        var filter_product_family = $("#filter_product_family").combogrid('getValue');

        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month_from=" + window.btoa(filter_period_month_from) +
            "&filter_period_month_to=" + window.btoa(filter_period_month_to) +
            "&filter_customer_name=" + filter_customer_name +
            "&filter_item_fg=" + filter_item_fg +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_product_family=" + window.btoa(filter_product_family);

        if (filter_period_year == "" || filter_period_month_from == "" || filter_period_month_to == "") {
            toastr.warning("Please select Periode, Customer, and Product No.!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('sales/sales_analysis/print') ?>' + url);
        }
    }

    function excel() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month_from = $("#filter_period_month_from").datebox("getValue");
        var filter_period_month_to = $("#filter_period_month_to").datebox("getValue");
        var filter_customer_name = $("#filter_customer_name").combobox("getValue");
        var filter_item_fg = $("#filter_item_fg").combogrid("getValue");
        var filter_division = $("#filter_division").textbox("getValue");
        var filter_product_family = $("#filter_product_family").combogrid('getValue');

        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month_from=" + window.btoa(filter_period_month_from) +
            "&filter_period_month_to=" + window.btoa(filter_period_month_to) +
            "&filter_customer_name=" + filter_customer_name +
            "&filter_item_fg=" + filter_item_fg +
            "&filter_division=" + window.btoa(filter_division) +
            "&filter_product_family=" + window.btoa(filter_product_family);

        if (filter_period_year == "" && filter_period_month_from == "" && filter_period_month_to == "") {
            toastr.warning("Please select Year, Periode, Customer, or Product No.!");
        } else {
            window.location.assign('<?= base_url('sales/sales_analysis/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $('#filter_period_year').combobox({
        url: '<?= base_url('planning/summary_forecasts/readPeriod/year'); ?>',
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

    // $('#filter_period_month').combobox({
    //     url: '<?= base_url('sales/summary_forecasts/readPeriod/month'); ?>',
    //     valueField: 'id',
    //     textField: 'name',
    //     prompt: 'Choose Months',
    //     icons: [{
    //         iconCls: 'icon-clear',
    //         handler: function(e) {
    //             $(e.data.target).combobox('clear').combobox('textbox').focus();
    //         }
    //     }],
    // });

    $('#filter_period_month_from').combobox({
        url: '<?= base_url('planning/summary_forecasts/readPeriod/month'); ?>',
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

    $('#filter_period_month_to').combobox({
        url: '<?= base_url('planning/summary_forecasts/readPeriod/month'); ?>',
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
                title: 'Product No',
                width: 200
            }, {
                field: 'name',
                title: 'Product Name',
                width: 200
            }]
        ],
    });

    $('#filter_division').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'number',
        textField: 'name',
        panelHeight: 'panelHeight',
        prompt: 'Choose Plant',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
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

    $('#filter_product_family').combogrid({
        url: '<?= base_url('planning/forecasts/readsProductFamily') ?>',
        panelWidth: 420,
        idField: 'number',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Product Family",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [[
            {field: 'number', title: 'Code', width: 100},
            {field: 'name', title: 'Product Family', width: 200}
        ]]
    });
</script>