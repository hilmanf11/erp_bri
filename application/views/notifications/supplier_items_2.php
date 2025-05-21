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
            url: '<?= base_url('notifications/notification_data/supplier_items_2/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
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
					field: 'item_rm_id',
					title: 'Part ID',
					halign: 'center',
					width: 150
				}, {
					field: 'item_rm_number',
					title: 'Part No.',
					halign: 'center',
					width: 150
				}, {
					field: 'item_rm_name',
					title: 'Part Name',
					halign: 'center',
					width: 200
				}, {
					field: 'maker',
					title: 'Maker',
					halign: 'center',
					width: 150
				}, {
					field: 'item_supplier',
					title: 'Supplier Product',
					halign: 'center',
					width: 150
				}, {
					field: 'item_family_name',
					title: 'Product Family',
					halign: 'center',
					width: 150
				}, {
					field: 'supplier_name',
					title: 'Supplier Name',
					halign: 'center',
					width: 80
				}, {
					field: 'type',
					title: 'Type',
					halign: 'center',
					width: 80
				}, {
					field: 'currency',
					title: 'Currency',
					halign: 'center',
					width: 80
				}, {
					field: 'mpq',
					title: 'MPQ',
					halign: 'center',
					width: 80
				}, {
					field: 'moq',
					title: 'MOQ',
					halign: 'center',
					width: 80
				}, {
					field: 'share_order',
					title: 'Share Order %',
					halign: 'center',
					width: 100
				}, {
					field: 'leadtime',
					title: 'Leadtime',
					halign: 'center',
					width: 100
				}, {
					field: 'price',
					title: 'Price',
					halign: 'center',
					align: 'right',
					width: 100
				}, {
					field: 'btn',
					title: 'History',
					halign: 'center',
					width: 80
				}, {
					field: 'valid_date',
					title: 'Valid Date',
					halign: 'center',
					width: 100
				}, {
					field: 'safety_stock',
					title: 'Safet Stock %',
					width: 100,
					halign: 'center',
				}, {
					field: 'calculate',
					title: 'Calculate MPQ',
					width: 100,
					halign: 'center',
				}]
			],
        }).datagrid('enableFilter');
    });
</script>