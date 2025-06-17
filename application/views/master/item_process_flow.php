<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:80,align:'center',sortable:true">Flow ID</th>
            <th rowspan="2" data-options="field:'name',width:100,halign:'center',sortable:true">Flow Type</th>
            <th rowspan="2" data-options="field:'process_a',width:150,halign:'center',sortable:true">WEIGHING</th>
            <th rowspan="2" data-options="field:'process_b',width:150,halign:'center',sortable:true">MIXING MB</th>
            <th rowspan="2" data-options="field:'process_l',width:150,halign:'center',sortable:true">MIXING FB</th>
            <!-- <th rowspan="2" data-options="field:'process_m',width:150,halign:'center',sortable:true">COOLING</th> -->
            <th rowspan="2" data-options="field:'process_c',width:150,halign:'center',sortable:true">CUTTING</th>
            <th rowspan="2" data-options="field:'process_d',width:150,halign:'center',sortable:true">BONDING</th>
            <th rowspan="2" data-options="field:'process_e',width:150,halign:'center',sortable:true">PRESS</th>
            <th rowspan="2" data-options="field:'process_n',width:150,halign:'center',sortable:true">EXTRUSION</th>
            <th rowspan="2" data-options="field:'process_m',width:150,halign:'center',sortable:true">COOLING</th>
            <th rowspan="2" data-options="field:'process_o',width:150,halign:'center',sortable:true">OVEN</th>
            <th rowspan="2" data-options="field:'process_f',width:150,halign:'center',sortable:true">FINISHING</th>
            <th rowspan="2" data-options="field:'process_g',width:150,halign:'center',sortable:true">VISUAL CHECK</th>
            <!-- <th rowspan="2" data-options="field:'process_n',width:150,halign:'center',sortable:true">SEALER</th> -->
            <th rowspan="2" data-options="field:'process_h',width:150,halign:'center',sortable:true">SUBCONT</th>
            <th rowspan="2" data-options="field:'process_i',width:150,halign:'center',sortable:true">SLITTING</th>
            <th rowspan="2" data-options="field:'process_j',width:150,halign:'center',sortable:true">POST CURE</th>
            <th rowspan="2" data-options="field:'process_k',width:150,halign:'center',sortable:true">PACKING</th>
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
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process ID</span>
                    <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Process Name</span>
                    <input style="width:60%;" name="name" id="item_process_id" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">WEIGHING</span>
                    <input style="width:60%;" name="process_a" id="process_a" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MIXING MB</span>
                    <input style="width:60%;" name="process_b" id="process_b" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MIXING FB</span>
                    <input style="width:60%;" name="process_l" id="process_l" class="easyui-textbox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">COOLING</span>
                    <input style="width:60%;" name="process_m" id="process_m" class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">CUTTING</span>
                    <input style="width:60%;" name="process_c" id="process_c" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">BONDING</span>
                    <input style="width:60%;" name="process_d" id="process_d" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PRESS</span>
                    <input style="width:60%;" name="process_e" id="process_e" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">EXTRUSION</span>
                    <input style="width:60%;" name="process_n" id="process_n" class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">COOLING</span>
                    <input style="width:60%;" name="process_m" id="process_m" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">OVEN</span>
                    <input style="width:60%;" name="process_o" id="process_o" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">FINISHING</span>
                    <input style="width:60%;" name="process_f" id="process_f" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">VISUAL CHECK</span>
                    <input style="width:60%;" name="process_g" id="process_g" class="easyui-textbox">
                </div>
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">SEALER</span>
                    <input style="width:60%;" name="process_n" id="process_n" class="easyui-textbox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">SUBCONT</span>
                    <input style="width:60%;" name="process_h" id="process_h" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">SLITTING</span>
                    <input style="width:60%;" name="process_i" id="process_i" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">POST CURE</span>
                    <input style="width:60%;" name="process_j" id="process_j" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PACKING</span>
                    <input style="width:60%;" name="process_k" id="process_k" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/item_process_flow/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_process_flow/create') ?>';
        $('#frm_insert').form('clear');
        $('#process_a').textbox('textbox').attr('placeholder', '0');
        $('#process_b').textbox('textbox').attr('placeholder', '0');
        $('#process_l').textbox('textbox').attr('placeholder', '0');
        $('#process_m').textbox('textbox').attr('placeholder', '0');
        $('#process_c').textbox('textbox').attr('placeholder', '0');
        $('#process_d').textbox('textbox').attr('placeholder', '0');
        $('#process_e').textbox('textbox').attr('placeholder', '0');
        $('#process_f').textbox('textbox').attr('placeholder', '0');
        $('#process_g').textbox('textbox').attr('placeholder', '0');
        $('#process_n').textbox('textbox').attr('placeholder', '0');
        $('#process_h').textbox('textbox').attr('placeholder', '0');
        $('#process_i').textbox('textbox').attr('placeholder', '0');
        $('#process_j').textbox('textbox').attr('placeholder', '0');
        $('#process_k').textbox('textbox').attr('placeholder', '0');
        $('#process_o').textbox('textbox').attr('placeholder', '0');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/item_process_flow/autoid') ?>",
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
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/item_process_flow/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_process_flow/delete') ?>',
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
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_process_flow/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_process_flow/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizable: true,
            remoteSort: false
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
</script>