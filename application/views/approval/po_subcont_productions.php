<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'print',width:80,align:'center',formatter:printFormatter">Print</th>
            <th data-options="field:'preview',width:80,align:'center',formatter:previewFormatter">Preview</th>
            <th data-options="field:'po_no',width:200">PO No</th>
            <th data-options="field:'po_date',width:100">PO Date</th>
            <th data-options="field:'due_date',width:100">Due Date</th>
            <th data-options="field:'subcont_name',width:200">Supplier Name</th>
            <th data-options="field:'currency',width:80">Currency</th>
            <th data-options="field:'total_amount',width:120,formatter: numberformatPrice">Total Amount</th>
            <th data-options="field:'notes',width:200">Notes</th>
            <th data-options="field:'revision',width:150">Revision</th>
            <th data-options="field:'order_type',width:120">Order Type</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Approve</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    function printFormatter(value, row) {
        var print = "printPo('" + row.po_no + "')";
        return '<a class="btn btn-primary w-100" onclick="' + print + '" style="pointer-events:visible;opacity:1;"><i class="fa fa-print"></i></a>';
    }

    function printPo(po_no) {
		var printUrl = "<?= base_url('purchase/Po_subcont_productions/print_po/') ?>" + window.btoa(po_no) + "/print";

		window.open(printUrl, "_blank");
    }

    function previewFormatter(value, row){
        return `
            <a href="javascript:void(0)"
            class="btn btn-warning w-100" style="pointer-events:visible;opacity:1;" onclick="previewPo('${row.po_no}')" title="Preview">
                <i class="fa fa-eye"></i>
            </a>
        `;
    }

    function previewPo(po_no){

        var url = "<?= base_url('purchase/Po_subcont_productions/print_po/') ?>"
                + window.btoa(po_no)
                + "/preview";

        window.parent.$('<div/>').dialog({
            title: 'Preview Purchase Order',
            width: '60%',
            height: '90%',
            modal: true,
            maximizable: true,
            content: '<iframe src="' + url + '" style="width:100%;height:99%;border:none;"></iframe>',
            onClose: function () {
                $(this).dialog('destroy');
            }
        });
    }

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
                confirmButtonText: 'Yes, Dispprove it!'
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
                                url: '<?= base_url('approvals/disapprove') ?>',
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
                }
            });
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
            url: '<?= base_url('approvals/approvalPoSubcontProductions/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
        }).datagrid('enableFilter');
    });
</script>