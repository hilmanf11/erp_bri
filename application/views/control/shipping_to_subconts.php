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

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <!-- <th data-options="field:'scan_id',width:250,halign:'center',hidden:true">Scan ID</th> -->
            <th data-options="field:'item_fg_number',width:250,halign:'center',sortable:true">Product No</th>
            <th data-options="field:'item_fg_name',width:300,halign:'center',sortable:true">Product Name</th>
            <th data-options="field:'workorder',width:250,halign:'center',sortable:true">WO No</th>
            <th data-options="field:'qty_label',width:150,halign:'center',align:'center',formatter:numberformatInt,sortable:true"> Qty Label</th>
            <th data-options="field:'shipping',width:150,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2,sortable:true"> Qty Delivery</th>
            <th data-options="field:'uom',width:100,align:'center',sortable:true">UoM</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 220px;">
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="workorder_label" name="workorder_label" class="scan" placeholder="SCAN LABEL HERE" autofocus>
            </div>
            <div class="fitem" style="padding:0 200px 10px 200px;">
                <a href="javascript:;" class="easyui-linkbutton" onclick="reload()"><i class="fa fa-rotate-right"></i> Reload</a>
                <a href="javascript:;" class="easyui-linkbutton" onclick="addDN()"><i class="fa fa-plus"></i> Add DN</a>
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

<!-- Dialog DN -->
<div id="dlg_insert" class="easyui-dialog" title="Add Delivery Note" data-options="closed: true,modal:true" style="width: 450px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>

            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Note No.</span>
                <input style="width:60%;" name="delivery_note_no" id="delivery_note_no" readonly required class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Date</span>
                <input style="width:60%;" name="delivery_date" id="delivery_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery Category</span>
                <select style="width:60%;" id="delivery_category" panelHeight="auto" class="easyui-combobox" data-options="editable:false" required>
                    <option value="Regular">Regular</option>
                    <option value="Rework">Rework</option>
                </select>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Delivery To</span>
                <select style="width:60%;" id="delivery_to_insert" panelHeight="auto" class="easyui-combobox" data-options="editable:false" required>
                    <option value="SUBCONT">Subcont</option>
                    <option value="TEFA">Teaching Factory</option>
                </select>
            </div>
            <div class="fitem" id="destination_wrapper">
                <span style="width:35%; display:inline-block;">Destination</span>
                <input style="width:60%;" name="destination" id="destination" required="" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Destination Code</span>
                <input style="width:60%;" name="destination_code" id="destination_code" required="" class="easyui-combogrid">
            </div>

        </fieldset>
    </form>
</div>


<script>
    function reload() {
        window.location.reload();
    }

    function addDN() {

        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning("Delivery Note data not found!");
            return;
        }

        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');

        $("#delivery_date").datebox({
            formatter: myformatter,
            parser: myparser,
            editable: false,
            onSelect: function(date){
                setTimeout(regenerateDeliveryNoteNo, 49);
            }
        });

        setTimeout(function(){
            $('#delivery_date').datebox('setValue', '<?= date("Y-m-d") ?>');
            $('#destination').combogrid('clear');
            $('#destination_code').combogrid('clear');
        }, 50);

    }

    $(function() {

        setTimeout(function() {

            $('#dg').datagrid({
                url: '<?= base_url("control/shipping_to_subconts/getShippingSubconts") ?>',
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
        // var moreThanQty = document.getElementById("moreThanQty");

        setTimeout(function() {
            $('#workorder_label').focus(); 
        }, 200);

        //Scan Label
        $('#workorder_label').keypress(function(e) {
            if (e.which == 13) {
                var workorder_label = $(this).val();

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/shipping_to_subconts/getChecksheetLabel') ?>",
                    data: "workorder_label=" + workorder_label,
                    dataType: "json",
                    success: function(json) {
                        console.log('Response : ', json);

                        if (json.title === "Not Found") {
                            serialNotFound.play();
                            toastr.warning(json.message, "Not Found");
                            $("#workorder_label").val('').focus();
                            return;
                        }

                        if (json.title === "Scanned") {
                            serialDuplicate.play();
                            toastr.warning(json.message, "Already Scanned");
                            $("#workorder_label").val('').focus();
                            return;
                        }

                        if (json.title === "Success" && json.total > 0) {

                            var row = json.rows[0];
                            console.log('Row : ', row);
                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('control/shipping_to_subconts/create') ?>",
                                data: {
                                    item_fg_id: row.item_fg_id,
                                    workorder: row.workorder,
                                    workorder_label: workorder_label,
                                    qty: row.qty_packing
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

        // Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {

                    var delivery_date = $("#delivery_date").datebox('getValue');
                    var delivery_note_no = $("#delivery_note_no").textbox('getValue');
                    var delivery_category = $("#delivery_category").combobox('getValue');
                    var delivery_to = $("#delivery_to_insert").combobox('getValue');
                    var destination = $("#destination").combogrid('getValue');
                    var destination_code = $("#destination_code").combogrid('getValue');

                    if(delivery_date == "" ||
                        delivery_note_no == "" ||
                        delivery_category == "" ||
                        delivery_to == "" ||
                        destination == "" ||
                        destination_code == ""
                    ) {
                        toastr.error("Please fill in all required fields first");
                        return;
                    }

                    var rows = $('#dg').datagrid('getRows');
                    if (rows.length === 0) {
                        toastr.warning("No rows found!");
                        return;
                    }

                    console.log(rows);

                    var payload = {
                        delivery_date: delivery_date,
                        delivery_note_no: delivery_note_no,
                        delivery_category: delivery_category,
                        delivery_to: delivery_to,
                        destination: destination,
                        destination_code: destination_code,
                        items: rows
                    };

                    var delivery_date = $("#delivery_date").datebox('getValue');
                    var delivery_note_no = $("#delivery_note_no").textbox('getValue');
                    var delivery_category = $("#delivery_category").combobox('getValue');
                    var delivery_to_insert = $("#delivery_to_insert").combobox('getValue');
                    var destination = $("#destination").combogrid('getValue');
                    var destination_code = $("#destination_code").combogrid('getValue');


                    $.ajax({
                        url: '<?= base_url("control/shipping_to_subconts/createDN") ?>',
                        type: 'POST',
                        data: { 
                            delivery_date: payload.delivery_date,
                            delivery_note_no: payload.delivery_note_no,
                            delivery_category: payload.delivery_category,
                            delivery_to: payload.delivery_to,
                            destination: payload.destination,
                            destination_code: payload.destination_code,
                            items: JSON.stringify(rows)
                        },
                        success: function(result){
                            var result = JSON.parse(result);
                            if(result.theme === 'success'){
                                toastr.success(result.message);
                                $('#dlg_insert').dialog('close');
                                $('#dg').datagrid('reload');
                            }else{
                                toastr.error(result.message);
                            }
                        }
                    });
                }
            }]
        });

    });


    $("#delivery_date").datebox({
        formatter: myformatter,
        parser: myparser,
        editable: false,
        onSelect: function(date){
            setTimeout(regenerateDeliveryNoteNo, 49);
        }
    });


    $('#delivery_to_insert').combobox({
        onChange: function (newValue, oldValue) {
            $("#destination").combogrid('enable');

            if (newValue === 'SUBCONT') {
                initSubcontGrid();
            } else if (newValue === 'TEFA') {
                initTefaGrid();
            } else {
                $('#destination').combogrid('clear');
                $('#destination').combogrid('grid').datagrid('loadData', []);
            }
        }
    });

    function initSubcontGrid() {
        $('#destination').combogrid({
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
            ]],
            onSelect: function(index, row) {
                $('#destination_code').combogrid('setValue', row.number);
                regenerateDeliveryNoteNo();
            }
        });
    }

    function initTefaGrid() {
        $('#destination').combogrid({
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
            ]],
            onSelect: function(index, row) {
                $('#destination_code').combogrid('setValue', row.number);
                regenerateDeliveryNoteNo();
            }
        });
    }

    function regenerateDeliveryNoteNo() {
        let trans_date = $('#delivery_date').datebox('getValue');
        let dest_code = $('#destination_code').combogrid('getValue');

        if (trans_date && dest_code) {
            $.ajax({
                type: "post",
                url: "<?= base_url('control/delivery_to_subconts/delivery_note_no') ?>",
                data: { trans_date: trans_date, destination_code: dest_code },
                dataType: "html",
                success: function(result) {
                    $("#delivery_note_no").textbox('setValue', result);
                }
            });
        }
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

</script>