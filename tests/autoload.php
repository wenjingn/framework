<?php
$srcdir = __DIR__.'/../src';
require $srcdir.'/AutoLoader.php';
$loader = new \AutoLoader;
$loader->add($srcdir);
$loader->register();
