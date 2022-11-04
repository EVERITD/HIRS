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
         <div class="leaveContainer" tabindex="1" style="box-shadow: 10px 10px 32px -18px rgba(0,0,0,1); width: 800px; padding: 0px;">
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Leave</p>
            <form id="leaveform">
               <div style="padding: 1rem; display:flex; justify-content:space-between; align-items:center">
                  <div class="form-check">
                     <input class="form-check-input" type="radio" name="data[leave_type]" id="exampleRadios1" value="VL" checked>
                     <label class="form-check-label" for="exampleRadios1">
                        Vacation Leave
                     </label>
                  </div>
                  <div class="form-check">
                     <input class="form-check-input" type="radio" name="data[leave_type]" id="exampleRadios1" value="HV" checked>
                     <label class="form-check-label" for="exampleRadios1">
                        Vacation Leave ( Half Day )
                     </label>
                  </div>
                  <div class="form-check">
                     <input class="form-check-input" type="radio" name="data[leave_type]" id="exampleRadios1" value="SL" checked>
                     <label class="form-check-label" for="exampleRadios1">
                        Sick Leave
                     </label>
                  </div>
                  <div class="form-check">
                     <input class="form-check-input" type="radio" name="data[leave_type]" id="exampleRadios2" value="HS">
                     <label class="form-check-label" for="exampleRadios2">
                        Sick Leave ( Half Day )
                     </label>
                  </div>
                  <div class="form-check">
                     <input class="form-check-input" type="radio" name="data[leave_type]" id="exampleRadios2" value="ML">
                     <label class="form-check-label" for="exampleRadios2">
                        Maternity Leave
                     </label>
                  </div>
               </div>
               <div class="row" style="padding: 0 1.5rem">
                  <div class="col">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Date Range :</span>
                        <input type="text" name="data[dates]" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>
                  <div class="col">
                     <select class="form-select form-select-sm" id="selecthrs" aria-label=".form-select-sm example" style="font-size: 11px;" name="data[hours]">
                        <option selected>Select hours to apply</option>
                        <option value="4.0">4hrs</option>
                        <option value="4.5">4hrs and 30min</option>
                        <option value="5.0">5hrs</option>
                        <option value="5.5">5hrs and 30min</option>
                        <option value="6.0">6hrs</option>
                        <option value="7.0">7hrs</option>
                        <option value="8.0">8hrs</option>
                        <option value="9.0">9hrs</option>
                        <option value="9.5">9hrs and 30min</option>
                     </select>
                  </div>
               </div>
               <div class="row" style="padding: 0 1.5rem;">
                  <div class="form-floating">
                     <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; min-height:300px;" name="data[reason]"></textarea>
                  </div>
               </div>
            </form>
            <div class="row" style="padding: 10px 2rem; text-align:right">
               <button type="button" class="btn btn-success" style="width:250px" onclick="submitreq(this)">Submit</button>
            </div>
         </div>
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
   let radioBtns = $('input[name="data[leave_type]"]')
   $('input[name="data[dates]"]').daterangepicker();
   radioBtns.map((index, rdo) => {
      rdo.addEventListener('click', (e) => {
         console.log(e.target.value)
         if (e.target.value == "ML") {
            $('#selecthrs').css("display", "none")
         } else {
            $('#selecthrs').css("display", "block")
         }
      })
   })


   function hideLeavehrs() {
      $('#selecthrs').css()
   }


   async function submitreq(e) {
      e.innerText = "Please wait ... Loading"
      e.setAttribute('disabled', '')
      const response = await fetch("../controller/transactionController.php", {
         method: "POST",
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: 'action=LEAVE&' + $('#leaveform').serialize()
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