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
            url: '<?= base_url('notifications/notification_data/supply_sheets/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
            columns: [
                [{ 
                    field: 'ck', 
                    checkbox: "true" 
                },{ 
                    field: 'request_no', 
                    width: 150, 
                    halign: 'center', 
                    title: "Request No" 
                },{ 
                    field: 'request_date', 
                    width: 150, 
                    halign: 'center', 
                    title: "Request Date" 
                },{ 
                    field: 'request_name', 
                    width: 150, 
                    halign: 'center', 
                    title: "Requester" 
                },{ 
                    field: 'period', 
                    width: 100, 
                    halign: 'center', 
                    title: "Period" 
                },{ 
                    field: 'wp', 
                    width: 100, 
                    halign: 'center', 
                    title: "WP" 
                },{ 
                    field: 'workorder', 
                    width: 150, 
                    halign: 'center', 
                    title: "Work Order" 
                },{ 
                    field: 'item_fg_number', 
                    width: 150, 
                    halign: 'center', 
                    title: "Product No" 
                },{ 
                    field: 'item_fg_name', 
                    width: 200, 
                    halign: 'center', 
                    title: "Product Name" 
                },{ 
                    field: 'item_rm_number', 
                    width: 150, 
                    halign: 'center', 
                    title: "Component No" 
                },{ 
                    field: 'item_rm_name', 
                    width: 200, 
                    halign: 'center', 
                    title: "Component Name" 
                },{ 
                    field: 'uom', 
                    width: 100, 
                    halign: 'center', 
                    title: "UoM" 
                },{ 
                    field: 'qty_req', 
                    width: 100, 
                    halign: 'center', 
                    title: "Qty Required",
                    formatter: numberformat
                },{ 
                    field: 'qty_act', 
                    width: 100, 
                    halign: 'center', 
                    title: "Qty Actual",
                    formatter: numberformat
                },{ 
                    field: 'qty_bal', 
                    width: 100, 
                    halign: 'center', 
                    title: "Qty Balance",
                    formatter: numberformat
                }]
            ],
        }).datagrid('enableFilter');
    });
</script>