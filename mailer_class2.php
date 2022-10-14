<?php

class Mailer
{
	public $conn;
	public function __construct()
	{
		// include "db_conn.php";
		require('backend/dbconn.php');
		$this->conn = $conn;

		/*ini_set('display_errors', true);
				error_reporting(E_ALL);*/
		error_reporting(0);
	}

	public function mailformat_request($empno, $name, $type, $datefiled, $controlno, $department, $br_name, $effdate, $reason, $mailerfr, $mailerto)
	{

		/*echo $name;
		echo $type;
		var_dump($mailerfr);
		var_dump($mailerto);
		*//*		*/
		require 'phpmailer/PHPMailerAutoload.php';
		$mail = new PHPMailer;
		$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->SMTPAuth = true;                              	// Set mailer to use SMTP
		$mail->Host = '192.168.16.35';  								// Specify main and backup SMTP servers
		$mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also
		$mail->Port = 25;
		foreach ($mailerfr as $key_fr => $values_fr) {
			foreach ($values_fr as $email_fr => $user_email_fr) {
				$userfrm = trim($user_email_fr);
			}
		}


		// $useraccnt = "select * from HO_PIS.SMTP.dbo.evermail where email = '$userfrm'; --mailformat_request,$name,$controlno,$empno";
		// $useraccntrun = mssql_query($useraccnt);
		// $row_sender   = mssql_fetch_object($useraccntrun);

		$useraccnt = "select * from HO_PIS.SMTP.dbo.evermail where email = '$userfrm'; --mailformat_request,$name,$controlno,$empno";
		$useraccntrun = sqlsrv_query($this->conn, $useraccnt);
		$row_sender   = sqlsrv_fetch_object($useraccntrun);


		$username = trim($row_sender->user_name);
		$password = trim($row_sender->password);

		$mail->Username = $username;                 // SMTP username
		$mail->Password = $password;
		$mail->setFrom($userfrm);

		foreach ($mailerto as $key => $values) {
			foreach ($values as $email => $user_email) {

				// die();
				$mail->addAddress($user_email);
				// $mail->addAddress("marvin.orsua@ever.ph", "Marvin");
				//var_dump(PHPMailer::validateAddress($user_email));
			}
		}
		// die();


		$mail->addCC($userfrm);
		// $mail->addBCC('alvin.delacruz@ever.ph');
		//$mail->addBCC('alvindelacruz091613@gmail.com');


		/*
	    $to = implode(",",$array_to);
		$cc = $from;
		$bcc = 'gene.paular@ever.ph';*/

		/*		$to = 'user.2010@ever.ph, alvin.delacruz@ever.ph';
		$from = 'newuser.2010@ever.ph,gene.paular@ever.ph';
		$cc = 'gene.paular@ever.ph';
		$bcc = 'gene.paular@ever.ph';*/
		$subject = 'Request for ' . trim($type) . ' - ' . trim($name) . ' (#' . $controlno . ')';

		$body = '
		  <link rel="stylesheet" href="mailer.css">
		  <table cellpadding="0" cellspacing="0" border="0" width="620" align="center" id="backgroundTable">
		    <tr>
		        <td valign="top">
		          <table cellpadding="0" cellspacing="0" border="0" align="center">
		            <tr>
		              <td style="border-top:1px solid #dfdedd;" >
		                <table border="0" cellpadding="0" cellspacing="0" width="622" align="center" style="background:#fff;
		                  border:1px solid #dfdedd;" >
		                  <tr>
		                    <td style="background:#f2f2f2;padding:20px 20px 12px 5px; line-height:15pt;color:#999999;border-top:2px #dfdedd solid;">
		                      <a href="http://www.ever.ph" style="text-decoration:none; font-size: 16px; color: red;">
		                        <strong>EVER &mdash; ' . strtoupper($br_name) . '</strong>
		                      </a>
		                    </td>
		                  </tr>
		                  <tr>
		                    <td style="padding:5px;">
		                      <table border="0" cellpadding="0" cellspacing="0" width="602" align="left" style="margin-bottom:0;padding-bottom:0;">
		                        <tr>
		                          <td valign="top">
		                            <h3 style="font-size:16px;color:black;color:black;text-decoration:none;border-bottom: solid 1px #eee;">
		                              Request for <em>' . $type . '</em><br/> (<a href="http://everloyalty1.ever.ph/hrforms/pisrequest/index.html?controlno=">' . $controlno . '</a>)
		                            </h3>
		                            <p style="font-size:11px;line-height:25px;color:#7e8686;text-align:justify;padding-top:10px;">
		                              Name: ' . strtoupper($name) . '<br>
		                              Date Filed: ' . $datefiled . '<br>
		                              Effectivity Date: ' . $effdate . '<br>
		                            </p>
		                            <p style="font-size:11px;line-height:25px;color:#7e8686;text-align:justify;padding-top:10px;">
		                              <strong>Reason:</strong><br>
		                              ' . $reason . '
		                            </p>
		                          </td>
		                        </tr>
		                      </table>
		                    </td>
		                  </tr>
		                  <tr>
		                    <td bgcolor="#f4f4f4" style="padding:17px 20px 12px 20px; line-height:15pt;color:#999999;border-top:2px #eee dashed;">
		                      <table cellspacing="0" cellpadding="0" width="622" style="border-collapse:collapse;border-spacing:0;border-width:0;">
		                        <tr>
		                          <td>
		                            EVER-ITD &copy; 2013
		                          </td>
		                        </tr>
		                      </table>
		                    </td>
		                  </tr>
		                </table>
		              </td>
		            </tr>
		          </table>
		        </td>
		    </tr>
		</table>
		';
		/*		$body = '
		  testing for OWA Mail!!
		';*/
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;

		$mail->Body = $body;
		/*	$mail->send();*/

		// ---testing---uncomment-if-done
		if (!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			//echo 'Message has been sent';
		}



		// To send HTML mail, the Content-type header must be set
		/*	$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'To: <' . $to . ">\r\n";
		$headers .= 'From: <' . $from . ">\r\n";
		$headers .= 'Cc: <' . $cc . ">\r\n";
		$headers .= 'Bcc: ' . $bcc . "\r\n";


		// Mail it
		mail($to, $subject, $body, $headers);
		//echo "Mail Sent"."<br>";*/
	}

	public function mailformat_approve($name, $type, $datefiled, $controlno, $br_name, $effdate, $reason, $mailerfr, $mailerto, $req_stat)
	{




		require 'phpmailer/PHPMailerAutoload.php';
		$mail = new PHPMailer;

		// $mail->isSMTP();
		$mail->SMTPAuth = true;                              // Set mailer to use SMTP
		$mail->Host = '192.168.16.35';  							// Specify main and backup SMTP servers
		$mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also
		$mail->Port = 25;
		foreach ($mailerfr as $key_fr => $values_fr) {
			foreach ($values_fr as $email_fr => $user_email_fr) {
				$userfrm = trim($user_email_fr);
			}
		}



		$useraccnt = "select * from HO_PIS.SMTP.dbo.evermail where email = '$userfrm'; --mailformat_approve,$name,$controlno";
		$useraccntrun = sqlsrv_query($this->conn, $useraccnt);
		$row_sender   = sqlsrv_fetch_object($useraccntrun);
		// $useraccntrun = mssql_query($useraccnt);
		// $row_sender   = mssql_fetch_object($useraccntrun);

		$username = trim($row_sender->user_name);
		$password = trim($row_sender->password);

		$mail->Username = $username;                 // SMTP username
		$mail->Password = $password;
		$mail->setFrom($userfrm);


		foreach ($mailerto as $key => $values) {
			foreach ($values as $email => $user_email) {
				$mail->addAddress($user_email);
				//var_dump(PHPMailer::validateAddress($user_email));

			}
		}
		// $mail->addCC($userfrm);
		//$mail->addBCC('alvin.delacruz@ever.ph');
		//  $mail->addBCC('user.2010@ever.ph');
		/*$to = implode(",",$array_to);
		$bcc = 'gene.paular@ever.ph';*/

		/*		$to = 'user.2010@ever.ph, alvin.delacruz@ever.ph';
		$from = 'newuser.2010@ever.ph,gene.paular@ever.ph';
		$cc = 'gene.paular@ever.ph';
		$bcc = 'gene.paular@ever.ph';*/
		$subject = 'Status Request for ' . trim($type) . ' - ' . trim($name) . ' (#' . $controlno . ')';

		$body = '
		  <link rel="stylesheet" href="mailer.css">
		  <table cellpadding="0" cellspacing="0" border="0" width="620" align="center" id="backgroundTable">
		    <tr>
		        <td valign="top">
		          <table cellpadding="0" cellspacing="0" border="0" align="center">
		            <tr>
		              <td style="border-top:1px solid #dfdedd;" >
		                <table border="0" cellpadding="0" cellspacing="0" width="622" align="center" style="background:#fff;
		                  border:1px solid #dfdedd;" >
		                  <tr>
		                    <td style="background:#f2f2f2;padding:20px 20px 12px 5px; line-height:15pt;color:#999999;border-top:2px #dfdedd solid;">
		                      <a href="http://www.ever.ph" style="text-decoration:none; font-size: 16px; color: red;">
		                        <strong>EVER &mdash; ' . strtoupper($br_name) . '</strong>
		                      </a>
		                    </td>
		                  </tr>
		                  <tr>
		                    <td style="padding:5px;">
		                      <table border="0" cellpadding="0" cellspacing="0" width="602" align="left" style="margin-bottom:0;padding-bottom:0;">
		                        <tr>
		                          <td valign="top">
		                            <h3 style="font-size:16px;color:black;color:black;text-decoration:none;border-bottom: solid 1px #eee;">
		                              <em>' . $req_stat . ' request for </em><em>' . $leavetype . '</em><br/> (<a href="http://everloyalty1.ever.ph/hrforms/pisrequest/index.html?controlno=">' . $controlno . '</a>)
		                            </h3>
		                            <p style="font-size:11px;line-height:25px;color:#7e8686;text-align:justify;padding-top:10px;">
		                              Requested by: ' . strtoupper($name) . '<br>
		                              Date Filed: ' . $datefiled . '<br>
		                              Effectivity Date: ' . $effdate . '<br>
		                              Request Status: ' . $req_stat . '<br>
		                            </p>
		                            <p style="font-size:11px;line-height:25px;color:#7e8686;text-align:justify;padding-top:10px;">
		                              <strong>Reason:</strong><br>
		                              ' . $reason . '
		                            </p>
		                          </td>
		                        </tr>
		                      </table>
		                    </td>
		                  </tr>
		                  <tr>
		                    <td bgcolor="#f4f4f4" style="padding:17px 20px 12px 20px; line-height:15pt;color:#999999;border-top:2px #eee dashed;">
		                      <table cellspacing="0" cellpadding="0" width="622" style="border-collapse:collapse;border-spacing:0;border-width:0;">
		                        <tr>
		                          <td>
		                            EVER-ITD &copy; 2013
		                          </td>
		                        </tr>
		                      </table>
		                    </td>
		                  </tr>
		                </table>
		              </td>
		            </tr>
		          </table>
		        </td>
		    </tr>
		</table>
		';

		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;

		$mail->Body = $body;
		/*	$mail->send();*/
		if (!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			//echo 'Message has been sent';
		}

		/*	// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		$headers .= 'To: <' . $to . ">\r\n";
		$headers .= 'From: <' . $from . ">\r\n";
		$headers .= 'Bcc: ' . $bcc . "\r\n";


		// Mail it
		mail($to, $subject, $body, $headers);
		//echo "Mail Sent"."<br>";*/
	}

	public function get_sender($param1)
	{

		try {
			$sql_sender = "select email from HO_PIS.eversql.ehelpdesk.dbo.employee where emp_no = '{$param1}' ";
			$rs_sender = sqlsrv_query($this->conn, $sql_sender);
			$nm_row = sqlsrv_num_fields($rs_sender);

			// $rs_sender = mssql_query($sql_sender);
			// $nm_row = mssql_num_rows($rs_sender);

			$sql_sender2 = "select email from hris_mainLogIn where temp_pass = '{$param1}' ";
			// $rs_sender2 = mssql_query($sql_sender2);
			// $rs_sender2 = mssql_query($sql_sender2);
			$rs_sender2 = sqlsrv_query($this->conn, $sql_sender2);
			$nm_row2 = sqlsrv_num_fields($rs_sender2);
			if ($nm_row > 0) {
				// while ($row_sender = mssql_fetch_object($rs_sender)) {
				while ($row_sender = sqlsrv_fetch_object($rs_sender)) {
					$email_sender[] = $row_sender;
				}
				// mssql_free_result($rs_sender);
				sqlsrv_free_stmt($rs_sender);
				return $email_sender;
			} elseif ($nm_row2 > 0) {

				// while ($row_sender2 = mssql_fetch_object($rs_sender2)) {
				while ($row_sender2 = sqlsrv_fetch_object($rs_sender2)) {
					$email_sender[] = $row_sender2;
				}
				// mssql_free_result($rs_sender2);
				sqlsrv_free_stmt($rs_sender2);
				return $email_sender;
			} else {
				return false;
			}
		} catch (\Throwable $th) {
			var_dump($th);
		}
	}

	public function get_recipient($param1)
	{
		// $sql_recipient = "select email, '2' as sequence from dbo.hris_mainLogIn where temp_pass in (select approver_empno from ref_hris_approver where emp_no = '{$param1}' and approver_empno <> '9900066' and is_approver = 1);";
		$sql_recipient = "select email from dbo.hris_mainLogIn where temp_pass in (select approver_empno from ref_hris_approver where emp_no = '{$param1}' and approver_empno <> '9900066' and is_approver = 1);";
		// $rs_recipient = mssql_query($sql_recipient);
		// $nm_row = mssql_num_rows($rs_recipient);
		$rs_recipient = sqlsrv_query($this->conn, $sql_recipient);
		$nm_row = sqlsrv_num_fields($rs_recipient);
		if ($nm_row > 0) {
			// while ($row_recipient = mssql_fetch_object($rs_recipient)) {
			while ($row_recipient = sqlsrv_fetch_object($rs_recipient)) {
				$email_recipient[] = $row_recipient;
			}
			// mssql_free_result($rs_recipient);
			sqlsrv_free_stmt($rs_recipient);
			return $email_recipient;
		} else {
			return false;
		}
	}

	public function get_recipient_approve($param1, $param2)
	{
		foreach ($param2 as $value) {
			$table = $value->tablename;
		}

		$sql_recipient_app = "select email from HO_PIS.eversql.ehelpdesk.dbo.employee where emp_no in (select emp_no from {$table} where controlno = '{$param1}') ";
		// $rs_recipient_app = mssql_query($sql_recipient_app);
		// $nm_row = mssql_num_rows($rs_recipient_app);
		$rs_recipient_app = sqlsrv_query($this->conn, $sql_recipient_app);
		$nm_row = sqlsrv_num_fields($rs_recipient_app);

		if ($nm_row > 0) {
			// while ($row_recipient_app = mssql_fetch_object($rs_recipient_app)) {
			while ($row_recipient_app = sqlsrv_fetch_object($rs_recipient_app)) {
				$email_recipient_app[] = $row_recipient_app;
			}
			// mssql_free_result($rs_recipient_app);
			sqlsrv_free_stmt($rs_recipient_app);
			return $email_recipient_app;
		} else {
			return false;
		}
	}

	public function get_bcc()
	{
		$sql_bcc = "select email from HO_PIS.eversql.ehelpdesk.dbo.employee where emp_no = '9900628' ";
		// $rs_bcc = mssql_query($sql_bcc);
		// $nm_row = mssql_num_rows($rs_bcc);
		$rs_bcc = sqlsrv_query($this->conn, $sql_bcc);
		$nm_row = sqlsrv_num_fields($rs_bcc);

		if ($nm_row > 0) {
			// while ($row_bcc = mssql_fetch_object($rs_bcc)) {
			while ($row_bcc = sqlsrv_fetch_object($rs_bcc)) {
				$email_bcc[] = $row_bcc;
			}
			// mssql_free_result($rs_bcc);
			sqlsrv_free_stmt($rs_bcc);
			return $email_bcc;
		} else {
			return false;
		}
	}

	public function get_table($param1)
	{
		$tbl_prefix = substr($param1, 0, 2);
		$sql_table = "select tablename from ref_controlno where module_code = '{$tbl_prefix}' ";
		// $rs_table = mssql_query($sql_table);
		// $nm_row = mssql_num_rows($rs_table);
		$rs_table = sqlsrv_query($this->conn, $sql_table);
		$nm_row = sqlsrv_num_fields($rs_table);

		if ($nm_row > 0) {
			// while ($row_table = mssql_fetch_object($rs_table)) {
			while ($row_table = sqlsrv_fetch_object($rs_table)) {
				$table[] = $row_table;
			}
			// mssql_free_result($rs_table);
			sqlsrv_free_stmt($rs_table);
			return $table;
		} else {
			return false;
		}
	}
}
