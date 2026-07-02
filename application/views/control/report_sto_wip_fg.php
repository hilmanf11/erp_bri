<div id="f" class="easyui-accordion" style="width:100%;">

    <div title="Click this to hide the filter" data-options="selected:true" style="padding:8px; background:#F4F4F4;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="filter_period_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="editable:false" required>
                    <input style="width:30%;" id="filter_period_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="editable:false" required>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Location</span>
                    <input style="width:60%;" id="filter_location" name="filter_location" class="easyui-combogrid" data-options="editable:false" required>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Location Name</span>
                    <input style="width:60%;" id="filter_location_name" name="location_name" class="easyui-textbox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Location Code</span>
                    <input style="width:60%;" id="filter_location_code" name="location_code" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">STO Doc No</span>
                    <input style="width:60%;" id="filter_doc_no" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Label Type</span>
                    <input style="width:60%;" name="filter_label_type"  id="filter_label_type" class="easyui-combogrid" data-options="editable:false">
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_fg" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Deviation</span>
                    <input style="width:60%;" id="filter_deviation" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Report Display</span>
                    <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto"
                        data-options="editable:false">
                        <option value="RECAP">RECAP</option>
                        <option value="DETAIL">DETAIL</option>
                    </select>
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
        var filter_period_month = $("#filter_period_month").combobox("getValue");
        var filter_period_year  = $("#filter_period_year").combobox("getValue");
        var filter_location     = $("#filter_location_code").textbox("getValue");
        var filter_doc_no       = $("#filter_doc_no").combobox("getValue");
        var filter_label_type   = $("#filter_label_type").combogrid('getValue');
        var filter_item_fg      = $("#filter_item_fg").combogrid("getValue");
        var filter_deviation    = $("#filter_deviation").combobox("getValue");
        var filter_display      = $("#filter_display").combobox('getValue');
        var filter_location_name    = $("#filter_location_name").textbox("getValue");

        if (!filter_period_month) {
            toastr.warning('Period Month is required!');
            $('#filter_location').combobox('textbox').focus();
            return false;
        }

        if (!filter_period_year) {
            toastr.warning('Period Year is required!');
            $('#filter_location').combobox('textbox').focus();
            return false;
        }

        if (!filter_location) {
            toastr.warning('Location is required!');
            $('#filter_location').combogrid('showPanel');
            $('#filter_location').combogrid('textbox').focus();
            return false;
        }

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_location=" + window.btoa(filter_location) +
            "&filter_doc_no=" + window.btoa(filter_doc_no) +
            "&filter_label_type=" + window.btoa(filter_label_type) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_deviation=" + window.btoa(filter_deviation) +
            "&filter_display=" + window.btoa(filter_display) +
            "&filter_location_name=" + window.btoa(filter_location_name);

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('control/report_sto_wip_fg/print') ?>' + url);
    }

    function excel() {
        var filter_period_month = $("#filter_period_month").combobox("getValue");
        var filter_period_year  = $("#filter_period_year").combobox("getValue");
        var filter_location     = $("#filter_location_code").textbox("getValue");
        var filter_doc_no       = $("#filter_doc_no").combobox("getValue");
        var filter_label_type   = $("#filter_label_type").combogrid('getValue');
        var filter_item_fg      = $("#filter_item_fg").combogrid("getValue");
        var filter_deviation    = $("#filter_deviation").combobox("getValue");
        var filter_display      = $("#filter_display").combobox('getValue');
        var filter_location_name    = $("#filter_location_name").textbox("getValue");

        if (!filter_period_month) {
            toastr.warning('Period Month is required!');
            $('#filter_location').combobox('textbox').focus();
            return false;
        }

        if (!filter_period_year) {
            toastr.warning('Period Year is required!');
            $('#filter_location').combobox('textbox').focus();
            return false;
        }

        if (!filter_location) {
            toastr.warning('Location is required!');
            $('#filter_location').combogrid('showPanel');
            $('#filter_location').combogrid('textbox').focus();
            return false;
        }

        var url = "?filter_period_month=" + window.btoa(filter_period_month) +
            "&filter_period_year=" + window.btoa(filter_period_year) +
            "&filter_location=" + window.btoa(filter_location) +
            "&filter_doc_no=" + window.btoa(filter_doc_no) +
            "&filter_label_type=" + window.btoa(filter_label_type) +
            "&filter_item_fg=" + window.btoa(filter_item_fg) +
            "&filter_deviation=" + window.btoa(filter_deviation) +
            "&filter_display=" + window.btoa(filter_display) +
            "&filter_location_name=" + window.btoa(filter_location_name);

        window.location.assign('<?= base_url('control/report_sto_wip_fg/print/excel') ?>' + url);
    }

    function pdf() {
        var filter_period_month = $("#filter_period_month").combobox("getValue");
        var filter_period_year  = $("#filter_period_year").combobox("getValue");
        var filter_location     = $("#filter_location_code").textbox("getValue");

        if (!filter_period_month) {
            toastr.warning('Period Month is required!');
            $('#filter_location').combobox('textbox').focus();
            return false;
        }

        if (!filter_period_year) {
            toastr.warning('Period Year is required!');
            $('#filter_location').combobox('textbox').focus();
            return false;
        }

        if (!filter_location) {
            toastr.warning('Location is required!');
            $('#filter_location').combogrid('showPanel');
            $('#filter_location').combogrid('textbox').focus();
            return false;
        }

        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    function reloadDocNoCombo() {
        var filter_period_month = $("#filter_period_month").combobox("getValue");
        var filter_period_year  = $("#filter_period_year").combobox("getValue");
        var filter_location     = $("#filter_location_code").textbox("getValue");

        var url = '<?= base_url('control/report_sto_wip_fg/readDocNo'); ?>'
                + '?filter_period_month=' + encodeURIComponent(filter_period_month)
                + '&filter_period_year=' + encodeURIComponent(filter_period_year)
                + '&filter_location=' + encodeURIComponent(filter_location);

        $('#filter_doc_no').combobox('reload', url);
    }

    $(function() {

        setDisplayBehaviour();

        $('#filter_doc_no').combobox({
            valueField: 'doc_no',
            textField: 'doc_no',
            prompt: 'Choose All',
            editable: true,
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        reloadDocNoCombo();

    });

    $('#filter_display').combobox({
        onChange: function() {
            setDisplayBehaviour();
        }
    });

    $('#filter_period_month').combobox({
        onChange: function(newValue, oldValue) {
            reloadDocNoCombo();
        }
    });

    $('#filter_period_year').combobox({
        onChange: function(newValue, oldValue) {
            reloadDocNoCombo();
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

    $('#filter_location').combogrid({
        url: '<?= base_url('control/report_sto_wip_fg/readLocations'); ?>',
        panelWidth: 440,
        idField: 'code',
        textField: 'name',
        valueField: 'code',
        mode: 'remote',
        fitColumns: true,
        prompt: "Choose Location",
        columns: [[
            {field: 'type', title: 'Source Type', width: 150},
            {field: 'code', title: 'Source Code', width: 110},
            {field: 'name', title: 'Source Name', width: 180}
        ]],
        onSelect: function(index, row) {
            $('#filter_location_code').textbox('setValue', row.code);
            $('#filter_location_name').textbox('setValue', row.name);

            $('#filter_label_type').combogrid('clear');
            $('#filter_label_type').combogrid('grid').datagrid('options').url =
                '<?= base_url("control/report_sto_wip_fg/readLabelTypes") ?>?location=' + row.code;

            $('#filter_label_type').combogrid('grid').datagrid('reload');

            reloadDocNoCombo();
        }
    });

    $('#filter_label_type').combogrid({
        url: '<?= base_url("control/report_sto_wip_fg/readLabelTypes") ?>',
        idField: 'name',
        textField: 'name',
        panelWidth: 600,
        fitColumns: true,
        prompt: "Choose All",
        columns:[[
            {field:'code',title:'TYPE CODE',width:100},
            {field:'name',title:'TYPE NAME',width:150},
            {field:'description',title:'DESCRIPTION',width:350}
        ]],
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
    });


    $('#filter_period_month').combobox({
        url: '<?= base_url('control/sto_wip_fg/readPeriod/month'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Months',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_period_year').combobox({
        url: '<?= base_url('control/sto_wip_fg/readPeriod/year'); ?>',
        valueField: 'id',
        textField: 'name',
        prompt: 'Choose Years',
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
    });

    $('#filter_deviation').combobox({
        valueField: 'id',
        textField: 'text',
        panelHeight: 'auto',
        prompt: 'Choose All',
        editable: false,
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combobox('clear').combobox('textbox').focus();
            }
        }],
        data: [
            { id: 'plus',  text: 'Deviation +' },
            { id: 'minus', text: 'Deviation -' }
        ]
    });

    function setDisplayBehaviour() {
        var display = $('#filter_display').combobox('getValue');

        if (display === 'RECAP') {
            $('#filter_doc_no').combobox('disable');
            $('#filter_doc_no').combobox('clear');

            $('#filter_deviation').combobox('enable');
        } else {
            $('#filter_deviation').combobox('disable');
            $('#filter_deviation').combobox('clear');

            $('#filter_doc_no').combobox('enable');
        }
    }


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