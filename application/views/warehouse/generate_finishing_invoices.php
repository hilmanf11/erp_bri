<!-- TABLE DATAGRID UTAMA (HEADER) -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'finishing_invoice_no',width:200,halign:'center',sortable:true">Invoice No</th>
            <th rowspan="2" data-options="field:'vendor_name',width:250,halign:'center'">Subcont / Teaching Factory</th>
            <th rowspan="2" data-options="field:'approved_to',width:120,halign:'center',align:'center',styler:styleApprovedStatus,formatter:formatApprovedStatus">Status Approve</th>
            <th rowspan="2" data-options="field:'approved_by',width:120,halign:'center'">Approve By</th>
            <th rowspan="2" data-options="field:'approved_date',width:120,halign:'center'">Approve Date</th>
            <th rowspan="2" data-options="field:'p_month',width:120,halign:'center',align:'center',sortable:true">Month</th>
            <th rowspan="2" data-options="field:'p_year',width:120,halign:'center',align:'center',sortable:true">Year</th>
            <th rowspan="2" data-options="field:'cutoff_from',width:100,halign:'center',align:'center'">Cutoff Start</th>
            <th rowspan="2" data-options="field:'cutoff_to',width:100,halign:'center',align:'center'">Cutoff End</th>
            <th rowspan="2" data-options="field:'total',width:120,halign:'center',align:'right'">Total</th>
            <th rowspan="2" data-options="field:'biaya_fee',width:120,halign:'center',align:'right'">Biaya Fee</th>
            <th rowspan="2" data-options="field:'grand_total',width:150,halign:'center',align:'right'">Grand Total</th>
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

<div id="toolbar" class="easyui-panel" style="width:100%; background: #F4F4F4; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 45%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Period</span>
                <input style="width:30%;" name="filter_month" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                <input style="width:30%;" name="filter_year" id="filter_year" class="easyui-combobox" data-options="prompt:'Year'">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Cutoff</span>
                <input style="width:29%;" id="filter_cutoff_from" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_cutoff_to" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Subcont</span>
                <input style="width:60%;" id="filter_subcont" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                <!-- <a href="javascript:;" class="easyui-linkbutton" onclick="print_pr()"><i class="fa fa-print"></i> Purchase Request</a> -->
            </div>
        </fieldset>
        <fieldset style="width: 33%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Process Generate Data</b></legend>
            <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
            <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
            <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
            <div id="p_remarks" class="easyui-panel" style="width:100%; height:120px; padding:10px; margin-top: 10px; overflow: auto;">
                <ul id="remarks">
                </ul>
            </div>
            <div class="fitem" style="text-align:left;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="downloadFailed()">
                    <i class="fa fa-download"></i> List Failed
                </a>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
    <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="printInvoice()"><i class="fa fa-print"></i> Print Invoice</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="printRecap()"><i class="fa fa-file-text-o"></i> Print Recap Invoice</a>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('warehouse/generate_finishing_invoices/print') ?>" style="width: 100%;" hidden></iframe>
<?php
    // Siapkan Data Bulan
    $arr_months = array();
    $month_names = array('1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
    foreach ($month_names as $key => $value) {
        $arr_months[] = array("id" => (string)$key, "name" => $value);
    }

    // Siapkan Data Tahun
    $arr_years = array();
    $tahun_before = date('Y', strtotime('-7 year'));
    $tahun_next = date('Y', strtotime('+1 year'));
    for ($i = $tahun_next; $i >= $tahun_before; $i--) {
        $arr_years[] = array("id" => (string)$i, "name" => (string)$i);
    }
?>

<script> 
    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_cutoff_from = $("#filter_cutoff_from").combobox('getValue');
        var filter_cutoff_to = $("#filter_cutoff_to").combobox('getValue');
        var filter_subcont = $("#filter_subcont").combogrid('getValue');

        if (filter_month !== "" && filter_year !== "" && filter_cutoff_from !== "" && filter_cutoff_to !== "" && filter_subcont !== "") {            
            $.messager.prompt('Generate MPS', 'Please input Password Generate', function(r) {
                if (r == "GENERATE") {
                    Swal.fire({
                        title: 'Please Wait 5 - 10 Minutes for Generating Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "get",
                        url: "<?= base_url('warehouse/generate_finishing_invoices/getdata') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_cutoff_from=" + window.btoa(filter_cutoff_from) +
                            "&filter_cutoff_to=" + window.btoa(filter_cutoff_to) +
                            "&filter_subcont=" + window.btoa(filter_subcont),
                        dataType: "text",
                        success: function(rows) {
                            let results = JSON.parse(rows);
                            Swal.close();
                            if(results.length>0){
                                requestData(results.length, results);
                            }
                            // requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('warehouse/generate_finishing_invoices/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            Swal.close();
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
                                                url: "<?= base_url('warehouse/generate_finishing_invoices/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });

                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                        }

                                        $("#p_remarks").append(title + "<br>");
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            Swal.fire({
                                                title: 'Connection Time Out, Check Your Connection',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            requestData(total, json, number, value, success + 0, failed + 0);
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            });
        } else {
            toastr.warning("Mohon lengkapi Filter terlebih dahulu ", "Information");
        }
    }

     //Delete Data
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.approved_to != "") {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('warehouse/generate_finishing_invoices/delete') ?>',
                                data: {
                                    id: row.id
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
                        } else {
                            toastr.error("You cannot delete this data, because it already Approved");
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function downloadFailed() {
        window.open('<?= base_url('warehouse/generate_finishing_invoices/uploadDownloadFailed') ?>', '_blank');
    }

    function filter() {
        var filter_cutoff_from = $("#filter_cutoff_from").datebox('getValue');
        var filter_cutoff_to = $("#filter_cutoff_to").datebox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_subcont = $("#filter_subcont").combogrid('getValue');
        url = "?filter_cutoff_from=" + filter_cutoff_from + "&filter_cutoff_to=" + filter_cutoff_to + "&filter_invoice_no=" + filter_invoice_no + "&filter_subcont=" + filter_subcont;
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/generate_finishing_invoices/datatables') ?>' + url
        });
        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('warehouse/generate_finishing_invoices/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_cutoff_from = $("#filter_cutoff_from").datebox('getValue');
        var filter_cutoff_to = $("#filter_cutoff_to").datebox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_subcont = $("#filter_subcont").combogrid('getValue');
        url = "?filter_cutoff_from=" + filter_cutoff_from + "&filter_cutoff_to=" + filter_cutoff_to + "&filter_invoice_no=" + filter_invoice_no + "&filter_subcont=" + filter_subcont;
        window.location.assign('<?= base_url('warehouse/generate_finishing_invoices/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html('Generate');

        var filter_cutoff_from = $("#filter_cutoff_from").datebox('getValue');
        var filter_cutoff_to = $("#filter_cutoff_to").datebox('getValue');
        url = "?filter_cutoff_from=" + filter_cutoff_from + "&filter_cutoff_to=" + filter_cutoff_to
        
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/generate_finishing_invoices/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [10, 20, 50, 100],
            pageSize: 10,
            view: detailview, 
            resizable: true,
            remoteSort: true,
            detailFormatter: function(index, row) {
                return '<div style="padding:5px;position:relative;"><table class="ddv" title="Detail Of ' + row.finishing_invoice_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('warehouse/generate_finishing_invoices/datatableDetails?id=') ?>' + window.btoa(row.id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [[
                        {field: 'item_number', title: 'PART NUMBER', width: 150, halign: 'center'},
                        {field: 'item_name', title: 'PART NAME', width: 250, halign: 'center'},
                        {field: 'price', title: 'PRICE (Rp)', width: 100, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'qty', title: 'QTY', width: 80, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'price_fg', title: 'FG (Rp)', width: 120, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'qty_1', title: 'QTY NG', width: 90, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'price_defect', title: 'NG (Rp)', width: 120, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'qty_rework', title: 'QTY REWORK', width: 90, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'price_rework', title: 'REWORK (Rp)', width: 120, halign: 'center', align: 'right', formatter: formatRupiah},
                        {field: 'sub_total', title: 'TOTAL INCOME', width: 130, halign: 'center', align: 'right', formatter: formatRupiah}
                    ]],
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
        $("#expected_date").datebox({
            onChange: function() {
                var request_date = $("#request_date").datebox('getValue');
                var expected_date = $("#expected_date").datebox('getValue');
                if (expected_date < request_date) {
                    $("#expected_date").datebox('clear');
                    toastr.warning("Request Date > Expected Date");
                }
            }
        });
        $("#request_date").datebox({
            onChange: function() {
                var request_date = $("#request_date").datebox('getValue');
                var expected_date = $("#expected_date").datebox('getValue');
                if (expected_date < request_date) {
                    $("#request_date").datebox('clear');
                    toastr.warning("Request Date < Expected Date");
                }
            }
        });

        $("#filter_invoice_no").combobox({
            url: '<?= base_url('warehouse/generate_finishing_invoices/readInvoice') ?>',
            valueField: 'finishing_invoice_no',
            textField: 'finishing_invoice_no',
            prompt: "Select Invoice No"
        });

        $("#filter_subcont").combogrid({
            url: '<?= base_url('warehouse/generate_finishing_invoices/readSubcont') ?>',
            idField: 'subcont', // Di combogrid, valueField berubah nama menjadi idField
            textField: 'name',
            prompt: "Select Subcont / TF",
            mode: 'remote',     // Wajib ditambahkan agar fitur pencarian $_POST['q'] berfungsi
            panelWidth: 450,    // Lebarkan panel agar 2 kolom muat dan tidak terpotong
            fitColumns: true,   // Agar lebar kolom otomatis menyesuaikan panelWidth
            columns: [[
                {field: 'name', title: 'Name', width: 250, halign: 'center'},
                {field: 'source', title: 'Source', width: 150, halign: 'center'}
            ]]
        });

        // 1. Fungsi Penghitung Cutoff (Tetap sama persis)
        function updateCutoffDates() {
            var month = $("#filter_month").combobox('getValue');
            var year = $("#filter_year").combobox('getValue');

            if (month && year) {
                var toDate = new Date(year, month - 1, 15);
                var fromDate = new Date(year, month - 2, 14);

                var formatToDate = toDate.getFullYear() + '-' + String(toDate.getMonth() + 1).padStart(2, '0') + '-15';
                var formatFromDate = fromDate.getFullYear() + '-' + String(fromDate.getMonth() + 1).padStart(2, '0') + '-14';

                $("#filter_cutoff_from").datebox('setValue', formatFromDate);
                $("#filter_cutoff_to").datebox('setValue', formatToDate);
            } else {
                $("#filter_cutoff_from").datebox('clear');
                $("#filter_cutoff_to").datebox('clear');
            }
        }

        // 2. Combobox Bulan (Versi Local Data)
        $('#filter_month').combobox({
            data: <?= json_encode($arr_months) ?>, 
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            value: '<?= date("n") ?>', 
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onLoadSuccess: function() {
                // Panggil perhitungan cutoff saat data selesai di-render pertama kali
                updateCutoffDates();
            },
            onChange: function(newValue, oldValue) {
                updateCutoffDates();
            }
        });

        // 3. Combobox Tahun (Versi Local Data)
        $('#filter_year').combobox({
            data: <?= json_encode($arr_years) ?>, 
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            value: '<?= date("Y") ?>', 
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onLoadSuccess: function() {
                // Panggil perhitungan cutoff saat data selesai di-render pertama kali
                updateCutoffDates();
            },
            onChange: function(newValue, oldValue) {
                updateCutoffDates();
            }
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }
    //Format Datepicker
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
        if (value != null) {
            const formatter = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function statusformat(value, row) {
        if (value == 0) {
            return "<b style='color:red;'>UNCONVERTED</b>";
        } else if (value == 1) {
            return "<b style='color:green;'>CONVERTED</b>";
        }
    }

    function statusStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFC8C8;';
        } else if (value == 1) {
            return 'background-color:#C8FFCC;';
        }
    }

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


    function styleApprovedStatus(value, row) {
        value = formatApprovedStatus(value, row);

        switch (value) {
            case 'Checking':
                return 'background:#FF5F5F;color:#fff;';

            case 'Approved':
                return 'background:#53D636;color:#fff;';

            case 'Disapprove':
                return 'background:#FF0000;color:#fff;';
        }

        return '';
    }

    function formatApprovedStatus(value, row) {
        if (parseInt(row.deleted) === 2) {
            return 'Disapprove';
        }
        if (!row.approved_to || row.approved_to.trim() === '') {
            return 'Approved';
        }
        return 'Checking';
    }

    function formatRupiah(value, row, index) {
        if (value === null || value === undefined || value === '') {
            return '0';
        }
        var num = parseFloat(value);
        if (isNaN(num)) {
            return value;
        }

        return num.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function printInvoice() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            // Membuka halaman print Controller dengan melempar ID master invoice
            window.open('<?= base_url('warehouse/generate_finishing_invoices/print_invoice/') ?>' + row.id, '_blank');
        } else {
            $.messager.alert('Warning', 'Pilih salah satu baris invoice yang ingin di-print!', 'warning');
        }
    }

    function printRecap() {
        var rows = $('#dg').datagrid('getSelections');
        
        if (rows.length === 0) {
            toastr.warning('Pilih minimal satu baris invoice yang ingin direkap!');
            return;
        }

        var ids = [];
        var hasTF = false;
        var hasSub = false;

        // Ambil tanggal dari baris pertama sebagai patokan pembanding
        var firstInvoiceDate = rows[0].finishing_invoice_date;
        var firstPeriodStart = rows[0].period_start;
        var firstPeriodEnd   = rows[0].period_end;

        for (var i = 0; i < rows.length; i++) {
            // VALIDASI 1: Cek apakah statusnya belum Approved
            if (rows[i].approved_to !== null && rows[i].approved_to !== undefined && rows[i].approved_to !== '') {
                toastr.error('Gagal! Invoice nomor <b>' + rows[i].finishing_invoice_no + '</b> belum fully approved. Rekap hanya bisa dicetak untuk data yang sudah disetujui.');
                return; 
            }

            // VALIDASI 2: Cek kesamaan tanggal dengan baris pertama
            if (rows[i].finishing_invoice_date !== firstInvoiceDate || 
                rows[i].period_start !== firstPeriodStart || 
                rows[i].period_end !== firstPeriodEnd) {
                toastr.error('Validasi Gagal! Tanggal Invoice, Cutoff From, dan Cutoff To dari data yang dipilih harus sama.');
                return;
            }

            ids.push(rows[i].id);
            
            var invoiceNo = rows[i].finishing_invoice_no.toUpperCase();
            if (invoiceNo.includes('TF')) {
                hasTF = true;
            } else if (invoiceNo.includes('SUB')) {
                hasSub = true;
            }
        }

        // VALIDASI 3: Pengecekan campuran kategori TF dan SUB
        if (hasTF && hasSub) {
            toastr.error('Tidak boleh mencampur antara Invoice Teaching Factory dan Subcont/Koordinator dalam satu cetakan rekap!');
            return;
        }

        var url = '<?= base_url('warehouse/generate_finishing_invoices/print_recap/') ?>?ids=' + ids.join(',');
        window.open(url, '_blank');
    }
</script>
