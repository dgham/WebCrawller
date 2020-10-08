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

echo 'Loading data from websites....';
$html = file_get_contents('https://tn.kompass.com/a/centres-de-soins-de-longue-duree-et-maisons-de-convalescence/2390051/');
$doc = new \DOMDocument('1.0', 'UTF-8');
// set error level
$internalErrors = libxml_use_internal_errors(true);
$doc->loadHTML($html);
$xpath = new \DOMXpath($doc);
$insUrl = $xpath->query('//li[@class="searchItemLi"]');
$links = [];
foreach ($insUrl as $value) {
    $array = $value->getElementsByTagName("a");
    foreach ($array as $items) {
        $institutionUrl =  $items->getAttribute("href");
        if($institutionUrl == "https://tn.kompass.com/s/chimie-plastique-sante/04/" || $institutionUrl == "https://tn.kompass.com/a/sante/23/"){
            sleep(1);
        }
        else{
        $text = trim(preg_replace("/[\r\n]+/", " ", $items->nodeValue));
        echo '
Downloading: ' . $institutionUrl .'..';
        $html = file_get_contents($institutionUrl);
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
                        $activity = utf8_decode($crawler->filter('.company-activities')->text());
                        $activity = str_replace("'", "`", $activity);
                        $activity = str_replace(",", " ", $activity);
                        $place = utf8_decode($crawler->filter('.spRight')->text());
                        $place = str_replace("'", "`", $place);
                        $place = str_replace(",", " ", $place);
                        // $phoneid = $crawler->filter('input[name="companyTargetId"]')->attr('value');
                        $phone = $crawler->filter('.faxNumber')->text();
                        $website = $crawler->filter('.listWww p a')->attr('href');
                        $description=utf8_decode($crawler->filter('.company-activities')->text());
                        $description = str_replace(",", " ", $description);
                        $description = str_replace("'", "`", $description);
                        $type=$crawler->filter('.blockInterieur td')->extract(['_text']);
                        $type=  utf8_decode(trim($type[2]));
                        if ($type == 'Etablissement Public à Caractère Administratif (E.P.A.)'){
                        $type="public hospital";
                        }
                        else{
                            $type="private hospital";
                        }

                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $arr = [];
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_health_institution where  health_institution_name LIKE '%$name%' and health_institution_address LIKE '%$place%';");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {
                                try {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $sql = "INSERT INTO wp_health_institution (health_institution_id, health_institution_name,health_institution_type,health_institution_country,
                                    health_institution_address,health_institution_activity,health_institution_phone,health_institution_website,health_institution_description,created_at) 
                                    VALUES (0,'$name','$type','Tunisia','$place','$activity','$phone','$website','$description',now());";
                                 $conn->exec($sql);
                                    echo '#';
                                } catch (PDOException $e) {
                                    echo $e->getMessage();
                                }
                            }
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }
                        $conn = null;

                        try {
                            $arr = [];
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_health_institution where  health_institution_name LIKE '%$name%' and health_institution_address LIKE '%$place%';");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {
                                try {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $sql = "INSERT INTO wp_health_institution (health_institution_id, health_institution_name,health_institution_type,health_institution_country,
                                    health_institution_address,health_institution_activity,health_institution_phone,health_institution_description,created_at) 
                                    VALUES (0,'$name',$type,'Tunisia','$place','$activity','$phone','$description',now());";
                                 $conn->exec($sql);
                                    echo '#';
                                } catch (PDOException $e) {
                                    echo $e->getMessage();
                                }
                            }
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }
                        $conn = null;
                    });
                $loop->run();
            }
        }
        }
    }
}
echo'\nDone';
$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
$brower->get('https://psychologie-sante.tn/liste-adresses-et-tel-des-cliniques-en-tunisie/')
    ->then(function (ResponseInterface $response) {
        echo '
Downloading: https://psychologie-sante.tn/liste-adresses-et-tel-des-cliniques-en-tunisie/';
        $crawler = new Crawler((string) $response->getbody());
        $name = $crawler->filter('.column-1')->extract(['_text']);
        $activity = $crawler->filter('.column-2')->extract(['_text']);
        $address = $crawler->filter('.column-3')->extract(['_text']);
        $tel = $crawler->filter('.column-4')->extract(['_text']);
        for ($j = 1; $j < sizeof($name); $j++) {
            $servername = "localhost";
            $username = "amira";
            $password = "amira";
            $dbname = "g_health24";
            $institutionName = $name[$j];
            $institutionType = 'clinical';
            $institutionActivity = $activity[$j];
            $institutionAddress = $address[$j];
            $institutionAddress = str_replace(",", " ", $institutionAddress);
            $institutionTel = $tel[$j];
            try {
                $arr = [];
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = $conn->prepare("SELECT * from wp_health_institution where health_institution_name LIKE '%$institutionName%' ;");
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '';
                } else {
                    $sql = "INSERT INTO wp_health_institution (health_institution_id, health_institution_name,
                 health_institution_type ,health_institution_country,health_institution_address,health_institution_activity,
                 health_institution_fax,created_at)
                 VALUES (0,'$institutionName','$institutionType','Tunsia','$institutionAddress',
                 '$institutionActivity', '$institutionTel',now())";
                    // use exec() because no results are returned
                    $conn->exec($sql);
                    echo '#';
                }
                $conn = null;
            } catch (PDOException $e) {
                echo '';
            }
            $conn = null;
        }
    });
$loop->run();
echo '\nDone';


/////getting clinic data///////
for ($i = 1; $i < 13; $i++) {
    echo '
Downloading: https://annuairepro-tunisie.com/recherche-entreprise-clinique.html?page=' . $i;
    $html = file_get_contents('https://annuairepro-tunisie.com/recherche-entreprise-clinique.html?page=' . $i);
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
            $loop = \React\EventLoop\Factory::create();
            $factory = new \React\MySQL\Factory($loop);
            $brower = new Browser($loop);
            $brower->get($href)
                ->then(function (ResponseInterface $response) {
                    $crawler = new Crawler((string) $response->getbody());
                    $name =  $crawler->filter('.onecolumn.with-search h1')->text();
                    $type = 'clinical';
                    $country = 'Tunisia';
                    $address = $crawler->filter('.address strong')->extract(['_text']);
                    $address = $address[1] . $address[2];
                    $address = str_replace("'", "`", $address);
                    $address = str_replace(",", " ", $address);
                    $pdg = $crawler->filter('.contactinfo .name')->text();
                    $phone = $crawler->filter('.contactinfo .phone')->text();
                    $website = $crawler->filter('.button.orange')->attr('href');
                    $fax = $crawler->filter('.contactinfo .fax')->text();
                    $type='clinical';
                    $servername = "localhost";
                    $username = "amira";
                    $password = "amira";
                    $dbname = "g_health24";
                    try {
                        $arr = [];
                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sql = $conn->prepare("SELECT * from wp_health_institution where health_institution_name LIKE '%$name%' and health_institution_address LIKE '%$address%' ;");
                        $sql->execute();
                        if ($sql->rowCount() > 0) {
                            echo '';
                        } else {
                            $sql = "INSERT INTO wp_health_institution (	health_institution_id, health_institution_name,
                           health_institution_type ,health_institution_director,health_institution_country,health_institution_address,health_institution_phone,
                            health_institution_fax,health_institution_website,created_at)
                             VALUES (0,'$name','$type','$pdg','$country','$address','$phone','$fax','$website',now())";
                            // use exec() because no results are returned
                            $conn->exec($sql);
                            echo '#';
                        }
                        $conn = null;
                    } catch (PDOException $e) {
                        echo '';
                    }
                    $conn = null;
                });
            $loop->run();
        }
    }
}
echo '\nDone';
for ($i = 1; $i < 4; $i++) {
    echo '
Downloading: https://annuairepro-tunisie.com/sous-activite-hopitaux.html?page=' . $i;
    $html = file_get_contents('https://annuairepro-tunisie.com/sous-activite-hopitaux.html?page=' . $i);
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
            $loop = \React\EventLoop\Factory::create();
            $factory = new \React\MySQL\Factory($loop);
            $brower = new Browser($loop);
            $brower->get($href)
                ->then(function (ResponseInterface $response) {
                    $crawler = new Crawler((string) $response->getbody());
                    $name = $crawler->filter('.onecolumn.with-search h1')->text();
                    $type = 'hospital';
                    $country = 'Tunisia';
                    $address = $crawler->filter('.address strong')->extract(['_text']);
                    $address = $address[1] . $address[2];
                    $address = str_replace("'", "`", $address);
                    $address = str_replace(",", " ", $address);
                    $phone = $crawler->filter('.contactinfo .phone')->text();
                    $fax = $crawler->filter('.contactinfo .fax')->text();
                    $servername = "localhost";
                    $username = "amira";
                    $password = "amira";
                    $dbname = "g_health24";
                    try {
                        $arr = [];
                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $sql = $conn->prepare("SELECT * from wp_health_institution where health_institution_name LIKE '%$name%' ;");
                        $sql->execute();
                        if ($sql->rowCount() > 0) {
                            echo '';
                        } else {
                            $sql = "INSERT INTO wp_health_institution (	health_institution_id, health_institution_name,
                           health_institution_type ,health_institution_country,health_institution_address,health_institution_phone,
                            health_institution_fax,created_at)
                             VALUES (0,'$name','$type','$country','$address','$phone','$fax',now())";
                            // use exec() because no results are returned
                            $conn->exec($sql);
                            echo '#';
                        }
                        $conn = null;
                    } catch (PDOException $e) {
                        echo '';
                    }
                    $conn = null;
                });
            $loop->run();
        }
    }
}
echo '\nDone';