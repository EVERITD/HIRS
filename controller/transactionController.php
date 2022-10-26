<?php

require('../backend/standard.php');
require('../backend/dbconn.php');
include "../mailer_class2.php";
$response = [];

if ($_SERVER['HTTP_AUTORIZATION']) {
   $MAIN_TOKEN = trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTORIZATION']));
   if ($MAIN_TOKEN) {
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
               $loa_params['leavestatusid'] = '1';
               $loa_params['reason'] = $reason;
               $loa_params['encoded_by'] = $logname;
               $loa_params['encoded_date'] = 'getdate()';
               $loa_params['isapproved'] = '0';
               $loa_params['approved_by'] = '---';
               // $loa_params['approved_date'] = '';
               $loa_params['audit_user'] = '---';
               // $loa_params['audit_date'] = '';
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
                  //--------------------------------



                  //------------SENDING MAIL AS FOR OLD CODE----------------
                  $date_filed = date('Y-m-d');
                  $lceff_date = ($datefrom == $dateto) ? $datefrom : $datefrom . ' &mdash; ' . $dateto;

                  try {
                     $Mailer = new Mailer();
                     $mailer_from = $Mailer->get_sender($emp_no);
                     $mailer_to = $Mailer->get_recipient($emp_no);
                     $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                     $Mailer->mailformat_request($emp_no,  $emp_name, $leave_name['lrs_desc'], $date_filed, $controlno,  $department, $br_name, $lceff_date, $reason,  $mailer_from, $test_mail_to);

                     $response['status'] = '200';
                     $response['error'] = false;
                     $response['message'] = "Successfully saved request!";
                  } catch (\Throwable $th) {
                     $response['status'] = '503';
                     $response['error'] = true;
                     $response['message'] = "Unable to send email, the system will automatically sent this later.";
                     var_dump($th);
                  }
                  // ---------------------------------------------------------


               }
            }
         }
      }
      if ($_POST['action'] == "TAR") {
         $Employee = new Standard("");
         $_query1 = "select rtrim(ltrim(a.emp_no)) as emp_no,g.br_name,rtrim(ltrim(d.deptname)) as department,ltrim(rtrim(a.firstname))+'.'+ltrim(rtrim(a.lastname)) as log_name, ltrim(rtrim(a.lastname))+', '+ltrim(rtrim(a.firstname))+' '+substring(ltrim(rtrim(middlename)), 1, 1)+'.' as name, ltrim(rtrim(g.id_prefix)) as id_prefix from ref_emp_mast a left join ref_emp_trans b on a.emp_no = b.emp_no left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code and b.dept_code = c.dept_code and b.post_code = c.post_code left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code left join hris_mainLogIn e on b.emp_no in (e.user_name,e.temp_pass) left join ref_emp_stat f on f.emp_stat = b.emp_stat and f.br_prefix = b.br_code left join ref_branch g on d.br_code = g.br_code where ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000') and e.log_key = 1 and token_id like rtrim(ltrim('" .  $MAIN_TOKEN . "'))";
         $stmt1 = sqlsrv_query($conn, $_query1);
         $continue = false;
         if (sqlsrv_fetch($stmt1)) {
            $empData = $Employee->bindMetaData($stmt1);
            $emp_no = $empData['emp_no'];
            $emp_name = $empData['name'];
            $logname = $empData['log_name'];
            $department = $empData['department'];
            $br_name = $empData['br_name'];
            $continue = true;
         }

         if ($continue) {
            $data = $_POST['data'];

            //Check inputs
            $params['timein'] = $data['timein'] ? str_replace(":", ".", $data['timein']) : 0;
            $params['timeout'] = $data['timeout'] ? str_replace(":", ".", $data['timeout']) : 0;
            $params['lunchin'] = $data['lbIn'] ? str_replace(":", ".", $data['lbIn']) : 0;
            $params['lunchout'] = $data['lbOut'] ? str_replace(":", ".", $data['lbOut']) : 0;
            $params['coffeein'] = $data['cbIn'] ? str_replace(":", ".", $data['cbIn']) : 0;
            $params['coffeeout'] = $data['cbOut'] ? str_replace(":", ".", $data['cbOut']) : 0;
            $params['effdate'] = date("Y-m-d", strtotime($data['effdte']));
            $params['reason'] =  $data['remarks'];


            $tarCheckQuery = "select CONVERT(VARCHAR(10),effdate, 101) as effdate,timein,timeout,leavestatusid,encoded_by,encoded_date,isapproved from tar_file where emp_no = '" . $emp_no . "' and CONVERT(VARCHAR(10),effdate, 101) like '" .  $params['effdate'] . "' and leavestatusid not in ('6','5') ";

            $tarCheckStmt = sqlsrv_query($conn, $tarCheckQuery);
            if (sqlsrv_fetch($tarCheckStmt)) {
               $response['status'] = '503';
               $response['error'] = true;
               $response['message'] = "TAR date already filed, please select new date!";
            } else {
               $GCN = new Standard("");
               $controlno = $GCN->generateControlNumber('TR');
               $controlno = $controlno['controlno'];
               $params['controlno'] = $controlno;
               $params['emp_no'] = $emp_no;
               $params['leavestatusid'] = 1;
               $params['encoded_by'] = $logname;
               $params['encoded_date'] = 'getdate()';
               $params['isapproved'] = 0;
               $params['approved_by'] = '---';
               $params['audit_user'] = '---';
               $sendEmail = false;

               try {
                  $Insertion = new Standard("tar_file");
                  $resultLOA = $Insertion->inserData($params);

                  if ($resultLOA) {
                     $sendEmail = true;
                     $clsControlNo = new Standard("");
                     $stat_control_no = $clsControlNo->nextControlNumber('TR');
                  } else {
                     $response['status'] = '503';
                     $response['error'] = true;
                     $response['message'] = "Unable to save request, Please try again later";
                  }
               } catch (\Throwable $th) {
                  $sendEmail = false;
                  var_dump($th);
               }

               if ($sendEmail) {
                  try {
                     $date_filed = date('Y-m-d');
                     $Mailer = new Mailer();
                     $mailer_from = $Mailer->get_sender($params['emp_no']);
                     $mailer_to = $Mailer->get_recipient($params['emp_no']);
                     $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                     $Mailer->mailformat_request($params['emp_no'],  $emp_name, 'Temporary Attendance Record', $date_filed, $params['controlno'],  $department, $br_name, $params['effdate'], $params['reason'],  $mailer_from, $test_mail_to);
                     $response['status'] = '200';
                     $response['error'] = false;
                     $response['message'] = "Successfully saved request!";
                  } catch (\Throwable $th) {
                     $response['status'] = '503';
                     $response['error'] = true;
                     $response['message'] = "Unable to send email, the system will automatically sent this later.";
                     var_dump($th);
                  }
               } else {
                  $response['status'] = '503';
                  $response['error'] = true;
                  $response['message'] = "Unable to save request, Please try again later";
               }
            }
         }
      }
      if ($_POST['action'] == "OUE") {
         $data = $_POST['data'];
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         if (!$employee) {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "Token Unrecognized!.";
            echo json_encode($response);
            die();
         }

         switch ($data['typeDay']) {
            case 'OT':
               approverAutoInsert($conn, $employee['emp_no']);
               $branch = $employee['br_name'] == "Head Office" ? 'HO' : 'Br';
               $app_date = date('D', strtotime($params['date'])) == "Sat" ? 'Sat' : 'NotSat';
               $params['isLess1'] = $branch . $app_date;

               $params['emp_no'] = $employee['emp_no'];
               $params['llctype'] = $data['typeDay'];
               $params['ot_code'] = $data['typeofday'];
               $params['log_name'] = $employee['logname'];
               $params['date'] = date_format(date_create($data['date']), "m/d/Y");
               $params['time_from'] = str_replace(":", ".", $data['hourfrom']);
               $params['time_to'] = str_replace(":", ".", $data['hourto']);
               $params['no_of_hrs'] = number_format((float)$params['time_to'] - $params['time_from'], 2, '.', '');
               $params['reason'] = $data['remarks'];
               $params['lcfye'] = $data['type_option'] == 'fye' ? 1 : 0;

               $query_ot = "execute sp_portal_ot_save '" .  $params['llctype'] . "','" .  $params['emp_no'] . "','" . $params['ot_code'] . "','" . $params['date'] . "','" .  $params['time_from'] . "','" .  $params['time_to'] . "','" . $params['reason']  . "','" .   $params['log_name'] . "','" . $params['lcfye'] . "'," . $params['isLess1'] . "";
               $stmt1 = sqlsrv_query($conn, $query_ot);

               $query_ref_ot = "select * from ref_offset_ot where emp_no = '" . $params['emp_no']  . "'";
               $stmt_ref_ot = sqlsrv_query($conn, $query_ref_ot);
               if (sqlsrv_fetch($stmt_ref_ot)) {
                  try {
                     $ins_query = "update ref_offset_ot set hrs_remain = hrs_remain+(select dbo.convertmin_hrs((select(select dbo.converthrs_min('" . $params['time_to'] . "'))-(select dbo.converthrs_min('" . $params['time_from']  . "')))) as hrs), audit_date = getdate() where emp_no = '" . $params['emp_no'] . "'";
                     $stmt_ins_query = sqlsrv_query($conn, $ins_query);
                     $continue = true;
                  } catch (\Throwable $th) {
                     $continue = false;
                  }
               } else {
                  try {
                     $ins_query = "insert into ref_offset_ot(emp_no,hrs_remain,audit_date)
                     select '" . $params['emp_no'] . "',(select dbo.convertmin_hrs((select(select dbo.converthrs_min('" . $params['time_to'] . "'))-(select dbo.converthrs_min('" . $params['time_from']  . "')))) as hrs),getdate()";
                     $stmt_ins_query = sqlsrv_query($conn, $ins_query);
                     $continue = true;
                  } catch (\Throwable $th) {
                     $continue = false;
                  }
               }

               if ($continue) {
                  $query_TD = "select '@ '+ ltrim(rtrim(ot_name)) as ot_name from ref_ot_code where ot_code = '" . trim($params['ot_code']) . "'";
                  $stmt_TD = sqlsrv_query($conn, $query_TD);
                  if (sqlsrv_fetch($stmt_TD)) {
                     $OT_CODE = new Standard('');
                     $OT = $OT_CODE->bindMetaData($stmt_TD);
                     $OT_name = $OT['ot_name'];

                     $GCN = new Standard("");
                     $controlno = $GCN->generateControlNumber('OT');
                     $controlno = $controlno['controlno'];


                     //-----------------UPDATE CONTROL NUMBER SQ-------------------
                     $clsControlNo = new Standard("");
                     $stat_control_no = $clsControlNo->nextControlNumber('OT');
                     //-------------------------------------------------------------

                     //-----------------GETTING USED CONTROL #-------------------
                     $cControlNo = new Standard("");
                     $queryC = "select 'OT'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'OT'),8) as controlno";
                     $stmt_queryC = sqlsrv_query($conn, $queryC);
                     if (sqlsrv_fetch($stmt_queryC)) {
                        $controlno = $cControlNo->bindMetaData($stmt_queryC);
                        $controlno =  $controlno['controlno'];
                     }
                     //--------------------------------

                     //------------SENDING MAIL AS FOR OLD CODE----------------
                     $leave_type = "Overtime " .  $OT_name;
                     $date_filed = date('Y-m-d');
                     try {
                        $Mailer = new Mailer();
                        $mailer_from = $Mailer->get_sender($params['emp_no']);
                        $mailer_to = $Mailer->get_recipient($params['emp_no']);
                        $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                        $Mailer->mailformat_request($params['emp_no'], $employee['emp_name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['date'],  $params['reason'], $mailer_from,  $test_mail_to[0]['email']);

                        $response['status'] = '200';
                        $response['error'] = false;
                        $response['message'] = "Successfully saved request!";
                     } catch (\Throwable $th) {
                        $response['status'] = '503';
                        $response['error'] = true;
                        $response['message'] = "Unable to send email, the system will automatically sent this later.";
                        var_dump($th);
                     }
                     // ---------------------------------------------------------
                  }
               } else {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "There was a problem with the data provided. Please contact the IT Software Dept.";
               }

               break;
            case 'UT':
               $params['emp_no'] = $employee['emp_no'];
               $params['time_from'] = str_replace(":", ".", $data['hourfrom']);
               $params['time_to'] = str_replace(":", ".", $data['hourto']);
               $params['date'] = date_format(date_create($data['date']), "m/d/Y");
               $params['reason'] = $data['remarks'];
               $params['filedhrs'] = number_format((float)$params['time_to'] - $params['time_from'], 2, '.', '');

               if ($params['filedhrs'] < 1) {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "Sorry the number of hours were unsufficient!";
                  echo json_encode($response);
                  die();
               }

               $query = "select CONVERT(VARCHAR(10),effdate, 101),CONVERT(VARCHAR(10),effdate, 101) as effdate,timein,timeout from undertime_file where emp_no = '" . $employee['emp_no'] . "' and CONVERT(VARCHAR(10),effdate, 101) like '" . $params['date'] . "'  and cast(substring('" . $params['time_from'] . "',1,5) as decimal(10,2)) between timein and timeout and leavestatusid not in(6,5) ";

               $resultUTT = sqlsrv_query($conn, $query);
               if (sqlsrv_fetch($resultUTT)) {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "Date already filed! Please Select again.";
                  echo json_encode($response);
                  die();
               } else {
                  try {
                     $query_insert = "Insert into undertime_file (controlno,emp_no,shift_code,effdate,timein,timeout,reason, leavestatusid,encoded_by,encoded_date,isapproved,approved_by,audit_user,isoffset) select (select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno+1))) from ref_controlno where module_code = 'UN'),8)) ,'" . $employee['emp_no'] . "','','" . $params['date'] . "'," . $params['time_from'] . ", " . $params['time_to'] . ",'" . $params['reason'] . "',1,'" . $employee['logname'] . "',getdate(),0,'---','---','0' from ref_controlno where module_code = 'UN' ";
                     $stmt_insert = sqlsrv_query($conn, $query_insert);
                  } catch (\Throwable $th) {
                     var_dump($th);
                  }


                  //-----------Control Number related-----------------------
                  $clsControlNo = new Standard("");
                  $stat_control_no = $clsControlNo->nextControlNumber('UN');


                  $cControlNo = new Standard("");
                  $queryC = "select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'UN'),8) as controlno";
                  $stmt_queryC = sqlsrv_query($conn, $queryC);
                  if (sqlsrv_fetch($stmt_queryC)) {
                     $controlno = $cControlNo->bindMetaData($stmt_queryC);
                     $controlno =  $controlno['controlno'];
                  }
                  //--------------------------------------------------------



                  //------------SENDING MAIL AS FOR OLD CODE----------------



                  $leave_type = "Undertime ";
                  $date_filed = date('Y-m-d');
                  try {
                     $Mailer = new Mailer();
                     $mailer_from = $Mailer->get_sender($params['emp_no']);
                     $mailer_to = $Mailer->get_recipient($params['emp_no']);
                     $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                     $Mailer->mailformat_request($params['emp_no'], $employee['emp_name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['date'],  $params['reason'], $mailer_from,  $test_mail_to[0]['email']);

                     $response['status'] = '200';
                     $response['error'] = false;
                     $response['message'] = "Successfully saved request!";
                  } catch (\Throwable $th) {
                     $response['status'] = '503';
                     $response['error'] = true;
                     $response['message'] = "Unable to send email, the system will automatically sent this later.";
                     var_dump($th);
                  }
                  //-----------------------------------------------------------
               }

               break;
            default:
               break;
         }
      }
      if ($_POST['action'] == "OFFSETTING") {

         $data = $_POST['data'];
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         if (!$employee) {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "Token Unrecognized!.";
            echo json_encode($response);
            die();
         }

         $params['time_from'] = str_replace(":", ".", $data['time_from']);
         $params['time_to'] = str_replace(":", ".", $data['time_to']);
         $params['effective_date'] =  date_format(date_create($data['date'] ? $data['date'] : $data['eff_date']), "m/d/Y");
         $params['remarks'] = $data['remarks'];
         approverAutoInsert($conn, $employee['emp_no']);

         if ($data['filetype'] == "Undertime") {
            $params['ot_code'] = $data['ot_code'];
            $query = "SELECT isfye,controlno,remain_hrs as left_time_bal  from ot_file where emp_no ='" . $employee['emp_no'] . "' and controlno = '" . $params['ot_code'] . "'";
            $stmt = sqlsrv_query($conn, $query);
            if (sqlsrv_fetch($stmt)) {
               $ot_file = new Standard("");
               $ot_file_time_bal =  $ot_file->bindMetaData($stmt);
            }

            if ($ot_file_time_bal) {
               $req_master = new Standard("");
               $query_req_master = "select * from emp_request_master where emp_no = '" . $employee['emp_no']  . "' and ot_cntno = '" . $params['ot_code'] . "' and isoffset = 1";
               $stmt_req_master = sqlsrv_query($conn, $query_req_master);

               // --get overtime
               $query_lefthrs_ot = "select '" . $ot_file_time_bal['left_time_bal'] . "'-(select dbo.convertmin_hrs((select(select dbo.converthrs_min('" . $params['time_to'] . "'))-(select dbo.converthrs_min('" . $params['time_from'] . "'))))) as left_hrs";
               //
               $stmt_lefthrs_ot = sqlsrv_query($conn, $query_lefthrs_ot);
               if (sqlsrv_fetch($stmt_lefthrs_ot)) {
                  $lefthrs_ot = new Standard("");
                  $leftOThrsxx = floatval($lefthrs_ot->bindMetaData($stmt_lefthrs_ot)['left_hrs']);
               }
               $int = 12.30;


               if ($int >  floatval($params['time_from']) && $int < floatval($params['time_to'])) //&& substr($empNo,0,2) = '99')
               {
                  $leftOThrs = 1 - abs($leftOThrsxx);
               } else {
                  $leftOThrs = $leftOThrsxx;
               }

               if ($leftOThrs < 0 || $ot_file_time_bal['isfye'] == "1") {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "System has detected FYE overtime for offsetting! </br></br>Please select on wholeday offset only!";
                  echo json_encode($response);
                  die();
               }

               $query_check_ot = "select remain_hrs from ot_file where emp_no = '" . $employee['emp_no'] . "' and controlno = '" . $params['ot_code']  . "'";
               $stmt_check_ot = sqlsrv_query($conn, $query_check_ot);
               if (sqlsrv_fetch($stmt_check_ot)) {
                  $check_ot  = new Standard("");
                  $remain_hrs = floatval($check_ot->bindMetaData($stmt_check_ot)['remain_hrs']);
               }
               $fileHrs =  $remain_hrs - $leftOThrs;


               //---------------INSERT-------------------------------
               try {

                  $query_insert = "update ot_file set used_un_hrs = '" . $fileHrs . "',isused = case when no_of_hrs-'" . $fileHrs . "' = 0 then 1 else 0 end,ut_cntno = (select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno+1))) from ref_controlno where module_code = 'UN'),8)), remain_hrs = no_of_hrs-'" . $fileHrs . "' from ot_file where emp_no = '" . $employee['emp_no'] . "' and controlno = '" . trim($params['ot_code']) . "'		
                     
                  Insert into undertime_file (controlno,emp_no,shift_code,effdate,timein,timeout,reason,leavestatusid,encoded_by,encoded_date,isapproved,approved_by,audit_user,isoffset,ot_cntno,used_ot_hrs) select (select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno+1))) from ref_controlno where module_code = 'UN'),8)) ,'" . $employee['emp_no'] . "','','" .  $params['effective_date'] . "',substring('" . $params['time_from'] . "',1,5), 
                  substring('" .  $params['time_to'] . "',1,5),'" . $params['remarks'] . "',1,'" . strtolower($employee['logname']) . "',getdate(),0,'---','---','1','" . trim($params['ot_code']) . "'
                  ,(select dbo.convertmin_hrs((select(select dbo.converthrs_min('" . $params['time_to'] . "'))-(select dbo.converthrs_min('" . $params['time_from'] . "')))) as hrs)
                  from ref_controlno where module_code = 'UN' 
            
                  update ot_file set used_un_hrs = (select sum(used_ot_hrs) from undertime_file where emp_no = '" . $employee['emp_no'] . "' and  ot_cntno = '" . trim($params['ot_code']) . "'),
                  isused = case when no_of_hrs-(select sum(used_ot_hrs) from undertime_file where emp_no = '" . $employee['emp_no'] . "' and  ot_cntno = '" . trim($params['ot_code']) . "') = 0 then 1 else 0 end , remain_hrs = no_of_hrs-(select sum(used_ot_hrs) from undertime_file where emp_no = '" . $employee['emp_no'] . "' and  ot_cntno = '" . trim($params['ot_code']) . "') from ot_file where emp_no = '" . $employee['emp_no'] . "' and controlno = '" . trim($params['ot_code']) . "'";

                  sqlsrv_query($conn, $query_insert);
               } catch (\Throwable $th) {
                  var_dump($th);
                  die();
               }

               $clsControlNo = new Standard("");
               $stat_control_no = $clsControlNo->nextControlNumber('UN');


               $cControlNo = new Standard("");
               $queryC = "select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'UN'),8) as controlno";
               $stmt_queryC = sqlsrv_query($conn, $queryC);
               if (sqlsrv_fetch($stmt_queryC)) {
                  $controlno = $cControlNo->bindMetaData($stmt_queryC);
                  $controlno =  $controlno['controlno'];
               }

               //------------SENDING MAIL AS FOR OLD CODE----------------
               $leave_type = "Undertime  - Offset of OT Control # " .  $controlno;
               $date_filed = date('Y-m-d');
               try {
                  $Mailer = new Mailer();
                  $mailer_from = $Mailer->get_sender($employee['emp_no']);
                  $mailer_to = $Mailer->get_recipient($employee['emp_no']);
                  $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                  $Mailer->mailformat_request($employee['emp_no'], $employee['emp_name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'],  $params['remarks'], $mailer_from,  $test_mail_to[0]['email']);
                  $response['status'] = '200';
                  $response['error'] = false;
                  $response['message'] = "Successfully saved request!";
               } catch (\Throwable $th) {
                  $response['status'] = '503';
                  $response['error'] = true;
                  $response['message'] = "Unable to send email, the system will automatically sent this later.";
                  var_dump($th);
               }
               // ---------------------------------------------------------
            } else {
               $response['error'] = true;
               $response['status'] = 503;
               $response['message'] = "Remaining hours of the selected overtime is unsufficient. Please select others";
            }
         } else if ($data['filetype'] == "MultipleOffset") {
            $params['day'] = date("D", strtotime($params['effective_date']));

            $query_check_time = "select case when dbo.convertmin_hrs((select dbo.converthrs_min('" . $params['time_to'] . "')-(select dbo.converthrs_min('" . $params['time_from'] . "')))) > 9.3 then
            dbo.convertmin_hrs((select dbo.converthrs_min('" .  $params['time_to'] . "')-(select dbo.converthrs_min('" . $params['time_from'] . "')))) - 1 else
            dbo.convertmin_hrs((select dbo.converthrs_min('" . $params['time_to'] . "')-(select dbo.converthrs_min('" . $params['time_from'] . "')))) end as time_check";

            $stmt_check_time = sqlsrv_query($conn, $query_check_time);

            if (sqlsrv_fetch($stmt_check_time)) {
               $time_checking = new Standard("");
               $result_time_check = $time_checking->bindMetaData($stmt_check_time);

               if ($params['day'] == 'Sat') {
                  $timeSet = $result_time_check['time_check'];
               } else if ($params['day'] != 'Sat' and $params['time_from']  >= 13.30) {
                  $timeSet = $result_time_check['time_check'];
               } else {
                  $valuetotime = $result_time_check['time_check'];
                  $time_data = explode('.', $valuetotime);

                  if ($employee['br_name'] != "Head Office") {
                     $timeSet = $valuetotime > 8 ? $valuetotime - 1 : $valuetotime;
                  } else {
                     $timeSet = $valuetotime > 5 ? $valuetotime - 1 : $valuetotime;
                  }
               }

               if ($timeSet) {
                  $params['sum_hrs'] = 0; // 

                  $GCN = new Standard("");
                  $controlno = $GCN->generateControlNumber('UN');
                  $controlno = $controlno['controlno'];

                  foreach ($data['items'] as $key => $value) {
                     $query_ot_file = "SELECT emp_no,remain_hrs from ot_file where controlNo ='" . $value . "' and emp_no ='" . $employee['emp_no'] . "'";
                     $stmt_ot_file = sqlsrv_query($conn, $query_ot_file);
                     if (sqlsrv_fetch($stmt_ot_file)) {
                        $cls_ot_file = new Standard('');
                        $result_ot_file = $cls_ot_file->bindMetaData($stmt_ot_file);
                     }
                     $params['sum_hrs'] += floatval($result_ot_file['remain_hrs']);

                     $used_un_hrs = $result_ot_file['remain_hrs'] == 0.00 ? 0 : $result_ot_file['remain_hrs'];

                     $query_update_ot = "UPDATE ot_file SET used_un_hrs = " . $used_un_hrs . ", ut_cntno ='" . $controlno . "', remain_hrs = 0  where controlNo ='" . $value . "' and emp_no = '" . $employee['emp_no'] . "'";
                     sqlsrv_query($conn, $query_update_ot);
                  }

                  // -------------INSERT DATA------------------------------------------
                  try {

                     $query_insert = "	Insert into undertime_file (controlno,emp_no,shift_code,effdate,timein,timeout,reason,leavestatusid,encoded_by,encoded_date,isapproved,approved_by,audit_user,isoffset,ot_cntno,used_ot_hrs) 
                     select (select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno+1))) from ref_controlno 
                     where module_code = 'UN'),8)) ,'" . $employee['emp_no'] . "','','" . $offDate . "',substring('" . $params['time_from'] . "',1,5), substring('" . $params['time_to'] . "',1,5),'" .  $params['remarks'] . "',1,'" . strtolower($employee['logname']) . "',getdate(),0,'---','---','1','multiple'
                     ,(select dbo.convertmin_hrs((select(select dbo.converthrs_min('" . $params['time_to'] . "'))-(select dbo.converthrs_min('" . $params['time_from'] . "')))) as hrs)
                     from ref_controlno where module_code = 'UN'";

                     sqlsrv_query($conn, $query_insert);
                  } catch (\Throwable $th) {
                     var_dump($th);
                     die();
                  }
                  // -------------------------------------------------------



                  //-----------------UPDATE CONTROL NUMBER SQ-------------------
                  $clsControlNo = new Standard("");
                  $stat_control_no = $clsControlNo->nextControlNumber('UN');
                  //-------------------------------------------------------------


                  //-----------------GETTING USED CONTROL #-------------------
                  $cControlNo = new Standard("");
                  $queryC = "select 'UN'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'UN'),8) as controlno";
                  $stmt_queryC = sqlsrv_query($conn, $queryC);
                  if (sqlsrv_fetch($stmt_queryC)) {
                     $controlno = $cControlNo->bindMetaData($stmt_queryC);
                     $controlno =  $controlno['controlno'];
                  }

                  //------------SENDING MAIL AS FOR OLD CODE----------------
                  $leave_type = "Undertime  - Offset of OT Control # " .  $controlno;
                  $date_filed = date('Y-m-d');
                  try {
                     $Mailer = new Mailer();
                     $mailer_from = $Mailer->get_sender($employee['emp_no']);
                     $mailer_to = $Mailer->get_recipient($employee['emp_no']);
                     $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                     $Mailer->mailformat_request($employee['emp_no'], $employee['emp_name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'],  $params['remarks'], $mailer_from,  $test_mail_to[0]['email']);
                     $response['status'] = '200';
                     $response['error'] = false;
                     $response['message'] = "Successfully saved request!";
                  } catch (\Throwable $th) {
                     $response['status'] = '503';
                     $response['error'] = true;
                     $response['message'] = "Unable to send email, the system will automatically sent this later.";
                     var_dump($th);
                  }
                  // ---------------------------------------------------------



               }
            } else {
               $response['error'] = true;
               $response['status'] = 503;
               $response['message'] = "Error on selected HOUR[from] and HOUR[to]. Please try again.";
               echo json_encode($response);
            }
         } else if ($data['filetype'] == "DayAbsent") {
            $data = $_POST['data'];

            $query = "SELECT no_of_hrs, isfye from ot_file where emp_no='" . $employee['emp_no'] . "' and controlno='" . $data['ot_code'] . "'";

            $stmt = sqlsrv_query($conn, $query);
            if (sqlsrv_fetch($stmt)) {
               $cls_ot_file = new Standard('');
               $ot_data = $cls_ot_file->bindMetaData($stmt);
               if ($ot_data['isfye'] != 1) {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "The overtime selected is not applicable for a whole day absent, Please select other filed overtime.";
                  echo json_encode($response);
               } else {
                  $query_sched = "select a.total_hrs from ref_schedule a left join ref_emp_trans b on a.shift_code = b.shift_code and a.br_prefix = substring(b.emp_no,1,2) where  b.emp_no = '" . $employee['emp_no'] . "'";
                  $stmt_sched = sqlsrv_query($conn, $query_sched);

                  $continue = false;
                  if (sqlsrv_fetch($stmt_sched)) {
                     $cls_Sched = new Standard('');
                     $total_sched = $cls_Sched->bindMetaData($stmt_sched);
                     $continue = true;
                  } else {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "You do not have any schedule. Please contact the administrator";
                     echo json_encode($response);
                  }

                  if ($continue) {

                     $query_check_filed = "select * from emp_request_master where ot_cntno = '" . $data['ot_code'] . "' and leavestatusid in(1,2,3,7,8)";

                     $stmt_check_filed = sqlsrv_query($conn, $query_check_filed);
                     if (sqlsrv_fetch($stmt_check_filed)) {
                        $response['error'] = true;
                        $response['status'] = 503;
                        $response['message'] = "Overtime already filed, Please select other OT.";
                        echo json_encode($response);
                     } else {

                        $GCN = new Standard("");
                        $controlno = $GCN->generateControlNumber('CS');
                        $controlno = $controlno['controlno'];
                        $xcontinue = false;
                        // ----------------INSERT----------------------
                        try {
                           $query_insert = "insert into emp_request_master (controlno,emp_no,lrs_type,date_from,date_to,leavestatusid,reason,encoded_by,encoded_date, isapproved,approved_by,approved_date,audit_user,audit_date,ispis,isoffset,ot_cntno,used_ot_hrs)  VALUES ('" . $controlno . "', '" . $employee['emp_no'] . "', 'LOAA', '" . $params['effective_date'] . "', '" . $params['effective_date'] . "', 1, '" . $params['remarks'] . "', '" . $employee['logname'] . "',getdate(), '0', '---',NULL, '---', NULL, '0',1, '" . $data['ot_code'] . "', '" . $total_sched['total_hrs'] . "')";
                           sqlsrv_query($conn, $query_insert);
                           $xcontinue = true;
                        } catch (\Throwable $th) {
                           $response['error'] = true;
                           $response['status'] = 503;
                           $response['message'] = "Unable to retrieve data, Please try again. If this error persist please contact the administrator.";
                           echo json_encode($response);
                           $xcontinue = false;
                        }

                        if ($xcontinue) {


                           //--------------------OT UPDATE------------------------------
                           $query = "UPDATE ot_file  SET isused = 1, used_un_hrs = remain_hrs, remain_hrs = 0 where controlno = '" .  $data['ot_code'] . "' and emp_no = '" . $employee['emp_no'] . "'";
                           $query_stmt = sqlsrv_query($conn, $query);
                           // ----------------------------------------------------------



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

                           $leave_type = 'Leave of Absence Authorized ' . ' &mdash; ' . '<br/>Offset of OT Control # ' .   $controlno;
                           $date_filed = date('Y-m-d');
                           $control_no = $ctrlList->controlno;
                           $lceff_date = $offDate;
                           $reason      = str_replace("'", "`", $offRem);
                           $mailer = new Mailer();
                           $mailer_from = $mailer->get_sender($employee['emp_no']);
                           $mailer_to = $mailer->get_recipient($employee['emp_no']);
                           $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
                           $mailer_send = $mailer->mailformat_request($employee['emp_no'], $employee['emp_name'], $leave_type, $date_filed,   $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from,  $test_mail_to);
                           $response['status'] = '200';
                           $response['error'] = false;
                           $response['message'] = "Successfully saved request!";
                        }
                     }
                  }
               }
            } else {
               $response['error'] = true;
               $response['status'] = 503;
               $response['message'] = "Unable to retrieve data, Please try again. If this error persist please contact the administrator.";
               echo json_encode($response);
            }
         } else {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "File Request Unrecognized!, Please try again";
            echo json_encode($response);
            die();
         }
      }
      if ($_POST['action'] == "SELECTOT") {
         $employee = extractEmployee($conn, $MAIN_TOKEN);

         $query_OTCONTROL = "select controlno as contNo, no_of_hrs as raw, convert(char(12),date,110) as otdate,replace(time_from,'.',':') as otFrom,replace(time_to,'.',':') as otTo,
         case when (no_of_hrs-used_un_hrs) <= 0 then replace(remain_hrs,'.',' hours ') +
               case when isfye = 1 then ' minutes (FYE)'
                  else ' minutes' end
         else
            replace(no_of_hrs-used_un_hrs,'.',' hours ') +
               case when isfye = 1 then ' minutes (FYE)'
                  else ' minutes' end end as no_of_hrs
         from ot_file where emp_no = '" . $employee['emp_no'] . "' and isused = 0 and leavestatusid not in (3,4,5,6,7) and remain_hrs >= 1
         and controlno not in(
         select (select char(39)+case when (select distinct ot_cntno from emp_request_master where emp_no = '" . $employee['emp_no'] . "' and leavestatusid in(1,2,3,7,8) and ltrim(rtrim(ot_cntno)) not in ('','cancelled',null)) is null
         then 'NO LOA' else (select distinct ot_cntno from emp_request_master where emp_no = '" . $employee['emp_no'] . "' and leavestatusid in(1,2,3,7,8) and ltrim(rtrim(ot_cntno)) not in ('','cancelled',null)) end+char(39)+','+char(39)+
         case when (select distinct ot_cntno from undertime_file where emp_no = '" . $employee['emp_no'] . "' and leavestatusid in(1,2,3,7,8) and ltrim(rtrim(ot_cntno)) not in ('','cancelled',null)) is null
         then 'NO UT' else (select distinct ot_cntno from undertime_file where emp_no = '" . $employee['emp_no'] . "' and leavestatusid in(1,2,3,7,8) and ltrim(rtrim(ot_cntno)) not in ('','cancelled',null)) end+char(39))
         )order by controlno desc";

         $OT = new Standard("");
         $OT_control = [];
         $stmt_OT_CONTROL = sqlsrv_query($conn, $query_OTCONTROL);
         while (sqlsrv_fetch($stmt_OT_CONTROL)) {
            array_push($OT_control, $OT->bindMetaData($stmt_OT_CONTROL));
         }
         $response['error'] = true;
         $response['status'] = 503;
         $response['data'] = $OT_control;
      }
      if ($_POST['action'] == "CHANGEOFWORKSCHED") {
         $data = $_POST['data'];

         $params['off_dat_off'] = date_format(date_create($data['off_dat_off'] ? $data['off_dat_off'] : $data['off_dat_off']), "m/d/Y");
         $params['timein'] = str_replace(":", ".", $data['timein']);
         $params['timeout'] = str_replace(":", ".", $data['timeout']);
         $params['lunchbreak_in'] = str_replace(":", ".", $data['lunchbreak_in']);
         $params['lunchbreak_out'] = str_replace(":", ".", $data['lunchbreak_out']);
         $params['coffee_in'] = str_replace(":", ".", $data['coffee_in']);
         $params['coffee_out'] = str_replace(":", ".", $data['coffee_out']);
         $params['effective_date'] =  date_format(date_create($data['date'] ? $data['date'] : $data['eff_date']), "m/d/Y");
         $params['remarks'] = $data['remarks'];
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         if (!$employee) {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "Token Unrecognized!.";
            echo json_encode($response);
            die();
         }

         switch ($data['reqtype']) {
            case 'RS':
               $lrs_cls = new Standard("");
               $queryLName = "select lrs_desc from ref_lrs_type where lrs_type like 'CHA'";
               $stmt_lname = sqlsrv_query($conn, $queryLName);
               if (sqlsrv_fetch($stmt_lname)) {
                  $leave_name = $lrs_cls->bindMetaData($stmt_lname);
               }
               $GCN = new Standard("");
               $controlno = $GCN->generateControlNumber('CW');
               $controlno = $controlno['controlno'];
               $xcontinue = false;

               //-----------------UPDATE CONTROL NUMBER SQ-------------------
               $clsControlNo = new Standard("");
               $stat_control_no = $clsControlNo->nextControlNumber('CW');
               //-------------------------------------------------------------

               //-----------------GETTING USED CONTROL #-------------------
               $cControlNo = new Standard("");
               $queryC = "select 'CW'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'CW'),8) as controlno";
               $stmt_queryC = sqlsrv_query($conn, $queryC);
               if (sqlsrv_fetch($stmt_queryC)) {
                  $controlno = $cControlNo->bindMetaData($stmt_queryC);
                  $controlno =  $controlno['controlno'];
               }

               $query_insert  = "insert into emp_request_master (controlno,emp_no,lrs_type,date_from,date_to,leavestatusid,reason,encoded_by, encoded_date,isapproved,approved_by,approved_date,audit_user,audit_date,ispis) select '" . $controlno . "','" . $employee['emp_no'] . "','CHA','" .  $params['effective_date'] . "','" .  $params['effective_date'] . "',1,'" . $lcRem . "','" . strtolower($employee['logname']) . "',getdate(),'0','---', NULL,'---',NULL,'0' ";
               $stmt_insert = sqlsrv_query($conn, $query_insert);
               if ($stmt_insert) {

                  $insert_time_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','AMI','" . $params['timein']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_time_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','PMO','" . $params['timeout']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_lunch_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','LBI','" . $params['lunchbreak_in']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_lunch_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','LBO','" . $params['lunchbreak_out']  . "','" . strtolower($employee['logname']) . "',getdate() ";


                  $insert_coffee_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','CBI','" . $params['coffee_in']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_coffee_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','CBO','" . $params['coffee_out']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $stmt_timein = sqlsrv_query($insert_time_in);
                  $stmt_timeout = sqlsrv_query($insert_time_out);
                  $stmt_lunchin = sqlsrv_query($insert_lunch_in);
                  $stmt_lunchout = sqlsrv_query($insert_lunch_out);
                  $stmt_coffeeIn = sqlsrv_query($insert_coffee_in);
                  $stmt_coffeeOut = sqlsrv_query($insert_coffee_out);

                  if (!$stmt_timein) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Time in data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_timeout) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Time out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_lunchin) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Lunch break In data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_lunchout) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Lunch break Out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_coffeeIn) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Coffee break In data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_coffeeOut) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Coffee break Out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }

                  $leave_type = $leave_name;
                  $date_filed = date('Y-m-d');
                  $mailer = new Mailer();
                  $mailer_from = $mailer->get_sender($employee['emp_no']);
                  $mailer_to = $mailer->get_recipient($employee['emp_no']);
                  $mailer_send = $mailer->mailformat_request($employee['emp_no'], $employee['name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from, $mailer_to);

                  $response['error'] = false;
                  $response['status'] = 200;
                  $response['message'] = "Request successfuly submitted";
               } else {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "Unable to save request. Please try again later.";
               }
               # code...
               break;
            case 'LB':
               $lrs_cls = new Standard("");
               $queryLName = "select lrs_desc from ref_lrs_type where lrs_type like 'LUB'";
               $stmt_lname = sqlsrv_query($conn, $queryLName);
               if (sqlsrv_fetch($stmt_lname)) {
                  $leave_name = $lrs_cls->bindMetaData($stmt_lname);
               }

               $GCN = new Standard("");
               $controlno = $GCN->generateControlNumber('LB');
               $controlno = $controlno['controlno'];
               $xcontinue = false;

               //-----------------UPDATE CONTROL NUMBER SQ-------------------
               $clsControlNo = new Standard("");
               $stat_control_no = $clsControlNo->nextControlNumber('LB');
               //-------------------------------------------------------------

               //-----------------GETTING USED CONTROL #-------------------
               $cControlNo = new Standard("");
               $queryC = "select 'LB'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'LB'),8) as controlno";
               $stmt_queryC = sqlsrv_query($conn, $queryC);
               if (sqlsrv_fetch($stmt_queryC)) {
                  $controlno = $cControlNo->bindMetaData($stmt_queryC);
                  $controlno =  $controlno['controlno'];
               }

               $query_insert  = "insert into emp_request_master (controlno,emp_no,lrs_type,date_from,date_to,leavestatusid,reason,encoded_by, encoded_date,isapproved,approved_by,approved_date,audit_user,audit_date,ispis) select '" . $controlno . "','" . $employee['emp_no'] . "','LUB','" .  $params['effective_date'] . "','" .  $params['effective_date'] . "',1,'" . $lcRem . "','" . strtolower($employee['logname']) . "',getdate(),'0','---', NULL,'---',NULL,'0' ";
               $stmt_insert = sqlsrv_query($conn, $query_insert);
               if ($stmt_insert) {
                  $insert_time_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','AMI','" . $params['timein']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_time_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','PMO','" . $params['timeout']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_lunch_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','LBI','" . $params['lunchbreak_in']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_lunch_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','LBO','" . $params['lunchbreak_out']  . "','" . strtolower($employee['logname']) . "',getdate() ";


                  $insert_coffee_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','CBI','" . $params['coffee_in']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_coffee_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','CBO','" . $params['coffee_out']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $stmt_timein = sqlsrv_query($insert_time_in);
                  $stmt_timeout = sqlsrv_query($insert_time_out);
                  $stmt_lunchin = sqlsrv_query($insert_lunch_in);
                  $stmt_lunchout = sqlsrv_query($insert_lunch_out);
                  $stmt_coffeeIn = sqlsrv_query($insert_coffee_in);
                  $stmt_coffeeOut = sqlsrv_query($insert_coffee_out);

                  if (!$stmt_timein) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Time in data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_timeout) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Time out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_lunchin) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Lunch break In data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_lunchout) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Lunch break Out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_coffeeIn) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Coffee break In data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_coffeeOut) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Coffee break Out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }

                  $leave_type = $leave_name;
                  $date_filed = date('Y-m-d');
                  $mailer = new Mailer();
                  $mailer_from = $mailer->get_sender($employee['emp_no']);
                  $mailer_to = $mailer->get_recipient($employee['emp_no']);
                  $mailer_send = $mailer->mailformat_request($employee['emp_no'], $employee['name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from, $mailer_to);

                  $response['error'] = false;
                  $response['status'] = 200;
                  $response['message'] = "Request successfuly submitted";
               } else {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "Unable to save request. Please try again later.";
               }
               # code...
               break;
            case 'CB':
               $lrs_cls = new Standard("");
               $queryLName = "select lrs_desc from ref_lrs_type where lrs_type like 'COB'";
               $stmt_lname = sqlsrv_query($conn, $queryLName);
               if (sqlsrv_fetch($stmt_lname)) {
                  $leave_name = $lrs_cls->bindMetaData($stmt_lname);
               }

               $GCN = new Standard("");
               $controlno = $GCN->generateControlNumber('CB');
               $controlno = $controlno['controlno'];
               $xcontinue = false;

               //-----------------UPDATE CONTROL NUMBER SQ-------------------
               $clsControlNo = new Standard("");
               $stat_control_no = $clsControlNo->nextControlNumber('CB');
               //-------------------------------------------------------------

               //-----------------GETTING USED CONTROL #-------------------
               $cControlNo = new Standard("");
               $queryC = "select 'CB'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'CB'),8) as controlno";
               $stmt_queryC = sqlsrv_query($conn, $queryC);
               if (sqlsrv_fetch($stmt_queryC)) {
                  $controlno = $cControlNo->bindMetaData($stmt_queryC);
                  $controlno =  $controlno['controlno'];
               }
               $query_insert  = "insert into emp_request_master (controlno,emp_no,lrs_type,date_from,date_to,leavestatusid,reason,encoded_by, encoded_date,isapproved,approved_by,approved_date,audit_user,audit_date,ispis) select '" . $controlno . "','" . $employee['emp_no'] . "','COB','" .  $params['effective_date'] . "','" .  $params['effective_date'] . "',1,'" . $lcRem . "','" . strtolower($employee['logname']) . "',getdate(),'0','---', NULL,'---',NULL,'0' ";
               $stmt_insert = sqlsrv_query($conn, $query_insert);
               if ($stmt_insert) {
                  $insert_time_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','AMI','" . $params['timein']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_time_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','PMO','" . $params['timeout']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_lunch_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','LBI','" . $params['lunchbreak_in']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_lunch_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','LBO','" . $params['lunchbreak_out']  . "','" . strtolower($employee['logname']) . "',getdate() ";


                  $insert_coffee_in = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','CBI','" . $params['coffee_in']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $insert_coffee_out = "insert into emp_request_detail (controlno,emp_no,sched_type,change_time,encoded_by,encoded_date) select'" .  $controlno  . "','" .  $employee['emp_no'] . "','CBO','" . $params['coffee_out']  . "','" . strtolower($employee['logname']) . "',getdate() ";

                  $stmt_timein = sqlsrv_query($insert_time_in);
                  $stmt_timeout = sqlsrv_query($insert_time_out);
                  $stmt_lunchin = sqlsrv_query($insert_lunch_in);
                  $stmt_lunchout = sqlsrv_query($insert_lunch_out);
                  $stmt_coffeeIn = sqlsrv_query($insert_coffee_in);
                  $stmt_coffeeOut = sqlsrv_query($insert_coffee_out);

                  if (!$stmt_timein) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Time in data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_timeout) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Time out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_lunchin) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Lunch break In data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_lunchout) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Lunch break Out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_coffeeIn) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Coffee break In data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }
                  if (!$stmt_coffeeOut) {
                     $response['error'] = true;
                     $response['status'] = 503;
                     $response['message'] = "Coffee break Out data was not able to save to database!";
                     echo json_encode($response);
                     die();
                  }

                  $leave_type = $leave_name;
                  $date_filed = date('Y-m-d');
                  $mailer = new Mailer();
                  $mailer_from = $mailer->get_sender($employee['emp_no']);
                  $mailer_to = $mailer->get_recipient($employee['emp_no']);
                  $mailer_send = $mailer->mailformat_request($employee['emp_no'], $employee['name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from, $mailer_to);
                  $response['error'] = false;
                  $response['status'] = 200;
                  $response['message'] = "Request successfuly submitted";
               } else {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "Unable to save request. Please try again later.";
               }

               break;
            case 'DO':
               $query = "select * from emp_request_master where lrs_type = 'DOF' and convert(char(10),date_from,112) like convert(char(10),cast('" .  $params['effective_date'] . "' as datetime),112) and leavestatusid not in(6,5) and emp_no = '" .  $employee['emp_no'] . "' ";
               $stmt = sqlsrv_query($conn, $query);
               if (sqlsrv_fetch($stmt)) {
                  $response['error'] = true;
                  $response['status'] = 503;
                  $response['message'] = "Request date already filed.";
               } else {
                  $GCN = new Standard("");
                  $controlno = $GCN->generateControlNumber('CD');
                  $controlno = $controlno['controlno'];
                  $xcontinue = false;

                  //-----------------UPDATE CONTROL NUMBER SQ-------------------
                  $clsControlNo = new Standard("");
                  $stat_control_no = $clsControlNo->nextControlNumber('CD');
                  //-------------------------------------------------------------

                  //-----------------GETTING USED CONTROL #-------------------
                  $cControlNo = new Standard("");
                  $queryC = "select 'CD'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'CD'),8) as controlno";
                  $stmt_queryC = sqlsrv_query($conn, $queryC);
                  if (sqlsrv_fetch($stmt_queryC)) {
                     $controlno = $cControlNo->bindMetaData($stmt_queryC);
                     $controlno =  $controlno['controlno'];
                  }


                  $query_insert = "insert into emp_request_master (controlno,emp_no,lrs_type,date_from,date_to,leavestatusid,reason,encoded_by, encoded_date,isapproved,approved_by,approved_date,audit_user,audit_date,ispis) select '" . $controlno . "','" . $employee['emp_no']  . "','DOF','" . $params['off_dat_off'] . "','" . $params['effective_date'] . "',1,'" .  $params['remarks'] . "','" . $employee['logname'] . "',getdate(),'0','---',NULL,'---',NULL,'0' ";
                  $stmt_insert = sqlsrv_query($conn, $query_insert);
                  if ($stmt_insert) {
                     $response['error'] = true;
                     $response['status'] = 200;
                     $response['message'] = "Request successfuly submitted.";
                  } else {
                     $response['error'] = true;
                     $response['status'] =  503;
                     $response['message'] = "System Error. Please try again later";
                  }
               }
               break;

            default:
               $response['error'] = true;
               $response['status'] =  403;
               $response['message'] = "File not found";
               break;
         }
      }
      if ($_POST['action'] == "ITENARYAPPROVAL") {
         $data = $_POST['data'];
         $dates = explode('-', $data['eff_date']);
         $params['date_from'] = trim($dates['0']);
         $params['date_to'] = trim($dates['1']);
         $params['timefrom'] = str_replace(":", ".", $data['timefrom']);

         $params['timeto'] = str_replace(":", ".", $data['timeto']);
         $params['remarks'] = $data['remarks'];
         $params['iarType'] = $data['iarType'];


         $employee = extractEmployee($conn, $MAIN_TOKEN);

         $query_check = "select controlno,emp_no,effdate,effdateto,timefr,timeto,leavestatusid,type,remark,ispis,encoded_by,encoded_date,approved_by,approved_date 
         from iar_file where emp_no = '" . $employee['emp_no'] . "' and ((CONVERT(CHAR(8),effdate, 112) between convert(char(8),cast('" . $params['date_from'] . "' as datetime),112) and 
         convert(char(8),cast('" .  $params['date_to'] . "' as datetime),112)) 
         or (CONVERT(CHAR(8),effdateto, 112) between convert(char(8),cast('" . $params['date_from'] . "' as datetime),112) and convert(char(8),cast('" .  $params['date_to'] . "' as datetime),112)))
         and leavestatusid not in(6,5)";

         $stmt_check = sqlsrv_query($conn, $query_check);
         if (sqlsrv_fetch($stmt_check)) {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "Effective already date.";
            echo json_encode($response);
         } else {


            $GCN = new Standard("");
            $controlno = $GCN->generateControlNumber('IA');
            $controlno = $controlno['controlno'];
            $xcontinue = false;

            //-----------------UPDATE CONTROL NUMBER SQ-------------------
            $clsControlNo = new Standard("");
            $stat_control_no = $clsControlNo->nextControlNumber('IA');
            //-------------------------------------------------------------

            //-----------------GETTING USED CONTROL #-------------------
            $cControlNo = new Standard("");
            $queryC = "select 'IA'+right('00000000'+(select ltrim(rtrim(str(controlno))) from ref_controlno where module_code = 'IA'),8) as controlno";
            $stmt_queryC = sqlsrv_query($conn, $queryC);
            if (sqlsrv_fetch($stmt_queryC)) {
               $controlno = $cControlNo->bindMetaData($stmt_queryC);
               $controlno =  $controlno['controlno'];
            }

            $query_insert = "Insert into iar_file (controlno,emp_no,effdate,effdateto,timefr,timeto,leavestatusid,type,remark,encoded_by,encoded_date,isapproved,approved_by,audit_user) select '" . $controlno . "','" . $employee['emp_no']  . "','" . $params['date_from']  . "','" . $params['date_to']  . "',substring('" . $params['timefrom'] . "',1,5),substring('" .  $params['timeto']  . "',1,5),1,'" . $params['iarType'] . "','" . str_replace("'", "`", $params['remarks']) . "','" . strtolower($employee['logname']) . "',getdate(),0,'---','---'";
            $stmt_insert = sqlsrv_query($conn, $query_insert);
            if ($stmt_insert) {
               $leave_type = 'Itinerary Approval';
               $date_filed = date('Y-m-d');
               $lceff_date = $params['date_from'] . '@ ' . $params['timefrom'] . ' - ' . $params['date_to'] . ' @ ' . $params['timeto'];

               $mailer = new Mailer();
               $mailer_from = $mailer->get_sender($employee['emp_no']);
               $mailer_to = $mailer->get_recipient($employee['emp_no']);
               $test_mail_to[0]['email'] = "marvin.orsua@ever.ph";
               $mailer_send = $mailer->mailformat_request($employee['emp_no'], $employee['emp_name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'],   $lceff_date, $params['remarks'], $mailer_from,  $test_mail_to);
               $response['status'] = '200';
               $response['error'] = false;
               $response['message'] = "Successfully saved request!";
            } else {
               $response['error'] = true;
               $response['status'] = 503;
               $response['message'] = "Unable to save request. Please try again later.";
            }
         }
      }
      if ($_POST['action'] == "deleterequest") {
         $contNoSub = substr($_POST['controlno'], 0, 2);

         switch (true) {
            case ($contNoSub == 'CS' || $contNoSub == 'CW' || $contNoSub == 'CD' || $contNoSub == 'LB' || $contNoSub == 'CB'):
               $toTable = 'emp_request_master';

               break;

            case ($contNoSub == 'LV'):
               $toTable = 'leave_trans';
               break;

            case ($contNoSub == 'OT'):
               $toTable = 'ot_file';
               break;

            case ($contNoSub == 'UN'):
               $toTable = 'undertime_file';
               break;

            case ($contNoSub == 'IA'):
               $toTable = 'iar_file';
               break;

            case ($contNoSub == 'TR'):
               $toTable = 'tar_file';
               break;

            case ($contNoSub == 'AD'):
               $toTable = 'rsr_aday_only_file';
               break;

            default:
               $toTable = 'leave_trans';
               break;
         }


         $qryDel = "select * from " . $toTable . " where leavestatusid = '1' and isapproved = '0' and controlno = '" . $_POST['controlno'] . "'";
         $resultDel = sqlsrv_query($conn, $qryDel);
         if (!sqlsrv_fetch($resultDel)) {

            $response['status'] = 'error';
            $response['message'] = "No Requests Found!(for deletion)...";
            echo json_encode($response);
            die();
         } else {
            //for updating in offset
            $tableCancel = array("emp_request_master", "undertime_file");

            //check property on submitted (offset/not)

            if (in_array(trim($toTable), $tableCancel)) {
               $clsResult = new Standard('');
               $isOffset = $clsResult->bindMetaData($resultDel)['isoffset'];
               if ($isOffset == 1) {
                  $queryUpd = "update ot_file set ut_cntno = null,used_un_hrs = 0,remain_hrs = no_of_hrs,isused = 0 where ut_cntno = '" . $_POST['controlno'] . "'
                               update " . $toTable . " set leavestatusid = '6',approved_date = getdate() ,ot_cntno = 'cancelled',used_ot_hrs = 0 where controlno = '" . $_POST['controlno'] . "'";
                  $resultUpd = sqlsrv_query($conn, $queryUpd);
               } else {
                  //regular filed
                  $queryUpd = "update " . $toTable . " set leavestatusid = '6',approved_date = getdate() where controlno = '" . $_POST['controlno'] . "'";
                  $resultUpd = sqlsrv_query($conn, $queryUpd);
               }
            } else {
               //regular filed
               $queryUpd = "update " . $toTable . " set leavestatusid = '6',approved_date = getdate() where controlno = '" . $_POST['controlno'] . "'";
               $resultUpd = sqlsrv_query($conn, $queryUpd);
            }


            $response['error'] = false;
            $response['status'] = 200;
            $response['message'] = "Successfuly deleted request.";
         }
      }
      if ($_POST['action'] == "approvals") {
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         $employee['emp_no'] = "9900628";
         if ($employee) {
            $query = "SELECT e.controlno,
                     Rtrim(Ltrim(a.lastname)) + ', '
                     + Rtrim(Ltrim(a.firstname)) + ' '
                     + Substring(a.middlename, 1, 1) + '. ('
                     + Ltrim(Rtrim(z.sitecode)) + ')'       AS NAME,
                     CASE
                     WHEN Ltrim(Rtrim(h.leave_code)) IN( 'HV', 'HS' ) THEN
                     Ltrim(Rtrim(h.leave_name)) + ' '
                     + Ltrim(Rtrim(CONVERT(CHAR(6), e.no_of_hrs)))
                     + ' hours only'
                     ELSE Ltrim(Rtrim(h.leave_name))
                     END                                    AS leave_name,
                     CONVERT(CHAR(12), date_ffrom, 101)     AS date_Ffrom,
                     CONVERT(CHAR(12), date_fto, 101)       AS date_Fto,
                     e.reason,
                     CONVERT(CHAR(12), e.encoded_date, 101) AS encoded_date,
                     g.leavestatus,
                     CASE
                     WHEN Ltrim(Rtrim(e.approved_by)) = '---'
                           AND Ltrim(Rtrim(e.audit_user)) = '---' THEN 'New Request'
                     ELSE
                        CASE
                           WHEN Ltrim(Rtrim(e.approved_by)) != '---'
                              AND Ltrim(Rtrim(e.audit_user)) = '---' THEN
                           'Reviewed | Unapproved'
                           ELSE
                           CASE
                              WHEN Ltrim(Rtrim(e.approved_by)) != '---'
                                    AND Ltrim(Rtrim(e.audit_user)) != '---' THEN
                              'Reviewed | Approved'
                              ELSE
                                 CASE
                                 WHEN Ltrim(Rtrim(e.approved_by)) = '---'
                                       AND Ltrim(Rtrim(e.audit_user)) != '---' THEN
                                 'Approved | Approved'
                                 END
                           END
                        END
                     END                                    AS apphead
            FROM   leave_trans e
                     LEFT JOIN ref_emp_trans b
                           ON e.emp_no = b.emp_no
                     LEFT JOIN ref_emp_mast a
                           ON a.emp_no = e.emp_no
                     LEFT JOIN ref_leavestat g
                           ON e.leavestatusid = g.leavestatusid
                     LEFT JOIN ref_leave_code h
                           ON e.leave_code = h.leave_code
                     LEFT JOIN ref_hris_approver i
                           ON b.emp_no = i.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(a.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  b.br_code LIKE '%%'
                     AND e.leavestatusid IN ( '1', '8' )
                     AND i.approver_empno = '" . $employee['emp_no'] . "'
            UNION
            SELECT j.controlno,
                     Rtrim(Ltrim(k.lastname)) + ', '
                     + Rtrim(Ltrim(k.firstname)) + ' '
                     + Substring(k.middlename, 1, 1) + '. ('
                     + Ltrim(Rtrim(z.sitecode)) + ')',
                     o.lrs_desc,
                     CONVERT(CHAR(12), j.date_from, 101),
                     CONVERT(CHAR(12), j.date_to, 101),
                     CASE
                     WHEN Ltrim(Rtrim(j.ot_cntno)) != '' THEN
                     Ltrim(Rtrim(j.reason)) + ' [Offsetting '
                     + Ltrim(Rtrim(j.ot_cntno)) + ']'
                     ELSE Ltrim(Rtrim(j.reason))
                     END,
                     CONVERT(CHAR(12), j.encoded_date, 101),
                     n.leavestatus,
                     CASE
                     WHEN Ltrim(Rtrim(j.approved_by)) = '---'
                           AND Ltrim(Rtrim(j.audit_user)) = '---' THEN 'New Request'
                     ELSE
                        CASE
                           WHEN Ltrim(Rtrim(j.approved_by)) != '---'
                              AND Ltrim(Rtrim(j.audit_user)) = '---' THEN
                           'Reviewed | Unapproved'
                           ELSE
                           CASE
                              WHEN Ltrim(Rtrim(j.approved_by)) != '---'
                                    AND Ltrim(Rtrim(j.audit_user)) != '---' THEN
                              'Reviewed | Approved'
                              ELSE
                                 CASE
                                 WHEN Ltrim(Rtrim(j.approved_by)) = '---'
                                       AND Ltrim(Rtrim(j.audit_user)) != '---' THEN
                                 'Approved | Approved'
                                 END
                           END
                        END
                     END AS apphead
            FROM   emp_request_master j
                     LEFT JOIN ref_emp_mast k
                           ON j.emp_no = k.emp_no
                     LEFT JOIN ref_emp_trans l
                           ON l.emp_no = j.emp_no
                     LEFT JOIN ref_leavestat n
                           ON n.leavestatusid = j.leavestatusid
                     LEFT JOIN ref_emp_request_type o
                           ON o.lrs_type = j.lrs_type
                     LEFT JOIN ref_hris_approver p
                           ON j.emp_no = p.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(k.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  l.br_code LIKE '%%'
                     AND j.leavestatusid IN ( '1', '8' )
                     AND p.approver_empno = '" . $employee['emp_no'] . "'
            UNION
            SELECT j1.controlno,
                     Rtrim(Ltrim(k1.lastname)) + ', '
                     + Rtrim(Ltrim(k1.firstname)) + ' '
                     + Substring(k1.middlename, 1, 1) + '. ('
                     + Ltrim(Rtrim(z.sitecode)) + ')',
                     'Overtime ' + l1.ot_name,
                     CONVERT(CHAR(12), j1.date, 101) + '@ ' + CASE WHEN j1.time_from BETWEEN 0
                     AND
                     11.99 THEN Rtrim(Ltrim(CONVERT(CHAR(6), j1.time_from)))+ ' am ' ELSE CASE
                     WHEN
                     j1.time_from BETWEEN 12 AND 12.99 THEN
                     Rtrim(Ltrim(CONVERT(CHAR(6), j1.time_from)))+ ' pm ' ELSE Rtrim(Ltrim(
                     CONVERT(
                     CHAR(6), j1.time_from-12)))+ ' pm ' END
                     END,
                     CONVERT(CHAR(12), CASE WHEN time_from > time_to THEN
                     CONVERT(CHAR(12), j1.date+1, 101) ELSE CONVERT(CHAR(12), j1.date, 101)
                     END, 101)
                     + '@ ' + CASE WHEN
                     j1.time_to BETWEEN 0 AND 11.99 THEN
                     Rtrim(Ltrim(CONVERT(CHAR(6), j1.time_to)))+
                     ' am '
                     ELSE CASE WHEN j1.time_to BETWEEN 12 AND 12.99 THEN Rtrim(Ltrim(CONVERT(
                     CHAR(6),
                     j1.time_to)))+ ' pm ' ELSE Rtrim(Ltrim(CONVERT(CHAR(6), j1.time_to-12)))+
                     ' pm '
                     END END,
                     j1.reason,
                     CONVERT(CHAR(12), j1.encoded_date, 101),
                     o1.leavestatus,
                     CASE
                     WHEN Ltrim(Rtrim(j1.approved_by)) = '---'
                           AND Ltrim(Rtrim(j1.audit_user)) = '---' THEN 'New Request'
                     ELSE
                        CASE
                           WHEN Ltrim(Rtrim(j1.approved_by)) != '---'
                              AND Ltrim(Rtrim(j1.audit_user)) = '---' THEN
                           'Reviewed | Unapproved'
                           ELSE
                           CASE
                              WHEN Ltrim(Rtrim(j1.approved_by)) != '---'
                                    AND Ltrim(Rtrim(j1.audit_user)) != '---' THEN
                              'Reviewed | Approved'
                              ELSE
                                 CASE
                                 WHEN Ltrim(Rtrim(j1.approved_by)) = '---'
                                       AND Ltrim(Rtrim(j1.audit_user)) != '---' THEN
                                 'Approved | Approved'
                                 END
                           END
                        END
                     END AS apphead
            FROM   ot_file j1
                     LEFT JOIN ref_emp_mast k1
                           ON j1.emp_no = k1.emp_no
                     LEFT JOIN ref_ot_code l1
                           ON j1.ot_code = l1.ot_code
                     LEFT JOIN ref_emp_trans m1
                           ON m1.emp_no = j1.emp_no
                     LEFT JOIN ref_leavestat o1
                           ON o1.leavestatusid = j1.leavestatusid
                     LEFT JOIN ref_hris_approver p1
                           ON j1.emp_no = p1.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(k1.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  m1.br_code LIKE '%%'
                     AND j1.leavestatusid IN ( '1', '8' )
                     AND p1.approver_empno = '" . $employee['emp_no'] . "'
            UNION
            SELECT j2.controlno,
                     Rtrim(Ltrim(k2.lastname)) + ', '
                     + Rtrim(Ltrim(k2.firstname)) + ' '
                     + Substring(k2.middlename, 1, 1) + '. ('
                     + Ltrim(Rtrim(z.sitecode)) + ')',
                     'Undertime ',
                     CONVERT(CHAR(12), j2.effdate, 101) + '@ ' + CASE WHEN j2.timein BETWEEN 0
                     AND
                     11.99 THEN Rtrim(Ltrim(CONVERT(CHAR(6), j2.timein)))+ ' am ' ELSE CASE
                     WHEN
                     j2.timein BETWEEN 12 AND 12.99 THEN
                     Rtrim(Ltrim(CONVERT(CHAR(6), j2.timein)))+
                     ' pm '
                     ELSE Rtrim(Ltrim(CONVERT(CHAR(6), j2.timein-12)))+ ' pm ' END END,
                     CONVERT(CHAR(12), j2.effdate, 101) + '@ ' + CASE WHEN j2.timeout BETWEEN
                     0 AND
                     11.99 THEN Rtrim(Ltrim(CONVERT(CHAR(6), j2.timeout)))+ ' am ' ELSE CASE
                     WHEN
                     j2.timeout BETWEEN 12 AND 12.99 THEN
                     Rtrim(Ltrim(CONVERT(CHAR(6), j2.timeout)))+
                     ' pm '
                     ELSE Rtrim(Ltrim(CONVERT(CHAR(6), j2.timeout-12)))+ ' pm ' END END,
                     CASE
                     WHEN Ltrim(Rtrim(j2.ot_cntno)) != '' THEN
                     Ltrim(Rtrim(j2.reason)) + ' [Offsetting '
                     + Ltrim(Rtrim(j2.ot_cntno)) + ']'
                     ELSE Ltrim(Rtrim(j2.reason))
                     END,
                     CONVERT(CHAR(12), j2.encoded_date, 101),
                     o2.leavestatus,
                     CASE
                     WHEN Ltrim(Rtrim(j2.approved_by)) = '---'
                           AND Ltrim(Rtrim(j2.audit_user)) = '---' THEN 'New Request'
                     ELSE
                        CASE
                           WHEN Ltrim(Rtrim(j2.approved_by)) != '---'
                              AND Ltrim(Rtrim(j2.audit_user)) = '---' THEN
                           'Reviewed | Unapproved'
                           ELSE
                           CASE
                              WHEN Ltrim(Rtrim(j2.approved_by)) != '---'
                                    AND Ltrim(Rtrim(j2.audit_user)) != '---' THEN
                              'Reviewed | Approved'
                              ELSE
                                 CASE
                                 WHEN Ltrim(Rtrim(j2.approved_by)) = '---'
                                       AND Ltrim(Rtrim(j2.audit_user)) != '---' THEN
                                 'Approved | Approved'
                                 END
                           END
                        END
                     END AS apphead
            FROM   undertime_file j2
                     LEFT JOIN ref_emp_mast k2
                           ON j2.emp_no = k2.emp_no
                     LEFT JOIN ref_emp_trans m2
                           ON m2.emp_no = j2.emp_no
                     LEFT JOIN ref_leavestat o2
                           ON o2.leavestatusid = j2.leavestatusid
                     LEFT JOIN ref_hris_approver p2
                           ON j2.emp_no = p2.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(k2.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  m2.br_code LIKE '%%'
                     AND j2.leavestatusid IN ( '1', '8' )
                     AND p2.approver_empno = '" . $employee['emp_no'] . "'
            UNION
            SELECT j3.controlno,
                     Rtrim(Ltrim(k3.lastname)) + ', '
                     + Rtrim(Ltrim(k3.firstname)) + ' '
                     + Substring(k3.middlename, 1, 1) + '. ('
                     + Ltrim(Rtrim(z.sitecode)) + ')',
                     'itinerary Approval Request',
                     CONVERT(CHAR(12), j3.effdate, 101) + '@ ' + CASE WHEN j3.timefr BETWEEN 0
                     AND
                     11.99 THEN Rtrim(Ltrim(CONVERT(CHAR(6), j3.timefr)))+ ' am ' ELSE CASE
                     WHEN
                     j3.timefr BETWEEN 12 AND 12.99 THEN
                     Rtrim(Ltrim(CONVERT(CHAR(6), j3.timefr)))+
                     ' pm '
                     ELSE Rtrim(Ltrim(CONVERT(CHAR(6), j3.timefr-12)))+ ' pm ' END END,
                     CONVERT(CHAR(12), j3.effdateto, 101) + '@ '
                     + CASE WHEN j3.timeto BETWEEN 0 AND 11.99 THEN
                     Rtrim(Ltrim(CONVERT(CHAR(6), j3.timeto)))+ ' am ' ELSE CASE WHEN
                     j3.timeto
                     BETWEEN 12 AND 12.99 THEN Rtrim(Ltrim(
                     CONVERT(CHAR(6), j3.timeto)))+ ' pm ' ELSE
                     Rtrim(Ltrim(CONVERT(CHAR(6), j3.timeto-12)))+ ' pm ' END END,
                     CASE
                     WHEN Ltrim(Rtrim(j3.ot_cntno)) != '' THEN
                     Ltrim(Rtrim(j3.remark)) + ' [Offsetting '
                     + Ltrim(Rtrim(j3.ot_cntno)) + ']'
                     ELSE Ltrim(Rtrim(j3.remark))
                     END,
                     CONVERT(CHAR(12), j3.encoded_date, 101),
                     o3.leavestatus,
                     CASE
                     WHEN Ltrim(Rtrim(j3.approved_by)) = '---'
                           AND Ltrim(Rtrim(j3.audit_user)) = '---' THEN 'New Request'
                     ELSE
                        CASE
                           WHEN Ltrim(Rtrim(j3.approved_by)) != '---'
                              AND Ltrim(Rtrim(j3.audit_user)) = '---' THEN
                           'Reviewed | Unapproved'
                           ELSE
                           CASE
                              WHEN Ltrim(Rtrim(j3.approved_by)) != '---'
                                    AND Ltrim(Rtrim(j3.audit_user)) != '---' THEN
                              'Reviewed | Approved'
                              ELSE
                                 CASE
                                 WHEN Ltrim(Rtrim(j3.approved_by)) = '---'
                                       AND Ltrim(Rtrim(j3.audit_user)) != '---' THEN
                                 'Approved | Approved'
                                 END
                           END
                        END
                     END AS apphead
            FROM   iar_file j3
                     LEFT JOIN ref_emp_mast k3
                           ON j3.emp_no = k3.emp_no
                     LEFT JOIN ref_emp_trans m3
                           ON m3.emp_no = j3.emp_no
                     LEFT JOIN ref_leavestat o3
                           ON o3.leavestatusid = j3.leavestatusid
                     LEFT JOIN ref_hris_approver q3
                           ON j3.emp_no = q3.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(k3.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  m3.br_code LIKE '%%'
                     AND j3.leavestatusid IN ( '1', '8' )
                     AND q3.approver_empno = '" . $employee['emp_no'] . "'
            UNION
            SELECT a1.controlno,
                     Rtrim(Ltrim(k.lastname)) + ', '
                     + Rtrim(Ltrim(k.firstname)) + ' '
                     + Substring(k.middlename, 1, 1) + '. ('
                     + Ltrim(Rtrim(z.sitecode)) + ')',
                     'RSR A DAY ONLY',
                     CONVERT(CHAR(12), a1.effective_date_fr, 101),
                     CONVERT(CHAR(12), a1.effective_date_to, 101)
                     + ' '
                     + Ltrim(Rtrim(CONVERT(CHAR(6), a1.t_in_r)))
                     + '>'
                     + Ltrim(Rtrim(CONVERT(CHAR(6), a1.l_out_r)))
                     + '>'
                     + Ltrim(Rtrim(CONVERT(CHAR(6), a1.l_in_r)))
                     + '>'
                     + Ltrim(Rtrim(CONVERT(CHAR(6), a1.c_out_r)))
                     + '>'
                     + Ltrim(Rtrim(CONVERT(CHAR(6), a1.c_in_r)))
                     + '>'
                     + Ltrim(Rtrim(CONVERT(CHAR(6), a1.t_out_r)))
                     + '>',
                     Ltrim(Rtrim(a1.reason)),
                     CONVERT(CHAR(12), a1.encoded_date, 101),
                     n.leavestatus,
                     CASE
                     WHEN Ltrim(Rtrim(a1.approved_by)) = '---'
                           AND Ltrim(Rtrim(a1.audit_user)) = '---' THEN 'New Request'
                     ELSE
                        CASE
                           WHEN Ltrim(Rtrim(a1.approved_by)) != '---'
                              AND Ltrim(Rtrim(a1.audit_user)) = '---' THEN
                           'Reviewed | Unapproved'
                           ELSE
                           CASE
                              WHEN Ltrim(Rtrim(a1.approved_by)) != '---'
                                    AND Ltrim(Rtrim(a1.audit_user)) != '---' THEN
                              'Reviewed | Approved'
                              ELSE
                                 CASE
                                 WHEN Ltrim(Rtrim(a1.approved_by)) = '---'
                                       AND Ltrim(Rtrim(a1.audit_user)) != '---' THEN
                                 'Approved | Approved'
                                 END
                           END
                        END
                     END AS apphead
            FROM   rsr_aday_only_file a1
                     LEFT JOIN ref_emp_mast k
                           ON a1.emp_no = k.emp_no
                     LEFT JOIN ref_emp_trans l
                           ON l.emp_no = a1.emp_no
                     LEFT JOIN ref_leavestat n
                           ON n.leavestatusid = a1.leavestatusid
                     LEFT JOIN ref_hris_approver p
                           ON a1.emp_no = p.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(k.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  l.br_code LIKE '%%'
                     AND a1.leavestatusid IN ( '1', '8' )
                     AND p.approver_empno = '" . $employee['emp_no'] . "'
            UNION
            SELECT j4.controlno,
               Rtrim(Ltrim(k4.lastname)) + ', '
               + Rtrim(Ltrim(k4.firstname)) + ' '
               + Substring(k4.middlename, 1, 1) + '. ('
               + Ltrim(Rtrim(z.sitecode)) + ')',
               'Temporary Attendance Record',
               CONVERT(CHAR(12), j4.effdate, 101),
               CONVERT(CHAR(12), j4.effdate, 101) + ' '
               + Ltrim(Rtrim(CONVERT(CHAR(6), j4.timein)+'>'+CONVERT(CHAR(6), j4.lunchout)+'>'+
               CONVERT(CHAR(6), j4.lunchin)+'>'+CONVERT(CHAR(6), j4.coffeeout)+'>'+CONVERT(CHAR(6), j4.coffeein)+'>'+CONVERT(CHAR(6), j4.timeout)+'>'+CONVERT(CHAR(6), j4.fye_in)+'>'+CONVERT(CHAR(6), j4.fye_out))),
               j4.reason,
               CONVERT(CHAR(12), j4.encoded_date, 101),
               o4.leavestatus,
            CASE
               WHEN Ltrim(Rtrim(j4.approved_by)) = '---'
                     AND Ltrim(Rtrim(j4.audit_user)) = '---' THEN 'New Request'
               ELSE
                  CASE
                  WHEN Ltrim(Rtrim(j4.approved_by)) != '---'
                        AND Ltrim(Rtrim(j4.audit_user)) = '---' THEN 'Reviewed | Unapproved'
                  ELSE
                     CASE
                        WHEN Ltrim(Rtrim(j4.approved_by)) != '---'
                           AND Ltrim(Rtrim(j4.audit_user)) != '---' THEN
                        'Reviewed | Approved'
                        ELSE
                        CASE
                           WHEN Ltrim(Rtrim(j4.approved_by)) = '---'
                                 AND Ltrim(Rtrim(j4.audit_user)) != '---' THEN
                           'Approved | Approved'
                        END
                     END
                  END
            END AS apphead
            FROM   tar_file j4
                     LEFT JOIN ref_emp_mast k4
                           ON j4.emp_no = k4.emp_no
                     LEFT JOIN ref_emp_trans m4
                           ON m4.emp_no = j4.emp_no
                     LEFT JOIN ref_position n4
                           ON n4.br_code = m4.br_code
                              AND n4.div_code = m4.div_code
                              AND n4.rank_code = m4.rank_code
                              AND n4.dept_code = m4.dept_code
                              AND n4.post_code = m4.post_code
                     LEFT JOIN ref_leavestat o4
                           ON o4.leavestatusid = j4.leavestatusid
                     LEFT JOIN ref_hris_approver p4
                           ON j4.emp_no = p4.emp_no
                     LEFT JOIN ref_branch z
                           ON Substring(Ltrim(Rtrim(k4.emp_no)), 1, 2) =
                              Ltrim(Rtrim(z.id_prefix))
            WHERE  m4.br_code LIKE '%%'
                     AND j4.leavestatusid IN ( '1', '8' )
                     AND j4.isapproved = '0'
                     AND p4.approver_empno = '" . $employee['emp_no'] . "'
            ORDER  BY controlno ASC ";
            $stmt = sqlsrv_query($conn, $query);
            $clsApproval = new Standard('');
            $apprData = [];
            while (sqlsrv_fetch($stmt)) {
               array_push($apprData, $clsApproval->bindMetaData($stmt));
            }
            if (count($apprData)) {
               $response['error'] = false;
               $response['status'] = 200;
               $response['data'] =  $apprData;
            } else {
               $response['error'] = true;
               $response['status'] = 503;
               $response['message'] = "No data found";
            }
         } else {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "Token unrecognized.";
         }
      }
      if ($_POST['action'] == "approvereq") {
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         //Contribution tables checking before update by first 2 digit control number...
         $contNoSubHeadOk = substr($_POST['controlid'], 0, 2);
         switch (true) {
            case ($contNoSubHeadOk == 'CS' || $contNoSubHeadOk == 'CW' || $contNoSubHeadOk == 'CD' || $contNoSubHeadOk == 'LB' || $contNoSubHeadOk == 'CB'):
               $toTableHeadOk = 'emp_request_master';
               $Name_j = ",j.lrs_desc as leave_name,case when a.date_from = a.date_to then ltrim(rtrim(convert(char(12),a.date_from))) else ltrim(rtrim(convert(char(12),a.date_from)))+ltrim(rtrim(convert(char(12),a.date_to))) end as reqdate ";
               $l_Join = "left join ref_lrs_type j on a.lrs_type = j.lrs_type ";
               break;

            case ($contNoSubHeadOk == 'LV'):
               $toTableHeadOk = 'leave_trans';
               $Name_j = ",j.leave_name as leave_name,case when a.date_fto = a.date_ffrom then ltrim(rtrim(convert(char(12),a.date_fto))) else ltrim(rtrim(convert(char(12),a.date_ffrom)))+ltrim(rtrim(convert(char(12),a.date_fto))) end as reqdate ";
               $l_Join = "left join ref_leave_code j on a.leave_code = j.leave_code  ";
               break;

            case ($contNoSubHeadOk == 'TR'):
               $toTableHeadOk = 'tar_file';
               $Name_j = ",'Temporary Attendance Record' as leave_name,ltrim(rtrim(convert(char(12),a.effdate)))+' @ '+convert(char(6),a.timein)+' | '+convert(char(6),a.lunchout)+' | '+convert(char(6),a.lunchin)+' | '+convert(char(6),a.coffeeout)+' | '+convert(char(6),a.coffeein)+' | '+convert(char(6),a.timeout)+' | '+convert(char(6),a.fye_in)+' | '+convert(char(6),a.fye_out) as reqdate ";
               $l_Join = "";

               break;


            case ($contNoSubHeadOk == 'OT'):
               $toTableHeadOk = 'ot_file';
               $Name_j = ",'Overtime @ '+j.ot_name as leave_name,ltrim(rtrim(convert(char(12),a.date)))+' @ '+convert(char(6),a.time_from)+' to '+convert(char(6),a.time_to) as reqdate ";
               $l_Join = "left join ref_ot_code j on a.ot_code = j.ot_code ";

               break;

            case ($contNoSubHeadOk == 'UN'):
               $toTableHeadOk = 'undertime_file';
               $Name_j = ",'Undertime ' as leave_name,'Hours rendered: '+ltrim(rtrim(convert(char(12),a.effdate)))+' @ '+convert(char(6),a.timein)+' | '+convert(char(6),a.timeout) as reqdate ";
               $l_Join = "";
               break;
            case ($contNoSubHeadOk == 'IA'):
               $toTableHeadOk = 'iar_file';
               $Name_j = ",'Itinerary Approval Report ' as leave_name,ltrim(rtrim(convert(char(12),a.effdate)))+' @ '+convert(char(6),a.timefr)+' | '+convert(char(6),a.timeto) as reqdate ";
               $l_Join = "";
               break;
            case ($contNoSubHeadOk == 'AD'):
               $toTableHeadOk = 'rsr_aday_only_file';
               $Name_j = ",'RSR A DAY ONLY' as leave_name,ltrim(rtrim(convert(char(12),a.effective_date_fr)))+' @ '+convert(char(6),a.effective_date_to)+' | '+ convert(char(6),a.t_in_r)+ ' | '+convert(char(6),a.l_out_r)+' | '+convert(char(6),a.l_in_r)+' | '+convert(char(6),a.c_out_r)+' | '+convert(char(6),a.c_in_r)+' | '+convert(char(6),a.t_out_r) as reqdate ";
               $l_Join = "";
               break;

            default:
               $response['error'] = false;
               $response['status'] = 'error';
               $response['message'] = "System ERROR! (unrecognize control number)";
               echo json_encode($response);
               die();
         }

         //check for requestor employee number
         $qryRequestor = "select emp_no,leavestatusid from " . $toTableHeadOk . " where controlno = '" . $_POST['controlid'] . "' ";

         $resultRequestorEmpno = sqlsrv_query($conn, $qryRequestor);
         $employee['emp_no'] = "9900628";
         while ($empnumber = sqlsrv_fetch_object($resultRequestorEmpno)) {


            //select approver's position (Manager/Supervisor)
            $qryappRank = "select top 1 case when (select count(emp_no) from ref_hris_approver where emp_no = '" . trim($empnumber->emp_no) . "')= 1 then 'Approver' ";
            $qryappRank .= "when a.approver_rank_code > b.approver_rank_code then 'Reviewer' ";
            $qryappRank .= "when a.approver_rank_code = b.approver_rank_code then 'Approver' ";
            $qryappRank .= "when a.approver_rank_code < b.approver_rank_code then 'Approver' end as approver_login_as ";
            $qryappRank .= "from (select * from ref_hris_approver where emp_no = '" . trim($empnumber->emp_no) . "' and approver_empno = '" . trim($employee['emp_no']) . "') a  ";
            $qryappRank .= "left join (select * from ref_hris_approver where emp_no = '" . trim($empnumber->emp_no) . "' and approver_empno != '" . trim($employee['emp_no']) . "') b on a.emp_no = b.emp_no  ";

            $responseppRank = sqlsrv_query($conn, $qryappRank);
            while ($appRank = sqlsrv_fetch_object($responseppRank)) {
               if ($appRank->approver_login_as == "Reviewer" and $empnumber->leavestatusid == 1) {
                  //update leave trans on selected Filed user leave under supervisor
                  $queryUpd = "update " . $toTableHeadOk . " set leavestatusid = '8',approved_by = '" . $employee['logname'] . "',isapproved = '0',approved_date = getdate(),remarks ='Reviewed by: <br/>" . $employee['logname'] . ' - ' . str_replace('\'', '`', $employee['position']) . ' (' . trim($appRank->approver_login_as) . ')<br/>' . "' where controlno = '" . $_POST['controlid'] . "'";
                  $apprem = "Reviewed by: " . $employee['logname'] . "-" . str_replace('\'', '`', $employee['position']) . " (" . trim($appRank->approver_login_as) . ")";
               } else {
                  //update leave trans on selected Filed user leave under 2x supervisor/direct manager
                  $queryUpd = "update " . $toTableHeadOk . " set leavestatusid = '2',audit_user = '" . $employee['logname'] . "',isapproved = '1',approved_date = getdate(),remarks =ltrim(rtrim(remarks))+'Approved by: <br/>" . $employee['logname'] . ' - ' . str_replace('\'', '`', $employee['position']) . ' (' . trim($appRank->approver_login_as) . ')' . "' where controlno = '" . $_POST['controlid'] . "'";
                  $apprem = "Approved by: " . $employee['logname'] . "-" . str_replace('\'', '`', $employee['position']) . " (" . trim($appRank->approver_login_as) . ")";
               }
            }
         }
         $resultUpd = sqlsrv_query($conn, $queryUpd);
         if ($resultUpd) {
            $response['error'] = false;
            $response['status'] = '200';
            $response['message'] = 'Request Approved!';
         } else {
            $response['error'] = true;
            $response['status'] = '503';
            $response['message'] = 'Request was not processed';
         }


         $qryRequeststat = "select b.leavestatus,a.encoded_date as datefiled,ltrim(rtrim(c.lastname))+', '  +ltrim(rtrim(c.firstname))+' '+upper(substring(ltrim(rtrim(c.middlename)),1,1))+'.' as name,
               h.br_name,i.email" . $Name_j . "
               from " . $toTableHeadOk . " a
               left join  ref_leavestat b on a.leavestatusid = b.leavestatusid
               left join ref_emp_mast c on a.emp_no = c.emp_no
               left join ref_emp_trans d on d.emp_no = c.emp_no
               left join ref_position e on d.br_code = e.br_code and d.div_code = e.div_code
               and d.rank_code = e.rank_code and d.dept_code = e.dept_code and d.post_code = e.post_code
               left join hris_mainLogIn f on c.emp_no =  f.temp_pass
               left join ref_department g on g.br_code = d.br_code and g.div_code = d.div_code and g.dept_code = d.dept_code
               left join ref_branch h on g.br_code = h.br_code
               left join eversql.ehelpdesk.dbo.employee i on i.emp_no = c.emp_no and i.is_active = 1
               " . $l_Join . "
               where a.controlno = '" . $_POST['controlid'] . "'";
         $resultRequeststat = sqlsrv_query($qryRequeststat);
         while ($reqstat = sqlsrv_fetch_object($resultRequeststat)) {
            $reStat = $reqstat->leavestatus;
            $dateFiled = $reqstat->datefiled;
            $reqname = $reqstat->name;
            $reqmail = $reqstat->email;
            $reqbr = $reqstat->br_name;
            $reqleavename = $reqstat->leave_name;
            $reqdate = $reqstat->reqdate;
         }


         $leave_type = trim($reqleavename);
         $date_filed = $dateFiled;
         $control_no = $_POST['controlid'];
         $lceff_date = $reqdate;
         $reqstat   = trim($reStat);
         $branch     = strtoupper($reqbr);
         $reason    = '<strong>' . $apprem . '</strong>';

         $mailer = new Mailer();
         $mailer_from = $mailer->get_sender($employee['emp_no']);
         $table = $mailer->get_table($control_no);
         $reqmail = $mailer->get_recipient_approve($control_no, $table);
         $mailer_send = $mailer->mailformat_approve($reqname, $leave_type, $date_filed, $control_no, $branch, $lceff_date, $reason, $mailer_from, $reqmail, $reqstat);
      }
      if ($_POST['action'] == "deletereq") {
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         $contNoSubHeadOk = substr($_POST['controlid'], 0, 2);
         $employee['emp_no'] = "9900628";
         switch (true) {
            case ($contNoSubHeadCancel == 'CS' || $contNoSubHeadCancel == 'CW' || $contNoSubHeadCancel == 'CD' || $contNoSubHeadCancel == 'LB' || $contNoSubHeadCancel == 'CB'):
               $toTableHeadCancel = 'emp_request_master';
               $Name_j = ",j.lrs_desc as leave_name,case when a.date_from = a.date_to then ltrim(rtrim(convert(char(12),a.date_from))) else ltrim(rtrim(convert(char(12),a.date_from)))+ltrim(rtrim(convert(char(12),a.date_to))) end as reqdate ";
               $l_Join = "left join ref_lrs_type j on a.lrs_type = j.lrs_type ";
               break;

            case ($contNoSubHeadCancel == 'LV'):
               $toTableHeadCancel = 'leave_trans';
               $Name_j = ",j.leave_name as leave_name,case when a.date_fto = a.date_ffrom then ltrim(rtrim(convert(char(12),a.date_fto))) else ltrim(rtrim(convert(char(12),a.date_ffrom)))+ltrim(rtrim(convert(char(12),a.date_fto))) end as reqdate ";
               $l_Join = "left join ref_leave_code j on a.leave_code = j.leave_code  ";
               break;

            case ($contNoSubHeadCancel == 'TR'):
               $toTableHeadCancel = 'tar_file';
               $Name_j = ",'Temporary Attendance Record' as leave_name,ltrim(rtrim(convert(char(12),a.effdate)))+' @ '+convert(char(6),a.timein)+' | '+convert(char(6),a.lunchout)+' | '+convert(char(6),a.lunchin)+' | '+convert(char(6),a.coffeeout)+' | '+convert(char(6),a.coffeein)+' | '+convert(char(6),a.timeout)+' | '+convert(char(6),a.fye_in)+' | '+convert(char(6),a.fye_out)as reqdate ";
               $l_Join = "";
               break;

            case ($contNoSubHeadCancel == 'OT'):
               $toTableHeadCancel = 'ot_file';
               $Name_j = ",'Overtime @ '+j.ot_name as leave_name,ltrim(rtrim(convert(char(12),a.date)))+' @ '+convert(char(6),a.time_from)+' to '+convert(char(6),a.time_to) as reqdate ";
               $l_Join = "left join ref_ot_code j on a.ot_code = j.ot_code ";
               break;

            case ($contNoSubHeadCancel == 'UN'):
               $toTableHeadCancel = 'undertime_file';
               $Name_j = ",'Undertime ' as leave_name,'Hours rendered: '+ltrim(rtrim(convert(char(12),a.effdate)))+' @ '+convert(char(6),a.timein)+' | '+convert(char(6),a.timeout) as reqdate ";
               $l_Join = "";
               break;

            case ($contNoSubHeadCancel == 'IA'):
               $toTableHeadCancel = 'iar_file';
               $Name_j = ",'Itinerary Approval Report ' as leave_name,ltrim(rtrim(convert(char(12),a.effdate)))+' @ '+convert(char(6),a.timefr)+' | '+convert(char(6),a.timeto) as reqdate ";
               $l_Join = "";
               break;

            case ($contNoSubHeadCancel == 'AD'):
               $toTableHeadCancel = 'rsr_aday_only_file';
               $Name_j = ",'RSR A DAY ONLY' as leave_name,ltrim(rtrim(convert(char(12),a.effective_date_fr)))+' @ '+convert(char(6),a.effective_date_to)+' | '+ convert(char(6),a.t_in_r)+ ' | '+convert(char(6),a.l_out_r)+' | '+convert(char(6),a.l_in_r)+' | '+convert(char(6),a.c_out_r)+' | '+convert(char(6),a.c_in_r)+' | '+convert(char(6),a.t_out_r) as reqdate ";
               $l_Join = "";
               break;

            default:
               $response['error'] = true;
               $response['status'] = 'error';
               $response['message'] = "System ERROR! (unrecognize control number)";
               echo json_encode($response);
               die();
         }

         //balik value if cancel
         $tableCancel = array("emp_request_master", "undertime_file");
         if (in_array(trim($toTableHeadCancel), $tableCancel)) {
            $qryDel = "select * from " . $toTableHeadCancel . " where leavestatusid in ('1','8') and isapproved = '0' and controlno = '" . $_POST['controlid'] . "'";
            $resultDel = sqlsrv_query($qryDel);

            while ($offSetRows = sqlsrv_fetch_object($resultDel)) {
               $isOffset = $offSetRows->isoffset;
            }

            if ($isOffset == 1) {
               sqlsrv_query("update ot_file set ut_cntno = null,used_un_hrs = 0,remain_hrs = no_of_hrs,isused = 0 where ut_cntno = '" . $_POST['controlid'] . "'
								 update " . $toTableHeadCancel . " set approved_date = getdate() ,ot_cntno = 'cancelled',used_ot_hrs = 0 where controlno = '" . $_POST['controlid'] . "'");
            }
         }


         //Set dis approved into upproved if request is not a authorized leave
         if ($contNoSubHeadCancel == 'CS') {
            //update leave trans on selected Filed user leave under head
            $queryUpd = "update " . $toTableHeadCancel . " set lrs_type = 'LOAU',leavestatusid = '2',approved_by = '" . $employee['logname'] . "',remarks = '" . trim($lCancelReq) . "',approved_date = getdate() where controlno = '" . $_POST['controlid'] . "'";
            $resultUpd = sqlsrv_query($queryUpd);

            $response['error'] = false;
            $response['status'] = 'success';
            $response['message'] = 'Request set as Unauthorized!';


            $qryRequeststat = "select b.leavestatus,a.encoded_date as datefiled,ltrim(rtrim(c.lastname))+', '+ltrim(rtrim(c.firstname))+' '+upper(substring(ltrim(rtrim(c.middlename)),1,1))+'.' as name,
				h.br_name,i.email" . $Name_j . "
				from " . $toTableHeadCancel . " a
				left join  ref_leavestat b on a.leavestatusid = b.leavestatusid
				left join ref_emp_mast c on a.emp_no = c.emp_no
				left join ref_emp_trans d on d.emp_no = c.emp_no
				left join ref_position e on d.br_code = e.br_code and d.div_code = e.div_code
				and d.rank_code = e.rank_code and d.dept_code = e.dept_code and d.post_code = e.post_code
				left join hris_mainLogIn f on c.emp_no =  f.temp_pass
				left join ref_department g on g.br_code = d.br_code and g.div_code = d.div_code and g.dept_code = d.dept_code
				left join ref_branch h on g.br_code = h.br_code
				left join eversql.ehelpdesk.dbo.employee i on i.emp_no = c.emp_no and i.is_active = 1
				" . $l_Join . "
				where a.controlno = '" . $_POST['controlid'] . "' ";

            $resultRequeststat = sqlsrv_query($qryRequeststat);
            while ($reqstat = sqlsrv_fetch_object($resultRequeststat)) {
               $reStat = $reqstat->leavestatus;
               $dateFiled = $reqstat->datefiled;
               $reqname = $reqstat->name;
               $reqmail = $reqstat->email;
               $reqbr = $reqstat->br_name;
               $reqleavename = $reqstat->leave_name;
               $reqdate = $reqstat->reqdate;
            }

            $leave_type = trim($reqleavename);
            $date_filed = $dateFiled;
            $control_no = $_POST['controlid'];
            $lceff_date = $reqdate;
            $reqstat   = trim($reStat);
            $branch     = strtoupper($reqbr);
            $reason    = '<strong>' . trim($lCancelReq) . '</strong>';


            //remove this section (this is for auto sending of mail)
            $mailer = new Mailer();
            $mailer_from = $mailer->get_sender($employee['emp_no']);
            $table = $mailer->get_table($control_no);
            $reqmail = $mailer->get_recipient_approve($control_no, $table);
            $mailer_send = $mailer->mailformat_approve($reqname, $leave_type, $date_filed, $control_no, $branch, $lceff_date, $reason, $mailer_from, $reqmail, $reqstat);

            echo json_encode($response);
            die();
         } else {
            //update leave trans on selected Filed user leave under head
            $queryUpd = "update " . $toTableHeadCancel . " set leavestatusid = '5',approved_by = '" . $employee['logname'] . "',remarks = '" . trim($lCancelReq) . "',approved_date = getdate() where controlno = '" . $_POST['controlid'] . "'";
            $resultUpd = sqlsrv_query($queryUpd);

            $response['error'] = false;
            $response['status'] = 'success';
            $response['message'] = 'Request Canceled!';


            $qryRequeststat = "select b.leavestatus,a.encoded_date as datefiled,ltrim(rtrim(c.lastname))+', '+ltrim(rtrim(c.firstname))+' '+upper(substring(ltrim(rtrim(c.middlename)),1,1))+'.' as name,h. br_name,i.email" . $Name_j . "
               from " . $toTableHeadCancel . " a
               left join  ref_leavestat b on a.leavestatusid = b.leavestatusid
               left join ref_emp_mast c on a.emp_no = c.emp_no
               left join ref_emp_trans d on d.emp_no = c.emp_no
               left join ref_position e on d.br_code = e.br_code and d.div_code = e.div_code
               and d.rank_code = e.rank_code and d.dept_code = e.dept_code and d.post_code = e.post_code
               left join hris_mainLogIn f on c.emp_no =  f.temp_pass
               left join ref_department g on g.br_code = d.br_code and g.div_code = d.div_code and g.dept_code = d.dept_code
               left join ref_branch h on g.br_code = h.br_code
               left join eversql.ehelpdesk.dbo.employee i on i.emp_no = c.emp_no and i.is_active = 1
               " . $l_Join . "
               where a.controlno = '" . $_POST['controlid'] . "' ";

            $resultRequeststat = sqlsrv_query($qryRequeststat);
            while ($reqstat = sqlsrv_fetch_object($resultRequeststat)) {
               $reStat = $reqstat->leavestatus;
               $dateFiled = $reqstat->datefiled;
               $reqname = $reqstat->name;
               $reqmail = $reqstat->email;
               $reqbr = $reqstat->br_name;
               $reqleavename = $reqstat->leave_name;
               $reqdate = $reqstat->reqdate;
            }


            $leave_type = trim($reqleavename);
            $date_filed = $dateFiled;
            $control_no = $_POST['controlid'];
            $lceff_date = $reqdate;
            $reqstat   = trim($reStat);
            $branch     = strtoupper($reqbr);
            $reason    = '<strong>' . trim($lCancelReq) . '</strong>';

            $mailer = new Mailer();
            $mailer_from = $mailer->get_sender($employee['emp_no']);
            $table = $mailer->get_table($control_no);
            $reqmail = $mailer->get_recipient_approve($control_no, $table);
            $mailer_send = $mailer->mailformat_approve($reqname, $leave_type, $date_filed, $control_no, $branch, $lceff_date, $reason, $mailer_from, $reqmail, $reqstat);
         }
      }
   } else {
      $response['error'] = true;
      $response['status'] = 503;
      $response['message'] = "Token unrecognized.";
   }
} else {
   $response['error'] = true;
   $response['status'] = 404;
   $response['message'] = "Page Not Found.";
}



function extractEmployee($conn, $MAIN_TOKEN)
{
   $Employee = new Standard("");
   $_query1 = "select rtrim(ltrim(a.emp_no)) as emp_no,g.br_name,c.post_name as position, rtrim(ltrim(d.deptname)) as department,ltrim(rtrim(a.firstname))+'.'+ltrim(rtrim(a.lastname)) as log_name, ltrim(rtrim(a.lastname))+', '+ltrim(rtrim(a.firstname))+' '+substring(ltrim(rtrim(middlename)), 1, 1)+'.' as name, ltrim(rtrim(g.id_prefix)) as id_prefix from ref_emp_mast a left join ref_emp_trans b on a.emp_no = b.emp_no left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code and b.dept_code = c.dept_code and b.post_code = c.post_code left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code left join hris_mainLogIn e on b.emp_no in (e.user_name,e.temp_pass) left join ref_emp_stat f on f.emp_stat = b.emp_stat and f.br_prefix = b.br_code left join ref_branch g on d.br_code = g.br_code where ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000') and e.log_key = 1 and token_id like rtrim(ltrim('" .  $MAIN_TOKEN . "'))";
   $stmt1 = sqlsrv_query($conn, $_query1);
   if (sqlsrv_fetch($stmt1)) {
      $empData = $Employee->bindMetaData($stmt1);
      $data['emp_no'] = $empData['emp_no'];
      $data['emp_name'] = $empData['name'];
      $data['logname'] = $empData['log_name'];
      $data['department'] = $empData['department'];
      $data['br_name'] = $empData['br_name'];
      $data['position'] = $empData['position'];
      return $data;
   } else {
      return false;
   }
}

function approverAutoInsert($conn, $emp_no)
{
   $query_approver = "Execute approver_auto_insert '" . $emp_no . "'," . $emp_no . "";
   $stmt_approver = sqlsrv_query($conn, $query_approver);
}

echo json_encode($response);
