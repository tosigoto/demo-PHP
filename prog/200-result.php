<?php
/**
 * 計算する。
 * 計算結果を表示する。
 */
namespace men2nd;

use DateTime;

/**
 * ページのタイトル
 */
const PAGE_TITLE = '200-result';
/**
 * 戻り先ページのURL
 */
const BACKWARD_PAGE_URL = '100-upload.php';

/**
 * 設定値を読み込む
 */
require_once '../class/Config.class.php';

/**
 * @var string 出力
 */
$output = "";

// ファイルアップロードを担当するクラスをインポート
require_once '../class/FileUpload.class.php';
/**
 * @var array CSVファイルアップロードの結果とメッセージ
 */
$csvUploadResultAry = FileUpload::doit(Config::UPLOAD_FILE_PARAM_NAME);
if ($csvUploadResultAry['ok'] === true)
{
	$tableName = Config::DB_TABLE_PREFIX . (new DateTime())->format('Ymd_His') . '_' . substr(bin2hex(random_bytes(32)), 0, 8);

	// 計算機能＆出力作成を担当するクラスをインポート
	require_once '../class/Calc.class.php';
	/**
	 * @var Calc 計算機能＆出力作成を担当するクラス
	 */
	$calcmng = new Calc($tableName, Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS, isset($_GET[Config::ARGV_DEBUG_PARAM]));

	$csvPath = $csvUploadResultAry['filePath'];
	/**
	 * @var string 計算結果
	 */
	$contents = $calcmng->doit($csvPath);

	$fileDeletedMsg = "";
	if (unlink($csvPath) === true)
	{
		$fileDeletedMsg = "アップロードされたファイルを削除しました。";
	}
	else
	{
		$fileDeletedMsg = "アップロードされたファイルを削除できませんでした。";
	}

	$output = <<< END
	<main>
	{$contents}

	<p>{$fileDeletedMsg}</p>
	</main>
	END;
}
else
{
	$output = <<< END
	<p>{$csvUploadResultAry['msg']}</p>
	END;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php echo PAGE_TITLE; ?></title>
	<script src="lib/chart.js" type="text/javascript"></script>
	<style>
	html, body { width:100%; height:100%; margin:0px; padding:0px; }

	section.data { float: left; }
	#age-area { width: 400px; }
	#blood-area { width: 300px; }
	#tdfk-area { width: 300px; }
	textarea.debug { width: 90%; height: 20em; background-color: #efefef; }

	@media only screen and (max-width:700px)
	{
		form, input { font-size:1.0em; }
		section { padding:1em; }
	}
	</style>
</head>
<body>
<?php echo $output; ?>

	<hr>
	<a href="<?php 
	echo BACKWARD_PAGE_URL;
	if (isset($_GET[Config::ARGV_DEBUG_PARAM]) === true)
	{
		echo '?' . htmlspecialchars(Config::ARGV_DEBUG_PARAM, ENT_QUOTES);
	}
	?>">戻る</a>
</body>
</html>
