<div id="dlg_help" class="easyui-dialog" title="About Menu" data-options="closed: true,modal:true" style="width: 800px; height: 500px; left: 10px; top: 20px;">
    <div class="easyui-accordion" style="width:100%; height: 100%;">
        <div title="RELATIONS" style="padding: 20px;">
            <ul>
                <li>The Data Process Type is taken from <b>Master Data > Engineering > Flow Process</b></li>
                <li>The Data Divisions is taken from <b>Master Data > General Master > Divisions</b></li>
                <li>The Data Box is taken from <b>Master Data > Engineering > Boxs</b></li>
                <li>The Data Colors is taken from <b>Master Data > Engineering > Colors</b></li>
                <li>The Data UoM is taken from <b>Master Data > General Master > Unit of Measure</b></li>
                <li>The Data Category is taken from <b>Master Data > General Master > Category</b></li>
                <li>The Data Product Family is taken from <b>Master Data > Engineering > Product Family</b></li>
                <li>The Data Product Family Sub is taken from <b>Master Data > Engineering > Product Family Sub</b></li>
            </ul>
        </div>
    </div>
</div>
<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead data-options="frozen:true" style="height: auto;" fitColumns="true">
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'attachment',width:100,halign:'center',formatter:cellbutton">Attachment</th>
            <th data-options="field:'id',width:150,align:'center'">Product ID</th>
            <th data-options="field:'number',width:150,halign:'center'">Product No.</th>
            <th data-options="field:'name',width:150,halign:'center'">Product <br>Name</th>
        </tr>
    </thead>

    <thead style="height: auto;" fitColumns="true">
        <tr>
            <th rowspan="2" data-options="field:'specification',width:100,align:'center',sortable:true">Specification</th>
            <th rowspan="2" data-options="field:'total_mold',width:50,align:'center',sortable:true">Total <br>Mold</th>
            <th rowspan="2" data-options="field:'process',width:80,align:'center',sortable:true">Flow <br>Type</th>
            <th rowspan="2" data-options="field:'product_type',width:80,align:'center',sortable:true">Product <br>Type</th>
            <th rowspan="2" data-options="field:'item_category_name',width:100,align:'center',sortable:true">Category</th>
            <th rowspan="2" data-options="field:'item_family_name',width:150,align:'center',sortable:true">Product Family</th>
            <th rowspan="2" data-options="field:'item_family_sub_name',width:150,align:'center'" hidden>Sub Product Family</th>
            <th rowspan="2" data-options="field:'lot',width:100,align:'center',sortable:true">Lot</th>
            <th rowspan="2" data-options="field:'weight',width:100,align:'center',sortable:true">Weight (gram)</th>
            <th rowspan="2" data-options="field:'leadtime',width:80,align:'center',sortable:true">Lead Time <br>(Day)</th>
            <th rowspan="2" data-options="field:'lifetime',width:80,align:'center',sortable:true">Life Time <br>(Day)</th>
            <th rowspan="2" data-options="field:'mpq',width:50,align:'center',sortable:true">MPQ</th>
            <th rowspan="2" data-options="field:'moq',width:50,align:'center',sortable:true">MOQ</th>
            <th rowspan="2" data-options="field:'uom',width:50,align:'center',sortable:true">UoM</th>
            <th rowspan="2" data-options="field:'qty_box',width:80,align:'center',sortable:true">QTY/Box</th>
            <th rowspan="2" data-options="field:'box_sub',width:80,align:'center',sortable:true">QTY/Sub Box</th>
            <!-- <th rowspan="2" data-options="field:'safety_stock',width:100,halign:'center'">Safety Stock</th> -->
            <th rowspan="2" data-options="field:'min',width:50,align:'center',sortable:true">Min</th>
            <th rowspan="2" data-options="field:'max',width:50,align:'center',sortable:true">Max</th>
            <th rowspan="2" data-options="field:'status',width:100,align:'center', styler:cellStyler, formatter:cellFormatter,sortable:true">Status</th>
            <!-- <th rowspan="2" data-options="field:'approved_to',width:100,halign:'center', styler:styleApproved, formatter:formatApproved">Approved To</th>
            <th rowspan="2" data-options="field:'approved_by',width:100,halign:'center'">Approved by</th>
            <th rowspan="2" data-options="field:'approved_date',width:100,halign:'center'">Approved date</th> -->
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
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="$('#dlg_help').dialog('open');"><i class="fa fa-info"></i> Help</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 1000px; height: auto; padding:10px; top: 10px;">
    <form id="frm_insert" method="post" novalidate enctype="multipart/form-data">
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product ID</span>
                    <input style="width:60%;" name="id" id="id" required="" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No.</span>
                    <input style="width:60%;" name="number" id="number" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Name</span>
                    <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Specification</span>
                    <input style="width:60%;" name="specification" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Flow Type</span>
                    <input style="width:60%;" name="process" id="process" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Type</span>
                    <select style="width:60%;" name="product_type" class="easyui-combobox" panelHeight="auto">
                        <option value="EXPORT">EXPORT</option>
                        <!-- <option value="IMPORT">IMPORT</option> -->
                        <option value="LOCAL">LOCAL</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Category</span>
                    <input style="width:60%;" name="item_category_number" id="item_category_number" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_number" id="item_family_number" required="" class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Sub Product Family</span>
                    <input style="width:60%;" name="item_family_sub_number" id="item_family_sub_number" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lot</span>
                    <input style="width:60%;" name="lot" id="lot" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Weight (Gram)</span>
                    <input style="width:30%;" name="weight" id="weight" precision="2" class="easyui-numberbox">
                </div>
            </div>
            <div style="float:left; width:50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime (Day)</span>
                    <input style="width:60%;" name="leadtime" id="leadtime" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Lifetime</span>
                    <input style="width:60%;" name="lifetime" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ</span>
                    <input style="width:60%;" name="mpq" id="mpq" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MOQ</span>
                    <input style="width:60%;" name="moq" id="moq" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Safety Stock (%)</span>
                    <input style="width:60%;" name="safety_stock" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">UoM</span>
                    <input style="width:60%;" name="uom" id="uom" required="" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Box</span>
                    <input style="width:60%;" name="qty_box" id="qty_box" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty/Sub Box</span>
                    <input style="width:60%;" name="box_sub" id="box_sub" class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-filebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="status" id="status" required="" panelHeight="auto" class="easyui-combobox">
                        <option value="0">Active</option>
                        <option value="1">Not Active</option>
                    </select>
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
<iframe id="printout" src="<?= base_url('master/item_fg/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_fg/create') ?>';
        $('#frm_insert').form('clear');

        $('#status').combobox('setValue', '0');
        $('#id').textbox('setValue', 'Auto Generate');
        $('#item_category_number').combobox('setValue', 'FG');
    }

    //EDIT DATA
    function update() {
        $('#frm_insert').form('clear');
        var row = $('#dg').datagrid('getSelected');

        setTimeout(function() {
            $('#id').textbox('setValue', row.id);
            $('#item_family_sub_number').combobox('setValue', row.item_family_sub_number);
            $('#item_family_sub_number').combobox('setText', row.item_family_sub_name);
        }, 500);

        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            // $('#id').textbox('disable');

            url_save = '<?= base_url('master/item_fg/update') ?>?id=' + btoa(row.id);
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
                            url: '<?= base_url('master/item_fg/delete') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                                if (result.theme === 'error') {
                                    toastr.error(result.message);
                                    $.messager.alert('Error', result.message, 'error');
                                } else {
                                    toastr.success('Data successfully deleted.');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error('Failed to delete data because it is still being used in the Module Bill of Material.');
                                // $.messager.alert("Error", 'Failed to delete data because it is still being used in the Module Bill of Material.');
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
        window.location.assign('<?= base_url('template/tmp_item_fg.xls') ?>');
    }
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_fg/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_fg/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            resizable: true,
            remoteSort: false,
            frozenColumns: [
                [{
                        field: 'attachment',
                        title: 'Attachment',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        formatter: cellbutton
                    },
                    {
                        field: 'id',
                        title: 'Product ID',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'number',
                        title: 'Product No.',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'name',
                        title: 'Product Name',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    }
                ]
            ],
            columns: [
                [{
                        field: 'specification',
                        title: 'Specification',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'total_mold',
                        title: 'Total Mold',
                        width: 80,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'process',
                        title: 'Flow Type',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'product_type',
                        title: 'Product Type',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'item_category_name',
                        title: 'Category',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'item_family_name',
                        title: 'Product Family',
                        width: 120,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    // {
                    //     field: 'item_family_sub_name',
                    //     title: 'Sub Product Family',
                    //     width: 150,
                    //     align: 'center'
                    // },
                    {
                        field: 'lot',
                        title: 'Lot',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'weight',
                        title: 'Weight (gram)',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'leadtime',
                        title: 'Lead Time (Day)',
                        width: 80,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'lifetime',
                        title: 'Life Time (Day)',
                        width: 80,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'mpq',
                        title: 'MPQ',
                        width: 50,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'moq',
                        title: 'MOQ',
                        width: 50,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'uom',
                        title: 'UoM',
                        width: 50,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'qty_box',
                        title: 'QTY/Box',
                        width: 80,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'box_sub',
                        title: 'QTY/Sub Box',
                        width: 80,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'min',
                        title: 'Min',
                        width: 50,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'max',
                        title: 'Max',
                        width: 50,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'status',
                        title: 'Status',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                        styler: cellStyler,
                        formatter: cellFormatter
                    },
                    {
                        field: 'created_by',
                        title: 'Created By',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'created_date',
                        title: 'Created Date',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'updated_by',
                        title: 'Updated By',
                        width: 100,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    },
                    {
                        field: 'updated_date',
                        title: 'Updated Date',
                        width: 150,
                        align: 'center',
                        sortable: true,
                        resizable: true,
                    }
                ]
            ]
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

    $('#process').combobox({
        url: '<?= base_url('master/item_process_flow/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Process Type',
    });

    $('#uom').combobox({
        url: '<?= base_url('master/uom/reads'); ?>',
        valueField: 'name',
        textField: 'name',
        prompt: 'Choose Unit of Measure',
    });

    $('#item_category_number').combobox({
        url: '<?php echo base_url('master/item_categories/readsfgrm'); ?>',
        valueField: 'number',
        textField: 'name',
        prompt: "Choose Product Category",
        onSelect: function(item_categories) {
            $('#item_family_number').combobox({
                url: '<?php echo base_url('master/item_familys/readsbynumber/'); ?>' + item_categories.number,
                valueField: 'number',
                textField: 'name',
                prompt: "Choose Product Family",
                onSelect: function(item_family_subs) {
                    $.ajax({
                        type: "post",
                        url: '<?php echo base_url('master/item_fg/autoid/'); ?>' + item_categories.number + '/' + item_family_subs.number,
                        dataType: "html",
                        success: function(response) {
                            $('#id').textbox('setValue', response);
                        }
                    });

                    $('#item_family_sub_number').combobox({
                        url: '<?php echo base_url('master/item_family_subs/readsByNumber'); ?>/' + item_family_subs.number,
                        valueField: 'number',
                        textField: 'name',
                        prompt: "Choose Sub Family Product",
                        onSelect: function(item_family) {
                            $.ajax({
                                type: "post",
                                url: '<?php echo base_url('master/item_fg/autoid/'); ?>' + item_categories.number + '/' + item_family_subs.number + '/' + item_family.number,
                                dataType: "html",
                                success: function(response) {
                                    $('#id').textbox('setValue', response);
                                }
                            });
                        }
                    });
                }
            });
        }
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

    //FORMATTER LOGO
    function cellFormatterLogo(value) {
        if (value == 0) {
            return 'YES';
        } else {
            return 'NO';
        }
    };

    //CELLSTYLE APPROVE
    function styleApproved(value, row, index) {
        if (value == "" || value === null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }
    //FORMATTER APPROVE
    function formatApproved(value) {
        if (value == "" || value === null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    //
    function cellbutton(value) {
        if (value != null) {
            return '<a target="_blank" href="' + value + '" class="btn btn-primary btn-sm" style="pointer-events: auto; opacity:1; width:100%;"><i class="fa fa-eye"></i> View</a>';
            // alert(value);
        }
    };

    // UPLOAD DATA
    $('#dlg_upload').dialog({
        buttons: [{
            text: 'List Failed',
            handler: function() {
                window.open('<?= base_url('master/item_fg/uploadDownloadFailed') ?>', '_blank');
            }
        }, {
            text: 'Upload',
            iconCls: 'icon-ok',
            handler: function() {
                $('#frm_upload').form('submit', {
                    url: '<?= base_url('master/item_fg/upload') ?>',
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
                            url: "<?= base_url('master/item_fg/uploadclearFailed') ?>"
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
                                    url: "<?= base_url('master/item_fg/uploadCreate') ?>",
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
                                                url: "<?= base_url('master/item_fg/uploadcreateFailed') ?>",
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