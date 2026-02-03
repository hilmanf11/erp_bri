<div id="dlg_info" class="easyui-dialog" title="Information" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" data-options="selected:false" style="width:100%; height: 100%;">
        <div title="English" style="padding: 20px;">
            <p>
                <b>General Information:</b></br>
                The Product Family Sub module stores master data for detailed item grouping under a specific Product Family.
            </p>

            <ul>
                <li>ID identifies the product family subcategory record in the system.</li>
                <li>Code represents the short code for the product family subcategory.</li>
                <li>Name indicates the name of the subcategory.</li>
                <li>Category defines the item category, such as Raw Material, Finish Good, or others.</li>
                <li>Product Family indicates the parent product family to which the subcategory belongs.</li>
                <li>Kind specifies the item type or characteristic, if applicable.</li>
                <li>Density stores material density information when required.</li>
                <li>Description provides additional information about the product family subcategory.</li>
            </ul>

            <span>Product Family Sub data is used as a reference in various transactions and processes that require more detailed classification based on Product Family.</span></br></br>

            <span>Changes will apply only to transactions created after the changes are made and will not affect existing data.</span>
        </div>
        <div title="Indonesian" style="padding: 20px;">
            <p>
                <b>Informasi Umum:</b></br>
                Modul Product Family Sub menyimpan data master pengelompokan item secara lebih detail yang berada di bawah Product Family.
            </p>

            <ul>
                <li>ID menunjukkan identitas data sub product family di sistem.</li>
                <li>Code merepresentasikan kode singkat sub product family.</li>
                <li>Name menunjukkan nama sub product family.</li>
                <li>Category menentukan kategori item, seperti Raw Material, Finish Good, dan lainnya.</li>
                <li>Product Family menunjukkan product family induk dari subkategori tersebut.</li>
                <li>Kind menjelaskan jenis atau karakteristik item, jika diperlukan.</li>
                <li>Density berisi informasi densitas material apabila digunakan.</li>
                <li>Description memberikan keterangan tambahan mengenai sub product family.</li>
            </ul>

            <span>Data Product Family Sub digunakan sebagai acuan pada berbagai transaksi dan proses yang membutuhkan klasifikasi item lebih detail berdasarkan Product Family.</span></br></br>

            <span>Perubahan data hanya berlaku untuk transaksi yang dibuat setelah perubahan dilakukan dan tidak mempengaruhi data yang sudah ada.</span>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <!-- <th rowspan="2" data-options="field:'id',width:80,align:'center'">ID</th> -->
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Code</th>
            <th rowspan="2" data-options="field:'name',width:150,halign:'center'">Name</th>
            <th rowspan="2" data-options="field:'item_category_name',width:150,halign:'center'">Category</th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'kind',width:100,halign:'center'">Kind</th>
            <th rowspan="2" data-options="field:'density',width:100,halign:'center'">Density</th>
            <th rowspan="2" data-options="field:'description',width:200,halign:'center'">Description</th>
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
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">ID</span>
                <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Category</span>
                <input style="width:60%;" name="item_category_id" id="item_category_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="item_family_id" id="item_family_id" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Code</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Kind</span>
                <!-- <select style="width:60%;" name="kind" id="kind" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose Kind</option>
                    <option value="TUBE">TUBE</option>
                    <option value="CUBE">CUBE</option>
                </select> -->

                <input style="width:60%;" name="kind" id="kind" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Density</span>
                <input style="width:60%;" name="density" id="density" class="easyui-numberbox" data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" id="description" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/item_family_subs/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_family_subs/create') ?>';
        $('#frm_insert').form('clear');
        
        $.ajax({
            type : "post",
            url : "<?= base_url('master/item_family_subs/autoid')?>",
            dataType : "html",
            success : function(response){
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
            url_save = '<?= base_url('master/item_family_subs/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_family_subs/delete') ?>',
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
        window.location.assign('<?= base_url('master/item_family_subs/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_family_subs/datatables') ?>',
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
    });

    $('#item_category_id').combobox({
        url:'<?= base_url('master/item_categories/reads'); ?>',
        valueField:'id',
        textField:'name',
        prompt: 'Choose Category',
        onSelect: function(category){
            $('#item_family_id').combobox({
                url:'<?= base_url('master/item_familys/reads/'); ?>' + category.id,
                valueField:'id',
                textField:'name',
                prompt: 'Choose Product Family',
            });
        }
    });
</script>