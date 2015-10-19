<?php

include_once('IXR_Library.php');
require_once 'simplehtmldom/simple_html_dom.php';

// this code will be comment out after creating.
ini_set('display_errors', 'On');

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

$id = 0;

$baseUrl = "http://www.probuilds.net/champions";
$pageData = mb_convert_encoding(file_get_contents($baseUrl),'UTF-8','auto');
$html = str_get_html($pageData);

/*
echo "<pre>";
echo var_dump($html);
echo "</pre>";
*/

foreach($html->find('li[class=left tooltip]') as $championRecord){

  //$realName = $championRecord->find('h3[class=mb5 gold]').innerText;
  //$type = $championRecord->find('p[class=mb5 white]').innerText;

//  echo $championRecord;
  $realName = $championRecord->find('h3')[0];
  $type = $championRecord->find('li[class=left tooltip]')


//  echo "realName = " . $urlName;
//  echo "type = " . $urlName;
/*
  $stmt = $dbh->prepare("INSERT INTO LoLChampion (id, realName, urlName, type) VALUES (?, ?, ?, ?)");
  $stmt->bindParam(1, $id);
  $stmt->bindParam(2, $urlName);
  $stmt->bindParam(3, $realName);
  $stmt->bindParam(4, $type);

  $stmt->execute();
*/
  echo "insert ok<br>";

  $id++;
}

echo "finished";

//<div class='block alt'>
//foreach($html->find('div[class=property  js-property js-cassetLink]') as $house){

//echo ($cnt);