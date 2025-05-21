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
            url: '<?= base_url('notifications/notification_data/forecasts/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
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
					field: 'p_month',
					width: 80,
					align: 'center',
					title: "Month",
				}, {
					field: 'p_year',
					width: 80,
					align: 'center',
					title: "Year",
				}, {
					field: 'revision',
					width: 80,
					align: 'center',
					title: "Revision",
				}, {
					field: 'issued_date',
					width: 100,
					align: 'center',
					title: "Issued Date",
				}, {
					field: 'customer_name',
					width: 200,
					halign: 'center',
					title: "Customer",
				}, {
					field: 'item_fg_number',
					width: 150,
					halign: 'center',
					title: "Product No",
				}, {
					field: 'item_fg_name',
					width: 150,
					halign: 'center',
					title: "Product Name"
				}, {
					field: 'document_no',
					width: 100,
					halign: 'center',
					title: "Document No",
				}, {
					field: 'month_1',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M1",
				}, {
					field: 'month_2',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M2",
				}, {
					field: 'month_3',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M3",
				}, {
					field: 'month_4',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M4",
				}, {
					field: 'month_5',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M5",
				}, {
					field: 'month_6',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M6",
				}, {
					field: 'month_7',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M7",
				}, {
					field: 'month_8',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M8",
				}, {
					field: 'month_9',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M9",
				}, {
					field: 'month_10',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M10",
				}, {
					field: 'month_11',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M11",
				}, {
					field: 'month_12',
					width: 80,
					halign: 'center',
					align: 'right',
					formatter: numberformat,
					title: "M12",
				}]
			],
        }).datagrid('enableFilter');
    });
</script>