
<!-- <div id="toolbar" style="padding:10px;"> -->

<div id="f" class="easyui-accordion" style="width:100%;">

    <div title="Click this to hide the filter" data-options="selected:true" style="padding:8px; background:#F4F4F4;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" id="filter_plant" class="easyui-combobox" panelHeight="auto"
                    data-options="editable:false">
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Transaction Date</span>
                <input style="width:26.6%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>"
                    data-options="formatter:myformatter,parser:myparser, editable:false">
                <span style="width:6%; display:inline-block; text-align:center;">to</span>
                <input style="width:26.62%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>"
                    data-options="formatter:myformatter,parser:myparser, editable:false">

            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_items" class="easyui-combogrid">
            </div>
        </div>

        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Customer Name</span>
                <input style="width:60%;" id="filter_customer_id" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto"
                    data-options="editable:false">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> LSB</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>

    </fieldset>

    <?= $button ?>
    </div>
</div>
<!-- </div> -->

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 500px; border: 0;"></iframe>
</div>

<script>
    $(function () {
        function updatePrintoutHeight() {
            if ($('.accordion-header-selected').length > 0) {
                $('#printout').css('height', '500px');
            } else {
                $('#printout').css('height', '95%');
            }
        }

        updatePrintoutHeight();
        setInterval(updatePrintoutHeight, 200);
    });

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
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');

        url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_items=" + filter_items +
            "&filter_display=" + filter_display +
            "&filter_product_family=" + filter_product_family +
            "&filter_customer_id=" + filter_customer_id +
            "&filter_plant=" + filter_plant;

        $("#printout").contents().find('html').html(
            "<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/report_history_transactions_wip/print') ?>' + url);
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_items = $("#filter_items").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');

        url = "?filter_from=" + filter_from +
            "&filter_to=" + filter_to +
            "&filter_items=" + filter_items +
            "&filter_display=" + filter_display +
            "&filter_product_family=" + filter_product_family +
            "&filter_customer_id=" + filter_customer_id +
            "&filter_plant=" + filter_plant;

        window.location.assign('<?= base_url('control/report_history_transactions_wip/print/excel') ?>' + url);
    }

    $(function() {

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
            url: '<?= base_url('control/report_history_transactions_wip/reads_product_family') ?>',
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
            columns: [
                [{
                        field: 'number',
                        title: 'Code',
                        width: 100
                    },
                    {
                        field: 'name',
                        title: 'Product Family',
                        width: 200
                    }
                ]
            ]
        });


        var minDate = new Date(2026, 4, 25);

        $('#filter_from').datebox().datebox('calendar').calendar({
            validator: function(date){
                return date >= minDate;
            }
        });

        $('#filter_to').datebox().datebox('calendar').calendar({
            validator: function(date){
                return date >= minDate;
            }
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
    });

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
</script>