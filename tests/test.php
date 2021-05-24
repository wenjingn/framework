<?php
namespace Http;
require 'autoload.php';


$account = [
    'username' => 'dev',
    'passwd'   => 'dev',
];

$items = [
    ['barcode' => 6902796694217, 'market' => 1, 'quantity' => 1, 'price' => 3],
    ['barcode' => 6924187858084, 'market' => 1, 'quantity' => 1, 'price' => 3],
];

$files = [
    new \Media\Multi\Part('json', json_encode($items), 'json.json'),
];

$c   = new Client;
$c->setDebug(0xc);
$req = new Request;
$res = new Response;

# post form data
$req->setMethod('POST');
$req->url = new Url;
$req->url->setHost('bbcash-api')->setPrefix('/api')->setPath('/login');
$req->setContent($account, 'form');
$res = $c->send($req);

# with cookie
$req->cookies->adds($res->cookies);
$req->url->setPath('/profile');
$c->send($req);

# post json type
$req->url->setPath('/stock/post');
$req->setContent($items, 'json');
$res = $c->send($req);
