<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Type is taken from <b>Master Data > PPIC > Man Power</b></li>
                <li>The Data Area is taken from <b>Master Data > PPIC > Delivery Areas</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'nik',width:220,align:'center',sortable:true">NIK</th>
            <th rowspan="2" data-options="field:'name',width:300,halign:'center',sortable:true">Name</th>
            <th rowspan="2" data-options="field:'position',width:220,halign:'center',sortable:true">Position</th>
            <th rowspan="2" data-options="field:'status',width:120,align:'center', styler:cellStyler, formatter:cellFormatter,sortable:true">Status</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:180,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:200,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:180,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:200,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a> -->
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">NIK</span>
                <input style="width:60%;" name="nik" id="nik" required class="easyui-numberbox" maxlength="100">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" id="name" required class="easyui-textbox" maxlength="100">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Position</span>
                <select style="width:60%;" id="position" name="position" panelHeight="auto" class="easyui-combobox" data-options="editable:false" required>
                    <option value="Press">Press</option>
                    <option value="Internal Process">Internal Process</option>
                    <option value="Visual Checker">Visual Checker</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                    <option value="0">Active</option>
                    <option value="1">Not Active</option>
                </select>
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
<iframe id="printout" src="<?= base_url('master/man_powers/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/man_powers/create') ?>';
        $('#frm_insert').form('clear');

        $('#nik').numberbox('enable');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $('#nik').numberbox('disable');

            url_save = '<?= base_url('master/man_powers/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/man_powers/delete') ?>',
                            data: {
                                id: row.id
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
        window.location.assign('<?= base_url('template/tmp_man_power.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/man_powers/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/man_powers/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            fitColumns: true,
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

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function () {
                window.open('<?= base_url('master/man_powers/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/man_powers/upload') ?>',
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
                            url: "<?= base_url('master/man_powers/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('master/man_powers/uploadCreate') ?>",
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
            return 'Non Active';
        }
    };

</script>