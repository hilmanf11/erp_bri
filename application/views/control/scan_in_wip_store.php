<style>
    .easyui-panel-body{
        position:relative;
    }

    .page-scan .scan-btn *{
        font-size:inherit !important;
    }

    .page-scan{
        position:absolute;
        inset:0;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:28px;
        box-sizing:border-box;
        background:linear-gradient(135deg,#f4f7fb,#eef2f6);
    }

    .scan-card{
        width:100%;
        max-width:1250px;
        min-height:520px;
        background:white;
        border-radius:24px;
        box-shadow:0 22px 55px rgba(0,0,0,0.08);
        padding:55px 60px 60px;
        display:flex;
        flex-direction:column;
    }

    .scan-title{
        font-size: 24px !important;
        text-align:center;
        font-size:clamp(30px,2.4vw,42px);
        font-weight:700;
        color:#243447;
        margin-bottom:45px;
        letter-spacing:.5px;
    }

    .menu-grid{
        flex:1;
        display:flex;
        flex-direction:column;
        justify-content:center;
        gap:32px;
    }
    .row{
        display:flex;
        justify-content:center;
        gap:52px;
        flex-wrap:wrap;
    }

    .menu-grid a:nth-child(1){ grid-column:2; }
    .menu-grid a:nth-child(2){ grid-column:4; }

    .menu-grid a:nth-child(3){ grid-column:1; }
    .menu-grid a:nth-child(4){ grid-column:3; }

    /* .scan-btn{
        width:300px;
        height:220px;
        padding:22px 43px !important;

        border-radius:22px;
        text-decoration:none;
        color:#fff;

        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        text-align:center;

        gap:14px;
        font-size:clamp(10px,1.5vw,19px) !important;
        font-weight:700;

        border:1px solid rgba(0,0,0,0.05);
        box-shadow:0 10px 22px rgba(0,0,0,0.07);
        transition:.28s ease;

        white-space:normal;
        line-height:1.45;
    }

    .scan-btn:hover{
        transform:translateY(-8px);
        color: #fff !important;
        box-shadow:0 22px 38px rgba(0,0,0,0.16);
    }

    .scan-btn i{
        font-size:clamp(42px,3vw,60px);
        opacity:.9;
    } */

    .scan-btn{
        width:300px;
        height:220px;
        padding:22px 43px !important;

        border-radius:22px;
        text-decoration:none;

        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        text-align:center;

        gap:14px;
        font-size:clamp(10px,1.5vw,19px) !important;
        font-weight:700;

        border:1px solid rgba(0,0,0,0.05);
        box-shadow:0 10px 22px rgba(0,0,0,0.07);

        transition:
            transform .28s ease,
            box-shadow .28s ease,
            filter .28s ease,
            color .28s ease;

        white-space:normal;
        line-height:1.45;

        color:#ffffff; /* pastikan default putih */
    }

    .scan-btn:hover{
        transform:translateY(-8px) scale(1.02);
        box-shadow:0 24px 45px rgba(0,0,0,0.18);
        filter:brightness(1.06) saturate(1.05);
        color:#ffffff !important;
    }

    .incoming{
        background:linear-gradient(135deg,#5fcf8a,#3cab6d);
    }

    .internal{
        background:linear-gradient(135deg,#6fb7e9,#4a96cc);
    }

    .return{
        background:linear-gradient(135deg,#f6b562,#e39a3f);
    }

    .rework{
        background:linear-gradient(135deg,#f07a6e,#d65c51);
    }

    @media(max-width:1200px){
        .menu-grid{ grid-template-columns:repeat(3,1fr); }
    }
    @media(max-width:850px){
        .menu-grid{ grid-template-columns:repeat(2,1fr); }
    }
</style>

<div class="page-scan">
    <div class="scan-card">
        <div class="scan-title">Scan In WIP Store</div>

        <div class="menu-grid">

            <div class="row row-2">

                <a href="javascript:void(0)"
                onclick="openScanPage(
                    'Scan In From Internal Finishing',
                    '<?= base_url('control/scan_in_from_internal_finishing/index/') ?>' + getToken()
                )"
                class="scan-btn internal">
                    Scan In From</br>Internal Finishing
                </a>

                <a href="javascript:void(0)"
                onclick="openScanPage(
                    'Scan In From External Subcont',
                    '<?= base_url('control/scan_in_from_external_finishing/index/') ?>' + getToken()
                )"
                class="scan-btn incoming">
                    Scan In From</br>External Finishing
                </a>

            </div>

            <div class="row row-3">
                <a href="javascript:void(0)"
                onclick="openScanPage(
                    'Scan In Return',
                    '<?= base_url('control/scan_in_return/index/') ?>' + getToken()
                )"
                class="scan-btn return">
                    Scan In Return</br>(not yet checked)
                </a>

                <a href="javascript:void(0)"
                onclick="openScanPage(
                    'Scan In Rework',
                    '<?= base_url('control/scan_in_rework/index/') ?>' + getToken()
                )"
                class="scan-btn rework">
                    Scan In Rework
                </a>
            </div>

        </div>

    </div>
</div>

<script>
    function openScanPage(title, url){

        if(parent && parent.addTab){
            parent.addTab(title,url);
        }
        else if(window.addTab){
            addTab(title,url);
        }
        else{
            window.location=url;
        }
    }

    function getToken(){
        return window.location.pathname.split('/').pop();
    }
</script>