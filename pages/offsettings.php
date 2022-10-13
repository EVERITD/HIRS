<?php require '../layout/header.php'; ?>
<style>
   table th {
      font-size: 10px;
   }

   table td {
      font-size: 10px;
      font-weight: normal;
   }

   #table_id_info {
      font-size: 11px;
   }
</style>
<div class="row">
   <div class="col-3">
      <?php require '../components/sidebar.php'; ?>
   </div>
   <div class="col">
      <?php require '../components/navbar.php'; ?>
      <div class="container" style="height:calc(100vh - 50px);display:flex; align-items:center; justify-content:center;z-index:99px; ">
         <div class="leaveContainer" tabindex="1" style="box-shadow: 10px 10px 32px -18px rgba(0,0,0,1); width: 600px; padding: 0px;">
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Offsetting</p>
            <div class="row" style="padding: 0 1rem; display:flex; align-items:center;justify-content:space-around">
               <p>Offset Type :</p>
               <div class="col-4">
                  <div class=" form-check d-flex">
                     <input class="form-check-input" type="radio" name="typeDay" id="exampleRadios1" value="Undertime" checked style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Undertime - ( 3hrs less )
                     </label>
                  </div>
               </div>
               <div class="col-4">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="typeDay" id="exampleRadios2" value="MultipleOffset" style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios2">
                        Mutiple OT in One Offset
                     </label>
                  </div>
               </div>
               <div class="col-4">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="typeDay" id="exampleRadios3" value="DayAbsent" style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios3">
                        Day absent
                     </label>
                  </div>
               </div>
            </div>
            <div class="row" style="padding: 0 1rem">
               <div class="input-group input-group-sm mb-3">
                  <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Effectivity Date :</span>
                  <input type="date" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
               </div>
            </div>
            <div class="row" style="padding: 0 1rem" id="selectOT">
               <div class="col-12">
                  <select class="form-select form-select-sm" id="selecthrs" aria-label=".form-select-sm example" style="font-size: 11px;">
                     <option selected>Select overtime to apply</option>
                     <option value="4">2hrs and 15min - 10-04-2022 - 6:00 pm to 8:15pm </option>
                  </select>
               </div>
            </div>
            <div class="row" style="padding: 0 1rem; margin-top: 1rem" id="multipleoffset">
               <div class="col-12">
                  <table style="width:100%">
                     <thead style="border-top: 1px solid black;background-color:#198754; color:white;">
                        <tr>
                           <td style="padding:0 10px">Control No.</td>
                           <td>Date</td>
                           <td>From</td>
                           <td>To</td>
                           <td>Hrs Available</td>
                           <td>Action</td>
                        </tr>
                     </thead>
                     <tbody style="border-top: 1px solid black;">
                        <tr>
                           <td style="font-weight:bold!important;padding:0 10px">OT00129799</td>
                           <td style="font-weight:bold!important">10-04-2022</td>
                           <td style="font-weight:bold!important">6:00</td>
                           <td style="font-weight:bold!important">8:15</td>
                           <td style="font-weight:bold!important">2 hours 15 minutes</td>
                           <td>
                              <button style="width:100%; border:.5px solid grey">SET</button>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>

            <div class="row" style="padding: 0 1rem; margin-top:1rem;" id="hrstooffset">
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Hours to offset :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px"> TO :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
            </div>
            <div class="row" style="padding: 0 1rem; margin-top:10px;">
               <div class="input-group mb-3">
                  <span class="input-group-text" id="basic-addon1" style="font-size: 11px;"> Remarks :</span>
                  <div class="form-floating">
                     <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px;"></textarea>
                  </div>
               </div>

            </div>
            <div class="row" style="padding: 10px 2rem; text-align:right">
               <button type="button" class="btn btn-success" style="width:100%">Submit</button>
            </div>
         </div>
      </div>
   </div>
</div>
<?php require '../layout/footer.php' ?>
<script>
   // $('input[name="dates"]').daterangepicker();
   $(document).ready(() => {
      filterOffsetType()
      $('#multipleoffset').hide()
   })

   function filterOffsetType() {
      let rdoTypes = $("input[name='typeDay']")
      rdoTypes.map((_index, rdo) => {
         rdo.addEventListener('click', (e) => {
            console.log(e.target.value)
            if (e.target.value.toLowerCase() == 'dayabsent') {
               $('#hrstooffset').hide()
               $('#multipleoffset').hide()
            } else if (e.target.value.toLowerCase() == 'multipleoffset') {
               $('#hrstooffset').show()
               $('#multipleoffset').show()
               $('#selectOT').hide()
            } else {
               $('#multipleoffset').hide()
               $('#hrstooffset').show()
               $('#selectOT').show()
            }
         })
      })
   }
</script>