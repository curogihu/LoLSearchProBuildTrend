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
        //echo var_dump($value) . "<br>";

        /*
        $list[] = array($cnt =>
                    array("timestamp" => $value["timestamp"],
                          "itemId" => $value["itemId"]));
        */
        $insertSql .= "(" . "1" . ", " . $value["itemId"] . ", " . $value["timestamp"] . "), ";

        //echo "during: " . $insertSql . "<br>";

        $cnt++;
      }
    }
  }
}

echo "before: " . $insertSql . "<br><br>";

// -2 means an unnesessary space and an unnesesarry comma
$insertSql = substr($insertSql, 0, strlen($insertSql) - 2);
echo "after: " . $insertSql;


try{
  $stmt = $dbh->prepare($insertSql);
  $stmt->execute();

}catch(PDOException $e){
  print('PDO Error: '.$e->getMessage());

}catch(Exception $e2){
  print('Unexpected Error: '.$e->getMessage());

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