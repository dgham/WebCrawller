<?php

header('Content-Type: text/html; charset=UTF-8');



use Clue\React\Buzz\Browser;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\ORM\Query\AST\Functions\LengthFunction;
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
        "\u00e8" => "E",
        "\u00e9" => "E",
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
        "\u00ff" => "ÿ",
        "\u00e9" => "é",
        ""
    );
    return strtr($valor, $utf8_ansi2);
}

mb_internal_encoding('UTF-8');



$repeat = 0;

while ($repeat < 10) {
    for ($i = 0; $i < 4; $i++) {
        echo '
Fetching: https://www.med.tn/annuaire-medecins-tunisie/' . $i . ' ...';
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
                $href = $item->getAttribute("href");
                $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
                $loop = \React\EventLoop\Factory::create();
                $factory = new \React\MySQL\Factory($loop);
                $brower = new Browser($loop);
                $brower->get($href)
                    ->then(function (ResponseInterface $response) {
                        $crawler = new Crawler((string) $response->getbody());
                        $name = Utf8_ansi($crawler->filter('.doctitle')->text());
                        $name = str_replace(",", " ", $name);
                        $speciality = mb_strtolower($crawler->filter('.docsubtitle')->text());
                        $speciality = str_replace("'", '', $speciality);
                        $speciality = str_replace(",", '', $speciality);
                        $address = mb_strtolower(($crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem')->text()));
                        $address = str_replace(",", " ", $address);
                        $address = str_replace("'", "`", $address);
                        $country = 'Tunisia';
                        $phone = trim($crawler->filter('meta[itemprop="telephone"]')->attr('content'));
                        $exercie_mod = $crawler->filter('.pfdetail-ftext.widget_tag_cloud a')->extract(['_text']);
                        $exercie = implode("/", $exercie_mod);
                        $exercie = str_replace(",", " ", $exercie);
                        $exercie = str_replace("'", "`", $exercie);
                        $exercie = mb_strtolower($exercie);
                        $cabinet = $crawler->filter('.imgs-grid-image .image-wrap img')->extract(['_text']);
                        $bio = $crawler->filter('.pfdetailitem-subelement.pf-onlyitem.clearfix .clearfix')->extract(['_text']);
                        if (sizeof($bio) > 0) {
                            $bio[0] = str_replace("'", "`", $bio[0]);
                            $bio[0] = str_replace(",", " ", $bio[0]);
                            $bio[0] = '<h2 class="y4vuei-3 cBtaUV">' . $name . '</h2>' . $bio[0]. ' '.$exercie;
                            $bio_doctor = $bio[0];
                        } else {
                            $bio_doctor = null;
                        }
                        $region = $crawler->filter('span[itemprop=name]')->extract(['_text']);
                        $region = $region[2];
                        $region = str_replace(',', ' ', $region);
                        $region = str_replace("'", "`", $region);
                        $location = $crawler->filter('.pftabcontainer.pf-itempagedetail-element .hidden-xs a')->attr('href');
                        $location = str_replace('https://www.google.com/maps?saddr&daddr=', '{latitude:', $location);
                        $location = str_replace(',', ',longitude:', $location);
                        $location = $location . '}';
                        $location = explode(',', $location);
                        if (!empty($location)) {
                            $location[0] = str_replace('{latitude:', '', $location[0]);
                            $location[1] = str_replace('longitude:', '', $location[1]);
                            $location[1] = str_replace('}', '', $location[1]);
                            $latitude = $location[0];
                            $longitude = $location[1];
                        } else {
                            $latitude = null;
                            $longitude = null;
                        }
                        $picture = strval($crawler->filter('.doctitle img')->attr('src'));
                        ///getting working time of doctors///
                        $day = $crawler->filter('.pfdetailitem-subelement.opening.pf-onlyitem.clearfix .pf-ftitle')->extract(['_text']);
                        $time = $crawler->filter('.pfdetailitem-subelement.opening.pf-onlyitem.clearfix .pfdetail-ftext')->extract(['_text']);
                        if (sizeof($day) > 1) {
                            $morningStart = str_replace("\t", "", substr($time[0], 1, 5));
                            $morningStart = str_replace(" ", "", $morningStart);
                            $morningStart = str_replace(",", "", $morningStart);
                            $morningClose = str_replace("\t", "", substr($time[0], 6, 10));
                            $morningClose = str_replace("\n", "", $morningClose);
                            $morningClose = str_replace(",", "", $morningClose);
                            $morningClose = str_replace("    ", "", $morningClose);
                            $eveningStart = str_replace("\t", "", substr($time[0], 55, -5));
                            $eveningStart = str_replace(" ", "", $eveningStart);
                            $eveningStart = str_replace("\n", "", $eveningStart);
                            $eveningStart = str_replace(",", "", $eveningStart);
                            $eveningClose = str_replace("\t", "", substr($time[0], -5, 5));
                            $eveningClose = str_replace(" ", "", $eveningClose);
                            $eveningClose = str_replace(",", "", $eveningClose);
                            $eveningClose = str_replace("\n", "", $eveningClose);
                            if (strlen($eveningStart) > 0) {
                                $monday_timing = $morningStart . '-' . $morningClose . '/' . $eveningStart . '-' . $eveningClose;
                            } else {
                                $monday_timing = $morningStart . '-' . $morningClose;
                            }
                        } else {
                            $monday_timing = " ";
                        }
                        if (sizeof($day) > 2) {
                            $tuesdaymorningStart = str_replace("\t", "", substr($time[1], 1, 5));
                            $tuesdaymorningStart = str_replace(" ", "", $tuesdaymorningStart);
                            $tuesdaymorningStart = str_replace(",", "", $tuesdaymorningStart);
                            $tuesdaymorningClose = str_replace("\t", "", substr($time[1], 6, 10));
                            $tuesdaymorningClose = str_replace("\n", "", $tuesdaymorningClose);
                            $tuesdaymorningClose = str_replace(",", "", $tuesdaymorningClose);
                            $tuesdaymorningClose = str_replace("    ", "", $tuesdaymorningClose);
                            $tuesdayeveningStart = str_replace("\t", "", substr($time[1], 55, -5));
                            $tuesdayeveningStart = str_replace(" ", "", $tuesdayeveningStart);
                            $tuesdayeveningStart = str_replace("\n", "", $tuesdayeveningStart);
                            $tuesdayeveningStart = str_replace(",", "", $tuesdayeveningStart);
                            $tuesdayeveningClose = str_replace("\t", "", substr($time[1], -5, 5));
                            $tuesdayeveningClose = str_replace(" ", "", $tuesdayeveningClose);
                            $tuesdayeveningClose = str_replace(",", "", $tuesdayeveningClose);
                            $tuesdayeveningClose = str_replace("\n", "", $tuesdayeveningClose);

                            if (strlen($tuesdayeveningStart) > 0) {
                                $tuesday_timing = $tuesdaymorningStart . '-' . $tuesdaymorningClose . '/' . $tuesdayeveningStart . '-' . $tuesdayeveningClose;
                            } else {
                                $tuesday_timing = $tuesdaymorningStart . '-' . $tuesdaymorningClose;
                            }
                        } else {
                            $tuesday_timing = " ";
                        }
                        if (sizeof($day) > 3) {
                            $WednesdaymorningStart = str_replace("\t", "", substr($time[2], 1, 5));
                            $WednesdaymorningStart = str_replace(" ", "", $WednesdaymorningStart);
                            $WednesdaymorningStart = str_replace(",", "", $WednesdaymorningStart);
                            $WednesdaymorningClose = str_replace("\t", "", substr($time[2], 6, 10));
                            $WednesdaymorningClose = str_replace("\n", "", $WednesdaymorningClose);
                            $WednesdaymorningClose = str_replace(",", "", $WednesdaymorningClose);
                            $WednesdaymorningClose = str_replace("    ", "", $WednesdaymorningClose);
                            $WednesdayeveningStart = str_replace("\t", "", substr($time[2], 55, -5));
                            $WednesdayeveningStart = str_replace(" ", "", $WednesdayeveningStart);
                            $WednesdayeveningStart = str_replace("\n", "", $WednesdayeveningStart);
                            $WednesdayeveningStart = str_replace(",", "", $WednesdayeveningStart);
                            $WednesdayeveningClose = str_replace("\t", "", substr($time[2], -5, 5));
                            $WednesdayeveningClose = str_replace(" ", "", $WednesdayeveningClose);
                            $WednesdayeveningClose = str_replace(",", "", $WednesdayeveningClose);
                            $WednesdayeveningClose = str_replace("\n", "", $WednesdayeveningClose);

                            if (strlen($WednesdayeveningStart) > 0) {
                                $Wednesday_timing = $WednesdaymorningStart . '-' . $WednesdaymorningClose . '/' . $WednesdayeveningStart . '-' . $WednesdayeveningClose;
                            } else {
                                $Wednesday_timing = $WednesdaymorningStart . '-' . $WednesdaymorningClose;
                            }
                        } else {
                            $Wednesday_timing = " ";
                        }
                        if (sizeof($day) >= 4) {
                            $ThursdaymorningStart = str_replace("\t", "", substr($time[3], 1, 5));
                            $ThursdaymorningStart = str_replace(" ", "", $ThursdaymorningStart);
                            $ThursdaymorningStart = str_replace(",", "", $ThursdaymorningStart);
                            $ThursdaymorningClose = str_replace("\t", "", substr($time[3], 6, 10));
                            $ThursdaymorningClose = str_replace("\n", "", $ThursdaymorningClose);
                            $ThursdaymorningClose = str_replace(",", "", $ThursdaymorningClose);
                            $ThursdaymorningClose = str_replace("    ", "", $ThursdaymorningClose);
                            $ThursdayeveningStart = str_replace("\t", "", substr($time[3], 55, -5));
                            $ThursdayeveningStart = str_replace(" ", "", $ThursdayeveningStart);
                            $ThursdayeveningStart = str_replace("\n", "", $ThursdayeveningStart);
                            $ThursdayeveningStart = str_replace(",", "", $ThursdayeveningStart);
                            $ThursdayeveningClose = str_replace("\t", "", substr($time[3], -5, 5));
                            $ThursdayeveningClose = str_replace(" ", "", $ThursdayeveningClose);
                            $ThursdayeveningClose = str_replace(",", "", $ThursdayeveningClose);
                            $ThursdayeveningClose = str_replace("\n", "", $ThursdayeveningClose);

                            if (strlen($ThursdayeveningStart) > 0) {
                                $Thursday_timing = $ThursdaymorningStart . '-' . $ThursdaymorningClose . '/' . $ThursdayeveningStart . '-' . $ThursdayeveningClose;
                            } else {
                                $Thursday_timing = $ThursdaymorningStart . '-' . $ThursdaymorningClose;
                            }
                        } else {
                            $Thursday_timing = " ";
                        }
                        if (sizeof($day) >= 5) {
                            $FridaymorningStart = str_replace("\t", "", substr($time[4], 1, 5));
                            $FridaymorningStart = str_replace(" ", "", $FridaymorningStart);
                            $FridaymorningStart = str_replace(",", "", $FridaymorningStart);
                            $FridaymorningClose = str_replace("\t", "", substr($time[4], 6, 10));
                            $FridaymorningClose = str_replace("\n", "", $FridaymorningClose);
                            $FridaymorningClose = str_replace(",", "", $FridaymorningClose);
                            $FridaymorningClose = str_replace("    ", "", $FridaymorningClose);
                            $FridayeveningStart = str_replace("\t", "", substr($time[4], 55, -5));
                            $FridayeveningStart = str_replace(" ", "", $FridayeveningStart);
                            $FridayeveningStart = str_replace("\n", "", $FridayeveningStart);
                            $FridayeveningStart = str_replace(",", "", $FridayeveningStart);
                            $FridayeveningClose = str_replace("\t", "", substr($time[4], -5, 5));
                            $FridayeveningClose = str_replace(" ", "", $FridayeveningClose);
                            $Friday = str_replace(",", "", $FridayeveningClose);
                            $FridayeveningClose = str_replace("\n", "", $FridayeveningClose);
                            if (strlen($FridayeveningStart) > 0) {
                                $Friday_timing = $FridaymorningStart . '-' . $FridaymorningClose . '/' . $FridayeveningStart . '-' . $FridayeveningClose;
                            } else {
                                $Friday_timing = $FridaymorningStart . '-' . $FridaymorningClose;
                            }
                        } else {
                            $Friday_timing = " ";
                        }
                        if (sizeof($day) === 6) {
                            $SaturdaymorningStart = str_replace("\t", "", substr($time[5], 1, 5));
                            $SaturdaymorningStart = str_replace(" ", "", $SaturdaymorningStart);
                            $SaturdaymorningStart = str_replace(",", "", $SaturdaymorningStart);
                            $SaturdaymorningClose = str_replace("\t", "", substr($time[5], 6, 10));
                            $SaturdaymorningClose = str_replace("\n", "", $SaturdaymorningClose);
                            $SaturdaymorningClose = str_replace(",", "", $SaturdaymorningClose);
                            $SaturdaymorningClose = str_replace("    ", "", $SaturdaymorningClose);
                            $SaturdayeveningStart = str_replace("\t", "", substr($time[5], 55, -5));
                            $SaturdayeveningStart = str_replace(" ", "", $SaturdayeveningStart);
                            $SaturdayeveningStart = str_replace("\n", "", $SaturdayeveningStart);
                            $SaturdayeveningStart = str_replace(",", "", $SaturdayeveningStart);
                            $SaturdayeveningClose = str_replace("\t", "", substr($time[5], -5, 5));
                            $SaturdayeveningClose = str_replace(" ", "", $SaturdayeveningClose);
                            $SaturdayeveningClose = str_replace(",", "", $SaturdayeveningClose);
                            $SaturdayeveningClose = str_replace("\n", "", $SaturdayeveningClose);
                            if (strlen($SaturdayeveningStart) > 0) {
                                $Saturday_timing = $SaturdaymorningStart . '-' . $SaturdaymorningClose . '/' . $SaturdayeveningStart . '-' . $SaturdayeveningClose;
                            } else {
                                $Saturday_timing = $SaturdaymorningStart . '-' . $SaturdaymorningClose;
                            }
                        } else {
                            $Saturday_timing = " ";
                        }
                        $name_doctor_maj = ucfirst($name);
                        $doctor_min = mb_strtolower($name);
                        $doctor_city = $region . '|tunisa';
                        // insert into database //
                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8;", $username, $password);
                            $conn->query("SET NAMES 'utf8'");
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%';");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {

                                $sql = "INSERT INTO wp_doctor (id_doctor,doctor_min, doctor_name,status,coments_status,doctor_speciality,doctor_bio,doctor_address,doctor_country,latitude,longitude,
                                               doctor_region, doctor_phone,exercie_mode,post_author,ping_status,featuredItem,created_at,picture,opening_monday,opening_Tuesday,opening_Wednesday,
                                               opening_Thursday,opening_Friday,opening_Saturday) VALUES (0,'$doctor_min','$name_doctor_maj','publish','open'
                                               ,'$speciality','$bio_doctor','$address','$country',
                                               '$latitude','$longitude','$doctor_city','$phone','$exercie',1,1,1,now(),'$picture','$monday_timing','$tuesday_timing',
                                               '$Wednesday_timing','$Thursday_timing','$Friday_timing','$Saturday_timing')";
                                $conn->exec($sql);
                                echo '#';
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
    $repeat++;
}


$repeat = 0;

while ($repeat < 10) {
    for ($i = 0; $i < 4; $i++) {
        echo '
Fetching: https://www.med.tn/annuaire-medecins-tunisie/' . $i . ' ...';
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
                $href = $item->getAttribute("href");
                $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
                $loop = \React\EventLoop\Factory::create();
                $factory = new \React\MySQL\Factory($loop);
                $brower = new Browser($loop);
                $brower->get($href)
                    ->then(function (ResponseInterface $response) {
                        $crawler = new Crawler((string) $response->getbody());
                        $name = Utf8_ansi($crawler->filter('.doctitle')->text());
                        $name = str_replace(",", " ", $name);
                        $speciality = mb_strtolower($crawler->filter('.docsubtitle')->text());
                        $speciality = str_replace("'", '', $speciality);
                        $speciality = str_replace(",", '', $speciality);
                        $address = mb_strtolower(($crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem')->text()));
                        $address = str_replace(",", " ", $address);
                        $address = str_replace("'", "`", $address);
                        $country = 'Tunisia';
                        $phone = trim($crawler->filter('meta[itemprop="telephone"]')->attr('content'));
                        $exercie_mod = $crawler->filter('.pfdetail-ftext.widget_tag_cloud a')->extract(['_text']);
                        $exercie = implode("/", $exercie_mod);
                        $exercie = str_replace(",", " ", $exercie);
                        $exercie = str_replace("'", "`", $exercie);
                        $exercie = mb_strtolower($exercie);
                        $cabinet = $crawler->filter('.imgs-grid-image .image-wrap img')->extract(['_text']);
                        $bio = $crawler->filter('.pfdetailitem-subelement.pf-onlyitem.clearfix .clearfix')->extract(['_text']);
                        if (sizeof($bio) > 0) {
                            $bio[0] = str_replace("'", "`", $bio[0]);
                            $bio[0] = str_replace(",", " ", $bio[0]);
                            $bio[0] = '<h2 class="y4vuei-3 cBtaUV">' . $name . '</h2>' . $bio[0]. ' '.$exercie;
                            $bio_doctor = $bio[0];
                        } else {
                            $bio_doctor = null;
                        }
                        

                        $region = $crawler->filter('span[itemprop=name]')->extract(['_text']);
                        $region = $region[2];
                        $region = str_replace(',', ' ', $region);
                        $region = str_replace("'", "`", $region);


                        $location = $crawler->filter('.pftabcontainer.pf-itempagedetail-element .hidden-xs a')->attr('href');
                        $location = str_replace('https://www.google.com/maps?saddr&daddr=', '{latitude:', $location);
                        $location = str_replace(',', ',longitude:', $location);
                        $location = $location . '}';
                        $location = explode(',', $location);
                        if (!empty($location)) {
                            $location[0] = str_replace('{latitude:', '', $location[0]);
                            $location[1] = str_replace('longitude:', '', $location[1]);
                            $location[1] = str_replace('}', '', $location[1]);
                            $latitude = $location[0];
                            $longitude = $location[1];
                        } else {
                            $latitude = null;
                            $longitude = null;
                        }
                        $picture = strval($crawler->filter('.doctitle img')->attr('src'));

                        ///getting working time of doctors///
                        $day = $crawler->filter('.pfdetailitem-subelement.opening.pf-onlyitem.clearfix .pf-ftitle')->extract(['_text']);
                        $time = $crawler->filter('.pfdetailitem-subelement.opening.pf-onlyitem.clearfix .pfdetail-ftext')->extract(['_text']);
                        if (sizeof($day) === 1) {
                            $morningStart = str_replace("\t", "", substr($time[0], 1, 5));
                            $morningStart = str_replace(" ", "", $morningStart);
                            $morningStart = str_replace(",", "", $morningStart);
                            $morningClose = str_replace("\t", "", substr($time[0], 6, 10));
                            $morningClose = str_replace("\n", "", $morningClose);
                            $morningClose = str_replace(",", "", $morningClose);
                            $morningClose = str_replace("    ", "", $morningClose);
                            $eveningStart = str_replace("\t", "", substr($time[0], 55, -5));
                            $eveningStart = str_replace(" ", "", $eveningStart);
                            $eveningStart = str_replace("\n", "", $eveningStart);
                            $eveningStart = str_replace(",", "", $eveningStart);
                            $eveningClose = str_replace("\t", "", substr($time[0], -5, 5));
                            $eveningClose = str_replace(" ", "", $eveningClose);
                            $eveningClose = str_replace(",", "", $eveningClose);
                            $eveningClose = str_replace("\n", "", $eveningClose);
                            if (strlen($eveningStart) > 0) {
                                $monday_timing = $morningStart . '-' . $morningClose . '/' . $eveningStart . '-' . $eveningClose;
                            } else {
                                $monday_timing = $morningStart . '-' . $morningClose;
                            }
                        } else {
                            $monday_timing = " ";
                        }
                        if (sizeof($day) > 2) {
                            $tuesdaymorningStart = str_replace("\t", "", substr($time[1], 1, 5));
                            $tuesdaymorningStart = str_replace(" ", "", $tuesdaymorningStart);
                            $tuesdaymorningStart = str_replace(",", "", $tuesdaymorningStart);
                            $tuesdaymorningClose = str_replace("\t", "", substr($time[1], 6, 10));
                            $tuesdaymorningClose = str_replace("\n", "", $tuesdaymorningClose);
                            $tuesdaymorningClose = str_replace(",", "", $tuesdaymorningClose);
                            $tuesdaymorningClose = str_replace("    ", "", $tuesdaymorningClose);
                            $tuesdayeveningStart = str_replace("\t", "", substr($time[1], 55, -5));
                            $tuesdayeveningStart = str_replace(" ", "", $tuesdayeveningStart);
                            $tuesdayeveningStart = str_replace("\n", "", $tuesdayeveningStart);
                            $tuesdayeveningStart = str_replace(",", "", $tuesdayeveningStart);
                            $tuesdayeveningClose = str_replace("\t", "", substr($time[1], -5, 5));
                            $tuesdayeveningClose = str_replace(" ", "", $tuesdayeveningClose);
                            $tuesdayeveningClose = str_replace(",", "", $tuesdayeveningClose);
                            $tuesdayeveningClose = str_replace("\n", "", $tuesdayeveningClose);

                            if (strlen($tuesdayeveningStart) > 0) {
                                $tuesday_timing = $tuesdaymorningStart . '-' . $tuesdaymorningClose . '/' . $tuesdayeveningStart . '-' . $tuesdayeveningClose;
                            } else {
                                $tuesday_timing = $tuesdaymorningStart . '-' . $tuesdaymorningClose;
                            }
                        } else {
                            $tuesday_timing = " ";
                        }
                        if (sizeof($day) > 3) {
                            $WednesdaymorningStart = str_replace("\t", "", substr($time[2], 1, 5));
                            $WednesdaymorningStart = str_replace(" ", "", $WednesdaymorningStart);
                            $WednesdaymorningStart = str_replace(",", "", $WednesdaymorningStart);
                            $WednesdaymorningClose = str_replace("\t", "", substr($time[2], 6, 10));
                            $WednesdaymorningClose = str_replace("\n", "", $WednesdaymorningClose);
                            $WednesdaymorningClose = str_replace(",", "", $WednesdaymorningClose);
                            $WednesdaymorningClose = str_replace("    ", "", $WednesdaymorningClose);
                            $WednesdayeveningStart = str_replace("\t", "", substr($time[2], 55, -5));
                            $WednesdayeveningStart = str_replace(" ", "", $WednesdayeveningStart);
                            $WednesdayeveningStart = str_replace("\n", "", $WednesdayeveningStart);
                            $WednesdayeveningStart = str_replace(",", "", $WednesdayeveningStart);
                            $WednesdayeveningClose = str_replace("\t", "", substr($time[2], -5, 5));
                            $WednesdayeveningClose = str_replace(" ", "", $WednesdayeveningClose);
                            $WednesdayeveningClose = str_replace(",", "", $WednesdayeveningClose);
                            $WednesdayeveningClose = str_replace("\n", "", $WednesdayeveningClose);

                            if (strlen($WednesdayeveningStart) > 0) {
                                $Wednesday_timing = $WednesdaymorningStart . '-' . $WednesdaymorningClose . '/' . $WednesdayeveningStart . '-' . $WednesdayeveningClose;
                            } else {
                                $Wednesday_timing = $WednesdaymorningStart . '-' . $WednesdaymorningClose;
                            }
                        } else {
                            $Wednesday_timing = " ";
                        }
                        if (sizeof($day) >= 4) {
                            $ThursdaymorningStart = str_replace("\t", "", substr($time[3], 1, 5));
                            $ThursdaymorningStart = str_replace(" ", "", $ThursdaymorningStart);
                            $ThursdaymorningStart = str_replace(",", "", $ThursdaymorningStart);
                            $ThursdaymorningClose = str_replace("\t", "", substr($time[3], 6, 10));
                            $ThursdaymorningClose = str_replace("\n", "", $ThursdaymorningClose);
                            $ThursdaymorningClose = str_replace(",", "", $ThursdaymorningClose);
                            $ThursdaymorningClose = str_replace("    ", "", $ThursdaymorningClose);
                            $ThursdayeveningStart = str_replace("\t", "", substr($time[3], 55, -5));
                            $ThursdayeveningStart = str_replace(" ", "", $ThursdayeveningStart);
                            $ThursdayeveningStart = str_replace("\n", "", $ThursdayeveningStart);
                            $ThursdayeveningStart = str_replace(",", "", $ThursdayeveningStart);
                            $ThursdayeveningClose = str_replace("\t", "", substr($time[3], -5, 5));
                            $ThursdayeveningClose = str_replace(" ", "", $ThursdayeveningClose);
                            $ThursdayeveningClose = str_replace(",", "", $ThursdayeveningClose);
                            $ThursdayeveningClose = str_replace("\n", "", $ThursdayeveningClose);

                            if (strlen($ThursdayeveningStart) > 0) {
                                $Thursday_timing = $ThursdaymorningStart . '-' . $ThursdaymorningClose . '/' . $ThursdayeveningStart . '-' . $ThursdayeveningClose;
                            } else {
                                $Thursday_timing = $ThursdaymorningStart . '-' . $ThursdaymorningClose;
                            }
                        } else {
                            $Thursday_timing = " ";
                        }
                        if (sizeof($day) >= 5) {
                            $FridaymorningStart = str_replace("\t", "", substr($time[4], 1, 5));
                            $FridaymorningStart = str_replace(" ", "", $FridaymorningStart);
                            $FridaymorningStart = str_replace(",", "", $FridaymorningStart);
                            $FridaymorningClose = str_replace("\t", "", substr($time[4], 6, 10));
                            $FridaymorningClose = str_replace("\n", "", $FridaymorningClose);
                            $FridaymorningClose = str_replace(",", "", $FridaymorningClose);
                            $FridaymorningClose = str_replace("    ", "", $FridaymorningClose);
                            $FridayeveningStart = str_replace("\t", "", substr($time[4], 55, -5));
                            $FridayeveningStart = str_replace(" ", "", $FridayeveningStart);
                            $FridayeveningStart = str_replace("\n", "", $FridayeveningStart);
                            $FridayeveningStart = str_replace(",", "", $FridayeveningStart);
                            $FridayeveningClose = str_replace("\t", "", substr($time[4], -5, 5));
                            $FridayeveningClose = str_replace(" ", "", $FridayeveningClose);
                            $Friday = str_replace(",", "", $FridayeveningClose);
                            $FridayeveningClose = str_replace("\n", "", $FridayeveningClose);
                            if (strlen($FridayeveningStart) > 0) {
                                $Friday_timing = $FridaymorningStart . '-' . $FridaymorningClose . '/' . $FridayeveningStart . '-' . $FridayeveningClose;
                            } else {
                                $Friday_timing = $FridaymorningStart . '-' . $FridaymorningClose;
                            }
                        } else {
                            $Friday_timing = " ";
                        }
                        if (sizeof($day) === 6) {
                            $SaturdaymorningStart = str_replace("\t", "", substr($time[5], 1, 5));
                            $SaturdaymorningStart = str_replace(" ", "", $SaturdaymorningStart);
                            $SaturdaymorningStart = str_replace(",", "", $SaturdaymorningStart);
                            $SaturdaymorningClose = str_replace("\t", "", substr($time[5], 6, 10));
                            $SaturdaymorningClose = str_replace("\n", "", $SaturdaymorningClose);
                            $SaturdaymorningClose = str_replace(",", "", $SaturdaymorningClose);
                            $SaturdaymorningClose = str_replace("    ", "", $SaturdaymorningClose);
                            $SaturdayeveningStart = str_replace("\t", "", substr($time[5], 55, -5));
                            $SaturdayeveningStart = str_replace(" ", "", $SaturdayeveningStart);
                            $SaturdayeveningStart = str_replace("\n", "", $SaturdayeveningStart);
                            $SaturdayeveningStart = str_replace(",", "", $SaturdayeveningStart);
                            $SaturdayeveningClose = str_replace("\t", "", substr($time[5], -5, 5));
                            $SaturdayeveningClose = str_replace(" ", "", $SaturdayeveningClose);
                            $SaturdayeveningClose = str_replace(",", "", $SaturdayeveningClose);
                            $SaturdayeveningClose = str_replace("\n", "", $SaturdayeveningClose);
                            if (strlen($SaturdayeveningStart) > 0) {
                                $Saturday_timing = $SaturdaymorningStart . '-' . $SaturdaymorningClose . '/' . $SaturdayeveningStart . '-' . $SaturdayeveningClose;
                            } else {
                                $Saturday_timing = $SaturdaymorningStart . '-' . $SaturdaymorningClose;
                            }
                        } else {
                            $Saturday_timing = " ";
                        }
                        $name_doctor_maj = ucfirst($name);
                        $doctor_min = mb_strtolower($name);
                        $doctor_city = $region . '|tunisa';
                        // insert into database //
                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8;", $username, $password);
                            $conn->query("SET NAMES 'utf8'");
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%';");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {

                                $sql = "INSERT INTO wp_doctor (id_doctor,doctor_min, doctor_name,status,coments_status,doctor_speciality,doctor_bio,doctor_address,doctor_country,latitude,longitude,
                                               doctor_region, doctor_phone,exercie_mode,post_author,ping_status,featuredItem,created_at,picture,opening_monday,opening_Tuesday,opening_Wednesday,
                                               opening_Thursday,opening_Friday,opening_Saturday) VALUES (0,'$doctor_min','$name_doctor_maj','publish','open'
                                               ,'$speciality','$bio_doctor','$address','$country',
                                               '$latitude','$longitude','$doctor_city','$phone','$exercie',1,1,1,now(),'$picture','$monday_timing','$tuesday_timing',
                                               '$Wednesday_timing','$Thursday_timing','$Friday_timing','$Saturday_timing')";
                                $conn->exec($sql);
                                echo '#';
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
    $repeat++;
}


$repeat = 0;

while ($repeat < 10) {
    for ($i = 0; $i < 4; $i++) {
        echo '
Fetching: https://www.med.tn/annuaire-medecins-tunisie/' . $i . ' ...';
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
                $href = $item->getAttribute("href");
                $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
                $loop = \React\EventLoop\Factory::create();
                $factory = new \React\MySQL\Factory($loop);
                $brower = new Browser($loop);
                $brower->get($href)
                    ->then(function (ResponseInterface $response) {
                        $crawler = new Crawler((string) $response->getbody());
                        $name = Utf8_ansi($crawler->filter('.doctitle')->text());
                        $name = str_replace(",", " ", $name);
                        $speciality = mb_strtolower($crawler->filter('.docsubtitle')->text());
                        $speciality = str_replace("'", '', $speciality);
                        $speciality = str_replace(",", '', $speciality);
                        $address = mb_strtolower(($crawler->filter('.pf-itempage-sidebarinfo-elurl.pf-itempage-sidebarinfo-elitem')->text()));
                        $address = str_replace(",", " ", $address);
                        $address = str_replace("'", "`", $address);
                        $country = 'Tunisia';
                        $phone = trim($crawler->filter('meta[itemprop="telephone"]')->attr('content'));
                        $exercie_mod = $crawler->filter('.pfdetail-ftext.widget_tag_cloud a')->extract(['_text']);
                        $exercie = implode("/", $exercie_mod);
                        $exercie = str_replace(",", " ", $exercie);
                        $exercie = str_replace("'", "`", $exercie);
                        $exercie = mb_strtolower($exercie);
                        $cabinet = $crawler->filter('.imgs-grid-image .image-wrap img')->extract(['_text']);
                        $bio = $crawler->filter('.pfdetailitem-subelement.pf-onlyitem.clearfix .clearfix')->extract(['_text']);
                        if (sizeof($bio) > 0) {
                            $bio[0] = str_replace("'", "`", $bio[0]);
                            $bio[0] = str_replace(",", " ", $bio[0]);
                            $bio[0] = '<h2 class="y4vuei-3 cBtaUV">' . $name . '</h2>' . $bio[0]. ' '.$exercie;
                            $bio_doctor = $bio[0];
                        } else {
                            $bio_doctor = null;
                        }
                        

                        $region = $crawler->filter('span[itemprop=name]')->extract(['_text']);
                        $region = $region[2];
                        $region = str_replace(',', ' ', $region);
                        $region = str_replace("'", "`", $region);


                        $location = $crawler->filter('.pftabcontainer.pf-itempagedetail-element .hidden-xs a')->attr('href');
                        $location = str_replace('https://www.google.com/maps?saddr&daddr=', '{latitude:', $location);
                        $location = str_replace(',', ',longitude:', $location);
                        $location = $location . '}';
                        $location = explode(',', $location);
                        if (!empty($location)) {
                            $location[0] = str_replace('{latitude:', '', $location[0]);
                            $location[1] = str_replace('longitude:', '', $location[1]);
                            $location[1] = str_replace('}', '', $location[1]);
                            $latitude = $location[0];
                            $longitude = $location[1];
                        } else {
                            $latitude = null;
                            $longitude = null;
                        }
                        $picture = strval($crawler->filter('.doctitle img')->attr('src'));

                        ///getting working time of doctors///
                        $day = $crawler->filter('.pfdetailitem-subelement.opening.pf-onlyitem.clearfix .pf-ftitle')->extract(['_text']);
                        $time = $crawler->filter('.pfdetailitem-subelement.opening.pf-onlyitem.clearfix .pfdetail-ftext')->extract(['_text']);
                        if (sizeof($day) === 1) {
                            $morningStart = str_replace("\t", "", substr($time[0], 1, 5));
                            $morningStart = str_replace(" ", "", $morningStart);
                            $morningStart = str_replace(",", "", $morningStart);
                            $morningClose = str_replace("\t", "", substr($time[0], 6, 10));
                            $morningClose = str_replace("\n", "", $morningClose);
                            $morningClose = str_replace(",", "", $morningClose);
                            $morningClose = str_replace("    ", "", $morningClose);
                            $eveningStart = str_replace("\t", "", substr($time[0], 55, -5));
                            $eveningStart = str_replace(" ", "", $eveningStart);
                            $eveningStart = str_replace("\n", "", $eveningStart);
                            $eveningStart = str_replace(",", "", $eveningStart);
                            $eveningClose = str_replace("\t", "", substr($time[0], -5, 5));
                            $eveningClose = str_replace(" ", "", $eveningClose);
                            $eveningClose = str_replace(",", "", $eveningClose);
                            $eveningClose = str_replace("\n", "", $eveningClose);
                            if (strlen($eveningStart) > 0) {
                                $monday_timing = $morningStart . '-' . $morningClose . '/' . $eveningStart . '-' . $eveningClose;
                            } else {
                                $monday_timing = $morningStart . '-' . $morningClose;
                            }
                        } else {
                            $monday_timing = " ";
                        }
                        if (sizeof($day) > 2) {
                            $tuesdaymorningStart = str_replace("\t", "", substr($time[1], 1, 5));
                            $tuesdaymorningStart = str_replace(" ", "", $tuesdaymorningStart);
                            $tuesdaymorningStart = str_replace(",", "", $tuesdaymorningStart);
                            $tuesdaymorningClose = str_replace("\t", "", substr($time[1], 6, 10));
                            $tuesdaymorningClose = str_replace("\n", "", $tuesdaymorningClose);
                            $tuesdaymorningClose = str_replace(",", "", $tuesdaymorningClose);
                            $tuesdaymorningClose = str_replace("    ", "", $tuesdaymorningClose);
                            $tuesdayeveningStart = str_replace("\t", "", substr($time[1], 55, -5));
                            $tuesdayeveningStart = str_replace(" ", "", $tuesdayeveningStart);
                            $tuesdayeveningStart = str_replace("\n", "", $tuesdayeveningStart);
                            $tuesdayeveningStart = str_replace(",", "", $tuesdayeveningStart);
                            $tuesdayeveningClose = str_replace("\t", "", substr($time[1], -5, 5));
                            $tuesdayeveningClose = str_replace(" ", "", $tuesdayeveningClose);
                            $tuesdayeveningClose = str_replace(",", "", $tuesdayeveningClose);
                            $tuesdayeveningClose = str_replace("\n", "", $tuesdayeveningClose);

                            if (strlen($tuesdayeveningStart) > 0) {
                                $tuesday_timing = $tuesdaymorningStart . '-' . $tuesdaymorningClose . '/' . $tuesdayeveningStart . '-' . $tuesdayeveningClose;
                            } else {
                                $tuesday_timing = $tuesdaymorningStart . '-' . $tuesdaymorningClose;
                            }
                        } else {
                            $tuesday_timing = " ";
                        }
                        if (sizeof($day) > 3) {
                            $WednesdaymorningStart = str_replace("\t", "", substr($time[2], 1, 5));
                            $WednesdaymorningStart = str_replace(" ", "", $WednesdaymorningStart);
                            $WednesdaymorningStart = str_replace(",", "", $WednesdaymorningStart);
                            $WednesdaymorningClose = str_replace("\t", "", substr($time[2], 6, 10));
                            $WednesdaymorningClose = str_replace("\n", "", $WednesdaymorningClose);
                            $WednesdaymorningClose = str_replace(",", "", $WednesdaymorningClose);
                            $WednesdaymorningClose = str_replace("    ", "", $WednesdaymorningClose);
                            $WednesdayeveningStart = str_replace("\t", "", substr($time[2], 55, -5));
                            $WednesdayeveningStart = str_replace(" ", "", $WednesdayeveningStart);
                            $WednesdayeveningStart = str_replace("\n", "", $WednesdayeveningStart);
                            $WednesdayeveningStart = str_replace(",", "", $WednesdayeveningStart);
                            $WednesdayeveningClose = str_replace("\t", "", substr($time[2], -5, 5));
                            $WednesdayeveningClose = str_replace(" ", "", $WednesdayeveningClose);
                            $WednesdayeveningClose = str_replace(",", "", $WednesdayeveningClose);
                            $WednesdayeveningClose = str_replace("\n", "", $WednesdayeveningClose);

                            if (strlen($WednesdayeveningStart) > 0) {
                                $Wednesday_timing = $WednesdaymorningStart . '-' . $WednesdaymorningClose . '/' . $WednesdayeveningStart . '-' . $WednesdayeveningClose;
                            } else {
                                $Wednesday_timing = $WednesdaymorningStart . '-' . $WednesdaymorningClose;
                            }
                        } else {
                            $Wednesday_timing = " ";
                        }
                        if (sizeof($day) >= 4) {
                            $ThursdaymorningStart = str_replace("\t", "", substr($time[3], 1, 5));
                            $ThursdaymorningStart = str_replace(" ", "", $ThursdaymorningStart);
                            $ThursdaymorningStart = str_replace(",", "", $ThursdaymorningStart);
                            $ThursdaymorningClose = str_replace("\t", "", substr($time[3], 6, 10));
                            $ThursdaymorningClose = str_replace("\n", "", $ThursdaymorningClose);
                            $ThursdaymorningClose = str_replace(",", "", $ThursdaymorningClose);
                            $ThursdaymorningClose = str_replace("    ", "", $ThursdaymorningClose);
                            $ThursdayeveningStart = str_replace("\t", "", substr($time[3], 55, -5));
                            $ThursdayeveningStart = str_replace(" ", "", $ThursdayeveningStart);
                            $ThursdayeveningStart = str_replace("\n", "", $ThursdayeveningStart);
                            $ThursdayeveningStart = str_replace(",", "", $ThursdayeveningStart);
                            $ThursdayeveningClose = str_replace("\t", "", substr($time[3], -5, 5));
                            $ThursdayeveningClose = str_replace(" ", "", $ThursdayeveningClose);
                            $ThursdayeveningClose = str_replace(",", "", $ThursdayeveningClose);
                            $ThursdayeveningClose = str_replace("\n", "", $ThursdayeveningClose);

                            if (strlen($ThursdayeveningStart) > 0) {
                                $Thursday_timing = $ThursdaymorningStart . '-' . $ThursdaymorningClose . '/' . $ThursdayeveningStart . '-' . $ThursdayeveningClose;
                            } else {
                                $Thursday_timing = $ThursdaymorningStart . '-' . $ThursdaymorningClose;
                            }
                        } else {
                            $Thursday_timing = " ";
                        }
                        if (sizeof($day) >= 5) {
                            $FridaymorningStart = str_replace("\t", "", substr($time[4], 1, 5));
                            $FridaymorningStart = str_replace(" ", "", $FridaymorningStart);
                            $FridaymorningStart = str_replace(",", "", $FridaymorningStart);
                            $FridaymorningClose = str_replace("\t", "", substr($time[4], 6, 10));
                            $FridaymorningClose = str_replace("\n", "", $FridaymorningClose);
                            $FridaymorningClose = str_replace(",", "", $FridaymorningClose);
                            $FridaymorningClose = str_replace("    ", "", $FridaymorningClose);
                            $FridayeveningStart = str_replace("\t", "", substr($time[4], 55, -5));
                            $FridayeveningStart = str_replace(" ", "", $FridayeveningStart);
                            $FridayeveningStart = str_replace("\n", "", $FridayeveningStart);
                            $FridayeveningStart = str_replace(",", "", $FridayeveningStart);
                            $FridayeveningClose = str_replace("\t", "", substr($time[4], -5, 5));
                            $FridayeveningClose = str_replace(" ", "", $FridayeveningClose);
                            $Friday = str_replace(",", "", $FridayeveningClose);
                            $FridayeveningClose = str_replace("\n", "", $FridayeveningClose);
                            if (strlen($FridayeveningStart) > 0) {
                                $Friday_timing = $FridaymorningStart . '-' . $FridaymorningClose . '/' . $FridayeveningStart . '-' . $FridayeveningClose;
                            } else {
                                $Friday_timing = $FridaymorningStart . '-' . $FridaymorningClose;
                            }
                        } else {
                            $Friday_timing = " ";
                        }
                        if (sizeof($day) === 6) {
                            $SaturdaymorningStart = str_replace("\t", "", substr($time[5], 1, 5));
                            $SaturdaymorningStart = str_replace(" ", "", $SaturdaymorningStart);
                            $SaturdaymorningStart = str_replace(",", "", $SaturdaymorningStart);
                            $SaturdaymorningClose = str_replace("\t", "", substr($time[5], 6, 10));
                            $SaturdaymorningClose = str_replace("\n", "", $SaturdaymorningClose);
                            $SaturdaymorningClose = str_replace(",", "", $SaturdaymorningClose);
                            $SaturdaymorningClose = str_replace("    ", "", $SaturdaymorningClose);
                            $SaturdayeveningStart = str_replace("\t", "", substr($time[5], 55, -5));
                            $SaturdayeveningStart = str_replace(" ", "", $SaturdayeveningStart);
                            $SaturdayeveningStart = str_replace("\n", "", $SaturdayeveningStart);
                            $SaturdayeveningStart = str_replace(",", "", $SaturdayeveningStart);
                            $SaturdayeveningClose = str_replace("\t", "", substr($time[5], -5, 5));
                            $SaturdayeveningClose = str_replace(" ", "", $SaturdayeveningClose);
                            $SaturdayeveningClose = str_replace(",", "", $SaturdayeveningClose);
                            $SaturdayeveningClose = str_replace("\n", "", $SaturdayeveningClose);
                            if (strlen($SaturdayeveningStart) > 0) {
                                $Saturday_timing = $SaturdaymorningStart . '-' . $SaturdaymorningClose . '/' . $SaturdayeveningStart . '-' . $SaturdayeveningClose;
                            } else {
                                $Saturday_timing = $SaturdaymorningStart . '-' . $SaturdaymorningClose;
                            }
                        } else {
                            $Saturday_timing = " ";
                        }
                        $name_doctor_maj = ucfirst($name);
                        $doctor_min = mb_strtolower($name);
                        $doctor_city = $region . '|tunisa';
                        // insert into database //
                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8;", $username, $password);
                            $conn->query("SET NAMES 'utf8'");
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%';");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {

                                $sql = "INSERT INTO wp_doctor (id_doctor,doctor_min, doctor_name,status,coments_status,doctor_speciality,doctor_bio,doctor_address,doctor_country,latitude,longitude,
                                               doctor_region, doctor_phone,exercie_mode,post_author,ping_status,featuredItem,created_at,picture,opening_monday,opening_Tuesday,opening_Wednesday,
                                               opening_Thursday,opening_Friday,opening_Saturday) VALUES (0,'$doctor_min','$name_doctor_maj','publish','open'
                                               ,'$speciality','$bio_doctor','$address','$country',
                                               '$latitude','$longitude','$doctor_city','$phone','$exercie',1,1,1,now(),'$picture','$monday_timing','$tuesday_timing',
                                               '$Wednesday_timing','$Thursday_timing','$Friday_timing','$Saturday_timing')";
                                $conn->exec($sql);
                                echo '#';
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
    $repeat++;
}


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
//     $name_speciality = ($specialitys[$id]['name_l1']);
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
//         $name = mb_strtolower(($doctorData['results'][$i]['doc_profile']['first_name'] . ' ' . $doctorData['results'][$i]['doc_profile']['last_name']));
//         $name_doctor_maj = ucfirst($name);
//         $doctor_min = mb_strtolower($name);
//         $bio = mb_strtolower(($doctorData['results'][$i]['dr_biography_l1']));
//         $bio = str_replace("'", "`", $bio[0]);
//         $bio= str_replace(",", " ", $bio[0]);
//         $bio = '<h2 class="y4vuei-3 cBtaUV">' . $name_doctor_maj . '</h2>' .$bio;
//         $picture = $doctorData['results'][$i]['dr_pic'];
//         $country = 'tunisia';
//         if (sizeof($doctorData['results'][$i]['doc_prices'][0]) > 0) {
//             $price = $doctorData['results'][$i]['doc_prices'][0]['amount'] . $doctorData['results'][$i]['doc_prices'][0]['currency'];
//         } else {
//             $price = null;
//         }
//         $speciality = mb_strtolower(($name_speciality));

//         try {
//             $arr = [];
//             $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8mb4;", $username, $password);
//             $conn->exec("SET CHARACTER SET utf8 ");
//             $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//             $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%' and doctor_bio like '%$bio%' ;");
//             $sql->execute();
//             if ($sql->rowCount() > 0) {
//                 echo '';
//             } else {
//                 $sql = "INSERT INTO wp_doctor (id_doctor, doctor_min,doctor_name,status,coments_status,doctor_speciality,
//                 doctor_address,doctor_country,latitude,longitude, doctor_region,doctor_phone,,post_author,ping_status,
//                 featuredItem,created_at) VALUES
//                  (0,'$doctor_min','$name_doctor_maj','publish','open','$specility','$address','$country','$phone',,1,1,1,now()
//                  ,'$picture','$price')";
                
//                 $conn->exec($sql);
//             }
//             $conn = null;
//         } catch (PDOException $e) {
//             echo '';
//         }
//         $conn = null;
//     }
// }
try {
    for ($i = 0; $i < 2960; $i = $i + 20) {
        echo '
Fetching: https://tunisie-medicale.com/index.php/docteur/index/' . $i . ' ...';
        $html = file_get_contents('https://tunisie-medicale.com/index.php/docteur/index/' . $i);
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
                        $name = mb_strtolower(($crawler->filter('.big_team_widget h2 span')->text()));
                        $name = str_replace(",", " ", $name);
                        $name = str_replace("'", "` ", $name);
                        $name_doctor_maj = ucfirst($name);
                        $doctor_min = mb_strtolower($name);
                       
                        $country = 'Tunisia';
                        $region =  mb_strtolower(($crawler->filter('p[itemprop=addressLocality]')->text()));
                        $region = str_replace(",", " ", $region);
                        $region = str_replace("'", "` ", $region);
                        $doctor_city = $region . '|tunisa';
                        $content = str_replace("\n", '', $response->getbody());
                        $content = str_replace("\r", '', $content);
                        $content = str_replace("\t", '', $content);
                        preg_match_all('`lat: (.*),                    lng: (.*)                };`', $content, $matches);
                        if ($matches[0] == null) {
                            $latitude = null;
                            $longitude = null;
                        } else {
                            $latitude = $matches[1][0];
                            $longitude = $matches[2][0];
                        }
                      
                        $specility = mb_strtolower(($crawler->filter('h3[itemprop=medicalSpecialty]')->text()));
                        
                        $address = mb_strtolower(($crawler->filter('.contact_details_1 li a')->text()));
                        $address = str_replace(",", " ", $address);
                        $address = str_replace("'", "` ", $address);
                        $phone = $crawler->filter('a[itemprop=telephone]')->attr('href');
                        $phone = strval(str_replace("tel:", "", $phone));
                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8mb4;", $username, $password);
                            $conn->exec("SET CHARACTER SET utf8 ");
                            // set the PDO error mode to exception
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name%' and doctor_address like '%$address%'");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {
                                try {
                                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
                                    // set the PDO error mode to exception
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $sql = "INSERT INTO wp_doctor (id_doctor, doctor_min,doctor_name,status,coments_status,doctor_speciality,doctor_address,doctor_country,latitude,longitude,
                            doctor_region,doctor_phone,post_author,ping_status,featuredItem,created_at) VALUES (0,'$doctor_min','$name_doctor_maj','publish','open','$specility','$address','$country','$latitude','$longitude','$doctor_city','$phone',1,1,1,now())";
                              
                               // use exec() because no results are returned                              
                                    $conn->exec($sql);
                                    echo '#';
                                } catch (PDOException $e) {
                                    echo $e->getMessage();
                                }
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
    echo '\n Done';
} catch (Exception $e) {
    echo 'error getting data';
}

$country = array(
    'tunis', 'sfax', 'sousse', 'monastir', 'ariana', 'ben-arous', 'nabeul', 'mahdia',
    'kairouan', 'bizerte', 'medenine', 'mannouba', 'gabes', 'beja', 'gafsa', 'jendouba', 'le-Kef', 'sidi-bouzid',
    'kasserine', 'zaghouan', 'siliana', 'kebili', 'tataouine', 'tozeur',
);
for ($k = 0; $k <= 23; $k++) {
    for ($i = 1; $i <= 100; $i++) {
        $brower->get('http://www.ordre-medecins.org.tn/components/com_annuaires/annuaires.php?ville=' . $country[$k] . '&page=' . $i)
            ->then(function (ResponseInterface $response) {
                $crawler = new Crawler((string) $response->getbody());
                $name = $crawler->filter('.col-ann-9 .table tr td')->extract(['_text']);
                if ((sizeof($name)) == 50) {
                    for ($j = 0; $j <= 45; $j = $j + 5) {
                        $servername = "localhost";
                        $username = "amira";
                        $password = "amira";
                        $dbname = "g_health24";
                        try {
                            $arr = [];
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname;CHARSET=utf8mb4;", $username, $password);
                            $conn->exec("SET CHARACTER SET utf8 ");
                            // set the PDO error mode to exception
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $sql = $conn->prepare("SELECT * from wp_doctor where doctor_name LIKE '%$name[$j]%' ;");
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo '';
                            } else {
                                $doctorname = utf8_decode($name[$j]);
                                $speciality = mb_strtolower(($name[$j + 1]));
                                $exercie_mode = mb_strtolower(($name[$j + 2]));
                                $address = mb_strtolower(($name[$j + 3]));
                                $phone = utf8_decode($name[$j + 4]);
                                $sql = "INSERT INTO wp_doctor (id_doctor, doctor_name,doctor_speciality,exercie_mode,doctor_address,doctor_phone,country,created_at)
                                 VALUES (0,'$doctorname','$speciality','$exercie_mode','$address','$phone','Tunsia',now())";
                                // use exec() because no results are returned
                                $conn->exec($sql);
                            }
                            $conn = null;
                        } catch (PDOException $e) {
                            echo '';
                        }
                        $conn = null;
                    }
                }
            });
            $loop->run();
    }
}
