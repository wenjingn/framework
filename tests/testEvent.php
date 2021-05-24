<?php
require 'autoload.php';

$req = new Http\Request;
$res = new Http\Response;

$req->on('receive', function($req, $res){
    print_r($req);
    print_r($res);
});

$req->trigger('receive', $res);
