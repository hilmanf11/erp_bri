<style>
    .scan {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 40px !important;
    }

    .swal2-container {
        z-index: 9999 !important;
    }
    .swal2-popup {
        z-index: 99999 !important;
    }
</style>

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <!-- <th field="ck" checkbox="true"></th> -->
            <th data-options="field:'action',width:100,halign:'center',formatter: buttonEdit">Action</th>
            <th data-options="field:'item_fg_number',width:200,halign:'center'">Product No</th>
            <th data-options="field:'item_fg_name',width:250,halign:'center'">Product Name</th>
            <th data-options="field:'workorder',width:250,halign:'center'">WO No</th>
            <th data-options="field:'workorder_label',width:250,halign:'center'">Serial WO No</th>
            <th data-options="field:'serial_label',width:250,halign:'center'">Serial Label</th>

            <!-- <th data-options="field:'is_partial', width:80, align:'center', formatter:partialFormatter, editor:{type:'checkbox',options:{on:1,off:0}}">Partial</th> -->

            <th data-options="field:'qty',width:150,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2,sortable:true,editor:{type:'numberbox',options:{precision:2}}"> Qty</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 283px;">
    <div style="width: 100%; padding: 10px;">

        <a href="javascript:void(0)" onclick="backToMenu()" class="easyui-linkbutton" iconCls="icon-back">
           Back to WIP Store
        </a>

        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;padding-top: 15px;">

            <legend style="text-align: center;"><b>Scan In From External Finishing</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Type</span>

                    <!-- <select
                        id="incoming_type"
                        name="incoming_type"
                        class="easyui-combobox"
                        style="width:60%;"
                        data-options="
                            editable:false,
                            onChange:function(val){
                                togglePartialColumn(val);
                            }
                        "
                    >
                        <option value="Regular">Regular</option>
                        <option value="BPM">BPM</option>
                    </select> -->

                    <select
                        id="incoming_type"
                        name="incoming_type"
                        class="easyui-combobox"
                        style="width:60%;"
                        data-options="editable:false"
                    >
                        <option value="Regular">Regular</option>
                        <option value="Rework">Rework</option>
                        <option value="BPM">BPM</option>
                    </select>

                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Doc No</span>
                    <input style="width:60%;" id="incoming_doc_no" name="incoming_doc_no" class="easyui-textbox" data-options="editable:false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming Date</span>
                    <input style="width:60%;" id="incoming_date" name="incoming_date" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser,editable:false" class="easyui-datebox" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Incoming From</span>
                    <input style="width:60%;" id="incoming_from" name="incoming_from" class="easyui-combogrid" data-options="editable:false" required>
                </div>

                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Incoming From Code</span>
                    <input style="width:60%;" id="incoming_from_code" name="incoming_from_code" class="easyui-textbox">
                </div>

                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="start_scan()"><i class="fa fa-search"></i> Start Scan</a>
                </div>
            </div>

            <div style="width: 70%; float: left;">
                <div class="fitem" style="padding:0 70px 0 40px;">
                    <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
                </div>
                <div class="fitem" style="padding:0 70px 0 40px;">
                    <input style="width:100%; height: 80px;" type="text" id="workorder_label" name="workorder_label" class="scan" placeholder="SCAN LABEL HERE" autofocus>
                </div>

                <div class="fitem" style="padding:0 70px 10px 40px;">

                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="btnPreview()">
                        <i class="fa fa-search"></i> Preview Data
                    </a>

                    <a href="javascript:;" class="easyui-linkbutton" onclick="reload()">
                        <i class="fa fa-rotate-right"></i> Reload
                    </a>

                </div>
            </div>

        </fieldset>
    </div>
</div>


<audio id="serialDuplicate">
    <source src="<?= base_url('assets/audio/serial_duplicate.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialSuccess">
    <source src="<?= base_url('assets/audio/serial_success.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialNotFound">
    <source src="<?= base_url('assets/audio/serial_notfound.mpeg') ?>" type="audio/mpeg">
</audio>

<!-- <audio id="moreThanQty">
    <source src="<?= base_url('assets/audio/more_than_qty.mp3') ?>" type="audio/mpeg">
</audio> -->

<div id="dlgSummaryIncoming" class="easyui-dialog" title="Summary WIP Store From External Finishing" style="width:900px;height:500px;padding:10px" closed="true" modal="true" buttons="#dlgSummaryIncomingButtons">

    <table id="dgSummaryIncoming" class="easyui-datagrid" style="width:100%;height:100%;"></table>
</div>

<div id="dlgSummaryIncomingButtons">
    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-no" onclick="$('#dlgSummaryIncoming').dialog('close')">Close</a>

    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveSummaryIncoming()">Save</a>
</div>

<script>

    let isLoadingExistingData = false;
    let checkPartial = false;

    function reload() {
        window.location.reload();

        $('#workorder_label').val('').prop('disabled', true);
        $('a[onclick="start_scan()"]').linkbutton('enable');
    }

    function start_scan() {
        var incoming_doc_no = $("#incoming_doc_no").textbox('getValue');
        var incoming_date = $("#incoming_date").datebox('getValue');
        var incoming_from = $("#incoming_from").combogrid('getValue');
        var incoming_type = $("#incoming_type").combobox('getValue');

        if(incoming_doc_no != "" && incoming_date != "" && incoming_from != "" && incoming_type != "") {

            $('#incoming_doc_no').textbox('disable');
            $('#incoming_date').datebox('disable');
            $('#incoming_from').combogrid('disable');
            $('#incoming_type').combobox('disable');

            $('#workorder_label').prop('disabled', false).focus();
            $('a[onclick="start_scan()"]').linkbutton('disable');

            toastr.success("Scan mode activated");

        } else {
            toastr.error("Please fill in all required fields first");
        }
    }

    $(function() {

        $('#workorder_label').prop('disabled', true);
        // togglePartialColumn($('#incoming_type').combobox('getValue'));
    
        setTimeout(function() {

            $('#dg').datagrid({
                url: '<?= base_url("control/scan_in_from_external_finishing/getScanIncoming") ?>',
                rownumbers: true,
                onLoadSuccess: function(data) {
                    if (data.total === 0) {
                        // console.log("Data Not Found!");

                        $('#incoming_doc_no').textbox('enable');
                        $('#incoming_date').datebox('enable');
                        $('#incoming_from').combogrid('enable');
                        $('#incoming_type').combobox('enable');
                        $('a[onclick="start_scan()"]').linkbutton('enable');
                    
                    } else {
                        isLoadingExistingData = true;
                        
                        $('#workorder_label').prop('disabled', false).focus();
                        $('a[onclick="start_scan()"]').linkbutton('disable');

                        $("#incoming_type").combobox('setValue', data.rows[0]['incoming_type']);
                        $("#incoming_doc_no").textbox('setValue', data.rows[0]['incoming_doc_no']);
                        $("#incoming_date").datebox('setValue', data.rows[0]['incoming_date']);
                        $("#incoming_from_code").textbox('setValue', data.rows[0]['incoming_from']);

                        $("#incoming_from").combogrid('setValue', data.rows[0]['incoming_from_number']);

                        $('#incoming_doc_no').textbox('disable');
                        $('#incoming_date').datebox('disable');
                        $('#incoming_from').combogrid('disable');
                        $('#incoming_type').combobox('disable');

                        setTimeout(() => {
                            isLoadingExistingData = false;
                        }, 200);
                        
                        // console.log('Data : ', data.rows[0])
                    }
                },
                onLoadError: function(xhr) {
                    console.error("Load datagrid error:", xhr.responseText);
                },
                onBeforeEdit: function (index, row) {
                    row.editing = true;
                    
                    row.old_qty = row.qty;
                    $(this).datagrid('refreshRow', index);
                },
                onAfterEdit: function (index, row) {

                    if (row.qty <= 0) {
                        toastr.error('Qty must be greater than 0');
                        row.qty = row.old_qty;

                        $('#dg').datagrid('updateRow', {
                            index: index,
                            row: row
                        });

                        $('#dg').datagrid('beginEdit', index);
                        return;
                    }

                    row.editing = false;
                    $(this).datagrid('refreshRow', index);

                    updateQty(row, index);
                },
                onCancelEdit: function (index, row) {
                    row.editing = false;
                    $(this).datagrid('refreshRow', index);
                }

            });

        }, 50);

        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        // var moreThanQty = document.getElementById("moreThanQty");

        setTimeout(function() {
            $('#workorder_label').focus(); 
        }, 200);

        //Scan Label
        $('#workorder_label').keypress(function(e) {
            if (e.which == 13) {
                var workorder_label    = $(this).val();
                var incoming_doc_no    = $("#incoming_doc_no").textbox('getValue');
                var incoming_date      = $("#incoming_date").datebox('getValue');
                var incoming_from      = $("#incoming_from").combogrid('getValue');
                var incoming_from_code = $("#incoming_from_code").textbox('getValue');
                var incoming_type      = $("#incoming_type").combogrid('getValue');

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/scan_in_from_external_finishing/getChecksheetLabel') ?>",
                    // data: "workorder_label=" + workorder_label,
                    data: {
                        workorder_label: workorder_label,
                        incoming_from_code: incoming_from_code,
                        incoming_type: incoming_type,
                    },
                    dataType: "json",
                    success: function(json) {                        
                        console.log('Response : ', json);

                        if (json.title === "Not Found") {
                            serialNotFound.play();
                            toastr.warning(json.message, "Not Found");
                            $("#workorder_label").val('').focus();
                            return;
                        } else if (json.title === "Scanned" || json.title === "Available") {
                            serialDuplicate.play();
                            toastr.warning(json.message, "Already Scanned");
                            $("#workorder_label").val('').focus();
                            return;
                        } else if(json.title !== "success") {
                            toastr.warning(json.message, json.title);
                            $("#workorder_label").val('').focus();
                            return;
                        }

                        if (json.title === "success") {

                            var rows = json.data;

                            var header = {
                                incoming_doc_no: incoming_doc_no,
                                incoming_date: incoming_date,
                                incoming_from: incoming_from,
                                incoming_from_code: incoming_from_code,
                                incoming_type: incoming_type,
                            }

                            console.log('Header : ', header);
                            console.log('Rows : ', rows);

                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('control/scan_in_from_external_finishing/create_bulk') ?>",
                                data: {
                                    header: header,
                                    rows: rows,
                                },
                                dataType: "json",
                                success: function(result) {
                                    // console.log('DATA ', result);

                                    if (result.theme === "success") {
                                        serialSuccess.play();
                                        toastr.success(result.message, result.title);
                                    } else {
                                        if (result.title == "Available") {
                                            serialDuplicate.play();
                                        } else if(result.title == "Not Found") {
                                            serialNotFound.play();
                                        } else if (result.title == "Already Scanned") {
                                            // serialDuplicate.play();
                                        }

                                        toastr.warning(result.message, result.title);
                                    }

                                    $("#workorder_label").val('');
                                    $('#workorder_label').focus();
                                    $('#dg').datagrid('reload');

                                },
                                error: function(xhr, status, error) {
                                    toastr.error("An error occurred: " + error, "Error");
                                }
                            });

                            return;
                        }
                    }
                });
            }
        });

    });

    $('#incoming_from').combogrid({
        onChange: function (newValue, oldValue) {

            regenerateIncomingDocNo();
        }
    });

    $('#incoming_date').datebox({
        editable: false,
        onChange: function () {
            regenerateIncomingDocNo();
        }
    });

    function regenerateIncomingDocNo() {

        if (isLoadingExistingData) {
            return;
        }

        let incoming_date = $('#incoming_date').datebox('getValue');
        let incoming_from = $('#incoming_from').combogrid('getValue');

        if (incoming_date && incoming_from) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/scan_in_from_external_finishing/incoming_doc_no') ?>",
                data: { incoming_date: incoming_date, incoming_from: incoming_from },
                dataType: "html",
                success: function(result) {
                    // console.log(result);
                    
                    $("#incoming_doc_no").textbox('setValue', result);
                    $("#incoming_doc_no").textbox('setText', result);
                }
            });
        }
    }

    $('#incoming_from').combogrid({
        url: '<?= base_url('control/scan_in_from_external_finishing/readIncomingFrom'); ?>',
        panelWidth: 440,
        idField: 'number',
        textField: 'name',
        valueField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Source",
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'number', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        onSelect: function(index, row) {
            $('#incoming_from_code').textbox('setValue', row.id);
        }
    });

    function buttonEdit(value, row, index) {
        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a>';
            return s;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a>';
            return e;
        }
    }

    // function getRowIndex(target) {
    //     return $(target).closest('tr.datagrid-row').attr('datagrid-row-index');
    // }

    function getRowIndex(target) {
        return parseInt(
            $(target).closest('tr.datagrid-row').attr('datagrid-row-index'),
            10
        );
    }

    // function editrow(target) {
    //     var index = getRowIndex(target);
    //     $('#dg').datagrid('selectRow', index);
    //     $('#dg').datagrid('beginEdit', index);
    // }

    function editrow(target) {
        var index = getRowIndex(target);
        var dg = $('#dg');

        dg.datagrid('selectRow', index);
        dg.datagrid('beginEdit', index);

        var row = dg.datagrid('getRows')[index];

        var edPartial = dg.datagrid('getEditor', { index: index, field: 'is_partial' });
        if (edPartial) {
            $(edPartial.target).prop('disabled', true);
        }
    }


    // let editIndex = undefined;

    // function editrow(target) {
    //     var index = getRowIndex(target);
    //     if (editIndex !== undefined) {
    //         $('#dg').datagrid('endEdit', editIndex);
    //     }
    //     $('#dg').datagrid('beginEdit', index);
    //     editIndex = index;
    // }

    // $('#dg').datagrid({
    //     onAfterEdit: function () {
    //         editIndex = undefined;
    //     }
    // });


    // function saverow(target) {
    //     var index = getRowIndex(target);
    //     $('#dg').datagrid('endEdit', index);
    // }

    function saverow(target) {
        var index = getRowIndex(target);
        var dg = $('#dg');
        var row = dg.datagrid('getRows')[index];

        var ed = dg.datagrid('getEditor', { index: index, field: 'qty' });
        var newQty = ed ? $(ed.target).numberbox('getValue') : row.qty;

        newQty = parseFloat(newQty);

        if (row.is_partial == 1 && newQty === parseFloat(row.old_qty)) {
            toastr.warning('Product No. ' + row.item_fg_number + ' with Serial WO No. ' + row.workorder_label + ': Partial Qty has not changed!');

            dg.datagrid('beginEdit', index);
            return;
        }

        dg.datagrid('endEdit', index);
    }

    
    function updateQty(row, index) {
        // console.log('UPDATE QTY : ', JSON.stringify(row));
        $.ajax({
            type: 'POST',
            url: '<?= base_url("control/scan_in_from_external_finishing/updateQty") ?>',
            dataType: 'json',
            data: {
                scan_id: row.scan_id,
                item_fg_id: row.item_fg_id,
                workorder: row.workorder,
                workorder_label: row.workorder_label,
                qty: row.qty,
                is_partial: row.is_partial || 0,
                old_qty: row.old_qty || 0,
            },
            success: function (res) {
                if (res.status === 'success') {
                    toastr.success('Qty updated');

                    $('#dg').datagrid('updateRow', {
                        index: index,
                        row: {
                            qty: row.qty
                        }
                    });
                } else {
                    toastr.error(res.message || 'Failed update');
                    $('#dg').datagrid('reload');
                }
            },
            error: function () {
                toastr.error('Server error');
                $('#dg').datagrid('reload');
            }
        });
    }


    function btnPreview() {

        if (hasEditingRow()) {
            toastr.warning('There is still unsaved data. Please save all changes before continuing');
            return;
        }

        $('#dlgSummaryIncoming').dialog('open').dialog('center');

        $('#dgSummaryIncoming').datagrid({
            url: '<?= base_url('control/scan_in_from_external_finishing/getSummaryIncoming') ?>',
            method: 'get',
            fitColumns: true,
            singleSelect: true,

            columns: [[
                {
                    field: 'no',
                    title: 'No',
                    width: 60,
                    align: 'center',
                    formatter: function (v, r, i) {
                        return r.is_total ? '' : i + 1;
                    }
                },
                {
                    field: 'item_fg_number',
                    title: 'Product No',
                    width: 180
                },
                {
                    field: 'item_fg_name',
                    title: 'Product Name',
                    width: 300,
                    formatter: function (value, row) {
                        // TOTAL mentok kanan (SIMPLE)
                        return row.is_total
                            ? '<b style="float:right">TOTAL</b>'
                            : value;
                    }
                },
                {
                    field: 'qty_total',
                    title: 'Qty',
                    width: 120,
                    align: 'right',
                    formatter: numberformat
                }
            ]],

            onLoadSuccess: function (data) {
                let totalQty = 0;

                $.each(data.rows, function (i, row) {
                    totalQty += Number(row.qty_total || 0);
                });

                let rows = $('#dgSummaryIncoming').datagrid('getRows');
                if (rows.length && rows[rows.length - 1].is_total) {
                    $('#dgSummaryIncoming').datagrid('deleteRow', rows.length - 1);
                }

                $('#dgSummaryIncoming').datagrid('appendRow', {
                    is_total: true,
                    qty_total: totalQty
                });
            }
        });
    }

    function saveSummaryIncoming() {
        var incoming_type = $("#incoming_type").combobox('getValue');
        var incoming_doc_no = $("#incoming_doc_no").textbox('getValue');
        var incoming_date   = $("#incoming_date").datebox('getValue');
        var incoming_from   = $("#incoming_from").combogrid('getValue');
        var incoming_from_code = $("#incoming_from_code").textbox('getValue');

        if (incoming_doc_no == "" || incoming_date == "" || incoming_from == "" || incoming_type == "") {
            toastr.warning('Incoming header is required');
            return;
        }

        // endEditing();
        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('Data Not Found!');
            return;
        }

        // console.log('ROWS : ', rows);

        let items = [];

        rows.forEach(row => {
            if (row.item_fg_id) {
                items.push({
                    incoming_type: incoming_type,
                    incoming_doc_no: incoming_doc_no,
                    incoming_date: incoming_date,
                    incoming_from: incoming_from_code,

                    delivery_note_no: row.delivery_note_no,

                    item_fg_id: row.item_fg_id,
                    workorder: row.workorder,
                    workorder_label: row.workorder_label,
                    qty: row.qty,
                });
            }
        });

        if (items.length === 0) {
            toastr.error("No data to save");
            return;
        }

        // console.log('ITEMS : ', items);
        
        Swal.fire({
            title: 'Confirm Save',
            text: 'Are you sure you want to save this incoming data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('control/scan_in_from_external_finishing/saveSummaryIncoming') ?>',
                    data: { items: items },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Saving...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        Swal.close();

                        if (res.theme === "success") {
                            Swal.fire({
                                title: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                            }).then(() => {
                                $('#dg').datagrid('reload');
                                $('#dlgSummaryIncoming').dialog('close');
                                window.location.reload();
                            });
                        } else {
                            toastr.error(res.message, res.title || "Error");
                        }
                    },
                    error: function () {
                        Swal.close();
                        toastr.error("Server error while saving");
                    }
                });

            }
        });

    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatInt(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function numberStyle2(value, row, index) {
        let shipping = parseFloat(row.shipping || 0);
        let delivery = parseFloat(row.delivery || 0);

        if (shipping < delivery) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

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

    // function togglePartialColumn(type) {
    //     if (type === 'BPM') {
    //         $('#dg').datagrid('hideColumn', 'is_partial');
    //     } else {
    //         $('#dg').datagrid('showColumn', 'is_partial');
    //     }
    // }

    function partialFormatter(value, row, index) {
        var checked = parseInt(row.is_partial) === 1 == 1 ? 'checked' : '';
        return `
            <input type="checkbox"
                class="dg-partial"
                data-index="${index}"
                ${checked}
                onchange="onPartialChange(this)">
        `;
    }

    $.extend($.fn.datagrid.methods, {
        editCell: function(jq, param){
            return jq.each(function(){
                var opts = $(this).datagrid('options');
                var fields = $(this).datagrid('getColumnFields',true)
                    .concat($(this).datagrid('getColumnFields'));

                for(var i=0; i<fields.length; i++){
                    var col = $(this).datagrid('getColumnOption', fields[i]);
                    col.editor1 = col.editor;
                    if (fields[i] != param.field){
                        col.editor = null;
                    }
                }

                $(this).datagrid('beginEdit', param.index);

                for(var i=0; i<fields.length; i++){
                    var col = $(this).datagrid('getColumnOption', fields[i]);
                    col.editor = col.editor1;
                }
            });
        }
    });

    function onPartialChange(el) {
        var index = $(el).data('index');
        var dg    = $('#dg');
        var row   = dg.datagrid('getRows')[index];

        if (row.total_label > 1) {
            toastr.warning('Partial cannot be canceled because the same label has been used previously');

            row.is_partial = 1;
            el.checked = true;

            dg.datagrid('refreshRow', index);
            return;
        }

        row.is_partial = el.checked ? 1 : 0;

        if (row.is_partial === 1) {
            dg.datagrid('selectRow', index);
            dg.datagrid('editCell', {
                index: index,
                field: 'qty'
            });
        } else {
            dg.datagrid('cancelEdit', index);
            dg.datagrid('refreshRow', index);
        }
    }

    function hasEditingRow() {
        var dg = $('#dg');
        var rows = dg.datagrid('getRows');

        for (var i = 0; i < rows.length; i++) {
            if (dg.datagrid('getEditors', i).length > 0) {
                dg.datagrid('selectRow', i);
                return true;
            }
        }
        return false;
    }

    function backToMenu(){
        var token = window.location.pathname.split('/').pop();
        window.location.href = "<?= base_url('control/scan_in_wip_store/index/') ?>" + token;
    }
</script>