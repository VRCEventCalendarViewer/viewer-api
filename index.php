<?php

include("confidential.php");

// GETパラメーターを取得
if(isset($_GET['gcal_id']) && $_GET['gcal_id'] !== ""){
  $gcal_id = filter_input(INPUT_GET, 'gcal_id', FILTER_SANITIZE_STRING);
}

if(isset($_GET['start']) && $_GET['start'] !== ""
  && ($tmp = strtotime(filter_input(INPUT_GET, 'start', FILTER_SANITIZE_STRING))) !== false){
  $start = date('Y-m-d 00:00:00', $tmp);
}
else{
  $start = date('Y-m-01 00:00:00');
}

if(isset($_GET['end']) && $_GET['end'] !== ""
  && ($tmp = strtotime(filter_input(INPUT_GET, 'end', FILTER_SANITIZE_STRING))) !== false){
  $end = date('Y-m-d 23:59:59', $tmp);
}
else{
  $end = date('Y-m-d 23:59:59', strtotime($start . " +1 month -1 day"));
}

$dest = DEST;
$user = USER;
$pass = PASS;

try{
  $db = new PDO($dest, $user, $pass);
} catch (PDOException $ex){
  exit();
}

// DBからデータ取得
if(isset($gcal_id)){
  $sql = 'SELECT * FROM events WHERE gcal_id = :gcal_id';
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':gcal_id', $gcal_id, PDO::PARAM_STR);
}
else{
  $sql = 'SELECT * FROM events WHERE start >= :start and end <= :end';
  $stmt = $db->prepare($sql);
  //$stmt->bindValue(':itemLimit', 10, PDO::PARAM_INT);
  $stmt->bindValue(':start', $start, PDO::PARAM_STR);
  $stmt->bindValue(':end', $end, PDO::PARAM_STR);
}
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$json['events'] = $result;

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
echo json_encode($json, JSON_UNESCAPED_UNICODE);