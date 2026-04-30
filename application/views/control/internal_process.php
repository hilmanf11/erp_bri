<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'process_name',width:230,halign:'center',sortable:true">Process Name</th>
            <th rowspan="2" data-options="field:'process_date',width:230,halign:'center',sortable:true">Process Date</th>
            <th rowspan="2" data-options="field:'doc_no',width:230,halign:'center',sortable:true">Document No</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:170,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:200,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:170,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:200,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 195px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Name</span>
                    <select style="width:60%;" id="filter_process_name" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                        <option value="Cutting Punch">Cutting Punch</option>
                        <option value="Internal Finishing">Internal Finishing</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Date</span>

                    <input style="width:26.45%;" id="filter_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false" class="easyui-datebox">
                    <span style="width:6%; display:inline-block; text-align:center;">to</span>
                    <input style="width:26.45%;" id="filter_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false" class="easyui-datebox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" id="filter_doc_no" class="easyui-combobox">
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
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add_cp()"><i class="fa fa-plus"></i> Add CP</a>

        <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add()"><i class="fa fa-plus"></i> Add INF</a> -->

        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update_cp()"><i class="fa fa-edit"></i> Update CP</a>

        <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update()"><i class="fa fa-edit"></i> Update INF</a> -->

        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- <div id="toolbar2" style="padding: 2px; margin-top: -38px; background-color: #f5f5f5 !important">
    <a href="javascript:void(0)" id="btn-add" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" id="btn-remove" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div> -->

<div id="toolbar_cp">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit2()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- <div id="toolbar_cp" style="padding: 2px; margin-top: -38px; background-color: #f5f5f5 !important">
    <a href="javascript:void(0)" id="btn-add2" class="easyui-linkbutton" data-options="plain:true" onclick="append2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" id="btn-remove2" class="easyui-linkbutton" data-options="plain:true" onclick="removeit2()"><i class="fa fa-times"></i> Remove</a>
</div> -->


<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add Internal Process" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 50px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Date</span>
                    <input style="width:60%;" name="process_date" id="process_date" required class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Name</span>
                    <input style="width:60%;" name="process_name" id="process_name" class="easyui-textbox" value="Internal Finishing" readonly required>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" name="doc_no" id="doc_no" class="easyui-textbox" readonly required>
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product No List" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Insert & Update -->
<div id="dlg_insert2" class="easyui-dialog" title="Add Cutting Punch" data-options="closed: true,modal:true" style="width: 1200px; height: 600px; padding:10px; top: 20px; left: 50px;">
    <form id="frm_insert2" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Date</span>
                    <input style="width:60%;" name="process_date2" id="process_date2" required class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Name</span>
                    <input style="width:60%;" name="process_name2" id="process_name2" class="easyui-textbox" readonly required>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" name="doc_no2" id="doc_no2" class="easyui-textbox" readonly required>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" name="workorder2" id="workorder2" class="easyui-combobox" required>
                </div>
            </div>
        </fieldset>
        <table id="dg3" class="easyui-datagrid" style="width:100%;" title="Product No List" toolbar="#toolbar_cp"></table>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/internal_process/print') ?>" style="width: 100%;" hidden></iframe>

<script>

    var isReadOnlyMode = false;
    var isReadOnlyMode2 = false;

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $('#frm_insert').form('clear');   

        $('#btn-add').show();

        addTable();

        $("#process_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                setTimeout(regenerateDocNoInternal, 49);
            }
        });

        setTimeout(function(){
            $("#process_date").datebox('enable');
            $('#process_name').textbox('setValue', 'Internal Finishing');
            $("#doc_no").textbox('enable');
            $("#doc_no").textbox('clear');
            $('#process_date').datebox('setValue', '<?= date("Y-m-d") ?>');
            regenerateDocNoInternal();
        }, 50);

        url_save = '<?= base_url('control/internal_process/create') ?>';
    }

    function add_cp() {
        $('#dlg_insert2').dialog('open');
        $('#dg3').datagrid('loadData', []);
        $('#frm_insert2').form('clear');   

        $('#btn-add2').show();

        addTableCP();

        $("#process_date2").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                setTimeout(regenerateDocNoCP, 49);
            }
        });

        setTimeout(function(){
            $("#process_date2").datebox('enable');
            $("#workorder2").combobox('enable');
            $('#process_name2').textbox('setValue', 'Cutting Punch');
            $("#doc_no2").textbox('enable');
            $("#doc_no2").textbox('clear');
            $('#process_date2').datebox('setValue', '<?= date("Y-m-d") ?>');
            regenerateDocNoCP();
        }, 50);

        url_save = '<?= base_url('control/internal_process/create') ?>';

    }

    function addTable(link = "", readonly = false) {
         isReadOnlyMode = readonly;
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
                            url: '<?= base_url('control/internal_process/readItemFg/'); ?>',
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
                                    field: 'ok_press',
                                    title: 'OK Press',
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
                                var dg = $('#dg2');
                                var rows = dg.datagrid('getRows');
                                param.process_date = $('#process_date').datebox('getValue');

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
                                var edName = dg.datagrid('getEditor', { index: idx, field: 'item_fg_name' });
                                var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
                                var edOKPress   = dg.datagrid('getEditor', { index: idx, field: 'ok_press' });

                                if (row.item_fg_id) {
                                    if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
                                    if (edName) $(edName.target).textbox('setValue', row.item_fg_name);
                                    if (edWO)   $(edWO.target).textbox('setValue', row.workorder);
                                    if (edOKPress)   $(edOKPress.target).textbox('setValue', row.ok_press);
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
                                    field: 'ok_press'
                                });

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.workorder);
                                $(ed5.target).textbox('setValue', rows.ok_press);
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
                    field: 'ok_press',
                    width: 100,
                    align: 'center',
                    title: "OK Press",
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
                    field: 'internal',
                    width: 100,
                    align: 'center',
                    title: "Internal",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            onChange: function (newValue, oldValue) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;

                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var edOKPress = dg.datagrid('getEditor', { index: rowIndex, field: 'ok_press' });
                                var OKPress = edOKPress
                                    ? parseFloat($(edOKPress.target).numberbox('getValue')) || 0
                                    : parseFloat(row.ok_press) || 0;

                                var internal = parseFloat(newValue) || 0;

                                if (internal > OKPress) {
                                    toastr.warning('Nilai Internal tidak boleh lebih besar dari OK Press!');
                                    internal = OKPress;
                                    var edInternal = dg.datagrid('getEditor', { index: rowIndex, field: 'internal' });
                                    if (edInternal) {
                                        $(edInternal.target).numberbox('setValue', internal);
                                    }
                                }

                                var external = OKPress - internal;

                                var edExternal = dg.datagrid('getEditor', { index: rowIndex, field: 'external' });
                                if (edExternal) {
                                    $(edExternal.target).numberbox('setValue', external);
                                } else {
                                    row.external = external;
                                    dg.datagrid('refreshRow', rowIndex);
                                }
                            }
                        }
                    }
                }, {
                    field: 'external',
                    width: 100,
                    align: 'center',
                    title: "To External",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            readonly: true
                        }
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

    // function onClickCell(index, field) {
    //     if (editIndex != index) {
    //         if (endEditing()) {
    //             $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
    //             editIndex = index;
    //         } else {
    //             setTimeout(function() {
    //                 $('#dg2').datagrid('selectRow', editIndex);
    //             }, 0);
    //         }
    //     }
    // }

    function onClickCell(index, field) {
        var dg = $('#dg2');

        dg.datagrid('selectRow', index);

        if (isReadOnlyMode) {
            var row = dg.datagrid('getRows')[index];
            dg.datagrid('options').onSelect.call(dg, index, row);
            return;
        }

        if (editIndex != index) {
            if (endEditing()) {
                dg.datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    dg.datagrid('selectRow', editIndex);
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
        var process_date = $("#process_date").datebox('getValue');
        var process_name = $("#process_name").textbox('getValue');
        var doc_no = $("#doc_no").textbox('getValue');

        if (process_date != "" && process_name != "" && doc_no != "") {
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
    //     buttonClickEffect('#btn-remove');
    //     if (editIndex == undefined) {
    //         return true;
    //     }

    //     var dg = $('#dg2');
    //     var row = dg.datagrid('getSelected');
    //     var rowIndex = dg.datagrid('getRowIndex', row);

    //     var ed = dg.datagrid('getEditor', {
    //         index: editIndex,
    //         field: 'item_fg_id'
    //     });

    //     var ed1 = dg.datagrid('getEditor', {
    //         index: editIndex,
    //         field: 'workorder'
    //     });

    //     // var subcont_id = $("#subcont_id").combogrid('getValue');
    //     var item_fg_id = $(ed.target).textbox('getValue');
    //     var workorder = $(ed1.target).textbox('getValue');
    //     var doc_no = $("#doc_no").textbox('getValue');

    //     $.ajax({
    //         method: 'post',
    //         url: '<?= base_url('control/internal_process/delete') ?>',
    //         data: {
    //             doc_no: doc_no,
    //             workorder: workorder,
    //             item_fg_id: item_fg_id
    //         },
    //         success: function(result) {
    //             var result = eval('(' + result + ')');
    //             toastr.success(result.message);
    //         },
    //         error: function(jqXHR, textStatus, errorThrown) {
    //             toastr.error(jqXHR.statusText);
    //             $.messager.alert("Error", jqXHR.statusText, 'error');
    //         },
    //         complete: function(data) {
    //             $('#dg').datagrid('reload');
    //         }
    //     });

    //     $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
    //     editIndex = undefined;
    // }


    // function removeit() {
    //     buttonClickEffect('#btn-remove');

    //     var dg = $('#dg2');
    //     var row = dg.datagrid('getSelected');

    //     if (!row) {
    //         toastr.warning("Please select a row first!");
    //         return;
    //     }

    //     var index = (typeof editIndex !== 'undefined' && editIndex !== undefined)
    //         ? editIndex
    //         : dg.datagrid('getRowIndex', row);

    //     var item_fg_id = row.item_fg_id;
    //     var workorder = row.workorder;
    //     var doc_no = $("#doc_no").textbox('getValue');

    //     if (!item_fg_id || !workorder) {
    //         toastr.error("Missing data: item_fg_id or workorder not found.");
    //         return;
    //     }

    //     $.ajax({
    //         method: 'post',
    //         url: '<?= base_url('control/internal_process/delete') ?>',
    //         data: {
    //             internal_doc_no: doc_no,
    //             workorder: workorder,
    //             item_fg_id: item_fg_id
    //         },
    //         success: function(result) {
    //             try {
    //                 var result = JSON.parse(result);
    //             } catch (e) {
    //                 toastr.error("Invalid server response");
    //                 return;
    //             }

    //             if (result.status === 'error') {
    //                 toastr.error(result.message);
    //                 return;
    //             }

    //             toastr.success(result.message);
    //             dg.datagrid('deleteRow', index);
    //         },
    //         error: function(jqXHR) {
    //             toastr.error(jqXHR.statusText);
    //             $.messager.alert("Error", jqXHR.statusText, 'error');
    //         },
    //         complete: function() {
    //             $('#dg').datagrid('reload');
    //         }
    //     });
    // }


    function removeit() {
        buttonClickEffect('#btn-remove');

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');

        if (!row) return;

        var rowIndex = dg.datagrid('getRowIndex', row);

        var item_fg_id = row.item_fg_id ?? "";
        var workorder  = row.workorder ?? "";
        var doc_no     = $("#doc_no").textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/internal_process/delete') ?>',
            data: {
                internal_doc_no: doc_no,
                workorder: workorder,
                item_fg_id: item_fg_id
            },
            success: function(res) {
                try {
                    res = JSON.parse(res);

                    if(res.message == "Cannot delete data that is still in use") {
                        toastr.error(res.message);
                    }else{
                        toastr.success(res.message);
                        dg.datagrid('deleteRow', rowIndex);
                    }

                } catch (e) {
                    toastr.error("Invalid response");
                }
            },
            error: function(jqXHR) {
                toastr.error(jqXHR.statusText);
                $.messager.alert("Error", jqXHR.statusText, 'error');
            },
            complete: function() {
                $('#dg').datagrid('reload');
            }
        });
    }

    // function addTableCP(link = "", readonly = false) {
    //      isReadOnlyMode2 = readonly;
    //     $('#dg3').datagrid({
    //         url: link,
    //         fitColumns: true,
    //         singleSelect: true,
    //         columns: [
    //             [{
    //                 field: 'id',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "ID",
    //                 editor: {
    //                     type: 'textbox'
    //                 },
    //                 hidden: true
    //             }, {
    //                 field: 'item_fg_number',
    //                 width: 150,
    //                 halign: 'center',
    //                 title: "Product No",
    //                 editor: {
    //                     type: 'combogrid',
    //                     options: {
    //                         url: '<?= base_url('control/internal_process/readItemFgCP/'); ?>',
    //                         method: 'post',
    //                         required: true,
    //                         panelWidth: 750,
    //                         idField: 'number',
    //                         textField: 'number',
    //                         valueField: 'item_fg_id',
    //                         mode: 'remote',
    //                         fitColumns: true,
    //                         prompt: 'Choose Product No',
    //                         columns: [
    //                             [{
    //                                 field: 'number',
    //                                 title: 'Product No',
    //                                 halign: 'center',
    //                                 width: 200
    //                             },{
    //                                 field: 'name',
    //                                 title: 'Product Name',
    //                                 halign: 'center',
    //                                 width: 200
    //                             },{
    //                                 field: 'workorder',
    //                                 title: 'Workorder',
    //                                 halign: 'center',
    //                                 width: 150
    //                             },{
    //                                 field: 'ok_press',
    //                                 title: 'OK Press',
    //                                 halign: 'center',
    //                                 align: 'center',
    //                                 formatter: numberFormatField,
    //                                 width: 150,
    //                                 editor: {
    //                                     type: 'numberbox',
    //                                     options: {
    //                                         precision: 0,
    //                                         required: true,
    //                                     }
    //                                 }
    //                             }]
    //                         ],
    //                         onBeforeLoad: function(param) {
    //                             var dg = $('#dg3');
    //                             var rows = dg.datagrid('getRows');
    //                             param.process_date = $('#process_date2').datebox('getValue');

    //                             var used = rows
    //                                 .filter(r => r.item_fg_id && r.workorder)
    //                                 .map(r => r.item_fg_id + '_' + r.workorder);

    //                             param.exclude_keys = used.join(',');
    //                         },
    //                         onLoadSuccess: function(data) {
    //                             var dg = $('#dg3');
    //                             var row = dg.datagrid('getSelected');
    //                             if (!row) return;
    //                             var idx = dg.datagrid('getRowIndex', row);

    //                             var edId   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
    //                             var edNo   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
    //                             var edName = dg.datagrid('getEditor', { index: idx, field: 'item_fg_name' });
    //                             var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
    //                             var edOKPress   = dg.datagrid('getEditor', { index: idx, field: 'ok_press' });

    //                             if (row.item_fg_id) {
    //                                 if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
    //                                 if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
    //                                 if (edName) $(edName.target).textbox('setValue', row.item_fg_name);
    //                                 if (edWO)   $(edWO.target).textbox('setValue', row.workorder);
    //                                 if (edOKPress)   $(edOKPress.target).textbox('setValue', row.ok_press);
    //                             }
    //                         },

    //                         onSelect: function(value, rows) {
    //                             var dg = $('#dg3');
    //                             var row = dg.datagrid('getSelected');
    //                             var rowIndex = dg.datagrid('getRowIndex', row);
    //                             var ed1 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_fg_id'
    //                             });
    //                             var ed2 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_fg_number'
    //                             });
    //                             var ed3 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'item_fg_name'
    //                             });
    //                             var ed4 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'workorder'
    //                             });
    //                             var ed5 = dg.datagrid('getEditor', {
    //                                 index: rowIndex,
    //                                 field: 'ok_press'
    //                             });

    //                             $(ed1.target).textbox('setValue', rows.item_fg_id);
    //                             $(ed2.target).textbox('setValue', rows.number);
    //                             $(ed3.target).textbox('setValue', rows.name);
    //                             $(ed4.target).textbox('setValue', rows.workorder);
    //                             $(ed5.target).textbox('setValue', rows.ok_press);
    //                         },
    //                     }
    //                 }
    //             }, {
    //                 field: 'item_fg_id',
    //                 width: 200,
    //                 hidden: true,
    //                 halign: 'center',
    //                 title: "Product ID",
    //                 editor: {
    //                     type: 'textbox'
    //                 }
    //             }, {
    //                 field: 'item_fg_name',
    //                 width: 200,
    //                 halign: 'center',
    //                 title: "Product Name",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'workorder',
    //                 width: 200,
    //                 halign: 'center',
    //                 title: "WO No",
    //                 editor: {
    //                     type: 'textbox',
    //                     options: {
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'ok_press',
    //                 width: 100,
    //                 align: 'center',
    //                 title: "OK Press",
    //                 formatter: numberFormatField,
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         precision: 0,
    //                         required: true,
    //                         readonly: true
    //                     }
    //                 }
    //             }, {
    //                 field: 'ok_punch',
    //                 width: 100,
    //                 align: 'center',
    //                 title: "OK Punch",
    //                 formatter: numberFormatField,
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         precision: 0,
    //                         required: true,
    //                         onChange: function(newValue, oldValue) {
    //                             validateOkNgPunch(this);
    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'ng_punch',
    //                 width: 100,
    //                 align: 'center',
    //                 title: "NG Punch",
    //                 formatter: numberFormatField,
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         precision: 0,
    //                         required: true,
    //                         onChange: function(newValue, oldValue) {
    //                             validateOkNgPunch(this);
    //                         }
    //                     }
    //                 }
    //             }, {
    //                 field: 'punch_process',
    //                 width: 100,
    //                 align: 'center',
    //                 title: "Punch Process",
    //                 formatter: numberFormatField,
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         precision: 0,
    //                         required: true,
    //                     }
    //                 }
    //             }, {
    //                 field: 'os_cutting_punch',
    //                 width: 100,
    //                 align: 'center',
    //                 title: "OS Cutting Punch",
    //                 formatter: numberFormatField,
    //                 editor: {
    //                     type: 'numberbox',
    //                     options: {
    //                         precision: 0,
    //                         required: true,
    //                         readonly: true
    //                     }
    //                 }
    //             }]
    //         ],
    //         onClickCell: onClickCell2
    //     });
    // }


    function addTableCP(link = "", readonly = false) {
         isReadOnlyMode2 = readonly;
        $('#dg3').datagrid({
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
                            url: '<?= base_url('control/internal_process/readItemFgCP/'); ?>',
                            method: 'post',
                            required: true,
                            panelWidth: 750,
                            idField: 'number',
                            // idField: 'unique_key',
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
                                    width: 150
                                },{
                                    field: 'name',
                                    title: 'Product Name',
                                    halign: 'center',
                                    width: 150
                                },{
                                    field: 'workorder',
                                    title: 'Workorder',
                                    halign: 'center',
                                    width: 150
                                },{
                                    field: 'workorder_label',
                                    title: 'Serial WO Press',
                                    halign: 'center',
                                    width: 150
                                },{
                                    field: 'ok_press',
                                    title: 'OK Press',
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
                                var dg = $('#dg3');
                                var rows = dg.datagrid('getRows');
                                param.process_date = $('#process_date2').datebox('getValue');
                                param.workorder = $('#workorder2').combobox('getValue');
                                console.log("TEST : ", $('#workorder2').combobox('getValue'));

                                var used = rows
                                    .filter(r => r.item_fg_id && r.workorder && r.workorder_label)
                                    .map(r => r.item_fg_id + '_' + r.workorder + '_' + r.workorder_label);

                                param.exclude_keys = used.join(',');
                            },
                            onLoadSuccess: function(data) {
                                var dg = $('#dg3');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;
                                var idx = dg.datagrid('getRowIndex', row);

                                var edId   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
                                var edNo   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                var edName = dg.datagrid('getEditor', { index: idx, field: 'item_fg_name' });
                                var edWOLabel   = dg.datagrid('getEditor', { index: idx, field: 'workorder_label' });
                                var edOKPress   = dg.datagrid('getEditor', { index: idx, field: 'ok_press' });
                                var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });

                                if (row.item_fg_id) {
                                    if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
                                    if (edName) $(edName.target).textbox('setValue', row.item_fg_name);
                                    if (edWOLabel)   $(edWOLabel.target).textbox('setValue', row.workorder_label);
                                    if (edOKPress)   $(edOKPress.target).textbox('setValue', row.ok_press);
                                    if (edWO)   $(edWO.target).textbox('setValue', row.workorder);
                                }
                            },

                            onSelect: function(value, rows) {
                                var dg = $('#dg3');
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
                                    field: 'workorder_label'
                                });
                                var ed5 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'ok_press'
                                });
                                var ed6 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'workorder'
                                });

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', rows.workorder_label);
                                $(ed5.target).textbox('setValue', rows.ok_press);
                                $(ed6.target).textbox('setValue', rows.workorder);
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
                    field: 'workorder',
                    width: 200,
                    halign: 'center',
                    hidden: true,
                    title: "WO No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
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
                    field: 'workorder_label',
                    width: 200,
                    halign: 'center',
                    title: "Serial WO Press",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'ok_press',
                    width: 100,
                    align: 'center',
                    title: "OK Press",
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
                    field: 'ng_punch',
                    width: 100,
                    align: 'center',
                    title: "NG Punch",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            onChange: function(newValue, oldValue) {
                                validateOkNgPunch(this);
                            }
                        }
                    }
                }, {
                    field: 'ok_punch',
                    width: 100,
                    align: 'center',
                    title: "OK Punch",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                            readonly: true,
                            // onChange: function(newValue, oldValue) {
                            //     validateOkNgPunch(this);
                            // }
                        }
                    }
                }, {
                    field: 'punch_process',
                    width: 100,
                    hidden: true,
                    align: 'center',
                    title: "Punch Process",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                        }
                    }
                }, 
                // {
                //     field: 'os_cutting_punch',
                //     width: 100,
                //     align: 'center',
                //     title: "OS Cutting Punch",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //             required: true,
                //             readonly: true
                //         }
                //     }
                // }

                {
                    field: 'mp_punch',
                    width: 100,
                    align: 'center',
                    title: "MP Punch",
                    editor: {
                        type: 'textbox',
                        options: {
                            precision: 0,
                            required: true,
                        }
                    }
                },
                ]
            ],
            onClickCell: onClickCell2
        });
    }


    function validateOkNgPunch(target) {
        var dg = $('#dg3');
        var tr = $(target).closest('tr.datagrid-row');
        var index = parseInt(tr.attr('datagrid-row-index'));

        var ok_press_ed  = dg.datagrid('getEditor', { index: index, field: 'ok_press' });
        var ok_punch_ed  = dg.datagrid('getEditor', { index: index, field: 'ok_punch' });
        var ng_punch_ed  = dg.datagrid('getEditor', { index: index, field: 'ng_punch' });
        var punch_proc_ed = dg.datagrid('getEditor', { index: index, field: 'punch_process' });
        // var os_cut_ed     = dg.datagrid('getEditor', { index: index, field: 'os_cutting_punch' });

        var ok_press  = parseInt($(ok_press_ed.target).numberbox('getValue')) || 0;
        var ok_punch  = parseInt($(ok_punch_ed.target).numberbox('getValue')) || 0;
        var ng_punch  = parseInt($(ng_punch_ed.target).numberbox('getValue')) || 0;

        if (ng_punch > ok_press) {
            $(ng_punch_ed.target).numberbox('clear');
            toastr.warning("NG Punch must not exceed OK Press!", "Information");
            return;
        }

        var final_ok_punch = ok_press - ng_punch;
        $(ok_punch_ed.target).numberbox('setValue', final_ok_punch);

        $(punch_proc_ed.target).numberbox('setValue', ok_press);
    }

    var editIndex2 = undefined;

    function endEditing2() {
        if (editIndex2 == undefined) {
            return true
        }
        if ($('#dg3').datagrid('validateRow', editIndex2)) {
            $('#dg3').datagrid('endEdit', editIndex2);
            editIndex2 = undefined;
            return true;
        } else {
            return false;
        }
    }

    // function onClickCell2(index, field) {

    //     if (isReadOnlyMode2) {
    //         $('#dg3').datagrid('selectRow', index);
    //         return;
    //     }

    //     if (editIndex2 != index) {
    //         if (endEditing2()) {
    //             $('#dg3').datagrid('selectRow', index).datagrid('beginEdit', index);
    //             editIndex2 = index;
    //         } else {
    //             setTimeout(function() {
    //                 $('#dg3').datagrid('selectRow', editIndex2);
    //             }, 0);
    //         }
    //     }
    // }

    function onClickCell2(index, field) {
        var dg = $('#dg3');

        dg.datagrid('selectRow', index);

        if (isReadOnlyMode2) {
            var row = dg.datagrid('getRows')[index];
            dg.datagrid('options').onSelect.call(dg, index, row);
            return;
        }

        if (editIndex2 != index) {
            if (endEditing2()) {
                dg.datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex2 = index;
            } else {
                setTimeout(function() {
                    dg.datagrid('selectRow', editIndex2);
                }, 0);
            }
        }
    }

    function buttonClickEffect2(btn) {
        $(btn).addClass('btn-clicked2');
        setTimeout(() => {
            $(btn).removeClass('btn-clicked2');
        }, 300);
    }

    function append2() {
        buttonClickEffect2('#btn-add2');
        var process_date = $("#process_date2").datebox('getValue');
        var process_name = $("#process_name2").textbox('getValue');
        var doc_no = $("#doc_no2").textbox('getValue');
        var workorder = $("#workorder2").combobox('getValue');

        if (process_date != "" && process_name != "" && doc_no != "" && workorder != "") {
            if (endEditing2()) {
                $('#dg3').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex2 = $('#dg3').datagrid('getRows').length - 1;
                $('#dg3').datagrid('selectRow', editIndex2).datagrid('beginEdit', editIndex2);
            }
        } else {
            toastr.error("Please fill in all required fields first");
        }
    }

    // function removeit2() {
    //     buttonClickEffect2('#btn-remove2');

    //     var dg = $('#dg3');
    //     var row = dg.datagrid('getSelected');

    //     // jika belum ada row dipilih, tampilkan peringatan
    //     if (!row) {
    //         toastr.warning("Please select a row first!");
    //         return;
    //     }

    //     // jika sedang edit, ambil index dari editIndex2
    //     var index = (typeof editIndex2 !== 'undefined' && editIndex2 !== undefined)
    //         ? editIndex2
    //         : dg.datagrid('getRowIndex', row);

    //     // ambil nilai field langsung dari row, bukan editor
    //     var item_fg_id = row.item_fg_id;
    //     var workorder = row.workorder;
    //     var doc_no = $("#doc_no2").textbox('getValue');

    //     if (!item_fg_id || !workorder) {
    //         toastr.error("Missing data: item_fg_id or workorder not found.");
    //         return;
    //     }

    //     $.ajax({
    //         method: 'post',
    //         url: '<?= base_url('control/internal_process/delete') ?>',
    //         data: {
    //             internal_doc_no: doc_no,
    //             workorder: workorder,
    //             item_fg_id: item_fg_id
    //         },
    //         success: function(result) {
    //             try {
    //                 var result = JSON.parse(result);
    //             } catch (e) {
    //                 toastr.error("Invalid server response");
    //                 return;
    //             }

    //             if (result.status === 'error') {
    //                 toastr.error(result.message);
    //                 return;
    //             }

    //             toastr.success(result.message);
    //             dg.datagrid('deleteRow', index);
    //         },
    //         error: function(jqXHR) {
    //             toastr.error(jqXHR.statusText);
    //             $.messager.alert("Error", jqXHR.statusText, 'error');
    //         },
    //         complete: function() {
    //             $('#dg').datagrid('reload');
    //         }
    //     });
    // }

    function removeit2() {
        buttonClickEffect2('#btn-remove2');

        var dg = $('#dg3');
        var row = dg.datagrid('getSelected');

        if (!row) return;

        var rowIndex = dg.datagrid('getRowIndex', row);

        var item_fg_id = row.item_fg_id ?? "";
        var workorder  = row.workorder ?? "";
        var workorder_label  = row.workorder_label ?? "";
        var doc_no     = $("#doc_no2").textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/internal_process/delete') ?>',
            data: {
                internal_doc_no: doc_no,
                workorder: workorder,
                workorder_label: workorder_label,
                item_fg_id: item_fg_id
            },
            success: function(res) {
                try {
                    res = JSON.parse(res);

                    if(res.message == "Cannot delete data that is still in use") {
                        toastr.error(res.message);
                    }else{
                        toastr.success(res.message);
                        dg.datagrid('deleteRow', rowIndex);
                    }
                } catch (e) {
                    toastr.error("Invalid response");
                }
            },
            error: function(jqXHR) {
                toastr.error(jqXHR.statusText);
                $.messager.alert("Error", jqXHR.statusText, 'error');
            },
            complete: function() {
                $('#dg').datagrid('reload');
            }
        });
    }

    //EDIT DATA
    function update_cp() {
        var row = $('#dg').treegrid('getSelected');

        if(row.punch_process > 0) {
            if (row) {
                $('#dlg_insert2').dialog('open');
                $('#frm_insert2').form('load', row);

                setTimeout(function() {
                    $('#process_date2').datebox('setValue', row.process_date);
                    $('#process_name2').textbox('setValue', row.process_name);
                    $('#workorder2').combobox('setValue', row.workorder);
                    $('#doc_no2').textbox('setValue', row.doc_no);
                }, 200);

                $("#process_date2").datebox('disable');
                $("#process_name2").textbox('disable');
                $("#doc_no2").textbox('disable');
                $("#workorder2").combobox('disable');

                $('#btn-add2').hide();

                addTableCP('<?= base_url('control/internal_process/datatableUpdates?doc_no=') ?>' + window.btoa(row.doc_no), true);
            } else {
                toastr.warning("Please select one of the data in the table first!", "Information");
            }
        }else{
                toastr.warning("Can only update the Cutting Punch", "Information");
        }
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');

        if(row.internal > 0) {
            if (row) {
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);

                setTimeout(function() {
                    $('#process_date').datebox('setValue', row.process_date);
                    $('#process_name').textbox('setValue', row.process_name);
                    $('#doc_no').textbox('setValue', row.doc_no);
                }, 200);

                $("#process_date").datebox('disable');
                $("#process_name").textbox('disable');
                $("#doc_no").textbox('disable');

                $('#btn-add').hide();

                addTable('<?= base_url('control/internal_process/datatableUpdates?doc_no=') ?>' + window.btoa(row.doc_no), true);
            } else {
                toastr.warning("Please select one of the data in the table first!", "Information");
            }
        } else {
                toastr.warning("Can only update the Internal Finishing", "Information");
        }
    }

    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        console.log('ROWSS : ', rows);
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        console.log(row);
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('control/internal_process/delete') ?>',
                            data: {
                                internal_doc_no: row.doc_no,
                                item_fg_id: row.item_fg_id,
                                workorder: row.workorder,
                                workorder_label: row.workorder_label,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');

                                if(result.status === 'error'){
                                    toastr.error(result.message);
                                    return;
                                }

						        toastr.success(result.message);
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("Delete failed: " + jqXHR.statusText);
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
        var filter_process_name = $("#filter_process_name").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_process_name=" + window.btoa(filter_process_name) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_doc_no=" + window.btoa(filter_doc_no);


        // definisi kolom dinamis
        var columns;
        if (filter_process_name === "Cutting Punch") {
            columns = [[
                { field: 'ck', checkbox: true, rowspan: 2 },
                { field: 'process_name', title: 'Process Name', width: 230, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'process_date', title: 'Process Date', width: 230, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'doc_no', title: 'Document No', width: 230, sortable: true, rowspan: 2, halign: 'center' },
                { title: 'Created', colspan: 2, halign: 'center' },
                { title: 'Updated', colspan: 2, halign: 'center' }
            ],[
                { field: 'created_by', title: 'By', width: 170, align: 'center', sortable: true },
                { field: 'created_date', title: 'Date', width: 200, align: 'center', sortable: true },
                { field: 'updated_by', title: 'By', width: 170, align: 'center', sortable: true },
                { field: 'updated_date', title: 'Date', width: 200, align: 'center', sortable: true }
            ]];
        } else if (filter_process_name === "Internal Finishing") {
            columns = [[
                { field: 'ck', checkbox: true, rowspan: 2 },
                { field: 'process_name', title: 'Process Name', width: 230, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'process_date', title: 'Process Date', width: 230, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'doc_no', title: 'Document No', width: 230, sortable: true, rowspan: 2, halign: 'center' },
                { title: 'Created', colspan: 2, halign: 'center' },
                { title: 'Updated', colspan: 2, halign: 'center' }
            ],[
                { field: 'created_by', title: 'By', width: 170, align: 'center', sortable: true },
                { field: 'created_date', title: 'Date', width: 200, align: 'center', sortable: true },
                { field: 'updated_by', title: 'By', width: 170, align: 'center', sortable: true },
                { field: 'updated_date', title: 'Date', width: 200, align: 'center', sortable: true }
            ]];
        }

        // rebuild datagrid
        $('#dg').datagrid({
            url: '<?= base_url('control/internal_process/datatables') ?>' + url,
            columns: columns,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/internal_process/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_process_name = $("#filter_process_name").combobox('getValue');
        var filter_item_fg = $("#filter_item_fg").combogrid('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_process_name=" + window.btoa(filter_process_name) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_doc_no=" + window.btoa(filter_doc_no);

        window.location.assign('<?= base_url('control/internal_process/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {

        $('#dg').datagrid({
            url: '<?= base_url('control/internal_process/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            fitColumns: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.doc_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                var filter_process_name = $("#filter_process_name").combobox('getValue');
                var filter_item_fg = $('#filter_item_fg').combogrid('getValue');

                if(filter_process_name == "Cutting Punch") {

                    ddv.datagrid({
                        url: '<?= base_url('control/internal_process/datatableDetails?doc_no=') ?>' + window.btoa(row.doc_no) + '&filter_item_fg=' + window.btoa(filter_item_fg),
                        singleSelect: true,
                        rownumbers: true,
                        fitColumns: true,
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
                                field: 'workorder_label',
                                title: 'Serial WO No',
                                halign: 'center',
                                width: 200
                            }, {
                                field: 'ok_press',
                                title: 'OK Press',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
                            }, {
                                field: 'punch_process',
                                title: 'Punch Process',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
                            }, {
                                field: 'ok_punch',
                                title: 'OK Punch',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
                            }, {
                                field: 'ng_punch',
                                title: 'NG Punch',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
                            },
                            // {
                            //     field: 'os_cutting_punch',
                            //     title: 'OS Cutting Punch',
                            //     halign: 'center',
                            //     align: 'right',
                            //     width: 100,
                            //     formatter: numberFormat
                            // }
                            ]
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

                } else if (filter_process_name == "Internal Finishing") {

                    ddv.datagrid({
                        url: '<?= base_url('control/internal_process/datatableInternalDetails?doc_no=') ?>' + window.btoa(row.doc_no) + '&filter_item_fg=' + window.btoa(filter_item_fg),
                        singleSelect: true,
                        rownumbers: true,
                        fitColumns: true,
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
                                field: 'ok_press',
                                title: 'OK Press',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
                            }, {
                                field: 'internal',
                                title: 'Internal',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
                            }, {
                                field: 'external',
                                title: 'External',
                                halign: 'center',
                                align: 'right',
                                width: 100,
                                formatter: numberFormat
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
                }

                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        function reloadDocNoCombo() {
            var filter_froms = $("#filter_from").datebox("getValue");
            var filter_tos   = $("#filter_to").datebox("getValue");
            var filter_process_name  = $("#filter_process_name").combobox("getValue");

            var url = '<?= base_url('control/internal_process/readDocNo'); ?>'
                    + '?filter_from=' + encodeURIComponent(filter_froms)
                    + '&filter_to=' + encodeURIComponent(filter_tos)
                    + '&filter_process_name=' + encodeURIComponent(filter_process_name);

            $('#filter_doc_no').combobox('reload', url);
        }

        $('#filter_doc_no').combobox({
            valueField: 'doc_no',
            textField: 'doc_no',
            prompt: 'Choose All',
            editable: false,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        reloadDocNoCombo();

        $('#filter_from, #filter_to').datebox({
            onChange: function() {
                reloadDocNoCombo();
            }
        });

        $('#filter_process_name').combobox({
            onChange: function(newValue, oldValue) {
                reloadDocNoCombo();
            }
        });


        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var process_date = $("#process_date").datebox('getValue');
                    var process_name = $("#process_name").textbox('getValue');
                    var doc_no = $("#doc_no").textbox('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();                    

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('control/internal_process/create') ?>',
                                data: {
                                    id: rows[i].id,
                                    item_fg_id: rows[i].item_fg_id,
                                    process_date: process_date,
                                    process_name: process_name,
                                    doc_no: doc_no,
                                    workorder: rows[i].workorder,
                                    ok_press: rows[i].ok_press,
                                    internal: rows[i].internal,
                                    external: rows[i].external
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


        //SAVE DATA
        $('#dlg_insert2').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var process_date = $("#process_date2").datebox('getValue');
                    var process_name = $("#process_name2").textbox('getValue');
                    var doc_no = $("#doc_no2").textbox('getValue');

                    var rows = $('#dg3').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing2();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('control/internal_process/create2') ?>',
                                data: {
                                    id: rows[i].id,
                                    item_fg_id: rows[i].item_fg_id,
                                    process_date: process_date,
                                    process_name: process_name,
                                    doc_no: doc_no,
                                    workorder: rows[i].workorder,
                                    workorder_label: rows[i].workorder_label,
                                    ok_press: rows[i].ok_press,
                                    punch_process: rows[i].punch_process,
                                    ok_punch: rows[i].ok_punch,
                                    ng_punch: rows[i].ng_punch,
                                    os_cutting_punch: 0, //rows[i].os_cutting_punch
                                    mp_punch: rows[i].mp_punch,
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
                    $('#dlg_insert2').dialog('close');
                }
            }]
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

    function regenerateDocNoInternal() {
        let process_date = $('#process_date').datebox('getValue');
        let process_name = $('#process_name').textbox('getValue');

        if (process_date && process_name) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/internal_process/doc_no_internal') ?>",
                data: { process_date: process_date, process_name: process_name },
                dataType: "html",
                success: function(result) {
                    $("#doc_no").textbox('setValue', result);
                }
            });
        }
    }

    function regenerateDocNoCP() {
        let process_date = $('#process_date2').datebox('getValue');
        // let process_name = $('#process_name2').textbox('getValue');

        if (process_date) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/internal_process/doc_no_cp') ?>",
                data: { process_date: process_date },
                dataType: "html",
                success: function(result) {
                    $("#doc_no2").textbox('setValue', result);
                }
            });
        }
    }

    $("#workorder2").combobox({
        url: '<?= base_url('control/internal_process/readWorkorder') ?>',
        valueField: 'workorder',
        textField: 'workorder',
        prompt: "Select Workorder",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
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

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }

</script>