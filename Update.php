<?php 

$servername = "localhost";
$username = "amira";
$password = "amira";
$dbname = "g_health24";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$fetch = $conn->prepare("SELECT * from wp_doctor;");
$fetch->execute();
if ($fetch->rowCount() > 0) {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $result = $fetch->fetchAll(); //make the select
    for ($i=0; $i <3188; $i++) { 
      $doctor_speciality=$result[$i]['doctor_speciality'];
      $doctor_speciality_maj=ucfirst($doctor_speciality);
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = $conn->prepare("SELECT * from speciality where specialities LIKE '%$doctor_speciality%';");
      $sql->execute();
      if ($sql->rowCount() > 0) {
          echo '';
      } else {
        $sql = "INSERT INTO speciality (id, specialities,specialitie_fr_maj	) VALUES (0,'$doctor_speciality','$doctor_speciality_maj')";
        $conn->exec($sql);
        echo '#';

      }
    }

}
} catch (PDOException $e) {
  echo $e->getMessage();
}
echo '
update specialitys....
';
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
      $doctor_speciality=trim($result[$i]['speciality_fr']);
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $fetch = $conn->prepare("SELECT * from speciality where specialities LIKE '%$doctor_speciality%';");
      $fetch->execute();
      if ($fetch->rowCount() > 0) {
       $res = $fetch->fetchAll(); //make the select
       $id= $res[0]['id'];
      $doctor_speciality_en=$result[$i]['speciality_en'];
      $doctor_speciality_enmaj=$result[$i]['speciality_en_maj'];
      $doctor_speciality_frdescription=$result[$i]['speciality_description'];
      $doctor_speciality_endescription=$result[$i]['speciality_description_en'];
      $sql = "UPDATE  speciality SET specialite_en=?, specialitie_en_maj=?, description_fr=?, description_en	=? WHERE id=?";
              $stmt= $conn->prepare($sql);
              $stmt->execute([$doctor_speciality_en, $doctor_speciality_enmaj,$doctor_speciality_frdescription,$doctor_speciality_endescription, $id]);
              $fetch->execute();
              echo '#';
      echo '#';
      } else {
      echo'';        
      }
    }
}
} catch (PDOException $e) {
  echo $e->getMessage();
}
$conn = null;


//   $servername = "localhost";
//   $username = "amira";
//   $password = "amira";
//   $dbname = "g_health24";
//   try {
//       $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
//       $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//   $fetch = $conn->prepare("SELECT * from wp_doctor;");
//   $fetch->execute();
//   if ($fetch->rowCount() > 0) {
//       $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
//       $result = $fetch->fetchAll(); //make the select
//       for ($i=0; $i <3327 ; $i++) { 
//         $name_doctor_maj = ucfirst($result[$i]['doctor_name']);
//         $id_doctor=$result[$i]['id_doctor'];
//         $doctor_min=mb_strtolower($result[$i]['doctor_name']);
//         $doctor_city=$result[$i]['doctor_region'].'|tunisa';
//         $bio=$result[$i]['doctor_bio'];
//         $bio='<h2 class="y4vuei-3 cBtaUV">'.$name_doctor_maj.'</h2>'.$bio;
//         $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
//         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         $sql = "UPDATE wp_doctor SET doctor_min=?, doctor_name=?, status=?, coments_status=?,doctor_bio=?, post_author=?,ping_status=?,featuredItem=?,doctor_city=? WHERE id_doctor=?";
//         $stmt= $conn->prepare($sql);
//         $stmt->execute([$doctor_min, $name_doctor_maj,'publish','open', $bio,1,1,1,$doctor_city, $id_doctor]);
//         $fetch->execute();
//         print_r('#');
//       }

//   }
// } catch (PDOException $e) {
//     echo $e->getMessage();
// }
// $conn = null;


$servername = "localhost";
$username = "amira";
$password = "amira";
$dbname = "g_health24";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$fetch = $conn->prepare("SELECT * from wp_doctor;");
$fetch->execute();
if ($fetch->rowCount() > 0) {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $result = $fetch->fetchAll(); //make the select
    for ($i=0; $i <3188; $i++) { 
      $doctor_speciality=$result[$i]['doctor_speciality'];
      $doctor_speciality_maj=ucfirst($doctor_speciality);
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = $conn->prepare("SELECT * from speciality where specialities LIKE '%$doctor_speciality%';");
      $sql->execute();
      if ($sql->rowCount() > 0) {
          echo '';
      } else {
        $sql = "INSERT INTO speciality (id, specialities,specialitie_fr_maj	) VALUES (0,'$doctor_speciality','$doctor_speciality_maj')";
        $conn->exec($sql);
        echo '#';

      }
    }

}
} catch (PDOException $e) {
  echo $e->getMessage();
}



?>