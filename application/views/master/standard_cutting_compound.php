<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'attachment',width:150,halign:'center',formatter: btnDetails,sortable:true">Attachment</th>
            <th rowspan="2" data-options="field:'item_fg_id',align:'center',width:120,sortable:true">Product ID</th>
            <th rowspan="2" data-options="field:'item_fg_number',align:'center',width:120,sortable:true">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',align:'center',width:120,sortable:true">Product Name</th>
            <th rowspan="2" data-options="field:'compound_id',align:'center',width:120,sortable:true">Compound ID</th>
            <th rowspan="2" data-options="field:'compound_number_internal',align:'center',width:120,sortable:true">Compound Name</th>
            <!-- <th rowspan="2" data-options="field:'status',width:100, styler:cellStyler, align:'center', formatter:cellFormatter,sortable:true">Status</th> -->
            <!-- Dimension Group -->
            <th colspan="3" data-options="field:'',width:100,halign:'center'"> Dimension (mm)</th>
            <th rowspan="2" data-options="field:'weight',align:'center',width:120,sortable:true">Weight (gr)</th>            
            <!-- Weight Tolerance Group -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Weight Tolerance (gr)</th>
            <!-- Created Group -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>            
            <!-- Updated Group -->
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <!-- Dimension children -->
            <th data-options="field:'length',width:80,align:'center',sortable:true">Length (P)</th>
            <th data-options="field:'width',width:80,align:'center',sortable:true">Width (L)</th>
            <th data-options="field:'height',width:80,align:'center',sortable:true">Height (T)</th>
            <!-- Weight Tolerance children -->
            <th data-options="field:'tolerance_upper',width:80,align:'center',sortable:true">Upper</th>
            <th data-options="field:'tolerance_lower',width:80,align:'center',sortable:true">Lower</th>
            <!-- Created children -->
            <th data-options="field:'created_by',width:100,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center',sortable:true"> Date</th>
            <!-- Updated children -->
            <th data-options="field:'updated_by',width:100,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width:50%;float:left">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product ID</span>
                    <input style="width:60%;" name="item_fg_id" id="item_fg_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="item_fg_number" id="item_fg_number" required="" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="item_fg_name" id="item_fg_name" required="" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Compound ID</span>
                    <input style="width:60%;" name="compound_id" id="compound_id" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Compound No</span>
                    <input style="width:60%;" name="compound_number_internal" id="compound_number_internal" required="" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Length (P)</span>
                    <input style="width:40%;" name="length" id="length" class="easyui-numberbox" required>
                    <span style="padding-left: 8px;">Millimeter</span>
                </div>
            </div>

            <div style="width:50%;float:left">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Width (L)</span>
                    <input style="width:40%;" name="width" id="width" class="easyui-numberbox" required>
                    <span style="padding-left: 8px;">Millimeter</span>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Height (T)</span>
                    <input style="width:40%;" name="height" id="height" class="easyui-numberbox" required>
                    <span style="padding-left: 8px;">Millimeter</span>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Weight</span>
                    <input style="width:40%;" name="weight" id="weight" class="easyui-numberbox" data-options="precision:2" required>
                    <span style="padding-left: 8px;">Gram</span>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Tolerance Upper</span>
                    <input style="width:40%;" name="tolerance_upper" id="tolerance_upper" class="easyui-numberbox" data-options="precision:2">
                    <span style="padding-left: 8px;">Gram</span>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Tolerance Lower</span>
                    <input style="width:40%;" name="tolerance_lower" id="tolerance_lower" class="easyui-numberbox" data-options="precision:2">
                    <span style="padding-left: 8px;">Gram</span>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment_cutting_upload" id="attachment_cutting_upload" class="easyui-filebox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-textbox">
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
<iframe id="printout" src="<?= base_url('master/standard_cutting_compound/print') ?>" style="width: 100%;" hidden></iframe>
<script>

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/standard_cutting_compound/create') ?>';
        $('#frm_insert').form('clear');

    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/standard_cutting_compound/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/standard_cutting_compound/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This item cannot be deleted, Please make sure it didn't have any relation");
                                // $.messager.alert("Error", jqXHR.statusText, 'error');
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
        window.location.assign('<?= base_url('template/tmp_standard_cutting_compound.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/standard_cutting_compound/print/excel') ?>');
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


    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/standard_cutting_compound/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            fit: true,
            rownumbers: true,
            resizable: true,
            remoteSort: false
        }).datagrid('enableFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {

                    var item_fg_id = $("#item_fg_id").combogrid('getValue');
                    var compound_id = $("#compound_id").combogrid('getValue');
                    var length = $("#length").numberbox('getValue');
                    var width = $("#width").numberbox('getValue');
                    var height = $("#height").numberbox('getValue');
                    var weight = $("#weight").numberbox('getValue');
                    var tolerance_upper = $("#tolerance_upper").numberbox('getValue');
                    var tolerance_lower = $("#tolerance_lower").numberbox('getValue');
                    var attachment = $("#attachment").textbox('getValue');

                    $.ajax({
                        type: "post",
                        url: url_save,
                        data: {
                            item_fg_id: item_fg_id,
                            item_rm_id: compound_id,
                            length: length,
                            width: width,
                            height: height,
                            weight: weight,
                            tolerance_upper: tolerance_upper,
                            tolerance_lower: tolerance_lower,
                            attachment: attachment,
                        },
                        dataType: "json",
                        success: function(result) {
                            $('#dlg_insert').dialog('close');

                            Swal.fire({
                                title: result.message,
                                icon: result.theme,
                                confirmButtonText: 'Ok',
                                allowOutsideClick: false,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#dg').datagrid('reload');

                                }
                            });
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            toastr.clear();

                            if (jqXHR.responseText.includes("Error Number: 1062")) {
                                toastr.error("Product ID and Compound ID combination must be unique");
                            }else{
                                toastr.error("Failed to save data");
                            }

                            $('#dg').datagrid('reload');
                            $('#dlg_insert').dialog('close');
                        }
                    });
                }
            }]
        });

        //Upload Data
        // $('#dlg_upload').dialog({
        //     buttons: [{
        //         text: 'List Failed',
        //         handler: function() {
        //             window.open('<?= base_url('master/standard_cutting_compound/uploadDownloadFailed') ?>', '_blank');
        //         }
        //     }, {
        //         text: 'Upload',
        //         iconCls: 'icon-ok',
        //         handler: function() {
        //             $('#frm_upload').form('submit', {
        //                 url: '<?= base_url('master/standard_cutting_compound/upload') ?>',
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
        //                         url: "<?= base_url('master/standard_cutting_compound/uploadclearFailed') ?>"
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
        //                                 url: "<?= base_url('master/standard_cutting_compound/uploadCreate') ?>",
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
        //                                             url: "<?= base_url('master/standard_cutting_compound/uploadcreateFailed') ?>",
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
                    window.open('<?= base_url('master/standard_cutting_compound/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function () {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('master/standard_cutting_compound/upload') ?>',
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
                                url: "<?= base_url('master/standard_cutting_compound/uploadclearFailed') ?>" 
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
                                url: "<?= base_url('master/standard_cutting_compound/uploadCreate') ?>",
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

        $('#item_fg_id').combogrid({
            url: '<?= base_url('master/item_fg/readRubberParts') ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product ID",
            columns: [
                [{
                    field: 'id',
                    title: 'Product ID',
                    width: 100
                },{
                    field: 'number',
                    title: 'Product No',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 100
                }, ]
            ],
            onSelect: function (index, row) {
                $('#item_fg_number').textbox('setValue', row.number);
                $('#item_fg_name').textbox('setValue', row.name);
            }
        });

        $('#compound_id').combogrid({
            url: '<?= base_url('master/item_rm/readsC') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Compound ID",
            columns: [
                [{
                    field: 'id',
                    title: 'Part ID',
                    width: 100
                },{
                    field: 'number',
                    title: 'Part No',
                    width: 150
                }, ]
            ],
            onSelect: function (index, row) {
                $('#compound_number_internal').textbox('setValue', row.number);
            }
        });

    });

    function btnDetails(val, row, index) {
        var attachment = row.attachment;

        if (attachment != null && attachment != "") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/image/standard_cutting_compound/') ?>' + row.attachment + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
        } else {
            return '<center>-</center>';
        }
    }

    $('#attachment_cutting_upload').filebox({
        buttonText: 'Browse File',
        accept: '.jpg, .png, .pdf',
        onChange: function() {
            var files = $(this).filebox('files');
            var formData = new FormData();

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                formData.append('file', file, file.name);
            }

            $.ajax({
                url: '<?= base_url('master/standard_cutting_compound/upload_att_cutting') ?>',
                type: 'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(data) {
                    if (data.success == true) {
                        toastr.success(data.message);
                        console.log(data);
                        $('#attachment').textbox('setValue', data.filename);
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

</script>