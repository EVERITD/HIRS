<?php

class Standard
{
   public $table;
   public $conn;


   function __construct($table)
   {
      require('dbconn.php');
      $this->table = $table;
      $this->conn = $conn;
   }
   function selectData($params)
   {
      $filter = $this->genFilter($params);

      try {
         $_query = "SELECT * from $this->table where " . $filter;
         $stmt = sqlsrv_query($this->conn, $_query);
         if (sqlsrv_fetch($stmt)) {
            return $this->bindMetaData($stmt);
         } else {
            return false;
         }
      } catch (\Throwable $th) {
         var_dump($th);
      }
      die();
   }
   function inserData($params)
   {
      $columns = "";
      $values = "";

      foreach ($params as $key => $value) {
         if ($columns != "") {
            $columns .= ",";
         }

         if ($values != "") {
            $values .= ",";
         }
         $columns .= $key;
         if ($value == 'getdate()') {
            $values .= str_replace("'", "", $value);
         } else {
            $values .= "'" . $value . "'";
         }
      }
      try {
         $query = "INSERT INTO $this->table  ($columns) VALUES ($values)";
         $stmt = sqlsrv_query($this->conn, $query);
         if ($stmt) {
            return true;
         } else {
            return false;
         }
      } catch (\Throwable $th) {
         var_dump($th);
         die();
      }
   }

   function updateData($sets, $params)
   {
      $sets = $this->genSets($sets);
      $filter = $this->genFilter($params);

      try {
         $_query = "UPDATE $this->table set $sets where " . $filter;
         $stmt = sqlsrv_query($this->conn, $_query);
         if ($stmt) {
            return true;
         } else {
            return false;
         }
      } catch (\Throwable $th) {
         var_dump($th);
      }
      die();
   }
   function bindMetaData($stmt)
   {
      $ctr = 0;
      $data = [];
      try {
         foreach (sqlsrv_field_metadata($stmt) as $field) {
            if ($field['Name'] == 'log_date' || $field['Name'] == 'approved_date') {
               $date = sqlsrv_get_field($stmt, $ctr);
               if ($date) {
                  $data[$field['Name']] = trim($date->format('Y-m-d h:i A'));
               }
            } else if ($field['Name'] == 'date') {
               $date = sqlsrv_get_field($stmt, $ctr);
               if ($date) {
                  $data[$field['Name']] = trim($date->format('Y-m-d'));
               }
            } else {
               $data[$field['Name']] = trim(sqlsrv_get_field($stmt, $ctr));
            }
            $ctr++;
         }
         return $data;
      } catch (\Throwable $th) {
         var_dump($th);
      }
   }
   function encryptpass($params)
   {

      $sqlencrypta = "exec encrypt_pass '{$params['password']}','{$params['email']}'";
      $sqlencrypteda = sqlsrv_query($this->conn, $sqlencrypta);
      $sqlencryptedRowsa = sqlsrv_fetch_array($sqlencrypteda, SQLSRV_FETCH_NUMERIC);
      $chnpass = trim($sqlencryptedRowsa[0]);
      return $chnpass;
   }
   function genFilter($params)
   {
      $filter = "";
      foreach ($params as $key => $value) {

         if ($filter != "") {
            $filter .= " AND ";
         }

         $filter  .= $key . " = '" . $value . "'";
      }
      return $filter;
   }
   function genSets($params)
   {
      $sets = "";
      foreach ($params as $key => $value) {

         if ($sets != "") {
            $sets .= ", ";
         }
         if ($value != "getdate()") {
            $sets  .= $key . " = '" . $value . "'";
         } else {
            $sets  .= $key . " = " . $value . "";
         }
      }
      return $sets;
   }
   function generateControlNumber($module)
   {
      $query = "SELECT '$module' + RIGHT('00000000'+(SELECT Ltrim(Rtrim(Str(controlno+1))) FROM ref_controlno WHERE module_code = '" . $module . "'), 8) as controlno";
      $stmt = sqlsrv_query($this->conn, $query);
      if (sqlsrv_fetch($stmt)) {
         return  $this->bindMetaData($stmt);
      } else {
         var_dump("ERROR generating control number!");
         return false;
      }
   }
   function nextControlNumber($module)
   {
      try {
         $query = "UPDATE ref_controlno SET  controlno = controlno + 1 WHERE  module_code = '" . $module . "'";
         $stmt = sqlsrv_query($this->conn, $query);
         if ($stmt) {
            return true;
         } else {
            return false;
         }
      } catch (\Throwable $th) {
         var_dump($th);
         die();
      }
   }
}
