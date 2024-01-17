<?php
require_once 'autoload.php';


$opt['debug'] = true;
App::Run($opt);

//$db = new Orm(1, '10.0.0.10', 'DBChessGame', 'sa', 'QWEqwe123');
//$db->sqlWatch = function ($sql, $time) {
//    echo "sql:$sql time:$time \n";
//
//};
//$total = 0;
//$result = $db->table('TbUser')->limit(2)->page(1)->pageTotal($total)->select(['UserGameID', 'WeChatNickName']);
//echo "total:$total\n";
//
//$db->table('TbUser')->where(['Id' => 111111])->update(['Sign' => '你好啊'], 2);
