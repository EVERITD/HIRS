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
         <div class="leaveContainer" tabindex="1" style="box-shadow: 10px 10px 32px -18px rgba(0,0,0,1); width: 400px; padding: 0px;">
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Leave of Absence</p>
            <div class="row" style="padding: 0 1rem">
               <div class="col">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Effectivity Date: :</span>
                     <input type="text" style="font-size:11px;" name="dates" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm" id="dates">
                  </div>
               </div>
            </div>
            <div class="row" style="padding: 0 1rem">
               <p>Remarks :</p>
            </div>
            <div class="row" style="padding: 0 1rem">
               <div class="col">
                  <div class="form-floating">
                     <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; min-height:200px;padding-top:10px" id="txtRemarks" name="txtRemarks"></textarea>
                  </div>
               </div>
            </div>
            <div class="row" style="padding: 10px 2rem; text-align:right">
               <button type="button" class="btn btn-success" style="width:100%" onclick="submitLeave()">Submit</button>
            </div>
         </div>
      </div>
   </div>
</div>
<?php require '../layout/footer.php' ?>
<script>
   $('input[name="dates"]').daterangepicker();

   async function submitLeave() {
      let remarks = $('textarea[name="txtRemarks"]').val();
      let endDate = $('#dates').data('daterangepicker').endDate.format("YYYY-MM-DD");
      let startDate = $('#dates').data('daterangepicker').startDate.format("YYYY-MM-DD");
      let empno = $('#emp_no').html()
      let log_name = $('#log_name').html()

      const response = await fetch("../controller/transactionController.php", {
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         method: 'POST',
         body: `action=LeaveOfAbsence&remarks=${remarks}&dtefrm=${startDate}&dteto=${endDate}&isAuthorized=LOAA&request_type=leave_of_absence`
      })
      const data = await response.json()
      if (data['error']) {
         alert(data['message']);
      } else {
         alert(data['message']);
      }
      // if(data)
   }
</script>