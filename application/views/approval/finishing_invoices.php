<style>
    .swal2-validation-message {
        background: #fff !important;
    }
</style>
<!-- TABLE DATAGRID HEADER -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'finishing_invoice_no',width:150">Invoice No</th>
            <th data-options="field:'finishing_invoice_date',width:100">Invoice Date</th>
            <th data-options="field:'vendor_name',width:180">Vendor / Subcont</th>
            <th data-options="field:'period_start',width:100">Period Start</th>
            <th data-options="field:'period_end',width:100">Period End</th>
            <th data-options="field:'total',width:120,align:'right'">Total</th>
            <th data-options="field:'biaya_fee',width:120,align:'right'">Biaya Fee</th>
            <th data-options="field:'grand_total',width:120,align:'right'">Grand Total</th>
            <th data-options="field:'created_by',width:100">Created By</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script type="text/javascript" src="<?= base_url('assets/easyui/datagrid-detailview.js') ?>"></script>

<script>
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
		$('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
	}

	function approve() {
		var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Approve Data',
                text: "Are you sure? You want to approve this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestApprove(rows.length, rows);
                    function requestApprove(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('approvals/approve') ?>',
                                data: {
                                    id: row.id,
									tablename: "<?= $table ?>"
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg').datagrid('reload');
                                        Swal.close();
                                        Swal.fire(
                                            'Approve Completed',
                                            'Approve Data has been completed, You cannot restore data that has been approved',
                                            'success'
                                        );
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                },
                            });
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
	}

    // function disapprove() {
	// 	var rows = $('#dg').datagrid('getSelections');
    //     if (rows.length > 0) {
    //         Swal.fire({
    //             title: 'Disapprove Data',
    //             text: "Are you sure? You want to disapprove this data!",
    //             icon: 'warning',
    //             showCancelButton: true,
    //             allowOutsideClick: false,
    //             allowEscapeKey: false,
    //             confirmButtonText: 'Yes, Dispprove it!'
    //         }).then((result) => {
    //             if (result.isConfirmed) {
    //                 Swal.fire({
    //                     title: 'Please Wait...',
    //                     showConfirmButton: false,
    //                     allowOutsideClick: false,
    //                     allowEscapeKey: false,
    //                     didOpen: () => {
    //                         Swal.showLoading();
    //                     },
    //                 });

    //                 requestApprove(rows.length, rows);
    //                 function requestApprove(total, json, number = 1, value = 0) {
    //                     if (value < 100) {
    //                         var row = json[number-1];
    //                         value = Math.floor((number / total) * 100);

    //                         $.ajax({
    //                             method: 'post',
    //                             url: '<?= base_url('approvals/disapprove') ?>',
    //                             data: {
    //                                 id: row.id,
	// 								tablename: "<?= $table ?>"
    //                             },
    //                             success: function(result) {
    //                                 var result = eval('(' + result + ')');
    //                                 requestApprove(total, json, number + 1, value);

    //                                 if (number == total) {
    //                                     $('#dg').datagrid('reload');
    //                                     Swal.close();
    //                                     Swal.fire(
    //                                         'Disapprove Completed',
    //                                         'Disapprove Data has been completed, You cannot restore data that has been disapproved',
    //                                         'success'
    //                                     );
    //                                 }
    //                             },
    //                             error: function(jqXHR, textStatus, errorThrown) {
    //                                 toastr.error(jqXHR.statusText);
    //                             },
    //                         });
    //                     }
    //                 }
    //             }
    //         });
    //     } else {
    //         toastr.info("Please select one of the data in the table first");
    //     }
	// }

    function disapprove() {
        var rows = $('#dg').datagrid('getSelections');

        if (rows.length > 0) {
            Swal.fire({
                title: 'Disapprove Data',
                text: "Are you sure? You want to disapprove this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Disapprove it!'
            }).then((result) => {

                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Disapprove Reason',
                        input: 'textarea',
                        inputLabel: 'Reason',
                        inputPlaceholder: 'Enter the reason for disapproval...',
                        inputAttributes: {
                            'aria-label': 'Disapprove reason'
                        },
                        showCancelButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value || !value.trim()) {
                                return 'Reason is required!';
                            }
                        }
                    }).then((reasonResult) => {

                        if (reasonResult.isConfirmed) {

                            var reason = reasonResult.value.trim();

                            Swal.fire({
                                title: 'Please Wait...',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            requestApprove(rows.length, rows, 1, 0, reason);
                        }
                    });
                }
            });

            function requestApprove(total, json, number = 1, value = 0, reason = '') {
                if (value < 100) {
                    var row = json[number - 1];
                    value = Math.floor((number / total) * 100);

                    $.ajax({
                        method: 'post',
                        url: '<?= base_url('approvals/disapprove') ?>',
                        data: {
                            id: row.id,
                            tablename: "<?= $table ?>",
                            reason: reason
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            requestApprove(
                                total,
                                json,
                                number + 1,
                                value,
                                reason
                            );

                            if (number == total) {
                                $('#dg').datagrid('reload');

                                Swal.close();

                                Swal.fire(
                                    'Disapprove Completed',
                                    'Disapprove Data has been completed, You cannot restore data that has been disapproved',
                                    'success'
                                );
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            toastr.error(jqXHR.statusText);
                        },
                    });
                }
            }

        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }


    function numberformat(value, row) {
		const formatter = new Intl.NumberFormat('id-ID');

		return "<b>" + formatter.format(value) + "</b>";
	}

    function Itemid(value, row) {
		return "'" + value;
	}

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('approvals/approvalFinishingInvoices/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
            view: detailview, // Mengaktifkan fitur subgrid
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                
                // PANGGIL FUNGSI DETAILS YANG ADA DI CONTROLLER TRANSAKSI
                // Encode ID master menggunakan btoa() agar sesuai dengan format base64_decode di controller PHP
                var encodedId = btoa(row.id);
                
                ddv.datagrid({
                    url: '<?= base_url('warehouse/finishing_invoices/datatableDetails?id=') ?>' + encodedId,
                    fitColumns: true,
                    singleSelect: true,
                    rownumbers: true,
                    loadMsg: 'Memuat data barang...',
                    height: 'auto',
                    columns: [[
                        {field: 'item_number', title: 'Part Number', width: 120},
                        {field: 'item_name', title: 'Part Name', width: 150},
                        {field: 'qty', title: 'Qty FG', width: 80, align: 'right'},
                        {field: 'price_fg', title: 'Price FG', width: 100, align: 'right'},
                        {field: 'qty_1', title: 'Qty Defect', width: 80, align: 'right'},
                        {field: 'price_defect', title: 'Price Defect', width: 100, align: 'right'},
                        {field: 'sub_total', title: 'Sub Total', width: 120, align: 'right'}
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
        }).datagrid('enableFilter');
    });
</script>