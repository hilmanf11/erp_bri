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
                <li>The Data Cycle Time is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Productivity is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Cavity Actual is taken from <b>Master Data > Engineering > Menu Loading</b></li>
                <li>The Data Capacity/Hour is taken from <b>Calculation, to know any futher Please Check FORMULATION Below</b></li>
                <li>The Data Capacity/Shift is taken from <b>Calculation, to know any futher Please Check FORMULATION Below</b></li>
                <li>The Data Capacity/Day is taken from <b>Calculation, to know any futher Please Check FORMULATION Below</b></li>
            </ul>
        </div>
        <div title="FORMULATION" style="padding: 20px;">
            <ul>
                <li><b>Capacity/Hour :</b> (3600/Cycle Time) * Cavity Actual * (Productivity/100).</li>
                <li><b>Capacity/Shift :</b> (3600/Cycle Time) * Cavity Actual * (Productivity/100) * Capacity Hour.</li>
                <li><b>Capacity/Day :</b> ((3600/Cycle Time) * Cavity Actual * (Productivity/100) * Capacity Hour) * Shift hour * Shift.</li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_fg_number',width:150,align:'center',sortable:true">Product No.</th>
            <th rowspan="2" data-options="field:'machine_number',width:150,align:'center',sortable:true">Machine No.</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:150,halign:'center',sortable:true">Product Name</th>
            <th rowspan="2" data-options="field:'cycle_time',width:120,halign:'center',sortable:true">Cycle Time <br>(Second)</th>
            <th rowspan="2" data-options="field:'productcivity',width:120,halign:'center',sortable:true">Efficiency (%)</th>
            <th rowspan="2" data-options="field:'cavity_actual',width:120,halign:'center',sortable:true">Cavity Actual</th>
            <th rowspan="2" data-options="field:'capacity_hour',width:120,halign:'center',sortable:true">Capacity/Hour</th>
            <th rowspan="2" data-options="field:'capacity_shift',width:120,halign:'center',sortable:true">Capacity/Shift</th>
            <th rowspan="2" data-options="field:'capacity_day',width:120,halign:'center',sortable:true">Capacity/Day</th>
            <th rowspan="2" data-options="field:'remarks',width:150,halign:'center',sortable:true">Remarks</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No.</span>
                <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No.</span>
                <input style="width:60%;" name="machine_id" id="machine_id" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cycle Time</span>
                <input style="width:60%;" id="cycle_time" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Efficiency %</span>
                <input style="width:60%;" id="productcivity" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem" id="cavity_wrapper">
                <span style="width:35%; display:inline-block;">Cavity Actual</span>
                <input style="width:60%;" id="cavity_actual" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Capacity/Hour</span>
                <input style="width:60%;" name="capacity_hour" id="capacity_hour" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Capacity/Shift</span>
                <input style="width:60%;" name="capacity_shift" id="capacity_shift" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Capacity/Day</span>
                <input style="width:60%;" name="capacity_day" id="capacity_day" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
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
<iframe id="printout" src="<?= base_url('master/production_capacities/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/production_capacities/create') ?>';
        $('#frm_insert').form('clear');

    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/production_capacities/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/production_capacities/delete') ?>',
                            data: {
                                id: row.id
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
        window.location.assign('<?= base_url('template/tmp_production_capacities.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/production_capacities/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/production_capacities/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizable: true,
            remoteSort: false,
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

    $('#item_fg_id').combogrid({
        url: '<?php echo base_url('master/production_capacities/readItems'); ?>',
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
        onSelect: function(val, rows) {

            let { item_family_number } = rows;

            console.log(rows);

            if(item_family_number === "CD") {

                $('#cavity_actual').textbox('clear');
                $('#cavity_actual').textbox('disableValidation');
                $('#cavity_wrapper').hide();

                $("#machine_id").textbox('setValue', rows.machine_id);
                $("#cycle_time").textbox('setValue', rows.cycle_time);
                $("#productcivity").textbox('setValue', rows.productcivity);
    
                // var capacity_hour = Math.ceil((rows.shift_hour * 3600) / rows.cycle_time);
                // var capacity_hour = Math.ceil((3600 / rows.cycle_time) * rows.mpq * (rows.productcivity / 100));
                // var capacity_shift = Math.ceil((capacity_hour * rows.shift_hour));
                // var capacity_day = Math.ceil((capacity_shift * rows.shift));

                var cycle_per_hour = 3600 / rows.cycle_time; 
                var capacity_hour = cycle_per_hour * rows.mpq * (rows.productcivity / 100);

                capacity_hour = Math.ceil(capacity_hour / rows.mpq) * rows.mpq;
                var capacity_shift = Math.ceil(capacity_hour * rows.shift_hour);
                var capacity_day   = Math.ceil(capacity_shift * rows.shift);

    
                $("#capacity_hour").textbox('setValue', capacity_hour);
                $("#capacity_shift").textbox('setValue', capacity_shift);
                $("#capacity_day").textbox('setValue', capacity_day);
            }else{
                $('#cavity_actual').textbox('enableValidation');
                $('#cavity_wrapper').show();

                $("#machine_id").textbox('setValue', rows.machine_id);
                $("#cycle_time").textbox('setValue', rows.cycle_time);
                $("#productcivity").textbox('setValue', rows.productcivity);
                $("#cavity_actual").textbox('setValue', rows.cavity_actual);
    
                // var capacity_hour = Math.ceil((rows.shift_hour * 3600) / rows.cycle_time);
                var capacity_hour = Math.ceil((3600 / rows.cycle_time) * rows.cavity_actual * (rows.productcivity / 100));
                var capacity_shift = Math.ceil(capacity_hour * rows.shift_hour);
                var capacity_day = Math.ceil(capacity_shift * rows.shift);
    
                $("#capacity_hour").textbox('setValue', capacity_hour);
                $("#capacity_shift").textbox('setValue', capacity_shift);
                $("#capacity_day").textbox('setValue', capacity_day);
            }

        }
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('master/production_capacities/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/production_capacities/upload') ?>',
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
                            url: "<?= base_url('master/production_capacities/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('master/production_capacities/uploadCreate') ?>",
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
                                            }

                                        }, i * delayPerItem);
                                    });
                                }

                                $('#dg').datagrid('reload');
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