<?php

//inctioのライブラリ呼び出し
include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';

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


$baseUrl = "http://www.probuilds.net/champions/Garen";
$pageData = mb_convert_encoding(file_get_contents($baseUrl),'UTF-8','auto');
$html = str_get_html($pageData);
$targetBuildList = $html->find('div[id=game-feed]')[0];

foreach ($targetBuildList->find('a') as $targetBuildDetail) {
  $buildDetailUrl = $targetBuildDetail->href;

  if ($buildDetailUrl !== "#"){
    //echo $tmpStr . "<br>";

    // insert
    $buildDetailPageData = mb_convert_encoding(file_get_contents($buildDetailUrl),'UTF-8','auto');
    $buildDetailHtml = str_get_html($buildDetailPageData);

    // extract JSON inside javascript tag
    $test = getBuildHistoryCordsFromPage($buildDetailHtml);
    $number = getParticipantIdFromJavascriptCords($test);

    $firstBranketIndex = strpos($test, "{");
    $lastBranketIndex = strrpos($test, "}");
    $extractStr = mb_substr($test, $firstBranketIndex, $lastBranketIndex - $firstBranketIndex + 1);
    $obj = json_decode($extractStr, true);

    $cnt = 0;
    $insertSql = "INSERT INTO LoLBuildHistory (championId, itemId, elapsedTime) VALUES ";

    //echo "before: " . $insertSql . "<br>";

    foreach ($obj["frames"] as $record) {

      if(array_key_exists("events", $record)){

        foreach ($record["events"] as $value) {

          if($value["eventType"] === "ITEM_PURCHASED" &&
              $value["participantId"] === $number){

            $insertSql .= "(" . "30" . ", " . $value["itemId"] . ", " . $value["timestamp"] . "), ";
            $cnt++;
          }
        }
      }
    }

    // [-2] means an unnesessary space and an unnesesarry comma
    $insertSql = substr($insertSql, 0, strlen($insertSql) - 2);

    try{
      $stmt = $dbh->prepare($insertSql);
      $stmt->execute();

    }catch(PDOException $e){
      print('PDO Error: '.$e->getMessage());

    }catch(Exception $e2){
      print('Unexpected Error: '.$e->getMessage());

    }

  }
}

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

  return intval($participantId);
}