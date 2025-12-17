<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Part No Internal</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'request_no',width:150,align:'center'">Supply Sheet</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right', formatter:numberformat">MPQ</th>
            <th rowspan="2" data-options="field:'begin',width:80,halign:'center',align:'right', formatter:numberformat">Begin</th>
            <th rowspan="2" data-options="field:'need',width:80,halign:'center',align:'right', formatter:numberformat">Need</th>
            <th rowspan="2" data-options="field:'qty_req',width:80,halign:'center',align:'right', formatter:numberformat">Supply</th>
            <th rowspan="2" data-options="field:'issued',width:80,halign:'center',align:'right', formatter:numberformat">Issued</th>
            <th rowspan="2" data-options="field:'balance',width:80,halign:'center',align:'right', formatter:numberformat">Balance</th>
            <th rowspan="2" data-options="field:'warehouse',width:80,halign:'center',align:'right', formatter:numberformat">Warehouse</th>
            <th rowspan="2" data-options="field:'uom',width:100,align:'center'">Uom</th>
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

<div id="toolbar" style="height: 200px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 35%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>                
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supply No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
                </div>

                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
        </fieldset>
        <?= $button ?>

        <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="calculate_balance()">
            <i class="fa fa-calculator"></i> Calculate Balance
        </a>

    </div>
</div>

<!-- <div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div> -->

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supply Sheet</span>
                <input style="width:60%;" name="request_no" id="request_no" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Uom</span>
                <input style="width:60%;" name="uom" id="uom" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Begin</span>
                <input style="width:30%;" name="begin" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Need</span>
                <input style="width:30%;" name="need" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Issued</span>
                <input style="width:30%;" name="issued" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Warehouse</span>
                <input style="width:30%;" name="warehouse" class="easyui-numberbox" required data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_calculate" class="easyui-dialog" title="Form Calculate"
     data-options="closed:true,modal:true"
     style="width:500px; padding:10px; top:20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom:10px; border-radius:4px;">
            <legend><b>Calculate Balance</b></legend>

            <div class="fitem" style="margin-bottom:10px;">
                <span style="width:35%; display:inline-block;">Start Date</span>
                <input style="width:60%;" id="calculate_from"
                       name="start_date"
                       value="<?= date("Y-m-01") ?>"
                       data-options="formatter:myformatter,parser:myparser,editable:false"
                       class="easyui-datebox">
            </div>

            <div class="fitem" style="margin-bottom:10px;">
                <span style="width:35%; display:inline-block;">End Date</span>
                <input style="width:60%;" id="calculate_to"
                       name="end_date"
                       value="<?= date("Y-m-t") ?>"
                       data-options="formatter:myformatter,parser:myparser,editable:false"
                       class="easyui-datebox">
            </div>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="cal_item_rm_id"
                       id="cal_item_rm_id"
                       class="easyui-combogrid" required>
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
<iframe id="printout" src="<?= base_url('warehouse/wip_balances/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    function calculate_balance() {
        $('#dlg_calculate').dialog('open');
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('warehouse/wip_balances/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('warehouse/wip_balances/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('warehouse/wip_balances/delete') ?>',
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


    function filter() {
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');

        url = "?filter_item_rm_id=" + filter_item_rm_id + "&filter_request_no=" + filter_request_no;

        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_balances/datatables') ?>' + url,
            fit: true,
            pagination: true,
            rownumbers: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onLoadSuccess : function(data){
                console.log(data);
                
            }
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/wip_balances/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');

        url = "?filter_item_rm_id=" + filter_item_rm_id + "&filter_request_no=" + filter_request_no;

        window.location.assign('<?= base_url('warehouse/wip_balances/print/excel') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('warehouse/wip_balances/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_wip_balances.xls') ?>');
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_balances/datatables') ?>',
            pagination: true,
            clientPaging: false,
            // remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });
        // }).datagrid('enableFilter');



        $('#dlg_calculate').dialog({
            buttons: [{
                text: 'Start',
                iconCls: 'icon-ok',
                handler: function() {
                    var start_date = $('#calculate_from').datebox('getValue');
                    var end_date   = $('#calculate_to').datebox('getValue');
                    var item_rm_id = $('#cal_item_rm_id').combogrid('getValue');

                    if (!start_date || !end_date || !item_rm_id) {
                        toastr.warning("Please fill all fields before starting.", "Warning");
                        return;
                    }

                    Swal.fire({
                        title: 'Recalculating Balance...',
                        html: 'Please wait 5-10 minutes while data is being processed.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $('#dlg_calculate').dialog('close');
                        }
                    });

                    $.ajax({
                        url: "<?= base_url('warehouse/wip_balances/calculate_balance') ?>",
                        type: "POST",
                        dataType: "json",
                        data: {
                            start_date: start_date,
                            end_date: end_date,
                            cal_item_rm_id: item_rm_id
                        },
                        success: function(response) {
                            Swal.close();

                            if (response.status === "success") {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Balance Updated!',
                                    text: response.message
                                }).then(() => {
                                    $('#dlg_calculate').dialog('close');
                                    $('#dg_request').datagrid('reload');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
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
                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });
        $('#cal_item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number_internal',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number_internal',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
        });
        $('#filter_item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number_internal',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number_internal',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
            onSelect: function(valItem, rowItem) {
                $("#uom").textbox('setValue', rowItem.uom);
            }
        });
        $("#request_no").combobox({
            url: '<?= base_url('planning/supply_sheets/readRequestNo') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Supply Sheet"
        });

        $("#filter_request_no").combobox({
            url: '<?= base_url('warehouse/wip_balances/readRequestNoWP') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Select Supply No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    });

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

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('warehouse/wip_balances/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('warehouse/wip_balances/upload') ?>',
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
                            url: "<?= base_url('warehouse/wip_balances/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('warehouse/wip_balances/uploadCreate') ?>",
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
                                                url: "<?= base_url('warehouse/wip_balances/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        // $('#dg').datagrid('reload');
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