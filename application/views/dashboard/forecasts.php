<head>
    <!-- Pastikan untuk memasukkan link ke Highcharts sebelum skrip lain -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <!-- Jika Anda membutuhkan modul tambahan seperti exporting, tambahkan di sini -->
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>

<body>
    <div class="row m-2">
        <div class="col-lg-6">
            <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox">
                    <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox" multiple="true">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer Name</span>
                    <input style="width:60%;" id="filter_customer_name" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Display By</span>
                    <input style="width:60%;" id="filter_display_by" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <!-- Tombol Reload Halaman -->
                    <a href="javascript:;" class="easyui-linkbutton" onclick="reloadPage()"><i class="fa fa-refresh"></i> Reload</a>
                </div>
            </fieldset>
        </div>
        <div class="col-lg-10">
            <div class="row">
            </div>
        </div>
        <div class="col-lg-5">
            <div class="row">
                <div class="col-lg-15 mt-3">
                    <div class="card">
                        <div class="card-header">
                            Chart Forecast
                        </div>
                        <div class="card-body">
                            <div id="chartForecast"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    function filter() {
        var filter_period_year = $("#filter_period_year").combobox("getValue");
        var filter_period_month = $("#filter_period_month").combobox("getValues"); // Bisa multiple month
        var filter_customer_name = $("#filter_customer_name").combogrid("getValue");
        var filter_item_fg = $("#filter_item_fg").combogrid("getValue");
        var filter_display_by = $("#filter_display_by").combobox("getValue");

        // Jika tidak ada bulan yang dipilih, pilih semua bulan
        if (filter_period_month.length === 0) {
            filter_period_month = $("#filter_period_month").combobox("getData").map(function(item) {
                return item.id;
            });
        }

        if (filter_period_year === "") {
            toastr.warning("Please select Period Year.");
            return;
        }

        if (filter_display_by === "" || filter_customer_name === "") {
            toastr.warning("Please select Customer Name and Display By");
            return;
        }

        // Tentukan URL berdasarkan pilihan filter_display_by
        var url = filter_display_by === "qty" ?
            "<?= base_url('dashboard/forecasts/chartByQty') ?>" :
            "<?= base_url('dashboard/forecasts/chartByAmount') ?>";

        $.ajax({
            type: "post",
            url: url,
            data: {
                filter_period_year: window.btoa(filter_period_year),
                filter_period_month: window.btoa(filter_period_month.join(",")), // Gabungkan bulan
                filter_customer_name: filter_customer_name,
                filter_item_fg: filter_item_fg,
                filter_display_by: filter_display_by
            },
            dataType: "json",
            success: function(response) {
                var chartOptions = {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: response.customer_name ? response.customer_name : 'Customer Forecast'
                    },
                    xAxis: {
                        categories: response.categories
                    },
                    series: response.data.map(function(series) {
                        return {
                            name: series.name,
                            data: series.data
                        };
                    }),
                    yAxis: {
                        title: {
                            text: response.filter_display_by === "qty" ? 'Quantity' : 'Amount'
                        },
                        labels: {
                            formatter: function() {
                                return response.filter_display_by === "qty" ?
                                    Highcharts.numberFormat(this.value, 0, '.', ',') :
                                    'Rp' + Highcharts.numberFormat(this.value, 0, '.', ',');
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        valuePrefix: response.filter_display_by === "amount" ? 'Rp' : '',
                        valueDecimals: 0
                    }
                };

                Highcharts.chart('chartForecast', chartOptions);
            }
        });
    }

    function reloadPage() {
        location.reload(); // Fungsi untuk memuat ulang halaman
    }

    $('#filter_period_year').combobox({
        url: '<?= base_url('dashboard/forecasts/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years'
    });

    $('#filter_period_month').combobox({
        url: '<?= base_url('dashboard/forecasts/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose All Months',
        onLoadSuccess: function() {
            $(this).combobox('clear'); // Kosongkan saat data berhasil dimuat
        }
    });

    $('#filter_item_fg').combogrid({
        url: '<?= base_url("master/item_fg/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'number',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Product No.",
        columns: [
            [{
                    field: 'number',
                    title: 'Product No',
                    width: 200
                },
                {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }
            ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }]
    });

    $('#filter_customer_name').combogrid({
        url: '<?= base_url("master/customers/reads") ?>',
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Customer",
        columns: [
            [{
                    field: 'number',
                    title: 'Code',
                    width: 50
                },
                {
                    field: 'name',
                    title: 'Customer Name',
                    width: 100
                }
            ]
        ],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }]
    });

    $('#filter_display_by').combobox({
        data: [{
                value: 'qty',
                text: 'Qty'
            },
            {
                value: 'amount',
                text: 'Amount'
            }
        ],
        valueField: 'value',
        textField: 'text',
        prompt: 'Choose Display By'
    });
</script>