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
      /* height: calc(100vh - 200px); */
      height: 81.3vh;
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

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>

<div id="toolbar" style="padding:10px;">

    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->

    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">

        <legend><b>Form Filter Data</b></legend>

        <div style="width: 50%; float:left;">

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" id="filter_plant" class="easyui-combobox">
            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Receipt Date</span>

                <input style="width:26.6%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                <span style="width:6%; display:inline-block; text-align:center;">to</span>
                <input style="width:26.62%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Product No</span>

                <input style="width:60%;" id="filter_items" class="easyui-combogrid">

            </div>

            <div class="fitem">

                <span style="width:35%; display:inline-block;">Report Display</span>

                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">

                    <option value="RECAP">RECAP</option>

                    <option value="DETAIL">DETAIL</option>

                </select>

            </div>

        </div>

        <div style="width: 50%; float:left;">
        <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combogrid">
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans In</span>
                <select style="width:60%;" id="filter_qty_in" class="easyui-combobox" panelHeight="auto">
                    <option value="ALL">ALL</option>
                    <option value="ZERO">=0</option>
                    <option value="NONZERO">>0</option>
                </select>
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Out</span>
                <select style="width:60%;" id="filter_qty_out" class="easyui-combobox" panelHeight="auto">
                    <option value="ALL">ALL</option>
                    <option value="ZERO">=0</option>
                    <option value="NONZERO">>0</option>
                </select>
            </div>

            <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">

                <span style="width:35%; display:inline-block;"></span>

                <a href="javascript:;" class="easyui-linkbutton" onclick="filter_detail_transaction()"><i class="fa fa-search"></i> Detail Transaction</a>

                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>

            </div>
        </div>

    </fieldset>

    <?= $button ?>

    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="excel_detail_transaction()"><i class="fa fa-file"></i> Export Detail Transaction</a>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">

    <iframe id="printout" src="" style="width: 100%; height:500px; border: 0;"></iframe>

</div>

<script>

    function reload() {

        window.location.reload();

    }



    function pdf() {

        $("#printout").get(0).contentWindow.print();

    }



    function filter() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_items = $("#filter_items").combogrid('getValue');

        var filter_display = $("#filter_display").combobox('getValue');

        var filter_product_family = $("#filter_product_family").combogrid('getValue');

        var filter_qty_in = $("#filter_qty_in").combobox('getValue');

        var filter_qty_out = $("#filter_qty_out").combobox('getValue');

        var filter_plant = $("#filter_plant").combobox('getValue');



        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_product_family=" + filter_product_family + "&filter_qty_in=" + filter_qty_in + "&filter_qty_out=" + filter_qty_out +
        "&filter_plant=" + filter_plant;

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");

        $("#printout").attr('src', '<?= base_url('warehouse/report_history_transactions_fg/print') ?>' + url);

    }

    function filter_detail_transaction() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combogrid('getValue');
        var filter_qty_in = $("#filter_qty_in").combobox('getValue');
        var filter_qty_out = $("#filter_qty_out").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_product_family=" + filter_product_family + "&filter_qty_in=" + filter_qty_in + "&filter_qty_out=" + filter_qty_out + "&filter_plant=" + filter_plant;

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/report_history_transactions_fg/detail_transaction') ?>' + url);
    }

    function excel() {

        var filter_from = $("#filter_from").datebox('getValue');

        var filter_to = $("#filter_to").datebox('getValue');

        var filter_items = $("#filter_items").combogrid('getValue');

        var filter_display = $("#filter_display").combobox('getValue');

        var filter_product_family = $("#filter_product_family").combogrid('getValue');

        var filter_qty_in = $("#filter_qty_in").combobox('getValue');

        var filter_qty_out = $("#filter_qty_out").combobox('getValue');

        var filter_plant = $("#filter_plant").combobox('getValue');



        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_display=" + filter_display + "&filter_product_family=" + filter_product_family + "&filter_qty_in=" + filter_qty_in + "&filter_qty_out=" + filter_qty_out +
        "&filter_plant=" + filter_plant;

        window.location.assign('<?= base_url('warehouse/report_history_transactions_fg/print/excel') ?>' + url);

    }

    function excel_detail_transaction() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_product_family = $("#filter_product_family").combogrid('getValue');
        var filter_qty_in = $("#filter_qty_in").combobox('getValue');
        var filter_qty_out = $("#filter_qty_out").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');

        url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_items=" + filter_items + "&filter_product_family=" + filter_product_family + "&filter_qty_in=" + filter_qty_in + "&filter_qty_out=" + filter_qty_out + "&filter_plant=" + filter_plant;
        
        window.location.assign('<?= base_url('warehouse/report_history_transactions_fg/detail_transaction/excel') ?>' + url);
    }

    $(function() {

        $("#filter_transtype").combobox({

            url: '<?= base_url('master/transaction_types/reads') ?>',

            valueField: 'number',

            textField: 'name',

            prompt: "Select Transaction Type",

            icons: [{

                iconCls: 'icon-clear',

                handler: function(e) {

                    $(e.data.target).combobox('clear').combobox('textbox').focus();

                }

            }],

        });



        $('#filter_items').combogrid({

            url: '<?= base_url('master/item_fg/reads') ?>',

            panelWidth: 420,

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

                    width: 100

                }, {

                    field: 'name',

                    title: 'Product Name',

                    width: 200

                }, ]

            ]

        });

        $('#filter_product_family').combogrid({

            url: '<?= base_url('warehouse/report_history_transactions_fg/reads_product_family') ?>',

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

    });

    $('#filter_plant').combobox({
        url: '<?= base_url('master/divisions/reads'); ?>',
        valueField: 'id',
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

</script>