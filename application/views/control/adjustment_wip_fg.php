<style>
  .dialog-button{
    border-bottom: 0 !important;
  }

    .btn-clicked {
        background-color: #e0e0e0 !important;
        transform: scale(0.97);
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
</style>

<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:190">Transaction No</th>
            <!-- <th rowspan="2" data-options="field:'status',width:120,align:'center',formatter:statusformat,styler:statusStyle">Status</th> -->
            <th rowspan="2" data-options="field:'request_date',width:120,halign:'center'">Transaction Date</th>
            <th rowspan="2" data-options="field:'request_name',width:120,halign:'center'">Requester</th>
            <th rowspan="2" data-options="field:'transaction_type',width:120,halign:'center',align:'center'">Type</th>
            <!-- <th rowspan="2" data-options="field:'period',width:100,halign:'center'">Period</th>
            <th rowspan="2" data-options="field:'workorder',width:120,halign:'center'">Workorder</th> -->
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:150,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformatQpa">Qty</th>
            <th rowspan="2" data-options="field:'remarks',width:100,align:'center'">Remarks</th>
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

<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:60%;" id="filter_period" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Workorder</span>
                    <input style="width:60%;" id="filter_workorder" class="easyui-combobox">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction No</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_product_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:30%;" id="filter_kanban_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div> -->
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Date</span>
                    <input style="width:29%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                    <input style="width:27%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" id="filter_transaction_type" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>

        <!-- <a href="javascript:;" class="easyui-linkbutton" plain="true" onclick="print_kanban()"><i class="fa fa-print"></i> Print Kanban</a> -->
    </div>
</div>

<!-- Insert & Update -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; height: 600px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
                    <input style="width:60%;" name="request_date" id="request_date" value="<?= date("Y-m-d") ?>" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request No</span>
                    <input style="width:60%;" name="request_no" id="request_no" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Name</span>
                    <input style="width:60%;" name="request_name" id="request_name" value="<?= $this->session->name ?>" readonly class="easyui-textbox">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Transaction Type</span>
                    <input style="width:60%;" name="transaction_type" id="transaction_type" class="easyui-combobox" data-options="editable: false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" class="easyui-textbox">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Add Adjustment WIP FG" toolbar="#toolbar2" data-options="singleSelect: true">
        </table>
    </form>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar2" style="padding: 2px; margin-top: -36px; background-color: #f5f5f5 !important">
    <a href="javascript:void(0)" id="btn-add" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" id="btn-remove" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('control/adjustment_wip_fg/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    $(document).ready(function () {
        $('#dlg_insert').dialog({
            onOpen: function () {
                setTimeout(() => {
                    const panel = $('#dlg_insert').closest('.panel.window.panel-htop');
                    const toolbar = $('#toolbar2');

                    // Pindahkan toolbar ke dalam panel jika belum
                    if (!toolbar.parent().hasClass('panel')) {
                        panel.append(toolbar);
                    }

                    // Tambahkan class & posisi sticky
                    function positionToolbar() {
                        const panelHeight = panel.height();
                        const toolbarHeight = toolbar.outerHeight();
                        toolbar.css({
                            top: (panelHeight - toolbarHeight - 10) + 'px'
                        });
                    }

                    positionToolbar();
                    $(window).on('resize', positionToolbar);
                }, 100); // delay karena dialog render async
            }
        });
    });

    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#dg2').datagrid('loadData', []);
        request_no();

        $("#request_date").datebox('enable');
        $("#request_no").textbox('enable');
        $("#transaction_type").combobox('setValue', '');
    }

    function request_no(reqDate = "") {
        if (reqDate == "") {
            var request_date = $("#request_date").datebox('getValue');
        } else {
            var request_date = reqDate;
        }
        $.ajax({
            type: "post",
            url: "<?= base_url('control/adjustment_wip_fg/request_no') ?>/" + window.btoa(request_date),
            dataType: "html",
            success: function(result) {
                $("#request_no").textbox('setValue', result);
            }
        });
    }

    $.extend($.fn.validatebox.defaults.rules, {
        notZero: {
            validator: function(value){
                return parseFloat(value) > 0;
            },
            message:'The quantity must be greater than 0'
        }
    });

    //INSERT ADD ROW
    function addTable(url = "") {
        var lastIndex;
        var dg = $('#dg2').datagrid({
            url: url,
            // singleSelect: true,
            columns: [
                [{
                    field: 'item_number',
                    width: 250,
                    halign: 'center',
                    title: "Product No",
                    editor: {
                        type: 'combogrid',
                        options: {
                            url: '<?= base_url('control/adjustment_wip_fg/readItemFg') ?>',
                            required: true,
                            panelWidth: 400,
                            idField: 'number',
                            textField: 'number',
                            mode: 'remote',
                            fitColumns: true,
                            prompt: 'Choose Product No',
                            columns: [
                                [{
                                    field: 'number',
                                    title: 'Product No',
                                    width: 100
                                }, {
                                    field: 'name',
                                    title: 'Product Name',
                                    width: 200
                                }]
                            ],
                            onSelect: function(value, row) {
                                var dg = $('#dg2');
                                var allRows = dg.datagrid('getRows');
                                var isDuplicate = allRows.some(function(r) {
                                    return r.item_number === row.number;
                                });

                                if (isDuplicate) {
                                    toastr.warning('Item Has Been Add!');
                                    var rowIndex = dg.datagrid('getRowIndex', row);
                                    dg.datagrid('cancelEdit', rowIndex);
                                    return;
                                }

                                var rowIndex = dg.datagrid('getRowIndex', dg.datagrid('getSelected'));
                                
                                var ed = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_fg_id'
                                });
                                var ed2 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'item_name'
                                });
                                var ed3 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'uom'
                                });
                                var ed4 = dg.datagrid('getEditor', {
                                    index: rowIndex,
                                    field: 'stock'
                                });

                                var item_fg_id = $(ed.target).textbox('setValue', row.id);
                                var item_name = $(ed2.target).textbox('setValue', row.name);
                                var uom = $(ed3.target).textbox('setValue', row.uom);

                            }
                        }
                    }
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    // hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_name',
                    width: 150,
                    halign: 'center',
                    title: "Part Name",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    title: "Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            required: true,
                            precision: 2,
                            validType: 'notZero'
                        }
                    }
                },
                {
                    field: 'uom',
                    width: 80,
                    align: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }]
            ],
            onClickRow: function(rowIndex) {
                if (lastIndex != rowIndex) {
                    $(this).datagrid('endEdit', lastIndex);
                    $(this).datagrid('beginEdit', rowIndex);
                }
                lastIndex = rowIndex;
            },
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

    function append() {
        if (endEditing()) {
            

            var request_no = $("#request_no").textbox('getValue');
            var request_date = $("#request_date").datebox('getValue');
            var request_name = $("#request_name").textbox('getValue');
            var transaction_type = $("#transaction_type").combobox('getValue');
            var remarks = $("#remarks").textbox('getValue');

            // if (totalrows <= 0) {
            if(request_no == "" || request_date == "" || request_name == "") {
                toastr.error("please complete your input data");
                return;
            }

            $('#dg2').datagrid('appendRow', {
                qty: '0'
            });
            editIndex = $('#dg2').datagrid('getRows').length - 1;
            $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
        }
    }

    // function removeit() {
    //     if (endEditing()) {
    //         var row = $('#dg2').datagrid('getSelected'); // Dapatkan baris yang dipilih
    //         if (row) {
    //             var rowIndex = $('#dg2').datagrid('getRowIndex', row); // Dapatkan index baris
    //             $('#dg2').datagrid('deleteRow', rowIndex); // Hapus baris yang dipilih
    //         }
    //         editIndex = undefined; // Reset editIndex
    //     }
    // }

    function removeit() {
        if (!endEditing()) {
            return;
        }

        var row = $('#dg2').datagrid('getSelected');

        if (!row) {
            toastr.warning('Please select data');
            return;
        }

        var rowIndex = $('#dg2').datagrid('getRowIndex', row);

        if (row.id) {

            $.post(
                '<?= base_url('control/adjustment_wip_fg/delete') ?>',
                { id: row.id },
                function(result) {

                    if (result.theme == 'success') {

                        $('#dg2').datagrid('deleteRow', rowIndex);

                        toastr.success(result.message, result.title);

                    } else {

                        toastr.error(
                            result.message || 'Delete failed',
                            result.title || 'Error'
                        );

                    }

                },
                'json'
            );

        } else {

            $('#dg2').datagrid('deleteRow', rowIndex);

        }

        editIndex = undefined;
    }

    //Update Data
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if(row.state == "closed"){
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#request_date").datebox('disable');
                $("#request_no").textbox('disable');
                
                setTimeout(function() {
                    $("#request_no").textbox('setValue', row.request_no);
                }, 1000);
          

                addTable('<?= base_url('control/adjustment_wip_fg/datatableUpdate/') ?>' + window.btoa(row.request_no));
            }else{
                toastr.warning("Please Select Header of Table", "Information");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
    // function deleted() {
    //     var rows = $('#dg').treegrid('getSelections');
    //     if (rows.length > 0) {
    //         $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
    //             if (r) {
    //                 for (var i = 0; i < rows.length; i++) {
    //                     var row = rows[i];
    //                     $.ajax({
    //                         method: 'post',
    //                         url: '<?= base_url('control/adjustment_wip_fg/delete') ?>',
    //                         data: {
    //                             id: row.id,
    //                             request_no: row.request_no,
    //                             item_fg_id: row.item_fg_id
    //                         },
    //                         success: function(result) {
    //                             var result = eval('(' + result + ')');
    //                             $('#dg').treegrid('reload');

    //                             toastr.success(result.message, result.title);
    //                         },
    //                         error: function(jqXHR, textStatus, errorThrown) {
    //                             toastr.error(jqXHR.statusText);
    //                             $.messager.alert("Error", jqXHR.statusText, 'error');
    //                         },
    //                         complete: function(data) {
    //                             $('#dg').treegrid('reload');
    //                         }
    //                     });
    //                 }
    //             }
    //         });
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    function deleted() {

        var rows = $('#dg').treegrid('getSelections');
        if (rows.length == 0) {
            toastr.warning("Please select one of the data in the table first!", "Information");
            return;
        }

        $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {

            if (!r) return;

            var requestNos = [];
            var deleteRows = [];

            $.each(rows, function(i, row) {
                var isParent = row.id == row.request_no;

                if (isParent) {
                    requestNos.push(row.request_no);
                    deleteRows.push({
                        type: 'parent',
                        request_no: row.request_no
                    });
                }
            });

            $.each(rows, function(i, row) {
                var isParent = row.id == row.request_no;
                
                if (!isParent && requestNos.indexOf(row.request_no) === -1) {
                    deleteRows.push({
                        type: 'child',
                        id: row.id,
                        request_no: row.request_no
                    });
                }
            });

            $.each(deleteRows, function(i, item) {

                $.ajax({
                    method: 'post',
                    url: '<?= base_url('control/adjustment_wip_fg/deleteParentChild') ?>',
                    data: item,
                    success: function(result) {
                        result = JSON.parse(result);
                        toastr.success(result.message, result.title);
                    },
                    error: function(jqXHR) {
                        toastr.error(jqXHR.statusText);
                    },
                    complete: function() {
                        if (i === deleteRows.length - 1) {
                            $('#dg').treegrid('reload');
                        }
                    }
                });

            });

        });
    }

    function filter() {
        // var filter_period = $("#filter_period").combobox('getValue');
        // var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        // var filter_kanban_date = $("#filter_kanban_date").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_transaction_type = $("#filter_transaction_type").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_product_no=" + btoa(filter_product_no) + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_transaction_type=" + filter_transaction_type;
        $('#dg').treegrid({
            url: '<?= base_url('control/adjustment_wip_fg/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/adjustment_wip_fg/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        // var filter_period = $("#filter_period").combobox('getValue');
        // var filter_workorder = $("#filter_workorder").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');
        // var filter_kanban_date = $("#filter_kanban_date").datebox('getValue');
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_transaction_type = $("#filter_transaction_type").combobox('getValue');

        url = "?&filter_request_no=" + filter_request_no + "&filter_product_no=" + btoa(filter_product_no) + "&filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_transaction_type=" + filter_transaction_type;
        window.location.assign('<?= base_url('control/adjustment_wip_fg/print/excel') ?>' + url);
    }

    function print_kanban() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            window.open("<?= base_url('control/adjustment_wip_fg/print_kanban/') ?>" + window.btoa(row.request_no));// + "/" + window.btoa(operation), "_blank"
        }else{
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function reload() {
        window.location.reload();
    }
    $(function() {
        addTable();
        $('#dg').treegrid({
            url: '<?= base_url('control/adjustment_wip_fg/datatables') ?>',
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'request_no',
            singleSelect: false,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var request_no = $("#request_no").textbox('getValue');
                    var request_date = $("#request_date").datebox('getValue');
                    var request_name = $("#request_name").textbox('getValue');
                    var transaction_type = $("#transaction_type").combobox('getValue');
                    var remarks = $("#remarks").textbox('getValue');

                    // if (totalrows <= 0) {
                    if(request_no == "" || request_date == "" || request_name == "") {
                        toastr.error("please complete your input data");
                    } else {

                        $("#dg2").datagrid('acceptChanges');
                        var rows = $('#dg2').datagrid('getRows');

                        for (var i = 0; i < rows.length; i++) {
                            if (parseFloat(rows[i].qty || 0) < 1) {
                                toastr.warning('The quantity must be greater than 0');
                                return false;
                            }
                        }

                        var totalrows = rows.length;
                        endEditing();

                        for (let i = 0; i < totalrows; i++) {
                            if (rows[i].item_fg_id) {
                                $.ajax({
                                    type: "post",
                                    url: '<?= base_url('control/adjustment_wip_fg/create') ?>',
                                    data: {
                                        request_date: request_date,
                                        request_no: request_no,
                                        request_name: request_name,
                                        transaction_type: transaction_type,
                                        remarks: remarks,
                                        item_fg_id: rows[i].item_fg_id,
                                        qty: rows[i].qty
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "error") {
                                            toastr.warning(result.message, "Error");
                                        }
                                    }
                                });
                            }
                        }

                        Swal.fire({
                            title: "Data Saved Successfully",
                            icon: "success",
                            confirmButtonText: 'Ok',
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                        $('#dg').treegrid('reload');
                        $('#dlg_insert').dialog('close');
                    }
                }
            }]
        });

        // $('#item_fg_id').combogrid({
        //     url: '<?= base_url('master/item_fg/reads/001') ?>',
        //     panelWidth: 420,
        //     idField: 'id',
        //     textField: 'number',
        //     mode: 'remote',
        //     fitColumns: true,
        //     prompt: "Choose Product",
        //     columns: [
        //         [{
        //             field: 'number',
        //             title: 'Product No',
        //             width: 100
        //         }, {
        //             field: 'name',
        //             title: 'Product Name',
        //             width: 200
        //         }, ]
        //     ]
        // });

        // $("#filter_period").combobox({
        //     url: '<?= base_url('control/adjustment_wip_fg/readPeriod') ?>',
        //     valueField: 'period',
        //     textField: 'period',
        //     prompt: "Choose period",
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //         }
        //     }],
        //     onSelect: function(period) {
        //         $("#filter_workorder").combobox({
        //             url: '<?= base_url('control/adjustment_wip_fg/readWp/') ?>' + period.period,
        //             valueField: 'workorder',
        //             textField: 'workorder',
        //             prompt: "Choose Workorder",
        //             icons: [{
        //                 iconCls: 'icon-clear',
        //                 handler: function(e) {
        //                     $(e.data.target).combobox('clear').combobox('textbox').focus();
        //                 }
        //             }],
        //             onSelect: function(wp) {
        //                 $("#filter_request_no").combobox({
        //                     url: '<?= base_url('control/adjustment_wip_fg/readRequestNo/') ?>' + period.period + '/' + window.btoa(wp.workorder),
        //                     valueField: 'request_no',
        //                     textField: 'request_no',
        //                     prompt: "Choose Request No",
        //                     icons: [{
        //                         iconCls: 'icon-clear',
        //                         handler: function(e) {
        //                             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //                         }
        //                     }],
        //                 });
        //             }
        //         });
        //     }
        // });

        $("#filter_request_no").combobox({
            url: '<?= base_url('control/adjustment_wip_fg/readRequestNos/') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Request No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_product_no').combogrid({
            url: '<?= base_url('control/adjustment_wip_fg/readProducts/') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }, ]
            ]
        });

        $("#request_date").datebox({
            onChange: function(val) {
                request_no(val);
            }
        });
    });

    $("#transaction_type").combobox({
        url: '<?= base_url('control/adjustment_wip_fg/readType/') ?>',
        valueField: 'type',
        textField: 'name',
        prompt: "Choose Type",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $("#filter_transaction_type").combobox({
        url: '<?= base_url('control/adjustment_wip_fg/readType/') ?>',
        valueField: 'type',
        textField: 'name',
        prompt: "Choose Type",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

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

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatQpa(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:green;'>OPEN</b>";
        } else if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#C8FFCC;';
        } else if (value == 1) {
            return 'background-color:#FFC8C8;';
        }
    }

    function BtnPrintLabel(val, row) {
        return '<a style="text-decoration: none; font-weight:bold;" target="_blank" href="<?= base_url('control/adjustment_wip_fg/print_label/') ?>' + window.btoa(row.id) + '"><i class="fa fa-print"></i> Print</a>';
    }

    function statusformatFinance(value, row) {
        if (value == 1) {
            return "<b style='color:red;'>CLOSED</b>";
        } else {
            return "<b style='color:green;'>OPEN</b>";
        }
    }

    function statusStyleFinance(value, row, index) {
        if (value == 1) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
</script>