<?php

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
require __DIR__ . '/vendor/autoload.php';
//getting json data specialitys from https://www.tobba.tn///
$url = 'https://api.keeplyna.com/api/tc_auth/specialities/';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$html = curl_exec($ch);
curl_close($ch);
$specialitys = json_decode(($html), true);
for ($i = 0; $i < 28; $i++) {
    $speciality_name = $specialitys[$i]['name_l1'];
    $speciality_description = $specialitys[$i]['description_l1'];
    $speciality_name=str_replace(",", " ", $speciality_name);
    $speciality_name=str_replace("'", "` ", $speciality_name);
    $speciality_description=str_replace(",", " ", $speciality_description);
    $speciality_description=str_replace("'", "` ", $speciality_description);
    $servername = "localhost";
    $username = "amira";
    $password = "amira";
    $dbname = "g_health24";
    try {
        $arr = [];
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $conn->prepare("SELECT * from wp_speciality where speciality_name LIKE '%$speciality_name%'");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '.';
        } else {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "INSERT INTO wp_speciality (id, speciality_name,speciality_description,created_at)
                    VALUES (0,'$speciality_name', '$speciality_description',now() )";
            // use exec() because no results are returned
            echo '#';
            $conn->exec($sql);
        }
        $conn = null;
    } catch (PDOException $e) {
        echo '.';
    }

    $conn = null;
}
