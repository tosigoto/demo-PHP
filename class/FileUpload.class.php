<?php
namespace men2nd;

require_once '../class/Config.class.php';

class FileUpload
{
	/**
	 * ファイルをアップロードする。
	 *
	 * @param string $paramName  inputタグのnameパラメータ
	 * @param string $destinationDir  アップロードされたファイルの移動先パス
	 * @param int $permissions  アップロードされたファイルのパーミッション
	 * @return array $resultAry  ['ok' => true or false, 'msg' => メッセージ]
	 */
	final public static function doit($paramName, $destinationDir, $permissions = 0644)
	{
		/**
		 * @var array アップロード結果を返す。
		 * 'ok'   アップロードの成否(true or false)
		 * 'msg'   メッセージ
		 */
		$resultAry = ['ok' => false, 'msg' => null, 'filePath' => null];

		/**
		 * @var string アップロードされたファイルの temporary name
		 */
		$tmpName = $_FILES[$paramName]["tmp_name"] ?? null;
		if ($tmpName !== null && is_uploaded_file($tmpName) === true)
		{
			$resultAry['ok'] = true;
			$resultAry['msg'] = 'ファイルをアップロードしました。';
			$resultAry['filePath'] = $tmpName;
		}
		else
		{
			$resultAry['ok'] = false;
			$resultAry['msg'] = "ファイルが選択されていませんでした。";
		}
		return $resultAry;
	}
}
