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

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('notifications/notification_data/purchase_requests/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
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
					field: 'id',
					hidden: true,
					width: 150,
					halign: 'center',
					title: "ID",
				}, {
					field: 'request_no',
					title: 'Request No',
					halign: 'center',
					width: 150
				}, {
					field: 'request_date',
					title: 'Request Date',
					halign: 'center',
					width: 150
				}, {
					field: 'expected_date',
					title: 'Expected Date',
					halign: 'center',
					width: 150
				}, {
					field: 'request_name',
					title: 'Request Name',
					halign: 'center',
					width: 150
				}, {
					field: 'item_number',
					title: 'Product No',
					halign: 'center',
					width: 150
				}, {
					field: 'item_name',
					title: 'Product Name',
					halign: 'center',
					width: 200
				}, {
					field: 'category_name',
					title: 'Product Family',
					halign: 'center',
					width: 80
				}, {
					field: 'uom',
					title: 'UoM',
					halign: 'center',
					width: 80
				}, {
					field: 'qty',
					title: 'Qty',
					halign: 'center',
					width: 80
				}, {
					field: 'remarks',
					title: 'Remarks',
					halign: 'center',
					width: 80
				}, {
					field: 'po_no',
					title: 'PO No',
					halign: 'center',
					width: 80
				}]
			],
        }).datagrid('enableFilter');
    });
</script>