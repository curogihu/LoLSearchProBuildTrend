<?php

// this code will be comment out after creating.
ini_set('display_errors', 'On');


/*
  old version
$link = mysql_connect('localhost', 'root', 'root');

if (!$link) {
    die('接続失敗です。'.mysql_error());
}

$db_selected = mysql_select_db('LoLItemHistory', $link);

if (!$db_selected){
    die('データベース選択失敗です。'.mysql_error());
}

mysql_set_charset('utf8');
*/

$dsn = 'mysql:dbname=LoLItemHistory;host=localhost';
$user = 'root';
$password = 'root';

try{
    $dbh = new PDO($dsn, $user, $password);

}catch (PDOException $e){
    print('Error:'.$e->getMessage());
    die();
}

date_default_timezone_set("Asia/Tokyo");

//inctioのライブラリ呼び出し
include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';

// I have to add each character name when searching.


$baseUrl = "http://www.probuilds.net/champions/";
$characterName = "Garen";
$pageData = mb_convert_encoding(file_get_contents($baseUrl . $characterName),'UTF-8','auto');
$html = str_get_html($pageData);

// If using local html file
//$html = file_get_html('sample.html');

$id = 0;
$record = 0;

foreach($html->find('div[class=block]') as $buildRecord){

  $itemRecord = 0;

  foreach($buildRecord->find('img') as $item){

    $itemFlg = strpos($item->src, "http://www.probuilds.net/resources/img/items/");
    $emptyFlg = strpos($item->src, "EmptyIcon.png");

    if($itemFlg !== false && $emptyFlg === false){
      $itemName = str_replace("'", "\'", $item->alt);
      //echo $item->alt . '<br>';
      //echo $item->src . '<br>';

//      $sql = "INSERT INTO LoLItem (id, record, item, name) VALUES (" . 
//                $id . ", " . $record . ", " . $itemRecord . ", '" . str_replace("'", "\'", $item->alt) . "')";
      //echo($sql);
      //echo("<br>");

//      $result_flag = mysql_query($sql);

/*
      if (!$result_flag) {
          die('INSERTクエリーが失敗しました。'.mysql_error());
      }else{
        $id++;
        $itemRecord++;
      }

*/
      $stmt = $dbh->prepare("INSERT INTO LoLItem (id, record, item, name) VALUES (?, ?, ?, ?)");
      $stmt->bindParam(1, $id);
      $stmt->bindParam(2, $record);
      $stmt->bindParam(3, $itemRecord);
      $stmt->bindParam(4, $itemName);
      $stmt->execute();

//      echo "INSERT INTO LoLItem (id, record, item, name) VALUES (" . 
//                $id . ", " . $record . ", " . $itemRecord . ", '" . str_replace("'", "\'", $item->alt) . "')<br>";

      $id++;
      $itemRecord++;

    }
  }

  $record++;

  echo("<br>");
}


//<div class='block alt'>
//foreach($html->find('div[class=property  js-property js-cassetLink]') as $house){

//echo ($cnt);