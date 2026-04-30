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
            <th data-options="field:'item_fg_number',width:250,halign:'center'">Product No</th>
            <th data-options="field:'item_fg_name',width:300,halign:'center'">Product Name</th>
            <th data-options="field:'workorder',width:250,halign:'center'">WO No</th>
            <th data-options="field:'workorder_label',width:250,halign:'center'">Serial WO No</th>
            <th data-options="field:'serial_label',width:250,halign:'center'">Serial Label</th>
            <th data-options="field:'qty',width:150,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2,sortable:true,editor:{type:'numberbox',options:{precision:2}}"> Qty</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 283px;">
    <div style="width: 100%; padding: 10px;">

        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;padding-top: 15px;">

            <legend><b>Scan Out Rework</b></legend>
            <div style="width: 30%; float: left;">

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Category</span>
                    <input style="width:60%;" name="delivery_category" id="delivery_category" required="" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Date</span>
                    <input style="width:60%;" name="delivery_date" id="delivery_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem" id="destination_wrapper">
                    <span style="width:35%; display:inline-block;">Destination</span>
                    <input style="width:60%;" name="destination" id="destination" required="" class="easyui-combogrid" data-options="editable: false">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Destination Code</span>
                    <input style="width:60%;" name="destination_code" id="destination_code" required="" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">DNR No</span>
                    <input style="width:60%;" name="dnr_no" id="dnr_no" readonly required class="easyui-textbox">
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
                    <input style="width:100%; height: 80px;" type="text" id="serial_label" name="serial_label" class="scan" placeholder="SCAN LABEL HERE" autofocus>
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

<div id="dlgSummaryScanOutRework" class="easyui-dialog" title="Summary Scan Out Rework" style="width:900px;height:500px;padding:10px" closed="true" modal="true" buttons="#dlgSummaryScanOutReworkButtons">

    <table id="dgSummaryScanOutRework" class="easyui-datagrid" style="width:100%;height:100%;"></table>
</div>

<div id="dlgSummaryScanOutReworkButtons">
    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-no" onclick="$('#dlgSummaryScanOutRework').dialog('close')">Close</a>

    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveSummaryScanOutRework()">Save</a>
</div>

<script>

    let isLoadingExistingData = false;

    function reload() {
        window.location.reload();

        $('#serial_label').val('').prop('disabled', true);
        $('a[onclick="start_scan()"]').linkbutton('enable');
    }

    function start_scan() {
        var dnr_no = $("#dnr_no").textbox('getValue');
        var delivery_date = $("#delivery_date").datebox('getValue');
        var delivery_category = $("#delivery_category").textbox('getValue');
        var destination = $("#destination").combogrid('getValue');

        if(dnr_no != "" && delivery_date != "" && destination != "") {

            $('#dnr_no').textbox('disable');
            $('#delivery_date').datebox('disable');
            $('#delivery_category').textbox('disable');
            $('#destination').combogrid('disable');

            $('#serial_label').prop('disabled', false).focus();
            $('a[onclick="start_scan()"]').linkbutton('disable');

            toastr.success("Scan mode activated");

        } else {
            toastr.error("Please fill in all required fields first");
        }
    }

    $(function() {

        $('#serial_label').prop('disabled', true);

        $('#delivery_category').textbox('setValue', 'Rework');
        $('#delivery_category').textbox('readonly', true);

        setTimeout(function(){
            $('#delivery_date').datebox('setValue', '<?= date("Y-m-d") ?>');
            $('#destination').combogrid('clear');
            $('#destination_code').combogrid('clear');
        }, 50);

        setTimeout(function() {

            $('#dg').datagrid({
                url: '<?= base_url("control/scan_out_rework/getScanOutRework") ?>',
                rownumbers: true,
                onLoadSuccess: function(data) {
                    if (data.total === 0) {
                        // console.log("Data Not Found!");

                        $('#dnr_no').textbox('enable');
                        $('#delivery_date').datebox('enable');
                        $('#delivery_category').textbox('enable');
                        $('#destination').combogrid('enable');
                        $('a[onclick="start_scan()"]').linkbutton('enable');
                    
                    } else {
                        isLoadingExistingData = true;
                        
                        $('#serial_label').prop('disabled', false).focus();
                        $('a[onclick="start_scan()"]').linkbutton('disable');

                        $("#dnr_no").textbox('setValue', data.rows[0]['dnr_no']);
                        $("#delivery_date").datebox('setValue', data.rows[0]['delivery_date']);
                        $("#delivery_category").textbox('setValue', data.rows[0]['delivery_category']);
                        $("#destination_code").textbox('setValue', data.rows[0]['destination']);

                        $("#destination").combogrid('setValue', data.rows[0]['destination_number']);

                        $('#dnr_no').textbox('disable');
                        $('#delivery_date').datebox('disable');
                        $('#delivery_category').textbox('disable');
                        $('#destination').combogrid('disable');

                        setTimeout(() => {
                            isLoadingExistingData = false;
                        }, 200);
                        
                        // console.log('Data : ', data.rows[0])
                    }
                },
                onLoadError: function(xhr) {
                    console.error("Load datagrid error:", xhr.responseText);
                },
            });

        }, 50);

        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        // var moreThanQty = document.getElementById("moreThanQty");

        setTimeout(function() {
            $('#serial_label').focus(); 
        }, 200);

        //Scan Label
        $('#serial_label').keypress(function(e) {
            if (e.which == 13) {
                var serial_label        = $(this).val();
                var dnr_no              = $("#dnr_no").textbox('getValue');
                var delivery_date       = $("#delivery_date").datebox('getValue');
                var delivery_category   = $("#delivery_category").textbox('getValue');
                var destination         = $("#destination").combogrid('getValue');
                var destination_code    = $("#destination_code").textbox('getValue');

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/scan_out_rework/getChecksheetLabel') ?>",
                    // data: "serial_label=" + serial_label,
                    data: {
                        serial_label: serial_label,
                        destination_code: destination_code
                    },
                    dataType: "json",
                    success: function(json) {
                        console.log('Response : ', json);
                        console.log('Destination Code : ', destination_code);                        

                        if (json.title === "Not Found") {
                            serialNotFound.play();
                            toastr.warning(json.message, "Not Found");
                            $("#serial_label").val('').focus();
                            return;
                        } else if (json.title === "Scanned" || json.title === "Available") {
                            serialDuplicate.play();
                            toastr.warning(json.message, "Already Scanned");
                            $("#serial_label").val('').focus();
                            return;
                        } else if(json.title !== "success") {
                            toastr.warning(json.message, json.title);
                            $("#serial_label").val('').focus();
                            return;
                        }

                        if (json.title === "success") {

                            var rows = json.data;
                            // console.log('Rows : ', rows);

                            rows = rows.map(function(row) {
                                row.delivery_category = delivery_category;
                                row.delivery_date = delivery_date;
                                row.destination = destination_code;
                                row.delivery_note_no = row.delivery_note_no || null;
                                row.dnr_no = dnr_no;
                                return row;
                            });

                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('control/scan_out_rework/create_bulk') ?>",
                                data: {
                                    rows: rows
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

                                    $("#serial_label").val('');
                                    $('#serial_label').focus();
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

    $('#destination').combogrid({
        onChange: function (newValue, oldValue) {

            regenerateScanOutReworkDocNo();
        }
    });

    $('#delivery_date').datebox({
        editable: false,
        onChange: function () {
            regenerateScanOutReworkDocNo();
        }
    });

    function regenerateScanOutReworkDocNo() {

        if (isLoadingExistingData) {
            return;
        }

        let delivery_date = $('#delivery_date').datebox('getValue');
        let destination = $('#destination').combogrid('getValue');

        if (delivery_date && destination) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/scan_out_rework/dnr_no') ?>",
                data: { delivery_date: delivery_date, destination: destination },
                dataType: "html",
                success: function(result) {
                    // console.log(result);

                    $("#dnr_no").textbox('setValue', result);
                    $("#dnr_no").textbox('setText', result);
                }
            });
        }
    }

    $('#destination').combogrid({
        url: '<?= base_url('control/scan_out_rework/readScanOutRework'); ?>',
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
            $('#destination_code').textbox('setValue', row.number);
        }
    });


    function btnPreview() {

        $('#dlgSummaryScanOutRework').dialog('open').dialog('center');

        $('#dgSummaryScanOutRework').datagrid({
            url: '<?= base_url('control/scan_out_rework/getSummaryScanOutRework') ?>',
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

                let rows = $('#dgSummaryScanOutRework').datagrid('getRows');
                if (rows.length && rows[rows.length - 1].is_total) {
                    $('#dgSummaryScanOutRework').datagrid('deleteRow', rows.length - 1);
                }

                $('#dgSummaryScanOutRework').datagrid('appendRow', {
                    is_total: true,
                    qty_total: totalQty
                });
            }
        });
    }

    function saveSummaryScanOutRework() {
        var dnr_no = $("#dnr_no").textbox('getValue');
        var delivery_date   = $("#delivery_date").datebox('getValue');
        var delivery_category = $("#delivery_category").textbox('getValue');
        var destination   = $("#destination").combogrid('getValue');
        var destination_code = $("#destination_code").textbox('getValue');

        if (dnr_no == "" || delivery_date == "" || delivery_category == "" || destination == "") {
            toastr.warning('Scan Out Rework header is required');
            return;
        }

        // endEditing();
        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('Data Not Found!');
            return;
        }

        let items = [];

        rows.forEach(row => {
            if (row.item_fg_id) {
                items.push({
                    // dnr_no: dnr_no,
                    delivery_date: delivery_date,
                    delivery_category: delivery_category,
                    destination: destination_code,

                    dnr_no: row.dnr_no,
                    delivery_note_no: row.delivery_note_no,
                    scan_id: row.scan_id,
                    item_fg_id: row.item_fg_id,
                    workorder: row.workorder,
                    workorder_label: row.workorder_label,
                    serial_label: row.serial_label,
                    qty: row.qty,
                });
            }
        });

        if (items.length === 0) {
            toastr.error("No data to save");
            return;
        }

        console.log('ITEMS : ', items);

        Swal.fire({
            title: 'Confirm Save',
            text: 'Are you sure you want to save this data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('control/scan_out_rework/saveSummaryScanOutRework') ?>',
                    data: { 
                        items: items 
                    },
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

                        // if (res.theme === "success") {
                        //     Swal.fire({
                        //         title: res.message,
                        //         text: 'Do you want to print Delivery Note?',
                        //         icon: 'success',
                        //         showCancelButton: true,
                        //         confirmButtonText: 'Yes, Print',
                        //         cancelButtonText: 'Cancel',
                        //         allowOutsideClick: false
                        //     }).then((result) => {

                        //         $('#dg').datagrid('reload');
                        //         $('#dlgSummaryScanOutRework').dialog('close');

                        //         if (result.isConfirmed) {
                        //             let dnr_no = $("#dnr_no").textbox('getValue');
                        //             print_dn_to_rework(dnr_no);
                        //         } else {
                        //             window.location.reload();
                        //         }

                        //     });
                        // } else {
                        //     toastr.error(res.message, res.title || "Error");
                        // }
                    },
                    error: function () {
                        Swal.close();
                        toastr.error("Server error while saving");
                    }
                });

            }
        });

    }


    // function print_dn_to_rework(dnr_no) {
    //     console.log(dnr_no);
    //     var url = '<?= base_url('control/scan_out_rework/print_dn_to_rework/') ?>' + window.btoa(dnr_no);
    //     window.open(url, '_blank');
    // }

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
</script>