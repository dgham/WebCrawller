
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


$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
echo'
https://tunisie-medicale.com/index.php/pharmacie/index/...';
for ($nb = 1; $nb <520; $nb=$nb+20) {

    $brower->get('https://tunisie-medicale.com/index.php/pharmacie/index/' . $nb)
        ->then(function (ResponseInterface $response) {
            $crawler = new Crawler((string) $response->getbody());
            $name = $crawler->filter('li h2 a')->attr('title');
            $name = str_replace(",", " ", $name);
            $name = str_replace("'", "` ", $name);
            $type = $crawler->filter('.team_widget h3')->text();
            $type = str_replace("Phar…", "pharmacie", $type);
            $address = $crawler->filter('.doctor-details')->text();
            $address = str_replace(",", " ", $address);
            $address = str_replace("'", "` ", $address);
            $location = $crawler->filter('.doctor-details a')->attr('href');
            if (str_contains($location,':Address')){
               $latitude=null;
               $longitude=null;
            }
            else{
                $location = str_replace('http://maps.google.com/?q=', '{latitude:', $location);
                    $location = str_replace(',', ',longitude:', $location);
                    $location = $location . '}';
                    $location = explode(',',$location);
                    $location[0]=str_replace('{latitude:','',$location[0]);
                    $location[1]=str_replace('longitude:','',$location[1]);
                    $location[1]=str_replace('}','',$location[1]);
                    $latitude=$location[0];
                    $longitude=$location[1];
                    dump($latitude);
                    dump($longitude);
            }
            $servername = "localhost";
            $username = "amira";
            $password = "amira";
            $dbname = "g_health24";
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$name%' and pharmacy_address like '%$address%'");
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '';
                } else {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                    // set the PDO error mode to exception
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_type,pharmacy_address,pharmacy_country,pharmacy_location,created_at) 
                            VALUES (0,'$name','$type','$address','Tunsia','$location',now())";
                    // use exec() because no results are returned

                    $conn->exec($sql);
                    echo '#';
                }
                $conn = null;
            } catch (Exception $e) {
                echo '.';
            }
            $conn = null;
        });
}
$loop->run();
echo '
Done.';


die;
$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
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
https://www.med.tn/pharmacie/' . $data . '/' . $country[$k].'..';
            $html = file_get_contents('https://www.med.tn/pharmacie/' . $data . '/' . $country[$k]);
            $doc = new \DOMDocument('1.0', 'UTF-8');
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
                            $pharmacy_phone = $crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem a')->text();
                            $location = $crawler->filter('.pf-itempage-maparea.pf-itempage-elements iframe')->attr('src');
                            $location = str_replace('https://maps.google.com/maps?q=', '{latitude:', $location);
                            $location = str_replace('&hl=fr&z=14&output=embed', '', $location);
                            $location = str_replace(',', ',longitude:', $location);
                            $location = $location . '}';
                            $location = explode(',',$location);
                            if(!empty($location)){
                             $location[0]=str_replace('{latitude:','',$location[0]);
                             $location[1]=str_replace('longitude:','',$location[1]);
                             $location[1]=str_replace('}','',$location[1]);
                             $latitude=$location[0];
                             $longitude=$location[1];
                            }else{
                             $latitude=null;
                             $longitude=null;
                            }
                            $picture = $crawler->filter('.docimage a img')->attr('src');
                            $city = $crawler->filter('.pf-breadcrumbs.pf-breadcrumbs-special ul li span')->extract(['_text']);
                            $city = $city[2];
                            $servername = "localhost";
                            $username = "amira";
                            $password = "amira";
                            $dbname = "g_health24";
                            try {
                                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$pharmacy_name%' and pharmacy_address LIKE '%$pharmacy_address%'");
                                $sql->execute();
                                if ($sql->rowCount() > 0) {
                                    echo '';
                                } else {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_type,pharmacy_address,pharmacy_country,
                            pharmacy_city,latitude,longitude,phone,picture,created_at) 
                            VALUES (0,'$pharmacy_name','$pharmacy_type','$pharmacy_address','Tunisia','$city','$latitude','$longitude','$pharmacy_phone','$picture',now())";
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

echo '
Done';

for ($i = 0; $i < 7; $i++) {
    echo '
https://annuairepro-tunisie.com/recherche-entreprise-pharmacie.html?page=' . $i . '...';
    $html = file_get_contents('https://annuairepro-tunisie.com/recherche-entreprise-pharmacie.html?page=' . $i);
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
                    $namepharmacy = Utf8_ansi($crawler->filter('.onecolumn.with-search h1')->text());
                    $namepharmacy = str_replace(",", " ", $namepharmacy);
                    $namepharmacy = str_replace("'", "` ", $namepharmacy);
                    $adresspharmacy = $crawler->filter('.address strong')->extract(['_text']);
                    $adresspharmacy = $adresspharmacy[1];
                    $adresspharmacy = str_replace(',', ' ', $adresspharmacy);
                    $adresspharmacy = str_replace("'", " `", $adresspharmacy);
                    $adresspharmacy = Utf8_ansi($adresspharmacy);
                    $phonepharmacy = $crawler->filter('ul .phone')->text();
                    $phonepharmacy = '+216 ' . $phonepharmacy;
                    $location = '{latitude:0,longitude:0}';
                    $managerpharmacy = Utf8_ansi($crawler->filter('.contactinfo .name')->text());
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
                            $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_address,pharmacy_country,phone,pharmacy_location,pharmacy_manager,created_at) 
                                VALUES (0,'$namepharmacy','$adresspharmacy','Tunisia','$phonepharmacy','$location','$managerpharmacy',now())";
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

echo '
Done.
';

$loop = \React\EventLoop\Factory::create();
$factory = new \React\MySQL\Factory($loop);
$brower = new Browser($loop);
echo '
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
            $location = '{latitude:0,longitude:0}';
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

                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = $conn->prepare("SELECT * from wp_pharmacy where pharmacy_name LIKE '%$namepharmacy%' and phone like '%$phone%'");
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '';
                } else {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO wp_pharmacy (pharmacy_id, pharmacy_name,pharmacy_address,pharmacy_country,pharmacy_location,phone,created_at) 
                            VALUES (0,'$namepharmacy','$addresspharmacy','Tunisia','$location','$phone',now())";
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
