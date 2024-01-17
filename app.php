<?php

require_once 'vendor/autoload.php';
$options = [
    'static' => ['/'],
    'index' => 'index',
    'debug' => false,
];
App::Run($options);

// windows 删除所有远程tag
// git tag -l | ForEach-Object { git push origin --delete $_ }
// 删除所有本地的tag
// git tag -l | ForEach-Object { git tag -d $_ }


/*
nginx配置

location ~ ^/(\.user.ini|\.htaccess|\.git|\.svn|\.project|LICENSE|README.md)
{
    return 404;
}

location /  {
    fastcgi_pass 127.0.0.1:9000;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/app.php;
}
*/