<?php
/*
Plugin Name: Gold Price (NPR) Scraper
Description: Scrapes gold prices from a website.
Version: 1.2
Author: Aayush Niraula
*/

register_activation_hook(__FILE__, 'gold_price_scraper_activate');
register_deactivation_hook(__FILE__, 'gold_price_scraper_deactivate');

function gold_price_scraper_activate(){
    schedule_gold_price_scraping(); 
}

function gold_price_scraper_deactivate()
{
    wp_clear_scheduled_hook('daily_gold_price_scraping');
}

add_action('wp', 'schedule_gold_price_scraping');
add_action('daily_gold_price_scraping', 'scrape_gold_price');

function scrape_gold_price(){
            
    $url = 'https://www.fenegosida.org/rate-history.php';

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
    
    $gold24K = $xpath->query('//div[@id="header-rate"]/div[@class="rate-gold post"][1]/p/b');
    $gold22K = $xpath->query('//div[@id="header-rate"]/div[@class="rate-gold post"][2]/p/b');
    $silver = $xpath->query('//div[@id="header-rate"]/div[@class="rate-silver post"]/p/b');
    // print_r($gold22K->item(1)->nodeValue);
    if ($gold24K->length > 0 && $gold22K->length > 0 && $silver->length > 0) {
        $price24KGold = preg_replace('/[^0-9]/', '', $gold24K->item(1)->nodeValue);
        $price22KGold = preg_replace('/[^0-9]/', '', $gold22K->item(1)->nodeValue);
        $priceSilver = preg_replace('/[^0-9]/', '', $silver->item(1)->nodeValue);

        update_option('gold_price_24k_npr', $price24KGold);
        update_option('gold_price_22k_npr', $price24KGold);
        update_option('silver_price', $priceSilver);
    } else {
        error_log('Failed to scrape gold and silver prices.');
    }
    
    $numbers = [
        'gold24Kprice' => $price24KGold,
        'gold22Kprice' => $price22KGold,
        'silverPrice' => $priceSilver
    ];
    

    return $numbers;

    
}

    // add_action('init', 'scrape_gold_prices');
    function schedule_gold_price_scraping() {
        if (!wp_next_scheduled('daily_gold_price_scraping')) {
            wp_schedule_event(time(), 'daily', 'daily_gold_price_scraping');
        }
    }


    
?>
