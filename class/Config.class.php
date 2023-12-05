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
	 * @var string ファイルのアップロード先ディレクトリ
	 */
	const UPLOAD_DIR_NAME = 'upload';

	/**
	 * 計算結果の画面にデバッグ情報を表示するGETパラメータ
	 */
	const ARGV_DEBUG_PARAM = 'debug';
}
