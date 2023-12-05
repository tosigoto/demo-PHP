# demo-PHP  
ダミー個人情報CSVデータの解析結果を表示するプログラム。  
  
.  
├── class  
│   ├── Calc.class.php  計算クラス  
│   ├── Config.class.php  設定値クラス  
│   ├── DbImport.class.php  CSVファイルをDBにインポートするクラス  
│   └── FileUpload.class.php  ファイルをアップロードするクラス  
├── lib  
│   └── chart.js  グラフ表示JS  
├── prog  
│   ├── 100-upload.php  ここからファイルのアップロードを行う　＝＞　200-result.phpに遷移する  
│   └── 200-result.php  計算結果を表示する  
└── upload  ファイルアップロード先ディレクトリ。要パーミッション:0750  
