<?php
/****
单例评论数据插入model

$file_name = '商品评论表.xls';
$readfile = SheetModel::getins();
$readfile->getSheet($file_name, 'comment');
$comm = CommentModel::getins();
$comm->insert();
 ****/
class CommentModel {
	protected $mysql = NULL;
	protected $conf = array(
		// 评论星级
		'comment_rank' => 2,
		// 开始日期
		'begin_date' => '20150101',
		// 日期间隔
		'interval_day' => 2,
		// 每日开始时间
		'start_time' => 8,
		// 每日结束时间
		'end_time' => 10,
		// 评论最小条数
		'comment_min_num' => 3,
		// 评论最大条数
		'comment_max_num' => 5);
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

	public function insert() {
		# code...
		$conf = $this->conf;
		$sql = "
		INSERT INTO
			ecs_comment (
				comment_type,
				id_value,
				email,
				user_name,
				content,
				comment_rank,
				add_time,
				ip_address,
				STATUS,
				parent_id,
				user_id,
				comment_tag
			)
		SELECT
			c.comment_type,
			c.id_value,
			c.email,
			ifnull(e.user_name, 'unname'),
			c.content,
			c.comment_rank,
			UNIX_TIMESTAMP(c.add_time),
			c.ip_address,
			c. STATUS,
			c.parent_id,
			ifnull(e.user_id, '0'),
			'0'
		FROM
			(
			SELECT
				'0' AS comment_type,
				/*商品ID*/
				a.id_value,
				'' AS email,
				'' AS user_name,
				/*商品评论*/
				a.content,
				/*4-5星*/
				FLOOR({$conf['comment_rank']} + (RAND() * (5 + 1 - {$conf['comment_rank']}))) AS comment_rank,
				/**年*月*日至今-48小时某一随机日期时间*/
				DATE_FORMAT(
					CONCAT(
					/*begindate至当前时间-48小时的随机日期*/
						FROM_UNIXTIME(
							UNIX_TIMESTAMP({$conf['begin_date']}) + ROUND(
								RAND() * (
									UNIX_TIMESTAMP() - UNIX_TIMESTAMP({$conf['begin_date']})
								)
							) - 86400 * {$conf['interval_day']},
							'%Y%m%d'
						),
						/* 每天开始时间,结束时间*/
						LPAD(FLOOR({$conf['start_time']} + (RAND() * ({$conf['end_time']} + 1 - {$conf['start_time']}))), 2, 0),
						/*分钟*/
						LPAD(FLOOR(1 + (RAND() * 59)), 2, 0),
						/*秒*/
						LPAD(FLOOR(1 + (RAND() * 59)), 2, 0)
					),
					'%Y-%m-%d %H:%i:%s'
				) AS add_time,
				'49.4.177.197' AS ip_address,
				'1' AS STATUS,
				'0' AS parent_id,
				FLOOR(10000000000 + (RAND() * 3000)) AS mobile_phone,
				/*会员电话*/
				'1' AS is_cash_back
			FROM
				comment a
			WHERE
			/*评论最小条数,最大条数*/
				(
					SELECT
						COUNT(1)
					FROM
						comment b
					WHERE
						a.id_value = b.id_value
					AND a.content > b.content
				) < FLOOR({$conf['comment_min_num']} +(RAND() * ({$conf['comment_max_num']} + 1 - {$conf['comment_min_num']})))
			) c
		LEFT JOIN (
			SELECT
				d.user_id,
				d.user_name,
				d.mobile_phone
			FROM
				ecs_users d
			WHERE
				d.mobile_phone BETWEEN '10000000000'
			AND '10000002999'
			) e
		ON e.mobile_phone = c.mobile_phone;
		";

		$this->mysql->query($sql);
		$this->mysql->close();
	}
}
?>