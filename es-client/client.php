<?php
    require 'vendor/autoload.php';
    use Elasticsearch\ClientBuilder;
function create_es_client ($host , $port, $scheme = 'http', $username = "", $password = "") {
    if ($username == "" && $password == "") {
        $hosts = [
            $scheme.'://'.$host.':'.$port
        ];
    } else {
        $hosts = [
            $scheme.'://'.$username.':'.$password.'@'.$host.':'.$port
        ];
    }
    $client = ClientBuilder::create()->setHosts($hosts)->build();
    return $client;
}
