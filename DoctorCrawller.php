<?php

use Clue\React\Buzz\Browser;
use Doctrine\DBAL\Types\JsonType;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

require __DIR__ . '/vendor/autoload.php';

echo "Loading data....";
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

// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// $brower->get('https://www.med.tn/medecin/cardiologue/ariana/dr-ahmed-wajih-bouladi-164368.html')
//     ->then(function (ResponseInterface $response) {
//         $crawler = new Crawler((string) $response->getbody());
// $location=$crawler->filter('.pftabcontainer.pf-itempagedetail-element .hidden-xs a')->attr('href');
// $location= str_replace('https://www.google.com/maps?saddr&daddr=','{lantitude:',$location);
//     $location=str_replace(',',',longitude:',$location);
//     $location=$location .'}';
//     dump($location);


//     });
//     $loop->run();
//     die;
$repeat=0;

while ($repeat<10){
for($i=0;$i<4;$i++){
    echo '
Fetching: https://tunisie-medicale.com/index.php/docteur/index/'.$i.' ...';
    $html = file_get_contents('https://www.med.tn/annuaire-medecins-tunisie/' . $i);
    $doc = new \DOMDocument('1.0', 'UTF-8');
    // set error level
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    $xpath = new \DOMXpath($doc);
    $articles = $xpath->query('//div[@class="card-doctor-header"]');
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
                    $name = trim(Utf8_ansi($crawler->filter('.doctitle')->text()));
                    $name = strtolower(str_replace(",", " ", $name));
                    $speciality = trim(Utf8_ansi($crawler->filter('.docsubtitle')->text()));
                    $speciality=strtoupper($speciality);
                    $speciality=str_replace("'",'',$speciality);
                    $speciality=str_replace(",",'',$speciality);
                    $address = strtolower(trim(Utf8_ansi($crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem')->text())));
                    $address=str_replace(","," ",$address);
                    $address=str_replace("'","`",$address);
                    $country='Tunisia';
                    $phone=trim($crawler->filter('meta[itemprop="telephone"]')->attr('content'));
                    $exercie_mod=$crawler->filter('.pfdetail-ftext.widget_tag_cloud a')->extract(['_text']);
                    $exercie= implode(",", $exercie_mod);
                    $bio=trim(Utf8_ansi($crawler->filter('.pfdetailitem-subelement.pf-onlyitem.clearfix .clearfix')->text()));
                    $bio=str_replace("'",'',$bio);
                    $bio=str_replace(",",'',$bio);
                    $location=$crawler->filter('.pftabcontainer.pf-itempagedetail-element .hidden-xs a')->attr('href');
                    $location= str_replace('https://www.google.com/maps?saddr&daddr=','{lantitude:',$location);
                    $location=str_replace(',',',longitude:',$location);
                    $location=$location .'}';
                    $picture=$crawler->filter('.doctitle img')->attr('src');

                    $servername = "localhost";
                                        $username = "amira";
                                        $password = "amira";
                                        $dbname = "g_health24";
                                        try {
                                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                            // set the PDO error mode to exception
                                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                            $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%' and doctor_address like '%$address%'");
                                            $sql->execute();
                                            if ($sql->rowCount() > 0) {
                                                echo '';
                                            } else {
                                                try{
                                                   
                                                $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
                                                // set the PDO error mode to exception
                                              
                                                $sql = "INSERT INTO wp_doctor (id_doctor, doctor_name,doctor_speciality,doctor_bio,doctor_address,doctor_country,doctor_location,
                                                doctor_phone,exercie_mode,created_at,picture) VALUES (0,'$name','$speciality','$bio','$address','$country','$location','$phone','$exercie_mod',now(),'$picture')";
                                                // use exec() because no results are returned                          
                                                $conn->exec($sql);
                                                
                                                echo '#';
                                                }catch(PDOException $e){
                                                    echo $e->getMessage();
                                                }
                                            }
                                            $conn = null;
                                        } catch (Exception $e) {
                                            echo $e->getMessage();
                                        }
                                        $conn = null;
                
                
                });
                $loop->run();
                     }
    }
}
    $repeat++;
}
 



    
// try{
// for ($i = 0; $i < 2960; $i = $i + 20) {
//     echo '
// Fetching: https://tunisie-medicale.com/index.php/docteur/index/'.$i .' ...';
//     $html = file_get_contents('https://tunisie-medicale.com/index.php/docteur/index/' . $i);
//     $doc = new \DOMDocument('1.0', 'UTF-8');
//     // set error level
//     $internalErrors = libxml_use_internal_errors(true);
//     $doc->loadHTML($html);
//     $xpath = new \DOMXpath($doc);
//     $articles = $xpath->query('//h2');
//     $links = [];
//     foreach ($articles as $container) {
//         $arr = $container->getElementsByTagName("a");
//         foreach ($arr as $item) {
//             $href =  $item->getAttribute("href");
//             $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
//             $loop = \React\EventLoop\Factory::create();
//             $factory = new \React\MySQL\Factory($loop);
//             $brower = new Browser($loop);
//             $brower->get($href)
//                 ->then(function (ResponseInterface $response) {
//                     $crawler = new Crawler((string) $response->getbody());
//                     $name = Utf8_ansi($crawler->filter('.big_team_widget h2 span')->text());
//                     $name = str_replace(",", " ", $name);
//                     $name = str_replace("'", "` ", $name);
//                     $country = 'Tunisia';
//                     $region =  trim(Utf8_ansi($crawler->filter('p[itemprop=addressLocality]')->text()));
//                     $region = str_replace(",", " ", $region);
//                     $region = str_replace("'", "` ", $region);
//                     $content = str_replace("\n", '', $response->getbody());
//                     $content = str_replace("\r", '', $content);
//                     $content = str_replace("\t", '', $content);
//                     preg_match_all('`lat: (.*),                    lng: (.*)                };`', $content, $matches);         
//                     if ($matches[0] == null){
//                         $lat = '0';
//                         $long = '0';
//                         $location='{latitude:'.$lat.',longitude:'.$long.'}';
//                     }else{
//                         $lat = $matches[1][0];
//                         $long = $matches[2][0];
//                         $location='{latitude:'.$lat.',longitude:'.$long.'}';
//                     }
//                     $specility = utf8_decode($crawler->filter('h3[itemprop=medicalSpecialty]')->text());
//                     $address = Utf8_ansi($crawler->filter('.contact_details_1 li a')->text());
//                     $address = str_replace(",", " ", $address);
//                     $address = strtolower(str_replace("'", "` ", $address));
//                     $phone = $crawler->filter('a[itemprop=telephone]')->attr('href');
//                     $phone = strval(str_replace("tel:", "", $phone));
//                     $servername = "localhost";
//                     $username = "amira";
//                     $password = "amira";
//                     $dbname = "g_health24";
//                     try {
//                         $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//                         // set the PDO error mode to exception
//                         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                         $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%' and doctor_address like '%$address%'");
//                         $sql->execute();
//                         if ($sql->rowCount() > 0) {
//                             echo '';
//                         } else {
//                             try{
//                             $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
//                             // set the PDO error mode to exception
//                             $sql = "INSERT INTO wp_doctor (id_doctor, doctor_name,doctor_speciality,doctor_address,doctor_country,doctor_location,
//                             doctor_region,doctor_phone,created_at) VALUES (0,'$name','$specility','$address','$country','$location','$region','$phone',now())";
//                             // use exec() because no results are returned                          
//                             $conn->exec($sql);
//                             echo '#';
//                             }catch(PDOException $e){
//                                 echo $e->getMessage();
//                             }
//                         }
//                         $conn = null;
//                     } catch (Exception $e) {
//                         echo '';
//                     }
//                     $conn = null;
//                 });
//             $loop->run();
//         }
//     }
// }
// echo '\n Done';
// } catch (Exception $e) {
// echo 'error getting data';
// }



// /// definition de longitude et latitude /////
// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// $brower->get('https://tunisie-medicale.com/index.php/docteur/2999-bouraoui-hichem-medecine-generale-ariana')
//     ->then(function (ResponseInterface $response) {
//         $crawler = new Crawler((string) $response->getbody());
//         $location = $crawler->filter('kk')->extract(['_text']);
//         $content = str_replace("\n", '', $response->getbody());
//         $content = str_replace("\r", '', $content);
//         $content = str_replace("\t", '', $content);
//         preg_match_all('`lat: (.*),                    lng: (.*)                };`', $content, $matches);
//         $lat = $matches[1][0];
//         $long = $matches[2][0];

//     });
// $loop->run();
// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// $country = array(
//     'tunis', 'sfax', 'sousse', 'monastir', 'ariana', 'ben-arous', 'nabeul', 'mahdia',
//     'kairouan', 'bizerte', 'medenine', 'mannouba', 'gabes', 'beja', 'gafsa', 'jendouba', 'le-Kef', 'sidi-bouzid',
//     'kasserine', 'zaghouan', 'siliana', 'kebili', 'tataouine', 'tozeur',
// );
// for ($k = 0; $k <= 23; $k++) {
//     for ($i = 1; $i <= 100; $i++) {
//         $brower->get('http://www.ordre-medecins.org.tn/components/com_annuaires/annuaires.php?ville=' . $country[$k] . '&page=' . $i)
//             ->then(function (ResponseInterface $response) {
//                 $crawler = new Crawler((string) $response->getbody());
//                 $name = $crawler->filter('.col-ann-9 .table tr td')->extract(['_text']);
//                 if ((sizeof($name)) == 50) {
//                     for ($j = 0; $j <= 45; $j = $j + 5) {
//                         $servername = "localhost";
//                         $username = "amira";
//                         $password = "amira";
//                         $dbname = "g_health24";
//                         try {
//                             $arr = [];
//                             $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//                             // set the PDO error mode to exception
//                             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                             $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name[$j]%' ;");
//                             $sql->execute();
//                             if ($sql->rowCount() > 0) {
//                                 echo '';
//                             } else {
//                                 $doctorname = utf8_decode($name[$j]);
//                                 $speciality = utf8_decode($name[$j + 1]);
//                                 $exercie_mode = utf8_decode($name[$j + 2]);
//                                 $address = utf8_decode($name[$j + 3]);
//                                 $phone = utf8_decode($name[$j + 4]);
//                                 $sql = "INSERT INTO wp_doctor (id_doctor, doctor_name,doctor_speciality,exercie_mode,doctor_address,doctor_phone,country,created_at)
//         VALUES (0,'$doctorname','$speciality','$exercie_mode','$address','$phone','Tunsia',now())";
//                                 // use exec() because no results are returned
//                                 $conn->exec($sql);
//                             }
//                             $conn = null;
//                         } catch (PDOException $e) {
//                             echo '';
//                         }
//                         $conn = null;
//                     }
//                 }
//             });
//     }
// }

// $loop->run();

// $loop = \React\EventLoop\Factory::create();
// $factory = new \React\MySQL\Factory($loop);
// $brower = new Browser($loop);
// for ($i = 1; $i <= 100; $i++) {
//     $brower->get('https://www.med.tn/annuaire-medecins-tunisie')
//         ->then(function (ResponseInterface $response) {
//             $crawler = new Crawler((string) $response->getbody());
//             $name = utf8_decode($crawler->filter('.card-doctor-header .card-doctor-info .practitioner-name')->text());
//             $speciality = utf8_decode($crawler->filter('.card-doctor-header .card-doctor-info .practitioner-speciality')->text());
//             $bio = utf8_decode($crawler->filter('.card-doctor-header .card-doctor-info .practitioner-bio')->text());
//             $address = utf8_decode($crawler->filter('.card-doctor-header .card-doctor-info .practitioner-address p')->text());
//             $servername = "localhost";
//             $username = "amira";
//             $password = "amira";
//             $dbname = "g_health24";
//             try {
//                 $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//                 // set the PDO error mode to exception
//                 $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                 $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%' ;");
//                 $sql->execute();
//                 if ($sql->rowCount() > 0) {
//                     echo '';
//                 } else {
//                     try {
//                         $sql = "INSERT INTO wp_doctor (id_doctor, doctor_name,doctor_speciality,doctor_address,doctor_bio,country,created_at)
//                VALUES (0,'$name','$speciality','$address','$bio','Tunisa',now())";
//                         // use exec() because no results are returned
//                         $conn->exec($sql);
//                     } catch (PDOException $e) {
//                         echo '';
//                     }
//                 }
//                 $conn = null;
//             } catch (PDOException $e) {
//                 echo '';
//             }
//             $conn = null;
//         });
// }
// $loop->run();


// $url = 'https://api.keeplyna.com/api/tc_auth/specialities/';
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
// $html = curl_exec($ch);
// curl_close($ch);
// $specialitys = json_decode(($html), true);
// for ($id = 0; $id < 27; $id++) {
//     $idspec = $specialitys[$id]['pk'];
//     $name_speciality = $specialitys[$id]['name_l1'];
//     $data = array('speciality_id' => $idspec);
//     $url = 'https://api.keeplyna.com/api/tc_auth/doctor/new_list_doctor/';
//     $opts = array('http' => array(
//         'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
//             "Content-Length: " . strlen(http_build_query($data)) . "\r\n" . "User-Agent:MyAgent/1.0\r\n", 'method'  => 'POST',
//         'content' => http_build_query($data),
//     ));
//     $contexts = stream_context_create($opts);
//     $result = file_get_contents($url, false, $contexts);
//     if ($result === FALSE) {
//         echo 'error';
//     }
//     $doctorData = json_decode(($result), true);

//     $servername = "localhost";
//     $username = "amira";
//     $password = "amira";
//     $dbname = "g_health24";
//     for ($i = 0; $i < sizeof($doctorData['results']); $i++) {
//         $name = utf8_decode($doctorData['results'][$i]['doc_profile']['first_name'] . ' ' . $doctorData['results'][$i]['doc_profile']['last_name']);
//         $bio = utf8_decode($doctorData['results'][$i]['dr_biography_l1']);
//         $picture = $doctorData['results'][$i]['dr_pic'];
//         $country = 'Tunisia';
//         if (sizeof($doctorData['results'][$i]['doc_prices'][0]) > 0) {
//             $price = $doctorData['results'][$i]['doc_prices'][0]['amount'] . $doctorData['results'][$i]['doc_prices'][0]['currency'];
//         } else {
//             $price = null;
//         }
//         $speciality = utf8_decode($name_speciality);

//         try {
//             $arr = [];
//             $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//             $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%' and doctor_bio like '%$bio%' ;");
//             $sql->execute();
//             if ($sql->rowCount() > 0) {
//                 echo '';
//             } else {
//                 $sql = "INSERT INTO wp_doctor (id_doctor, doctor_name,doctor_speciality,doctor_bio,country,created_at,picture,doctor_price)
//                 VALUES (0,'$name','$speciality','$bio','$country',now(),'$picture','$price')";
//                 $conn->exec($sql);
//             }
//             $conn = null;
//         } catch (PDOException $e) {
//             echo '';
//         }
//         $conn = null;
//     }
// }
