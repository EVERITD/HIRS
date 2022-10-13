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

   .dataTables_paginate.paging_simple_numbers {
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
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Overtime, Undertime and Early Leave</p>
            <div class="row" style="padding: 0 1rem; display:flex; align-items:center;justify-content:space-around">
               <p>Apply for :</p>
               <div class="col-4">
                  <div class=" form-check d-flex">
                     <input class="form-check-input" type="radio" name="typeDay" id="exampleRadios1" value="overtime" checked style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Overtime
                     </label>
                  </div>
               </div>
               <div class="col-4">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="typeDay" id="exampleRadios1" value="undertime" style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Undertime / Early Leave
                     </label>
                  </div>
               </div>


            </div>
            <div class="row" style="padding: 0 1rem; overflow:hidden" id="ot_options">
               <p>Overtime options :</p>
               <div class="col">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="overtime_options" id="exampleRadios1" value="vaca_leave" checked style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Regular
                     </label>
                  </div>
               </div>
               <div class="col">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="overtime_options" id="exampleRadios1" value="vaca_leave" checked style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Overtime
                     </label>
                  </div>
               </div>
               <div class="col">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="overtime_options" id="exampleRadios1" value="vaca_leave" checked style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Morning
                     </label>
                  </div>
               </div>
               <div class="col">
                  <div class="form-check d-flex">
                     <input class="form-check-input" type="radio" name="overtime_options" id="exampleRadios1" value="vaca_leave" checked style="margin-right: 10px;">
                     <label class="form-check-label" for="exampleRadios1">
                        Fiscal Year
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

            <div class="row" style="padding: 0 1rem">
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Hours rendered from :</span>
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


            <div class="row" style="padding: 0 1rem">
               <p>Type of Day :</p>
               <div class="col-12">
                  <select class="form-select form-select-sm" id="selecthrs" aria-label=".form-select-sm example" style="font-size: 11px;">
                     <option selected>Select hours to apply</option>
                     <option value="4">4hrs</option>
                     <option value="4.5">4hrs and 30min</option>
                     <option value="5">5hrs</option>
                     <option value="5.5">5hrs and 30min</option>
                     <option value="6">6hrs</option>
                     <option value="7">7hrs</option>
                     <option value="8">8hrs</option>
                     <option value="9">9hrs</option>
                     <option value="9.5">9hrs and 30min</option>
                  </select>
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
   let rdoInput = $('input[name="typeDay"]')

   rdoInput.map((_index, _obj) => {
      _obj.addEventListener("click", (e) => {
         if (e.target.value == 'undertime') {
            $('#ot_options').css("height", "0px")
         } else {
            $('#ot_options').css("height", "inherit")
         }
      })
   })

   console.log()
</script>