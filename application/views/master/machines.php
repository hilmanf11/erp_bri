<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',align:'center',width:100">Machine ID</th>
            <th rowspan="2" data-options="field:'asset_id',halign:'center',width:150">Asset No</th>
            <th rowspan="2" data-options="field:'number',halign:'center',width:100">Machine No</th>
            <th rowspan="2" data-options="field:'name',align:'center',width:100">Name Of <br>Machine</th>
            <th rowspan="2" data-options="field:'item_type_process_name',align:'center',width:100">Process Type</th>
            <th rowspan="2" data-options="field:'specification',halign:'center',width:100">Specification</th>
            <th rowspan="2" data-options="field:'purchase_date',halign:'center',width:80">Purchase <br>Date</th>
            <th rowspan="2" data-options="field:'manufactur_date',halign:'center',width:100">Manufacturing <br>Date</th>
            <th rowspan="2" data-options="field:'maker',halign:'center',width:100">Maker</th>
            <th rowspan="2" data-options="field:'toonage',halign:'center',width:100">Toneage Of <br>Machine</th>
            <th rowspan="2" data-options="field:'item_uom_name',halign:'center',width:80">Uom</th>
            <th rowspan="2" data-options="field:'vacum',align:'center',width:100">Vacum</th>
            <th rowspan="2" data-options="field:'rt',align:'center',width:80">RT</th>
            <th rowspan="2" data-options="field:'item_type_name',width:100,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'brand',width:100,halign:'center'">Brand</th>
            <th rowspan="2" data-options="field:'status',width:100, styler:cellStyler, formatter:cellFormatter">Status</th>
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
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:30%;" name="id" id="id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset No</span>
                <input style="width:60%;" name="asset_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine No</span>
                <input style="width:60%;" name="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Machine Name</span>
                <input style="width:60%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Process Type</span>
                <input style="width:60%;" name="type_process_id" id="type_process_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Specification</span>
                <input style="width:60%;" name="specification" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Purchase Date</span>
                <input style="width:60%;" name="purchase_date" data-options="formatter:myformatter,parser:myparser" required="" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Manufacturing Date</span>
                <input style="width:60%;" name="manufactur_date" data-options="formatter:myformatter,parser:myparser" required="" class="easyui-datebox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Maker</span>
                <input style="width:60%;" name="maker" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tonage of Machine</span>
                <input style="width:30%;" name="toonage" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Unit of Measure</span>
                <input style="width:60%;" name="uom" id="uom" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
            <span style="width:35%; display:inline-block;">Vacum</span>
                <select style="width:30%;" name="vacum" class="easyui-combobox" panelHeight="auto">
                    <option value="EXPORT">YES</option>
                    <option value="IMPORT">NO</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">RT</span>
                <input style="width:30%;" name="rt" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <input style="width:60%;" name="type_id" id="type_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Brand</span>
                <input style="width:60%;" name="brand" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:30%;" name="status" required="" panelHeight="auto" class="easyui-combobox">
                    <option value="0">Active</option>
                    <option value="1">Inactive</option>
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
<iframe id="printout" src="<?= base_url('master/machines/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/machines/create') ?>';
        $('#frm_insert').form('clear');
        
        // auto id
        $.ajax({
            type: "post",
            url: '<?= base_url('master/machines/autoid') ?>',
            dataType: "html",
            success: function (response) {
                $('#id').textbox('setValue', response);
            }
        });
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/machines/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/machines/delete') ?>',
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

   //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_machines.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/machines/print/excel') ?>');
    }
    
    //RELOAD
    function reload() {
        window.location.reload();
    }

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
            return 'Inactive';
        }
    };

    // FORMAT tahun-bulan-tanggal
    function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/machines/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true
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
        // //GET CURRENCY
        // $('#currency').combogrid({
        //     url: '<?= base_url('master/currencies/reads') ?>',
        //     panelWidth: 420,
        //     idField: 'number',
        //     textField: 'name',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Currency",
        //     columns: [
        //         [{
        //             field: 'symbol',
        //             title: 'Symbol',
        //             width: 100
        //         }, {
        //             field: 'number',
        //             title: 'Currency ID',
        //             width: 120
        //         }, {
        //             field: 'name',
        //             title: 'Currency Name',
        //             width: 250
        //         }, ]
        //     ]
        // });

         //Upload Data
         $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('master/machines/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/machines/upload') ?>',
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
                                url: "<?= base_url('master/machines/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('master/machines/uploadCreate') ?>",
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
                                                    url: "<?= base_url('master/machines/uploadcreateFailed') ?>",
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


        $('#type_process_id').combobox({
            url: '<?= base_url('master/type_process/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Process Type"
        });

        $('#type_id').combobox({
            url: '<?= base_url('master/types/reads') ?>',
            valueField: 'id',
            textField: 'name',
            prompt: "Choose Type"
         });

        $('#uom').combobox({
            url: '<?= base_url('master/uom/reads') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Choose Unit Of Measure"
        });
    });
</script>