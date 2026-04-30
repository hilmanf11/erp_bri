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
            <th data-options="field:'workorder_label',width:250,halign:'center'">Serial WO No</th>
            <th data-options="field:'serial_label',width:250,halign:'center'">Serial Label</th>


            <th data-options="field:'qty',width:150,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2,sortable:true,editor:{type:'numberbox',options:{precision:2}}"> Qty</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 250px;">
    <div style="width: 100%; padding: 10px;">

        <a href="javascript:void(0)" onclick="backToMenu()" class="easyui-linkbutton" iconCls="icon-back">
            Back to WIP Store
        </a>

        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend style="text-align: center;"><b>Scan In Return</b></legend>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="serial_label" name="serial_label" class="scan" placeholder="SCAN LABEL HERE" autofocus>
            </div>
            <div class="fitem" style="padding:0 200px 10px 200px;">

                <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="btnPreview()">
                    <i class="fa fa-search"></i> Preview Data
                </a>

                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()">
                    <i class="fa fa-rotate-right"></i> Reload
                </a>

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


<div id="dlgReturn" class="easyui-dialog" title="Summary WIP Store Scan In Return" style="width:1000px;height:500px;padding:10px" closed="true" modal="true" buttons="#dlgReturnButtons">

    <table id="dgReturn" class="easyui-datagrid" style="width:100%;height:100%;"></table>
</div>

<div id="dlgReturnButtons">
    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-no" onclick="$('#dlgReturn').dialog('close')">Close</a>

    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveSummary()">Save</a>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    $(function() {

        setTimeout(function() {

            $('#dg').datagrid({
                url: '<?= base_url("control/scan_in_return/getScanInReturn") ?>',
                rownumbers: true,
                onLoadSuccess: function(data) {
                    if (data.total === 0) {
                        console.warn("Data Not Found!");
                    }
                },
                onLoadError: function(xhr) {
                    console.error("Load datagrid error:", xhr.responseText);
                }
            });

        }, 50);

        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");


        setTimeout(function() {
            $('#serial_label').focus(); 
        }, 200);


        //Scan Label
        $('#serial_label').keypress(function(e) {
            if (e.which == 13) {
                var serial_label = $(this).val();

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/scan_in_return/getChecksheetLabel') ?>",
                    data: "serial_label=" + serial_label,
                    dataType: "json",
                    success: function(json) {
                        console.log('Response : ', json);

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

                            var header = json.header;
                            var details = json.details;

                            console.log('HEADER:', header);
                            console.log('DETAILS:', details);

                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('control/scan_in_return/create') ?>",
                                data: {
                                    header: JSON.stringify(header),
                                    details: JSON.stringify(details)
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

    function btnPreview() {

        $('#dlgReturn').dialog('open').dialog('center');

        $('#dgReturn').datagrid({
            url: '<?= base_url('control/scan_in_return/getSummary') ?>',
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
                    width: 300
                },
                {
                    field: 'serial_label',
                    title: 'Serial Label',
                    width: 300,
                    formatter: function (value, row) {
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

                let rows = $('#dgReturn').datagrid('getRows');
                if (rows.length && rows[rows.length - 1].is_total) {
                    $('#dgReturn').datagrid('deleteRow', rows.length - 1);
                }

                $('#dgReturn').datagrid('appendRow', {
                    is_total: true,
                    qty_total: totalQty
                });
            }
        });
    }

    function saveSummary() {

        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('Data Not Found!');
            return;
        }

        let items = [];

        rows.forEach(row => {
            if (row.item_fg_id) {
                items.push({
                    item_fg_id: row.item_fg_id,
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

        // console.log('ITEMS : ', items);
        
        Swal.fire({
            title: 'Confirm Save',
            text: 'Are you sure you want to save this WIP Store data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('control/scan_in_return/saveSummary') ?>',
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
                                $('#dlgReturn').dialog('close');
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

    function backToMenu(){
        var token = window.location.pathname.split('/').pop();
        window.location.href = "<?= base_url('control/scan_in_wip_store/index/') ?>" + token;
    }
</script>