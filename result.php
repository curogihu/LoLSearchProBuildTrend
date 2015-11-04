<?php
//session_start();

ini_set('display_errors', 'On');

$dsn = 'mysql:dbname=LoLItemHistory;host=localhost';
$user = 'root';
$password = 'root';

$_SESSION["targetChampionId"] = $_POST["targetChampionId"];

if(empty($_SESSION["targetChampionId"])){
  echo "post value = nothing. <br>";

}else{
  echo "post value = " . $_SESSION["targetChampionId"] . ".<br>";

}

try{
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch (PDOException $e){
    echo $e->getMessage();
    die();
}

try{
  $results = $dbh->query("SELECT * FROM LoLChampion ORDER BY championId");
  $championDataArr = $results->fetchAll(PDO::FETCH_ASSOC);

  if(!empty($_SESSION["targetChampionId"])){
    $buildResults = $dbh->query(getSQLForResearchBuild($_SESSION["targetChampionId"]));
    $buildArr = $buildResults->fetchAll(PDO::FETCH_ASSOC);
  }

}catch(Exception $e){
  echo $e->getMessage();
  die();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" type="text/css" href="default.css">
  <title>Document</title>
</head>

<body>

  <div id="adLeft">
    <p>left side</p>
  </div>

  <div id="contents">
    <div id="header">
      <p>Test header</p>
      <h1>LoL Build Trend</h1>
    </div>

    <div id="container">
      <div id="searchForm">
        <form action="result.php" method="post">
          <select name="targetChampionId" id="targetChampionId">
          <?php echo createOptionList($championDataArr, $_SESSION["targetChampionId"]); ?>
          </select>

          <input type="submit" value="search">
        </form>
      </div>

      <div id="resultList">
        <?php echo createBuildList($buildArr); ?>
      </div>
    </div>
  </div>

  <div id="adRight">
    <p>right side</p>
  </div>
<!--
  <div id="footer">
    <p>Test footer</p>
  </div>
-->
</body>
</html>

<?php

function createOptionList($championArr){
  $outputStr = "";

  foreach($championArr as $championData){
    if($championData["championId"] !== $_SESSION["targetChampionId"]){
      // <option value="Ahri">Ahri</option>
      $outputStr .= "<option value='" . $championData["championId"] . "'>";

    }else{
      // bug <option value="Braum" selected="">Braum</option>
      $outputStr .= "<option value='" . $$championData["championId"] . "' selected>";
    }

    $outputStr .= "[" . sprintf("%03d", $championData["championId"]) . "]" .
                    convertStringForHTML($championData["championName"]) . "</option>";
  }

  return $outputStr;
}

function createBuildList($buildArr){

  $basePeriodCategory = "";
  $baseCnt = "";
  $outputStr = "";

  $tableCnt = 1;
  $buildRank = 1;
  $lastArrIdx = count($buildArr) - 1;

  for($idx = 0; $idx <= $lastArrIdx; $idx++){

    if($basePeriodCategory !== $buildArr[$idx]["periodCategory"]){
      $outputStr .= "<div id='build" . $tableCnt . "'>";
      $outputStr .= "<h2>" . $buildArr[$idx]["periodCategory"] . "</h2>";
      $outputStr .= "<table id='buildTable" . $tableCnt . "'>";
      $outputStr .= "<tr>";
      $outputStr .= "<th class='itemName'>Item Name</th>";
      $outputStr .= "<th class='itemImage'>Image</th>";
      $outputStr .= "<th class='itemFrequency'>frequency</th>";
      $outputStr .= "</tr>";

      $basePeriodCategory = $buildArr[$idx]["periodCategory"];
      $baseCnt = $buildArr[$idx]["frequency"];
    }

    if($buildArr[$idx]["frequency"] !== $baseCnt){
      $baseCnt = $buildArr[$idx]["frequency"];
      $buildRank++;
    }

    $outputStr .="<tr class='buildRank" . $buildRank . "'>";

    // modify
    $outputStr .='<td class="itemName">' . convertStringForHTML($buildArr[$idx]["displayItemName"]) . '</td>';

    if(!empty($buildArr[$idx]["displayItemImagePath"])){
/*
      $outputStr .= '<td class="itemImage"><img src="images/' . $buildArr[$idx]["displayItemImagePath"] .
                      '" alt="found" title="' . convertStringForHTML($buildArr[$idx]["displayItemName"]) . '"></td>';
*/
      $outputStr .= '<td class="itemImage"><img src="images/' . $buildArr[$idx]["displayItemImagePath"] .
                      '" alt="found" title="test"></td>';

    }else{
      $outputStr .= "<td class='itemImage'><img src='images/item_notfound.jpg' alt='notfound' title='" . $buildArr[$idx]["itemId"] . "'></td>";
    }

    $outputStr .="<td class='itemFrequency'>" . $buildArr[$idx]["frequency"] . "</td>";
    $outputStr .="</tr>";

    //echo "idx = " . $idx . "<br>";
    //echo "lastArrIdx = " . $lastArrIdx . "<br>";

    if(($idx + 1 < $lastArrIdx) &&
        ($basePeriodCategory !== $buildArr[$idx + 1]["periodCategory"]) || 
        $idx === $lastArrIdx){
      $outputStr .= "</table>";
      $outputStr .= "</div>";

      $tableCnt++;
      $buildRank = 1;
    }
  }


  return $outputStr;
}

function getSQLForResearchBuild($championId){
  return "select accumTable.periodCategory, " .
          "accumTable.itemId, " .
          "accumTable.displayItemName, " .
          "accumTable.displayItemImagePath, " .
          "count(accumTable.itemId) as frequency " .
          "from (" .
          "select CASE WHEN lbh.elapsedTime <= 300000 THEN '00-05min' " .
          "WHEN lbh.elapsedTime between 300001 and 600000 THEN '05-10min' " .
          "WHEN lbh.elapsedTime between 600001 and 1200000 THEN '10-20min' " .
          "WHEN lbh.elapsedTime between 1200001 and 1800000 THEN '20-30min' " .
          "ELSE '30-XXmin' " .
          "END AS periodCategory, " .
          "lbh.itemId, " .
          "IFNULL(li.itemName, '') as displayItemName, " .
          "IFNULL(li.itemImagePath, '') as displayItemImagePath " .
          "FROM LoLBuildHistory lbh LEFT JOIN LoLItem li ON lbh.itemId = li.itemId " .
          "WHERE championId = " .  $championId . " " .
          ") as accumTable " .
          "group by accumTable.periodCategory, " .
          "accumTable.itemId, " .
          "accumTable.displayItemName, " .
          "accumTable.displayItemImagePath " .
          "order by accumTable.periodCategory asc, " .
          "frequency desc, " .
          "accumTable.itemId asc ";
}


// this method was created for improving my knowledge.
function convertStringForHTML($str){
  // if forgetting correcting champion name from [/'] to ['].
  //$str = str_replace("\"", "&#92;", $str);

  // the following cords aren't necessary even if the data include special characters.
  //$str = str_replace("'", "&#39;", $str);
  //$str = str_replace("&", "&#38;", $str);

  return $str;
}

/*
select accumTable.periodCategory, accumTable.itemId, accumTable.displayItemName, accumTable.displayItemImagePath, count(accumTable.itemId) as frequency
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
order by accumTable.periodCategory, frequency desc, accumTable.itemId
*/

/*
select lbh.championId, lbh.buildId, lbh.elapsedTime, lbh.itemid, li.itemName, ifnull(li.itemImagePath,"") as displayItemImagePath from LoLBuildHistory lbh left join LoLItem li on lbh.itemId = li.itemId
*/