<?php

echo "Start: " . date("H:i:s") . "<br><br>";

ini_set('display_errors', 'On');
$dsn = 'mysql:dbname=LoLItemHistory;host=localhost';
$user = 'root';
$password = 'root';

try{
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch (PDOException $e){
    echo $e->getMessage();
    die();
}

date_default_timezone_set("Asia/Tokyo");

try{
  $results = $dbh->query('SELECT * FROM LoLChampion WHERE championId BETWEEN 0 AND 2 ORDER BY championId');
  $championDataArr = $results->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
  echo $e->getMessage();
  die();
}

//inctioのライブラリ呼び出し
include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';
$stmt = $dbh->prepare("DELETE FROM LoLItem");
$stmt->execute();

$id = 0;

foreach($championDataArr as $championData){
  $pageData = mb_convert_encoding(file_get_contents($championData["championUrl"]),'UTF-8','auto');
  $html = str_get_html($pageData);

  // If using local html file
  //$html = file_get_html('sample.html');

  $championId = $championData["championId"];
  $record = 0;

  foreach($html->find('div[class=block]') as $buildRecord){
    $itemRecord = 0;

    foreach($buildRecord->find('img') as $item){
      $itemFlg = strpos($item->src, "http://www.probuilds.net/resources/img/items/");
      $emptyFlg = strpos($item->src, "EmptyIcon.png");

      if($itemFlg !== false && $emptyFlg === false){
        // Without conversing single quotation, couldn't insert record.
        $itemName = str_replace("'", "\'", $item->alt);
        $stmt = $dbh->prepare("INSERT INTO LoLItem (id, championId, record, item, name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $id);
        $stmt->bindParam(2, $championId);
        $stmt->bindParam(3, $record);
        $stmt->bindParam(4, $itemRecord);
        $stmt->bindParam(5, $itemName);
        $stmt->execute();
        $id++;
        $itemRecord++;
      }
    }

    $record++;
  }

  echo "ChampionId: " . $championId . ", Start: " . date("H:i:s") . "<br>";
}

echo "<br>End: " . date("H:i:s");