<?php
require 'PHPMailerAutoload.php';

$mail = new PHPMailer;


//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = '192.168.17.139';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'gene.paular';                 // SMTP username
$mail->Password = 'itdgene';                           // SMTP password
$mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 25;                                    // TCP port to connect to

$mail->setFrom('gene.paular@ever.ph', 'mechelle');
$mail->addAddress('gene.paular@ever.ph');     // Add a recipient
//$mail->addAddress('ellen@example.com');               // Name is optional
//$mail->addReplyTo('info@example.com', 'Information');
$mail->addCC('alvin.delacruz@ever.ph');
//$mail->addBCC('bcc@example.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
                              // Set email format to HTML
$mail->isHTML(true);    
$mail->Subject = 'Here is the subject';



/**/
/*$to = "alvindelacruz091613@gmail.com";// "alvindelacruz091613@gmail.com";
$subject = "HTML email";*/
 
$message = "
<div align = 'center'>
				</br></br></br></br>
				<table style='width:1000px;shadow: 4px 4px 8px yellow;box-shadow: 1px 1px 1px 1px rgba(0, 0, 2, 5);' cellpadding='0'cellspacing='0' border = '1' >
				<tr>
				<td colspan='2' style='background-color:blue;'>
				  <h1 style='margin:0;text-align:center;padding:0;color:white;font-family:verdana;text-shadow: 4px 4px 8px yellow;font-size:100%'>test<br/>   &nbsp; A R T I C L E  &nbsp; F O R  &nbsp; C O N S I G N M E N T</h1>
				</td>
				</tr>
				  <tr>
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Control Number</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'><a href= 'http://everloyalty.ever.ph/purchForms/index.php'> &nbsp;&nbsp;test</a></td>
				  </tr>
				  <tr>
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Category</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'><p>&nbsp;&nbsp;test</td>
				  </tr>  
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Barcode</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test</td>
				  </tr>
				  <tr>
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Merchandise</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'>&nbsp;&nbsp;test</td>
				  </tr>
				  <tr>  
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Vendor</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test | test</td>   
				  </tr>
				  <tr>                      
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Description</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test/td>
				  </tr>
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Final SRP</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'>  &nbsp;&nbsp;test</td>
				  </tr> 
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;MRP Type</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test</td>
				  </tr>
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Plan Cycle B | P</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test</td>
				  </tr>	
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Initial Order Site</b></label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test</td>
				  </tr>					  			  
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Date Prepared</label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test</td>
				  </tr>
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Prepared by</label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'> &nbsp;&nbsp;test</td>
				  </tr>    
				  <tr>            
				    <td style= 'background-color:grey;font-family:verdana;width:210px;vertical-align:top;color:black;font-size:85%'><label><b> &nbsp;&nbsp;Remarks</label></td>
				    <td style= 'width:500px;vertical-align:top;font-family:verdana;font-size:85%'><p>&nbsp;&nbsp;Remarks for Reference : </p><p>&nbsp;&nbsp;test</p></td>
				  </tr>       
				  <tr>
				    <td colspan='2' style='background-color:blue;text-align:center;color:white;font-family:verdana;'>copyright © ITD software team 2015</td>
				  </tr>
				</table>
				</div>";


// Always set content-type when sending HTML email
/*$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: alvin.delacruz@ever.ph' . "\r\n";
$headers .= 'Cc: gene.paular@ever.ph' . "\r\n";

mail($to,$subject,$message,$headers);*/
$mail->Body    = $message;
$mail->AltBody = $message;
if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}


?><!-- user.2010@ever.ph, , rommel.david@ever.ph -->