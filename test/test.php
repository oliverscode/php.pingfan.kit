<?php
require_once 'autoload.php';


//$opt['debug'] = true;
//App::Run($opt);


$cache = new FileCache();
//$cache->set("a", "123", 5);
echo $cache->get("a");