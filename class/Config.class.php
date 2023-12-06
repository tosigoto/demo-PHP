<?php

namespace men2nd;

/**
 * 設定値クラス
 */
class Config
{
	/**
	 * CSVデータのインポート先DBのホスト
	 */
	const DB_HOST = 'localhost';
	/**
	 * CSVデータのインポート先DBの名前
	 */
	const DB_NAME = 'db_demo';
	/**
	 * CSVデータのインポート先DBのユーザー
	 */
	const DB_USER = 'demouser';
	/**
	 * CSVデータのインポート先DBのパスワード
	 */
	const DB_PASS = 'demopass';

	/**
	 * CSVデータのインポート先テーブルの接頭辞
	 */
	const DB_TABLE_PREFIX = 't_people_';
	/**
	 * 計算後にCSVデータのインポート先テーブルを削除するかどうか
	 */
	const DB_TABLE_DELETE = true;

	/**
	 * @var string inputタグのnameパラメータ
	 */
	const UPLOAD_FILE_PARAM_NAME = 'upfile';

	/**
	 * @var string[] 都道府県名のマスターデータ
	 */
	const MASTER_TDFK_ARY = ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '山梨県', '長野県', '新潟県', '富山県', '石川県', '福井県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'];
	
	/**
	 * 計算結果の画面にデバッグ情報を表示するGETパラメータ
	 */
	const ARGV_DEBUG_PARAM = 'debug';
}
