<div id="dlg_info" class="easyui-dialog" title="Information" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" data-options="selected:false" style="width:100%; height: 100%;">
        <div title="English" style="padding: 20px;">
            <p>
                <b>General Information:</b></br>
                The Product Family module stores master data for grouping items based on their product type and accounting classification.
            </p>

            <ul>
                <li>ID identifies the product family record in the system.</li>
                <li>Code represents the short code for the product family.</li>
                <li>Name indicates the product family name.</li>
                <li>Category defines the item category, such as Raw Material, Finish Good, Asset, or Consumable.</li>
                <li>Account No specifies the accounting account number associated with the product family.</li>
                <li>Account Name represents the name of the accounting account used for reporting.</li>
                <li>Description provides additional information about the product family.</li>
            </ul>

            <span>Product Family data is used across transactions, inventory processing, accounting, and reporting.</span></br></br>

            <span>Changes will apply only to transactions created after the changes are made and will not affect existing data.</span>
        </div>
        <div title="Indonesian" style="padding: 20px;">
            <p>
                <b>Informasi Umum:</b></br>
                Modul Product Family menyimpan data master pengelompokan item berdasarkan jenis produk dan klasifikasi akuntansi.
            </p>

            <ul>
                <li>ID menunjukkan identitas data product family di sistem.</li>
                <li>Code merepresentasikan kode singkat product family.</li>
                <li>Name menunjukkan nama product family.</li>
                <li>Category menentukan kategori item, seperti Raw Material, Finish Good, Asset, atau Consumable.</li>
                <li>Account No menunjukkan nomor akun akuntansi yang terkait dengan product family.</li>
                <li>Account Name merepresentasikan nama akun akuntansi untuk keperluan pelaporan.</li>
                <li>Description berisi keterangan tambahan mengenai product family.</li>
            </ul>

            <span>Data Product Family digunakan pada berbagai transaksi, proses inventory, akuntansi, dan laporan.</span></br></br>

            <span>Perubahan data hanya berlaku untuk transaksi yang dibuat setelah perubahan dilakukan dan tidak mempengaruhi data yang sudah ada.</span>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'id',width:80,align:'center',sortable:true">ID</th>
            <th rowspan="2" data-options="field:'number',width:80,halign:'center',sortable:true">Code</th>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center',sortable:true">Name</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center',sortable:true">Category</th>
            <th rowspan="2" data-options="field:'account_number',width:120,halign:'center',sortable:true">Account No</th>
            <th rowspan="2" data-options="field:'account_name',width:120,halign:'center',sortable:true">Account Name</th>
            <th rowspan="2" data-options="field:'description',width:150,halign:'center',sortable:true">Description</th>
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
                <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family Code</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:60%;" name="item_category_id" id="item_category_id" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account No</span>
                <input style="width:60%;" name="account_number" id="account_number" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Account Name</span>
                <input style="width:60%;" name="account_name" id="account_name" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" id="description" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/item_familys/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_familys/create') ?>';
        $('#frm_insert').form('clear');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/item_familys/autoid') ?>",
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
            url_save = '<?= base_url('master/item_familys/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_familys/delete') ?>',
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
        window.location.assign('<?= base_url('master/item_familys/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_familys/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizeable: true,
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

    $('#item_category_id').combobox({
        url: '<?= base_url('master/item_categories/reads'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Category',
    });

    $('#account_number').combogrid({
        url:'<?= base_url('finance/account_coa/read/'); ?>',
        panelWidth: 300,
        idField: 'account_number',
        textField: 'account_number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Account No",
        columns: [
            [{
                field: 'account_number',
                title: 'Account Code',
                width: 150
            }, {
                field: 'account_name',
                title: 'Account Name',
                width: 150
            }]
        ],
            onSelect: function(index, row) {
                $('#account_name').textbox('setValue', row.account_name);
            }
    });
</script>