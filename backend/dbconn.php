<?php
try {
   // $serverName = "192.168.16.24"; //serverName\instanceName
   // $pass = "Masterkey2";

   $serverName = "192.168.11.108"; //serverName\instanceName
   $pass = "donterase";
   $connectionInfo = array("Database" => "PIS", "UID" => "sa", "PWD" => $pass, "TrustServerCertificate" => True);
   $conn = sqlsrv_connect($serverName, $connectionInfo);
   if ($conn) {
      return $conn;
   } else {
      $response['error'] = true;
      $response['message'] = "Unable to connect to database. Please try again";
      echo json_encode($response);
      die();
   }
} catch (PDOException $e) {

   echo "Connection failed: " . $e->getMessage();
   die();
}
