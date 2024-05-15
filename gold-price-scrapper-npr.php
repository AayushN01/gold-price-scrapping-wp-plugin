<?php
/*
Plugin Name: Gold Price Scraper
Description: Scrapes gold prices from a website.
Version: 1.0
Author: Your Name
*/

register_activation_hook(__FILE__, 'gold_price_scraper_activate');

function gold_price_scraper_activate(){
    schedule_gold_price_scraping(); 
}

register_deactivation_hook(__FILE__, 'gold_price_scraper_deactivate');

function gold_price_scraper_deactivate()
{
    wp_clear_scheduled_hook('daily_gold_price_scraping');
}

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

    return $numbers;
    // $price24KNPR = $numbers[0];
    // $price22KNPR = $numbers[1];  
    
    // echo "Numbers:\n";
    // print_r($numbers);
    // echo "Gold Price 24K (NPR) :" . $price24KNPR ."\n";
    // echo "Gold Price 22K (NPR) :" . $price22KNPR;
    
}

    // add_action('init', 'scrape_gold_prices');
    function schedule_gold_price_scraping() {
        if (!wp_next_scheduled('daily_gold_price_scraping')) {
            wp_schedule_event(time(), 'daily', 'daily_gold_price_scraping');
        }
    }
    add_action('wp', 'schedule_gold_price_scraping');
    add_action('daily_gold_price_scraping', 'scrape_gold_price');

    
?>
