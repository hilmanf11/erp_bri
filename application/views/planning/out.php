<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="background: #F4F4F4;">
<?= $button ?>
<div style="display:flex;flex-direction:row;padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="display:flex;flex:1;flex-direction:column;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Chemical Weighting Software</b></legend>
            <div class="fitem">
                <!-- <span style="width:35%; display:inline-block;">Product No</span> -->
                <input style="width:100%;" id="item_fg_id" class="easyui-combobox">
            </div>
        <div style="display: flex;
            font-weight: bold;
            padding: 10px 0;justify-content:space-around;">
            <div style="width: 25%;"><span>Item Name</span></div>
            <div style="width: 25%;"><span>Location</span></div>
            <div style="width: 25%;"><span>Quantity</span></div>
            <div><span style="display:none;">Action</span></div>
        </div>
            <div id="inputContainer" class="fitem" style="height:250px;overflow:auto;margin-bottom:10px;">
                <!-- <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid"> -->
            </div>
            <div class="fitem" style="display:flex;flex-direction:row;justify-content:end;">
                <a href="javascript:;" class="easyui-linkbutton" style="background:linear-gradient(#1ab9c9, #1aa8c9) !important;" onclick="print()">Print</a>
            </div>
        </fieldset>
        <!-- <?= $button ?> -->
    </div>
    <div style="display:flex;flex:1;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Information</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" id="item_id" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item Name</span>
                <input style="width:60%;" id="item_name" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Qty</span>
                <input style="width:60%;" id="qty" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">UoM</span>
                <input style="width:60%;" id="filteuomr_item_rm_id" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Weight</span>
                <input style="width:60%;" id="weight" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tolerance</span>
                <input style="width:60%;" id="tolerance" class="easyui-textbox" disabled>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Tolerance Upper</span>
                <input style="width:60%;" id="tolerance_upper" class="easyui-textbox" disabled>
            </div>
            <div class="fitem" style="display:flex;flex-direction:row;">
                <span style="width:36%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" style="margin-right:15px;background:linear-gradient(#1ac975, #1ac92c) !important;" onclick="open()">Open</a>
                <a href="javascript:;" class="easyui-linkbutton" style="background:linear-gradient(#1ab9c9, #1aa8c9) !important;" onclick="closed()">Closed</a>
            </div>
        </fieldset>
    </div>
</div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/bom/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    function createRow() {
            var row = $('<div class="fitem" style="display:flex;flex-direction:row;justify-content:space-around;margin-bottom:10px;"></div>');
            row.append('<input type="text" class="easyui-textbox" style="width: 25%;"/>');
            row.append('<input type="text" class="easyui-textbox" style="width: 25%;" />');
            row.append('<input type="text" class="easyui-textbox" style="width: 25%;"/>');
            row.append('<a href="javascript:void(0)" class="easyui-linkbutton">Start</a>');
            return row;
    }
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/bom/create') ?>';
        $('#frm_insert').form('clear');
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
                            url: '<?= base_url('master/bom/delete') ?>',
                            data: {
                                item_fg_id: row.item_fg_id
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

    //FILTER DATA
    function filter() {
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        $('#dg').datagrid({
            url: '<?= base_url('master/bom/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/bom/print') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        var container = $('#inputContainer');
            for (var i = 0; i < 45; i++) {
                container.append(createRow());
            }
            $('.easyui-linkbutton').linkbutton();
    });

    $('#item_fg_id').combobox({
        url: '<?= base_url('master/item_fg/reads/'); ?>',
        valueField: 'id',
        textField: 'number',
        prompt: "Choose Compound Number",
        icons: [{
            iconCls: 'icon-clear',
            handler: function (e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/bom/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/bom/upload') ?>',
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
                            url: "<?= base_url('master/bom/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/bom/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/bom/uploadcreateFailed') ?>",
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