<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATION" style="padding: 20px;">
            <ul>
                <li>The Data Customers is taken from <b>Master Data > Marketing > Customers</b></li>
                <li>The Data Line Production is taken from <b>Master Data > General Master > Line Production</b></li>
                <li>The Data Sales Order No is taken from the results of Customer selection and Get Data <b>Sales Order</b> Module</li>
                <li>The Data Product No is taken from the results of Sales Order No selection</li>
            </ul>
        </div>
        <div title="CONDITION" style="padding: 20px;">
            <ul>
                <li>If Status <b style="color: green">OPEN</b> then data new created in <b>Production Schedules</b></li>
                <li>If Status <b style="color: orange">SUPPLY</b> then data has been created in <b>Supply Sheet</b> when qty balance = 0</li>
                <li>If Status <b style="color: red">CLOSED</b> then data has been Scanned in <b>Scan Receipt FG</b></li>
                <li>If Qty in Production Schedule > Qty in Sales Order then <b style="color: red">ERROR</b></li>
            </ul>
        </div>
    </div>
</div>

<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'period',width:80,align:'center'">Period</th>
            <th rowspan="2" data-options="field:'wp',width:80,align:'center'">WP NO</th>
            <th rowspan="2" data-options="field:'status_wo',width:80,align:'center',formatter:statusformat,styler:statusStyle">Status WO</th>
            <th rowspan="2" data-options="field:'trans_date',width:150,align:'center'">WP Date</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Work Order</th>
            <th rowspan="2" data-options="field:'machine_number',width:120,align:'center'">Machine</th>
            <th rowspan="2" data-options="field:'process_name',width:100,align:'center'">Process</th>
            <th rowspan="2" data-options="field:'item_number',width:150">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200">Product Name</th>
            <th rowspan="2" data-options="field:'mold_id',width:200">Mold ID</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'status_mold',width:100,align:'center',formatter:statusMoldFormat, styler:statusMoldStyle">Status Mold</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 235px; padding:10px;">
    <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:60%;" name="filter_period" id="filter_period" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP Date</span>
                <input style="width:29.8%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                <input style="width:29.8%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Select Month'">
                <input style="width:30%;" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Select Year'" panelHeight="auto">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No</span>
                <input style="width:60%;" id="filter_machine_no" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Customer</span>
                <input style="width:60%;" id="filter_customers" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Sales Order</span>
                <input style="width:60%;" id="filter_sales_order" class="easyui-combobox">
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP No</span>
                <input style="width:60%;" id="filter_wp" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status WO</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="0">OPEN</option>
                    <option value="1">SUPPLY</option>
                    <option value="2">CLOSED</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status Mold</span>
                <select style="width:60%;" id="filter_status_mold" class="easyui-combobox" panelHeight="auto">
                    <option value="">Select All</option>
                    <option value="Change">Change</option>
                    <option value="Continue">Continue</option>
                </select>
            </div>
            <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
    </fieldset>
    <?= $button ?>

    <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="calculate_compound()">
        <i class="fa fa-calculator"></i> Calculate Compound
    </a>

    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');">
        <i class="fa fa-info"></i> Help
    </a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" id="month" name="month" required class="easyui-combobox">
                <input style="width:30%;" id="year" name="year" required class="easyui-combobox" panelHeight="auto">
            </div>
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
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP Date</span>
                <input style="width:60%;" name="trans_date" id="trans_date" required
                    class="easyui-datebox" data-options="formatter:myformatter,parser:myparser,editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">WP No</span>
                <input style="width:60%;" name="wp" id="wp" readonly required="" class="easyui-textbox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Process</span>
                <input style="width:60%;" name="process_id" required="" id="process_id" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Planning Pcs</span>
                <input style="width:60%;" name="qty" id="qty" required="" class="easyui-numberbox" readonly>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_calculate" class="easyui-dialog" title="Form Calculate Compound"
     data-options="closed:true,modal:true"
     style="width:520px; padding:10px; top:20px;">
    <form id="cal_frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom:10px; border-radius:4px;">
            <legend><b>Calculate Compound</b></legend>

            <div class="fitem" style="margin-bottom:10px;">
                <span style="width:35%; display:inline-block;">WP Press Date</span>
                <input style="width:26%;" id="calculate_from" name="start_date" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox" required>
                <span style="padding: 0 8px;">To</span>
                <input style="width:25.9%;" id="calculate_to" name="end_date" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox" required>
            </div>

            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" name="cal_item_fg_id" id="cal_item_fg_id" class="easyui-combogrid">
            </div> -->
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
<iframe id="printout" src="<?= base_url('planning/production_schedule_press/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    
    let suppressMonthYearChange = false;

    function calculate_compound() {
        $('#dlg_calculate').dialog('open');
        $('#cal_frm_insert').form('clear');
    }

    //HELP
    function helps() {
        $('#dlg_help').dialog('open');
    }
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('planning/production_schedule_press/create') ?>';
        $('#frm_insert').form('clear');

        suppressMonthYearChange = true;
        $("#month").combobox('setValue', "<?= date("m") ?>");
        $("#year").combobox('setValue', "<?= date("Y") ?>");
        suppressMonthYearChange = false;

        var today = '<?= date("Y-m-d") ?>';
        $('#trans_date').datebox('setValue', today);

        // ambil WP untuk hari ini
        $.post("<?= base_url('planning/production_schedule_press/get_wp_number') ?>", { trans_date: today }, function(res){
            var data = JSON.parse(res);
            $('#wp').textbox('setValue', data.wp);
        });

        $('#machine_id').combogrid('clear');
        $('#item_fg_id').combogrid('clear');
        $('#mold_id').combobox('clear');
        $('#process_id').combogrid('clear');
        $('#qty').numberbox('clear');
    }

    $('#trans_date').datebox({
        onSelect: function(date){
            var d = date.getFullYear() + "-" + 
                    ("0"+(date.getMonth()+1)).slice(-2) + "-" + 
                    ("0"+date.getDate()).slice(-2);
            $.post("<?= base_url('planning/production_schedule_press/get_wp_number') ?>", { trans_date: d }, function(res){
                var data = JSON.parse(res);
                $('#wp').textbox('setValue', data.wp);
            });
        }
    });

    //Edit Data
    // function update() {
    //     var row = $('#dg').datagrid('getSelected');
    //     if (row) {
    //         suppressMonthYearChange = true;
    //         $('#dlg_insert').dialog('open');
    //         $('#frm_insert').form('load', row);

    //         $('#machine_id').combogrid('setValue', row.machine_id);
    //         $('#machine_id').combogrid('setText', row.machine_number);

    //         $('#item_fg_id').combogrid('setValue', row.item_fg_id);
    //         $('#item_fg_id').combogrid('setText', row.item_number);

    //         url_save = '<?= base_url('planning/production_schedule_press/update') ?>?id=' + btoa(row.id);

    //         setTimeout(function(){
    //             suppressMonthYearChange = false;
    //         }, 200);
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            suppressMonthYearChange = true;
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            // Set URL untuk update
            url_save = '<?= base_url('planning/production_schedule_press/update') ?>?id=' + btoa(row.id);

            $('#machine_id').combogrid('setValue', row.machine_id);
            $('#machine_id').combogrid('setText', row.machine_number);

            // Reload item_fg_id berdasarkan machine_id
            $('#item_fg_id').combogrid({
                url: '<?= base_url("planning/production_schedule_press/readItemPressMolds/") ?>' + btoa(row.machine_id),
                panelWidth: 420,
                idField: 'id',
                textField: 'number',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Product No",
                columns: [[
                    { field: 'number', title: 'Product No', width: 100 },
                    { field: 'name', title: 'Product Name', width: 200 }
                ]],
                onSelect: function(index, row_fg) {
                    // ketika pilih item_fg → load mold
                    $("#process_id").combogrid("setValue", "PC006");

                    $('#mold_id').combobox({
                        url: '<?= base_url('planning/production_schedule_press/readSettingMolds/'); ?>' + window.btoa(row_fg.id) + '/' + window.btoa(row.machine_id),
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
                    // setelah item_fg_id terload → set value dari data lama
                    if (row.item_fg_id) {
                        // $('#item_fg_id').combogrid('setValue', row.item_fg_id);
                        // $('#item_fg_id').combogrid('setText', row.item_number);

                        $('#mold_id').combobox({
                            url: '<?= base_url('planning/production_schedule_press/readSettingMolds/'); ?>' + window.btoa(row.item_fg_id) + '/' + window.btoa(row.machine_id),
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

            $('#item_fg_id').combogrid('setValue', row.item_fg_id);
            $('#item_fg_id').combogrid('setText', row.item_number);

            setTimeout(function() {
                suppressMonthYearChange = false;
            }, 200);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/production_schedule_press/delete') ?>',
                            data: {
                                id: row.id,
                                item_fg_id: row.item_fg_id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                toastr.success(result.message);
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
        window.location.assign('<?= base_url('planning/production_schedule_press/exportTemplate') ?>');
        // window.location.assign('<?= base_url('template/tmp_prod_sch.xls') ?>');
    }

    function filter() {
        // var filter_month = $("#filter_month").combobox('getValue');
        // var filter_year = $("#filter_year").combobox('getValue');
        // var filter_customers = $("#filter_customers").combogrid('getValue');
        // var filter_sales_order = $("#filter_sales_order").combobox('getValue');

        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_machine_no = $("#filter_machine_no").combogrid('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_status_mold = $("#filter_status_mold").combobox('getValue');

        url = "?filter_period=" + filter_period + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_machine_no=" + filter_machine_no + "&filter_wp=" + filter_wp +  "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status + "&filter_status_mold=" + filter_status_mold;

        $('#dg').datagrid({
            url: '<?= base_url('planning/production_schedule_press/datatables') ?>' + url,
            fit: true,
            pagination: true,
            rownumbers: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        // $("#printout").attr('src', '<?= base_url('planning/production_schedule_press/print') ?>' + url);
        var cachebuster = "&_t=" + new Date().getTime();
        $("#printout").attr('src', '<?= base_url('planning/production_schedule_press/print') ?>' + url + cachebuster);

    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        // var filter_month = $("#filter_month").combobox('getValue');
        // var filter_year = $("#filter_year").combobox('getValue');
        // var filter_customers = $("#filter_customers").combogrid('getValue');
        // var filter_sales_order = $("#filter_sales_order").combobox('getValue');

        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_period = $("#filter_period").combobox('getValue');
        var filter_machine_no = $("#filter_machine_no").combogrid('getValue');
        var filter_wp = $("#filter_wp").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_status_mold = $("#filter_status_mold").combobox('getValue');


        url = "?filter_period=" + filter_period + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_machine_no=" + filter_machine_no + "&filter_wp=" + filter_wp +  "&filter_item_fg_id=" + filter_item_fg_id + "&filter_status=" + filter_status + "&filter_status_mold=" + filter_status_mold;

        window.location.assign('<?= base_url('planning/production_schedule_press/print/excel') ?>' + url);
    }

    function print_job_order() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            window.open('<?= base_url('planning/production_schedule_press/print_job_order/') ?>' + row.id, "_blank");
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        filter();

        $('#dlg_calculate').dialog({
            buttons: [{
                text: 'Start',
                iconCls: 'icon-ok',
                handler: function() {
                    var start_date = $('#calculate_from').datebox('getValue');
                    var end_date   = $('#calculate_to').datebox('getValue');
                    // var item_fg_id = $('#cal_item_fg_id').combogrid('getValue');

                    if (!start_date || !end_date) {
                        toastr.warning("WP Press Date is required", "Warning");
                        return;
                    }

                    Swal.fire({
                        title: 'Recalculating Compound...',
                        html: 'Please wait 5-10 minutes while data is being processed.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $('#dlg_calculate').dialog('close');
                        }
                    });

                    $.ajax({
                        url: "<?= base_url('planning/production_schedule_press/calculate_compound') ?>",
                        type: "POST",
                        dataType: "json",
                        data: {
                            start_date: start_date,
                            end_date: end_date,
                            // cal_item_fg_id: item_fg_id
                        },
                        success: function(response) {
                            Swal.close();

                            if (response.status === "success") {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Calculation Completed',
                                    text: response.message
                                }).then(() => {
                                    $('#dlg_calculate').dialog('close');
                                    $('#dg_request').datagrid('reload');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Calculation Failed',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            Swal.fire('Error', 'Failed to process request: ' + error, 'error');
                        }
                    });
                }
            }]
        });


        //Save Data
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

        // $('#cal_item_fg_id').combogrid({
        //     url: '<?= base_url('master/item_fg/readRubberPartAll') ?>',
        //     panelWidth: 400,
        //     idField: 'id',
        //     textField: 'number',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Product No",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Product No',
        //             width: 200
        //         }, {
        //             field: 'name',
        //             title: 'Product Name',
        //             width: 200
        //         }]
        //     ],
        // });

        //Get Customer
        $("#filter_machine_no").combogrid({
            url: '<?= base_url('planning/production_schedule_press/readMachinePressMolds') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
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

        // $("#filter_month").combobox({
        //     url: '<?= base_url('planning/production_schedule_press/readMonth') ?>',
        //     valueField: 'number',
        //     textField: 'name',
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //         }
        //     }],
        // });
        // $("#filter_year").combobox({
        //     url: '<?= base_url('planning/production_schedule_press/readYear') ?>',
        //     valueField: 'number',
        //     textField: 'number',
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //         }
        //     }],
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
                var period = data.period;

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
        
        // $('#filter_customers').combogrid({
        //     url: '<?= base_url('master/customers/reads') ?>',
        //     panelWidth: 420,
        //     idField: 'id',
        //     textField: 'name',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Select Customer",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Customer No',
        //             width: 120
        //         }, {
        //             field: 'name',
        //             title: 'Customer Name',
        //             width: 250
        //         }, ]
        //     ],
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combogrid('clear').combogrid('textbox').focus();
        //         }
        //     }],
        //     onSelect: function(val, row) {
        //         $("#filter_sales_order").combobox({
        //             url: '<?= base_url('planning/sales_orders/readSalesOrder/') ?>' + row.id,
        //             valueField: 'sales_order_no',
        //             textField: 'sales_order_no',
        //             prompt: "Select Sales Order",
        //             icons: [{
        //                 iconCls: 'icon-clear',
        //                 handler: function(e) {
        //                     $(e.data.target).combobox('clear').combobox('textbox').focus();
        //                 }
        //             }],
        //         });
        //     }
        // });

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/readRubberParts') ?>',
            panelWidth: 420,
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
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });
        $("#month").combobox({
            url: '<?= base_url('planning/production_schedule_press/readMonth') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Month"
        });
        $("#year").combobox({
            url: '<?= base_url('planning/production_schedule_press/readYear') ?>',
            valueField: 'number',
            textField: 'number',
            prompt: "Year"
        });
        // $("#machine_id").combobox({
        //     url: '<?= base_url('master/production_schedule_press/readMachinePressMolds') ?>',
        //     valueField: 'id',
        //     textField: 'name',
        //     prompt: "Choose Machines"
        // });

        $('#machine_id').combogrid({
            url: '<?= base_url('planning/production_schedule_press/readMachinePressMolds') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Machine No",
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
            onSelect : function(index, row) {
                console.log('Machine Select : ', row);
                $("#item_fg_id").combogrid({
                    url: '<?= base_url("planning/production_schedule_press/readItemPressMolds/") ?>' + btoa(row.id),
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Product No",
                    columns: [
                        [
                            { field: 'number', title: 'Product No', width: 100 },
                            { field: 'name', title: 'Product Name', width: 200 }
                        ]
                    ],
                    onSelect: function (index, row) {
                        console.log('ITEM FG : ', row);
                        
                        $("#process_id").combogrid("setValue", "PC006");

                        $('#mold_id').combobox({
                            url: '<?= base_url('planning/production_schedule_press/readSettingMolds/'); ?>' + window.btoa(row.id) + '/' + window.btoa(row.machine_id),
                            valueField: 'mold_id',
                            textField: 'mold_id',
                            prompt: 'Choose Mold ID',
                            onLoadSuccess: function(data) {
                                if (data.length === 1) {
                                    $('#mold_id').combobox('setValue', data[0].mold_id);

                                    loadQtyAuto(row.id, row.machine_id, data[0].mold_id);
                                }
                            },
                            onSelect: function(moldRow) {
                                loadQtyAuto(row.id, row.machine_id, moldRow.mold_id);
                            }
                        });

                    }
                });

            }
        });

        function loadQtyAuto(item_fg_id, machine_id, mold_id) {
            $.post("<?= base_url('planning/production_schedule_press/getCapacity') ?>",
            {
                item_fg_id: item_fg_id,
                machine_id: machine_id,
                mold_id: mold_id
            },
            function (res) {
                var data = JSON.parse(res);
                $('#qty').numberbox('setValue', data.capacity_day || '');
            });
        }

        // $("#item_fg_id").combogrid({
        //     url: '<?= base_url("planning/production_schedule_press/readItems2") ?>',
        //     panelWidth: 420,
        //     idField: 'id',
        //     textField: 'number',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Select Product No",
        //     columns: [
        //         [
        //             { field: 'number', title: 'Product No', width: 100 },
        //             { field: 'name', title: 'Product Name', width: 200 }
        //         ]
        //     ],
        //     onSelect: function (index, row) {
        //         // if (row.item_family_number === 'CD') {
        //         //     $('#process_id').combogrid('enable'); // Aktifkan combobox process_id
        //         // } else {
        //         //     $('#process_id').combogrid('disable'); // Nonaktifkan combobox process_id
        //         // }

        //         let item_family_number = row.item_family_number;

        //         console.log(item_family_number);

        //         $("#process_id").combogrid({
        //             url: '<?= base_url("planning/production_schedule_press/readProcess?item_family_number=") ?>' + item_family_number,
        //             panelWidth: 300,
        //             idField: 'id',
        //             textField: 'name',
        //             mode: 'remote',
        //             fitColumns: true,
        //             prompt: "Select Process",
        //             columns: [
        //                 [
        //                     { field: 'id', title: 'Process ID', width: 100 },
        //                     { field: 'name', title: 'Process Name', width: 200 }
        //                 ]
        //             ]
        //         });
        //     }
        // });

        // $("#process_id").combogrid({
        //     url: '<?= base_url("planning/production_schedule_press/readProcess") ?>',
        //     panelWidth: 300,
        //     idField: 'id',
        //     textField: 'name',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Select Process",
        //     columns: [
        //         [
        //             { field: 'id', title: 'Process ID', width: 100 },
        //             { field: 'name', title: 'Process Name', width: 200 }
        //         ]
        //     ]
        // });
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('planning/production_schedule_press/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('planning/production_schedule_press/upload') ?>',
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
                            url: "<?= base_url('planning/production_schedule_press/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('planning/production_schedule_press/uploadCreate') ?>",
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
                                toastr.error("Upload failed: " + error);
                                $.messager.alert("Upload Error", "An error occurred while saving the data", "error");
                            }
                        });
                    }
                });
            }
        }]
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

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if(value == 1){
            return "<b style='color:orange;'>SUPPLY</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusMoldFormat(value, row) {
        if (value == 'Change') {
            return "<b style='color:orange;'>Change</b>";
        } else if(value == 'Continue'){
            return "<b style='color: #2fa192;'>Continue</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if(value == 1){
            return 'background-color:#FFDFBD;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function statusMoldStyle(value, row, index) {
        if (value == 'Change') {
            return 'background-color:#FFDFBD;';
        } else if(value == 'Continue'){
            return 'background-color:#c3f8f1;';
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatInput(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return formatter.format(value);
    }

    var monthNames = [
        "Januari","Februari","Maret","April","Mei","Juni",
        "Juli","Agustus","September","Oktober","November","Desember"
    ];

    $('#month, #year').combobox({
        onChange: function(){
            
            if (suppressMonthYearChange) return;

            var month = $('#month').combobox('getValue');
            var year  = $('#year').combobox('getValue');
            if(month && year){
                // if(mode === "insert") {
                    var firstDay = year + '-' + ("0"+month).slice(-2) + '-01';
                    $('#trans_date').datebox('setValue', firstDay);

                    $.post("<?= base_url('planning/production_schedule_press/get_wp_number') ?>", 
                        { trans_date: firstDay }, 
                        function(res){
                            var data = JSON.parse(res);
                            $('#wp').textbox('setValue', data.wp);
                        }
                    );
                // }
            }
        }
    });

    $('#trans_date').datebox({
        onSelect: function(date){
            var month = $('#month').combobox('getValue');
            var month = $('#month').combobox('getValue');
            var year  = $('#year').combobox('getValue');

            if(month && year){
                var selectedMonth = date.getMonth() + 1;
                var selectedYear  = date.getFullYear();

                if(parseInt(month) !== selectedMonth || parseInt(year) !== selectedYear){
                    $.messager.alert(
                        'Warning', 
                        'Tanggal harus berada pada bulan ' + monthNames[parseInt(month)-1] + ' ' + year, 
                        'warning'
                    );

                    var firstDay = year + '-' + ("0"+month).slice(-2) + '-01';
                    $('#trans_date').datebox('setValue', firstDay);

                    $.post("<?= base_url('planning/production_schedule_press/get_wp_number') ?>", 
                        { trans_date: firstDay }, 
                        function(res){
                            var data = JSON.parse(res);
                            $('#wp').textbox('setValue', data.wp);
                        }
                    );

                    setTimeout(function(){
                        $('#trans_date').datebox('clear');
                        $('#wp').textbox('clear');
                    }, 100);

                } else {
                    var d = selectedYear + "-" + ("0"+selectedMonth).slice(-2) + "-" + ("0"+date.getDate()).slice(-2);
                    $.post("<?= base_url('planning/production_schedule_press/get_wp_number') ?>", 
                        { trans_date: d }, 
                        function(res){
                            var data = JSON.parse(res);
                            $('#wp').textbox('setValue', data.wp);
                        }
                    );
                }
            } else {
                $.messager.alert('Warning', 'Silakan pilih Month dan Year terlebih dahulu.', 'warning');
                $('#trans_date').datebox('clear');
                $('#wp').textbox('clear');
            }
        }
    });

</script>