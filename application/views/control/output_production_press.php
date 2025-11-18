<style>
    .datagrid-wrap {
        position: relative;
        overflow: hidden;
    }

    .datagrid-view {
        overflow-y: auto !important;
        max-height: 500px;
    }

    .datagrid-header {
        position: sticky !important;
        top: 0;
        z-index: 20;
        background: #f4f4f4;
    }

    .datagrid-header-inner {
        border-bottom: 1px solid #d0d0d0;
    }
</style>

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

            <!-- <th rowspan="2" data-options="field:'status_wo',width:150,align:'center',formatter:statusformat,styler:statusStyle">Status (Press Achievment)</th> -->

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
<div id="toolbar" style="height: 265px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" name="filter_period" id="filter_period" class="easyui-combobox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Production Date</span>
                    <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                    <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>

                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Production Date</span>
                    <input style="width:60%;" id="filter_trans_date" value="<?= date("Y-m-d") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" id="filter_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shift</span>
                    <select style="width:60%;" id="filter_shift" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WP No</span>
                    <input style="width:60%;" id="filter_wp" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WO No</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">Choose All</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
                <!-- <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div> -->

                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>

            </div>
        </fieldset>
        <?= $button ?>

        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="updateMachineOnly()"><i class="fa fa-edit"></i> Update by Machine</a>

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
                    <span style="width:35%; display:inline-block;">Doc No</span>
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
                        <!-- <option value="" disabled>Choose All</option> -->
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>

                <div class="fitem" id="machine_no_insert_wrapper">
                    <span style="width:35%; display:inline-block;">Unscheduled Machine No</span>
                    <input style="width:60%;" id="machine_no_insert" class="easyui-combogrid">
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
<iframe id="printout" src="<?= base_url('control/output_production_press/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    let currentMode = '';

    //ADD DATA
    function add() {
        currentMode = 'add';
        // $('#dlg_insert').dialog('open');

        $('#dlg_insert').dialog({
            title: 'Add New',
            modal: true,
            closed: false,
            maximized: true,
            resizable: true,
        }).dialog('open');


        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('control/output_production_press/create') ?>';
        $('#frm_insert').form('clear');
        $("#machine_no_insert").combogrid('clear');
        $('#machine_no_insert_wrapper').show();
        $("#trans_date").datebox('enable');
        $("#number").textbox('enable');
        $("#period").combobox('enable');
        $("#wp").combobox('enable');
        $("#shift").combobox('enable');
        // $("#trans_date").datebox('setValue', "<?= date("Y-m-d") ?>");
        // autonumber();

        $("#trans_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                var d = $.fn.datebox.defaults.formatter(date);
                autonumber(d);
            }
        });

        var today = "<?= date('Y-m-d') ?>";
        $("#trans_date").datebox('setValue', today);

        autonumber(today);

        $("#period").combobox({
            url: '<?= base_url('planning/production_schedule_press/readPeriod') ?>',
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

                $("#wp").combobox({
                    url: '<?= base_url('planning/production_schedule_press/readWp?period=') ?>' + btoa(period),
                    valueField: 'wp',
                    textField: 'wp',
                    prompt: "Select WP"
                });
            }            
        });

        // var period = $("#period").combobox('getValue');
        // console.log('ppp', period);
        
        // $("#wp").combobox({
        //     url: '<?= base_url('planning/production_schedule_press/readWp?period=') ?>' + btoa(period),
        //     valueField: 'wp',
        //     textField: 'wp',
        //     prompt: "Select Period",
        //     onLoadSuccess: function(data) {
        //         // Parse the data if needed
        //         $("#period").combobox('setValue', "<?= date("Ym") ?>");
        //     }
        // });
    }

    function autonumber(trans_date) {
        $.ajax({
            type: "post",
            url: "<?= base_url('control/output_production_press/autonumber') ?>",
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
            // fit: true,
            height: 480,
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
                }, 


                {
                    field: 'machine_number',
                    width: 100,
                    rowspan: 2,
                    halign: 'center',
                    align: 'center',
                    title: "Machine No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/output_production_press/readMachinePressByWP'); ?>',
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
                                param.wp = window.btoa($('#wp').textbox('getValue'));
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
                                        wp: window.btoa($('#wp').textbox('getValue'))
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
                                    $(edWO.target).combogrid('setValue', '');
                                    $(edWO.target).combogrid('grid').datagrid('load', {
                                        machine_id: window.btoa(row.machine_id),
                                        period: window.btoa($('#period').textbox('getValue')),
                                        wp: window.btoa($('#wp').textbox('getValue'))
                                    });
                                }
                            },
                            // onHidePanel: function() {
                            //     var t = $(this).combogrid('getText');
                            //     var g = $(this).combogrid('grid');
                            //     var rows = g.datagrid('getRows');
                            //     var exists = false;

                            //     for (var i = 0; i < rows.length; i++) {
                            //         if (rows[i].number === t) {
                            //             exists = true;
                            //             break;
                            //         }
                            //     }

                            //     if (!exists) {
                            //         $(this).combogrid('setValue', '');
                            //         var grid = $(this).combogrid('grid');
                            //         grid.datagrid('load', {});
                            //     }
                            // }

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

                }, 
                {
                    field: 'item_fg_number',
                    width: 150,
                    rowspan: 2,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/output_production_press/readItemFg/'); ?>',
                            required: true,
                            panelWidth: 750,
                            idField: 'item_fg_id',
                            textField: 'number',
                            // valueField: 'item_fg_id',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [[
                                { field: 'number', title: 'Product No', width: 200 },
                                { field: 'name', title: 'Product Name', width: 150 },
                                { field: 'planning_qty', title: 'Planning/day (pcs)', width: 150 },
                                { field: 'workorder', title: 'Workorder', width: 150 },
                                { field: 'mold_id', title: 'Mold ID', width: 150 }
                            ]],
                            onBeforeLoad: function(param) {
                                param.period = window.btoa($('#period').textbox('getValue'));
                                param.wp = window.btoa($('#wp').textbox('getValue'));
                                const dg = $('#dg2');
                                const row = dg.datagrid('getSelected');
                                param.machine_id = row && row.machine_id ? window.btoa(row.machine_id) : '';
                            },
                            onLoadSuccess: function(data) {
                                const dg = $('#dg2');
                                const row = dg.datagrid('getSelected');
                                if (!row) return;
                                const idx = dg.datagrid('getRowIndex', row);

                                const edId = dg.datagrid('getEditor', { index: idx, field: 'item_fg_id' });
                                const edNo = dg.datagrid('getEditor', { index: idx, field: 'item_fg_number' });
                                const edName = dg.datagrid('getEditor', { index: idx, field: 'item_fg_name' });
                                const edPlan = dg.datagrid('getEditor', { index: idx, field: 'planning_qty' });
                                const edPlnShift = dg.datagrid('getEditor', { index: idx, field: 'planning_qty_shift' });
                                const edWO = dg.datagrid('getEditor', { index: idx, field: 'workorder' });
                                const edMold = dg.datagrid('getEditor', { index: idx, field: 'mold_id' });

                                // Auto pilih kalau cuma satu data
                                if (data.rows && data.rows.length === 1) {
                                    const item = data.rows[0];
                                    $(edNo.target).combogrid('grid').datagrid('selectRecord', item.item_fg_id);
                                }

                                let pln_qty = row.planning_qty ? Math.ceil(row.planning_qty / 3) : 0;
                                let planning_qty = row.planning_qty ? Math.round(row.planning_qty) : 0;

                                if (row.item_fg_id) {
                                    if (edId) $(edId.target).textbox('setValue', row.item_fg_id);
                                    if (edNo) $(edNo.target).combogrid('setValue', row.item_fg_number);
                                    if (edName) $(edName.target).textbox('setValue', row.item_fg_name);
                                    if (edPlan) $(edPlan.target).numberbox('setValue', row.planning_qty);
                                    if (edPlnShift) $(edPlnShift.target).numberbox('setValue', pln_qty);
                                    if (edWO) $(edWO.target).textbox('setValue', row.workorder);
                                    if (edMold) $(edMold.target).textbox('setValue', row.mold_id);
                                }
                            },
                            onSelect: function(index, rows) {
                                const dg = $('#dg2');
                                const row = dg.datagrid('getSelected');
                                const rowIndex = dg.datagrid('getRowIndex', row);

                                const ed1 = dg.datagrid('getEditor', { index: rowIndex, field: 'item_fg_id' });
                                const ed2 = dg.datagrid('getEditor', { index: rowIndex, field: 'item_fg_number' });
                                const ed3 = dg.datagrid('getEditor', { index: rowIndex, field: 'item_fg_name' });
                                const ed4 = dg.datagrid('getEditor', { index: rowIndex, field: 'planning_qty' });
                                const ed5 = dg.datagrid('getEditor', { index: rowIndex, field: 'workorder' });
                                const ed6 = dg.datagrid('getEditor', { index: rowIndex, field: 'planning_qty_shift' });
                                const ed7 = dg.datagrid('getEditor', { index: rowIndex, field: 'mold_id' });

                                let pln_qty = rows.planning_qty ? Math.ceil(rows.planning_qty / 3) : 0;
                                let planning_qty = rows.planning_qty ? Math.round(rows.planning_qty) : 0;

                                $(ed1.target).textbox('setValue', rows.item_fg_id);
                                $(ed2.target).combogrid('setValue', rows.number);
                                $(ed3.target).textbox('setValue', rows.name);
                                $(ed4.target).textbox('setValue', planning_qty);
                                $(ed5.target).textbox('setValue', rows.workorder);
                                $(ed6.target).textbox('setValue', pln_qty);
                                $(ed7.target).textbox('setValue', rows.mold_id);
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
                },

                {
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
                    field: 'item_fg_name',
                    width: 100,
                    rowspan: 2,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'planning_qty',
                    width: 100,
                    rowspan: 2,
                    halign: 'center',
                    title: "Planning/day <br> (pcs)",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true
                        }
                    }
                }, {
                    field: 'planning_qty_shift',
                    width: 100,
                    rowspan: 2,
                    halign: 'center',
                    title: "Planning/shift <br> (pcs)",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true
                        }
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
                    field: 'mold_id',
                    width: 150,
                    rowspan: 2,
                    halign: 'center',
                    align: 'center',
                    title: "Mold ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, 

                // {
                //     field: 'operator',
                //     width: 130,
                //     rowspan: 2,
                //     halign: 'center',
                //     title: "Operator <br>Name",
                //     editor: {
                //         type: 'combogrid',
                //         options: {
                //             url: '<?= base_url('api/hris_bri/getOperatorName') ?>',
                //             method: 'get',
                //             mode: 'remote',
                //             idField: 'name',
                //             textField: 'name',
                //             valueField: 'name',
                //             prompt: 'Choose Operator Name',
                //             panelWidth: 265,
                //             fitColumns: true,
                //             required: true,
                //             queryParams: {},
                //             onShowPanel: function() {
                //                 var grid = $(this).combogrid('grid');
                //                 if (!grid.data('loaded')) {
                //                     grid.datagrid('load', {});
                //                     grid.data('loaded', true);
                //                 }
                //             },
                //             columns: [[
                //                 { field: 'name', title: 'Operator Name', width: 250 },
                //             ]],
                //             loadFilter: function(data){
                //                 return Array.isArray(data) ? data : [];
                //             },
                //             onHidePanel: function() {
                //                 var t = $(this).combogrid('getText');
                //                 var g = $(this).combogrid('grid');
                //                 var rows = g.datagrid('getRows');
                //                 var exists = false;

                //                 for (var i = 0; i < rows.length; i++) {
                //                     if (rows[i].name === t) {
                //                         exists = true;
                //                         break;
                //                     }
                //                 }

                //                 if (!exists) {
                //                     $(this).combogrid('setValue', '');
                //                     g.datagrid('loadData', []);
                //                     g.removeData('loaded');
                //                 }
                //             }
                //         }
                //     }
                // }, 

                {
                    field: 'operator',
                    width: 130,
                    rowspan: 2,
                    halign: 'center',
                    title: "Operator <br>Name",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/man_powers/reads') ?>',
                            // method: 'get',
                            mode: 'remote',
                            idField: 'name',
                            textField: 'name',
                            valueField: 'name',
                            prompt: 'Choose Operator Name',
                            panelWidth: 265,
                            fitColumns: true,
                            required: true,
                            queryParams: {},
                            onShowPanel: function() {
                                var grid = $(this).combogrid('grid');
                                if (!grid.data('loaded')) {
                                    grid.datagrid('load', {});
                                    grid.data('loaded', true);
                                }
                            },
                            columns: [[
                                { field: 'name', title: 'Operator Name', width: 250 },
                            ]],
                            loadFilter: function(data){
                                return Array.isArray(data) ? data : [];
                            },
                            onHidePanel: function() {
                                var t = $(this).combogrid('getText');
                                var g = $(this).combogrid('grid');
                                var rows = g.datagrid('getRows');
                                var exists = false;

                                for (var i = 0; i < rows.length; i++) {
                                    if (rows[i].name === t) {
                                        exists = true;
                                        break;
                                    }
                                }

                                if (!exists) {
                                    $(this).combogrid('setValue', '');
                                    g.datagrid('loadData', []);
                                    g.removeData('loaded');
                                }
                            }
                        }
                    }
                }, {
                    title: 'Output', 
                    colspan: 4,
                    halign: 'center', 
                    align: 'center' 
                }, {
                    field: 'actual_cavity',
                    width: 60,
                    rowspan: 2,
                    align: 'center',
                    title: "Actual <br>Cavity",
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
                //     field: 'standard_curing_time',
                //     width: 110,
                //     rowspan: 2,
                //     align: 'center',
                //     title: "Standard Curing <br> Time (second)",
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
                    field: 'actual_curing_time',
                    width: 100,
                    rowspan: 2,
                    align: 'center',
                    title: "Act Curing <br> Time (second)",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                        }
                    }
                }, {
                    field: 'shift_hour',
                    width: 65,
                    rowspan: 2,
                    align: 'center',
                    title: "Hour/Shift",
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
                //     field: 'target_shoot',
                //     width: 60,
                //     rowspan: 2,
                //     align: 'center',
                //     title: "Target <br>Shoot",
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
                    field: 'actual_shoot',
                    width: 60,
                    rowspan: 2,
                    align: 'center',
                    title: "Actual <br>Shoot",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            required: true,
                        }
                    }
                }, {
                    field: 'total_compound_used',
                    width: 110,
                    rowspan: 2,
                    align: 'center',
                    title: "Total Compound <br>Used (kg)",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            required: true,
                        }
                    }
                }, {
                    field: 'waste',
                    width: 60,
                    rowspan: 2,
                    align: 'center',
                    title: "Waste <br> (kg)",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            // required: true,
                        }
                    }
                }, 
                
                // {   
                //     title: 'Downtime', 
                //     colspan: 6, 
                //     halign: 'center',
                //     align: 'center',
                // }, {
                //     field: 'remarks',
                //     width: 80,
                //     rowspan: 2,
                //     align: 'center',
                //     halign: 'center',
                //     title: "Remarks",
                //     editor: {
                //         type: 'textbox',
                //     }
                // }
                ],

                [{
                    
                    field: 'qty_ok',
                    width: 50,
                    align: 'center',
                    title: "OK",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 0,
                            onChange: function (newValue, oldValue) {
                                hitungTotal(this);
                            }

                            // onChange: function (newValue, oldValue) {
                            //     var dg = $('#dg2');
                            //     var row = dg.datagrid('getSelected');
                            //     var idx = dg.datagrid('getRowIndex', row);

                            //     var edPlan = dg.datagrid('getEditor', { index: idx, field: 'planning_qty' });
                            //     var edNg   = dg.datagrid('getEditor', { index: idx, field: 'qty_ng' });

                            //     var planning = edPlan ? parseFloat($(edPlan.target).numberbox('getValue')) || 0 : 0;
                            //     var ng       = edNg ? parseFloat($(edNg.target).numberbox('getValue')) || 0 : 0;
                            //     var ok       = parseFloat(newValue) || 0;

                            //     if (ok + ng > planning) {
                            //         $(this).numberbox('setValue', '');
                            //         toastr.error("Qty OK cannot exceed Planning Qty");
                            //     }
                            // }

                        }
                    }
                }, {
                    field: 'qty_ng',
                    width: 90,
                    align: 'center',
                    title: "NG Produksi",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 0,
                            onChange: function (newValue, oldValue) {
                                hitungTotal(this);
                            }

                            // onChange: function (newValue, oldValue) {
                            //     var dg = $('#dg2');
                            //     var row = dg.datagrid('getSelected');
                            //     var idx = dg.datagrid('getRowIndex', row);

                            //     var edPlan = dg.datagrid('getEditor', { index: idx, field: 'planning_qty' });
                            //     var edOk   = dg.datagrid('getEditor', { index: idx, field: 'qty_ok' });

                            //     var planning = edPlan ? parseFloat($(edPlan.target).numberbox('getValue')) || 0 : 0;
                            //     var ok       = edOk ? parseFloat($(edOk.target).numberbox('getValue')) || 0 : 0;
                            //     var ng       = parseFloat(newValue) || 0;

                            //     if (ok + ng > planning) {
                            //         $(this).numberbox('setValue', '');
                            //         toastr.error("Qty OK + Qty NG cannot exceed Planning Qty");
                            //     }
                            // }

                        }
                    }
                }, {
                    field: 'qty_ng_mold',
                    width: 70,
                    align: 'center',
                    title: "NG Mold",
                    formatter: numberFormatField,
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 0,
                            onChange: function (newValue, oldValue) {
                                hitungTotal(this);
                            }
                        }
                    }
                }, {
                    field: 'total_qty',
                    width: 65,
                    align: 'center',
                    title: "Total",
                    editor: {
                        type: 'numberbox',
                        options: {
                            readonly: true,
                            precision: 0,
                        }
                    }
                }, 
                
                // {
                //     field: 'mold_cleaning',
                //     width: 95,
                //     align: 'center',
                //     title: "Mold Cleaning",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //         }
                //     }
                // }, {
                //     field: 'trial',
                //     width: 50,
                //     align: 'center',
                //     title: "Trial",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //         }
                //     }
                // }, {
                //     field: 'mold_changing',
                //     width: 100,
                //     align: 'center',
                //     title: "Mold Changing",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //         }
                //     }
                // }, {
                //     field: 'machine_repair',
                //     width: 100,
                //     align: 'center',
                //     title: "Machine Repair",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //         }
                //     }
                // }, {
                //     field: 'mold_repair',
                //     width: 80,
                //     align: 'center',
                //     title: "Mold Repair",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //         }
                //     }
                // }, {
                //     field: 'others',
                //     width: 80,
                //     align: 'center',
                //     title: "Others",
                //     formatter: numberFormatField,
                //     editor: {
                //         type: 'numberbox',
                //         options: {
                //             precision: 0,
                //         }
                //     }
                // }
            ]
            ],
            onClickCell: onClickCell,

            onLoadSuccess: function(data) {
                var dg = $('#dg2');
                for (var i = 0; i < data.rows.length; i++) {
                    var row = data.rows[i];

                    // Hitung planning_qty_shift jika belum ada
                    if (row.planning_qty && !row.planning_qty_shift) {
                        row.planning_qty_shift = Math.ceil(row.planning_qty / 3);
                    }

                    // Hitung total_qty
                    var ok   = parseFloat(row.qty_ok)   || 0;
                    var ng   = parseFloat(row.qty_ng)   || 0;
                    var mold = parseFloat(row.qty_ng_mold) || 0;
                    row.total_qty = ok + ng + mold;

                    dg.datagrid('updateRow', {
                        index: i,
                        row: row
                    });
                    dg.datagrid('refreshRow', i);
                }
            },

            onBeginEdit: function(index, row) {
                var ed = $(this).datagrid('getEditor', { index: index, field: 'shift_hour' });
                if (ed){
                    var val = $(ed.target).numberbox('getValue');
                    if (!val) {
                        $(ed.target).numberbox('setValue', 7);
                    }
                }
            }

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

    // function append() {
    //     var period = $("#period").combobox('getValue');
    //     var wp = $("#wp").combobox('getValue');
    //     if (period != "") {
    //         if (endEditing()) {
    //             $('#dg2').datagrid('appendRow', {
    //                 qty: '0',
    //                 qty_wip: '0'
    //             });
    //             editIndex = $('#dg2').datagrid('getRows').length - 1;
    //             $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);

    //             var dg = $('#dg2');
    //             var row = dg.datagrid('getSelected');
    //             var rowIndex = dg.datagrid('getRowIndex', row);

    //             var item_fg_id = dg.datagrid('getEditor', {
    //                 index: rowIndex,
    //                 field: 'item_fg_id'
    //             });
    //             // var item_fg_number = dg.datagrid('getEditor', {
    //             //     index: rowIndex,
    //             //     field: 'item_fg_number'
    //             // });
    //             var item_fg_name = dg.datagrid('getEditor', {
    //                 index: rowIndex,
    //                 field: 'item_fg_name'
    //             });
    //             var planning_qty = dg.datagrid('getEditor', {
    //                 index: rowIndex,
    //                 field: 'planning_qty'
    //             });

    //             $(item_fg_id.target).combogrid({
    //                 url: '<?= base_url('control/output_production_press/readItemFg/'); ?>' + period + '/' + wp,
    //                 panelWidth: 450,
    //                 idField: 'item_fg_id',
    //                 textField: 'number',
    //                 mode: 'remote',
    //                 fitColumns: true,
    //                 prompt: 'Choose Product No',
    //                 columns: [
    //                     [{
    //                         field: 'number',
    //                         title: 'Product No',
    //                         width: 150
    //                     }, {
    //                         field: 'name',
    //                         title: 'Product Name',
    //                         width: 200
    //                     }, {
    //                         field: 'planning_qty',
    //                         title: 'Planning',
    //                         width: 80
    //                     }]
    //                 ],
    //                 onSelect: function(value, rows) {
    //                     // $(item_fg_number.target).textbox('setValue', rows.number);
    //                     $(item_fg_name.target).textbox('setValue', rows.name);
    //                     $(planning_qty.target).textbox('setValue', rows.planning_qty);
    //                     // $(lot_no.target).textbox('setValue', rows.lot_no);
    //                     // $(dn_number.target).textbox('setValue', rows.document_no);
    //                     // $(so_number.target).textbox('setValue', rows.document_no);
    //                 }
    //             });
    //         }
    //     } else {
    //         toastr.error("Please Choose Product No and Process first");
    //     }
    // }

    function append() {
        var period = $("#period").combobox('getValue');
        var wp = $("#wp").combobox('getValue');
        var shift = $("#shift").combobox('getValue');

        if (period != "" && wp != "" && shift != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    planning_qty: '',
                });
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
            url: '<?= base_url('control/output_production_press/delete') ?>',
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
            // $('#dlg_insert').dialog('open');

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
            $("#period").combobox('disable');
            $("#wp").combobox('disable');
            $("#shift").combobox('disable');

            $('#machine_no_insert_wrapper').hide();
            // $("#machine_no_insert").combogrid('disable');
            $("#machine_no_insert").combogrid('clear');

            addTable('<?= base_url('control/output_production_press/datatableUpdates?number=') ?>' + window.btoa(row.number));
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    // function updateMachineOnly() {
    //     currentMode = 'updateMachineOnly';
    //     var row = $('#dg').treegrid('getSelected');

    //     console.log('Row : ', row);
    //     if (row) {
    //         if (row.machine_id) {
    //             $('#dlg_insert').dialog({
    //                 title: 'Update by Machine',
    //                 modal: true,
    //                 closed: false,
    //                 maximized: true,
    //                 resizable: true,
    //             }).dialog('open');

    //             $('#frm_insert').form('load', row);
    //             $("#trans_date").datebox('disable');
    //             $("#number").textbox('disable');
    //             $("#period").combobox('disable');
    //             $("#wp").combobox('disable');
    //             $("#shift").combobox('disable');

    //             $("#machine_no_insert").combogrid('clear');
    //             $('#machine_no_insert_wrapper').show();
    //             $("#machine_no_insert").combogrid('disable');

    //             addTable('<?= base_url('control/output_production_press/datatableUpdateByMachines?number=') ?>'
    //                 + window.btoa(row.number));

    //             var urlMachines = "<?= base_url('control/output_production_press/readMachinesByNumber?number=') ?>" + window.btoa(row.number);
            
    //             $.ajax({
    //                 url: urlMachines,
    //                 type: "GET",
    //                 dataType: "json",
    //                 success: function(res) {
    //                     console.log("Machines loaded:", res);
    //                     if (Array.isArray(res) && res.length > 0) {
    //                         $("#machine_no_insert").combogrid('setValues', res);
    //                     } else {
    //                         $("#machine_no_insert").combogrid('clear');
    //                     }
    //                 },
    //                 error: function(xhr, status, error) {
    //                     console.error("Error loading machine list:", error);
    //                 }
    //             });
    //         } else {
    //             toastr.warning("Please select one of the data in the table first!", "Information");
    //         }
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    function updateMachineOnly() {
        currentMode = 'updateMachineOnly';
        var row = $('#dg').treegrid('getSelected');
        // console.log('Row:', row);

        if (!row) {
            toastr.warning("Please select one of the data in the table first!", "Information");
            return;
        }

        // Cek global apakah masih ada machine dengan item_fg_id kosong
        const checkUrl = "<?= base_url('control/output_production_press/checkMachinesWithoutItemFg?number=') ?>" + window.btoa(row.number);

        $.ajax({
            url: checkUrl,
            type: "GET",
            dataType: "json",
            success: function(res) {
                // console.log("Check result:", res);

                if (res.has_empty_machines) {
                    // Masih ada mesin kosong
                    $('#dlg_insert').dialog({
                        title: 'Update by Machine',
                        modal: true,
                        closed: false,
                        maximized: true,
                        resizable: true,
                    }).dialog('open');

                    $('#frm_insert').form('load', row);
                    $("#trans_date").datebox('disable');
                    $("#number").textbox('disable');
                    $("#period").combobox('disable');
                    $("#wp").combobox('disable');
                    $("#shift").combobox('disable');

                    $("#machine_no_insert").combogrid('clear');
                    $('#machine_no_insert_wrapper').show();
                    $("#machine_no_insert").combogrid('disable');

                    addTable('<?= base_url('control/output_production_press/datatableUpdateByMachines?number=') ?>' + window.btoa(row.number));

                    const urlMachines = "<?= base_url('control/output_production_press/readMachinesByNumber?number=') ?>" + window.btoa(row.number);
                    $.ajax({
                        url: urlMachines,
                        type: "GET",
                        dataType: "json",
                        success: function(res) {
                            // console.log("Machines loaded:", res);
                            if (Array.isArray(res) && res.length > 0) {
                                $("#machine_no_insert").combogrid('setValues', res);
                            } else {
                                $("#machine_no_insert").combogrid('clear');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading machine list:", error);
                        }
                    });
                } else {
                    // Tidak ada mesin kosong
                    toastr.warning("All machines already have assigned item FG. No data to update!", "Information");
                }
            },
            error: function(xhr, status, error) {
                // console.error("Error checking machine condition:", error);
                toastr.error("Failed to check machine condition!", "Error");
            }
        });
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
                            url: '<?= base_url('control/output_production_press/delete') ?>',
                            data: {
                                number: row.number,
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                // toastr.error(jqXHR.statusText);
                                // $.messager.alert("Error", jqXHR.statusText, 'error');

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
    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_output_production_press.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        // var filter_from = $("#filter_from").datebox('getValue');
        // var filter_to = $("#filter_to").datebox('getValue');
        // var filter_trans_date = $("#filter_trans_date").datebox('getValue');

        var filter_period = $("#filter_period").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        // var url = "?filter_period=" + filter_period + "&filter_trans_date=" + filter_trans_date + 

        var url = "?filter_period=" + filter_period + "&filter_from=" + filter_from + "&filter_to=" + filter_to +
        "&filter_number=" + filter_number + "&filter_shift=" + filter_shift + "&filter_wp=" + filter_wp + "&filter_workorder=" + filter_workorder + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        $('#dg').datagrid({
            url: '<?= base_url('control/output_production_press/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/output_production_press/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        // var filter_from = $("#filter_from").datebox('getValue');
        // var filter_to = $("#filter_to").datebox('getValue');

        var filter_period = $("#filter_period").datebox('getValue');
        // var filter_trans_date = $("#filter_trans_date").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_number = $("#filter_number").combobox('getValue');
        var filter_shift = $("#filter_shift").combobox('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        // var url = "?filter_period=" + filter_period + "&filter_trans_date=" + filter_trans_date + 

        var url = "?filter_period=" + filter_period + "&filter_from=" + filter_from + "&filter_to=" + filter_to +
        "&filter_number=" + filter_number + "&filter_shift=" + filter_shift + "&filter_wp=" + filter_wp + "&filter_workorder=" + filter_workorder + "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status;

        window.location.assign('<?= base_url('control/output_production_press/print/excel') ?>' + url);
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
            url: '<?= base_url('control/output_production_press/datatables') ?>',
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
                var filter_workorder = $("#filter_workorder").combogrid('getValue');
                var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('control/output_production_press/datatableDetails?number=') ?>' + window.btoa(row.number) + "&filter_workorder=" + window.btoa(filter_workorder) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id),
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
                        },{
                            field: 'item_fg_id',
                            title: 'Product ID',
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
                            field: 'item_rm_number',
                            title: 'Compound Name Used',
                            rowspan: 2,
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'planning_qty',
                            title: 'Planning/day (pcs)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 130,
                            formatter: numberformat
                        }, {
                            field: 'planning_qty_shift',
                            title: 'Planning/shift (pcs)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 135,
                            formatter: numberformat
                        }, {
                            field: 'workorder',
                            title: 'Work Order No',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 150
                        }, {
                            field: 'mold_id',
                            title: 'Mold ID',
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
                            title: 'Output', 
                            colspan: 4,
                            halign: 'center', 
                            align: 'center' 
                        }, {
                            field: 'minus_prod',
                            title: 'Minus Production',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'standard_cavity',
                            title: 'Standard Cavity',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'actual_cavity',
                            title: 'Actual Cavity',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 100,
                            formatter: numberformat
                        }, {
                            field: 'standard_curing_time',
                            title: 'Standard Curing Time <br> (second)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 150,
                            formatter: numberformat
                        }, {
                            field: 'actual_curing_time',
                            title: 'Actual Curing Time <br> (second)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 140,
                            formatter: numberformat
                        }, {
                            field: 'shift_hour',
                            title: 'Hour/Shift',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 100,
                            formatter: numberformat
                        }, {
                            field: 'target_shoot',
                            title: 'Target Shoot',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 100,
                            formatter: numberformat
                        }, {
                            field: 'actual_shoot',
                            title: 'Actual Shoot',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 100,
                            formatter: numberformat
                        }, {
                            field: 'shoot_deviation',
                            title: 'Shoot Deviation',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'achievment',
                            title: '% Achievment',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'ng_prod',
                            title: '% NG Production',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'ng_mold',
                            title: '% NG Mold',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'total_compound_used',
                            title: 'Total Compound Used <br> (kg)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 150,
                            formatter: numberformatPrecision
                        }, {
                            field: 'waste',
                            title: 'Waste <br> (kg)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 80,
                            formatter: numberformatPrecision
                        }, {
                            field: 'waste_percen',
                            title: '% Waste',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 120,
                            formatter: numberformat
                        }, {
                            field: 'total_used_shoot',
                            title: 'Total Used/shoot (gr)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 160,
                            formatter: numberformatPrecision
                        }, {
                            field: 'total_waste_shoot',
                            title: 'Total Waste/shoot (gr)',
                            rowspan: 2,
                            halign: 'center',
                            align: 'center',
                            width: 160,
                            formatter: numberformatPrecision
                        }],

                        [{
                            field: 'qty_ok',
                            width: 100,
                            align: 'center',
                            title: "OK",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'qty_ng',
                            width: 100,
                            align: 'center',
                            title: "NG Produksi",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'qty_ng_mold',
                            width: 100,
                            align: 'center',
                            title: "NG Mold",
                            formatter: numberformat,
                            editor: {
                                type: 'numberbox',
                            }
                        }, {
                            field: 'total_qty',
                            width: 100,
                            align: 'center',
                            title: "Total",
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

        $('#trans_date').datebox().datebox('calendar').calendar({
            validator: function(date){
                var now = new Date();
                var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                return date <= today;
            }
        });

        //SAVE DATA
        // $('#dlg_insert').dialog({
        //     buttons: [{
        //         text: 'Save All',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             var trans_date = $("#trans_date").datebox('getValue');
        //             var number = $("#number").textbox('getValue');
        //             var period = $("#period").combobox('getValue');
        //             var wp = $("#wp").combobox('getValue');
        //             var shift = $("#shift").combobox('getValue');
        //             var pic = $("#pic").textbox('getValue');
        //             var machine_no_insert = $("#machine_no_insert").combogrid('getValues');

        //             console.log('Machine No Insert : ', machine_no_insert);

        //             if (!trans_date || !number || !period || !wp || !shift) {
        //                 toastr.error("Please complete all required fields before saving");
        //                 return;
        //             }

        //             var rows = $('#dg2').datagrid('getRows');
        //             var totalrows = rows.length;
        //             endEditing();

        //             console.log(JSON.stringify(rows));
                    

        //             for (let i = 0; i < totalrows; i++) {
        //                 if (rows[i].item_fg_id) {

        //                     var dataFinal = {
        //                         trans_date: trans_date,
        //                         number: number,
        //                         period: period,
        //                         wp: wp,
        //                         shift: shift,
        //                         pic: pic,
        //                         id: rows[i].id,
        //                         machine_id: rows[i].machine_id,
        //                         item_fg_id: rows[i].item_fg_id,
        //                         planning_qty: rows[i].planning_qty,
        //                         qty_ok: rows[i].qty_ok,
        //                         qty_ng: rows[i].qty_ng,
        //                         qty_ng_mold: rows[i].qty_ng_mold,
        //                         workorder: rows[i].workorder,
        //                         actual_cavity: rows[i].actual_cavity,
        //                         operator: rows[i].operator,
        //                         // standard_curing_time: rows[i].standard_curing_time,
        //                         actual_curing_time: rows[i].actual_curing_time,
        //                         shift_hour: rows[i].shift_hour,
        //                         // target_shoot: rows[i].target_shoot,
        //                         actual_shoot: rows[i].actual_shoot,
        //                         total_compound_used: rows[i].total_compound_used,
        //                         waste: rows[i].waste,
        //                         // mold_cleaning: rows[i].mold_cleaning,
        //                         // trial: rows[i].trial,
        //                         // mold_changing: rows[i].mold_changing,
        //                         // machine_repair: rows[i].machine_repair,
        //                         // mold_repair: rows[i].mold_repair,
        //                         // others: rows[i].others,
        //                         // remarks: rows[i].remarks
        //                     };

        //                     var url_save = "<?= base_url('control/output_production_press/create') ?>";

        //                     $.ajax({
        //                         type: "post",
        //                         url: url_save,
        //                         data: dataFinal,
        //                         dataType: "json",
        //                         success: function(result) {
        //                             if (result.theme === "error") {
        //                                 toastr.error(result.message);
        //                             }else{
        //                                 if (i == (totalrows - 1)) {
        //                                     Swal.fire({
        //                                         title: result.message,
        //                                         icon: result.theme,
        //                                         confirmButtonText: 'Ok',
        //                                         allowOutsideClick: false,
        //                                     }).then((result) => {
        //                                         if (result.isConfirmed) {
        //                                             window.location.reload();
        //                                         }
        //                                     });
        //                                 }
        //                             }
        //                         },
        //                         error: function(xhr, status, error) {
        //                             toastr.error("Server error: " + error);
        //                         }
        //                     });
        //                 }
        //             }

        //             $('#dg').datagrid('reload');
        //             $('#dlg_insert').dialog('close');
        //         }
        //     }]
        // });

        // SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var trans_date = $("#trans_date").datebox('getValue');
                    var number = $("#number").textbox('getValue');
                    var period = $("#period").combobox('getValue');
                    var wp = $("#wp").combobox('getValue');
                    var shift = $("#shift").combobox('getValue');
                    var pic = $("#pic").textbox('getValue');
                    var machine_no_insert = $("#machine_no_insert").combogrid('getValues'); // multiple machine id

                    console.log('Machine No Insert : ', machine_no_insert);

                    if (!trans_date || !number || !period || !wp || !shift) {
                        toastr.error("Please complete all required fields before saving");
                        return;
                    }

                    endEditing();
                    var rows = $('#dg2').datagrid('getRows') || [];
                    var totalrows = rows.length;
                    

                    // Array semua data dari datagrid
                    var dataToSave = [];

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            dataToSave.push({
                                trans_date: trans_date,
                                number: number,
                                period: period,
                                wp: wp,
                                shift: shift,
                                pic: pic,
                                id: rows[i].id,
                                machine_id: rows[i].machine_id,
                                item_fg_id: rows[i].item_fg_id,
                                mold_id: rows[i].mold_id,
                                planning_qty: rows[i].planning_qty,
                                qty_ok: rows[i].qty_ok,
                                qty_ng: rows[i].qty_ng,
                                qty_ng_mold: rows[i].qty_ng_mold,
                                workorder: rows[i].workorder,
                                actual_cavity: rows[i].actual_cavity,
                                operator: rows[i].operator,
                                actual_curing_time: rows[i].actual_curing_time,
                                shift_hour: rows[i].shift_hour,
                                actual_shoot: rows[i].actual_shoot,
                                total_compound_used: rows[i].total_compound_used,
                                waste: rows[i].waste
                            });
                        }
                    }

                    // Tambahkan machine tambahan
                    // if (machine_no_insert.length > 0) {
                    //     for (let j = 0; j < machine_no_insert.length; j++) {
                    //         dataToSave.push({
                    //             trans_date: trans_date,
                    //             number: number,
                    //             period: period,
                    //             wp: wp,
                    //             shift: shift,
                    //             pic: pic,
                    //             machine_id: machine_no_insert[j],

                    //             // semua field lain kosong/null
                    //             item_fg_id: null,
                    //             planning_qty: null,
                    //             qty_ok: null,
                    //             qty_ng: null,
                    //             qty_ng_mold: null,
                    //             workorder: null,
                    //             actual_cavity: null,
                    //             operator: null,
                    //             actual_curing_time: null,
                    //             shift_hour: null,
                    //             actual_shoot: null,
                    //             total_compound_used: null,
                    //             waste: null
                    //         });
                    //     }
                    // }

                    if (currentMode === 'add' && machine_no_insert.length > 0) {
                        for (let j = 0; j < machine_no_insert.length; j++) {
                            dataToSave.push({
                                trans_date: trans_date,
                                number: number,
                                period: period,
                                wp: wp,
                                shift: shift,
                                pic: pic,
                                machine_id: machine_no_insert[j],

                                // semua field lain kosong/null
                                item_fg_id: null,
                                mold_id: null,
                                planning_qty: null,
                                qty_ok: null,
                                qty_ng: null,
                                qty_ng_mold: null,
                                workorder: null,
                                actual_cavity: null,
                                operator: null,
                                actual_curing_time: null,
                                shift_hour: null,
                                actual_shoot: null,
                                total_compound_used: null,
                                waste: null
                            });
                        }
                    }

                    console.log('Save Mode:', currentMode);
                    console.log('Machine No Insert:', machine_no_insert);

                    console.log('Final Data to Save:', dataToSave);

                    // Loop simpan semua ke server
                    if (dataToSave.length === 0) {
                        toastr.error("No data to save");
                        return;
                    }

                    var url_save = "<?= base_url('control/output_production_press/create') ?>";

                    let successCount = 0;
                    for (let k = 0; k < dataToSave.length; k++) {
                        $.ajax({
                            type: "post",
                            url: url_save,
                            data: dataToSave[k],
                            dataType: "json",
                            success: function(result) {
                                if (result.theme === "error") {
                                    toastr.error(result.message);
                                } else {
                                    successCount++;
                                    if (successCount === dataToSave.length) {
                                        Swal.fire({
                                            title: "All data saved successfully",
                                            icon: "success",
                                            confirmButtonText: 'Ok',
                                            allowOutsideClick: false,
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }
                                }
                            },
                            error: function(xhr, status, error) {
                                toastr.error("Server error: " + error);
                            }
                        });
                    }

                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

    });

    // UPLOAD DATA
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('control/output_production_press/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('control/output_production_press/upload') ?>',
    //                 onSubmit: function() {
    //                     if ($(this).form('validate') == false) {
    //                         return $(this).form('validate');
    //                     } else {
    //                         $.messager.progress({
    //                             title: 'Please Wait',
    //                             msg: 'Importing Excel to Database'
    //                         });
    //                     }
    //                 },
    //                 success: function(result) {
    //                     $.messager.progress('close');
    //                     //Clear File
    //                     $.ajax({
    //                         url: "<?= base_url('control/output_production_press/uploadclearFailed') ?>"
    //                     });
    //                     var json = eval('(' + result + ')');
    //                     requestData(json.total, json);

    //                     function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                         if (value < 100) {
    //                             value = Math.floor((number / total) * 100);
    //                             $('#p_upload').progressbar('setValue', value);
    //                             $('#p_start').html(number);
    //                             $('#p_finish').html(total);

    //                             $.ajax({
    //                                 type: "POST",
    //                                 async: true,
    //                                 url: "<?= base_url('control/output_production_press/uploadCreate') ?>",
    //                                 data: {
    //                                     "data": json[number - 1]
    //                                 },
    //                                 cache: false,
    //                                 dataType: "json",
    //                                 success: function(result) {
    //                                     if (result.theme == "success") {
    //                                         $('#p_success').html(success);
    //                                         var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
    //                                         requestData(total, json, number + 1, value, success + 1, failed + 0);
    //                                     } else {
    //                                         $('#p_failed').html(failed);
    //                                         var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
    //                                         //Json Failed
    //                                         $.ajax({
    //                                             type: "POST",
    //                                             async: true,
    //                                             url: "<?= base_url('control/output_production_press/uploadcreateFailed') ?>",
    //                                             data: {
    //                                                 data: json[number - 1],
    //                                                 message: result.message
    //                                             },
    //                                             cache: false
    //                                         });
    //                                         requestData(total, json, number + 1, value, success + 0, failed + 1);
    //                                     }
    //                                     $("#p_remarks").append(title + "<br>");
    //                                 }
    //                             });
    //                         }
    //                     }
    //                 }
    //             });
    //         }
    //     }]
    // });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('control/output_production_press/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('control/output_production_press/upload') ?>',
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
                            url: "<?= base_url('control/output_production_press/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('control/output_production_press/uploadCreate') ?>",
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

    // $('#filter_division').combobox({
    //     url: '<?= base_url('master/divisions/reads'); ?>',
    //     valueField: 'id',
    //     textField: 'name',
    //     panelHeight: 'panelHeight',
    //     prompt: 'Choose Division',
    // });

    $("#filter_period").combobox({
        url: '<?= base_url('planning/production_schedule_press/readPeriod') ?>',
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
                url: '<?= base_url('planning/production_schedule_press/readWp?period=') ?>' + btoa(period),
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
        url: '<?= base_url('control/output_production_press/readNumber'); ?>',
        valueField: 'number',
        textField: 'number',
        prompt: 'Choose Doc No',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_workorder').combobox({
        url: '<?= base_url('control/output_production_press/readWoNos'); ?>',
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

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/readRubberParts'); ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No",
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
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 250
            }, ]
        ],
    });

    $("#machine_no_insert").combogrid({
        url: '<?= base_url('planning/production_schedule_press/readMachinePressMolds') ?>',
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