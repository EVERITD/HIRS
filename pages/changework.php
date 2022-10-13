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

   span {
      font-size: 11px !important;
   }
</style>
<div class="row">
   <div class="col-3">
      <?php require '../components/sidebar.php'; ?>
   </div>
   <div class="col">
      <?php require '../components/navbar.php'; ?>
      <div class="container" style="height:calc(100vh - 50px);display:flex; align-items:center; justify-content:center;z-index:99px; ">
         <div class="leaveContainer" tabindex="1" style="box-shadow: 10px 10px 32px -18px rgba(0,0,0,1); width: 800px; padding: 0px;">
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Change of Work Schedule</p>
            <div class="row" style="padding:0 1rem;">
               <div class="col-6" style="margin-bottom:1rem ;">
                  <select class="form-select form-select-sm" id="selecthrs" aria-label=".form-select-sm example" style="font-size: 11px;" onchange="changeSchedule(this)">
                     <option selected>Select work schedule</option>
                     <option value="Regular Schedule">Regular Schedule</option>
                     <option value="Lunch Break">Lunch Break</option>
                     <option value="Coffee Break">Coffee Break</option>
                     <option value="Day off">Day off</option>
                  </select>
               </div>
               <div class="col-6">
                  <div class="input-group mb-3">
                     <span class="input-group-text" id="basic-addon1">Effective Date :</span>
                     <input type="date" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
                  </div>
               </div>
               <div class="col-6" id="dayoff">
                  <div class="input-group mb-3">
                     <span class="input-group-text" id="basic-addon1">Official Day Off :</span>
                     <input type="date" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
                  </div>
               </div>
            </div>
            <div class="row" style="padding:0 1rem;" id="timeinout">
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Time In :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Time Out:</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
            </div>
            <div class="row" style="padding:0 1rem;" id="lunchbreak">
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Lunch Break ( Out ) :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Lunch Break ( In ) :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
            </div>
            <div class="row" style="padding:0 1rem;" id="coffeebreak">
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Coffee Break ( Out ) :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
               <div class="col-6">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Coffee Break ( In ) :</span>
                     <input type="time" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
            </div>
            <div class="row" style="padding:0 1rem;">
               <div class="col-12">
                  <div class="input-group mb-3">
                     <span class="input-group-text" id="basic-addon1"> Remarks :</span>
                     <div class="form-floating">
                        <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; min-height:200px;"></textarea>
                     </div>
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


   function changeSchedule(e) {
      switch (e.value) {
         case 'Regular Schedule':
            $('#timeinout').show()
            $('#coffeebreak').show()
            $('#lunchbreak').show()
            $('#dayoff').hide()
            break;
         case 'Coffee Break':
            $('#timeinout').hide()
            $('#lunchbreak').hide()
            $('#dayoff').hide()
            $('#coffeebreak').show()
            break;
         case 'Day off':
            $('#dayoff').show()
            $('#coffeebreak').hide()
            $('#lunchbreak').hide()
            $('#timeinout').hide()
            break;
         case 'Lunch Break':
            $('#timeinout').hide()
            $('#coffeebreak').hide()
            $('#lunchbreak').show()
            $('#dayoff').hide()
            break;
         default:

      }

   }
</script>