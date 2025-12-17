<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Customer is taken from <b>Master Data > Marketing > Customer</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:150,align:'center',sortable:true">Mold ID</th>
            <th rowspan="2" data-options="field:'mold_name',width:150,halign:'center',sortable:true">Mold Name</th>
            <th rowspan="2" data-options="field:'type',width:130,halign:'center',sortable:true">Property of</th>
            <th rowspan="2" data-options="field:'customer_name',width:200,halign:'center',sortable:true">Customer Name</th>
            <th rowspan="2" data-options="field:'project_year',width:150,halign:'center',sortable:true,formatter:formatYear">Project Year</th>
            <th rowspan="2" data-options="field:'total_mold',width:150,halign:'center',sortable:true">Total Mold</th>
            <th rowspan="2" data-options="field:'mold_no',width:150,halign:'center',sortable:true">Mold No</th>
            <th rowspan="2" data-options="field:'mold_year',width:150,halign:'center',sortable:true,formatter:formatYear">Mold Year</th>
            <th rowspan="2" data-options="field:'mold_size',width:150,halign:'center',sortable:true">Mold Size</th>
            <th rowspan="2" data-options="field:'standard_curing_time',width:150,halign:'center',sortable:true">Standard Curing <br>Time</th>
            <th rowspan="2" data-options="field:'cavity_standard',width:150,halign:'center',sortable:true">Standard Cavity</th>
            <th rowspan="2" data-options="field:'cavity_actual',width:150,halign:'center',sortable:true">Actual Cavity</th>
            <th rowspan="2" data-options="field:'shoot_standard',width:150,halign:'center',sortable:true">Standard Shoot</th>
            <th rowspan="2" data-options="field:'shoot_actual',width:150,halign:'center',sortable:true">Actual Shoot</th>
            <th rowspan="2" data-options="field:'target_shoot',width:150,halign:'center',sortable:true">Target Shoot</th>
            <th rowspan="2" data-options="field:'model',width:150,halign:'center',sortable:true">Mold Model</th>
            <th rowspan="2" data-options="field:'mold_type',width:80,halign:'center',sortable:true">Mold Type</th>
            <th rowspan="2" data-options="field:'remark',width:150,halign:'center',sortable:true">Remarks</th>
            <th rowspan="2" data-options="field:'status',width:150,halign:'center', styler:cellStyler, formatter:cellFormatter,sortable:true">Status</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1100px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold ID</span>
                    <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Name</span>
                    <input style="width:60%;" name="mold_name" id="mold_name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Property of</span>
                    <select style="width:60%;" name="type" id="type" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="EX">EXTERNAL</option>
                        <option value="IN">INTERNAL</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer_id" id="customer_id" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Project Year</span>
                    <input style="width:60%;" name="project_year" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Total Mold</span>
                    <input style="width:60%;" name="total_mold" id="total_mold" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold No</span>
                    <input style="width:60%;" name="mold_no" id="mold_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Year</span>
                    <input style="width:60%;" name="mold_year" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Size</span>
                    <input style="width:60%;" name="mold_size" id="mold_size" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Standard Curing Time</span>
                    <input style="width:60%;" name="standard_curing_time" class="easyui-numberbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Standard Cavity</span>
                    <input style="width:60%;" name="cavity_standard" id="cavity_standard" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Actual Cavity</span>
                    <input style="width:60%;" name="cavity_actual" id="cavity_actual" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Standard Shoot</span>
                    <input style="width:60%;" name="shoot_standard" id="shoot_standard" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Actual Shoot</span>
                    <input style="width:60%;" name="shoot_actual" id="shoot_actual" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Target Shoot</span>
                    <input style="width:60%;" name="target_shoot" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Model</span>
                    <select style="width:60%;" name="model" id="model" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="COM">COMPRESSION</option>
                        <option value="INJ">INJECTION</option>
                        <option value="TRF">TRANSFER</option>
                        <option value="EXT">EXTRUSION</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Mold Type</span>
                    <select style="width:60%;" name="mold_type" id="mold_type" panelHeight="auto" class="easyui-combobox">
                        <option value="SINGLE">SINGLE</option>
                        <option value="DOUBLE">DOUBLE</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remark" id="remark" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Active</option>
                        <option value="1">Not Active</option>
                    </select>
                </div>
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
<iframe id="printout" src="<?= base_url('master/molds/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/molds/create') ?>';
        $('#frm_insert').form('clear');
        $('#mold_type').combobox('setValue', 'SINGLE');
        $('#status').combobox('setValue', '0');
        $('#cavity_standard').numberbox('setValue', '1');
        $('#cavity_actual').numberbox('setValue', '1');
        $('#id').textbox('textbox').attr('placeholder', 'Auto Generate');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/molds/autoid') ?>",
            dataType: "html",
            success: function(response) {
                $('#id').textbox('setValue', response);
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            console.log(row);
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/molds/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/molds/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                if (result.theme == "success") {
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
        window.location.assign('<?= base_url('template/tmp_molds.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/molds/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    // // FORMAT tahun-bulan-tanggal
    // function myformatter(date) {
    //     var y = date.getFullYear();
    //     var m = date.getMonth(); // Mengambil indeks bulan (0 - Januari, 11 - Desember)
    //     var monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    //     return monthNames[m] + ' ' + y;
    // }

    // function myparser(s) {
    //     if (!s) return new Date();
    //     var parts = s.split(' ');
    //     if (parts.length === 2) {
    //         var monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    //         var m = monthNames.indexOf(parts[0]); // Mencari indeks bulan dari nama bulan
    //         var y = parseInt(parts[1]);
    //         if (m !== -1 && !isNaN(y)) {
    //             return new Date(y, m);
    //         }
    //     }
    //     return new Date();
    // }

    // FORMAT tahun-bulan-tanggal
    function myformatter(date) {
        if (!date) return '';
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function myparser(s) {
    if (!s || s === '0000-00-00') {
        return null; // biar datebox kosong
    }
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/molds/datatables') ?>',
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

        $('#type').combobox({
            url: '<?php echo base_url('master/molds/type'); ?>',
            valueField: 'value',
            textField: 'name',
            prompt: "Choose Type",
            onSelect: function(selectedOption_type) {
                // Ambil nilai yang dipilih dari combobox
                var selectedValue_type = selectedOption_type.value;
                var selectedName_type = selectedOption_type.name;

                $('#model').combobox({
                    url: '<?php echo base_url('master/molds/model'); ?>',
                    valueField: 'value',
                    textField: 'name',
                    prompt: "Choose Model",
                    onSelect: function(selectedOption_model) {
                        // Ambil nilai yang dipilih dari combobox
                        var selectedValue_model = selectedOption_model.value;
                        var selectedName_model = selectedOption_model.name;

                        // Lakukan permintaan AJAX untuk mendapatkan ID berdasarkan nilai yang dipilih
                        $.ajax({
                            type: "post",
                            url: '<?php echo base_url('master/molds/autoid/'); ?>' + selectedValue_type + '/' + selectedValue_model,
                            dataType: "html",
                            success: function(response) {
                                // Set nilai response ke elemen dengan ID '#id'
                                $('#id').textbox('setValue', response);
                            }
                        });
                    }
                });
            }
        });
    });



    $('#customer_id').combobox({
        url: '<?= base_url('master/customers/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Customer',
    });

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'Active';
        } else {
            return 'Not Active';
        }
    };

    // UPLOAD DATA
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('master/molds/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('master/molds/upload') ?>',
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
    //                         url: "<?= base_url('master/molds/uploadclearFailed') ?>"
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
    //                                 url: "<?= base_url('master/molds/uploadCreate') ?>",
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
    //                                             url: "<?= base_url('master/molds/uploadcreateFailed') ?>",
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
                window.open('<?= base_url('master/molds/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/molds/upload') ?>',
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
                            url: "<?= base_url('master/molds/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('master/molds/uploadCreate') ?>",
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


    function formatYear(value) {
    if (value == '0000-00-00') {
        return '';
    }
    return value;
    }
</script>