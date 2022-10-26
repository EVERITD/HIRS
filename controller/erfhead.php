<?php
require('../backend/standard.php');
require('../backend/dbconn.php');
include "../mailer_class2.php";
$response = [];

if ($_SERVER['HTTP_AUTORIZATION']) {
   $MAIN_TOKEN = trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTORIZATION']));
   if ($MAIN_TOKEN) {
      if ($_POST['action'] == 'FORVALIDATION') {
         $employee = extractEmployee($conn, $MAIN_TOKEN);
         $data['ot'] = getOTreq($conn);
         $data['lv'] = getLV($conn);
         $data['tr'] = getTR($conn);
         $data['ot_br'] = getOTBRreq($conn);
         $data['ot_br'] = getOTBRreq($conn);
         $data['un'] = getUN($conn);
         $data['iar'] = getIAR($conn);
         $xdata = array_merge($data['ot'], $data['lv'], $data['tr'], $data['ot_br'], $data['un'],  $data['iar']);
         try {
            echo json_encode($data['ot']);
         } catch (\Throwable $th) {
            throw $th;
         }

         die();
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

function getOTBRreq($conn)
{

   $query_ot = "
         SELECT TOP 10 e.controlno,
         a.emp_no,
         Rtrim(Ltrim(a.lastname)) + ', '
         + Rtrim(Ltrim(a.firstname)) + ' '
         + Substring(a.middlename, 1, 1) + '.' AS NAME,
         br_name,
         c.br_code,
         c.div_code,
         c.dept_code,
         d.deptname,
         c.post_code,
         c.post_name,
         a.pict_file,
         'OT <' + e.ot_code + '>',
         'Overtime</br>'
         + CONVERT(CHAR(5), time_from) + ' to '
         + CONVERT(CHAR(5), time_to)
         + '</br> Time total: '
         + CONVERT(CHAR(6), no_of_hrs),
         c.rank_code,
         CONVERT(CHAR(12), date, 101)          AS date_Ffrom,
         CONVERT(CHAR(12), date, 101)          AS date_Fto,
         reason,
         e.leavestatusid,
         1.00,
         no_of_hrs,
         encoded_by,
         CONVERT(CHAR(12), encoded_date, 101)  AS encoded_date,
         isapproved,
         g.leavestatus,
         approved_by,
         approved_date,
         e.audit_user,
         e.audit_date,
         e.ispis
      FROM   ref_emp_mast a
         LEFT JOIN ref_emp_trans b
               ON a.emp_no = b.emp_no
         LEFT JOIN ref_position c
               ON b.br_code = c.br_code
                  AND b.div_code = c.div_code
                  AND b.rank_code = c.rank_code
                  AND b.dept_code = c.dept_code
                  AND b.post_code = c.post_code
         LEFT JOIN ref_department d
               ON d.br_code = b.br_code
                  AND d.div_code = b.div_code
                  AND d.dept_code = b.dept_code
         LEFT JOIN ot_file e
               ON a.emp_no = e.emp_no
         LEFT JOIN ref_branch f
               ON c.br_code = f.br_code
         LEFT JOIN ref_leavestat g
               ON e.leavestatusid = g.leavestatusid
      WHERE  ( b.emp_stat IN ( 'regu', 'prob', 'cont', 'ojt' )
         AND b.date_end IS NULL
            OR date_end > Getdate()
            OR date_end = '1900-01-01 00:00:00.000' )
         AND b.br_code LIKE '%'
         AND d.div_code LIKE '%'
         AND d.dept_code LIKE '%'
         AND c.rank_code LIKE '%'
         AND controlno IS NOT NULL
         AND e.leavestatusid = '2'
         AND isapproved = '1'";
   $stmt_ot = sqlsrv_query($conn, $query_ot);
   $clsOT = new Standard('');
   $data = [];
   while (sqlsrv_fetch($stmt_ot)) {
      array_push($data, $clsOT->bindMetaData($stmt_ot));
   }

   return $data;
}
function getOTreq($conn)
{
   $query_ot = "
      SELECT TOP 10 e.controlno,
         a.emp_no,
         Rtrim(Ltrim(a.lastname)) + ', '
         + Rtrim(Ltrim(a.firstname)) + ' '
         + Substring(a.middlename, 1, 1) + '.' AS NAME,
         br_name,
         c.br_code,
         c.div_code,
         c.dept_code,
         c.post_code,
         a.pict_file,
         'OT <' + e.ot_code + '>',
         'Overtime</br>'
         + CONVERT(CHAR(5), time_from) + ' to '
         + CONVERT(CHAR(5), time_to)
         + '</br> Time total: '
         + CONVERT(CHAR(6), no_of_hrs),
         c.rank_code,
         CONVERT(CHAR(12), date, 101)          AS date_Ffrom,
         CONVERT(CHAR(12), date, 101)          AS date_Fto,
         reason,
      
         CONVERT(CHAR(12), encoded_date, 101)  AS encoded_date,
         isapproved,
         g.leavestatus,
         approved_by,
         approved_date,
         e.audit_date,
         e.ispis
      FROM   ref_emp_mast a
         LEFT JOIN ref_emp_trans b
               ON a.emp_no = b.emp_no
         LEFT JOIN ref_position c
               ON b.br_code = c.br_code
                  AND b.div_code = c.div_code
                  AND b.rank_code = c.rank_code
                  AND b.dept_code = c.dept_code
                  AND b.post_code = c.post_code
         LEFT JOIN ref_department d
               ON d.br_code = b.br_code
                  AND d.div_code = b.div_code
                  AND d.dept_code = b.dept_code
         LEFT JOIN ot_file e
               ON a.emp_no = e.emp_no
         LEFT JOIN ref_branch f
               ON c.br_code = f.br_code
         LEFT JOIN ref_leavestat g
               ON e.leavestatusid = g.leavestatusid
      WHERE  ( b.emp_stat IN ( 'regu', 'prob', 'cont', 'ojt' )
         AND b.date_end IS NULL
            OR date_end > Getdate()
            OR date_end = '1900-01-01 00:00:00.000' )
         AND b.br_code LIKE '%'
         AND d.div_code LIKE '%'
         AND d.dept_code LIKE '%'
         AND c.rank_code LIKE '%'
         AND controlno IS NOT NULL
         AND e.leavestatusid = '2'
         AND isapproved = '1'
         ORDER BY encoded_date DESC,NAME ASC";
   $stmt_ot = sqlsrv_query($conn, $query_ot);
   $clsOT = new Standard('');
   $data = [];
   while (sqlsrv_fetch($stmt_ot)) {
      array_push($data, $clsOT->bindMetaData($stmt_ot));
      // var_dump($clsOT->bindMetaData($stmt_ot));
   }

   return $data;
}
function getLV($conn)
{
   $query_ot = "
         SELECT TOP 10 e.controlno,
            a.emp_no,
            Rtrim(Ltrim(a.lastname)) + ', '
            + Rtrim(Ltrim(a.firstname)) + ' '
            + Substring(a.middlename, 1, 1) + '.' AS NAME,
            br_name,
            c.br_code,
            c.div_code,
            c.dept_code,
            d.deptname,
            c.post_code,
            c.post_name,
            a.pict_file,
            e.leave_code,
            h.leave_name,
            c.rank_code,
            CONVERT(CHAR(12), date_ffrom, 101)    AS date_Ffrom,
            CONVERT(CHAR(12), date_fto, 101)      AS date_Fto,
            reason,
            e.leavestatusid,
            no_of_days,
            no_of_hrs,
            encoded_by,
            CONVERT(CHAR(12), encoded_date, 101)  AS encoded_date,
            isapproved,
            g.leavestatus,
            approved_by,
            approved_date,
            e.audit_user,
            e.audit_date,
            e.ispis
      FROM   ref_emp_mast a
            LEFT JOIN ref_emp_trans b
                  ON a.emp_no = b.emp_no
            LEFT JOIN ref_position c
                  ON b.br_code = c.br_code
                     AND b.div_code = c.div_code
                     AND b.rank_code = c.rank_code
                     AND b.dept_code = c.dept_code
                     AND b.post_code = c.post_code
            LEFT JOIN ref_department d
                  ON d.br_code = b.br_code
                     AND d.div_code = b.div_code
                     AND d.dept_code = b.dept_code
            LEFT JOIN leave_trans e
                  ON a.emp_no = e.emp_no
            LEFT JOIN ref_branch f
                  ON c.br_code = f.br_code
            LEFT JOIN ref_leavestat g
                  ON e.leavestatusid = g.leavestatusid
            LEFT JOIN ref_leave_code h
                  ON e.leave_code = h.leave_code
      WHERE  ( b.emp_stat IN ( 'regu', 'prob', 'cont', 'ojt' )
               AND b.date_end IS NULL
               OR date_end > Getdate()
               OR date_end = '1900-01-01 00:00:00.000' )
            AND b.br_code LIKE '%'
            AND d.div_code LIKE '%'
            AND d.dept_code LIKE '%'
            AND c.rank_code LIKE '%'
            AND controlno IS NOT NULL
            AND e.leavestatusid = '2'
            AND isapproved = '1'";
   $stmt_ot = sqlsrv_query($conn, $query_ot);
   $clsOT = new Standard('');
   $data = [];
   while (sqlsrv_fetch($stmt_ot)) {
      array_push($data, $clsOT->bindMetaData($stmt_ot));
   }
   return $data;
}
function getTR($conn)
{

   $query_ot = "
         SELECT TOP 10 e.controlno,
         a.emp_no,
         Rtrim(Ltrim(a.lastname)) + ', '
         + Rtrim(Ltrim(a.firstname)) + ' '
         + Substring(a.middlename, 1, 1) + '.' AS NAME,
         br_name,
         c.br_code,
         c.div_code,
         c.dept_code,
         d.deptname,
         c.post_code,
         c.post_name,
         a.pict_file,
         'TR',
         'Temporary Attendance Record' + '</br> >'
         + CONVERT(CHAR(5), timein) + ' >'
         + CONVERT(CHAR(5), lunchout) + ' >'
         + CONVERT(CHAR(5), lunchin) + ' >'
         + CONVERT(CHAR(5), coffeeout) + ' >'
         + CONVERT(CHAR(5), coffeein) + ' >'
         + CONVERT(CHAR(5), timeout),
         c.rank_code,
         CONVERT(CHAR(12), e.effdate, 101)     AS date_Ffrom,
         CONVERT(CHAR(12), e.effdate, 101)     AS date_Fto,
         reason,
         e.leavestatusid,
         1.00,
         .00,
         encoded_by,
         CONVERT(CHAR(12), encoded_date, 101)  AS encoded_date,
         isapproved,
         g.leavestatus,
         approved_by,
         approved_date,
         e.audit_user,
         e.audit_date,
         e.ispis
      FROM   ref_emp_mast a
         LEFT JOIN ref_emp_trans b
               ON a.emp_no = b.emp_no
         LEFT JOIN ref_position c
               ON b.br_code = c.br_code
                  AND b.div_code = c.div_code
                  AND b.rank_code = c.rank_code
                  AND b.dept_code = c.dept_code
                  AND b.post_code = c.post_code
         LEFT JOIN ref_department d
               ON d.br_code = b.br_code
                  AND d.div_code = b.div_code
                  AND d.dept_code = b.dept_code
         LEFT JOIN tar_file e
               ON a.emp_no = e.emp_no
         LEFT JOIN ref_branch f
               ON c.br_code = f.br_code
         LEFT JOIN ref_leavestat g
               ON e.leavestatusid = g.leavestatusid
      WHERE  ( b.emp_stat IN ( 'regu', 'prob', 'cont', 'ojt' )
         AND b.date_end IS NULL
            OR date_end > Getdate()
            OR date_end = '1900-01-01 00:00:00.000' )
         AND b.br_code LIKE '%'
         AND d.div_code LIKE '%'
         AND d.dept_code LIKE '%'
         AND c.rank_code LIKE '%'
         AND controlno IS NOT NULL
         AND e.leavestatusid = '2'
         AND isapproved = '1'";
   $stmt_ot = sqlsrv_query($conn, $query_ot);
   $clsOT = new Standard('');
   $data = [];
   while (sqlsrv_fetch($stmt_ot)) {
      array_push($data, $clsOT->bindMetaData($stmt_ot));
   }

   return $data;
}
function getIAR($conn)
{

   $query_ot = "SELECT TOP 10 e.controlno,
          a.emp_no,
          Rtrim(Ltrim(a.lastname)) + ', '
          + Rtrim(Ltrim(a.firstname)) + ' '
          + Substring(a.middlename, 1, 1) + '.' AS NAME,
          br_name,
          c.br_code,
          c.div_code,
          c.dept_code,
          d.deptname,
          c.post_code,
          c.post_name,
          a.pict_file,
          'IAR',
          'Itirenary Approval Report</br>from: '
          + CONVERT(CHAR(5), e.timefr) + ' to: '
          + CONVERT(CHAR(5), e.timeto),
          c.rank_code,
          CONVERT(CHAR(12), effdate, 101)       AS date_Ffrom,
          CONVERT(CHAR(12), effdate, 101)       AS date_Fto,
          e.remark,
          e.leavestatusid,
          1.00,
          timeto - timefr,
          encoded_by,
          CONVERT(CHAR(12), encoded_date, 101)  AS encoded_date,
          isapproved,
          g.leavestatus,
          approved_by,
          approved_date,
          e.audit_user,
          e.audit_date,
          e.ispis
   FROM   ref_emp_mast a
          LEFT JOIN ref_emp_trans b
                 ON a.emp_no = b.emp_no
          LEFT JOIN ref_position c
                 ON b.br_code = c.br_code
                    AND b.div_code = c.div_code
                    AND b.rank_code = c.rank_code
                    AND b.dept_code = c.dept_code
                    AND b.post_code = c.post_code
          LEFT JOIN ref_department d
                 ON d.br_code = b.br_code
                    AND d.div_code = b.div_code
                    AND d.dept_code = b.dept_code
          LEFT JOIN iar_file e
                 ON a.emp_no = e.emp_no
          LEFT JOIN ref_branch f
                 ON c.br_code = f.br_code
          LEFT JOIN ref_leavestat g
                 ON e.leavestatusid = g.leavestatusid
   WHERE  ( b.emp_stat IN ( 'regu', 'prob', 'cont', 'ojt' )
            AND b.date_end IS NULL
             OR date_end > Getdate()
             OR date_end = '1900-01-01 00:00:00.000' )
          AND b.br_code LIKE '%'
          AND d.div_code LIKE '%'
          AND d.dept_code LIKE '%'
          AND c.rank_code LIKE '%'
          AND controlno IS NOT NULL
          AND e.leavestatusid = '2'
          AND isapproved = '1'
   ORDER  BY encoded_date DESC,
             NAME ASC ";
   $stmt_ot = sqlsrv_query($conn, $query_ot);
   $clsOT = new Standard('');
   $data = [];
   while (sqlsrv_fetch($stmt_ot)) {
      array_push($data, $clsOT->bindMetaData($stmt_ot));
   }

   return $data;
}
function getUN($conn)
{

   $query_ot = "
            SELECT TOP 10 e.controlno,
            a.emp_no,
            Rtrim(Ltrim(a.lastname)) + ', '
            + Rtrim(Ltrim(a.firstname)) + ' '
            + Substring(a.middlename, 1, 1) + '.' AS NAME,
            br_name,
            c.br_code,
            c.div_code,
            c.dept_code,
            d.deptname,
            c.post_code,
            c.post_name,
            a.pict_file,
            'UT',
            'Undertime</br>Rendered: '
            + Ltrim(Rtrim(Cast(e.timein AS CHAR(10))))
            + ' to '
            + Ltrim(Rtrim(Cast(e.timeout AS CHAR(10)))),
            c.rank_code,
            CONVERT(CHAR(12), effdate, 101)       AS date_Ffrom,
            CONVERT(CHAR(12), effdate, 101)       AS date_Fto,
            reason,
            e.leavestatusid,
            1.00,
            timeout - timein,
            encoded_by,
            CONVERT(CHAR(12), encoded_date, 101)  AS encoded_date,
            isapproved,
            g.leavestatus,
            approved_by,
            approved_date,
            e.audit_user,
            e.audit_date,
            e.ispis
         FROM   ref_emp_mast a
            LEFT JOIN ref_emp_trans b
                  ON a.emp_no = b.emp_no
            LEFT JOIN ref_position c
                  ON b.br_code = c.br_code
                     AND b.div_code = c.div_code
                     AND b.rank_code = c.rank_code
                     AND b.dept_code = c.dept_code
                     AND b.post_code = c.post_code
            LEFT JOIN ref_department d
                  ON d.br_code = b.br_code
                     AND d.div_code = b.div_code
                     AND d.dept_code = b.dept_code
            LEFT JOIN undertime_file e
                  ON a.emp_no = e.emp_no
            LEFT JOIN ref_branch f
                  ON c.br_code = f.br_code
            LEFT JOIN ref_leavestat g
                  ON e.leavestatusid = g.leavestatusid
         WHERE  ( b.emp_stat IN ( 'regu', 'prob', 'cont', 'ojt' )
            AND b.date_end IS NULL
               OR date_end > Getdate()
               OR date_end = '1900-01-01 00:00:00.000' )
            AND b.br_code LIKE '%'
            AND d.div_code LIKE '%'
            AND d.dept_code LIKE '%'
            AND c.rank_code LIKE '%'
            AND controlno IS NOT NULL
            AND e.leavestatusid = '2'
            AND isapproved = '1'";
   $stmt_ot = sqlsrv_query($conn, $query_ot);
   $clsOT = new Standard('');
   $data = [];
   while (sqlsrv_fetch($stmt_ot)) {
      array_push($data, $clsOT->bindMetaData($stmt_ot));
   }

   return $data;
}


echo json_encode($response);
