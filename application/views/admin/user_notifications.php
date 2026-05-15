<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'table_name',width:150,halign:'center'">Module</th>
            <th rowspan="2" data-options="field:'user_notification_name_1',width:125,align:'center'">Notification <br> Level 1</th>
            <th rowspan="2" data-options="field:'user_notification_name_2',width:125,align:'center'">Notification <br> Level 2</th>
            <th rowspan="2" data-options="field:'user_notification_name_3',width:125,align:'center'">Notification <br> Level 3</th>
            <th rowspan="2" data-options="field:'user_notification_name_4',width:125,align:'center'">Notification <br> Level 4</th>
            <th rowspan="2" data-options="field:'user_notification_name_5',width:125,align:'center'">Notification <br> Level 5</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Module</span>
                <input style="width:60%;" name="menus_id" id="menus_id" class="easyui-combogrid" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Notification Level 1</span>
                <input style="width:60%;" name="user_notification_1" id="user_notification_1" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Notification Level 2</span>
                <input style="width:60%;" name="user_notification_2" id="user_notification_2" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Notification Level 3</span>
                <input style="width:60%;" name="user_notification_3" id="user_notification_3" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Notification Level 4</span>
                <input style="width:60%;" name="user_notification_4" id="user_notification_4" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Notification Level 5</span>
                <input style="width:60%;" name="user_notification_5" id="user_notification_5" class="easyui-combobox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('admin/user_notifications/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('admin/user_notifications/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    // function update() {
    //     var row = $('#dg').datagrid('getSelected');
    //     if (row) {
    //         $('#dlg_insert').dialog('open');
    //         $('#frm_insert').form('load', row);
    //         url_save = '<?= base_url('admin/user_notifications/update') ?>?id=' + btoa(row.id);
    //     } else {
    //         toastr.info("Please select one of the data in the table first");
    //     }
    // }

    function update() {
        var row = $('#dg').datagrid('getSelected');

        if (row) {

            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);

            $('#user_notification_1').combogrid('setValue', row.user_notification_1);
            $('#user_notification_1').combogrid('setText', row.user_notification_name_1);

            $('#user_notification_2').combogrid('setValue', row.user_notification_2);
            $('#user_notification_2').combogrid('setText', row.user_notification_name_2);

            $('#user_notification_3').combogrid('setValue', row.user_notification_3);
            $('#user_notification_3').combogrid('setText', row.user_notification_name_3);

            $('#user_notification_4').combogrid('setValue', row.user_notification_4);
            $('#user_notification_4').combogrid('setText', row.user_notification_name_4);

            $('#user_notification_5').combogrid('setValue', row.user_notification_5);
            $('#user_notification_5').combogrid('setText', row.user_notification_name_5);

            url_save = '<?= base_url('admin/user_notifications/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.info("Please select one of the data in the table first");
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
                            url: '<?= base_url('admin/user_notifications/delete') ?>',
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
            toastr.info("Please select one of the data in the table first");
        }
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //EXPORT EXCEL
    function excel() {
        window.location.assign('<?= base_url('admin/user_notifications/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('admin/user_notifications/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
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
        //DATA USERS
        $('#user_notification_1').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_notification_2').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_notification_3').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_notification_4').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_notification_4').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });
        //DATA USERS
        $('#user_notification_5').combogrid({
            url: '<?= base_url('admin/setting_users/getusers') ?>',
            panelWidth: 420,
            idField: 'username',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select User",
            columns: [
                [{
                    field: 'username',
                    title: 'Username',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Fullname',
                    width: 250
                }, ]
            ]
        });

    });

    //DATA MENUS
    $('#menus_id').combogrid({
        url: '<?= base_url('admin/user_notifications/getMenuModule') ?>',
        panelWidth: 420,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Menu",
        columns: [
            [{
                field: 'parent_name',
                title: 'Parent Menu',
                width: 200
            },  
            {
                field: 'name',
                title: 'Name',
                width: 320
            }]
        ]
    });
</script>