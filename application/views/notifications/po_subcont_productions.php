<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" id="approveall" class="easyui-linkbutton" data-options="plain:true" onclick="deleteAll()"><i class="fa fa-check"></i> Delete</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
		$('#dg').datagrid('toExcel', "notifications_<?= $table ?>.xls");
	}

	function deleteAll() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('notifications/delete') ?>',
                            data: {
                                id: row.id_notification
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                            complete: function(data) {
                                $('#dg').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            $.messager.alert('Information', 'Please select one of the data in the table first!', 'info');
        }
    }

    function numberformat(value, row) {
		const formatter = new Intl.NumberFormat('id-ID');

		return "<b>" + formatter.format(value) + "</b>";
	}

	function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function printPo(po_no) {

		var printUrl = "<?= base_url('purchase/Po_subcont_productions/print_po/') ?>" + window.btoa(po_no);
		window.open(printUrl, "_blank");
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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('notifications/notification_data/po_subcont_productions/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
            columns: [
				[{
					field: 'ck',
					checkbox: "true",
				},{
                    field: 'print',
                    width: 80,
                    align: 'center',
                    title: "Print",
                    formatter: function(value, row) {
                        var print = "printPo('" + row.po_no + "')";
                        return '<a class="btn btn-primary w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i></a>';
                    }
                },{
                    field: 'preview',
                    width: 80,
                    align: 'center',
                    title: "Preview",
                    formatter: function(value, row) {
                        var print = "previewPo('" + row.po_no + "')";
                        return '<a class="btn btn-warning w-100" onClick="' + print + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i></a>';
                    }
                },{
					field: 'po_no',
					width: 180,
					halign: 'center',
					title: "PO NO",
				},{
					field: 'po_date',
					width: 100,
					align: 'center',
					title: "PO Date",
				},{
					field: 'due_date',
					width: 100,
					align: 'center',
					title: "Due Date",
				},{
					field: 'subcont_name',
					width: 250,
					halign: 'center',
					title: "Supplier Name"
				}, {
					field: 'currency',
					width: 80,
					align: 'center',
					title: "Currency",
				}, {
					field: 'total_amount',
					width: 100,
					halign: 'center',
					align: 'right',
					title: "Total Amount",
					formatter: numberformatPrice,
				}, {
                    field: 'notes',
                    width: 200,
                    halign: 'center',
                    title: "Notes",
				}, {
                    field: 'revision',
                    width: 150,
                    halign: 'center',
                    title: "Revision",
				}, {
                    field: 'order_type',
                    width: 120,
                    halign: 'center',
                    title: "Order Type",
				}]
			],
        }).datagrid('enableFilter');
    });
</script>