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
      <div class="container" style="height:calc(100vh - 50px);display:flex; align-items:center; justify-content:center;z-index:99px;">
         <div class="leaveContainer" tabindex="1" style="box-shadow: 10px 10px 32px -18px rgba(0,0,0,1); width: 600px; padding: 0px; overflow:hidden;overflow-y:scroll; max-height:550px">
            <form id="form_offsetting">
               <p style="background-color: #b82525; padding: 10px 1rem; color:white; font-size: 15px!important">Application for Offsetting</p>
               <div class="row" style="padding: 0 1rem; display:flex; align-items:center;justify-content:space-around">
                  <p>Offset Type :</p>
                  <div class="col-4">
                     <div class=" form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[filetype]" id="exampleRadios1" value="Undertime" checked style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios1">
                           Undertime - ( 3hrs less )
                        </label>
                     </div>
                  </div>
                  <div class="col-4">
                     <div class="form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[filetype]" id="exampleRadios2" value="MultipleOffset" style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios2">
                           Mutiple OT in One Offset
                        </label>
                     </div>
                  </div>
                  <div class="col-4">
                     <div class="form-check d-flex">
                        <input class="form-check-input" type="radio" name="data[filetype]" id="exampleRadios3" value="DayAbsent" style="margin-right: 10px;">
                        <label class="form-check-label" for="exampleRadios3">
                           Day absent
                        </label>
                     </div>
                  </div>
               </div>
               <div class="row" style="padding: 0 1rem">
                  <div class="input-group input-group-sm mb-3">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Effectivity Date :</span>
                     <input type="date" style="font-size:11px;" name="data[eff_date]" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                  </div>
               </div>
               <div class="row" style="padding: 0 1rem" id="selectOT">
                  <div class="col-12">
                     <select class="form-select form-select-sm" id="select_ot_appy" aria-label=".form-select-sm example" style="font-size: 11px;" name="data[ot_code]">
                        <option selected value="">Select overtime to apply</option>
                     </select>
                  </div>
               </div>

               <div class="row" style="padding: 0 1rem; margin-top: 1rem" id="multipleoffset">
                  <div class="col-12">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px; border-radius:0px;">Overtime available :</span>
                     <table style="width:100%;">
                        <thead style="border-top: 1px solid black;background-color:#198754; color:white;">
                           <tr>
                              <td style="padding:0 10px">Control No.</td>
                              <td>Date</td>
                              <td>From</td>
                              <td>To</td>
                              <td>Hrs Available</td>
                              <td>Action</td>
                           </tr>
                        </thead>
                        <tbody style="border-top: 1px solid black;" id="multi-of-body">

                        </tbody>
                     </table>
                  </div>
               </div>
               <div class="row" style="padding: 0 1rem; margin-top: 1rem" id="selectedmultipleoffset">
                  <div class="col-12">
                     <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px; border-radius:0px;">Selected overtime</span>
                     <table style="width:100% ;border-bottom:1px solid black">
                        <thead style="border-top: 1px solid black;background-color:#b82525; color:white;">
                           <tr>
                              <td style="padding:0 10px"> No. of hours</td>
                              <td>Date</td>
                              <td>Action</td>
                           </tr>
                        </thead>
                        <tbody style="border-top: 1px solid black;" id="multi-selected-of-body">

                        </tbody>
                     </table>
                     <p>Total hours to be used : <span style="font-weight: bold;" id="total_no_hrs">0</span></p>
                  </div>
               </div>


               <div class="row" style="padding: 0 1rem; margin-top:1rem;" id="hrstooffset">
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px">Hours to offset :</span>
                        <input type="time" style="font-size:11px;" name="data[time_from]" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" id="inputGroup-sizing-sm" style="font-size: 11px"> TO :</span>
                        <input type="time" style="font-size:11px;" name="data[time_to]" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">
                     </div>
                  </div>
               </div>
               <div class="row" style="padding: 0 1rem; margin-top:10px;">
                  <div class="input-group mb-3">
                     <span class="input-group-text" id="basic-addon1" style="font-size: 11px;"> Remarks :</span>
                     <div class="form-floating">
                        <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="border-radius:2px; font-size:11px; padding-top: 10px!important" name="data[remarks]"></textarea>
                     </div>
                  </div>

               </div>
               <div class="row" style="padding: 10px 2rem; text-align:right">
                  <button type="button" onclick="submitReq()" class="btn btn-success" style="width:100%">Submit</button>
               </div>
            </form>
         </div>
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
   // $('input[name="dates"]').daterangepicker();
   $(document).ready(() => {
      filterOffsetType()
      $('#multipleoffset').hide()
      $('#selectedmultipleoffset').hide()
      getOTControl();
   })

   function filterOffsetType() {
      let rdoTypes = $("input[name='data[filetype]']")
      rdoTypes.map((_index, rdo) => {
         rdo.addEventListener('click', (e) => {
            getOTControl()
            if (e.target.value.toLowerCase() == 'dayabsent') {
               $('#selectOT').show()
               $('#hrstooffset').hide()
               $('#multipleoffset').hide()
               $('#selectedmultipleoffset').hide()

            } else if (e.target.value.toLowerCase() == 'multipleoffset') {
               $('#hrstooffset').show()
               $('#multipleoffset').show()
               $('#selectedmultipleoffset').show()
               $('#selectOT').hide()
            } else {
               $('#multipleoffset').hide()
               $('#selectedmultipleoffset').hide()
               $('#hrstooffset').show()
               $('#selectOT').show()
            }
         })
      })
   }

   async function getOTControl() {
      const response = await fetch("../controller/transactionController.php", {
         method: 'POST',
         headers: {
            'Content-type': 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: 'action=SELECTOT'
      })
      const {
         data
      } = await response.json()
      if (data) {
         $('#select_ot_appy').empty()
         $('#multi-of-body').empty();
         data.forEach((item, i) => {
            let newOption = `<option style="display:flex;justify-content:space-between; border:1px solid; width:100%" value=${item['contNo']}>
                                 <label>${converDate(item['otdate'])} </label>
                                 &emsp;&emsp;
                                 <label>${converT(item['otFrom'])}&emsp; to &emsp;${converT(item['otTo'])}</label>
                                 &emsp;&emsp;
                                 <label>${item['no_of_hrs']}</label>
                                 &emsp;&emsp;
                                 <p>${ calcDiff(item['otFrom'], item['otTo'])}</p>
                              </option>`;
            let newTd = `<tr data-control-id="${item['contNo']}">
                           <td style="font-weight:bold!important;padding:0 10px" id="tbl_controlNo">${item['contNo']}</td>
                           <td style="font-weight:bold!important">${converDate(item['otdate'])}</td>
                           <td style="font-weight:bold!important">${converT(item['otFrom'])}</td>
                           <td style="font-weight:bold!important">${converT(item['otTo'])}</td>
                           <td style="font-weight:bold!important">${item['no_of_hrs']}</td>
                           <td>
                              <input type="button" style="width:100%; border:.5px solid grey" value="SET" onclick="setItem(['${item['contNo']}', '${item['no_of_hrs']}', '${item['raw']}'])"/>
                           </td>
                        </tr>`;
            $('#multi-of-body').append(newTd);
            $('#select_ot_appy').append(newOption);

         });

      }
   }

   function converT(time) {
      time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
      if (time.length > 1) {
         time = time.slice(1);
         time[5] = +time[0] < 12 ? 'AM' : 'PM';
         time[0] = +time[0] % 12 || 12;
      }
      return time.join('');
   }

   function converDate(paramdate) {
      const date = new Date(paramdate)
      return date.toDateString()
   }

   async function setItem(e) {
      e[1] = e[1].toLowerCase();
      if (e[1].search(/fye/) < 0) {
         $(`tr[data-control-id="${e[0]}"`).css("display", "none");
         let seletedItem = `<tr data-selected-ot-id="${e[0]}">
                           <td id="selected_ot_data">${e[0]}</td>
                           <td>${e[1]}</td>
                           <td>
                           <input type="button" onclick="removeSelected('${e[0]}', '${e[2]}')" style="width:100%; border:.5px solid grey" value="Remove" />
                           </td>
                        </tr>`
         $('#multi-selected-of-body').append(seletedItem)
         let cur_total = parseFloat($('#total_no_hrs').html())
         cur_total += parseFloat(e[2]);
         $('#total_no_hrs').html(cur_total)
         $('#total_no_hrs').css("opacity", '0')
         if (await validateSet() == false) {
            removeSelected(e[0], e[2])
            $('#total_no_hrs').css("opacity", '1')
         } else {
            $('#total_no_hrs').css("opacity", '1')
         }
      } else {
         alert("Fiscal Year End cannot be used for MULTI-OFFSETTINGS, Please try other options.")
      }
   }

   function removeSelected(e, x) {
      $(`tr[data-control-id="${e}"`).css("display", "table-row");
      $(`tr[data-selected-ot-id="${e}"`).remove()
      let cur_total = parseFloat($('#total_no_hrs').html())
      cur_total -= parseFloat(x)
      cur_total = parseFloat(cur_total).toFixed(2)
      $('#total_no_hrs').html(cur_total)
   }

   function msToTime(duration) {
      var milliseconds = Math.floor((duration % 1000) / 100),
         seconds = Math.floor((duration / 1000) % 60),
         minutes = Math.floor((duration / (1000 * 60)) % 60),
         hours = Math.floor((duration / (1000 * 60 * 60)) % 24);

      hours = (hours < 10) ? "0" + hours : hours;
      minutes = (minutes < 10) ? "0" + minutes : minutes;
      seconds = (seconds < 10) ? "0" + seconds : seconds;

      return hours + ":" + minutes + ":" + seconds
   }

   function calcDiff(timefrom, timeto) {
      let timefrom1 = new Date("01/01/2007 " + timefrom + ":00");
      let timeto1 = new Date("01/01/2007 " + timeto + ":00");
      return msToTime(timeto1 - timefrom1)
   }

   async function submitReq(e) {
      let reqbody = '';
      let timefrom = $('input[name="data[time_from]"]').val()
      let timeto = $('input[name="data[time_to]"]').val()
      let tobefiled = calcDiff(timefrom, timeto)
      let xcontine = false;
      switch ($("input[name='data[filetype]']:checked").val().toUpperCase()) {
         case "UNDERTIME":
            let ottime = $('#select_ot_appy').find(":selected").children()[3].innerText

            if (parseFloat(tobefiled.replace(':', '.')) > parseFloat(ottime.replace(':', '.'))) {
               $('#errormsg').html("Unable to continue. Please check HOUR[FROM] AND HOUR[TO] and compare to selected OT to offset")
               $('.alert-danger').show()
               xcontinue = false
            } else {
               xcontinue = true
            }
            reqbody = $('#form_offsetting').serialize() + '&action=OFFSETTING';
            break;
         case "MULTIPLEOFFSET":
            let totalsethrs = $('#total_no_hrs').html()

            if (parseFloat(tobefiled.replace(':', '.')) > parseFloat(totalsethrs)) {
               $('#errormsg').html("Unable to continue. Please check HOUR[FROM] AND HOUR[TO]. HOUR FROM AND TO is greater than the total selected overtime")
               $('.alert-danger').show()
               xcontinue = false
            } else {
               xcontinue = true
            }
            let tblIds = document.querySelectorAll('#selected_ot_data')
            let data = '';
            let counter = 0;
            tblIds.forEach(item => {
               if (data != '') {
                  data += '&'
               }
               data += `data[items][${counter}]=${item.innerHTML}`
               counter++;
            })
            reqbody = $('#form_offsetting').serialize() + '&' + data + '&data[filetype]=MultipleOffset&action=OFFSETTING';
            break;
         case "DAYABSENT":
            reqbody = $('#form_offsetting').serialize() + '&data[filetype]=DayAbsent&action=OFFSETTING';
            break;

         default:
            break;
      }

      if (xcontine) {
         e.innerText = "Please wait ... Loading"
         const response = await fetch("../controller/transactionController.php", {
            method: 'POST',
            headers: {
               'Content-type': 'application/x-www-form-urlencoded',
               'Autorization': `Bearer ${$('#token').html()}`
            },
            body: reqbody + `&emp_no=${$('#select_branchemp').val() ? $('#select_branchemp').val() : '' }`
         })
         const {
            error,
            message
         } = await response.json();
         if (error) {
            $('#errormsg').html(message)
            $('.alert-danger').show()
            e.innerText = "Submit"
            e.removeAttribute('disabled')
         } else {
            $('#successmsg').html(message)
            $('.alert-success').show()
            e.innerText = "Submit"
            e.removeAttribute('disabled')
            $('#multi-selected-of-body').empty()
         }
         getOTControl();
      }

   }

   async function validateSet() {
      let totalsethrs = parseFloat($('#total_no_hrs').html());
      const response = await fetch("../controller/offsettingController.php", {
         method: 'POST',
         headers: {
            "Content-type": 'application/x-www-form-urlencoded',
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: `data=${totalsethrs}&action=validateOTsetting`
      })
      const {
         message,
         error
      } = await response.json();
      if (error) {
         alert(message)
         return false
      }

   }

   function diffTime(from, to) {
      let fromTime = parseFloat(from.replace(":", '.'))
      let toTime = parseFloat(to.replace(":", '.'))
      console.log(toTime, fromTime)
   }
</script>