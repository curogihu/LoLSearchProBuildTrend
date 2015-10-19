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

}catch(PDOException $e){
    print('Connect Error: '.$e->getMessage());
    die();
}

date_default_timezone_set("Asia/Tokyo");

//$championId = 0;

$baseUrl = "http://www.probuilds.net/champions";
$pageData = mb_convert_encoding(file_get_contents($baseUrl),'UTF-8','auto');
$html = str_get_html($pageData);

/*
// check whether I could get html object.
echo "<pre>";
echo var_dump($html);
echo "</pre>";
*/

$target = $html->find('ul[class=search-results-results champion-results]')[0];
$cnt = 0;

foreach($target->find('li[class=left tooltip]') as $championRecord){
  $championName = $championRecord->find('h3')[0]->plaintext;
  $championUrl = $championRecord->find('a')[0]->href;
  $championType = $championRecord->find('p')[0]->plaintext;

  try{
    $stmt = $dbh->prepare("INSERT INTO LoLChampion (championName, championUrl, championType) VALUES (?, ?, ?)");
    
    $stmt->bindParam(1, $championName);
    $stmt->bindParam(2, $championUrl);
    $stmt->bindParam(3, $championType);

    $stmt->execute();

    $cnt++;

  }catch(PDOException $e){
    print('Insert Error: '.$e->getMessage());
  }

  

  //echo "insert ok<br>";

//  $championId++;
}

echo "finished, insert cnt: " . $cnt;

die();