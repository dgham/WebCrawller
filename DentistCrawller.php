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
echo 'Loading data';
try {
    for ($i = 0; $i < 2841; $i = $i + 20) {
        echo '
Fetching: https://tunisie-medicale.com/index.php/dentiste/index/' . $i . ' ...';
        $html = file_get_contents('https://tunisie-medicale.com/index.php/dentiste/index/' . $i);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        $xpath = new \DOMXpath($doc);
        $articles = $xpath->query('//h2');
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
                        $name = utf8_decode($crawler->filter('.big_team_widget h2 span')->text());
                        $name = str_replace(",", " ", $name);
                        $name = str_replace("'", "` ", $name);
                        $country = 'Tunisia';
                        $region =  utf8_decode($crawler->filter('p[itemprop=addressLocality]')->text());
                        $region = str_replace(",", " ", $region);
                        $region = str_replace("'", "` ", $region);
                        $location = $crawler->filter('script')->extract(['_text']);
                        $content = str_replace("\n", '', $response->getbody());
                        $content = str_replace("\r", '', $content);
                        $content = str_replace("\t", '', $content);
                        preg_match_all('`lat: (.*),                    lng: (.*)                };`', $content, $matches);
                        if ($matches[0] == null) {
                            $lat = '0';
                            $long = '0';
                            $location = $lat . ',' . $long;
                        } else {
                            $lat = $matches[1][0];
                            $long = $matches[2][0];
                            $location = $lat . ',' . $long;
                        }

                        $specility = 'Dentist';
                        $address = utf8_decode($crawler->filter('.contact_details_1 li a')->text());
                        $address = str_replace(",", " ", $address);
                        $address = str_replace("'", "` ", $address);
                        $phone = $crawler->filter('a[itemprop=telephone]')->attr('href');
                        $phone = str_replace("tel:", "", $phone);
                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            // set the PDO error mode to exception
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_dentist where dentist_name LIKE '%$name%' and dentist_address like '%$address%'");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {
                                $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                                // set the PDO error mode to exception
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $sql = "INSERT INTO wp_dentist (dentist_id, dentist_name,dentist_speciality,dentist_address,dentist_country,
                                dentist_region,dentist_location,dentist_phone,created_at) 
                            VALUES (0,'$name','$specility','$address','$country','$region','$location','$phone',now())";
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
    echo 'Done\n';
} catch (Exception $e) {
    echo 'error getting data';
}
