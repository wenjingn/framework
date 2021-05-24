<?php
require 'autoload.php';
use Http\Response;

$res = file_get_contents(__DIR__.'/response');
$o = new Response;
$o->parse($res);
echo $o;
