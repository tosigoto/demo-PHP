<?php
namespace men2nd;

use PDO;

// 設定値クラスをインポート
require_once 'Config.class.php';
// CSVファイルをDBにインポートするクラスをインポート
require_once 'DbImport.class.php';

/**
 * 計算クラス。
 * CSVデータをDBにインポートする。
 * コンテンツを作成する。
 * インポートしたCSVデータをDBから削除する。
 * コンテンツを返す。
 */
class Calc
{
	/**
	 * @var string DBにインポートするCSVデータの格納先テーブル名
	 */
	private $tableName;
	/**
	 * @var bool デバッグ情報を表示するかどうか
	 */
	private $isDebug;
	/**
	 * @var DbImport DB管理クラスのインスタンス
	 */
	private $dbmng;

	/**
	 * @param string $tableName  DBにインポートするCSVデータの格納先テーブル名
	 */
	function __construct($tableName, $host, $db, $user, $pass, $isDebug = false)
	{
		$this->tableName = $tableName;
		$this->isDebug = $isDebug;
		/**
		 * @var DbImport DB管理クラス。テーブル作成・インポート・抽出・削除などを行う。
		 */
		$this->dbmng = new DbImport($host, $db, $user, $pass);
	}

	/**
	 * CSVデータをDBにインポートする。
	 * コンテンツを作成する。
	 * インポートしたCSVデータをDBから削除する。
	 * コンテンツを返す。
	 * 
	 * @param string $csvPath  インポートするCSVファイルのパス
	 * @return string $contents  
	 */
	function doit($csvPath)
	{
		// CSVデータをDBにインポートする。
		$this->importCsvToDb($csvPath);
		// コンテンツを作成する。
		$contents = $this->getContents();
		// インポートしたCSVデータをDBから削除する。
		if (Config::DB_TABLE_DELETE === true)
		{
			$this->dbmng->resetData();
		}
		// コンテンツを返す。
		return $contents;
	}

	/**
	 * CSVファイルに都道府県カラムを追加する。
	 * CSVファイルをDBにインポートする。
	 * 
	 * @param string $csvPath  インポートするCSVファイルのパス
	 */
	private function importCsvToDb($csvPath)
	{
		/**
		 * @var string テーブル作成SQL文
		 */
		$createSql = <<< END
		CREATE TABLE IF NOT EXISTS {$this->tableName} (
		  id INT NOT NULL AUTO_INCREMENT
		, age INT NOT NULL DEFAULT 0
		, blood VARCHAR(2) NOT NULL DEFAULT 'XX'
		, address VARCHAR(100) NOT NULL DEFAULT ''
		, tdfk VARCHAR(4) NULL DEFAULT ''
		, PRIMARY KEY (id)
		);
		END;
		/**
		 * @var array テーブル内のINTタイプのカラム名の配列
		 */
		$intTypeColumnsAry = ['age'];
		/**
		 * @var array
		 * DBにインポートするカラム。
		 * CSVファイル内のでカラム'oldColumn' => インポート先テーブル内でのカラム'newColumn'
		 */
		$requiredColumns = [
			'年齢' => 'age',
			'血液型' => 'blood',
			'住所' => 'address',
			'都道府県' => 'tdfk',
		];

		// CSVファイルをDBにインポートする
		$this->dbmng->importCsv($csvPath, $requiredColumns, $this->tableName, $createSql, $intTypeColumnsAry);
		// CSVインポート先テーブルに都道府県カラムをセットする
		$this->setTdfkIntoDb();
	}

	/**
	 * CSVインポート先テーブルに都道府県カラムをセットする
	 */
	private function setTdfkIntoDb()
	{
		$idAndAddressAry = $this->dbmng->getData("SELECT id, address FROM {$this->tableName}");

		/**
		 * @var string 都道府県をセットするSQL文
		 */
		$updateSql = "UPDATE {$this->tableName} SET tdfk = ? WHERE id = ?;";
		$pdo = $this->dbmng->getPdo();
		$stmt = $pdo->prepare($updateSql);

		// トランザクションを開始する
		$pdo->beginTransaction();
		foreach (array_slice($idAndAddressAry, 1) as $ary)
		{
			$id = $ary[0];
			$address = $ary[1];

			/**
			 * @var string DBに入力する都道府県
			 */
			$tdfk = '';
			foreach (Config::MASTER_TDFK_ARY as $masterTdfk)
			{
				if (str_starts_with($address, $masterTdfk))
				{
					$tdfk = $masterTdfk;
					break;
				}
			}

			$stmt->bindValue(1, $tdfk, PDO::PARAM_STR);
			$stmt->bindValue(2, $id, PDO::PARAM_INT);
			$stmt->execute();
		}
		// トランザクションをコミットする
		$pdo->commit();
		// DB接続を閉じる
		$pdo = null;
	}

	/**
	 * デバッグ情報をhtml形式で返す。
	 * 
	 * @param string[] &$debugBufAry  デバッグ情報の配列
	 * @return string
	 */
	private function getDebugPart(&$debugBufAry)
	{
		return '<textarea class="debug" readonly>' . htmlspecialchars(implode("", $debugBufAry), ENT_QUOTES, 'utf-8') . '</textarea>';
	}

	/**
	 * 結果データから抽出し、
	 * ラベル、データ、デバッグデータをセットする。
	 * 
	 * @param array &$resultAry DBから抽出されたデータ
	 * @param array &$labelBuf ラベル格納配列
	 * @param array &$dataBuf データ格納配列
	 * @param array &$debugBuf デバッグ情報格納配列
	 */
	private function getLabelDataDebug(&$resultAry, &$labelBuf, &$dataBuf, &$debugBuf)
	{
		foreach (array_slice($resultAry, 1) as $ary)
		{
			$label = htmlspecialchars($ary[0], ENT_QUOTES, 'utf-8');
			$labelBuf[] = "'$label'";
			$dataBuf[] = $ary[1];

			if ($this->isDebug === true)
			{
				$debugBuf[] = implode("\t", $ary) . "\n";
			}
		}
	}

	/**
	 * 計算結果のコンテンツ（年齢のヒストグラム、血液型の円グラフ、都道府県別人数のTOP5グラフ）を返す。
	 * 
	 * @return string
	 */
	private function getContents()
	{
		/**
		 * @var string 画面表示用　年齢　ラベル
		 */
		$output_age_labels = "";
		/**
		 * @var string 画面表示用　年齢　データ
		 */
		$output_age_data = "";
		/**
		 * @var string
		 */
		$output_age_debug = "";
		/**
		 * @var string[]
		 */
		$debug_age_buf = [];
		{// 年齢のヒストグラム
			/**
			 * @var string[]
			 */
			$unionBuf = [];
			$ageStep = 10;
			$age = 0;
			while ($age < 100)
			{
				/**
				 * @var int
				 */
				$begAge = $age;
				/**
				 * @var int
				 */
				$endAge = $age + $ageStep;
				$sql = <<< END
				SELECT '$begAge 代' AS age, COUNT(*) AS cnt FROM {$this->tableName} WHERE $begAge <= age AND age < $endAge
				END;
				$unionBuf[] = $sql;

				$age = $endAge;
			}
			$unionBuf[] = <<< END
			SELECT '$age 歳以上' AS age, COUNT(*) AS cnt FROM {$this->tableName} WHERE $age <= age
			END;

			$totalSql = implode("\n  UNION ", $unionBuf). ';';
			if ($this->isDebug === true)
			{
				$debug_age_buf[] = "$totalSql\n\n";
			}

			$resultAry = $this->dbmng->getData($totalSql);
			/**
			 * @var string[]
			 */
			$labelBuf = [];
			/**
			 * @var string[]
			 */
			$dataBuf = [];

			$this->getLabelDataDebug($resultAry, $labelBuf, $dataBuf, $debug_age_buf);

			$output_age_labels = implode(', ', $labelBuf);
			$output_age_data = implode(', ', $dataBuf);
			if ($this->isDebug === true)
			{
				$output_age_debug = $this->getDebugPart($debug_age_buf);
			}
		}

		/**
		 * @var string 画面表示用　血液型　ラベル
		 */
		$output_blood_labels = "";
		/**
		 * @var string 画面表示用　血液型　データ
		 */
		$output_blood_data = "";
		/**
		 * @var string
		 */
		$output_blood_debug = "";
		/**
		 * @var string[]
		 */
		$debug_blood_buf = [];
		{ // 血液型の円グラフ
			$querySql_blood = <<< END
			SELECT
			  blood
			  , COUNT(*) AS cnt
			FROM
			  {$this->tableName}
			GROUP BY blood
			ORDER BY blood
			;
			END;
			if ($this->isDebug === true)
			{
				$debug_blood_buf[] = "$querySql_blood\n\n";
			}

			$resultAry = $this->dbmng->getData($querySql_blood);
			/**
			 * @var string[]
			 */
			$labelBuf = [];
			/**
			 * @var string[]
			 */
			$dataBuf = [];

			$this->getLabelDataDebug($resultAry, $labelBuf, $dataBuf, $debug_blood_buf);

			$output_blood_labels = implode(', ', $labelBuf);
			$output_blood_data = implode(', ', $dataBuf);
			if ($this->isDebug === true)
			{
				$output_blood_debug = $this->getDebugPart($debug_blood_buf);
			}
		}

		/**
		 * @var string 画面表示用　都道府県　ラベル
		 */
		$output_tdfk_labels = "";
		/**
		 * @var string 画面表示用　都道府県　データ
		 */
		$output_tdfk_data = "";
		/**
		 * 
		 */
		$output_tdfk_debug = "";
		$debug_tdfk_buf = [];
		{ // 都道府県別人数のTOP5グラフ
			$querySql_tdfk = <<< END
			SELECT
			  tdfk
			  , COUNT(*) AS cnt
			FROM
			  {$this->tableName}
			GROUP BY tdfk
			ORDER BY cnt DESC, tdfk ASC
			LIMIT 0, 5
			;
			END;
			if ($this->isDebug === true)
			{
				$debug_tdfk_buf[] = "$querySql_tdfk\n\n";
			}

			$resultAry = $this->dbmng->getData($querySql_tdfk);
			$labelBuf = [];
			$dataBuf = [];

			$this->getLabelDataDebug($resultAry, $labelBuf, $dataBuf, $debug_tdfk_buf);

			$output_tdfk_labels = implode(', ', $labelBuf);
			$output_tdfk_data = implode(', ', $dataBuf);
			if ($this->isDebug === true)
			{
				$output_tdfk_debug = $this->getDebugPart($debug_tdfk_buf);
			}
		}

		return <<< END
		<section id="age-area" class="data">
		<h2>年齢　ヒストグラム</h2>
		<canvas id="mychart-bar"></canvas>
		<script type="text/javascript">
		var ctx = document.getElementById('mychart-bar');
		var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: [$output_age_labels],
			datasets: [{
			label: '人数',
			data: [$output_age_data],
			backgroundColor: 'teal',
			}],
		},
		});
		</script>
		$output_age_debug
		</section>


		<section id="blood-area" class="data">
		<h2>血液型　円グラフ</h2>
		<canvas id="mychart-pie"></canvas>
		<script>
		var ctx = document.getElementById('mychart-pie');
		ctx.width = 300;
		ctx.height = 300;
		var myChart = new Chart(ctx, {
		type: 'pie',
		data: {
			labels: [$output_blood_labels],
			datasets: [{
			label: '人数',
			data: [$output_blood_data],
			backgroundColor: ['red', 'purple', 'blue', 'green'],
			}],
		},
		});
		</script>
		$output_blood_debug
		</section>


		<section id="tdfk-area" class="data">
		<h2>都道府県　人数TOP5</h2>
		<canvas id="mychart-horizbar"></canvas>
		<script type="text/javascript">
		var ctx = document.getElementById('mychart-horizbar');
		var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: [$output_tdfk_labels],
			datasets: [{
			label: '人数',
			data: [$output_tdfk_data],
			backgroundColor: 'teal',
			}],
		},
		options: {
			indexAxis: 'y',
		},
		});
		</script>
		$output_tdfk_debug
		</section>


		<br style="clear: both;" >
		END;
	}
}
