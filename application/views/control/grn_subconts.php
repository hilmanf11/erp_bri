<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'printed',width:100,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'incoming_doc_no',width:220,halign:'center',sortable:true">Incoming Doc No</th>
            <th rowspan="2" data-options="field:'incoming_date',width:180,halign:'center',sortable:true">Incoming Date</th>
            <th rowspan="2" data-options="field:'source_name',width:220,halign:'center',sortable:true">Source Name</th>
            <th rowspan="2" data-options="field:'total_qty_incoming',width:200,halign:'center',sortable:true, formatter:numberFormatField, align:'center'">Total Qty Incoming</th>
            <th rowspan="2" data-options="field:'grand_total_price',width:200,halign:'center',sortable:true, formatter:numberFormat, align:'center'">Grand Total Price</th>
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
<div id="toolbar" style="height: 230px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Date</span>
                    <input style="width:29.8%;" id="filter_from" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:29.8%;" id="filter_to" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source Name</span>
                    <input style="width:60%;" id="filter_source_name" name="filter_source_name" class="easyui-combogrid" data-options="editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note No</span>
                    <input style="width:60%;" id="filter_delivery_note_no" class="easyui-combobox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Doc No</span>
                    <input style="width:60%;" id="filter_incoming_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder Label</span>
                    <input style="width:60%;" id="filter_workorder_label" class="easyui-combobox">
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>

        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="print_recap()" id="print_recap"><i class="fa fa-print"></i> Print</a>

        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="print_recap_excel()" id="export_recap"><i class="fa fa-file"></i> Export Recap</a>

        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="print_detail_excel()" id="export_detail"><i class="fa fa-file"></i> Export Detail</a>

        <?= $button ?>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/grn_subconts/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    let isFiltered = false;

    //FILTER DATA
    function filter() {
        isFiltered = true;
        
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_workorder_label = $("#filter_workorder_label").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_source_name=" + window.btoa(filter_source_name) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_workorder_label=" + window.btoa(filter_workorder_label);

        $('#dg').datagrid({
            url: '<?= base_url('control/grn_subconts/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.incoming_doc_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                let filter_delivery_note_no = $('#filter_delivery_note_no').combobox('getValue');
                let filter_workorder_label = $('#filter_workorder_label').combobox('getValue');

                ddv.datagrid({
                    url: '<?= base_url('control/grn_subconts/datatableDetails?incoming_doc_no=') ?>' + encodeURIComponent(window.btoa(row.incoming_doc_no))
                    + '&delivery_note_no=' + encodeURIComponent(window.btoa(filter_delivery_note_no))  
                    + '&workorder_label=' + encodeURIComponent(window.btoa(filter_workorder_label)),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 240
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 240
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 240
                        },{
                            field: 'delivery_note_no',
                            title: 'Delivery Note No',
                            halign: 'center',
                            width: 240
                        }, {
                            field: 'delivery_date',
                            title: 'Delivery Date',
                            align: 'center',
                            width: 200
                        }, {
                            field: 'target_date',
                            title: 'Target Date',
                            align: 'center',
                            width: 200
                        }, {
                            field: 'workorder',
                            title: 'Workorder',
                            align: 'center',
                            width: 200
                        }, {
                            field: 'workorder_label',
                            title: 'Workorder Label',
                            align: 'center',
                            width: 200
                        }, {
                            field: 'qty_incoming',
                            title: 'Qty Incoming',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormatField
                        }, {
                            field: 'price_pcs',
                            title: 'Price/pcs',
                            halign: 'center',
                            align: 'right',
                            width: 120,
                            formatter: numberFormat,
                        }, {
                            field: 'total_price',
                            title: 'Total Price',
                            halign: 'center',
                            align: 'right',
                            width: 120,
                            formatter: numberFormat
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function(data) {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);

                        console.log(data);
                        
                    },
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            },
            onLoadSuccess: function(data) {
                toggleButtonExport(data.total || data.rows.length);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/grn_subconts/print') ?>' + url);
    }

    //PRINT PDF
    function print_recap() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function print_recap_excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_workorder_label = $("#filter_workorder_label").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_source_name=" + window.btoa(filter_source_name) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_workorder_label=" + window.btoa(filter_workorder_label);

        window.location.assign('<?= base_url('control/grn_subconts/print/excel') ?>' + url);
    }

    //PRINT DETAIL EXCEL
    function print_detail_excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_source_name = $("#filter_source_name").combogrid('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_workorder_label = $("#filter_workorder_label").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_source_name=" + window.btoa(filter_source_name) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_workorder_label=" + window.btoa(filter_workorder_label);

        window.location.assign('<?= base_url('control/grn_subconts/print_detail/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    function reloadDeliveryNoteCombo() {
        var filter_froms = $("#filter_from").datebox("getValue");
        var filter_tos   = $("#filter_to").datebox("getValue");
        var filter_source_name   = $("#filter_source_name").combogrid("getValue");

        var url = '<?= base_url('control/grn_subconts/readDeliveryNoteNo'); ?>'
                + '?filter_from=' + encodeURIComponent(filter_froms)
                + '&filter_to=' + encodeURIComponent(filter_tos)
                + '&filter_source_name=' + encodeURIComponent(filter_source_name);

        $('#filter_delivery_note_no').combobox('reload', url);
    }

    function reloadIncomingDocNoCombo() {
        var filter_froms = $("#filter_from").datebox("getValue");
        var filter_tos   = $("#filter_to").datebox("getValue");
        var filter_source_name = $("#filter_source_name").combogrid("getValue");

        var url = '<?= base_url('control/grn_subconts/readIncomingDocNo'); ?>'
                + '?filter_from=' + encodeURIComponent(filter_froms)
                + '&filter_to=' + encodeURIComponent(filter_tos)
                + '&filter_source_name=' + encodeURIComponent(filter_source_name);

        $('#filter_incoming_doc_no').combobox('reload', url);
    }

    function reloadWorkorderLabel() {
        var filter_froms = $("#filter_from").datebox("getValue");
        var filter_tos   = $("#filter_to").datebox("getValue");
        var filter_source_name = $("#filter_source_name").combogrid("getValue");

        var url = '<?= base_url('control/grn_subconts/readWorkorderLabels'); ?>'
                + '?filter_from=' + encodeURIComponent(filter_froms)
                + '&filter_to=' + encodeURIComponent(filter_tos)
                + '&filter_source_name=' + encodeURIComponent(filter_source_name);

        $('#filter_workorder_label').combobox('reload', url);
    }

    $(function() {
        toggleButtonExport();

        $('#filter_delivery_note_no').combobox({
            valueField: 'delivery_note_no',
            textField: 'delivery_note_no',
            prompt: 'Choose All',
            editable: true,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_incoming_doc_no').combobox({
            valueField: 'incoming_doc_no',
            textField: 'incoming_doc_no',
            prompt: 'Choose All',
            editable: true,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        $('#filter_workorder_label').combobox({
            valueField: 'workorder_label',
            textField: 'workorder_label',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        reloadDeliveryNoteCombo();
        reloadIncomingDocNoCombo();
        reloadWorkorderLabel();

        $('#filter_from, #filter_to').datebox({
            onChange: function() {
                reloadDeliveryNoteCombo();
                reloadIncomingDocNoCombo();
                reloadWorkorderLabel();
            }
        });
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


    $('#filter_source_name').combogrid({
        url: '<?= base_url('control/grn_subconts/readSourceName'); ?>',
        panelWidth: 440,
        idField: 'id',
        textField: 'name',
        valueField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Source",
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'number', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        editable: false,
        onSelect: function(index, row) {
            reloadDeliveryNoteCombo();
            reloadIncomingDocNoCombo();
            reloadWorkorderLabel();

            toggleButtonExport();
        },
        onChange: function(newValue, oldValue) {
            reloadDeliveryNoteCombo();
            reloadIncomingDocNoCombo();
            reloadWorkorderLabel();

            isFiltered = false;
            // disableExportButtons();
        }
    });

    function toggleButtonExport(totalRows = 0) {
        var sourceName = $('#filter_source_name').combogrid('getValue');

        var enable =
            sourceName &&
            isFiltered &&
            totalRows > 0;

        // if (enable) {
        //     $('#print_recap').linkbutton('enable');
        //     $('#export_recap').linkbutton('enable');
        //     $('#export_detail').linkbutton('enable');
        // } else {
        //     disableExportButtons();
        // }
    }

    // function disableExportButtons() {
    //     $('#print_recap').linkbutton('disable');
    //     $('#export_recap').linkbutton('disable');
    //     $('#export_detail').linkbutton('disable');
    // }

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else if(value == 2) {
            return 'background: #F3A26D; color: white';
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
    //FORMATTER DELIVERY STATUS 
    function cellFormatterDeliveryStatus(value) {
        if (value == 0) {
            return 'ON SCHEDULE';
        } else if(value == 1) {
            return 'DELAY';
        }else {
            return 'EARLY';
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
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        let print = "print_grn_subcont('" + row.incoming_doc_no + "')";
        if(row.printed==0){
            return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }else{
            return '<a class="btn btn-secondary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';

        }
    }

    function print_grn_subcont(incoming_doc_no) {
        console.log(incoming_doc_no);
        window.open("<?= base_url('control/grn_subconts/print_grn_subcont/') ?>" + window.btoa(incoming_doc_no), "_blank", "width=1200,height=600");
    }

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }

</script>