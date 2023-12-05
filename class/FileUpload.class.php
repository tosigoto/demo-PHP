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
		$tmpName = isset($_FILES[$paramName]["tmp_name"]) ? //
		$_FILES[$paramName]["tmp_name"] : //
		null;

		if ($tmpName !== null && is_uploaded_file($tmpName) === true)
		{
			/**
			 * @var string アップロードされたファイルのファイル名
			 */
			$fileName = isset($_FILES[Config::UPLOAD_FILE_PARAM_NAME]["name"]) ? $_FILES[Config::UPLOAD_FILE_PARAM_NAME]["name"] : null;
			if ($fileName != null)
			{
				$filePath = "$destinationDir/$fileName";
				if (move_uploaded_file($tmpName, $filePath) === true)
				{
					$resultAry['ok'] = true;
					$resultAry['filePath'] = $filePath;

					$is_chmod_ok = chmod($filePath, $permissions);
					$permStringDec = decoct($permissions);

					if ($is_chmod_ok === true)
					{
						$resultAry['msg'] = "$fileName をアップロードしました。パーミッションを$permStringDec に変更しました。\n";
					}
					else
					{
						$resultAry['msg'] = "$fileName をアップロードしましたが、パーミッションを$permStringDec に変更できませんでした。\n";
					}
				}
				else
				{
					$resultAry['ok'] = false;
					$resultAry['msg'] = "ファイルをアップロードできませんでした。($tmpName)";
				}
			}
			else
			{
				$resultAry['ok'] = false;
				$resultAry['msg'] = "ファイル名を取得できませんでした。\n($tmpName)";
			}
		}
		else
		{
			$resultAry['ok'] = false;
			$resultAry['msg'] = "ファイルが選択されていませんでした。\n($tmpName)";
		}
		return $resultAry;
	}
}
