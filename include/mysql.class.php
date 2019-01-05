<?php
/****
单例Mysql数据库类
 ****/
class Mysql {
	private $conf = array();
	private $conn = NULL;
	protected static $Ins = NULL;

	//方法前加final，方法不能被覆盖；类前加final，类不能被继承。
	final protected function __construct() {

		//读取配置文件
		$this->conf = conf::getIns();
		//连接数据库
		$this->connect($this->conf->server_name, $this->conf->username, $this->conf->password, $this->conf->database);
		//设置默认客户端字符集
		$this->set_Char($this->conf->charset);
	}
	final protected function __clone() {
		# code...
	}
	final public function __destruct() {
	}

	public static function getIns() {
		if (!(self::$Ins instanceof self)) {
			self::$Ins = new self();
		}
		return self::$Ins;
	}

	private function connect($h, $u, $p, $d) {
		// $this->conn = mysql_connect($h,$u,$p) or die("error connecting");
		$this->conn = mysqli_connect($h, $u, $p, $d);
		if (!$this->conn) {
			$err = new Exception('连接失败');
			throw $err;
		}
	}

	private function set_Char($char) {
		// $sql='set names '.$char;
		// $this->query($sql);
		mysqli_set_charset($this->conn, $char);
	}

	public function query($sql) {
		log::write($sql, __CLASS__);
		return mysqli_query($this->conn, $sql);
	}
	public function getAll($sql) {
		$list = array();
		$rs = $this->query($sql);
		if (!$rs) {
			return false;
		}
		while ($row = mysqli_fetch_assoc($rs)) {
			$list[] = $row;
		}
		return $list;
	}
	public function getRow($sql) {
		$rs = $this->query($sql);
		if (!$rs) {
			return false;
		}
		return mysqli_fetch_assoc($rs);
	}
	public function getOne($sql) {
		$rs = $this->query($sql);
		if (!$rs) {
			return false;
		}
		return mysqli_fetch_row($rs)[0];
	}
	public function close() {
		mysqli_close($this->conn);
	}

}

?>