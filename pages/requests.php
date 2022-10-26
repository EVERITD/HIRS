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
            <h3 style="font-weight: bold;margin-top: 1rem;"><i class="bi bi-pin-angle" style="margin-right: 10px;"></i>My Submitted Requests</h3>
            <label for="">All your request are gathered in this table, You can also view its status whether it is Deleted, For Approval, In-Process, Cancelled. <br>
               <i class="bi bi-folder-x" id="deleteBtn" style="font-size:15px; cursor:pointer; color:red"></i> Means you can still cancel your request.
               <i class="bi bi-bookmark-check" id="deleteBtn" style="font-size:15px; cursor:pointer; color:green"></i> Means your request has been confirm whether it is is Deleted, For Approval, In-Process, Cancelled. </label>
         </div>

         <table style=" font-size: 11px;font-weight:bold;padding: 1rem 0; " class=" row-border" id="table_id">
            <thead>
               <tr style="background-color: #b82525;color:white; letter-spacing: 1px">
                  <th style="width:50px;">Control #</th>
                  <th style="width:85px;">Date Created</th>
                  <th style="width:100px;">Effective Date</th>
                  <th style="width:200px;">Particular</th>
                  <th style="width:150px;">Type</th>
                  <th>Appr. Date</th>
                  <th>Status</th>
                  <th style="width:20px;">Action</th>
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
         "url": "../controller/userController.php",
         "contentType": "application/x-www-form-urlencoded",
         "type": "post",
         "data": {
            "emp_no": $('#emp_no').html(),
            "action": "getrequests"
         },
         "success": (response) => {
            requestTable.clear();
            response.forEach(item => {
               let newDate = new Date(item['date'])
               let status = item['leavestatus'];
               requestTable.row.add([
                  `<p style="font-weight:bold;font-size:10px!important;">${item['controlno']}</p>`,
                  item['encoded_date'] ? item['encoded_date'] : '-',
                  item['date_Ffrom'] + ' ' + item['time_from'] + ' ' + item['date_Fto'] + ' ' + item['time_to'],
                  item['reason'] ? item['reason'] : '-',
                  item['leave_name'] ? item['leave_name'] : '-',
                  item['approved_date'] ? item['approved_date'] : '-',
                  getstatus(status),
                  genAction(status, item['controlno']),
               ]).draw(false)

            });


            // $('#deleteBtn').hover(() => {
            //    if ($('#deleteBtn').hasClass('bi-folder-x')) {
            //       console.log('here')
            //       $('#deleteBtn').removeClass('bi-folder-x')
            //       $('#deleteBtn').addClass('bi-folder-minus')
            //    } else {
            //       console.log('there')
            //       $('#deleteBtn').removeClass('bi-folder-minus')
            //       $('#deleteBtn').addClass('bi-folder-x')
            //    }

            // })
         },
         "error": (err) => {
            console.log(err)
            requestTable.row.add([
               `-`,
               '-',
               '-',
               '-',
               '-',
               '-',
               '-',
               '-',
            ]).draw(false)

         }
      }
   });

   function getstatus(status) {
      if (status == "Posted" || status == "In-Process") {
         return `<p style='color:green;font-size:10px!important;'>${status}</p>`
      } else if (status == "Approved") {
         return `<p style='color:blue;font-size:10px!important;'>${status}</p>`
      } else if (status == "Deleted" || status == "Cancelled") {
         return `<p style='color:red;font-size:10px!important;'>${status}</p>`
      } else {
         return status
      }
   }

   function genAction(status, controlno) {

      if (status == "Posted" || status == "In-Process" || status == "Deleted" || status == "Cancelled" || status == 'Approved') {
         return `<i class="bi bi-bookmark-check" style="font-size:15px;color:green"></i>`
      } else {
         return `<i class="bi bi-folder-x" id="deleteBtn" style="font-size:15px; cursor:pointer; color:red" onclick="handleDelete('${controlno}')" onmouseover="handlemouseover(this)" onmouseleave="handlemouseover(this)"></i>`
      }
   }

   function handlemouseover(e) {
      let classes = e.className
      if (classes.includes('bi-folder-x')) {
         e.classList.remove("bi-folder-x")
         e.classList.add('bi-folder-minus')
      } else {
         e.classList.add('bi-folder-x')
         e.classList.remove('bi-folder-minus')
      }
   }

   async function handleDelete(controlno) {

      const response = await fetch("../controller/transactionController.php", {
         method: 'POST',
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: `controlno=${controlno}&action=deleterequest`
      })
      const {
         error,
         message
      } = await response.json()
      if (error) {
         $('#errormsg').html(message)
         $('.alert-danger').show()
      } else {
         $('#successmsg').html(message)
         $('.alert-success').show()
         $('#table_id').DataTable().ajax.reload();
      }

   }
</script>