
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
        "\u00c8" => "E",
        "\u00c9" => "E",
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
        "\u00e8" => "e",
        "\u00e9" => "e",
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
echo 'Loading data from websites...';

$country = array(
    'tunis', 'sfax', 'sousse', 'monastir', 'ariana', 'ben-arous', 'nabeul', 'mahdia',
    'kairouan', 'bizerte', 'medenine', 'mannouba', 'gabes', 'beja', 'gafsa', 'jendouba', 'le-Kef', 'sidi-bouzid',
    'kasserine', 'zaghouan', 'siliana', 'kebili', 'tataouine', 'tozeur',
);
$status = array(
    'jour', 'nuit', 'garde'
);
for ($i = 0; $i <= 2; $i++) {
foreach ($status as $data) {
    for ($k = 0; $k <= 23; $k++) {
        echo '
https://www.med.tn/pharmacie/' . $data . '/' . $country[$k];
            $html = file_get_contents('https://www.med.tn/pharmacie/' . $data . '/' . $country[$k]);
            $doc = new \DOMDocument('1.0', 'UTF-8');
            // set error level
            $internalErrors = libxml_use_internal_errors(true);
            $doc->loadHTML($html);
            $xpath = new \DOMXpath($doc);
            $articles = $xpath->query('//div[@class="card-doctor-picture"]');
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
                            $pharmacy_name = $crawler->filter('.doctitle')->text();
                            $pharmacy_type = $crawler->filter('.docsubtitle')->text();
                            $pharmacy_address = $crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem')->text();
                            $pharmacy_phone = $crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem a')->text();;
                            $location = $crawler->filter('.pf-itempage-maparea.pf-itempage-elements iframe')->attr('src');
                            $location = str_replace('https://maps.google.com/maps?q=', '{lantitude:', $location);
                            $location = str_replace('&hl=fr&z=14&output=embed', '', $location);
                            $location = str_replace(',', ',longitude:', $location);
                            $location = $location . '}';
                            $picture = $crawler->filter('.docimage a img')->attr('src');
                            $city = $crawler->filter('.pf-breadcrumbs.pf-breadcrumbs-special ul li span')->extract(['_text']);
                            $city = $city[2];
                            $servername = "localhost";
                            $username = "amira";
                            $password = "amira";
                            $dbname = "g_health24";
                            try {
                                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                // set the PDO error mode to exception
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$pharmacy_name%' and pharmacy_address LIKE '%$pharmacy_address%'");
                                $sql->execute();
                                if ($sql->rowCount() > 0) {
                                    echo '';
                                } else {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                                    // set the PDO error mode to exception
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_type,pharmacy_address,pharmacy_country,
                            pharmacy_city,pharmacy_location,phone,picture,created_at) 
                            VALUES (0,'$pharmacy_name','$pharmacy_type','$pharmacy_address','Tunisia','$city','$location','$pharmacy_phone','$picture',now())";
                                    // use exec() because no results are returned
                                    $conn->exec($sql);
                                    echo '#';
                                }
                                $conn = null;
                            } catch (Exception $e) {
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




// $i = null;
// $country = array(
//     'tunis', 'sfax', 'sousse', 'monastir', 'ariana', 'ben-arous', 'nabeul', 'mahdia',
//     'kairouan', 'bizerte', 'medenine', 'mannouba', 'gabes', 'beja', 'gafsa', 'jendouba', 'le-Kef', 'sidi-bouzid',
//     'kasserine', 'zaghouan', 'siliana', 'kebili', 'tataouine', 'tozeur',
// );
// $status = array(
//     'jour', 'nuit', 'garde'
// );
// $repeat=0;
// while($repeat<50){
// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// foreach ($status as $data) {
//    echo'
// https://www.med.tn/pharmacie/' . $data.'..';
// for ($k = 0; $k <= 23; $k++) {
//     for ($i = 0; $i <= 100; $i++) {
//             $brower->get('https://www.med.tn/pharmacie/' . $data . '/' . $country[$k])
//                 ->then(function (ResponseInterface $response ,$country) {
//                     $crawler = new Crawler((string) $response->getbody());
//                     $pharmacytype = $crawler->filter('.practitioner-title')->text();
//                     $pharmacyname = $crawler->filter('.practitioner-name')->text();
//                     $pharmacyimage = $crawler->filter('.card-doctor-picture a img')->eq(0)->attr('src');
//                     $pharmacyimage = 'https://www.med.tn/' . $pharmacyimage;
//                     $pharmacystatus = $crawler->filter('.practitioner-speciality .text-success')->text();
//                     $pharmacyaddress = $crawler->filter('.practitioner-address')->text();
//                     $pharmacyaddress = str_replace(",", " ", $pharmacyaddress);
//                     $pharmacyaddress = str_replace("'", "`", $pharmacyaddress);
//                     $pharmacyname = str_replace(",", " ", $pharmacyname);
//                     $pharmacyname = str_replace("'", "` ", $pharmacyname);
//                     $pharmacyphone = $crawler->filter('.btn.btn-phone .hide-tel')->text();
//                     $servername = "localhost";
//                     $username = "amira";
//                     $password = "amira";
//                     $dbname = "g_health24";
//                     try {
//                         $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//                         // set the PDO error mode to exception
//                         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                         $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$pharmacyname%' and pharmacy_address LIKE '%pharmacyaddress%'");
//                         $sql->execute();
//                         if ($sql->rowCount() > 0) {
//                             echo '';
//                         } else {
//                             $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
//                             // set the PDO error mode to exception
//                             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                             $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_type,pharmacy_address,pharmacy_country,phone,status,picture,created_at) 
//                             VALUES (0,'$pharmacyname','$pharmacytype','$pharmacyaddress','Tunisia','$pharmacyphone','$pharmacystatus','$pharmacyimage',now())";
//                             // use exec() because no results are returned
                           
//                             $conn->exec($sql);
//                             echo '#';
//                         }
//                         $conn = null;
//                     } catch (Exception $e) {
//                         echo '.';
//                     }
//                     $conn = null;
//                 });
//         }
    

// $loop->run();
// $repeat++;
// }
// }
// }
$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
echo'
https://psychologie-sante.tn/pharmacies-region-du-centre-de-la-tunisie/ ...';
    $brower->get('https://psychologie-sante.tn/pharmacies-region-du-centre-de-la-tunisie/')
        ->then(function (ResponseInterface $response) {
            $crawler = new Crawler((string) $response->getbody());
            for ($i = 203; $i < 301; $i = $i + 2) {
                $nampharmacy = Utf8_ansi($crawler->filter('.row-' . $i . '.odd .column-1')->text());
                $prenompharmacy = Utf8_ansi($crawler->filter('.row-' . $i . '.odd .column-2')->text());
                $namepharmacy = $nampharmacy . ' ' . $prenompharmacy;
                $addresspharmacy = Utf8_ansi($crawler->filter('.row-' . $i . '.odd .column-3')->text());
                $code = Utf8_ansi($crawler->filter('.row-' . $i . '.odd .column-4')->text());
                $phone = Utf8_ansi($crawler->filter('.row-' . $i . '.odd .column-5')->text());
                $namepharmacy = str_replace(",", " ", $namepharmacy);
                $namepharmacy = str_replace("'", "` ", $namepharmacy);
                $addresspharmacy = str_replace(",", " ", $addresspharmacy);
                $addresspharmacy = str_replace("'", "` ", $namepharmacy);
                $code = str_replace(",", " ", $code);
                $code = str_replace("'", "` ", $code);
                $code = str_replace(",", " ", $code);
                $code = str_replace("'", "` ", $code);
                $servername = "localhost";
                $username = "amira";
                $password = "amira";
                $dbname = "g_health24";
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$namepharmacy%' and phone like '%$phone%'");
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '';
                    } else {
                        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_address,pharmacy_country,postal_code,phone,created_at) 
                            VALUES (0,'$namepharmacy','$addresspharmacy','Tunisia','$code','$phone',now())";
                        // use exec() because no results are returned

                        $conn->exec($sql);
                        echo '#';
                    }
                    $conn = null;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $conn = null;
            }
        });
$loop->run();





for($i=0;$i<7;$i++){
$html = file_get_contents('https://annuairepro-tunisie.com/recherche-entreprise-pharmacie.html?page='.$i);
$doc = new \DOMDocument('1.0', 'UTF-8');
// set error level
$internalErrors = libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new \DOMXpath($doc);
$articles = $xpath->query('//div[@class="title"]');
$links = [];
foreach ($articles as $container) {
    $arr = $container->getElementsByTagName("a");
    foreach ($arr as $item) {
        $href =  $item->getAttribute("href");
        $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
dump($href);
die;

$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
echo'
https://annuairepro-tunisie.com/recherche-entreprise-pharmacie.html ...';
for ($nb = 1; $nb < 8; $nb++) {
    $brower->get('https://annuairepro-tunisie.com/recherche-entreprise-pharmacie.html?page=' . $nb)
        ->then(function (ResponseInterface $response) {
            $crawler = new Crawler((string) $response->getbody());
            $namepharmacy = $crawler->filter('.title a')->text();
            $adresspharmacy = $crawler->filter('.body p')->text();
            $phonepharmacy = $crawler->filter('ul .phone')->text();
            $namepharmacy = str_replace(",", " ", $namepharmacy);
            $namepharmacy = str_replace("'", "` ", $namepharmacy);
            $adresspharmacy = str_replace(",", " ", $adresspharmacy);
            $adresspharmacy = str_replace("'", "` ", $adresspharmacy);
            $servername = "localhost";
            $username = "amira";
            $password = "amira";
            $dbname = "g_health24";
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$namepharmacy%' and pharmacy_address like '%$phonepharmacy%'");
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '';
                } else {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_address,pharmacy_country,phone,created_at) 
                            VALUES (0,'$namepharmacy','$adresspharmacy','Tunisia','$phonepharmacy',now())";
                    // use exec() because no results are returned

                    $conn->exec($sql);
                    echo '#';
                }
                $conn = null;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $conn = null;
        });
}
$loop->run();
    }
}
}

// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// echo'
// https://tunisie-medicale.com/index.php/pharmacie/index/...';
// for ($nb = 1; $nb < 20; $nb++) {
//     $brower->get('https://tunisie-medicale.com/index.php/pharmacie/index/' . $nb)
//         ->then(function (ResponseInterface $response) {
//             $crawler = new Crawler((string) $response->getbody());
//             $name = $crawler->filter('li h2 a')->attr('title');
//             $name = str_replace(",", " ", $name);
//             $name = str_replace("'", "` ", $name);
//             $type = $crawler->filter('.team_widget h3')->text();
//             $type = str_replace("Phar…", "pharmacie", $type);
//             $address = $crawler->filter('.doctor-details')->text();
//             $address = str_replace(",", " ", $address);
//             $address = str_replace("'", "` ", $address);
//             $mapurl = $crawler->filter('.doctor-details a')->attr('href');
//             $servername = "localhost";
//             $username = "amira";
//             $password = "amira";
//             $dbname = "g_health24";
//             try {
//                 $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//                 // set the PDO error mode to exception
//                 $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                 $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$name%' and pharmacy_address like '%$address%'");
//                 $sql->execute();
//                 if ($sql->rowCount() > 0) {
//                     echo '';
//                 } else {
//                     $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
//                     // set the PDO error mode to exception
//                     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                     $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_type,pharmacy_address,pharmacy_country,map_url,created_at) 
//                             VALUES (0,'$name','$type','$address','Tunsia','$mapurl',now())";
//                     // use exec() because no results are returned
                 
//                     $conn->exec($sql);
//                     echo '#';
//                 }
//                 $conn = null;
//             } catch (Exception $e) {
//                 echo '.';
//             }
//             $conn = null;
//         });
// }
// $loop->run();