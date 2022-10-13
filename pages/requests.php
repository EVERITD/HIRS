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
      <div class="container" style="height:calc(100vh - 50px);overflow-y:scroll!important;padding:5px 2rem">
         <table style="font-size: 11px;font-weight:bold;padding: 1rem 0; " class="row-border" id="table_id">
            <thead>
               <tr style="background-color: #b82525;color:white; letter-spacing: 1px">
                  <th style="width:50px;">Control #</th>
                  <th style="width:90px;">Date Created</th>
                  <th style="width:150px;">Effective Date</th>
                  <th style="width:200px;">Particular</th>
                  <th style="width:150px;">Type</th>
                  <th>Approved Date</th>
                  <th>Status</th>
                  <th style="width:20px;">Action</th>
               </tr>
            </thead>
            <tbody>

            </tbody>
         </table>
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
            response.forEach(item => {
               let newDate = new Date(item['date'])
               let status = item['leavestatus'];
               console.log(item);
               requestTable.row.add([
                  `<p style="font-weight:bold;font-size:10px!important;">${item['controlno']}</p>`,
                  item['encoded_date'] ? item['encoded_date'] : '-',
                  item['date_Ffrom'] + ' ' + item['time_from'] + ' ' + item['date_Fto'] + ' ' + item['time_to'],
                  item['reason'] ? item['reason'] : '-',
                  item['leave_name'] ? item['leave_name'] : '-',
                  item['approved_date'] ? item['approved_date'] : '-',
                  getstatus(status),
                  '-',
               ]).draw(false)
            });
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
</script>