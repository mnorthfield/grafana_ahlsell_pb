<?php
require __DIR__ . '/vendor/autoload.php';
$filePath = dirname(__FILE__);

use InfluxDB2\Client;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;

define("BUCKET","ahlsell");
define("ORG","phasebox");
define("INFLUX_URL","http://localhost:8086");
define("INFLUX_TOKEN","Uv77LqH4mHoMr1PBrPrYyDsu005CU4x2mUlmm1BbffT6EZGSybH6IHqO25FxT5tu8Qtrmi55Tg_8WLaV-elAaw==");
define("DELAY_BETWEEN_CALLS", 10); //Delay mellan varje http anrop till ahlsell ( i sekunder)


require_once 'updateGrafana.php';

$client = new InfluxDB2\Client([
    "url" => INFLUX_URL,
    "token" => INFLUX_TOKEN,
    "bucket" => BUCKET,
    "org" => ORG,
    "precision" => InfluxDB2\Model\WritePrecision::S
]);

$writeApi = $client->createWriteApi();

function ahlsell_get_whs()
{
    $whs = json_decode(file_get_contents('https://www.ahlsell.se/api/warehouses'));

    $return = array();
    foreach($whs as $wharehouse){
        $return[$wharehouse->id] = $wharehouse->name;
    }

    //fixa så att central lagret kommer med
    $return[2] = "Centrallager";
    return $return;
}


function ahlsell_get_wh_status($whData,$artNr,$bucket) {
    global $writeApi;
    
    $total = 0;

    $amounts = getWhStock($artNr);

    foreach ($amounts as $wharehouse) {
        $whId = $wharehouse->id;

        //tabort special lager
        if(!isset($whData[$whId]))
        {
            //print $whId . " dose not seam to be be a normal wharehouse" . "\r\n";
            continue;
        }
        $name = $whData[$whId];
        $artNr = (string)$artNr;
        $amount = (int)$wharehouse->stock->quantity;
        

        print "Got " . $name . "  " . $whId . "   " . $amount . "\r\n";
        
        $point = Point::measurement('sales')
        ->addTag('butik', 'ahlsell')
        ->addTag('whId', $whId)
        ->addTag('whName', $name)
        ->addTag('artNr', $artNr)
        ->addField('amout', $amount)
        ->time(microtime(true));
        

        $writeApi->write($point, WritePrecision::S, $bucket, ORG);
        $total += $amount;
        
        
    }
    sleep(DELAY_BETWEEN_CALLS);

    $point = Point::measurement('sales')
    ->addTag('butik', 'ahlsell')
    ->addTag('artNr', $artNr)
    ->addField('totalt', $total)
    ->time(microtime(true));
    $writeApi->write($point, WritePrecision::S, $bucket, ORG);
    print "\r\n Totalt: " . $total . "\r\n";
}


function getWhStock($artNr)
{
    
    // WarehouseId=2 för att få med centrallagret
    $url = "https://www.ahlsell.se/api/warehouses/stock?variantNumber=" . $artNr . "&WarehouseId=2";

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

    //execute post
    $result = json_decode(curl_exec($ch));

    
    return $result;    
}

function ahsell_get_wh_artnr_status($yaml_artNrs)
{

    $yaml = file_get_contents($yaml_artNrs);
    $artData = yaml_parse($yaml);
    $whData = ahlsell_get_whs();

    foreach ($artData as $bucket => $articles)
    {
        
        foreach ($articles as $art) {

            $artNr = key($art);
            $name = $art[$artNr];
    
            print "Getting " . $artNr . "   " . $name . "     " . date("Y-m-d H:i:s") . "\r\n";
    
            ahlsell_get_wh_status($whData,$artNr,$bucket);
    
        }
    }
}
ahlsell_get_whs();
ahsell_get_wh_artnr_status($filePath . "/artNr.yaml");




?>
