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

    .datagrid-header-rownumber,
    .datagrid-cell-rownumber{
        width:40px !important;
    }

</style>

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'label',width:200,halign:'center'">Serial No</th>
            <th rowspan="2" data-options="field:'item_fg_id',width:150,halign:'center'">Product ID</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:200,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'label_type',width:150,halign:'center'">Label Type</th>
            <th rowspan="2" data-options="field:'qty',width:130,halign:'center',align:'right',formatter:numberformatInt,styler:numberStyle">Qty</th>
            <th rowspan="2" data-options="field:'uom',width:100,halign:'center',align:'center'">UOM</th>
            <th rowspan="2" data-options="field:'location_name',width:100,halign:'center',align:'center'">Location</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center',align:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:150,halign:'center',align:'center'">By</th>
            <th data-options="field:'created_date',width:150,halign:'center',align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 218px;">
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;padding-top: 15px;">

            <legend><b>Form STO WIP FG</b></legend>
            <div style="width: 30%; float: left; margin-top: 2px;">

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Doc No</span>
                    <input style="width:60%;" id="doc_no" name="doc_no" class="easyui-textbox" data-options="editable:false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Location</span>
                    <input style="width:60%;" id="location" name="location" class="easyui-combogrid" data-options="editable:false" required>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Location Code</span>
                    <input style="width:60%;" id="location_code" name="location_code" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="period_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="editable:false" required>
                    <input style="width:30%;" id="period_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="editable:false" required>
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-top: 5px; padding-right: 3.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnReset" style="margin-right: 2px;" onclick="reset()">
                        <i class="fa fa-rotate-right"></i> Reset
                    </a>
                    <a href="javascript:;" class="easyui-linkbutton" id="btnStartScan" onclick="start_scan()"><i class="fa fa-play"></i> Start Scan</a>
                </div>
            </div>

            <div style="width: 70%; float: left;">
                <div class="fitem" style="padding:0 70px 0 40px;">
                    <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
                </div>
                <div class="fitem" style="padding:0 70px 0 40px;">
                    <input style="width:100%; height: 80px;" type="text" id="workorder_label" name="workorder_label" class="scan" placeholder="SCAN LABEL HERE" autofocus>
                </div>

                <div class="fitem" style="padding:0 70px 0px 40px;">
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="btnPreview()">
                        <i class="fa fa-search"></i> Preview Data
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

<div id="dlgStoWipFg" class="easyui-dialog" title="STO WIP FG" style="width:1000px;height:600px;padding:10px;" closed="true" modal="true" buttons="#dlgStoWipFgButtons">

    <table id="dgStoWipFg" class="easyui-datagrid" style="width:100%;height:100%;"></table>
</div>

<div id="dlgStoWipFgButtons">
    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-no" onclick="$('#dlgStoWipFg').dialog('close')">Close</a>
    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveSummaryStoWipFg()">Save</a>
</div>

<script>
    let isLoadingExistingData = false;

    function reset() {
        const doc_no = $("#doc_no").textbox('getValue');

        Swal.fire({
            title: 'Reset Header?',
            text: 'Data header will be removed',
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, Reset',
        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                type: "POST",
                url: "<?= base_url('control/sto_wip_fg/resetHeader') ?>",
                data: {
                    doc_no: doc_no
                },
                dataType: "json",
                beforeSend: function() {
                    Swal.fire({
                        title: 'Resetting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(result) {
                    Swal.close();

                    if(result.theme !== 'success' && result.theme !== 'reload'){
                        toastr.warning(result.message, result.title);
                        return;
                    }

                    toastr.success(result.message, result.title);

                    $('#doc_no').textbox('enable');
                    $('#location').combogrid('enable');
                    $('#period_month').combobox('enable');
                    $('#period_year').combobox('enable');

                    $('#doc_no').textbox('clear');
                    $('#location').combogrid('clear');
                    $('#location_code').textbox('clear');

                    $('#workorder_label').val('').prop('disabled', true);

                    $('#btnStartScan').linkbutton('disable');
                    $('#btnReset').linkbutton('disable');

                    window.location.reload();
                    $('#dg').datagrid('reload');
                },

                error: function() {
                    Swal.close();
                    toastr.error('Server error');
                }
            });

        });
    }

    function start_scan() {
        var doc_no          = $("#doc_no").textbox('getValue');
        var location        = $("#location").combogrid('getValue');
        var period_month    = $("#period_month").combobox('getValue');
        var period_year     = $("#period_year").combobox('getValue');

        if(location == "" || period_month == "" || period_year == ""){
            toastr.error("Please fill in all required fields first");
            return;
        }

        $.ajax({
            type: "POST",
            url: "<?= base_url('control/sto_wip_fg/createHeader') ?>",
            dataType: "json",
            data: {
                doc_no: doc_no,
                location: location,
                period_month: period_month,
                period_year: period_year
            },
            beforeSend: function(){
                Swal.fire({
                    title: 'Generate Document No',
                    text: 'Please wait...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(result){
                Swal.close();

                if(result.theme === 'reload'){

                    toastr.warning(result.message, result.title);

                    setTimeout(function(){
                        location.href = location.pathname + '?r=' + Date.now();
                    }, 1000);

                    return;
                }

                if(result.theme != 'success'){
                    toastr.warning(result.message, result.title);
                    return;
                }

                var oldDocNo = doc_no;
                var newDocNo = result.doc_no;

                if(newDocNo){
                    $("#doc_no").textbox('setValue', newDocNo);
                }

                function activateScanMode(){

                    $('#doc_no').textbox('disable');
                    $('#location').combogrid('disable');
                    $('#period_month').combobox('disable');
                    $('#period_year').combobox('disable');

                    $('#btnStartScan').linkbutton('disable');
                    
                    $('#btnReset').linkbutton('enable');
                    toastr.success("Scan mode activated");
                    
                    setTimeout(() => {
                        $('#workorder_label').prop('disabled', false).focus();
                    }, 300);
                }

                if(oldDocNo !== newDocNo){
                    Swal.fire({
                        icon: 'info',
                        title: 'Document No Updated',
                        html:'Your Document No changed:<br><b>'+newDocNo+'</b>',
                        confirmButtonText: 'Continue'
                    }).then(() => {
                        activateScanMode();
                    });

                } else {
                    activateScanMode();
                }
            },
            error:function(){
                Swal.close();
                toastr.error("Server error");
            }
        });
    }

    $(function() {

        $('#workorder_label').prop('disabled', true);
        $('#btnStartScan').linkbutton('disable');
        $('#btnReset').linkbutton('disable');
        $('#btnPreview').linkbutton('disable');

        $.getJSON(
            '<?= base_url("control/sto_wip_fg/getCurrentHeader") ?>',
            function(row){
                if(row.scan_id){
                    // console.log('ROW : ', row);

                    isLoadingExistingData = true;
                    
                    $("#doc_no").textbox('setValue', row.doc_no);
                    // console.log($("#doc_no").textbox('getValue'));
                    $("#period_month").combobox('setValue', row.period_month);
                    $("#period_year").combobox('setValue', row.period_year);
                    $("#location").combogrid('setValue', row.location_name);
                    $("#location_code").textbox('setValue', row.location_code);

                    $('#doc_no').textbox('disable');
                    $('#location').combogrid('disable');
                    $('#period_month').combobox('disable');
                    $('#period_year').combobox('disable');

                    $('#workorder_label').prop('disabled', false);
                    $('#btnStartScan').linkbutton('disable');

                    isLoadingExistingData = false;

                }else{

                    $('#doc_no').textbox('enable');
                    $('#location').combogrid('enable');
                    $('#period_month').combobox('enable');
                    $('#period_year').combobox('enable');
                }
            }
        );

        setTimeout(function() {

            $('#dg').datagrid({
                url:'<?= base_url("control/sto_wip_fg/getStoWipFg") ?>',
                rownumbers:true,

                onLoadSuccess:function(data){
                    const scanMode = $('#workorder_label').prop('disabled') === false;

                        if(scanMode){
                            if(data.total === 0){
                                $('#btnReset').linkbutton('enable');
                                $('#btnPreview').linkbutton('disable');
                            }else{
                                $('#btnReset').linkbutton('disable');
                                $('#btnPreview').linkbutton('enable');
                            }
                        }else{
                            $('#btnReset').linkbutton('disable');
                        }
                },
            });

        }, 50);

        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");

        setTimeout(function() {
            $('#workorder_label').focus(); 
        }, 200);

        $('#workorder_label').keypress(function(e) {
            if (e.which == 13) {
                var workorder_label= $(this).val();
                var location       = $("#location").combogrid('getValue');
                var location_code  = $("#location_code").textbox('getValue');
                var period_month   = $("#period_month").combogrid('getValue');
                var period_year    = $("#period_year").combogrid('getValue');
                var doc_no         = $("#doc_no").textbox('getValue');

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/sto_wip_fg/getChecksheetLabel') ?>",
                    data: {
                        workorder_label: workorder_label,
                        location: location_code,
                    },
                    dataType: "json",
                    success: function(json) {
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
                            var row = json.data;

                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('control/sto_wip_fg/create_bulk') ?>",
                                data: {
                                    doc_no: doc_no,
                                    location: location_code,
                                    period_month: period_month,
                                    period_year: period_year,
                                    rows: json.data
                                },
                                dataType: "json",
                                success: function(result) {

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
                                error: function(xhr) {

                                    const response = xhr.responseText || '';

                                    if (
                                        xhr.responseText &&
                                        xhr.responseText.includes("Duplicate entry") &&
                                        xhr.responseText.includes("uq_label")
                                    ) {
                                        serialDuplicate.play();
                                        toastr.warning('Label has already been scanned', 'Scanned');
                                        $('#workorder_label').val('').focus();

                                        return;
                                    }

                                    toastr.error('An error occurred', 'Error');
                                }
                            });

                            return;
                        }

                    }
                });
            }
        });

    });

    function validateStartScanButton() {

        let location     = $('#location').combogrid('getValue');
        let period_month = $('#period_month').combobox('getValue');
        let period_year  = $('#period_year').combobox('getValue');

        if (location && period_month && period_year) {
            $('#btnStartScan').linkbutton('enable');
        } else {
            $('#btnStartScan').linkbutton('disable');
        }
    }

    $('#period_month').combobox({
        onChange: function(newValue, oldValue) {
            regenerateDocNo();
            validateStartScanButton();
        }
    });

    $('#period_year').combobox({
        onChange: function(newValue, oldValue) {
            regenerateDocNo();
            validateStartScanButton();
        }
    });

    function regenerateDocNo() {
        if (isLoadingExistingData) {
            return;
        }
        // console.log("regenerateDocNo");

        let period_month = $('#period_month').combobox('getValue');
        let period_year = $('#period_year').combobox('getValue');
        let location = $('#location').combogrid('getValue');

        if (!period_month || !period_year || !location) {
            $('#doc_no').textbox('clear');
            return;
        }

        if (period_month && location) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/sto_wip_fg/sto_doc_no') ?>",
                data: { 
                    period_month: period_month, 
                    period_year: period_year, 
                    location: location 
                },
                dataType: "html",
                success: function(result) {
                    // console.log("Generated =", result);
                    
                    $("#doc_no").textbox('setValue', result);
                    // $("#doc_no").textbox('setText', result);

                    // console.log("After set =", $("#doc_no").textbox("getValue"));
                }
            });
        }
    }

    $('#location').combogrid({
        url: '<?= base_url('control/sto_wip_fg/readLocations'); ?>',
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
            $('#location_code').textbox('setValue', row.number);
            regenerateDocNo();
            validateStartScanButton();  
        }
    });


    $('#period_month').combobox({
        url: '<?= base_url('control/sto_wip_fg/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#period_year').combobox({
        url: '<?= base_url('control/sto_wip_fg/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

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
        if (parseFloat(value) === 0){
            return 'background-color:#FFC8C8; color: #000;';
        } else {
            return 'background-color:#C8FFCC; color: #000;';
        }
    }

    function numberStyle2(value, row, index) {
        return 'background-color:#C8FFCC; color: #000;';
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


    function btnPreview() {

        $('#dlgStoWipFg').dialog('open').dialog('center');

        $('#dgStoWipFg').datagrid({
            url: '<?= base_url('control/sto_wip_fg/getSummary') ?>',
            method: 'get',
            fitColumns: true,
            singleSelect: true,

            columns: [[
                {
                    field: 'no',
                    title: 'No',
                    width: 60,
                    align: 'center',
                    halign: 'center',
                    formatter: function (v, r, i) {
                        return r.is_total ? '' : i + 1;
                    }
                },
                {
                    field: 'label_type',
                    title: 'Label Type',
                    width: 150,
                    halign: 'center',
                },
                {
                    field: 'item_fg_number',
                    title: 'Product No',
                    width: 180,
                    halign: 'center',
                },
                {
                    field: 'item_fg_name',
                    title: 'Product Name',
                    width: 200,
                    halign: 'center',
                    formatter: function (value, row) {
                        return row.is_total
                            ? '<b style="float:right">TOTAL</b>'
                            : value;
                    }
                },
                {
                    field: 'qty',
                    title: 'Qty',
                    width: 120,
                    halign: 'center',
                    align: 'right',
                    formatter: numberformatInt
                }
            ]],

            onLoadSuccess: function (data) {
                let totalQty = 0;

                $.each(data.rows, function (i, row) {
                    totalQty += Number(row.qty || 0);
                });

                let rows = $('#dgStoWipFg').datagrid('getRows');
                if (rows.length && rows[rows.length - 1].is_total) {
                    $('#dgStoWipFg').datagrid('deleteRow', rows.length - 1);
                }

                $('#dgStoWipFg').datagrid('appendRow', {
                    is_total: true,
                    qty: totalQty
                });
            }
        });
    }

    function saveSummaryStoWipFg() {

        var doc_no        = $("#doc_no").textbox('getValue');
        var location      = $("#location").combogrid('getValue');
        var location_code = $("#location_code").textbox('getValue');
        var period_month  = $("#period_month").combogrid('getValue');
        var period_year   = $("#period_year").combogrid('getValue');

        if (location == "" || doc_no == "" || period_month == "" || period_year == "") {
            toastr.warning('Data header is required');
            return;
        }

        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('Data Not Found!');
            return;
        }

        let items = [];

        rows.forEach(row => {
            if (row.item_fg_id) {
                items.push({
                    doc_no: doc_no,

                    parent_id: row.sto_wip_fg_id,
                    detail_id: row.id,
                    scan_id: row.scan_id,
                });
            }
        });

        if (items.length === 0) {
            toastr.error("No data to save");
            return;
        }

        Swal.fire({
            title: 'Confirm Save',
            text: 'Are you sure you want to save this STO WIP FG data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('control/sto_wip_fg/saveSummaryStoWipFg') ?>',
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
                                $('#dlgStoWipFg').dialog('close');
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

</script>