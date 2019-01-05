<?php
/****
库存处理入口文件
 ****/
//定义防跳墙访问常量
define('ACC', true);
//设置不超时
set_time_limit(0);
//设置PHP能使用的内存大小
@ini_set('memory_limit', '512M');
require dirname(__FILE__) . '/include/init.php';
// //库存更新
// $file_name = date('Ymd', time()) . '.xls';
// $readfile = SheetModel::getins();
// $sheets = $readfile->getSheet($file_name, 'stock');

// $Stock = StockModel::getIns();
// $Stock->mergeStock($sheets, 'stock');

//商品评价插入
$file_name = '商品评论表.xls';
$readfile = SheetModel::getins();
$readfile->getSheet($file_name, 'comment');

$comm = CommentModel::getins();
$comm->insert();

?>