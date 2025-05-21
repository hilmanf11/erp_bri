<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_rm_id',width:100,align:'center',sortable:true">Part ID</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:150,halign:'center',sortable:true">Part No</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:250,halign:'center',sortable:true">Part Name</th>
            <th rowspan="2" data-options="field:'eq_1_name',width:150,halign:'center',sortable:true">Equivalent 1</th>
            <th rowspan="2" data-options="field:'eq_2_name',width:150,halign:'center',sortable:true">Equivalent 2</th>
            <th rowspan="2" data-options="field:'eq_3_name',width:150,halign:'center',sortable:true">Equivalent 3</th>
            <th rowspan="2" data-options="field:'eq_4_name',width:150,halign:'center',sortable:true">Equivalent 4</th>
            <th rowspan="2" data-options="field:'eq_5_name',width:150,halign:'center',sortable:true">Equivalent 5</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
        <?= $button ?>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" required="" class="easyui-combogrid">
            </div>
            <!-- <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:60%;" name="item_rm_name" id="item_rm_name" readOnly class="easyui-textbox">
            </div> -->
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Equivalent 1</span>
                <input style="width:60%;" name="eq_1" id="eq_1" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Equivalent 2</span>
                <input style="width:60%;" name="eq_2" id="eq_2" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Equivalent 3</span>
                <input style="width:60%;" name="eq_3" id="eq_3" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Equivalent 4</span>
                <input style="width:60%;" name="eq_4" id="eq_4" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Equivalent 5</span>
                <input style="width:60%;" name="eq_5" id="eq_5" class="easyui-combogrid">
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
<iframe id="printout" src="<?= base_url('master/equivalent/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/equivalent/create') ?>';
        $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/equivalent/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/equivalent/delete') ?>',
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
        window.location.assign('<?= base_url('master/equivalent/exportTemplate') ?>');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var url = "?filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        window.location.assign('<?= base_url('master/equivalent/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/equivalent/datatables') ?>',
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
                text: 'Save All',
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

    $('#item_rm_id').combogrid({
        url: '<?= base_url('master/equivalent/readRmId/'); ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Part Name",
        columns: [
            [{
                field: 'number',
                title: 'Part No',
                width: 120
            }, {
                field: 'name',
                title: 'Part Name',
                width: 250
            }, ]
        ],
        onSelect: function (item) {console.log(item)
            $("#item_rm_name").textbox('setValue', item.name);
        }
    });

$('#eq_1').combogrid({
    url: '<?= base_url('master/equivalent/readRmId/'); ?>',
    panelWidth: 420,
    idField: 'id',
    textField: 'name',
    mode: 'remote',
    fitColumns: true,
    prompt: "Choose Part Name",
    columns: [
        [{
            field: 'number',
            title: 'Part No',
            width: 120
        }, {
            field: 'name',
            title: 'Part Name',
            width: 250
        }, ]
    ]
});
$('#eq_2').combogrid({
    url: '<?= base_url('master/equivalent/readRmId/'); ?>',
    panelWidth: 420,
    idField: 'id',
    textField: 'name',
    mode: 'remote',
    fitColumns: true,
    prompt: "Choose Part Name",
    columns: [
        [{
            field: 'number',
            title: 'Part No',
            width: 120
        }, {
            field: 'name',
            title: 'Part Name',
            width: 250
        }, ]
    ]
});
$('#eq_3').combogrid({
    url: '<?= base_url('master/equivalent/readRmId/'); ?>',
    panelWidth: 420,
    idField: 'id',
    textField: 'name',
    mode: 'remote',
    fitColumns: true,
    prompt: "Choose Part Name",
    columns: [
        [{
            field: 'number',
            title: 'Part No',
            width: 120
        }, {
            field: 'name',
            title: 'Part Name',
            width: 250
        }, ]
    ]
});
$('#eq_4').combogrid({
    url: '<?= base_url('master/equivalent/readRmId/'); ?>',
    panelWidth: 420,
    idField: 'id',
    textField: 'name',
    mode: 'remote',
    fitColumns: true,
    prompt: "Choose Part Name",
    columns: [
        [{
            field: 'number',
            title: 'Part No',
            width: 120
        }, {
            field: 'name',
            title: 'Part Name',
            width: 250
        }, ]
    ]
});
$('#eq_5').combogrid({
    url: '<?= base_url('master/equivalent/readRmId/'); ?>',
    panelWidth: 420,
    idField: 'id',
    textField: 'name',
    mode: 'remote',
    fitColumns: true,
    prompt: "Choose Part Name",
    columns: [
        [{
            field: 'number',
            title: 'Part No',
            width: 120
        }, {
            field: 'name',
            title: 'Part Name',
            width: 250
        }, ]
    ]
});


    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/equivalent/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/equivalent/upload') ?>',
                    onSubmit: function() {
                        if ($(this).form('validate') == false) {
                            return $(this).form('validate');
                        } else {
                            $.messager.progress({
                                title: 'Please Wait',
                                msg: 'Importing Excel to Database'
                            });
                        }
                    },
                    success: function(result) {
                        $.messager.progress('close');
                        //Clear File
                        $.ajax({
                            url: "<?= base_url('master/equivalent/uploadclearFailed') ?>"
                        });
                        var json = eval('(' + result + ')');
                        requestData(json.total, json);

                        function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                            if (value < 100) {
                                value = Math.floor((number / total) * 100);
                                $('#p_upload').progressbar('setValue', value);
                                $('#p_start').html(number);
                                $('#p_finish').html(total);

                                $.ajax({
                                    type: "POST",
                                    async: true,
                                    url: "<?= base_url('master/equivalent/uploadCreate') ?>",
                                    data: {
                                        "data": json[number - 1]
                                    },
                                    cache: false,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            $('#p_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('master/equivalent/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks").append(title + "<br>");
                                    }
                                });
                            }
                        }
                    }
                });
            }
        }]
    });
</script>