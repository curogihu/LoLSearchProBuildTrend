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
  $results = $dbh->query('SELECT * FROM LoLChampion WHERE championId ORDER BY championId');
  $championDataArr = $results->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
  echo $e->getMessage();
  die();
}

//inctioのライブラリ呼び出し
include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';

// If using local html file
$html = file_get_html('buildHistory.html');
$cnt = 0;

$test = getBuildHistoryCordsFromPage($html);
$number = getParticipantIdFromJavascriptCords($test);
$aaa = strpos($test, '{"eventType":"ITEM_PURCHASED"');
$cnt = 0;

//$json = convertStringToJson($test);

//echo $test;
//echo "number = " . $number;

$firstBranketIndex = strpos($test, "{");
$lastBranketIndex = strrpos($test, "}");

//echo "first = " . $firstBranketIndex;
//echo "last = " . $lastBranketIndex;
$extractStr = mb_substr($test, $firstBranketIndex, $lastBranketIndex - $firstBranketIndex + 1);
$obj = json_decode($extractStr, true);

//echo var_dump($json);

/*
foreach($json[0]->frames[0]->events as $tmpArr){
  echo "cnt = " . $cnt . " , contents = " . var_dump($tmpArr);
  $cnt++;
}
*/

/*
foreach ($obj as $value) {
  echo var_dump($obj["frames"][1]);
}
*/

//foreach ($obj["frames"][1] as $value) {
  //echo var_dump($obj["frames"][1]);

  //echo var_dump($obj["frames"][1]["events"][1]);
  //echo var_dump($obj["frames"][1]["events"][4]);
//}
$cnt = 0;

/*
foreach ($obj["frames"][3]["events"] as $record) {
  echo var_dump($record);
  echo "<br>";
  $cnt++;
}

echo ($cnt - 1);
*/
foreach ($obj["frames"][14]["events"][0] as $record) {
  echo var_dump($record);
  echo "<br>";
  $cnt++;
}

echo ($cnt - 1);


/*
$championId = $championData["championId"];


foreach($html->find('div[class=block]') as $buildRecord){


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
        $stmt = $dbh->prepare("INSERT INTO LoLItem (itemId, itemName) VALUES (?, ?)");
        $stmt->bindParam(1, $itemId);
        $stmt->bindParam(2, $itemName);
        $stmt->execute();

      }catch(PDOException $e){
        echo $e->getMessage();
        die();

      }catch(Exception $e2){
        echo $e->getMessage();
        die();
      }

    }
  }
}
*/
echo "<br>End: " . date("H:i:s");

// it didn't work when adding private or public
function getBuildHistoryCordsFromPage($htmlPage){
  foreach ($htmlPage->find('<script type="text/javascript">') as $javascriptContent) {
    if(strstr($javascriptContent, "window.participantId = ")){
      return $javascriptContent;
    }
  }

  return "";
}

function getParticipantIdFromJavascriptCords($javascriptContent){
  $paticipantString = strstr($javascriptContent, "window.participantId =");
  $participantId = preg_replace('/[^0-9]/', '', $paticipantString);

  return $participantId;
}

//function convertStringToJson($str){
//  $firstBranketIndex = strpos($str, "[");
//  $lastBranketIndex = strrpos($str, "]");
//}