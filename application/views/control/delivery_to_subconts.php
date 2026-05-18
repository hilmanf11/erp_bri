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
            <th rowspan="2" data-options="field:'delivery_note_no',width:220,halign:'center',sortable:true">Delivery Note No</th>
            <th rowspan="2" data-options="field:'delivery_date',width:180,halign:'center',sortable:true">Delivery Date</th>
            <th rowspan="2" data-options="field:'target_date',width:180,halign:'center',sortable:true">Target Date</th>
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
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery To</span>
                    <select style="width:60%;" id="filter_delivery_to" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                        <option value="" selected>All</option>
                        <option value="SUBCONT">Subcont</option>
                        <option value="TEFA">Teaching Factory</option>
                    </select>
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
                    <span style="width:35%; display:inline-block;">Delivery Note No</span>
                    <input style="width:60%;" id="filter_delivery_note_no" class="easyui-combobox">
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

<!-- <div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div> -->

<div id="toolbar2" style="padding: 2px; margin-top: -38px; background-color: #f5f5f5 !important">
    <a href="javascript:void(0)" id="btn-add" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" id="btn-remove" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>

    <span id="total_qty_delivery">0</span>
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add Delivery Note" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 50px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:60%;" name="delivery_date" id="delivery_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Category</span>
                    <select style="width:60%;" id="delivery_category" panelHeight="auto" class="easyui-combobox" data-options="editable:false" required>
                        <option value="Regular">Regular</option>
                        <option value="Rework">Rework</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note No.</span>
                    <input style="width:60%;" name="delivery_note_no" id="delivery_note_no" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery To</span>
                    <select style="width:60%;" id="delivery_to_insert" panelHeight="auto" class="easyui-combobox" data-options="editable:false" required>
                        <option value="SUBCONT">Subcont</option>
                        <option value="TEFA">Teaching Factory</option>
                    </select>
                </div>
                <div class="fitem" id="destination_wrapper">
                    <span style="width:35%; display:inline-block;">Destination</span>
                    <input style="width:60%;" name="destination" id="destination" required="" class="easyui-combogrid">
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Destination Code</span>
                    <input style="width:60%;" name="destination_code" id="destination_code" required="" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product No List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/delivery_to_subconts/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    $(document).ready(function () {
        $('#dlg_insert').dialog({
            onOpen: function () {
                setTimeout(() => {
                    const panel = $('#dlg_insert').closest('.panel.window.panel-htop');
                    const toolbar = $('#toolbar2');

                    if (!toolbar.parent().hasClass('panel')) {
                        panel.append(toolbar);
                    }

                    function positionToolbar() {
                        const panelHeight = panel.height();
                        const toolbarHeight = toolbar.outerHeight();
                        toolbar.css({
                            top: (panelHeight - toolbarHeight - 10) + 'px'
                        });
                    }

                    positionToolbar();
                    $(window).on('resize', positionToolbar);
                }, 100);
            }
        });
    });

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');   

        $("#delivery_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                setTimeout(regenerateDeliveryNoteNo, 49);
            }
        });

        setTimeout(function(){
            $("#delivery_date").datebox('enable');
            $("#delivery_note_no").textbox('enable');
            $("#delivery_category").combobox('enable');
            $("#delivery_to_insert").combobox('enable');
            $("#destination").combogrid('enable');
            $("#delivery_note_no").textbox('clear');
            $('#delivery_date').datebox('setValue', '<?= date("Y-m-d") ?>');
        }, 50);

        url_save = '<?= base_url('control/delivery_to_subconts/create') ?>';
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
                            url: '<?= base_url('control/delivery_to_subconts/readItemFgLast/'); ?>',
                            method: 'post',
                            required: true,
                            panelWidth: 950,
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
                                    field: 'wp',
                                    halign: 'center',
                                    halign: 'center',
                                    align: 'center',
                                    title: 'WP No',
                                    width: 150
                                },{
                                    field: 'trans_date',
                                    title: 'Prod Date',
                                    halign: 'center',
                                    align: 'center',
                                    width: 150
                                },{
                                    field: 'workorder',
                                    title: 'Workorder',
                                    halign: 'center',
                                    width: 150
                                },{
                                    field: 'qty_output',
                                    title: 'Qty OK',
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
                                },{
                                    field: 'source_type',
                                    title: 'Source Type',
                                    halign: 'center',
                                    align: 'center',
                                    width: 200,
                                }]
                            ],
                            onBeforeLoad: function(param) {
                                var dg = $('#dg2');
                                var rows = dg.datagrid('getRows');
                                param.delivery_date = $('#delivery_date').datebox('getValue');
                                param.destination   = $('#destination').combogrid('getValue');

                                var used = rows
                                    .filter(r => r.item_fg_id && r.workorder)
                                    .map(r => r.item_fg_id + '_' + r.workorder);

                                param.exclude_keys = used.join(',');
                            },
                            onLoadSuccess: function(data) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;
                                var idx = dg.datagrid('getRowIndex', row);

                                console.log(data);

                                var edId   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
                                var edNo   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                var edName = dg.datagrid('getEditor', { index: idx, field: 'item_fg_name' });
                                var edWP   = dg.datagrid('getEditor', { index: idx, field: 'wp' });
                                var edTransDate   = dg.datagrid('getEditor', { index: idx, field: 'trans_date' });
                                var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
                                var edQtyOK   = dg.datagrid('getEditor', { index: idx, field: 'qty_output' });
                                var edUom   = dg.datagrid('getEditor', { index: idx, field: 'uom' });
                                var edSourceType   = dg.datagrid('getEditor', { index: idx, field: 'source_type' });

                                if (data.rows && data.rows.length === 1) {
                                    var item = data.rows[0];
                                    $(edNo.target).combogrid('grid').datagrid('selectRecord', item.item_fg_id);
                                }

                                if (row.item_fg_id) {
                                    if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
                                    if (edName) $(edName.target).textbox('setValue', row.item_fg_name);
                                    if (edWP)   $(edWP.target).textbox('setValue', row.wp);
                                    if (edTransDate)   $(edWO.target).textbox('setValue', row.trans_date);
                                    if (edWO)   $(edWO.target).textbox('setValue', row.workorder);
                                    if (edQtyOK)   $(edQtyOK.target).textbox('setValue', row.qty_output);
                                    if (edUom)   $(edUom.target).textbox('setValue', row.uom);
                                    if (edSourceType)   $(edSourceType.target).textbox('setValue', row.source_type);
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
                                    field: 'qty_output'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed7 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'trans_date'
                                });
                                var ed8 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'source_type'
                                });
                                var ed9 = dg.datagrid('getEditor', { 
                                    index: rowIndex, 
                                    field: 'internal_doc_no' 
                                });

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.workorder);
                                $(ed5.target).textbox('setValue', rows.qty_output);
                                $(ed6.target).textbox('setValue', rows.uom);
                                $(ed7.target).textbox('setValue', rows.trans_date);
                                $(ed8.target).textbox('setValue', rows.source_type);

                                if (rows.source_type === 'Internal Process') {
                                    if (ed9) $(ed9.target).textbox('setValue', rows.internal_doc_no || '');
                                } else {
                                    if (ed9) $(ed9.target).textbox('setValue', null);
                                }
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
                    field: 'internal_doc_no',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "Internal Document No",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'source_type',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "Source Type",
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
                    field: 'trans_date',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "Prod Date",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'qty_output',
                    width: 100,
                    align: 'center',
                    title: "Qty Output Press",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            readonly: true
                        }
                    }
                },


                //  {
                //     field: 'qty_total',
                //     width: 100,
                //     align: 'center',
                //     title: "Total Qty",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //             required: true,
                //         }
                //     }
                // },


                {
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
                            onChange: function(newValue, oldValue) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;

                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var edQtyOut = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_output' });
                                var qtyOut = 0;
                                if (edQtyOut) {
                                    qtyOut = parseFloat($(edQtyOut.target).numberbox('getValue')) || 0;
                                } else {
                                    qtyOut = parseFloat(row.qty_output) || 0;
                                }

                                var qtyDel = parseFloat(newValue) || 0;
                                if (qtyDel > qtyOut) {
                                    toastr.warning('Qty Delivery must not exceed Qty Output!');

                                    var edQtyDel = dg.datagrid('getEditor', { index: rowIndex, field: 'qty_delivery' });
                                    if (edQtyDel) {
                                        $(edQtyDel.target).numberbox('setValue', '');
                                    }

                                    // row.qty_delivery = 0;
                                    updateTotalQtyDelivery();
                                }

                                row.qty_delivery = qtyDel;
                                updateTotalQtyDelivery();
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
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'remarks',
                    width: 100,
                    align: 'center',
                    title: "Remarks",
                    editor: {
                        type: 'textbox'
                    }
                }]
            ],
            onClickCell: onClickCell,
            onAfterEdit: function(index, row) {
                updateTotalQtyDelivery();
            },
            onLoadSuccess: function() {
                updateTotalQtyDelivery();
            },

        });
    }

    function updateTotalQtyDelivery() {
        var dg = $('#dg2');
        var rows = dg.datagrid('getRows') || [];
        var total = 0;

        for (var i = 0; i < rows.length; i++) {
            // kalau ada editor untuk baris ini, ambil nilainya dari editor (real-time)
            var ed = dg.datagrid('getEditor', { index: i, field: 'qty_delivery' });
            var val = 0;
            if (ed && ed.target) {
                // numberbox editor -> pakai numberbox('getValue')
                try {
                    val = parseFloat($(ed.target).numberbox('getValue')) || 0;
                } catch (e) {
                    val = parseFloat(rows[i].qty_delivery) || 0;
                }
            } else {
                val = parseFloat(rows[i].qty_delivery) || 0;
            }

            total += val;
        }

        // set di dua tempat (toolbar & dialog text)
        $("#total_insert_qty").text(total);
        $("#total_qty_delivery").text(total);
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

    function buttonClickEffect(btn) {
        $(btn).addClass('btn-clicked');
        setTimeout(() => {
            $(btn).removeClass('btn-clicked');
        }, 300);
    }

    function append() {
        buttonClickEffect('#btn-add');
        var delivery_category = $("#delivery_category").combobox('getValue');
        var delivery_to_insert = $("#delivery_to_insert").combobox('getValue');
        var destination = $("#destination").combogrid('getValue');

        if (delivery_category != "" && delivery_to_insert != "" && destination != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
                updateTotalQtyDelivery();
            }
        } else {
            toastr.error("Please fill in all required fields first");
        }
    }

    function removeit() {
        buttonClickEffect('#btn-remove');
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

        var ed1 = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'workorder'
        });

        // var subcont_id = $("#subcont_id").combogrid('getValue');
        var item_fg_id = $(ed.target).textbox('getValue');
        var workorder = $(ed1.target).textbox('getValue');
        var delivery_note_no = $("#delivery_note_no").textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/delivery_to_subconts/delete') ?>',
            data: {
                delivery_note_no: delivery_note_no,
                workorder: workorder,
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
        updateTotalQtyDelivery();
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            setTimeout(function() {
                $('#delivery_to_insert').combobox('setValue', row.delivery_to);
                $('#delivery_category').combobox('setValue', row.delivery_category);

                $('#delivery_note_no').textbox('setValue', row.delivery_note_no);
                $('#destination').combogrid('setValue', row.destination_name);
                $('#destination_code').combogrid('setValue', row.destination_code);
            }, 200);

            $("#delivery_to_insert").combobox('disable');
            $("#delivery_category").combobox('disable');
            $("#delivery_date").datebox('disable');
            $("#delivery_note_no").textbox('disable');
            $("#destination").combogrid('disable');

            addTable('<?= base_url('control/delivery_to_subconts/datatableUpdates?delivery_note_no=') ?>' + window.btoa(row.delivery_note_no));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }


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
                            url: '<?= base_url('control/delivery_to_subconts/deleteAll') ?>',
                            data: {
                                delivery_note_no: row.delivery_note_no,
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

    // function deleted() {
    //     var rows = $('#dg').datagrid('getSelections');

    //     if (rows.length === 0) {
    //         toastr.warning("Please select data first!");
    //         return;
    //     }

    //     let items = [];

    //     rows.forEach(row => {
    //         items.push({
    //             delivery_note_no: row.delivery_note_no,
    //             scan_id: row.scan_id,
    //             item_fg_id: row.item_fg_id,
    //             workorder: row.workorder
    //         });
    //     });

    //     if (items.length === 0) {
    //         toastr.error("No data to delete");
    //         return;
    //     }

    //     $.messager.confirm('Warning', 'Are you sure you want to delete selected data?', function (r) {
    //         if (!r) return;

    //         $.ajax({
    //             type: 'POST',
    //             url: '<?= base_url('control/delivery_to_subconts/deleteAll') ?>',
    //             data: { items: items },
    //             dataType: 'json',
    //             success: function (res) {
    //                 toastr.clear();

    //                 if(result.theme == "success") {
    //                     toastr.success(result.message);
    //                 } else {
    //                     toastr.error(result.message);
    //                 }
    //             },
    //             error: function (jqXHR) {
    //                 if (jqXHR.responseText && jqXHR.responseText.includes("Error Number: 1451")) {
    //                     toastr.error("Cannot delete data that is still in use");
    //                 } else {
    //                     toastr.error("Server error while deleting");
    //                 }
    //             },
    //             complete: function(data) {
    //                 $('#dg').datagrid('reload');
    //             }
    //         });
    //     });
    // }

    //FILTER DATA
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_delivery_to = $("#filter_delivery_to").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_delivery_to=" + window.btoa(filter_delivery_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no);

        $('#dg').datagrid({
            url: '<?= base_url('control/delivery_to_subconts/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.delivery_note_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                // var filterProductFamily = $('#filter_product_family').combogrid('getValue');
                // var encodedProductFamily = filterProductFamily ? "&product_family=" + window.btoa(filterProductFamily) : "";

                ddv.datagrid({
                    url: '<?= base_url('control/delivery_to_subconts/datatableDetails?delivery_note_no=') ?>' + window.btoa(row.delivery_note_no),
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

                        // {
                        //     field: 'remarks',
                        //     title: 'Remarks',
                        //     align: 'center',
                        //     width: 150
                        // },

                        {
                            field: 'qty_incoming',
                            title: 'Qty Incoming',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberFormat
                        }, 
                        {
                            field: 'qty_outstanding',
                            title: 'Qty Outstanding',
                            halign: 'center',
                            align: 'right',
                            width: 120,
                            formatter: numberFormat
                        },
                        {
                            field: 'uom',
                            title: 'UOM',
                            align: 'center',
                            width: 100
                        }, {
                            field: 'status_incoming',
                            title: 'Status Incoming',
                            halign: 'center',
                            align: 'center',
                            width: 150,
                            formatter: formatStatusIncoming,
                            styler: styleStatusIncoming,
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
                        
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/delivery_to_subconts/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_delivery_to = $("#filter_delivery_to").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_delivery_note_no = $("#filter_delivery_note_no").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_delivery_to=" + window.btoa(filter_delivery_to) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_delivery_note_no=" + window.btoa(filter_delivery_note_no);

        window.location.assign('<?= base_url('control/delivery_to_subconts/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
        addTable();
        $('#filter_delivery_category').textbox('setValue', 'Regular');

        function reloadDeliveryNoteCombo() {
            var filter_froms = $("#filter_from").datebox("getValue");
            var filter_tos   = $("#filter_to").datebox("getValue");
            var delivery_to  = $("#filter_delivery_to").combobox("getValue");

            var url = '<?= base_url('control/delivery_to_subconts/readDelivery_note_no'); ?>'
                    + '?filter_from=' + encodeURIComponent(filter_froms)
                    + '&filter_to=' + encodeURIComponent(filter_tos)
                    + '&delivery_to=' + encodeURIComponent(delivery_to);

            $('#filter_delivery_note_no').combobox('reload', url);
        }

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

        reloadDeliveryNoteCombo();

        $('#filter_from, #filter_to').datebox({
            onChange: function() {
                reloadDeliveryNoteCombo();
            }
        });

        $('#filter_delivery_to').combobox({
            onChange: function(newValue, oldValue) {
                reloadDeliveryNoteCombo();
            }
        });


        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [
                // {
                //     text: '<span id="total_qty_delivery" style="font-size: 14px !important; color:#000; font-weight:normal;">Total Qty Delivery : <b style="font-size: 14px !important; border: #000 !important;" id="total_insert_qty">' + $('#total_qty_delivery').text() + '</b></span>',
                //     plain: true,
                //     handler: function(){ }
                // },

                {
                    text: '<span style="font-size: 14px !important; color:#000;">Total Qty Delivery : <b id="total_insert_qty" style="font-size: 14px !important; border: #000 !important;">0</b></span>',
                    plain: true,
                    handler: function(){ }
                },


                {
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var delivery_date = $("#delivery_date").datebox('getValue');
                    var delivery_note_no = $("#delivery_note_no").textbox('getValue');
                    var delivery_category = $("#delivery_category").combobox('getValue');
                    var delivery_to_insert = $("#delivery_to_insert").combobox('getValue');
                    var destination = $("#destination").combogrid('getValue');
                    var destination_code = $("#destination_code").combogrid('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            console.log(rows[i].source_type);
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('control/delivery_to_subconts/create') ?>',
                                data: {
                                    item_fg_id: rows[i].item_fg_id,
                                    internal_doc_no: rows[i].internal_doc_no,
                                    delivery_date: delivery_date,
                                    delivery_note_no: delivery_note_no,
                                    delivery_category: delivery_category,
                                    delivery_to: delivery_to_insert,
                                    destination: destination,
                                    prod_date: rows[i].trans_date,
                                    workorder: rows[i].workorder,
                                    qty_output: rows[i].qty_output,
                                    qty_delivery: rows[i].qty_delivery,
                                    source_type: rows[i].source_type,
                                    remarks: rows[i].remarks
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
    });

    // $('#filter_customer_id').combobox({
    //     url: '<?= base_url('master/customers/reads'); ?>',
    //     valueField: 'id',
    //     textField: 'name',
    //     prompt: 'Choose All',
    //     icons: [{
    //         iconCls: 'icon-clear',
    //         handler: function(e) {
    //             $(e.data.target).combobox('clear').combobox('textbox').focus();
    //         }
    //     }],
    //     onSelect: function(customer) {
    //         var filter_from = $("#filter_from").datebox("getValue");
    //         var filter_to = $("#filter_to").datebox("getValue");

    //         $('#filter_delivery_note_no').combobox({
    //             url: '<?= base_url('control/delivery_to_subconts/readDelivery_note_no?customer_id='); ?>' + customer.id + "&filter_from=" + filter_from + "&filter_to=" + filter_to,
    //             valueField: 'delivery_note_no',
    //             textField: 'delivery_note_no',
    //             prompt: 'Choose All',
    //             icons: [{
    //                 iconCls: 'icon-clear',
    //                 handler: function(e) {
    //                     $(e.data.target).combobox('clear').combobox('textbox').focus();
    //                 }
    //             }],
    //         });
    //     }
    // });

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

    // $('#delivery_order_no').combobox({
    //     url: '<?= base_url('control/delivery_to_subconts/readDo/') ?>' + customer.id + "/" + btoa(control_orders.division) + "/" + btoa(customer_address.id),
    //     valueField: 'delivery_order_no',
    //     textField: 'delivery_order_no',
    //     multiple: true,
    //     prompt: 'Choose DO No.',
    // });

    $('#delivery_to_insert').combobox({
        onChange: function (newValue, oldValue) {
            $("#destination").combogrid('enable');

            if (newValue === 'SUBCONT') {
                initSubcontGrid();
            } else if (newValue === 'TEFA') {
                initTefaGrid();
            } else {
                $('#destination').combogrid('clear');
                $('#destination').combogrid('grid').datagrid('loadData', []); // clear data
            }
        }
    });

    function initSubcontGrid() {
        $('#destination').combogrid({
            url: '<?= base_url('master/subconts/reads/'); ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Subcont",
            columns: [[
                {field: 'number', title: 'Subcont Code', width: 120},
                {field: 'name', title: 'Subcont Name', width: 250}
            ]],
            onSelect: function(index, row) {
                $('#destination_code').combogrid('setValue', row.number); // << kode subcont
                regenerateDeliveryNoteNo();
            }
        });
    }

    function initTefaGrid() {
        $('#destination').combogrid({
            url: '<?= base_url('master/teaching_factory/reads/'); ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Teaching Factory",
            columns: [[
                {field: 'number', title: 'TF Code', width: 120},
                {field: 'name', title: 'TF Name', width: 250}
            ]],
            onSelect: function(index, row) {
                $('#destination_code').combogrid('setValue', row.number); // << kode TF
                regenerateDeliveryNoteNo();
            }
        });
    }

    function regenerateDeliveryNoteNo() {
        let trans_date = $('#delivery_date').datebox('getValue');
        let dest_code = $('#destination_code').combogrid('getValue');

        if (trans_date && dest_code) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/delivery_to_subconts/delivery_note_no') ?>",
                data: { trans_date: trans_date, destination_code: dest_code },
                dataType: "html",
                success: function(result) {
                    $("#delivery_note_no").textbox('setValue', result);
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
        var print = "print_dn_to_sc('" + row.delivery_note_no + "')"; 
        if(row.printed==0){
            return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }else{
            return '<a class="btn btn-secondary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';

        }
    }

    function print_dn_to_sc(delivery_note_no) {
        console.log(delivery_note_no);
        window.open("<?= base_url('control/delivery_to_subconts/print_dn_to_sc/') ?>" + window.btoa(delivery_note_no), "_blank", "width=1200,height=600");
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
        // if(value == "" || value === null) {
        //     return '<b>CLOSED</b>';
        // } else {
        //     return '<b>OPEN</b>';
        // }

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