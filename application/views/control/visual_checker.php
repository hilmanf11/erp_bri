<style>
    #dlgProblem {
        padding: 10px !important;
    }

    .ng-wrapper {
        height: calc(100% - 12px);
        padding-bottom: 0px;
    }
</style>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'printed',width:100,align:'center', formatter:btnPrint">Print</th>
            <th rowspan="2" data-options="field:'check_date',width:120,halign:'center',sortable:true">Check Date</th>
            <th rowspan="2" data-options="field:'visual_process',width:120,halign:'center',sortable:true">Visual Process</th>
            <th rowspan="2" data-options="field:'inspector',width:180,halign:'center',sortable:true">Inspector</th>
            <th rowspan="2" data-options="field:'item_fg_id',width:150,halign:'center',sortable:true">Product ID</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:180,halign:'center',sortable:true">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:180,halign:'center',sortable:true">Product Name</th>
            <th rowspan="2" data-options="field:'source',width:150,halign:'center',sortable:true, align:'center'">Source</th>
            <th rowspan="2" data-options="field:'workorder',width:130,halign:'center',sortable:true,align:'center'">WO No</th>
            <th rowspan="2" data-options="field:'check_qty',width:140,halign:'center',sortable:true,formatter:numberFormat, align:'right'">Check Qty (pcs)</th>
            <th rowspan="2" data-options="field:'ok_qty',width:140,halign:'center',sortable:true,formatter:numberFormat, align:'right'">OK Qty (pcs)</th>
            <th rowspan="2" data-options="field:'rework_qty',width:140,halign:'center',sortable:true,formatter:numberFormat, align:'right'">Rework Qty (pcs)</th>
            <th rowspan="2" data-options="field:'total_ng_qty',width:140,halign:'center',sortable:true,formatter:numberFormat, align:'right'">Total NG Qty (pcs)</th>
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
<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; padding-bottom: 8px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Type Date</span>
                    <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto" data-options="editable:false">
                        <!-- <option value="">Select All</option> -->
                        <option value="Check Date" selected>Check Date</option>
                        <option value="Production Date">Production Date</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Check Date</span>

                    <input style="width:26.35%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false" class="easyui-datebox">
                    <span style="width:6%; display:inline-block; text-align:center;">to</span>
                    <input style="width:26.35%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false" class="easyui-datebox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Production Date</span>

                    <input style="width:26.35%;" id="filter_from_prod" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false" class="easyui-datebox">
                    <span style="width:6%; display:inline-block; text-align:center;">to</span>
                    <input style="width:26.35%;" id="filter_to_prod" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false" class="easyui-datebox">
                </div>
            </div>

            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Visual Process</span>
                    <select style="width:60%;" name="filter_visual_process" id="filter_visual_process" panelHeight="auto" class="easyui-combobox" data-options="editable:false,prompt:'Choose visual process'" required>
                        <option value="">All</option>
                        <option value="Check">Check</option>
                        <option value="Sortir">Sortir</option>
                        <option value="Repair">Repair</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
            </div>

            <div style="width: 33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source</span>
                    <input style="width:60%;" id="filter_source" class="easyui-combogrid">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Inspector</span>
                    <input style="width:60%;" name="filter_inspector" id="filter_inspector" class="easyui-combogrid">
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
<div id="dlg_insert" class="easyui-dialog" title="Add Visual Checker" data-options="closed: true,modal:true" style="width: 1220px; height: 600px; padding:10px; top: 20px; left: 50px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Check Date</span>
                    <input style="width:60%;" name="check_date" id="check_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Inspector</span>
                    <input style="width:60%;" name="inspector" id="inspector" class="easyui-combogrid" required>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Visual Process</span>
                    <select style="width:60%;" name="visual_process" id="visual_process" panelHeight="auto" class="easyui-combobox" data-options="editable:false,prompt:'Choose visual process'" required>
                        <option value="Check">Check</option>
                        <option value="Sortir">Sortir</option>
                        <option value="Repair">Repair</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer" id="customer" class="easyui-combogrid" required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product No List" toolbar="#toolbar2"></table>
    </form>
</div>

<div id="dlgProblem" class="easyui-dialog" closed="true"
     style="width:600px;height:465px;"
     data-options="modal:true,closed:true,buttons:'#dlgProblemButtons',title:'NG Problem List'">

    <div class="ng-wrapper">
        <table id="tblNG"></table>
    </div>
</div>

<div id="dlgProblemButtons">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="saveNG()">Save</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#dlgProblem').dialog('close')">Cancel</a>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/visual_checker/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    let currentIndexProblem = null;
    let problemData = {};  // Menyimpan hasil NG Qty per row dg2

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');   

        // $("#check_date").datebox({
        //     formatter: myformatter,
        //     parser: myparser,
        //     editable: false,
        //     onSelect: function(date){
        //         setTimeout(regenerateDeliveryNoteNo, 49);
        //     }
        // });

        setTimeout(function(){
        //     $("#delivery_from").textbox('enable');
        //     $("#delivery_date").textbox('enable');

            $("#check_date").datebox('enable');
            $('#check_date').datebox('setValue', '<?= date("Y-m-d") ?>');


        //     var url = '<?= base_url('control/visual_checker/readDeliveryNoteNoSCTF/'); ?>?t=' + new Date().getTime();
        //     $('#delivery_note_no').combogrid('grid').datagrid('reload', url);

        }, 50);

        url_save = '<?= base_url('control/visual_checker/create') ?>';
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            fitColumns: true,
            fit: true,
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
                            url: '<?= base_url('control/visual_checker/readItemFg/'); ?>',
                            method: 'post',
                            required: true,
                            panelWidth: 850,
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
                                    title: 'WO No',
                                    halign: 'center',
                                    width: 150
                                },{
                                    field: 'qty_receive',
                                    title: 'Incoming Qty (pcs)',
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
                                    field: 'source',
                                    title: 'Source',
                                    halign: 'center',
                                    width: 150
                                },]
                            ],
                            onBeforeLoad: function(param) {
                                var dg = $('#dg2');
                                var rows = dg.datagrid('getRows');
                                param.customer_id = $('#customer').combogrid('getValue');

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

                                var edId   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
                                var edNo   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
                                var edSC   = dg.datagrid('getEditor', { index: idx, field: 'source' });

                                if (data.rows && data.rows.length === 1) {
                                    var item = data.rows[0];
                                    $(edNo.target).combogrid('grid').datagrid('selectRecord', item.item_fg_id);
                                }

                                if (row.item_fg_id) {
                                    if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
                                    if (edWO)   $(edWO.target).textbox('setValue', row.workorder);
                                    if (edSC)   $(edSC.target).textbox('setValue', row.source);
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
                                    field: 'workorder'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'source'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'qty_receive'
                                });

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.workorder);
                                $(ed4.target).textbox('setValue', rows.source);
                                $(ed5.target).textbox('setValue', rows.qty_receive);
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
                    field: 'qty_receive',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "QTY Receive",
                    editor: {
                        type: 'textbox'
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
                    field: 'source',
                    width: 200,
                    halign: 'center',
                    title: "Source",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'operator_finishing',
                    width: 200,
                    halign: 'center',
                    title: "Operator Finishing",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true,
                        }
                    }
                }, {
                    field: 'compound_lot_no',
                    width: 200,
                    halign: 'center',
                    title: "Compound Lot No",
                    editor: {
                        type: 'textbox',
                        options: {
                            required: true
                        }
                    }
                }, {
                    field: 'check_qty',
                    width: 100,
                    align: 'center',
                    title: "Check Qty",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            onChange: function() {
                                let dg = $('#dg2');
                                let row = dg.datagrid('getSelected');
                                let idx = dg.datagrid('getRowIndex', row);
                                validateVisualCheckQTY(idx);
                            }
                        }
                    }
                }, {
                    field: 'ok_qty',
                    width: 100,
                    align: 'center',
                    title: "OK Qty",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            onChange: function() {
                                let dg = $('#dg2');
                                let row = dg.datagrid('getSelected');
                                let idx = dg.datagrid('getRowIndex', row);
                                validateVisualRow(idx);
                            }
                        }
                    }
                }, {
                    field: 'rework_qty',
                    width: 100,
                    align: 'center',
                    title: "Rework Qty",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            onChange: function() {
                                let dg = $('#dg2');
                                let row = dg.datagrid('getSelected');
                                let idx = dg.datagrid('getRowIndex', row);
                                validateVisualRow(idx);
                            }
                        }
                    }
                }, {
                    field: 'qty_ng_total',
                    // hidden: true,
                    width: 120,
                    align: 'center',
                    title: "Total NG Qty",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'problems',
                    width: 200,
                    halign: 'center',
                    title: "Problems",
                    formatter: buttonProblem,
                }]
            ],
            onClickCell: onClickCell
        });
    }

    function validateVisualCheckQTY(index) {
        let dg = $('#dg2');

        let edQtyR     = dg.datagrid('getEditor', { index: index, field: 'qty_receive' });
        let edCheck  = dg.datagrid('getEditor', { index: index, field: 'check_qty' });

        if (!edCheck || !edQtyR) return;

        let ok_qty     = parseFloat($(edQtyR.target).numberbox('getValue'))     || 0;
        let check_qty  = parseFloat($(edCheck.target).numberbox('getValue'))  || 0;

        if (check_qty > ok_qty) {
            toastr.warning('Check Qty tidak boleh lebih besar dari Incoming QTY');
            $(edCheck.target).numberbox('setValue', ok_qty);
        }
    }

    function validateVisualRow(index) {
        let dg = $('#dg2');

        let edCheck     = dg.datagrid('getEditor', { index: index, field: 'check_qty' });
        let edOK        = dg.datagrid('getEditor', { index: index, field: 'ok_qty' });
        let edRework    = dg.datagrid('getEditor', { index: index, field: 'rework_qty' });
        let edNGTotal   = dg.datagrid('getEditor', { index: index, field: 'qty_ng_total' });

        if (!edCheck || !edOK || !edRework) return;

        let check_qty  = parseInt($(edCheck.target).numberbox('getValue'))  || 0;
        let ok_qty     = parseInt($(edOK.target).numberbox('getValue'))     || 0;
        let rework_qty = parseInt($(edRework.target).numberbox('getValue')) || 0;

        // 1. OK QTY > Check QTY
        if (ok_qty > check_qty) {
            toastr.warning("OK Qty tidak boleh melebihi Check Qty");
            ok_qty = check_qty;
            $(edOK.target).numberbox('setValue', ok_qty);
        }

        // 2. Rework QTY > Check QTY
        if (rework_qty > check_qty) {
            toastr.warning("Rework Qty tidak boleh melebihi Check Qty");
            rework_qty = check_qty;
            $(edRework.target).numberbox('setValue', rework_qty);
        }

        // 3. OK + Rework > Check
        if ((ok_qty + rework_qty) > check_qty) {
            toastr.warning("OK Qty + Rework Qty tidak boleh melebihi Check Qty");

            rework_qty = check_qty - ok_qty;
            if (rework_qty < 0) rework_qty = 0;

            $(edRework.target).numberbox('setValue', rework_qty);
        }

        // ============================
        // 4. HITUNG TOTAL NG
        // ============================

        if (edNGTotal) {
            let total_ng = check_qty - (ok_qty + rework_qty);

            if (total_ng < 0) total_ng = 0;

            $(edNGTotal.target).numberbox('setValue', total_ng);
        }
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
        var inspector      = $("#inspector").combogrid('getValue');
        var visual_process = $("#visual_process").combobox('getValue');
        var customer       = $("#customer").combogrid('getValue');

        if (inspector != "" && visual_process != "" && customer != "") {
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

    // function removeit() {

    //     var dg = $('#dg2');
    //     var row = dg.datagrid('getSelected');

    //     if (!row) return;

    //     var rowIndex = dg.datagrid('getRowIndex', row);

    //     var item_fg_id = row.item_fg_id ?? "";
    //     var workorder  = row.workorder ?? "";
    //     // var doc_no     = $("#doc_no").textbox('getValue');


    //     // if (editIndex == undefined) {
    //     //     return true;
    //     // }

    //     // var dg = $('#dg2');
    //     // var row = dg.datagrid('getSelected');
    //     // var rowIndex = dg.datagrid('getRowIndex', row);

    //     // var ed = dg.datagrid('getEditor', {
    //     //     index: editIndex,
    //     //     field: 'item_fg_id'
    //     // });

    //     // var ed1 = dg.datagrid('getEditor', {
    //     //     index: editIndex,
    //     //     field: 'workorder'
    //     // });

    //     // var item_fg_id = $(ed.target).textbox('getValue');
    //     // var workorder = $(ed1.target).textbox('getValue');
    //     // var incoming_doc_no = $("#incoming_doc_no").textbox('getValue');

    //     $.ajax({
    //         method: 'post',
    //         url: '<?= base_url('control/visual_checker/delete') ?>',
    //         data: {
    //             // subcont_id: row.subcont_id,
    //             // incoming_doc_no: incoming_doc_no,
    //             item_fg_id: item_fg_id,
    //             workorder: workorder,
    //         },
    //         success: function(result) {
    //             try {
    //                 res = JSON.parse(res);

    //                 if(res.message == "Cannot delete data that is still in use") {
    //                     toastr.error(res.message);
    //                 }else{
    //                     toastr.success(res.message);
    //                     dg.datagrid('deleteRow', rowIndex);
    //                 }

    //             } catch (e) {
    //                 toastr.error("Invalid response");
    //             }
    //         },
    //         error: function(jqXHR, textStatus, errorThrown) {
    //             toastr.error(jqXHR.statusText);
    //             $.messager.alert("Error", jqXHR.statusText, 'error');
    //         },
    //         complete: function(data) {
    //             $('#dg').datagrid('reload');
    //         }
    //     });
    // }

    function removeit() {
        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        if (!row) return;

        var rowIndex = dg.datagrid('getRowIndex', row);

        if (row.detail_id) {
            $.ajax({
                method: 'post',
                url: "<?= base_url('control/visual_checker/delete') ?>",
                data: { detail_id: row.detail_id, item_fg_id: row.item_fg_id, workorder: row.workorder },
                success: function(res) {
                    dg.datagrid('deleteRow', rowIndex);
                    toastr.success(res.message);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    toastr.error(jqXHR.statusText);
                    $.messager.alert("Error", jqXHR.statusText, 'error');
                }
            });
        } else {
            dg.datagrid('deleteRow', rowIndex);
            toastr.success("Data deleted successfully");
        }

        if (problemData[rowIndex]) {
            delete problemData[rowIndex];
        }
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

            $("#check_date").datebox('disable');
            $("#incoming_doc_no").textbox('disable');
            $("#delivery_note_no").combogrid('disable');

            addTable('<?= base_url('control/visual_checker/datatableUpdates?incoming_doc_no=') ?>' + window.btoa(row.incoming_doc_no));
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
                            url: '<?= base_url('control/visual_checker/deleteAll') ?>',
                            data: {
                                id: row.id,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');

                                toastr.success(result.message);
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
        var filter_from_prod = $("#filter_from_prod").datebox('getValue');
        var filter_to_prod = $("#filter_to_prod").datebox('getValue');
        var filter_visual_process = $("#filter_visual_process").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_source = $("#filter_source").combogrid('getValue');
        var filter_inspector = $("#filter_inspector").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');

        console.log(filter_source);


        var url = "?filter_type=" + window.btoa(filter_type) +
            "&filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_from_prod=" + window.btoa(filter_from_prod) +
            "&filter_to_prod=" + window.btoa(filter_to_prod) +
            "&filter_visual_process=" + window.btoa(filter_visual_process) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_source=" + window.btoa(filter_source) +
            "&filter_inspector=" + window.btoa(filter_inspector);

        $('#dg').datagrid({
            url: '<?= base_url('control/visual_checker/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.item_fg_number + '"></table></div>';
            },

            onBeforeExpand: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                if (ddv.length && ddv.datagrid('options')) {
                    try { ddv.datagrid('destroy'); } catch (e) { /* ignore */ }
                }
            },

            onExpandRow: function(index, row) {
                var dg = $(this);
                var ddv = dg.datagrid('getRowDetail', index).find('table.ddv');

                let filter_type = $('#filter_type').combobox('getValue');
                let filter_item_fg = $('#filter_item_fg').combogrid('getValue') || row.item_fg_id;

                let url = '<?= base_url('control/visual_checker/datatableDetails?id=') ?>'
                        + window.btoa(row.id)
                        + '&item_fg=' + window.btoa(filter_item_fg);

                if (filter_type === "Production Date") {
                    let filter_from_prod = $('#filter_from_prod').datebox('getValue');
                    let filter_to_prod = $('#filter_to_prod').datebox('getValue');

                    url += '&filter_from_prod=' + window.btoa(filter_from_prod)
                        +  '&filter_to_prod='   + window.btoa(filter_to_prod);
                }

                $.getJSON(url, function(resp) {

                        let rows = resp.rows || [];
                        let dynCols = (resp.columns || []).map(c => ({
                            field: c.code,
                            title: c.name,
                            width: 150,
                            align: 'center',
                            formatter: numberFormat
                        }));

                        let finalCols = [
                            { 
                                field: 'prod_date', 
                                title: 'Prod Date', 
                                width: 120 
                            }, { 
                                field: 'operator_name', 
                                title: 'Operator Press', 
                                width: 200 
                            }, { 
                                field: 'compound_lot_no', 
                                title: 'Compound Lot No', 
                                width: 200 
                            }, { 
                                field: 'source', 
                                title: 'Source', 
                                width: 150 
                            }, { 
                                field: 'operator_finishing', 
                                title: 'Operator Finishing', 
                                width: 150 
                            }, { 
                                field: 'qty_ng_total', 
                                title: 'Total NG', 
                                width: 120 
                            },

                            ...dynCols
                        ];

                        try { ddv.datagrid('destroy'); } catch(e){}

                        ddv.datagrid({
                            data: rows,
                            columns: [finalCols],
                            rownumbers: true,
                            singleSelect: true,
                            fitColumns: false,
                        });

                        setTimeout(() => dg.datagrid('fixDetailRowHeight', index), 20);
                    }
                );

                dg.datagrid('fixDetailRowHeight', index);
            }

        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/visual_checker/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_visual_process = $("#filter_visual_process").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_source = $("#filter_source").combogrid('getValue');
        var filter_inspector = $("#filter_inspector").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_visual_process=" + window.btoa(filter_visual_process) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_source=" + window.btoa(filter_source) +
            "&filter_inspector=" + window.btoa(filter_inspector);

        window.location.assign('<?= base_url('control/visual_checker/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();
        addTable();

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {

                    endEditing();
                    
                    let rowsDetail = $('#dg2').datagrid('getRows');
                    
                    var check_date = $("#check_date").datebox('getValue');
                    var inspector = $("#inspector").combogrid('getValue');
                    var visual_process = $("#visual_process").combobox('getValue');
                    var customer_id = $("#customer").combogrid('getValue');

                    $.ajax({
                        type: "post",
                        url: "<?= base_url('control/visual_checker/createHeader') ?>",
                        data: {
                            check_date: check_date,
                            inspector: inspector,
                            visual_process: visual_process,
                            customer_id: customer_id,
                        },
                        dataType: "json",
                        success: function(res) {
                            // saveAllDetail(header_id, rowsDetail);

                            if (!res.status || !res.id) {
                                Swal.fire("Error", "Failed save header", "error");
                                return;
                            }

                            let header_id = res.id;
                            saveAllDetail(header_id, rowsDetail);
                        }
                    });


                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        function saveAllDetail(header_id, rowsDetail) {

            let total = rowsDetail.length;

            rowsDetail.forEach((row, idx) => {

                $.ajax({
                    type: "post",
                    url: "<?= base_url('control/visual_checker/createDetail') ?>",
                    data: {
                        visual_checker_id: header_id,
                        item_fg_id: row.item_fg_id,
                        workorder: row.workorder,
                        source: row.source,
                        operator_finishing: row.operator_finishing,
                        compound_lot_no: row.compound_lot_no,
                        check_qty: row.check_qty,
                        ok_qty: row.ok_qty,
                        rework_qty: row.rework_qty,
                        qty_ng_total: row.qty_ng_total
                    },
                    dataType: "json",
                    success: function(res) {

                        // saveNGPerDetail(idx, detail_id);

                        // if (idx === total - 1) {
                        //     Swal.fire({
                        //         title: "Success",
                        //         icon: "success"
                        //     }).then(() => {
                        //         window.location.reload();
                        //     });
                        // }


                        if (res.status && res.id) {
                            let detail_id = res.id;

                            saveNGPerDetail(idx, detail_id);
                        }

                        if (idx === total - 1) {
                            Swal.fire({
                                title: "Data saved successfully",
                                icon: "success"
                            }).then(() => {
                                window.location.reload();
                            });
                        }

                    }
                });
            });
        }

        function saveNGPerDetail(rowIndex, detail_id) {

            let ngList = problemData[rowIndex];
            if (!ngList) return;

            Object.keys(ngList).forEach(ng_code => {

                let qty = ngList[ng_code];

                $.ajax({
                    type: "post",
                    url: "<?= base_url('control/visual_checker/createNG') ?>",
                    data: {
                        detail_id: detail_id,
                        ng_code: ng_code,
                        qty_ng: qty
                    }
                });
            });
        }

        $('#inspector').combogrid({
            url: '<?= base_url("master/man_powers/readVisualCheckers") ?>',
            panelWidth: 400,
            idField: 'nik',
            textField: 'name',
            valueField: 'nik',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Inspector Name",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'nik',
                    title: 'NIK',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }]
            ],
        });

        $('#customer').combogrid({
            url: '<?= base_url("master/customers/reads") ?>',
            panelWidth: 350,
            idField: 'id',
            textField: 'name',
            valueField: 'id',
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
                    field: 'id',
                    title: 'Customer ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Customer Name',
                    width: 250
                }]
            ],
        });


        $("#filter_from_prod").datebox('disable');
        $("#filter_to_prod").datebox('disable');

        $("#filter_type").combobox({
            onChange: function(val) {
                if (val == "Check Date") {
                    $("#filter_from").datebox('enable');
                    $("#filter_to").datebox('enable');
                    $("#filter_from_prod").datebox('disable');
                    $("#filter_to_prod").datebox('disable');
                } else if (val == "Production Date") {
                    $("#filter_from").datebox('disable');
                    $("#filter_to").datebox('disable');
                    $("#filter_from_prod").datebox('enable');
                    $("#filter_to_prod").datebox('enable');
                } else {
                    $("#filter_from").datebox('enable');
                    $("#filter_to").datebox('enable');
                    $("#filter_from_prod").datebox('disable');
                    $("#filter_to_prod").datebox('disable');
                }
            }
        });

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

    $('#filter_inspector').combogrid({
        url: '<?= base_url("master/man_powers/readVisualCheckers") ?>',
        panelWidth: 400,
        idField: 'nik',
        textField: 'name',
        valueField: 'nik',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Inspector Name",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'nik',
                title: 'NIK',
                width: 200
            }, {
                field: 'name',
                title: 'Name',
                width: 200
            }]
        ],
    });

    $('#filter_source').combogrid({
        url: '<?= base_url('control/visual_checker/readSources'); ?>',
        panelWidth: 440,
        idField: 'number',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Source",
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'number', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        onSelect: function(index, row) {
            // $('#destination_code').textbox('setValue', row.number);
        }
    });

    function buttonProblem(value, row, index) {
        var e = '<a href="javascript:void(0)" class="btn btn-danger btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="setProblem(' + index + ')">Set Problem</a>';
        return e;
    }

    function setProblem(index) {
        currentIndexProblem = index;

        $('#tblNG').datagrid('load', "<?= base_url('master/master_ng/getData'); ?>");

        $('#dlgProblem').dialog('open');
    }

    let lastIndexNG = undefined;

    $('#tblNG').datagrid({
        fit: true,
        fitColumns: true,
        rownumbers: true,
        singleSelect: false,
        striped: true,
        border: true,    
        onBeforeSelect: () => false,
        onClickRow: () => false,
        columns:[[
            {
                field:'code',
                title:'NG Code',
                width:100,
                align:'center'
            },
            {
                field:'name',
                title:'NG Name',
                width:200
            },
            { 
                field:'ng_qty',
                title:'Qty',
                width:80,
                align:'center',
                editor:{
                    type:'numberbox',
                    options:{
                        precision:0,
                        min:0
                    }
                }
            }
        ]],
        onLoadSuccess: function() {
            if (problemData[currentIndexProblem]) {
                let rows = $('#tblNG').datagrid('getRows');
                let saved = problemData[currentIndexProblem];

                rows.forEach((r, i) => {
                    if (saved[r.code] !== undefined) {
                        $('#tblNG').datagrid('beginEdit', i);
                        let ed = $('#tblNG').datagrid('getEditor', { index: i, field: 'ng_qty' });
                        $(ed.target).numberbox('setValue', saved[r.code]);
                        $('#tblNG').datagrid('endEdit', i);
                    }
                });
            }
        },
        onClickCell: function (index, field) {
            if (field === 'ng_qty') {
                if (lastIndexNG !== index) {
                    $('#tblNG').datagrid('endEdit', lastIndexNG);
                }

                $('#tblNG').datagrid('beginEdit', index);

                let ed = $('#tblNG').datagrid('getEditor', { index, field: 'ng_qty' });
                if (ed) {
                    $(ed.target).numberbox({
                        onChange: function (newValue, oldValue) {
                            validateNG(index, newValue);
                        }
                    });
                }

                lastIndexNG = index;
            }
            else {
                $('#tblNG').datagrid('endEdit', lastIndexNG);
                lastIndexNG = undefined;
            }
        }

    });

    function saveNG() {
        let dg = $('#tblNG');
        let rows = dg.datagrid('getRows');

        rows.forEach((r, i) => dg.datagrid('endEdit', i));

        let totalNG = 0;
        rows.forEach((r) => {
            totalNG += parseInt(r.ng_qty || 0);
        });

        let totalNGQty = getTotalNGQty();
        if (totalNG > totalNGQty) {
            toastr.warning(
                "Total NG (" + totalNG + ") tidak boleh melebihi Total NG Qty (" + totalNGQty + ")"
            );
            return;
        }

        let temp = {};
        rows.forEach((r) => {
            if (r.ng_qty && parseInt(r.ng_qty) > 0) {
                temp[r.code] = parseInt(r.ng_qty);
            }
        });

        problemData[currentIndexProblem] = temp;

        console.log("DATA TERSIMPAN UNTUK INDEX:", currentIndexProblem, temp);

        $('#dlgProblem').dialog('close');
    }

    function getTotalNGQty() {
        let ed = $('#dg2').datagrid('getEditor', {
            index: currentIndexProblem,
            field: 'qty_ng_total'
        });

        if (ed) {
            return parseInt($(ed.target).numberbox('getValue') || 0);
        }

        let row = $('#dg2').datagrid('getRows')[currentIndexProblem];
        return parseInt(row.qty_ng_total || 0);
    }

    function validateNG(index, newValue) {
        let dg = $('#tblNG');

        dg.datagrid('endEdit', index);
        dg.datagrid('beginEdit', index);

        newValue = parseInt(newValue || 0);

        let rows = dg.datagrid('getRows');
        let total = 0;

        rows.forEach((r, i) => {
            total += parseInt(r.ng_qty || 0);
        });

        let totalNGQty = getTotalNGQty();
        if (total > totalNGQty) {
            let ed = dg.datagrid('getEditor', { index, field: 'ng_qty' });
            $(ed.target).numberbox('setValue', 0);

            dg.datagrid('endEdit', index);
            dg.datagrid('beginEdit', index);

            toastr.warning(
                "Total NG (" + total + ") tidak boleh melebihi Total NG Qty (" + totalNGQty + ")"
            );
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

    function btnPrint(val, row) {
        var print = "print_vc('" + row.id + "','" + row.item_fg_id + "')";

        if(row.printed==0){
            return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
        }else{
            return '<a class="btn btn-secondary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';

        }
    }

    function print_vc(id, item_fg_id) {
        console.log(id, item_fg_id);

        const encodedId = window.btoa(id);
        const encodedItemFg = window.btoa(item_fg_id);


        const url = "<?= base_url('control/visual_checker/print_vc/') ?>" 
                + encodedId + "/" + encodedItemFg;


        window.open(url, "_blank", "width=1200,height=600");

        // window.open("<?= base_url('control/visual_checker/print_vc/') ?>" + window.btoa(id), + window.btoa(item_fg_id), "_blank", "width=1200,height=600");
    }

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }
</script>