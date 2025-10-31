<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'incoming_doc_no',width:180,halign:'center',sortable:true">Incoming Doc No</th>
            <th rowspan="2" data-options="field:'incoming_date',width:180,halign:'center',sortable:true">Incoming Date</th>
            <th rowspan="2" data-options="field:'delivery_note_no',width:180,halign:'center',sortable:true">Delivery Note No</th>
            <th rowspan="2" data-options="field:'delivery_date',width:180,halign:'center',sortable:true">Delivery Date</th>
            <th rowspan="2" data-options="field:'incoming_from',width:180,halign:'center',sortable:true">Incoming From</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:150,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:160,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:150,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:160,align:'center',sortable:true"> Date</th>
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
                    <input style="width:29.8%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                    <input style="width:29.8%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Doc No</span>
                    <input style="width:60%;" id="filter_incoming_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery From</span>
                    <select style="width:60%;" id="filter_delivery_from" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                        <option value="" selected>All</option>
                        <option value="SUBCONT">Subcont</option>
                        <option value="TEFA">Teaching Factory</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont</span>
                    <input style="width:60%;" id="filter_subcont_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Teaching Factory</span>
                    <input style="width:60%;" id="filter_teaching_factory_id" class="easyui-combogrid">
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

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add Incoming From SC/TF" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 50px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Date</span>
                    <input style="width:60%;" name="incoming_date" id="incoming_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Doc No</span>
                    <input style="width:60%;" name="incoming_doc_no" id="incoming_doc_no" readonly class="easyui-textbox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note No</span>
                    <input style="width:60%;" name="delivery_note_no" id="delivery_note_no" class="easyui-combogrid" required>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem" id="destination_wrapper">
                    <span style="width:35%; display:inline-block;">Incoming From</span>
                    <input style="width:60%;" name="delivery_from" id="delivery_from" required class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:60%;" name="delivery_date" id="delivery_date" required class="easyui-textbox" readonly>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Destination Code</span>
                    <input style="width:60%;" name="destination_code" id="destination_code" required class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product No List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/incoming_from_sc_tf/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');   

        $("#incoming_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                setTimeout(regenerateDeliveryNoteNo, 49);
            }
        });
         
        setTimeout(function(){
            $("#delivery_from").textbox('enable');
            $("#delivery_date").textbox('enable');

            $("#incoming_date").datebox('enable');
            $("#incoming_doc_no").textbox('enable');
            $("#delivery_note_no").combogrid('enable');
            $("#incoming_doc_no").textbox('clear');
            $('#incoming_date').datebox('setValue', '<?= date("Y-m-d") ?>');
            // $('#delivery_note_no').combogrid('reload');


            var url = '<?= base_url('control/incoming_from_sc_tf/readDeliveryNoteNoSCTF/'); ?>?t=' + new Date().getTime();
            $('#delivery_note_no').combogrid('grid').datagrid('reload', url);

        }, 50);

        url_save = '<?= base_url('control/incoming_from_sc_tf/create') ?>';
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            fitColumns: true,
            singleSelect: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    },
                    hidden: true
                }, {
                    field: 'item_fg_number',
                    width: 150,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/incoming_from_sc_tf/readItemFg/'); ?>',
                            method: 'post',
                            required: true,
                            panelWidth: 750,
                            idField: 'number',
                            textField: 'number',
                            valueField: 'item_fg_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    halign: 'center',
                                    width: 200
                                },{
                                    field: 'name',
                                    title: 'Product Name',
                                    halign: 'center',
                                    width: 200
                                },{
                                    field: 'workorder',
                                    title: 'Workorder',
                                    halign: 'center',
                                    width: 150
                                },{
                                    field: 'qty_delivery',
                                    title: 'Qty Delivery',
                                    halign: 'center',
                                    align: 'center',
                                    formatter: numberFormatField,
                                    width: 150,
                                    editor: {
                                        type: 'numberbox',
                                        options: {
                                            precision: 0,
                                            required: true,
                                        }
                                    }
                                }]
                            ],
                            onBeforeLoad: function(param) {
                                param.delivery_note_no = $('#delivery_note_no').combogrid('getValue');
                                console.log('DEL : ', param.delivery_note_no);
                            },
                            onLoadSuccess: function(data) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;
                                var idx = dg.datagrid('getRowIndex', row);

                                var edId   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
                                var edNo   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                var edName = dg.datagrid('getEditor', { index: idx, field: 'item_fg_name' });
                                // var edWP   = dg.datagrid('getEditor', { index: idx, field: 'wp' });
                                // var edTransDate   = dg.datagrid('getEditor', { index: idx, field: 'trans_date' });
                                var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
                                var edQtyDel   = dg.datagrid('getEditor', { index: idx, field: 'qty_delivery' });
                                var edUom   = dg.datagrid('getEditor', { index: idx, field: 'uom' });

                                if (data.rows && data.rows.length === 1) {
                                    var item = data.rows[0];
                                    $(edNo.target).combogrid('grid').datagrid('selectRecord', item.item_fg_id);
                                }

                                if (row.item_fg_id) {
                                    if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
                                    if (edName) $(edName.target).textbox('setValue', row.item_fg_name);
                                    // if (edWP)   $(edWP.target).textbox('setValue', row.wp);
                                    // if (edTransDate)   $(edTransDate.target).textbox('setValue', row.trans_date);
                                    if (edWO)   $(edWO.target).textbox('setValue', row.workorder);
                                    if (edQtyDel)   $(edQtyDel.target).textbox('setValue', row.qty_delivery);
                                    if (edUom)   $(edUom.target).textbox('setValue', row.uom);
                                }
                            },

                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);
                                var ed1 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_number'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'workorder'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_delivery'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.workorder);
                                $(ed5.target).textbox('setValue', rows.qty_delivery);
                                $(ed6.target).textbox('setValue', rows.uom);
                            },
                        }
                    }
                }, {
                    field: 'item_fg_id',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_name',
                    width: 200,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'workorder',
                    width: 200,
                    halign: 'center',
                    title: "WO No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_delivery',
                    width: 100,
                    align: 'center',
                    title: "Qty Delivery",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_receive',
                    width: 100,
                    align: 'center',
                    title: "Qty Receive",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            onChange: function(newValue, oldValue) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;

                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var edQtyDel = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_delivery' });
                                var qtyDel = 0;
                                if (edQtyDel) {
                                    qtyDel = parseFloat($(edQtyDel.target).numberbox('getValue')) || 0;
                                } else {
                                    qtyDel = parseFloat(row.qty_delivery) || 0;
                                }

                                var qtyRec = parseFloat(newValue) || 0;
                                if (qtyRec > qtyDel) {
                                    toastr.warning('Qty Receive must not exceed Qty Delivery!');

                                    var edQtyRec = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_receive' });
                                    if (edQtyRec) {
                                        $(edQtyRec.target).numberbox('setValue', '');
                                    }

                                    // row.qty_delivery = 0;
                                }
                            }
                        }
                    }
                }, {
                    field: 'uom',
                    width: 100,
                    align: 'center',
                    title: "UOM",
                    editor: {
                        type: 'textbox',
                        readonly: true,
                    }
                }]
            ],
            onClickCell: onClickCell
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

    function append() {
        var delivery_note_no = $("#delivery_note_no").combogrid('getValue');
        var delivery_from = $("#delivery_from").textbox('getValue');
        var delivery_date = $("#delivery_date").textbox('getValue');

        if (delivery_note_no != "" && delivery_from != "" && delivery_date != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please fill in all required fields first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'item_fg_id'
        });

        var item_fg_id = $(ed.target).textbox('getValue');
        var incoming_doc_no = $("#incoming_doc_no").textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/incoming_from_sc_tf/delete') ?>',
            data: {
                // subcont_id: row.subcont_id,
                incoming_doc_no: incoming_doc_no,
                item_fg_id: item_fg_id
            },
            success: function(result) {
                var result = eval('(' + result + ')');
                toastr.success(result.message);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                toastr.error(jqXHR.statusText);
                $.messager.alert("Error", jqXHR.statusText, 'error');
            },
            complete: function(data) {
                $('#dg').datagrid('reload');
            }
        });

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        // console.log(row);
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            setTimeout(function() {
                $('#delivery_from').textbox('setValue', row.delivery_from);
                $('#delivery_from').textbox('setText', row.delivery_from_text);

                $('#incoming_doc_no').textbox('setValue', row.incoming_doc_no);
                $('#destination_code').textbox('setValue', row.destination_code);
            }, 200);

            $("#delivery_from").textbox('disable');
            $("#delivery_date").textbox('disable');

            $("#incoming_date").datebox('disable');
            $("#incoming_doc_no").textbox('disable');
            $("#delivery_note_no").combogrid('disable');

            addTable('<?= base_url('control/incoming_from_sc_tf/datatableUpdates?incoming_doc_no=') ?>' + window.btoa(row.incoming_doc_no));
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
                            url: '<?= base_url('control/incoming_from_sc_tf/delete') ?>',
                            data: {
                                incoming_doc_no: row.incoming_doc_no
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
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_delivery_from = $('#filter_delivery_from').combobox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_teaching_factory_id = $("#filter_teaching_factory_id").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_delivery_from=" + window.btoa(filter_delivery_from) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_teaching_factory_id=" + window.btoa(filter_teaching_factory_id);

        $('#dg').datagrid({
            url: '<?= base_url('control/incoming_from_sc_tf/datatables') ?>' + url,
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
                var filter_item_fg = $('#filter_item_fg').combogrid('getValue');
                var filter_delivery_from = $('#filter_delivery_from').combobox('getValue');

                // var filterProductFamily = $('#filter_product_family').combogrid('getValue');
                // var encodedProductFamily = filterProductFamily ? "&product_family=" + window.btoa(filterProductFamily) : "";


                ddv.datagrid({
                    url: '<?= base_url('control/incoming_from_sc_tf/datatableDetails?incoming_doc_no=') ?>' + window.btoa(row.incoming_doc_no) + '&item_fg=' + window.btoa(filter_item_fg) + '&delivery_from=' + window.btoa(filter_delivery_from),
                    singleSelect: true,
                    fitColumns: true,
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
                            field: 'qty_delivery',
                            title: 'Qty Delivery',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormat
                        }, {
                            field: 'qty_receive',
                            title: 'Qty Receive',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormat
                        }, {
                            field: 'uom',
                            title: 'UOM',
                            align: 'center',
                            width: 100
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

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/incoming_from_sc_tf/print') ?>' + url);
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
        var filter_incoming_doc_no = $("#filter_incoming_doc_no").combobox('getValue');
        var filter_delivery_from = $('#filter_delivery_from').combobox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_teaching_factory_id = $("#filter_teaching_factory_id").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_delivery_from=" + window.btoa(filter_delivery_from) +
            "&filter_incoming_doc_no=" + window.btoa(filter_incoming_doc_no) +
            "&filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_teaching_factory_id=" + window.btoa(filter_teaching_factory_id);

        window.location.assign('<?= base_url('control/incoming_from_sc_tf/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
        addTable();

        function reloadDeliveryNoteCombo() {
            var filter_froms = $("#filter_from").datebox("getValue");
            var filter_tos   = $("#filter_to").datebox("getValue");
            var filter_delivery_from  = $("#filter_delivery_from").combobox("getValue");

            var url = '<?= base_url('control/incoming_from_sc_tf/readIncomingDocNo'); ?>'
                    + '?filter_from=' + encodeURIComponent(filter_froms)
                    + '&filter_to=' + encodeURIComponent(filter_tos)
                    + '&filter_delivery_from=' + encodeURIComponent(filter_delivery_from);

            $('#filter_incoming_doc_no').combobox('reload', url);
        }

        $('#filter_incoming_doc_no').combobox({
            valueField: 'incoming_doc_no',
            textField: 'incoming_doc_no',
            prompt: 'Choose All',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        reloadDeliveryNoteCombo();

        $('#filter_from, #filter_to').datebox({
            onSelect: function() {
                reloadDeliveryNoteCombo();
            }
        });

        $('#filter_delivery_from').combobox({
            onChange: function(newValue, oldValue) {
                reloadDeliveryNoteCombo();
            }
        });


        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var incoming_date = $("#incoming_date").datebox('getValue');
                    var incoming_doc_no = $("#incoming_doc_no").textbox('getValue');
                    var delivery_note_no = $("#delivery_note_no").combogrid('getValue');
                    var delivery_from = $("#delivery_from").textbox('getValue');
                    var delivery_date = $("#delivery_date").textbox('getValue');
                    // var destination = $("#destination").combogrid('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('control/incoming_from_sc_tf/create') ?>',
                                data: {
                                    incoming_date: incoming_date,
                                    incoming_doc_no: incoming_doc_no,
                                    delivery_note_no: delivery_note_no,
                                    delivery_from: delivery_from,
                                    delivery_date: delivery_date,

                                    id: rows[i].id,
                                    item_fg_id: rows[i].item_fg_id,
                                    workorder: rows[i].workorder,
                                    qty_delivery: rows[i].qty_delivery,
                                    qty_receive: rows[i].qty_receive,
                                },
                                dataType: "json",
                                success: function(result) {
                                    if (i == (totalrows - 1)) {
                                        Swal.fire({
                                            title: result.message,
                                            icon: result.theme,
                                            confirmButtonText: 'Ok',
                                            allowOutsideClick: false,
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.reload();
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }

                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });


        $("#filter_teaching_factory_id").combobox('disable');

        $("#filter_delivery_from").combobox({
            onChange: function(val) {
                if (val == "SUBCONT") {
                    $("#filter_subcont_id").combobox('enable');

                    $("#filter_teaching_factory_id").combobox('disable');
                } else if (val == "TEFA") {
                    $("#filter_teaching_factory_id").combobox('enable');

                    $("#filter_subcont_id").combobox('disable');
                }
            }
        });

    });

    $('#filter_subcont_id').combogrid({
        url: '<?= base_url('master/subconts/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose subcont",
        columns: [
            [{
                field: 'id',
                title: 'subcont ID',
                width: 150
            }, {
                field: 'number',
                title: 'subcont Code',
                width: 150
            }, {
                field: 'name',
                title: 'subcont Name',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_teaching_factory_id').combogrid({
        url: '<?= base_url('master/teaching_factory/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Teaching Factory",
        columns: [
            [{
                field: 'id',
                title: 'Tefa Factory',
                width: 150
            }, {
                field: 'number',
                title: 'Tefa Code',
                width: 150
            }, {
                field: 'name',
                title: 'Tefa Name',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/readRubberParts") ?>',
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

    // $('#delivery_to_insert').combobox({
    //     onChange: function (newValue, oldValue) {
    //         $("#destination").combogrid('enable');

    //         if (newValue === 'SUBCONT') {
    //             initSubcontGrid();
    //         } else if (newValue === 'TEFA') {
    //             initTefaGrid();
    //         } else {
    //             $('#destination').combogrid('clear');
    //             $('#destination').combogrid('grid').datagrid('loadData', []); // clear data
    //         }
    //     }
    // });

    $('#delivery_note_no').combogrid({
        url: '<?= base_url('control/incoming_from_sc_tf/readDeliveryNoteNoSCTF/'); ?>',
        panelWidth: 500,
        idField: 'delivery_note_no',
        textField: 'delivery_note_no',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Delivery Note No",
        columns: [[
            {field: 'delivery_from', title: 'Delivery From', width: 150},
            {field: 'delivery_note_no', title: 'Delivery Note No', width: 200},
            {field: 'delivery_date', title: 'Delivery Date', width: 150},
        ]],
        onSelect: function(index, row) {
            $('#delivery_from').textbox('setValue', row.destination);
            $('#delivery_from').textbox('setText', row.incoming_from);
            $('#destination_code').textbox('setValue', row.destination_code);
            $('#delivery_date').textbox('setValue', row.delivery_date);
            regenerateDeliveryNoteNo();
        }
    });

    // function initSubcontGrid() {
    //     $('#destination').combogrid({
    //         url: '<?= base_url('master/subconts/reads/'); ?>',
    //         panelWidth: 420,
    //         idField: 'id',
    //         textField: 'name',
    //         mode: 'remote',
    //         fitColumns: true,
    //         prompt: "Choose Subcont",
    //         columns: [[
    //             {field: 'number', title: 'Subcont Code', width: 120},
    //             {field: 'name', title: 'Subcont Name', width: 250}
    //         ]],
    //         onSelect: function(index, row) {
    //             $('#destination_code').combogrid('setValue', row.number); // << kode subcont
    //             regenerateDeliveryNoteNo();
    //         }
    //     });
    // }

    // function initTefaGrid() {
    //     $('#destination').combogrid({
    //         url: '<?= base_url('master/teaching_factory/reads/'); ?>',
    //         panelWidth: 420,
    //         idField: 'id',
    //         textField: 'name',
    //         mode: 'remote',
    //         fitColumns: true,
    //         prompt: "Choose Teaching Factory",
    //         columns: [[
    //             {field: 'number', title: 'TF Code', width: 120},
    //             {field: 'name', title: 'TF Name', width: 250}
    //         ]],
    //         onSelect: function(index, row) {
    //             $('#destination_code').combogrid('setValue', row.number); // << kode TF
    //             regenerateDeliveryNoteNo();
    //         }
    //     });
    // }

    function regenerateDeliveryNoteNo() {
        let incoming_date = $('#incoming_date').datebox('getValue');
        let dest_code = $('#destination_code').textbox('getValue');

        if (incoming_date && dest_code) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/incoming_from_sc_tf/incoming_doc_no') ?>",
                data: { incoming_date: incoming_date, destination_code: dest_code },
                dataType: "html",
                success: function(result) {
                    $("#incoming_doc_no").textbox('setValue', result);
                }
            });
        }
    }

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

    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };

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

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }
</script>