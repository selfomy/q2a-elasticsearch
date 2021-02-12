<?php
    require 'vendor/autoload.php';
    use Elasticsearch\ClientBuilder;
function create_es_client ($host , $port, $scheme = 'http', $username = null, $password = null) {
    if ($username == "" || $password == "") {
        $hosts = [
        'host' => $host,
        'port' => $port,
        'scheme' => $scheme
        ];
    } else {
        $hosts = [
            'host' => $host,
            'port' => $port,
            'scheme' => $scheme,
            'user' => $username,
            'pass' => $password,
            ];
    }
    $client = ClientBuilder::create()->setHosts($hosts)->build();
    return $client;
}
