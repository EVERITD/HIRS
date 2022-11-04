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
            <form id="OUEform">
               <div class="row" style="padding: 0 1rem; display:flex; align-items:center;justify-content:space-around">
                  <p>Apply for :</p>
                  <div class="col-4">
                     <div class=" form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[typeDay]" id="exampleRadios1" value="OT" checked style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios1">
                           Overtime
                        </label>
                     </div>
                  </div>
                  <div class="col-4">
                     <div class="form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[typeDay]" id="exampleRadios1" value="UT" style="margin-right: 10px;">
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
                        <input class="form-check-input" type="radio" name="data[type_option]" id="exampleRadios1" value="regular" checked style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios1">
                           Regular
                        </label>
                     </div>
                  </div>
                  <div class="col">
                     <div class="form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[type_option]" id="exampleRadios1" value="overtime" checked style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios1">
                           Over night
                        </label>
                     </div>
                  </div>
                  <div class="col">
                     <div class="form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[type_option]" id="exampleRadios1" value="morning" checked style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios1">
                           Morning
                        </label>
                     </div>
                  </div>
                  <div class="col">
                     <div class="form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[type_option]" id="exampleRadios1" value="fye" checked style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios1">
                           Fiscal Year End (FYE)
                        </label>
                     </div>
                  </div>
               </div>


               <div class="row" style="padding: 0 1rem">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm " style="font-size: 11px">Effectivity Date :</span>
                     <input type="date" style="font-size:11px;" name="data[date]" class="form-control eff_date" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>

               <div class="row" style="padding: 0 1rem">
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Hours rendered from :</span>
                        <input type="time" style="font-size:11px;" name="data[hourfrom]" class="form-control" id="hr_from" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px"> TO :</span>
                        <input type="time" style="font-size:11px;" name="data[hourto]" class="form-control" id="hr_to" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>
               </div>


               <div class="row" style="padding: 0 1rem" id="select_typ_day">
                  <p>Type of Day :</p>
                  <div class="col-12">
                     <select class="form-select form-select-sm" id="selecthrs" aria-label=".form-select-sm example" style="font-size: 11px;" name="data[typeofday]">
                        <option selected value="I1">I1 - Regular Day</option>
                        <option value="K1">K1 - SPECIAL HOLIDAY</option>
                        <option value="K2">K2 - LEGAL HOLIDAY/option>
                        <option value="K3">K3 - DAY OFF & LEGAL HOLIDAY</option>
                        <option value="K4">K4 - DAY OFF & SPECIAL HOLIDAY</option>
                        <option value="K5">K5 - REGULAR HOLIDAY</option>
                        <option value="K6">K6 - EXC. DAYOFF & SPECIAL HOLIDAY</option>
                        <option value="K7">K7 - EXCESS LEGAL HOLIDAY</option>
                        <option value="K8">K8 - EXC. DAYOFF & LEGAL HOLIDAY</option>
                        <option value="K9">K9 - EXC. DO & SP</option>
                     </select>
                  </div>
               </div>

               <div class="row" style="padding: 0 1rem; margin-top:10px;">
                  <div class="input-group mb-3">
                     <span class="input-group-text" id="basic-addon1" style="font-size: 11px;"> Remarks :</span>
                     <div class="form-floating">
                        <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" name="data[remarks]" style="border-radius:2px; font-size:11px;padding:10px;"></textarea>
                     </div>
                  </div>

               </div>
            </form>
            <div class="row" style="padding: 10px 2rem; text-align:right">
               <button type="button" class="btn btn-success" style="width:100%" onclick="submitRequest(e)">Submit</button>
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
   let rdoInput = $('input[name="data[typeDay]"]')

   rdoInput.map((_index, _obj) => {
      _obj.addEventListener("click", (e) => {
         console.log(e.target.value)
         if (e.target.value == 'UT') {
            $('#ot_options').css("height", "0px")
            $('#select_typ_day').css("height", "0px")
            $('#select_typ_day').css("overflow", "hidden")
         } else {
            $('#ot_options').css("height", "inherit")
            $('#select_typ_day').css("height", "inherit")
         }
      })
   })



   function checkFields() {
      let remarks = $('#floatingTextarea').val()
      let eff_data = $('.eff_date').val();
      let hr_from = $('#hr_from').val();
      let hr_to = $('#hr_to').val();

      if (remarks == '') {
         $('#floatingTextarea').css("border", '1px solid #b82525')
         return false
      } else if (eff_data == '') {
         $('.eff_date').css("border", '1px solid #b82525')
         return false
      } else if (hr_from == '') {
         $('#hr_from').css("border", '1px solid #b82525')
         return false
      } else if (hr_to == "") {
         $('#hr_to').css("border", '1px solid #b82525')
         return false
      } else {
         return true
      }
   }
   async function submitRequest(e) {

      if (checkFields()) {
         e.innerText = "Please wait ... Loading"
         e.setAttribute('disabled', '')
         let remarks = $('textarea[name="txtRemarks"]').val();
         const response = await fetch("../controller/transactionController.php", {
            method: "POST",
            headers: {
               'Content-type': 'application/x-www-form-urlencoded',
               'Autorization': `Bearer ${$('#token').html()}`
            },
            body: $('#OUEform').serialize() + `&action=OUE&emp_no=${$('#select_branchemp').val() ? $('#select_branchemp').val() : '' }`
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

   }
</script>