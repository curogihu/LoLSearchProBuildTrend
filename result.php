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


//getSQLForResearchBuild($_SESSION["targetChampionId"])
  //echo "string = " . getSQLForResearchBuild($_SESSION["targetChampionId"]);

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

  <div id="header">
    <p>Test header</p>
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
<!--
  <div id="footer">
    <p>Test footer</p>
  </div>
-->
</body>
</html>

<?php

function createOptionList($championArr){
  foreach($championArr as $championData){
    if($championData["championId"] !== $_SESSION["targetChampionId"]){
      // <option value="Ahri">Ahri</option>
      echo "<option value='" . $championData["championId"] . "'>" .
              str_replace("\"", "&#92;", $championData["championName"]) . "</option>";

    }else{
      // bug <option value="Braum" selected="">Braum</option>
      echo "<option value='" . $$championData["championId"] . "' selected>" .
              str_replace("\"", "&#92;", $championData["championName"]) . "</option>";

    }
  }
}

function createBuildList($buildArr){

  $basePeriodCategory = "";
  $baseCnt = "";
  $outputStr = "";

  $tableCnt = 1;
  $buildRank = 1;
  $lastArrIdx = count($buildArr);

  for($idx = 0; $idx < $lastArrIdx; $idx++){
    //$outputStr .= $buildArr[$idx]["periodCategory"];

    if($basePeriodCategory !== $buildArr[$idx]["periodCategory"]){
      $outputStr .= "<div id='build" . $tableCnt . "'>";
      $outputStr .= "<h2>" . $buildArr[$idx]["periodCategory"] . "</h2>";
      $outputStr .= "<table id='buildTable" . $tableCnt . "'>";
      $outputStr .= "<tr>";
      $outputStr .= "<th>Item Name</th>";
      $outputStr .= "<th>Item Icon</th>";
      $outputStr .= "<th>Frequent</th>";
      $outputStr .= "</tr>";

      $basePeriodCategory = $buildArr[$idx]["periodCategory"];
      $baseCnt = $buildArr[$idx]["frequent"];
    }

    if($buildArr[$idx]["frequent"] !== $baseCnt){
      $baseCnt = $buildArr[$idx]["frequent"];
      $buildRank++;
    }

    $outputStr .="<tr id='buildRank" . $buildRank . "'>";
    $outputStr .="<td>" . $buildArr[$idx]["displayItemName"] . "</td>";
    $outputStr .="<td>" . $buildArr[$idx]["displayItemImagePath"] . "</td>";
    $outputStr .="<td>" . $buildArr[$idx]["frequent"] . "</td>";
    $outputStr .="</tr>";

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
          "count(accumTable.itemId) as frequent " .
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
          "frequent desc, " .
          "accumTable.itemId asc ";
}

/*
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
order by accumTable.periodCategory, frequent desc, accumTable.itemId
*/



/*
select lbh.championId, lbh.buildId, lbh.elapsedTime, lbh.itemid, li.itemName, ifnull(li.itemImagePath,"") as displayItemImagePath from LoLBuildHistory lbh left join LoLItem li on lbh.itemId = li.itemId
*/