<?php
require('../backend/standard.php');
require('../backend/dbconn.php');
include "../mailer_class2.php";
$response = [];

if ($_SERVER['HTTP_AUTORIZATION']) {
   $MAIN_TOKEN = trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTORIZATION']));
   if ($_POST['action'] == "LeaveOfAbsence") {
      $datefrom = $_POST['dtefrm'];
      $dateto = $_POST['dteto'];
      $authorized = $_POST['isAuthorized'];
      $reason = $_POST['remarks'];
      $request_type = $_POST['request_type'];
      $token =  $MAIN_TOKEN;
      $emp_no = "";
      $logname = "";
      $department = "";
      $emp_name = "";
      $br_name = "";


      $Employee = new Standard("");
      $_query1 = "select rtrim(ltrim(a.emp_no)) as emp_no,g.br_name,rtrim(ltrim(d.deptname)) as department,ltrim(rtrim(a.firstname))+'.'+ltrim(rtrim(a.lastname)) as log_name, ltrim(rtrim(a.lastname))+', '+ltrim(rtrim(a.firstname))+' '+substring(ltrim(rtrim(middlename)), 1, 1)+'.' as name, ltrim(rtrim(g.id_prefix)) as id_prefix from ref_emp_mast a left join ref_emp_trans b on a.emp_no = b.emp_no left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code and b.dept_code = c.dept_code and b.post_code = c.post_code left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code left join hris_mainLogIn e on b.emp_no in (e.user_name,e.temp_pass) left join ref_emp_stat f on f.emp_stat = b.emp_stat and f.br_prefix = b.br_code left join ref_branch g on d.br_code = g.br_code where ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000') and e.log_key = 1 and token_id like rtrim(ltrim('" . $token . "'))";
      $stmt1 = sqlsrv_query($conn, $_query1);
      if (sqlsrv_fetch($stmt1)) {
         $empData = $Employee->bindMetaData($stmt1);
         $emp_no = $empData['emp_no'];
         $emp_name = $empData['name'];
         $logname = $empData['log_name'];
         $department = $empData['department'];
         $br_name = $empData['br_name'];
      }

      //Check or Insert Approver
      //Automatically Inserts Approver if no approver is present
      //from old code
      $query_approver = "Execute approver_auto_insert '" . $emp_no . "'," . $emp_no . "";
      $stmt_approver = sqlsrv_query($conn, $query_approver);


      //----------------Check LOA dates from and to, if already filed-------------------
      $query_dates = "select *,case when date_from = date_to then ltrim(rtrim(convert(char(10),date_from,101))) else ltrim(rtrim(convert(char(10),date_from,101)))+' to '+ltrim(rtrim(convert(char(10),date_to,101))) end as daterec from emp_request_master where emp_no = '" . $emp_no . "' and leavestatusid in (1,2,3,4,7) and (convert(char(8),(CONVERT(DATETIME,'" . $datefrom . "',111)),112) between convert(char(8),date_from,112) and convert(char(8),date_to,112) or convert(char(8),(CONVERT(DATETIME,'" . $dateto . "',111)),112) between convert(char(8),date_from,112) and convert(char(8),date_to,112))";
      $stmt_loa = sqlsrv_query($conn, $query_dates);
      $leave_name = "";

      if (sqlsrv_fetch($stmt_loa)) {
         $response['status'] = '503';
         $response['error'] = true;
         $response['message'] = "Date's already filed, please select new date's!";
      } else {
         $query_lrs = "SELECT lrs_desc from ref_emp_request_type  where lrs_type like '" . $authorized . "'";
         $stmt_lrs = sqlsrv_query($conn, $query_lrs);
         $lrs_desc = new Standard("");
         if (sqlsrv_fetch($stmt_lrs)) {
            $leave_name = $lrs_desc->bindMetaData($stmt_lrs);
         } else {
            return false;
         }
      }

      if ($leave_name != "") {
         $query_leave = "select *,case when date_Ffrom = date_Fto then ltrim(rtrim(convert(char(10),date_Ffrom,101))) else ltrim(rtrim(convert(char(10),date_Ffrom,101)))+' to '+ltrim(rtrim(convert(char(10),date_Fto,101))) end as daterec from leave_trans where emp_no like '" . $emp_no . "' and leavestatusid in (1,2,3,4,7) and (convert(char(8),(CONVERT(DATETIME,'" . $datefrom . "',111)),112) between convert(char(8),date_Ffrom,112) and convert(char(8),date_Fto,112) or convert(char(8),(CONVERT(DATETIME,'" . $dateto . "',111)),112) between convert(char(8),date_Ffrom,112) and convert(char(8),date_Fto,112))";
         $stmt_leave = sqlsrv_query($conn, $query_leave);
         if (sqlsrv_fetch($stmt)) {
            $response['status'] = '503';
            $response['error'] = true;
            $response['message'] = "Date's already filed, please select new date's!";
         } else {



            //-----------GenerateControllNumber-------------
            $GCN = new Standard("");
            $controlno = $GCN->generateControlNumber('CS');
            $controlno = $controlno['controlno'];
            //----------------------------------------------

            $loa_params['controlno'] = $controlno;
            $loa_params['emp_no'] = $emp_no;
            $loa_params['lrs_type'] = $authorized;
            $loa_params['date_from'] = $datefrom;
            $loa_params['date_to'] = $dateto;
            $loa_params['leavestatusid'] = '1';
            $loa_params['reason'] = $reason;
            $loa_params['encoded_by'] = $logname;
            $loa_params['encoded_date'] = 'getdate()';
            $loa_params['isapproved'] = '0';
            $loa_params['approved_by'] = '---';
            $loa_params['approved_date'] = '';
            $loa_params['audit_user'] = '---';
            $loa_params['audit_date'] = '';
            $loa_params['ispis'] =  '0';

            //------------CREATING LEAVE OF ABSENCE REQUEST-----------
            $Insertion = new Standard("emp_request_master");
            $resultLOA = $Insertion->inserData($loa_params);
            //--------------------------------------------------------

            if (!$resultLOA) {
               $response['status'] = '503';
               $response['error'] = true;
               $response['message'] = "Unable to save request, please try again later";
            } else {
               //-----------------UPDATE CONTROL NUMBER SQ-------------------
               $clsControlNo = new Standard("");
               $stat_control_no = $clsControlNo->nextControlNumber('CS');
               //-------------------------------------------------------------


               //-----------------GETTING USED CONTROL #-------------------
               $cControlNo = new Standard("");
               $queryC = "select 'CS'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'CS'),8) as controlno";
               $stmt_queryC = sqlsrv_query($conn, $queryC);
               if (sqlsrv_fetch($stmt_queryC)) {
                  $controlno = $cControlNo->bindMetaData($stmt_queryC);
                  $controlno =  $controlno['controlno'];
               }
               //

               //------------SENDING MAIL AS FOR OLD CODE----------------

               $date_filed = date('Y-m-d');
               $lceff_date = ($datefrom == $dateto) ? $datefrom : $datefrom . ' &mdash; ' . $dateto;

               try {
                  $Mailer = new Mailer();
                  $mailer_from = $Mailer->get_sender($emp_no);
                  $mailer_to = $Mailer->get_recipient($emp_no);
                  $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                  $Mailer->mailformat_request($emp_no,  $emp_name, $leave_name['lrs_desc'], $date_filed, $controlno,  $department, $br_name, $lceff_date, $reason,  $mailer_from, $test_mail_to);
               } catch (\Throwable $th) {
                  var_dump($th);
               }
               // ---------------------------------------------------------

               $response['status'] = '200';
               $response['error'] = false;
               $response['message'] = "Successfully saved request!";
            }
         }
      }
   }
}

echo json_encode($response);
