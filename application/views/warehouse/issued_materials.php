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
    .info-table-eq {
        margin-top: 10px;
        margin-bottom: 30px;
    }
    .info-table-eq td {
        padding: 4px 8px;
        font-size: 14px !important;
    }
    .check-btn {
        border: none;
        background-color: transparent;
        font-size: 18px !important;
        cursor: pointer;
    }
    .check-btn.inactive {
        opacity: 0.9;
        cursor: not-allowed;
    }

</style>
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'request_no',halign:'center',width:180">Supplysheet No</th>
            <th rowspan="2" data-options="field:'period',halign:'center',width:100" hidden>Period</th>
            <th rowspan="2" data-options="field:'wp',width:80,halign:'center'" hidden>WP</th>
            <th rowspan="2" data-options="field:'workorder',width:150,halign:'center'" hidden>WO ID</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:150,halign:'center'">Part ID</th>
            <th rowspan="2" data-options="field:'item_rm_no',width:200,halign:'center'">Part No Internal</th>
            <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Part Name</th>
            <th rowspan="2" data-options="field:'mpq',width:80,halign:'center',align:'right'">MPQ</th>
            <th colspan="4" data-options="field:'',width:100,halign:'center',align:'right',formatter:numberformat"> Quantity</th>
            <th rowspan="2" data-options="field:'warehouse',width:80,align:'center',formatter:numberformat" hidden>Stock WHS</th>
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th>
            <th rowspan="2" data-options="field:'stock',width:80,halign:'center',align:'right',formatter:stockFormatter,styler:numberStockStyle">Stock</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
        </tr>
        <tr>
            <!-- <th data-options="field:'qty_supply',width:80,halign:'center',align:'right',formatter:numberformat">Need</th>
            <th data-options="field:'qty_act',width:80,halign:'center',align:'right',formatter:numberformat">Supply</th> -->

            <th data-options="field:'qty_act',width:80,halign:'center',align:'right',formatter:numberformat">Need</th>
            <th data-options="field:'qty_supply',width:80,halign:'center',align:'right',formatter:numberformat">Supply</th>

            <th data-options="field:'qty_req',width:90,halign:'center',align:'right',formatter:numberformat">Actual Issued</th>
            <th data-options="field:'balance',width:80,halign:'center',align:'right',formatter:numberformat,styler:numberStyle">Bal <br>Supply</th>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="dlg-equivalent" class="easyui-dialog" title="EQUIVALENT PART LIST" style="width:700px;height:auto;padding:10px" closed="true" modal="true">
    <table class="info-table-eq">
        <tr>
            <td style="font-weight: bold;">Part Master</td>
            <td> : </td>
            <td id="item_master_name">-</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Need</td>
            <td> : </td>
            <td id="item_master_need">0,00</td>
        </tr>
    </table>

    <table id="table-equivalent" class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'ck',width:50,align:'center',formatter:rowNumberFormatter">NO</th>
                <th data-options="field:'item_rm_name',width:150,align:'center'">EQUIVALENT</th>
                <th data-options="field:'bal_wip',width:80,align:'center'">BAL WIP</th>
                <!-- <th data-options="field:'mpq',width:80,align:'center',formatter:numberformat">MPQ</th> -->
                <th data-options="field:'stock',width:80,align:'center',formatter:numberformat">STOCK</th>
                <th data-options="field:'action',width:80,align:'center',formatter:equivalentSelectFormatter">ACTION</th>
            </tr>
        </thead>
    </table>
</div>

<div id="toolbar" style="height: 320px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Scan Barcode</b></legend>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="request_no" name="request_no" class="scan" placeholder="SCAN SUPPLY SHEET HERE">
            </div>
            <div class="fitem" style="padding:0 200px 0 200px;">
                <input style="width:100%; height: 80px;" type="text" id="receipt_id" name="receipt_id" class="scan" placeholder="SCAN LABEL HERE">
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
<audio id="moreThanQty">
    <source src="<?= base_url('assets/audio/more_than_qty.mp3') ?>" type="audio/mpeg">
</audio>
<audio id="FIFOValidation">
    <source src="<?= base_url('assets/audio/fifo-v1.mp3') ?>" type="audio/mp3">
</audio>
<audio id="labelAlreadyScan">
    <source src="<?= base_url('assets/audio/label_already.mp3') ?>" type="audio/mp3">
</audio>
<script>
    let globalNeed = 0;
    let globalSelectedRow = null; 

    function reload() {
        window.location.reload();
    }
    $(function() {
        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");
        var moreThanQty = document.getElementById("moreThanQty");
        var FIFOValidation = document.getElementById("FIFOValidation");
        var labelAlreadyScan = document.getElementById("labelAlreadyScan");

        // Variabel untuk menyimpan item_rm_id yang diharapkan
        var expected_item_rm_ids = [];

        //Scan Supply Sheet
        $('#request_no').focus();
        $('#request_no').keypress(function(e) {
            if (e.which == 13) {
                var request_no = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/issued_materials/getSupplySheet') ?>",
                    data: "request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            var row = json.rows;
                            // Simpan semua item_rm_id yang diharapkan
                            expected_item_rm_ids = row.map(item => item.item_rm_id);
                            for (let i = 0; i < json.total; i++) {
                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/issued_materials/create') ?>",
                                    data:
                                        "&item_rm_id=" + row[i].item_rm_id +
                                        "&request_no=" + row[i].request_no +
                                        "&period=" + row[i].period +
                                        "&wp=" + row[i].wp +
                                        "&workorder=" + row[i].workorder +
                                        "&qty=" + row[i].qty_req,
                                    dataType: "json",
                                    success: function(result) {
                                        $('#receipt_id').focus();
                                        // Update stock WHS here
                                        $('#dg').datagrid('updateRow', {
                                            index: i,
                                            row: {
                                                warehouse: result.warehouse // Assuming result contains stock_whs
                                            }
                                        });
                                    }
                                });
                            }
                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/issued_materials/datatables?request_no=') ?>' + window.btoa(request_no),
                                rownumbers: true,
                                onClickCell: function(index, field, value) {
                                    const row = $(this).datagrid('getRows')[index];
                                    globalSelectedRow = row;
                                }
                            });
                        } else {
                            toastr.warning("Supply Sheet not found!");
                            $("#request_no").val('');
                        }
                    }
                });
            }
        });

        //Scan Label
        $('#receipt_id').keypress(function(e) {
            if (e.which == 13) {
                var receipt_id = $(this).val();
                var request_no = $("#request_no").val();

                if (!request_no) {
                    request_no = null;
                }
                
                $.ajax({
                    type: "POST",
                    url: "<?= base_url('warehouse/issued_materials/getPoReceipt') ?>",
                    data: "receipt_id=" + receipt_id + "&request_no=" + request_no,
                    dataType: "json",
                    success: function(json) {
                        if (json.total > 0) {
                            console.log('Json OKE: ', json);
                            var row = json.rows;
                            for (let i = 0; i < json.total; i++) {
                                creteLabel(request_no, receipt_id, row[i].item_rm_id, row[i].qty, row[i].eq_item_rm_id, row[i].qty_po);
                            }

                            $('#dg').datagrid({
                                url: '<?= base_url('warehouse/issued_materials/datatables?request_no=') ?>' + window.btoa(request_no),
                                rownumbers: true
                            });
                            
                        } else {
                            serialNotFound.play();
                            toastr.warning("Label not found!");
                            $("#receipt_id").val('');
                        }
                    }
                });
            }
        });
    });
    function creteLabel(request_no, receipt_id, item_rm_id, qty, eq_item_rm_id, qty_po) {
        console.log('Last : ', request_no, receipt_id, item_rm_id, qty, eq_item_rm_id, qty_po);

        // Last :  SH-250625-0001 POR-20250312-0003-0010131 RMFLNA-0009 25.00 RMFLNA-0001 25.00


                                $.ajax({
                                    type: "POST",
                                    url: "<?= base_url('warehouse/issued_materials/create_label') ?>",
                                    data: "request_no=" + request_no +
                                        "&label_no=" + receipt_id +
                                        "&item_rm_id=" + item_rm_id +
                                        "&qty=" + qty +
                                        "&eq_item_rm_id=" + eq_item_rm_id +
                                        "&qty_po=" + qty_po,
                                    dataType: "json",
                                    success: function(result) {
                                        if (result.theme == "success") {
                                            serialSuccess.play();
                                            toastr.success(result.message, result.title);
                                            $("#receipt_id").val('');
                                            $('#receipt_id').focus();
                                        } else {
                                            if (result.title == "Not Scanned In" || result.title == "Not Registered") {
                                                serialNotFound.play();
                                            // } else if (result.title == "More Than Qty") {
                                            //     // moreThanQty.play();
                                            } else if(result.title == "FIFO Violation") {
                                                FIFOValidation.play();
                                            }else if (result.title == "Label Already Scan"){
                                                labelAlreadyScan.play();
                                            } else {
                                                serialDuplicate.play();
                                            }
                                            toastr.error(result.message, result.title);
                                            $("#receipt_id").val('');
                                            $('#receipt_id').focus();
                                        }
                                    }
                                });
    }
    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }
    function numberStyle(value, row, index) {
        if (value < 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
    function numberStockStyle(value, row, index) {
        if (value == 0) {
            return 'background-color:#FFDD77;';
        } else if(value < 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }
    function rowNumberFormatter(value, row, index) {
        return index + 1;
    }
    // function stockFormatter(value, row, index) {
    //     const num = parseFloat(value);
    //     const formatted = numberformat(value);

    //     if (num === 0) {
    //         return `<a href="javascript:void(0);" style="color: #000; text-decoration: none;" onclick="showEquivalents('${row.item_rm_id}', '${row.item_rm_name}', ${row.qty_act})">${formatted}</a>`;
    //     }
    //     return formatted;
    // }

    // function showEquivalents(item_rm_id, item_rm_name, need) {
    //     $('#item_master_name').text('-');
    //     $('#item_master_need').text('0,00');

    //     const formattedNeed = parseFloat(need).toLocaleString('id-ID', {
    //         minimumFractionDigits: 2,
    //         maximumFractionDigits: 2
    //     });

    //     $('#item_master_name').text(item_rm_name);
    //     $('#item_master_need').text(formattedNeed);

    //     globalNeed = parseFloat(need);
    //     globalSelectedRow = $('#dg').datagrid('getSelected'); 

    //     // console.log('RM : ', item_rm_id);

    //     $('#dlg-equivalent').dialog('open');
    //     $('#table-equivalent').datagrid({
    //         url: '<?= base_url('warehouse/issued_materials/get_equivalents?item_rm_id=') ?>' + item_rm_id,
    //         method: 'get',
    //         pagination: false,
    //         fitColumns: true,
    //         singleSelect: true
    //     });
    // }


    // function equivalentSelectFormatter(value, row, index) {
    //     const balWip = parseFloat(row.bal_wip || 0);
    //     const stock = parseFloat(row.stock || 0);
    //     const totalAvailable = balWip + stock;

    //     const isDisabled = totalAvailable < globalNeed;
    //     const disabledAttr = isDisabled ? 'disabled' : '';

    //     const selectedRow = $('#dg').datagrid('getSelected'); // baris utama

    //     if (!selectedRow) return ''; // fallback

    //     console.log('Row : ', row);

    //     const {
    //         request_no,
    //         item_rm_id,
    //         qty_act
    //     } = selectedRow;

    //     console.log('Selected : ', selectedRow);

    //     const qtyPo = qty_act || 0;
    //     console.log('equivalentId', row.id);
    //     return `
    //         <button 
    //             class="easyui-linkbutton" 
    //             ${disabledAttr} 
    //             onclick="confirmReplacePart(
    //                 '${request_no}', 
    //                 '${row.id}', 
    //                 '${item_rm_id}',
    //                 '${qtyPo}'
    //             )">Pilih</button>`;
    // }

    // function equivalentSelectFormatter(value, row, index) {
    //     const balWip = parseFloat(row.bal_wip || 0);
    //     const stock = parseFloat(row.stock || 0);
    //     const totalAvailable = balWip + stock;

    //     const isDisabled = totalAvailable < globalNeed;
    //     const disabledAttr = isDisabled ? 'disabled' : '';

    //     const selectedRow = globalSelectedRow;

    //     if (!selectedRow) return '';

    //     console.log(selectedRow);

    //     const {
    //         request_no,
    //         item_rm_id,
    //         qty_act
    //     } = selectedRow;

    //     return `
    //         <button 
    //             class="easyui-linkbutton" 
    //             ${disabledAttr} 
    //             onclick="confirmReplacePart(
    //                 '${request_no}', 
    //                 '${row.id}', 
    //                 '${item_rm_id}',
    //                 '${qty_act}'
    //             )">Pilih</button>`;
    // }

    function stockFormatter(value, row, index) {
        const num = parseFloat(value);
        const formatted = numberformat(value);

        if (num === 0) {
            return `<a href="javascript:void(0);" style="color: #000; text-decoration: none;" 
                onclick='showEquivalents(${JSON.stringify(row)})'>${formatted}</a>`;
        }
        return formatted;
    }

    function showEquivalents(rowData) {
        const {
            item_rm_id,
            item_rm_name,
            qty_act
        } = rowData;

        $('#item_master_name').text(item_rm_name);
        $('#item_master_need').text(parseFloat(qty_act).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        globalNeed = parseFloat(qty_act);
        globalSelectedRow = rowData;

        $('#dlg-equivalent').dialog('open');
        $('#table-equivalent').datagrid({
            url: '<?= base_url('warehouse/issued_materials/get_equivalents?item_rm_id=') ?>' + item_rm_id,
            method: 'get',
            pagination: false,
            fitColumns: true,
            singleSelect: true,
            onLoadSuccess: function(data) {
                const dg = $('#table-equivalent');
                const panel = dg.datagrid('getPanel');

                if (!data.rows || data.rows.length === 0) {
                    const colCount = dg.datagrid('getColumnFields').length;

                    dg.datagrid('loadData', {
                        total: 1,
                        rows: [{
                            ck: '',
                            item_rm_name: '',
                            bal_wip: '',
                            stock: '',
                            action: ''
                        }]
                    });

                    const body = panel.find('.datagrid-body');
                    const firstRow = body.find('tr.datagrid-row');

                    firstRow.find('td[field="ck"]').css({
                        'border': 'none',
                        'background': 'transparent',
                        'width': '700px'
                    }).off();

                    panel.find('.datagrid-body td[field="ck"]').hover(function(e) {
                        e.stopPropagation();
                    });

                    firstRow.find('td').not(':first').remove();

                    firstRow.find('td')
                        .attr('colspan', colCount)
                        .css({
                            'text-align': 'center',
                            'font-style': 'italic',
                            'color': '#666',
                            'font-weight': 'bold'
                        })
                        .find('div')
                        .css('width', '100%')
                        .attr('style', 'width:100%; font-size: 14px !important;')
                        .text('This Item Has No Equivalent Part');

                    dg.datagrid('options').singleSelect = false;
                } else {
                    dg.datagrid('options').singleSelect = true;
                }
            }

        });
    }

    // function equivalentSelectFormatter(value, row, index) {
    //     console.log('Row 2 : ',row);

    //     const balWip = parseFloat(row.bal_wip || 0);
    //     const stock = parseFloat(row.stock || 0);
    //     const mpq = parseFloat(row.mpq || 0);
    //     const totalAvailable = balWip + stock;

    //     const allRows = $('#table-equivalent').datagrid('getRows') || [];
    //     const need = globalNeed;

    //     // Urutkan berdasarkan eq_priority
    //     const sorted = allRows.slice().sort((a, b) => a.eq_priority - b.eq_priority);

    //     let allowPriority = null;

    //     for (let r of sorted) {
    //         const bw = parseFloat(r.bal_wip || 0);
    //         const st = parseFloat(r.stock || 0);
    //         const total = bw + st;

    //         if (bw >= need && bw >= (r.mpq || 0)) {
    //             allowPriority = r.eq_priority;
    //             break;
    //         } else if (bw < need && total >= need) {
    //             allowPriority = -1;
    //             break;
    //         }
    //     }

    //     let isEnabled = false;
    //     if (allowPriority !== -1 && row.eq_priority === allowPriority && balWip >= need && balWip >= mpq) {
    //         isEnabled = true;
    //     }

    //     const disabledAttr = !isEnabled ? 'disabled' : '';

    //     const selectedRow = globalSelectedRow;
    //     if (!selectedRow) return '';

    //     const {
    //         request_no,
    //         item_rm_id,
    //         qty_act
    //     } = selectedRow;

    //     return `
    //         <button 
    //             class="check-btn ${isEnabled ? 'active' : 'inactive'}"
    //             ${isEnabled ? `
    //                 onclick="confirmReplacePart(
    //                     '${request_no}', 
    //                     '${row.id}', 
    //                     '${item_rm_id}',
    //                     '${qty_act}'
    //                 )"` : 'disabled'}
    //         >✅</button>`;
    // }


    function equivalentSelectFormatter(value, row, index) {
        console.log('Row 2 : ',row);

        const balWip = parseFloat(row.bal_wip || 0);
        const stock = parseFloat(row.stock || 0);
        const totalAvailable = balWip + stock;

        const allRows = $('#table-equivalent').datagrid('getRows') || [];
        const need = globalNeed;

        // Urutkan berdasarkan eq_priority
        const sorted = allRows.slice().sort((a, b) => a.eq_priority - b.eq_priority);

        let allowPriority = null;

        for (let r of sorted) {
            const bw = parseFloat(r.bal_wip || 0);
            const st = parseFloat(r.stock || 0);
            const total = bw + st;

            if (bw >= need) {
                allowPriority = r.eq_priority;
                break;
            } else if (bw < need && total >= need) {
                allowPriority = -1; // disable all
                break;
            }
        }

        let isEnabled = false;
        if (allowPriority !== -1 && row.eq_priority === allowPriority && balWip >= need) {
            isEnabled = true;
        }

        const disabledAttr = !isEnabled ? 'disabled' : '';

        const selectedRow = globalSelectedRow;
        if (!selectedRow) return '';

        const {
            request_no,
            item_rm_id,
            qty_act
        } = selectedRow;

        return `
            <button class="check-btn ${isEnabled ? 'active' : 'inactive'}" ${isEnabled ? `
                    onclick="confirmReplacePart(
                        '${request_no}', 
                        '${row.id}', 
                        '${item_rm_id}',
                        '${qty_act}'
                    )"` : 'disabled'}
            >✅</button>`;
    }

    // let isReplacingPart = false;

    function confirmReplacePart(requestNo, itemRmId, eqItemRmId, qtyPo) {
        console.log('Detail : ', requestNo, itemRmId, eqItemRmId, qtyPo);
        // Detail :  SH-250625-0001 RMFLNA-0009 RMFLNA-0001 14.80

        // if (isReplacingPart) {
        //     console.warn("Preventing duplicate request");
        //     return;
        // }

        $.messager.confirm('Warning',
            `Are you sure want to change this supply to part equivalent?`,
            function (r) {
                if (r) {
                    isReplacingPart = true; 
                    
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('warehouse/issued_materials/create_eq_part') ?>",
                        data: {
                            request_no: requestNo,
                            item_rm_id: itemRmId,
                            eq_item_rm_id: eqItemRmId,
                            qty_po: qtyPo,
                        },
                        dataType: "json",
                        success: function(result) {
                            if (result.theme === "success") {
                                serialSuccess.play();
                                toastr.success(result.message, result.title);
                                $('#dg').datagrid('reload');
                                $('#dlg-equivalent').dialog('close');
                                resetEquivalentDialogState();
                            } else {
                                toastr.error(result.message, result.title);
                            }
                        },
                        error: function () {
                            $.messager.alert('Error', 'Terjadi kesalahan saat mengganti part equivalent.', 'error');
                        }
                    });
                }
            }
        );
    }

    $('#dlg-equivalent').dialog({
        onClose: function() {
            resetEquivalentDialogState(); // reset semua state
        }
    });


    function resetEquivalentDialogState() {
        globalSelectedRow = null;
        globalNeed = 0;
        $('#item_master_name').text('-');
        $('#item_master_need').text('0,00');
        $('#table-equivalent').datagrid('loadData', { total: 0, rows: [] }); // Kosongkan datagrid
    }


</script>