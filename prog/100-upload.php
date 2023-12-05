<?php
/**
 * ファイルをアップロードする。
 */
namespace men2nd;

/**
 * ページのタイトル
 */
const PAGE_TITLE = '100-upload';
/**
 * 移動先ページのURL
 */
const FORWARD_PAGE_URL = './200-result.php';

/**
 * 設定値を読み込む
 */
require_once '../class/Config.class.php';

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php echo PAGE_TITLE; ?></title>
<style>
html, body { width:100%; height:100%; margin:0px; padding:0px; }
section { line-height:3em; }
@media only screen and (max-width:700px)
{
	form, input { font-size:1.0em; }
	section { padding:1em; }
}
</style>
</head>
<body>
<section>
	<form action="<?php 
	echo FORWARD_PAGE_URL;
	if (isset($_GET[Config::ARGV_DEBUG_PARAM]) === true)
	{
		echo '?' . htmlspecialchars(Config::ARGV_DEBUG_PARAM, ENT_QUOTES, 'utf-8');
	}
	?>" method="post" enctype="multipart/form-data">
		ファイル：<br />
		<input type="file" name="<?php echo htmlspecialchars(Config::UPLOAD_FILE_PARAM_NAME, ENT_QUOTES, 'utf-8'); ?>" style="width:90%" /><br />
		<input type="submit" value="アップロード" />
	</form>
</section>
</body>
</html>
