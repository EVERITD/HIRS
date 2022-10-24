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
            $params['timein'] = str_replace(":", ".", $data['timein']);
            $params['timeout'] = str_replace(":", ".", $data['timeout']);
            $params['lunchin'] = str_replace(":", ".", $data['lbIn']);
            $params['lunchout'] = str_replace(":", ".", $data['lbOut']);
            $params['coffeein'] = str_replace(":", ".", $data['cbIn']);
            $params['coffeeout'] = str_replace(":", ".", $data['cbOut']);
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
               var_dump($employee);
               die();
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
                  $mailer_from = $mailer->get_sender($employee['empno']);
                  $mailer_to = $mailer->get_recipient($employee['empno']);
                  $mailer_send = $mailer->mailformat_request($employee['empno'], $employee['name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from, $mailer_to);

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
                  $mailer_from = $mailer->get_sender($employee['empno']);
                  $mailer_to = $mailer->get_recipient($employee['empno']);
                  $mailer_send = $mailer->mailformat_request($employee['empno'], $employee['name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from, $mailer_to);

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
                  $mailer_from = $mailer->get_sender($employee['empno']);
                  $mailer_to = $mailer->get_recipient($employee['empno']);
                  $mailer_send = $mailer->mailformat_request($employee['empno'], $employee['name'], $leave_type, $date_filed, $controlno, $employee['department'], $employee['br_name'], $params['effective_date'], $params['remarks'], $mailer_from, $mailer_to);
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
   $_query1 = "select rtrim(ltrim(a.emp_no)) as emp_no,g.br_name,rtrim(ltrim(d.deptname)) as department,ltrim(rtrim(a.firstname))+'.'+ltrim(rtrim(a.lastname)) as log_name, ltrim(rtrim(a.lastname))+', '+ltrim(rtrim(a.firstname))+' '+substring(ltrim(rtrim(middlename)), 1, 1)+'.' as name, ltrim(rtrim(g.id_prefix)) as id_prefix from ref_emp_mast a left join ref_emp_trans b on a.emp_no = b.emp_no left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code and b.dept_code = c.dept_code and b.post_code = c.post_code left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code left join hris_mainLogIn e on b.emp_no in (e.user_name,e.temp_pass) left join ref_emp_stat f on f.emp_stat = b.emp_stat and f.br_prefix = b.br_code left join ref_branch g on d.br_code = g.br_code where ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000') and e.log_key = 1 and token_id like rtrim(ltrim('" .  $MAIN_TOKEN . "'))";
   $stmt1 = sqlsrv_query($conn, $_query1);
   if (sqlsrv_fetch($stmt1)) {
      $empData = $Employee->bindMetaData($stmt1);
      $data['emp_no'] = $empData['emp_no'];
      $data['emp_name'] = $empData['name'];
      $data['logname'] = $empData['log_name'];
      $data['department'] = $empData['department'];
      $data['br_name'] = $empData['br_name'];
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
