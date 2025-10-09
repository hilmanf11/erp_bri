<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Subcont is taken from <b>Master Data > PPIC > Subconts</b></li>
                <li>The Data Product No is taken from <b>Master Data > Engineering > Item Finish Good</b></li>
            </ul>
        </div>
    </div>
</div>

<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'subcont_id',width:210,halign:'center',sortable:true">Subcont ID</th>
            <th rowspan="2" data-options="field:'subcont_name',width:315,halign:'center',sortable:true">Subcont Name</th>
            <th rowspan="2" data-options="field:'subcont_number',width:210,halign:'center',sortable:true">Subcont Code</th>
            <th rowspan="2" data-options="field:'status',width:120,align:'center', styler:cellStyler, formatter:cellFormatter,sortable:true">Status</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:150,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:160,align:'center',sortable:true"> By</th>
            <th data-options="field:'created_date',width:170,align:'center',sortable:true"> Date</th>
            <th data-options="field:'updated_by',width:160,align:'center',sortable:true"> By</th>
            <th data-options="field:'updated_date',width:170,align:'center',sortable:true"> Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery To</span>
                    <select style="width:60%;" id="filter_delivery_to" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                        <option selected value="SUBCONT">Subcont</option>
                        <option value="TEFA">Teaching Factory</option>
                    </select>`
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont</span>
                    <input style="width:60%;" id="filter_subcont_id" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Teaching Factory</span>
                    <input style="width:60%;" id="filter_teaching_factory_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No.</span>
                    <input style="width:60%;" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>

        <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a> -->

    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1200px; height: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <!-- <div style="width: 50%; float: left;"> -->
            <div class="fitem">
                <span style="width:15%; display:inline-block;">Delivery To</span>
                <select style="width:40%;" id="delivery_to_insert" panelHeight="auto" class="easyui-combobox" data-options="editable:false">
                    <option value="" disabled selected>Choose Delivery To</option>
                    <option value="SUBCONT" selected>Subcont</option>
                    <option value="TEFA">Teaching Factory</option>
                </select>
            </div>
            <!-- </div> -->

            <!-- <div style="width: 50%; float: left;"> -->
            <div class="fitem" id="subcont_wrapper">
                <span style="width:15%; display:inline-block;">Subcont</span>
                <input style="width:40%;" name="subcont_id" id="subcont_id" required="" class="easyui-combogrid">
            </div>

            <div class="fitem" id="teaching_factory_wrapper">
                <span style="width:15%; display:inline-block;">Teaching Factory</span>
                <input style="width:40%;" name="teaching_factory_id" id="teaching_factory_id" required="" class="easyui-combogrid">
            </div>
            <!-- </div> -->

        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="subcont Item Lists" toolbar="#toolbar2"></table>
    </form>
</div>

<!-- Detail Histories -->
<!-- <div id="dlg_history" class="easyui-dialog" title="Price Histories" data-options="closed: true,modal:true" style="width: 400px; height: 300px; top: 20px;">
    <table id="dg_history" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'price',width:100,halign:'center',formatter: priceformat">Price</th>
                <th data-options="field:'valid_date',width:100,halign:'center'">Valid Date</th>
            </tr>
        </thead>
    </table>
</div> -->

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
<iframe id="printout" src="<?= base_url('master/setting_subconts/print') ?>" style="width: 100%;" hidden></iframe>

<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        $("#delivery_to_insert").combobox('setValue', 'SUBCONT');

        $('#delivery_to_insert').combobox('enable');
        $('#subcont_id').combogrid('enable');
        $('#teaching_factory_id').combogrid('enable');

        url_save = '<?= base_url('master/setting_subconts/create') ?>';
        $('#frm_insert').form('clear');
    }

    function addTable(link = "") {
        $('#dg2').datagrid({
            url: link,
            fitColumns: true,
            singleSelect: true,
            columns: [
                [{
                    field: 'item_fg_number',
                    width: 200,
                    halign: 'center',
                    title: "Product No.",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('master/item_fg/readRubberParts'); ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No.',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No.',
                                    width: 150
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, rows) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_number'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_name'
                                });

                                $(ed.target).textbox('setValue', rows.number);
                                $(ed2.target).textbox('setValue', rows.id);
                                $(ed3.target).textbox('setValue', rows.name);
                            }
                        }
                    }
                }, {
                    field: 'item_fg_id',
                    width: 200,
                    hidden: true,
                    halign: 'center',
                    title: "Product ID",
                    editor: {
                        type: 'textbox'
                    }
                }, {
                    field: 'item_fg_name',
                    width: 200,
                    halign: 'center',
                    title: "Product Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, 
                
                // {
                //     field: 'share_order',
                //     width: 200,
                //     halign: 'center',
                //     title: "Share Job Order %",
                //     editor: {
                //         type: 'numberbox'
                //     }
                // }, 
                
                {
                    field: 'type',
                    width: 200,
                    halign: 'center',
                    title: "Type",
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose type',
                            panelHeight: true,
                            required: true,
                            data: [{
                                    name: "SERVICE CHARGE"
                                },
                                {
                                    name: "PRODUCT"
                                },
                            ]
                        }
                    }
                }, {
                    field: 'currency',
                    width: 150,
                    halign: 'center',
                    title: "Currrency",
                    editor: {
                        type: 'combobox',
                        options: {
                            valueField: 'name',
                            textField: 'name',
                            prompt: 'Choose Currrency',
                            panelHeight: true,
                            required: true,
                            data: [{
                                    name: "IDR"
                                },
                                {
                                    name: "JPY"
                                },
                                {
                                    name: "USD"
                                },
                            ]
                        }
                    }
                }, {
                    field: 'price',
                    width: 100,
                    align: 'center',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2
                        }
                    }
                }, {
                    field: 'valid_date',
                    width: 180,
                    halign: 'center',
                    title: "Valid Date Until",
                    editor: {
                        type: 'datebox',
                        options: {
                            formatter: myformatter,
                            parser: myparser,
                            editable: false,
                            required: true
                        },
                    }
                }, 

                // {
                //     field: 'capacity',
                //     width: 200,
                //     halign: 'center',
                //     title: "Capacity/Day (Pcs)",
                //     editor: {
                //         type: 'numberbox'
                //     }
                // }, {
                //     field: 'leadtime',
                //     width: 200,
                //     halign: 'center',
                //     title: "Lead Time (Days)",
                //     editor: {
                //         type: 'numberbox'
                //     }
                // }

                ]
            ],
            onClickCell: onClickCell
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function onClickCell(index, field) {
        if (editIndex != index) {
            if (endEditing()) {
                $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                editIndex = index;
            } else {
                setTimeout(function() {
                    $('#dg2').datagrid('selectRow', editIndex);
                }, 0);
            }
        }
    }

    function append() {
        var subcont_id = $("#subcont_id").combogrid('getValue');
        var teaching_factory_id = $("#teaching_factory_id").combogrid('getValue');
        if (subcont_id != "" || teaching_factory_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Subcont or Teaching Factory first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        var dg = $('#dg2');
        var row = dg.datagrid('getSelected');
        var rowIndex = dg.datagrid('getRowIndex', row);

        var ed = dg.datagrid('getEditor', {
            index: editIndex,
            field: 'item_fg_id'
        });

        // var subcont_id = $("#subcont_id").combogrid('getValue');
        var item_fg_id = $(ed.target).textbox('getValue');

        $.ajax({
            method: 'post',
            url: '<?= base_url('master/setting_subconts/delete') ?>',
            data: {
                subcont_id: row.subcont_id,
                teaching_factory_id: row.tf_id,
                item_fg_id: item_fg_id
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

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            $("#item_fg_id").combogrid('disable');

            $('#delivery_to_insert').combobox('disable');

            // tentukan delivery_to
            if (row.subcont_id) {
                $('#delivery_to_insert').combobox('setValue', 'SUBCONT');
                $('#subcont_id').combogrid('setValue', row.subcont_id);

                $('#subcont_id').combogrid('disable');

                $('#teaching_factory_wrapper').hide();
                $('#teaching_factory_id').combogrid('clear');
                $('#teaching_factory_id').combogrid('disableValidation');
            } else if (row.tf_id) {
                $('#delivery_to_insert').combobox('setValue', 'TEFA');
                $('#teaching_factory_id').combogrid('setValue', row.tf_id);

                $('#teaching_factory_id').combogrid('disable');

                $('#subcont_wrapper').hide();
                $('#subcont_id').combogrid('clear');
                $('#subcont_id').combogrid('disableValidation');
            } else {
                $('#delivery_to_insert').combobox('clear');
            }

            // trigger onChange agar wrapper hide/show sesuai
            $('#delivery_to_insert').combobox('setValue', $('#delivery_to_insert').combobox('getValue'));

            addTable(
                '<?= base_url('master/setting_subconts/datatableUpdates?subcont_id=') ?>' 
                + window.btoa(row.subcont_id ? row.subcont_id : '') 
                + '&teaching_factory_id=' + window.btoa(row.tf_id ? row.tf_id : '')
            );
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
                            url: '<?= base_url('master/setting_subconts/delete') ?>',
                            data: {
                                subcont_id: row.subcont_id,
                                teaching_factory_id: row.tf_id
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
        window.location.assign('<?= base_url('template/tmp_setting_products.xls') ?>');
    }

    function filter() {
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var deliveryTo = $('#filter_delivery_to').combobox('getValue');
        var filter_teaching_factory_id = $("#filter_teaching_factory_id").combogrid('getValue');

        var url = "?filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&delivery_to=" + window.btoa(deliveryTo) +
            "&filter_teaching_factory_id=" + window.btoa(filter_teaching_factory_id) ;        

        // if(deliveryTo === ""){
        //     toastr.warning("Please select Delivery To first!", "Information");
        //     return;
        // }

        // definisi kolom dinamis
        var columns;
        if (deliveryTo === "SUBCONT") {
            columns = [[
                { field: 'ck', checkbox: true, rowspan: 2 },
                { field: 'subcont_id', title: 'Subcont ID', width: 210, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'subcont_name', title: 'Subcont Name', width: 315, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'subcont_number', title: 'Subcont Code', width: 210, sortable: true, rowspan: 2, halign: 'center' },
                { field: 'status', title: 'Status', width: 120, align: 'center', styler: cellStyler, formatter: cellFormatter, sortable: true, rowspan: 2 },
                { title: 'Created', colspan: 2, halign: 'center' },
                { title: 'Updated', colspan: 2, halign: 'center' }
            ],[
                { field: 'created_by', title: 'By', width: 160, align: 'center', sortable: true },
                { field: 'created_date', title: 'Date', width: 170, align: 'center', sortable: true },
                { field: 'updated_by', title: 'By', width: 160, align: 'center', sortable: true },
                { field: 'updated_date', title: 'Date', width: 170, align: 'center', sortable: true }
            ]];
        } else if (deliveryTo === "TEFA") {
            columns = [[
                { field: 'ck', checkbox: true, rowspan: 2 },
                { field: 'tf_id', title: 'TF ID', width: 210, sortable: true, halign: 'center', rowspan: 2 },
                { field: 'tf_name', title: 'TF Name', width: 315, sortable: true, halign: 'center', rowspan: 2 },
                { field: 'tf_number', title: 'TF Code', width: 210, sortable: true, halign: 'center', rowspan: 2 },
                { field: 'status', title: 'Status', width: 120, align: 'center', styler: cellStyler, formatter: cellFormatter, sortable: true, rowspan: 2 },
                { title: 'Created', colspan: 2, halign: 'center' },
                { title: 'Updated', colspan: 2, halign: 'center' }
            ],[
                { field: 'created_by', title: 'By', width: 160, align: 'center', sortable: true },
                { field: 'created_date', title: 'Date', width: 170, align: 'center', sortable: true },
                { field: 'updated_by', title: 'By', width: 160, align: 'center', sortable: true },
                { field: 'updated_date', title: 'Date', width: 170, align: 'center', sortable: true }
            ]];
        }

        // rebuild datagrid
        $('#dg').datagrid({
            url: '<?= base_url('master/setting_subconts/datatables') ?>' + url,
            columns: columns,
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/setting_subconts/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_subcont_id = $("#filter_subcont_id").combogrid('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var deliveryTo = $('#filter_delivery_to').combobox('getValue');
        var filter_teaching_factory_id = $("#filter_teaching_factory_id").combogrid('getValue');

        var url = "?filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) +
            "&delivery_to=" + window.btoa(deliveryTo) +
            "&filter_teaching_factory_id=" + window.btoa(filter_teaching_factory_id) ;   

        window.location.assign('<?= base_url('master/setting_subconts/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //ADD DATA
        addTable();

        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/setting_subconts/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizable: true,
            remoteSort: false,
            view: detailview,
            detailFormatter: function(index, row) {

                let sub_tf_name = row.subcont_name ? row.subcont_name : row.tf_name;
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + sub_tf_name + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
                var delivery_to = $('#filter_delivery_to').combobox('getValue');

                let sub_tf_number = row.subcont_number ? row.subcont_number : row.tf_number;
                ddv.datagrid({
                    url: '<?= base_url('master/setting_subconts/datatableDetails?number=') ?>' + window.btoa(sub_tf_number) + "&filter_item_fg_id=" + window.btoa(filter_item_fg_id) + "&delivery_to=" + window.btoa(delivery_to),
                    fitColumns: true,
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'item_fg_id',
                            title: 'Product ID',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_number',
                            title: 'Product No.',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_fg_name',
                            title: 'Product Name',
                            halign: 'center',
                            width: 200
                        }, 
                        
                        // {
                        //     field: 'share_order',
                        //     title: 'Share Job Order',
                        //     halign: 'center',
                        //     width: 130
                        // }, 
                        
                        {
                            field: 'type',
                            title: 'Type',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'price',
                            title: 'Price',
                            halign: 'center',
                            align: 'right',
                            width: 150,
                            formatter: priceformat
                        }, {
                            field: 'valid_date',
                            title: 'Valid Date Until',
                            halign: 'center',
                            width: 200
                        }, 
                        
                        // {
                        //     field: 'capacity',
                        //     title: 'Cap/Day (Pcs)',
                        //     halign: 'center',
                        //     width: 150
                        // }, {
                        //     field: 'leadtime',
                        //     title: 'Lead Time (Days)',
                        //     halign: 'center',
                        //     width: 150
                        // }
                    
                        ]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            // onOpen: function() {
            //     setTimeout(function() {
            //         $("#delivery_to_insert").combobox('setValue', 'SUBCONT');
            //     }, 100);
            // },
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var subcont_id = $("#subcont_id").combogrid('getValue');
                    var teaching_factory_id = $("#teaching_factory_id").combogrid('getValue');

                    var rows = $('#dg2').datagrid('getRows');
                    var totalrows = rows.length;
                    endEditing();

                    for (let i = 0; i < totalrows; i++) {
                        if (rows[i].item_fg_id) {
                            $.ajax({
                                type: "post",
                                url: '<?= base_url('master/setting_subconts/create') ?>',
                                data: {
                                    subcont_id: subcont_id,
                                    teaching_factory_id: teaching_factory_id,
                                    item_fg_id: rows[i].item_fg_id,
                                    // share_order: rows[i].share_order,
                                    type: rows[i].type,
                                    currency: rows[i].currency,
                                    price: rows[i].price,
                                    valid_date: rows[i].valid_date,
                                    // capacity: rows[i].capacity,
                                    // leadtime: rows[i].leadtime,
                                    status: rows[i].status
                                },
                                dataType: "json",
                                success: function(result) {
                                    if (i == (totalrows - 1)) {
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
                                }
                            });
                        }
                    }

                    $('#dg').datagrid('reload');
                    $('#dlg_insert').dialog('close');
                }
            }]
        });

        $("#filter_teaching_factory_id").combobox('disable');

        $("#filter_delivery_to").combobox({
            onChange: function(val) {
                if (val == "SUBCONT") {
                    $("#filter_subcont_id").combobox('enable');

                    $("#filter_teaching_factory_id").combobox('disable');
                } else if (val == "TEFA") {
                    $("#filter_teaching_factory_id").combobox('enable');

                    $("#filter_subcont_id").combobox('disable');
                }
            }
        });

        // $("#delivery_to_insert").combobox('setValue', 'SUBCONT');
        $("#delivery_to_insert").combobox({
            // value: 'SUBCONT',
            onChange: function(val) {
                if (val == "SUBCONT") {
                    // aktifkan subcont
                    $('#subcont_wrapper').show();
                    $('#subcont_id').combogrid('enableValidation');

                    // nonaktifkan tefa
                    $('#teaching_factory_wrapper').hide();
                    $('#teaching_factory_id').combogrid('clear');
                    $('#teaching_factory_id').combogrid('disableValidation');

                } else if (val == "TEFA") {
                    // aktifkan tefa
                    $('#teaching_factory_wrapper').show();
                    $('#teaching_factory_id').combogrid('enableValidation');

                    // nonaktifkan subcont
                    $('#subcont_wrapper').hide();
                    $('#subcont_id').combogrid('clear');
                    $('#subcont_id').combogrid('disableValidation');

                } else {
                    // kalau kosong, hide semua
                    $('#subcont_wrapper').hide();
                    $('#teaching_factory_wrapper').hide();
                    $('#subcont_id').combogrid('disableValidation');
                    $('#teaching_factory_id').combogrid('disableValidation');
                }
            }
        });


        // definisi combogrid
        $('#subcont_id').combogrid({
            url: '<?= base_url('master/subconts/reads/'); ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Subcont",
            columns: [[
                {field: 'number', title: 'Subcont Code', width: 120},
                {field: 'name', title: 'Subcont Name', width: 250}
            ]]
        });

        $('#teaching_factory_id').combogrid({
            url: '<?= base_url('master/teaching_factory/reads/'); ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Teaching Factory",
            columns: [[
                {field: 'number', title: 'TF Code', width: 120},
                {field: 'name', title: 'TF Name', width: 250}
            ]]
        });
    });

    // $('#subcont_id').combogrid({
    //     url: '<?= base_url('master/subconts/reads/'); ?>',
    //     panelWidth: 420,
    //     idField: 'id',
    //     textField: 'name',
    //     mode: 'remote',
    //     fitColumns: true,
    //     prompt: "Choose Subcont",
    //     columns: [
    //         [{
    //             field: 'number',
    //             title: 'Subcont Code',
    //             width: 120
    //         }, {
    //             field: 'name',
    //             title: 'Subcont Name',
    //             width: 250
    //         }, ]
    //     ]
    // });

    // $('#teaching_factory_id').combogrid({
    //     url: '<?= base_url('master/teaching_factory/reads/'); ?>',
    //     panelWidth: 420,
    //     idField: 'id',
    //     textField: 'name',
    //     mode: 'remote',
    //     fitColumns: true,
    //     prompt: "Choose Teaching Factory",
    //     columns: [
    //         [{
    //             field: 'number',
    //             title: 'TF Code',
    //             width: 120
    //         }, {
    //             field: 'name',
    //             title: 'TF Name',
    //             width: 250
    //         }, ]
    //     ]
    // });

    $('#filter_subcont_id').combogrid({
        url: '<?= base_url('master/subconts/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose subcont",
        columns: [
            [{
                field: 'id',
                title: 'subcont ID',
                width: 150
            }, {
                field: 'number',
                title: 'subcont Code',
                width: 150
            }, {
                field: 'name',
                title: 'subcont Name',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_teaching_factory_id').combogrid({
        url: '<?= base_url('master/teaching_factory/reads'); ?>',
        panelWidth: 750,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Teaching Factory",
        columns: [
            [{
                field: 'id',
                title: 'Tefa Factory',
                width: 150
            }, {
                field: 'number',
                title: 'Tefa Code',
                width: 150
            }, {
                field: 'name',
                title: 'Tefa Name',
                width: 200
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

    $('#filter_item_fg_id').combogrid({
        url: '<?= base_url('master/item_fg/readRubberParts'); ?>',
        panelWidth: 500,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No.",
        columns: [
            [{
                field: 'id',
                title: 'Product ID',
                width: 180
            }, {
                field: 'number',
                title: 'Product No.',
                width: 150
            }, {
                field: 'name',
                title: 'Product Name',
                width: 150
            }, ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });

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

    // function priceformat(value, row) {
    //     if (row.currency == "USD") {
    //         var digits = 4;
    //         var currency = 'USD';
    //         var format = "en-IN";
    //     } else if (row.currency == "JPY") {
    //         var digits = 2;
    //         var currency = 'JPY';
    //         var format = "ja-JP";
    //     } else if (row.currency == "EUR") {
    //         var digits = 2;
    //         var currency = 'EUR';
    //         var format = "de-DE";
    //     } else {
    //         var digits = 0;
    //         var currency = 'IDR';
    //         var format = "id-ID";
    //     }

    //     if (value != null) {
    //         const formatter = new Intl.NumberFormat(format, {
    //             style: 'currency',
    //             currency: currency,
    //             minimumFractionDigits: digits
    //         });
    //         return "<b>" + formatter.format(value) + "</b>";
    //     }
    // }

    function priceformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    // UPLOAD DATA
    // $('#dlg_upload').dialog({
    //     buttons: [{
    //         text: 'List Failed',
    //         handler: function() {
    //             window.open('<?= base_url('master/setting_subconts/uploadDownloadFailed') ?>', '_blank');
    //         }
    //     }, {
    //         text: 'Upload',
    //         iconCls: 'icon-ok',
    //         handler: function() {
    //             $('#frm_upload').form('submit', {
    //                 url: '<?= base_url('master/setting_subconts/upload') ?>',
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
    //                         url: "<?= base_url('master/setting_subconts/uploadclearFailed') ?>"
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
    //                                 url: "<?= base_url('master/setting_subconts/uploadCreate') ?>",
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
    //                                             url: "<?= base_url('master/setting_subconts/uploadcreateFailed') ?>",
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
                window.open('<?= base_url('master/setting_subconts/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function () {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/setting_subconts/upload') ?>',
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
                            url: "<?= base_url('master/setting_subconts/uploadclearFailed') ?>" 
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
                            url: "<?= base_url('master/setting_subconts/uploadCreate') ?>",
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

</script>