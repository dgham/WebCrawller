<?php

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
require __DIR__ . '/vendor/autoload.php';

echo'Loading data...';
//getting json data specialitys from https://www.tobba.tn///
$url = 'https://api.keeplyna.com/api/tc_auth/specialities/';
echo'
https://api.keeplyna.com/api/tc_auth/specialities/...';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$html = curl_exec($ch);
curl_close($ch);
$specialitys = json_decode(($html), true);
for ($i = 0; $i < 28; $i++) {
    $speciality_fr = mb_strtolower($specialitys[$i]['name_l1']);
    $speciality_fr_maj =ucfirst(mb_strtolower($specialitys[$i]['name_l1']));
    $speciality_en = mb_strtolower($specialitys[$i]['name_l2']);
    $speciality_en_maj =ucfirst(mb_strtolower($specialitys[$i]['name_l2']));
    $speciality_description_fr = $specialitys[$i]['description_l1'];
    $speciality_description_en = $specialitys[$i]['description_l2'];

    $speciality_fr=str_replace(",", " ", $speciality_fr);
    $speciality_fr=str_replace("'", "` ", $speciality_fr);
    $speciality_description_fr=str_replace(",", " ", $speciality_description_fr);
    $speciality_description_fr=str_replace("'", "` ", $speciality_description_fr);
    $speciality_en=str_replace(",", " ", $speciality_en);
    $speciality_en=str_replace("'", "` ", $speciality_en);
    $speciality_description_en=str_replace(",", " ", $speciality_description_en);
    $speciality_description_en=str_replace("'", "` ", $speciality_description_en);
    $speciality_fr_maj=str_replace(",", " ", $speciality_fr_maj);
    $speciality_fr_maj=str_replace("'", "` ", $speciality_fr_maj);
    $speciality_en_maj=str_replace(",", " ", $speciality_en_maj);
    $speciality_en_maj=str_replace("'", "` ", $speciality_en_maj);
    $servername = "localhost";
    $username = "amira";
    $password = "amira";
    $dbname = "g_health24";
    try {
        $arr = [];
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $conn->prepare("SELECT * from wp_speciality where speciality_fr LIKE '%$speciality_fr%'");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '';
        } else {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO wp_speciality (id, speciality_fr,speciality_fr_maj,speciality_description,speciality_en,speciality_en_maj,speciality_description_en,created_at)
                    VALUES (0,'$speciality_fr','$speciality_fr_maj','$speciality_description_fr','$speciality_en','$speciality_en_maj','$speciality_description_en' , now() )";
            // use exec() because no results are returned
            echo '#';
            $conn->exec($sql);
        }
        $conn = null;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    $conn = null;
}
echo'
Done.
';

$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
echo'
https://www.med.tn/';
$brower->get("https://www.med.tn/")
    ->then(function (ResponseInterface $response) {
        $crawler = new Crawler((string) $response->getbody());
        $name = $crawler->filter('#field_speciality option')->extract(['_text']);
        for ($i=1;$i<=137;$i++){
            $name[$i]=str_replace("  ","",$name[$i]);
            $name[$i]=str_replace("\n","",$name[$i]);
            $speciality=mb_strtolower($name[$i]);
            $speciality_maj= ucfirst($speciality);
        $servername = "localhost";
        $username = "amira";
        $password = "amira";
        $dbname = "g_health24";
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8;", $username, $password);
            $conn->query("SET NAMES 'utf8'");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = $conn->prepare("SELECT * from wp_speciality where speciality_fr LIKE '%$name[$i]%'");
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '';
            } else {
                $sql = "INSERT INTO wp_speciality (id, speciality_fr,speciality_fr_maj,created_at)
                VALUES (0,'$speciality','$speciality_maj',now() )";
                $conn->exec($sql);
                echo '#';
               
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $conn = null;
    }
    });
$loop->run();

