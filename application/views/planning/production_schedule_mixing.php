<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family: Inter, Segoe UI, sans-serif;
    }
    body{
        background:#f7f9fc;
        padding:0 10px;
    }

    .wrapper{
        background:#fff;
        border:1px solid #e6ebf2;
        border-radius:24px;
        overflow:hidden;
        box-shadow: 0 4px 20px rgba(15,23,42,.03);
    }

    .toolbar{
        display:flex;
        justify-content:space-between;
        align-items:center;

        min-height:52px;
        padding:10px;

        position:sticky;
        top:0;

        z-index:1000;

        background:#f7f9fc;
    }

    .toolbar-left,
    .toolbar-right{
        display:flex;
        align-items:center;
    }

    .toolbar-left{
        gap:24px;
    }

    .toolbar-right{
        gap:32px;
    }

    .filter-item{
        display:flex;
        align-items:center;
        gap:8px;
    }

    .filter-item label{
        font-size:14px;
        font-weight:600;
        color:#64748b;
        white-space:nowrap;
    }

    .filter-item select{
        height:36px;
        min-width:180px;

        border:1px solid #dbe3ef;
        border-radius:10px;

        padding:0 12px;
        background:#fff;
    }

    .top-bar{
        display:flex;
        justify-content:flex-end;
        gap:30px;
        padding:12px 5px;
    }

    .status-filter{
        display:flex;
        align-items:center;
        gap:10px;
        cursor:pointer;
        user-select:none;

        color:#64748b;
        font-size:14px;
        font-weight:600;
    }

    .status-filter input{
        display:none;
    }

    .status-pill{
        width:34px;
        height:18px;
        border-radius:999px;
        position:relative;
        transition:all .25s ease;
    }

    .status-pill::before{
        content:'';
        width:12px;
        height:12px;
        border-radius:50%;
        position:absolute;
        top:3px;
        left:3px;
        transition:all .25s ease;
    }

    .status-filter input:checked + .status-pill::before{
        transform:translateX(16px);
    }

    .status-filter input:not(:checked) + .status-pill{
        background:#e5e7eb !important;
    }

    .status-filter input:not(:checked) + .status-pill::before{
        background:#94a3b8 !important;
        transform:translateX(0);
    }

    .status-open-pill{
        background:#dcfce7;
    }

    .status-open-pill::before{
        background:#22c55e;
    }

    .status-progress-pill{
        background:#dbeafe;
    }

    .status-progress-pill::before{
        background:#2563eb;
    }

    .status-closed-pill{
        background:#fee2e2;
    }

    .status-closed-pill::before{
        background:#ef4444;
    }

    .status-released-pill{
        background:#FFE8CC;
    }

    .status-released-pill::before{
        background:#FF7A00;
    }

    .status-filter input:not(:checked) + .status-pill{
        opacity:.25;
    }


    .table-container{
        height: calc(100vh - 123px);
        overflow: auto;
        position: relative;
    }

    .table-container table{
        border-collapse:separate;
        border-spacing:0;
        table-layout:fixed;
    }

    .table-container th{
        background:#fff;
        color:#1e293b;
        font-size:16px;
        font-weight:700;
        border:0.5px solid #e8edf5;
        text-align:center;
        padding:14px;
    }

    .table-container td{
        border:0.5px solid #e8edf5;
        height:122px;
        vertical-align:top;
        padding:10px;
        background:#fff;
    }

    .month-header{
        font-size:20px;
        font-weight:700;
        color:#1e3a8a;
    }

    .sub-header{
        font-size:14px;
        color:#64748b;
    }

    .center{
        text-align:center;
    }

    .card{
        border-radius:12px;
        padding:12px 14px;

        display:flex;
        flex-direction:column;

        justify-content:space-between;
        align-items:center;

        text-align:left;

        width:100%;
        height:100px;

        box-sizing:border-box;
        overflow:hidden;

        cursor:pointer;
    }

    .card:hover{
        transform:translateY(-2px);
    }

    .card-code{
        font-size:14px !important;
        font-weight:700;
        letter-spacing:.3px;

        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        width:100%;
    }

    .card-qty{
        font-size:18px !important;
        font-weight:800;
        line-height:1;
    }

    .card-status{
        display:flex;
        align-items:center;
        gap:6px;

        font-size:13px !important;
        font-weight:600;
    }

    .card-status::before{
        content:'';
        width:8px;
        height:8px;
        border-radius:50%;
    }

    .status-open .card-status::before{
        background:#2e7d32;
    }

    .status-progress .card-status::before{
        background:#2563eb;
    }

    .status-closed .card-status::before{
        background:#dc2626;
    }

    .status-open{
        background:#edf9ec;
        border:2px solid #a7d9a4;
    }

    .status-open .card-code,
    .status-open .card-qty,
    .status-open .card-status,
    .status-open .merge-top-qty{
        color:#2e7d32;
    }

    .status-progress{
        background:#eef4ff;
        border:2px solid #b8d0ff;
    }

    .status-progress .card-code,
    .status-progress .card-qty,
    .status-progress .card-status,
    .status-progress .merge-top-qty{
        color:#2563eb;
    }

    .status-closed{
        background:#fff0f1;
        border:2px solid #ffb4bb;
    }

    .status-closed .card-code,
    .status-closed .card-qty,
    .status-closed .card-status,
    .status-closed .merge-top-qty{
        color:#dc2626;
    }

    .status-released{
        background:#FFF4E8;
        border:2px solid #FFB869;
    }

    .status-released .card-code,
    .status-released .card-qty,
    .status-released .card-status,
    .status-released .merge-top-qty{
        color:#F97316;
    }

    .status-released .card-status::before{
        background:#F97316;
    }

    /* #calendar-footer td{
        text-align:center;
        vertical-align:middle !important;
    } */

    tfoot td{
        height:60px !important;
        text-align:center;
        font-size:16px;
        font-weight:700;
        color:#1e293b;
    }

    .footer{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:18px 20px;
        color:#64748b;
    }

    .unit-select{
        padding:8px 12px;
        border-radius:10px;
        border:1px solid #dbe3ef;
    }


    .no-col{
        width:60px !important;
        min-width:60px !important;
        max-width:60px !important;
        z-index: 100 !important;
    }

    .day-col{
        width:200px;
        min-width:200px;
        max-width:200px;
    }

    .compound-col{
        width:230px !important;
        min-width:230px !important;
        z-index: 100 !important;
    }

    .table-container{
        overflow:auto;
        position:relative;
    }

    #calendar-header th:nth-child(1),
    #tbody td:nth-child(1){
        position:sticky;
        left:0;
        z-index:30;
        background:#fff;
    }

    #calendar-header th:nth-child(2),
    #tbody td:nth-child(2){
        position:sticky;
        left:60px;
        z-index:29;
        background:#fff;
    }

    #tbody td:not(:nth-child(1)):not(:nth-child(2)){
        width:200px;
        min-width:200px;
        max-width:200px;
    }

    #calendar-header tr:first-child th{
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 20;
    }

    #calendar-header tr:nth-child(2) th{
        position: sticky;
        top: 47px;
        background: #fff;
        z-index: 19;
    }

    #tbody td:nth-child(1),
    #tbody td:nth-child(2){
        overflow:hidden !important;
    }

    /* #calendar-footer td:first-child{
        position:sticky;
        left:0;
        z-index:20;
        background:#f8fafc;
    } */


    .compound-info{
        display:flex;
        flex-direction:column;
        gap:5px;
        text-align:left;
    }

    .compound-id-text{
        font-size:18px;
        font-weight:700;
        color:#1e293b;
    }

    .compound-no-text{
        font-size:14px;
        color:#64748b;
    }

    .compound-mpq{
        font-size:14px;
        color:#64748b;
    }

    .compound-dot{
        width:10px;
        height:10px;
        border-radius:50%;
        background:#2563eb;
        margin-top:6px;
        flex-shrink:0;
    }

    .compound-row{
        display:flex;
        gap:12px;
        align-items:flex-start;
    }

    .btn-convert{
        height:34px;
        padding:0 16px;

        display:flex;
        align-items:center;
        gap:8px;

        border:1px solid rgba(37,99,235,.15);
        border-radius:10px;

        background:#2563eb;
        color:#fff;

        font-size:13px;
        font-weight:600;

        cursor:pointer;
        transition:.25s;
    }

    .btn-convert:hover{
        background:#1d4ed8;
    }

    .btn-convert i{
        font-size:14px;
    }

    .btn-refresh{
        width:34px;
        height:34px;

        border:1px solid rgba(206, 220, 249, 0.3);
        border-radius:50%;

        background:#eef4ff;
        color:#2563eb;

        box-shadow: 0 2px 8px rgba(37,99,235,.12);

        cursor:pointer;

        display:flex;
        align-items:center;
        justify-content:center;

        font-size:16px;

        transition:all .25s ease;
    }

    .btn-refresh:hover{
        background:#dbeafe;
        border-color:rgba(206, 220, 249, 0.3);
        transform:rotate(180deg);
    }

    .weekend-header{
        color:#dc2626 !important;
    }

    .weekend-header .sub-header{
        color:#dc2626 !important;
    }

    tfoot td{
        font-size:14px !important;
        font-weight:700;
        color:#1e293b;
        background:#f8fafc;
    }

    .schedule-popup{
        text-align:left;
    }

    .popup-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:14px;
        border-bottom: 1px solid #f4f4f4;
        padding-bottom: 10px;
    }

    .popup-title{
        font-size:20px !important;
        font-weight:700;
        color:#1e293b;
    }

    .popup-compound{
        padding:5px 10px;
        border:1px solid #dbeafe;
        border-radius:12px;
        color:#2563eb;
        font-weight:600;
    }

    .popup-body{
        display:grid;
        grid-template-columns:350px 1fr;
        gap:25px;
    }

    .popup-left{
        border-right:1px solid #e5e7eb;
        padding-right:20px;
    }

    .qty-row{
        display:flex;
        align-items:center;
        gap:10px;
        margin:15px 0 25px;
    }

    .qty-big{
        font-size:24px !important;
        font-weight:700;
    }

    .info-grid{
        display:grid;
        grid-template-columns:140px 1fr;
        gap:14px;
        align-items: center;
    }

    .popup-right table{
        width:100%;
        border-collapse:collapse;
        margin-top:15px;
    }

    .popup-right th{
        background:#f8fafc;
    }

    .popup-table{
        width:100%;
        border-collapse:collapse;
        table-layout:auto !important;
    }

    .popup-table th{
        background:#f8fafc;
        border:1px solid #e5e7eb;
        padding:12px;
        text-align:center;

        height:auto !important;
        min-height:auto !important;
    }

    .popup-table td{
        border:1px solid #e5e7eb;
        padding:12px;

        height:auto !important;
        min-height:auto !important;

        vertical-align:middle;
    }

    .popup-right th,
    .popup-right td{
        border:1px solid #e5e7eb;
        padding:12px;
    }

    .summary-box{
        margin-top:20px;
        margin-left:auto;
        width:350px;

        display:grid;
        grid-template-columns:1fr 140px;

        border:1px solid #e5e7eb;
    }

    .summary-box div{
        padding:12px;
        border-bottom:1px solid #e5e7eb;
    }

    .summary-box div:nth-child(even){
        text-align:right;
        font-weight:700;
        color:#2563eb;
    }

    .popup-close{
        width:30px;
        height:30px;

        border:none;
        border-radius:10px;

        background:#f8fafc;

        color:#64748b;
        font-size:22px;

        cursor:pointer;

        transition:.2s;
    }

    .popup-close:hover{
        background:#e2e8f0;
        color:#1e293b;
    }

    .swal2-content {
        padding: 5px !important;
    }

    .swal2-content .title-header {
        font-size: 18px;
        font-weight: 700;
    }

    .popup-info{
        margin-top:20px;

        display:flex;
        align-items:center;
        gap:12px;

        padding:10px 12px;

        background:#f8fbff;
        border:1px solid #dbeafe;
        border-radius:10px;

        color:#64748b;
        font-size:13px;
        line-height:1.5;
    }

    .popup-info-icon{
        width:24px;
        height:24px;

        border-radius:50%;

        background:#2563eb;
        color:#fff;

        display:flex;
        align-items:center;
        justify-content:center;

        font-size:12px;
        font-weight:700;

        flex-shrink:0;
    }


    #filter_item_fg + .select2-container{
        min-width:220px !important;
        width:220px !important;
    }

    /* #filter_export_excel + .select2-container{
        min-width:150px !important;
        width:150px !important;
    } */

    #filter_period + .select2-container{
        width:180px !important;
        min-width:180px !important;
    }

    .select2-container--default .select2-selection--single {
        height: 40px !important;
        border: 1px solid #dbe3ef !important;
        border-radius: 12px !important;
        background: #fff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 1px 2px rgba(15,23,42,.03);
        transition: all .2s ease;
    }

    .select2-container--default .select2-selection--single:hover {
        border-color: #cbd5e1 !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 4px rgba(37,99,235,.08) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
        color: #334155 !important;
        font-size: 13px !important;
        font-weight: 500;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 8px !important;
    }

    .select2-dropdown {
        border: 1px solid #dbe3ef !important;
        border-radius: 14px !important;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15,23,42,.12);
    }

    .select2-search--dropdown {
        padding: 10px !important;
        background: #f8fafc;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #dbe3ef !important;
        border-radius: 10px !important;
        height: 38px !important;
        padding: 0 12px !important;
        outline: none;
    }

    .select2-results__option {
        padding: 10px 14px !important;
    }

    .select2-results__option--highlighted {
        background: #eef4ff !important;
        color: #2563eb !important;
    }

    .select2-results__option[aria-selected=true] {
        background: #dbeafe !important;
        color: #1d4ed8 !important;
    }

    .empty-state{
        /* min-height:180px; */
        height: calc(100vh - 248px);
        padding:32px;

        display:flex;
        align-items:center;
        gap:16px;
    }

    .empty-icon{
        width:52px;
        height:52px;
        border-radius:12px;
        background:#f8fafc;
        border:1px solid #e2e8f0;

        display:flex;
        align-items:center;
        justify-content:center;

        flex-shrink:0;
    }

    .empty-icon i{
        font-size:22px;
        color:#94a3b8;
    }

    .empty-content{
        display:flex;
        flex-direction:column;
        gap:4px;
    }

    .empty-title{
        font-size:18px;
        font-weight:700;
        color:#334155;
    }

    .empty-subtitle{
        font-size:14px;
        color:#64748b;
    }


    .convert-popup{
        text-align:left;
    }

    .convert-body{
        padding:10px 0;
    }

    .form-group{
        margin-bottom:22px;
    }

    .form-group label{
        display:block;
        margin-bottom:8px;
        font-size:13px !important;
        font-weight:600;
        color:#334155;
    }

    .date-range{
        display:flex;
        align-items:center;
        gap:14px;
    }

    .range-separator{
        font-size:13px !important;
        font-weight:600;
        color:#64748b;
    }

    .form-input{
        flex:1;
        height:42px;
        padding:0 14px;
        border:1px solid #dbe3ef;
        border-radius:12px;
        font-size:13px !important;
        background:#fff;
        transition:.2s;
    }

    .form-input:focus{
        outline:none;
        border-color:#2563eb;
        box-shadow:0 0 0 4px rgba(37,99,235,.08);
    }

    .popup-footer{
        margin-top:24px;
        display:flex;
        justify-content:flex-end;
    }

    .date-input{
        position:relative;
        flex:1;
    }

    .form-input-date{
        width:100%;
        height:42px !important;
        padding:0 42px 0 14px;
        border:1px solid #dbe3ef;
        border-radius:12px;
        background:#fff !important;
        color:#334155 !important;
        font-size:13px !important;
        font-weight:500 !important;
        transition:all .2s ease !important;
        box-shadow:0 1px 2px rgba(15,23,42,.03) !important;
    }

    .form-input-date:hover{
        border-color:#cbd5e1;
    }

    .form-input-date:focus{
        outline:none;
        border-color:#2563eb;
        box-shadow:0 0 0 4px rgba(37,99,235,.08);
    }

    .date-input i{
        position:absolute;
        right:14px;
        top:50%;
        transform:translateY(-50%) !important;
        color:#2563eb;
        font-size:15px;
        pointer-events:none;
    }

    .form-input-date::-webkit-calendar-picker-indicator{
        opacity:0;
        position:absolute;
        right:0;
        top:0;
        width:100%;
        height:100%;
        cursor:pointer;
    }

    .card{
        display:flex;
        flex-direction:column;
        padding:0;
        overflow:hidden;
    }

    .merge-top-row{
        width:100%;
        display:grid;
        flex:0 0 auto;
        align-items:center;
        justify-items:center;
        min-height:40px;
        border-bottom:1px solid rgba(249,115,22,.2);
        padding:12px 0;
    }

    .merge-top-qty{
        text-align:center;
        font-size:16px !important;
        font-weight:700;
    }

    /* .card-body{
        flex:1;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        padding:12px 14px;
    } */

    .card-body{
        display:grid;
        grid-template-columns:1fr 1fr 1fr;
        align-items:center;
        width:100%;
    }

    .merge-col{
        display:flex;
        justify-content:center;
        align-items:center;
        height:100%;
    }

    .merge-col:not(:last-child){
        border-right:1px solid rgba(249,115,22,.2);
    }

    .simulation-summary{
        margin-top:20px;
        border:1px solid #dbeafe;
        border-radius:14px;
        background:#f8fbff;
        padding:18px;
    }

    .simulation-title{
        font-size:16px;
        font-weight:700;
        color:#2563eb;
        margin-bottom:18px;
    }

    .simulation-grid{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:14px;
    }

    .simulation-card{
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:14px;
    }

    .simulation-label{
        font-size:12px;
        color:#64748b;
        margin-bottom:8px;
    }

    .simulation-value{
        font-size:22px;
        font-weight:700;
        color:#1e293b;
    }

    .simulation-value.primary{
        color:#2563eb;
    }

    .simulation-value.danger{
        color:#dc2626;
    }

    #simulationPanel{
        animation:fadeSimulation .25s ease;
    }

    @keyframes fadeSimulation{
        from{
            opacity:0;
            transform:translateY(8px);
        }
        to{
            opacity:1;
            transform:none;
        }
    }

    .simulation-batch{
        margin-top:18px;
        background:#fff;
        border:1px solid #dbeafe;
        border-radius:12px;
        overflow:hidden;
    }

    .simulation-batch-header{
        padding:12px 16px;
        background:#eff6ff;
        color:#2563eb;
        font-weight:700;
    }

    .simulation-day{
        display:flex;
        justify-content:space-between;
        padding:10px 16px;
        border-bottom:1px solid #f1f5f9;
    }

    .simulation-total{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        background:#f8fafc;
    }

    .simulation-total div{
        padding:16px;
        text-align:center;
    }

    .simulation-total b{
        display:block;
        margin-top:8px;
        font-size:18px;
    }

    .btn-detail{
        border:none;
        background:#2563eb;
        color:#fff;
        border-radius:8px;
        padding:8px 14px;
        cursor:pointer;
        font-size:12px;
    }

    .simulation-detail{
        padding:15px;
        background:#fff;
    }

    .simulation-table{
        width:100%;
        border-collapse:collapse;
    }

    .simulation-table th{
        background:#f8fafc;
        border:1px solid #e2e8f0;
        padding:10px;
        font-size:12px;
    }

    .simulation-table td{
        border:1px solid #e2e8f0;
        padding:10px;
        font-size:12px;
    }

    .simulation-batch-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .btn-cancel-simulation{
        height:36px;
        padding:0 18px;
        border:none;
        border-radius:10px;
        background:#fee2e2;
        color:#dc2626;
        font-size:13px;
        font-weight:600;
        cursor:pointer;
        transition:.2s;
        margin-right: 10px;
    }

    .btn-cancel-simulation:hover{
        background:#fecaca;
    }

    .single-card{
        width:100%;
        height:100%;

        display:flex;
        flex-direction:column;
    }

    .single-card-code{
        font-size:14px !important;
        font-weight:700;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .single-card-spacer{
        flex:1;
    }

    .single-card-qty{
        font-size:18px;
        font-weight:800;
        line-height:1.2;
        margin-bottom:8px;
    }

    .single-card-status{
        display:flex;
        align-items:center;
        gap:6px;
        font-size:14px;
        font-weight:600;
    }
</style>

<div class="toolbar">
    <div class="toolbar-left">
        <div class="filter-item">
            <label>Period</label>
            <select id="filter_period"></select>
        </div>

        <div class="filter-item">
            <label>Compound No</label>
            <select id="filter_item_fg"></select>
        </div>

        <!-- <div class="filter-item">
            <label>Export Excel</label>
            <select id="filter_export_excel">
                <option value="">Choose Data</option>
                <option value="export_recap">Export Recap</option>
                <option value="export_detail">Export Detail</option>
            </select>
        </div> -->

        <button class="btn-convert" onclick="convertSchedule()">
            <i class="fa fa-random"></i>
            Convert
        </button>

        <button class="btn-refresh" onclick="window.location.reload();">
            <i class="fa fa-refresh"></i>
        </button>
    </div>

    <div class="toolbar-right">
        <label class="status-filter">
            <input type="checkbox" class="filter-status" value="open" checked>
            <span class="status-pill status-open-pill"></span>
            Open
        </label>

        <label class="status-filter">
            <input type="checkbox" class="filter-status" value="released" checked>
            <span class="status-pill status-released-pill"></span>
            Released
        </label>

        <label class="status-filter">
            <input type="checkbox" class="filter-status" value="progress" checked>
            <span class="status-pill status-progress-pill"></span>
            On Progress
        </label>

        <label class="status-filter">
            <input type="checkbox" class="filter-status" value="closed" checked>
            <span class="status-pill status-closed-pill"></span>
            Closed
        </label>
    </div>
</div>

<div class="wrapper">
    <div class="table-container">
        <table>
            <thead id="calendar-header"></thead>
            <tbody id="tbody"></tbody>
            <!-- <tfoot id="calendar-footer"></tfoot> -->
        </table>
    </div>

    <div class="footer">
        <div id="footer-total">
            Showing 0 to 0 of 0 compounds
        </div>

        <!-- <div>
            Unit
            <select class="unit-select">
                <option>Kg</option>
            </select>
        </div> -->
    </div>
</div>

<script>
    $(document).on('click','.card',function(){
        loadDetailSchedule({
            header_id : $(this).data('header-id'),
            item_rm_id : $(this).data('item-rm-id'),
            wp_date    : $(this).data('wp-date'),
            convert_id  : $(this).data('convert-id')
        });
    });

    function getStatusBadge(status){
        const map = {
            open : {
                text : 'Open',
                bg   : '#dcfce7',
                color: '#15803d'
            },
            released:{
                text:'Released',
                bg:'#FFE8CC',
                color:'#F97316'
            },
            progress : {
                text : 'On Progress',
                bg   : '#dbeafe',
                color: '#2563eb'
            },
            closed : {
                text : 'Closed',
                bg   : '#fee2e2',
                color: '#dc2626'
            }
        };

        const s = map[status];

        return `
            <span style="
                background:${s.bg};
                color:${s.color};
                padding:4px 12px;
                border-radius:999px;
                font-size:12px;
                font-weight:700;
            ">
                ${s.text}
            </span>
        `;
    }

    let convertSimulation = null;
    let simulationMode = false;

    function convertSchedule() {
        convertSimulation = null;
        simulationMode = false;

        Swal.fire({
            width: '900px',
            showConfirmButton: false,
            allowOutsideClick: false,
            willClose: function () {
                cancelSimulation();
            },
            html: `
                <div class="convert-popup">

                    <div class="popup-header">
                        <div class="popup-title">
                            Convert to Workorder
                        </div>

                        <button class="popup-close" onclick="Swal.close()">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div class="convert-body">

                        <div class="form-group">
                            <label>WP Mix Date</label>

                            <div class="date-range">
                                <div class="date-input">
                                    <i class="fa fa-calendar"></i>
                                    <input type="date" id="wp_mix_date_from" class="form-input-date" value="<?= date('Y-m-d') ?>">
                                </div>

                                <span class="range-separator">To</span>

                                <div class="date-input">
                                    <i class="fa fa-calendar"></i>
                                    <input type="date" id="wp_mix_date_to" class="form-input-date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Compound No</label>
                            <select id="popup_item_fg" style="width:100%"></select>
                        </div>

                        <div class="form-group">
                            <label>Convert Method</label>

                            <select id="convert_method" style="width:100%">
                                <option value="round_mpq_moq">Round by MPQ/MOQ</option>
                                <option value="merge_wp_round">Merge WP and Round</option>
                            </select>
                        </div>

                        <div>
                    </div>

                    <div
                        id="simulationPanel"
                        style="
                            display:none;
                            margin-top:20px;
                        ">
                    </div>

                    <div class="popup-footer">
                        <button class="btn-cancel-simulation" id="btnCancelSimulation" style="display:none">
                            <i class="fa fa-times"></i>
                            Cancel Simulation
                        </button>

                        <button class="btn-convert" id="btnPreviewConvert">
                            <i class="fa fa-search"></i>
                            Preview
                        </button>
                    </div>

                </div>
            `,
            didOpen: function () {

                $('#btnCancelSimulation').on('click',function(){
                    cancelSimulation();
                    simulationMode = false;
                    convertSimulation = null;
                    $('#btnPreviewConvert')
                        .html('<i class="fa fa-search"></i> Preview');
                });

                $('#popup_item_fg').select2({
                    dropdownParent: $('.swal2-popup'),
                    placeholder: 'Choose Compound No',
                    allowClear: false,
                    ajax: {
                        url: '<?= base_url("master/item_fg/readCompounds") ?>',
                        type: 'POST',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term || '' };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data.rows || data, function(item){
                                    return {
                                        id: item.id,
                                        text: item.number,
                                        number: item.number,
                                        name: item.name
                                    };
                                })
                            };
                        }
                    },

                    templateResult: function(item){

                        if(!item.id) return item.text;

                        return $(`
                            <div>
                                <div style="font-weight:700">
                                    ${item.number}
                                </div>

                                <div style="
                                    font-size:12px;
                                    color:#64748b;
                                ">
                                    ${item.name}
                                </div>
                            </div>
                        `);

                    },

                    templateSelection: function(item){
                        return item.number || item.text;
                    }

                });

                $('#convert_method').select2({
                    dropdownParent: $('.swal2-popup'),
                    minimumResultsForSearch: Infinity,
                    width: '100%'
                });

                $('#btnPreviewConvert').on('click', function(){
                    if(simulationMode){
                        startConvert();
                        return;
                    }else{
                        previewConvert();
                    }
                });

            }

        });

    }

    function previewConvert(){
        const from = $('#wp_mix_date_from').val();
        const to = $('#wp_mix_date_to').val();
        const item_fg_id = $('#popup_item_fg').val();
        const method = $('#convert_method').val();

        if(!from || !to){
            toastr.warning('WP Mix Date is required');
            return;
        }

        if(!item_fg_id){
            toastr.warning('Compound No is required');
            return;
        }

        $('#btnPreviewConvert')
            .prop('disabled',true)
            .html('<i class="fa fa-spinner fa-spin"></i> Previewing...');

        $.ajax({
            url:'<?= base_url("planning/production_schedule_mixing/previewConvert") ?>',
            type:'POST',
            dataType:'json',
            data:{
                from,
                to,
                item_fg_id,
                method
            },
            success:function(res){
                $('#btnPreviewConvert')
                    .prop('disabled',false);

                if(!res.success){
                    $('#btnPreviewConvert')
                        .html('<i class="fa fa-search"></i> Preview');
                    toastr.error(res.message);
                    return;
                }

                convertSimulation = res.simulation;
                // console.log('CONVERT : ', convertSimulation);
                
                simulationMode = true;
                renderSimulation();
                $('#btnCancelSimulation').show();
                $('#btnPreviewConvert')
                    .html('<i class="fa fa-play"></i> Start');
            },

            error:function(xhr){
                $('#btnPreviewConvert')
                    .prop('disabled',false)
                    .html('<i class="fa fa-search"></i> Preview');

                toastr.error('Preview failed');
                console.log(xhr.responseText);

            }

        });
    }

    function renderSimulation(){
        const summary = convertSimulation.summary;

        $('#simulationPanel').show().html(`

            <div class="simulation-summary">
                <div class="simulation-title">
                    <i class="fa fa-calculator"></i>
                    Simulation Result
                </div>

                <div class="simulation-grid">

                    <div class="simulation-card">
                        <div class="simulation-label">
                            Total Need
                        </div>

                        <div class="simulation-value">
                            ${Number(summary.total_need).toFixed(2)} Kg
                        </div>
                    </div>

                    <div class="simulation-card">
                        <div class="simulation-label">
                            Planning Qty
                        </div>

                        <div class="simulation-value">
                            ${Number(summary.total_planning).toFixed(0)} Kg
                        </div>
                    </div>

                    <div class="simulation-card">
                        <div class="simulation-label">
                            Waste
                        </div>

                        <div class="simulation-value danger">
                            ${Number(summary.total_waste).toFixed(2)} Kg
                        </div>
                    </div>

                    <div class="simulation-card">
                        <div class="simulation-label">
                            Total Batch
                        </div>

                        <div class="simulation-value primary">
                            ${summary.batch_count}
                        </div>
                    </div>
                </div>

                <div id="simulationBatchContainer"></div>
            </div>

        `);

        renderSimulationBatch();

        $('#wp_mix_date_from').prop('disabled',true);
        $('#wp_mix_date_to').prop('disabled',true);
        $('#popup_item_fg').prop('disabled',true).trigger('change.select2');
        $('#convert_method').prop('disabled',true).trigger('change.select2');
    }

    function cancelSimulation(){
        simulationMode = false;
        convertSimulation = null;
        $('#simulationPanel')
            .hide()
            .html('');

        $('#btnCancelSimulation')
            .hide();

        $('#btnPreviewConvert')
            .html('<i class="fa fa-search"></i> Preview');
        
        $('#btnPreviewConvert')
            .prop('disabled',false);

        $('#wp_mix_date_from')
            .prop('disabled',false);

        $('#wp_mix_date_to')
            .prop('disabled',false);

        $('#popup_item_fg')
            .prop('disabled',false)
            .trigger('change.select2');

        $('#convert_method')
            .prop('disabled',false)
            .trigger('change.select2');

    }

    function renderSimulationBatch(){
        let html=''; 
        
        convertSimulation.batches.forEach(function(batch,index){

            // let daily='';
            // Object.keys(batch.daily_qty).forEach(function(date){
            //     daily+=`
            //         <div class="simulation-day">
            //             <span>
            //                 ${
            //                     new Date(date).toLocaleDateString(
            //                         'en-GB',
            //                         {
            //                             day:'2-digit',
            //                             month:'short'
            //                         }
            //                     )
            //                 }
            //             </span>
            //             <b>${Number(batch.daily_qty[date]).toFixed(2)} Kg</b>
            //         </div>
            //     `;
            // });

            html+=`
            <div class="simulation-batch">
                <div class="simulation-batch-header">
                    <div>
                        <div style="margin-bottom: 5px;">Batch ${index+1}</div>
                        <small>
                            ${formatDate(batch.from)} - ${formatDate(batch.to)}
                        </small>
                    </div>

                    <button
                        class="btn-detail"
                        onclick="toggleBatch(${index}, this)">
                        <span>Show Detail</span>
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </div>

                <div class="simulation-total">
                    <div>
                        Need <b>${Number(batch.need).toFixed(2)} Kg</b>
                    </div>

                    <div>
                        Planning <b>${Number(batch.planning).toFixed(0)} Kg</b>
                    </div>

                    <div>
                        Waste <b style="color:#dc2626">${Number(batch.waste).toFixed(2)} Kg</b>
                    </div>
                </div>

                <div class="simulation-detail" id="batch-detail-${index}" style="display:none">
                    <table class="simulation-table">
                        <thead>
                            <tr style="text-align: center">
                                <th>WP Mix Date</th>
                                <th>WP Press Date</th>
                                <th>Workorder Press</th>
                                <th>Product No</th>
                                <th>Qty Press (Pcs)</th>
                                <th>Qty Need (Kg)</th>
                            </tr>
                        </thead>

                        <tbody>
                            ${renderBatchRows(batch.rows)}
                        </tbody>
                    </table>
                </div>
            </div>
            `;
        });

        $('#simulationBatchContainer').html(html);

    }

    function renderBatchRows(rows){
        let html='';

        rows.forEach(function(row){
            html+=`
                <tr>
                    <td>${row.wp_mix_date}</td>
                    <td>${row.wp_press_date}</td>
                    <td>${row.workorder_press}</td>
                    <td>${row.item_fg_number || row.product_no || ''}</td>
                    <td style="text-align:right">${Number(row.qty_press).toLocaleString('id-ID')}</td>
                    <td style="text-align:right">${Number(row.qty_need_kg).toFixed(2)}</td>
                </tr>
            `;
        });

        return html;
    }

    function toggleBatch(index, btn){
        const detail = $('#batch-detail-' + index);
        const icon   = $(btn).find('i');
        const text   = $(btn).find('span');

        if(detail.is(':visible')){
            detail.slideUp(200);
            text.text('Show Detail');
            icon
                .removeClass('fa-chevron-up')
                .addClass('fa-chevron-down');

        }else{
            detail.slideDown(200);
            text.text('Hide Detail');
            icon
                .removeClass('fa-chevron-down')
                .addClass('fa-chevron-up');

        }
    }

    function startConvert(){
        if(!convertSimulation){
            toastr.warning('Preview simulation first.');
            return;
        }

        $('#btnPreviewConvert')
            .prop('disabled',true)
            .html('<i class="fa fa-spinner fa-spin"></i> Converting...');

        $.ajax({
            url:'<?= base_url("planning/production_schedule_mixing/convert") ?>',
            type:'POST',
            dataType:'json',
            data:{
                from:$('#wp_mix_date_from').val(),
                to:$('#wp_mix_date_to').val(),
                item_fg_id:$('#popup_item_fg').val(),
                method:$('#convert_method').val()
            },
            success:function(res){
                $('#btnPreviewConvert')
                    .prop('disabled',false);

                if(!res.success){
                    toastr.error(res.message || 'Convert failed');
                    $('#btnPreviewConvert')
                        .html('<i class="fa fa-play"></i> Start');
                    return;
                }

                toastr.success('Convert successfully.');
                setTimeout(function(){
                    Swal.close();
                    loadData();
                },500);
            },

            error:function(xhr){
                $('#btnPreviewConvert')
                    .prop('disabled',false)
                    .html('<i class="fa fa-play"></i> Start');
                toastr.error('Convert failed');
                console.log(xhr.responseText);
            }

        });

    }


    function showDetailSchedule(data){
        const header  = data.header;
        const details = data.details || [];
        let detailRows = '';

        details.forEach(item => {
            detailRows += `
                <tr>
                    <td>${item.wp_press_date}</td>
                    <td>${item.workorder_press}</td>
                    <td>${item.product_no}</td>
                    <td style="text-align:right;">
                        ${parseFloat(item.composition).toFixed(4)}
                    </td>
                    <td style="text-align:right;">
                        ${formatQty(item.qty_press)}
                    </td>
                    <td style="text-align:right;">
                        ${parseFloat(item.qty_need_kg).toFixed(4)}
                    </td>
                </tr>
            `;
        });

        Swal.fire({
            width:'1300px',
            showConfirmButton:false,
            showConfirmButton: false,
            allowOutsideClick: true,
            allowEscapeKey: true,
            allowEnterKey: false,

            html:`
            <div class="schedule-popup">
                <div class="popup-header">
                    <div class="popup-title">
                        Detail Schedule
                    </div>

                    <button
                        class="popup-close"
                        onclick="Swal.close()">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <div class="popup-body">
                    <div class="popup-left">
                        <div class="title-header">
                        Schedule on ${
                            new Date(header.wp_mix_date)
                                .toLocaleDateString('en-GB',{
                                    day:'2-digit',
                                    month:'short',
                                    year:'numeric'
                                })
                        }
                        </div>

                        <div class="qty-row">
                            <div class="qty-big">
                               ${parseFloat(header.total_round_qty_kg).toFixed(0)} Kg
                            </div>
                            ${getStatusBadge(data.status)}
                        </div>

                        <div class="info-grid">
                            <div>Work Order</div>
                            <div>${header.workorder_mix_compound}</div>
                            <div>Compound No</div>
                            <div class="popup-compound">${header.compound_no}</div>
                            <div>MPQ</div>
                            <div>${header.mpq} Kg</div>
                            <div>MOQ</div>
                            <div>${header.moq} Kg</div>
                            <div>Created By</div>
                            <div>${header.created_by}</div>
                            <div>Created Date</div>
                            <div>${header.created_date}</div>
                        </div>

                    </div>

                    <div class="popup-right">
                        <div class="title-header">Calculated from Production Schedule Press</div>

                        <table class="popup-table">
                            <thead>
                                <tr>
                                    <th>WP Date Press</th>
                                    <th>Work Order Press</th>
                                    <th>Product No</th>
                                    <th>QPA (gram)</th>
                                    <th>Qty Press (pcs)</th>
                                    <th>Need (Kg)</th>
                                </tr>
                            </thead>

                            <tbody>
                                ${detailRows}
                            </tbody>
                        </table>

                        <div class="summary-box">
                            <div>
                                Total Need Original
                            </div>

                            <div>
                                ${parseFloat(header.total_qty_kg).toFixed(4)} Kg
                            </div>

                            <div>
                                Round To ${header.round_type}
                            </div>

                            <div>
                                ${parseFloat(header.total_round_qty_kg).toFixed(0)} Kg
                            </div>
                        </div>

                        <div class="popup-info">
                            <div class="popup-info-icon">i</div>

                            <span>
                                Production Schedule Mixing adalah kebutuhan produksi compound H-3 untuk memenuhi
                                Cutting Compound Schedule (H-1).
                            </span>
                        </div>

                    </div>
                </div>
            </div>
            `
        });

    }

    function loadDetailSchedule(param){
        $.ajax({
            url : '<?= base_url("planning/production_schedule_mixing/readDetail") ?>',
            type : 'POST',
            dataType : 'json',
            data : param,
            success:function(res){
                showDetailSchedule(res);
            }
        });
    }

    const monthNames = [
        'January','February','March','April',
        'May','June','July','August',
        'September','October','November','December'
    ];

    function renderCalendar(data){

        const period = $('#filter_period').val();
        const arr = period.split('-');
        const year  = parseInt(arr[0]);
        const month = parseInt(arr[1]) - 1;

        const monthNames = [
            'January','February','March','April',
            'May','June','July','August',
            'September','October','November','December'
        ];

        const dayNames = [
            'Sun','Mon','Tue','Wed','Thu','Fri','Sat'
        ];

        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const wpMap = {};

        data.forEach(row => {
            row.schedule.forEach(item => {
                wpMap[item.day] = item.wp;
            });
        });

        let headerHtml = `
        <tr>
            <th rowspan="2" class="no-col">No</th>
            <th rowspan="2" class="compound-col">
                Compound<br>
                <span class="sub-header">(Unit : Kg)</span>
            </th>
            <th colspan="${daysInMonth}" class="month-header">
                ${monthNames[month]} ${String(year).slice(-2)}
            </th>
        </tr>
        <tr>
        `;

        for(let d=1; d<=daysInMonth; d++){
            const date = new Date(year, month, d);
            const dow = date.getDay();

            const weekend = (dow === 0 || dow === 6) ? 'weekend-header' : '';
            const wp = wpMap[d] ? `WP(${wpMap[d]})` : '';

            headerHtml += `
                <th class="day-col ${weekend}">
                    ${dayNames[dow]} ${d} ${wp}
                </th>
            `;
        }

        headerHtml += '</tr>';
        $('#calendar-header').html(headerHtml);

        let tbodyHtml = '';
        data.forEach(row => {
            let tr = `
            <tr>
                <td class="center">${row.no}</td>

                <td>
                    <div class="compound-row">
                        <div class="compound-dot"></div>
                        <div class="compound-info">
                            <div class="compound-id-text">
                                ${row.compound_id}
                            </div>

                            <div class="compound-no-text">
                                ${row.compound_no}
                            </div>

                            <div class="compound-mpq">
                                MPQ : ${row.mpq} Kg
                            </div>

                            <div class="compound-mpq">
                                Life Time : ${row.lifetime} Hari
                            </div>
                        </div>
                    </div>
                </td>
            `;

            let d = 1;

            while(d <= daysInMonth){
                const item = row.schedule.find(x => x.start_day == d);
                if(item){
                    const statusText = {
                        open:'Open',
                        progress:'On Progress',
                        closed:'Closed',
                        released:'Released',
                    };

                    let dailyHtml = '';
                    let cardBodyData = '';
                    let styleCardBody = '';

                    if(item.daily_qty && item.daily_qty.length > 1){
                        let cols = [];

                        for(let i=0;i<item.span;i++){
                            cols.push(`<div class="merge-top-qty">0 Kg</div>`);
                        }

                        item.daily_qty.forEach(function(q){
                            cols[q.offset] = `
                                <div class="merge-top-qty">
                                    ${parseFloat(q.qty).toFixed(2)} Kg
                                </div>
                            `;
                        });

                        dailyHtml = `
                            <div class="merge-top-row"
                                style="grid-template-columns:repeat(${item.span},1fr)">
                                ${cols.join('')}
                            </div>
                        `;

                        // cardBodyData = `

                        //         <div class="card-code">
                        //             ${item.code}
                        //         </div>
                        //         <div class="card-qty" style="font-size: 16px !important; display: flex; justify-content: space-between;">
                        //             ${parseFloat(item.qty).toFixed(0)} Kg <span class="card-status" style="padding-left: 10px;">${statusText[item.status]}</span>
                        //         </div>
                        // `;


                        cardBodyData = `
                            <div class="merge-col">
                                <div class="card-code">
                                    ${item.code}
                                </div>
                            </div>

                            <div class="merge-col">
                                <div class="card-qty">
                                    ${parseFloat(item.qty).toFixed(0)} Kg
                                </div>
                            </div>

                            <div class="merge-col">
                                <div class="card-status">
                                    ${statusText[item.status]}
                                </div>
                            </div>
                        `;


                        styleCardBody = `style="display: grid; text-align: center;"`;

                    } else {

                        cardBodyData = `
                            <div class="single-card">

                                <div class="single-card-code card-code">
                                    ${item.code}
                                </div>

                                <div class="single-card-spacer"></div>

                                <div class="single-card-qty card-qty">
                                    ${parseFloat(item.qty).toFixed(0)} Kg
                                </div>

                                <div class="single-card-status card-status">
                                    ${statusText[item.status]}
                                </div>

                            </div>
                        `;

                        styleCardBody = `style="display:flex;"`;
                    }

                    tr += `
                        <td colspan="${item.span}">
                            <div class="card status-${item.status}"
                                data-header-id="${row.id}"
                                data-status="${item.status}"
                                data-compound="${row.compound_no}"
                                data-qty="${item.qty}"
                                data-item-rm-id="${row.compound_id}"
                                data-wp-date="${item.wp_date}"
                                data-convert-id="${item.convert_id || ''}">

                                ${dailyHtml}

                                <div class="card-body" ${styleCardBody}>
                                    ${cardBodyData}
                                </div>


                            </div>
                        </td>
                    `;
                    d += item.span;

                }else{
                    tr += '<td></td>';
                    d++;
                }

            }

            tr += '</tr>';

            tbodyHtml += tr;
        });

        $('#tbody').html(tbodyHtml);

        const totalData = data.length;
        $('#footer-total').text(
            totalData > 0
                ? `Showing 1 to ${totalData} of ${totalData} compounds`
                : 'Showing 0 to 0 of 0 compounds'
        );

        filterCards();
    }

    $('#filter_period').on('change', function(){
        loadData();
    });

    $('#filter_item_fg').on('change', function(){
        loadData();
    });

    $('.filter-status').on('change', function(){
        filterCards();
    });

    function filterCards(){
        const activeStatus = [];
        $('.filter-status:checked').each(function(){
            activeStatus.push($(this).val());
        });

        $('.card').each(function(){
            const status = $(this).data('status');
            if(activeStatus.includes(status)){
                $(this).show();
            }else{
                $(this).closest('td').find('.card').hide();
            }
        });
    }

    filterCards();

    $('#filter_item_fg').select2({
        placeholder: 'All Compound',
        allowClear: false,
        ajax: {
            url: '<?= base_url("master/item_fg/readCompounds") ?>',
            dataType: 'json',
            method: 'post',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term || ''
                };
            },
            processResults: function(data) {
                return {
                    results: $.map(data.rows || data, function(item) {
                        return {
                            id: item.id,
                            text: item.number,
                            number: item.number,
                            name: item.name
                        };
                    })
                };
            },
            cache: true
        },
        templateResult: function(item) {
            if (!item.id) {
                return item.text;
            }

            return $(`
                <div style="display:flex;flex-direction:column;gap:2px;">
                    <div style="
                        font-size:14px;
                        font-weight:700;
                        color:#1e293b;
                    ">
                        ${item.number}
                    </div>

                    <div style="
                        font-size:12px;
                        color:#64748b;
                    ">
                        ${item.name}
                    </div>
                </div>
            `);
        },
        templateSelection: function(item) {
            return item.number || item.text;
        },
    });

    function loadData(){
        $.ajax({
            url : '<?= base_url("planning/production_schedule_mixing/read") ?>',
            type : 'POST',
            dataType : 'json',
            data : {
                period    : $('#filter_period').val(),
                item_fg_id: $('#filter_item_fg').val()
            },
            success:function(res){
                if(!res.rows || res.rows.length === 0){
                    $('#tbody').html(`
                    <tr>
                        <td colspan="100">
                            <div class="empty-state">

                                <div class="empty-icon">
                                    <i class="fa fa-search"></i>
                                </div>

                                <div class="empty-content">
                                    <div class="empty-title">
                                        No Compound Found
                                    </div>

                                    <div class="empty-subtitle">
                                        No production schedule available for the selected period and compound.
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                    `);

                    $('#footer-total').text(
                        'Showing 0 to 0 of 0 compounds'
                    );
                    return;
                }

                renderCalendar(res.rows || []);
            },
            error:function(xhr){
                console.log(xhr.responseText);
            }
        });
    }

    function loadPeriods(){
        $.ajax({
            url : '<?= base_url("planning/production_schedule_mixing/readPeriods") ?>',
            type : 'GET',
            dataType : 'json',
            success:function(rows){
                $('#filter_period').empty();

                rows.forEach(function(row){
                    const year  = row.period.substring(0,4);
                    const month = parseInt(row.period.substring(4,6));

                    $('#filter_period').append(`
                        <option value="${year}-${String(month).padStart(2,'0')}">
                            ${monthNames[month - 1]} ${year}
                        </option>
                    `);
                });

                const now = new Date();
                const currentPeriod = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2,'0');

                if(rows.length > 0){
                    if($('#filter_period option[value="'+currentPeriod+'"]').length){
                        $('#filter_period').val(currentPeriod);
                    }else{
                        $('#filter_period').val($('#filter_period option:first').val());
                    }

                    loadData();
                }

            }
        });
    }

    $('#filter_period').select2({
        placeholder: 'Select Period',
        width: '180px'
    });

    function formatQty(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatDate(date) {
        return new Date(date).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    loadPeriods();
</script>