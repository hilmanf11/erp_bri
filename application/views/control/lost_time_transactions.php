<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'period',width:150,halign:'center',align:'center'">Period</th>
            <th rowspan="2" data-options="field:'trans_date',width:200,align:'center'">Production Date</th>
            <th rowspan="2" data-options="field:'number',width:200,align:'center'">Document No</th>
            <th rowspan="2" data-options="field:'shift',width:100,align:'center'">Shift</th>
            <th rowspan="2" data-options="field:'wp',width:100,align:'center'">WP No</th>
            <th rowspan="2" data-options="field:'pic',width:150,align:'center'">PIC</th>

            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:155,align:'center'"> By</th>
            <th data-options="field:'created_date',width:160,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:155,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:160,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 230px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 33.33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" name="filter_period" id="filter_period" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Production Date</span>
                    <input style="width:29.7%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:29.7%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>

                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Production Date</span>
                    <input style="width:60%;" id="filter_trans_date" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lost Time Doc No</span>
                    <input style="width:60%;" id="filter_number" class="easyui-combobox">
                </div>
            </div>

            <div style="width: 33.33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP No</span>
                    <input style="width:60%;" id="filter_wp" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WO No</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div>
            </div>

            <div style="width: 33.33%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="padding:10px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" name="period" id="period" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Production Date</span>
                    <input style="width:60%;" name="trans_date" id="trans_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Output Doc No</span>
                    <input style="width:60%;" name="number_output" id="number_output" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lost Time Doc No</span>
                    <input style="width:60%;" name="number" id="number" class="easyui-textbox" readonly required>
                </div>
            </div>
            <div style="float: left; width:48%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP No</span>
                    <input style="width:60%;" name="wp" id="wp" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" name="shift" id="shift" class="easyui-combobox" panelHeight="auto" editable="false" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PIC</span>
                    <input style="width:60%;" name="pic" id="pic" class="easyui-textbox">
                </div>
            </div>

        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Product Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 550px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/lost_time_transactions/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    let currentMode = '';

    //ADD DATA
    function add() {
        currentMode = 'add';
        $('#dlg_insert').dialog({
            title: 'Add New',
            modal: true,
            closed: false,
            maximized: true,
            resizable: true,
        }).dialog('open');

        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('control/lost_time_transactions/create') ?>';
        $('#frm_insert').form('clear');
        $("#trans_date").datebox('enable');
        $("#number").textbox('enable');
        $("#number_output").combobox('enable');
        $("#period").combobox('enable');
        $("#wp").combobox('enable');
        $("#shift").combobox('enable');

        $("#trans_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                var d = $.fn.datebox.defaults.formatter(date);
                autonumber(d);

                var dd = date.getFullYear() + '-' + 
                ('0' + (date.getMonth()+1)).slice(-2) + '-' + 
                ('0' + date.getDate()).slice(-2);

                $("#number_output").combobox({
                    url: '<?= base_url('control/lost_time_transactions/readOutputDocNo?trans_date=') ?>' + btoa(dd),
                    valueField: 'number',
                    textField: 'number',
                    prompt: "Select Output Doc No",
                });
            }
        });

        var today = "<?= date('Y-m-d') ?>";
        $("#trans_date").datebox('setValue', today);
        autonumber(today);

        $("#number_output").combobox({
            url: '<?= base_url('control/lost_time_transactions/readOutputDocNo?trans_date=') ?>' + btoa(today),
            valueField: 'number',
            textField: 'number',
            prompt: "Select Output Doc No",
            onSelect: function (data) {
                var period = data.period;
                var wp_value = data.wp;
                var shift_value = data.shift;
                var pic_value = data.pic;

                // $("#wp").combobox({
                //     url: '<?= base_url('control/lost_time_transactions/readWp?period=') ?>' + btoa(period),
                //     valueField: 'wp',
                //     textField: 'wp',
                //     prompt: "Select WP",
                //     onLoadSuccess: function () {
                //         if (wp_value) {
                //             $("#wp").combobox('setValue', wp_value);
                //         }
                //     }
                // });

                // $("#period").combobox('setValue', period);

                $("#wp").combobox({
                    url: '<?= base_url('control/lost_time_transactions/readWp?period=') ?>' + btoa(period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP",
                    onLoadSuccess: function () {
                        var opts = $(this).combobox('getData');
                        var exists = opts.some(o => o.wp === wp_value);
                        if (exists) {
                            $("#wp").combobox('setValue', wp_value);
                        } else {
                            $("#wp").combobox('clear');
                        }
                    }
                });

                $("#shift").combobox({
                    data: [
                        { value: "1", text: "1" },
                        { value: "2", text: "2" },
                        { value: "3", text: "3" }
                    ],
                    valueField: 'value',
                    textField: 'text',
                    editable: false,
                    panelHeight: 'auto',
                    onLoadSuccess: function () {
                        var opts = $(this).combobox('getData');
                        var exists = opts.some(o => o.value == shift_value);
                        if (exists) {
                            $("#shift").combobox('setValue', shift_value);
                        } else {
                            $("#shift").combobox('clear');
                        }
                    }
                });

                $("#pic").textbox('setValue', pic_value);

            } 
        });

        $("#period").combobox({
            url: '<?= base_url('control/lost_time_transactions/readPeriod') ?>',
            valueField: 'period',
            textField: 'period',
            prompt: "Select Period",
            onLoadSuccess: function(data) {
                var defaultVal = "<?= date("Ym") ?>";
                $("#period").combobox('setValue', defaultVal);
                $("#period").combobox('select', defaultVal);
            },
            onSelect: function (data) {
                var period = data.period;
                var wp_date = data.wp;

                $("#wp").combobox({
                    url: '<?= base_url('control/lost_time_transactions/readWp?period=') ?>' + btoa(period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP"
                });
            }            
        });
    }

    function autonumber(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('control/lost_time_transactions/autonumber') ?>",
            data: { trans_date: trans_date },
            dataType: "html",
            success: function(result) {
                $("#number").textbox('setValue', result);
            }
        });
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            fit: true,
            fitColumns: true,
            columns: [
                [{
                    field: 'id',
                    width: 150,
                    halign: 'center',
                    rowspan: 2,
                    title: "ID",
                    editor: {
                        type: 'textbox'
                    },
                    hidden: true
                }, {
                    field: 'machine_number',
                    width: 100,
                    rowspan: 2,
                    halign: 'center',
                    align: 'center',
                    title: "Machine No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readMachinePressByWP'); ?>',
                            method: 'post',
                            required: true,
                            panelWidth: 300,
                            idField: 'number',
                            textField: 'number',
                            valueField: 'machine_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Machine No',
                            columns: [[
                                { field: 'number', title: 'Machine No', width: 150 },
                                { field: 'name', title: 'Machine Name', width: 150 }
                            ]],
                            onBeforeLoad: function(param) {
                                param.period = window.btoa($('#period').textbox('getValue'));
                                param.number_output = window.btoa($('#number_output').combobox('getValue'));
                                param.wp = window.btoa($('#wp').textbox('getValue'));
                                param.shift = window.btoa($('#shift').textbox('getValue'));
                            },
                            onLoadSuccess: function(data) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;
                                var idx = dg.datagrid('getRowIndex', row);

                                var edId = dg.datagrid('getEditor', { index: idx, field: 'machine_id' });
                                var edNo = dg.datagrid('getEditor', { index: idx, field: 'machine_number' });

                                if (edId && edNo) {
                                    if (row.machine_id) {
                                        $(edId.target).textbox('setValue', row.machine_id);
                                        $(edNo.target).combogrid('setValue', row.machine_number);
                                    }
                                }

                                var edWO = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                if (edWO) {
                                    // reload WO sesuai machine terpilih
                                    $(edWO.target).combogrid('grid').datagrid('load', {
                                        machine_id: window.btoa(row.machine_id),
                                        period: window.btoa($('#period').textbox('getValue')),
                                        wp: window.btoa($('#wp').textbox('getValue')),
                                        shift: window.btoa($('#shift').textbox('getValue'))
                                    });
                                    
                                }
                            },
                            onSelect: function(index, row) {
                                var dg = $('#dg2');
                                var r = dg.datagrid('getSelected');
                                var idx = dg.datagrid('getRowIndex', r);

                                var ed = dg.datagrid('getEditor', {
                                    index: idx,
                                    field: 'machine_id'
                                });

                                if (ed) $(ed.target).textbox('setValue', row.machine_id);

                                r.machine_id = row.machine_id;
                                r.machine_number = row.number;
                                dg.datagrid('updateRow', { index: idx, row: r });

                                var edWO = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                if (edWO) {
                                    // reload WO sesuai machine terpilih
                                    $(edWO.target).combogrid('grid').datagrid('load', {
                                        machine_id: window.btoa(row.machine_id),
                                        period: window.btoa($('#period').textbox('getValue')),
                                        wp: window.btoa($('#wp').textbox('getValue')),
                                        shift: window.btoa($('#shift').textbox('getValue')),
                                    });
                                }
                            },
                            onHidePanel: function() {
                                var t = $(this).combogrid('getText');
                                var g = $(this).combogrid('grid');
                                var rows = g.datagrid('getRows');
                                var exists = false;

                                for (var i = 0; i < rows.length; i++) {
                                    if (rows[i].number === t) {
                                        exists = true;
                                        break;
                                    }
                                }

                                if (!exists) {
                                    $(this).combogrid('setValue', '');
                                    var grid = $(this).combogrid('grid');
                                    grid.datagrid('load', {});
                                }
                            }
                        }
                    }
                }, {
                    field: 'item_fg_number',
                    width: 150,
                    rowspan: 2,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readItemFg/'); ?>',
                            required: true,
                            panelWidth: 750,
                            idField: 'item_fg_id',
                            textField: 'number',
                            // valueField: 'item_fg_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 200
                                }, {
                                    field: 'workorder',
                                    title: 'Workorder',
                                    width: 150
                                }, {
                                    field: 'operator',
                                    title: 'Operator Name',
                                    width: 150
                                }]
                            ],
                            onBeforeLoad: function(param) {
                                param.period = window.btoa($('#period').textbox('getValue'));
                                param.wp = window.btoa($('#wp').textbox('getValue'));
                                param.shift = window.btoa($('#shift').textbox('getValue'));
                                const dg = $('#dg2');
                                const row = dg.datagrid('getSelected');
                                param.machine_id = row && row.machine_id ? window.btoa(row.machine_id) : '';
                            },
                            onLoadSuccess: function(data) {
                                var rows = Array.isArray(data) ? data : (data.rows || []);
                                if (rows.length === 0) return;

                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                if (!row) return;
                                var idx = dg.datagrid('getRowIndex', row);

                                var edId   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
                                var edNo   = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                var edWO   = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
                                var edOperator   = dg.datagrid('getEditor', { index: idx, field: 'operator' });

                                var number_output = $('#number_output').combobox('getValue');
                                var wp = $('#wp').textbox('getValue');
                                var shift = $('#shift').textbox('getValue');
                                var period = $('#period').textbox('getValue');

                                // cari record cocok
                                var match = rows.find(function(r) {
                                    return (
                                        (r.number_output == number_output || r.item_fg_number == number_output) &&
                                        r.wp == wp &&
                                        r.shift == shift &&
                                        r.period == period
                                    );
                                });

                                if (match) {
                                    var grid = $(edNo.target).combogrid('grid');
                                    grid.datagrid('selectRecord', match.item_fg_id);

                                    if (edId) $(edId.target).textbox('setValue', match.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', match.number || match.item_fg_number);
                                    if (edWO) $(edWO.target).textbox('setValue', match.workorder);
                                    if (edOperator) $(edOperator.target).textbox('setValue', match.operator);
                                } else if (rows.length === 1) {
                                    var item = rows[0];
                                    var grid = $(edNo.target).combogrid('grid');
                                    grid.datagrid('selectRecord', item.item_fg_id);
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
                                    field: 'operator'
                                });

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).textbox('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.workorder);
                                $(ed4.target).textbox('setValue', rows.operator);
                            },
                            onHidePanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const text = cg.combogrid('getText').trim();
                                const rows = g.datagrid('getRows');
                                const exists = rows.some(r => r.number === text);

                                if (!exists && text !== '') {
                                    cg.combogrid('setValue', '');
                                    // g.datagrid('loadData', { total: 0, rows: [] }); // reset list

                                    var grid = $(this).combogrid('grid');
                                    grid.datagrid('load', {});
                                }
                            }

                        }
                    }
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'lt_trial_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "LT Trial ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'lt_machine_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "LT Machine ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'lt_material_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "LT Material ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'lt_methode_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "LT Methode ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'lt_man_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "LT Man ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'machine_id',
                    width: 150,
                    rowspan: 2,
                    hidden: true,
                    halign: 'center',
                    title: "Machine ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'workorder',
                    width: 110,
                    rowspan: 2,
                    halign: 'center',
                    align: 'center',
                    title: "WO No",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'operator',
                    width: 110,
                    rowspan: 2,
                    halign: 'center',
                    align: 'center',
                    title: "Operator Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    title: 'Planned Lost Time', 
                    colspan: 4,
                    halign: 'center', 
                    align: 'center' 
                }, {
                    title: 'Unplanned Lost Time', 
                    colspan: 8,
                    halign: 'center', 
                    align: 'center' 
                },
                ],

                [{
                    field: 'cleaning_mold',
                    width: 90,
                    align: 'center',
                    title: "Cleaning Mold <br>(minutes)",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,

                        }
                    }
                }, {
                    field: 'changing_mold',
                    width: 90,
                    align: 'center',
                    title: "Changing Mold <br>(minutes)",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,                            
                        }
                    }
                }, {
                    field: 'lt_trial',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "Trial Project ",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readLTFactors'); ?>',
                            method: 'post',
                            panelWidth: 350,
                            idField: 'detail',
                            textField: 'detail',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Trial Project Detail',
                            columns: [[
                                { field: 'detail', title: 'Detail', width: 200 },
                                { field: 'category', title: 'Category', width: 150 },
                            ]],
                            onBeforeLoad: function(param) {
                                param.factor = "TRIAL PROJECT";
                            },
                            onSelect: function(index, row) {
                                var dg = $('#dg2');
                                var selected = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', selected);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lt_trial_id'
                                });

                                if (ed) {
                                    $(ed.target).textbox('setValue', row.id);
                                }
                            },
                            onHidePanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const text = cg.combogrid('getText').trim();
                                const rows = g.datagrid('getRows');
                                const exists = rows.some(r => r.detail === text);

                                if (!exists && text !== '') {
                                    cg.combogrid('clear');
                                }
                            },
                            onShowPanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const rows = g.datagrid('getRows');

                                if (!rows || rows.length === 0) {
                                    const opts = cg.combogrid('options');
                                    const param = { factor: "TRIAL PROJECT" };
                                    g.datagrid('load', param);
                                }
                            }

                        }
                    }
                }, {
                    field: 'trial_duration',
                    width: 75,
                    align: 'center',
                    title: "Duration <br>(minutes)",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                        }
                    }
                }, {
                    field: 'lt_machine',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "Machine",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readLTFactors'); ?>',
                            method: 'post',
                            panelWidth: 350,
                            idField: 'detail',
                            textField: 'detail',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Machine Detail',
                            columns: [[
                                { field: 'detail', title: 'Detail', width: 200 },
                                { field: 'category', title: 'Category', width: 150 },
                            ]],
                            onBeforeLoad: function(param) {
                                param.factor = "MACHINE";
                            },
                            onSelect: function(index, row) {
                                var dg = $('#dg2');
                                var selected = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', selected);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lt_machine_id'
                                });

                                if (ed) {
                                    $(ed.target).textbox('setValue', row.id);
                                }
                            },
                            onHidePanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const text = cg.combogrid('getText').trim();
                                const rows = g.datagrid('getRows');
                                const exists = rows.some(r => r.detail === text);

                                if (!exists && text !== '') {
                                    cg.combogrid('clear');
                                }
                            },
                            onShowPanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const rows = g.datagrid('getRows');

                                if (!rows || rows.length === 0) {
                                    const opts = cg.combogrid('options');
                                    const param = { factor: "MACHINE" };
                                    g.datagrid('load', param);
                                }
                            }

                        }
                    }
                },


                {
                    field: 'machine_duration',
                    width: 75,
                    align: 'center',
                    title: "Duration <br>(minutes)",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                        }
                    }
                }, {
                    field: 'lt_material',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "Material",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readLTFactors'); ?>',
                            method: 'post',
                            panelWidth: 350,
                            idField: 'detail',
                            textField: 'detail',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Material Detail',
                            columns: [[
                                { field: 'detail', title: 'Detail', width: 200 },
                                { field: 'category', title: 'Category', width: 150 },
                            ]],
                            onBeforeLoad: function(param) {
                                param.factor = "MATERIAL";
                            },
                            onSelect: function(index, row) {
                                var dg = $('#dg2');
                                var selected = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', selected);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lt_material_id'
                                });

                                if (ed) {
                                    $(ed.target).textbox('setValue', row.id);
                                }
                            },
                            onHidePanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const text = cg.combogrid('getText').trim();
                                const rows = g.datagrid('getRows');
                                const exists = rows.some(r => r.detail === text);

                                if (!exists && text !== '') {
                                    cg.combogrid('clear');
                                }
                            },
                            onShowPanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const rows = g.datagrid('getRows');

                                if (!rows || rows.length === 0) {
                                    const opts = cg.combogrid('options');
                                    const param = { factor: "MATERIAL" };
                                    g.datagrid('load', param);
                                }
                            }
                        }
                    }
                }, {
                    field: 'material_duration',
                    width: 75,
                    align: 'center',
                    title: "Duration <br>(minutes)",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                        }
                    }
                }, {
                    field: 'lt_methode',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "Methode",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readLTFactors'); ?>',
                            method: 'post',
                            panelWidth: 350,
                            idField: 'detail',
                            textField: 'detail',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Methode Detail',
                            columns: [[
                                { field: 'detail', title: 'Detail', width: 200 },
                                { field: 'category', title: 'Category', width: 150 },
                            ]],
                            onBeforeLoad: function(param) {
                                param.factor = "METHODE";
                            },
                            onSelect: function(index, row) {
                                var dg = $('#dg2');
                                var selected = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', selected);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lt_methode_id'
                                });

                                if (ed) {
                                    $(ed.target).textbox('setValue', row.id);
                                }
                            },
                            onHidePanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const text = cg.combogrid('getText').trim();
                                const rows = g.datagrid('getRows');
                                const exists = rows.some(r => r.detail === text);

                                if (!exists && text !== '') {
                                    cg.combogrid('clear');
                                }
                            },
                            onShowPanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const rows = g.datagrid('getRows');

                                if (!rows || rows.length === 0) {
                                    const opts = cg.combogrid('options');
                                    const param = { factor: "METHODE" };
                                    g.datagrid('load', param);
                                }
                            }
                        }
                    }
                }, {
                    field: 'methode_duration',
                    width: 75,
                    align: 'center',
                    title: "Duration <br>(minutes)",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                        }
                    }
                }, {
                    field: 'lt_man',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "Man",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/lost_time_transactions/readLTFactors'); ?>',
                            method: 'post',
                            panelWidth: 350,
                            idField: 'detail',
                            textField: 'detail',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Man Detail',
                            columns: [[
                                { field: 'detail', title: 'Detail', width: 200 },
                                { field: 'category', title: 'Category', width: 150 },
                            ]],
                            onBeforeLoad: function(param) {
                                param.factor = "MAN";
                            },
                            onSelect: function(index, row) {
                                var dg = $('#dg2');
                                var selected = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', selected);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'lt_man_id'
                                });

                                if (ed) {
                                    $(ed.target).textbox('setValue', row.id);
                                }
                            },
                            onHidePanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const text = cg.combogrid('getText').trim();
                                const rows = g.datagrid('getRows');
                                const exists = rows.some(r => r.detail === text);

                                if (!exists && text !== '') {
                                    cg.combogrid('clear');
                                }
                            },
                            onShowPanel: function() {
                                const cg = $(this);
                                const g = cg.combogrid('grid');
                                const rows = g.datagrid('getRows');

                                if (!rows || rows.length === 0) {
                                    const opts = cg.combogrid('options');
                                    const param = { factor: "MAN" };
                                    g.datagrid('load', param);
                                }
                            }
                        }
                    }
                }, {
                    field: 'man_duration',
                    width: 75,
                    align: 'center',
                    title: "Duration <br>(minutes)",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                        }
                    }
                },
            ]
            ],
            onClickCell: onClickCell,
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
        var period = $("#period").combobox('getValue');
        var number_output = $("#number_output").combobox('getValue');
        var wp = $("#wp").combobox('getValue');
        var shift = $("#shift").combobox('getValue');

        if (period != "" && number_output != "" && wp != "" && shift != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {});
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.warning("Please fill in all required fields");
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
            field: 'id'
        });

        var id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('control/lost_time_transactions/delete') ?>',
            data: {
                id: id,
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
        currentMode = 'update';
        var row = $('#dg').treegrid('getSelected');
        console.log('Row : ', row);

        if (row) {
            $('#dlg_insert').dialog({
                title: 'Edit Data',
                modal: true,
                closed: false,
                maximized: true,
                resizable: true,
            }).dialog('open');

            $('#frm_insert').form('load', row);
            $("#trans_date").datebox('disable');
            $("#number").textbox('disable');
            $("#number_output").combobox('disable');
            $("#period").combobox('disable');
            $("#wp").combobox('disable');
            $("#shift").combobox('disable');

            addTable('<?= base_url('control/lost_time_transactions/datatableUpdates?number=') ?>' + window.btoa(row.number));
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
                            url: '<?= base_url('control/lost_time_transactions/delete') ?>',
                            data: {
                                number: row.number,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
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
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('control/lost_time_transactions/exportTemplate') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_period = $("#filter_period").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_period=" + filter_period + "&filter_from=" + filter_from + "&filter_to=" + filter_to +
        "&filter_number=" + filter_number + "&filter_shift=" + filter_shift + "&filter_wp=" + filter_wp + "&filter_workorder=" + filter_workorder + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_category=" + filter_category + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('control/lost_time_transactions/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/lost_time_transactions/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_period = $("#filter_period").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_period=" + filter_period + "&filter_from=" + filter_from + "&filter_to=" + filter_to +
        "&filter_number=" + filter_number + "&filter_shift=" + filter_shift + "&filter_wp=" + filter_wp + "&filter_workorder=" + filter_workorder + "&filter_item_fg_id=" + "&filter_category=" + filter_category + filter_item_fg_id + "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('control/lost_time_transactions/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('control/lost_time_transactions/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            // fitColumns: true,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.number + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_workorder = $("#filter_workorder").combobox('getValue');
                var filter_category = $("#filter_category").combobox('getValue');
                var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('control/lost_time_transactions/datatableDetails?number=') ?>' + window.btoa(row.number) + "&filter_workorder=" + window.btoa(filter_workorder) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) + "&filter_category=" + window.btoa(filter_category),
                    singleSelect: true,
                    rownumbers: true,
                    // fitColumns: true,
                    columns: [
                        [{
                            field: 'machine_number',
                            title: 'Machine No',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 80,
                        }, {
                            field: 'mold_id',
                            title: 'Mold Id',
                            rowspan: 2,
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No',
                            rowspan: 2,
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            rowspan: 2,
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'planning_qty_shift',
                            title: 'Plan/shift (pcs)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 135,
                            formatter: numberformat
                        }, {
                            field: 'workorder',
                            title: 'WO No',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 150
                        }, {
                            field: 'operator',
                            title: 'Operator Name',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 130
                        }, {   
                            title: 'Planned Lost Time', 
                            colspan: 5,
                            halign: 'center', 
                            align: 'center' 
                        }, {
                            title: 'Unplanned Lost Time', 
                            colspan: 12,
                            halign: 'center', 
                            align: 'center' 
                        }],

                        [{
                            field: 'cleaning_mold',
                            width: 130,
                            align: 'center',
                            title: "Cleaning Mold <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'changing_mold',
                            width: 130,
                            align: 'center',
                            title: "Changing Mold <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, 
                        {
                            field: 'lt_trial',
                            title: 'Trial',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'lt_trial_category',
                            title: 'Category',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'trial_duration',
                            width: 100,
                            align: 'center',
                            title: "Duration <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        },
                        
                        {
                            field: 'lt_machine',
                            title: 'Machine',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'lt_machine_category',
                            title: 'Category',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'machine_duration',
                            width: 100,
                            align: 'center',
                            title: "Duration <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'lt_material',
                            title: 'Material',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'lt_material_category',
                            title: 'Category',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'material_duration',
                            width: 100,
                            align: 'center',
                            title: "Duration <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'lt_methode',
                            title: 'Methode',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'lt_methode_category',
                            title: 'Category',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'methode_duration',
                            width: 100,
                            align: 'center',
                            title: "Duration <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'lt_man',
                            title: 'Man',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'lt_man_category',
                            title: 'Category',
                            align: 'center',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'man_duration',
                            width: 100,
                            align: 'center',
                            title: "Duration <br>(minutes)",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function(data) {
                        setTimeout(function() {
                            // console.log('Data : ', data.rows);
                            $('#dg').datagrid('fixDetailRowHeight', index);

                            var rows = ddv.datagrid('getRows');
                            for (var i = 0; i < rows.length; i++) {
                                var r = rows[i];
                                if (r.machine_id && (!r.item_fg_id || r.item_fg_id === null || r.item_fg_id === "")) {
                                    var row = ddv.datagrid('getPanel').find('tr[datagrid-row-index="' + i + '"]');
                                    row.css('background-color', '#ffcccc');
                                }
                            }
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        // SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var period = $("#period").combobox('getValue');
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number_output = $("#number_output").combobox('getValue');
                    var number = $("#number").textbox('getValue');
                    var wp = $("#wp").combobox('getValue');
                    var shift = $("#shift").combobox('getValue');
                    var pic = $("#pic").textbox('getValue');

                    if (!trans_date || !number_output || !number || !period || !wp || !shift) {
                        toastr.error("Please complete all required fields before saving");
                        return;
                    }

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    console.log(JSON.stringify(rows));
                    

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {

                            var dataFinal = {
                                period: period,
                                trans_date: trans_date,
                                number_output: number_output,
                                number: number,
                                wp: wp,
                                shift: shift,
                                pic: pic,
                                id: rows[i].id,
                                machine_id: rows[i].machine_id,
                                item_fg_id: rows[i].item_fg_id,
                                workorder: rows[i].workorder,
                                operator: rows[i].operator,
                                cleaning_mold: rows[i].cleaning_mold,
                                changing_mold: rows[i].changing_mold,

                                lt_trial_id: rows[i].lt_trial_id,
                                trial_duration: rows[i].trial_duration,

                                lt_machine_id: rows[i].lt_machine_id,
                                machine_duration: rows[i].machine_duration,
                                lt_material_id: rows[i].lt_material_id,
                                material_duration: rows[i].material_duration,
                                lt_methode_id: rows[i].lt_methode_id,
                                methode_duration: rows[i].methode_duration,
                                lt_man_id: rows[i].lt_man_id,
                                man_duration: rows[i].man_duration,
                            };

                            var url_save = "<?= base_url('control/lost_time_transactions/create') ?>";

                            $.ajax({
                                type: "post",
                                url: url_save,
                                data: dataFinal,
                                dataType: "json",
                                success: function(result) {
                                    if (result.theme === "error") {
                                        toastr.error(result.message);
                                    }else{
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
                                },
                                error: function(xhr, status, error) {
                                    toastr.error("Server error: " + error);
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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('control/lost_time_transactions/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('control/lost_time_transactions/upload') ?>',
                    onSubmit: function () {
                        if (!$(this).form('validate')) return false;

                        $.messager.progress({
                            title: 'Please Wait',
                            msg: 'Importing Excel to Database'
                        });
                    },
                    success: function (result) {
                        $.messager.progress('close');
                        // Clear File
                        $.ajax({ 
                            url: "<?= base_url('control/lost_time_transactions/uploadclearFailed') ?>" 
                        });

                        let res = JSON.parse(result);
                        let dataList = res.data ?? [];

                        console.log(dataList);

                        if (dataList.length === 0) {
                            $.messager.alert("Upload Failed", "Data not found from Excel file", "error");
                            return;
                        }

                        // Reset UI
                        $('#p_upload').progressbar('setValue', 0);
                        $('#p_start').html(0);
                        $('#p_finish').html(dataList.length);
                        $('#p_success').html(0);
                        $('#p_failed').html(0);
                        $('#p_remarks').html('');

                        let totalExpected = dataList.length;

                        // Kirim semua data
                        $.ajax({
                            type: "POST",
                            url: "<?= base_url('control/lost_time_transactions/uploadCreate') ?>",
                            data: JSON.stringify({ data: dataList }),
                            dataType: "json",
                            success: function (response) {

                                $('#p_upload').progressbar('setValue', 0);
                                let successCount = 0;
                                let failedCount = 0;
                                let progressCount = 0;
                                let total = response.total_expected ?? response.results.length;
                                
                                function updateProgress() {
                                    let percent = Math.floor((progressCount / total) * 100);
                                    $('#p_upload').progressbar('setValue', percent);
                                    $('#p_start').html(progressCount);
                                    $('#p_success').html(successCount);
                                    $('#p_failed').html(failedCount);
                                }

                                if (response.results && response.results.length > 0) {
                                    let delayPerItem = 50;
                                    response.results.forEach(function (r, i) {
                                        setTimeout(function () {
                                            let color = r.status === "success" ? "green" : "red";

                                            if (r.status === "success") successCount++;
                                            else failedCount++;

                                            $('#p_remarks').append(
                                                `<b style="color: ${color};">${r.item}</b> | ${r.message}<br>`
                                            );

                                            progressCount++;
                                            updateProgress();

                                            if(progressCount == total) {
                                                if (response.theme === 'error') {
                                                    $.messager.alert(response.title ?? "Upload Failed", response.message ?? "Some data failed to save", "error");
                                                }

                                                $('#dg').datagrid('reload');
                                            }

                                        }, i * delayPerItem);
                                    });
                                }

                            },

                            error: function (xhr, status, error) {
                                $.messager.alert("Upload Error", "An error occurred while saving the data", "error");
                            }
                        });
                    }
                });
            }
        }]
    });

    $("#filter_period").combobox({
        url: '<?= base_url('control/lost_time_transactions/readPeriod') ?>',
        valueField: 'period',
        textField: 'period',
        prompt: "Select Period",
        onLoadSuccess: function(data) {
            var defaultVal = "<?= date("Ym") ?>";
            $("#filter_period").combobox('setValue', defaultVal);
            $("#filter_period").combobox('select', defaultVal);
        },
        onSelect: function (data) {
            var period = data.period; // 202509

            var year = parseInt(period.substring(0, 4));
            var month = parseInt(period.substring(4, 6));

            var firstDay = new Date(year, month - 1, 1);
            var lastDay = new Date(year, month, 0);

            function pad(num) {
                return num < 10 ? '0' + num : num;
            }

            var filter_from_value = `${year}-${pad(month)}-01`;
            var filter_to_value = `${year}-${pad(month)}-${pad(lastDay.getDate())}`;

            $("#filter_from").datebox('setValue', filter_from_value);
            $("#filter_to").datebox('setValue', filter_to_value);

            $("#filter_wp").combobox({
                url: '<?= base_url('control/lost_time_transactions/readWp?period=') ?>' + btoa(period),
                valueField: 'wp',
                textField: 'wp',
                prompt: "Select WP",
                icons: [{
                    iconCls: 'icon-clear',
                    handler: function(e) {
                        $(e.data.target).combobox('clear').combobox('textbox').focus();
                    }
                }],
            });
        }
    });

    $('#filter_number').combobox({
        url: '<?= base_url('control/lost_time_transactions/readNumber'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Lost Time Doc No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_workorder').combobox({
        url: '<?= base_url('control/lost_time_transactions/readWoNos'); ?>',
        valueField: 'workorder',
        textField: 'workorder',
        prompt: 'Choose Work Order No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_category').combobox({
        url: '<?= base_url('control/lost_time_transactions/readCategories'); ?>',
        valueField: 'category',
        textField: 'category',
        prompt: 'Choose Category',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/readRubberParts'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
        columns: [
            [{
                field: 'number',
                title: 'Product No',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 250
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $("#machine_no_insert").combogrid({
        url: '<?= base_url('control/lost_time_transactions/readMachinePressMolds') ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        multiple: true,
        prompt: "Choose Machine No",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'number',
                title: 'Machine No',
                width: 100
            }, {
                field: 'name',
                title: 'Machine Name',
                width: 100
            }, ]
        ],
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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatPrecision(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }

    function hitungTotal(target) {
        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var idx = dg.datagrid('getRowIndex', row);

        var edOk   = dg.datagrid('getEditor', { index: idx, field: 'qty_ok' });
        var edNg   = dg.datagrid('getEditor', { index: idx, field: 'qty_ng' });
        var edMold = dg.datagrid('getEditor', { index: idx, field: 'qty_ng_mold' });
        var edTot  = dg.datagrid('getEditor', { index: idx, field: 'total_qty' });

        var ok    = edOk   ? parseFloat($(edOk.target).numberbox('getValue'))   || 0 : 0;
        var ng    = edNg   ? parseFloat($(edNg.target).numberbox('getValue'))   || 0 : 0;
        var mold  = edMold ? parseFloat($(edMold.target).numberbox('getValue')) || 0 : 0;

        var total = ok + ng + mold;

        if (edTot) {
            $(edTot.target).numberbox('setValue', total);
        }
    }

</script>