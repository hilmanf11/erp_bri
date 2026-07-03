<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'dept_id',width:120,halign:'center',sortable:true">Dept ID</th>
            <th rowspan="2" data-options="field:'plant_name',width:200,halign:'center',sortable:true">Plant</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center',sortable:true">Name</th>
            <th rowspan="2" data-options="field:'description',width:200,halign:'center',sortable:true">Description</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:120,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:150,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:120,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 195px; padding: 10px;">
    <div style="width: 100%;">
        <fieldset style="width: 50%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" id="filter_plant" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Department</span>
                <input style="width:60%;" id="filter_department" class="easyui-combogrid">
            </div>
            <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" id="plant" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Dept ID</span>
                <input style="width:60%;" name="dept_id" id="dept_id" required="" class="easyui-textbox" readonly>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" id="description" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/departments/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#toolbar2').show();
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        url_save = '<?= base_url('master/departments/create') ?>';
        $('#frm_insert').form('clear');

        $.ajax({
            type: "post",
            url: "<?= base_url('master/departments/autoid') ?>",
            dataType: "html",
            success: function(res) {
                $('#dept_id').textbox('setValue', res);
                console.log('RES : ', res);
                
            }
        });
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');

        if (!row) {
            toastr.warning("Please select one of the data in the table first!");
            return;
        }

        $('#dlg_insert').dialog('open');

        $('#dept_id').textbox('setValue', row.dept_id);
        $('#name').textbox('setValue', row.name);
        $('#description').textbox('setValue', row.description);

        $('#plant').combogrid('setValue', row.plant);
        $('#plant').combogrid('setText', row.plant_name);

        url_save = '<?= base_url("master/departments/update") ?>';

        $('#toolbar2').hide();
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
                            url: '<?= base_url('master/departments/delete') ?>',
                            data: {
                                id: row.dept_id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                toastr.success(result.message);
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

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }
    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_departments.xls') ?>');
    }

    //FILTER DATA
    function filter() {
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_department = $("#filter_department").combogrid('getValue');

        var url = "?filter_plant=" + window.btoa(filter_plant) +
            "&filter_department=" + window.btoa(filter_department);

        $('#dg').datagrid({
            url: '<?= base_url('master/departments/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/departments/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_department = $("#filter_department").combogrid('getValue');

        var url = "?filter_plant=" + window.btoa(filter_plant) +
            "&filter_department=" + window.btoa(filter_department);

        window.location.assign('<?= base_url('master/departments/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        autoUppercase('name');
        autoUppercase('description');

        //SETTING DATAGRID EASYUI
        var filter_plant = $("#filter_plant").combogrid('getValue');
        var filter_department = $("#filter_department").combogrid('getValue');
        url = "?filter_plant=" + window.btoa(filter_plant) + "&filter_department=" + window.btoa(filter_department);

        $('#dg').datagrid({
            url: '<?= base_url('master/departments/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizable: true,
            remoteSort: false,
            fitColumns: true,
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    var plant_id = $("#plant").combogrid('getValue');
                    var dept_id = $("#dept_id").textbox('getValue');
                    var name = $("#name").textbox('getValue');
                    var description = $("#description").textbox('getValue');

                    if(!plant_id || !dept_id || !name) {
                        toastr.warning("Please fill in all required fields!", "Information");
                        return;
                    } else {
                        $.ajax({
                            type: "post",
                            url: '<?= base_url('master/departments/create') ?>',
                            data: {
                                id: dept_id,
                                plant_id: plant_id,
                                name: name,
                                description: description,
                            },
                            dataType: "json",
                            success: function(result) {
                                Swal.fire({
                                    title: result.message,
                                    icon: result.theme,
                                    confirmButtonText: 'Ok',
                                    allowOutsideClick: false,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.reload();
                                    }
                                });
                            }
                        });
                    }


                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });
    });

    function autoUppercase(id) {
        $('#' + id).textbox('textbox').on('input', function () {
            this.value = this.value.toUpperCase();
        });
    }

    $('#plant').combogrid({
        url: '<?= base_url('master/divisions/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Plant",
        required: true,
        columns: [
            [{
                field: 'id',
                title: 'Plant ID',
                width: 150
            }, {
                field: 'number',
                title: 'Plant Number',
                width: 150
            }, {
                field: 'name',
                title: 'Plant Name',
                width: 200
            }]
        ],
    });

    $('#filter_plant').combogrid({
        url: '<?= base_url('master/divisions/reads'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Plant",
        columns: [
            [{
                field: 'id',
                title: 'Plant ID',
                width: 150
            }, {
                field: 'number',
                title: 'Plant Number',
                width: 150
            }, {
                field: 'name',
                title: 'Plant Name',
                width: 200
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();

                $('#filter_department').combogrid('clear');
                reloadDepartment('');
            }
        }],
        onSelect: function(index, row) {
            $('#filter_department').combogrid('clear');
            reloadDepartment(row.id);
        }
    });

    $('#filter_department').combogrid({
        url: '<?= base_url('master/departments/reads'); ?>',
        panelWidth: 320,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Department",
        columns: [
            [{
                field: 'id',
                title: 'Dept ID',
                width: 120
            }, {
                field: 'name',
                title: 'Dept Name',
                width: 200
            }]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    function reloadDepartment(plant_id = '') {
        $('#filter_department').combogrid({
            queryParams: {
                plant_id: plant_id
            }
        });

        $('#filter_department').combogrid('grid').datagrid('reload');
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
            return 'Not Active';
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
</script>