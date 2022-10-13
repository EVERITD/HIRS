<?php

include "../mailer_class2.php";

$Mailer = new Mailer();

// var_dump($Mailer->get_sender('9902158'));
// var_dump($Mailer->get_recipient('9902158'));

// $mailer->mailformat_request($eempno, $numRowsUserTok->name, $leave_type, $date_filed, $control_no, $numRowsUserTok->department, $numRowsUserTok->br_name, $lceff_date, $lReason, $mailer_from, $mailer_to);
var_dump($Mailer->get_recipient('9902158'));
die();
