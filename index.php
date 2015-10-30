<?php
session_start();

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

try{
  $results = $dbh->query("SELECT * FROM LoLChampion ORDER BY championId");
  $championDataArr = $results->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
  echo $e->getMessage();
  die();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
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
        <?php echo createOptionList($championDataArr); ?>
        </select>

        <input type="submit" value="search">
      </form>
    </div>

    <div id="resultList">
      <p>00-05min</p>
      <table border="1">
        <tr>
          <td>Item1</td>
          <td>Item2</td>
          <td>Item3</td>
          <td>Item4</td>
          <td>Item5</td>
        </tr>
        <tr>
          <td>20</td>
          <td>12</td>
          <td>8</td>
          <td>5</td>
          <td>3</td>
        </tr>
      </table>

      <p>06-10min</p>
      <table border="1">
        <tr>
          <td>Item1</td>
          <td>Item2</td>
          <td>Item3</td>
          <td>Item4</td>
          <td>Item5</td>
        </tr>
        <tr>
          <td>20</td>
          <td>12</td>
          <td>8</td>
          <td>5</td>
          <td>3</td>
        </tr>
      </table>

      <p>11-20min</p>
      <table border="1">
        <tr>
          <td>Item1</td>
          <td>Item2</td>
          <td>Item3</td>
          <td>Item4</td>
          <td>Item5</td>
        </tr>
        <tr>
          <td>20</td>
          <td>12</td>
          <td>8</td>
          <td>5</td>
          <td>3</td>
        </tr>
      </table>

      <p>21-30min</p>
      <table border="1">
        <tr>
          <td>Item1</td>
          <td>Item2</td>
          <td>Item3</td>
          <td>Item4</td>
          <td>Item5</td>
        </tr>
        <tr>
          <td>20</td>
          <td>12</td>
          <td>8</td>
          <td>5</td>
          <td>3</td>
        </tr>
      </table>

      <p>30-99min</p>
      <table border="1">
        <tr>
          <td>Item1</td>
          <td>Item2</td>
          <td>Item3</td>
          <td>Item4</td>
          <td>Item5</td>
        </tr>
        <tr>
          <td>20</td>
          <td>12</td>
          <td>8</td>
          <td>5</td>
          <td>3</td>
        </tr>
      </table>
    </div>
  </div>

  <div id="footer">
    <p>Test footer</p>
  </div>
</body>
</html>

<?php

function createOptionList($championArr){
  foreach($championArr as $championData){
    echo "<option value='" . $championData["championId"] . "'>" . str_replace("\"", "&#92;", $championData["championName"]) . "</option>";
  }
}
