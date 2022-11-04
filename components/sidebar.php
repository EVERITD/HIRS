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

   .select2-selection__placeholder {
      font-size: 11px;
   }

   .select2-search__field {
      font-size: 11px;
   }

   .select2-results__option.select2-results__message {
      font-size: 11px;
   }

   .select2-selection__rendered {
      font-size: 11px;
   }
</style>
<?php
$PInfo = $_SESSION['emp_details'];
$Approver = $_SESSION['Approver'];
$USER = $_SESSION['user']['user_name'];

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
            <i class="bi bi-person-circle" style="font-size: 350%;"></i>
         </div>
         <div class="col d-flex" style="justify-content: center;flex-direction:column">
            <p style="margin-bottom: 0; font-weight:bold; text-transform:uppercase; font-size:12px!important"><?php echo $PInfo['name'] ? $PInfo['name'] : "" ?></p>
            <p style="margin: 0;font-weight:bold" id="emp_no"><?php echo $PInfo['emp_no'] ? $PInfo['emp_no'] : ""  ?></p>
            <p style="display:none" id="log_name"><?php echo strtolower($USER) ? strtolower($USER) : ""  ?></p>
            <p style="display:none" id="id_prefix"><?php echo  $PInfo['id_prefix']  ? $PInfo['id_prefix'] : ""  ?></p>
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
            if ($PInfo['id_prefix'] != '99') {
               echo '<li id="listItem" class="list-group-item list-group-item-action">
                  <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-people" style="font-size: 15px;"></i> &nbsp;Apply Request for branch employee :</p>
                  <select class="form-select form-select-sm" id="select_branchemp" aria-label=".form-select-sm example" style="font-size: 11px;" name="data[ot_code]">
                     <option selected value="">Select overtime to apply</option>
                  </select>
               </li>';
            }

            if ($PInfo['pis_controller'] || $PInfo['pis_recruiter'] || $PInfo['is_dept_head'] || strtolower($USER) == "johann.go" || strtolower($USER) == "gene.paular" || strtolower($USER) == "APPLE JOY.ALOJACIN" || strtolower($USER) == "luis.aserado" || strtolower($USER) == 'marvin.orsua') {
               echo '<p style="margin: 0;font-weight:bold;text-transform:uppercase" class="list-group-item list-group-item-action" onclick="showAdminMenu()"><i class="bi bi-shield-lock-fill" style="font-size: 15px;">&nbsp;&nbsp;</i>Admin Access</p>
               <div class="auto-hide" style="overflow:hidden;" id="adminMenu">';
               if ($PInfo['pis_controller']  != false || strtolower($USER) == 'marvin.orsua') {
                  echo ' <a id="listItem" href="pendingValidation.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Pending Validation</a>';
               }
               if ($PInfo['is_dept_head'] != false || strtolower($USER) == 'marvin.orsua') {
                  echo ' <a id="listItem" href="approval.php" style="margin: 0;" class="list-group-item list-group-item-action">- Approvals</a>
                  <a id="listItem" href="history.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Request History</a>
                  ';
               }
               if (strtolower($USER) == "gene.paular" || strtolower($USER) == "johann.go" || strtolower($USER) == "luis.aserado" || strtolower($USER) == 'marvin.orsua') {
                  echo '<p id="listItem" style="margin: 0;" class="list-group-item list-group-item-action" onclick="open_overtime()">- Overtime Approvals</p>';
               }
               if (strtolower($USER) == "johann.go" || strtolower($USER) == "gene.paular" || strtolower($USER) == "apple.monteloyola" || strtolower($USER) == "luis.aserado" || strtolower($USER) == 'marvin.orsua') {
                  echo '<p id="listItem" onclick="open_ot_report()" style="margin: 0;" class="list-group-item list-group-item-action">- Overtime Report</p>';
               }
               echo '</div>';
            }


            if ($PInfo['id_prefix'] == '99' || $PInfo['id_prefix'] == '58') {
               echo ` <a id="listItem" href="attendance.php" class="list-group-item list-group-item-action" aria-current="true">
                  <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-person-video2" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;Attendance</p>
               </a>`;
            }


            if ($PInfo['id_prefix'] != '99') {
               echo '
                  <a id="listItem" href="http://localhost/PISREPORTS_CW/other/master.php?search=1&empno=' . $PInfo['emp_no'] . '" class="list-group-item list-group-item-action" aria-current="true" onclick="">
                     <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-building" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;All Submitted Request</p>
                  </a>

                  <a id="listItem" href="http://localhost/PISREPORTS_CW/other/master.php?search=0&empno=' . $PInfo['emp_no'] . '" class="list-group-item list-group-item-action" aria-current="true" onclick="">
                     <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-file-binary-fill" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;Other New Request</p>
                  </a> 
                  ';
            }

            ?>



            <p style="margin: 0;font-weight:bold;text-transform:uppercase" class="list-group-item list-group-item-action" onclick="showNewRequest()"><i class="bi bi-receipt-cutoff" style="font-size: 15px;">&nbsp;&nbsp;</i>New Request</p>
            <div class="auto-hide" style="overflow:hidden;" id="newrq">
               <?php
               if ($PInfo['id_prefix'] == '99') {
                  echo '<a id="listItem" href="leave.php" style="margin: 0; font-size:11px;" class="list-group-item list-group-item-action">- Leave</a>';
               }
               ?>

               <a id="listItem" href="leaveAbsence.php" style="margin: 0;" class="list-group-item list-group-item-action">- Leave of Absence</a>
               <a id="listItem" href="tar.php" style="margin: 0;" class="list-group-item list-group-item-action">- Temporary Attendance Record</a>
               <a id="listItem" href="ot.php" style="margin: 0;" class="list-group-item list-group-item-action">- Overtime, Undertime and Early Leave</a>
               <a id="listItem" href="offsettings.php" style="margin: 0;" class="list-group-item list-group-item-action">- Offsetting</a>
               <a id="listItem" href="changework.php" style="margin: 0;" class="list-group-item list-group-item-action">- Change of Work Schedule</a>
               <?php
               if ($PInfo['id_prefix'] == '99') {
                  echo '<a id="listItem" href="itenerary.php" style="margin: 0;" class="list-group-item list-group-item-action">- Itinerary Approval</a>';
               }
               ?>

            </div>
            <!-- </a> -->
            <a id="listItem" href="requests.php" class="list-group-item list-group-item-action">
               <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-pin-angle-fill" style="font-size: 15px;"></i> &nbsp;My Requests</p>
            </a>


            <li id="listItem" href="attendance.php" class="list-group-item list-group-item-action" aria-current="true" data-bs-toggle="modal" data-bs-target="#staticBackdrop" onclick="getpostedtransactions()">
               <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-award-fill" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;My Credits & Other Informations</p>
            </li>

            <form method="POST" id="logoutform">
               <li id="listItem" href="attendance.php" class="list-group-item list-group-item-action" aria-current="true" onclick="$('#logoutform').submit()">
                  <p style="margin: 0; font-weight:bold;text-transform:uppercase"><i class="bi bi-door-open" style="font-size: 15px;"></i>&nbsp;&nbsp;&nbsp;Log out</p>
                  <input type="text" name="action" value="Logout" style="display:none">
               </li>
            </form>
         </ul>
      </div>
   </div>
</div>

<script>
   $(document).ready(() => {
      let id_prefix = $('#id_prefix').text()
      $('.btn-danger').focusout()
      showNewRequest()

      $('#select_branchemp').select2({
         placeholder: 'Search by Employee Number, Firstname, Lastname',
         allowClear: true,
         ajax: {
            url: '../controller/userController.php',
            dataType: "json",
            headers: {
               'Autorization': `Bearer ${$('#token').html()}`
            },
            type: 'POST',
            'data': (params) => {
               var query = {
                  'idprefix': id_prefix,
                  'action': 'getbranchemp'
               }
               return query;
            },
            processResults: function({
               data: {
                  bremployee
               }
            }) {

               return {
                  results: $.map(bremployee, (item) => {
                     return {
                        id: item.empno,
                        text: item.name
                     }
                  })
               }

            },
         },

      });

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

   function open_ot_report() {
      let username = $('#log_name').text().toLocaleLowerCase();

      $.ajax({
         type: "POST",
         dataType: "json",
         url: "http://localhost/PISREPORTS_CW/otreport.php",
         data: {
            isajax: 1,
            function: "openpage",
            username: username,
         },
         success: function(result) {
            if (result.status == 1) {
               window.open(`http://localhost/PISREPORTS_CW/otreport.php?user=${username}`, "_blank");
            }
         },
         error: function(err) {
            alert("Connection Error, Please try again. If this error persist please contact the administrator")
         }

      });
   }

   function open_overtime() {
      let username = $('#log_name').text().toLocaleLowerCase();
      $.ajax({
         type: "POST",
         dataType: "json",
         url: "http://localhost/PISREPORTS_CW/overtime.php",
         data: {
            isajax: 1,
            function: "openpage",
            username: username,
         },
         success: function(result) {
            if (result.status == 1) {
               window.open(`http://localhost/PISREPORTS_CW/overtime.php?user=${username}`, "_blank");
            }
         },
         error: function() {
            alert("Connection Error, Please try again. If this error persist please contact the administrator")
         }

      });
   }
   // async function getbranchemployees() {

   //    if (id_prefix != '') {
   //       const response = await fetch("../controller/userController.php", {
   //          headers: {
   //             'Content-type': 'application/x-www-form-urlencoded'
   //          },
   //          method: 'POST',
   //          body: `idprefix=${id_prefix}&action=getbranchemp`
   //       })
   //       const data = await response.json()
   //       console.log(data)
   //    }

   // }


   // getbranchemployees()
</script>