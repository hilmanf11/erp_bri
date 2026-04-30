<div id="dlg_info" class="easyui-dialog" title="Information" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" data-options="selected:false" style="width:100%; height: 100%;">
        <div title="English" style="padding: 20px;">
            <p>
                <b>General Information:</b></br>
                The Master NG module stores master data for non-good (NG) or defect categories used in quality-related transactions.
            </p>

            <ul>
                <li>Code identifies the NG or defect category.</li>
                <li>Name represents the defect type name.</li>
                <li>Description provides additional information about the defect, if required.</li>
            </ul>

            <span>Master NG data is used across various transactions, including Visual Checker output, to record, classify, and analyze product defects.</span></br></br>

            <span>Changes will apply only to transactions created after the changes are made and will not affect existing data.</span>
        </div>
        <div title="Indonesian" style="padding: 20px;">
            <p>
                <b>Informasi Umum:</b></br>
                Modul Master NG menyimpan data master kategori NG (cacat) yang digunakan dalam proses dan transaksi terkait kualitas produk.
            </p>

            <ul>
                <li>Code menunjukkan kode kategori NG atau cacat.</li>
                <li>Name merepresentasikan jenis cacat produk.</li>
                <li>Description berisi keterangan tambahan mengenai cacat, jika diperlukan.</li>
            </ul>

            <span>Data Master NG digunakan pada berbagai transaksi, termasuk output Visual Checker, untuk pencatatan, pengelompokan, dan analisa cacat produk.</span></br></br>

            <span>Perubahan data hanya berlaku untuk transaksi yang dibuat setelah perubahan dilakukan dan tidak mempengaruhi data yang sudah ada.</span>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'code',width:100,align:'center'">Code</th>
            <th rowspan="2" data-options="field:'name',width:150,align:'center'">Name</th>
            <th rowspan="2" data-options="field:'type',width:270,align:'center'">Type</th>
            <th rowspan="2" data-options="field:'description',width:200,align:'center'">Description</th>
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
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_info').dialog('open');"><i class="fa fa-info"></i> Info</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Add New Master NG</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Code</span>
                <input style="width:60%;" name="code" id="code" class="easyui-textbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" class="easyui-textbox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Type</span>
                <input style="width:60%;" name="type" id="type" class="easyui-combobox" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <textarea style="width:60%;" name="description" class="easyui-textbox"></textarea>
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/master_ng/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#code').textbox('readonly', false);
        url_save = '<?= base_url('master/master_ng/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $('#code').textbox('readonly', true);
            url_save = '<?= base_url('master/master_ng/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/master_ng/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                if(result.theme == "success") {
                                    toastr.success(result.message);
                                } else {
                                    toastr.error(result.message);
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
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

    function formatDeleted(value, row, index) {
        return value == 1 ? 'Yes' : 'No';
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/master_ng/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/master_ng/datatables') ?>',
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

                                $('#dlg_insert').dialog('close');
                                $('#dg').datagrid('reload');
                            } else {
                                toastr.error(result.message, result.title);
                            }
                        }
                    });
                }
            }]
        });
    });

    var typeData = [
        { type: 'RECEIVING RAW MATERIAL' },
        { type: 'WAREHOUSE RAW MATERIAL' },
        { type: 'SUPPLY RAW MATERIAL' },
        { type: 'WEIGHING' },
        { type: 'MIXING I' },
        { type: 'MIXING II' },
        { type: 'INSPECTION COMPOUND' },
        { type: 'WAREHOUSE COMPOUND' },
        { type: 'CUTTING' },
        { type: 'SUPPLY COMPOUND' },
        { type: 'MOLDING PRESS' },
        { type: 'EXTRUSION' },
        { type: 'RANDOM CHECK INSPECTION PRESS' },
        { type: 'WIP' },
        { type: 'FINISHING' },
        { type: 'RANDOM CHECK INSPECTION VISUAL' },
        { type: 'POSTCURE/SECONDCURE' },
        { type: 'CHECKING' },
        { type: 'PACKAGING' },
        { type: 'RECEIVING FINISHED GOODS' },
        { type: 'OUT GOING CHECK' },
        { type: 'FINISHED GOODS WAREHOUSE AND DELIVERY' }
    ];

    $('#type').combobox({
        data: typeData,
        valueField: 'type',
        textField: 'type',
        mode: 'local',
        panelHeight: 300,
        panelWidth: 300,
        prompt: 'Select Type',

        filter: function(q, row){
            return row.type.toLowerCase().indexOf(q.toLowerCase()) >= 0;
        },

        formatter: function(row){
            return '<div style="white-space:normal;line-height:16px;">' + row.type + '</div>';
        },

        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],

        onHidePanel: function() {
            var t = $(this).combobox('getText');

            var exists = typeData.some(function(row){
                return row.type === t;
            });

            if (!exists) {
                $(this).combobox('clear');
            }
        }
    });
</script>