<div id="f" class="easyui-accordion" style="width:100%;">

    <div title="Click this to hide the filter" data-options="selected:true" style="padding:8px; background:#F4F4F4;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Report Display</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto" required data-options="editable: false">
                        <option value="RECAP">RECAP</option>
                        <option value="DETAIL">DETAIL</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Check Date</span>
                    <input style="width:29.8%;" id="filter_date_from" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                    <input style="width:29.8%;" id="filter_date_to" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser" class="easyui-datebox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">NG Kind</span>
                    <input style="width:60%;" id="filter_ng_kind" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Source</span>
                    <input style="width:60%;" id="filter_source" class="easyui-combogrid" data-options="editable: false">
                </div>
                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>

        <?= $button ?>
    </div>

</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 500px; border: 0;"></iframe>
</div>

<script>
    $(function () {
        function updatePrintoutHeight() {
            if ($('.accordion-header-selected').length > 0) {
                $('#printout').css('height', '500px');
            } else {
                $('#printout').css('height', '95%');
            }
        }

        updatePrintoutHeight();
        setInterval(updatePrintoutHeight, 200);
    });
    
    function filter() {
        var filter_date_from    = $("#filter_date_from").datebox("getValue");
        var filter_date_to      = $("#filter_date_to").datebox("getValue");
        var filter_ng_kind      = $("#filter_ng_kind").combobox("getValue");
        var filter_item_fg      = $("#filter_item_fg").combobox("getValue");
        var filter_display      = $("#filter_display").combobox("getValue");
        var filter_source       = $("#filter_source").combogrid('getValue');

        var url = "?filter_date_from=" + window.btoa(filter_date_from) +
            "&filter_date_to=" + window.btoa(filter_date_to) +
            "&filter_ng_kind=" + window.btoa(filter_ng_kind) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_display=" + window.btoa(filter_display) +
            "&filter_source=" + window.btoa(filter_source);

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/report_visual_checker/print') ?>' + url);
    }

    function excel() {
        var filter_date_from    = $("#filter_date_from").datebox("getValue");
        var filter_date_to      = $("#filter_date_to").datebox("getValue");
        var filter_ng_kind      = $("#filter_ng_kind").combobox("getValue");
        var filter_item_fg      = $("#filter_item_fg").combobox("getValue");
        var filter_display      = $("#filter_display").combobox("getValue");
        var filter_source       = $("#filter_source").combogrid('getValue');

        var url = "?filter_date_from=" + window.btoa(filter_date_from) +
            "&filter_date_to=" + window.btoa(filter_date_to) +
            "&filter_ng_kind=" + window.btoa(filter_ng_kind) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_display=" + window.btoa(filter_display) +
            "&filter_source=" + window.btoa(filter_source);

        window.location.assign('<?= base_url('control/report_visual_checker/print/excel') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#filter_display').combobox({
                onChange: function (newValue, oldValue) {
                    toggleFilter(newValue);
                }
            });

            toggleFilter($('#filter_display').combobox('getValue'));

        $('#filter_ng_kind').combobox({
            url: '<?= base_url("master/master_ng/readByNames") ?>',
            panelWidth: 500,
            idField: 'code',
            textField: 'name',
            valueField: 'code',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select NG Kind",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            loadFilter: function(data){
                return Array.isArray(data) ? data : data.rows || [];
            },
            onShowPanel: function() {
                $(this).combobox('textbox').val('');

                $(this).combobox('reload');
            },
            onHidePanel: function() {
                var t = $(this).combobox('getText');
                var data = $(this).combobox('getData');

                var exists = data.some(row => row.name === t);

                if (!exists) {
                    $(this).combobox('clear');
                }
            }
        });


        $('#filter_item_fg').combogrid({
            url: '<?= base_url("master/item_fg/reads") ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'number',
            valueField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Product ID',
                    width: 150
                },{
                    field: 'number',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ]
        });

        $('#filter_source').combogrid({
            url: '<?= base_url('control/report_visual_checker/readSourceLists'); ?>',
            panelWidth: 440,
            idField: 'number',
            textField: 'name',
            valueField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Source",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],

            loadFilter: function(data) {
                let rows = Array.isArray(data) ? data : data.rows || [];

                let internal = {
                    id: 'INTERNAL',
                    number: 'INTERNAL',
                    name: 'Internal Finishing',
                    type: 'Internal'
                };

                rows.unshift(internal);

                return {
                    total: rows.length,
                    rows: rows
                };
            },

            columns: [[
                {field: 'type', title: 'Source Type', width: 150},
                {field: 'number', title: 'Source Code', width: 110},
                {field: 'name', title: 'Source Name', width: 180}
            ]],
        });

    });

    function toggleFilter(mode) {
        if (mode === 'RECAP') {
            // enable
            $('#filter_date_from').datebox('enable');
            $('#filter_date_to').datebox('enable');
            $('#filter_item_fg').combogrid('enable');

            // disable
            $('#filter_ng_kind').combobox('disable');
            $('#filter_source').combogrid('disable');

        } else if (mode === 'DETAIL') {
            // enable semua
            $('#filter_date_from').datebox('enable');
            $('#filter_date_to').datebox('enable');
            $('#filter_item_fg').combogrid('enable');
            $('#filter_ng_kind').combobox('enable');
            $('#filter_source').combogrid('enable');
        }
    }

    // FORMAT tahun-bulan-tanggal
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }

</script>