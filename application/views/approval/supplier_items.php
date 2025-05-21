<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'id',width:150,halign:'center',hidden:true">ID</th>
            <th data-options="field:'item_rm_id',width:200,halign:'center',hidden:true">Part No</th>
            <th data-options="field:'item_rm_number',width:80,halign:'center',align:'right'">Part No</th>
            <th data-options="field:'item_rm_name',width:100,align:'center'">Part Name</th>
            <th data-options="field:'maker',width:80,halign:'center',align:'right'">Maker</th>
            <th data-options="field:'item_supplier',width:100,align:'center'">Supplier Product</th>
            <th data-options="field:'item_family_name',width:100,align:'center'">Product Family</th>
            <th data-options="field:'supplier_name',width:100,align:'center'">Supplier Name</th>
            <th data-options="field:'type',width:100,align:'center'">Type</th>
            <th data-options="field:'currency',width:100,align:'center'">Currency</th>
            <th data-options="field:'mpq',width:100,align:'center'">MPQ</th>
            <th data-options="field:'moq',width:100,align:'center'">MOQ</th>
            <th data-options="field:'share_order',width:100,align:'center'">Share Order</th>
            <th data-options="field:'leadtime',width:100,align:'center'">Leadtime</th>
            <th data-options="field:'price',width:100,align:'center'">Price</th>
            <th data-options="field:'btn',width:100,align:'center'">History</th>
            <th data-options="field:'valid_date',width:100,align:'center'">Valid Date</th>
            <th data-options="field:'safety_stock',width:100,align:'center'">Safet Stock</th>
            <th data-options="field:'calculate',width:100,align:'center'">Calculate MPQ</th>
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
            url: '<?= base_url('approvals/approvalSupplierItems/') ?>' + "<?= base64_encode($approved_to) ?>" + "/" + "<?= base64_encode($approved_by) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
        }).datagrid('enableFilter');
    });
</script>