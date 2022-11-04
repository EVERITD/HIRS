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

   #table_id tr {
      border-bottom: 1px solid black;
      border-width: inherit !important;
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
      <div class="container" style="height:calc(100vh - 60px);overflow-y:scroll!important;padding:5px 2rem">
         <div class="">
            <h3 style="font-weight: bold;margin-top: 1rem;"><i class="bi bi-shield-lock-fill" style="margin-right:1rem"></i>Pending for approvals</h3>
            <label for="">All request of your handled team.
               <i class="bi bi-hand-thumbs-down" id="deleteBtn" style="font-size:15px; cursor:pointer; color:red"></i>Approve.
               <i class="bi bi-hand-thumbs-up" id="deleteBtn" style="font-size:15px; cursor:pointer; color:green"></i> Cancel </label>
         </div>
         <hr>
         <table style=" font-size: 11px;font-weight:bold;padding: 1rem 0; " class=" row-border" id="table_id">
            <thead>
               <tr style="background-color: #b82525;color:white; letter-spacing: 1px">
                  <th style="width:50px;">Control #</th>
                  <th style="width:85px;">Date Created</th>
                  <th style="width:100px;">Effective Date</th>
                  <th style="width:200px;">Particular</th>
                  <th style="width:150px;">Type</th>
                  <th style="">Status</th>
                  <th style="width:100px;">Action</th>
               </tr>
            </thead>
            <tbody id="table-data-rows">

            </tbody>
         </table>
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


      <!-- //modal -->
      <div class="modal" tabindex="-1" id="myModal">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header" style="background-color:#b82525;">
                  <h5 class="modal-title" style="font-size: 14px;color:white;font-weight:bold;letter-spacing:1px; text-transform:uppercase">Disapprove Request</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                  <p>Hi! We are about to disapprove request no <span id='drrq_no' style="font-weight:bold">123123123</span> of <span id="drrq_name" style="font-weight:bold"></span>, May we know why ? or Any comments about the request ?</p>

                  <div class="form-floating">
                     <textarea class="form-control disapproveremarks" placeholder="Leave a comment here" id="floatingTextarea" style="font-size: 13px;"></textarea>
                     <label for="floatingTextarea">Comments</label>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" onclick="submitdisapprove()" data-bs-dismiss="modal">Submit</button>
               </div>
            </div>
         </div>
      </div>
      <!-- //modal -->


   </div>
</div>
<?php require '../layout/footer.php' ?>
<script>
   let requestTable = $('#table_id').DataTable({
      ordering: false,
      searching: false,
      pageLength: 8,
      bLengthChange: false,
      "ajax": {
         "url": "../controller/transactionController.php",
         "type": "post",
         "headers": {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         "data": {
            'action': 'approvals'
         },
         "success": ({
            data,
            error
         }) => {
            if (error) {
               requestTable.row.add([
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
               ]).draw(false)
            } else {
               requestTable.clear()
               data.forEach(item => {
                  let name = item['NAME'].replace('()', '')
                  requestTable.row.add([
                     `${item['controlno']}`,
                     `${item['encoded_date']}`,
                     `${item['date_Ffrom'] == item['date_Fto'] ? item['date_Ffrom'] :  item['date_Ffrom'] + ' - ' + item['date_Fto'] }`,
                     `<p style="margin:0px; font-weight:bold">${name}<p> ${item['reason']}`,
                     `${item['leave_name']}`,
                     `<p style="margin:0px; color:green">${item['apphead']}</p>`,
                     `
                        <button  type="button" class="btn btn-success" onclick="submitdata('${item['controlno']}')">   
                           <i class="bi bi-hand-thumbs-up" style="font-size:15px; cursor:pointer"></i>
                        </button>
                        
                        <button  type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#myModal" onclick="handleShowReason('${item['controlno']}', '${name}')">   
                           <i class="bi bi-hand-thumbs-down" style="font-size:15px; cursor:pointer" ></i>
                        </button>
                        
                     `,
                  ]).draw(false)
               });

            }
         }
      }
   });

   async function submitdata(id) {
      const response = await fetch("../controller/transactionController.php", {
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         method: 'POST',
         body: `controlid=${id}&action=approvereq`
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
      $('#table_id').DataTable().ajax.reload();
   }

   function handleShowReason(controlno, name) {
      $('#drrq_no').text(controlno)
      $('#drrq_name').text(name)
   }

   async function submitdisapprove() {
      const response = await fetch("../controller/transactionController.php", {
         method: 'POST',
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: `controlno=${$('#drrq_no').text()}&remarks=${$('textarea.disapproveremarks').val()}&action=deletereq`
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
      $('#table_id').DataTable().ajax.reload();
   }

   // getData()
</script>