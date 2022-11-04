<style>
   #leaveInfo td {
      font-size: 12px !important;
      padding: 5px;
   }
</style>

<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="border-radius: 3px; width: 500px;">
         <div class="modal-header" style="background-color: #b82525; border-radius: 3px;">
            <p class="modal-title" id="staticBackdropLabel" style="font-weight:bold; font-size: 12px!important; text-transform:uppercase; letter-spacing: 1px; color:white">My Credits & Other Informations</p>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-12">
                  <p style="font-weight: bold; text-transform:uppercase">Leave Information :</p>
                  <table border="1" style="width: 100%; padding:5px" id="leaveInfo">
                     <thead>
                        <tr>
                           <td style="font-weight: bold;border-bottom:1px solid black">&nbsp</td>
                           <td colspan="2" style="font-weight: bold;border-bottom:1px solid black">Earned</td>
                           <td colspan="2" style="font-weight: bold;border-bottom:1px solid black">Filed</td>
                           <td colspan="2" style="font-weight: bold;border-bottom:1px solid black">Balance</td>
                        </tr>
                        <tr>
                           <td style="border-bottom:1px solid black">&nbsp</td>
                           <td style="border-bottom:1px solid black">Days</td>
                           <td style="border-bottom:1px solid black; border-right:1px solid black;">Hrs</td>
                           <td style="border-bottom:1px solid black">Days</td>
                           <td style="border-bottom:1px solid black; border-right:1px solid black;">Hrs</td>
                           <td style="border-bottom:1px solid black">Days</td>
                           <td style="border-bottom:1px solid black">Hrs</td>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                           <td style="font-size: 13px!important;font-weight:bold;border-right:1px solid black;">VL</td>
                           <td id="earnvldays">0</td>
                           <td style="border-right:1px solid black;" id="earnvlhrs">0</td>
                           <td id="filedvldays"></td>
                           <td id="filedvlhrs" style="border-right:1px solid black;">0</td>
                           <td id="balvldays">0</td>
                           <td id="balvlhrs">0</td>
                        </tr>
                        <tr>
                           <td style="font-size: 13px!important;font-weight:bold;border-right:1px solid black;">SL</td>
                           <td id="earnsldays">0</td>
                           <td style="border-right:1px solid black;" id="earnslhrs">0</td>
                           <td id="filedsldays">0</td>
                           <td id="filedslhrs" style="border-right:1px solid black;">0</td>
                           <td id="balsldays">0</td>
                           <td id="balslhrs">0</td>
                        </tr>

                     </tbody>
                  </table>
               </div>
               <div class="col-12">
                  <hr>
                  <p style="font-weight: bold; text-transform:uppercase">Lates as of <span id="latetransasof"></span></p>

                  <div class="latesContainer">
                     <p style="font-size:11px;">Vacation Leave: <span id="vld" style="font-weight: bold; padding-left: 1rem;">1</span> day/s</p>
                  </div>

               </div>

            </div>
            <div class="col-12">
               <hr>
               <p style="font-weight: bold; text-transform:uppercase">My Posted Transactions as of <span id="postedtransasof"></span> </p>
               <div class="row">
                  <div class="col-12">
                     <p style="font-size:8px; margin-bottom:2px"><span id="vld" style="font-weight: bold; padding-left: 1rem;">0</span> day/s or 0 hours of Vacation Leave</p>
                  </div>
                  <div class="col-12">
                     <p style="font-size:8px; margin-bottom:2px"><span id="vlh" style="font-weight: bold; padding-left: 1rem;">0</span> hours of Vacation Leave (half-day)</p>
                  </div>
                  <div class="col-12">

                     <p style="font-size:8px; margin-bottom:2px"><span id="sld" style="font-weight: bold; padding-left: 1rem;">0</span> day/s of Sick Leave</p>
                  </div>
                  <div class="col-12">
                     <p style="font-size:8px; margin-bottom:2px"><span id="slh" style="font-weight: bold; padding-left: 1rem;">0</span> of Sick Leave (half-day)</p>
                  </div>
                  <div class="col-12">
                     <p style="font-size:8px; margin-bottom:2px"><span id="ml" style="font-weight: bold; padding-left: 1rem;">0</span> day/s of Maternity Leave</p>
                  </div>
                  <div class="col-12">
                     <p style="font-size:8px; margin-bottom:2px"> <span id="pl" style="font-weight: bold; padding-left: 1rem;">0</span> day/s or 0 hours of Paternity Leave</p>
                  </div>
               </div>
               <hr>
            </div>

            <div class="col-12" style="text-align: center">
               <p style="font-weight: bold; text-transform:uppercase">HR-FORM Approver :</p>
               <div class="" style="display: flex; justify-content:center">
                  <div class="approver_detail">
                     <i class="bi bi-person-badge" style="font-size:  45px;"></i>
                     <p style="font-weight: bold; text-transform:uppercase; margin:0; font-size:14px!important;margin-top:10px; border-bottom: 2px solid black" id="apprv_name"></p>
                     <p style="font-weight: bold; text-transform:uppercase;" id="apprv_pos">IT Software Manager</p>
                  </div>
               </div>

            </div>
         </div>
      </div>

   </div>
</div>
</div>


</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

<script>
   getpostedtransactions()
   $('.alert-success').hide()
   $('.alert-danger').hide()
   async function getpostedtransactions() {
      const response = await fetch("../controller/userController.php", {
         headers: {
            'Content-type': 'application/x-www-form-urlencoded'
         },
         method: 'POST',
         body: `empno=${$('#emp_no').html()}&action=postedtransactions`
      })
      const data = await response.json();
      console.log(data)
      if (data) {
         $('#vld').html(data['vld'] ? data['vld'] : 0)
         $('#vlh').html(data['vlh'] ? data['vlh'] : 0)
         $('#sld').html(data['sld'] ? data['sld'] : 0)
         $('#slh').html(data['slh'] ? data['slh'] : 0)
         $('#pl').html(data['pl'] ? data['pl'] : 0)
         $('#ml').html(data['ml'] ? data['ml'] : 0)
      }

      getEmployeeTableLeaves()
      getApprover()
   }

   function successMsg() {
      console.log('test success show')
      $('.alert-success').show()
   }

   function Close() {
      console.log('test success close')
      $('.alert-success').hide()
   }

   function errorMsg() {
      console.log('test error show')
      $('.alert-danger').show()
   }

   function errorMsg() {
      console.log('test error close')
      $('.alert-danger').hide()
   }

   function checkInputFields(selectorArray) {
      let response = true;
      selectorArray.forEach(element => {
         let input = $(`${element}`).val();
         if (input == '') {
            $(`${element}`).css('border', '2px solid red')
            response = false;
         } else {
            $(`${element}`).css('border', '1px solid #ced4da')
         }
      });
      return response
   }
   async function getEmployeeTableLeaves() {
      const response = await fetch("../controller/userController.php", {
         headers: {
            "Content-type": "application/x-www-form-urlencoded",
            'Autorization': `Bearer ${$('#token').html()}`
         },
         method: 'POST',
         body: 'action=getLeaves'
      })
      const {
         vl,
         sl
      } = await response.json();
      $('#earnvldays').text(Math.floor(vl.vlearn_days))
      $('#earnvlhrs').text(Math.floor(vl.vlearn_hrs))
      $('#filedvldays').text(Math.floor(vl.vlfile_days))
      $('#filedvlhrs').text(Math.floor(vl.vlfile_hrs))
      $('#balvldays').text(Math.floor(vl.vlbal_days))
      $('#balvlhrs').text(Math.floor(vl.vlbal_hrs))


      $('#earnsldays').text(Math.floor(sl.slearn_days))
      $('#earnslhrs').text(Math.floor(sl.slearn_hrs))
      $('#filedsldays').text(Math.floor(sl.slfile_days))
      $('#filedslhrs').text(Math.floor(sl.slfile_hrs))
      $('#balsldays').text(Math.floor(sl.slbal_days))
      $('#balslhrs').text(Math.floor(sl.slbal_hrs))

   }
   async function getApprover() {
      const response = await fetch("../controller/userController.php", {
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         method: 'POST',
         body: 'action=getapprover'
      })
      const {
         Approver: {
            name,
            position
         }
      } = await response.json()
      $('#apprv_name').text(name)
      $('#apprv_pos').text(position)

   }
   async function getLates() {
      const response = await fetch("../controller/userController.php", {
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         method: 'POST',
         body: 'action=getLates'
      })
      const {
         data
      } = await response.json();
      $('.latesContainer').empty();
      data.map((item) => {
         $('.latesContainer').append(`<p style="font-size:11px; margin-bottom:2px">${item['actual_in_date']}</p>`)
      })
      let dateObj = new Date()
      let date = (dateObj.getUTCMonth() + 1) + '/' + dateObj.getUTCDate() + ' of ' + dateObj.getUTCFullYear();
      $('#postedtransasof').text(date)
      $('#latetransasof').text(date)
   }
   getLates()
</script>