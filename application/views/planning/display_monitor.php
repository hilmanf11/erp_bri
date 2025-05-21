<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
</div>
<p style="display:flex;flex:1;justify-content:center;text-align:center;font-weight:bold;font-size:calc(1.325rem + .5vw) !important;">DISPLAY MONITORING CHEMICAL WEIGHTING AVAILABITY STOCK</p>
<div class="containerb">
  <div class="box-container" id="boxContainer"></div>
  <div class="orange-container">
    <div style="border: 1px solid black;justify-content:center; text-align:center;padding:10px;margin-bottom:2px;font-weight:bold;"><span>Status PLC : </span><span style="color:rgba(71, 114, 199, 1);font-weight:bold;font-style:italic;">ON</span></div>
    <div style="border: 1px solid black;justify-content:center; text-align:center;padding:10px;margin-bottom:2px;font-weight:bold;"><span>Status PLC : </span><span style="color:rgba(254, 1, 0, 1);font-weight:bold;font-style:italic;">OFF</span></div>
    
    <div style="border: 1px solid black;justify-content:center; align-items:flex-start;padding:5px;margin-bottom:2px;">
        <div style="justify-content:flex-start;align-items:center; display:flex;flex-direction:row;"><button style="width:90px; height:25px;background-color:rgba(2, 173, 83, 1);margin-right:10px;margin-bottom:5px;" disabled></button><h2 style='color:black;font-size:calc(1.325rem + .5vw) !important;font-weight:500;margin-bottom:.5rem;line-height:1.2'>FULL </h2></div>
        <div style="justify-content:flex-start;align-items:center; display:flex;flex-direction:row;"><button style="width:90px; height:25px;background-color:rgba(254, 1, 0, 1);margin-right:10px;margin-bottom:5px;" disabled></button><h2 style='color:black;font-size:calc(1.325rem + .5vw) !important;font-weight:500;margin-bottom:.5rem;line-height:1.2'>EMPTY</h2></div>
    </div>
    <div style="border: 1px solid black;justify-content:center; align-items:flex-start;padding:5px;margin-bottom:2px;">
        <div style="justify-content:flex-start;align-items:center; display:flex;flex-direction:row;"><button style="width:90px; height:25px;background-color:rgba(2, 173, 83, 1);margin-right:10px;margin-bottom:5px;" disabled></button><h2 id="totalOn" style='color:black;font-size:calc(1.325rem + .5vw) !important;font-weight:500;margin-bottom:.5rem;line-height:1.2'></h2></div>
        <div style="justify-content:flex-start;align-items:center; display:flex;flex-direction:row;"><button style="width:90px; height:25px;background-color:rgba(254, 1, 0, 1);margin-right:10px;margin-bottom:5px;" disabled></button><h2 id="totalOff" style='color:black;font-size:calc(1.325rem + .5vw) !important;font-weight:500;margin-bottom:.5rem;line-height:1.2'></h2></div>
    </div>
    <div style="border: 1px solid black;justify-content:center; align-items:flex-start;padding:10px;margin-bottom:2px;">
    <canvas id="myPieChart" width="200" height="200"></canvas>
    </div>
  </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/uom/print') ?>" style="width: 100%;" hidden></iframe>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/uom/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {

        const container = document.querySelector('.containerb');
        const boxContainer = document.getElementById("boxContainer");
        const orangeContainer = document.querySelector(".orange-container");
        const h2on = document.getElementById("totalOn");
        const h2off = document.getElementById("totalOff");
        // Set styles for .container dynamically
        container.style.display = "flex";
        container.style.flexDirection = "row";
        container.style.width = "100%";
        container.style.justifyContent = "space-between";
        container.style.border = "1px solid";

        // Set styles for .box-container dynamically
        boxContainer.style.display = "flex";
        boxContainer.style.flexDirection = "row";
        boxContainer.style.flexWrap = "wrap";
        boxContainer.style.padding = "1px";
        boxContainer.style.backgroundColor = "white";
        boxContainer.style.flex = "2";
        boxContainer.style.width = "80%";
        boxContainer.style.alignContent = "flex-start";

        // Set styles for .orange-container dynamically
        //orangeContainer.style.flex = "1";
        orangeContainer.style.backgroundColor = "white";
        orangeContainer.style.display = "flex";
        orangeContainer.style.flexDirection = "column";
        orangeContainer.style.flexWrap = "wrap";
        orangeContainer.style.padding = "2px";
        //orangeContainer.style.width = "20%";
        $.ajax({
                            method: 'post',
                            url: '<?= base_url('planning/display_monitor/datatables') ?>',
                            success: function(result) {

                                if(result.length>0){
                                    const data = JSON.parse(result)
                                    const penuh = data.reduce((acc, item) => {return item.sensor == 0 ? acc + 1 : acc;}, 0);
                                    const kosong = data.reduce((acc, item) => {return item.sensor == 1 ? acc + 1 : acc;}, 0);

                                    h2on.textContent = penuh;
                                    h2off.textContent = kosong;
                                    for (let i = 0; i < data.length; i++) {
                                        const box = document.createElement('div');
                                        box.classList.add('box');
                                        
                                        box.style.backgroundColor = (data[i].sensor==1) ? 'rgba(254, 1, 0, 1)' : 'rgba(2, 173, 83, 1)';
                                        box.style.height = '145px';
                                        box.style.display = 'flex';
                                        box.style.flexDirection = 'column';
                                        box.style.color = 'white';
                                        box.style.margin = '2px 1px';
                                        box.style.padding = '5px';
                                        box.style.textAlign = 'center';
                                        box.style.width = `calc(100% / 15 - 2px)`; // 15 boxes per row
                                        // Add label and number inside the box
                                        box.innerHTML = `<label style='font-size:14px !important;'>LOC</label><h2 style='color:black;margin-top: 1.45em !important;font-size:calc(1.325rem + .9vw) !important;font-weight:500;margin-bottom:.5rem;line-height:1.2'>${data[i].box}</h2>`;

                                        // Append the box to the container
                                        boxContainer.appendChild(box);
                                    }


                                    // Get the data passed from the controller
                                    var ctx = document.getElementById('myPieChart').getContext('2d');
                                    
                                    var myPieChart = new Chart(ctx, {
                                        type: 'pie', // Pie chart type
                                        data: {
                                            labels: ['FULL', 'EMPTY'], // Labels for the chart
                                            datasets: [{
                                                label: 'Total', // Label for the dataset
                                                data: [penuh,kosong], // The data for the chart
                                                backgroundColor: ['rgb(71, 114, 199)','rgb(119, 137, 128)'], // Segment colors 'rgb(254, 97, 0)', 
                                                hoverOffset: 4
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'bottom', // Position of the legend
                                                },
                                                tooltip: {
                                                    enabled: true
                                                }
                                            }
                                        }
                                    });
                                }


                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error(jqXHR.statusText);
                                $.messager.alert("Error", jqXHR.statusText, 'error');
                            },
                        });

    });
</script>