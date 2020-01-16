<?php
require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Pdr\Client;

$client = new Client();

// print_r($client);

// Search domain
$domain = 'ipokkkll-wer.co.zw';
$domainsearch = $client->searchDomain($domain);

$status = $domainsearch->data->attributes->status;

if($status === 'available') {
    // Build post data
    $postfields = array(
        'domain' => $domain,
        'nameservers' => array(
            "ns1" => "ns1.pnrhost.com",
            "ns2" => "ns2.pnrhost.com"
        ),
        'contacts' => array(
            'registrant' => array(
                "first_name" => "SDK Privy",
                "last_name" =>"SDK Reza",
                "email" =>"privyreza@gmail.com",
                "company" =>"Pnrhost",
                "mobile" =>"0773234827",
                "street_address" =>"78 Test Street",
                "core_business" =>"SDK Test Core Business",
                "city" =>"Harare",
                "country" =>"Zimbabwe"
            ),
        )
    );
    call('RegisterDomain', $postfields);
}

function call($action, $data){
    global $client;
    $func =  lcfirst($action);

    return $client->$func($data);
}