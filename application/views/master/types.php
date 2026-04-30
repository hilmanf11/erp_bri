<div id="dlg_info" class="easyui-dialog" title="Information" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" data-options="selected:false" style="width:100%; height: 100%;">
        <div title="English" style="padding: 20px;">
            <p>
                <b>General Information:</b></br>
                The Machine Type module stores master data for classifying machines based on their operational or process type.
            </p>

            <ul>
                <li>ID identifies the machine type record in the system.</li>
                <li>Code represents the short code for the machine type.</li>
                <li>Name indicates the machine type name.</li>
                <li>Description provides additional information about the machine type, if required.</li>
            </ul>

            <span>Machine Type data is used as a reference in machine master data and production-related processes.</span></br></br>

            <span>Changes will apply only to data and transactions created after the changes are made and will not affect existing data.</span>
        </div>
        <div title="Indonesian" style="padding: 20px;">
            <p>
                <b>Informasi Umum:</b></br>
                Modul Machine Type menyimpan data master pengelompokan jenis mesin berdasarkan fungsi atau proses operasionalnya.
            </p>

            <ul>
                <li>ID menunjukkan identitas jenis mesin di dalam sistem.</li>
                <li>Code merepresentasikan kode singkat jenis mesin.</li>
                <li>Name menunjukkan nama jenis mesin.</li>
                <li>Description berisi keterangan tambahan mengenai jenis mesin, jika diperlukan.</li>
            </ul>

            <span>Data Machine Type digunakan sebagai acuan pada master mesin dan proses terkait produksi.</span></br></br>

            <span>Perubahan data hanya berlaku untuk data dan transaksi yang dibuat setelah perubahan dilakukan dan tidak mempengaruhi data yang sudah ada.</span>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:80,align:'center',sortable:true">Id</th>
            <th rowspan="2" data-options="field:'number',width:80,halign:'center',sortable:true">Code</th>
            <th rowspan="2" data-options="field:'name',width:120,halign:'center',sortable:true">Name</th>
            <th rowspan="2" data-options="field:'description',width:200,halign:'center',sortable:true">Description</th>
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
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_info').dialog('open');"><i class="fa fa-info"></i> Info</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:30%;" name="id" id="id" required="" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Code</span>
                <input style="width:30%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%; height:60px;" name="description" multiline:"true" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/types/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/types/create') ?>';
        $('#frm_insert').form('clear');
        // auto id
        $.ajax({
            types: "post",
            url: '<?= base_url('master/types/autoid') ?>',
            datatypes: "html",
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
            url_save = '<?= base_url('master/types/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/types/delete') ?>',
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
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/types/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/types/datatables') ?>',
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