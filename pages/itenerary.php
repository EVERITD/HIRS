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
            <form id="iarform">
               <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Leave</p>
               <div style="padding: 1rem; display:flex;  align-items:center">
                  <div class="form-check">
                     <input class="form-check-input" type="radio" name="data[iarType]" id="exampleRadios1" value="BR" checked>
                     <label class="form-check-label" for="exampleRadios1">
                        Branch
                     </label>
                  </div>
                  <div class="form-check" style="margin-left: 10px;">
                     <input class="form-check-input" type="radio" name="data[iarType]" id="officailbuss" value="OB" checked>
                     <label class="form-check-label" for="officailbuss">
                        Official Business/Field Work
                     </label>
                  </div>
               </div>
               <div class="row" style="padding: 0 1.5rem">
                  <div class="col-12">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Effective Date :</span>
                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" name="data[eff_date]">
                     </div>
                  </div>

               </div>
               <div class="row" style="padding: 0 1.5rem">
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Time Applicable :</span>
                        <input type="time" name="data[timeto]" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">To :</span>
                        <input type="time" name="data[timefrom]" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>

               </div>
               <div class="row" style="padding: 0 1.5rem;">
                  <div class="form-floating">
                     <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; padding-top:10px" name="data[remarks]"></textarea>
                  </div>
               </div>
               <div class="row" style="padding: 10px 2rem; text-align:right">
                  <button type="button" class="btn btn-success" style="width:250px" onclick="submitReq()">Submit</button>
               </div>
            </form>
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
   let radioBtns = $('input[name="exampleRadios"]')
   $('input[name="data[eff_date]"]').daterangepicker();
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

   async function submitReq() {
      let xdata = $('#iarform').serialize()
      const response = await fetch("../controller/transactionController.php", {
         method: 'POST',
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: xdata + '&action=ITENARYAPPROVAL'
      })
      const {
         error,
         message
      } = await response.json();
      if (error) {
         $('#errormsg').html(message)
         $('.alert-danger').show()
      } else {
         $('#successmsg').html(message)
         $('.alert-success').show()
      }
   }
</script>