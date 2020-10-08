<?php

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';
function Utf8_ansi($valor = '')
{
    $utf8_ansi2 = array(
        "\u00c0" => "À",
        "\u00c1" => "Á",
        "\u00c2" => "Â",
        "\u00c3" => "Ã",
        "\u00c4" => "Ä",
        "\u00c5" => "Å",
        "\u00c6" => "Æ",
        "\u00c7" => "Ç",
        "\u00c8" => "È",
        "\u00c9" => "É",
        "\u00ca" => "Ê",
        "\u00cb" => "Ë",
        "\u00cc" => "Ì",
        "\u00cd" => "Í",
        "\u00ce" => "Î",
        "\u00cf" => "Ï",
        "\u00d1" => "Ñ",
        "\u00d2" => "Ò",
        "\u00d3" => "Ó",
        "\u00d4" => "Ô",
        "\u00d5" => "Õ",
        "\u00d6" => "Ö",
        "\u00d8" => "Ø",
        "\u00d9" => "Ù",
        "\u00da" => "Ú",
        "\u00db" => "Û",
        "\u00dc" => "Ü",
        "\u00dd" => "Ý",
        "\u00df" => "ß",
        "\u00e0" => "à",
        "\u00e1" => "á",
        "\u00e2" => "â",
        "\u00e3" => "ã",
        "\u00e4" => "ä",
        "\u00e5" => "å",
        "\u00e6" => "æ",
        "\u00e7" => "ç",
        "\u00e8" => "è",
        "\u00e9" => "é",
        "\u00ea" => "ê",
        "\u00eb" => "ë",
        "\u00ec" => "ì",
        "\u00ed" => "í",
        "\u00ee" => "î",
        "\u00ef" => "ï",
        "\u00f0" => "ð",
        "\u00f1" => "ñ",
        "\u00f2" => "ò",
        "\u00f3" => "ó",
        "\u00f4" => "ô",
        "\u00f5" => "õ",
        "\u00f6" => "ö",
        "\u00f8" => "ø",
        "\u00f9" => "ù",
        "\u00fa" => "ú",
        "\u00fb" => "û",
        "\u00fc" => "ü",
        "\u00fd" => "ý",
        "\u00ff" => "ÿ"
    );

    return strtr($valor, $utf8_ansi2);
}





// $speciality= array('laboratoire-danalyses-de-biologie-medicale','laboratoire-danatomie-et-cytologie-pathologiques','laboratoire-de-cytogenetique');
// $country = array(
//     'tunis', 'sfax', 'sousse', 'monastir', 'ariana', 'ben-arous', 'nabeul', 'mahdia',
//     'kairouan', 'bizerte', 'medenine', 'mannouba', 'gabes', 'beja', 'gafsa', 'jendouba', 'le-Kef', 'sidi-bouzid',
//     'kasserine', 'zaghouan', 'siliana', 'kebili', 'tataouine', 'tozeur',
// );
// foreach($speciality as $value){
//     for($dataa=0;$dataa<23;$dataa++){
// $html = file_get_contents('https://www.med.tn/laboratoire/'.$value.'/'.$country[$dataa]);
// $doc = new \DOMDocument('1.0', 'UTF-8');

// // set error level
// $internalErrors = libxml_use_internal_errors(true);
// $doc->loadHTML($html);
// $xpath = new \DOMXpath($doc);
// $articles = $xpath->query('//div[@class="praticien__img"]');

// $links = [];
// foreach($articles as $container) {
//   $arr = $container->getElementsByTagName("a");
//   foreach($arr as $item) {
//     $href =  $item->getAttribute("href");
//     $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// $brower->get($href)
//     ->then(function (ResponseInterface $response) {
//         $crawler = new Crawler((string) $response->getbody());
//         $name = Utf8_ansi($crawler->filter('.doctitle')->text());
//         $name = str_replace("'", "`", $name);
//         $type = utf8_decode($crawler->filter('.docsubtitle')->text());
//         $type = str_replace("'", "`", $type);
//         $place = utf8_decode($crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem')->text());
//         $place = str_replace(",", "", $place);
//         $place = str_replace("'", "`", $place);
//         $servername = "localhost";
//         $username = "amira";
//         $password = "amira";
//         $dbname = "g_health24";
//         try {
//             $arr = [];
//             $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//             $sql = $conn->prepare("SELECT * from wp_laboratory where labo_address LIKE '%$place%';");
//             $sql->execute();
//             if ($sql->rowCount() > 0) {
//                 echo '.';
//             } else {
//                     $sql = "INSERT INTO wp_laboratory (id, labo_name,labo_type,labo_country,labo_region
//                 created_at) VALUES (0,'$name','$type','Tunisie','$place',,NOW());";
//                     $conn->exec($sql);
//                     echo '#';

//                 $conn = null;
//             }
//         } catch (PDOException $e) {
//             echo '';
//         }

//         $conn = null;

//     });

// $loop->run();
// }

// }
// }
//     }

try {
    $lab_spec = array('laboratoires-d-analyses-medicales', 'laboratoires-cliniques', 'laboratoires-de-radiologie', 'laboratoires-pathologiques');
    $labo_id = array(2393008, 2393003, 2393006, 2393002);
    foreach ($lab_spec as $spec) {
        foreach ($labo_id as $id_spec) {
            for ($i = 1; $i < 3; $i++) {
                $html = file_get_contents('https://tn.kompass.com/a/' . $spec . '/' . $id_spec . '/page-' . $i . '/');
                $doc = new \DOMDocument('1.0', 'UTF-8');
                // set error level
                $internalErrors = libxml_use_internal_errors(true);
                $doc->loadHTML($html);
                $xpath = new \DOMXpath($doc);
                $articles = $xpath->query('//div[@class="col-xs-12 col-sm-8 col-md-8 company-container"]');
                $links = [];
                foreach ($articles as $container) {
                    $arr = $container->getElementsByTagName("a");
                    foreach ($arr as $item) {
                        $href =  $item->getAttribute("href");
                        $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
                        $loop = \React\EventLoop\Factory::create();
                        $factory = new \React\MySQL\Factory($loop);
                        $brower = new Browser($loop);
                        $brower->get($href)
                            ->then(function (ResponseInterface $response) {
                                $crawler = new Crawler((string) $response->getbody());
                                $name = utf8_decode($crawler->filter('.companyCol1.blockNameCompany h1')->text());
                                $name = str_replace("'", "`", $name);
                                $type = utf8_decode($crawler->filter('.company-activities')->text());
                                $type = str_replace("'", "`", $type);
                                $type = str_replace(",", " ", $type);
                                $place = utf8_decode($crawler->filter('.spRight')->text());
                                $place = str_replace("'", "`", $place);
                                $place = str_replace(",", " ", $place);
                                // $phoneid = $crawler->filter('input[name="companyTargetId"]')->attr('value');
                                $phone = $crawler->filter('.faxNumber')->text();
                                $servername = "localhost";
                                $username = "amira";
                                $password = "amira";
                                $dbname = "g_health24";
                                try {
                                    $arr = [];
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $sql = $conn->prepare("SELECT * from wp_laboratory where labo_address LIKE '%$place%';");
                                    $sql->execute();
                                    if ($sql->rowCount() > 0) {
                                        echo '.';
                                    } else {
                                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                        $sql = "INSERT INTO wp_laboratory (id, labo_name,labo_type,labo_country,labo_address
                ,phone,created_at) VALUES (0,'$name','$type','Tunisia','$place','$phone',NOW());";
                                        $conn->exec($sql);
                                        echo '#';

                                        $conn = null;
                                    }
                                } catch (PDOException $e) {
                                    echo '';
                                }
                                $conn = null;
                            });
                        $loop->run();
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    echo '';
}
$url = 'https://api.keeplyna.com/api/sample/labs/tunisie/';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$html = curl_exec($ch);
curl_close($ch);
$laboratory = json_decode(($html), true);
$labo_size = sizeof($laboratory['results']);
for ($i = 0; $i < $labo_size; $i++) {
    $labo_ref = $laboratory['results'][$i]['lab_ref'];
    $data = array('lab_ref' => $labo_ref);
    $url = 'https://api.keeplyna.com/api/sample/maps_labs/lab_code/';
    $opts = array('http' => array(
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
            "Content-Length: " . strlen(http_build_query($data)) . "\r\n" . "User-Agent:MyAgent/1.0\r\n", 'method'  => 'POST',
        'content' => http_build_query($data),
    ));
    $contexts = stream_context_create($opts);
    $result = file_get_contents($url, false, $contexts);
    if ($result === FALSE) {
        echo 'error';
    }
    $labo_data = json_decode(($result), true);
    $labo_name = Utf8_ansi($labo_data['name_fr']);
    $labo_type = utf8_decode($labo_data['presentation_fr']);
    $labo_country = Utf8_ansi($labo_data['country_obj']['name_l1']);
    $labo_region = Utf8_ansi($labo_data['region_obj']['name_l1']);
    $labo_location = utf8_decode($labo_data['address_fr']);
    $labo_email = Utf8_ansi($labo_data['email']);
    $labo_phone = Utf8_ansi($labo_data['phone_nbr']);
    $labo_type = str_replace(",", " ", $labo_type);
    $labo_type = str_replace("'", "` ", $labo_type);
    $labo_location = str_replace(",", " ", $labo_location);
    $labo_location = str_replace("'", "` ", $labo_location);
    $servername = "localhost";
    $username = "amira";
    $password = "amira";
    $dbname = "g_health24";
    try {
        $arr = [];
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = $conn->prepare("SELECT * from wp_laboratory where labo_name LIKE '%$labo_name%' and labo_address LIKE '%$labo_location%';");
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '.';
        } else {
            try {
                $sql = "INSERT INTO wp_laboratory (id, labo_name,labo_type,labo_country,labo_region,
                labo_address,email,phone,created_at) VALUES (0,'$labo_name','$labo_type','$labo_country',
                '$labo_region','$labo_location','$labo_email','$labo_phone',now());";
                $conn->exec($sql);

                echo '#';
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $conn = null;
}
