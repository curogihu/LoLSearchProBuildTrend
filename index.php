<?php

/*
$user = 'root';
$password = 'root';
$db = 'LoLItemHistory';
$host = 'localhost';
$port = 3306;

$link = mysqli_init();
$success = mysqli_real_connect(
   $link, 
   $host, 
   $user, 
   $password, 
   $db,
   $port
);
*/

// this code will be comment out after creating.
ini_set('display_errors', 'On');

$link = mysql_connect('localhost', 'root', 'root');

if (!$link) {
    die('接続失敗です。'.mysql_error());
}

$db_selected = mysql_select_db('LoLItemHistory', $link);

if (!$db_selected){
    die('データベース選択失敗です。'.mysql_error());
}

mysql_set_charset('utf8');

date_default_timezone_set("Asia/Tokyo");

//inctioのライブラリ呼び出し
include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';

// I have to add each character name when searching.

/*
$baseUrl = "http://www.probuilds.net/champions/";
$characterName = "Garen";
$pageData = mb_convert_encoding(file_get_contents($baseUrl . $characterName),'UTF-8','auto');
$html = str_get_html($page_data);
*/
$html = file_get_html('sample.html');

//$cnt = 0;

$id = 0;
$record = 0;

foreach($html->find('div[class=block]') as $buildRecord){

  $itemRecord = 0;

  foreach($buildRecord->find('img') as $item){

    $itemFlg = strpos($item->src, "http://www.probuilds.net/resources/img/items/");
    $emptyFlg = strpos($item->src, "EmptyIcon.png");

    if($itemFlg !== false && $emptyFlg === false){
      //echo $item->alt . '<br>';
      //echo $item->src . '<br>';

      $sql = "INSERT INTO LoLItem (id, record, item, name) VALUES (" . 
                $id . ", " . $record . ", " . $itemRecord . ", '" . str_replace("'", "\'", $item->alt) . "')";
      //echo($sql);
      //echo("<br>");

      $result_flag = mysql_query($sql);

      if (!$result_flag) {
          die('INSERTクエリーが失敗しました。'.mysql_error());
      }else{
        $id++;
        $itemRecord++;
      }
    }
  }

  $record++;

  echo("<br>");
}


//<div class='block alt'>
//foreach($html->find('div[class=property  js-property js-cassetLink]') as $house){

//echo ($cnt);