<?php
namespace Http;
use Media\Multi;
use Media\Multi\Part;
require 'autoload.php';

$multi = new Multi;
$part[] = new Part('json', json_encode(['name'=>'jing','email'=>'qq.com']), 'json.json');
$part[] = new Part('png', file_get_contents('/home/jing/png.png'), 'png.png');
$part[] = new Part('jpg', file_get_contents('/home/jing/jpg.jpg'), 'jpg.jpg');
$part[] = new Part('jpeg', file_get_contents('/home/jing/jpeg.jpeg'), 'jpeg.jpeg');
foreach ($part as $o) {
    $multi->add($o);
}

$c = new Client;
$c->setDebug(0x0);

$r = new Request;
$url = (new Url)->setHost('bbcash-api')->setPrefix('/api');
$r->url = $url;

# Multi::__toString pass
$r->url->setPath('/dev/acceptPostFiles');
$r->setMethod('POST');
$r->setContent($multi);
$res = $c->send($r);
echo $res;

# Multi::parse pass
$r->url->setPath('/dev/acceptPutPatchFiles');
$r->setMethod('PUT');
$r->setContent($multi);
echo $c->send($r);
