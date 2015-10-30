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
  $results = $dbh->query('SELECT * FROM LoLChampion ORDER BY championId');
  $championDataArr = $results->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
  echo $e->getMessage();
  die();
}

//inctioのライブラリ呼び出し
include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';

$stmt = $dbh->prepare("DELETE FROM LoLItemInvestigate");
$stmt->execute();

foreach($championDataArr as $championData){
  $pageData = mb_convert_encoding(file_get_contents($championData["championUrl"]),'UTF-8','auto');
  $html = str_get_html($pageData);

  // If using local html file
  //$html = file_get_html('sample.html');

  $championId = $championData["championId"];
  $buildRecordId = 1;

  foreach($html->find('div[class=block]') as $buildRecord){

    $orderId = 1;

    foreach($buildRecord->find('img') as $item){
      $targetImagePath = $item->src;

      $itemFlg = strpos($targetImagePath, "http://www.probuilds.net/resources/img/items/");
      $emptyFlg = strpos($targetImagePath, "EmptyIcon.png");

      if($itemFlg !== false && $emptyFlg === false){
        $finalSlashIndex = mb_strrpos($targetImagePath, "/");
        $finalDotIndex = mb_strrpos($targetImagePath, ".");
        $itemId = substr($targetImagePath, $finalSlashIndex + 1, $finalDotIndex - $finalSlashIndex - 1);

        // Without conversing single quotation, couldn't insert record.
        $itemName = str_replace("'", "\'", $item->alt);

        try{
          $stmt = $dbh->prepare("INSERT INTO LoLItemInvestigate (championId, buildRecordId, orderId, itemId, itemName) VALUES (?, ?, ?, ?, ?)");
          $stmt->bindParam(1, $championId);
          $stmt->bindParam(2, $buildRecordId);
          $stmt->bindParam(3, $orderId);
          $stmt->bindParam(4, $itemId);
          $stmt->bindParam(5, $itemName);
          $stmt->execute();

          $orderId++;

        }catch(PDOException $e){
          echo $e->getMessage();
          die();

        }catch(Exception $e2){
          echo $e->getMessage();
          die();
        }

      }
    }

    $buildRecordId++;
  }

  echo "ChampionId: " . $championId . ", Start: " . date("H:i:s") . "<br>";
}
//"UPDATE LoLItem SET itemName = replace(itemName, \"\\\\\", \"\")";
/*
$stmt = $dbh->prepare("UPDATE LoLItem SET itemName = replace(itemName, \"\\\\\", \"\")");
$stmt->execute();
*/
echo "<br>End: " . date("H:i:s");