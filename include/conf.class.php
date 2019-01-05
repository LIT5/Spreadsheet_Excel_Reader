<?php
/****
单例配置文件读取类
读取config.inc.php
 ****/
class Conf {
	protected static $ins = null;
	protected $data = array();

	final protected function __construct() {
		require ROOT_PATH . '\conf\config.inc.php';
		$this->data = $_CFG;
	}
	final protected function __clone() {
	}
	final public function __destruct() {
	}

	public static function getIns() {
		if (!(self::$ins instanceof self)) {
			self::$ins = new self();
		}
		return self::$ins;
	}

	//魔术方法读取data
	public function __get($key) {
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		} else {
			echo "不存在" . $key . '<br/>';
		}

	}
	public function __set($key, $value) {
		if (isset($this->data[$key])) {
			echo "已存在" . $key . '<br/>';
		} else {
			$this->data[$key] = $value;
		}
	}

}
?>