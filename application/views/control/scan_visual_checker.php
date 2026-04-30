<style>
    .scan {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 40px !important;
    }

    .swal2-container {
        z-index: 9999 !important;
    }
    .swal2-popup {
        z-index: 99999 !important;
    }

    #dlgProblem {
        padding: 10px !important;
    }

    .ng-wrapper {
        height: calc(100% - 12px);
        padding-bottom: 0px;
    }

    .datagrid-header-rownumber,
    .datagrid-cell-rownumber{
        width:40px !important;
    }


    /* .datagrid-row-selected{
        background: transparent !important;
        color: inherit !important;
    } */

    .btn-problem{
        pointer-events:none;
        opacity:.4;
    }

    .datagrid-row-editing .btn-problem{
        pointer-events:auto;
        opacity:1;
    }

    .btn-lg {
        padding: 15px !important;
    }

    #printLabel,
    #reload,
    #checkComplete {
        font-size: 20px !important;
        line-height: normal;
    }

    .btn-lg .fa-print,
    .btn-lg .fa-rotate-right,
    .btn-lg .fa-check {
        font-size: 20px !important;
    }

    .btn-lg .fa-check{
        color: #23d223;
    }

    .btn-lg .fa-print{
        color: #e9a11e;
    }

    .btn-lg .fa-rotate-right{
        color: #3fc4ff;
    }

</style>

<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <!-- <th rowspan="2" field="ck" checkbox="true"></th> -->
            <th rowspan="2" data-options="field:'action',width:100,halign:'center',formatter: buttonEdit">Action</th>
            <th rowspan="2" data-options="field:'item_fg_number',width:200,halign:'center'">Product No</th>
            <th rowspan="2" data-options="field:'item_fg_name',width:200,halign:'center'">Product Name</th>
            <th rowspan="2" data-options="field:'workorder_label',width:150,halign:'center'">Serial WO No</th>
            <th rowspan="2" data-options="field:'source',width:100,halign:'center',align:'center'">Source</th>

            <!-- <th rowspan="2" data-options="field:'operator_finishing',width:150,halign:'center',styler: emptyRedStyler,editor:{type: 'textbox', options: { required: true }}">Operator Finishing</th>
            <th rowspan="2" data-options="field:'compound_lot_no',width:150,halign:'center',styler: emptyRedStyler,editor:{type: 'textbox', options: { required: true }}">Compound Lot No</th> -->

            <th rowspan="2" data-options="
                field:'operator_finishing',
                width:150,
                halign:'center',
                styler: emptyRedStyler,
                editor:{
                    type:'textbox',
                    options:{
                        required:true,
                        inputEvents: $.extend({}, $.fn.textbox.defaults.inputEvents, {
                            input: function(e){
                                let val = e.target.value.toUpperCase();
                                val = val.replace(/[^A-Z]/g, '');
                                e.target.value = val;
                            }
                        })
                    }
                }
            ">Operator Finishing</th>

            <th rowspan="2" data-options="
                field:'compound_lot_no',
                width:150,
                halign:'center',
                styler: emptyRedStyler,
                editor:{
                    type:'textbox',
                    options:{
                        required:true,
                        inputEvents: $.extend({}, $.fn.textbox.defaults.inputEvents, {
                            input: function(e){
                                let val = e.target.value.toUpperCase();
                                e.target.value = val;
                            }
                        })
                    }
                }
            ">Compound Lot No</th>

            <th colspan="7" data-options="align:'center'">QTY</th>
            <th rowspan="2" data-options="field:'problems',width:150,halign:'center',formatter: buttonProblem">NG Problems</th>
        </tr>
        <tr>
            <th data-options="field:'qty_on_label',width:80,halign:'center',align:'right',formatter:numberformat, styler:numberStyle">On Label</th>

            <th data-options="field:'qty_actual',width:80,halign:'center',align:'right',formatter:numberformat, styler:numberStyle,
            editor:{
                type:'numberbox',
                options:{
                    required: true,
                    onChange:function(){
                        var i = getEditingIndex(this);
                        if(i>=0){
                            setTimeout(function(){ recalcRow(i); },0);
                        }
                    },
                    validType:'greaterThanZero'
                }
            }">Actual</th>

            <th data-options="field:'qty_deviation',width:80,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2">Deviation</th>

            <th data-options="field:'qty_ok',width:80,halign:'center',align:'right',formatter:numberformat, styler:numberStyle,
            editor:{
                type:'numberbox',
                options:{
                    required: true,
                    onChange:function(){
                        var i = getEditingIndex(this);
                        if(i>=0){
                            setTimeout(function(){ recalcRow(i); },0);
                        }
                    },
                    validType:'greaterThanZero'
                }
            }">OK</th>

            <th data-options="field:'qty_rework',width:80,halign:'center',align:'right',
            formatter:numberformat, styler:numberStyle2,
            editor:{
                type:'numberbox',
                options:{
                    required: true,
                    validType:'notNegative',
                    onChange:function(value){
                        var index = getEditingIndex(this);
                        if(index < 0) return;

                        var dg = $('#dg');
                        var row = dg.datagrid('getRows')[index];
                        var editor = dg.datagrid('getEditor',{index:index, field:'qty_rework'});

                        if(!editor) return;

                        var $target = $(editor.target);
                        var val = Number(value);

                        if(val < 0){
                            toastr.error('Value cannot be negative');
                            $target.numberbox('setValue', row._prev_qty_rework || 0);
                            return;
                        }

                        row._prev_qty_rework = val;
                        setTimeout(function(){ recalcRow(index); },0);
                    }
                }
            }">Rework</th>

            <th data-options="field:'total_ng',width:80,halign:'center',align:'right',
            formatter:numberformat, styler:numberStyle2,
            editor:{
                type:'numberbox',
                options:{
                    required: true,
                    validType:'notNegative',
                    onChange:function(value){
                        var index = getEditingIndex(this);
                        if(index < 0) return;

                        var dg = $('#dg');
                        var row = dg.datagrid('getRows')[index];
                        var editor = dg.datagrid('getEditor',{index:index, field:'total_ng'});

                        if(!editor) return;

                        var $target = $(editor.target);
                        var val = Number(value);

                        if(val < 0){
                            toastr.error('Value cannot be negative');
                            $target.numberbox('setValue', row._prev_total_ng || 0);
                            return;
                        }

                        row._prev_total_ng = val;
                        setTimeout(function(){ recalcRow(index); },0);
                    }
                }
            }">Total NG</th>

            <th data-options="field:'qty_return',width:80,halign:'center',align:'right',formatter:numberformat, styler:numberStyle2">Return</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 255px;">
    <div style="width: 100%; padding: 10px;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;padding-top: 15px;">

            <legend><b>Form Scan Visual Checker</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Check Date</span>
                    <input style="width:60%;" name="check_date" id="check_date" required="" value="<?= date('Y-m-d') ?>" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Inspector</span>
                    <input style="width:60%;" name="inspector" id="inspector" class="easyui-combogrid" required>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Visual Process</span>
                    <select style="width:60%;" name="visual_process" id="visual_process" panelHeight="auto" class="easyui-combobox" data-options="editable:false,prompt:'Choose visual process'" required>
                        <option value="Check">Check</option>
                        <option value="Sortir">Sortir</option>
                        <option value="Repair">Repair</option>
                    </select>
                </div>

                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="customer" id="customer" class="easyui-combogrid">
                </div>

                <div class="fitem" style="text-align: right; width: 100%; padding-right: 4.5%;">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="start_scan()"><i class="fa fa-play"></i> Start Scan</a>
                </div>
            </div>

            <div style="width: 70%; float: left;">
                <div class="fitem" style="padding:0 70px 0 40px;">
                    <span style="display:inline-block;">Trans Date : <b><?= date("d F Y") ?></b> | Receive By : <b><?= $this->session->name ?></b></span>
                </div>
                <div class="fitem" style="padding:0 70px 0 40px;">
                    <input style="width:100%; height: 80px;" type="text" id="workorder_label" name="workorder_label" class="scan" placeholder="SCAN LABEL HERE" autofocus>
                </div>

                <div class="fitem" style="padding:0 70px 0px 40px;">

                    <!--
                    <a href="javascript:;" class="easyui-linkbutton" id="btnPreview" onclick="btnPreview()">
                        <i class="fa fa-search"></i> Preview Data
                    </a>
                    -->

                    <a href="javascript:;" class="easyui-linkbutton btn-lg" style="margin-right: 5px !important;" id="btnComplete" onclick="btnComplete()">
                        <i class="fa fa-check"></i> <span id="checkComplete">Complete</span>
                    </a>

                    <a href="javascript:;" class="easyui-linkbutton btn-lg" style="margin-right: 5px !important;" id="btnCreateLabel" onclick="btnCreateLabel()">
                        <i class="fa fa-print"></i> <span id="printLabel">Create Label</span>
                    </a>

                    <a href="javascript:;" class="easyui-linkbutton btn-lg" onclick="reload()">
                        <i class="fa fa-rotate-right"></i> <span id="reload">Reload</span>
                    </a>

                </div>
            </div>

        </fieldset>
    </div>
</div>


<audio id="serialDuplicate">
    <source src="<?= base_url('assets/audio/serial_duplicate.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialSuccess">
    <source src="<?= base_url('assets/audio/serial_success.mpeg') ?>" type="audio/mpeg">
</audio>
<audio id="serialNotFound">
    <source src="<?= base_url('assets/audio/serial_notfound.mpeg') ?>" type="audio/mpeg">
</audio>


<div id="dlgProblem" class="easyui-dialog" closed="true"
     style="width:600px;height:465px;"
     data-options="modal:true,closed:true,buttons:'#dlgProblemButtons',title:'NG Problem List'">

    <div class="ng-wrapper">
        <table id="tblNG"></table>
    </div>
</div>

<div id="dlgProblemButtons">

    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-no" onclick="$('#dlgProblem').dialog('close')">Close</a>

    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="saveNG()">Save</a>
</div>

<div id="dlgScanVc" class="easyui-dialog" title="Scan Visual Checker" style="width:1270px;height:600px;padding:10px;" closed="true" modal="true" buttons="#dlgScanVcButtons">

    <table id="dgScanVc" class="easyui-datagrid" style="width:100%;height:100%;"></table>
</div>

<div id="dlgScanVcButtons">
    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-no" onclick="$('#dlgScanVc').dialog('close')">Close</a>

    <a href="javascript:;" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveSummaryScanVc()">Finish</a>
</div>

<script>

    let isLoadingExistingData = false;
    let currentIndexProblem = null;
    let problemData = {};

    function reload() {
        window.location.reload();

        $('#workorder_label').val('').prop('disabled', true);
        $('a[onclick="start_scan()"]').linkbutton('enable');
    }

    function start_scan() {
        var check_date      = $("#check_date").datebox('getValue');
        var inspector       = $("#inspector").combogrid('getValue');
        var visual_process  = $("#visual_process").combobox('getValue');
        var customer        = $("#customer").combogrid('getValue');

        if(check_date != "" && inspector != "" && visual_process != "") {
            $('#check_date').datebox('disable');
            $('#inspector').combogrid('disable');
            $('#visual_process').combobox('disable');
            $('#customer').combogrid('disable');

            $('#workorder_label').prop('disabled', false).focus();
            $('a[onclick="start_scan()"]').linkbutton('disable');

            toastr.success("Scan mode activated");

        } else {
            toastr.error("Please fill in all required fields first");
        }
    }

    $(function() {

        $('#workorder_label').prop('disabled', true);
        $('#btnCreateLabel').linkbutton('disable');

        setTimeout(function() {

            $('#dg').datagrid({
                url:'<?= base_url("control/scan_visual_checker/getScanVisualChecker") ?>',
                rownumbers:true,

                onLoadSuccess:function(data){
                    checkRowStatus();
                    $('.datagrid-header-rownumber').text('No');

                    var dg=$(this);
                    var rows=dg.datagrid('getRows');

                    for(let i=0;i<rows.length;i++){
                        let r=rows[i];

                        r.qty_actual   = Number(r.qty_actual||0);
                        r.qty_ok       = Number(r.qty_ok||0);
                        r.qty_rework   = Number(r.qty_rework||0);
                        r.qty_on_label = Number(r.qty_on_label||0);
                        r.total_ng     = Number(r.qty_ng_total||0);

                        r.qty_deviation = r.qty_actual - r.qty_on_label;
                        r.qty_return = r.qty_actual - r.qty_ok - r.qty_rework - r.total_ng;

                        dg.datagrid('refreshRow',i);
                    }

                    rows.forEach((r,i)=>{

                        $.get("<?= base_url('control/scan_visual_checker/getNGByDetail') ?>/"+r.id,function(res){

                            let arr = JSON.parse(res||"[]");
                            if(arr.length){

                                let dbNG={};
                                arr.forEach(x=>{
                                    dbNG[x.code]=parseInt(x.qty_ng);
                                });

                                problemData[i]=dbNG;
                            }

                        });

                    });

                    if(data.total === 0){
                        $('#check_date').datebox('enable');
                        $('#inspector').combogrid('enable');
                        $('#visual_process').combobox('enable');
                        $('#customer').combogrid('enable');
                        $('a[onclick="start_scan()"]').linkbutton('enable');

                    }else{
                        $('a[onclick="start_scan()"]').linkbutton('disable');

                        $("#check_date").datebox('setValue',data.rows[0]['check_date']);
                        $("#inspector").combogrid('setValue',data.rows[0]['inspector']);
                        $("#visual_process").combobox('setValue',data.rows[0]['visual_process']);
                        $("#customer").combogrid('setValue',data.rows[0]['customer_name']);

                        $('#check_date').datebox('disable');
                        $('#inspector').combogrid('disable');
                        $('#visual_process').combobox('disable');
                        $('#customer').combogrid('disable');
                    }
                },

                onBeginEdit:function(index,row){          
                    
                    var dg = $(this);

                    var edOperator = dg.datagrid('getEditor',{index:index,field:'operator_finishing'});
                    var edLot      = dg.datagrid('getEditor',{index:index,field:'compound_lot_no'});

                    if(row.serial_label){
                        if(edOperator) $(edOperator.target).textbox('readonly', true);
                        if(edLot) $(edLot.target).textbox('readonly', true);
                    } else {
                        if(edOperator) $(edOperator.target).textbox('readonly', false);
                        if(edLot) $(edLot.target).textbox('readonly', false);
                    }

                    $(this).datagrid('selectRow',index);

                    row._prev_qty_rework = row.qty_rework;
                    row._prev_total_ng = row.total_ng;

                    var dg = $('#dg');
                    function clearIfZero(field){
                        var ed = dg.datagrid('getEditor',{index:index,field:field});
                        if(!ed) return;

                        var nb = $(ed.target);
                        var val = Number(nb.numberbox('getValue'));

                        if(!val){
                            nb.numberbox('setValue','');
                        }
                    }

                    clearIfZero('qty_actual');
                    clearIfZero('qty_ok');
                },

                onBeforeEdit:function(index,row){
                    row.editing=true;
                    $(this).datagrid('refreshRow',index);
                },
                onAfterEdit:function(index,row){
                    row.qty_actual = Number(row.qty_actual||0);
                    row.qty_ok     = Number(row.qty_ok||0);
                    row.qty_rework = Number(row.qty_rework||0);
                    row.qty_on_label = Number(row.qty_on_label||0);

                    row.total_ng = Number(row.total_ng||0);

                    row.qty_deviation = row.qty_actual - row.qty_on_label;
                    row.qty_return      = row.qty_actual - row.qty_ok - row.qty_rework - row.total_ng;

                    if(row.qty_ok + row.qty_rework + row.total_ng > row.qty_actual){
                        toastr.error('OK + Rework + Total NG tidak boleh lebih dari Actual');
                        $('#dg').datagrid('beginEdit',index);
                        return;
                    }

                    let sumNG = getSumNGFromProblem(index);
                    let totalNG = parseInt(row.total_ng || 0);

                    if(totalNG > 0 && !problemData[index] || row.qty_actual == ''){
                        toastr.error('Please fill in the NG Problems details first');
                        $('#dg').datagrid('beginEdit',index);
                        return;
                    }

                    if(sumNG > totalNG){
                        toastr.warning('The total NG details cannot be greater than the Total NG ('+totalNG+')');
                        $('#dg').datagrid('beginEdit',index);
                        return;
                    }

                    if(sumNG < totalNG){
                        toastr.warning('The total NG details cannot be less than the Total NG ('+totalNG+')');
                        $('#dg').datagrid('beginEdit',index);
                        return;
                    }

                    row.editing=false;
                    $('#dg').datagrid('refreshRow',index);
                    let detail_id = row.id;

                    // updateQty(row,index);
                    // saveNGPerDetail(index, detail_id);

                    updateQty(row,index,function(){
                        saveNGPerDetail(index, detail_id);
                    });

                },
                onCancelEdit:function(index,row){
                    row.editing=false;
                    $(this).datagrid('refreshRow',index);
                }

            });

        }, 50);

        //Audio Config
        var serialDuplicate = document.getElementById("serialDuplicate");
        var serialSuccess = document.getElementById("serialSuccess");
        var serialNotFound = document.getElementById("serialNotFound");


        setTimeout(function() {
            $('#workorder_label').focus(); 
        }, 200);


        //Scan Label
        $('#workorder_label').keypress(function(e) {
            if (e.which == 13) {
                var workorder_label = $(this).val();
                var check_date      = $("#check_date").datebox('getValue');
                var inspector       = $("#inspector").combogrid('getValue');
                var visual_process  = $("#visual_process").combobox('getValue');
                var customer        = $("#customer").combogrid('getValue');

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('control/scan_visual_checker/getChecksheetLabel') ?>",
                    data: {
                        workorder_label: workorder_label
                    },
                    dataType: "json",
                    success: function(json) {
                        console.log('Response : ', json);

                        if (json.title === "Not Found") {
                            serialNotFound.play();
                            toastr.warning(json.message, "Not Found");
                            $("#workorder_label").val('').focus();
                            return;
                        } else if (json.title === "Scanned" || json.title === "Available") {
                            serialDuplicate.play();
                            toastr.warning(json.message, "Already Scanned");
                            $("#workorder_label").val('').focus();
                            return;
                        } else if(json.title !== "success") {
                            toastr.warning(json.message, json.title);
                            $("#workorder_label").val('').focus();
                            return;
                        }


                        // if (json.title === "success") {
                        //     var row = json.data;

                        //     $.ajax({
                        //         type: "POST",
                        //         url: "<?= base_url('control/scan_visual_checker/create') ?>",
                        //         data: {
                        //             check_date: check_date,
                        //             inspector: inspector,
                        //             visual_process: visual_process,
                        //             customer: customer,

                        //             item_fg_id: row.item_fg_id,
                        //             workorder: row.workorder,
                        //             label: label,
                        //             qty: row.qty,
                        //         },
                        //         dataType: "json",
                        //         success: function(result) {
                        //             if (result.theme === "success") {
                        //                 serialSuccess.play();
                        //                 toastr.success(result.message, result.title);
                        //             } else {
                        //                 if (result.title == "Available") {
                        //                     serialDuplicate.play();
                        //                 } else if(result.title == "Not Found") {
                        //                     serialNotFound.play();
                        //                 } else if (result.title == "Already Scanned") {
                        //                     // serialDuplicate.play();
                        //                 }

                        //                 toastr.warning(result.message, result.title);
                        //             }

                        //             $("#workorder_label").val('');
                        //             $('#workorder_label').focus();
                        //             $('#dg').datagrid('reload');

                        //         },
                        //         error: function(xhr, status, error) {
                        //             toastr.error("An error occurred: " + error, "Error");
                        //         }
                        //     });

                        //     return;
                        // }


                        if (json.title === "success") {
                            var row = json.data;

                            $.ajax({
                                type: "POST",
                                url: "<?= base_url('control/scan_visual_checker/create_bulk') ?>",
                                data: {
                                    check_date: check_date,
                                    inspector: inspector,
                                    visual_process: visual_process,
                                    customer: customer,
                                    rows: json.data
                                },
                                dataType: "json",
                                success: function(result) {

                                    if (result.theme === "success") {
                                        serialSuccess.play();
                                        toastr.success(result.message, result.title);
                                    } else {
                                        if (result.title == "Available") {
                                            serialDuplicate.play();
                                        } else if(result.title == "Not Found") {
                                            serialNotFound.play();
                                        } else if (result.title == "Already Scanned") {
                                            // serialDuplicate.play();
                                        }

                                        toastr.warning(result.message, result.title);
                                    }

                                    $("#workorder_label").val('');
                                    $('#workorder_label').focus();
                                    $('#dg').datagrid('reload');

                                },
                                error: function(xhr, status, error) {
                                    toastr.error("An error occurred: " + error, "Error");
                                }
                            });

                            return;
                        }

                    }
                });
            }
        });

    });

    function saveNGPerDetail(rowIndex, detail_id){

        let ngList = problemData[rowIndex] || {};

        $.ajax({
            type:'POST',
            url:"<?= base_url('control/scan_visual_checker/saveNGDetail') ?>",
            data:{
                detail_id:detail_id,
                ng:JSON.stringify(ngList)
            },
            success:function(res){
                console.log('NG saved',res);
            }
        });
    }

    // $('#inspector').combogrid({
    //     url: '<?= base_url("master/man_powers/readVisualCheckers") ?>',
    //     panelWidth: 400,
    //     idField: 'nik',
    //     textField: 'name',
    //     valueField: 'nik',
    //     mode: 'remote',
    //     fitColumns: true,
    //     prompt: "Select Inspector Name",
    //     icons: [{
    //         iconCls: 'icon-clear',
    //         handler: function(e) {
    //             $(e.data.target).combogrid('clear').combogrid('textbox').focus();
    //         }
    //     }],
    //     columns: [
    //         [{
    //             field: 'nik',
    //             title: 'NIK',
    //             width: 200
    //         }, {
    //             field: 'name',
    //             title: 'Name',
    //             width: 200
    //         }]
    //     ],
    // });

    $('#inspector').combogrid({
        url: '<?= base_url("master/man_powers/readVisualCheckers") ?>',
        panelWidth: 400,
        idField: 'nik',
        textField: 'name',
        valueField: 'nik',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Inspector Name",

        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],

        columns: [[
            { field: 'nik', title: 'NIK', width: 200 },
            { field: 'name', title: 'Name', width: 200 }
        ]],

        queryParams: {},

        onShowPanel: function() {
            var grid = $(this).combogrid('grid');
            if (!grid.data('loaded')) {
                grid.datagrid('load', {});
                grid.data('loaded', true);
            }
        },

        loadFilter: function(data){
            return Array.isArray(data) ? data : [];
        },

        onHidePanel: function() {
            var t = $(this).combogrid('getText');
            var g = $(this).combogrid('grid');
            var rows = g.datagrid('getRows');
            var exists = false;

            for (var i = 0; i < rows.length; i++) {
                if (rows[i].name === t) {
                    exists = true;
                    break;
                }
            }

            if (!exists) {
                $(this).combogrid('clear');
                g.datagrid('loadData', []);
                g.removeData('loaded');
            }
        },
    });


    // $('#customer').combogrid({
    //     url: '<?= base_url("master/customers/reads") ?>',
    //     panelWidth: 350,
    //     idField: 'id',
    //     textField: 'name',
    //     valueField: 'id',
    //     mode: 'remote',
    //     fitColumns: true,
    //     prompt: "Select Customer",
    //     icons: [{
    //         iconCls: 'icon-clear',
    //         handler: function(e) {
    //             $(e.data.target).combogrid('clear').combogrid('textbox').focus();
    //         }
    //     }],
    //     columns: [
    //         [{
    //             field: 'id',
    //             title: 'Customer ID',
    //             width: 100
    //         }, {
    //             field: 'name',
    //             title: 'Customer Name',
    //             width: 250
    //         }]
    //     ],
    // });


    $('#customer').combogrid({
        url: '<?= base_url("master/customers/reads") ?>',
        panelWidth: 350,
        idField: 'id',
        textField: 'name',
        valueField: 'id',
        mode: 'remote',
        fitColumns: true,
        prompt: "Select Customer",
        icons: [{
            iconCls: 'icon-clear',
            handler: function(e) {
                $(e.data.target).combogrid('clear').combogrid('textbox').focus();
            }
        }],
        columns: [
            [{
                field: 'id',
                title: 'Customer ID',
                width: 100
            }, {
                field: 'name',
                title: 'Customer Name',
                width: 250
            }]
        ],

        queryParams: {},

        onShowPanel: function() {
            var grid = $(this).combogrid('grid');
            if (!grid.data('loaded')) {
                grid.datagrid('load', {});
                grid.data('loaded', true);
            }
        },

        loadFilter: function(data){
            return Array.isArray(data) ? data : [];
        },

        onHidePanel: function() {
            var t = $(this).combogrid('getText');
            var g = $(this).combogrid('grid');
            var rows = g.datagrid('getRows');
            var exists = false;

            for (var i = 0; i < rows.length; i++) {
                if (rows[i].name === t) {
                    exists = true;
                    break;
                }
            }

            if (!exists) {
                $(this).combogrid('clear');
                g.datagrid('loadData', []);
                g.removeData('loaded');
            }
        },
    });


    function recalcRow(index){
        var dg = $('#dg');
        var row = dg.datagrid('getRows')[index];
        if(!row) return;

        var edActual  = dg.datagrid('getEditor',{index:index,field:'qty_actual'});
        var edOk      = dg.datagrid('getEditor',{index:index,field:'qty_ok'});
        var edRework  = dg.datagrid('getEditor',{index:index,field:'qty_rework'});
        var edTotalNg = dg.datagrid('getEditor',{index:index,field:'total_ng'});

        var prev_ok       = Number(row.qty_ok||0);
        var prev_rework   = Number(row.qty_rework||0);
        var prev_total_ng = Number(row.total_ng||0);

        var qty_actual = edActual ? Number($(edActual.target).numberbox('getValue')) : Number(row.qty_actual||0);
        var qty_ok     = edOk ? Number($(edOk.target).numberbox('getValue')) : Number(row.qty_ok||0);
        var qty_rework = edRework ? Number($(edRework.target).numberbox('getValue')) : Number(row.qty_rework||0);
        var total_ng = edRework ? Number($(edTotalNg.target).numberbox('getValue')) : Number(row.total_ng||0);
        var qty_on_label = Number(row.qty_on_label||0);

        if(qty_ok + qty_rework + total_ng > qty_actual){
            toastr.error('OK Quantity + Rework Quantity + Total NG must not exceed Actual Quantity');

            if(edOk){
                $(edOk.target).numberbox('setValue', prev_ok);
            }
            if(edRework){
                $(edRework.target).numberbox('setValue', prev_rework);
            }
            if(edTotalNg) {
                $(edTotalNg.target).numberbox('setValue', prev_total_ng);
            }

            return;
        }

        row.qty_actual = qty_actual;
        row.qty_ok = qty_ok;
        row.qty_rework = qty_rework;
        row.total_ng = total_ng;

        var deviation = qty_actual - qty_on_label;
        var qty_return  = qty_actual - qty_ok - qty_rework - total_ng;

        row.qty_deviation = deviation;
        row.qty_return = qty_return;

        var panel = dg.datagrid('getPanel');
        var tr = panel.find('tr.datagrid-row[datagrid-row-index="'+index+'"]');

        tr.find('td[field="qty_deviation"] .datagrid-cell').html(numberformat(deviation));
        tr.find('td[field="qty_return"] .datagrid-cell').html(numberformat(qty_return));
    }

    function getEditingIndex(target){
        var tr = $(target).closest('div.datagrid-cell')
                        .closest('td[field]')
                        .closest('tr.datagrid-row');
        return parseInt(tr.attr('datagrid-row-index'),10);
    }

    function buttonEdit(value, row, index) {
        let status = (row.type_status || '').toUpperCase();
        let allowEdit = (status === 'SCANNING');

        if (!allowEdit) {
            return '<a href="javascript:void(0)" class="btn btn-success btn-sm w-100" style="pointer-events:none; opacity:0.9; background-color: #C8FFCC !important; color: #000 !important; font-weight: bold; border-color: #C8FFCC;">Complete</a>';
        }

        if (row.editing) {
            var s = '<a href="javascript:void(0)" class="btn btn-success btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="saverow(this)">Save</a>';
            return s;
        } else {
            var e = '<a href="javascript:void(0)" class="btn btn-primary btn-sm w-100" style="pointer-events:auto; opacity:1;" onclick="editrow(this)">Edit</a>';
            return e;
        }
    }

    function getRowIndex(target) {
        return parseInt(
            $(target).closest('tr.datagrid-row').attr('datagrid-row-index'),
            10
        );
    }

    function editrow(target){
        var index = getRowIndex(target);
        var dg = $('#dg');

        dg.datagrid('selectRow',index);
        dg.datagrid('beginEdit',index);
    }

    function saverow(target){
        var index = getRowIndex(target);
        var dg = $('#dg');

        var edOperator = dg.datagrid('getEditor',{index:index,field:'operator_finishing'});
        var edLot      = dg.datagrid('getEditor',{index:index,field:'compound_lot_no'});
        var edActual   = dg.datagrid('getEditor',{index:index,field:'qty_actual'});
        var edOK       = dg.datagrid('getEditor',{index:index,field:'qty_ok'});

        function getVal(ed,type='textbox'){
            if(!ed) return '';
            if(type==='numberbox'){
                return $(ed.target).numberbox('getValue');
            }
            return $(ed.target).textbox('getValue');
        }

        var operator = getVal(edOperator);
        var lot      = getVal(edLot);
        var actual   = getVal(edActual,'numberbox');
        var ok       = getVal(edOK,'numberbox');

        if(!operator || !operator.trim()){
            toastr.warning('Operator Finishing is required');
            return;
        }

        if(!lot || !lot.trim()){
            toastr.warning('Compound Lot No is required');
            return;
        }

        if(actual === '' || actual == 0){
            toastr.warning('Actual quantity is required and must be greater than 0');
            return;
        }

        if(ok === '' || ok == 0){
            toastr.warning('OK quantity is required and must be greater than 0');
            return;
        }

        dg.datagrid('endEdit',index);
    }

    function updateQty(row,index,callback){

        $.ajax({
            type:'POST',
            url:'<?= base_url("control/scan_visual_checker/updateQty") ?>',
            dataType:'json',
            data:{
                scan_id:row.scan_id,
                item_fg_id:row.item_fg_id,
                workorder:row.workorder,
                workorder_label:row.workorder_label,

                operator_finishing:row.operator_finishing,
                compound_lot_no:row.compound_lot_no,

                qty_actual:row.qty_actual,
                qty_deviation:row.qty_deviation,
                qty_ok:row.qty_ok,
                qty_rework:row.qty_rework,
                total_ng:row.total_ng,
                qty_return:row.qty_return,
            },
            success:function(res){

                if(res.status==='success'){
                    toastr.success('Data updated');

                    $('#dg').datagrid('updateRow',{
                        index:index,
                        row:row
                    });

                    if(typeof callback === "function"){
                        callback();
                    }

                }else{
                    toastr.error(res.message||'Failed update');
                    $('#dg').datagrid('reload');
                }
            },
            error:function(){
                toastr.error('Server error');
                $('#dg').datagrid('reload');
            }
        });
    }

    function buttonProblem(value,row,index){
        return '<a href="javascript:void(0)" '+
            'class="btn btn-danger btn-sm w-100 btn-problem" '+
            'onclick="setProblem('+index+')">Set Problem</a>';
    }

    function setProblem(index){
        var row = $('#dg').datagrid('getRows')[index];
        var total = parseFloat(row.total_ng) || 0;

        if(total === 0){
            toastr.warning('Total NG quantity is 0, cannot set problem');
            return;
        }

        currentIndexProblem = index;

        if(problemData[index]){
            $('#tblNG').datagrid('load', "<?= base_url('control/scan_visual_checker/getDataMasterNg'); ?>");
            $('#dlgProblem').dialog('open');
            return;
        }

        $.get("<?= base_url('control/scan_visual_checker/getNGByDetail') ?>/"+row.id,function(res){

            let dbNG={};
            let arr=JSON.parse(res||"[]");

            arr.forEach(r=>{
                dbNG[r.code]=parseInt(r.qty_ng);
            });

            problemData[index]=dbNG;

            $('#tblNG').datagrid('load', "<?= base_url('control/scan_visual_checker/getDataMasterNg'); ?>");
            $('#dlgProblem').dialog('open');
        });
    }

    let lastIndexNG = undefined;

    $('#tblNG').datagrid({
        fit: true,
        fitColumns: true,
        rownumbers: true,
        singleSelect: false,
        striped: true,
        border: true,    
        onBeforeSelect: () => false,
        onClickRow: () => false,
        columns:[[
            {
                field:'code',
                title:'NG Code',
                width:100,
                align:'center'
            },
            {
                field:'name',
                title:'NG Name',
                width:200
            },
            { 
                field:'ng_qty',
                title:'Qty',
                width:80,
                align:'center',
                editor:{
                    type:'numberbox',
                    options:{
                        precision:0,
                        min:0
                    }
                }
            }
        ]],
        onLoadSuccess: function() {
            if (problemData[currentIndexProblem]) {
                let rows = $('#tblNG').datagrid('getRows');
                let saved = problemData[currentIndexProblem];

                rows.forEach((r, i) => {
                    if (saved[r.code] !== undefined) {
                        $('#tblNG').datagrid('beginEdit', i);
                        let ed = $('#tblNG').datagrid('getEditor', { index: i, field: 'ng_qty' });
                        $(ed.target).numberbox('setValue', saved[r.code]);
                        $('#tblNG').datagrid('endEdit', i);
                    }
                });
            }
        },
        onClickCell: function (index, field) {
            if (field === 'ng_qty') {
                if (lastIndexNG !== index) {
                    $('#tblNG').datagrid('endEdit', lastIndexNG);
                }

                $('#tblNG').datagrid('beginEdit', index);

                let ed = $('#tblNG').datagrid('getEditor', { index, field: 'ng_qty' });
                if (ed) {
                    $(ed.target).numberbox({
                        onChange: function (newValue, oldValue) {
                            validateNG(index, newValue);
                        }
                    });
                }

                lastIndexNG = index;
            } else {
                $('#tblNG').datagrid('endEdit', lastIndexNG);
                lastIndexNG = undefined;
            }
        }

    });

    function saveNG() {
        let dg = $('#tblNG');
        let rows = dg.datagrid('getRows');

        rows.forEach((r, i) => dg.datagrid('endEdit', i));

        let totalNG = 0;
        rows.forEach((r) => {
            totalNG += parseInt(r.ng_qty || 0);
        });

        let totalNGQty = getTotalNGQty();
        if (totalNG > totalNGQty) {
            toastr.warning("Cannot exceed the Total NG Qty (" + totalNGQty + ")");
            return;
        }

        let temp = {};
        rows.forEach((r) => {
            if (r.ng_qty && parseInt(r.ng_qty) > 0) {
                temp[r.code] = parseInt(r.ng_qty);
            }
        });

        problemData[currentIndexProblem] = temp;
        $('#dlgProblem').dialog('close');
    }

    function getTotalNGQty() {
        let ed = $('#dg').datagrid('getEditor', {
            index: currentIndexProblem,
            field: 'total_ng'
        });
        if (ed) {
            return parseInt($(ed.target).numberbox('getValue') || 0);
        }
        let row = $('#dg').datagrid('getRows')[currentIndexProblem];
        
        return parseInt(row.total_ng || 0);
    }

    function validateNG(index, newValue) {
        let dg = $('#tblNG');
        dg.datagrid('endEdit', index);
        dg.datagrid('beginEdit', index);

        newValue = parseInt(newValue || 0);

        let rows = dg.datagrid('getRows');
        let total = 0;
        rows.forEach((r, i) => {
            total += parseInt(r.ng_qty || 0);
        });

        let totalNGQty = getTotalNGQty();
        if (total > totalNGQty) {
            let ed = dg.datagrid('getEditor', { index, field: 'ng_qty' });
            $(ed.target).numberbox('setValue', '');

            dg.datagrid('endEdit', index);
            dg.datagrid('beginEdit', index);

            toastr.warning("Cannot exceed the Total NG Qty (" + totalNGQty + ")");
        }
    }

    function getSumNGFromProblem(index){
        let ngList = problemData[index] || {};
        let sum = 0;

        Object.keys(ngList).forEach(k=>{
            sum += parseInt(ngList[k] || 0);
        });

        return sum;
    }


    function btnComplete(params) {
        if(hasEditingRow()){
            return;
        }

        if(hasIncompleteRequired()){
            return;
        }

        var check_date      = $("#check_date").datebox('getValue');
        var inspector       = $("#inspector").combogrid('getValue');
        var visual_process  = $("#visual_process").combobox('getValue');
        var customer        = $("#customer").combogrid('getValue');

        if (check_date == "" || inspector == "" || visual_process == "") {
            toastr.warning('Scan Visual Checkcer header is required');
            return;
        }

        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('Data Not Found!');
            return;
        }

        // console.log('ROWS : ', rows);


        let items = [];

        rows.forEach(row => {
            if (row.item_fg_id) {
                items.push({
                    check_date: check_date,
                    inspector: inspector,
                    visual_process: visual_process,
                    customer: customer ?? '',

                    parent_id: row.visual_checker_id,
                    detail_id: row.id,
                    scan_id: row.scan_id,
                    item_fg_id: row.item_fg_id,
                    workorder: row.workorder,
                    workorder_label: row.workorder_label,
                });
            }
        });

        if (items.length === 0) {
            toastr.error("No data to save");
            return;
        }

        console.log('ITEMS : ', items);

        Swal.fire({
            title: 'Confirm Complete',
            text: 'Are you sure you want to complete this scan visual checker data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('control/scan_visual_checker/completeScanVc') ?>',
                    data: { items: items },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Saving...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        Swal.close();

                        if (res.theme === "success") {
                            Swal.fire({
                                title: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                            }).then(() => {
                                $('#dg').datagrid('reload');
                                window.location.reload();
                            });
                        } else {
                            toastr.error(res.message, res.title || "Error");
                        }
                    },
                    error: function () {
                        Swal.close();
                        toastr.error("Server error while saving");
                    }
                });

            }
        });

        checkRowStatus();
    }


    function checkRowStatus(){

        var dg = $('#dg');
        var rows = dg.datagrid('getRows');

        if(rows.length === 0){
            $('#btnComplete').linkbutton('disable');
            $('#btnCreateLabel').linkbutton('disable');
            $('#workorder_label').prop('disabled', true);
            return;
        }

        var allComplete = true;

        for(var i=0;i<rows.length;i++){

            let status = (rows[i].type_status || '').toUpperCase();

            if(status !== 'COMPLETED' && status !== 'FINISHED'){
                allComplete = false;
                break;
            }

        }

        if(allComplete){

            $('#btnComplete').linkbutton('disable');
            $('#btnCreateLabel').linkbutton('enable');
            $('#workorder_label').val('').prop('disabled', true);

        }else{

            $('#btnComplete').linkbutton('enable');
            $('#btnCreateLabel').linkbutton('disable');
            $('#workorder_label').prop('disabled', false);

        }
    }

    function btnCreateLabel() {
        $('#dg').datagrid('acceptChanges');

        var rows = $('#dg').datagrid('getRows');
        var totalrows = rows.length;

        console.log('ROWSS : ', rows);

        if (totalrows === 0) {
            return toastr.error("No data available!");
        }

        $('#dlgScanVc').dialog({
            title: 'Result Scan Visual Checker',
            modal: true,
            closed: false,
            maximized: true,
            resizable: true,
        }).dialog('open');

        $('#dgScanVc').datagrid({
            url: '<?= base_url('control/scan_visual_checker/getSummaryVc') ?>',
            method: 'get',
            fitColumns: true,
            // queryParams: {
            //     workorder_labels: workorder_labels.join(',')
            // },
            columns: [[
                {
                    field: 'no',
                    title: 'No',
                    width: 60,
                    rowspan: 2,
                    align: 'center',
                    formatter: function (v, r, i) {
                        return r.is_total ? '' : i + 1;
                    }
                },
                {
                    field: 'item_fg_id',
                    title: 'Product ID',
                    width: 150,
                    rowspan: 2
                },
                {
                    field: 'item_fg_number',
                    title: 'Product No',
                    width: 285,
                    rowspan: 2
                },
                // {
                //     field: 'item_fg_name',
                //     title: 'Product Name',
                //     width: 300,
                //     rowspan: 2
                // },
                {
                    title: 'QTY',
                    colspan: 7,
                    align: 'center'
                },
                {
                    field: 'compound_lot_no',
                    title: 'Compound Lot No',
                    width: 210,
                    rowspan: 2,
                    formatter: function(v){
                        if(!v) return '';
                        return v.replace(/\n/g,'<br>');
                    }
                },
                {
                    title: 'Print Action',
                    colspan: 3,
                    align: 'center'
                }
            ],[
                {
                    field: 'total_qty_ok',
                    title: 'Total OK',
                    width: 120,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'std_packing',
                    title: 'Std Packing',
                    width: 150,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'total_label_rfg',
                    title: 'Label RFG',
                    width: 120,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'return_ok',
                    title: 'Return OK',
                    width: 120,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'return_original',
                    title: 'Return Original',
                    width: 150,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'total_qty_return',
                    title: 'Total Return',
                    width: 150,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'total_qty_rework',
                    title: 'Total Rework',
                    width: 150,
                    align: 'right',
                    halign: 'center',
                    formatter: numberformat
                },
                {
                    field: 'label_rfg',
                    title: 'Label RFG',
                    width: 150,
                    align: 'right',
                    halign: 'center',
                    formatter: function(v,r,i){

                        let totalOk     = parseInt(r.total_qty_ok || 0);
                        let stdPacking  = parseInt(r.std_packing || 0);
                        let totalLabel  = parseInt(r.total_label_rfg || 0);
                        let isPrintRfg  = parseInt(r.is_print_rfg || 0);

                        // tidak bisa print
                        if(
                            totalOk <= 0 || 
                            totalOk < stdPacking || 
                            totalLabel == 0
                        ){
                            return disableBtn('RFG');
                        }

                        // check double print
                        if(isPrintRfg === 1){
                            return disableBtn('RFG');
                        }

                        return enableBtn('RFG', "printLabelRfg('"+r.item_fg_id+"','"+r.scan_id+"')");
                    }

                },
                {
                    field: 'label_return',
                    title: 'Label Return',
                    width: 150,
                    align: 'right',
                    halign: 'center',
                    formatter: function(v,r,i){
                        let totalReturn   = parseInt(r.total_qty_return || 0);
                        let totalOk       = parseInt(r.total_qty_ok || 0);
                        let stdPacking    = parseInt(r.std_packing || 0);
                        let isPrintRfg    = parseInt(r.is_print_rfg || 0);
                        let isPrintReturn = parseInt(r.is_print_return || 0);

                        // tidak ada qty return
                        if(totalReturn <= 0){
                            return disableBtn('Return');
                        }

                        // check double print
                        if(isPrintReturn === 1){
                            return disableBtn('Return');
                        }

                        // EXCEPTION (boleh skip RFG)
                        let allowSkipRfg = (totalOk < stdPacking);

                        // WAJIB RFG dulu
                        if(!allowSkipRfg && isPrintRfg == 0){
                            return disableBtn('Return');
                        }

                        return enableBtn('Return', "printLabelReturn('"+r.item_fg_id+"','"+r.scan_id+"')");
                    }


                },
                {
                    field: 'label_rework',
                    title: 'Label Rework',
                    width: 180,
                    align: 'right',
                    halign: 'center',
                    formatter: function(v,r,i){

                        let totalRework     = parseInt(r.total_qty_rework || 0);
                        let isPrintRework   = parseInt(r.is_print_rework || 0);

                        if(totalRework <= 0){
                            return disableBtn('Rework');
                        }

                        // check double print
                        if(isPrintRework === 1){
                            return disableBtn('Rework');
                        }

                        return enableBtn('Rework', "printLabelRework('"+r.item_fg_id+"','"+r.scan_id+"')");
                    }

                }
            ]],

            onLoadSuccess: function (data) {
                // console.log(data);
            }
        });

    }



    function emptyRedStyler(value,row,index){
        if(value == null || String(value).trim() === ''){
            return 'color:red;background:#FFC8C8;font-weight:bold;';
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatInt(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });
        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberStyle(value, row, index) {
        if (parseFloat(value) === 0){
            return 'background-color:#FFC8C8; color: #000;';
        } else {
            return 'background-color:#C8FFCC; color: #000;';
        }
    }

    function numberStyle2(value, row, index) {
        return 'background-color:#C8FFCC; color: #000;';
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


    function hasEditingRow(){

        var dg = $('#dg');
        var rows = dg.datagrid('getRows');

        for(var i=0;i<rows.length;i++){

            var editors = dg.datagrid('getEditors', i);

            if(editors.length > 0){
                dg.datagrid('selectRow', i);
                toastr.warning('There is still data being edited. Please save first');
                return true;
            }
        }

        return false;
    }

    function hasIncompleteRequired(){

        var dg = $('#dg');
        var rows = dg.datagrid('getRows');

        for(var i=0;i<rows.length;i++){

            var r = rows[i];

            if(
                !r.operator_finishing ||
                !r.compound_lot_no ||
                r.qty_actual === '' ||
                r.qty_actual === null ||
                r.qty_ok === '' ||
                r.qty_ok === null ||
                r.qty_ok === undefined ||
                !Number(r.qty_actual) ||
                !Number(r.qty_ok)
            ){

                dg.datagrid('selectRow', i);
                toastr.warning('Please complete all required fields before completing');
                return true;
            }
        }

        return false;
    }


    // function hasEditingRow() {

    //     var dg = $('#dg');
    //     var rows = dg.datagrid('getRows');

    //     for (var i = 0; i < rows.length; i++) {

    //         var rowIndex = dg.datagrid('getRowIndex', rows[i]);

    //         if (dg.datagrid('getEditors', rowIndex).length > 0) {
    //             dg.datagrid('selectRow', rowIndex);
    //             toastr.warning('There is still selected data being edited. Please save first');

    //             return true;
    //         }
    //     }

    //     return false;
    // }

    // function hasIncompleteRequired(){

    //     var dg = $('#dg');
    //     var rows = dg.datagrid('getRows');

    //     for(var i=0;i<rows.length;i++){

    //         let r = rows[i];
    //         let rowIndex = dg.datagrid('getRowIndex', r);

    //         if(
    //             !r.operator_finishing ||
    //             !r.compound_lot_no ||
    //             r.qty_actual === '' || 
    //             r.qty_actual === null || 
    //             r.qty_ok === '' || 
    //             r.qty_ok === null || 
    //             r.qty_ok === undefined ||

    //             !Number(r.qty_actual) ||
    //             !Number(r.qty_ok)
    //         ) {

    //             dg.datagrid('selectRow',rowIndex);
    //             toastr.warning('Please complete all required fields before creating label');

    //             return true;
    //         }
    //     }

    //     return false;
    // }


    function backToMenu(){
        var token = window.location.pathname.split('/').pop();
        window.location.href = "<?= base_url('control/scan_in_wip_store/index/') ?>" + token;
    }

    $.extend($.fn.validatebox.defaults.rules, {
        greaterThanZero: {
            validator: function(value){
                return Number(value) > 0;
            },
            message: 'Value must be greater than 0'
        }
    });

    $.extend($.fn.validatebox.defaults.rules, {
        notNegative: {
            validator: function(value){
                return Number(value) >= 0;
            },
            message: 'Value cannot be negative'
        }
    });


    // function printLabelRfg(item_fg_id, scan_id){
    //     var url = "<?= base_url('control/scan_visual_checker/print_label_rfg') ?>?item_fg_id=" + item_fg_id;
    //     window.open(url, '_blank');

    //     $.post("<?= base_url('control/scan_visual_checker/setPrintRfg') ?>", {
    //         item_fg_id: item_fg_id,
    //         scan_id: scan_id
    //     }, function(res){

    //         let result = JSON.parse(res);

    //         if(result.status){
    //             // 🔹 3. reload datagrid biar button berubah
    //             $('#dgScanVc').datagrid('reload');
    //         }else{
    //             toastr.error('Failed update print status');
    //         }

    //     });
    // }


    // function printLabelRfg(item_fg_id){
    //     var url = "<?= base_url('control/scan_visual_checker/print_label_rfg') ?>?item_fg_id=" + item_fg_id;
    //     window.open(url, '_blank');
    // }

    // function printLabelReturn(item_fg_id){
    //     var url = "<?= base_url('control/scan_visual_checker/print_label_return') ?>?item_fg_id=" + item_fg_id;
    //     window.open(url, '_blank');
    // }

    // function printLabelRework(item_fg_id){
    //     var url = "<?= base_url('control/scan_visual_checker/print_label_rework') ?>?item_fg_id=" + item_fg_id;
    //     window.open(url, '_blank');
    // }


    function printLabelRfg(item_fg_id, scan_id){

        let item_fg_id_encoded = window.btoa(item_fg_id);
        let scan_id_encoded = window.btoa(scan_id);

        window.open("<?= base_url('control/scan_visual_checker/print_label_rfg/') ?>"+item_fg_id_encoded+"/"+scan_id_encoded);

        setTimeout(function(){
            updatePrintRfg(item_fg_id, scan_id);
        }, 1000);
    }

    function updatePrintRfg(item_fg_id, scan_id){
        $.post("<?= base_url('control/scan_visual_checker/setPrintRfg') ?>", {
            item_fg_id: item_fg_id,
            scan_id: scan_id
        }, function(res){

            let result = JSON.parse(res);

            if(result.status){
                $('#dgScanVc').datagrid('reload');
            } else if(result.affected == 0){
                toastr.warning('Label already printed!');
            } else {
                toastr.error('Failed update print status');
            }
        });
    }


    function printLabelReturn(item_fg_id, scan_id){

        let item_fg_id_encoded = window.btoa(item_fg_id);
        let scan_id_encoded = window.btoa(scan_id);

        window.open("<?= base_url('control/scan_visual_checker/print_label_return/') ?>"+item_fg_id_encoded+"/"+scan_id_encoded);

        setTimeout(function(){
            updatePrintReturn(item_fg_id, scan_id);
        }, 1000);
    }

    function updatePrintReturn(item_fg_id, scan_id){
        $.post("<?= base_url('control/scan_visual_checker/setPrintReturn') ?>", {
            item_fg_id: item_fg_id,
            scan_id: scan_id
        }, function(res){

            let result = JSON.parse(res);

            if(result.status){
                $('#dgScanVc').datagrid('reload');
            } else if(result.affected == 0){
                toastr.warning('Label already printed!');
            } else {
                toastr.error('Failed update print status');
            }
        });
    }


    function printLabelRework(item_fg_id, scan_id){

        let item_fg_id_encoded = window.btoa(item_fg_id);

        window.open("<?= base_url('control/scan_visual_checker/print_label_rework/') ?>"+item_fg_id_encoded);

        setTimeout(function(){
            updatePrintRework(item_fg_id, scan_id);
        }, 1000);
    }

    function updatePrintRework(item_fg_id, scan_id){
        $.post("<?= base_url('control/scan_visual_checker/setPrintRework') ?>", {
            item_fg_id: item_fg_id,
            scan_id: scan_id
        }, function(res){

            let result = JSON.parse(res);

            if(result.status){
                $('#dgScanVc').datagrid('reload');
            } else if(result.affected == 0){
                toastr.warning('Label already printed!');
            } else {
                toastr.error('Failed update print status');
            }
        });
    }


    function disableBtn(label){
        return '<div style="text-align:center;">'
            +'<a href="javascript:void(0)" style="color:#999;text-decoration:none;font-size:14px !important;cursor:not-allowed; color: #ff0000 !important;">'
            +'<i class="fa fa-print" style="font-size:14px !important;"></i> '+label
            +'</a></div>';
    }

    function enableBtn(label, onclick){
        return '<div style="text-align:center;">'
            +'<a href="javascript:void(0)" onclick="'+onclick+'" style="color:black;text-decoration:none;font-size:14px !important; color: #3fc4ff !important;">'
            +'<i class="fa fa-print" style="font-size:14px !important;"></i> '+label
            +'</a></div>';
    }




    // function printLabelRfg(item_fg_id){

    //     var form = $('<form>',{
    //         action: '<?= base_url("control/scan_visual_checker/print_label_rfg") ?>',
    //         method: 'POST',
    //         target: '_blank'
    //     });

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'item_fg_id',
    //         value:item_fg_id
    //     }));

    //     $('body').append(form);

    //     form.submit();
    //     form.remove();
    // }

    // function printLabelRfg(item_fg_id){
    //     var rows = $('#dg').datagrid('getChecked');

    //     if(rows.length === 0){
    //         return toastr.error("No data selected");
    //     }

    //     var workorder_labels = [];

    //     $.each(rows, function(i,r){
    //         workorder_labels.push(r.workorder_label);
    //     });

    //     var form = $('<form>',{
    //         action: '<?= base_url("control/scan_visual_checker/print_label_rfg") ?>',
    //         method: 'POST',
    //         target: '_blank'
    //     });

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'item_fg_id',
    //         value:item_fg_id
    //     }));

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'workorder_labels',
    //         value: JSON.stringify(workorder_labels)
    //     }));

    //     $('body').append(form);

    //     form.submit();
    //     form.remove();
    // }

    // function printLabelReturn(item_fg_id){

    //     var rows = $('#dg').datagrid('getChecked');

    //     if(rows.length === 0){
    //         return toastr.error("No data selected");
    //     }

    //     var workorder_labels = [];

    //     $.each(rows, function(i,r){
    //         workorder_labels.push(r.workorder_label);
    //     });

    //     var form = $('<form>',{
    //         action: '<?= base_url("control/scan_visual_checker/print_label_return") ?>',
    //         method: 'POST',
    //         target: '_blank'
    //     });

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'item_fg_id',
    //         value:item_fg_id
    //     }));

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'workorder_labels',
    //         value: JSON.stringify(workorder_labels)
    //     }));

    //     $('body').append(form);

    //     form.submit();

    //     form.remove();
    // }

    // function printLabelRework(item_fg_id){

    //     var rows = $('#dg').datagrid('getChecked');

    //     if(rows.length === 0){
    //         return toastr.error("No data selected");
    //     }

    //     var workorder_labels = [];

    //     $.each(rows, function(i,r){
    //         workorder_labels.push(r.workorder_label);
    //     });

    //     var form = $('<form>',{
    //         action: '<?= base_url("control/scan_visual_checker/print_label_rework") ?>',
    //         method: 'POST',
    //         target: '_blank'
    //     });

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'item_fg_id',
    //         value:item_fg_id
    //     }));

    //     form.append($('<input>',{
    //         type:'hidden',
    //         name:'workorder_labels',
    //         value: JSON.stringify(workorder_labels)
    //     }));

    //     $('body').append(form);

    //     form.submit();

    //     form.remove();
    // }


    function saveSummaryScanVc() {
        var check_date      = $("#check_date").datebox('getValue');
        var inspector       = $("#inspector").combogrid('getValue');
        var visual_process  = $("#visual_process").combobox('getValue');
        var customer        = $("#customer").combogrid('getValue');

        if (check_date == "" || inspector == "" || visual_process == "") {
            toastr.warning('Scan Visual Checkcer header is required');
            return;
        }

        var rows = $('#dg').datagrid('getRows');
        if (rows.length === 0) {
            toastr.warning('Data Not Found!');
            return;
        }

        // console.log('ROWS : ', rows);


        let items = [];

        rows.forEach(row => {
            if (row.item_fg_id) {
                items.push({
                    check_date: check_date,
                    inspector: inspector,
                    visual_process: visual_process,
                    customer: customer ?? '',

                    parent_id: row.visual_checker_id,
                    detail_id: row.id,
                    scan_id: row.scan_id,
                    item_fg_id: row.item_fg_id,
                    workorder: row.workorder,
                    workorder_label: row.workorder_label,
                });
            }
        });

        if (items.length === 0) {
            toastr.error("No data to save");
            return;
        }

        console.log('ITEMS : ', items);

        var rowsScan = $('#dgScanVc').datagrid('getRows');

        for (let i = 0; i < rowsScan.length; i++) {
            let r = rowsScan[i];

            if (r.is_total) continue;

            if (!isRowValidForFinish(r)) {
                toastr.warning('All labels (RFG, Return, and Rework) must be printed before finishing.');
                return;
            }
        }

        Swal.fire({
            title: 'Confirm Save',
            text: 'Are you sure you want to save this incoming data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: '<?= base_url('control/scan_visual_checker/saveSummaryScanVc') ?>',
                    data: { items: items },
                    dataType: 'json',
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Saving...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        Swal.close();

                        if (res.theme === "success") {
                            Swal.fire({
                                title: res.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                allowOutsideClick: false,
                            }).then(() => {
                                $('#dg').datagrid('reload');
                                $('#dlgScanVc').dialog('close');
                                window.location.reload();
                            });
                        } else {
                            toastr.error(res.message, res.title || "Error");
                        }
                    },
                    error: function () {
                        Swal.close();
                        toastr.error("Server error while saving");
                    }
                });

            }
        });

    }


    function isRowValidForFinish(r) {

        let totalOk         = parseInt(r.total_qty_ok || 0);
        let stdPacking      = parseInt(r.std_packing || 0);
        let totalLabel      = parseInt(r.total_label_rfg || 0);
        let totalReturn     = parseInt(r.total_qty_return || 0);
        let totalRework     = parseInt(r.total_qty_rework || 0);

        let isPrintRfg      = parseInt(r.is_print_rfg || 0);
        let isPrintReturn   = parseInt(r.is_print_return || 0);
        let isPrintRework   = parseInt(r.is_print_rework || 0);

        // RFG
        let needRfg = !(totalOk <= 0 || totalOk < stdPacking || totalLabel == 0);

        if (needRfg && isPrintRfg === 0) {
            return false;
        }

        // RETURN
        let allowSkipRfg = (totalOk < stdPacking);
        let needReturn = totalReturn > 0;

        if (needReturn) {
            if (!allowSkipRfg && isPrintRfg === 0) {
                return false;
            }
            if (isPrintReturn === 0) {
                return false;
            }
        }

        // REWORK
        let needRework = totalRework > 0;

        if (needRework && isPrintRework === 0) {
            return false;
        }

        return true;
    }


</script>