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
            url: '<?= base_url('notifications/notification_data/suppliers/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
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
					field: 'number',
					width: 150,
					align: 'center',
					title: "Cust. Number",
				}, {
					field: 'name',
					width: 100,
					align: 'center',
					title: "Cust. Name",
				}, {
					field: 'type',
					width: 150,
					align: 'center',
					title: "Type",
				}, {
					field: 'address',
					width: 100,
					align: 'center',
					title: "Address",
				}, {
					field: 'contact_person',
					width: 150,
					halign: 'center',
					title: "Contact <br>Person",
				}, {
					field: 'telp',
					width: 80,
					halign: 'center',
					title: "Telp",
				}, {
					field: 'fax',
					width: 150,
					halign: 'center',
					title: "Fax"
				}, {
					field: 'email',
					width: 80,
					halign: 'center',
					title: "Email",
				}, {
					field: 'website',
					width: 80,
					halign: 'center',
					title: "Website",
				}, {
					field: 'currency',
					width: 80,
					halign: 'center',
					title: "Currency",
				}, {
					field: 'payment_terms',
					width: 80,
					halign: 'center',
					title: "Payment Terms",
				}, {
					field: 'incoterm',
					width: 80,
					halign: 'center',
					title: "Incoterm",
				}, {
					field: 'vat',
					width: 80,
					halign: 'center',
					title: "Vat",
				}, {
					field: 'vat_status',
					width: 80,
					halign: 'center',
					title: "Vat Status",
				}, {
					field: 'tax',
					width: 80,
					halign: 'center',
					title: "Tax",
				}, {
					field: 'bank_account',
					width: 80,
					halign: 'center',
					title: "Bank Account",
				}, {
					field: 'bank_name',
					width: 80,
					halign: 'center',
					title: "Bank Name",
				}, {
					field: 'status',
					width: 80,
					halign: 'center',
					title: "Status",
				},]
			],
        }).datagrid('enableFilter');
    });
</script>