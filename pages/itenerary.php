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

   input[type="text"] {
      font-size: 12px !important;
   }

   .form-check {
      display: flex;
   }

   .form-check label {
      margin-left: 5px;
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
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Leave</p>
            <div style="padding: 1rem; display:flex;  align-items:center">
               <div class="form-check">
                  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="vaca_leave" checked>
                  <label class="form-check-label" for="exampleRadios1">
                     Branch
                  </label>
               </div>
               <div class="form-check" style="margin-left: 10px;">
                  <input class="form-check-input" type="radio" name="exampleRadios" id="officailbuss" value="vaca_leave_half" checked>
                  <label class="form-check-label" for="officailbuss">
                     Official Business/Field Work
                  </label>
               </div>
            </div>
            <div class="row" style="padding: 0 1.5rem">
               <div class="col-12">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Effective Date :</span>
                     <input type="text" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>

            </div>
            <div class="row" style="padding: 0 1.5rem">
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Time Applicable :</span>
                     <input type="time" name="timefrm" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">To :</span>
                     <input type="time" name="timeto" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>

            </div>
            <div class="row" style="padding: 0 1.5rem;">
               <div class="form-floating">
                  <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; padding-top:10px"></textarea>
               </div>
            </div>
            <div class="row" style="padding: 10px 2rem; text-align:right">
               <button type="button" class="btn btn-success" style="width:250px">Submit</button>
            </div>
         </div>
      </div>
   </div>
</div>
<?php require '../layout/footer.php' ?>
<script>
   let radioBtns = $('input[name="exampleRadios"]')
   $('input[name="dates"]').daterangepicker();
   radioBtns.map((index, rdo) => {
      rdo.addEventListener('click', (e) => {
         if (e.target.value == "maternity_leave") {
            $('#selecthrs').css("display", "none")
         } else {
            $('#selecthrs').css("display", "block")
         }
      })
   })


   function hideLeavehrs() {
      $('#selecthrs').css()
   }
</script>