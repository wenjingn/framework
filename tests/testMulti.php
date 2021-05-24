<?php
require 'autoload.php';

use Http\Request,
    Http\Client,
    Http\Url;

$c = new Client(20);
function gen()
{
    $page = 1;
    while (true) {
        if ($page > 1000) return;
        $req = new Request;
        $req->url = new Url;
        $req->url->setHost('bbcash-api')->setPrefix('/api')->setPath('/dev/echoJson');
        $req->url->query->set('page', $page++);
        $req->on('receive', function($req, $res, $c, $info=null) {
            printf("req:%s, res:%s\n", $req->url->query, $res->content);
        });
        yield $req;
    }
}

$start = microtime(true);
$c->multi(gen());
/*
foreach (gen() as $i => $req) {
    if ($i == 1000) break;
    $c->send($req);
}
*/
printf("cost: %f\n", microtime(true)-$start);
