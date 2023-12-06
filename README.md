# demo-PHP  
ダミー個人情報CSVデータの解析結果を表示するプログラム。  
  
## ファイル構成  
.  
├── class  
│   ├── Calc.class.php  計算クラス  
│   ├── Config.class.php  設定値クラス  
│   ├── DbImport.class.php  CSVファイルをDBにインポートするクラス  
│   └── FileUpload.class.php  ファイルをアップロードするクラス  
└── prog  ドキュメントルート  
│   ├── 100-upload.php  ここからファイルのアップロードを行う　＝＞　200-result.phpに遷移する  
│   ├── 200-result.php  計算結果を表示する  
│   └── lib/chart.js  グラフ表示JS  


## 動作確認環境  
* PHP 8.1.2  
* MySQL 8.0.35  
  * Config::DB_NAMEに設定した名前のデータベース  
      DB作成SQL例：  
        CREATE DATABASE IF NOT EXISTS db_demo CHARACTER SET utf8mb4;  
  * Config::DB_USERに設定した名前 と DB_PASSに設定したパスワード のユーザー  
      ユーザー設定例：  
        CREATE user demouser@localhost identified by 'demopass';  
        GRANT ALL PRIVILEGES ON db_demo.* TO demouser@localhost;  
        FLUSH PRIVILEGES;  
* Apache 2.4.52  
* ダミー個人情報データ  
  個人情報テストデータジェネレーター( https://testdata.userlocal.jp/ )にて作成。  
  年齢,血液型,住所を必ず含むデータ。  
