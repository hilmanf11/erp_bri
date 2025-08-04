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
      height: 76.5vh;
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

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 270px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="filter_plant" class="easyui-combobox">
                </div>
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
                    <span style="width:35%; display:inline-block;">Delivery Note No</span>
                    <input style="width:60%;" id="filter_delivery_note_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Order No</span>
                    <input style="width:60%;" id="filter_customer_order_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_product_family" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Delivery</span>
                    <select style="width:60%;" id="filter_status_delivery" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">ON SCHEDULE</option>
                        <option value="1">DELAY</option>
                        <option value="2">EARLY</option>
                    </select>
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status Invoice</span>
                    <select style="width:60%;" id="filter_status" panelHeight="auto" class="easyui-combobox">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div> -->
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>


<script>
    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status_delivery = $("#filter_status_delivery").combobox('getValue');
        // var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status_delivery=" + window.btoa(filter_status_delivery) +
            // "&filter_status=" + window.btoa(filter_status) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_plant=" + window.btoa(filter_plant);

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('sales/report_delivery_notes/print') ?>' + url);
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
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_customer_order_no = $("#filter_customer_order_no").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_status_delivery = $("#filter_status_delivery").combobox('getValue');
        // var filter_status = $("#filter_status").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combogrid('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_customer_order_no=" + window.btoa(filter_customer_order_no) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_status_delivery=" + window.btoa(filter_status_delivery) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            // "&filter_status=" + window.btoa(filter_status) +
            "&filter_plant=" + window.btoa(filter_plant);

        window.location.assign('<?= base_url('sales/report_delivery_notes/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
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
            $('#filter_delivery_note_no').combobox({
                url: '<?= base_url('sales/delivery_notes/readDelivery_note_no/'); ?>' + customer.id,
                valueField: 'delivery_note_no',
                textField: 'delivery_note_no',
                prompt: 'Choose All',
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
                onSelect: function(deliver_note) {
                    $('#filter_customer_order_no').combobox({
                        url: '<?= base_url('sales/delivery_notes/readCustomerOrder/'); ?>' + customer.id,
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

    $('#filter_plant').combobox({
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

    //CELLSTYLE STATUS
    // function cellStyler(value, row, index) {
    //     if (value == 0) {
    //         return 'background: #53D636; color:white;';
    //     } else if(value == 2) {
    //         return 'background: #F3A26D; color: white';
    //     } else {
    //         return 'background: #FF5F5F; color:white;';
    //     }
    // }
    //FORMATTER STATUS
    // function cellFormatter(value) {
    //     if (value == 0) {
    //         return 'OPEN';
    //     } else {
    //         return 'CLOSE';
    //     }
    // };
    //FORMATTER DELIVERY STATUS 
    // function cellFormatterDeliveryStatus(value) {
    //     if (value == 0) {
    //         return 'ON SCHEDULE';
    //     } else if(value == 1) {
    //         return 'DELAY';
    //     }else {
    //         return 'EARLY';
    //     }
    // };

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

    // function numberFormat(value, row) {
    //     const formatter = new Intl.NumberFormat('id-ID', {
    //         minimumFractionDigits: 0
    //     });
    //     return "<b>" + formatter.format(value) + "</b>";
    // }

    //CELLSTYLE APPROVE
    // function styleApproved(value, row, index) {
    //     if (value == "" || value === null) {
    //         return 'background: #53D636; color:white;';
    //     } else {
    //         return 'background: #FF5F5F; color:white;';
    //     }
    // }

    //FORMATTER APPROVE
    // function formatApproved(value) {
    //     if (value == "" || value === null) {
    //         return 'Approved';
    //     } else {
    //         return 'Checking';
    //     }
    // }
</script>