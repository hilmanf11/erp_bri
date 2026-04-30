<style>
  .dialog-button{
    border-bottom: 0 !important;
  }

    .btn-clicked {
        background-color: #e0e0e0 !important;
        transform: scale(0.97);
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
</style>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'printed',width:100,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'dnr_no',width:220,halign:'center',sortable:true">DNR No</th>
            <th rowspan="2" data-options="field:'delivery_date',width:220,halign:'center',sortable:true">Delivery Date</th>
            <th rowspan="2" data-options="field:'destination_name',width:220,halign:'center',sortable:true">Destination</th>
            <th rowspan="2" data-options="field:'total_qty_delivery',width:130,halign:'center',sortable:true, formatter:numberFormat, align:'center'">Total Qty Delivery</th>
            
            <th rowspan="2" data-options="field:'status_header',width:130,halign:'center',align:'center',formatter:formatStatus,styler:styleStatus">Status</th>

            <th rowspan="2" data-options="field:'approved_to',width:130,halign:'center',align:'center',formatter:formatApproved,styler:styleApproved">Status <br>Approve</th>

            <th rowspan="2" data-options="field:'approved_by',width:130,halign:'center',align:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:130,halign:'center'">Approve Date</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:160,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:200,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:160,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:200,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 198px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Category</span>
                    <input style="width:60%;" id="filter_delivery_category" data-options="editable: false" class="easyui-textbox">
                </div>
                <div class="fitem" id="destination_wrapper">
                    <span style="width:35%; display:inline-block;">Destination</span>
                    <input style="width:60%;" name="filter_destination" id="filter_destination" class="easyui-combogrid" data-options="editable: false">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Destination Code</span>
                    <input style="width:60%;" name="filter_destination_code" id="filter_destination_code" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:29.8%;" id="filter_from" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:29.8%;" id="filter_to" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">DNR No</span>
                    <input style="width:60%;" id="filter_dnr_no" class="easyui-combobox">
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/delivery_rework/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log('ROWS : ', rows);
        
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('control/delivery_rework/deleteAll') ?>',
                            data: {
                                dnr_no: row.dnr_no,
                                scan_id: row.scan_id,
                                // item_fg_id: row.item_fg_id,
                                // workorder: row.workorder,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');

                                if(result.theme == "success") {
                                    toastr.success(result.message);
                                } else {
                                    toastr.error(result.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                // toastr.error(jqXHR.statusText);

                                if (jqXHR.responseText && jqXHR.responseText.includes("Error Number: 1451")) {
                                    toastr.error("Cannot delete data that is still in use");
                                } else {
                                    toastr.error("Delete failed: " + jqXHR.statusText);
                                }
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
        var filter_dnr_no = $("#filter_dnr_no").combobox('getValue');
        // var filter_destination = $("#filter_destination").combogrid('getValue');
        var filter_destination_code = $("#filter_destination_code").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_destination=" + window.btoa(filter_destination_code) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_dnr_no=" + window.btoa(filter_dnr_no);

        $('#dg').datagrid({
            url: '<?= base_url('control/delivery_rework/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.dnr_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('control/delivery_rework/datatableDetails?dnr_no=') ?>' + window.btoa(row.dnr_no),
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
                        }, {
                            field: 'workorder',
                            title: 'WO No',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'prod_date',
                            title: 'Production Date',
                            align: 'center',
                            width: 200
                        }, {
                            field: 'qty_delivery',
                            title: 'Qty Delivery',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormat
                        },
                        {
                            field: 'qty_incoming',
                            title: 'Qty Incoming',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormat
                        }, 
                        {
                            field: 'uom',
                            title: 'UOM',
                            align: 'center',
                            width: 100
                        }, 
                        {
                            field: 'status_incoming',
                            title: 'Status Incoming',
                            halign: 'center',
                            align: 'center',
                            width: 150,
                            formatter: formatStatusIncoming,
                            styler: styleStatusIncoming,
                        }
                        ]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function(data) {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);

                        console.log('Data :', data);
                        
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/delivery_rework/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_dnr_no = $("#filter_dnr_no").combobox('getValue');
        // var filter_destination = $("#filter_destination").combogrid('getValue');
        var filter_destination_code = $("#filter_destination_code").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_destination=" + window.btoa(filter_destination_code) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_dnr_no=" + window.btoa(filter_dnr_no);

        window.location.assign('<?= base_url('control/delivery_rework/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
        $('#filter_delivery_category').textbox('setValue', 'Rework');

        function reloadDeliveryNoteCombo() {
            var filter_froms = $("#filter_from").datebox("getValue");
            var filter_tos   = $("#filter_to").datebox("getValue");
            var filter_destination_code  = $("#filter_destination_code").combogrid("getValue");

            var url = '<?= base_url('control/delivery_rework/read_dnr_no'); ?>'
                    + '?filter_from=' + encodeURIComponent(filter_froms)
                    + '&filter_to=' + encodeURIComponent(filter_tos)
                    + '&filter_destination=' + encodeURIComponent(filter_destination_code);

            $('#filter_dnr_no').combobox('reload', url);
        }

        $('#filter_dnr_no').combobox({
            valueField: 'dnr_no',
            textField: 'dnr_no',
            prompt: 'Choose All',
            editable: true,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        reloadDeliveryNoteCombo();

        $('#filter_from, #filter_to').datebox({
            onChange: function() {
                reloadDeliveryNoteCombo();
            }
        });

        $('#filter_destination_code').combogrid({
            onChange: function(newValue, oldValue) {
                reloadDeliveryNoteCombo();
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

    $('#filter_destination').combogrid({
        url: '<?= base_url('control/scan_out_rework/readScanOutRework'); ?>',
        panelWidth: 440,
        idField: 'number',
        textField: 'name',
        valueField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: 'Choose All',
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'number', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        onSelect: function(index, row) {
            $('#filter_destination_code').combogrid('setValue', row.number);
        }
    });

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
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function btnPrint(val, row) {
        var print = "print_dn_rework('" + row.dnr_no + "')"; 
        if(row.printed==0){
            return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }else{
            return '<a class="btn btn-secondary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';

        }
    }

    function print_dn_rework(dnr_no) {
        console.log(dnr_no);
        window.open("<?= base_url('control/delivery_rework/print_dn_rework/') ?>" + window.btoa(dnr_no), "_blank", "width=1200,height=600");
    }

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null) {
            return '<b>Approved</b>';
        } else {
            return '<b>Checking</b>';
        }
    }

    function styleStatus(value, row, index) {
        if(value == '0') {
            return 'background: #53D636; color: white;';
        } else if(value == '1') {
            return 'background: #FF5F5F; color: white;';
        } else if(value == '2') {
            return 'background: #F3A26D; color: white;';
        } else if(value == '3') {
            return 'background: #B2A5FF; color: white;';
        }
    }

    function formatStatus(value) {
        if(value == '0') {
            return '<b>OPEN</b>';
        } else if(value == '1') {
            return '<b>CLOSED</b>';
        } else if(value == '2') {
            return '<b>ON GOING</b>';
        } else if(value == '3') {
            return '<b>OVER</b>';
        }
    }

    function styleStatusIncoming(value, row, index) {
        if(value == '0') {
            return 'background: #53D636; color: white;';
        } else if(value == '1') {
            return 'background: #FF5F5F; color: white;';
        } else if(value == '2') {
            return 'background: #F3A26D; color: white;';
        } else if(value == '3') {
            return 'background: #B2A5FF; color: white;';
        }
    }

    function formatStatusIncoming(value) {
        if(value == '0') {
            return '<b>OPEN</b>';
        } else if(value == '1') {
            return '<b>CLOSED</b>';
        } else if(value == '2') {
            return '<b>ON GOING</b>';
        } else if(value == '3') {
            return '<b>OVER</b>';
        }
    }

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }

</script>