<style>
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    #p {
      display: flex;
      flex-direction: column;
      /* height: calc(100vh - 200px); */
      height: 100vh;
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
                <li><b>Filter Data</b> is to show Forecast Compound Used base on <b>Filter Period</b>, The data is based on <b>Order Management > Forecasting > Forecast Customer</b> that were previously input.</li>
                <li><b>The Grand Total</b> is the sum from each data filtered through the <b>Filter Period</b> or <b>Filter Compound No.</b> </li>
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
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                    <input style="width:29.6%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Compound No</span>
                    <input style="width:60%;" id="filter_compound_no" class="easyui-combobox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combogrid">
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
        var filter_period_month = $("#filter_period_month").datebox("getValue");        
        var filter_compound_no = $("#filter_compound_no").combobox("getValue");
        var filter_product_family = $("#filter_product_family").combogrid('getValue');

        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_compound_no=" + filter_compound_no +
            "&filter_product_family=" + window.btoa(filter_product_family);

        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode or Compound No.!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/forecast_compound_used/print') ?>' + url);
        }
    }

    function excel() {
        var filter_period_year = $("#filter_period_year").datebox("getValue");
        var filter_period_month = $("#filter_period_month").datebox("getValue");
        var filter_compound_no = $("#filter_compound_no").combobox("getValue");
        var filter_product_family = $("#filter_product_family").combogrid('getValue');

        var url = "?filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_compound_no=" + filter_compound_no +
            "&filter_product_family=" + window.btoa(filter_product_family);

        if (filter_period_year == "" && filter_period_month == "") {
            toastr.warning("Please select Periode or Compound No.!");
        } else {
            window.location.assign('<?= base_url('planning/forecast_compound_used/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $('#filter_period_year').combobox({
        url: '<?= base_url('planning/forecast_compound_used/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onChange: () => {
            updateCompoundNo()
        }
    });

    $('#filter_period_month').combobox({
        url: '<?= base_url('planning/forecast_compound_used/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        onChange: () => {
            updateCompoundNo()
        }
    });

    function updateCompoundNo() {
        var year = $('#filter_period_year').combobox('getValue');
        var month = $('#filter_period_month').combobox('getValue');

        if (year && month) {
            var newUrl = '<?= base_url("planning/forecast_compound_used/readCompoundNo") ?>' + '/' + window.btoa(month) + '/' + window.btoa(year);
            
            $('#filter_compound_no').combobox('clear');
            $('#filter_compound_no').combobox('reload', newUrl);
            $('#filter_compound_no').combobox('options').url = newUrl;
        }
    }

    $('#filter_compound_no').combobox({
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Compound No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
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

    $(function() {
        updateCompoundNo();
    });
</script>