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
</style>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar" pagination="true">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'serial_label',halign:'center',width:250">Serial Label No</th>
            <th rowspan="2" data-options="field:'item_number',width:150,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right'"> Qty</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'scan_date',width:120,halign:'center'">Scan In Date</th>
            <th rowspan="2" data-options="field:'scan_by',width:120,halign:'center'">Scan In By</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<div id="toolbar" style="height: 240px;">
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="serial_label" name="serial_label" class="scan" placeholder="SCAN LABEL HERE">
            </div>
            <div class="fitem" style="padding:0 200px 10px 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
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
<script>
    function reload() {
        window.location.reload();
    }
    $(function() {
    // Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");

        // Set fokus ke input scan saat halaman dimuat
        $('#serial_label').focus();

            // Inisialisasi DataGrid untuk menampilkan data yang sudah di-scan sebelumnya
            $('#dg').datagrid({
                url: '<?= base_url('warehouse/scan_in_rfg/getAllScannedData') ?>', // Menampilkan semua data yang sudah di-scan
                method: 'GET',
                rownumbers: true,
                pagination: true,
                singleSelect: false
            });

        // Event ketika tombol Enter ditekan pada input scan
        $('#serial_label').keypress(function(e) {
            if (e.which == 13) {
                var serial_label = $("#serial_label").val().trim();

                if (serial_label === '') return;

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/scan_in_rfg/getSerialLabel') ?>",
                    data: { serial_label: serial_label },
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/scan_in_rfg/create') ?>",
                                    data: {
                                        serial_label: serial_label,
                                        item_fg_id: row[i].item_fg_id,
                                        uom: row[i].uom,
                                        qty: row[i].qty_packing
                                    },
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#serial_label").val('');
                                            $('#serial_label').focus();
                                            $('#dg').datagrid('reload'); // Memuat ulang tabel setelah scan berhasil
                                        } else {
                                            if (result.title == "Not Scanned In" || result.title == "Not Registered") {
                                                serialNotFound.play();
                                            } else {
                                                serialDuplicate.play();
                                            }
                                            toastr.error(result.message, result.title);
                                            $("#serial_label").val('');
                                            $('#serial_label').focus();
                                        }
                                    }
                                });
                            }
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#serial_label").val('');
                            $('#serial_label').focus();
                        }
                    }
                });
            }
        });
    });

    setInterval(function () {
        let currentDate = new Date().toISOString().split('T')[0]; // Ambil tanggal hari ini
        let storedDate = localStorage.getItem("lastScanDate");

        if (storedDate !== currentDate) {
            localStorage.setItem("lastScanDate", currentDate);
            window.location.reload(); // Refresh halaman jika tanggal berubah
        }
    }, 60000); // Cek setiap 1 menit

    // function numberformat(value, row) {
    //     const formatter = new Intl.NumberFormat('id-ID', {
    //         minimumFractionDigits:
    //     });
    //     return "<b>" + formatter.format(value) + "</b>";
    // }

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
</script>