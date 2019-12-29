<?php
require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Pdr\Client;

$client = new Client();

// print_r($client);

// Search domain
$domainsearch = $client->searchDomain('ipokkkwer.co.zw');

$domain_id = json_decode($domainsearch)->data->attributes->domain_id;

// Submit Contact
$contact = $client->createContact(
    [
        "domain_id" => $domain_id,
        "first_name" => "SDK Privy",
        "last_name" =>"SDK Reza",
        "email" =>"privyreza@gmail.com",
        "company" =>"Pnrhost",
        "mobile" =>"0773234827",
        "street_address" =>"78 Test Street",
        "core_business" =>"SDK Test Core Business",
        "city" =>"Harare",
        "country" =>"Zimbabwe"
    ]
);

// Submit NS
$ns = $client->createNS([
    "domain_id" => $domain_id,
    "ns1" => "ns1.pnrhost.com",
    "ns2" => "ns2.pnrhost.com"
]);

// Register domain
$domain = $client->registerDomain($domain_id);