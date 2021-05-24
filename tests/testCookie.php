<?php
require 'autoload.php';
use Http\Request,
    Http\Client;

$r = new Request;
//$r->url->setHost('bbcash-api')->setPath('/api/dev/setCookie');
$r->url = 'http://bbcash-api/api/dev/setCookie';

$c = new Client;
$c->setDebug(0xf);
echo $c->send($r);
