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
if(isset($html)){
  exit("This program couldn't get html due to probably changing html structure");
}
*/

/*
// check whether I could get html object.
echo "<pre>";
echo var_dump($html);
echo "</pre>";
*/

/* 
Same champions data also appear in <ul class="search-from hidden">.
So, I wrote this codes in order to avoid adding duplicated data.
*/
$target = $html->find('ul[class=search-results-results champion-results]')[0];
$championId = 0;

$stmt = $dbh->prepare("DELETE FROM LoLChampion");
$stmt->execute();

foreach($target->find('li[class=left tooltip]') as $championRecord){
  $championName = $championRecord->find('h3')[0]->plaintext;
  $championUrl = $championRecord->find('a')[0]->href;
  $championType = $championRecord->find('p')[0]->plaintext;

  try{
    $stmt = $dbh->prepare("INSERT INTO LoLChampion (championId, championName, championUrl, championType) VALUES (?, ?, ?, ?)");

    $stmt->bindParam(1, $championId);
    $stmt->bindParam(2, $championName);
    $stmt->bindParam(3, $championUrl);
    $stmt->bindParam(4, $championType);

    $stmt->execute();

    $championId++;

  }catch(PDOException $e){
    print('Insert Error: '. $e->getMessage());
  }
}

echo "finished, insert max champion id: " . $championId;

die();

public function issetChampionData($id, $name, $url, $type){

  if(not isset($id)){
    return false;
  }

  if(not isset($name)){
    return false;
  }

  if(not isset($url)){
    return false;
  }

  if(not isset($type)){
    return false;
  }

  return true;
}

public function displayChampionData($id, $name, $url, $type){
  echo "Champion id: " . $id . "<br>" .
        "Champion name: " . $name . "<br>" .
        "Champion url: " . $url . "<br>" .
        "Champion type: " . $type ;
}