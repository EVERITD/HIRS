<style>
   #newrq {
      height: 0px;
      transition: .5s ease-in-out;
   }

   #adminMenu {
      height: 0px;
      transition: .5s ease-in-out;
   }

   .list-group-item {
      cursor: pointer;
   }

   #newrq.show {
      height: auto;
   }

   #adminMenu.show {
      height: auto;
   }

   a {
      font-size: 11px;
   }

   #listItem {
      transition: .5s ease
   }

   #listItem:hover {
      background-color: #b82525;
      color: white;
      /* transform: translateX(1150%); */
      transform: scale(1.1)
   }
</style>
<?php
$PInfo = $_SESSION['emp_details'];
$Approver = $_SESSION['Approver'];
?>
<div class="offcanvas offcanvas-start " id="offcanvasExample" aria-labelledby="offcanvasExampleLabel" data-bs-backdrop="false" style="width: 350px; visibility:visible;transform: translateX(0%)">
   <div class="" style="background-color:brown; height:40px">
      <div class="logo d-flex" style="height: 100%; align-items:center">
         <img src="../assets/download2.png" alt="" style="height: 100%;">
         <p style="margin: 0 10px; color:white;">Personal Information System</p>
      </div>
   </div>
   <div class="offcanvas-header" style="display: flex;align-items:center; justify-content:center; flex-direction:column;padding:1rem 0!important;">
      <div class="row" style="padding: 0 1rem;">
         <div class="col-3">
            <img src="../assets/gray.jpeg" alt="" style="width: 100%; border-radius: 50%;" />
         </div>
         <div class="col d-flex" style="justify-content: center;flex-direction:column">
            <p style="margin-bottom: 0; font-weight:bold; text-transform:uppercase; font-size:12px!important"><?php echo $PInfo['name'] ? $PInfo['name'] : "" ?></p>
            <p style="margin: 0;font-weight:bold" id="emp_no"><?php echo $PInfo['emp_no'] ? $PInfo['emp_no'] : ""  ?></p>
            <p style="display:none" id="log_name"><?php echo $PInfo['log_name'] ? $PInfo['log_name'] : ""  ?></p>
            <p style="display:none" id="token"><?php echo $_SESSION['token'] ? $_SESSION['token'] : ""  ?></p>
            <p style="margin: 0;font-weight:bold"><?php echo $PInfo['position'] ? $PInfo['position'] : ""  ?></p>
            <p style="margin: 0;">Last Login: <span style="font-weight: bold;"><?php echo $PInfo['log_date'] ? $PInfo['log_date'] : ""  ?></span></p>
            <!-- <p></p> -->
            <!-- <button type="button" class="btn btn-danger">Log out</button> -->
         </div>
      </div>
   </div>

   <div class="offcanvas-body" style="padding:0!important;">
      <div class="dropdown mt-3" style="margin-top: 0!important;">
         <ul class="list-group" style="border-radius: 0px;">
            <?php
            if ($PInfo['pis_controller'] || $PInfo['pis_recruiter'] || $PInfo['is_dept_head'] || $PInfo['log_name'] == "JOHANN.GO" || $PInfo['log_name'] == "GENIVER.PAULAR" || $PInfo['log_name'] == "APPLE JOY.ALOJACIN" || $PInfo['log_name'] == "LUIS PAULO.ASERADO" || $PInfo['log_name'] == 'CHRISTIAN MARVIN.ORSUA') {
               echo '<p style="margin: 0;font-weight:bold;text-transform:uppercase" class="list-group-item list-group-item-action" onclick="showAdminMenu()"><i class="bi bi-shield-lock-fill" style="font-size: 15px;">&nbsp;&nbsp;</i>Admin Access</p>
               <div class="auto-hide" style="overflow:hidden;" id="adminMenu">';
               if ($PInfo['pis_controller']  != false) {
                  echo ' <a id="listItem" href="leave.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Pending Validation</a>';
               }
               if ($PInfo['is_dept_head'] != false) {
                  echo ' <a id="listItem" href="tar.php" style="margin: 0;" class="list-group-item list-group-item-action">- Approvals</a>
                  <a id="listItem" href="leave.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Request History</a>
                  ';
               }
               if ($PInfo['log_name'] == "GENIVER.PAULAR" || $PInfo['log_name'] == "JOHANN.GO" || $PInfo['log_name'] == "LUIS PAULO.ASERADO" || $PInfo['log_name'] == 'CHRISTIAN MARVIN.ORSUA') {
                  echo '<a id="listItem" href="tar.php" style="margin: 0;" class="list-group-item list-group-item-action">- Overtime Approvals</a>';
               }
               if ($PInfo['log_name'] == "JOHANN.GO" || $PInfo['log_name'] == "GENIVER.PAULAR" || $PInfo['log_name'] == "APPLE JOY.ALOJACIN" || $PInfo['log_name'] == "LUIS PAULO.ASERADO" || $PInfo['log_name'] == 'CHRISTIAN MARVIN.ORSUA') {
                  echo '<a id="listItem" href="leaveAbsence.php" style="margin: 0;" class="list-group-item list-group-item-action">- Overtime Report</a>';
               }
               echo '</div>';
            }

            ?>



            <!-- <a id="listItem" href="leave.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Pending Validation</a> -->
            <!-- <a id="listItem" href="leaveAbsence.php" style="margin: 0;" class="list-group-item list-group-item-action">- Overtime Report</a>
               <a id="listItem" href="tar.php" style="margin: 0;" class="list-group-item list-group-item-action">-Overtime Approvals</a> -->




            <a id="listItem" href="attendance.php" class="list-group-item list-group-item-action" aria-current="true">
               <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-person-video2" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;Attendance</p>
            </a>

            <p style="margin: 0;font-weight:bold;text-transform:uppercase" class="list-group-item list-group-item-action" onclick="showNewRequest()"><i class="bi bi-receipt-cutoff" style="font-size: 15px;">&nbsp;&nbsp;</i>New Request</p>
            <div class="auto-hide" style="overflow:hidden;" id="newrq">
               <a id="listItem" href="leave.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Leave</a>
               <a id="listItem" href="leaveAbsence.php" style="margin: 0;" class="list-group-item list-group-item-action">- Leave of Absence</a>
               <a id="listItem" href="tar.php" style="margin: 0;" class="list-group-item list-group-item-action">- Temporary Attendance Record</a>
               <a id="listItem" href="ot.php" style="margin: 0;" class="list-group-item list-group-item-action">- Overtime, Undertime and Early Leave</a>
               <a id="listItem" href="offsettings.php" style="margin: 0;" class="list-group-item list-group-item-action">- Offsetting</a>
               <a id="listItem" href="changework.php" style="margin: 0;" class="list-group-item list-group-item-action">- Change of Work Schedule</a>
               <a id="listItem" href="itenerary.php" style="margin: 0;" class="list-group-item list-group-item-action">- Itinerary Approval</a>
            </div>
            <!-- </a> -->
            <a id="listItem" href="requests.php" class="list-group-item list-group-item-action">
               <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-pin-angle-fill" style="font-size: 15px;"></i> &nbsp;My Requests</p>
            </a>

            <li id="listItem" href="attendance.php" class="list-group-item list-group-item-action" aria-current="true" data-bs-toggle="modal" data-bs-target="#staticBackdrop" onclick="getpostedtransactions()">
               <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-award-fill" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;My Credits & Other Informations</p>
            </li>

         </ul>
      </div>
   </div>
</div>

<script>
   $(document).ready(() => {
      $('.btn-danger').focusout()
      console.log('test')
      showNewRequest()
   })

   function showNewRequest() {
      if (!$('#newrq').hasClass("show")) {
         $('#newrq').addClass('show')
      } else {
         $('#newrq').removeClass('show')

      }
   }


   function showAdminMenu() {
      if (!$('#adminMenu').hasClass("show")) {
         $('#adminMenu').addClass('show')
      } else {
         $('#adminMenu').removeClass('show')

      }
   }
</script>