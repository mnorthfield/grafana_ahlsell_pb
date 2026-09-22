<?php
$url = "https://www.ahlsell.se/api/warehouses/stock?variantNumber=1400929&WarehouseId=2";

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);

//So that curl_exec returns the contents of the cURL; rather than echoing it
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

//execute post
$result = json_decode(curl_exec($ch));

var_dump($result);