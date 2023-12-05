<?php
namespace men2nd;

use PDO;
use PDOException;

/**
 * @var class DB管理クラス。テーブル作成・インポート・抽出・削除などを行う。
 */
class DbImport
{
	/**
	 * @var string CSVデータを格納するテーブルの名前
	 */
	private $tableName;

	private $host;
	private $db;

	private $user;
	private $pass;

	private $charset;

	/**
	 * @param string $host  ホスト名
	 * @param string $db  データベース名
	 * @param string $user  ユーザー名
	 * @param string $pass  パスワード
	 * @param string $charset  文字コード
	 */
	function __construct($host, $db, $user, $pass, $charset = 'utf8mb4')
	{
		$this->host = $host;
		$this->db = $db;

		$this->user = $user;
		$this->pass = $pass;

		$this->charset = $charset;
	}

	/**
	 * PDOインスタンスを返す。
	 */
	public function getPdo()
	{
		$dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

		try {
			$pdo = new PDO($dsn, $this->user, $this->pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			die('Database connection failed: ' . $e->getMessage());
		}
		return $pdo;
	}

	/**
	 * CSVデータを計算に必要なデータだけにする。
	 * 
	 * @param array $csv  CSVデータ
	 * @param array $requiredColumns  必要なカラム
	 * @return array $extractedCsv  必要なカラムのデータだけになったCSVデータ
	 */
	private function getRequiredDataCsv(&$csv, &$requiredColumns)
	{
		$csvColumnAry = $csv[0];

		/**
		 * @var string[] CSVファイル内でのカラム名
		 */
		$oldColumnAry = array_keys($requiredColumns);
		/**
		 * @var string[] DB内でのカラム名
		 */
		$newColumnAry = array_values($requiredColumns);

		/**
		 * @var int[] 必要なカラムの位置を記憶する配列
		 */
		$requiredColumnsIndexAry = [];
		$imax = count($csvColumnAry);
		for ($i = 0; $i < $imax; $i++)
		{ 
			$col = $csvColumnAry[$i];
			if (in_array($col, $oldColumnAry, true) === true)
			{
				$requiredColumnsIndexAry[] = $i;
			}
		}

		$extractedCsv = [$newColumnAry];
		foreach (array_slice($csv, 1) as $csvRow)
		{
			/**
			 * @var array 必要なデータの配列
			 */
			$matchedValueAry = [];
			foreach ($requiredColumnsIndexAry as $i)
			{
				$matchedValueAry[] = $csvRow[$i];
			}
			$extractedCsv[] = $matchedValueAry;
		}

		return $extractedCsv;
	}

	/**
	 * CSVファイルをDBにインポートする。
	 * 
	 * @param string $csvFilePath  インポートするCSVファイルのパス
	 * @param string[] $requiredColumns  インポートするカラム
	 * @param string $tableName  DBのテーブル名
	 * @param string $createSql  テーブルを作るSQL文
	 * @param array $intTypeColumnsAry  テーブル内のINTタイプのカラム名の配列
	 */
	public function importCsv($csvFilePath, $requiredColumns, $tableName, $createSql, $intTypeColumnsAry)
	{
		$pdo = $this->getPdo();

		// create
		$stmt = $pdo->prepare($createSql);
		$stmt->execute();

		// CSVファイルを読み込む
		$csv = array_map('str_getcsv', file($csvFilePath));
		// 必要なデータだけにする
		$csv = $this->getRequiredDataCsv($csv, $requiredColumns);

		$columns = $csv[0];
		$placeHolderLine = implode(',', array_fill(0, count($columns), '?'));
		
		// データベースにデータを挿入
		$columnsLine = implode(',', $columns);
		$sql = "INSERT INTO {$tableName} ($columnsLine) VALUES ($placeHolderLine)";
		$stmt = $pdo->prepare($sql);

		$pdo->beginTransaction();
		foreach (array_slice($csv, 1) as $row)
		{
			// パラメータをバインド
			foreach ($columns as $idx => $col)
			{
				$value = $row[$idx] ?? '';
				$paramType = in_array($col, $intTypeColumnsAry) ? PDO::PARAM_INT : PDO::PARAM_STR;

				$stmt->bindValue($idx + 1, $value, $paramType); // パラメータは1から始まるため、$idx + 1 としている。
			}

			// クエリを実行
			$stmt->execute();
		}
		$pdo->commit();
		// DB接続を閉じる
		$pdo = null;

		// テーブル名を保持
		$this->tableName = $tableName;
	}

	/**
	 * DBからデータを取得する。
	 * 二次元配列で返す。
	 * 
	 * @param string $querySql  DBからデータを抽出するSQL文
	 * @return array $retAry  データを格納した二次元配列。一行目はカラム名。
	 */
	public function getData($querySql)
	{
		/**
		 * @var array データを格納する二次元配列
		 */
		$retAry = [];

		$pdo = $this->getPdo();

		$stmt = $pdo->prepare($querySql);
		$res = $stmt->execute();
		if ($res === true)
		{
			/**
			 * @var int 行数をカウントする
			 */
			$rowCounter = 0;
			while ($data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				// 最初の行からカラム名を取得する
				if ($rowCounter == 0)
				{
					$retAry[] = array_keys($data);
				}
				// データを取得する
				$retAry[] = array_values($data);
				$rowCounter += 1;
			}
		}
		$pdo = null;
		return $retAry;
	}

	/**
	 * インポートしたデータを削除する。(DROP TABLE)
	 */
	public function resetData()
	{
		$this->deleteThisTable($this->tableName);
	}

	/**
	 * 指定したテーブルを削除する。(DROP TABLE)
	 * 
	 * @return bool $result true or false
	 */
	public function deleteThisTable($tableName)
	{
		$pdo = $this->getPdo();
		$stmt = $pdo->prepare("DROP TABLE {$tableName};");
		$result = $stmt->execute();
		$pdo = null;

		return $result;
	}
}
