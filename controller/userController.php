<?php

require('../backend/standard.php');
require('../backend/dbconn.php');
$response = [];
if ($_POST['action'] == "login") {
   $params['user_name'] = $_POST['data']['email'];
   try {
      $newCls = new Standard("");
      $encrypted = $newCls->encryptpass($_POST['data']);
      $params['user_pass'] = $encrypted;
      $newCls = new Standard("hris_mainLogIn");
      $user = $newCls->selectData($params);
      if (!$user) {
         $response['status'] = 'Method Not Allowed!';
         $response['code'] = 405;
         $response['data'] = false;
      } else {
         $token = $user['user_name'] . date('Y-m-d');
         $token  = md5($token);
         $sets['log_key'] = '1';
         $sets['log_date'] = 'getdate()';
         $sets['token_id'] = $token;
         $params2['user_name'] =  $user['user_name'];
         // var_dump($token);

         //-----------FOR CLARIFICATION--------------------------
         $newCls = new Standard("hris_mainlogin");
         $HRIS_STATUS = $newCls->updateData($sets, $params2);
         // ----------------------------------------------- ------
         $response['token'] = $token;
         $response['data'] = true;
         $response['status'] = 'Success';
         $response['code'] = 200;
      }
   } catch (\Throwable $th) {
      $response['status'] = 'Page Not Found!';
      $response['code'] = 404;
      $response['data'] = false;
   }
   // die();
}
if ($_POST['action'] == 'postedtransactions') {
   try {
      $newCls = new Standard("");
      $query = "select distinct a.emp_no,vl_count,sl_count,ml_count,pl_count,hv_count,hs_count from leave_trans a left join (select count(leave_code) as vl_count,emp_no from leave_trans where rtrim(ltrim(leave_code)) = 'VL' and leavestatusid not in(5,6) and substring(convert(char(10),date_Ffrom,112),1,4) = substring(convert(char(10),getdate(),112),1,4) -1 group by emp_no,leave_code) b on a.emp_no = b.emp_no left join (select count(leave_code) as sl_count,emp_no from leave_trans where rtrim(ltrim(leave_code)) = 'SL' and leavestatusid not in(5,6) and substring(convert(char(10),date_Ffrom,112),1,4) = substring(convert(char(10),getdate(),112),1,4) -1 group by emp_no,leave_code) c on a.emp_no = c.emp_no left join (select count(leave_code) as ml_count,emp_no from leave_trans where rtrim(ltrim(leave_code)) = 'ML' and leavestatusid not in(5,6) and substring(convert(char(10),date_Ffrom,112),1,4) = substring(convert(char(10),getdate(),112),1,4) -1 group by emp_no,leave_code) d on a.emp_no = d.emp_no left join (select count(leave_code) as pl_count,emp_no from leave_trans where rtrim(ltrim(leave_code)) = 'PL' and leavestatusid not in(5,6) and substring(convert(char(10),date_Ffrom,112),1,4) = substring(convert(char(10),getdate(),112),1,4) -1 group by emp_no,leave_code) e on a.emp_no = e.emp_no left join (select count(leave_code) as hv_count,emp_no from leave_trans where rtrim(ltrim(leave_code)) = 'HV' and leavestatusid not in(5,6) and substring(convert(char(10),date_Ffrom,112),1,4) = substring(convert(char(10),getdate(),112),1,4) -1 group by emp_no,leave_code) f on a.emp_no = f.emp_no left join (select count(leave_code) as hs_count,emp_no from leave_trans where rtrim(ltrim(leave_code)) = 'HS' and leavestatusid not in(5,6) and substring(convert(char(10),date_Ffrom,112),1,4) = substring(convert(char(10),getdate(),112),1,4) -1 group by emp_no,leave_code) g on a.emp_no = g.emp_no where a.emp_no = '" . $_POST['empno'] . "'";
      $stmt = sqlsrv_query($conn, $query);
      if (sqlsrv_fetch($stmt)) {
         $data = $newCls->bindMetaData($stmt);
         $response['vld'] =  $data['vl_count'];
         $response['vlh'] =  $data['hv_count'];
         $response['sld'] = $data['sl_count'];
         $response['slh'] = $data[' hs_count'];
         $response['ml'] = $data[' ml_count'];
         $response['pl'] = $data[' pl_count'];
      } else {
         $response['vld'] = 0;
         $response['vlh'] = 0;
         $response['sld'] = 0;
         $response['slh'] = 0;
         $response['ml'] = 0;
         $response['pl'] = 0;
      }
   } catch (\Throwable $th) {
      //throw $th;
   }
}
if ($_POST['action'] == "getattendance") {

   try {
      $data = [];
      $Attendance = new Standard("");
      $query = "SELECT * from pistime where emp_no = '" . $_POST['emp_no'] . "' order by date DESC";
      $stmt = sqlsrv_query($conn, $query);
      while (sqlsrv_fetch($stmt)) {
         $data_raw = $Attendance->bindMetaData($stmt);
         foreach ($data_raw as $key => $value) {
            if ($key == 'in1') {
               $data_raw[$key] = converttime($value);
            }
            if ($key == 'in2') {
               $data_raw[$key] = converttime($value);
            }
            if ($key == 'in3') {
               $data_raw[$key] = converttime($value);
            }
            if ($key == 'in4') {
               $data_raw[$key] = converttime($value);
            }
            if ($key == 'out1') {
               $data_raw[$key] = converttime($value);
            }
            if ($key == 'out2') {
               $data_raw[$key] = converttime($value);
            }
            if ($key ==  'out3') {
               $data_raw[$key] = converttime($value);
            }
            if ($key ==  'out4') {
               $data_raw[$key] = converttime($value);
            }
         }
         array_push($data,  $data_raw);
      }
      $response = $data;
   } catch (\Throwable $th) {
      var_dump($th);
   }
}
if ($_POST['action'] == "getrequests") {
   $data = [];
   $request = new Standard("");
   $_query = "SELECT e.controlno,
                  a.emp_no,
                  Rtrim(Ltrim(a.lastname)) + ', '
                  + Rtrim(Ltrim(a.firstname)) + ' '
                  + Substring(a.middlename, 1, 1) + '.'  AS NAME,
                  CASE
                  WHEN Ltrim(Rtrim(e.leave_code)) IN( 'HV', 'HS' ) THEN
                  Ltrim(Rtrim(h.leave_name)) + '<br/>'
                  + Ltrim(Rtrim(CONVERT(CHAR(6), e.no_of_hrs)))
                  + ' hours only'
                  ELSE Ltrim(Rtrim(h.leave_name))
                  END                                    AS leave_name,
                  CONVERT(CHAR(12), date_ffrom, 101)     AS date_Ffrom,
                  CONVERT(CHAR(12), date_fto, 101)       AS date_Fto,
                  e.reason,
                  CONVERT(CHAR(12), e.encoded_date, 101) AS encoded_date,
                  e.approved_by,
                  e.approved_date,
                  e.remarks,
                  g.leavestatus,
                  NULL                                   AS time_from,
                  NULL                                   AS time_to,
                  encoded_date                           AS sortdate
               FROM   ref_emp_mast a
                  LEFT JOIN ref_emp_trans b
                        ON a.emp_no = b.emp_no
                  LEFT JOIN leave_trans e
                        ON a.emp_no = e.emp_no
                  LEFT JOIN ref_leavestat g
                        ON e.leavestatusid = g.leavestatusid
                  LEFT JOIN ref_leave_code h
                        ON e.leave_code = h.leave_code
               WHERE  e.emp_no = '" . $_POST['emp_no'] . "'
               UNION
               SELECT x.controlno,
                  x.emp_no,
                  Rtrim(Ltrim(a1.lastname)) + ', '
                  + Rtrim(Ltrim(a1.firstname)) + ' '
                  + Substring(a1.middlename, 1, 1) + '.',
                  b1.lrs_desc,
                  CONVERT(CHAR(12), date_from, 101),
                  CONVERT(CHAR(12), date_to, 101),
                  CASE
                  WHEN Ltrim(Rtrim(x.ot_cntno)) != '' THEN
                  Ltrim(Rtrim(x.reason))
                  + '</br>[Offsetting '
                  + Ltrim(Rtrim(x.ot_cntno)) + ']'
                  ELSE Ltrim(Rtrim(x.reason))
                  END,
                  CONVERT(CHAR(12), x.encoded_date, 101),
                  x.approved_by,
                  x.approved_date,
                  x.remarks,
                  c1.leavestatus,
                  NULL,
                  NULL,
                  encoded_date AS sortdate
               FROM   emp_request_master x
                  LEFT JOIN ref_emp_mast a1
                        ON x.emp_no = a1.emp_no
                  LEFT JOIN ref_lrs_type b1
                        ON x.lrs_type = b1.lrs_type
                  LEFT JOIN ref_leavestat c1
                        ON c1.leavestatusid = x.leavestatusid
               WHERE  x.emp_no = '" . $_POST['emp_no'] . "'
               UNION
               SELECT y.controlno,
                  y.emp_no,
                  Rtrim(Ltrim(a2.lastname)) + ', '
                  + Rtrim(Ltrim(a2.firstname)) + ' '
                  + Substring(a2.middlename, 1, 1) + '.',
                  'OT @ ' + b2.ot_name,
                  CONVERT(CHAR(12), y.date, 101),
                  CASE
                  WHEN time_from > time_to THEN CONVERT(CHAR(12), date + 1, 101)
                  ELSE CONVERT(CHAR(12), date, 101)
                  END,
                  y.reason,
                  CONVERT(CHAR(12), encoded_date, 101),
                  y.approved_by,
                  y.approved_date,
                  y.remarks,
                  c2.leavestatus,
                  CASE
                  WHEN time_from BETWEEN 0 AND 11.99 THEN
                  Rtrim(Ltrim(CONVERT(CHAR(6), time_from)))
                  + ' am '
                  ELSE
                     CASE
                        WHEN time_from BETWEEN 12 AND 12.99 THEN
                        Rtrim(Ltrim(CONVERT(CHAR(6), time_from)))
                        + ' pm '
                        ELSE
                        CASE
                           WHEN time_from BETWEEN 24.00 AND 24.99 THEN
                           Rtrim(Ltrim(CONVERT(CHAR(6), time_from-12)))
                           + ' pm '
                           ELSE Rtrim(Ltrim(CONVERT(CHAR(6), time_from-12)))
                                 + ' pm '
                        END
                     END
                  END,
                  CASE
                  WHEN time_to BETWEEN 0 AND 11.99 THEN
                  Rtrim(Ltrim(CONVERT(CHAR(6), time_to)))
                  + ' am '
                  ELSE
                     CASE
                        WHEN time_to BETWEEN 12 AND 12.99 THEN
                        Rtrim(Ltrim(CONVERT(CHAR(6), time_to)))
                        + ' pm '
                        ELSE
                        CASE
                           WHEN time_to BETWEEN 24.00 AND 24.99 THEN
                           Rtrim(Ltrim(CONVERT(CHAR(6), time_to-12)))
                           + ' am '
                           ELSE Rtrim(Ltrim(CONVERT(CHAR(6), time_to-12)))
                                 + ' pm '
                        END
                     END
                  END,
                  encoded_date AS sortdate
               FROM   ot_file y
                  LEFT JOIN ref_emp_mast a2
                        ON y.emp_no = a2.emp_no
                  LEFT JOIN ref_ot_code b2
                        ON y.ot_code = b2.ot_code
                  LEFT JOIN ref_leavestat c2
                        ON y.leavestatusid = c2.leavestatusid
               WHERE  y.emp_no = '" . $_POST['emp_no'] . "'
               UNION
               SELECT z.controlno,
                  z.emp_no,
                  Rtrim(Ltrim(a3.lastname)) + ', '
                  + Rtrim(Ltrim(a3.firstname)) + ' '
                  + Substring(a3.middlename, 1, 1) + '.',
                  'Undertime /</br>Early Leaving',
                  CONVERT(CHAR(12), effdate, 101),
                  CONVERT(CHAR(12), effdate, 101),
                  CASE
                  WHEN Ltrim(Rtrim(z.ot_cntno)) != '' THEN
                  Ltrim(Rtrim(z.reason))
                  + '</br>[Offsetting '
                  + Ltrim(Rtrim(z.ot_cntno)) + ']'
                  ELSE Ltrim(Rtrim(z.reason))
                  END,
                  CONVERT(CHAR(12), encoded_date, 101),
                  z.approved_by,
                  z.approved_date,
                  z.remarks,
                  b3.leavestatus,
                  CASE
                  WHEN timein BETWEEN 0 AND 11.99 THEN
                  Rtrim(Ltrim(CONVERT(CHAR(6), timein)))
                  + ' am '
                  ELSE
                     CASE
                        WHEN timein BETWEEN 12 AND 12.99 THEN
                        Rtrim(Ltrim(CONVERT(CHAR(6), timein)))
                        + ' pm '
                        ELSE Rtrim(Ltrim(CONVERT(CHAR(6), timein-12)))
                           + ' pm '
                     END
                  END,
                  CASE
                  WHEN timeout BETWEEN 0 AND 11.99 THEN
                  Rtrim(Ltrim(CONVERT(CHAR(6), timeout)))
                  + ' am '
                  ELSE
                     CASE
                        WHEN timeout BETWEEN 12 AND 12.99 THEN
                        Rtrim(Ltrim(CONVERT(CHAR(6), timeout)))
                        + ' pm '
                        ELSE Rtrim(Ltrim(CONVERT(CHAR(6), timeout-12)))
                           + ' pm '
                     END
                  END,
                  encoded_date AS sortdate
               FROM   undertime_file z
                  LEFT JOIN ref_emp_mast a3
                        ON z.emp_no = a3.emp_no
                  LEFT JOIN ref_leavestat b3
                        ON z.leavestatusid = b3.leavestatusid
               WHERE  z.emp_no = '" . $_POST['emp_no'] . "'
               UNION
               SELECT a.controlno,
                  a.emp_no,
                  Rtrim(Ltrim(a1.lastname)) + ', '
                  + Rtrim(Ltrim(a1.firstname)) + ' '
                  + Substring(a1.middlename, 1, 1) + '.',
                  'Itinerary Approval Request',
                  CONVERT(CHAR(12), a.effdate, 101),
                  CONVERT(CHAR(12), a.effdateto, 101),
                  CASE
                  WHEN Ltrim(Rtrim(a.ot_cntno)) != '' THEN
                  Ltrim(Rtrim(a.remark))
                  + '</br>[Offsetting '
                  + Ltrim(Rtrim(a.ot_cntno)) + ']'
                  ELSE Ltrim(Rtrim(a.remark))
                  END,
                  CONVERT(CHAR(12), a.encoded_date, 101),
                  approved_by,
                  approved_date,
                  remarks,
                  a2.leavestatus,
                  '',
                  CASE WHEN timefr BETWEEN 0 AND 11.99 THEN Rtrim(Ltrim(CONVERT(CHAR(6),
                  timefr)))
                  + ' am ' ELSE CASE WHEN timefr BETWEEN 12 AND 12.99 THEN Rtrim(Ltrim(
                  CONVERT(
                  CHAR(6), timefr)))+ ' pm ' ELSE Rtrim(Ltrim(CONVERT(CHAR(6), timefr-12)))
                  +
                  ' pm ' END END + ' to ' + CASE WHEN timeto BETWEEN 0 AND 11.99 THEN Rtrim
                  (Ltrim(
                  CONVERT(CHAR(6), timeto)))+ ' am ' ELSE CASE WHEN timeto BETWEEN 12 AND
                  12.99
                  THEN Rtrim(Ltrim(CONVERT(CHAR(6), timeto)))+ ' pm ' ELSE Rtrim(Ltrim(
                  CONVERT(
                  CHAR(6), timeto-12)))+ ' pm ' END END,
                  encoded_date AS sortdate
               FROM   iar_file a
                  LEFT JOIN ref_emp_mast a1
                        ON a.emp_no = a1.emp_no
                  LEFT JOIN ref_leavestat a2
                        ON a.leavestatusid = a2.leavestatusid
               WHERE  a.emp_no = '" . $_POST['emp_no'] . "'
               UNION
               SELECT b.controlno,
                  b.emp_no,
                  Rtrim(Ltrim(a1.lastname)) + ', '
                  + Rtrim(Ltrim(a1.firstname)) + ' '
                  + Substring(a1.middlename, 1, 1) + '.',
                  'Temporary Attendance Record',
                  CONVERT(CHAR(12), b.effdate, 101),
                  Ltrim(Rtrim(CONVERT(CHAR(6), b.timein) + '>'
                              + CONVERT(CHAR(6), b.lunchout) + '>'
                              + CONVERT(CHAR(6), b.lunchin) + '>'
                              + CONVERT(CHAR(6), b.coffeeout) + '>'
                              + CONVERT(CHAR(6), b.coffeein) + '>'
                              + CONVERT(CHAR(6), b.timeout) + '>'
                              + CONVERT(CHAR(6), b.fye_in) + '>'
                              + CONVERT(CHAR(6), b.fye_out))),
                  b.reason,
                  CONVERT(CHAR(12), b.encoded_date, 101),
                  approved_by,
                  approved_date,
                  remarks,
                  a2.leavestatus,
                  NULL,
                  NULL,
                  encoded_date AS sortdate
               FROM   tar_file b
                  LEFT JOIN ref_emp_mast a1
                        ON b.emp_no = a1.emp_no
                  LEFT JOIN ref_leavestat a2
                        ON b.leavestatusid = a2.leavestatusid
               WHERE  b.emp_no = '" . $_POST['emp_no'] . "'
               UNION
               SELECT
                  x12.controlno,
                  x12.emp_no,
                  Rtrim(Ltrim(a1.lastname)) + ', '
                  + Rtrim(Ltrim(a1.firstname)) + ' '
                  + Substring(a1.middlename, 1, 1) + '.',
                  'RSR ADAY ONLY' AS lrs_desc,
                  CONVERT(CHAR(12), effective_date_fr, 101),
                  CONVERT(CHAR(12), effective_date_to, 101)
                  + '<br>'
                  +
                  Ltrim(Rtrim(CONVERT(CHAR(6), x12.t_in_ch)+'>'+CONVERT(CHAR(6), x12.l_out_ch)+'>'+CONVERT(CHAR(6),
                  x12.l_in_ch)+'>'+CONVERT(CHAR(6), x12.c_out_ch)+'>'+CONVERT(CHAR(6), x12.c_in_ch)+'>'+CONVERT(CHAR(6), x12.t_out_ch)+'>'+CONVERT(CHAR(6), x12.ex_in_ch)+'>'+CONVERT(CHAR(6), x12.ex_out_ch))),
                  Ltrim(Rtrim(x12.reason)),
                  CONVERT(CHAR(12), x12.encoded_date, 101),
                  x12.approved_by,
                  x12.approved_date,
                  x12.remarks,
                  c1.leavestatus,
                  NULL,
                  NULL,
                  encoded_date    AS sortdate
               FROM   rsr_aday_only_file x12
                  LEFT JOIN ref_emp_mast a1
                        ON x12.emp_no = a1.emp_no
                  LEFT JOIN ref_leavestat c1
                        ON c1.leavestatusid = x12.leavestatusid
               WHERE  x12.emp_no = '" . $_POST['emp_no'] . "'
   ORDER  BY sortdate DESC ";;
   $stmt = sqlsrv_query($conn, $_query);
   while (sqlsrv_fetch($stmt)) {
      $data_raw = $request->bindMetaData($stmt);
      array_push($data, $data_raw);
   }
   $response = $data;
}
if ($_POST['action'] == "getLeaves") {
   $MAIN_TOKEN = trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTORIZATION']));
   $employee = extractEmployee($conn, $MAIN_TOKEN);
   $queryLeave = "execute get_vl '" . $employee['emp_no'] . "','VL'";
   $stmtLeave = sqlsrv_query($conn, $queryLeave);

   if (sqlsrv_fetch($stmtLeave)) {
      $clsLeave = new Standard('');
      $response['vl'] = $clsLeave->bindMetaData($stmtLeave);
   }

   $queryLeave = "execute get_sl '" . $employee['emp_no'] . "','SL'";
   $stmtLeave = sqlsrv_query($conn, $queryLeave);
   if (sqlsrv_fetch($stmtLeave)) {
      $clsLeave = new Standard('');
      $response['sl'] = $clsLeave->bindMetaData($stmtLeave);
   }
}
if ($_POST['action'] == 'getapprover') {
   $MAIN_TOKEN = trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTORIZATION']));
   $employee = extractEmployee($conn, $MAIN_TOKEN);
   $queryHeadMan = "select approver_name as name,approver_position as position from ref_hris_approver where emp_no = '" . $employee['emp_no'] . "' and is_approver = 1 ";

   $stmt = sqlsrv_query($conn, $queryHeadMan);
   if (sqlsrv_fetch($stmt)) {
      $clsApprover = new Standard('');
      $response['Approver'] = $clsApprover->bindMetaData($stmt);
   }
}

function extractEmployee($conn, $MAIN_TOKEN)
{
   $Employee = new Standard("");
   $_query1 = "select rtrim(ltrim(a.emp_no)) as emp_no,g.br_name,c.rank_code, c.post_name as position, rtrim(ltrim(d.deptname)) as department,ltrim(rtrim(a.firstname))+'.'+ltrim(rtrim(a.lastname)) as log_name, ltrim(rtrim(a.lastname))+', '+ltrim(rtrim(a.firstname))+' '+substring(ltrim(rtrim(middlename)), 1, 1)+'.' as name, ltrim(rtrim(g.id_prefix)) as id_prefix from ref_emp_mast a left join ref_emp_trans b on a.emp_no = b.emp_no left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code and b.dept_code = c.dept_code and b.post_code = c.post_code left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code left join hris_mainLogIn e on b.emp_no in (e.user_name,e.temp_pass) left join ref_emp_stat f on f.emp_stat = b.emp_stat and f.br_prefix = b.br_code left join ref_branch g on d.br_code = g.br_code where ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000') and e.log_key = 1 and token_id like rtrim(ltrim('" .  $MAIN_TOKEN . "'))";
   $stmt1 = sqlsrv_query($conn, $_query1);
   if (sqlsrv_fetch($stmt1)) {
      $empData = $Employee->bindMetaData($stmt1);
      $data['emp_no'] = $empData['emp_no'];
      $data['emp_name'] = $empData['name'];
      $data['logname'] = $empData['log_name'];
      $data['department'] = $empData['department'];
      $data['br_name'] = $empData['br_name'];
      $data['position'] = $empData['position'];
      $data['rank_code'] = $empData['rank_code'];
      return $data;
   } else {
      return false;
   }
}

function converttime($rawTime)
{
   if ($rawTime[0] != '.') {
      $time = strtotime(str_replace('.', ':', $rawTime));
      return date('h:i A', $time);
   } else {
      return '-';
   }
}

echo json_encode($response);
