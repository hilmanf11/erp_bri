<style>
    .window-shadow{
        background: none !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
    }
</style>

<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Product No is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
                <li>The Data Mold ID is taken from <b>Master Data > Engineering > Master Mold</b></li>
                <li>The Data Machine No is taken from <b>Master Data > Maintenance > Machines</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead frozen="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'item_fg_id',width:150,align:'center',sortable:true">Product ID</th>
            <th data-options="field:'item_fg_number',width:150,halign:'center',sortable:true">Product No.</th>
            <th data-options="field:'item_fg_name',width:200,halign:'center',sortable:true">Product Name</th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'machine_number',width:150,halign:'center',sortable:true">Machine No.</th>
            <th rowspan="2" data-options="field:'machine_toonage',width:150,halign:'center',sortable:true">Toonage of Machine </th>
            <th rowspan="2" data-options="field:'mold_id',width:150,halign:'center',sortable:true">Mold ID</th>
            <th rowspan="2" data-options="field:'mold_cavity_actual',width:100,halign:'center',sortable:true">Cavity Actual</th>
            <th rowspan="2" data-options="field:'mold_cavity_standard',width:120,halign:'center',sortable:true">Cavity Standard</th>
            <th rowspan="2" data-options="field:'shift',width:100,halign:'center',sortable:true">Shift</th>
            <th rowspan="2" data-options="field:'shift_hour',width:100,halign:'center',sortable:true">Hour/Shift</th>
            <th rowspan="2" data-options="field:'productcivity',width:100,halign:'center',sortable:true">Efficiency (%)</th>
            <th rowspan="2" data-options="field:'cycle_time',width:90,halign:'center',sortable:true">Cycle Time <br>(Second)</th>
            <!-- <th rowspan="2" data-options="field:'cycle_time_process',width:150,halign:'center',sortable:true">Cycle Time Second <br>Process</th> -->
            <th rowspan="2" data-options="field:'manpower',width:100,halign:'center',sortable:true">Man Power</th>
            <th rowspan="2" data-options="field:'runner',width:110,halign:'center',sortable:true">Compound/Shoot</th>
            <th rowspan="2" data-options="field:'priority',width:110,halign:'center',sortable:true">Priority</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machines</span>
                <input style="width:60%;" name="machine_id" id="machine_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="item_fg_id" required="" id="item_fg_id" class="easyui-combogrid">
            </div>
            <div class="fitem" id="mold_wrapper">
                <span style="width:35%; display:inline-block;">Mold ID</span>
                <input style="width:60%;" name="mold_id" id="mold_id" required="" class="easyui-combobox">
            </div>

            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No.</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem" id="mold_wrapper">
                <span style="width:35%; display:inline-block;">Mold ID</span>
                <input style="width:60%;" name="mold_id" id="mold_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No.</span>
                <input style="width:60%;" name="machine_id" id="machine_id" required="" class="easyui-combobox">
            </div> -->

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift</span>
                <input style="width:60%;" name="shift" id="shift" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Shift/Hour</span>
                <input style="width:60%;" name="shift_hour" id="shift_hour" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Efficiency</span>
                <input style="width:60%;" name="productcivity" id="productcivity" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:44%;" name="cycle_time" id="cycle_time" readonly="" precision="2" class="easyui-numberbox">
                <span style="padding-left: 5px;">Second</span>
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time Process</span>
                <input style="width:60%;" name="cycle_time_process" id="cycle_time_process" precision="2" class="easyui-numberbox">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Man Power</span>
                <input style="width:60%;" name="manpower" id="manpower" class="easyui-numberbox">
            </div>
            <div class="fitem" id="runner-wrapper">
                <span style="width:35%; display:inline-block;">Compound/Shoot</span>
                <input style="width:48%;" name="runner" id="runner" precision="5" class="easyui-numberbox">
                <span style="padding-left: 5px;">Gram</span>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Priority</span>
                <input style="width:60%;" name="priority" id="priority" class="easyui-numberbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
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
<iframe id="printout" src="<?= base_url('master/menu_loadings/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/menu_loadings/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (!row) {
            toastr.warning("Please select data first");
            return;
        }

        console.log('ROW : ', row);
        

        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('load', row);

        $('#machine_id').combogrid('setValue', row.machine_id);

        url_save = '<?= base_url('master/menu_loadings/update') ?>?id=' + btoa(row.id);


        // if(row.mold_id == null) {
        //     $('#mold_id').combobox('clear');
        //     $('#mold_id').combobox('disableValidation'); 
        //     $('#mold_wrapper').hide();
        //     $('#runner-wrapper').hide();
        // }else{
        //     $('#mold_wrapper').show();
        //     $('#runner-wrapper').show();
        //     $('#mold_id').combobox('enableValidation');
        // }


        let { item_family_number, machine_id } = row;

        if (item_family_number === 'CD') {
            $('#mold_id').combobox('clear');
            $('#mold_id').combobox('disableValidation'); 
            $('#mold_wrapper').hide();
            $('#runner-wrapper').hide();

            $('#item_fg_id').combogrid({
                url: '<?php echo base_url('master/menu_loadings/readItemMachines/'); ?>'+ btoa(row.machine_id),
                required: true,
                panelWidth: 500,
                idField: 'item_fg_id',
                textField: 'item_fg_number',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Product No',
                columns: [
                    [{
                        field: 'item_fg_id',
                        title: 'Product ID',
                        width: 120
                    }, {
                        field: 'item_fg_number',
                        title: 'Product No.',
                        width: 150
                    }, {
                        field: 'item_fg_name',
                        title: 'Product Name',
                        width: 200
                    }]
                ],
                onSelect: function(index, row) {
                    $('#cycle_time').numberbox('setValue', row.cycle_time || 0);
                },
                onLoadSuccess: function(data) {
                    // console.log('Data', data);

                    if (data.rows && data.rows.length === 1) {
                        $('#item_fg_id').combogrid('grid').datagrid('selectRow', 0);
                        $('#item_fg_id').combogrid('setValue', data.rows[0].item_fg_id);
                    }

                }
            });

        } else {
            $('#mold_wrapper').show();
            $('#runner-wrapper').show();
            $('#mold_id').combobox('enableValidation');

            $('#item_fg_id').combogrid({
                url: '<?php echo base_url('master/menu_loadings/readItemMachines/'); ?>'+ btoa(row.machine_id),
                required: true,
                panelWidth: 500,
                idField: 'item_fg_id',
                textField: 'item_fg_number',
                valueField: 'item_fg_id',
                mode: 'remote',
                fitColumns: true,
                prompt: 'Choose Product No',
                columns: [
                    [{
                        field: 'item_fg_id',
                        title: 'Product ID',
                        width: 120
                    }, {
                        field: 'item_fg_number',
                        title: 'Product No.',
                        width: 150
                    }, {
                        field: 'item_fg_name',
                        title: 'Product Name',
                        width: 200
                    }]
                ],
                onSelect: function(index, row) {
                    $('#cycle_time').numberbox('setValue', row.cycle_time || 0);

                    $('#mold_id').combobox({
                        url: '<?= base_url('master/menu_loadings/readSettingMolds/'); ?>' + btoa(row.item_fg_id) 
                        + '/'+ btoa(machine_id),
                        valueField: 'mold_id',
                        textField: 'mold_id',
                        prompt: 'Choose Mold ID',
                        onLoadSuccess: function(data) {
                            if (data.length === 1) {
                                $('#mold_id').combobox('setValue', data[0].mold_id);
                            }
                        }
                    });
                },
                onLoadSuccess: function(data) {
                    if (row.item_fg_id) {
                        $('#mold_id').combobox({
                            url: '<?= base_url('master/menu_loadings/readSettingMolds/'); ?>' + window.btoa(row.item_fg_id) + '/' + window.btoa(row.machine_id),
                            valueField: 'mold_id',
                            textField: 'mold_id',
                            prompt: 'Choose Mold ID',
                            onLoadSuccess: function(data) {
                                if (data.length === 1) {
                                    $('#mold_id').combobox('setValue', data[0].mold_id);
                                } else if (row.mold_id) {
                                    $('#mold_id').combobox('setValue', row.mold_id);
                                }
                            }
                        });
                    }
                }

            });

        }


        $('#item_fg_id').combogrid('setValue', row.item_fg_id);
        $('#item_fg_id').combogrid('setText', row.item_fg_number);

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
                            url: '<?= base_url('master/menu_loadings/delete') ?>',
                            data: {
                                id: row.id
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
        window.location.assign('<?= base_url('template/tmp_menu_loadings.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/menu_loadings/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/menu_loadings/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizable: true,
            remoteSort: false,
            frozenColumns: [
                [{
                        field: 'item_fg_id',
                        title: 'Product ID',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'item_fg_number',
                        title: 'Product No.',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'item_fg_name',
                        title: 'Product Name',
                        width: 200,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                ]
            ],
            columns: [
                [{
                        field: 'machine_number',
                        title: 'Machine No.',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'machine_toonage',
                        title: 'Toonage of Machine',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'mold_id',
                        title: 'Mold ID',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'mold_cavity_actual',
                        title: 'Cavity Actual',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'mold_cavity_standard',
                        title: 'Cavity Standard',
                        width: 120,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'shift',
                        title: 'Shift',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'shift_hour',
                        title: 'Hour/Shift',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'productcivity',
                        title: 'Efficiency (%)',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'cycle_time',
                        title: 'Cycle Time <br>(Second)',
                        width: 90,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    // {
                    //     field: 'cycle_time_process',
                    //     title: 'Cycle Time Second <br>Process',
                    //     width: 150,
                    //     align: 'center',
                    //     sortable: true,
                    //     resizable: true
                    // },
                    {
                        field: 'manpower',
                        title: 'Man Power',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'runner',
                        title: 'Compound/Shoot',
                        width: 130,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'priority',
                        title: 'Priority',
                        width: 110,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'created_by',
                        title: 'Created By',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'created_date',
                        title: 'Created Date',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'updated_by',
                        title: 'Updated By',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                    {
                        field: 'updated_date',
                        title: 'Updated Date',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true
                    },
                ]
            ]
        }).datagrid('enableFilter');
        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
    });

    // $('#item_fg_id').combobox({
    //     url: '<?= base_url('master/item_fg/reads/'); ?>',
    //     valueField: 'id',
    //     textField: 'number',
    //     prompt: 'Choose Product No.',
    //     onSelect: function(item_fg) {
    //         $('#mold_id').combobox({
    //             url: '<?= base_url('master/molds/reads/'); ?>' + btoa(item_fg.id),
    //             valueField: 'id',
    //             textField: 'id',
    //             prompt: 'Choose Mold ID',
    //         });
    //     }
    // });
    
    // $('#item_fg_id').combogrid({
    //     url: '<?php echo base_url('master/menu_loadings/readItems'); ?>',
    //     required: true,
    //     panelWidth: 500,
    //     idField: 'item_fg_id',
    //     textField: 'item_fg_number',
    //     mode: 'remote',
    //     fitColumns: true,
    //     prompt: 'Choose Product No',
    //     columns: [
    //         [{
    //             field: 'item_fg_id',
    //             title: 'Product ID',
    //             width: 120
    //         }, {
    //             field: 'item_fg_number',
    //             title: 'Product No.',
    //             width: 150
    //         }, {
    //             field: 'item_fg_name',
    //             title: 'Product Name',
    //             width: 200
    //         }]
    //     ],
    //     onSelect: function(index, row) {
    //         let { item_family_number } = row;

    //         if (item_family_number === 'CD') {
    //             // $('#mold_id').combobox('disable');

    //             $('#mold_id').combobox('clear');
    //             $('#mold_id').combobox('disableValidation'); 
    //             $('#mold_wrapper').hide();
    //             $('#runner-wrapper').hide();
    //         } else {
    //             // $('#mold_id').combobox('enable');
                
    //             $('#mold_wrapper').show();
    //             $('#runner-wrapper').show();
    //             $('#mold_id').combobox('enableValidation');

    //             $('#mold_id').combobox({
    //                 url: '<?= base_url('master/menu_loadings/readSettingMolds/'); ?>' + btoa(row.item_fg_id),
    //                 valueField: 'mold_id',
    //                 textField: 'mold_id',
    //                 prompt: 'Choose Mold ID',
    //                 onLoadSuccess: function(data) {
    //                     if (data.length === 1) {
    //                         $('#mold_id').combobox('setValue', data[0].mold_id);
    //                     }
    //                 }
    //             });
    //         }

    //         $('#machine_id').combobox({
    //             url: '<?= base_url('master/menu_loadings/readMachines/'); ?>' + btoa(row.item_fg_id) + '/' + btoa(item_family_number),
    //             valueField: 'machine_id',
    //             textField: 'number',
    //             prompt: 'Choose Machine No.',
    //             onLoadSuccess: function(data) {
    //                 console.log('Data :  ', data);
    //                 if (data.length === 1) {
    //                     $('#machine_id').combobox('setValue', data[0].machine_id);
    //                 }

    //                 $('#cycle_time').numberbox('setValue', data[0].cycle_time ? data[0].cycle_time : 0);
    //             }
    //         });
    //     }
    // });


    // $('#item_fg_id').combogrid({
    //     url: '<?php echo base_url('master/menu_loadings/readItems'); ?>',
    //     required: true,
    //     panelWidth: 500,
    //     idField: 'item_fg_id',
    //     textField: 'item_fg_number',
    //     mode: 'remote',
    //     fitColumns: true,
    //     prompt: 'Choose Product No',
    //     columns: [
    //         [{
    //             field: 'item_fg_id',
    //             title: 'Product ID',
    //             width: 120
    //         }, {
    //             field: 'item_fg_number',
    //             title: 'Product No.',
    //             width: 150
    //         }, {
    //             field: 'item_fg_name',
    //             title: 'Product Name',
    //             width: 200
    //         }]
    //     ],
    //     onSelect: function(index, row) {
    //         let { item_family_number } = row;

    //         if (item_family_number === 'CD') {
    //             // $('#mold_id').combobox('disable');

    //             $('#mold_id').combobox('clear');
    //             $('#mold_id').combobox('disableValidation'); 
    //             $('#mold_wrapper').hide();
    //             $('#runner-wrapper').hide();
    //         } else {
    //             // $('#mold_id').combobox('enable');
                
    //             $('#mold_wrapper').show();
    //             $('#runner-wrapper').show();
    //             $('#mold_id').combobox('enableValidation');

    //             $('#mold_id').combobox({
    //                 url: '<?= base_url('master/menu_loadings/readSettingMolds/'); ?>' + btoa(row.item_fg_id),
    //                 valueField: 'mold_id',
    //                 textField: 'mold_id',
    //                 prompt: 'Choose Mold ID',
    //                 onLoadSuccess: function(data) {
    //                     if (data.length === 1) {
    //                         $('#mold_id').combobox('setValue', data[0].mold_id);
    //                     }
    //                 }
    //             });
    //         }

    //         $('#machine_id').combobox({
    //             url: '<?= base_url('master/menu_loadings/readMachines/'); ?>' + btoa(row.item_fg_id) + '/' + btoa(item_family_number),
    //             valueField: 'machine_id',
    //             textField: 'number',
    //             prompt: 'Choose Machine No.',
    //             onLoadSuccess: function(data) {
    //                 console.log('Data :  ', data);
    //                 if (data.length === 1) {
    //                     $('#machine_id').combobox('setValue', data[0].machine_id);
    //                 }

    //                 $('#cycle_time').numberbox('setValue', data[0].cycle_time ? data[0].cycle_time : 0);
    //             }
    //         });
    //     }
    // });

    $('#machine_id').combogrid({
        url: '<?= base_url('master/menu_loadings/readMachines/'); ?>',
        panelWidth: 450,
        idField: 'machine_id',
        valueField: 'machine_id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: 'Choose Machine No.',
        columns: [
            [{
                field: 'number',
                title: 'Machine No',
                width: 150
            }, {
                field: 'type_process_name',
                title: 'Process Type',
                width: 150
            }, {
                field: 'toonage',
                title: 'Tooneage of Machine',
                width: 150
            }]
        ],
        onSelect: function(index, row) {

            let { item_family_number, machine_id } = row;

            if (item_family_number === 'CD') {
                $('#mold_id').combobox('clear');
                $('#mold_id').combobox('disableValidation'); 
                $('#mold_wrapper').hide();
                $('#runner-wrapper').hide();

                $('#item_fg_id').combogrid({
                    url: '<?php echo base_url('master/menu_loadings/readItemMachines/'); ?>'+ btoa(row.machine_id),
                    required: true,
                    panelWidth: 500,
                    idField: 'item_fg_id',
                    textField: 'item_fg_number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product No',
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            width: 120
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            width: 150
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            width: 200
                        }]
                    ],
                    onSelect: function(index, row) {
                        $('#cycle_time').numberbox('setValue', row.cycle_time || 0);
                    },
                    onLoadSuccess: function(data) {
                        console.log('Data', data);

                        if (data.rows && data.rows.length === 1) {
                            $('#item_fg_id').combogrid('grid').datagrid('selectRow', 0);
                            $('#item_fg_id').combogrid('setValue', data.rows[0].item_fg_id);
                        }

                    }
                });

            } else {
                // $('#mold_id').combobox('enable');
                
                $('#mold_wrapper').show();
                $('#runner-wrapper').show();
                $('#mold_id').combobox('enableValidation');

                $('#item_fg_id').combogrid({
                    url: '<?php echo base_url('master/menu_loadings/readItemMachines/'); ?>'+ btoa(row.machine_id),
                    required: true,
                    panelWidth: 500,
                    idField: 'item_fg_id',
                    textField: 'item_fg_number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Product No',
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            width: 120
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            width: 150
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            width: 200
                        }]
                    ],
                    onSelect: function(index, row) {
                        $('#cycle_time').numberbox('setValue', row.cycle_time || 0);

                        $('#mold_id').combobox({
                            url: '<?= base_url('master/menu_loadings/readSettingMolds/'); ?>' + btoa(row.item_fg_id) 
                            + '/'+ btoa(machine_id),
                            valueField: 'mold_id',
                            textField: 'mold_id',
                            prompt: 'Choose Mold ID',
                            onLoadSuccess: function(data) {
                                if (data.length === 1) {
                                    $('#mold_id').combobox('setValue', data[0].mold_id);
                                }
                            }
                        });
                    },
                });

            }
        }
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('master/menu_loadings/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/menu_loadings/upload') ?>',
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
                            url: "<?= base_url('master/menu_loadings/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('master/menu_loadings/uploadCreate') ?>",
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
</script>