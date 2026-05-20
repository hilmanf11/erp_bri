<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar"></table>

<script>

    function numberFormatField(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        
        $('#dg').datagrid({
            url: '<?= base_url('notifications/notification_data/delivery_to_subconts_notif/') ?>' + "<?= base64_encode($user) ?>" + "/" + "<?= base64_encode($name) ?>",
            pagination: false,
            singleSelect: false,
            clientPaging: false,
            rownumbers: true,
            fit: true,
            columns: [
                [{
                    field: 'ck',
                    checkbox: true,
                }, {
                    field: 'delivery_note_no',
                    width: 150,
                    halign: 'center',
                    align: 'left',
                    title: "Delivery Note No"
                }, {
                    field: 'delivery_date',
                    width: 150,
                    halign: 'center',
                    align: 'left',
                    title: "Delivery Date"
                }, {
                    field: 'target_date',
                    width: 150,
                    halign: 'center',
                    align: 'left',
                    title: "Target Date "
                }, {
                    field: 'item_fg_id',
                    width: 150,
                    halign: 'center',
                    align: 'left',
                    title: "Product ID"
                }, {
                    field: 'item_fg_number',
                    width: 200,
                    halign: 'center',
                    align: 'left',
                    title: "Product No"
                }, {
                    field: 'item_fg_name',
                    width: 200,
                    halign: 'center',
                    align: 'left',
                    title: "Product Name"
                }, {
                    field: 'destination_name',
                    width: 150,
                    halign: 'center',
                    align: 'left',
                    title: "Destination Name"
                }, {
                    field: 'prod_date',
                    width: 150,
                    halign: 'center',
                    align: 'left',
                    title: "Production Date"
                }, {
                    field: 'qty_delivery',
                    width: 150,
                    halign: 'center',
                    align: 'right',
                    title: "Qty Delivery",
                    formatter: numberFormatField
                }, {
                    field: 'qty_incoming',
                    width: 150,
                    halign: 'center',
                    align: 'right',
                    title: "Qty Incoming",
                    formatter: numberFormatField
                }, {
                    field: 'qty_outstanding',
                    width: 150,
                    halign: 'center',
                    align: 'right',
                    title: "Qty Outstanding",
                    formatter: numberFormatField
                }, {
                    field: 'uom',
                    width: 100,
                    halign: 'center',
                    align: 'center',
                    title: "UOM"
                }]
            ]
        }).datagrid('enableFilter');
    });
</script>