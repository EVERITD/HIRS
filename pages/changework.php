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
         <form id="form_changework">
            <div class="leaveContainer" tabindex="1" style="box-shadow: 10px 10px 32px -18px rgba(0,0,0,1); width: 800px; padding: 0px;">
               <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Change of Work Schedule</p>
               <div class="row" style="padding:0 1rem;">
                  <div class="col-6" style="margin-bottom:1rem ;">
                     <select class="form-select form-select-sm" id="selectworksched" aria-label=".form-select-sm example" style="font-size: 11px;" onchange="changeSchedule(this)" name="data[reqtype]">
                        <option selected>Select work schedule</option>
                        <option value="RS">Regular Schedule</option>
                        <option value="LB">Lunch Break</option>
                        <option value="CB">Coffee Break</option>
                        <option value="DO">Day off</option>
                     </select>
                  </div>
                  <div class="col-6">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Effective Date :</span>
                        <input type="date" class="form-control" placeholder="Effective Date" aria-label="Username" aria-describedby="basic-addon1" name="data[eff_date]">
                     </div>
                  </div>
                  <div class="col-6" id="dayoff">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Official Day Off :</span>
                        <input type="date" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[off_dat_off]">
                     </div>
                  </div>
               </div>
               <div class="row" style="padding:0 1rem;" id="timeinout">
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Time In :</span>
                        <input type="time" style="font-size:11px;" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[timein]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Time Out:</span>
                        <input type="time" style="font-size:11px;" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[timeout]">
                     </div>
                  </div>
               </div>
               <div class="row" style="padding:0 1rem;" id="lunchbreak">
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Lunch Break ( Out ) :</span>
                        <input type="time" style="font-size:11px;" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[lunchbreak_out]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Lunch Break ( In ) :</span>
                        <input type="time" style="font-size:11px;" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[lunchbreak_in]">
                     </div>
                  </div>
               </div>
               <div class="row" style="padding:0 1rem;" id="coffeebreak">
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Coffee Break ( Out ) :</span>
                        <input type="time" style="font-size:11px;" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[coffee_out]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Coffee Break ( In ) :</span>
                        <input type="time" style="font-size:11px;" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[coffee_in]">
                     </div>
                  </div>
               </div>
               <div class="row" style="padding:0 1rem;">
                  <div class="col-12">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1"> Remarks :</span>
                        <div class="form-floating">
                           <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; min-height:200px;" name="data[remarks]"></textarea>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row" style="padding: 10px 2rem; text-align:right">
                  <button type="button" class="btn btn-success" style="width:100%" onclick="submitReq(this)">Submit</button>
               </div>
            </div>
         </form>
      </div>
      <!-- SUCCESS -->
      <div class="alert alert-success alert-dismissible" role="alert" style="position: absolute; top:40px; width:100%">
         <h4 class="alert-heading">Well done!</h4>
         <p id="successmsg"></p>
         <hr>
         <input type="button" value="Okay" data-dismiss="alert" onclick=" $('.alert-success').hide()">
      </div>

      <!-- FAILED -->
      <div class="alert alert-danger alert-dismissible" role="alert" style="position: absolute; top:40px; width:100%">
         <h4 class="alert-heading">Failed :(</h4>
         <p id="errormsg"></p>
         <hr>
         <input type="button" value="Okay" data-dismiss="alert" onclick="$('.alert-danger').hide()">
      </div>
   </div>
</div>
<?php require '../layout/footer.php' ?>
<script>
   function changeSchedule(e) {
      switch (e.value) {
         case 'RS':
            $('#timeinout').show()
            $('#coffeebreak').show()
            $('#lunchbreak').show()
            $('#dayoff').hide()
            break;
         case 'CB':
            $('#timeinout').hide()
            $('#lunchbreak').hide()
            $('#dayoff').hide()
            $('#coffeebreak').show()
            break;
         case 'DO':
            $('#dayoff').show()
            $('#coffeebreak').hide()
            $('#lunchbreak').hide()
            $('#timeinout').hide()
            break;
         case 'LB':
            $('#timeinout').hide()
            $('#coffeebreak').hide()
            $('#lunchbreak').show()
            $('#dayoff').hide()
            break;
         default:

      }

   }



   async function submitReq(e) {
      e.innerText = "Please wait ... Loading"
      e.setAttribute('disabled', '')
      const response = await fetch("../controller/transactionController.php", {
         method: "POST",
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: $('#form_changework').serialize() + `&action=CHANGEOFWORKSCHED&emp_no=${$('#select_branchemp').val() ? $('#select_branchemp').val() : '' }`
      })
      const {
         error,
         message
      } = await response.json();
      if (error) {
         $('#errormsg').html(message)
         $('.alert-danger').show()
         e.innerText = "Submit"
         e.removeAttribute('disabled')
      } else {
         $('#successmsg').html(message)
         $('.alert-success').show()
         e.innerText = "Submit"
         e.removeAttribute('disabled')
      }

   }
</script>