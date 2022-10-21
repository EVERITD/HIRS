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
            <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Temporary Attendance Record</p>
            <form id="tarform">
               <div class="row" style="padding:0 1rem;">
                  <div class="col-12">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Effective Date :</span>
                        <input type="date" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[effdte]">
                     </div>
                  </div>
                  <div class="col-12">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Time In:</span>
                        <input type="time" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[timein]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Launch Break ( OUT ) :</span>
                        <input type="time" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[lbOut]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Launch Break ( IN ) :</span>
                        <input type="time" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[lbIn]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Coffee Break ( OUT ) :</span>
                        <input type="time" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[cbOut]">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Coffee Break ( IN ) :</span>
                        <input type="time" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[cbIn]">
                     </div>
                  </div>
                  <div class="col-12">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1"> Time Out :</span>
                        <input type="time" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="data[timeout]">
                     </div>
                  </div>

                  <div class="col-12">
                     <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1"> Remarks :</span>
                        <div class="form-floating">
                           <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; min-height:200px;padding-top:10px" name="data[remarks]"></textarea>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
            <div class="row" style="padding: 10px 2rem; text-align:right">
               <button type="button" class="btn btn-success" style="width:100%" onclick="submitrequest()">Submit</button>
            </div>
         </div>
      </div>
   </div>
</div>
<?php require '../layout/footer.php' ?>
<script>
   $('input[name="dates"]').daterangepicker();
   async function submitrequest() {
      const response = await fetch("../controller/transactionController.php", {
         method: "POST",
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: $('#tarform').serialize() + '&action=TAR'
      })

      const data = await response.json();
      if (data) {
         alert(data.message)
      }
   }
</script>