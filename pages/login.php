<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../assets/style.css?v<?php echo time(); ?>">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

   <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">

   <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
   <title>EVER HR-FORMS</title>
</head>

<body style="padding:0; overflow:hidden">
   <div style="height: 100vh;">
      <div class="row" style="height:100vh; ">
         <div class="col-6 d-flex" style="background-color:#b82525;align-items:center;justify-content:center">
            <img src="../assets/download3.png?v<?php echo time(); ?>" alt="" srcset="" style="width: 250px">
         </div>
         <div class="col-6" style="display:flex; align-items:center; justify-content:center; background-image: url('../assets/undraw.png'); background-size:100%; background-repeat:no-repeat; background-position:8rem 10rem;">
            <div class="row loginform" style="width: 280px; padding: 2rem 1rem; box-shadow: 2px 3px 13px -3px rgba(0,0,0,0.45);border-radius: 3px;background-color:white">
               <div class="col-12" style="text-align: left;">
                  <h3 style="font-weight: bold;line-height:25px">Welcome to Ever HR-FORMS Portal</h3>
                  <p style="font-size: 11px;line-height:10px;font-weight:normal">Let's get you ready. Please enter your username and password</p>
                  <hr>
               </div>
               <form id="loginForm">
                  <div class="col-12">
                     <label for="inputEmail4" class="form-label" style="font-weight: bold;">Username :</label>
                     <input type="email" class="form-control" id="inputEmail4" name="data[email]">
                  </div>
                  <div class="col-12">
                     <label for="inputEmail4" class="form-label" style="font-weight: bold;">Password :</label>
                     <input type="password" class="form-control" id="inputEmail4" name="data[password]" onkeypress="console.log(this)">
                  </div>
                  <div class="col-12" style="margin: 10px 0;">
                     <button type="button" class="btn btn-success" style="width: 100%;" onclick="login()">Log In</button>
                  </div>
               </form>
            </div>
         </div>

      </div>

      <?php require '../layout/footer.php' ?>
      <script>
         $(document).ready(() => {
            console.log('ready')
         })

         async function login() {
            let formdata = $('#loginForm').serialize()
            const res = await fetch("../controller/userController.php", {
               method: 'POST',
               headers: {
                  'Content-type': 'application/x-www-form-urlencoded'
               },
               body: formdata + '&action=login'
            })
            const data = await res.json()
            if (data) {
               if (data['token']) {
                  localStorage.setItem('token', data['token']);
                  window.location = "/HRIS_New/routes/index.php?login=" + data['token'];
               }
            }
         }
      </script>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />