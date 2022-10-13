<?php

require('../backend/dbconn.php');
require('../backend/standard.php');
if (!isset($_GET['login'])) {
   header("Location: ../pages/login.php");
} else {
   session_start();
   $token = $_GET['login'];
   $_SESSION['token'] = $token;
   $auth = new Standard("");
   $_query = "select * from hris_mainLogIn where token_id='$token'";
   $stmt = sqlsrv_query($conn, $_query);
   if (sqlsrv_fetch($stmt)) {
      $_SESSION['user'] = $auth->bindMetaData($stmt);
      $Employee = new Standard("");
      $_query1 = "select rtrim(ltrim(a.emp_no)) as emp_no,g.br_name,a.pict_file as pics,ltrim(rtrim(a.firstname))+'.'+ltrim(rtrim(a.lastname)) as log_name, ltrim(rtrim(a.lastname))+', '+ltrim(rtrim(a.firstname))+' '+substring(ltrim(rtrim(middlename)), 1, 1)+'.' as name,e.log_date, rtrim(ltrim(f.stat_name)) as status,rtrim(ltrim(c.rank_code)) as rank_code, rtrim(ltrim(c.post_name)) as position, rtrim(ltrim(d.deptname)) as department,a.sex,g.br_code,g.br_name,ltrim(rtrim(g.id_prefix)) as id_prefix from ref_emp_mast a left join ref_emp_trans b on a.emp_no = b.emp_no left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code and b.dept_code = c.dept_code and b.post_code = c.post_code left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code left join hris_mainLogIn e on b.emp_no in (e.user_name,e.temp_pass) left join ref_emp_stat f on f.emp_stat = b.emp_stat and f.br_prefix = b.br_code left join ref_branch g on d.br_code = g.br_code where ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000') and e.log_key = 1 and token_id like rtrim(ltrim('" . $token . "'))";
      $stmt1 = sqlsrv_query($conn, $_query1);
      if (sqlsrv_fetch($stmt1)) {
         $_SESSION['emp_details'] = $Employee->bindMetaData($stmt1);
         $_SESSION['emp_details']['pics'] = "http://everloyalty1.ever.ph/hrforms/PISAPI/pics/" . $_SESSION['emp_details']['pics'];
      }

      $Approver = new Standard("ref_hris_approver");
      $params['emp_no'] = $_SESSION['emp_details']['emp_no'];
      $_SESSION['Approver'] = $Approver->selectData($params);
      $_SESSION['pis_controller'] = false;
      $_SESSION['pis_recruiter'] = false;

      //check if user is a Pis controller
      $cparams['emp_no'] = $_SESSION['emp_details']['emp_no'];
      $cparams['level'] = '2';
      $clsController = new Standard("ref_Userlist");
      $resultClsController = $clsController->selectData($cparams);
      if ($resultClsController) {
         $_SESSION['emp_details']['pis_controller'] = true;
         $_SESSION['emp_details']['pis_recruiter'] = true;
      }

      //check if user is a Deparment Head
      $clsDepthead = new Standard("");
      $queryDeptHead = "select distinct a.emp_no,c.br_code,c.div_code,c.dept_code,c.post_code,c.rank_code
		from ref_emp_mast a
		left join ref_emp_trans b on a.emp_no = b.emp_no
		left join ref_position c on b.br_code = c.br_code and b.div_code = c.div_code and b.rank_code = c.rank_code
		and b.dept_code = c.dept_code and b.post_code = c.post_code
		left join ref_department d on d.br_code = b.br_code and d.div_code = b.div_code and d.dept_code = b.dept_code
		left join ref_hris_approver e on e.approver_empno = b.emp_no
		where b.emp_no = '" . $_SESSION['emp_details']['emp_no'] . "' and ( b.emp_stat in ('regu','prob','cont','ojt') and b.date_end is null or date_end > getdate() or date_end = '1900-01-01 00:00:00.000')
		and c.rank_code in ('03','04','05','06','07','08','09','10') and e.approver_empno is not null ";
      $stmt2 = sqlsrv_query($conn, $queryDeptHead);
      if (sqlsrv_fetch($stmt2)) {
         $_SESSION['emp_details']['is_dept_head'] = true;
      } else {
         $_SESSION['emp_details']['is_dept_head'] = false;
      }

      header("Location: ../pages/index.php");
   } else {
      header("Location: ../pages/login.php");
   }
}
