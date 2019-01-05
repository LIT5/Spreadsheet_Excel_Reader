<?php
/****
单例库存数据处理model

$file_name = date('Ymd', time()) . '.xls';
$readfile = SheetModel::getins();
$sheets = $readfile->getSheet($file_name, 'stock');

$Stock = StockModel::getIns();
$Stock->mergeStock($sheets, 'stock');
 ****/

class StockModel {
	protected $mysql = NULL;
	protected static $Ins = NULL;
	//方法前加final，方法不能被覆盖；类前加final，类不能被继承。
	final protected function __construct() {
		# code...
		$this->mysql = Mysql::getins();
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

	protected function insert($sheets, $table) {
		# code...
		$keys = join(",", array_keys($sheets[1]));
		$values = array();
		foreach ($sheets as $row) {
			array_push($values, "('" . join("','", _adds(array_values($row))) . "')");
		}
		$values = join(",", array_values($values));

		$insert = "INSERT INTO {$table} ({$keys}) values {$values} ";

		// $mysql = Mysql::getins();
		$this->mysql->query($insert);
	}

	public function mergeStock($sheets, $table) {
		$this->insert($sheets, $table);

		//插入新商品
		$sql = "
		INSERT INTO ecs_goods (
			goods_sn,
			goods_number,
			shop_price,
		goods_desc,
		supplier_status_txt,
		is_catindex
		)
		SELECT
			CONCAT('ESC', a.goods_sn) AS goods_sn,
			a.goods_num,
			a.shop_price,
			'goods_desc',
			'supplier_status_txt',
			NULL
		FROM
			stock a
		WHERE
			(
				SELECT
					count(1) AS checknum
				FROM
					ecs_goods b
				WHERE
					b.goods_sn = CONCAT('ESC', a.goods_sn)
			) = 0
			GROUP BY a.goods_sn";
		// echo "$sql";
		// exit;
		$this->mysql->query($sql);

		//上架商品
		$sql = "UPDATE ecs_goods a INNER JOIN (SELECT * from stock bb GROUP BY bb.goods_sn)b on a.goods_sn = CONCAT('ESC', b.goods_sn) set a.is_on_sale=1  WHERE b.goods_num >= 10";
		// echo "$sql";
		// exit;
		$this->mysql->query($sql);

		//更新上架商品的价格
		$sql = "UPDATE ecs_goods a INNER JOIN (SELECT * from stock bb GROUP BY bb.goods_sn)b on a.goods_sn = CONCAT('ESC', b.goods_sn) set a.goods_number = b.goods_num, a.shop_price = b.shop_price WHERE a.is_on_sale=1";
		$this->mysql->query($sql);

		//下架商品
		$sql = "UPDATE ecs_goods a INNER JOIN (SELECT * from stock bb GROUP BY bb.goods_sn)b on a.goods_sn = CONCAT('ESC', b.goods_sn) set a.is_on_sale=0  WHERE b.goods_num < 1 or (b.goods_num<20 and b.shop_price<20)";
		$this->mysql->query($sql);

		//清除当天临时表
		$sql = 'truncate stock';
		$this->mysql->query($sql);

		$this->mysql->close();
	}
}

?>