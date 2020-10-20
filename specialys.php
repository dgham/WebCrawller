<?php 

$conn = null;
$servername = "localhost";
$username = "amira";
$password = "amira";
$dbname = "g_health24";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$fetch = $conn->prepare("SELECT * from wp_speciality;");
$fetch->execute();
if ($fetch->rowCount() > 0) {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $result = $fetch->fetchAll(); //make the select
    for ($i=0; $i <40; $i++) { 
      $doctor_speciality=$result[$i]['speciality_fr'];
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = $conn->prepare("SELECT * from speciality where specialities LIKE '%$doctor_speciality%';");
      $sql->execute();
      if ($sql->rowCount() > 0) {
        $res = $fetch->fetchAll(); 
       print_r($res);
       die;
    
      } else {
      echo'';        
      }
    }
}
} catch (PDOException $e) {
  echo $e->getMessage();
}
$conn = null;


?>
