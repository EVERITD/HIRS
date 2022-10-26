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
         <div class="">
            <h3 style="font-weight: bold;margin-top: 1rem;"><i class="bi bi-person-video2" style="margin-right:1rem"></i>My Attendance</h3>

            <label for="">Hi! From this page you may view your attedance from your [TIME IN] to [TIME OUT], <br>If there are questions/concerns about your attendance please contact our HR Deparment . </label>
         </div>

         <table style="font-size: 11px;font-weight:bold;padding: 1rem 0; " id="table_id" class="row-border">
            <thead>
               <tr style="background-color: #b82525;color:white; letter-spacing: 1px">
                  <th style="width:20px;">Day</th>
                  <th style="width:50px;">Date</th>
                  <th style="width:50px;">IN 1</th>
                  <th style="width:50px;">OUT 1</th>
                  <th style="width:50px;">IN 2</th>
                  <th style="width:50px;">OUT 2</th>
                  <th style="width:50px;">IN 3</th>
                  <th style="width:50px;">OUT 3</th>
                  <th style="width:50px;">IN 4</th>
                  <th style="width:50px;">OUT 4</th>
                  <th style="width:50px;">Status</th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td>FRI</td>
                  <td> 09/28/2022</td>
                  <td>8:30 am</td>
                  <td>6:30 am</td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
               </tr>

            </tbody>
         </table>
      </div>
   </div>

</div>
<?php require '../layout/footer.php' ?>
<script>
   let attendanceTable = $('#table_id').DataTable({
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
            "action": "getattendance"
         },
         "success": (response) => {
            if (response.length == 0) {
               attendanceTable.row.add([
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-'

               ]).draw(false)
            } else {
               response.forEach(item => {
                  let newDate = new Date(item['date'])
                  attendanceTable.row.add([
                     `<p style="font-weight:bold; font-size:10px!important;"> ${getDay(newDate.getDay())} </p>`,
                     item['date'],
                     item['in1'],
                     item['out1'],
                     item['in2'],
                     item['out2'],
                     item['in3'],
                     item['out3'],
                     item['in4'],
                     item['out4'],
                     item['status'] ? item['status'] : '-',

                  ]).draw(false)
               });
            }

         },
         "error": (err) => {
            console.log(err)
         }
      }
   });

   function getDay(index) {
      let days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
      return days[index];
   }

   // async function getAttendance() {
   //    const response = await fetch("../controller/userController.php", {
   //       headers: {
   //          'Content-type': 'application/x-www-form-urlencoded'
   //       },
   //       method: 'POST',
   //       body: `emp_no=${$('#emp_no').html()}&action=getattendance`
   //    })
   //    const data = await response.json();
   //    if (data) {
   //       console.log(data)
   //    }
   // }
</script>