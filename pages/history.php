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
<div class="row">
   <div class="col-3">
      <?php require '../components/sidebar.php'; ?>
   </div>
   <div class="col">
      <?php require '../components/navbar.php'; ?>
      <div class="container" style="height:calc(100vh - 50px);overflow-y:scroll!important;padding:5px 2rem">
         <div class="">
            <h3 style="font-weight: bold;margin-top: 1rem;"><i class="bi bi-shield-lock-fill" style="margin-right:1rem"></i>Request History</h3>
            <label for="">Hi! From this page you may view your attedance from your [TIME IN] to [TIME OUT],[] <br>If there are questions/concerns about your attendance please contact our HR Deparment . </label>
         </div>
         <hr>
         <div class="search-field" style="margin-top: 1rem;">
            <div class="input-group">
               <select class="historysearchselect" style="width: 100%;"></select>
               <!-- <button type="button" class="btn btn-primary" style="width: 10%;">Search</button> -->
            </div>
         </div>

         <table style="font-size: 11px;font-weight:bold;padding: 1rem 0; " id="table_id" class="row-border">
            <thead>
               <tr style="background-color: #b82525;color:white; letter-spacing: 1px">
                  <th style="width:20px;">Control no.</th>
                  <th style="width:50px;">Date Created</th>
                  <th style="width:50px;">Effective Date</th>
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
   $(document).ready(function() {
      $('.historysearchselect').select2({
         placeholder: 'Search by Employee Number, Firstname, Lastname',
         ajax: {
            url: '../controller/transactionController.php',
            dataType: "json",
            headers: {
               'Autorization': `Bearer ${$('#token').html()}`
            },
            type: 'POST',
            'data': (params) => {
               var query = {
                  'search': params.term,
                  'action': 'gethistoryuserlist'
               }
               return query;
            },
            processResults: function({
               data
            }) {
               return {
                  results: $.map(data, (item) => {
                     return {
                        id: item.emp_no,
                        text: item.name
                     }
                  })
               }

            },
         },

      });
      $('.historysearchselect').on('change', (e) => {
         getUserHistory($('.historysearchselect').select2('data')[0])
      })
   });

   let tableHistory = $('#table_id').DataTable({
      ordering: false,
      searching: false,
      pageLength: 8,
      bLengthChange: false,
   }).draw(false)

   async function getUserHistory({
      id
   }) {

      tableHistory.clear();
      const response = await fetch("../controller/transactionController.php", {
         method: "POST",
         headers: {
            "Content-type": "application/x-www-form-urlencoded",
            'Autorization': `Bearer ${$('#token').html()}`
         },
         body: `empno=${id}&action=getuserhistory`
      })
      const {
         data,
         message,
         error
      } = await response.json()
      if (error) {

      } else {
         $.map(data, (item, index) => {
            const {
               controlNo,
               encodeDate,
               effDate,
               reason,
               leaveName,
               leaveStatContent
            } = item
            $('#table_id').DataTable().row.add([
               `<p style="font-weight:bold">${controlNo}</p>`,
               encodeDate,
               effDate,
               reason,
               leaveName,
               getstatus(leaveStatContent.trim())
            ]).draw(false)
         })
      }
   }

   function getstatus(status) {
      if (status == "Posted" || status == "In-Process") {
         return `<p style='color:green;font-size:10px!important;font-weight:bold'>${status}</p>`
      } else if (status == "Approved") {
         return `<p style='color:blue;font-size:10px!important;font-weight:bold'>${status}</p>`
      } else if (status == "Deleted" || status == "Cancelled") {
         return `<p style='color:red;font-size:10px!important;font-weight:bold'>${status}</p>`
      } else {
         return status
      }
   }
</script>