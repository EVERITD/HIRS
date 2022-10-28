<?php require '../layout/header.php'; ?>
<style>
   table {
      width: 100%;
   }

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
         <h3 style="font-weight: bold;margin-top: 1rem;">Validations</h3>
         <label for="">All your request are gathered in this table, You can also view its status whether it is Deleted, For Approval, In-Process, Cancelled. <br>
            <i class="bi bi-folder-x" id="deleteBtn" style="font-size:15px; cursor:pointer; color:red"></i> Means you can still cancel your request.
            <i class="bi bi-bookmark-check" id="deleteBtn" style="font-size:15px; cursor:pointer; color:green"></i> Means your request has been confirm whether it is is Deleted, For Approval, In-Process, Cancelled. </label>
         <hr>
         <table style="font-size: 11px;font-weight:bold;padding: 1rem 0; " id="table_id" class="row-border">
            <thead>
               <tr style="background-color: #b82525;color:white; letter-spacing: 1px">
                  <th style="width:20px;">Date</th>
                  <th style="width:50px;">Division</th>
                  <th style="width:50px;">Particulars</th>
                  <th style="width:50px;">Type</th>
                  <th style="width:50px;">Status</th>
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
   // $('#table_id').DataTable()


   let attendanceTable = $('#table_id').DataTable({
      ordering: false,
      searching: false,
      pageLength: 8,
      bLengthChange: false,
      "ajax": {
         "url": "../controller/erfhead.php",
         "headers": {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         "type": "post",
         "data": {
            'action': 'FORVALIDATION'
         },
         "success": (response) => {
            console.log(response)
            if (response.length == 0) {
               attendanceTable.row.add([
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
               ]).draw(false)
            } else {
               response.forEach(item => {
                  let newDate = new Date(item['date'])
                  attendanceTable.row.add([
                     item['encodeDate'],
                     item['divCode'],
                     `<img src="${item['pictFile']}" style="width:50px"><br>${item['name']}<br>${item['deptName']}, ${item['branch']} - ${item['postName']}`,
                     item['leaveName'],
                     item['leaveStat'],
                  ]).draw(false)
               });
            }

         },
         "error": (err) => {
            console.log(err)
         }
      }
   });
   async function getOt() {
      const response = await fetch("../controller/erfhead.php", {
         method: 'POST',
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: 'action=FORVALIDATION'
      })
      const data = await response.json();
      console.log(data)
   }
   // getOt()
</script>