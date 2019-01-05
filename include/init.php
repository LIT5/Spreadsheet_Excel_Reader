<?php
/****
初始化文件
 ****/
defined('ACC') || exit('ACC Denied');
define('DEBUG', true);
define('ROOT_PATH', dirname(__dir__));
define('LOGROOT', ROOT_PATH . '\log\\');

//设置报错级别
if (defined('DEBUG')) {
	error_reporting(E_ALL | E_STRICT);
} else {
	error_reporting(0);
}

// 自动加载类函数
function __autoloadinit($class) {
	switch (strtolower($class)) {

	case 'log':
	case 'conf':
	case 'mysql':
		require ROOT_PATH . '\include\\' . $class . '.class.php';
		break;

	case 'sheetmodel':
	case 'insertmodel':
	case 'commentmodel':
	case 'stockmodel':
		require ROOT_PATH . '\model\\' . $class . '.class.php';
		break;

	default:
		# code...
		require ROOT_PATH . '\lib\reader.php';
		break;
	}
}
spl_autoload_register('__autoloadinit');

//递归对数组进行转义;字符单引号等
function _adds($arr) {
	foreach ($arr as $k => $v) {
		if (is_string($v)) {
			$arr[$k] = addslashes($v);
		} elseif (is_array($v)) {
			$arr[$k] = _adds($v);
		}

	}
	return $arr;
}

?>