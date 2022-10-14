<?php

include "../mailer_class2.php";

$Mailer = new Mailer();

// var_dump($Mailer->get_sender('9902158'));
// var_dump($Mailer->get_recipient('9902158'));

$LOA['lrs_desc'] = "Leave of Absence Authorized";
$mailfrom[0]['email'] = 'marvin.orsua@ever.ph';
$mailto[0]['email'] = 'marvin.orsua@ever.ph';

echo '<pre>';
try {
   $Mailer->mailformat_request('9902158', 'ORSUA, CHRISTIAN MARVIN T.', $LOA, '2022-10-14', 'CS00019332', 'IT', 'Head Office', '2022-10-14', 'test', $mailfrom, $mailto);
} catch (\Throwable $th) {
   var_dump($th);
}

die();
