<?php
require('../backend/standard.php');
require('../backend/dbconn.php');
$response = [];

if ($_SERVER['HTTP_AUTORIZATION']) {
   $MAIN_TOKEN = trim(str_replace("Bearer", "", $_SERVER['HTTP_AUTORIZATION']));
   $employee = extractEmployee($conn, $MAIN_TOKEN);
   if ($MAIN_TOKEN && $employee['emp_no'] != '') {
      if ($_POST['action'] == "validateOTsetting") {
         $data = $_POST['data'];
         $query = "select a.emp_no,dbo.converthrs_fra(c.total_hrs) as work_hrs_aday,a.br_code
         from ref_emp_trans a
         left join ref_branch b on a.br_code = b.br_code
         left join ref_schedule c on b.id_prefix = c.br_prefix and a.shift_code = c.shift_code
         where a.emp_no = '" . $employee['emp_no'] . "'";
         $stmt = sqlsrv_query($conn, $query);
         $ref_schedule = new Standard('');
         $result = 0;
         if (sqlsrv_fetch($stmt)) {
            $result = $ref_schedule->bindMetaData($stmt);
            $continue = false;
            if ($result['work_hrs_aday'] < $data) {
               $response['error'] = true;
               $response['status'] = 503;
               $response['message'] = "Cannot proceed with request. Your current work schedule for a day is lower than the desired total overtime to be used.";
            }
            if ($continue) {
            }
         } else {
            $response['error'] = true;
            $response['status'] = 503;
            $response['message'] = "You currently do not have any schedule. Please contact the administrator.";
         }
      }
   } else {
      $response['error'] = true;
      $response['status'] = 503;
      $response['message'] = "Token unrecognized.";
   }
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
echo json_encode($response);
