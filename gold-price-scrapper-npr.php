<?php
/*
Plugin Name: Gold Price Scraper
Description: Scrapes gold prices from a website.
Version: 1.0
Author: Your Name
*/

   function scrape_gold_price(){
            
    $url = 'https://www.goldpriceindia.com/nepal-gold-price.php';

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 

    $response = curl_exec($curl);

    if ($response === false) {
        echo 'Error: ' . curl_error($curl);
        exit;
    }

    curl_close($curl);

    $dom = new DOMDocument();
    @$dom->loadHTML($response);

    // Create a DOMXPath object to query the DOM
    $xpath = new DOMXPath($dom);
    
    $rows = $xpath->query('//td[contains(@class, "prc align-center pad-15")]');

    $numbers = [];
    
    foreach ($rows as $row) {
        $number = preg_replace('/[^0-9.]/', '', $row->nodeValue);
        
        $numbers[] = $number;
    }

    $price24KNPR = $numbers[0];
    $price22KNPR = $numbers[1];  
    
    echo "Numbers:\n";
    print_r($numbers);
    echo "Gold Price 24K (NPR) :" . $price24KNPR ."\n";
    echo "Gold Price 22K (NPR) :" . $price22KNPR;
    
}

    // add_action('init', 'scrape_gold_prices');

    
?>
