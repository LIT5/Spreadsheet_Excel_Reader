<?php
/****
单例Log日志类

记录content信息到/log目录的日志

log::write($content,__CLASS__);

 ****/
class Log {
	final protected function __construct() {
	}
	final protected function __clone() {
	}
	final public function __destruct() {
	}

	//写日志
	public static function write($content, $class = '') {
		switch ($class) {
		case 'Mysql':
			$logFile = 'sql.log';
			break;

		default:
			$logFile = 'other.log';
			break;
		}
		$content = date('Y-m-d H:i:s') . ' ' . $content . "\r\n";
		//日志文件地址//判断大小分割日志
		$logPath = self::is_Bak($logFile);
		$fh = fopen($logPath, 'ab');
		fwrite($fh, $content);
		fclose($fh);
	}

	//读取日志大小M
	public static function is_Bak($logFile) {
		$logPath = LOGROOT . $logFile;
		if (!file_exists($logPath)) {
			//文件不存在，创建
			touch($logPath);
			return $logPath;
		}

		//如果存在，判断大小
		clearstatcache(true, $logPath);
		$size = filesize($logPath);
		if ($size <= 1024 * 1024) {
			return $logPath;
		}

		//此处文件大于1M
		if (!self::bak($logFile)) {
			return $logPath;
		} else {
			touch($logPath);
			return $logPath;
		}
	}

	//备份日志
	public static function bak($logFile) {
		//将大于1M的文件更名并转移到bak目录
		//旧目录
		$file = LOGROOT . $logFile;
		//新目录
		$newFile = LOGROOT . 'bak/' . date('ymd') . mt_rand(100, 999) . $logFile;
		//拷贝到新目录
		if (copy($file, $newFile)) {
			//删除旧目录下的文件
			return unlink($file);
		}
	}

}
?>