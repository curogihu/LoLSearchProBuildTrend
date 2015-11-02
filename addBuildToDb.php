<?php

define("PER_MAIL", 10);
define("BUILD_INSERT_DATE", date("Ymd"));

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

$results = $dbh->query('SELECT championId, championUrl FROM LoLChampion where championId between 59 and 68 ORDER BY championId');
$championDataArr = $results->fetchAll(PDO::FETCH_ASSOC);
$cnt = 0;

foreach($championDataArr as $championData){

  $pageData = mb_convert_encoding(file_get_contents($championData["championUrl"]),'UTF-8','auto');
  $html = str_get_html($pageData);
  $targetBuildList = $html->find('div[id=game-feed]')[0];

  $insertSql = "INSERT INTO LoLBuildHistory (buildInsertDate, championId, itemId, elapsedTime) VALUES ";

  foreach ($targetBuildList->find('a') as $targetBuildDetail) {
    $buildDetailUrl = $targetBuildDetail->href;

    if ($buildDetailUrl !== "#"){

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

      foreach ($obj["frames"] as $record) {

        if(array_key_exists("events", $record)){

          foreach ($record["events"] as $value) {

            if($value["eventType"] === "ITEM_PURCHASED" &&
                $value["participantId"] === $number){

              $insertSql .= "(" . BUILD_INSERT_DATE . ", " .
                                  $championData["championId"] . ", " .
                                  $value["itemId"] . ", " .
                                  $value["timestamp"] . "), ";
              $cnt++;
            }
          }
        }
      }
    }
  }

  // [-2] means an unnesessary space and an unnesesarry comma
  $insertSql = substr($insertSql, 0, strlen($insertSql) - 2);

  try{
    $stmt = $dbh->prepare($insertSql);
    $stmt->execute();
    $cnt++;

  }catch(PDOException $e){
    print('PDO Error: '.$e->getMessage());

  }catch(Exception $e2){
    print('Unexpected Error: '.$e->getMessage());

  }

  //sleep(5);
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

/*
SELECT
case
when elapsedTime <= 300000 then '00-05min'
when elapsedTime between 300001 and 600000 then '05-10min'
when elapsedTime between 600001 and 1200000 then '10-20min'
when elapsedTime between 1200001 and 1800000 then '20-30min'
else '30-XXmin'
end as periodCategory, itemId
FROM LoLBuildHistory
*/

/*
//result checked -> correct
select accumTable.periodCategory, accumTable.itemId, accumTable.displayItemName, accumTable.displayItemImagePath, count(accumTable.itemId) as frequent
from
(
SELECT case when lbh.elapsedTime <= 300000 then '00-05min'
            when lbh.elapsedTime between 300001 and 600000 then '05-10min'
            when lbh.elapsedTime between 600001 and 1200000 then '10-20min'
            when lbh.elapsedTime between 1200001 and 1800000 then '20-30min'
            else '30-XXmin'
       end as periodCategory,
       lbh.itemId,
       ifnull(li.itemName, "") as displayItemName,
       ifnull(li.itemImagePath, "") as displayItemImagePath
FROM LoLBuildHistory lbh left join LoLItem li on lbh.itemId = li.itemId
where championId = 21
) as accumTable
group by accumTable.periodCategory, accumTable.itemId, accumTable.displayItemName, accumTable.displayItemImagePath
order by accumTable.periodCategory, accumTable.frequent desc, accumTable.itemId
*/





/*
//result checked -> correct
select tmpTable.periodCategory, tmpTable.itemId, count(tmpTable.itemId) as frequent
from
(
SELECT case when elapsedTime <= 300000 then '00-05min'
            when elapsedTime between 300001 and 600000 then '05-10min'
            when elapsedTime between 600001 and 1200000 then '10-20min'
            when elapsedTime between 1200001 and 1800000 then '20-30min'
            else '30-XXmin'
       end as periodCategory,
       itemId
FROM LoLBuildHistory
where championId = 21
) as tmpTable 
group by tmpTable.periodCategory, tmpTable.itemId
*/