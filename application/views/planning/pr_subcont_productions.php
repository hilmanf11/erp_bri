<style>
    .messager-body .messager-input {
        padding: 4px !important;
    }
</style>

<div id="f" class="easyui-accordion" style="width:99.5%;">
    <div title="Click this to hide the filter" data-options="selected:true" style="padding:10px; background:#F4F4F4">
        <div style="display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
            <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Form Filter Data</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:29.8%;" name="filter_month" id="filter_month" value="<?= date("m") ?>" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:29.7%;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox" data-options="prompt:'Year'">
                </div>

                <!-- <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" name="filter_revision" id="filter_revision" value="<?= "0" ?>" class="easyui-combobox" data-options="prompt:'Revision'" panelHeight="auto">
                </div> -->

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Order Type</span>
                    <select style="width:60%;" id="filter_order_type" panelHeight="auto" class="easyui-combobox" required data-options="editable: false">
                        <option value="" disabled selected>Choose Order Type</option>
                        <option value="Regular">Regular</option>
                        <option value="Additional">Additional</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Subcont Name</span>
                    <input style="width:60%;" id="filter_subcont_id" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Document No</span>
                    <input style="width:60%;" id="filter_doc_no" class="easyui-combobox">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="filter_item_fg_id" id="filter_item_fg_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="formula()"><i class="fa fa-list"></i> Formula</a>
                </div>
            </fieldset>
            <fieldset style="width: 20%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Component Check</b></legend>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_forecast" value="on" readonly="true"> &nbsp; Forecast
                </div>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_fg" value="on" readonly="true"> &nbsp; Stock Finish Good
                </div>
                <div style="margin:12px;">
                    <input class="easyui-checkbox" id="check_ost_so" value="on" readonly="true"> &nbsp; OST SO Customer
                </div>
            </fieldset>
            <fieldset style="width: 40%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
                <legend><b>Process Generate Data</b></legend>
                <a href="javascript:;" style="float: left; color:green;" class="easyui-linkbutton" plain="true"><i class="fa fa-check"></i> SUCCESS : <b id="p_success">0</b></a>
                <a href="javascript:;" style="float: right; color:red;" class="easyui-linkbutton" plain="true" onclick="downloadFailed()"><i class="fa fa-times"></i> FAILED : <b id="p_failed">0</b></a>
                <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
                <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
                <div id="p_remarks" class="easyui-panel" style="width:100%; height:120px; padding:10px; margin-top: 10px; overflow: auto;">
                    <ul id="remarks">

                    </ul>
                </div>
            </fieldset>
        </div>

        <?= $button ?>
    </div>
</div>

<div id="dlg-formula" class="easyui-dialog" title="Formula" style="width: 600px; padding:10px; top: 20px;" data-options="closed: true, modal:false">
    <ul>
        <li>QTY ORDER = <b>(OST SO CUSTOMER - STOCK FG + FC M0 + (30% FC M1))</b></li>
        <li>OST SO CUSTOMER = <b>(SO NEXT PERIOD, CUT OFF 15 TO 16 EVERY MONTH)</b></li>
        <li>STOCK FG = <b>(DAILY STOCK)</b></li>
        <li>
            FC M0 = <b>(FORECAST FOR PERIOD WILL BE GENERATE)</b><br>
            SAMPLE : IF GENERATE PERIOD JANUARY THAN FC M0 = <b>FC JANUARY</b>
        </li>
        <li>FC M1 = <b>(FORECAST FOR NEXT MONTH/PERIOD)</b></li>
    </ul>

    <i>*) Next month everything is the same</i>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 400px; border: 0;"></iframe>
</div>

<script>

    $(function () {
        function updatePrintoutHeight() {
            if ($('.accordion-header-selected').length > 0) {
                $('#printout').css('height', '390px');
            } else {
                $('#printout').css('height', '110vh');
            }
        }

        updatePrintoutHeight();
        setInterval(updatePrintoutHeight, 200);
    });

    function formula() {
        $("#dlg-formula").dialog('open');
    }

    //Add Data
    function add() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_order_type = $("#filter_order_type").combobox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combobox('getValue');

        if(filter_order_type === ""){
            toastr.warning("Please select Order Type!", "Information");
            return;
        }

        if (filter_order_type === 'Additional') {
            checkPRRegular(filter_year, filter_month, filter_order_type, filter_subcont_id, function(res) {
                if (!res.status) {
                    toastr.warning(res.message);
                    return;
                } else {}

                doGeneratePR();
            });

        } else {
            doGeneratePR();
        }

    }

    function doGeneratePR() {

        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        var filter_order_type = $("#filter_order_type").combobox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');
        var check_forecast = $("#check_forecast").checkbox('options');
        var check_fg = $("#check_fg").checkbox('options');
        var check_ost_so = $("#check_ost_so").checkbox('options');

        if (
            check_forecast.checked == true && 
            check_fg.checked == true && 
            check_ost_so.checked == true
        ) {
            $.messager.prompt('Generate PR to Sub Prod', 'Please input Password Generate', function(r) {
                if (r == "GENERATEPR") {
                    Swal.fire({
                        title: 'Please Wait for Push Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                    Swal.fire({
                        title: 'Please Wait 5 - 10 Minutes for Generating Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "GET",
                        url: "<?= base_url('planning/pr_subcont_productions/getdata') ?>",
                        data: "filter_month=" + window.btoa(filter_month) +
                            "&filter_year=" + window.btoa(filter_year) +
                            "&filter_order_type=" + window.btoa(filter_order_type) +
                            // "&filter_item_fg_id=" + window.btoa(filter_item_fg_id)+ 
                            "&filter_subcont_id=" + window.btoa(filter_subcont_id),
                        dataType: "text",
                        success: function(rows) {
                            let results = JSON.parse(rows);
                            console.log('RESULT : ', results);

                            Swal.close();

                            // if(results.length>0){
                            //     requestData(results.length, results);
                            // }

                            if (results.length > 0) {

                                if (filter_order_type === 'Additional') {
                                    $.post(
                                        '<?= base_url("planning/pr_subcont_productions/generate_doc_no_additional") ?>',
                                        {
                                            p_month: filter_month,
                                            p_year: filter_year,
                                            subcont_id: filter_subcont_id
                                        },
                                        function (res) {
                                            let doc_no = res.doc_no;

                                            // inject doc_no ke semua item
                                            results.forEach(r => {
                                                r.doc_no = doc_no;
                                            });

                                            requestData(results.length, results);
                                        },
                                        'json'
                                    );
                                } else {
                                    requestData(results.length, results);
                                }
                            }


                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('planning/pr_subcont_productions/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            Swal.close();
                                            $('#p_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            $('#p_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;

                                            //Json Failed
                                            $.ajax({
                                                type: "POST",
                                                async: true,
                                                url: "<?= base_url('planning/pr_subcont_productions/uploadcreateFailed') ?>",
                                                data: {
                                                    data: json[number - 1],
                                                    message: result.message
                                                },
                                                cache: false
                                            });

                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Save Data Completed!', 'success');
                                        }

                                        $("#p_remarks").append(title + "<br>");
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            Swal.fire({
                                                title: 'Connection Time Out, Check Your Connection',
                                                showConfirmButton: false,
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading();
                                                },
                                            });

                                            requestData(total, json, number, value, success + 0, failed + 0);
                                        }
                                    });
                                }
                            }
                        }
                    });
                }else{
                    toastr.error("Opsss! Wrong Password!", "Error");
                }
            });

            setTimeout(function () {
                $(".messager-input").attr("type", "password");
            }, 50);
        } else {
            toastr.warning("Component Check Not Complete ", "Information");
        }

    }

    function downloadFailed() {
        window.open('<?= base_url('planning/pr_subcont_productions/uploadDownloadFailed') ?>', '_blank');
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        // var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_order_type = $("#filter_order_type").combobox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combobox('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');        

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            // "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_order_type=" + window.btoa(filter_order_type) +
            "&filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_doc_no=" + window.btoa(filter_doc_no) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/pr_subcont_productions/print') ?>' + url);
        }
    }

    // function revisionSelected(filter_month, filter_year) {
    //     $.ajax({
    //         type: "post",
    //         url: "<?= base_url('planning/pr_subcont_productions/revision') ?>",
    //         data: "filter_month=" + filter_month + "&filter_year=" + filter_year,
    //         dataType: "html",
    //         success: function(response) {
    //             $("#filter_revision").combobox('setValue', response);
    //         }
    //     });
    // }

    // function componentCheck(filter_month, filter_year, filter_revision, filter_subcont_id = "") {
    function componentCheck(filter_month, filter_year, filter_subcont_id = "") {

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/pr_subcont_productions/checkForecast') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year) +
                "&filter_subcont_id=" + window.btoa(filter_subcont_id),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_forecast').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_forecast').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/pr_subcont_productions/checkFg') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
            // "&filter_revision=" + window.btoa(filter_revision) +
                "&filter_year=" + window.btoa(filter_year),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_fg').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_fg').checkbox({
                        checked: false
                    });
                }
            }
        });

        $.ajax({
            type: "get",
            url: "<?= base_url('planning/pr_subcont_productions/checkOstSo') ?>",
            data: "filter_month=" + window.btoa(filter_month) +
                "&filter_year=" + window.btoa(filter_year),
                // "&filter_revision=" + window.btoa(filter_revision),
            dataType: "json",
            success: function(ost_so) {
                if (ost_so.theme == "success") {
                    $('#check_ost_so').checkbox({
                        checked: true
                    });
                } else {
                    $('#check_ost_so').checkbox({
                        checked: false
                    });
                }
            }
        });

    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").textbox('getValue');
        // var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_order_type = $("#filter_order_type").combobox('getValue');
        var filter_subcont_id = $("#filter_subcont_id").combobox('getValue');
        var filter_doc_no = $("#filter_doc_no").combobox('getValue');
        var filter_item_fg_id = $("#filter_item_fg_id").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            // "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_order_type=" + window.btoa(filter_order_type) +
            "&filter_subcont_id=" + window.btoa(filter_subcont_id) +
            "&filter_doc_no=" + window.btoa(filter_doc_no) +
            "&filter_item_fg_id=" + window.btoa(filter_item_fg_id);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please select Period!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/pr_subcont_productions/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $("#add").html('Generate');
        var month = $("#filter_month").combobox('getValue');
        var year = $("#filter_year").combobox('getValue');
        // var revision = $("#filter_revision").combobox('getValue');
        var subcont = $("#filter_subcont_id").combobox('getValue');

        componentCheck(month, year, subcont);
        // componentCheck(month, year, revision, subcont);
        // revisionSelected(month, year);

        $('#dg').datagrid({
            url: '<?= base_url('planning/pr_subcont_productions/datatables') ?>',
            rownumbers: true
        });

        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/pr_subcont_productions/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');
                // var revision = $("#filter_revision").combobox('getValue');
                var subcont = $("#filter_subcont_id").combobox('getValue');

                // if (year != "" || revision != "") {
                if (year != "") {
                    componentCheck(month, year, subcont);
                }

                reloadDocNo();
            }
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/pr_subcont_productions/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');
                // var revision = $("#filter_revision").combobox('getValue');
                var subcont = $("#filter_subcont_id").combobox('getValue');

                if (month != "") {
                    componentCheck(month, year, subcont);
                }

                reloadDocNo();
            }
        });

        // $('#filter_revision').combobox({
        //     url: '<?php echo base_url('planning/pr_subcont_productions/readRevisions'); ?>',
        //     valueField: 'id',
        //     textField: 'name',
        //     prompt: 'Choose Revision',
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //         }
        //     }],
        //     onChange: function(row) {
        //         var month = $("#filter_month").combobox('getValue');
        //         var year = $("#filter_year").combobox('getValue');
        //         var revision = $("#filter_revision").combobox('getValue');
        //         var subcont = $("#filter_subcont_id").combobox('getValue');

        //         if (month != "" || year != "") {
        //             componentCheck(month, year, revision, subcont);
        //         }
        //     }
        // });

        $('#filter_item_fg_id').combogrid({
            url: '<?= base_url('planning/pr_subcont_productions/readItemSubProd') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
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

        $('#filter_doc_no').combobox({
            url: '<?php echo base_url('planning/pr_subcont_productions/readDocNo'); ?>',
            valueField: 'doc_no',
            textField: 'doc_no',
            prompt: 'Choose Document No',
            onBeforeLoad: function(param){
                param.month = $('#filter_month').combobox('getValue');
                param.year  = $('#filter_year').combobox('getValue');
            },
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }]
        });

        function reloadDocNo(){
            var month = $('#filter_month').combobox('getValue');
            var year  = $('#filter_year').combobox('getValue');

            $('#filter_doc_no').combobox('clear');

            if(month === '' || year === ''){
                return;
            }

            $('#filter_doc_no').combobox('reload', {
                month: month,
                year : year
            });
        }

        $('#filter_subcont_id').combogrid({
            url: '<?= base_url('planning/pr_subcont_productions/readSubcontProduction'); ?>',
            panelWidth: 500,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Subcont",
            columns: [
                [{
                    field: 'id',
                    title: 'Subcont ID',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Subcont Code',
                    width: 150
                }, {
                    field: 'name',
                    title: 'Subcont Name',
                    width: 200
                }, ]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            onChange: function(row) {
                var month = $("#filter_month").combobox('getValue');
                var year = $("#filter_year").combobox('getValue');
                // var revision = $("#filter_revision").combobox('getValue');
                var order_type = $("#filter_order_type").combobox('getValue');
                var subcont = $("#filter_subcont_id").combobox('getValue');

                if (month != "" || year != "" || subcont != "") {
                    componentCheck(month, year, subcont);
                }

                // revisionSelected(month, year);
            }
        });

    });


    function checkPRRegular(year, month, order_type, subcont_id, callback) {
        $.ajax({
            type: "post",
            url: "<?= base_url('planning/Pr_subcont_productions/checkPRRegular/') ?>"
                + btoa(year) + "/" 
                + btoa(month) + "/" 
                + btoa(order_type) + "/" 
                + btoa(subcont_id),
            dataType: "json",
            success: function(res) {
                callback(res);
            }
        });
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