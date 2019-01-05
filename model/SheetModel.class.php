<?php
/****
单例库存文件处理model
$readfile = SheetModel::getins();
$sheets = $readfile->getSheet($file_name, $signal);
 ****/
class SheetModel {
	private $fileRoot = ROOT_PATH . '\excelfile\\';
	//删除30天前的文件
	private $day = 30;
	private $ExcelReader = NULL;
	protected static $Ins = NULL;

	//方法前加final，方法不能被覆盖；类前加final，类不能被继承。
	final protected function __construct() {

		# code...
		$this->ExcelReader = new Spreadsheet_Excel_Reader();
		$this->ExcelReader->setOutputEncoding('utf-8');

	}
	final protected function __clone() {
		# code...
	}
	final public function __destruct() {
	}

	protected function read($file, $signal) {
		# code...
		$filePath = $this->fileRoot . $file;
		if (file_exists($filePath)) {
			$this->ExcelReader->read($filePath);
			$sheets = $this->validate($this->ExcelReader->sheets, $signal);
			return $sheets;
		} else {
			$log = "  file--" . $file . "----not exist";
			log::write($log, __CLASS__);
			exit;
		}
	}

	protected function validate($sheets, $signal) {
		# code...
		switch ($signal) {
		case 'stock':
			# code...
			$cols = array('goods_sn' => '商品编码', 'goods_num' => '实时库存', 'shop_price' => '零售价');
			foreach ($cols as $key => $value) {
				if ($pos = array_search($value, $sheets[0]['cells'][1])) {
					$cols[$key] = $pos;
				} else {
					$log = "  file data type error excute error:缺少{$value}字段";
					echo $log;
					log::write($log, __CLASS__);
					exit;
				}
			}

			// excel最后一行为统计，舍弃
			for ($i = 2; $i < $sheets[0]['numRows']; $i++) {
				$result[$i - 1]['goods_sn'] = trim($sheets[0]['cells'][$i][$cols['goods_sn']]);
				$result[$i - 1]['goods_num'] = floor($sheets[0]['cells'][$i][$cols['goods_num']]);
				$result[$i - 1]['shop_price'] = trim($sheets[0]['cells'][$i][$cols['shop_price']]);
			}

			return $result;
			break;
		case 'comment':
			# code...
			$cols = array('id_value' => '商品ID', 'content' => '商品评论');
			foreach ($cols as $key => $value) {
				if ($pos = array_search($value, $sheets[0]['cells'][1])) {
					$cols[$key] = $pos;
				} else {
					$log = "  file data type error excute error:缺少{$value}字段";
					echo $log;
					log::write($log, __CLASS__);
					exit;
				}
			}

			// excel
			for ($i = 2; $i <= $sheets[0]['numRows']; $i++) {
				$result[$i - 1]['id_value'] = trim($sheets[0]['cells'][$i][$cols['id_value']]);
				$result[$i - 1]['content'] = trim($sheets[0]['cells'][$i][$cols['content']]);
			}

			return $result;
			break;

		default:
			# code...
			return '暂时没有' . $signal . '类型的解析计划';
			break;
		}

		// return $result;

	}

	//删除几天前的文件
	protected function delOldfile($fileRoot, $day = 0) {
		# code...
		if (!$day) {
			return false;
		}
		if (is_dir($fileRoot)) {
			if ($dh = opendir($fileRoot)) {
				while (false !== ($file = readdir($dh))) {
					if ($file != "." && $file != "..") {
						$filePath = $this->fileRoot . $file;

						if (!is_dir($filePath)) {
							$fileDate = filemtime($filePath);
							$passDay = round((time() - $fileDate) / 86400);
							if ($passDay > $day) {
								//删除文件
								if (unlink($filePath)) {
									$log = "  file--" . $file . "---- delete successful";
									log::write($log, __CLASS__);
								} else {
									$log = "  file--" . $file . "---- delete error";
									log::write($log, __CLASS__);
								}
							}
						}
					}
				}
			}
			closedir($dh);
		}
	}

	public static function getIns() {
		if (!(self::$Ins instanceof self)) {
			self::$Ins = new self();
		}
		return self::$Ins;
	}

	public function getSheet($file, $signal) {

		// $this->delOldfile($this->fileRoot, $this->day);

		return $this->read($file, $signal);

	}

}
?>